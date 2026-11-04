@extends('layouts.kepala-lab')

@section('title', 'Antrean Persetujuan - Portal Kepala Lab')
@section('page-title', 'Antrean Persetujuan')

@section('content')
    <div class="space-y-6">

        <!-- Top Header & Breadcrumbs -->
        <div class="pb-4 border-b border-[#e2e8f0]">
            <nav class="flex items-center gap-2 text-xs font-semibold text-slate-400 mb-1">
                <a href="{{ route('kepala-lab.dashboard') }}" class="hover:text-[#424dc4] transition-colors">Pusat
                    Kendali</a>
                <span>&rsaquo;</span>
                <span class="text-[#424dc4]">Antrean Persetujuan</span>
            </nav>
            <h2 class="text-xl sm:text-2xl font-extrabold text-[#0b1c30] tracking-tight">
                Antrean Persetujuan Reservasi Komputer
            </h2>
            <p class="text-xs sm:text-sm text-slate-500 mt-0.5">
                Tinjau pengajuan mahasiswa, verifikasi peruntukan riset, setujui untuk menerbitkan kode akses login PC, atau
                tolak dengan alasan yang jelas.
            </p>
        </div>

        <!-- 3-Grid Quick Metrics Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 sm:gap-4">
            <!-- Metric 1: Pending -->
            <div
                class="bg-white rounded-2xl p-4 sm:p-5 border {{ $pendingCount > 0 ? 'border-amber-300 bg-amber-50/20 ring-2 ring-amber-400/10' : 'border-[#e2e8f0]' }} shadow-xs flex items-center justify-between">
                <div class="min-w-0 pr-1">
                    <span class="text-[10px] sm:text-xs font-bold uppercase tracking-wider text-slate-500 block truncate">Menunggu Approval</span>
                    <p class="text-2xl sm:text-3xl font-black text-[#0b1c30] mt-0.5 sm:mt-1">{{ $pendingCount }}</p>
                    <span class="text-[10px] sm:text-[11px] block truncate mt-0.5 {{ $pendingCount > 0 ? 'text-amber-700 font-semibold' : 'text-slate-400' }}">
                        {{ $pendingCount > 0 ? '● Butuh persetujuan segera' : 'Antrean bersih' }}
                    </span>
                </div>
                <div
                    class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl sm:rounded-2xl {{ $pendingCount > 0 ? 'bg-amber-100 text-amber-600' : 'bg-slate-100 text-slate-500' }} flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>

            <!-- Metric 2: Approved Today -->
            <div class="bg-white rounded-2xl p-4 sm:p-5 border border-[#e2e8f0] shadow-xs flex items-center justify-between">
                <div class="min-w-0 pr-1">
                    <span class="text-[10px] sm:text-xs font-bold uppercase tracking-wider text-slate-500 block truncate">Disetujui Hari Ini</span>
                    <p class="text-2xl sm:text-3xl font-black text-emerald-600 mt-0.5 sm:mt-1">{{ $approvedTodayCount }}</p>
                    <span class="text-[10px] sm:text-[11px] text-emerald-600 font-semibold block truncate mt-0.5">Kode akses aktif diterbitkan</span>
                </div>
                <div
                    class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl sm:rounded-2xl bg-emerald-100 text-emerald-600 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>

            <!-- Metric 3: Rejected Today -->
            <div class="bg-white rounded-2xl p-4 sm:p-5 border border-[#e2e8f0] shadow-xs flex items-center justify-between">
                <div class="min-w-0 pr-1">
                    <span class="text-[10px] sm:text-xs font-bold uppercase tracking-wider text-slate-500 block truncate">Ditolak Hari Ini</span>
                    <p class="text-2xl sm:text-3xl font-black text-rose-600 mt-0.5 sm:mt-1">{{ $rejectedTodayCount }}</p>
                    <span class="text-[10px] sm:text-[11px] text-slate-400 block truncate mt-0.5">Dengan alasan resmi lab</span>
                </div>
                <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl sm:rounded-2xl bg-rose-100 text-rose-600 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </div>
            </div>
        </div>

        <!-- Navigation Tabs & Filter Toolbar -->
        <div class="bg-white rounded-2xl border border-[#e2e8f0] p-3.5 sm:p-5 shadow-xs space-y-4">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">

                <!-- Tabs (Antrean Menunggu & Riwayat) -->
                <div class="flex items-center gap-2 w-full sm:w-auto">
                    <a href="{{ route('kepala-lab.antrean', ['tab' => 'pending', 'computer_id' => $computerFilter, 'search' => $search]) }}"
                        class="flex-1 sm:flex-initial justify-center px-4 py-2 rounded-xl text-xs font-bold transition-all inline-flex items-center gap-2 {{ $tab !== 'history' ? 'bg-[#424dc4] text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>Antrean Menunggu</span>
                        <span
                            class="px-1.5 py-0.2 rounded-full text-[10px] font-extrabold {{ $tab !== 'history' ? 'bg-white/20 text-white' : 'bg-amber-100 text-amber-800' }}">
                            {{ $pendingCount }}
                        </span>
                    </a>

                    <a href="{{ route('kepala-lab.antrean', ['tab' => 'history', 'computer_id' => $computerFilter, 'search' => $search]) }}"
                        class="flex-1 sm:flex-initial justify-center px-4 py-2 rounded-xl text-xs font-bold transition-all inline-flex items-center gap-2 {{ $tab === 'history' ? 'bg-[#424dc4] text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                        <span>Riwayat Keputusan</span>
                        <span
                            class="px-1.5 py-0.2 rounded-full text-[10px] font-extrabold {{ $tab === 'history' ? 'bg-white/20 text-white' : 'bg-slate-200 text-slate-700' }}">
                            {{ $historyReservations->count() }}
                        </span>
                    </a>
                </div>

                <!-- Filter By PC & Search Bar -->
                <form method="GET" action="{{ route('kepala-lab.antrean') }}" class="flex flex-col sm:flex-row flex-wrap items-stretch sm:items-center gap-2 w-full md:w-auto">
                    <input type="hidden" name="tab" value="{{ $tab }}">

                    <!-- Filter Unit Komputer -->
                    <select name="computer_id" onchange="this.form.submit()"
                        class="text-xs px-3 py-2 rounded-xl border border-slate-200 bg-white text-slate-700 font-medium focus:outline-none focus:ring-2 focus:ring-[#424dc4]/20 focus:border-[#424dc4] cursor-pointer w-full sm:w-auto">
                        <option value="">Semua Unit PC</option>
                        @foreach($computers as $c)
                            <option value="{{ $c->id }}" {{ $computerFilter == $c->id ? 'selected' : '' }}>
                                {{ $c->computer_code }} ({{ $c->computer_name }})
                            </option>
                        @endforeach
                    </select>

                    <!-- Search Input -->
                    <div class="relative flex-1 sm:flex-initial">
                        <input type="text" name="search" value="{{ $search }}" placeholder="Cari Nama / NIM / Keperluan..."
                            class="text-xs pl-8 pr-3 py-2 rounded-xl border border-slate-200 bg-white text-slate-700 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-[#424dc4]/20 focus:border-[#424dc4] w-full sm:w-60">
                        <svg class="w-4 h-4 text-slate-400 absolute left-2.5 top-2.5" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>

                    @if($computerFilter || $search)
                        <a href="{{ route('kepala-lab.antrean', ['tab' => $tab]) }}"
                            class="px-3 py-2 rounded-xl text-xs font-bold text-rose-700 bg-rose-50 hover:bg-rose-100 border border-rose-200 transition-all shadow-2xs text-center inline-flex items-center justify-center"
                            title="Reset Filter">
                            Reset
                        </a>
                    @endif
                </form>
            </div>
        </div>

        <!-- Tab 1: Antrean Menunggu Persetujuan (Pending Approval) -->
        @if($tab !== 'history')
            @if($pendingReservations->isEmpty())
                <div class="bg-white rounded-3xl border border-[#e2e8f0] p-12 text-center shadow-xs">
                    <div
                        class="w-16 h-16 rounded-full bg-emerald-50 text-emerald-600 mx-auto flex items-center justify-center mb-3">
                        <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                    <h4 class="text-base font-bold text-slate-800">Tidak Ada Antrean Persetujuan</h4>
                    <p class="text-xs text-slate-500 mt-1 max-w-sm mx-auto">
                        {{ $search || $computerFilter ? 'Tidak ada permohonan reservasi yang sesuai dengan filter pencarian Anda.' : 'Semua permohonan reservasi komputer dari mahasiswa telah diproses dengan baik.' }}
                    </p>
                    @if($search || $computerFilter)
                        <div class="mt-4">
                            <a href="{{ route('kepala-lab.antrean') }}"
                                class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-[#eff4ff] hover:bg-[#424dc4] text-[#424dc4] hover:text-white border border-[#dce9ff] text-xs font-bold transition-all shadow-xs">
                                <span>Tampilkan Semua Antrean</span>
                            </a>
                        </div>
                    @endif
                </div>
            @else
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6">
                    @foreach($pendingReservations as $res)
                        <div
                            class="p-5 sm:p-6 rounded-2xl border border-amber-200/80 bg-gradient-to-br from-amber-50/30 via-white to-white shadow-xs space-y-4 hover:border-amber-300 transition-all">

                            <!-- Header Kartu -->
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="w-11 h-11 rounded-2xl bg-[#eff4ff] text-[#424dc4] font-bold text-xs flex items-center justify-center shrink-0 border border-[#dce9ff]">
                                        {{ strtoupper(substr($res->user->name ?? 'MS', 0, 2)) }}
                                    </div>
                                    <div>
                                        <h4 class="text-sm font-bold text-slate-900">{{ $res->user->name }}</h4>
                                        <p class="text-xs text-slate-500 font-mono">
                                            NIM: {{ $res->user->nim }} • {{ $res->user->jurusan ?? 'Teknik Informatika' }}
                                        </p>
                                    </div>
                                </div>
                                <span
                                    class="px-2.5 py-1 rounded-xl text-xs font-black font-mono bg-[#424dc4] text-white shrink-0 shadow-xs">
                                    {{ $res->computer->computer_code }}
                                </span>
                            </div>

                            <!-- Detail Jadwal & Keperluan -->
                            <div class="grid grid-cols-2 gap-2.5 p-3.5 rounded-xl bg-slate-50 border border-slate-100 text-xs">
                                <div>
                                    <span class="text-slate-400 block text-[10px] uppercase font-bold tracking-wider">Tanggal
                                        Reservasi</span>
                                    <span class="font-bold text-slate-800">
                                        {{ \Carbon\Carbon::parse($res->reservation_date)->isoFormat('dddd, D MMM Y') }}
                                    </span>
                                </div>
                                <div>
                                    <span class="text-slate-400 block text-[10px] uppercase font-bold tracking-wider">Jam
                                        Operasional</span>
                                    <span class="font-bold text-indigo-600 font-mono">
                                        {{ substr($res->start_time, 0, 5) }} – {{ substr($res->end_time, 0, 5) }} WIB
                                    </span>
                                </div>
                                <div class="col-span-2 pt-2 border-t border-slate-200/60">
                                    <span class="text-slate-400 block text-[10px] uppercase font-bold tracking-wider">Keperluan Riset /
                                        Praktikum</span>
                                    <span class="font-medium text-slate-800 leading-relaxed">{{ $res->purpose }}</span>
                                </div>
                                @if($res->notes)
                                    <div class="col-span-2 pt-1">
                                        <span class="text-slate-400 block text-[10px] uppercase font-bold tracking-wider">Kebutuhan Software
                                            / Catatan</span>
                                        <span class="text-slate-600 italic">{{ $res->notes }}</span>
                                    </div>
                                @endif
                            </div>

                            <!-- Footer & Aksi Approval -->
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pt-1 border-t border-slate-100">
                                <span class="text-[11px] text-slate-400">
                                    Diajukan {{ $res->created_at ? $res->created_at->diffForHumans() : 'baru saja' }}
                                </span>

                                <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 w-full sm:w-auto">
                                    <!-- Tombol Tolak (Buka Modal) -->
                                    <button type="button"
                                        @click="rejectReservationId = {{ $res->id }}; rejectStudentName = '{{ addslashes($res->user->name) }}'; rejectModalOpen = true;"
                                        class="w-full sm:w-auto text-center px-4 py-2 rounded-xl text-xs font-semibold text-rose-700 bg-rose-50 hover:bg-rose-100 border border-rose-200 transition-colors cursor-pointer">
                                        Tolak Permohonan
                                    </button>

                                    <!-- Tombol Setujui -->
                                    <form method="POST" action="{{ route('kepala-lab.reservasi.approve', $res) }}" class="w-full sm:w-auto">
                                        @csrf
                                        <button type="submit"
                                            class="w-full sm:w-auto inline-flex items-center justify-center gap-1.5 px-4 py-2 rounded-xl text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 shadow-xs transition-all cursor-pointer">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                            </svg>
                                            <span>Setujui & Terbitkan Kode</span>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        @else
            <!-- Tab 2: Riwayat Keputusan Terbaru -->
            <div class="bg-white rounded-3xl border border-[#e2e8f0] shadow-xs overflow-hidden">
                <div class="p-5 sm:p-6 border-b border-slate-100">
                    <h3 class="text-base font-bold text-[#0b1c30]">Riwayat Keputusan & Aktivitas Reservasi</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Daftar permohonan yang telah disetujui, ditolak, atau diselesaikan.
                    </p>
                </div>

                @if($historyReservations->isEmpty())
                    <div class="p-12 text-center text-slate-400">
                        <p class="text-xs font-bold text-slate-600">Belum ada riwayat keputusan</p>
                        <p class="text-[11px] text-slate-400 mt-0.5">Riwayat keputusan persetujuan akan dicatat di sini.</p>
                    </div>
                @else
                    <!-- Mobile Table Scroll Hint -->
                    <div class="sm:hidden px-4 py-2 bg-slate-50 border-b border-slate-100 flex items-center justify-between text-[11px] text-slate-500 font-medium">
                        <span class="flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-[#424dc4]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                            </svg>
                            Geser tabel ke samping untuk melihat detail
                        </span>
                        <span class="text-[10px] text-slate-400 font-mono">Riwayat</span>
                    </div>

                    <div class="overflow-x-auto custom-scrollbar">
                        <table class="w-full text-left border-collapse min-w-[700px]">
                            <thead>
                                <tr
                                    class="bg-[#f8f9ff] border-b border-[#e2e8f0] text-slate-500 text-[11px] font-bold uppercase tracking-wider">
                                    <th class="py-3 px-5">Mahasiswa</th>
                                    <th class="py-3 px-4">Komputer</th>
                                    <th class="py-3 px-4">Jadwal Reservasi</th>
                                    <th class="py-3 px-4">Keperluan</th>
                                    <th class="py-3 px-4">Status & Hasil</th>
                                    <th class="py-3 px-4 text-right">Waktu</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 text-xs">
                                @foreach($historyReservations as $h)
                                    <tr class="hover:bg-slate-50/60 transition-colors">
                                        <td class="py-3.5 px-5">
                                            <p class="font-bold text-[#0b1c30]">{{ $h->user->name ?? 'Mahasiswa' }}</p>
                                            <p class="text-[11px] text-slate-400 font-mono">{{ $h->user->nim ?? '-' }}</p>
                                        </td>
                                        <td class="py-3.5 px-4">
                                            <span
                                                class="px-2 py-0.5 rounded-md font-mono text-xs font-bold bg-[#eff4ff] text-[#424dc4]">
                                                {{ $h->computer->computer_code ?? 'PC' }}
                                            </span>
                                        </td>
                                        <td class="py-3.5 px-4">
                                            <p class="font-semibold text-slate-800">
                                                {{ \Carbon\Carbon::parse($h->reservation_date)->translatedFormat('d M Y') }}</p>
                                            <p class="text-[11px] text-slate-500 font-mono">{{ substr($h->start_time, 0, 5) }} -
                                                {{ substr($h->end_time, 0, 5) }}</p>
                                        </td>
                                        <td class="py-3.5 px-4 max-w-[200px] truncate" title="{{ $h->purpose }}">
                                            {{ $h->purpose }}
                                        </td>
                                        <td class="py-3.5 px-4">
                                            @if($h->status === 'APPROVED')
                                                <span
                                                    class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                                    Disetujui
                                                </span>
                                                <span class="block text-[10px] text-slate-500 mt-0.5">
                                                    Oleh: <span class="font-semibold text-slate-700">{{ $h->approver->name ?? 'Kepala Lab' }}</span>
                                                </span>
                                                @if($h->accessCode)
                                                    <span class="block text-[10px] font-mono text-emerald-700 mt-0.5 font-bold">
                                                        {{ $h->accessCode->code }}
                                                    </span>
                                                @endif
                                            @elseif($h->status === 'REJECTED')
                                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800">
                                                    Ditolak
                                                </span>
                                                <span class="block text-[10px] text-slate-500 mt-0.5">
                                                    Oleh: <span class="font-semibold text-slate-700">{{ $h->approver->name ?? 'Kepala Lab' }}</span>
                                                </span>
                                                @if($h->rejection_reason)
                                                    <span class="block text-[10px] text-rose-600 italic mt-0.5 truncate max-w-[150px]"
                                                        title="{{ $h->rejection_reason }}">
                                                        {{ $h->rejection_reason }}
                                                    </span>
                                                @endif
                                            @elseif($h->status === 'CHECKED_IN')
                                                <span
                                                    class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-indigo-100 text-indigo-800">
                                                    Sesi Aktif
                                                </span>
                                            @else
                                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700">
                                                    {{ $h->status }}
                                                </span>
                                            @endif
                                        </td>
                                        <td class="py-3.5 px-4 text-right text-[11px] text-slate-400 whitespace-nowrap">
                                            {{ $h->updated_at ? $h->updated_at->diffForHumans() : '-' }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        @endif

    </div>
@endsection