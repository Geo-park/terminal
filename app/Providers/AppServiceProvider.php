<?php

namespace App\Providers;

use App\Models\AttendanceSession;
use App\Models\Reservation;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer(['layouts.mahasiswa', 'layouts.kepala-lab'], function ($view) {
            \App\Services\LabScheduleService::syncExpiredSchedules();
        });

        View::composer('layouts.mahasiswa', function ($view) {
            if (Auth::check()) {
                $user = Auth::user();
                $now = Carbon::now('Asia/Jakarta');
                $today = Carbon::today('Asia/Jakarta');

                // Tangani simulasi jam operasional jika ada parameter URL
                if (request()->has('simulasi')) {
                    $mode = request()->query('simulasi');
                    if ($mode === 'jam_kerja') {
                        session(['simulasi_jam_kerja' => true]);
                    } elseif ($mode === 'tutup') {
                        session(['simulasi_jam_kerja' => false]);
                    } elseif ($mode === 'reset') {
                        session()->forget('simulasi_jam_kerja');
                    }
                }

                // Cek apakah jam operasional lab sudah selesai (Jam Operasional: 07.00 - 16.00 WIB)
                // Aturan: Setiap 1 hari sekali setelah pk 16.00 WIB, semua pesan harian otomatis hilang / dibersihkan
                $isOperatingHoursOver = ($now->hour >= 16 || $now->hour < 7);
                if (session()->has('simulasi_jam_kerja')) {
                    $isOperatingHoursOver = !session('simulasi_jam_kerja');
                }

                // Kuota harian terpakai (BR-015: Kuota dihitung setelah check-in berhasil)
                // Aturan: Kuota reservasi diisi ulang sehabis waktu operasional (pk 16.00 WIB) dibalikin lagi jadi full (2 sesi)
                // Khusus Akun VIP: Kuota sesi unlimited (tanpa batas)
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

                // Sesi Billing Aktif
                $activeSession = AttendanceSession::with('computer')
                    ->where('user_id', $user->id)
                    ->where('status', 'ACTIVE')
                    ->latest('check_in_at')
                    ->first();

                // Reservasi Disetujui (Menunggu Check-in / Kode Akses Siap)
                $approvedReservations = Reservation::with(['computer', 'accessCode', 'approver'])
                    ->where('user_id', $user->id)
                    ->where('status', 'APPROVED')
                    ->whereDate('reservation_date', '>=', $today)
                    ->orderBy('reservation_date')
                    ->orderBy('start_time')
                    ->get();

                // Reservasi Menunggu Persetujuan
                $pendingReservations = Reservation::with('computer')
                    ->where('user_id', $user->id)
                    ->where('status', 'PENDING_APPROVAL')
                    ->whereDate('reservation_date', '>=', $today)
                    ->orderBy('reservation_date')
                    ->orderBy('start_time')
                    ->get();

                // Riwayat 5 Reservasi Terakhir
                $recentReservations = Reservation::with(['computer', 'approver'])
                    ->where('user_id', $user->id)
                    ->latest()
                    ->take(5)
                    ->get();

                // Statistik Lab untuk Profil Mahasiswa
                $totalReservations = Reservation::where('user_id', $user->id)->count();
                $totalSessionsCompleted = AttendanceSession::where('user_id', $user->id)
                    ->where('status', 'COMPLETED')
                    ->count();

                // Susun SEMUA PESAN
                // ATURAN: Setiap 1 hari sekali saat jam operasional selesai (pk 16.00 WIB), semua pesan hilang / dibersihkan
                $notifications = [];

                if (!$isOperatingHoursOver) {
                    // 1. Pesan Sesi Billing Aktif
                    if ($activeSession) {
                        $endTimeStr = $activeSession->scheduled_end_at ? $activeSession->scheduled_end_at->format('H:i') : '-';
                        $notifications[] = [
                            'id' => 'notif-session-' . $activeSession->id,
                            'category' => 'active',
                            'title' => 'Sesi Billing Sedang Berjalan',
                            'message' => 'Anda sedang menggunakan unit ' . ($activeSession->computer->name ?? 'Komputer') . ' s/d pk ' . $endTimeStr . ' WIB. Harap simpan data secara berkala.',
                            'time' => 'Sedang Aktif',
                            'badge' => 'Sesi Aktif',
                            'color' => 'indigo',
                            'icon' => 'monitor',
                            'unread' => true,
                        ];
                    }

                    // 2. Pesan Reservasi Disetujui (Kode Akses Siap)
                    foreach ($approvedReservations as $approved) {
                        $code = $approved->accessCode->code ?? '-';
                        $approverName = $approved->approver->name ?? 'Kepala Lab';
                        $notifications[] = [
                            'id' => 'notif-approved-' . $approved->id,
                            'category' => 'active',
                            'title' => 'Kode Akses Siap: ' . $code,
                            'message' => 'Reservasi unit ' . ($approved->computer->name ?? 'PC') . ' pada ' . $approved->reservation_date->format('d M Y') . ' (' . substr($approved->start_time, 0, 5) . ' WIB) telah disetujui oleh ' . $approverName . '.',
                            'time' => 'Siap Check-in',
                            'badge' => 'Disetujui',
                            'color' => 'emerald',
                            'icon' => 'key',
                            'code' => $code,
                            'unread' => true,
                        ];
                    }

                    // 3. Pesan Reservasi Menunggu Persetujuan
                    foreach ($pendingReservations as $pending) {
                        $notifications[] = [
                            'id' => 'notif-pending-' . $pending->id,
                            'category' => 'active',
                            'title' => 'Reservasi Menunggu Persetujuan',
                            'message' => 'Pengajuan peminjaman ' . ($pending->computer->name ?? 'PC') . ' untuk tanggal ' . $pending->reservation_date->format('d M Y') . ' sedang diverifikasi Kepala Lab.',
                            'time' => 'Dalam Antrean',
                            'badge' => 'Menunggu',
                            'color' => 'amber',
                            'icon' => 'clock',
                            'unread' => true,
                        ];
                    }

                    // 4. Riwayat Status Reservasi Terakhir
                    foreach ($recentReservations as $recent) {
                        if ($recent->status === 'REJECTED') {
                            $approverName = $recent->approver->name ?? 'Kepala Lab';
                            $notifications[] = [
                                'id' => 'notif-recent-' . $recent->id,
                                'category' => 'history',
                                'title' => 'Pengajuan Reservasi Ditolak',
                                'message' => 'Peminjaman unit ' . ($recent->computer->name ?? 'PC') . ' pada ' . $recent->reservation_date->format('d M Y') . ' ditolak oleh ' . $approverName . '. Alasan: "' . ($recent->rejection_reason ?? 'Kebijakan lab') . '"',
                                'time' => $recent->updated_at ? $recent->updated_at->diffForHumans() : 'Selesai',
                                'badge' => 'Ditolak',
                                'color' => 'rose',
                                'icon' => 'x-circle',
                                'unread' => true,
                            ];
                        } elseif (in_array($recent->status, ['COMPLETED', 'CANCELLED'])) {
                            $statusLabel = $recent->status === 'COMPLETED' ? 'Selesai Digunakan' : 'Dibatalkan';
                            $color = $recent->status === 'COMPLETED' ? 'emerald' : 'slate';
                            $notifications[] = [
                                'id' => 'notif-recent-' . $recent->id,
                                'category' => 'history',
                                'title' => 'Reservasi ' . ($recent->computer->name ?? 'PC') . ' (' . $statusLabel . ')',
                                'message' => 'Peminjaman tanggal ' . $recent->reservation_date->format('d M Y') . ' status: ' . $statusLabel . '.',
                                'time' => $recent->updated_at ? $recent->updated_at->diffForHumans() : 'Selesai',
                                'badge' => $statusLabel,
                                'color' => $color,
                                'icon' => 'history',
                                'unread' => false,
                            ];
                        }
                    }

                    // 5. Pesan Info Kuota Harian
                    if ($isUnlimited) {
                        $notifications[] = [
                            'id' => 'notif-quota',
                            'category' => 'info',
                            'title' => 'Status Kuota Akun VIP Tester',
                            'message' => 'Akun Anda memiliki akses Sesi Unlimited (Bebas melakukan reservasi tanpa batasan kuota harian).',
                            'time' => 'Hari Ini',
                            'badge' => 'Unlimited ∞',
                            'color' => 'blue',
                            'icon' => 'ticket',
                            'unread' => true,
                        ];
                    } else {
                        $notifications[] = [
                            'id' => 'notif-quota',
                            'category' => 'info',
                            'title' => 'Status Kuota Harian',
                            'message' => $remainingQuotaToday > 0 
                                ? 'Sisa kuota harian Anda: ' . $remainingQuotaToday . ' dari 2 sesi untuk hari ini.'
                                : 'Kuota reservasi harian Anda hari ini telah penuh (2/2 sesi terpakai).',
                            'time' => 'Hari Ini',
                            'badge' => $remainingQuotaToday . '/2 Sesi',
                            'color' => 'blue',
                            'icon' => 'ticket',
                            'unread' => true,
                        ];
                    }

                    // 6. Pesan Pengumuman SOP Laboratorium
                    $notifications[] = [
                        'id' => 'notif-sop',
                        'category' => 'info',
                        'title' => 'Pengumuman Jam Operasional Lab',
                        'message' => 'Laboratorium buka pk 07.00 - 16.00 WIB (Senin - Jumat). Sesi maksimal 5 jam per peminjaman.',
                        'time' => 'SOP Lab',
                        'badge' => 'Pengumuman',
                        'color' => 'slate',
                        'icon' => 'info',
                        'unread' => false,
                    ];

                    // 7. Pesan Pengingat Deep Freeze & Backup Data
                    $notifications[] = [
                        'id' => 'notif-backup',
                        'category' => 'info',
                        'title' => 'Pengingat Penyimpanan Cloud',
                        'message' => 'Komputer lab terproteksi Deep Freeze. Harap simpan hasil kerja Anda ke Google Drive / GitHub sebelum sesi selesai.',
                        'time' => 'Info Sistem',
                        'badge' => 'Penting',
                        'color' => 'amber',
                        'icon' => 'cloud',
                        'unread' => false,
                    ];
                }

                $unreadCount = collect($notifications)->where('unread', true)->count();

                $view->with('remainingQuotaToday', $remainingQuotaToday);
                $view->with('usedSessionsToday', $usedSessionsToday);
                $view->with('isUnlimited', $isUnlimited);
                $view->with('hasActiveSession', !empty($activeSession));
                $view->with('notifications', $notifications);
                $view->with('unreadCount', $unreadCount);
                $view->with('isOperatingHoursOver', $isOperatingHoursOver);
                $view->with('totalReservations', $totalReservations);
                $view->with('totalSessionsCompleted', $totalSessionsCompleted);
            }
        });
    }
}
