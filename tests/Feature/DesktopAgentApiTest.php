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

class DesktopAgentApiTest extends TestCase
{
    use RefreshDatabase;

    protected User $student;
    protected Computer $computer1;
    protected Computer $computer2;

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

        $this->computer1 = Computer::firstOrCreate(
            ['computer_code' => 'PC-01'],
            [
                'computer_name' => 'Workstation AI 01',
                'specs' => 'RTX 4070 12GB',
                'status' => 'ONLINE_LOCKED',
                'is_active' => true,
            ]
        );

        $this->computer2 = Computer::firstOrCreate(
            ['computer_code' => 'PC-02'],
            [
                'computer_name' => 'Workstation AI 02',
                'specs' => 'RTX 4070 12GB',
                'status' => 'ONLINE_LOCKED',
                'is_active' => true,
            ]
        );
    }

    public function test_desktop_agent_can_query_computer_status(): void
    {
        $response = $this->getJson('/api/desktop/status/PC-01');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'computer_code' => 'PC-01',
                'status' => 'ONLINE_LOCKED',
                'can_login' => true,
            ]);
    }

    public function test_desktop_agent_checkin_success_with_16_digit_token(): void
    {
        $now = Carbon::parse('2026-11-02 09:15:00', 'Asia/Jakarta');
        Carbon::setTestNow($now);

        $reservation = Reservation::create([
            'user_id' => $this->student->id,
            'computer_id' => $this->computer1->id,
            'reservation_date' => '2026-11-02',
            'start_time' => '09:00',
            'end_time' => '12:00',
            'start_at' => Carbon::parse('2026-11-02 09:00:00', 'Asia/Jakarta'),
            'end_at' => Carbon::parse('2026-11-02 12:00:00', 'Asia/Jakarta'),
            'purpose' => 'Riset AI',
            'status' => 'APPROVED',
        ]);

        $token = '7K9P-X4M2-9AB3-C8F1';
        AccessCode::create([
            'reservation_id' => $reservation->id,
            'code' => $token,
            'code_hash' => hash('sha256', $token),
            'expires_at' => Carbon::parse('2026-11-02 12:00:00', 'Asia/Jakarta'),
        ]);

        // Client PC mengirim request check-in dari layar monitor PC-01
        $response = $this->postJson('/api/desktop/check-in', [
            'computer_code' => 'PC-01',
            'token' => $token,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'session' => [
                    'student_name' => 'M. Subagja',
                    'nim' => '22081010045',
                    'computer_code' => 'PC-01',
                ],
            ]);

        // Verifikasi database: reservation status menjadi CHECKED_IN dan ada session aktif
        $this->assertEquals('CHECKED_IN', $reservation->fresh()->status);
        $this->assertDatabaseHas('attendance_sessions', [
            'reservation_id' => $reservation->id,
            'user_id' => $this->student->id,
            'computer_id' => $this->computer1->id,
            'status' => 'ACTIVE',
        ]);

        Carbon::setTestNow();
    }

    public function test_desktop_agent_checkin_fails_if_token_for_different_pc(): void
    {
        $now = Carbon::parse('2026-11-02 09:15:00', 'Asia/Jakarta');
        Carbon::setTestNow($now);

        $reservation = Reservation::create([
            'user_id' => $this->student->id,
            'computer_id' => $this->computer1->id, // Dipesan untuk PC-01
            'reservation_date' => '2026-11-02',
            'start_time' => '09:00',
            'end_time' => '12:00',
            'start_at' => Carbon::parse('2026-11-02 09:00:00', 'Asia/Jakarta'),
            'end_at' => Carbon::parse('2026-11-02 12:00:00', 'Asia/Jakarta'),
            'purpose' => 'Riset AI',
            'status' => 'APPROVED',
        ]);

        $token = '7K9P-X4M2-9AB3-C8F1';
        AccessCode::create([
            'reservation_id' => $reservation->id,
            'code' => $token,
            'code_hash' => hash('sha256', $token),
            'expires_at' => Carbon::parse('2026-11-02 12:00:00', 'Asia/Jakarta'),
        ]);

        // Mahasiswa mencoba input token PC-01 di layar PC-02
        $response = $this->postJson('/api/desktop/check-in', [
            'computer_code' => 'PC-02',
            'token' => $token,
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
            ]);

        $this->assertEquals('APPROVED', $reservation->fresh()->status);

        Carbon::setTestNow();
    }

    public function test_desktop_agent_checkin_fails_if_before_start_time(): void
    {
        // Simulasikan jam 08:30 WIB (jadwal baru mulai pk 09:00)
        $now = Carbon::parse('2026-11-02 08:30:00', 'Asia/Jakarta');
        Carbon::setTestNow($now);

        $reservation = Reservation::create([
            'user_id' => $this->student->id,
            'computer_id' => $this->computer1->id,
            'reservation_date' => '2026-11-02',
            'start_time' => '09:00',
            'end_time' => '12:00',
            'start_at' => Carbon::parse('2026-11-02 09:00:00', 'Asia/Jakarta'),
            'end_at' => Carbon::parse('2026-11-02 12:00:00', 'Asia/Jakarta'),
            'purpose' => 'Riset AI',
            'status' => 'APPROVED',
        ]);

        $token = '7K9P-X4M2-9AB3-C8F1';
        AccessCode::create([
            'reservation_id' => $reservation->id,
            'code' => $token,
            'code_hash' => hash('sha256', $token),
            'expires_at' => Carbon::parse('2026-11-02 12:00:00', 'Asia/Jakarta'),
        ]);

        $response = $this->postJson('/api/desktop/check-in', [
            'computer_code' => 'PC-01',
            'token' => $token,
        ]);

        $response->assertStatus(422)
            ->assertJsonFragment([
                'success' => false,
            ]);

        Carbon::setTestNow();
    }

    public function test_desktop_agent_heartbeat_and_session_end(): void
    {
        $now = Carbon::parse('2026-11-02 09:00:00', 'Asia/Jakarta');
        Carbon::setTestNow($now);

        $reservation = Reservation::create([
            'user_id' => $this->student->id,
            'computer_id' => $this->computer1->id,
            'reservation_date' => '2026-11-02',
            'start_time' => '09:00',
            'end_time' => '11:00',
            'start_at' => $now,
            'end_at' => $now->copy()->addHours(2),
            'purpose' => 'Riset AI',
            'status' => 'CHECKED_IN',
        ]);

        $session = AttendanceSession::create([
            'reservation_id' => $reservation->id,
            'user_id' => $this->student->id,
            'computer_id' => $this->computer1->id,
            'check_in_at' => $now,
            'scheduled_end_at' => $now->copy()->addHours(2),
            'status' => 'ACTIVE',
        ]);

        // Heartbeat saat sesi aktif
        $hbResponse = $this->postJson('/api/desktop/heartbeat', [
            'computer_code' => 'PC-01',
            'session_id' => $session->id,
        ]);

        $hbResponse->assertStatus(200)
            ->assertJson([
                'success' => true,
                'status' => 'ACTIVE',
                'command' => 'KEEP_RUNNING',
            ]);

        // User logout / end session dari client PC
        $endResponse = $this->postJson('/api/desktop/session/end', [
            'computer_code' => 'PC-01',
            'session_id' => $session->id,
        ]);

        $endResponse->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $this->assertEquals('COMPLETED', $session->fresh()->status);

        Carbon::setTestNow();
    }

    public function test_desktop_agent_allows_vip_unlimited_student_to_checkin_exceeding_daily_quota(): void
    {
        $now = Carbon::parse('2026-11-02 13:00:00', 'Asia/Jakarta');
        Carbon::setTestNow($now);

        $vipStudent = User::create([
            'name' => 'VIP Tester',
            'nim' => '22081010777',
            'email' => 'vip.tester@uinbanten.ac.id',
            'jurusan' => 'Teknik Informatika',
            'password' => bcrypt('password'),
            'role' => 'mahasiswa',
            'is_active' => true,
            'is_unlimited_session' => true,
        ]);

        // Buat 2 sesi selesai di hari yang sama untuk VIP tester
        $dummyRes1 = Reservation::create([
            'user_id' => $vipStudent->id,
            'computer_id' => $this->computer1->id,
            'reservation_date' => '2026-11-02',
            'start_time' => '07:00',
            'end_time' => '08:00',
            'start_at' => Carbon::parse('2026-11-02 07:00:00', 'Asia/Jakarta'),
            'end_at' => Carbon::parse('2026-11-02 08:00:00', 'Asia/Jakarta'),
            'purpose' => 'Sesi 1',
            'status' => 'COMPLETED',
        ]);
        AttendanceSession::create([
            'reservation_id' => $dummyRes1->id,
            'user_id' => $vipStudent->id,
            'computer_id' => $this->computer1->id,
            'check_in_at' => Carbon::parse('2026-11-02 07:00:00', 'Asia/Jakarta'),
            'scheduled_end_at' => Carbon::parse('2026-11-02 08:00:00', 'Asia/Jakarta'),
            'check_out_at' => Carbon::parse('2026-11-02 08:00:00', 'Asia/Jakarta'),
            'status' => 'COMPLETED',
        ]);

        $dummyRes2 = Reservation::create([
            'user_id' => $vipStudent->id,
            'computer_id' => $this->computer1->id,
            'reservation_date' => '2026-11-02',
            'start_time' => '08:00',
            'end_time' => '09:00',
            'start_at' => Carbon::parse('2026-11-02 08:00:00', 'Asia/Jakarta'),
            'end_at' => Carbon::parse('2026-11-02 09:00:00', 'Asia/Jakarta'),
            'purpose' => 'Sesi 2',
            'status' => 'COMPLETED',
        ]);
        AttendanceSession::create([
            'reservation_id' => $dummyRes2->id,
            'user_id' => $vipStudent->id,
            'computer_id' => $this->computer1->id,
            'check_in_at' => Carbon::parse('2026-11-02 08:00:00', 'Asia/Jakarta'),
            'scheduled_end_at' => Carbon::parse('2026-11-02 09:00:00', 'Asia/Jakarta'),
            'check_out_at' => Carbon::parse('2026-11-02 09:00:00', 'Asia/Jakarta'),
            'status' => 'COMPLETED',
        ]);

        // Reservasi ke-3 disetujui untuk jam 13.00
        $res3 = Reservation::create([
            'user_id' => $vipStudent->id,
            'computer_id' => $this->computer1->id,
            'reservation_date' => '2026-11-02',
            'start_time' => '13:00',
            'end_time' => '15:00',
            'start_at' => Carbon::parse('2026-11-02 13:00:00', 'Asia/Jakarta'),
            'end_at' => Carbon::parse('2026-11-02 15:00:00', 'Asia/Jakarta'),
            'purpose' => 'Sesi 3 VIP',
            'status' => 'APPROVED',
        ]);

        $token = 'VIP9-TEST-9999-ABCD';
        AccessCode::create([
            'reservation_id' => $res3->id,
            'code' => $token,
            'code_hash' => hash('sha256', $token),
            'expires_at' => Carbon::parse('2026-11-02 15:00:00', 'Asia/Jakarta'),
        ]);

        // Check-in via API Desktop Agent -> HARUS SUKSES (meskipun sudah ada 2 sesi sebelumnya)
        $response = $this->postJson('/api/desktop/check-in', [
            'computer_code' => 'PC-01',
            'token' => $token,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $this->assertEquals('CHECKED_IN', $res3->fresh()->status);
        $this->assertEquals(1, AttendanceSession::where('reservation_id', $res3->id)->where('status', 'ACTIVE')->count());

        Carbon::setTestNow();
    }
}
