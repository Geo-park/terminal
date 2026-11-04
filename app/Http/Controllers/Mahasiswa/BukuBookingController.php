<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\Computer;
use App\Models\Reservation;
use App\Services\LabScheduleService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class BukuBookingController extends Controller
{
    /**
     * Tampilkan Buku Booking (Jadwal Reservasi Komputer Laboratorium).
     */
    public function index(Request $request)
    {
        // Sinkronisasi otomatis sesi & reservasi yang telah melewati batas waktu
        LabScheduleService::syncExpiredSchedules();

        $today = Carbon::today('Asia/Jakarta');

        // Tangkap tanggal yang dipilih atau default ke hari ini
        $dateParam = $request->query('date');
        try {
            $selectedDate = $dateParam ? Carbon::parse($dateParam, 'Asia/Jakarta')->startOfDay() : $today->copy();
        } catch (\Exception $e) {
            $selectedDate = $today->copy();
        }

        // Ambil 4 unit workstation aktif
        $computers = Computer::where('is_active', true)->orderBy('computer_code')->get();

        // Ambil semua reservasi aktif pada tanggal yang dipilih (Disetujui, Digunakan, Menunggu)
        // Reservasi yang selesai/dihentikan paksa langsung kosong kembali
        $reservations = Reservation::with(['computer', 'user', 'attendanceSession'])
            ->whereDate('reservation_date', $selectedDate)
            ->whereIn('status', ['APPROVED', 'CHECKED_IN', 'PENDING_APPROVAL'])
            ->orderBy('start_time')
            ->get();

        // Jam operasional lab: 07.00 - 16.00 WIB (9 blok jam: 07-08, 08-09, ..., 15-16)
        $timeSlots = [];
        for ($h = 7; $h < 16; $h++) {
            $startStr = sprintf('%02d:00', $h);
            $endStr = sprintf('%02d:00', $h + 1);
            $timeSlots[] = [
                'hour' => $h,
                'label' => "{$startStr} - {$endStr}",
                'start' => $startStr,
                'end' => $endStr,
            ];
        }

        // Susun matriks jadwal per komputer
        $scheduleMatrix = [];
        $totalBookedHours = 0;

        foreach ($computers as $computer) {
            $compReservations = $reservations->where('computer_id', $computer->id);
            $slotsData = [];
            $pcBookedHours = 0;

            foreach ($timeSlots as $slot) {
                $slotStart = Carbon::parse("{$selectedDate->toDateString()} {$slot['start']}", 'Asia/Jakarta');
                $slotEnd = Carbon::parse("{$selectedDate->toDateString()} {$slot['end']}", 'Asia/Jakarta');

                // 1. Cek apakah komputer sedang dalam mode MAINTENANCE pada slot ini
                $isSlotInMaintenance = false;
                if ($computer->status === 'MAINTENANCE') {
                    $maintStart = $computer->maintenance_started_at ?? Carbon::minValue();
                    $maintEnd = $computer->maintenance_until;
                    if ($maintEnd) {
                        $isSlotInMaintenance = ($slotStart < $maintEnd && $slotEnd > $maintStart);
                    } else {
                        $isSlotInMaintenance = true;
                    }
                }

                // Cari reservasi yang mencakup jam ini
                $matchingRes = $compReservations->first(function ($res) use ($slotStart, $slotEnd) {
                    $resStart = Carbon::parse("{$res->reservation_date->toDateString()} {$res->start_time}", 'Asia/Jakarta');
                    $resEnd = Carbon::parse("{$res->reservation_date->toDateString()} {$res->end_time}", 'Asia/Jakarta');
                    return ($resStart < $slotEnd && $resEnd > $slotStart);
                });

                if ($isSlotInMaintenance) {
                    $pcBookedHours++;
                    $slotsData[] = [
                        'status' => 'MAINTENANCE',
                        'reservation' => null,
                        'is_mine' => false,
                        'reason' => $computer->maintenance_reason ?? 'Perawatan Hardware/Software',
                        'until' => $computer->maintenance_until ? $computer->maintenance_until->translatedFormat('d M, H:i') . ' WIB' : 'Manual',
                    ];
                } elseif ($matchingRes) {
                    $pcBookedHours++;
                    $slotsData[] = [
                        'status' => 'BOOKED',
                        'reservation' => $matchingRes,
                        'is_mine' => ($matchingRes->user_id === auth()->id()),
                    ];
                } else {
                    $slotsData[] = [
                        'status' => 'AVAILABLE',
                        'reservation' => null,
                        'is_mine' => false,
                        'quick_reserve_url' => route('mahasiswa.reservasi.create', [
                            'computer_id' => $computer->id,
                            'date' => $selectedDate->toDateString(),
                            'start_time' => $slot['start'],
                            'end_time' => $slot['end'],
                        ]),
                    ];
                }
            }

            $totalBookedHours += $pcBookedHours;
            $scheduleMatrix[$computer->id] = [
                'computer' => $computer,
                'slots' => $slotsData,
                'booked_hours' => $pcBookedHours,
                'available_hours' => count($timeSlots) - $pcBookedHours,
                'reservations_list' => $compReservations->values(),
            ];
        }

        // Statistik Buku Booking Tanggal Terpilih
        $totalPossibleHours = count($computers) * count($timeSlots); // 4 * 9 = 36 jam PC
        $occupancyRate = $totalPossibleHours > 0 ? round(($totalBookedHours / $totalPossibleHours) * 100) : 0;
        $totalReservationsCount = $reservations->count();

        // Navigasi tanggal cepat
        $prevDate = $selectedDate->copy()->subDay()->toDateString();
        $nextDate = $selectedDate->copy()->addDay()->toDateString();
        $isToday = $selectedDate->isToday();
        $isPast = $selectedDate->isPast() && !$isToday;

        return view('mahasiswa.buku-booking.index', compact(
            'selectedDate',
            'computers',
            'timeSlots',
            'scheduleMatrix',
            'reservations',
            'totalBookedHours',
            'totalPossibleHours',
            'occupancyRate',
            'totalReservationsCount',
            'prevDate',
            'nextDate',
            'isToday',
            'isPast'
        ));
    }
}

