<?php

namespace Tests\Feature;

use App\Models\AccessCode;
use App\Models\AttendanceSession;
use App\Models\Computer;
use App\Models\Reservation;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MahasiswaDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::firstOrCreate([
            'nim' => '22081010045',
        ], [
            'name' => 'M. Subagja',
            'email' => 'subagja@example.com',
            'jurusan' => 'Teknik Informatika',
            'password' => bcrypt('password'),
            'role' => 'mahasiswa',
            'is_active' => true,
        ]);
    }

    public function test_dashboard_when_no_reservations_shows_no_billing_and_no_access_codes(): void
    {
        // Pastikan tidak ada sesi atau reservasi aktif
        AttendanceSession::where('user_id', $this->user->id)->delete();
        Reservation::where('user_id', $this->user->id)->delete();

        $response = $this->actingAs($this->user)->get(route('mahasiswa.dashboard'));

        $response->assertStatus(200);
        // Billing aktif tidak boleh muncul
        $response->assertDontSee('SESI BILLING AKTIF');
        // Kode akses check-in belum diterbitkan
        $response->assertSee('Belum Ada Kode Aktif');
        $response->assertSee('Belum Ada Riwayat Reservasi');
        $response->assertSee('0');
        $response->assertSee('/ 2 Sesi Digunakan');
    }

    public function test_dashboard_shows_billing_when_session_is_active(): void
    {
        $computer = Computer::firstOrCreate(
            ['computer_code' => 'PC-01'],
            ['computer_name' => 'Workstation Alpha', 'specs' => 'RTX 4070', 'status' => 'AVAILABLE', 'is_active' => true]
        );

        $reservation = Reservation::create([
            'user_id' => $this->user->id,
            'computer_id' => $computer->id,
            'reservation_date' => Carbon::today('Asia/Jakarta'),
            'start_time' => '09:00',
            'end_time' => '12:00',
            'start_at' => Carbon::now('Asia/Jakarta')->subHours(1),
            'end_at' => Carbon::now('Asia/Jakarta')->addHours(2),
            'purpose' => 'Riset Deep Learning',
            'status' => 'CHECKED_IN',
        ]);

        $accessCode = AccessCode::create([
            'reservation_id' => $reservation->id,
            'code' => 'LD-TEST-CODE',
            'code_hash' => hash('sha256', 'LD-TEST-CODE'),
            'expires_at' => Carbon::now('Asia/Jakarta')->addHours(2),
            'used_at' => Carbon::now('Asia/Jakarta')->subHours(1),
        ]);

        $session = AttendanceSession::create([
            'reservation_id' => $reservation->id,
            'user_id' => $this->user->id,
            'computer_id' => $computer->id,
            'check_in_at' => Carbon::now('Asia/Jakarta')->subMinutes(30),
            'scheduled_end_at' => Carbon::now('Asia/Jakarta')->addHours(2),
            'status' => 'ACTIVE',
        ]);

        $response = $this->actingAs($this->user)->get(route('mahasiswa.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('SESI BILLING AKTIF');
        $response->assertSee('LD-TEST-CODE');
        $response->assertSee('Akhiri Sesi Sekarang');

        // Test mengakhiri sesi (Model Billing Warnet)
        $endResponse = $this->actingAs($this->user)->post(route('mahasiswa.session.end', $session));
        $endResponse->assertRedirect(route('mahasiswa.dashboard'));

        $this->assertEquals('COMPLETED', $session->fresh()->status);
        $this->assertEquals('COMPLETED', $reservation->fresh()->status);

        // Setelah diakhiri, kartu billing aktif tidak boleh muncul lagi dan jadwal buku booking langsung kosong lagi
        $afterResponse = $this->actingAs($this->user)->get(route('mahasiswa.dashboard'));
        $afterResponse->assertDontSee('SESI BILLING AKTIF');

        $bookingResponse = $this->actingAs($this->user)->get(route('mahasiswa.buku-booking'));
        $bookingResponse->assertSee('0 Jam Terisi');

        // Cleanup
        $session->delete();
        $accessCode->delete();
        $reservation->delete();
    }

    public function test_notification_bell_and_profile_modals_rendered(): void
    {
        // Set waktu simulasi pada jam kerja (pk 10.00 WIB)
        Carbon::setTestNow(Carbon::create(2026, 10, 31, 10, 0, 0, 'Asia/Jakarta'));

        $response = $this->actingAs($this->user)->get(route('mahasiswa.dashboard'));

        $response->assertStatus(200);
        // Notifikasi Lonceng
        $response->assertSee('Pemberitahuan');
        $response->assertSee('Status Kuota Harian');
        $response->assertSee('Tandai dibaca');

        // Bagian Profil & Aturan Lab
        $response->assertSee('Profil Saya');
        $response->assertSee('Aturan Laboratorium');
        $response->assertSee('Statistik Aktivitas Lab');
        $response->assertSee('Hanya Lihat');
        $response->assertDontSee('Ubah Data & Sandi');
        $response->assertDontSee('Simpan Perubahan');
        $response->assertSee('Tata Tertib', false);
        $response->assertSee($this->user->name);
        $response->assertSee($this->user->nim);

        Carbon::setTestNow(); // Reset waktu
    }

    public function test_notifications_disappear_after_operating_hours(): void
    {
        // Set waktu setelah jam operasional selesai (pk 16.30 WIB)
        Carbon::setTestNow(Carbon::create(2026, 10, 31, 16, 30, 0, 'Asia/Jakarta'));

        $response = $this->actingAs($this->user)->get(route('mahasiswa.dashboard'));

        $response->assertStatus(200);
        // Semua pesan harian harus hilang/kosong
        $response->assertDontSee('Status Kuota Harian');
        $response->assertSee('isOperatingHoursOver: true');
        $response->assertSee('Jam Operasional Selesai (16.00 WIB)');

        Carbon::setTestNow(); // Reset waktu
    }

    public function test_student_profile_is_read_only_and_cannot_be_updated(): void
    {
        $response = $this->actingAs($this->user)->put('/profile', [
            'name' => 'M. Subagja Updated',
            'email' => 'subagja.new@example.com',
            'jurusan' => 'Sains Data & AI',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');

        $this->user->refresh();
        $this->assertEquals('M. Subagja', $this->user->name);
        $this->assertEquals('subagja@example.com', $this->user->email);
        $this->assertEquals('Teknik Informatika', $this->user->jurusan);
    }

    public function test_buku_booking_page_can_be_accessed(): void
    {
        $response = $this->actingAs($this->user)->get(route('mahasiswa.buku-booking'));

        $response->assertStatus(200);
        $response->assertSee('Buku Booking');
        $response->assertSee('Matriks Jadwal Jam Pemakaian Komputer');
        $response->assertSee('07.00');
        $response->assertSee('16.00 WIB');
    }

    public function test_buku_booking_displays_scheduled_reservations_and_time_slots(): void
    {
        $computer = Computer::firstOrCreate(
            ['computer_code' => 'PC-01'],
            ['computer_name' => 'Workstation Alpha', 'specs' => 'RTX 4070', 'status' => 'AVAILABLE', 'is_active' => true]
        );

        $today = Carbon::today('Asia/Jakarta');

        $reservation = Reservation::create([
            'user_id' => $this->user->id,
            'computer_id' => $computer->id,
            'reservation_date' => $today,
            'start_time' => '10:00',
            'end_time' => '13:00',
            'start_at' => Carbon::parse("{$today->toDateString()} 10:00:00", 'Asia/Jakarta'),
            'end_at' => Carbon::parse("{$today->toDateString()} 13:00:00", 'Asia/Jakarta'),
            'purpose' => 'Pelatihan AI Machine Learning',
            'status' => 'APPROVED',
        ]);

        $response = $this->actingAs($this->user)->get(route('mahasiswa.buku-booking', ['date' => $today->toDateString()]));

        $response->assertStatus(200);
        $response->assertSee('Pelatihan AI Machine Learning');
        $response->assertSee('10:00');
        $response->assertSee('13:00');
        $response->assertSee('PC-01');

        $reservation->delete();
    }

    public function test_dashboard_displays_buku_booking_portal(): void
    {
        $response = $this->actingAs($this->user)->get(route('mahasiswa.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Buku Booking');
        $response->assertSee(route('mahasiswa.buku-booking'));
        $response->assertSee('Buka Buku Booking');
    }

    public function test_quota_is_refilled_to_full_after_operating_hours(): void
    {
        $computer = Computer::firstOrCreate(
            ['computer_code' => 'PC-03'],
            ['computer_name' => 'Workstation Gamma', 'specs' => 'Ryzen 7', 'status' => 'AVAILABLE', 'is_active' => true]
        );

        // Gunakan tanggal yang sama dengan setTestNow agar sesi terhitung di tanggal tersebut
        $testDate = Carbon::create(2026, 10, 31, 0, 0, 0, 'Asia/Jakarta');
        $today = $testDate->toDateString(); // '2026-10-31'

        // Buat 2 sesi selesai untuk hari itu (kuota habis jika pada siang hari)
        $res1 = Reservation::create([
            'user_id' => $this->user->id,
            'computer_id' => $computer->id,
            'reservation_date' => $today,
            'start_time' => '08:00',
            'end_time' => '10:00',
            'start_at' => Carbon::parse("{$today} 08:00:00", 'Asia/Jakarta'),
            'end_at' => Carbon::parse("{$today} 10:00:00", 'Asia/Jakarta'),
            'purpose' => 'Sesi Pagi',
            'status' => 'COMPLETED',
        ]);
        $session1 = AttendanceSession::create([
            'reservation_id' => $res1->id,
            'user_id' => $this->user->id,
            'computer_id' => $computer->id,
            'check_in_at' => Carbon::parse("{$today} 08:00:00", 'Asia/Jakarta'),
            'scheduled_end_at' => Carbon::parse("{$today} 10:00:00", 'Asia/Jakarta'),
            'status' => 'COMPLETED',
        ]);

        $res2 = Reservation::create([
            'user_id' => $this->user->id,
            'computer_id' => $computer->id,
            'reservation_date' => $today,
            'start_time' => '11:00',
            'end_time' => '13:00',
            'start_at' => Carbon::parse("{$today} 11:00:00", 'Asia/Jakarta'),
            'end_at' => Carbon::parse("{$today} 13:00:00", 'Asia/Jakarta'),
            'purpose' => 'Sesi Siang',
            'status' => 'COMPLETED',
        ]);
        $session2 = AttendanceSession::create([
            'reservation_id' => $res2->id,
            'user_id' => $this->user->id,
            'computer_id' => $computer->id,
            'check_in_at' => Carbon::parse("{$today} 11:00:00", 'Asia/Jakarta'),
            'scheduled_end_at' => Carbon::parse("{$today} 13:00:00", 'Asia/Jakarta'),
            'status' => 'COMPLETED',
        ]);

        // 1. Pada siang hari jam 14.00 WIB, kuota terpakai 2/2 sesi (sisa 0)
        // $today sudah berupa string '2026-10-31', langsung gunakan untuk setTestNow
        Carbon::setTestNow(Carbon::parse("{$today} 14:00:00", 'Asia/Jakarta'));
        $dayResponse = $this->actingAs($this->user)->get(route('mahasiswa.dashboard'));
        $dayResponse->assertStatus(200);
        $dayResponse->assertSee('2');
        $dayResponse->assertSee('/ 2 Sesi Digunakan');
        $dayResponse->assertSee('Kuota harian Anda <strong>sudah habis</strong>', false);

        // 2. Sehabis jam operasional lab selesai (pk 16.30 WIB), kuota diisi ulang kembali jadi FULL (2 sesi)
        Carbon::setTestNow(Carbon::parse("{$today} 16:30:00", 'Asia/Jakarta'));
        $afterHoursResponse = $this->actingAs($this->user)->get(route('mahasiswa.dashboard'));
        $afterHoursResponse->assertStatus(200);
        $afterHoursResponse->assertSee('0');
        $afterHoursResponse->assertSee('/ 2 Sesi Digunakan');
        $afterHoursResponse->assertSee('2 sesi (Full)');
        $afterHoursResponse->assertSee('Full');

        // Cleanup
        $session1->delete();
        $session2->delete();
        $res1->delete();
        $res2->delete();
        Carbon::setTestNow();
    }

    public function test_unit_status_shows_free_time_windows(): void
    {
        $computer = Computer::firstOrCreate(
            ['computer_code' => 'PC-04'],
            ['computer_name' => 'Workstation Delta', 'specs' => 'Core i5', 'status' => 'AVAILABLE', 'is_active' => true]
        );

        $today = Carbon::today('Asia/Jakarta');

        // Simulasi booking jam 1 siang (13.00 - 15.00 WIB)
        // Sesuai contoh user: jika ada booking jam 1 (13.00), berarti kosong sampai jam 12.30
        $res = Reservation::create([
            'user_id' => $this->user->id,
            'computer_id' => $computer->id,
            'reservation_date' => $today,
            'start_time' => '13:00',
            'end_time' => '15:00',
            'start_at' => Carbon::parse("{$today->toDateString()} 13:00:00", 'Asia/Jakarta'),
            'end_at' => Carbon::parse("{$today->toDateString()} 15:00:00", 'Asia/Jakarta'),
            'purpose' => 'Riset Modeling AI',
            'status' => 'APPROVED',
        ]);

        $response = $this->actingAs($this->user)->get(route('mahasiswa.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Kosong: 07.00 – 12.30');
        $response->assertSee('Status Unit:');
        $response->assertSee('TERISI');
        $response->assertSee('Kosong');
        $response->assertSee('Terisi');
        $response->assertSee('Padat');
        $response->assertSee('Penuh');

        $res->delete();
    }

    public function test_urls_do_not_contain_mahasiswa_prefix(): void
    {
        // 1. Seluruh URL sekarang 1 nama / 1 segment (bersih)
        $this->assertEquals(url('/dashboard'), route('mahasiswa.dashboard'));
        $this->assertEquals(url('/buku-booking'), route('mahasiswa.buku-booking'));
        $this->assertEquals(url('/reservasi'), route('mahasiswa.reservasi.create'));

        // 2. Akses langsung ke URL 1 kata berhasil
        $response = $this->actingAs($this->user)->get('/dashboard');
        $response->assertStatus(200);

        $reservasiResponse = $this->actingAs($this->user)->get('/reservasi');
        $reservasiResponse->assertStatus(200);

        // 3. Akses URL lama /reservasi/create otomatis di-redirect ke /reservasi
        $createResponse = $this->actingAs($this->user)->get('/reservasi/create');
        $createResponse->assertRedirect('/reservasi');

        // 4. Akses URL lama /mahasiswa/dashboard otomatis di-redirect ke /dashboard
        $legacyResponse = $this->actingAs($this->user)->get('/mahasiswa/dashboard');
        $legacyResponse->assertRedirect('/dashboard');

        // 5. Akses URL lama /mahasiswa/reservasi/create otomatis di-redirect ke /reservasi
        $legacyCreateResponse = $this->actingAs($this->user)->get('/mahasiswa/reservasi/create');
        $legacyCreateResponse->assertRedirect('/reservasi');
    }

    public function test_student_cannot_start_billing_before_scheduled_time(): void
    {
        $testDate = '2026-11-05';
        Carbon::setTestNow(Carbon::parse("{$testDate} 10:00:00", 'Asia/Jakarta'));

        $computer = Computer::firstOrCreate(
            ['computer_code' => 'PC-02'],
            ['computer_name' => 'Workstation Beta', 'specs' => 'RTX 4070', 'status' => 'AVAILABLE', 'is_active' => true]
        );

        $reservation = Reservation::create([
            'user_id' => $this->user->id,
            'computer_id' => $computer->id,
            'reservation_date' => Carbon::parse($testDate),
            'start_time' => '13:00',
            'end_time' => '15:00',
            'start_at' => Carbon::parse("{$testDate} 13:00:00", 'Asia/Jakarta'),
            'end_at' => Carbon::parse("{$testDate} 15:00:00", 'Asia/Jakarta'),
            'purpose' => 'Riset Bioinformatika',
            'status' => 'APPROVED',
        ]);

        $accessCode = AccessCode::create([
            'reservation_id' => $reservation->id,
            'code' => 'LD-TIMING-123',
            'code_hash' => hash('sha256', 'LD-TIMING-123'),
            'expires_at' => Carbon::parse("{$testDate} 15:00:00", 'Asia/Jakarta'),
        ]);

        // 1. Pada UI Dashboard, tombol check-in berstatus belum waktunya
        $response = $this->actingAs($this->user)->get(route('mahasiswa.dashboard'));
        $response->assertStatus(200);
        $response->assertSee('Belum Waktunya (Mulai 13:00 WIB)');
        $response->assertDontSee('Mulai Sesi (Check-in)');

        // 2. Jika mencoba bypass submit check-in via POST, sistem wajib menolak
        $checkInResponse = $this->actingAs($this->user)->post(route('mahasiswa.reservasi.checkin', $reservation));
        $checkInResponse->assertSessionHasErrors(['reservation']);

        // Pastikan tidak ada sesi attendance yang dibuat dan status reservasi tetap APPROVED
        $this->assertEquals(0, AttendanceSession::where('reservation_id', $reservation->id)->count());
        $this->assertEquals('APPROVED', $reservation->fresh()->status);

        // 3. Ketika jam sudah masuk waktu peminjaman (13.05 WIB), tombol check-in aktif dan dapat dimulai
        Carbon::setTestNow(Carbon::parse("{$testDate} 13:05:00", 'Asia/Jakarta'));

        $activeViewResponse = $this->actingAs($this->user)->get(route('mahasiswa.dashboard'));
        $activeViewResponse->assertStatus(200);
        $activeViewResponse->assertSee('Input di Layar');
        $activeViewResponse->assertSee('16 Digit Token Masuk PC');
        $activeViewResponse->assertDontSee('Mulai Sesi (Check-in)');

        $validCheckInResponse = $this->actingAs($this->user)->post(route('mahasiswa.reservasi.checkin', $reservation));
        $validCheckInResponse->assertRedirect(route('mahasiswa.dashboard'));
        $validCheckInResponse->assertSessionHas('success');

        // Sesi berhasil aktif dan reservasi berubah jadi CHECKED_IN
        $this->assertEquals(1, AttendanceSession::where('reservation_id', $reservation->id)->where('status', 'ACTIVE')->count());
        $this->assertEquals('CHECKED_IN', $reservation->fresh()->status);

        Carbon::setTestNow();
    }

    public function test_student_cannot_check_in_after_scheduled_end_time(): void
    {
        $testDate = '2026-11-05';
        $computer = Computer::firstOrCreate(
            ['computer_code' => 'PC-03'],
            ['computer_name' => 'Workstation Gamma', 'specs' => 'RTX 4070', 'status' => 'AVAILABLE', 'is_active' => true]
        );

        $reservation = Reservation::create([
            'user_id' => $this->user->id,
            'computer_id' => $computer->id,
            'reservation_date' => Carbon::parse($testDate),
            'start_time' => '09:00',
            'end_time' => '11:00',
            'start_at' => Carbon::parse("{$testDate} 09:00:00", 'Asia/Jakarta'),
            'end_at' => Carbon::parse("{$testDate} 11:00:00", 'Asia/Jakarta'),
            'purpose' => 'Simulasi expired',
            'status' => 'APPROVED',
        ]);

        // Waktu sekarang sudah lewat dari end_time (11.05 WIB)
        Carbon::setTestNow(Carbon::parse("{$testDate} 11:05:00", 'Asia/Jakarta'));

        $response = $this->actingAs($this->user)->post(route('mahasiswa.reservasi.checkin', $reservation));
        $response->assertSessionHasErrors(['reservation']);

        $this->assertEquals(0, AttendanceSession::where('reservation_id', $reservation->id)->count());

        Carbon::setTestNow();
    }

    public function test_unlimited_session_student_displays_vip_badge_and_can_exceed_normal_quota(): void
    {
        $vipUser = User::create([
            'name' => 'Budi Unlimited',
            'nim' => '22081010888',
            'email' => 'unlimited@uinbanten.ac.id',
            'jurusan' => 'Teknik Informatika',
            'password' => bcrypt('password'),
            'role' => 'mahasiswa',
            'is_active' => true,
            'is_unlimited_session' => true,
        ]);

        $computer = Computer::firstOrCreate(
            ['computer_code' => 'PC-01'],
            ['computer_name' => 'Workstation Alpha', 'specs' => 'Core i7', 'status' => 'AVAILABLE', 'is_active' => true]
        );

        $testDate = '2026-11-04'; // Hari Rabu (weekday)
        Carbon::setTestNow(Carbon::parse("{$testDate} 10:00:00", 'Asia/Jakarta'));

        // Buat 2 sesi selesai di hari yang sama untuk user VIP ini (bagi user biasa, kuota habis)
        $res1 = Reservation::create([
            'user_id' => $vipUser->id,
            'computer_id' => $computer->id,
            'reservation_date' => Carbon::parse($testDate),
            'start_time' => '07:00',
            'end_time' => '08:30',
            'start_at' => Carbon::parse("{$testDate} 07:00:00", 'Asia/Jakarta'),
            'end_at' => Carbon::parse("{$testDate} 08:30:00", 'Asia/Jakarta'),
            'purpose' => 'Sesi 1 VIP',
            'status' => 'COMPLETED',
        ]);
        AttendanceSession::create([
            'reservation_id' => $res1->id,
            'user_id' => $vipUser->id,
            'computer_id' => $computer->id,
            'check_in_at' => Carbon::parse("{$testDate} 07:00:00", 'Asia/Jakarta'),
            'scheduled_end_at' => Carbon::parse("{$testDate} 08:30:00", 'Asia/Jakarta'),
            'check_out_at' => Carbon::parse("{$testDate} 08:30:00", 'Asia/Jakarta'),
            'status' => 'COMPLETED',
        ]);

        $res2 = Reservation::create([
            'user_id' => $vipUser->id,
            'computer_id' => $computer->id,
            'reservation_date' => Carbon::parse($testDate),
            'start_time' => '08:30',
            'end_time' => '10:00',
            'start_at' => Carbon::parse("{$testDate} 08:30:00", 'Asia/Jakarta'),
            'end_at' => Carbon::parse("{$testDate} 10:00:00", 'Asia/Jakarta'),
            'purpose' => 'Sesi 2 VIP',
            'status' => 'COMPLETED',
        ]);
        AttendanceSession::create([
            'reservation_id' => $res2->id,
            'user_id' => $vipUser->id,
            'computer_id' => $computer->id,
            'check_in_at' => Carbon::parse("{$testDate} 08:30:00", 'Asia/Jakarta'),
            'scheduled_end_at' => Carbon::parse("{$testDate} 10:00:00", 'Asia/Jakarta'),
            'check_out_at' => Carbon::parse("{$testDate} 10:00:00", 'Asia/Jakarta'),
            'status' => 'COMPLETED',
        ]);

        // Dashboard harus menampilkan VIP Unlimited
        $dashboardResponse = $this->actingAs($vipUser)->get(route('mahasiswa.dashboard'));
        $dashboardResponse->assertStatus(200);
        $dashboardResponse->assertSee('VIP Unlimited ∞');
        $dashboardResponse->assertSee('Bebas reservasi tanpa batas kuota harian');

        // Form reservasi baru juga harus menampilkan badge VIP Unlimited
        $createFormResponse = $this->actingAs($vipUser)->get(route('mahasiswa.reservasi.create'));
        $createFormResponse->assertStatus(200);
        $createFormResponse->assertSee('VIP Unlimited ∞ (Tanpa Batas)');

        // User VIP mengajukan sesi ke-3 di hari yang sama -> HARUS DIIZINKAN (tidak ditolak oleh kuota harian)
        $storeResponse = $this->actingAs($vipUser)->post(route('mahasiswa.reservasi.store'), [
            'computer_id' => $computer->id,
            'reservation_date' => $testDate,
            'start_time' => '11:00',
            'end_time' => '13:00',
            'purpose' => 'Riset Deep Learning sesi ke-3 VIP',
        ]);

        $storeResponse->assertRedirect(route('mahasiswa.dashboard'));
        $storeResponse->assertSessionHas('success');

        $this->assertEquals(1, Reservation::where('user_id', $vipUser->id)->where('purpose', 'Riset Deep Learning sesi ke-3 VIP')->count());

        Carbon::setTestNow();
    }
}
