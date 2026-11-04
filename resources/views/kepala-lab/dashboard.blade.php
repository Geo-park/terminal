@extends('layouts.kepala-lab')

@section('title', 'Pusat Kendali Laboratorium')
@section('page-title', 'Pusat Kendali Lab')

@section('content')
    <div class="space-y-6 sm:space-y-8">

        <!-- 1. Hero Welcoming Banner -->
        @php
            $nowJkt = \Carbon\Carbon::now('Asia/Jakarta');
            $isLabOpen = ($nowJkt->hour >= 7 && $nowJkt->hour < 16) && !$nowJkt->isWeekend();
        @endphp
        <div class="relative overflow-hidden rounded-2xl sm:rounded-3xl bg-gradient-to-r from-[#0b1329] via-[#1a1f4d] to-[#2c2b7c] p-5 sm:p-7 lg:p-8 text-white shadow-lg border border-white/10">
            <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-5 sm:gap-6">
                <div class="space-y-2">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 text-indigo-200 text-xs font-semibold backdrop-blur-md border border-white/15">
                        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                        Panel Otoritas Kepala Laboratorium
                    </div>
                    <h2 class="text-xl sm:text-2xl lg:text-3xl font-black tracking-tight text-white">
                        Selamat Datang, {{ auth()->user()->role === 'kepala_lab' ? 'Kepala Lab' : auth()->user()->name }}
                    </h2>
                    <p class="text-xs sm:text-sm text-slate-300 max-w-2xl leading-relaxed">
                        Pantau status 4 workstation komputer, setujui permohonan reservasi riset mahasiswa, dan kelola operasional billing lab secara terpusat.
                    </p>
                </div>

                <!-- Jam Operasional Info Card -->
                <div class="bg-white/10 backdrop-blur-md rounded-2xl p-4 border border-white/15 flex flex-col justify-between shrink-0 w-full sm:w-auto sm:min-w-[210px] shadow-sm">
                    <div class="flex items-center justify-between gap-2">
                        <span class="text-[10px] sm:text-[11px] font-bold uppercase tracking-wider text-indigo-200">Jam Operasional</span>
                        @if($isLabOpen)
                            <span class="inline-flex items-center gap-1 text-[10px] font-bold text-emerald-300 bg-emerald-500/20 px-2 py-0.5 rounded-full border border-emerald-400/30">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span> Buka
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 text-[10px] font-bold text-amber-300 bg-amber-500/20 px-2 py-0.5 rounded-full border border-amber-400/30">
                                <span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span> Tutup
                            </span>
                        @endif
                    </div>
                    <div class="mt-2 flex items-baseline gap-1.5">
                        <span class="text-2xl font-black font-mono tracking-tight text-white">07.00 – 16.00</span>
                        <span class="text-xs text-indigo-200 font-bold">WIB</span>
                    </div>
                    <span class="text-[11px] text-slate-300 mt-1">Senin – Jumat (Hari Kerja)</span>
                </div>
            </div>

            <!-- Ambient Glow Effect -->
            <div class="absolute -right-12 -bottom-12 w-72 h-72 rounded-full bg-indigo-500/20 blur-3xl pointer-events-none"></div>
            <div class="absolute top-0 right-1/3 w-48 h-48 rounded-full bg-blue-500/10 blur-2xl pointer-events-none"></div>
        </div>

        <!-- 2. Ringkasan Metrik Cepat (4-Grid Stat Cards) -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3.5 sm:gap-5">
            <!-- Stat 1: Pending Approval -->
            <div class="bg-white rounded-2xl p-4 sm:p-5 border {{ $pendingCount > 0 ? 'border-amber-300 bg-amber-50/20 shadow-amber-500/5' : 'border-[#e2e8f0]' }} shadow-xs flex items-center justify-between transition-all hover:shadow-sm">
                <div class="min-w-0 pr-2">
                    <span class="text-[10px] sm:text-xs font-bold uppercase tracking-wider text-slate-500 block truncate">Menunggu Approval</span>
                    <p class="text-2xl sm:text-3xl font-black text-[#0b1c30] mt-1">{{ $pendingCount }}</p>
                    <span class="text-[11px] block truncate mt-1 {{ $pendingCount > 0 ? 'text-amber-700 font-bold' : 'text-slate-400' }}">
                        {{ $pendingCount > 0 ? '● Butuh tindakan' : 'Antrean bersih' }}
                    </span>
                </div>
                <div class="w-11 h-11 sm:w-12 sm:h-12 rounded-2xl {{ $pendingCount > 0 ? 'bg-amber-100 text-amber-700' : 'bg-slate-100 text-slate-500' }} flex items-center justify-center shrink-0 shadow-2xs">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>

            <!-- Stat 2: Active Billing Sessions (Dipakai) -->
            <div class="bg-white rounded-2xl p-4 sm:p-5 border {{ ($dipakaiCount ?? 0) > 0 ? 'border-blue-300 bg-blue-50/20 shadow-blue-500/5' : 'border-[#e2e8f0]' }} shadow-xs flex items-center justify-between transition-all hover:shadow-sm">
                <div class="min-w-0 pr-2">
                    <span class="text-[10px] sm:text-xs font-bold uppercase tracking-wider text-slate-500 block truncate">PC Dipakai</span>
                    <p class="text-2xl sm:text-3xl font-black text-blue-700 mt-1">{{ $dipakaiCount ?? $activeSessionsCount }} <span class="text-sm sm:text-base font-bold text-slate-400 font-mono">/ 4</span></p>
                    <span class="text-[11px] text-blue-700 font-semibold block truncate mt-1">
                        {{ ($dipakaiCount ?? $activeSessionsCount) > 0 ? '● Sesi aktif berjalan' : 'Tidak ada sesi aktif' }}
                    </span>
                </div>
                <div class="w-11 h-11 sm:w-12 sm:h-12 rounded-2xl {{ ($dipakaiCount ?? 0) > 0 ? 'bg-blue-100 text-blue-700' : 'bg-slate-100 text-slate-500' }} flex items-center justify-center shrink-0 shadow-2xs">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                </div>
            </div>

            <!-- Stat 3: Unit PC Siap (Hidup) -->
            <div class="bg-white rounded-2xl p-4 sm:p-5 border border-[#e2e8f0] shadow-xs flex items-center justify-between transition-all hover:shadow-sm">
                <div class="min-w-0 pr-2">
                    <span class="text-[10px] sm:text-xs font-bold uppercase tracking-wider text-slate-500 block truncate">Unit PC Hidup</span>
                    <p class="text-2xl sm:text-3xl font-black text-emerald-600 mt-1">{{ $hidupCount ?? $availableComputersCount }} <span class="text-sm sm:text-base font-bold text-slate-400 font-mono">/ 4</span></p>
                    <span class="text-[11px] text-emerald-700 font-semibold block truncate mt-1">
                        {{ ($matiCount ?? 0) > 0 ? '(' . $matiCount . ' Unit Mati)' : 'Semua Siap Digunakan' }}
                    </span>
                </div>
                <div class="w-11 h-11 sm:w-12 sm:h-12 rounded-2xl bg-emerald-100 text-emerald-700 flex items-center justify-center shrink-0 shadow-2xs">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
            </div>

            <!-- Stat 4: Total Reservasi Hari Ini -->
            <div class="bg-white rounded-2xl p-4 sm:p-5 border border-[#e2e8f0] shadow-xs flex items-center justify-between transition-all hover:shadow-sm">
                <div class="min-w-0 pr-2">
                    <span class="text-[10px] sm:text-xs font-bold uppercase tracking-wider text-slate-500 block truncate">Reservasi Hari Ini</span>
                    <p class="text-2xl sm:text-3xl font-black text-[#0b1c30] mt-1">{{ $todayReservationsCount }}</p>
                    <span class="text-[11px] text-slate-500 block truncate mt-1">Pemesanan terdaftar</span>
                </div>
                <div class="w-11 h-11 sm:w-12 sm:h-12 rounded-2xl bg-slate-100 text-slate-600 flex items-center justify-center shrink-0 shadow-2xs">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
            </div>
        </div>

        <!-- 3. Antrean Persetujuan Reservasi (Approval Queue) -->
        <div id="antrean-approval" class="bg-white rounded-2xl sm:rounded-3xl border border-[#e2e8f0] p-4 sm:p-6 shadow-xs space-y-4 sm:space-y-5">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 pb-4">
                <div>
                    <div class="flex items-center gap-2.5">
                        <h3 class="text-base sm:text-lg font-bold text-[#0b1c30]">Antrean Persetujuan Peminjaman Komputer</h3>
                        @if($pendingCount > 0)
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-800 border border-amber-200">
                                {{ $pendingCount }} Menunggu
                            </span>
                        @endif
                    </div>
                    <p class="text-xs text-slate-500 mt-0.5">Tinjau dan setujui permohonan mahasiswa. Kode token akses PC diterbitkan otomatis saat disetujui.</p>
                </div>
                <a href="{{ route('kepala-lab.antrean') }}"
                    class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-[#eff4ff] hover:bg-[#424dc4] text-[#424dc4] hover:text-white border border-[#dce9ff] text-xs font-bold transition-all shadow-xs group w-fit">
                    <span>Buka Halaman Antrean Penuh</span>
                    <span class="group-hover:translate-x-0.5 transition-transform">&rarr;</span>
                </a>
            </div>

            @if($pendingReservations->isEmpty())
                <div class="text-center py-10 px-4">
                    <div class="w-14 h-14 rounded-2xl bg-emerald-50 text-emerald-600 border border-emerald-100 mx-auto flex items-center justify-center mb-3 shadow-2xs">
                        <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                    <h4 class="text-sm font-bold text-slate-800">Tidak Ada Antrean Persetujuan</h4>
                    <p class="text-xs text-slate-500 mt-1 max-w-sm mx-auto">Semua permohonan reservasi komputer dari mahasiswa telah diproses.</p>
                </div>
            @else
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                    @foreach($pendingReservations as $res)
                        <div class="p-4 sm:p-5 rounded-2xl border border-amber-200/90 bg-gradient-to-br from-amber-50/30 via-white to-white shadow-xs space-y-4">
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex items-center gap-3 min-w-0">
                                    <div class="w-10 h-10 rounded-xl bg-[#eff4ff] text-[#424dc4] font-black text-xs flex items-center justify-center shrink-0 border border-[#dce9ff]">
                                        {{ substr($res->user->name ?? 'M', 0, 2) }}
                                    </div>
                                    <div class="min-w-0">
                                        <h4 class="text-sm font-bold text-slate-900 truncate">{{ ($res->user && $res->user->role === 'kepala_lab') ? 'Kepala Laboratorium' : $res->user->name }}</h4>
                                        <p class="text-xs text-slate-500 font-mono truncate">{{ ($res->user && $res->user->role === 'kepala_lab') ? 'Otoritas Laboratorium' : 'NIM: ' . $res->user->nim . ' • ' . $res->user->jurusan }}</p>
                                    </div>
                                </div>
                                <span class="px-2.5 py-1 rounded-lg text-xs font-black bg-[#424dc4] text-white font-mono shrink-0 shadow-2xs">
                                    {{ $res->computer->computer_code }}
                                </span>
                            </div>

                            <!-- Detail Jadwal & Keperluan -->
                            <div class="grid grid-cols-2 gap-2.5 p-3 rounded-xl bg-slate-50 border border-slate-100 text-xs">
                                <div>
                                    <span class="text-slate-400 block text-[10px] uppercase font-bold tracking-wider">Tanggal</span>
                                    <span class="font-semibold text-slate-800">{{ \Carbon\Carbon::parse($res->reservation_date)->isoFormat('dddd, D MMM Y') }}</span>
                                </div>
                                <div>
                                    <span class="text-slate-400 block text-[10px] uppercase font-bold tracking-wider">Waktu</span>
                                    <span class="font-bold text-[#424dc4] font-mono">{{ substr($res->start_time, 0, 5) }} - {{ substr($res->end_time, 0, 5) }} WIB</span>
                                </div>
                                <div class="col-span-2 pt-1 border-t border-slate-200/70">
                                    <span class="text-slate-400 block text-[10px] uppercase font-bold tracking-wider">Keperluan Riset</span>
                                    <span class="font-medium text-slate-800">{{ $res->purpose }}</span>
                                </div>
                                @if($res->notes)
                                    <div class="col-span-2 pt-1">
                                        <span class="text-slate-400 block text-[10px] uppercase font-bold tracking-wider">Software / Tools</span>
                                        <span class="text-slate-600 italic">{{ $res->notes }}</span>
                                    </div>
                                @endif
                            </div>

                            <!-- Action Buttons -->
                            <div class="flex items-center justify-end gap-2 pt-1">
                                <!-- Tombol Tolak -->
                                <button type="button"
                                    @click="rejectReservationId = {{ $res->id }}; rejectStudentName = '{{ addslashes($res->user->name) }}'; rejectModalOpen = true;"
                                    class="px-3.5 py-2 rounded-xl text-xs font-semibold text-rose-700 bg-rose-50 hover:bg-rose-100 border border-rose-200 transition-colors cursor-pointer">
                                    Tolak
                                </button>

                                <!-- Tombol Setujui -->
                                <form method="POST" action="{{ route('kepala-lab.reservasi.approve', $res) }}">
                                    @csrf
                                    <button type="submit"
                                        class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 shadow-xs transition-all cursor-pointer">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                        </svg>
                                        <span>Setujui Permohonan</span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- 4. Monitoring 4 Unit Komputer Lab (Status: Hidup, Dipakai, Mati) -->
        <div id="workstation-grid" class="space-y-4">
            <!-- Header Section & Legend Status -->
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-3">
                <div>
                    <div class="flex items-center gap-2">
                        <h3 class="text-base sm:text-lg font-bold text-[#0b1c30]">Monitoring 4 Unit Komputer Lab</h3>
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black bg-indigo-50 text-[#424dc4] border border-indigo-100 uppercase tracking-wide">Live Status</span>
                    </div>
                    <p class="text-xs text-slate-500 mt-0.5">Pantau kondisi langsung seluruh workstation. Klik tombol untuk kendali unit.</p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <div class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-emerald-50 text-emerald-800 border border-emerald-200 text-xs font-bold">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        {{ $hidupCount ?? 0 }} Hidup
                    </div>
                    <div class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-blue-50 text-blue-800 border border-blue-200 text-xs font-bold">
                        <span class="w-2 h-2 rounded-full bg-blue-600 animate-ping"></span>
                        {{ $dipakaiCount ?? 0 }} Dipakai
                    </div>
                    <div class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-rose-50 text-rose-800 border border-rose-200 text-xs font-bold">
                        <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                        {{ $matiCount ?? 0 }} Mati
                    </div>
                    <a href="{{ route('kepala-lab.buku-booking') }}"
                        class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl bg-[#424dc4] hover:bg-[#363ea3] text-white text-xs font-bold transition-all shadow-sm shadow-[#424dc4]/20">
                        <span>Buku Booking</span>
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                        </svg>
                    </a>
                </div>
            </div>

            <!-- 4 Grid Komputer — Kartu Visual Bergaya -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-5">
                @foreach($computers as $comp)
                    @php
                        $hasActive = ($comp->active_session !== null);
                        $isMati    = ($comp->status === 'MAINTENANCE' || $comp->status === 'OFFLINE' || !$comp->is_active);

                        if ($hasActive) {
                            $compStatus  = 'DIPAKAI';
                            $cardBg      = 'bg-white border-2 border-blue-400 shadow-blue-100';
                            $headerBg    = 'bg-gradient-to-br from-blue-600 to-blue-700';
                            $badgeCls    = 'bg-blue-100 text-blue-800 border border-blue-300';
                            $dotCls      = 'bg-blue-500 animate-ping';
                            $dotWrap     = true;
                            $iconBox     = 'bg-blue-500/20 text-blue-100';
                            $bodyBg      = 'bg-blue-50 border-blue-200';
                            $bodyTitle   = 'text-blue-800';
                            $footerBg    = 'bg-blue-600 hover:bg-blue-700 text-white shadow-blue-600/25';
                            $footerLabel = 'Matikan / Set Maintenance';
                        } elseif ($isMati) {
                            $compStatus  = 'MATI';
                            $cardBg      = 'bg-white border-2 border-rose-300 shadow-rose-50';
                            $headerBg    = 'bg-gradient-to-br from-slate-500 to-slate-700';
                            $badgeCls    = 'bg-rose-100 text-rose-800 border border-rose-300';
                            $dotCls      = 'bg-rose-500';
                            $dotWrap     = false;
                            $iconBox     = 'bg-slate-400/20 text-slate-200';
                            $bodyBg      = 'bg-rose-50 border-rose-200';
                            $bodyTitle   = 'text-rose-800';
                            $footerBg    = 'bg-emerald-600 hover:bg-emerald-700 text-white shadow-emerald-600/25';
                            $footerLabel = 'Hidupkan Kembali PC';
                        } else {
                            $compStatus  = 'HIDUP';
                            $cardBg      = 'bg-white border-2 border-emerald-300 shadow-emerald-50';
                            $headerBg    = 'bg-gradient-to-br from-emerald-500 to-teal-600';
                            $badgeCls    = 'bg-emerald-100 text-emerald-800 border border-emerald-300';
                            $dotCls      = 'bg-emerald-400 animate-pulse';
                            $dotWrap     = false;
                            $iconBox     = 'bg-white/20 text-white';
                            $bodyBg      = 'bg-emerald-50 border-emerald-200';
                            $bodyTitle   = 'text-emerald-800';
                            $footerBg    = 'bg-amber-500 hover:bg-amber-600 text-white shadow-amber-500/25';
                            $footerLabel = 'Matikan / Set Maintenance';
                        }

                        $pcNum = filter_var($comp->computer_code, FILTER_SANITIZE_NUMBER_INT);
                    @endphp

                    <div class="rounded-2xl overflow-hidden shadow-sm hover:shadow-lg transition-all duration-200 {{ $cardBg }}">

                        {{-- ── HEADER BERWARNA (Ilustrasi Monitor) ── --}}
                        <div class="relative {{ $headerBg }} p-4 pb-5">

                            {{-- Badge Status kanan atas --}}
                            <div class="absolute top-3 right-3">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-black bg-white/90 backdrop-blur-sm
                                    {{ $compStatus === 'HIDUP' ? 'text-emerald-700' : ($compStatus === 'DIPAKAI' ? 'text-blue-700' : 'text-rose-700') }}">
                                    @if($dotWrap)
                                        <span class="relative flex h-1.5 w-1.5">
                                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full opacity-75 {{ $dotCls }}"></span>
                                            <span class="relative inline-flex rounded-full h-1.5 w-1.5 {{ $compStatus === 'DIPAKAI' ? 'bg-blue-600' : 'bg-emerald-500' }}"></span>
                                        </span>
                                    @else
                                        <span class="w-1.5 h-1.5 rounded-full {{ $dotCls }}"></span>
                                    @endif
                                    {{ $compStatus }}
                                </span>
                            </div>

                            {{-- Nomor & Kode PC --}}
                            <div class="flex items-center gap-3">
                                {{-- Nomor Bulat --}}
                                <div class="w-10 h-10 rounded-xl {{ $iconBox }} flex items-center justify-center shrink-0 font-black text-base font-mono border border-white/20 shadow-inner">
                                    {{ str_pad($pcNum, 2, '0', STR_PAD_LEFT) }}
                                </div>
                                <div class="min-w-0">
                                    <span class="text-lg font-black text-white font-mono block leading-tight tracking-tight">
                                        {{ $comp->computer_code }}
                                    </span>
                                    <span class="text-[10px] text-white/70 font-medium block truncate">
                                        {{ $comp->location ?? 'Lab Data Sains' }} – Meja {{ $pcNum }}
                                    </span>
                                </div>
                            </div>

                            {{-- Ilustrasi Monitor SVG --}}
                            <div class="absolute bottom-0 right-4 opacity-10 pointer-events-none">
                                <svg class="w-16 h-16 text-white" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M20 3H4a2 2 0 00-2 2v11a2 2 0 002 2h7v2H7v2h10v-2h-4v-2h7a2 2 0 002-2V5a2 2 0 00-2-2zm0 13H4V5h16v11z"/>
                                </svg>
                            </div>
                        </div>

                        {{-- ── BODY ── --}}
                        <div class="p-4 space-y-3">

                            {{-- Nama & Spesifikasi --}}
                            <div>
                                <h4 class="text-sm font-bold text-[#0b1c30] truncate" title="{{ $comp->computer_name }}">
                                    {{ $comp->computer_name }}
                                </h4>
                                <p class="text-[11px] text-slate-400 truncate mt-0.5" title="{{ $comp->specs }}">
                                    {{ $comp->specs ?? 'Intel Core i7 • 32GB RAM • RTX Dedicated' }}
                                </p>
                            </div>

                            {{-- Kotak Status Kondisi --}}
                            <div class="p-3 rounded-xl border text-xs space-y-1.5 {{ $bodyBg }}">
                                @if($compStatus === 'DIPAKAI')
                                    <div class="flex items-center gap-1.5 {{ $bodyTitle }} font-bold text-[10px] uppercase tracking-wider">
                                        <svg class="w-3.5 h-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                        </svg>
                                        Sesi Aktif — {{ $comp->active_session->check_in_at->format('H:i') }} WIB
                                    </div>
                                    <p class="font-bold text-slate-900 truncate" title="{{ ($comp->active_session->user && $comp->active_session->user->role === 'kepala_lab') ? 'Kepala Laboratorium' : $comp->active_session->user->name }}">
                                        {{ ($comp->active_session->user && $comp->active_session->user->role === 'kepala_lab') ? 'Kepala Laboratorium' : $comp->active_session->user->name }}
                                    </p>
                                    <p class="text-[11px] text-slate-500 font-mono">{{ ($comp->active_session->user && $comp->active_session->user->role === 'kepala_lab') ? 'Otoritas Lab' : 'NIM: ' . $comp->active_session->user->nim }}</p>

                                @elseif($compStatus === 'MATI')
                                    <div class="flex items-center gap-1.5 {{ $bodyTitle }} font-bold text-[10px] uppercase tracking-wider">
                                        <svg class="w-3.5 h-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                                        </svg>
                                        Unit Dimatikan / Maintenance
                                    </div>
                                    <p class="text-[11px] text-slate-600 line-clamp-2 leading-relaxed">
                                        {{ $comp->maintenance_reason ?? 'Perawatan hardware & update software sistem' }}
                                    </p>
                                    <p class="text-[10px] text-rose-700 font-semibold">
                                        @if($comp->maintenance_until)
                                            Hingga: {{ $comp->maintenance_until->translatedFormat('d M, H:i') }} WIB
                                        @else
                                            Mode: Manual (Terkunci Mati)
                                        @endif
                                    </p>

                                @else
                                    <div class="flex items-center gap-1.5 {{ $bodyTitle }} font-bold text-[10px] uppercase tracking-wider">
                                        <svg class="w-3.5 h-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                        </svg>
                                        Online &amp; Siap Pakai
                                    </div>
                                    <p class="text-[11px] text-slate-600 leading-relaxed">
                                        Desktop Agent standby terkunci. Menunggu mahasiswa input token 16 digit.
                                    </p>
                                    <p class="text-[10px] text-emerald-700 font-bold">● Tidak ada pengguna aktif</p>
                                @endif
                            </div>
                        </div>

                        {{-- ── FOOTER TOMBOL AKSI ── --}}
                        <div class="px-4 pb-4">
                            @if($compStatus === 'DIPAKAI')
                                <div class="flex gap-2">
                                    <form method="POST" action="{{ route('kepala-lab.session.force-end', $comp->active_session) }}" class="flex-1">
                                        @csrf
                                        <button type="submit"
                                            onclick="return confirm('Hentikan paksa sesi billing mahasiswa pada unit {{ $comp->computer_code }}?')"
                                            class="w-full py-2.5 rounded-xl text-xs font-bold inline-flex items-center justify-center gap-1.5 bg-rose-600 hover:bg-rose-700 text-white shadow-sm transition-all cursor-pointer">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                            </svg>
                                            Force End
                                        </button>
                                    </form>
                                    <button type="button"
                                        @click="maintComputerId={{ $comp->id }};maintComputerCode='{{ $comp->computer_code }}';maintComputerName='{{ addslashes($comp->computer_name) }}';maintDurationType='hours';maintDurationValue=2;maintReason='Perawatan hardware & update software sistem';maintenanceModalOpen=true"
                                        class="px-3 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 border border-slate-200 transition-colors cursor-pointer inline-flex items-center justify-center">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 5.636a9 9 0 010 12.728m0 0l-2.829-2.829m2.829 2.829L21 21M15.536 8.464a5 5 0 010 7.072m0 0l-2.829-2.829m-4.243 4.243a9 9 0 01-2.829-2.829m0 0L3 21m2.636-15.364a9 9 0 0112.728 0"/>
                                        </svg>
                                    </button>
                                </div>

                            @elseif($compStatus === 'MATI')
                                <form method="POST" action="{{ route('kepala-lab.komputer.toggle-maintenance', $comp) }}">
                                    @csrf
                                    <button type="submit"
                                        class="w-full py-2.5 rounded-xl text-xs font-black inline-flex items-center justify-center gap-2 {{ $footerBg }} shadow-sm transition-all cursor-pointer">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5.636 18.364a9 9 0 010-12.728m12.728 0a9 9 0 010 12.728m-9.9-2.829a5 5 0 010-7.07m7.072 0a5 5 0 010 7.07M12 3v9"/>
                                        </svg>
                                        {{ $footerLabel }}
                                    </button>
                                </form>

                            @else
                                <button type="button"
                                    @click="maintComputerId={{ $comp->id }};maintComputerCode='{{ $comp->computer_code }}';maintComputerName='{{ addslashes($comp->computer_name) }}';maintDurationType='hours';maintDurationValue=2;maintReason='Perawatan hardware & update software sistem';maintenanceModalOpen=true"
                                    class="w-full py-2.5 rounded-xl text-xs font-bold inline-flex items-center justify-center gap-2 {{ $footerBg }} shadow-sm transition-all cursor-pointer">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 5.636a9 9 0 010 12.728m0 0l-2.829-2.829m2.829 2.829L21 21M15.536 8.464a5 5 0 010 7.072m0 0l-2.829-2.829m-4.243 4.243a9 9 0 01-2.829-2.829m0 0L3 21m2.636-15.364a9 9 0 0112.728 0"/>
                                    </svg>
                                    {{ $footerLabel }}
                                </button>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- 5. Riwayat Reservasi Terbaru -->
        <div class="bg-white rounded-2xl sm:rounded-3xl border border-[#e2e8f0] p-4 sm:p-6 shadow-xs space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-100 pb-4">
                <div>
                    <h3 class="text-base font-bold text-[#0b1c30]">Log Riwayat Aktivitas & Reservasi Lab Terakhir</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Rekapitulasi 10 status peminjaman komputer terbaru.</p>
                </div>
                <span class="text-[11px] font-mono text-slate-400">10 Data Terbaru</span>
            </div>

            <!-- Mobile Table Scroll Hint -->
            <div class="sm:hidden px-3.5 py-2 bg-slate-50 border border-slate-100 rounded-xl flex items-center justify-between text-[11px] text-slate-500 font-medium">
                <span class="flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5 text-[#424dc4]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                    </svg>
                    Geser tabel ke samping untuk melihat detail
                </span>
            </div>

            <div class="overflow-x-auto custom-scrollbar -mx-4 sm:mx-0 px-4 sm:px-0">
                <table class="w-full text-left text-xs min-w-[650px]">
                    <thead>
                        <tr class="border-b border-slate-100 text-slate-400 uppercase tracking-wider text-[10px]">
                            <th class="py-3 px-3">Mahasiswa</th>
                            <th class="py-3 px-3">Unit</th>
                            <th class="py-3 px-3">Jadwal</th>
                            <th class="py-3 px-3">Keperluan</th>
                            <th class="py-3 px-3">Kode Akses</th>
                            <th class="py-3 px-3">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($recentReservations as $res)
                            <tr class="hover:bg-slate-50/60 transition-colors">
                                <td class="py-3.5 px-3">
                                    <span class="font-bold text-slate-900 block truncate max-w-[180px]">{{ ($res->user && $res->user->role === 'kepala_lab') ? 'Kepala Laboratorium' : $res->user->name }}</span>
                                    <span class="text-[11px] text-slate-500 font-mono">{{ ($res->user && $res->user->role === 'kepala_lab') ? 'Otoritas Lab' : $res->user->nim }}</span>
                                </td>
                                <td class="py-3.5 px-3 font-mono font-bold text-[#424dc4]">
                                    {{ $res->computer->computer_code }}
                                </td>
                                <td class="py-3.5 px-3">
                                    <span class="block text-slate-800 font-semibold">{{ \Carbon\Carbon::parse($res->reservation_date)->isoFormat('D MMM Y') }}</span>
                                    <span class="text-[11px] text-slate-500 font-mono">{{ substr($res->start_time, 0, 5) }} - {{ substr($res->end_time, 0, 5) }}</span>
                                </td>
                                <td class="py-3.5 px-3 text-slate-600 max-w-xs truncate" title="{{ $res->purpose }}">
                                    {{ $res->purpose }}
                                </td>
                                <td class="py-3.5 px-3 font-mono font-semibold text-slate-700">
                                    {{ $res->accessCode->code ?? '-' }}
                                </td>
                                <td class="py-3.5 px-3">
                                    @if($res->status === 'APPROVED')
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">DISETUJUI</span>
                                        <span class="block text-[9px] text-slate-400 mt-0.5">Oleh: {{ ($res->approver && $res->approver->role === 'kepala_lab') ? 'Kepala Lab' : ($res->approver->name ?? 'Kepala Lab') }}</span>
                                    @elseif($res->status === 'CHECKED_IN')
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-blue-100 text-blue-800 border border-blue-200">DIGUNAKAN</span>
                                    @elseif($res->status === 'COMPLETED')
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700 border border-slate-200">SELESAI</span>
                                    @elseif($res->status === 'REJECTED')
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800 border border-rose-200"
                                            title="{{ $res->rejection_reason }}">DITOLAK</span>
                                        <span class="block text-[9px] text-slate-400 mt-0.5">Oleh: {{ $res->approver->name ?? 'Kepala Lab' }}</span>
                                    @else
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-slate-100 text-slate-500 border border-slate-200">{{ $res->status }}</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-8 text-slate-400">
                                    Belum ada riwayat aktivitas reservasi.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
@endsection
