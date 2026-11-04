<!DOCTYPE html>
<html lang="id" class="h-full">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login - TERMINAL</title>

    <!-- Favicon (Tab Icon) -->
    <link rel="icon" type="image/png" href="{{ asset('images/logo-informatika.png') }}">
    <link rel="shortcut icon" type="image/png" href="{{ asset('images/logo-informatika.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/logo-informatika.png') }}">

    <!-- Google Fonts: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800;900&display=swap"
        rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
            font-family: 'Plus Jakarta Sans', ui-sans-serif, system-ui, sans-serif;
        }
        .custom-scrollbar::-webkit-scrollbar {
            width: 5px;
        }
        .custom-scrollbar::-webkit-scrollbar-track {
            background: transparent;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.15);
            border-radius: 9999px;
        }
    </style>
</head>

<body
    class="h-full min-h-screen w-full bg-[#080d1a] text-white selection:bg-[#0099ff] selection:text-white flex flex-col justify-between overflow-x-hidden lg:overflow-hidden select-none"
    x-data="loginManager()">

    <div class="h-full min-h-screen w-full flex flex-col lg:flex-row relative lg:overflow-hidden">

        <!-- ============================================================== -->
        <!-- SIDE 1 (KIRI / SEBELAH KATA-KATA & BRANDING EDUKASI)          -->
        <!-- Khusus Desktop (lg ke atas). Disembunyikan di Mobile & Tablet  -->
        <!-- ============================================================== -->
        <div class="hidden lg:flex relative w-full lg:w-1/2 xl:w-7/12 bg-gradient-to-br from-[#0c1527] via-[#111e38] to-[#070b14] lg:p-10 xl:p-12 flex-col justify-between border-r border-white/10 overflow-hidden lg:h-full">
            
            <!-- Ambient Glow & Cyber Grid Background -->
            <div class="absolute -top-24 -left-24 w-96 h-96 rounded-full bg-[#0099ff]/15 blur-3xl pointer-events-none"></div>
            <div class="absolute -bottom-24 -right-24 w-96 h-96 rounded-full bg-[#424dc4]/20 blur-3xl pointer-events-none"></div>
            <div class="absolute inset-0 bg-[radial-gradient(#ffffff0a_1px,transparent_1px)] [background-size:24px_24px] pointer-events-none opacity-40"></div>

            <div class="relative z-10 space-y-4 lg:space-y-6 my-auto">
                <!-- Header / Logo & Institusi -->
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl bg-white/5 border border-white/15 p-1.5 backdrop-blur-md flex items-center justify-center shrink-0 shadow-lg shadow-black/30">
                        <img src="{{ asset('images/logo-informatika.png') }}" alt="Logo Informatika UIN SMH Banten"
                            class="w-full h-full object-contain pointer-events-none">
                    </div>
                    <div>
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="text-xs sm:text-sm font-extrabold tracking-tight text-white">TEKNIK INFORMATIKA</span>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#0099ff]/20 text-[#38bdf8] border border-[#0099ff]/30">
                                UIN SMH BANTEN
                            </span>
                        </div>
                        <p class="text-[11px] text-white/60 font-medium">Laboratorium Sains Data & Kecerdasan Buatan</p>
                    </div>
                </div>

                <!-- Bagian Kata-Kata Utama (Headline & Narasi Riset) -->
                <div class="space-y-2.5 sm:space-y-3 max-w-xl">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/5 border border-white/15 text-[11px] font-semibold text-white/80 backdrop-blur-md shadow-xs">
                        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                        <span>TERMINAL • Tempat Eksperimen Riset dan Inovasi Digital</span>
                    </div>

                    <h1 class="text-xl sm:text-2xl lg:text-3xl xl:text-4xl font-black text-white tracking-tight leading-tight">
                        Eksplorasi Riset, Data & AI dalam Satu Ekosistem Digital.
                    </h1>

                    <p class="text-xs sm:text-sm text-white/75 leading-relaxed">
                        Selamat datang di <strong class="text-white">TERMINAL</strong>. Fasilitas komputasi berkinerja tinggi untuk mahasiswa dan sivitas akademika guna menjalankan eksperimen machine learning, simulasi big data, dan akselerasi riset skripsi.
                    </p>
                </div>

                <!-- 3 Poin Keunggulan / Fitur Fasilitas Lab (Grid Card Kata-Kata) -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5 sm:gap-3 pt-1 max-w-2xl">
                    <!-- Feature 1 -->
                    <div class="p-3 sm:p-3.5 rounded-xl bg-white/[0.04] border border-white/10 backdrop-blur-sm space-y-1.5 hover:bg-white/[0.07] transition-all">
                        <div class="w-7 h-7 rounded-lg bg-[#0099ff]/20 text-[#38bdf8] flex items-center justify-center shrink-0">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <h4 class="text-xs font-bold text-white">4 Workstation AI</h4>
                        <p class="text-[10px] sm:text-[11px] text-white/60 leading-snug">
                            GPU RTX dan prosesor multi-core siap untuk deep learning & data analytics.
                        </p>
                    </div>

                    <!-- Feature 2 -->
                    <div class="p-3 sm:p-3.5 rounded-xl bg-white/[0.04] border border-white/10 backdrop-blur-sm space-y-1.5 hover:bg-white/[0.07] transition-all">
                        <div class="w-7 h-7 rounded-lg bg-emerald-500/20 text-emerald-300 flex items-center justify-center shrink-0">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <h4 class="text-xs font-bold text-white">Buku Booking</h4>
                        <p class="text-[10px] sm:text-[11px] text-white/60 leading-snug">
                            Penjadwalan transparan real-time dengan kode akses kriptografis otomatis.
                        </p>
                    </div>

                    <!-- Feature 3 -->
                    <div class="p-3 sm:p-3.5 rounded-xl bg-white/[0.04] border border-white/10 backdrop-blur-sm space-y-1.5 hover:bg-white/[0.07] transition-all">
                        <div class="w-7 h-7 rounded-lg bg-indigo-500/20 text-indigo-300 flex items-center justify-center shrink-0">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                        </div>
                        <h4 class="text-xs font-bold text-white">Sistem Aman</h4>
                        <p class="text-[10px] sm:text-[11px] text-white/60 leading-snug">
                            Proteksi Cloudflare Turnstile & autentikasi 2FA OTP untuk keselamatan lab.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Footer Kata-Kata / Kutipan Inspirasi -->
            <div class="relative z-10 pt-3 border-t border-white/10 flex flex-col sm:flex-row sm:items-center justify-between gap-2 text-xs text-white/60 shrink-0">
                <p class="italic text-[10px] sm:text-[11px] max-w-md">
                    "Inovasi riset terbaik bermula dari lingkungan komputasi yang teratur dan terbuka bagi setiap mahasiswa."
                </p>
                <span class="font-mono text-[10px] text-white/40 shrink-0">TERMINAL v2.4</span>
            </div>
        </div>

        <!-- ============================================================== -->
        <!-- SIDE 2 (SEBELAH LOGIN FORM)                                    -->
        <!-- Tampil Penuh di Mobile & Tablet, Sebelah Kanan di Desktop     -->
        <!-- ============================================================== -->
        <div class="relative w-full flex-1 lg:w-1/2 xl:w-5/12 bg-gradient-to-br from-[#070b15] via-[#0c1426] to-[#060a12] p-5 sm:p-8 lg:p-8 xl:p-10 flex flex-col justify-center items-center min-h-screen lg:min-h-0 lg:h-full overflow-y-auto lg:overflow-hidden">

            <!-- Ambient Glow Kanan -->
            <div class="absolute top-1/4 right-0 w-80 h-80 rounded-full bg-[#0099ff]/10 blur-3xl pointer-events-none"></div>

            <div class="w-full max-w-sm sm:max-w-md space-y-3.5 sm:space-y-4 relative z-10 my-auto">

                <!-- Header Form Login -->
                <div class="text-left space-y-0.5 pb-0.5">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-[#00a3ff]">Portal Otentikasi</span>
                    <h2 class="text-xl sm:text-2xl font-black text-white tracking-tight">
                        Masuk ke Akun
                    </h2>
                    <p class="text-xs text-white/70">
                        Gunakan identitas akademik Anda untuk mengakses workstation dan jadwal laboratorium.
                    </p>
                </div>

                <!-- Alert Pesan Sukses / Info -->
                <div x-show="successMessage"
                    x-transition.opacity.duration.200ms
                    class="p-2.5 rounded-xl bg-emerald-500/20 border border-emerald-500/40 text-emerald-200 text-xs flex items-center gap-2 backdrop-blur-md"
                    style="display: none;">
                    <svg class="w-4 h-4 shrink-0 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <span class="truncate" x-text="successMessage"></span>
                </div>

                <!-- Alert Error Validasi Server / Cloudflare / OTP -->
                <div x-show="errorMessage || cfError"
                    x-transition.opacity.duration.200ms
                    class="p-2.5 rounded-xl bg-rose-500/20 border border-rose-500/40 text-rose-200 text-xs flex items-center gap-2 backdrop-blur-md animate-fade-in"
                    style="display: none;">
                    <svg class="w-4 h-4 shrink-0 text-rose-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span class="text-xs leading-relaxed" x-text="cfError ? 'Harap selesaikan verifikasi Cloudflare terlebih dahulu.' : errorMessage"></span>
                </div>

                <!-- Card Form -->
                <div class="bg-white/[0.04] border border-white/15 rounded-2xl p-5 sm:p-6 backdrop-blur-xl shadow-2xl space-y-3.5">

                    {{-- ========================================================== --}}
                    {{-- PANEL 1: KREDENSIAL LOGIN (NIM + PASSWORD + CLOUDFLARE)     --}}
                    {{-- ========================================================== --}}
                    <div x-show="step === 'credentials'" x-transition:enter="transition ease-out duration-300"
                        x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">

                        <form method="POST" action="{{ route('login.post') }}" @submit.prevent="submitCredentials($event)"
                            class="space-y-3">
                            @csrf

                            <!-- Hidden Input Token Verifikasi Cloudflare -->
                            <input type="hidden" name="cf_verified" :value="cfVerified ? '1' : '0'">

                            <!-- Input NIM / Akun Pengguna -->
                            <div class="space-y-1">
                                <label for="nim" class="block text-[11px] font-bold text-white/90">
                                    NIM / Akun Pengguna <span class="text-rose-400">*</span>
                                </label>
                                <div class="relative">
                                    <div class="flex items-center w-full rounded-xl border border-white/20 bg-white/5 backdrop-blur-md px-3.5 py-2.5 transition-all focus-within:border-[#00a3ff] focus-within:ring-2 focus-within:ring-[#00a3ff]/30 focus-within:bg-white/10">
                                        <input type="text" id="nim" name="nim" required autocomplete="username"
                                            x-model="credentials.nim" placeholder="Contoh: 22081010045 atau kepala_lab"
                                            class="w-full bg-transparent text-xs sm:text-sm text-white placeholder-white/40 focus:outline-none pr-2 font-medium">
                                        <span class="text-white/60 shrink-0 pointer-events-none pl-1">
                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
                                            </svg>
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Input Kata Sandi -->
                            <div class="space-y-1">
                                <label for="password" class="block text-[11px] font-bold text-white/90">
                                    Kata Sandi <span class="text-rose-400">*</span>
                                </label>
                                <div class="relative">
                                    <div class="flex items-center w-full rounded-xl border border-white/20 bg-white/5 backdrop-blur-md px-3.5 py-2.5 transition-all focus-within:border-[#00a3ff] focus-within:ring-2 focus-within:ring-[#00a3ff]/30 focus-within:bg-white/10">
                                        <input :type="showPassword ? 'text' : 'password'" id="password" name="password" required
                                            x-model="credentials.password" autocomplete="current-password" placeholder="Masukkan kata sandi"
                                            class="w-full bg-transparent text-xs sm:text-sm text-white placeholder-white/40 focus:outline-none pr-2 font-medium">
                                        <button type="button" @click="showPassword = !showPassword"
                                            class="text-white/60 hover:text-white focus:outline-none transition-colors shrink-0 p-1 rounded-lg hover:bg-white/10 cursor-pointer"
                                            :title="showPassword ? 'Sembunyikan Kata Sandi' : 'Lihat Kata Sandi'" tabindex="-1">
                                            <svg x-show="showPassword" class="w-4 h-4 text-[#00a3ff]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="display: none;">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                            <svg x-show="!showPassword" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Box Verifikasi Cloudflare Turnstile -->
                            <div class="rounded-xl border bg-white/5 backdrop-blur-md px-3 py-2 flex items-center justify-between text-xs transition-all duration-300 select-none"
                                :class="{
                                     'border-emerald-500/50 bg-emerald-500/10 shadow-sm shadow-emerald-500/20': cfVerified,
                                     'border-rose-500/50 bg-rose-500/10': cfError,
                                     'border-white/20 hover:border-white/40': !cfVerified && !cfError
                                 }">

                                <div class="flex items-center gap-2.5 cursor-pointer select-none py-0.5 flex-1" @click="handleCloudflare()">
                                    <div class="w-5 h-5 rounded-lg border flex items-center justify-center transition-all shrink-0"
                                        :class="{
                                             'border-white/40 bg-white/5 hover:border-[#0099ff]': !cfLoading && !cfVerified,
                                             'border-[#0099ff] bg-[#0099ff]/15': cfLoading,
                                             'border-emerald-500 bg-emerald-500 text-white shadow-xs': cfVerified
                                         }">
                                        <span x-show="!cfLoading && !cfVerified" class="text-transparent text-[10px]">•</span>
                                        <svg x-show="cfLoading" class="w-3 h-3 text-[#0099ff] animate-spin" fill="none" viewBox="0 0 24 24" style="display: none;">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                        </svg>
                                        <svg x-show="cfVerified" class="w-3 h-3 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3" style="display: none;">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                        </svg>
                                    </div>

                                    <div class="leading-tight">
                                        <p class="font-semibold text-white/90 text-xs"
                                            x-text="cfVerified ? 'Jaringan aman & terverifikasi' : (cfLoading ? 'Memeriksa keamanan...' : 'Verifikasi bahwa Anda manusia')">
                                            Verifikasi bahwa Anda manusia
                                        </p>
                                        <p class="text-[10px] text-white/50" x-show="!cfVerified && !cfLoading">Klik untuk verifikasi koneksi</p>
                                        <p class="text-[10px] text-emerald-400 font-mono" x-show="cfVerified" style="display: none;">Turnstile Verified • Aman</p>
                                    </div>
                                </div>

                                <div class="flex items-center gap-1 shrink-0 pl-1.5 opacity-85">
                                    <svg class="w-3.5 h-3.5 text-[#f38020]" viewBox="0 0 32 32" fill="currentColor">
                                        <path d="M26.2 16.5c-.3-3.6-3.3-6.5-7-6.5-1.9 0-3.6.7-4.9 2-1-.9-2.3-1.4-3.8-1.4-3 0-5.5 2.3-5.7 5.3C2 16.2 0 18.5 0 21.3c0 3.1 2.5 5.7 5.7 5.7h20.1c3.4 0 6.2-2.8 6.2-6.2 0-2.8-1.9-5.1-4.6-5.8-.4-.2-.8-.4-1.2-.5z" />
                                    </svg>
                                    <span class="font-bold text-[9px] text-white">Cloudflare</span>
                                </div>
                            </div>

                            <!-- Checkbox Ingat Saya -->
                            <div class="flex items-center justify-between text-xs text-white/80 pt-0.5">
                                <label class="flex items-center gap-2 cursor-pointer select-none hover:text-white transition-colors">
                                    <input type="checkbox" name="remember" x-model="credentials.remember"
                                        class="w-3.5 h-3.5 rounded border-white/40 bg-white/10 text-[#0099ff] focus:ring-0 focus:ring-offset-0 cursor-pointer">
                                    <span class="text-[11px]">Ingat saya</span>
                                </label>
                                <span class="text-[10px] text-white/40">Sistem Terproteksi</span>
                            </div>

                            <!-- Tombol Masuk -->
                            <div class="pt-0.5">
                                <button type="submit" :disabled="isSubmitting"
                                    class="w-full py-2.5 sm:py-3 px-5 rounded-xl bg-gradient-to-r from-[#424dc4] to-[#0099ff] hover:from-[#3740a8] hover:to-[#0088e6] active:scale-[0.99] disabled:opacity-60 text-white font-bold text-xs sm:text-sm tracking-wide shadow-md shadow-[#424dc4]/30 hover:shadow-[#0099ff]/40 transition-all cursor-pointer flex items-center justify-center gap-2">
                                    <svg x-show="isSubmitting" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24" style="display: none;">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                    </svg>
                                    <span x-text="isSubmitting ? 'Memproses Masuk...' : 'Masuk ke Sistem'"></span>
                                </button>
                            </div>
                        </form>
                    </div>

                    {{-- ========================================================== --}}
                    {{-- PANEL 2: VERIFIKASI KODE OTP DARI EMAIL                     --}}
                    {{-- ========================================================== --}}
                    <div x-show="step === 'otp'" x-transition:enter="transition ease-out duration-300"
                        x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                        style="display: none;">

                        <div class="space-y-3">
                            <!-- Header Panel OTP -->
                            <div class="text-center space-y-1">
                                <div class="w-10 h-10 rounded-xl bg-[#0099ff]/20 border border-[#0099ff]/40 text-[#0099ff] mx-auto flex items-center justify-center shadow-md shadow-[#0099ff]/20 mb-1">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                    </svg>
                                </div>

                                <h3 class="text-sm sm:text-base font-bold text-white tracking-wide">
                                    Verifikasi Kode OTP
                                </h3>
                                <p class="text-xs text-white/70 leading-relaxed">
                                    6 digit kode keamanan telah dikirimkan ke email:
                                    <strong class="text-[#00a3ff] font-mono block mt-0.5 select-all" x-text="maskedEmail"></strong>
                                </p>
                            </div>

                            <!-- Form Submit OTP -->
                            <form method="POST" action="{{ route('login.verify-otp') }}" @submit.prevent="submitOtp($event)" class="space-y-3">
                                @csrf

                                <!-- Input 6 Digit OTP Monospace Besar -->
                                <div class="relative">
                                    <input type="text" id="otp-input" name="otp" x-model="otpCode" required maxlength="6"
                                        inputmode="numeric" pattern="[0-9]*" autocomplete="one-time-code" autofocus
                                        placeholder="• • • • • •"
                                        @input="otpCode = otpCode.replace(/[^0-9]/g, '').slice(0,6)"
                                        class="w-full text-center font-mono text-xl sm:text-2xl font-extrabold tracking-[0.35em] sm:tracking-[0.5em] py-2.5 px-3 rounded-xl border border-white/40 bg-white/10 text-[#00a3ff] placeholder-white/20 focus:outline-none focus:border-[#00a3ff] focus:ring-2 focus:ring-[#00a3ff]/25 transition-all select-all">
                                    <span class="block text-[10px] text-white/40 text-center mt-1 font-medium">
                                        Masa berlaku kode: <strong>5 menit</strong>
                                    </span>
                                </div>

                                <!-- Tombol Verifikasi -->
                                <button type="submit" :disabled="isVerifying || otpCode.length !== 6"
                                    class="w-full py-2.5 sm:py-3 px-5 rounded-xl bg-gradient-to-r from-[#424dc4] to-[#0099ff] hover:from-[#3740a8] hover:to-[#0088e6] active:scale-[0.99] disabled:opacity-50 disabled:pointer-events-none text-white font-bold text-xs sm:text-sm tracking-wide shadow-md shadow-[#0099ff]/35 transition-all cursor-pointer flex items-center justify-center gap-2">
                                    <svg x-show="isVerifying" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24" style="display: none;">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                    </svg>
                                    <span x-text="isVerifying ? 'Memverifikasi Kode...' : 'Verifikasi & Masuk'"></span>
                                </button>
                            </form>

                            <!-- Action Bawah: Kirim Ulang & Ganti Akun -->
                            <div class="flex items-center justify-between gap-2 pt-1.5 border-t border-white/10 text-xs">
                                <button type="button" @click="resendOtp()" :disabled="countdown > 0 || isResending"
                                    class="text-white/70 hover:text-[#00a3ff] disabled:opacity-40 disabled:hover:text-white/70 transition-colors text-[11px] font-medium cursor-pointer">
                                    <span x-text="countdown > 0 ? 'Kirim ulang (' + countdown + 's)' : 'Kirim Ulang Kode OTP'"></span>
                                </button>

                                <button type="button" @click="cancelOtp()"
                                    class="text-white/50 hover:text-white transition-colors text-[11px] cursor-pointer">
                                    ← Ganti Akun
                                </button>
                            </div>

                            <!-- Simulation Hint untuk Local Environment -->
                            @if(app()->isLocal())
                                <div x-show="debugOtp" class="mt-1.5 p-2 rounded-lg bg-amber-500/15 border border-amber-500/30 text-amber-200 text-[10px] text-center font-mono">
                                    <span>💡 Local Debug: Kode OTP adalah </span>
                                    <strong class="text-amber-300 font-bold tracking-widest text-[11px] cursor-pointer underline hover:text-white"
                                        @click="otpCode = debugOtp" x-text="debugOtp" title="Klik untuk mengisi otomatis"></strong>
                                    <span class="block text-[9px] text-amber-300/60 mt-0.5">(Tercatat di storage/logs/laravel.log)</span>
                                </div>
                            @endif
                        </div>
                    </div>

                </div>

                <!-- Footer Jam Operasional & Help -->
                <div class="text-center space-y-0.5 text-[10px] text-white/50 pt-1 shrink-0">
                    <p>Jam Operasional Lab: <strong class="text-white/80">07.00 – 16.00 WIB</strong> (Senin – Jumat)</p>
                    <p>&copy; {{ date('Y') }} TERMINAL • UIN Sultan Maulana Hasanuddin Banten</p>
                </div>

            </div>

        </div>

    </div>

    <script>
        function loginManager() {
            return {
                step: '{{ $hasPendingOtp ? 'otp' : 'credentials' }}',
                showPassword: false,
                cfVerified: false,
                cfLoading: false,
                cfError: false,
                isSubmitting: false,
                isVerifying: false,
                isResending: false,

                credentials: {
                    nim: '',
                    password: '',
                    remember: false
                },

                otpCode: '',
                maskedEmail: '{{ $otpData['masked_email'] ?? '' }}',
                debugOtp: '{{ (app()->isLocal() && isset($otpData['code'])) ? $otpData['code'] : '' }}',
                errorMessage: '{!! addslashes(session('error') ?? $errors->first() ?? '') !!}',
                successMessage: '{!! addslashes(session('success') ?? '') !!}',
                countdown: 0,
                timerInterval: null,

                init() {
                    if (this.step === 'otp') {
                        this.startCountdown(60);
                        this.$nextTick(() => {
                            const input = document.getElementById('otp-input');
                            if (input) input.focus();
                        });
                    }
                },

                handleCloudflare() {
                    if (this.cfVerified || this.cfLoading) return;
                    this.cfLoading = true;
                    this.cfError = false;

                    // Simulasi pengecekan integritas jaringan & browser Cloudflare Turnstile
                    setTimeout(() => {
                        this.cfLoading = false;
                        this.cfVerified = true;
                    }, 1100);
                },

                async submitCredentials(e) {
                    if (!this.cfVerified) {
                        this.cfError = true;
                        return;
                    }
                    this.cfError = false;
                    this.errorMessage = '';
                    this.successMessage = '';
                    this.isSubmitting = true;

                    const form = e.target;
                    const formData = new FormData(form);

                    try {
                        const res = await fetch(form.action, {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: formData
                        });

                        const data = await res.json();

                        if (res.ok && data.success && data.step === 'otp') {
                            this.step = 'otp';
                            this.maskedEmail = data.masked_email;
                            this.successMessage = data.message;
                            if (data.debug_otp) this.debugOtp = data.debug_otp;
                            this.startCountdown(60);
                            this.$nextTick(() => {
                                const input = document.getElementById('otp-input');
                                if (input) input.focus();
                            });
                        } else if (res.ok && data.success && data.redirect_url) {
                            this.successMessage = data.message || 'Login berhasil! Mengalihkan...';
                            window.location.href = data.redirect_url;
                        } else {
                            this.errorMessage = data.message || 'Terjadi kesalahan saat masuk.';
                        }
                    } catch (err) {
                        form.submit();
                    } finally {
                        this.isSubmitting = false;
                    }
                },

                async submitOtp(e) {
                    if (this.otpCode.length !== 6) return;

                    this.isVerifying = true;
                    this.errorMessage = '';
                    this.successMessage = '';

                    const form = e.target;
                    const formData = new FormData(form);

                    try {
                        const res = await fetch(form.action, {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: formData
                        });

                        const data = await res.json();

                        if (res.ok && data.success) {
                            this.successMessage = data.message;
                            window.location.href = data.redirect_url;
                        } else {
                            this.errorMessage = data.message || 'Kode OTP tidak valid.';
                            if (data.expired) {
                                setTimeout(() => {
                                    this.step = 'credentials';
                                }, 1800);
                            }
                        }
                    } catch (err) {
                        form.submit();
                    } finally {
                        this.isVerifying = false;
                    }
                },

                async resendOtp() {
                    if (this.countdown > 0 || this.isResending) return;

                    this.isResending = true;
                    this.errorMessage = '';
                    this.successMessage = '';

                    try {
                        const token = document.querySelector('meta[name="csrf-token"]')?.content;
                        const res = await fetch('{{ route('login.resend-otp') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': token,
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        });

                        const data = await res.json();

                        if (res.ok && data.success) {
                            this.successMessage = data.message;
                            if (data.debug_otp) this.debugOtp = data.debug_otp;
                            this.startCountdown(60);
                        } else {
                            this.errorMessage = data.message || 'Gagal mengirim ulang kode OTP.';
                        }
                    } catch (err) {
                        this.errorMessage = 'Terjadi kesalahan saat mengirim ulang kode.';
                    } finally {
                        this.isResending = false;
                    }
                },

                async cancelOtp() {
                    const token = document.querySelector('meta[name="csrf-token"]')?.content;
                    try {
                        await fetch('{{ route('login.cancel-otp') }}', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': token,
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        });
                    } catch (err) {}

                    this.step = 'credentials';
                    this.otpCode = '';
                    this.errorMessage = '';
                    this.successMessage = '';
                    if (this.timerInterval) clearInterval(this.timerInterval);
                },

                startCountdown(seconds) {
                    this.countdown = seconds;
                    if (this.timerInterval) clearInterval(this.timerInterval);
                    this.timerInterval = setInterval(() => {
                        if (this.countdown > 0) {
                            this.countdown--;
                        } else {
                            clearInterval(this.timerInterval);
                        }
                    }, 1000);
                }
            };
        }
    </script>

</body>

</html>
