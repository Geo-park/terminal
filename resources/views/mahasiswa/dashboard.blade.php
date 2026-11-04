@extends('layouts.mahasiswa')

@section('title', 'Dashboard Mahasiswa')
@section('page-title', 'Dashboard')

@section('content')
    <div class="space-y-6" x-data="mahasiswaDashboard({
                                    hasActiveSession: {{ $activeSession ? 'true' : 'false' }},
                                    elapsedSeconds: {{ $activeSession ? (int) max(0, \Carbon\Carbon::now('Asia/Jakarta')->diffInSeconds($activeSession->check_in_at)) : 0 }},
                                    remainingSeconds: {{ $activeSession && $activeSession->scheduled_end_at ? (int) max(0, \Carbon\Carbon::now('Asia/Jakarta')->diffInSeconds($activeSession->scheduled_end_at, false)) : 0 }}
                                })">

        <!-- Alert Notifikasi Flash Sukses / Error -->
        @if(session('success'))
            <div
                class="p-4 sm:p-5 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm flex items-center justify-between shadow-xs animate-fade-in">
                <div class="flex items-center gap-3">
                    <div
                        class="w-9 h-9 rounded-xl bg-emerald-500 text-white flex items-center justify-center shrink-0 shadow-xs">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                    <div>
                        <h4 class="font-bold text-emerald-950 text-sm">Berhasil!</h4>
                        <p class="text-xs text-emerald-700 mt-0.5">{{ session('success') }}</p>
                    </div>
                </div>
                <button @click="$el.parentElement.remove()"
                    class="text-emerald-500 hover:text-emerald-800 p-1.5 rounded-lg transition-colors cursor-pointer">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        @endif

        @if($errors->any())
            <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-sm space-y-1 animate-shake">
                <div class="flex items-center gap-2 font-bold text-rose-900 text-xs sm:text-sm">
                    <svg class="w-4 h-4 shrink-0 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>Pemberitahuan:</span>
                </div>
                <ul class="list-disc list-inside text-xs pl-5 text-rose-700 space-y-0.5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- 1. Hero Greeting Banner (Split Layout sesuai DESIGN.md) -->
        <div
            class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-[#424dc4] via-[#5c67de] to-[#4648d4] p-6 sm:p-8 text-white elevation-2">
            <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div class="space-y-2 max-w-2xl">
                    <div
                        class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/15 backdrop-blur-md text-xs font-semibold tracking-wide text-white border border-white/20">
                        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                        <span>TERMINAL • Tempat Eksperimen Riset dan Inovasi Digital</span>
                    </div>
                    <h2 class="text-2xl sm:text-3xl font-extrabold tracking-tight">
                        Halo, {{ auth()->user()->name ?? 'Mahasiswa' }}! 👋
                    </h2>
                    <p class="text-white/85 text-sm sm:text-base leading-relaxed">
                        Laboratorium siap digunakan. Anda dapat mengajukan peminjaman, memantau sisa kuota harian, serta
                        mengakhiri sesi aktif kapan saja seperti billing warnet.
                    </p>
                    <div class="flex flex-wrap items-center gap-3 pt-2 text-xs font-medium text-white/90">
                        <span class="inline-flex items-center gap-1.5 bg-black/15 px-3 py-1 rounded-lg">
                            <svg class="w-4 h-4 text-[#eff4ff]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Jam Operasional: <strong>07.00 - 16.00 WIB</strong> (Senin–Jumat)
                        </span>
                        <span class="inline-flex items-center gap-1.5 bg-black/15 px-3 py-1 rounded-lg">
                            <svg class="w-4 h-4 text-emerald-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Durasi Maksimal: <strong>5 Jam / Sesi</strong>
                        </span>
                    </div>
                </div>

                <!-- Hero Stats Box (Dihitung Real-time dari Database) -->
                <div
                    class="bg-white/10 backdrop-blur-md rounded-2xl p-5 border border-white/20 w-full md:w-auto md:min-w-[240px] flex flex-col justify-between">
                    <div>
                        <span class="text-xs font-semibold uppercase tracking-wider text-white/75">Status Kuota Hari
                            Ini</span>
                        @if ($isUnlimited ?? false)
                            <div class="mt-2 flex items-baseline gap-2">
                                <span class="text-3xl sm:text-4xl font-extrabold tracking-tight text-white">{{ $usedSessionsToday }} Sesi</span>
                                <span class="text-xs font-bold text-emerald-300 bg-emerald-400/20 px-2.5 py-0.5 rounded-full border border-emerald-300/30">VIP Unlimited ∞</span>
                            </div>
                            <p class="text-[12px] text-white/80 mt-1">
                                Akun Khusus Pengujian: <strong>Bebas reservasi tanpa batas kuota harian.</strong>
                            </p>
                        @else
                            <div class="mt-2 flex items-baseline gap-2">
                                <span class="text-4xl font-extrabold tracking-tight text-white">{{ $usedSessionsToday }}</span>
                                <span class="text-lg font-semibold text-white/70">/ 2 Sesi Digunakan</span>
                            </div>
                            <p class="text-[12px] text-white/80 mt-1">
                                @if ($isOperatingHoursOver ?? false)
                                    Sisa <strong>2 sesi (Full)</strong> • Kuota diisi ulang sehabis jam operasional lab.
                                @elseif ($remainingQuotaToday > 0)
                                    Tersisa <strong>{{ $remainingQuotaToday }} sesi lagi</strong> untuk hari ini.
                                @else
                                    Kuota harian Anda <strong>sudah habis</strong> hari ini.
                                @endif
                            </p>
                        @endif
                    </div>
                    <div class="mt-4 pt-3 border-t border-white/15 flex items-center justify-between text-xs">
                        <span class="text-white/70">Total Unit Lab:</span>
                        <span class="font-bold text-white bg-white/20 px-2.5 py-0.5 rounded-full">{{ $computers->count() }}
                            Komputer</span>
                    </div>
                </div>
            </div>

            <!-- Decorative background shapes -->
            <div class="absolute -right-10 -bottom-10 w-64 h-64 rounded-full bg-white/10 blur-2xl pointer-events-none">
            </div>
            <div class="absolute right-40 -top-20 w-48 h-48 rounded-full bg-white/10 blur-xl pointer-events-none"></div>
        </div>


        {{-- 2. KONDISIONAL KONTEN: BILLING HANYA MUNCUL JIKA ADA SESI AKTIF --}}
        @if ($activeSession)
            <!-- Sesi Komputer Aktif Saat Ini (Model Billing Warnet - BR-015 / BR-018) -->
            <div
                class="rounded-2xl border-2 border-[#5c67de] bg-gradient-to-r from-white via-[#eff4ff] to-white p-6 elevation-2 transition-all">
                <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">

                    <!-- Left Info -->
                    <div class="flex items-start gap-4">
                        <div class="relative shrink-0">
                            <div
                                class="w-14 h-14 rounded-2xl bg-[#424dc4] text-white flex items-center justify-center shadow-md shadow-[#424dc4]/30">
                                <svg class="w-7 h-7 animate-pulse" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                    stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <span class="absolute -top-1 -right-1 flex h-3.5 w-3.5">
                                <span
                                    class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                <span
                                    class="relative inline-flex rounded-full h-3.5 w-3.5 bg-emerald-500 border-2 border-white"></span>
                            </span>
                        </div>

                        <div class="space-y-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <span
                                    class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200 uppercase tracking-wider">
                                    ● SESI BILLING AKTIF
                                </span>
                                <span class="text-xs text-slate-500 font-mono">
                                    ID:
                                    #RES-{{ $activeSession->reservation ? $activeSession->reservation->created_at->format('Ymd') : date('Ymd') }}-{{ sprintf('%02d', $activeSession->reservation_id ?? 1) }}
                                </span>
                            </div>
                            <h3 class="text-lg font-bold text-[#0b1c30]">
                                {{ $activeSession->computer->computer_code ?? 'PC-??' }}
                                <span
                                    class="text-sm font-semibold text-slate-600">({{ $activeSession->computer->computer_name ?? 'Workstation' }})</span>
                            </h3>
                            <div class="flex flex-wrap items-center gap-4 text-xs text-slate-600">
                                <span>Mulai: <strong class="text-slate-800">{{ $activeSession->check_in_at->format('H:i') }}
                                        WIB</strong></span>
                                <span>Batas Selesai: <strong
                                        class="text-slate-800">{{ $activeSession->scheduled_end_at ? $activeSession->scheduled_end_at->format('H:i') : '-' }}
                                        WIB</strong></span>
                                @if ($activeSession->reservation && $activeSession->reservation->accessCode)
                                    <span>Kode: <strong
                                            class="font-mono text-[#424dc4] bg-[#eff4ff] px-2 py-0.5 rounded-md border border-[#dce9ff]">{{ $activeSession->reservation->accessCode->code }}</strong></span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Center: Live Billing Timers -->
                    <div
                        class="flex items-center gap-4 sm:gap-6 bg-white py-3 px-5 rounded-xl border border-[#e2e8f0] elevation-1 w-full sm:w-auto justify-around sm:justify-start self-stretch sm:self-auto lg:self-center">
                        <div class="text-center">
                            <span class="block text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Durasi
                                Berjalan</span>
                            <span class="block text-xl sm:text-2xl font-extrabold text-[#0b1c30] font-mono"
                                x-text="elapsedTime">00:00:00</span>
                        </div>
                        <div class="h-8 w-px bg-slate-200"></div>
                        <div class="text-center">
                            <span class="block text-[11px] font-semibold text-[#5c67de] uppercase tracking-wider">Sisa
                                Waktu</span>
                            <span class="block text-xl sm:text-2xl font-extrabold text-[#424dc4] font-mono"
                                x-text="remainingTime">00:00:00</span>
                        </div>
                    </div>

                    <!-- Right: Stop Session Button (Warnet Billing Action) -->
                    <div class="flex items-center gap-3 self-stretch sm:self-auto">
                        <button @click="openEndSessionModal()"
                            class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-5 py-3 rounded-xl bg-gradient-to-r from-rose-600 to-rose-700 hover:from-rose-700 hover:to-rose-800 text-white font-bold text-sm shadow-md shadow-rose-600/20 hover:scale-[1.02] active:scale-[0.98] transition-all cursor-pointer">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M9 10a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1v-4z" />
                            </svg>
                            <span>Akhiri Sesi Sekarang</span>
                        </button>
                    </div>
                </div>
            </div>

        @elseif ($approvedReservation)
            <!-- Notifikasi Khusus: Reservasi Disetujui & Kode Akses Telah Muncul -->
            <div
                class="rounded-2xl border-2 border-emerald-400 bg-gradient-to-r from-emerald-50/70 via-white to-emerald-50/50 p-5 sm:p-6 shadow-sm">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div class="flex items-start gap-4">
                        <div
                            class="w-12 h-12 rounded-2xl bg-emerald-500 text-white flex items-center justify-center shrink-0 shadow-md shadow-emerald-500/20">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                            </svg>
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <span
                                    class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 uppercase tracking-wider">
                                    Reservasi Disetujui
                                </span>
                                <span class="text-xs text-slate-500 font-mono">
                                    #RES-{{ $approvedReservation->created_at->format('Ymd') }}-{{ sprintf('%02d', $approvedReservation->id) }}
                                </span>
                            </div>
                            <h3 class="text-base sm:text-lg font-bold text-[#0b1c30] mt-0.5">
                                Kode Akses Siap: {{ $approvedReservation->computer->computer_code }}
                                ({{ $approvedReservation->computer->computer_name }})
                            </h3>
                            <p class="text-xs text-slate-600 mt-0.5">
                                Jadwal:
                                <strong>{{ \Carbon\Carbon::parse($approvedReservation->reservation_date)->isoFormat('dddd, D MMMM Y') }}</strong>
                                pukul <strong>{{ substr($approvedReservation->start_time, 0, 5) }} -
                                    {{ substr($approvedReservation->end_time, 0, 5) }} WIB</strong>.
                            </p>
                            <div class="flex items-center gap-1.5 mt-2 text-[11px] text-emerald-800 bg-emerald-100/70 px-2.5 py-1 rounded-lg w-fit border border-emerald-200/60">
                                <svg class="w-3.5 h-3.5 text-emerald-700 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                </svg>
                                <span>
                                    Disetujui oleh: <strong class="font-bold text-emerald-950">{{ $approvedReservation->approver->name ?? 'Kepala Lab' }}</strong> (Kepala Lab)
                                    @if($approvedReservation->approved_at)
                                        • {{ $approvedReservation->approved_at->isoFormat('D MMM Y, HH:mm') }} WIB
                                    @endif
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Token 16 Digit & Petunjuk Input di Layar PC Workstation -->
                    <div class="flex flex-col sm:flex-row sm:items-center gap-3">
                        <div class="bg-white border-2 border-[#424dc4] px-4 py-3 rounded-2xl shadow-xs text-center sm:text-left flex items-center justify-between gap-3">
                            <div>
                                <span class="block text-[10px] uppercase font-bold text-slate-400">16 Digit Token Masuk PC</span>
                                <span
                                    class="font-mono text-base sm:text-lg font-black text-[#424dc4] tracking-wider select-all">
                                    {{ $approvedReservation->accessCode->code ?? 'MENUNGGU-TOKEN' }}
                                </span>
                            </div>
                            @if($approvedReservation->accessCode)
                                <button type="button"
                                    x-data="{ copied: false }"
                                    @click="navigator.clipboard.writeText('{{ $approvedReservation->accessCode->code }}'); copied = true; setTimeout(() => copied = false, 2000)"
                                    class="px-2.5 py-1.5 rounded-xl text-[11px] font-bold border transition-all cursor-pointer flex items-center gap-1 shrink-0"
                                    :class="copied ? 'bg-emerald-50 text-emerald-700 border-emerald-300' : 'bg-slate-100 hover:bg-slate-200 text-slate-700 border-slate-200'">
                                    <svg x-show="!copied" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                    </svg>
                                    <svg x-show="copied" class="w-3.5 h-3.5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    <span x-text="copied ? 'Tersalin!' : 'Salin'"></span>
                                </button>
                            @endif
                        </div>

                        <!-- Petunjuk Input di Layar PC -->
                        @php
                            $nowJkt = \Carbon\Carbon::now('Asia/Jakarta');
                            $cardResDate = $approvedReservation->reservation_date->format('Y-m-d');
                            $cardStartAt = \Carbon\Carbon::createFromFormat('Y-m-d H:i', $cardResDate . ' ' . substr($approvedReservation->start_time, 0, 5), 'Asia/Jakarta');
                            $cardEndAt = \Carbon\Carbon::createFromFormat('Y-m-d H:i', $cardResDate . ' ' . substr($approvedReservation->end_time, 0, 5), 'Asia/Jakarta');
                            $canStartBilling = $nowJkt->gte($cardStartAt) && $nowJkt->lt($cardEndAt);
                            $isBeforeStart = $nowJkt->lt($cardStartAt);
                        @endphp

                        @if ($canStartBilling)
                            <div class="p-3 bg-emerald-50 border border-emerald-200 rounded-2xl flex items-center gap-2.5 text-xs text-emerald-950">
                                <div class="w-8 h-8 rounded-xl bg-emerald-600 text-white flex items-center justify-center shrink-0">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                    </svg>
                                </div>
                                <div class="leading-tight">
                                    <span class="font-bold block text-emerald-900">Input di Layar {{ $approvedReservation->computer->computer_code }}</span>
                                    <span class="text-[11px] text-emerald-800">Ketikkan 16 digit token pada layar PC lab untuk membuka kunci Windows.</span>
                                </div>
                            </div>
                        @elseif ($isBeforeStart)
                            <div class="p-3 bg-slate-50 border border-slate-200 rounded-2xl flex items-center gap-2.5 text-xs text-slate-700">
                                <div class="w-8 h-8 rounded-xl bg-slate-200 text-slate-600 flex items-center justify-center shrink-0">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <div class="leading-tight">
                                    <span class="font-bold block text-slate-900">Belum Waktunya (Mulai {{ substr($approvedReservation->start_time, 0, 5) }} WIB)</span>
                                    <span class="text-[11px] text-slate-500">Token aktif dan siap diinputkan di layar {{ $approvedReservation->computer->computer_code }} pada jam tersebut.</span>
                                </div>
                            </div>
                        @else
                            <div class="p-3 bg-rose-50 border border-rose-200 rounded-2xl flex items-center gap-2.5 text-xs text-rose-700">
                                <div class="w-8 h-8 rounded-xl bg-rose-100 text-rose-600 flex items-center justify-center shrink-0">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <div class="leading-tight">
                                    <span class="font-bold block text-rose-900">Jadwal Telah Lewat</span>
                                    <span class="text-[11px] text-rose-600">Batas waktu pemakaian reservasi ini telah berakhir.</span>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

        @elseif ($pendingReservation)
            <!-- Notifikasi Reservasi Pending (Menunggu Persetujuan) — SSE Real-time -->
            <div
                x-data="reservationStatusStream({{ $pendingReservation->id }}, '{{ route('mahasiswa.reservasi.stream', $pendingReservation) }}')"
                x-init="startStream()"
                @destroy.window="stopStream()"
                class="rounded-2xl border border-amber-200 bg-amber-50/70 p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4">

                <!-- State: PENDING (default) -->
                <template x-if="currentStatus === 'PENDING_APPROVAL'">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-amber-500 text-white flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div>
                            <h4 class="text-sm font-bold text-amber-950">Pengajuan Reservasi Anda Sedang Ditinjau Kepala Lab</h4>
                            <p class="text-xs text-amber-800 mt-0.5">
                                {{ $pendingReservation->computer->computer_code }} • Jadwal:
                                {{ substr($pendingReservation->start_time, 0, 5) }} -
                                {{ substr($pendingReservation->end_time, 0, 5) }} WIB. Kode akses akan otomatis terbit setelah
                                disetujui.
                            </p>
                        </div>
                    </div>
                </template>

                <!-- State: APPROVED -->
                <template x-if="currentStatus === 'APPROVED'">
                    <div class="flex items-center gap-3 w-full">
                        <div class="w-10 h-10 rounded-xl bg-emerald-500 text-white flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h4 class="text-sm font-bold text-emerald-950">🎉 Reservasi Disetujui!</h4>
                            <p class="text-xs text-emerald-700 mt-0.5">
                                Disetujui oleh <span x-text="approver ?? 'Kepala Lab'"></span>. Halaman akan diperbarui…
                            </p>
                        </div>
                        <div class="text-xs text-emerald-600 animate-pulse font-medium">Memuat ulang…</div>
                    </div>
                </template>

                <!-- State: REJECTED -->
                <template x-if="currentStatus === 'REJECTED'">
                    <div class="flex items-center gap-3 w-full">
                        <div class="w-10 h-10 rounded-xl bg-rose-500 text-white flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h4 class="text-sm font-bold text-rose-950">Pengajuan Ditolak</h4>
                            <p class="text-xs text-rose-700 mt-0.5" x-text="rejectionReason ?? 'Kepala lab menolak pengajuan ini.'"></p>
                        </div>
                        <div class="text-xs text-rose-600 animate-pulse font-medium">Memuat ulang…</div>
                    </div>
                </template>

                <!-- Tombol Batalkan (hanya tampil saat pending) -->
                <template x-if="currentStatus === 'PENDING_APPROVAL'">
                    <div class="flex items-center gap-2">
                        <form method="POST" action="{{ route('mahasiswa.reservasi.cancel', $pendingReservation) }}">
                            @csrf
                            <button type="submit" onclick="return confirm('Yakin ingin membatalkan pengajuan ini?')"
                                class="px-3.5 py-2 rounded-xl border border-amber-300 text-amber-800 hover:bg-amber-100 text-xs font-semibold transition-colors cursor-pointer">
                                Batalkan
                            </button>
                        </form>
                    </div>
                </template>
            </div>

            <!-- Inisialisasi Script SSE Real-Time Status Stream -->
            <script>
                if (typeof window.reservationStatusStream !== 'function') {
                    window.reservationStatusStream = function(reservationId, streamUrl) {
                        return {
                            reservationId: reservationId,
                            streamUrl: streamUrl,
                            eventSource: null,
                            currentStatus: 'PENDING_APPROVAL',
                            approver: null,
                            rejectionReason: null,

                            startStream() {
                                if (!window.EventSource) {
                                    return;
                                }
                                if (this.eventSource) {
                                    this.eventSource.close();
                                }

                                this.eventSource = new EventSource(this.streamUrl);

                                this.eventSource.addEventListener('status_changed', (event) => {
                                    try {
                                        const data = JSON.parse(event.data);
                                        if (data && data.status && data.status !== 'PENDING_APPROVAL') {
                                            this.currentStatus = data.status;
                                            this.approver = data.approver;
                                            this.rejectionReason = data.rejection_reason;
                                            this.stopStream();

                                            // Berikan jeda 1.2s agar user melihat feedback visual status berubah, lalu muat ulang tampilan
                                            setTimeout(() => {
                                                if (window.WARLAB_SPA && typeof window.WARLAB_SPA.navigate === 'function') {
                                                    window.WARLAB_SPA.navigate(window.location.href, false);
                                                } else {
                                                    window.location.reload();
                                                }
                                            }, 1200);
                                        }
                                    } catch (e) {
                                        console.error('SSE Error parsing event data:', e);
                                    }
                                });

                                this.eventSource.addEventListener('timeout', () => {
                                    this.stopStream();
                                    if (this.currentStatus === 'PENDING_APPROVAL') {
                                        setTimeout(() => this.startStream(), 1000);
                                    }
                                });

                                this.eventSource.onerror = () => {
                                    if (this.currentStatus !== 'PENDING_APPROVAL') {
                                        this.stopStream();
                                    }
                                };
                            },

                            stopStream() {
                                if (this.eventSource) {
                                    this.eventSource.close();
                                    this.eventSource = null;
                                }
                            }
                        };
                    };
                }
            </script>
        @endif


        <!-- 3. Quick Action Portals (3-Column Equal Grid sesuai DESIGN.md Component 3) -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

            <!-- Portal 1: Ajukan Reservasi -->
            <a href="{{ route('mahasiswa.reservasi.create') }}"
                class="group bg-white rounded-2xl p-6 border border-[#e2e8f0] elevation-1 hover:elevation-2 hover:border-[#5c67de]/40 transition-all flex flex-col items-center text-center">
                <div
                    class="w-16 h-16 rounded-full bg-[#eef2ff] text-[#424dc4] flex items-center justify-center group-hover:scale-110 group-hover:bg-[#424dc4] group-hover:text-white transition-all shadow-xs">
                    <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                </div>
                <h3 class="mt-4 text-base font-bold text-[#0b1c30] group-hover:text-[#424dc4] transition-colors">
                    Ajukan Reservasi Baru
                </h3>
                <p class="mt-1.5 text-xs text-slate-500 leading-relaxed">
                    Pilih unit workstation (PC-01 s/d PC-04) dan ajukan jadwal peminjaman komputer lab.
                </p>
                <span
                    class="mt-4 px-4 py-2 rounded-xl bg-[#eff4ff] text-[#424dc4] group-hover:bg-[#424dc4] group-hover:text-white border border-[#dce9ff] text-xs font-bold inline-flex items-center gap-1.5 transition-all shadow-xs">
                    <span>Buka Formulir Reservasi</span>
                    <span class="group-hover:translate-x-0.5 transition-transform">&rarr;</span>
                </span>
            </a>

            <!-- Portal 2: Kode Akses Saya (Dinamis: Muncul jika disetujui, jika belum muncul info jelas) -->
            <div
                class="bg-white rounded-2xl p-6 border border-[#e2e8f0] elevation-1 flex flex-col items-center text-center">
                <div
                    class="w-16 h-16 rounded-full {{ $approvedReservation ? 'bg-emerald-100 text-emerald-600' : 'bg-[#eef2ff] text-[#424dc4]' }} flex items-center justify-center shadow-xs">
                    <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                    </svg>
                </div>
                <h3 class="mt-4 text-base font-bold text-[#0b1c30]">
                    Kode Akses Check-in
                </h3>

                @if ($approvedReservation && $approvedReservation->accessCode)
                    <p class="mt-1.5 text-xs text-emerald-700 font-semibold">
                        Token 16 Digit untuk {{ $approvedReservation->computer->computer_code }}:
                    </p>
                    <div
                        class="mt-2 font-mono font-extrabold text-xs sm:text-sm text-[#424dc4] bg-[#eff4ff] px-2.5 py-1.5 rounded-lg border border-[#dce9ff] select-all">
                        {{ $approvedReservation->accessCode->code }}
                    </div>
                    <span class="mt-2 text-[10px] text-slate-500 block leading-tight">
                        Ketikkan langsung pada layar monitor PC {{ $approvedReservation->computer->computer_code }} di lab
                    </span>
                @elseif ($activeSession)
                    <p class="mt-1.5 text-xs text-[#5c67de] font-semibold">
                        Sedang Digunakan di {{ $activeSession->computer->computer_code }}
                    </p>
                    <span class="mt-3 text-[11px] font-bold text-emerald-600 bg-emerald-50 px-2.5 py-1 rounded-md">
                        ● Sesi Billing Berjalan
                    </span>
                @else
                    <p class="mt-1.5 text-xs text-slate-500 leading-relaxed">
                        Kode akses check-in belum diterbitkan. Kode akan otomatis muncul setelah pengajuan reservasi Anda
                        disetujui.
                    </p>
                    <span class="mt-4 text-[11px] font-semibold text-slate-400 bg-slate-100 px-2.5 py-1 rounded-md">
                        Belum Ada Kode Aktif
                    </span>
                @endif
            </div>

            <!-- Portal 3: Buku Booking (Sesuai Permintaan) -->
            <a href="{{ route('mahasiswa.buku-booking') }}"
                class="group bg-white rounded-2xl p-6 border border-[#e2e8f0] elevation-1 hover:elevation-2 hover:border-[#5c67de]/40 transition-all flex flex-col items-center text-center">
                <div
                    class="w-16 h-16 rounded-full bg-[#eef2ff] text-[#424dc4] flex items-center justify-center group-hover:scale-110 group-hover:bg-[#424dc4] group-hover:text-white transition-all shadow-xs">
                    <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                </div>
                <h3 class="mt-4 text-base font-bold text-[#0b1c30] group-hover:text-[#424dc4] transition-colors">
                    Buku Booking
                </h3>
                <p class="mt-1.5 text-xs text-slate-500 leading-relaxed">
                    Lihat jadwal pemakaian PC lab dari jam berapa saja yang sudah direservasi.
                </p>
                <span
                    class="mt-4 px-4 py-2 rounded-xl bg-[#eff4ff] text-[#424dc4] group-hover:bg-[#424dc4] group-hover:text-white border border-[#dce9ff] text-xs font-bold inline-flex items-center gap-1.5 transition-all shadow-xs">
                    <span>Buka Buku Booking</span>
                    <span class="group-hover:translate-x-0.5 transition-transform">&rarr;</span>
                </span>
            </a>

        </div>

        <!-- 4. Status 4 Unit Komputer Laboratorium (Dinamis dari Database) -->
        <div id="status-komputer" class="bg-white rounded-2xl border border-[#e2e8f0] p-6 elevation-1 space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-100 pb-4">
                <div>
                    <h3 class="text-base font-bold text-[#0b1c30]">Status Unit Komputer Laboratorium</h3>
                    <p class="text-xs text-slate-500">Ketersediaan 4 PC spesifikasi Machine Learning & Sains Data hari ini.
                    </p>
                </div>
                <div class="flex items-center gap-3 text-xs">
                    <span class="inline-flex items-center gap-1.5 text-slate-600"><span
                            class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span> Kosong</span>
                    <span class="inline-flex items-center gap-1.5 text-slate-600"><span
                            class="w-2.5 h-2.5 rounded-full bg-[#424dc4]"></span> Terisi</span>
                    <span class="inline-flex items-center gap-1.5 text-slate-600"><span
                            class="w-2.5 h-2.5 rounded-full bg-amber-500"></span> Padat</span>
                    <span class="inline-flex items-center gap-1.5 text-slate-600"><span
                            class="w-2.5 h-2.5 rounded-full bg-rose-500"></span> Penuh</span>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                @foreach ($computers as $comp)
                    @if ($comp->real_status === 'PENUH')
                        <!-- Komputer Berstatus Penuh -->
                        <div class="rounded-xl border border-rose-200 p-4 bg-rose-50/60 flex flex-col justify-between shadow-xs">
                            <div class="flex items-start justify-between">
                                <div>
                                    <span
                                        class="text-xs font-bold font-mono text-rose-700 bg-rose-100 px-2 py-0.5 rounded-md">{{ $comp->computer_code }}</span>
                                    <h4 class="text-sm font-bold text-[#0b1c30] mt-1">{{ $comp->computer_name }}</h4>
                                </div>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800 border border-rose-200">
                                    PENUH
                                </span>
                            </div>
                            <p class="text-[11px] text-slate-500 mt-2">{{ $comp->specs ?? 'Workstation Laboratorium' }}</p>
                            <div class="mt-4 pt-3 border-t border-rose-200/60 flex items-center justify-between text-xs">
                                <span class="text-slate-500">Status Unit:</span>
                                <span class="font-bold text-rose-700">{{ $comp->status_desc }}</span>
                            </div>
                        </div>
                    @elseif ($comp->real_status === 'PADAT')
                        <!-- Komputer Berstatus Padat -->
                        <div class="rounded-xl border border-amber-200 p-4 bg-amber-50/50 flex flex-col justify-between shadow-xs">
                            <div class="flex items-start justify-between">
                                <div>
                                    <span
                                        class="text-xs font-bold font-mono text-amber-800 bg-amber-100 px-2 py-0.5 rounded-md">{{ $comp->computer_code }}</span>
                                    <h4 class="text-sm font-bold text-[#0b1c30] mt-1">{{ $comp->computer_name }}</h4>
                                </div>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-200">
                                    {{ $comp->status_badge }}
                                </span>
                            </div>
                            <p class="text-[11px] text-slate-500 mt-2">{{ $comp->specs ?? 'Workstation Laboratorium' }}</p>
                            <div class="mt-4 pt-3 border-t border-amber-200/60 flex items-center justify-between text-xs">
                                <span class="text-slate-500">Status Unit:</span>
                                <span class="font-bold text-amber-800">{{ $comp->status_desc }}</span>
                            </div>
                        </div>
                    @elseif ($comp->real_status === 'TERISI')
                        <!-- Komputer Berstatus Terisi -->
                        <div class="rounded-xl border {{ $comp->is_own_session ? 'border-2 border-[#5c67de] bg-[#eff4ff]' : 'border border-indigo-200 bg-indigo-50/40' }} p-4 flex flex-col justify-between shadow-xs">
                            <div class="flex items-start justify-between">
                                <div>
                                    <span
                                        class="text-xs font-bold font-mono text-[#424dc4] bg-[#eef2ff] px-2 py-0.5 rounded-md">{{ $comp->computer_code }}</span>
                                    <h4 class="text-sm font-bold text-[#0b1c30] mt-1">{{ $comp->computer_name }}</h4>
                                </div>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-indigo-100 text-[#424dc4] border border-[#dce9ff]">
                                    {{ $comp->status_badge }}
                                </span>
                            </div>
                            <p class="text-[11px] text-slate-600 mt-2">{{ $comp->specs ?? 'Workstation Laboratorium' }}</p>
                            <div class="mt-4 pt-3 border-t border-indigo-200/60 flex items-center justify-between text-xs">
                                <span class="text-slate-500">Status Unit:</span>
                                <span class="font-bold text-[#424dc4]">{{ $comp->status_desc }}</span>
                            </div>
                        </div>
                    @else
                        <!-- Komputer Berstatus Kosong -->
                        <div
                            class="rounded-xl border border-[#e2e8f0] p-4 bg-[#f8f9ff] flex flex-col justify-between hover:border-emerald-300 transition-all shadow-xs">
                            <div class="flex items-start justify-between">
                                <div>
                                    <span
                                        class="text-xs font-bold font-mono text-emerald-800 bg-emerald-100 px-2 py-0.5 rounded-md">{{ $comp->computer_code }}</span>
                                    <h4 class="text-sm font-bold text-[#0b1c30] mt-1">{{ $comp->computer_name }}</h4>
                                </div>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                    KOSONG
                                </span>
                            </div>
                            <p class="text-[11px] text-slate-500 mt-2">{{ $comp->specs ?? 'Workstation Laboratorium' }}</p>
                            <div class="mt-4 pt-3 border-t border-slate-200/60 flex items-center justify-between text-xs">
                                <span class="text-slate-500">Status Unit:</span>
                                <span class="font-bold text-emerald-700">{{ $comp->status_desc }}</span>
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>


        <!-- 5. Tabel Reservasi & Riwayat Peminjaman (Sesuai DESIGN.md Component 7) -->
        <div class="bg-white rounded-2xl border border-[#e2e8f0] elevation-1 overflow-hidden">
            <div class="p-6 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h3 class="text-base font-bold text-[#0b1c30]">Pengajuan & Riwayat Reservasi Anda</h3>
                    <p class="text-xs text-slate-500">Pantau status persetujuan Kepala Lab dan kode akses login.</p>
                </div>
                <a href="{{ route('mahasiswa.reservasi.create') }}"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-[#424dc4] hover:bg-[#363ea3] text-white text-xs font-bold transition-all shadow-xs">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                    Reservasi Baru
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse min-w-[720px]">
                    <thead>
                        <tr
                            class="bg-[#f8f9ff] border-b border-[#e2e8f0] text-[11px] font-bold uppercase tracking-wider text-slate-500">
                            <th class="py-3.5 px-6">ID & Komputer</th>
                            <th class="py-3.5 px-6">Jadwal Waktu (WIB)</th>
                            <th class="py-3.5 px-6">Keperluan Lab</th>
                            <th class="py-3.5 px-6">Status Reservasi</th>
                            <th class="py-3.5 px-6">Kode Akses</th>
                            <th class="py-3.5 px-6 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#f1f5f9] text-xs">
                        @forelse ($recentReservations as $res)
                            <tr class="hover:bg-slate-50/80 transition-colors">
                                <td class="py-4 px-6 font-semibold text-[#0b1c30]">
                                    <span
                                        class="block font-mono text-[11px] text-slate-400">#RES-{{ $res->created_at->format('Ymd') }}-{{ sprintf('%02d', $res->id) }}</span>
                                    <span class="text-sm font-bold text-[#424dc4]">
                                        {{ $res->computer->computer_code ?? 'PC-??' }}
                                        <span
                                            class="text-xs text-slate-600 font-normal">({{ $res->computer->computer_name ?? 'Workstation' }})</span>
                                    </span>
                                </td>
                                <td class="py-4 px-6 text-slate-600">
                                    <span class="font-medium text-slate-900 block">
                                        {{ \Carbon\Carbon::parse($res->reservation_date)->isoFormat('dddd, D MMM Y') }}
                                    </span>
                                    <span class="text-[11px] text-slate-500 font-mono">
                                        {{ substr($res->start_time, 0, 5) }} - {{ substr($res->end_time, 0, 5) }} WIB
                                    </span>
                                </td>
                                <td class="py-4 px-6 text-slate-600 max-w-[200px] truncate" title="{{ $res->purpose }}">
                                    {{ $res->purpose }}
                                </td>
                                <td class="py-4 px-6">
                                    @if ($res->status === 'PENDING_APPROVAL')
                                        <div class="space-y-0.5">
                                            <span
                                                class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                                Menunggu Approval
                                            </span>
                                            <span class="block text-[10px] text-slate-400">
                                                Verifikasi Kepala Lab
                                            </span>
                                        </div>
                                    @elseif ($res->status === 'APPROVED')
                                        <div class="space-y-0.5">
                                            <span
                                                class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                                Disetujui
                                            </span>
                                            <span class="block text-[10px] text-slate-500">
                                                Oleh: <span class="font-semibold text-slate-700">{{ $res->approver->name ?? 'Kepala Lab' }}</span>
                                            </span>
                                        </div>
                                    @elseif ($res->status === 'CHECKED_IN')
                                        <span
                                            class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-indigo-100 text-[#424dc4] border border-[#dce9ff]">
                                            <span class="w-1.5 h-1.5 rounded-full bg-[#424dc4] animate-pulse"></span>
                                            Sedang Digunakan
                                        </span>
                                    @elseif ($res->status === 'COMPLETED')
                                        <span
                                            class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-slate-100 text-slate-700 border border-slate-200">
                                            Selesai
                                        </span>
                                    @elseif ($res->status === 'CANCELLED')
                                        <span
                                            class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-slate-100 text-slate-500 border border-slate-200">
                                            Dibatalkan
                                        </span>
                                    @elseif ($res->status === 'REJECTED')
                                        <div class="space-y-1">
                                            <span
                                                class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                                Ditolak
                                            </span>
                                            <span class="block text-[10px] text-slate-500">
                                                Oleh: <span class="font-semibold text-slate-700">{{ $res->approver->name ?? 'Kepala Lab' }}</span>
                                            </span>
                                            @if($res->rejection_reason)
                                                <div class="text-[10px] text-rose-600 bg-rose-50/80 p-1.5 rounded-lg border border-rose-100 max-w-[200px] leading-tight">
                                                    <span class="font-bold">Alasan:</span> "{{ $res->rejection_reason }}"
                                                </div>
                                            @endif
                                        </div>
                                    @else
                                        <span
                                            class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-slate-100 text-slate-700 border border-slate-200">
                                            {{ $res->status }}
                                        </span>
                                    @endif
                                </td>
                                <td class="py-4 px-6">
                                    @if ($res->accessCode && in_array($res->status, ['APPROVED', 'CHECKED_IN']))
                                        <span
                                            class="font-mono font-bold text-[#424dc4] bg-[#eff4ff] px-2.5 py-1 rounded-md border border-[#dce9ff] select-all">
                                            {{ $res->accessCode->code }}
                                        </span>
                                    @elseif ($res->status === 'PENDING_APPROVAL')
                                        <span class="text-slate-400 italic text-[11px]">Dibuat setelah disetujui</span>
                                    @else
                                        <span class="text-slate-400 text-[11px]">-</span>
                                    @endif
                                </td>
                                <td class="py-4 px-6 text-right space-x-2">
                                    @if ($res->status === 'CHECKED_IN')
                                        <button @click="openEndSessionModal()"
                                            class="text-xs font-bold text-rose-600 hover:text-rose-800 bg-rose-50 hover:bg-rose-100 px-3 py-1.5 rounded-lg transition-colors cursor-pointer">
                                            Akhiri Sesi
                                        </button>
                                    @elseif ($res->status === 'APPROVED')
                                        @php
                                            $nowJkt = \Carbon\Carbon::now('Asia/Jakarta');
                                            $rowResDate = $res->reservation_date->format('Y-m-d');
                                            $rowStartAt = \Carbon\Carbon::createFromFormat('Y-m-d H:i', $rowResDate . ' ' . substr($res->start_time, 0, 5), 'Asia/Jakarta');
                                            $rowEndAt = \Carbon\Carbon::createFromFormat('Y-m-d H:i', $rowResDate . ' ' . substr($res->end_time, 0, 5), 'Asia/Jakarta');
                                            $canCheckInNow = $nowJkt->gte($rowStartAt) && $nowJkt->lt($rowEndAt);
                                            $isBeforeStart = $nowJkt->lt($rowStartAt);
                                        @endphp

                                        <div class="flex flex-col items-start gap-1">
                                            <span class="font-mono font-bold text-xs text-[#424dc4] bg-[#eff4ff] border border-[#dce9ff] px-2.5 py-1 rounded-lg select-all" title="Ketikkan token ini di layar {{ $res->computer->computer_code }}">
                                                {{ $res->accessCode->code ?? '-' }}
                                            </span>
                                            <span class="text-[10px] text-slate-500 font-medium">
                                                Input di {{ $res->computer->computer_code }}
                                            </span>
                                        </div>
                                    @elseif ($res->status === 'PENDING_APPROVAL')
                                        <form method="POST" action="{{ route('mahasiswa.reservasi.cancel', $res) }}"
                                            class="inline-block">
                                            @csrf
                                            <button type="submit" onclick="return confirm('Batalkan pengajuan reservasi ini?')"
                                                class="text-xs font-semibold text-rose-700 bg-rose-50 hover:bg-rose-100 border border-rose-200 px-3 py-1.5 rounded-lg transition-all shadow-2xs cursor-pointer">
                                                Batal
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-xs text-slate-400">-</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-12 px-6 text-center">
                                    <div class="max-w-xs mx-auto space-y-3">
                                        <div
                                            class="w-12 h-12 rounded-2xl bg-[#eff4ff] text-[#424dc4] flex items-center justify-center mx-auto">
                                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                                stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                        </div>
                                        <p class="text-sm font-bold text-[#0b1c30]">Belum Ada Riwayat Reservasi</p>
                                        <p class="text-xs text-slate-500">Anda belum pernah mengajukan peminjaman komputer.
                                            Billing dan kode akses akan muncul setelah Anda mengajukan dan disetujui.</p>
                                        <a href="{{ route('mahasiswa.reservasi.create') }}"
                                            class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-[#424dc4] hover:bg-[#363ea3] text-white text-xs font-bold transition-all shadow-xs">
                                            + Ajukan Reservasi Sekarang
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>


        <!-- 6. Modal Konfirmasi Akhiri Sesi Billing (Warnet Model) -->
        @if ($activeSession)
            <div x-show="billingModalOpen" class="fixed inset-0 z-50 overflow-y-auto"
                x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" style="display: none;">

                <div class="min-h-screen px-4 text-center flex items-center justify-center">
                    <!-- Overlay -->
                    <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity"
                        @click="billingModalOpen = false"></div>

                    <!-- Modal Content (Elevation-3 sesuai DESIGN.md) -->
                    <div
                        class="inline-block w-full max-w-md p-6 my-8 text-left align-middle bg-white rounded-3xl shadow-2xl border border-[#e2e8f0] transform transition-all relative z-10 elevation-3">
                        <div class="flex items-center gap-4">
                            <div
                                class="w-12 h-12 rounded-2xl bg-rose-100 text-rose-600 flex items-center justify-center shrink-0">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                            </div>
                            <div>
                                <h4 class="text-lg font-bold text-[#0b1c30]">Akhiri Sesi Komputer Sekarang?</h4>
                                <p class="text-xs text-slate-500">Model Billing Lab Data Sains</p>
                            </div>
                        </div>

                        <div class="mt-4 p-4 rounded-xl bg-slate-50 border border-slate-200 text-xs space-y-2">
                            <div class="flex justify-between">
                                <span class="text-slate-500">Komputer:</span>
                                <span class="font-bold text-[#0b1c30]">{{ $activeSession->computer->computer_code }}
                                    ({{ $activeSession->computer->computer_name }})</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-slate-500">Durasi Berjalan:</span>
                                <span class="font-mono font-bold text-[#424dc4]" x-text="elapsedTime">00:00:00</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-slate-500">Status Kuota:</span>
                                <span class="font-semibold text-slate-700">1 Sesi tetap terhitung (BR-018)</span>
                            </div>
                        </div>

                        <p class="mt-3 text-xs text-slate-600 leading-relaxed">
                            Setelah sesi diakhiri, <strong>Windows Desktop Agent di
                                {{ $activeSession->computer->computer_code }} akan otomatis terkunci kembali</strong>. Pastikan
                            Anda telah menyimpan seluruh pekerjaan Anda di komputer laboratorium.
                        </p>

                        <div class="mt-6 flex items-center justify-end gap-3">
                            <button type="button" @click="billingModalOpen = false"
                                class="px-4 py-2.5 rounded-xl border border-[#e2e8f0] text-xs font-bold text-slate-700 hover:bg-slate-50 transition-colors cursor-pointer">
                                Batal
                            </button>

                            <form method="POST" action="{{ route('mahasiswa.session.end', $activeSession) }}"
                                class="inline-block">
                                @csrf
                                <button type="submit"
                                    class="px-5 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-xs font-bold text-white shadow-md shadow-rose-600/20 transition-all cursor-pointer">
                                    Ya, Kunci & Selesaikan Sesi
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @endif

    </div>
@endsection

@push('scripts')
    <script>
        function mahasiswaDashboard(config) {
            return {
                billingModalOpen: false,
                hasActiveSession: config.hasActiveSession,
                elapsedSeconds: config.elapsedSeconds || 0,
                remainingSeconds: config.remainingSeconds || 0,
                elapsedTime: '00:00:00',
                remainingTime: '00:00:00',
                timerInterval: null,

                init() {
                    if (!this.hasActiveSession) return;

                    this.updateFormattedTimes();
                    this.timerInterval = setInterval(() => {
                        this.elapsedSeconds++;
                        if (this.remainingSeconds > 0) {
                            this.remainingSeconds--;
                            if (this.remainingSeconds <= 0) {
                                // Waktu habis!
                                window.location.reload();
                            }
                        }
                        this.updateFormattedTimes();
                    }, 1000);
                },

                updateFormattedTimes() {
                    this.elapsedTime = this.formatTime(this.elapsedSeconds);
                    this.remainingTime = this.formatTime(this.remainingSeconds);
                },

                formatTime(seconds) {
                    const h = String(Math.floor(seconds / 3600)).padStart(2, '0');
                    const m = String(Math.floor((seconds % 3600) / 60)).padStart(2, '0');
                    const s = String(seconds % 60).padStart(2, '0');
                    return `${h}:${m}:${s}`;
                },

                openEndSessionModal() {
                    this.billingModalOpen = true;
                }
            };
        }

        if (typeof window.reservationStatusStream !== 'function') {
            window.reservationStatusStream = function(reservationId, streamUrl) {
                return {
                    reservationId: reservationId,
                    streamUrl: streamUrl,
                    eventSource: null,
                    currentStatus: 'PENDING_APPROVAL',
                    approver: null,
                    rejectionReason: null,

                    startStream() {
                        if (!window.EventSource) return;
                        if (this.eventSource) this.eventSource.close();

                        this.eventSource = new EventSource(this.streamUrl);

                        this.eventSource.addEventListener('status_changed', (event) => {
                            try {
                                const data = JSON.parse(event.data);
                                if (data && data.status && data.status !== 'PENDING_APPROVAL') {
                                    this.currentStatus = data.status;
                                    this.approver = data.approver;
                                    this.rejectionReason = data.rejection_reason;
                                    this.stopStream();

                                    setTimeout(() => {
                                        if (window.WARLAB_SPA && typeof window.WARLAB_SPA.navigate === 'function') {
                                            window.WARLAB_SPA.navigate(window.location.href, false);
                                        } else {
                                            window.location.reload();
                                        }
                                    }, 1200);
                                }
                            } catch (e) {
                                console.error('SSE Error parsing event data:', e);
                            }
                        });

                        this.eventSource.addEventListener('timeout', () => {
                            this.stopStream();
                            if (this.currentStatus === 'PENDING_APPROVAL') {
                                setTimeout(() => this.startStream(), 1000);
                            }
                        });

                        this.eventSource.onerror = () => {
                            if (this.currentStatus !== 'PENDING_APPROVAL') {
                                this.stopStream();
                            }
                        };
                    },

                    stopStream() {
                        if (this.eventSource) {
                            this.eventSource.close();
                            this.eventSource = null;
                        }
                    }
                };
            };
        }
    </script>
@endpush