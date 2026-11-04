@extends('layouts.mahasiswa')

@section('title', 'Ajukan Reservasi Komputer')
@section('page-title', 'Ajukan Reservasi')

@section('content')
<div class="space-y-6" x-data="reservationForm()">

    <!-- Top Header & Breadcrumbs -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pb-3 border-b border-[#e2e8f0]">
        <div>
            <nav class="flex items-center gap-2 text-xs font-semibold text-slate-400 mb-1">
                <a href="{{ route('mahasiswa.dashboard') }}" class="hover:text-[#424dc4] transition-colors">Dashboard</a>
                <span>&rsaquo;</span>
                <span class="text-slate-600">Reservasi Komputer</span>
                <span>&rsaquo;</span>
                <span class="text-[#424dc4]">Formulir Baru</span>
            </nav>
            <h2 class="text-xl sm:text-2xl font-extrabold text-[#0b1c30] tracking-tight">
                Ajukan Reservasi Komputer
            </h2>
            <p class="text-xs sm:text-sm text-slate-500 mt-0.5">
                Pilih unit workstation laboratorium dan tentukan jadwal peminjaman Anda.
            </p>
        </div>

        <!-- Quick Status Badges -->
        <div class="flex flex-wrap items-center gap-2">
            @if ($isUnlimited ?? false)
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-indigo-50 border border-indigo-200 text-[#424dc4] text-xs font-semibold">
                    <span class="w-2 h-2 rounded-full bg-[#424dc4] animate-pulse"></span>
                    Sisa Kuota: <strong>VIP Unlimited ∞ (Tanpa Batas)</strong>
                </span>
            @else
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs font-semibold">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    Sisa Kuota: <strong>{{ $remainingQuotaToday }} / 2 Sesi</strong>
                </span>
            @endif
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-[#eff4ff] border border-[#dce9ff] text-[#424dc4] text-xs font-semibold">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                07.00 - 16.00 WIB
            </span>
        </div>
    </div>

    <!-- Alert Error Server-side Validation -->
    @if ($errors->any())
        <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-sm space-y-1 animate-shake">
            <div class="flex items-center gap-2 font-bold text-rose-900 text-xs sm:text-sm">
                <svg class="w-4 h-4 shrink-0 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>Terdapat kesalahan pada isian formulir:</span>
            </div>
            <ul class="list-disc list-inside text-xs pl-5 text-rose-700 space-y-0.5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Main 2-Column Split Layout -->
    <form method="POST" action="{{ route('mahasiswa.reservasi.store') }}" @submit="handleSubmit($event)">
        @csrf

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
            
            <!-- LEFT COLUMN: Formulir Interaktif Utama (8 of 12 Cols) -->
            <div class="lg:col-span-8 space-y-6">

                <!-- 1. Pilihan Komputer Workstation (BR-001 & BR-008) -->
                <div class="bg-white rounded-2xl border border-[#e2e8f0] p-5 sm:p-6 elevation-1">
                    <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-100">
                        <div class="flex items-center gap-2.5">
                            <span class="w-6 h-6 rounded-lg bg-[#eff4ff] text-[#424dc4] font-bold text-xs flex items-center justify-center">1</span>
                            <h3 class="text-sm font-bold text-[#0b1c30]">Pilih Unit Komputer Workstation</h3>
                        </div>
                        <span class="text-xs font-semibold text-slate-500">
                            Mahasiswa memilih sendiri (BR-008)
                        </span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                        @foreach ($computers as $comp)
                            @php
                                $isInMaint = $comp->isInMaintenance();
                            @endphp

                            @if ($isInMaint)
                                <!-- Unit Sedang Maintenance (Tidak Dapat Dipilih Mahasiswa) -->
                                <div class="relative rounded-xl border-2 border-dashed border-amber-300 bg-amber-50/40 p-4 transition-all flex flex-col justify-between opacity-80 select-none cursor-not-allowed">
                                    <div>
                                        <div class="flex items-center justify-between mb-2">
                                            <span class="px-2.5 py-0.5 rounded-md font-mono font-bold text-xs tracking-wider bg-amber-200 text-amber-900">
                                                {{ $comp->computer_code }}
                                            </span>

                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-300 flex items-center gap-1">
                                                <svg class="w-3 h-3 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                                </svg>
                                                Maintenance
                                            </span>
                                        </div>

                                        <h4 class="text-sm font-bold text-slate-700 mb-1">
                                            {{ $comp->computer_name }}
                                        </h4>

                                        <p class="text-[11px] text-slate-500 leading-relaxed mb-2.5">
                                            {{ $comp->specs }}
                                        </p>

                                        <div class="bg-amber-100/70 rounded-lg p-2.5 text-[11px] text-amber-900 space-y-0.5 border border-amber-200/60 mb-2">
                                            <p class="font-bold flex items-center gap-1 text-amber-950">
                                                <span>⚠️ Sedang Dalam Pemeliharaan</span>
                                            </p>
                                            <p class="text-[10px] text-amber-800">
                                                @if($comp->maintenance_until)
                                                    Selesai: {{ $comp->maintenance_until->translatedFormat('d M Y, H:i') }} WIB
                                                @else
                                                    Durasi: Sampai pemberitahuan berikutnya
                                                @endif
                                            </p>
                                            @if($comp->maintenance_reason)
                                                <p class="text-[10px] text-slate-600 truncate" title="{{ $comp->maintenance_reason }}">
                                                    <span class="font-semibold text-amber-900">Alasan:</span> {{ $comp->maintenance_reason }}
                                                </p>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="pt-2.5 border-t border-amber-200/60 flex items-center justify-between text-[11px]">
                                        <span class="text-slate-400">{{ $comp->location }}</span>
                                        <span class="inline-flex items-center gap-1 font-bold text-amber-700">
                                            Tidak Dapat Dipinjam
                                        </span>
                                    </div>
                                </div>
                            @else
                                <label class="relative rounded-xl border-2 p-4 cursor-pointer transition-all flex flex-col justify-between select-none group"
                                       :class="selectedComputer == '{{ $comp->id }}' ? 'border-[#424dc4] bg-[#f8faff] ring-2 ring-[#424dc4]/15 elevation-1' : 'border-slate-200 bg-white hover:border-slate-300 hover:bg-slate-50/50'"
                                       @click="setComputer('{{ $comp->id }}', '{{ $comp->computer_code }}', '{{ $comp->computer_name }}', '{{ $comp->specs }}')">
                                    
                                    <input type="radio" 
                                           name="computer_id" 
                                           value="{{ $comp->id }}" 
                                           x-model="selectedComputer" 
                                           class="sr-only">

                                    <div>
                                        <div class="flex items-center justify-between mb-2">
                                            <span class="px-2.5 py-0.5 rounded-md font-mono font-bold text-xs tracking-wider transition-colors"
                                                  :class="selectedComputer == '{{ $comp->id }}' ? 'bg-[#424dc4] text-white' : 'bg-slate-100 text-slate-700'">
                                                {{ $comp->computer_code }}
                                            </span>

                                            <div class="w-5 h-5 rounded-full border flex items-center justify-center transition-all"
                                                 :class="selectedComputer == '{{ $comp->id }}' ? 'border-[#424dc4] bg-[#424dc4] text-white' : 'border-slate-300 bg-white group-hover:border-slate-400'">
                                                <svg x-show="selectedComputer == '{{ $comp->id }}'" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                                </svg>
                                            </div>
                                        </div>

                                        <h4 class="text-sm font-bold text-[#0b1c30] mb-1">
                                            {{ $comp->computer_name }}
                                        </h4>

                                        <p class="text-[11px] text-slate-500 leading-relaxed mb-3">
                                            {{ $comp->specs }}
                                        </p>
                                    </div>

                                    <div class="pt-2.5 border-t border-slate-100 flex items-center justify-between text-[11px]">
                                        <span class="text-slate-400">{{ $comp->location }}</span>
                                        <span class="inline-flex items-center gap-1 font-semibold text-emerald-600">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                            Tersedia
                                        </span>
                                    </div>
                                </label>
                            @endif
                        @endforeach
                    </div>
                </div>

                <!-- 2. Jadwal & Waktu Peminjaman (BR-002, BR-003, BR-005) -->
                <div class="bg-white rounded-2xl border border-[#e2e8f0] p-5 sm:p-6 elevation-1">
                    <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-100">
                        <div class="flex items-center gap-2.5">
                            <span class="w-6 h-6 rounded-lg bg-[#eff4ff] text-[#424dc4] font-bold text-xs flex items-center justify-center">2</span>
                            <h3 class="text-sm font-bold text-[#0b1c30]">Jadwal Penggunaan Workstation</h3>
                        </div>
                        <span class="text-xs text-slate-500">
                            Senin–Jumat • Maks. 5 Jam / Sesi
                        </span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <!-- Tanggal Reservasi -->
                        <div>
                            <label for="reservation_date" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                Tanggal <span class="text-rose-500">*</span>
                            </label>
                            <input type="date" 
                                   id="reservation_date" 
                                   name="reservation_date" 
                                   x-model="reservationDate" 
                                   @change="validateDate()"
                                   min="{{ date('Y-m-d') }}" 
                                   required 
                                   class="w-full px-3.5 py-2.5 bg-white border border-slate-300 rounded-xl text-sm font-medium text-slate-800 focus:outline-none focus:border-[#424dc4] focus:ring-2 focus:ring-[#424dc4]/20 transition-all">
                            <p x-show="isWeekendSelected" class="text-[11px] text-rose-500 font-semibold mt-1" style="display: none;">
                                ⚠️ Lab hanya beroperasi Senin–Jumat.
                            </p>
                        </div>

                        <!-- Jam Mulai -->
                        <div>
                            <label for="start_time" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                Jam Mulai <span class="text-rose-500">*</span>
                            </label>
                            <select id="start_time" 
                                    name="start_time" 
                                    x-model="startTime" 
                                    @change="calculateDuration()"
                                    required 
                                    class="w-full px-3.5 py-2.5 bg-white border border-slate-300 rounded-xl text-sm font-medium text-slate-800 focus:outline-none focus:border-[#424dc4] focus:ring-2 focus:ring-[#424dc4]/20 transition-all cursor-pointer">
                                <option value="">Pilih Jam Mulai</option>
                                @for ($h = 7; $h <= 15; $h++)
                                    @foreach (['00', '30'] as $m)
                                        @php $t = sprintf('%02d:%s', $h, $m); @endphp
                                        <option value="{{ $t }}">{{ $t }} WIB</option>
                                    @endforeach
                                @endfor
                            </select>
                            <span class="text-[11px] text-slate-400 mt-1 block">Mulai paling awal 07.00</span>
                        </div>

                        <!-- Jam Selesai -->
                        <div>
                            <label for="end_time" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                Jam Selesai <span class="text-rose-500">*</span>
                            </label>
                            <select id="end_time" 
                                    name="end_time" 
                                    x-model="endTime" 
                                    @change="calculateDuration()"
                                    required 
                                    class="w-full px-3.5 py-2.5 bg-white border border-slate-300 rounded-xl text-sm font-medium text-slate-800 focus:outline-none focus:border-[#424dc4] focus:ring-2 focus:ring-[#424dc4]/20 transition-all cursor-pointer">
                                <option value="">Pilih Jam Selesai</option>
                                @for ($h = 7; $h <= 16; $h++)
                                    @foreach (['00', '30'] as $m)
                                        @php 
                                            $t = sprintf('%02d:%s', $h, $m); 
                                            if ($t == '07:00' || $t > '16:00') continue;
                                        @endphp
                                        <option value="{{ $t }}">{{ $t }} WIB</option>
                                    @endforeach
                                @endfor
                            </select>
                            <span class="text-[11px] text-slate-400 mt-1 block">Selesai maks. 16.00</span>
                        </div>
                    </div>

                    <!-- Live Duration Feedback Banner -->
                    <div x-show="durationMinutes > 0" x-transition.opacity class="mt-4 p-3.5 rounded-xl border flex items-center justify-between text-xs"
                         :class="durationError ? 'bg-rose-50 border-rose-200 text-rose-800' : 'bg-[#eff4ff] border-[#dce9ff] text-[#424dc4]'"
                         style="display: none;">
                        <div class="flex items-center gap-2.5">
                            <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>
                                Durasi Terhitung: <strong class="text-sm font-extrabold" x-text="durationText"></strong>
                            </span>
                        </div>
                        <span class="font-bold px-2.5 py-0.5 rounded-full text-[11px]"
                              :class="durationError ? 'bg-rose-200 text-rose-900' : 'bg-[#424dc4] text-white'">
                            <span x-text="durationError ? 'Tidak Valid' : 'Sesuai Aturan'"></span>
                        </span>
                    </div>
                </div>

                <!-- 3. Tujuan Penggunaan & Software Khusus -->
                <div class="bg-white rounded-2xl border border-[#e2e8f0] p-5 sm:p-6 elevation-1 space-y-4">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                        <div class="flex items-center gap-2.5">
                            <span class="w-6 h-6 rounded-lg bg-[#eff4ff] text-[#424dc4] font-bold text-xs flex items-center justify-center">3</span>
                            <h3 class="text-sm font-bold text-[#0b1c30]">Tujuan Penggunaan & Kebutuhan Software</h3>
                        </div>
                    </div>

                    <div>
                        <label for="purpose" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            Keperluan Reservasi <span class="text-rose-500">*</span>
                        </label>
                        <select id="purpose" 
                                name="purpose" 
                                required 
                                class="w-full px-3.5 py-2.5 bg-white border border-slate-300 rounded-xl text-sm font-medium text-slate-800 focus:outline-none focus:border-[#424dc4] focus:ring-2 focus:ring-[#424dc4]/20 transition-all cursor-pointer">
                            <option value="">-- Pilih Tujuan Penggunaan --</option>
                            <option value="Pengerjaan Skripsi / Tugas Akhir" {{ old('purpose') == 'Pengerjaan Skripsi / Tugas Akhir' ? 'selected' : '' }}>
                                Pengerjaan Skripsi / Tugas Akhir
                            </option>
                            <option value="Proyek Sains Data & Machine Learning" {{ old('purpose') == 'Proyek Sains Data & Machine Learning' ? 'selected' : '' }}>
                                Proyek Sains Data & Machine Learning (Model Training/Analysis)
                            </option>
                            <option value="Praktikum Mandiri & Coding Tugas Kuliah" {{ old('purpose') == 'Praktikum Mandiri & Coding Tugas Kuliah' ? 'selected' : '' }}>
                                Praktikum Mandiri & Coding Tugas Kuliah
                            </option>
                            <option value="Penelitian AI / Deep Learning GPU CUDA" {{ old('purpose') == 'Penelitian AI / Deep Learning GPU CUDA' ? 'selected' : '' }}>
                                Penelitian AI / Deep Learning Akselerasi GPU CUDA
                            </option>
                            <option value="Pengolahan Big Data & Analisis Statistik" {{ old('purpose') == 'Pengolahan Big Data & Analisis Statistik' ? 'selected' : '' }}>
                                Pengolahan Big Data & Analisis Statistik
                            </option>
                            <option value="Lainnya" {{ old('purpose') == 'Lainnya' ? 'selected' : '' }}>
                                Lainnya
                            </option>
                        </select>
                    </div>

                    <div>
                        <label for="notes" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            Catatan Kebutuhan Software / Library GPU <span class="text-slate-400 font-normal lowercase">(opsional)</span>
                        </label>
                        <textarea id="notes" 
                                  name="notes" 
                                  rows="2" 
                                  placeholder="Contoh: Butuh akses Jupyter Notebook, PyTorch CUDA 12, TensorFlow, atau software RStudio."
                                  class="w-full px-3.5 py-2.5 bg-white border border-slate-300 rounded-xl text-sm font-medium text-slate-800 placeholder-slate-400 focus:outline-none focus:border-[#424dc4] focus:ring-2 focus:ring-[#424dc4]/20 transition-all">{{ old('notes') }}</textarea>
                    </div>
                </div>

            </div>


            <!-- RIGHT COLUMN: Profil Mahasiswa & Ringkasan Live (4 of 12 Cols) -->
            <div class="lg:col-span-4 space-y-5 sticky top-24">

                <!-- Card 1: Data Identitas Mahasiswa (Otomatis dari DB) -->
                <div class="bg-white rounded-2xl border border-[#e2e8f0] p-5 elevation-1">
                    <div class="flex items-center justify-between pb-3 mb-3 border-b border-slate-100">
                        <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Data Pemohon</span>
                        <span class="inline-flex items-center gap-1 text-[11px] font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-200">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Terisi Otomatis
                        </span>
                    </div>

                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-11 h-11 rounded-full bg-gradient-to-tr from-[#424dc4] to-[#6063ee] text-white font-extrabold text-sm flex items-center justify-center shrink-0 shadow-xs">
                            {{ strtoupper(substr($user->name ?? 'MS', 0, 2)) }}
                        </div>
                        <div class="min-w-0">
                            <h4 class="text-sm font-bold text-[#0b1c30] truncate">{{ $user->name }}</h4>
                            <p class="text-xs font-mono text-[#424dc4] font-semibold">{{ $user->nim }}</p>
                        </div>
                    </div>

                    <div class="bg-slate-50 rounded-xl p-3 text-xs space-y-2 border border-slate-200/60">
                        <div class="flex justify-between items-center">
                            <span class="text-slate-500">Jurusan:</span>
                            <span class="font-bold text-slate-800">{{ $user->jurusan ?? 'Teknik Informatika' }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-slate-500">Fakultas:</span>
                            <span class="font-semibold text-slate-700">Sains & Teknologi</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-slate-500">Peran:</span>
                            <span class="font-bold text-[#424dc4] uppercase text-[10px] bg-[#eff4ff] px-2 py-0.5 rounded-md">Mahasiswa</span>
                        </div>
                    </div>
                </div>

                <!-- Card 2: Ringkasan Pengajuan (Live Dynamic Summary) -->
                <div class="bg-white rounded-2xl border-2 border-[#5c67de]/40 p-5 elevation-2 space-y-4">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                        <h4 class="text-sm font-bold text-[#0b1c30]">Ringkasan Pengajuan</h4>
                        <span class="text-[11px] font-mono font-bold text-[#424dc4] bg-[#eff4ff] px-2 py-0.5 rounded-md" x-text="selectedComputerCode">
                            {{ $computers->first()->computer_code ?? 'PC-01' }}
                        </span>
                    </div>

                    <div class="space-y-2.5 text-xs">
                        <div>
                            <span class="text-slate-400 block text-[11px]">Komputer Workstation</span>
                            <span class="font-bold text-[#0b1c30] text-sm" x-text="selectedComputerName">
                                {{ $computers->first()->computer_name ?? 'Workstation AI' }}
                            </span>
                        </div>

                        <div>
                            <span class="text-slate-400 block text-[11px]">Tanggal Penggunaan</span>
                            <span class="font-semibold text-slate-800" x-text="reservationDate ? reservationDate : 'Belum dipilih'"></span>
                        </div>

                        <div class="flex justify-between items-center pt-1 border-t border-slate-100">
                            <div>
                                <span class="text-slate-400 block text-[11px]">Rentang Waktu</span>
                                <span class="font-bold text-slate-800" x-text="(startTime && endTime) ? (startTime + ' - ' + endTime + ' WIB') : 'Pilih jam'"></span>
                            </div>
                            <div class="text-right">
                                <span class="text-slate-400 block text-[11px]">Durasi</span>
                                <span class="font-bold text-[#424dc4]" x-text="durationMinutes > 0 ? durationText : '-'"></span>
                            </div>
                        </div>
                    </div>

                    <!-- Tombol Aksi Submit & Batal -->
                    <div class="pt-2 space-y-2">
                        <button type="submit" 
                                :disabled="durationError || isWeekendSelected || !startTime || !endTime"
                                class="w-full py-3 px-4 rounded-xl bg-gradient-to-r from-[#424dc4] to-[#6063ee] hover:from-[#363ea3] hover:to-[#5053cc] disabled:opacity-50 disabled:cursor-not-allowed text-white font-bold text-sm shadow-md shadow-[#424dc4]/20 hover:scale-[1.01] active:scale-[0.99] transition-all cursor-pointer">
                            Kirim Pengajuan Reservasi
                        </button>

                        <a href="{{ route('mahasiswa.dashboard') }}" 
                           class="block w-full py-2.5 text-center rounded-xl border border-slate-200 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition-all cursor-pointer">
                            Batal & Kembali ke Dashboard
                        </a>
                    </div>

                    <div class="pt-2 border-t border-slate-100 text-[11px] text-slate-400 leading-relaxed space-y-1">
                        <p class="flex items-center gap-1.5 text-slate-500 font-medium">
                            <svg class="w-3.5 h-3.5 text-[#424dc4]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Ketentuan Lab:
                        </p>
                        <p>• Persetujuan diterbitkan oleh Kepala Lab.</p>
                        <p>• Kode akses berlaku sampai 30 mnt setelah mulai.</p>
                        <p>• Sesi dapat diakhiri lebih awal (billing warnet).</p>
                    </div>
                </div>

            </div>

        </div>
    </form>

</div>

@php
    $selectedCompId = old('computer_id', request('computer_id', $computers->first()->id ?? 1));
    $selectedCompModel = $computers->firstWhere('id', $selectedCompId) ?? $computers->first();
@endphp

<script>
    function reservationForm() {
        return {
            selectedComputer: '{{ $selectedCompModel->id ?? 1 }}',
            selectedComputerCode: '{{ $selectedCompModel->computer_code ?? 'PC-01' }}',
            selectedComputerName: '{{ $selectedCompModel->computer_name ?? 'Workstation 01' }}',
            selectedComputerSpecs: '{{ $selectedCompModel->specs ?? '' }}',

            reservationDate: '{{ old('reservation_date', request('date', date('Y-m-d'))) }}',
            startTime: '{{ old('start_time', request('start_time', '08:00')) }}',
            endTime: '{{ old('end_time', request('end_time', '11:00')) }}',

            durationMinutes: 180,
            durationText: '3 Jam 0 Menit',
            durationError: false,
            isWeekendSelected: false,

            init() {
                this.validateDate();
                this.calculateDuration();
            },

            setComputer(id, code, name, specs) {
                this.selectedComputer = id;
                this.selectedComputerCode = code;
                this.selectedComputerName = name;
                this.selectedComputerSpecs = specs;
            },

            validateDate() {
                if (!this.reservationDate) return;
                const d = new Date(this.reservationDate);
                const day = d.getDay(); // 0 = Minggu, 6 = Sabtu
                this.isWeekendSelected = (day === 0 || day === 6);
            },

            calculateDuration() {
                if (!this.startTime || !this.endTime) {
                    this.durationMinutes = 0;
                    this.durationText = '';
                    this.durationError = false;
                    return;
                }

                const [startH, startM] = this.startTime.split(':').map(Number);
                const [endH, endM] = this.endTime.split(':').map(Number);

                const startTotal = (startH * 60) + startM;
                const endTotal = (endH * 60) + endM;

                if (endTotal <= startTotal) {
                    this.durationMinutes = 0;
                    this.durationText = 'Jam selesai harus lebih akhir dari mulai';
                    this.durationError = true;
                    return;
                }

                const diff = endTotal - startTotal;
                this.durationMinutes = diff;

                const hours = Math.floor(diff / 60);
                const minutes = diff % 60;
                this.durationText = `${hours} Jam ${minutes > 0 ? minutes + ' Menit' : ''}`;

                if (diff > 300) {
                    this.durationText += ' (Maksimal 5 Jam)';
                    this.durationError = true;
                } else if (diff < 30) {
                    this.durationText += ' (Minimal 30 Menit)';
                    this.durationError = true;
                } else {
                    this.durationError = false;
                }
            },

            handleSubmit(e) {
                this.validateDate();
                this.calculateDuration();

                if (this.isWeekendSelected) {
                    e.preventDefault();
                    alert('Laboratorium Data Sains hanya buka hari kerja (Senin s/d Jumat).');
                    return false;
                }

                if (this.durationError) {
                    e.preventDefault();
                    alert('Harap periksa durasi peminjaman (maksimal 5 jam dan minimal 30 menit).');
                    return false;
                }
            }
        };
    }
</script>
@endsection
