<?php

namespace Tests\Feature;

use App\Mail\LoginOtpMail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class LoginOtpTest extends TestCase
{
    use RefreshDatabase;

    protected User $student;

    protected User $kepalaLab;

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
    }

    public function test_login_page_renders_successfully(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertSee('TERMINAL');
        $response->assertSee('Verifikasi bahwa Anda manusia');
    }

    public function test_login_requires_cloudflare_verification(): void
    {
        $response = $this->post('/login', [
            'nim' => '22081010045',
            'password' => 'password',
            'cf_verified' => '0',
        ]);

        $response->assertSessionHasErrors(['nim']);
        $this->assertGuest();
    }

    public function test_login_with_invalid_credentials_fails(): void
    {
        $response = $this->post('/login', [
            'nim' => '22081010045',
            'password' => 'wrongpassword',
            'cf_verified' => '1',
        ]);

        $response->assertSessionHasErrors(['nim']);
        $this->assertGuest();
    }

    public function test_student_login_authenticates_directly_without_otp(): void
    {
        Mail::fake();

        $response = $this->post('/login', [
            'nim' => '22081010045',
            'password' => 'password',
            'cf_verified' => '1',
        ]);

        // Mahasiswa langsung terautentikasi tanpa OTP
        $this->assertAuthenticatedAs($this->student);
        $this->assertFalse(session()->has('login_otp'));
        $response->assertRedirect(route('mahasiswa.dashboard'));

        // Tidak ada email OTP yang dikirim untuk mahasiswa
        Mail::assertNothingSent();
    }

    public function test_kepala_lab_login_requires_otp_and_sends_email(): void
    {
        Mail::fake();

        $response = $this->post('/login', [
            'nim' => 'kepala_lab',
            'password' => 'password',
            'cf_verified' => '1',
        ]);

        // Kepala Lab belum terautentikasi langsung karena wajib verifikasi OTP
        $this->assertGuest();

        // Sesi OTP harus tersimpan
        $response->assertSessionHas('login_otp');
        $otpSession = session('login_otp');

        $this->assertEquals($this->kepalaLab->id, $otpSession['user_id']);
        $this->assertNotEmpty($otpSession['code']);
        $this->assertEquals(6, strlen($otpSession['code']));

        // Email OTP harus terkirim ke alamat email Kepala Lab
        Mail::assertSent(LoginOtpMail::class, function ($mail) {
            return $mail->hasTo('kepalalab@uinbanten.ac.id');
        });
    }

    public function test_verify_otp_with_incorrect_code_fails(): void
    {
        // Simulasikan sesi OTP yang sedang aktif untuk Kepala Lab
        session([
            'login_otp' => [
                'user_id' => $this->kepalaLab->id,
                'code' => '123456',
                'email' => $this->kepalaLab->email,
                'expires_at' => now()->addMinutes(5)->timestamp,
            ],
        ]);

        $response = $this->post('/login/verify-otp', [
            'otp' => '999999',
        ]);

        $response->assertSessionHasErrors(['otp']);
        $this->assertGuest();
    }

    public function test_verify_otp_with_correct_code_authenticates_kepala_lab(): void
    {
        session([
            'login_otp' => [
                'user_id' => $this->kepalaLab->id,
                'code' => '482915',
                'email' => $this->kepalaLab->email,
                'remember' => false,
                'expires_at' => now()->addMinutes(5)->timestamp,
            ],
        ]);

        $response = $this->post('/login/verify-otp', [
            'otp' => '482915',
        ]);

        // Kepala Lab berhasil terotentikasi
        $this->assertAuthenticatedAs($this->kepalaLab);
        // Sesi OTP harus dibersihkan
        $this->assertFalse(session()->has('login_otp'));
        // Redirect ke /lab (dashboard Kepala Lab)
        $response->assertRedirect(route('kepala-lab.dashboard'));
    }

    public function test_resend_otp_generates_new_code_and_sends_email(): void
    {
        Mail::fake();

        session([
            'login_otp' => [
                'user_id' => $this->kepalaLab->id,
                'code' => '111111',
                'email' => $this->kepalaLab->email,
                'expires_at' => now()->addMinutes(5)->timestamp,
                'resend_available_at' => now()->subSecond()->timestamp, // Cooldown sudah lewat
            ],
        ]);

        $response = $this->post('/login/resend-otp');

        $response->assertSessionHas('success');
        $newSession = session('login_otp');

        // Kode harus baru
        $this->assertNotEquals('111111', $newSession['code']);

        Mail::assertSent(LoginOtpMail::class);
    }

    public function test_cancel_otp_clears_session_and_returns_to_login(): void
    {
        session([
            'login_otp' => [
                'user_id' => $this->kepalaLab->id,
                'code' => '111111',
            ],
        ]);

        $response = $this->post('/login/cancel-otp');

        $response->assertRedirect('/login');
        $this->assertFalse(session()->has('login_otp'));
    }
}
