<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\LoginOtpMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class LoginController extends Controller
{
    /**
     * Tampilkan formulir login / panel OTP.
     */
    public function showLoginForm()
    {
        $hasPendingOtp = session()->has('login_otp');
        $otpData = session('login_otp', []);

        return view('auth.login', compact('hasPendingOtp', 'otpData'));
    }

    /**
     * Langkah 1: Validasi Kredensial (NIM + Password + Cloudflare) dan Kirim OTP ke Email.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'nim' => ['required', 'string'],
            'password' => ['required', 'string'],
        ], [
            'nim.required' => 'Nomor Induk Mahasiswa (NIM) wajib diisi.',
            'password.required' => 'Kata sandi wajib diisi.',
        ]);

        if (! $request->boolean('cf_verified')) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Verifikasi keamanan Cloudflare wajib diselesaikan.',
                ], 422);
            }

            return back()->withInput($request->only('nim', 'remember'))->withErrors([
                'nim' => 'Verifikasi keamanan Cloudflare wajib diselesaikan.',
            ]);
        }

        $user = User::where(function ($query) use ($credentials) {
            $input = trim($credentials['nim']);
            $query->where('nim', $input)
                ->orWhere('email', $input);
            if (in_array(strtolower($input), ['kepala_lab', 'kepalalab', '198501012010011001'])) {
                $query->orWhere('role', 'kepala_lab');
            }
        })
            ->where('is_active', true)
            ->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'NIM / akun atau kata sandi yang Anda masukkan salah.',
                ], 422);
            }

            return back()->withInput($request->only('nim', 'remember'))->withErrors([
                'nim' => 'NIM / akun atau kata sandi yang Anda masukkan salah.',
            ]);
        }

        $remember = $request->boolean('remember');

        // KHUSUS MAHASISWA: Langsung login tanpa verifikasi OTP email
        if ($user->role !== 'kepala_lab') {
            Auth::login($user, $remember);
            $request->session()->regenerate();

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'step' => 'completed',
                    'redirect_url' => route('mahasiswa.dashboard'),
                    'message' => 'Login berhasil! Mengalihkan ke dashboard...',
                ]);
            }

            return redirect()->intended(route('mahasiswa.dashboard'));
        }

        // KHUSUS KEPALA LAB: Verifikasi 2FA dengan OTP yang dikirim ke email
        // Generate 6 digit angka OTP acak
        $otpCode = sprintf('%06d', random_int(100000, 999999));
        $maskedEmail = $this->maskEmail($user->email ?? 'kepalalab@uinbanten.ac.id');

        // Simpan sesi verifikasi OTP (berlaku 5 menit)
        session([
            'login_otp' => [
                'user_id' => $user->id,
                'code' => $otpCode,
                'email' => $user->email,
                'masked_email' => $maskedEmail,
                'remember' => $remember,
                'expires_at' => now()->addMinutes(5)->timestamp,
                'resend_available_at' => now()->addSeconds(60)->timestamp,
            ],
        ]);

        // Kirim email OTP resmi ke Kepala Lab
        if ($user->email) {
            try {
                Mail::to($user->email)->send(new LoginOtpMail($otpCode, $user));
            } catch (\Throwable $e) {
                Log::error('Gagal mengirimkan email OTP login Kepala Lab: '.$e->getMessage());
            }
        }

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'step' => 'otp',
                'masked_email' => $maskedEmail,
                'message' => 'Kode OTP verifikasi otoritas telah dikirim ke email Kepala Lab.',
                'debug_otp' => app()->isLocal() ? $otpCode : null,
            ]);
        }

        return redirect()->route('login')->with('success', 'Kode OTP 6 digit telah dikirimkan ke email '.$maskedEmail);
    }

    /**
     * Langkah 2: Verifikasi Kode OTP dari Panel Login dan Selesaikan Login.
     */
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'otp' => ['required', 'string', 'digits:6'],
        ], [
            'otp.required' => 'Masukkan 6 digit kode OTP yang telah dikirim ke email Anda.',
            'otp.digits' => 'Kode OTP harus berupa 6 digit angka.',
        ]);

        $otpSession = session('login_otp');

        if (! $otpSession || ! isset($otpSession['code'], $otpSession['user_id'])) {
            $msg = 'Sesi verifikasi OTP tidak ditemukan atau telah kedaluwarsa. Silakan masukkan kembali NIM dan kata sandi Anda.';
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $msg, 'expired' => true], 422);
            }

            return redirect()->route('login')->withErrors(['otp' => $msg]);
        }

        // Cek kedaluwarsa 5 menit
        if (now()->timestamp > $otpSession['expires_at']) {
            session()->forget('login_otp');
            $msg = 'Kode OTP telah kedaluwarsa (lebih dari 5 menit). Silakan login ulang.';
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $msg, 'expired' => true], 422);
            }

            return redirect()->route('login')->withErrors(['otp' => $msg]);
        }

        // Cek kecocokan kode OTP
        if ($request->otp !== $otpSession['code']) {
            $msg = 'Kode OTP yang Anda masukkan salah. Periksa kembali email Anda.';
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }

            return back()->withErrors(['otp' => $msg]);
        }

        // OTP Valid: Login pengguna
        $userId = $otpSession['user_id'];
        $remember = $otpSession['remember'] ?? false;
        session()->forget('login_otp');

        Auth::loginUsingId($userId, $remember);
        $request->session()->regenerate();

        $user = Auth::user();
        $targetUrl = ($user->role === 'kepala_lab') ? route('kepala-lab.dashboard') : route('mahasiswa.dashboard');

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'redirect_url' => $targetUrl,
                'message' => 'Verifikasi berhasil! Mengalihkan ke dashboard...',
            ]);
        }

        // Kepala Lab diarahkan langsung ke portal Kepala Lab tanpa tersangkut intended URL mahasiswa
        if ($user->role === 'kepala_lab') {
            return redirect()->to($targetUrl);
        }

        return redirect()->intended($targetUrl);
    }

    /**
     * Kirim Ulang Kode OTP ke Email dengan Proteksi Cooldown 60 Detik.
     */
    public function resendOtp(Request $request)
    {
        $otpSession = session('login_otp');

        if (! $otpSession || ! isset($otpSession['user_id'])) {
            $msg = 'Sesi login telah berakhir. Silakan login kembali.';
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $msg, 'expired' => true], 422);
            }

            return redirect()->route('login')->withErrors(['otp' => $msg]);
        }

        // Cek cooldown 60 detik
        if (isset($otpSession['resend_available_at']) && now()->timestamp < $otpSession['resend_available_at']) {
            $remaining = $otpSession['resend_available_at'] - now()->timestamp;
            $msg = "Harap tunggu {$remaining} detik sebelum meminta kirim ulang kode OTP.";
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $msg], 429);
            }

            return back()->withErrors(['otp' => $msg]);
        }

        $user = User::find($otpSession['user_id']);
        if (! $user) {
            session()->forget('login_otp');

            return redirect()->route('login');
        }

        // Generate kode OTP baru
        $newOtp = sprintf('%06d', random_int(100000, 999999));
        session([
            'login_otp' => array_merge($otpSession, [
                'code' => $newOtp,
                'expires_at' => now()->addMinutes(5)->timestamp,
                'resend_available_at' => now()->addSeconds(60)->timestamp,
            ]),
        ]);

        if ($user->email) {
            try {
                Mail::to($user->email)->send(new LoginOtpMail($newOtp, $user));
            } catch (\Throwable $e) {
                Log::error('Gagal mengirim ulang email OTP: '.$e->getMessage());
            }
        }

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Kode OTP baru telah berhasil dikirim ke email Anda.',
                'debug_otp' => app()->isLocal() ? $newOtp : null,
            ]);
        }

        return back()->with('success', 'Kode OTP baru telah dikirim ke email Anda.');
    }

    /**
     * Batalkan proses OTP dan kembali ke panel login kredensial.
     */
    public function cancelOtp(Request $request)
    {
        session()->forget('login_otp');

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(['success' => true]);
        }

        return redirect()->route('login');
    }

    /**
     * Logout pengguna dari sistem.
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    /**
     * Sensor alamat email untuk privasi tampilan (contoh: su*****@uinbanten.ac.id).
     */
    protected function maskEmail(string $email): string
    {
        $parts = explode('@', $email);
        if (count($parts) !== 2) {
            return $email;
        }

        $name = $parts[0];
        $domain = $parts[1];
        $len = strlen($name);

        if ($len <= 2) {
            $maskedName = substr($name, 0, 1).'***';
        } else {
            $maskedName = substr($name, 0, 2).str_repeat('*', max(3, $len - 2));
        }

        return $maskedName.'@'.$domain;
    }
}
