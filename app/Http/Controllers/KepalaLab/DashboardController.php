<?php

namespace App\Http\Controllers\KepalaLab;

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
     * Tampilkan Dashboard Pusat Kendali Laboratorium untuk Kepala Lab.
     */
    public function index(Request $request)
    {
        // Sinkronisasi otomatis sesi & reservasi yang telah melewati batas waktu
        LabScheduleService::syncExpiredSchedules();

        $today = Carbon::today('Asia/Jakarta');
        $now = Carbon::now('Asia/Jakarta');

        // 1. Antrean Reservasi yang Membutuhkan Persetujuan (Pending Approval)
        $pendingReservations = Reservation::with(['user', 'computer'])
            ->where('status', 'PENDING_APPROVAL')
            ->orderBy('reservation_date')
            ->orderBy('start_time')
            ->get();

        // 2. Sesi Billing yang Sedang Berjalan Saat Ini (Active Live Sessions)
        $activeSessions = AttendanceSession::with(['user', 'computer', 'reservation.accessCode'])
            ->where('status', 'ACTIVE')
            ->orderBy('check_in_at', 'desc')
            ->get();

        // 3. Status 4 Unit Workstation Komputer Lab
        $computers = Computer::orderBy('computer_code')->get()->map(function ($comp) {
            $currentSession = AttendanceSession::with('user')
                ->where('computer_id', $comp->id)
                ->where('status', 'ACTIVE')
                ->first();

            $comp->active_session = $currentSession;
            return $comp;
        });

        // 4. Riwayat 10 Reservasi Terakhir Hari Ini / Keseluruhan
        $recentReservations = Reservation::with(['user', 'computer', 'accessCode', 'approver'])
            ->whereIn('status', ['APPROVED', 'REJECTED', 'CHECKED_IN', 'COMPLETED', 'CANCELLED'])
            ->latest()
            ->take(10)
            ->get();

        // 5. Statistik Ringkas 3 Status: Hidup, Dipakai, Mati
        $dipakaiCount = $computers->whereNotNull('active_session')->count();
        $matiCount = $computers->filter(fn($c) => $c->status === 'MAINTENANCE' || !$c->is_active || $c->status === 'OFFLINE')->count();
        $hidupCount = $computers->filter(fn($c) => $c->status !== 'MAINTENANCE' && $c->status !== 'OFFLINE' && $c->is_active && $c->active_session === null)->count();

        $pendingCount = $pendingReservations->count();
        $activeSessionsCount = $dipakaiCount;
        $availableComputersCount = $hidupCount;
        $todayReservationsCount = Reservation::whereDate('reservation_date', $today)->count();

        return view('kepala-lab.dashboard', compact(
            'pendingReservations',
            'activeSessions',
            'computers',
            'recentReservations',
            'pendingCount',
            'activeSessionsCount',
            'availableComputersCount',
            'todayReservationsCount',
            'hidupCount',
            'dipakaiCount',
            'matiCount'
        ));
    }

    /**
     * Setujui Pengajuan Reservasi Komputer dan Terbitkan Kode Akses (BR-004 & BR-010).
     */
    public function approveReservation(Reservation $reservation)
    {
        if ($reservation->status !== 'PENDING_APPROVAL') {
            return back()->withErrors(['reservation' => 'Reservasi ini sudah diproses sebelumnya.']);
        }

        $reservation->update([
            'status' => 'APPROVED',
            'approved_by' => auth()->id(),
            'approved_at' => Carbon::now('Asia/Jakarta'),
        ]);

        // Generate Token Akses 16 Digit Kriptografis Unik (Format: XXXX-XXXX-XXXX-XXXX)
        // Karakter bebas ambigu: angka 2-9 dan huruf A-Z tanpa O, I, 0, 1
        $chars = '23456789ABCDEFGHJKLMNPQRSTUVWXYZ';
        $blocks = [];
        for ($b = 0; $b < 4; $b++) {
            $chunk = '';
            for ($c = 0; $c < 4; $c++) {
                $chunk .= $chars[random_int(0, strlen($chars) - 1)];
            }
            $blocks[] = $chunk;
        }
        $rawCode = implode('-', $blocks); // 16 digit alfanumerik dalam 4 blok (misal: 7K9P-X4M2-9AB3-C8F1)
        $expiresAt = Carbon::createFromFormat(
            'Y-m-d H:i',
            $reservation->reservation_date->format('Y-m-d') . ' ' . substr($reservation->end_time, 0, 5),
            'Asia/Jakarta'
        );

        AccessCode::create([
            'reservation_id' => $reservation->id,
            'code' => $rawCode,
            'code_hash' => hash('sha256', $rawCode),
            'expires_at' => $expiresAt,
        ]);

        $studentName = $reservation->user->name ?? 'Mahasiswa';
        $pcCode = $reservation->computer->computer_code ?? 'PC';

        return back()->with('success', "Reservasi {$studentName} pada {$pcCode} berhasil disetujui! Kode akses {$rawCode} telah otomatis diterbitkan untuk mahasiswa.");
    }

    /**
     * Tolak Pengajuan Reservasi Komputer beserta Alasan Resmi.
     */
    public function rejectReservation(Request $request, Reservation $reservation)
    {
        $request->validate([
            'rejection_reason' => ['required', 'string', 'max:500'],
        ], [
            'rejection_reason.required' => 'Berikan alasan penolakan agar mahasiswa memahami penyebabnya.',
        ]);

        if ($reservation->status !== 'PENDING_APPROVAL') {
            return back()->withErrors(['reservation' => 'Reservasi ini sudah diproses sebelumnya.']);
        }

        $reservation->update([
            'status' => 'REJECTED',
            'rejection_reason' => $request->rejection_reason,
            'approved_by' => auth()->id(),
            'approved_at' => Carbon::now('Asia/Jakarta'),
        ]);

        $studentName = $reservation->user->name ?? 'Mahasiswa';

        return back()->with('success', "Pengajuan reservasi {$studentName} telah ditolak dengan catatan alasan resmi.");
    }

    /**
     * Hentikan Paksa Sesi Billing Komputer (Intervensi Operator Kepala Lab).
     */
    public function forceEndSession(AttendanceSession $session)
    {
        if ($session->status !== 'ACTIVE') {
            return back()->withErrors(['session' => 'Sesi komputer ini sudah tidak aktif.']);
        }

        $now = Carbon::now('Asia/Jakarta');

        $session->update([
            'status' => 'FORCE_ENDED',
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

        $pcCode = $session->computer->computer_code ?? 'PC';
        $studentName = $session->user->name ?? 'Mahasiswa';

        return back()->with('success', "Sesi billing {$studentName} di unit {$pcCode} telah berhasil dihentikan paksa. Komputer dan jadwal langsung kosong kembali.");
    }

    /**
     * Atur Mode Pemeliharaan (Maintenance) Unit Komputer dengan Pilihan Durasi (Jam s/d Hari).
     */
    public function toggleMaintenance(Request $request, Computer $computer)
    {
        $now = Carbon::now('Asia/Jakarta');

        // Jika komputer saat ini sedang dalam mode Maintenance, aksi ini akan mengaktifkannya kembali
        if ($computer->status === 'MAINTENANCE') {
            $computer->update([
                'status' => 'ONLINE_LOCKED',
                'maintenance_started_at' => null,
                'maintenance_until' => null,
                'maintenance_reason' => null,
            ]);

            return back()->with('success', "Unit {$computer->computer_code} telah berhasil diaktifkan kembali dari mode Maintenance. Komputer kini siap dipinjam.");
        }

        // Mode: Set Maintenance Baru dengan Durasi (Per Jam atau Per Hari)
        $validated = $request->validate([
            'duration_type' => ['nullable', 'in:hours,days,custom,indefinite'],
            'duration_value' => ['nullable', 'integer', 'min:1'],
            'custom_until_date' => ['nullable', 'date'],
            'custom_until_time' => ['nullable', 'date_format:H:i'],
            'maintenance_reason' => ['nullable', 'string', 'max:255'],
        ]);

        $durationType = $validated['duration_type'] ?? 'hours';
        $durationValue = (int) ($validated['duration_value'] ?? 2);
        $reason = $validated['maintenance_reason'] ?? 'Perawatan hardware & update software sistem';
        $maintenanceUntil = null;
        $infoDurasi = '';

        if ($durationType === 'hours') {
            $maintenanceUntil = $now->copy()->addHours($durationValue);
            $infoDurasi = "selama {$durationValue} jam (hingga " . $maintenanceUntil->translatedFormat('d M Y, H:i') . " WIB)";
        } elseif ($durationType === 'days') {
            $maintenanceUntil = $now->copy()->addDays($durationValue);
            $infoDurasi = "selama {$durationValue} hari (hingga " . $maintenanceUntil->translatedFormat('d M Y, H:i') . " WIB)";
        } elseif ($durationType === 'custom' && !empty($validated['custom_until_date']) && !empty($validated['custom_until_time'])) {
            $maintenanceUntil = Carbon::createFromFormat(
                'Y-m-d H:i',
                "{$validated['custom_until_date']} {$validated['custom_until_time']}",
                'Asia/Jakarta'
            );
            $infoDurasi = "hingga " . $maintenanceUntil->translatedFormat('d M Y, H:i') . " WIB";
        } else {
            // Indefinite maintenance (sampai diaktifkan kembali manual oleh Kepala Lab)
            $maintenanceUntil = null;
            $infoDurasi = "hingga diaktifkan manual kembali oleh Kepala Lab";
        }

        // Hentikan sesi aktif yang sedang berjalan di PC ini jika ada
        $activeSession = AttendanceSession::where('computer_id', $computer->id)
            ->where('status', 'ACTIVE')
            ->first();

        if ($activeSession) {
            $activeSession->update([
                'status' => 'FORCE_ENDED',
                'actual_end_at' => $now,
            ]);
            if ($activeSession->reservation) {
                $activeSession->reservation->update(['status' => 'FORCE_ENDED']);
            }
        }

        $computer->update([
            'status' => 'MAINTENANCE',
            'maintenance_started_at' => $now,
            'maintenance_until' => $maintenanceUntil,
            'maintenance_reason' => $reason,
        ]);

        return back()->with('success', "Unit {$computer->computer_code} berhasil diset ke mode Maintenance {$infoDurasi}. Mahasiswa tidak dapat mereservasi unit ini selama pemeliharaan.");
    }
}

