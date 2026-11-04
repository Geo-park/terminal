<?php

namespace Tests\Feature;

use App\Models\AccessCode;
use App\Models\AttendanceSession;
use App\Models\Computer;
use App\Models\Reservation;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class KepalaLabPortalTest extends TestCase
{
    use RefreshDatabase;

    protected User $student;

    protected User $kepalaLab;

    protected Computer $computer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->student = User::firstOrCreate(
            ['nim' => '22081010045'],
            [
                'name' => 'M. Subagja',
                'email' => 'subagja@uinbanten.ac.id',
                'jurusan' => 'Teknik Informatika',
                'password' => Hash::make('password'),
                'role' => 'mahasiswa',
                'is_active' => true,
            ]
        );

        $this->kepalaLab = User::firstOrCreate(
            ['nim' => 'kepala_lab'],
            [
                'name' => 'Kepala Laboratorium',
                'email' => 'kepalalab@uinbanten.ac.id',
                'jurusan' => 'Laboratorium Sains Data & AI',
                'password' => Hash::make('password'),
                'role' => 'kepala_lab',
                'is_active' => true,
            ]
        );

        $this->computer = Computer::firstOrCreate(
            ['computer_code' => 'PC-01'],
            [
                'computer_name' => 'Workstation AI 01',
                'specs' => 'RTX 4070 12GB',
                'status' => 'ONLINE_LOCKED',
                'is_active' => true,
            ]
        );
    }

    public function test_student_cannot_access_kepala_lab_portal(): void
    {
        $response = $this->actingAs($this->student)->get('/lab');

        // Harus dialihkan kembali ke dashboard mahasiswa
        $response->assertRedirect(route('mahasiswa.dashboard'));
        $response->assertSessionHas('error');
    }

    public function test_kepala_lab_cannot_access_student_dashboard_and_is_redirected_to_lab(): void
    {
        $response = $this->actingAs($this->kepalaLab)->get('/dashboard');

        // Harus dialihkan ke portal Kepala Lab
        $response->assertRedirect(route('kepala-lab.dashboard'));
    }

    public function test_kepala_lab_can_view_lab_dashboard_and_station_grid(): void
    {
        $response = $this->actingAs($this->kepalaLab)->get('/lab');

        $response->assertStatus(200);
        $response->assertSee('Portal Kepala Laboratorium');
        $response->assertSee('Kepala Lab');
        $response->assertSee('PC-01');
        $response->assertSee('Menunggu Approval');
        $response->assertDontSee('Unit Workstation');
    }

    public function test_kepala_lab_can_approve_pending_reservation_and_generate_access_code(): void
    {
        $today = Carbon::today('Asia/Jakarta');

        $reservation = Reservation::create([
            'user_id' => $this->student->id,
            'computer_id' => $this->computer->id,
            'reservation_date' => $today,
            'start_time' => '10:00',
            'end_time' => '12:00',
            'start_at' => Carbon::parse("{$today->toDateString()} 10:00:00", 'Asia/Jakarta'),
            'end_at' => Carbon::parse("{$today->toDateString()} 12:00:00", 'Asia/Jakarta'),
            'purpose' => 'Riset Skripsi AI',
            'status' => 'PENDING_APPROVAL',
        ]);

        $response = $this->actingAs($this->kepalaLab)->post(route('kepala-lab.reservasi.approve', $reservation));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $reservation->refresh();
        $this->assertEquals('APPROVED', $reservation->status);
        $this->assertEquals($this->kepalaLab->id, $reservation->approved_by);
        $this->assertNotNull($reservation->approved_at);
        $this->assertEquals('Kepala Laboratorium', $reservation->approver->name);

        // Pastikan token akses 16 digit diterbitkan (Format: XXXX-XXXX-XXXX-XXXX)
        $this->assertNotNull($reservation->accessCode);
        $this->assertMatchesRegularExpression('/^[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}$/', $reservation->accessCode->code);
    }

    public function test_kepala_lab_can_reject_reservation_with_reason(): void
    {
        $today = Carbon::today('Asia/Jakarta');

        $reservation = Reservation::create([
            'user_id' => $this->student->id,
            'computer_id' => $this->computer->id,
            'reservation_date' => $today,
            'start_time' => '13:00',
            'end_time' => '15:00',
            'start_at' => Carbon::parse("{$today->toDateString()} 13:00:00", 'Asia/Jakarta'),
            'end_at' => Carbon::parse("{$today->toDateString()} 15:00:00", 'Asia/Jakarta'),
            'purpose' => 'Praktikum Mandiri',
            'status' => 'PENDING_APPROVAL',
        ]);

        $response = $this->actingAs($this->kepalaLab)->post(route('kepala-lab.reservasi.reject', $reservation), [
            'rejection_reason' => 'Jadwal lab bertabrakan dengan maintenance server.',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $reservation->refresh();
        $this->assertEquals('REJECTED', $reservation->status);
        $this->assertEquals('Jadwal lab bertabrakan dengan maintenance server.', $reservation->rejection_reason);
        $this->assertEquals($this->kepalaLab->id, $reservation->approved_by);
        $this->assertNotNull($reservation->approved_at);
        $this->assertEquals('Kepala Laboratorium', $reservation->approver->name);
    }

    public function test_kepala_lab_can_force_end_active_session(): void
    {
        $today = Carbon::today('Asia/Jakarta');

        $reservation = Reservation::create([
            'user_id' => $this->student->id,
            'computer_id' => $this->computer->id,
            'reservation_date' => $today,
            'start_time' => '08:00',
            'end_time' => '11:00',
            'start_at' => Carbon::now('Asia/Jakarta')->subHour(),
            'end_at' => Carbon::now('Asia/Jakarta')->addHour(),
            'purpose' => 'Pelatihan AI',
            'status' => 'CHECKED_IN',
        ]);

        $session = AttendanceSession::create([
            'reservation_id' => $reservation->id,
            'user_id' => $this->student->id,
            'computer_id' => $this->computer->id,
            'check_in_at' => Carbon::now('Asia/Jakarta')->subHour(),
            'scheduled_end_at' => Carbon::now('Asia/Jakarta')->addHour(),
            'status' => 'ACTIVE',
        ]);

        $response = $this->actingAs($this->kepalaLab)->post(route('kepala-lab.session.force-end', $session));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $session->refresh();
        $this->assertEquals('FORCE_ENDED', $session->status);
        $this->assertNotNull($session->actual_end_at);
    }

    public function test_kepala_lab_can_toggle_computer_maintenance(): void
    {
        $this->assertEquals('ONLINE_LOCKED', $this->computer->status);

        // Toggle ke Maintenance
        $response = $this->actingAs($this->kepalaLab)->post(route('kepala-lab.komputer.toggle-maintenance', $this->computer));
        $response->assertSessionHas('success');
        $this->computer->refresh();
        $this->assertEquals('MAINTENANCE', $this->computer->status);

        // Toggle kembali ke Aktif
        $response2 = $this->actingAs($this->kepalaLab)->post(route('kepala-lab.komputer.toggle-maintenance', $this->computer));
        $response2->assertSessionHas('success');
        $this->computer->refresh();
        $this->assertEquals('ONLINE_LOCKED', $this->computer->status);
    }

    public function test_kepala_lab_can_view_read_only_buku_booking(): void
    {
        $response = $this->actingAs($this->kepalaLab)->get('/lab/buku-booking');

        $response->assertStatus(200);
        $response->assertSee('Matriks Jadwal Laboratorium');
        $response->assertSee('PC-01');
        $response->assertDontSee('Mode Pantau (Hanya Lihat)');
        $response->assertDontSee('Kelola Antrean Approval');
        $response->assertDontSee('Ajukan Reservasi Baru');
        $response->assertDontSee('Pesan PC Ini');
    }

    public function test_student_cannot_access_kepala_lab_buku_booking(): void
    {
        $response = $this->actingAs($this->student)->get('/lab/buku-booking');

        $response->assertRedirect(route('mahasiswa.dashboard'));
        $response->assertSessionHas('error');
    }

    public function test_kepala_lab_accessing_student_buku_booking_is_redirected_to_lab_buku_booking(): void
    {
        $response = $this->actingAs($this->kepalaLab)->get('/buku-booking?date=2026-11-05');

        $response->assertRedirect('/lab/buku-booking?date=2026-11-05');
    }

    public function test_kepala_lab_can_view_antrean_page(): void
    {
        $response = $this->actingAs($this->kepalaLab)->get('/lab/antrean');

        $response->assertStatus(200);
        $response->assertSee('Antrean Persetujuan Reservasi Komputer');
        $response->assertSee('Antrean Menunggu');
        $response->assertSee('Riwayat Keputusan');
    }

    public function test_kepala_lab_can_access_antrean_via_clean_url(): void
    {
        $response = $this->actingAs($this->kepalaLab)->get('/antrean');

        $response->assertStatus(200);
        $response->assertSee('Antrean Persetujuan Reservasi Komputer');
        $response->assertSee('Antrean Menunggu');
    }

    public function test_student_cannot_access_kepala_lab_antrean(): void
    {
        $response = $this->actingAs($this->student)->get('/lab/antrean');

        $response->assertRedirect(route('mahasiswa.dashboard'));
        $response->assertSessionHas('error');
    }

    public function test_student_cannot_access_clean_antrean_url(): void
    {
        $response = $this->actingAs($this->student)->get('/antrean');

        $response->assertRedirect(route('mahasiswa.dashboard'));
        $response->assertSessionHas('error');
    }

    public function test_lab_dashboard_redirects_to_clean_lab_url(): void
    {
        $response = $this->actingAs($this->kepalaLab)->get('/lab/dashboard');

        $response->assertRedirect('/lab');
    }

    public function test_student_dashboard_displays_approver_and_rejection_reason(): void
    {
        $today = Carbon::today('Asia/Jakarta');

        $approvedRes = Reservation::create([
            'user_id' => $this->student->id,
            'computer_id' => $this->computer->id,
            'reservation_date' => $today,
            'start_time' => '10:00',
            'end_time' => '12:00',
            'start_at' => Carbon::parse("{$today->toDateString()} 10:00:00", 'Asia/Jakarta'),
            'end_at' => Carbon::parse("{$today->toDateString()} 12:00:00", 'Asia/Jakarta'),
            'purpose' => 'Riset AI',
            'status' => 'APPROVED',
            'approved_by' => $this->kepalaLab->id,
            'approved_at' => Carbon::now('Asia/Jakarta'),
        ]);

        AccessCode::create([
            'reservation_id' => $approvedRes->id,
            'code' => 'LD-APPR-TEST',
            'code_hash' => hash('sha256', 'LD-APPR-TEST'),
            'expires_at' => Carbon::now('Asia/Jakarta')->addHours(2),
        ]);

        $response = $this->actingAs($this->student)->get(route('mahasiswa.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Disetujui oleh:');
        $response->assertSee('Kepala Laboratorium');
    }

    public function test_when_user_session_force_ended_schedule_immediately_becomes_available(): void
    {
        $today = Carbon::today('Asia/Jakarta');

        $reservation = Reservation::create([
            'user_id' => $this->student->id,
            'computer_id' => $this->computer->id,
            'reservation_date' => $today,
            'start_time' => '10:00',
            'end_time' => '12:00',
            'start_at' => Carbon::parse("{$today->toDateString()} 10:00:00", 'Asia/Jakarta'),
            'end_at' => Carbon::parse("{$today->toDateString()} 12:00:00", 'Asia/Jakarta'),
            'purpose' => 'Riset Deep Learning Khusus',
            'status' => 'CHECKED_IN',
        ]);

        $session = AttendanceSession::create([
            'reservation_id' => $reservation->id,
            'user_id' => $this->student->id,
            'computer_id' => $this->computer->id,
            'check_in_at' => Carbon::now('Asia/Jakarta')->subMinutes(30),
            'scheduled_end_at' => Carbon::now('Asia/Jakarta')->addMinutes(90),
            'status' => 'ACTIVE',
        ]);

        // Sebelum diberhentikan: Jadwal di buku booking terisi
        $beforeBooking = $this->actingAs($this->kepalaLab)->get('/lab/buku-booking?date='.$today->toDateString());
        $beforeBooking->assertSee('Riset Deep Learning Khusus');

        // Kepala Lab memberhentikan user (Hentikan Paksa)
        $this->actingAs($this->kepalaLab)->post(route('kepala-lab.session.force-end', $session));

        // Setelah diberhentikan: Jadwal di buku booking LANGSUNG KOSONG LAGI
        $afterBooking = $this->actingAs($this->kepalaLab)->get('/lab/buku-booking?date='.$today->toDateString());
        $afterBooking->assertDontSee('Riset Deep Learning Khusus');
        $afterBooking->assertSee('0 Jam Terisi');

        // Dan di sisi mahasiswa juga langsung kosong lagi
        $studentBooking = $this->actingAs($this->student)->get(route('mahasiswa.buku-booking', ['date' => $today->toDateString()]));
        $studentBooking->assertDontSee('Riset Deep Learning Khusus');

        $session->delete();
        $reservation->delete();
    }

    public function test_when_time_expires_schedule_immediately_becomes_available(): void
    {
        $today = Carbon::today('Asia/Jakarta');

        $reservation = Reservation::create([
            'user_id' => $this->student->id,
            'computer_id' => $this->computer->id,
            'reservation_date' => $today,
            'start_time' => '08:00',
            'end_time' => '09:00',
            'start_at' => Carbon::parse("{$today->toDateString()} 08:00:00", 'Asia/Jakarta'),
            'end_at' => Carbon::parse("{$today->toDateString()} 09:00:00", 'Asia/Jakarta'),
            'purpose' => 'Sesi Berakhir Otomatis',
            'status' => 'CHECKED_IN',
        ]);

        $session = AttendanceSession::create([
            'reservation_id' => $reservation->id,
            'user_id' => $this->student->id,
            'computer_id' => $this->computer->id,
            'check_in_at' => Carbon::parse("{$today->toDateString()} 08:00:00", 'Asia/Jakarta'),
            'scheduled_end_at' => Carbon::parse("{$today->toDateString()} 09:00:00", 'Asia/Jakarta'),
            'status' => 'ACTIVE',
        ]);

        // Simulasikan waktu saat ini telah melewati batas jam sesi (pk 09.30 WIB)
        Carbon::setTestNow(Carbon::parse("{$today->toDateString()} 09:30:00", 'Asia/Jakarta'));

        // Akses buku booking: sistem otomatis menuntaskan sesi dan jadwal langsung kosong lagi
        $response = $this->actingAs($this->kepalaLab)->get('/lab/buku-booking?date='.$today->toDateString());
        $response->assertDontSee('Sesi Berakhir Otomatis');
        $response->assertSee('0 Jam Terisi');

        // Pastikan status database juga tersinkronisasi
        $this->assertEquals('COMPLETED', $session->fresh()->status);
        $this->assertEquals('COMPLETED', $reservation->fresh()->status);

        Carbon::setTestNow(); // Reset test now
        $session->delete();
        $reservation->delete();
    }

    public function test_kepala_lab_can_set_maintenance_with_hours_duration(): void
    {
        $now = Carbon::parse('2026-11-02 08:00:00', 'Asia/Jakarta');
        Carbon::setTestNow($now);

        $response = $this->actingAs($this->kepalaLab)
            ->post("/lab/komputer/{$this->computer->id}/toggle-maintenance", [
                'duration_type' => 'hours',
                'duration_value' => 4,
                'maintenance_reason' => 'Perawatan sistem & instalasi driver',
            ]);

        $response->assertRedirect();
        $this->computer->refresh();

        $this->assertEquals('MAINTENANCE', $this->computer->status);
        $this->assertEquals('Perawatan sistem & instalasi driver', $this->computer->maintenance_reason);
        $this->assertNotNull($this->computer->maintenance_until);
        $this->assertEquals($now->copy()->addHours(4)->toDateTimeString(), $this->computer->maintenance_until->toDateTimeString());

        Carbon::setTestNow();
    }

    public function test_kepala_lab_can_set_maintenance_with_days_duration(): void
    {
        $now = Carbon::parse('2026-11-02 08:00:00', 'Asia/Jakarta');
        Carbon::setTestNow($now);

        $response = $this->actingAs($this->kepalaLab)
            ->post("/lab/komputer/{$this->computer->id}/toggle-maintenance", [
                'duration_type' => 'days',
                'duration_value' => 2,
                'maintenance_reason' => 'Pembersihan hardware berkala',
            ]);

        $response->assertRedirect();
        $this->computer->refresh();

        $this->assertEquals('MAINTENANCE', $this->computer->status);
        $this->assertEquals($now->copy()->addDays(2)->toDateTimeString(), $this->computer->maintenance_until->toDateTimeString());

        Carbon::setTestNow();
    }

    public function test_student_cannot_reserve_computer_under_maintenance(): void
    {
        $now = Carbon::parse('2026-11-02 08:00:00', 'Asia/Jakarta');
        Carbon::setTestNow($now);

        // Set komputer ke status Maintenance selama 4 jam (sampai pk 12:00)
        $this->computer->update([
            'status' => 'MAINTENANCE',
            'maintenance_started_at' => $now,
            'maintenance_until' => $now->copy()->addHours(4),
            'maintenance_reason' => 'Pergantian thermal paste',
        ]);

        // Mahasiswa mencoba mereservasi unit yang sedang maintenance pada rentang jam tersebut
        $response = $this->actingAs($this->student)
            ->post(route('mahasiswa.reservasi.store'), [
                'computer_id' => $this->computer->id,
                'reservation_date' => '2026-11-02',
                'start_time' => '09:00',
                'end_time' => '11:00',
                'purpose' => 'Riset Deep Learning',
            ]);

        $response->assertSessionHasErrors('computer_id');
        $this->assertDatabaseMissing('reservations', [
            'user_id' => $this->student->id,
            'computer_id' => $this->computer->id,
            'purpose' => 'Riset Deep Learning',
        ]);

        Carbon::setTestNow();
    }

    public function test_maintenance_automatically_releases_when_duration_expires(): void
    {
        $now = Carbon::parse('2026-11-02 08:00:00', 'Asia/Jakarta');
        Carbon::setTestNow($now);

        $this->computer->update([
            'status' => 'MAINTENANCE',
            'maintenance_started_at' => $now,
            'maintenance_until' => $now->copy()->addHours(2), // sampai pk 10:00
            'maintenance_reason' => 'Update OS',
        ]);

        // Waktu melangkah ke pk 10:05 WIB (waktu maintenance sudah terlewati)
        Carbon::setTestNow($now->copy()->addHours(2)->addMinutes(5));

        // Akses buku booking atau dashboard memicu LabScheduleService::syncExpiredSchedules()
        $this->actingAs($this->kepalaLab)->get(route('kepala-lab.dashboard'));

        $this->computer->refresh();
        $this->assertEquals('ONLINE_LOCKED', $this->computer->status);
        $this->assertNull($this->computer->maintenance_until);
        $this->assertNull($this->computer->maintenance_reason);

        Carbon::setTestNow();
    }

    public function test_kepala_lab_can_early_reactivate_computer_from_maintenance(): void
    {
        $now = Carbon::parse('2026-11-02 08:00:00', 'Asia/Jakarta');
        Carbon::setTestNow($now);

        $this->computer->update([
            'status' => 'MAINTENANCE',
            'maintenance_started_at' => $now,
            'maintenance_until' => $now->copy()->addDays(3),
            'maintenance_reason' => 'Perbaikan PSU',
        ]);

        // Kepala Lab mengaktifkan kembali PC lebih cepat
        $response = $this->actingAs($this->kepalaLab)
            ->post("/lab/komputer/{$this->computer->id}/toggle-maintenance");

        $response->assertRedirect();
        $this->computer->refresh();

        $this->assertEquals('ONLINE_LOCKED', $this->computer->status);
        $this->assertNull($this->computer->maintenance_until);
        $this->assertNull($this->computer->maintenance_reason);

        Carbon::setTestNow();
    }

    public function test_kepala_lab_can_see_add_booking_elements_in_buku_booking(): void
    {
        $response = $this->actingAs($this->kepalaLab)->get('/lab/buku-booking');

        $response->assertStatus(200);
        $response->assertSee('Tambah Booking');
        $response->assertSee('Booking PC Ini');
        $response->assertSee('Tersedia');
        $response->assertSee('Tambah Booking Workstation');
    }

    public function test_kepala_lab_can_create_booking_for_student_successfully(): void
    {
        $tomorrow = Carbon::tomorrow('Asia/Jakarta')->toDateString();

        $response = $this->actingAs($this->kepalaLab)
            ->post(route('kepala-lab.buku-booking.store'), [
                'user_id' => $this->student->id,
                'computer_id' => $this->computer->id,
                'reservation_date' => $tomorrow,
                'start_time' => '08:00',
                'end_time' => '11:00',
                'purpose' => 'Praktikum Machine Learning',
                'notes' => 'Butuh PyTorch dan CUDA',
            ]);

        $response->assertRedirect(route('kepala-lab.buku-booking', ['date' => $tomorrow]));
        $response->assertSessionHas('success');

        $reservation = Reservation::where('user_id', $this->student->id)
            ->where('computer_id', $this->computer->id)
            ->whereDate('reservation_date', $tomorrow)
            ->first();

        $this->assertNotNull($reservation);
        $this->assertEquals('APPROVED', $reservation->status);
        $this->assertEquals($this->kepalaLab->id, $reservation->approved_by);
        $this->assertNotNull($reservation->approved_at);
        $this->assertEquals('Praktikum Machine Learning', $reservation->purpose);

        // Pastikan AccessCode otomatis terbit
        $accessCode = AccessCode::where('reservation_id', $reservation->id)->first();
        $this->assertNotNull($accessCode);
        $this->assertMatchesRegularExpression('/^[2-9A-Z]{4}-[2-9A-Z]{4}-[2-9A-Z]{4}-[2-9A-Z]{4}$/', $accessCode->code);
    }

    public function test_kepala_lab_can_create_booking_for_themselves(): void
    {
        $tomorrow = Carbon::tomorrow('Asia/Jakarta')->toDateString();

        $response = $this->actingAs($this->kepalaLab)
            ->post(route('kepala-lab.buku-booking.store'), [
                'user_id' => $this->kepalaLab->id,
                'computer_id' => $this->computer->id,
                'reservation_date' => $tomorrow,
                'start_time' => '13:00',
                'end_time' => '15:00',
                'purpose' => 'Riset Dosen & Pengujian Model AI',
            ]);

        $response->assertRedirect(route('kepala-lab.buku-booking', ['date' => $tomorrow]));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('reservations', [
            'user_id' => $this->kepalaLab->id,
            'computer_id' => $this->computer->id,
            'status' => 'APPROVED',
            'purpose' => 'Riset Dosen & Pengujian Model AI',
        ]);
    }

    public function test_kepala_lab_booking_fails_on_overlapping_slot(): void
    {
        $tomorrow = Carbon::tomorrow('Asia/Jakarta')->toDateString();

        // Buat reservasi awal jam 09.00 - 11.00
        Reservation::create([
            'user_id' => $this->student->id,
            'computer_id' => $this->computer->id,
            'reservation_date' => $tomorrow,
            'start_time' => '09:00',
            'end_time' => '11:00',
            'start_at' => Carbon::parse("{$tomorrow} 09:00:00", 'Asia/Jakarta'),
            'end_at' => Carbon::parse("{$tomorrow} 11:00:00", 'Asia/Jakarta'),
            'purpose' => 'Sesi Pertama',
            'status' => 'APPROVED',
            'approved_by' => $this->kepalaLab->id,
            'approved_at' => now(),
        ]);

        // Coba buat booking bertabrakan jam 10.00 - 12.00
        $response = $this->actingAs($this->kepalaLab)
            ->post(route('kepala-lab.buku-booking.store'), [
                'user_id' => $this->kepalaLab->id,
                'computer_id' => $this->computer->id,
                'reservation_date' => $tomorrow,
                'start_time' => '10:00',
                'end_time' => '12:00',
                'purpose' => 'Sesi Bertabrakan',
            ]);

        $response->assertSessionHasErrors('computer_id');
        $this->assertDatabaseMissing('reservations', [
            'purpose' => 'Sesi Bertabrakan',
        ]);
    }

    public function test_kepala_lab_booking_fails_on_computer_in_maintenance(): void
    {
        $tomorrow = Carbon::tomorrow('Asia/Jakarta')->toDateString();

        $this->computer->update([
            'status' => 'MAINTENANCE',
            'maintenance_started_at' => Carbon::parse("{$tomorrow} 07:00:00", 'Asia/Jakarta'),
            'maintenance_until' => Carbon::parse("{$tomorrow} 12:00:00", 'Asia/Jakarta'),
            'maintenance_reason' => 'Pergantian GPU',
        ]);

        $response = $this->actingAs($this->kepalaLab)
            ->post(route('kepala-lab.buku-booking.store'), [
                'user_id' => $this->student->id,
                'computer_id' => $this->computer->id,
                'reservation_date' => $tomorrow,
                'start_time' => '08:00',
                'end_time' => '10:00',
                'purpose' => 'Sesi Saat Maintenance',
            ]);

        $response->assertSessionHasErrors('computer_id');
        $this->assertDatabaseMissing('reservations', [
            'purpose' => 'Sesi Saat Maintenance',
        ]);
    }

    public function test_student_cannot_post_to_kepala_lab_buku_booking_store(): void
    {
        $tomorrow = Carbon::tomorrow('Asia/Jakarta')->toDateString();

        $response = $this->actingAs($this->student)
            ->post(route('kepala-lab.buku-booking.store'), [
                'user_id' => $this->student->id,
                'computer_id' => $this->computer->id,
                'reservation_date' => $tomorrow,
                'start_time' => '08:00',
                'end_time' => '10:00',
                'purpose' => 'Mencoba Akses Ilegal',
            ]);

        // Role middleware menolak mahasiswa
        $response->assertRedirect(route('mahasiswa.dashboard'));
        $this->assertDatabaseMissing('reservations', [
            'purpose' => 'Mencoba Akses Ilegal',
        ]);
    }
}
