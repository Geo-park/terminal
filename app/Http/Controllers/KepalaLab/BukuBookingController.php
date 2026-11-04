<?php

namespace App\Http\Controllers\KepalaLab;

use App\Http\Controllers\Controller;
use App\Models\AccessCode;
use App\Models\Computer;
use App\Models\Reservation;
use App\Models\User;
use App\Services\LabScheduleService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class BukuBookingController extends Controller
{
    /**
     * Tampilkan Buku Booking Khusus Kepala Lab / Dosen (Mode Pantau / Read-Only).
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
        $reservations = Reservation::with(['computer', 'user', 'attendanceSession', 'accessCode'])
            ->whereIn('status', ['APPROVED', 'CHECKED_IN', 'PENDING_APPROVAL'])
            ->whereDate('reservation_date', $selectedDate)
            ->orderBy('start_time')
            ->get();

        // Jumlah pending approval untuk badge di header/sidebar
        $pendingCount = Reservation::where('status', 'PENDING_APPROVAL')->count();

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

                    return $resStart < $slotEnd && $resEnd > $slotStart;
                });

                if ($isSlotInMaintenance) {
                    $pcBookedHours++;
                    $slotsData[] = [
                        'status' => 'MAINTENANCE',
                        'reservation' => null,
                        'reason' => $computer->maintenance_reason ?? 'Perawatan Hardware/Software',
                        'until' => $computer->maintenance_until ? $computer->maintenance_until->translatedFormat('d M, H:i').' WIB' : 'Manual',
                    ];
                } elseif ($matchingRes) {
                    $pcBookedHours++;
                    $slotsData[] = [
                        'status' => 'BOOKED',
                        'reservation' => $matchingRes,
                    ];
                } else {
                    $slotsData[] = [
                        'status' => 'AVAILABLE',
                        'reservation' => null,
                        // Dosen/Kepala Lab tidak bisa mengisi (read-only)
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
        $isPast = $selectedDate->isPast() && ! $isToday;

        // Ambil daftar pengguna untuk form tambah booking
        $users = User::where('is_active', true)->orderBy('name')->get();
        $students = $users->where('role', 'mahasiswa')->values();
        $staff = $users->where('role', '!=', 'mahasiswa')->values();

        return view('kepala-lab.buku-booking', compact(
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
            'isPast',
            'pendingCount',
            'users',
            'students',
            'staff'
        ));
    }

    /**
     * Tambahkan reservasi / booking komputer langsung oleh Kepala Lab (Auto-Approved).
     */
    public function store(Request $request)
    {
        LabScheduleService::syncExpiredSchedules();

        $validated = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'computer_id' => ['required', 'exists:computers,id'],
            'reservation_date' => ['required', 'date'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'purpose' => ['required', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:500'],
        ], [
            'user_id.required' => 'Pilih mahasiswa atau pengguna yang dipesankan.',
            'user_id.exists' => 'Pengguna yang dipilih tidak valid.',
            'computer_id.required' => 'Pilih unit workstation yang akan dipesan.',
            'computer_id.exists' => 'Komputer yang dipilih tidak valid.',
            'reservation_date.required' => 'Tanggal reservasi wajib dipilih.',
            'start_time.required' => 'Jam mulai peminjaman wajib diisi.',
            'end_time.required' => 'Jam selesai peminjaman wajib diisi.',
            'end_time.after' => 'Jam selesai harus lebih akhir dari jam mulai.',
            'purpose.required' => 'Keperluan penggunaan komputer wajib diisi.',
        ]);

        $dateCarbon = Carbon::parse($validated['reservation_date'], 'Asia/Jakarta');

        $startAt = Carbon::createFromFormat('Y-m-d H:i', "{$validated['reservation_date']} {$validated['start_time']}", 'Asia/Jakarta');
        $endAt = Carbon::createFromFormat('Y-m-d H:i', "{$validated['reservation_date']} {$validated['end_time']}", 'Asia/Jakarta');

        // Pastikan bukan tanggal lampau
        if ($dateCarbon->isPast() && ! $dateCarbon->isToday('Asia/Jakarta')) {
            return back()->withInput()->withErrors([
                'reservation_date' => 'Tanggal reservasi tidak boleh tanggal lampau.',
            ]);
        }

        // Jika reservasi untuk hari ini, pastikan jam selesai belum terlewat
        if ($dateCarbon->isToday('Asia/Jakarta') && $endAt->lte(Carbon::now('Asia/Jakarta'))) {
            return back()->withInput()->withErrors([
                'end_time' => 'Rentang jam reservasi untuk hari ini sudah terlewat dari waktu saat ini ('.Carbon::now('Asia/Jakarta')->format('H:i').' WIB).',
            ]);
        }

        // Jam operasional lab 07.00 - 16.00 WIB
        $earliestTime = Carbon::createFromFormat('Y-m-d H:i', "{$validated['reservation_date']} 07:00", 'Asia/Jakarta');
        $latestTime = Carbon::createFromFormat('Y-m-d H:i', "{$validated['reservation_date']} 16:00", 'Asia/Jakarta');

        if ($startAt->lt($earliestTime) || $endAt->gt($latestTime)) {
            return back()->withInput()->withErrors([
                'start_time' => 'Jam operasional laboratorium adalah pukul 07.00 – 16.00 WIB.',
            ]);
        }

        // Durasi minimal 30 menit
        $durationMinutes = $startAt->diffInMinutes($endAt);
        if ($durationMinutes < 30) {
            return back()->withInput()->withErrors([
                'end_time' => 'Durasi minimal booking adalah 30 menit.',
            ]);
        }

        // Cek apakah komputer sedang dalam mode pemeliharaan (MAINTENANCE)
        $selectedComp = Computer::findOrFail($validated['computer_id']);
        if ($selectedComp->status === 'MAINTENANCE') {
            $maintStart = $selectedComp->maintenance_started_at ?? Carbon::minValue();
            $maintEnd = $selectedComp->maintenance_until;

            $isMaintConflict = false;
            if ($maintEnd) {
                $isMaintConflict = ($startAt->lt($maintEnd) && $endAt->gt($maintStart));
            } else {
                $isMaintConflict = true;
            }

            if ($isMaintConflict) {
                return back()->withInput()->withErrors([
                    'computer_id' => "Unit komputer {$selectedComp->computer_code} ({$selectedComp->computer_name}) sedang dalam masa pemeliharaan (maintenance).",
                ]);
            }
        }

        // Cek tabrakan jadwal (overlap conflict)
        $overlapConflict = Reservation::where('computer_id', $validated['computer_id'])
            ->whereIn('status', ['PENDING_APPROVAL', 'APPROVED', 'CHECKED_IN'])
            ->where(function ($query) use ($startAt, $endAt) {
                $query->where('start_at', '<', $endAt)
                    ->where('end_at', '>', $startAt);
            })
            ->exists();

        if ($overlapConflict) {
            return back()->withInput()->withErrors([
                'computer_id' => "Komputer {$selectedComp->computer_code} sudah dipesan pada rentang jam tersebut. Silakan pilih unit komputer lain atau sesuaikan jam.",
            ]);
        }

        // Simpan Reservasi Baru dengan status APPROVED langsung
        $reservation = Reservation::create([
            'user_id' => $validated['user_id'],
            'computer_id' => $validated['computer_id'],
            'reservation_date' => $validated['reservation_date'],
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'start_at' => $startAt,
            'end_at' => $endAt,
            'purpose' => $validated['purpose'],
            'notes' => $validated['notes'] ?? null,
            'status' => 'APPROVED',
            'approved_by' => auth()->id(),
            'approved_at' => Carbon::now('Asia/Jakarta'),
        ]);

        // Terbitkan Kode Akses 16-Digit Kriptografis Unik (Format: XXXX-XXXX-XXXX-XXXX)
        $chars = '23456789ABCDEFGHJKLMNPQRSTUVWXYZ';
        $blocks = [];
        for ($b = 0; $b < 4; $b++) {
            $chunk = '';
            for ($c = 0; $c < 4; $c++) {
                $chunk .= $chars[random_int(0, strlen($chars) - 1)];
            }
            $blocks[] = $chunk;
        }
        $rawCode = implode('-', $blocks);
        $expiresAt = Carbon::createFromFormat(
            'Y-m-d H:i',
            $reservation->reservation_date->format('Y-m-d').' '.substr($reservation->end_time, 0, 5),
            'Asia/Jakarta'
        );

        AccessCode::create([
            'reservation_id' => $reservation->id,
            'code' => $rawCode,
            'code_hash' => hash('sha256', $rawCode),
            'expires_at' => $expiresAt,
        ]);

        $userName = $reservation->user->name ?? 'Pengguna';
        $timeStr = substr($reservation->start_time, 0, 5).' - '.substr($reservation->end_time, 0, 5);

        return redirect()->route('kepala-lab.buku-booking', ['date' => $validated['reservation_date']])
            ->with('success', "Booking untuk {$userName} di {$selectedComp->computer_code} ({$timeStr} WIB) berhasil ditambahkan! Kode Akses: {$rawCode}");
    }
}
