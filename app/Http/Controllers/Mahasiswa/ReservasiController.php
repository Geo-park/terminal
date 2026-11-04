<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\AttendanceSession;
use App\Models\Computer;
use App\Models\Reservation;
use App\Services\LabScheduleService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReservasiController extends Controller
{
    /**
     * Tampilkan formulir pengajuan reservasi komputer.
     */
    public function create()
    {
        LabScheduleService::syncExpiredSchedules();

        $user = auth()->user();
        $computers = Computer::where('is_active', true)->orderBy('computer_code')->get();

        // Cek kuota sesi hari ini (BR-007: Maks 2 sesi berhasil per mahasiswa per hari)
        // Aturan: Kuota reservasi diisi ulang sehabis waktu operasional dibalikin lagi jadi full (2 sesi)
        // Khusus Akun VIP / Tester: Kuota sesi unlimited (tanpa batas)
        $today = Carbon::today('Asia/Jakarta');
        $now = Carbon::now('Asia/Jakarta');
        $isOperatingHoursOver = ($now->hour >= 16 || $now->hour < 7);
        if (session()->has('simulasi_jam_kerja')) {
            $isOperatingHoursOver = !session('simulasi_jam_kerja');
        }

        $isUnlimited = (bool) ($user->is_unlimited_session ?? false);

        if ($isUnlimited) {
            $usedSessionsToday = AttendanceSession::where('user_id', $user->id)
                ->whereDate('check_in_at', $today)
                ->whereIn('status', ['ACTIVE', 'COMPLETED'])
                ->count();
            $remainingQuotaToday = 999;
        } elseif ($isOperatingHoursOver) {
            $usedSessionsToday = 0;
            $remainingQuotaToday = 2;
        } else {
            $usedSessionsToday = AttendanceSession::where('user_id', $user->id)
                ->whereDate('check_in_at', $today)
                ->whereIn('status', ['ACTIVE', 'COMPLETED'])
                ->count();
            $remainingQuotaToday = max(0, 2 - $usedSessionsToday);
        }

        // Ambil reservasi aktif / pending mahasiswa saat ini jika ada
        $activeReservations = Reservation::with('computer')
            ->where('user_id', $user->id)
            ->whereIn('status', ['PENDING_APPROVAL', 'APPROVED', 'CHECKED_IN'])
            ->orderBy('start_at', 'asc')
            ->get();

        return view('mahasiswa.reservasi.create', compact('user', 'computers', 'remainingQuotaToday', 'isUnlimited', 'activeReservations'));
    }

    /**
     * Simpan pengajuan reservasi baru ke database.
     */
    public function store(Request $request)
    {
        LabScheduleService::syncExpiredSchedules();

        $user = auth()->user();

        // Validasi input form dasar
        $validated = $request->validate([
            'computer_id' => ['required', 'exists:computers,id'],
            'reservation_date' => ['required', 'date', 'after_or_equal:today'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'purpose' => ['required', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:500'],
        ], [
            'computer_id.required' => 'Pilih komputer workstation yang ingin dipinjam.',
            'computer_id.exists' => 'Komputer yang dipilih tidak valid.',
            'reservation_date.required' => 'Tanggal reservasi wajib dipilih.',
            'reservation_date.after_or_equal' => 'Tanggal reservasi tidak boleh tanggal lampau.',
            'start_time.required' => 'Jam mulai peminjaman wajib diisi.',
            'end_time.required' => 'Jam selesai peminjaman wajib diisi.',
            'end_time.after' => 'Jam selesai harus lebih akhir dari jam mulai.',
            'purpose.required' => 'Keperluan penggunaan komputer wajib dipilih/diisi.',
        ]);

        $dateCarbon = Carbon::parse($validated['reservation_date'], 'Asia/Jakarta');

        // BR-003: Hari Operasional Senin - Jumat (Weekday)
        if ($dateCarbon->isWeekend()) {
            return back()->withInput()->withErrors([
                'reservation_date' => 'Laboratorium Data Sains hanya beroperasi pada hari kerja (Senin–Jumat).',
            ]);
        }

        $startAt = Carbon::createFromFormat('Y-m-d H:i', "{$validated['reservation_date']} {$validated['start_time']}", 'Asia/Jakarta');
        $endAt = Carbon::createFromFormat('Y-m-d H:i', "{$validated['reservation_date']} {$validated['end_time']}", 'Asia/Jakarta');

        // Jika reservasi untuk hari ini, pastikan jam mulai belum terlewat
        if ($dateCarbon->isToday('Asia/Jakarta') && $startAt->lt(Carbon::now('Asia/Jakarta'))) {
            return back()->withInput()->withErrors([
                'start_time' => 'Jam mulai reservasi untuk hari ini sudah terlewat dari waktu saat ini (' . Carbon::now('Asia/Jakarta')->format('H:i') . ' WIB).',
            ]);
        }

        // BR-002: Jam operasional 07.00 - 16.00 WIB
        $earliestTime = Carbon::createFromFormat('Y-m-d H:i', "{$validated['reservation_date']} 07:00", 'Asia/Jakarta');
        $latestTime = Carbon::createFromFormat('Y-m-d H:i', "{$validated['reservation_date']} 16:00", 'Asia/Jakarta');

        if ($startAt->lt($earliestTime) || $endAt->gt($latestTime)) {
            return back()->withInput()->withErrors([
                'start_time' => 'Jam operasional laboratorium adalah pukul 07.00 – 16.00 WIB.',
            ]);
        }

        // BR-005: Durasi maksimal 5 jam per sesi
        $durationMinutes = $startAt->diffInMinutes($endAt);
        if ($durationMinutes > 300) {
            return back()->withInput()->withErrors([
                'end_time' => 'Durasi maksimal satu sesi peminjaman adalah 5 jam (300 menit). Pilihan Anda: ' . round($durationMinutes / 60, 1) . ' jam.',
            ]);
        }

        if ($durationMinutes < 30) {
            return back()->withInput()->withErrors([
                'end_time' => 'Durasi minimal peminjaman adalah 30 menit.',
            ]);
        }

        // BR-007: Kuota harian (Maksimal 2 sesi berhasil per mahasiswa per hari)
        // Aturan: Kuota reservasi diisi ulang sehabis waktu operasional dibalikin lagi jadi full (2 sesi)
        // Khusus Akun VIP / Tester: Bebas batas kuota harian (Unlimited)
        $isUnlimited = (bool) ($user->is_unlimited_session ?? false);
        $isOperatingHoursOver = (Carbon::now('Asia/Jakarta')->hour >= 16 || Carbon::now('Asia/Jakarta')->hour < 7);
        if (session()->has('simulasi_jam_kerja')) {
            $isOperatingHoursOver = !session('simulasi_jam_kerja');
        }

        $usedSessionsOnDate = AttendanceSession::where('user_id', $user->id)
            ->whereDate('check_in_at', $validated['reservation_date'])
            ->whereIn('status', ['ACTIVE', 'COMPLETED'])
            ->count();

        if ($dateCarbon->isToday('Asia/Jakarta') && $isOperatingHoursOver) {
            $usedSessionsOnDate = 0;
        }

        if (!$isUnlimited && $usedSessionsOnDate >= 2) {
            return back()->withInput()->withErrors([
                'reservation_date' => 'Anda telah menghabiskan kuota maksimal (2 sesi) peminjaman komputer untuk tanggal ini.',
            ]);
        }

        // Validasi: Komputer sedang dalam pemeliharaan (MAINTENANCE)
        $selectedComp = Computer::findOrFail($validated['computer_id']);
        if ($selectedComp->status === 'MAINTENANCE') {
            $maintStart = $selectedComp->maintenance_started_at ?? Carbon::minValue();
            $maintEnd = $selectedComp->maintenance_until;

            $isMaintConflict = false;
            if ($maintEnd) {
                $isMaintConflict = ($startAt->lt($maintEnd) && $endAt->gt($maintStart));
            } else {
                $isMaintConflict = true; // Indefinite maintenance
            }

            if ($isMaintConflict) {
                $untilStr = $selectedComp->maintenance_until
                    ? " hingga " . $selectedComp->maintenance_until->translatedFormat('d F Y pukul H:i') . " WIB"
                    : "";
                $reasonStr = $selectedComp->maintenance_reason
                    ? " Alasan: {$selectedComp->maintenance_reason}."
                    : "";

                return back()->withInput()->withErrors([
                    'computer_id' => "Unit komputer {$selectedComp->computer_code} ({$selectedComp->computer_name}) sedang dalam masa pemeliharaan (maintenance){$untilStr}.{$reasonStr} Silakan pilih unit komputer lain.",
                ]);
            }
        }

        // BR-020: Jadwal tidak boleh bertabrakan pada komputer yang sama
        $overlapConflict = Reservation::where('computer_id', $validated['computer_id'])
            ->whereIn('status', ['PENDING_APPROVAL', 'APPROVED', 'CHECKED_IN'])
            ->where(function ($query) use ($startAt, $endAt) {
                $query->where('start_at', '<', $endAt)
                      ->where('end_at', '>', $startAt);
            })
            ->exists();

        if ($overlapConflict) {
            $computer = Computer::find($validated['computer_id']);
            return back()->withInput()->withErrors([
                'computer_id' => "Komputer {$computer->computer_code} ({$computer->computer_name}) sudah dipesan pada rentang jam tersebut. Silakan pilih komputer lain atau sesuaikan jadwal Anda.",
            ]);
        }

        // Simpan Reservasi Baru dengan status PENDING_APPROVAL (BR-004)
        $reservation = Reservation::create([
            'user_id' => $user->id,
            'computer_id' => $validated['computer_id'],
            'reservation_date' => $validated['reservation_date'],
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'start_at' => $startAt,
            'end_at' => $endAt,
            'purpose' => $validated['purpose'],
            'notes' => $validated['notes'] ?? null,
            'status' => 'PENDING_APPROVAL',
        ]);

        return redirect()->route('mahasiswa.dashboard')->with('success', 'Pengajuan reservasi berhasil dikirim! Menunggu persetujuan Kepala Lab.');
    }
}

