<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\AccessCode;
use App\Models\AttendanceSession;
use App\Models\Computer;
use App\Models\Reservation;
use App\Services\LabScheduleService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DashboardController extends Controller
{
    /**
     * Tampilkan halaman utama Dashboard Mahasiswa.
     */
    public function index()
    {
        // Sinkronisasi otomatis sesi & jadwal yang melewati batas waktu
        LabScheduleService::syncExpiredSchedules();

        $user = auth()->user();
        $today = Carbon::today('Asia/Jakarta');
        $now = Carbon::now('Asia/Jakarta');

        // Cek apakah jam operasional lab sudah selesai (Jam Operasional: 07.00 - 16.00 WIB)
        $isOperatingHoursOver = ($now->hour >= 16 || $now->hour < 7);
        if (session()->has('simulasi_jam_kerja')) {
            $isOperatingHoursOver = !session('simulasi_jam_kerja');
        }

        $isUnlimited = (bool) ($user->is_unlimited_session ?? false);

        // Kuota harian terpakai (BR-015: Kuota dihitung setelah check-in berhasil)
        // Aturan: Kuota reservasi diisi ulang sehabis waktu operasional dibalikin lagi jadi full (2 sesi)
        // Khusus Akun VIP: Kuota unlimited (tanpa batas)
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

        // 1. Sesi Billing Aktif Mahasiswa Saat Ini (Jika sedang check-in di PC)
        $activeSession = AttendanceSession::with(['computer', 'reservation.accessCode'])
            ->where('user_id', $user->id)
            ->where('status', 'ACTIVE')
            ->latest('check_in_at')
            ->first();

        // 2. Reservasi yang Disetujui (Menunggu Check-in / Kode Akses Tersedia)
        $approvedReservation = Reservation::with(['computer', 'accessCode', 'approver'])
            ->where('user_id', $user->id)
            ->where('status', 'APPROVED')
            ->whereDate('reservation_date', '>=', $today)
            ->orderBy('reservation_date')
            ->orderBy('start_time')
            ->first();

        // 3. Reservasi Pending (Menunggu Persetujuan Kepala Lab)
        $pendingReservation = Reservation::with('computer')
            ->where('user_id', $user->id)
            ->where('status', 'PENDING_APPROVAL')
            ->whereDate('reservation_date', '>=', $today)
            ->orderBy('reservation_date')
            ->orderBy('start_time')
            ->first();

        // 4. Riwayat 5 Reservasi Terakhir
        $recentReservations = Reservation::with(['computer', 'accessCode', 'attendanceSession', 'approver'])
            ->where('user_id', $user->id)
            ->latest()
            ->take(5)
            ->get();

        // 5. Status Real-time 4 Unit Komputer Lab (Kosong, Terisi, Padat, Penuh)
        $computers = Computer::where('is_active', true)
            ->orderBy('computer_code')
            ->get()
            ->map(function ($comp) use ($user, $today) {
                // Cek apakah ada sesi aktif di PC ini saat ini
                $currentSession = AttendanceSession::with('user')
                    ->where('computer_id', $comp->id)
                    ->where('status', 'ACTIVE')
                    ->first();

                // Hitung status okupansi 4 tingkat (KOSONG, TERISI, PADAT, PENUH) & jam kosong
                $occ = $this->getComputerOccupancyInfo($comp->id, $today, $currentSession, $user->id);

                $comp->real_status = $occ['real_status'];
                $comp->status_badge = $occ['status_badge'];
                $comp->status_color = $occ['status_color'];
                $comp->status_desc = $occ['status_desc'];
                $comp->free_desc = $occ['free_desc'];
                $comp->is_own_session = $occ['is_own_session'];

                return $comp;
            });

        // 6. Buku Booking Hari Ini (Jadwal Terisi Komputer Lab)
        $todayBookings = Reservation::with(['computer', 'user'])
            ->whereDate('reservation_date', $today)
            ->whereIn('status', ['APPROVED', 'CHECKED_IN', 'PENDING_APPROVAL'])
            ->orderBy('start_time')
            ->get();

        return view('mahasiswa.dashboard', compact(
            'computers',
            'recentReservations',
            'usedSessionsToday',
            'remainingQuotaToday',
            'isUnlimited',
            'activeSession',
            'approvedReservation',
            'pendingReservation',
            'todayBookings',
            'isOperatingHoursOver'
        ));
    }

    /**
     * Akhiri Sesi Billing Komputer Lebih Awal (Model Billing Warnet - BR-018).
     */
    public function endSession(AttendanceSession $session)
    {
        // Validasi kepemilikan sesi
        if ($session->user_id !== auth()->id()) {
            abort(403, 'Akses sesi tidak sah.');
        }

        if ($session->status !== 'ACTIVE') {
            return back()->withErrors(['session' => 'Sesi komputer ini sudah tidak aktif.']);
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

            if ($session->reservation->accessCode) {
                $session->reservation->accessCode->update(['used_at' => $now]);
            }
        }

        if ($session->computer && $session->computer->status !== 'MAINTENANCE') {
            $session->computer->update(['status' => 'ONLINE_LOCKED']);
        }

        $pcCode = $session->computer->computer_code ?? 'Komputer';

        return redirect()->route('mahasiswa.dashboard')
            ->with('success', "Sesi {$pcCode} telah berhasil diakhiri. Komputer dan jadwal langsung kosong kembali.");
    }

    /**
     * Simulasi Check-in Desktop Agent (Tahap 1 Web Prototype).
     * Memulai sesi aktif menggunakan kode akses yang telah disetujui.
     */
    public function checkIn(Reservation $reservation)
    {
        if ($reservation->user_id !== auth()->id()) {
            abort(403, 'Akses reservasi tidak sah.');
        }

        if ($reservation->status !== 'APPROVED') {
            return back()->withErrors(['reservation' => 'Hanya reservasi yang berstatus DISETUJUI yang dapat melakukan check-in.']);
        }

        // Cek apakah mahasiswa sedang memiliki sesi aktif lain (BR-007)
        $hasActiveSession = AttendanceSession::where('user_id', auth()->id())
            ->where('status', 'ACTIVE')
            ->exists();

        if ($hasActiveSession) {
            return back()->withErrors(['reservation' => 'Anda masih memiliki sesi komputer lain yang sedang berjalan. Akhiri sesi tersebut terlebih dahulu.']);
        }

        $now = Carbon::now('Asia/Jakarta');
        $resDateStr = $reservation->reservation_date->format('Y-m-d');
        $startAt = Carbon::createFromFormat(
            'Y-m-d H:i',
            $resDateStr . ' ' . substr($reservation->start_time, 0, 5),
            'Asia/Jakarta'
        );
        $endAt = Carbon::createFromFormat(
            'Y-m-d H:i',
            $resDateStr . ' ' . substr($reservation->end_time, 0, 5),
            'Asia/Jakarta'
        );

        // Validasi: Belum waktunya memulai sesi billing
        if ($now->lt($startAt)) {
            $formattedStart = substr($reservation->start_time, 0, 5);
            $formattedDate = $reservation->reservation_date->translatedFormat('d F Y');
            return back()->withErrors([
                'reservation' => "Belum waktu peminjaman. Sesi billing baru dapat dimulai pada tanggal {$formattedDate} pukul {$formattedStart} WIB sesuai jadwal yang disetujui."
            ]);
        }

        // Validasi: Waktu jadwal peminjaman sudah berakhir
        if ($now->gte($endAt)) {
            return back()->withErrors([
                'reservation' => 'Waktu jadwal peminjaman komputer ini sudah berakhir.'
            ]);
        }

        $session = AttendanceSession::create([
            'reservation_id' => $reservation->id,
            'user_id' => auth()->id(),
            'computer_id' => $reservation->computer_id,
            'check_in_at' => $now,
            'scheduled_end_at' => $endAt,
            'status' => 'ACTIVE',
        ]);

        $reservation->update(['status' => 'CHECKED_IN']);

        if ($reservation->accessCode) {
            $reservation->accessCode->update(['used_at' => $now]);
        }

        $pcCode = $reservation->computer->computer_code ?? 'PC';

        return redirect()->route('mahasiswa.dashboard')
            ->with('success', "Check-in berhasil! Sesi billing {$pcCode} sekarang aktif dan waktu mulai berjalan.");
    }

    /**
     * Batalkan Reservasi Sebelum Check-in (BR-013).
     */
    public function cancelReservation(Reservation $reservation)
    {
        if ($reservation->user_id !== auth()->id()) {
            abort(403, 'Akses reservasi tidak sah.');
        }

        if (!in_array($reservation->status, ['PENDING_APPROVAL', 'APPROVED'])) {
            return back()->withErrors(['reservation' => 'Reservasi ini tidak dapat dibatalkan.']);
        }

        $reservation->update(['status' => 'CANCELLED']);

        if ($reservation->accessCode) {
            $reservation->accessCode->update(['revoked_at' => now('Asia/Jakarta')]);
        }

        return redirect()->route('mahasiswa.dashboard')
            ->with('success', 'Reservasi peminjaman komputer berhasil dibatalkan.');
    }

    /**
     * Helper Simulasi Approve oleh Kepala Lab (Untuk Memudahkan Pengujian Alur).
     */
    public function simulateApprove(Reservation $reservation)
    {
        if ($reservation->user_id !== auth()->id()) {
            abort(403, 'Akses reservasi tidak sah.');
        }

        if ($reservation->status !== 'PENDING_APPROVAL') {
            return back()->withErrors(['reservation' => 'Reservasi sudah diproses sebelumnya.']);
        }

        $reservation->update(['status' => 'APPROVED']);

        // Generate Kode Akses Unik (BR-009 & BR-010)
        $rawCode = 'LD-' . strtoupper(Str::random(4)) . '-' . strtoupper(Str::random(4));
        AccessCode::create([
            'reservation_id' => $reservation->id,
            'code' => $rawCode,
            'code_hash' => hash('sha256', $rawCode),
            'expires_at' => Carbon::createFromFormat(
                'Y-m-d H:i',
                $reservation->reservation_date->format('Y-m-d') . ' ' . substr($reservation->end_time, 0, 5),
                'Asia/Jakarta'
            ),
        ]);

        return redirect()->route('mahasiswa.dashboard')
            ->with('success', "Reservasi disetujui! Kode akses login telah diterbitkan: {$rawCode}");
    }

    /**
     * Profil Mahasiswa bersifat hanya lihat (read-only) terhubung dengan data akademik.
     */
    public function updateProfile(Request $request)
    {
        return redirect()->back()->with('error', 'Profil mahasiswa terhubung langsung dengan sistem akademik kampus dan tidak dapat diubah secara mandiri.');
    }

    /**
     * Hitung informasi okupansi unit komputer untuk hari ini (07.00 - 16.00 WIB).
     * Menghasilkan status okupansi 4 tingkat: KOSONG, TERISI, PADAT, PENUH
     * serta teks rentang jam kosong.
     */
    protected function getComputerOccupancyInfo($computerId, $today, $currentSession = null, $currentUserId = null): array
    {
        $dayStartMin = 7 * 60; // 07.00 WIB (420 menit)
        $dayEndMin = 16 * 60;  // 16.00 WIB (960 menit)

        // Ambil semua reservasi hari ini yang aktif / disetujui / pending
        $reservations = Reservation::where('computer_id', $computerId)
            ->whereDate('reservation_date', $today)
            ->whereIn('status', ['APPROVED', 'CHECKED_IN', 'PENDING_APPROVAL'])
            ->orderBy('start_time')
            ->get();

        $busyBlocks = [];

        // Jika ada sesi aktif billing saat ini
        if ($currentSession && $currentSession->scheduled_end_at) {
            $sessStart = $currentSession->check_in_at ? ($currentSession->check_in_at->hour * 60 + $currentSession->check_in_at->minute) : $dayStartMin;
            $sessEnd = $currentSession->scheduled_end_at->hour * 60 + $currentSession->scheduled_end_at->minute;
            $busyBlocks[] = [
                'start' => max($dayStartMin, $sessStart),
                'end' => min($dayEndMin, $sessEnd),
            ];
        }

        foreach ($reservations as $res) {
            $sParts = explode(':', substr($res->start_time, 0, 5));
            $eParts = explode(':', substr($res->end_time, 0, 5));
            $rStart = ((int)$sParts[0]) * 60 + ((int)($sParts[1] ?? 0));
            $rEnd = ((int)$eParts[0]) * 60 + ((int)($eParts[1] ?? 0));

            $busyBlocks[] = [
                'start' => max($dayStartMin, $rStart),
                'end' => min($dayEndMin, $rEnd),
            ];
        }

        // Urutkan blok terisi berdasarkan jam mulai
        usort($busyBlocks, function ($a, $b) {
            return $a['start'] <=> $b['start'];
        });

        // Gabungkan blok terisi yang tumpang tindih
        $mergedBusy = [];
        foreach ($busyBlocks as $block) {
            if (empty($mergedBusy)) {
                $mergedBusy[] = $block;
            } else {
                $lastIndex = count($mergedBusy) - 1;
                if ($block['start'] <= $mergedBusy[$lastIndex]['end']) {
                    $mergedBusy[$lastIndex]['end'] = max($mergedBusy[$lastIndex]['end'], $block['end']);
                } else {
                    $mergedBusy[] = $block;
                }
            }
        }

        // Hitung total menit terisi
        $totalBusyMinutes = 0;
        foreach ($mergedBusy as $busy) {
            $totalBusyMinutes += ($busy['end'] - $busy['start']);
        }

        // Susun rentang jam kosong
        $freeSlots = [];
        $cursor = $dayStartMin;

        foreach ($mergedBusy as $busy) {
            // Buffer 30 menit sebelum booking berikutnya sesuai contoh user:
            // "jika ada boking di jam 1 (13.00) berarti kosong sampai jam 12.30"
            $freeEnd = max($cursor, $busy['start'] - 30);

            // Valid jika rentang minimal 30 menit
            if ($freeEnd - $cursor >= 30) {
                $freeSlots[] = [
                    'start' => $cursor,
                    'end' => $freeEnd,
                ];
            }

            // Setelah booking selesai, unit kembali kosong mulai waktu selesai
            $cursor = max($cursor, $busy['end']);
        }

        // Sisa waktu sampai jam operasional tutup (16.00 WIB)
        if ($dayEndMin - $cursor >= 30) {
            $freeSlots[] = [
                'start' => $cursor,
                'end' => $dayEndMin,
            ];
        }

        if (empty($mergedBusy)) {
            $freeDesc = 'Kosong: 07.00 – 16.00 WIB';
        } elseif (empty($freeSlots)) {
            $freeDesc = 'Penuh (07.00 – 16.00 WIB)';
        } else {
            $formatted = [];
            foreach ($freeSlots as $slot) {
                $s = sprintf('%02d.%02d', floor($slot['start'] / 60), $slot['start'] % 60);
                $e = sprintf('%02d.%02d', floor($slot['end'] / 60), $slot['end'] % 60);
                $formatted[] = "{$s} – {$e}";
            }
            $freeDesc = 'Kosong: ' . implode(' & ', $formatted) . ' WIB';
        }

        $isOwnSession = $currentSession && $currentUserId && ($currentSession->user_id === $currentUserId);

        // Klasifikasi 4 status sesuai permintaan: Kosong, Terisi, Padat, Penuh
        if (empty($mergedBusy)) {
            $status = 'KOSONG';
            $badge = 'KOSONG';
            $color = 'emerald';
        } elseif (empty($freeSlots)) {
            $status = 'PENUH';
            $badge = 'PENUH';
            $color = 'rose';
        } elseif ($totalBusyMinutes > 180 || count($mergedBusy) >= 2) {
            // Terisi lebih dari 3 jam atau ada >= 2 sesi/reservasi -> Padat
            $status = 'PADAT';
            $badge = $isOwnSession ? 'PADAT (SESI ANDA)' : 'PADAT';
            $color = 'amber';
        } else {
            // Terisi sebagian (<= 3 jam / 1 reservasi) -> Terisi
            $status = 'TERISI';
            $badge = $isOwnSession ? 'TERISI (SESI ANDA)' : 'TERISI';
            $color = 'indigo';
        }

        return [
            'real_status' => $status,
            'status_badge' => $badge,
            'status_color' => $color,
            'status_desc' => $freeDesc,
            'free_desc' => $freeDesc,
            'is_own_session' => $isOwnSession,
            'total_busy_minutes' => $totalBusyMinutes,
            'free_slots' => $freeSlots,
        ];
    }

    /**
     * Helper kompatibilitas untuk mendapatkan teks jam kosong.
     */
    protected function getFreeTimeWindowsDesc($computerId, $today, $currentSession = null): string
    {
        return $this->getComputerOccupancyInfo($computerId, $today, $currentSession)['free_desc'];
    }
}

