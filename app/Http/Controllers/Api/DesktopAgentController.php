<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AccessCode;
use App\Models\AttendanceSession;
use App\Models\Computer;
use App\Models\Reservation;
use App\Services\LabScheduleService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DesktopAgentController extends Controller
{
    /**
     * Memeriksa status operasional unit komputer laboratorium saat booting / idle.
     * GET /api/desktop/status/{computer_code}
     */
    public function getComputerStatus(string $computerCode): JsonResponse
    {
        LabScheduleService::syncExpiredSchedules();

        $computer = Computer::where('computer_code', strtoupper($computerCode))->first();

        if (!$computer) {
            return response()->json([
                'success' => false,
                'message' => "Unit komputer {$computerCode} tidak terdaftar di sistem.",
            ], 404);
        }

        $now = Carbon::now('Asia/Jakarta');

        if ($computer->isInMaintenance()) {
            return response()->json([
                'success' => true,
                'computer_code' => $computer->computer_code,
                'computer_name' => $computer->computer_name,
                'status' => 'MAINTENANCE',
                'can_login' => false,
                'message' => 'Komputer sedang dalam masa pemeliharaan (maintenance).',
                'maintenance_until' => $computer->maintenance_until?->toIso8601String(),
                'maintenance_reason' => $computer->maintenance_reason,
            ]);
        }

        $activeSession = AttendanceSession::with('user')
            ->where('computer_id', $computer->id)
            ->where('status', 'ACTIVE')
            ->first();

        if ($activeSession) {
            $remainingSeconds = max(0, $now->diffInSeconds($activeSession->scheduled_end_at, false));

            return response()->json([
                'success' => true,
                'computer_code' => $computer->computer_code,
                'computer_name' => $computer->computer_name,
                'status' => 'IN_USE',
                'can_login' => false,
                'message' => 'Komputer sedang digunakan oleh mahasiswa.',
                'session' => [
                    'session_id' => $activeSession->id,
                    'student_name' => $activeSession->user->name,
                    'nim' => $activeSession->user->nim,
                    'remaining_seconds' => $remainingSeconds,
                    'scheduled_end_at' => $activeSession->scheduled_end_at->toIso8601String(),
                ],
            ]);
        }

        return response()->json([
            'success' => true,
            'computer_code' => $computer->computer_code,
            'computer_name' => $computer->computer_name,
            'status' => 'ONLINE_LOCKED',
            'can_login' => true,
            'message' => 'Komputer siap menerima token akses check-in mahasiswa.',
        ]);
    }

    /**
     * Memvalidasi token 16-digit yang dimasukkan mahasiswa di layar monitor PC laboratorium.
     * POST /api/desktop/check-in
     */
    public function checkIn(Request $request): JsonResponse
    {
        LabScheduleService::syncExpiredSchedules();

        $validated = $request->validate([
            'computer_code' => ['required', 'string', 'exists:computers,computer_code'],
            'token' => ['required', 'string'],
        ], [
            'computer_code.required' => 'Identitas komputer laboratorium wajib disertakan.',
            'computer_code.exists' => 'Komputer laboratorium tidak valid.',
            'token.required' => 'Masukkan 16 digit token akses.',
        ]);

        $computer = Computer::where('computer_code', strtoupper($validated['computer_code']))->firstOrFail();

        // 1. Cek status pemeliharaan unit
        if ($computer->isInMaintenance()) {
            return response()->json([
                'success' => false,
                'message' => 'Komputer ini sedang dalam masa pemeliharaan teknis (maintenance) dan tidak dapat digunakan.',
            ], 422);
        }

        // 2. Cek apakah ada sesi lain yang masih aktif di PC ini
        $existingSession = AttendanceSession::where('computer_id', $computer->id)
            ->where('status', 'ACTIVE')
            ->first();

        if ($existingSession) {
            return response()->json([
                'success' => false,
                'message' => 'Komputer ini masih digunakan dalam sesi aktif lain. Harap tunggu hingga sesi selesai.',
            ], 422);
        }

        // 3. Normalisasi input token (hapus spasi, tanda strip, ubah ke uppercase)
        $rawToken = strtoupper(trim($validated['token']));
        $cleanToken = preg_replace('/[^A-Z0-9]/', '', $rawToken);

        // Cari AccessCode berdasarkan format asli atau hash sha256
        $accessCode = AccessCode::with(['reservation.user', 'reservation.computer'])
            ->where(function ($query) use ($rawToken, $cleanToken) {
                $query->where('code', $rawToken)
                    ->orWhere('code_hash', hash('sha256', $rawToken))
                    ->orWhere('code', $cleanToken)
                    ->orWhere('code_hash', hash('sha256', $cleanToken));
            })
            ->first();

        if (!$accessCode) {
            return response()->json([
                'success' => false,
                'message' => 'Token akses tidak valid. Periksa kembali 16 digit token yang Anda masukkan.',
            ], 404);
        }

        // 4. Token hanya bisa digunakan satu kali
        if ($accessCode->used_at !== null) {
            return response()->json([
                'success' => false,
                'message' => 'Token akses ini sudah pernah digunakan sebelumnya pada ' . $accessCode->used_at->translatedFormat('d M Y, H:i') . ' WIB.',
            ], 422);
        }

        $reservation = $accessCode->reservation;
        if (!$reservation) {
            return response()->json([
                'success' => false,
                'message' => 'Data reservasi untuk token ini tidak ditemukan.',
            ], 404);
        }

        // 5. Token terikat ke satu PC spesifik (tidak boleh dipakai di PC lain)
        if ($reservation->computer_id !== $computer->id) {
            $intendedPc = $reservation->computer->computer_code ?? 'lain';
            return response()->json([
                'success' => false,
                'message' => "Token ini diterbitkan khusus untuk unit {$intendedPc}. Anda tidak dapat menggunakannya di {$computer->computer_code}.",
            ], 422);
        }

        // 6. Status reservasi wajib APPROVED
        if ($reservation->status !== 'APPROVED') {
            return response()->json([
                'success' => false,
                'message' => "Pengajuan reservasi berstatus {$reservation->status} dan belum/tidak dapat digunakan.",
            ], 422);
        }

        $now = Carbon::now('Asia/Jakarta');
        $resDateStr = $reservation->reservation_date->format('Y-m-d');
        $todayStr = $now->toDateString();

        // 7. Cek kesesuaian tanggal reservasi
        if ($resDateStr !== $todayStr) {
            return response()->json([
                'success' => false,
                'message' => "Token ini dijadwalkan untuk tanggal {$resDateStr}, sedangkan hari ini adalah {$todayStr}.",
            ], 422);
        }

        $startAt = Carbon::createFromFormat('Y-m-d H:i', "{$resDateStr} " . substr($reservation->start_time, 0, 5), 'Asia/Jakarta');
        $endAt = Carbon::createFromFormat('Y-m-d H:i', "{$resDateStr} " . substr($reservation->end_time, 0, 5), 'Asia/Jakarta');

        // 8. Cek apakah jam mulai sudah tiba (belum waktunya tidak boleh mulai)
        if ($now->lt($startAt)) {
            return response()->json([
                'success' => false,
                'message' => "Waktu peminjaman belum dimulai. Sesi Anda dijadwalkan mulai pukul {$startAt->format('H:i')} WIB.",
            ], 422);
        }

        // 9. Cek apakah jam jadwal telah terlewat habis
        if ($now->gte($endAt)) {
            return response()->json([
                'success' => false,
                'message' => "Waktu jadwal reservasi telah berakhir pada pukul {$endAt->format('H:i')} WIB.",
            ], 422);
        }

        // 10. Cek apakah mahasiswa sedang memiliki sesi aktif di komputer lain
        $userActiveSession = AttendanceSession::where('user_id', $reservation->user_id)
            ->where('status', 'ACTIVE')
            ->first();

        if ($userActiveSession) {
            $otherPc = $userActiveSession->computer->computer_code ?? 'PC lain';
            return response()->json([
                'success' => false,
                'message' => "Anda masih memiliki sesi aktif yang berjalan di unit {$otherPc}. Selesaikan sesi tersebut terlebih dahulu.",
            ], 422);
        }

        // 11. Cek Kuota Harian Mahasiswa (Maksimal 2 sesi per hari, kecuali akun VIP unlimited)
        $isUnlimited = (bool) ($reservation->user->is_unlimited_session ?? false);
        $usedSessionsToday = AttendanceSession::where('user_id', $reservation->user_id)
            ->whereDate('check_in_at', $todayStr)
            ->whereIn('status', ['ACTIVE', 'COMPLETED'])
            ->count();

        if (!$isUnlimited && $usedSessionsToday >= 2) {
            return response()->json([
                'success' => false,
                'message' => 'Anda telah mencapai batas kuota harian (2 sesi pemakaian komputer per hari).',
            ], 422);
        }

        // Eksekusi Check-In Komputer Secara Atomik
        $session = DB::transaction(function () use ($reservation, $accessCode, $computer, $now, $endAt) {
            $session = AttendanceSession::create([
                'reservation_id' => $reservation->id,
                'user_id' => $reservation->user_id,
                'computer_id' => $computer->id,
                'check_in_at' => $now,
                'scheduled_end_at' => $endAt,
                'status' => 'ACTIVE',
            ]);

            $reservation->update([
                'status' => 'CHECKED_IN',
                'start_at' => $now,
            ]);

            $accessCode->update([
                'used_at' => $now,
            ]);

            $computer->update([
                'status' => 'ONLINE_LOCKED',
            ]);

            return $session;
        });

        $remainingSeconds = max(0, $now->diffInSeconds($endAt, false));

        return response()->json([
            'success' => true,
            'message' => "Check-in berhasil! Layar unit {$computer->computer_code} berhasil dibuka.",
            'session' => [
                'session_id' => $session->id,
                'student_name' => $reservation->user->name,
                'nim' => $reservation->user->nim,
                'computer_code' => $computer->computer_code,
                'computer_name' => $computer->computer_name,
                'check_in_time' => $now->format('H:i') . ' WIB',
                'scheduled_end_time' => $endAt->format('H:i') . ' WIB',
                'duration_seconds' => $remainingSeconds,
                'scheduled_end_at' => $endAt->toIso8601String(),
            ],
        ], 200);
    }

    /**
     * Heartbeat periodik dari Desktop Agent di komputer (setiap 15-30 detik).
     * POST /api/desktop/heartbeat
     */
    public function heartbeat(Request $request): JsonResponse
    {
        LabScheduleService::syncExpiredSchedules();

        $validated = $request->validate([
            'computer_code' => ['required', 'string', 'exists:computers,computer_code'],
            'session_id' => ['nullable', 'integer'],
        ]);

        $computer = Computer::where('computer_code', strtoupper($validated['computer_code']))->firstOrFail();
        $now = Carbon::now('Asia/Jakarta');

        if ($computer->isInMaintenance()) {
            return response()->json([
                'success' => true,
                'status' => 'MAINTENANCE',
                'command' => 'LOCK_NOW',
                'message' => 'Komputer diatur ke mode maintenance oleh Kepala Lab.',
            ]);
        }

        if (!empty($validated['session_id'])) {
            $session = AttendanceSession::find($validated['session_id']);

            if (!$session || $session->status !== 'ACTIVE') {
                return response()->json([
                    'success' => true,
                    'status' => 'TERMINATED',
                    'command' => 'LOCK_NOW',
                    'message' => 'Sesi billing telah diakhiri (waktu habis atau Force End oleh Kepala Lab).',
                ]);
            }

            $remainingSeconds = max(0, $now->diffInSeconds($session->scheduled_end_at, false));

            if ($remainingSeconds <= 0) {
                $session->update([
                    'status' => 'COMPLETED',
                    'actual_end_at' => $now,
                ]);

                if ($session->reservation) {
                    $session->reservation->update(['status' => 'COMPLETED', 'end_at' => $now]);
                }

                return response()->json([
                    'success' => true,
                    'status' => 'EXPIRED',
                    'command' => 'LOCK_NOW',
                    'message' => 'Waktu sesi penggunaan lab telah habis.',
                ]);
            }

            return response()->json([
                'success' => true,
                'status' => 'ACTIVE',
                'command' => 'KEEP_RUNNING',
                'remaining_seconds' => $remainingSeconds,
                'scheduled_end_at' => $session->scheduled_end_at->toIso8601String(),
            ]);
        }

        return response()->json([
            'success' => true,
            'status' => 'LOCKED',
            'command' => 'LOCK_NOW',
            'message' => 'Komputer terkunci menunggu login mahasiswa.',
        ]);
    }

    /**
     * Mengakhiri sesi pemakaian komputer saat mahasiswa logout dari layar PC.
     * POST /api/desktop/session/end
     */
    public function endSession(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'computer_code' => ['required', 'string', 'exists:computers,computer_code'],
            'session_id' => ['required', 'integer', 'exists:attendance_sessions,id'],
        ]);

        $session = AttendanceSession::with(['reservation', 'computer'])->findOrFail($validated['session_id']);

        if ($session->status !== 'ACTIVE') {
            return response()->json([
                'success' => false,
                'message' => 'Sesi ini sudah tidak aktif.',
            ], 422);
        }

        $now = Carbon::now('Asia/Jakarta');

        $session->update([
            'status' => 'COMPLETED',
            'actual_end_at' => $now,
        ]);

        if ($session->reservation) {
            $session->reservation->update([
                'status' => 'COMPLETED',
                'end_at' => $now,
                'end_time' => $now->format('H:i:s'),
            ]);
        }

        if ($session->computer && $session->computer->status !== 'MAINTENANCE') {
            $session->computer->update(['status' => 'ONLINE_LOCKED']);
        }

        return response()->json([
            'success' => true,
            'message' => 'Sesi penggunaan workstation berhasil diselesaikan. Layar komputer dikunci kembali.',
        ]);
    }
}

