<?php

namespace App\Services;

use App\Models\AttendanceSession;
use App\Models\Computer;
use App\Models\Reservation;
use Carbon\Carbon;
use Throwable;

class LabScheduleService
{
    /**
     * Sinkronisasi sesi aktif yang telah melewati batas waktu (waktu habis).
     * Ketika waktu habis (scheduled_end_at <= now) atau user diberhentikan,
     * status otomatis diselesaikan (COMPLETED) sehingga jadwal komputer
     * di buku booking dan dashboard langsung kosong kembali.
     */
    public static function syncExpiredSchedules(): void
    {
        try {
            $now = Carbon::now('Asia/Jakarta');

            // 1. Sesi aktif yang melewati scheduled_end_at -> otomatis selesai (waktu habis)
            $expiredSessions = AttendanceSession::with(['reservation', 'computer'])
                ->where('status', 'ACTIVE')
                ->whereNotNull('scheduled_end_at')
                ->where('scheduled_end_at', '<=', $now)
                ->get();

            foreach ($expiredSessions as $session) {
                $session->update([
                    'status' => 'COMPLETED',
                    'actual_end_at' => $session->scheduled_end_at,
                ]);

                if ($session->reservation && in_array($session->reservation->status, ['CHECKED_IN', 'APPROVED'])) {
                    $session->reservation->update([
                        'status' => 'COMPLETED',
                        'end_at' => $session->scheduled_end_at,
                        'end_time' => $session->scheduled_end_at->format('H:i:s'),
                    ]);
                }

                if ($session->computer && $session->computer->status !== 'MAINTENANCE') {
                    $session->computer->update(['status' => 'ONLINE_LOCKED']);
                }
            }

            // 2. Reservasi tanggal lampau (sebelum hari ini) yang masih tertinggal statusnya
            $today = Carbon::today('Asia/Jakarta');
            Reservation::whereIn('status', ['APPROVED', 'PENDING_APPROVAL'])
                ->where('reservation_date', '<', $today)
                ->update(['status' => 'CANCELLED_NO_SHOW']);

            // 3. Otomatis kembalikan status komputer dari MAINTENANCE jika waktu batas pemeliharaan telah berakhir
            $expiredMaintenances = Computer::where('status', 'MAINTENANCE')
                ->whereNotNull('maintenance_until')
                ->where('maintenance_until', '<=', $now)
                ->get();

            foreach ($expiredMaintenances as $comp) {
                $comp->update([
                    'status' => 'ONLINE_LOCKED',
                    'maintenance_started_at' => null,
                    'maintenance_until' => null,
                    'maintenance_reason' => null,
                ]);
            }

        } catch (Throwable $e) {
            report($e);
        }
    }
}
