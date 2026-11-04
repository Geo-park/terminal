@extends('layouts.mahasiswa')

@section('title', 'Buku Booking - Laboratorium Sains Data & AI')
@section('page-title', 'Buku Booking Komputer')

@section('content')
<div class="space-y-6">

    <!-- Top Header & Breadcrumbs -->
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 pb-4 border-b border-[#e2e8f0]">
        <div>
            <nav class="flex items-center gap-2 text-xs font-semibold text-slate-400 mb-1">
                <a href="{{ route('mahasiswa.dashboard') }}" class="hover:text-[#424dc4] transition-colors">Dashboard</a>
                <span>&rsaquo;</span>
                <span class="text-[#424dc4]">Buku Booking Lab</span>
            </nav>
            <h2 class="text-xl sm:text-2xl font-extrabold text-[#0b1c30] tracking-tight">
                Buku Booking & Jadwal Pemakaian Lab
            </h2>
            <p class="text-xs sm:text-sm text-slate-500 mt-0.5">
                Transparansi ketersediaan 4 unit Workstation AI. Pantau jam berapa saja komputer telah direservasi atau masih kosong.
            </p>
        </div>

        <!-- Tombol Aksi Reservasi Baru -->
        <div class="flex items-center gap-2.5">
            <a href="{{ route('mahasiswa.reservasi.create') }}" 
                class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-[#424dc4] hover:bg-[#363fa3] text-white text-xs font-bold transition-all shadow-md shadow-[#424dc4]/20 cursor-pointer">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                </svg>
                <span>Ajukan Reservasi Baru</span>
            </a>
        </div>
    </div>

    <!-- Date Navigation Toolbar -->
    <div class="bg-white rounded-2xl border border-[#e2e8f0] p-4 sm:p-5 elevation-1">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            
            <!-- Tanggal Terpilih & Navigasi Panah -->
            <div class="flex items-center justify-between sm:justify-start gap-2 sm:gap-3">
                <a href="{{ route('mahasiswa.buku-booking', ['date' => $prevDate]) }}" 
                    title="Hari Sebelumnya"
                    class="w-9 h-9 rounded-xl border border-slate-200 hover:border-[#424dc4] hover:bg-[#eff4ff] text-slate-600 hover:text-[#424dc4] flex items-center justify-center transition-colors shrink-0">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                    </svg>
                </a>

                <div class="text-center sm:text-left min-w-0">
                    <div class="flex items-center justify-center sm:justify-start gap-1.5 sm:gap-2 flex-wrap">
                        <span class="text-xs sm:text-base font-bold text-[#0b1c30] truncate">
                            {{ $selectedDate->translatedFormat('l, d F Y') }}
                        </span>
                        @if($isToday)
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                HARI INI
                            </span>
                        @endif
                    </div>
                    <span class="text-[11px] sm:text-xs text-slate-400 block truncate">Jam Operasional: 07.00 – 16.00 WIB (Senin – Jumat)</span>
                </div>

                <a href="{{ route('mahasiswa.buku-booking', ['date' => $nextDate]) }}" 
                    title="Hari Berikutnya"
                    class="w-9 h-9 rounded-xl border border-slate-200 hover:border-[#424dc4] hover:bg-[#eff4ff] text-slate-600 hover:text-[#424dc4] flex items-center justify-center transition-colors shrink-0">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                    </svg>
                </a>
            </div>

            <!-- Quick Jump Pills & Date Picker -->
            <div class="flex flex-wrap items-center justify-center sm:justify-start gap-1.5 sm:gap-2">
                @php
                    $todayStr = \Carbon\Carbon::today('Asia/Jakarta')->toDateString();
                    $tomorrowStr = \Carbon\Carbon::tomorrow('Asia/Jakarta')->toDateString();
                    $dayAfterStr = \Carbon\Carbon::today('Asia/Jakarta')->addDays(2)->toDateString();
                @endphp

                <a href="{{ route('mahasiswa.buku-booking', ['date' => $todayStr]) }}"
                    class="px-3 py-1.5 rounded-xl text-xs font-semibold transition-all {{ $selectedDate->toDateString() === $todayStr ? 'bg-[#424dc4] text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                    Hari Ini
                </a>
                <a href="{{ route('mahasiswa.buku-booking', ['date' => $tomorrowStr]) }}"
                    class="px-3 py-1.5 rounded-xl text-xs font-semibold transition-all {{ $selectedDate->toDateString() === $tomorrowStr ? 'bg-[#424dc4] text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                    Besok
                </a>
                <a href="{{ route('mahasiswa.buku-booking', ['date' => $dayAfterStr]) }}"
                    class="px-3 py-1.5 rounded-xl text-xs font-semibold transition-all {{ $selectedDate->toDateString() === $dayAfterStr ? 'bg-[#424dc4] text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                    Lusa
                </a>

                <!-- Custom Date Picker -->
                <form method="GET" action="{{ route('mahasiswa.buku-booking') }}" class="flex items-center gap-1.5 ml-0 sm:ml-1 w-full sm:w-auto justify-center mt-1 sm:mt-0">
                    <input type="date" name="date" value="{{ $selectedDate->toDateString() }}" 
                        onchange="this.form.submit()"
                        class="text-xs px-2.5 py-1.5 rounded-xl border border-slate-200 bg-white text-slate-700 font-medium focus:outline-none focus:ring-2 focus:ring-[#424dc4]/20 focus:border-[#424dc4] cursor-pointer w-full sm:w-auto">
                </form>
            </div>
        </div>

        <!-- Status Legend -->
        <div class="mt-4 pt-3 border-t border-slate-100 flex flex-wrap items-center justify-between gap-3 text-xs">
            <div class="flex flex-wrap items-center gap-4">
                <span class="inline-flex items-center gap-1.5 text-slate-600">
                    <span class="w-3 h-3 rounded-md bg-emerald-100 border border-emerald-300"></span>
                    <span>Tersedia (Kosong)</span>
                </span>
                <span class="inline-flex items-center gap-1.5 text-slate-600">
                    <span class="w-3 h-3 rounded-md bg-indigo-100 border border-indigo-300"></span>
                    <span>Disetujui (Terisi)</span>
                </span>
                <span class="inline-flex items-center gap-1.5 text-slate-600">
                    <span class="w-3 h-3 rounded-md bg-rose-100 border border-rose-300"></span>
                    <span>Billing Aktif</span>
                </span>
                <span class="inline-flex items-center gap-1.5 text-slate-600">
                    <span class="w-3 h-3 rounded-md bg-amber-100 border border-amber-300"></span>
                    <span>Menunggu Persetujuan</span>
                </span>
                <span class="inline-flex items-center gap-1.5 text-slate-600">
                    <span class="w-3 h-3 rounded-md bg-amber-200 border border-amber-400"></span>
                    <span>Maintenance (Pemeliharaan)</span>
                </span>
            </div>

            <div class="text-slate-400 text-[11px]">
                Klik slot <strong class="text-emerald-700">Tersedia</strong> untuk langsung reservasi pada jam tersebut.
            </div>
        </div>
    </div>

    <!-- Summary Metrics Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl p-4 sm:p-5 border border-[#e2e8f0] elevation-1 flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-2xl bg-[#eff4ff] text-[#424dc4] flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div>
                <span class="block text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Jam Terisi</span>
                <span class="text-base sm:text-lg font-extrabold text-[#0b1c30]">
                    {{ $totalBookedHours }} <span class="text-xs font-normal text-slate-400">/ {{ $totalPossibleHours }} Jam PC</span>
                </span>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-4 sm:p-5 border border-[#e2e8f0] elevation-1 flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div>
                <span class="block text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Jam Kosong Tersedia</span>
                <span class="text-base sm:text-lg font-extrabold text-emerald-600">
                    {{ $totalPossibleHours - $totalBookedHours }} <span class="text-xs font-normal text-slate-400">Jam PC</span>
                </span>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-4 sm:p-5 border border-[#e2e8f0] elevation-1 flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
            </div>
            <div>
                <span class="block text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Tingkat Okupansi</span>
                <span class="text-base sm:text-lg font-extrabold text-[#424dc4]">
                    {{ $occupancyRate }}%
                </span>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-4 sm:p-5 border border-[#e2e8f0] elevation-1 flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
            </div>
            <div>
                <span class="block text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Total Reservasi</span>
                <span class="text-base sm:text-lg font-extrabold text-[#0b1c30]">
                    {{ $totalReservationsCount }} <span class="text-xs font-normal text-slate-400">Sesi</span>
                </span>
            </div>
        </div>
    </div>

    <!-- Main Visual Timetable Grid: Matriks Jam vs Komputer -->
    <div class="bg-white rounded-3xl border border-[#e2e8f0] shadow-sm overflow-hidden">
        <div class="p-5 sm:p-6 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h3 class="text-base font-bold text-[#0b1c30]">Matriks Jadwal Jam Pemakaian Komputer</h3>
                <p class="text-xs text-slate-500">Rentang jam operasional 07.00 s/d 16.00 WIB untuk 4 unit Workstation AI.</p>
            </div>
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-[#eff4ff] text-[#424dc4] border border-[#dce9ff]">
                <span>Maks. 5 Jam/Sesi</span>
            </span>
        </div>

        <!-- Timetable Table -->
        <div class="sm:hidden px-4 py-2 bg-slate-50 border-b border-slate-100 flex items-center justify-between text-[11px] text-slate-500 font-medium">
            <span class="flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5 text-[#424dc4]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                </svg>
                Geser tabel ke kanan untuk melihat semua PC &rarr;
            </span>
            <span class="font-mono text-[10px] bg-white border border-slate-200 px-1.5 py-0.5 rounded text-[#424dc4] font-bold">4 Unit</span>
        </div>
        <div class="overflow-x-auto custom-scrollbar">
            <table class="w-full text-left border-collapse min-w-[760px]">
                <thead>
                    <tr class="bg-[#f8f9ff] border-b border-[#e2e8f0]">
                        <th class="py-3.5 px-4 text-xs font-bold text-slate-500 uppercase tracking-wider w-36 border-r border-[#e2e8f0]">
                            Rentang Jam
                        </th>
                        @foreach ($computers as $comp)
                            <th class="py-3.5 px-4 text-xs font-bold text-[#0b1c30] border-r border-[#e2e8f0] last:border-r-0">
                                <div class="flex items-center justify-between">
                                    <span class="px-2 py-0.5 rounded-md font-mono text-xs font-extrabold bg-[#eff4ff] text-[#424dc4]">
                                        {{ $comp->computer_code }}
                                    </span>
                                    <span class="text-[11px] font-semibold text-slate-500 truncate max-w-[140px]" title="{{ $comp->computer_name }}">
                                        {{ $comp->computer_name }}
                                    </span>
                                </div>
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($timeSlots as $slotIndex => $slot)
                        <tr class="hover:bg-slate-50/50 transition-colors">
                            <!-- Kolom Jam -->
                            <td class="py-3.5 px-4 text-xs font-bold text-slate-600 bg-slate-50/60 border-r border-[#e2e8f0] whitespace-nowrap">
                                <div class="flex items-center gap-2">
                                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <span>{{ $slot['label'] }} WIB</span>
                                </div>
                            </td>

                            <!-- 4 Kolom Komputer -->
                            @foreach ($computers as $comp)
                                @php
                                    $cell = $scheduleMatrix[$comp->id]['slots'][$slotIndex] ?? null;
                                @endphp

                                <td class="py-2 px-3 border-r border-slate-100 last:border-r-0 align-middle">
                                    @if ($cell && $cell['status'] === 'BOOKED')
                                        @php
                                            $res = $cell['reservation'];
                                            $isMine = $cell['is_mine'];
                                            $isSessionActive = ($res->status === 'CHECKED_IN');
                                            $isPending = ($res->status === 'PENDING_APPROVAL');
                                        @endphp

                                        <div class="rounded-xl p-2.5 transition-all {{ $isSessionActive ? 'bg-rose-50 border border-rose-200' : ($isMine ? 'bg-[#eff4ff] border border-[#dce9ff]' : 'bg-slate-50 border border-slate-200') }}">
                                            <div class="flex items-center justify-between gap-1 mb-1">
                                                <span class="text-[10px] font-bold font-mono px-1.5 py-0.2 rounded {{ $isSessionActive ? 'bg-rose-200 text-rose-800' : ($isMine ? 'bg-[#424dc4] text-white' : 'bg-slate-200 text-slate-700') }}">
                                                    {{ substr($res->start_time, 0, 5) }} – {{ substr($res->end_time, 0, 5) }}
                                                </span>
                                                @if($isSessionActive)
                                                    <span class="text-[9px] font-bold text-rose-700 uppercase">Billing Aktif</span>
                                                @elseif($isPending)
                                                    <span class="text-[9px] font-bold text-amber-700 uppercase">Menunggu</span>
                                                @else
                                                    <span class="text-[9px] font-bold text-emerald-700 uppercase">Disetujui</span>
                                                @endif
                                            </div>

                                            <p class="text-xs font-bold text-[#0b1c30] truncate">
                                                @if($isMine)
                                                    Jadwal Anda ({{ auth()->user()->name }})
                                                @elseif($res->user && $res->user->role === 'kepala_lab')
                                                    Kepala Laboratorium
                                                @else
                                                    {{ $res->user->name ?? 'Mahasiswa Lab' }}
                                                @endif
                                            </p>
                                            <p class="text-[11px] text-slate-500 truncate" title="{{ $res->purpose }}">
                                                {{ $res->purpose }}
                                            </p>
                                        </div>
                                    @elseif ($cell && $cell['status'] === 'MAINTENANCE')
                                        <!-- Slot Maintenance (Non-clickable) -->
                                        <div class="rounded-xl p-2.5 bg-amber-50/80 border border-amber-200/90 text-amber-900 select-none cursor-not-allowed">
                                            <div class="flex items-center justify-between gap-1 mb-1">
                                                <span class="text-[10px] font-bold font-mono px-1.5 py-0.5 rounded bg-amber-200/80 text-amber-900">
                                                    {{ $slot['label'] }}
                                                </span>
                                                <span class="text-[9px] font-bold uppercase tracking-wider text-amber-800 bg-amber-100 px-1.5 py-0.5 rounded">
                                                    Maintenance
                                                </span>
                                            </div>
                                            <p class="text-xs font-bold text-amber-950 truncate flex items-center gap-1">
                                                <svg class="w-3 h-3 text-amber-600 inline shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                                </svg>
                                                <span>Pemeliharaan Unit</span>
                                            </p>
                                            <p class="text-[11px] text-amber-800/80 truncate mt-0.5" title="{{ $cell['reason'] ?? 'Perawatan hardware & software' }}">
                                                {{ $cell['reason'] ?? 'Perawatan teknis' }}
                                            </p>
                                        </div>
                                    @else
                                        <!-- Slot Kosong / Tersedia -->
                                        <a href="{{ $cell['quick_reserve_url'] ?? route('mahasiswa.reservasi.create') }}"
                                            title="Klik untuk reservasi jam {{ $slot['label'] }} di {{ $comp->computer_code }}"
                                            class="group block w-full py-2.5 px-3 rounded-xl border border-dashed border-emerald-300 bg-emerald-50/40 hover:bg-emerald-100/60 hover:border-emerald-500 transition-all text-center">
                                            <span class="text-[11px] font-bold text-emerald-700 group-hover:text-emerald-900 inline-flex items-center gap-1">
                                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                                                </svg>
                                                <span>Tersedia</span>
                                            </span>
                                        </a>
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Ringkasan Per Komputer Hari Ini -->
    <div class="space-y-4">
        <div class="flex items-center justify-between">
            <h3 class="text-base font-bold text-[#0b1c30]">Daftar Jadwal Per Unit Komputer</h3>
            <span class="text-xs text-slate-400">Total 4 Unit Workstation</span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @foreach ($computers as $comp)
                @php
                    $matrixData = $scheduleMatrix[$comp->id] ?? null;
                    $compBookings = $matrixData['reservations_list'] ?? collect();
                    $bookedCount = $matrixData['booked_hours'] ?? 0;
                @endphp

                <div class="bg-white rounded-2xl border border-[#e2e8f0] p-5 elevation-1 flex flex-col justify-between">
                    <div>
                        <!-- Header Kartu -->
                        <div class="flex items-start justify-between gap-2 border-b border-slate-100 pb-3">
                            <div>
                                <span class="px-2 py-0.5 rounded-md text-xs font-mono font-bold bg-[#eff4ff] text-[#424dc4]">
                                    {{ $comp->computer_code }}
                                </span>
                                <h4 class="text-sm font-bold text-[#0b1c30] mt-1">{{ $comp->computer_name }}</h4>
                                <p class="text-[11px] text-slate-500">{{ $comp->specs ?? 'Spesifikasi Lab Data Sains' }}</p>
                            </div>
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold {{ $bookedCount > 0 ? 'bg-indigo-100 text-[#424dc4]' : 'bg-emerald-100 text-emerald-800' }}">
                                {{ $bookedCount }} Jam Terisi
                            </span>
                        </div>

                        <!-- Progress Bar Okupansi -->
                        <div class="my-3">
                            <div class="flex items-center justify-between text-[11px] text-slate-500 mb-1">
                                <span>Keterisian Hari Ini:</span>
                                <span class="font-bold text-[#0b1c30]">{{ round(($bookedCount / 9) * 100) }}%</span>
                            </div>
                            <div class="w-full bg-slate-100 h-2 rounded-full overflow-hidden">
                                <div class="bg-[#424dc4] h-full rounded-full transition-all duration-300" style="width: {{ ($bookedCount / 9) * 100 }}%"></div>
                            </div>
                        </div>

                        <!-- Daftar Jam Terisi -->
                        <div class="space-y-2 mt-3">
                            <span class="block text-[11px] font-bold uppercase tracking-wider text-slate-400">Jadwal Sesi Terdaftar:</span>
                            @if ($compBookings->isEmpty())
                                <div class="p-3 rounded-xl bg-emerald-50/50 border border-emerald-100 text-center">
                                    <p class="text-xs font-bold text-emerald-700">Belum Ada Reservasi</p>
                                    <p class="text-[11px] text-emerald-600 mt-0.5">Komputer ini kosong sepanjang hari (07.00 - 16.00 WIB).</p>
                                </div>
                            @else
                                <div class="space-y-1.5 max-h-40 overflow-y-auto custom-scrollbar">
                                    @foreach ($compBookings as $b)
                                        <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-100 flex items-center justify-between text-xs">
                                            <div class="min-w-0 pr-2">
                                                <div class="flex items-center gap-1.5">
                                                    <span class="font-mono font-bold text-[#424dc4]">{{ substr($b->start_time, 0, 5) }} - {{ substr($b->end_time, 0, 5) }} WIB</span>
                                                    @if($b->user_id === auth()->id())
                                                        <span class="px-1.5 py-0.2 rounded-full text-[9px] font-bold bg-[#eff4ff] text-[#424dc4]">Jadwal Anda</span>
                                                    @elseif($b->user && $b->user->role === 'kepala_lab')
                                                        <span class="text-[10px] text-slate-500">(Kepala Lab)</span>
                                                    @endif
                                                </div>
                                                <p class="text-[11px] text-slate-600 truncate mt-0.5">
                                                    {{ $b->purpose }}
                                                </p>
                                            <span class="px-2 py-0.5 rounded-md text-[10px] font-bold {{ $b->status === 'CHECKED_IN' ? 'bg-rose-100 text-rose-800' : ($b->status === 'APPROVED' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800') }}">
                                                {{ $b->status === 'CHECKED_IN' ? 'Aktif' : ($b->status === 'APPROVED' ? 'Disetujui' : 'Menunggu') }}
                                            </div>
                                            </span>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Footer Action -->
                    <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between">
                        <span class="text-[11px] text-slate-400">Sisa Kosong: {{ 9 - $bookedCount }} Jam</span>
                        <a href="{{ route('mahasiswa.reservasi.create', ['computer_id' => $comp->id, 'date' => $selectedDate->toDateString()]) }}" 
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-[#eff4ff] hover:bg-[#424dc4] text-[#424dc4] hover:text-white border border-[#dce9ff] text-xs font-bold transition-all shadow-xs group">
                            <span>Pesan PC Ini</span>
                            <span class="group-hover:translate-x-0.5 transition-transform">&rarr;</span>
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

</div>
@endsection

