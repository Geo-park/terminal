<!DOCTYPE html>
<html lang="id" class="h-full bg-[#f8f9ff]">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Portal Kepala Lab') - TERMINAL</title>

    <!-- Favicon (Tab Icon) -->
    <link rel="icon" type="image/png" href="{{ asset('images/logo-informatika.png') }}">
    <link rel="shortcut icon" type="image/png" href="{{ asset('images/logo-informatika.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/logo-informatika.png') }}">

    <!-- Google Fonts: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400&display=swap"
        rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body {
            font-family: 'Plus Jakarta Sans', ui-sans-serif, system-ui, sans-serif;
            color: #0b1c30;
        }

        .custom-scrollbar::-webkit-scrollbar {
            width: 4px;
        }

        .custom-scrollbar::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 9999px;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 9999px;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
    </style>
    @stack('styles')
</head>

<body class="h-full antialiased selection:bg-[#e0e7ff] selection:text-[#424dc4]" x-data="{
        miniSidebar: false,
        mobileMenuOpen: false,
        profileMenuOpen: false,
        rejectModalOpen: false,
        rejectReservationId: null,
        rejectStudentName: '',
        maintenanceModalOpen: false,
        maintComputerId: null,
        maintComputerCode: '',
        maintComputerName: '',
        maintDurationType: 'hours',
        maintDurationValue: 2,
        maintCustomDate: '{{ date('Y-m-d') }}',
        maintCustomTime: '16:00',
        maintReason: 'Perawatan hardware & update software sistem'
    }">

    <div class="min-h-full flex flex-col lg:flex-row">

        <!-- Mobile Backdrop -->
        <div x-show="mobileMenuOpen" x-transition:enter="transition-opacity ease-linear duration-300"
            x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
            x-transition:leave="transition-opacity ease-linear duration-300" x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0" class="fixed inset-0 z-40 bg-slate-900/50 backdrop-blur-xs lg:hidden"
            @click="mobileMenuOpen = false" style="display: none;"></div>

        <!-- Sidebar Navigation (Collapsible to Mini Icon-Only on Click) -->
        <aside
            class="fixed inset-y-0 left-0 z-50 bg-white border-r border-[#e2e8f0] flex flex-col justify-between transition-all duration-300 ease-in-out lg:translate-x-0"
            :class="{
                'w-[280px] lg:w-[260px]': !miniSidebar,
                'w-[280px] lg:w-[76px]': miniSidebar,
                'translate-x-0': mobileMenuOpen,
                '-translate-x-full lg:translate-x-0': !mobileMenuOpen
            }">

            <div class="flex flex-col h-full">
                <!-- Brand Header dengan Tombol Icon yang Bisa Diklik -->
                <div class="h-16 sm:h-20 flex items-center border-b border-[#f1f5f9] transition-all duration-300 px-5 lg:px-6"
                    :class="miniSidebar ? 'lg:justify-center lg:px-2' : 'justify-between'">

                    <div class="flex items-center gap-3">
                        <!-- Logo Informatika (Klik untuk buka/tutup teks sidebar) -->
                        <button type="button" @click="miniSidebar = !miniSidebar"
                            title="Klik untuk membuka / menutup teks sidebar"
                            class="w-10 h-10 flex items-center justify-center hover:scale-105 active:scale-95 transition-transform cursor-pointer shrink-0 focus:outline-none border-0 outline-none">
                            <img src="{{ asset('images/logo-informatika.png') }}" alt="Logo Informatika"
                                class="w-9 h-9 object-contain select-none pointer-events-none border-0 outline-none">
                        </button>

                        <!-- Teks Brand (Otomatis sembunyi saat sidebar mengecil jadi icon doang) -->
                        <div :class="miniSidebar ? 'lg:hidden' : 'block'"
                            class="overflow-hidden whitespace-nowrap">
                            <span class="block text-base font-black tracking-tight text-[#0b1c30]">TERMINAL</span>
                            <span class="block text-[11px] font-semibold text-[#424dc4] tracking-wide uppercase">Kepala Laboratorium</span>
                        </div>
                    </div>

                    <!-- Tombol Tutup Mobile -->
                    <button class="lg:hidden p-1.5 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 cursor-pointer"
                        @click="mobileMenuOpen = false">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Nav Menu Items -->
                <nav class="flex-1 py-5 sm:py-6 space-y-1.5 overflow-y-auto custom-scrollbar transition-all duration-300"
                    :class="miniSidebar ? 'px-3 lg:px-2' : 'px-4'">
                    <p :class="miniSidebar ? 'lg:hidden' : 'block'"
                        class="px-3 pb-2 text-[11px] font-bold uppercase tracking-wider text-slate-400 whitespace-nowrap">
                        Pusat Kendali Lab</p>

                    <!-- Dashboard / Pusat Kendali -->
                    @php
                        $isDashboardActive = request()->routeIs('kepala-lab.dashboard');
                    @endphp
                    <a href="{{ route('kepala-lab.dashboard') }}" title="Pusat Kendali Lab"
                        class="relative flex items-center rounded-xl text-sm font-semibold transition-all duration-200 group border {{ $isDashboardActive ? 'text-[#424dc4] bg-[#eff4ff] border-[#dce9ff] shadow-xs font-bold hover:bg-[#e5edff]' : 'text-[#64748b] border-transparent hover:text-[#424dc4] hover:bg-[#f8faff] hover:border-slate-200/80 hover:translate-x-1' }}"
                        :class="miniSidebar ? 'gap-3 px-3.5 py-3 lg:justify-center lg:p-3' : 'gap-3 px-3.5 py-3'">
                        @if($isDashboardActive)
                            <span class="active-indicator absolute left-0 top-1.5 bottom-1.5 w-1.5 bg-[#424dc4] rounded-r-full shadow-sm shadow-[#424dc4]/40"></span>
                        @else
                            <span class="opacity-0 group-hover:opacity-40 transition-opacity absolute left-0 top-2 bottom-2 w-1 bg-[#424dc4] rounded-r-full"></span>
                        @endif
                        <svg class="w-5 h-5 shrink-0 transition-all duration-200 {{ $isDashboardActive ? 'text-[#424dc4]' : 'text-slate-400 group-hover:text-[#424dc4] group-hover:scale-110' }}"
                            fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                        </svg>
                        <span :class="miniSidebar ? 'lg:hidden' : 'block'" class="whitespace-nowrap">Pusat Kendali</span>
                        @if($isDashboardActive)
                            <span :class="miniSidebar ? 'lg:hidden' : 'flex'" class="ml-auto items-center gap-1.5" title="Halaman Aktif">
                                <span class="w-1.5 h-1.5 rounded-full bg-[#424dc4] ring-4 ring-[#424dc4]/15"></span>
                            </span>
                        @endif
                    </a>

                    <!-- Antrean Approval -->
                    @php
                        $isAntreanActive = request()->routeIs('kepala-lab.antrean*');
                    @endphp
                    <a href="{{ route('kepala-lab.antrean') }}" title="Antrean Persetujuan"
                        class="relative flex items-center rounded-xl text-sm font-semibold transition-all duration-200 group border {{ $isAntreanActive ? 'text-[#424dc4] bg-[#eff4ff] border-[#dce9ff] shadow-xs font-bold hover:bg-[#e5edff]' : 'text-[#64748b] border-transparent hover:text-[#424dc4] hover:bg-[#f8faff] hover:border-slate-200/80 hover:translate-x-1' }}"
                        :class="miniSidebar ? 'gap-3 px-3.5 py-3 lg:justify-center lg:p-3' : 'gap-3 px-3.5 py-3'">
                        @if($isAntreanActive)
                            <span class="active-indicator absolute left-0 top-1.5 bottom-1.5 w-1.5 bg-[#424dc4] rounded-r-full shadow-sm shadow-[#424dc4]/40"></span>
                        @else
                            <span class="opacity-0 group-hover:opacity-40 transition-opacity absolute left-0 top-2 bottom-2 w-1 bg-[#424dc4] rounded-r-full"></span>
                        @endif
                        <svg class="w-5 h-5 shrink-0 transition-all duration-200 {{ $isAntreanActive ? 'text-[#424dc4]' : 'text-slate-400 group-hover:text-[#424dc4] group-hover:scale-110' }}" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span :class="miniSidebar ? 'lg:hidden' : 'block'" class="whitespace-nowrap">Antrean Approval</span>
                        @if(($pendingCount ?? 0) > 0)
                            <span :class="miniSidebar ? 'lg:hidden' : 'inline-flex'"
                                class="ml-auto text-[10px] font-bold px-2 py-0.5 rounded-full transition-colors whitespace-nowrap {{ $isAntreanActive ? 'bg-amber-500 text-white shadow-xs' : 'bg-amber-50 text-amber-700 border border-amber-200 group-hover:bg-amber-100' }}">
                                {{ $pendingCount }} Menunggu
                            </span>
                        @elseif($isAntreanActive)
                            <span :class="miniSidebar ? 'lg:hidden' : 'flex'" class="ml-auto items-center gap-1.5" title="Halaman Aktif">
                                <span class="w-1.5 h-1.5 rounded-full bg-[#424dc4] ring-4 ring-[#424dc4]/15"></span>
                            </span>
                        @endif
                    </a>

                    <!-- Buku Booking Jadwal Lab -->
                    @php
                        $isBukuBookingActive = request()->routeIs('kepala-lab.buku-booking*');
                    @endphp
                    <a href="{{ route('kepala-lab.buku-booking') }}" title="Lihat Buku Booking"
                        class="relative flex items-center rounded-xl text-sm font-semibold transition-all duration-200 group border {{ $isBukuBookingActive ? 'text-[#424dc4] bg-[#eff4ff] border-[#dce9ff] shadow-xs font-bold hover:bg-[#e5edff]' : 'text-[#64748b] border-transparent hover:text-[#424dc4] hover:bg-[#f8faff] hover:border-slate-200/80 hover:translate-x-1' }}"
                        :class="miniSidebar ? 'gap-3 px-3.5 py-3 lg:justify-center lg:p-3' : 'gap-3 px-3.5 py-3'">
                        @if($isBukuBookingActive)
                            <span class="active-indicator absolute left-0 top-1.5 bottom-1.5 w-1.5 bg-[#424dc4] rounded-r-full shadow-sm shadow-[#424dc4]/40"></span>
                        @else
                            <span class="opacity-0 group-hover:opacity-40 transition-opacity absolute left-0 top-2 bottom-2 w-1 bg-[#424dc4] rounded-r-full"></span>
                        @endif
                        <svg class="w-5 h-5 shrink-0 transition-all duration-200 {{ $isBukuBookingActive ? 'text-[#424dc4]' : 'text-slate-400 group-hover:text-[#424dc4] group-hover:scale-110' }}" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                        <span :class="miniSidebar ? 'lg:hidden' : 'block'" class="whitespace-nowrap">Buku Booking</span>
                        <span :class="miniSidebar ? 'lg:hidden' : 'inline-flex'"
                            class="ml-auto text-[10px] font-bold px-2 py-0.5 rounded-full transition-colors whitespace-nowrap {{ $isBukuBookingActive ? 'bg-emerald-600 text-white shadow-xs' : 'bg-emerald-50 text-emerald-700 border border-emerald-200 group-hover:bg-emerald-600 group-hover:text-white' }}">
                            Live
                        </span>
                    </a>

                    <!-- Status Operasional Lab (Hanya muncul saat sidebar lebar) -->
                    <div :class="miniSidebar ? 'lg:hidden' : 'block'" class="pt-4 pb-2">
                        <p class="px-3 pb-2 text-[11px] font-bold uppercase tracking-wider text-slate-400">Status Operasional</p>
                        <div class="px-3.5 py-3 rounded-xl bg-[#eff4ff] border border-[#dce9ff] text-xs space-y-1.5">
                            <div class="flex items-center justify-between text-[#424dc4] font-semibold">
                                <span>Sistem Laboratorium</span>
                                <span class="px-1.5 py-0.5 rounded-sm bg-emerald-100 text-emerald-700 text-[10px] font-bold">AKTIF</span>
                            </div>
                            <p class="text-slate-600 font-medium">Jam Operasional:<br><span class="text-[#0b1c30] font-bold">07.00 – 16.00 WIB</span></p>
                            <p class="text-[11px] text-slate-500 pt-1 border-t border-[#dce9ff]/70">4 Workstation • Auto Kode Akses</p>
                        </div>
                    </div>
                </nav>
            </div>
        </aside>

        <!-- Main Wrapper (Lebar menyesuaikan miniSidebar) -->
        <div class="flex-1 flex flex-col min-w-0 transition-all duration-300 ease-in-out"
            :class="miniSidebar ? 'lg:pl-[76px]' : 'lg:pl-[260px]'">

            <!-- Top Header Navbar -->
            <header
                class="h-16 sm:h-20 bg-white border-b border-[#e2e8f0] sticky top-0 z-30 px-3.5 sm:px-6 lg:px-8 flex items-center justify-between gap-2 sm:gap-4">

                <!-- Left: Mobile Menu Toggle & Title -->
                <div class="flex items-center gap-2.5 sm:gap-4 min-w-0">
                    <button type="button" @click="mobileMenuOpen = true"
                        class="p-1.5 sm:p-2 -ml-1 sm:-ml-2 rounded-xl text-slate-600 hover:bg-slate-100 lg:hidden cursor-pointer shrink-0"
                        title="Buka Menu">
                        <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                    <div class="min-w-0">
                        <div class="flex items-center gap-2">
                            <h1 class="text-sm sm:text-base lg:text-xl font-bold text-[#0b1c30] tracking-tight truncate">
                                Portal Kepala Laboratorium
                            </h1>
                            @if(request()->routeIs('kepala-lab.antrean*'))
                                <span class="hidden md:inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold bg-[#eff4ff] text-[#424dc4] border border-[#dce9ff] shrink-0">
                                    <span class="w-1.5 h-1.5 rounded-full bg-[#424dc4]"></span>
                                    Antrean Approval
                                </span>
                            @elseif(request()->routeIs('kepala-lab.buku-booking*'))
                                <span class="hidden md:inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 shrink-0">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                    Buku Booking Lab
                                </span>
                            @else
                                <span class="hidden md:inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold bg-[#eff4ff] text-[#424dc4] border border-[#dce9ff] shrink-0">
                                    <span class="w-1.5 h-1.5 rounded-full bg-[#424dc4]"></span>
                                    Pusat Kendali
                                </span>
                            @endif
                        </div>
                        <p class="hidden sm:block text-[11px] lg:text-xs text-slate-500 truncate">
                            Laboratorium Komputer Sains Data & Artificial Intelligence
                        </p>
                    </div>
                </div>

                <!-- Right: Quick Status & Profile Flyout Dropdown -->
                <div class="flex items-center gap-2 sm:gap-4 shrink-0">
                    @if(($pendingCount ?? 0) > 0)
                        <a href="{{ route('kepala-lab.antrean') }}"
                            class="hidden sm:flex items-center gap-2 px-3 py-1.5 rounded-full bg-amber-50 border border-amber-200 text-xs text-amber-800 hover:bg-amber-100 transition-colors">
                            <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                            <span class="font-medium">Persetujuan:</span>
                            <span class="font-bold">{{ $pendingCount }} Pending</span>
                        </a>
                    @endif

                    <!-- Profile Dropdown (Sesuai Struktur Portal Mahasiswa) -->
                    <div class="relative" @click.away="profileMenuOpen = false">
                        <button @click="profileMenuOpen = !profileMenuOpen"
                            type="button"
                            class="flex items-center gap-2 sm:gap-3 p-1 sm:p-1.5 rounded-xl hover:bg-slate-50 border border-transparent hover:border-[#e2e8f0] transition-all cursor-pointer">
                            <div
                                class="w-8 h-8 sm:w-9 sm:h-9 rounded-full bg-gradient-to-tr from-[#424dc4] to-[#6063ee] text-white font-bold text-xs flex items-center justify-center shadow-xs shrink-0">
                                KL
                            </div>
                            <div class="hidden md:block text-left pr-1">
                                <span class="block text-xs font-bold text-[#0b1c30]">
                                    {{ auth()->user()->role === 'kepala_lab' ? 'Kepala Laboratorium' : (auth()->user()->name ?? 'Kepala Laboratorium') }}
                                </span>
                                <span class="block text-[11px] text-[#5c67de] font-semibold">
                                    Otoritas Lab
                                </span>
                            </div>
                            <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4 text-slate-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                        <!-- Dropdown Menu -->
                        <div x-show="profileMenuOpen" x-transition:enter="transition ease-out duration-150"
                            x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                            x-transition:leave="transition ease-in duration-100"
                            x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                            class="absolute right-0 mt-2 w-64 max-w-[calc(100vw-1.5rem)] bg-white rounded-2xl shadow-xl border border-[#e2e8f0] py-2 z-50 elevation-3"
                            style="display: none;">

                            <div class="px-4 py-2.5 border-b border-slate-100">
                                <p class="text-xs font-bold text-[#0b1c30]">{{ auth()->user()->role === 'kepala_lab' ? 'Kepala Laboratorium' : (auth()->user()->name ?? 'Kepala Laboratorium') }}</p>
                                <p class="text-[11px] text-slate-500 font-medium">Akun Otoritas & Administrator</p>
                                <p class="text-[11px] text-[#5c67de] font-medium pt-0.5">Laboratorium Sains Data & AI</p>
                            </div>

                            <a href="{{ route('kepala-lab.buku-booking') }}"
                                class="w-full flex items-center gap-2.5 px-4 py-2.5 text-xs font-semibold text-slate-700 hover:bg-[#eff4ff] hover:text-[#424dc4] transition-colors cursor-pointer text-left">
                                <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                </svg>
                                Buku Booking Lab
                            </a>

                            <div class="border-t border-slate-100 my-1"></div>

                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit"
                                    class="w-full flex items-center gap-2.5 px-4 py-2 text-xs font-semibold text-rose-600 hover:bg-rose-50 transition-colors cursor-pointer">
                                    <svg class="w-4 h-4 text-rose-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                    </svg>
                                    Keluar (Logout)
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Main Content Container -->
            <main class="flex-1 p-3.5 sm:p-6 lg:p-8 max-w-7xl 2xl:max-w-[1536px] w-full mx-auto space-y-6 pb-24 lg:pb-8">

                <!-- Alert Sukses Flash -->
                @if (session('success'))
                    <div
                        class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs sm:text-sm font-semibold flex items-center gap-3 shadow-xs">
                        <svg class="w-5 h-5 shrink-0 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                            stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>{{ session('success') }}</span>
                    </div>
                @endif

                <!-- Alert Error Flash -->
                @if (session('error') || $errors->any())
                    <div
                        class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs sm:text-sm font-semibold flex items-center gap-3 shadow-xs">
                        <svg class="w-5 h-5 shrink-0 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                            stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>{{ session('error') ?? $errors->first() }}</span>
                    </div>
                @endif

                @yield('content')
            </main>

            <!-- Mobile Bottom Navigation Bar (Khusus layar di bawah lg) -->
            <nav class="lg:hidden fixed bottom-0 left-0 right-0 z-40 bg-white/95 backdrop-blur-md border-t border-[#e2e8f0] px-3 py-2 flex items-center justify-around shadow-lg">
                <!-- Pusat Kendali -->
                <a href="{{ route('kepala-lab.dashboard') }}"
                    class="flex flex-col items-center gap-1 py-1 px-3 rounded-xl transition-all {{ request()->routeIs('kepala-lab.dashboard') ? 'text-[#424dc4] font-bold' : 'text-slate-500 hover:text-[#424dc4]' }}">
                    <svg class="w-5 h-5 {{ request()->routeIs('kepala-lab.dashboard') ? 'stroke-[2.5]' : 'stroke-2' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    <span class="text-[10px] tracking-tight">Kendali</span>
                </a>

                <!-- Antrean Approval (dengan Badge bila ada pending) -->
                <a href="{{ route('kepala-lab.antrean') }}"
                    class="relative flex flex-col items-center gap-1 py-1 px-3 rounded-xl transition-all {{ request()->routeIs('kepala-lab.antrean*') ? 'text-[#424dc4] font-bold' : 'text-slate-500 hover:text-[#424dc4]' }}">
                    <div class="relative">
                        <svg class="w-5 h-5 {{ request()->routeIs('kepala-lab.antrean*') ? 'stroke-[2.5]' : 'stroke-2' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        @if(($pendingCount ?? 0) > 0)
                            <span class="absolute -top-1 -right-2.5 w-4 h-4 rounded-full bg-amber-500 text-white text-[9px] font-black flex items-center justify-center ring-2 ring-white">
                                {{ $pendingCount > 9 ? '9+' : $pendingCount }}
                            </span>
                        @endif
                    </div>
                    <span class="text-[10px] tracking-tight">Antrean</span>
                </a>

                <!-- Buku Booking -->
                <a href="{{ route('kepala-lab.buku-booking') }}"
                    class="flex flex-col items-center gap-1 py-1 px-3 rounded-xl transition-all {{ request()->routeIs('kepala-lab.buku-booking*') ? 'text-[#424dc4] font-bold' : 'text-slate-500 hover:text-[#424dc4]' }}">
                    <svg class="w-5 h-5 {{ request()->routeIs('kepala-lab.buku-booking*') ? 'stroke-[2.5]' : 'stroke-2' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                    <span class="text-[10px] tracking-tight">Booking</span>
                </a>

                <!-- Menu Drawer Button -->
                <button type="button" @click="mobileMenuOpen = true"
                    class="flex flex-col items-center gap-1 py-1 px-3 rounded-xl text-slate-500 hover:text-[#424dc4] transition-all cursor-pointer">
                    <svg class="w-5 h-5 stroke-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                    <span class="text-[10px] tracking-tight">Menu</span>
                </button>
            </nav>
        </div>
    </div>

    <!-- Modal Konfirmasi Penolakan Reservasi -->
    <div x-show="rejectModalOpen" x-transition.opacity.duration.200ms
        class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 bg-slate-900/60 backdrop-blur-xs overflow-y-auto"
        style="display: none;">
        <div class="bg-white rounded-2xl sm:rounded-3xl max-w-md w-full p-5 sm:p-6 shadow-2xl border border-slate-100 space-y-4 max-h-[90vh] overflow-y-auto my-auto"
            @click.away="rejectModalOpen = false">

            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-rose-100 text-rose-600 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-base font-bold text-slate-900">Tolak Permohonan Reservasi</h3>
                    <p class="text-xs text-slate-500" x-text="'Pemohon: ' + rejectStudentName"></p>
                </div>
            </div>

            <form method="POST" :action="'/lab/reservasi/' + rejectReservationId + '/reject'" class="space-y-4">
                @csrf
                <div>
                    <label for="rejection_reason" class="block text-xs font-bold text-slate-700 mb-1">
                        Alasan Penolakan <span class="text-rose-500">*</span>
                    </label>
                    <textarea id="rejection_reason" name="rejection_reason" rows="3" required
                        placeholder="Contoh: Jadwal bertabrakan dengan kegiatan praktikum resmi prodi, silakan pilih jam lain."
                        class="w-full text-xs rounded-xl border border-slate-300 p-3 focus:outline-none focus:border-indigo-600 focus:ring-2 focus:ring-indigo-600/20"></textarea>
                    <span class="text-[11px] text-slate-400">Alasan ini akan langsung tampil di portal mahasiswa.</span>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2">
                    <button type="button" @click="rejectModalOpen = false"
                        class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 border border-slate-200 transition-colors cursor-pointer">
                        Batal
                    </button>
                    <button type="submit"
                        class="px-4 py-2 rounded-xl text-xs font-bold bg-rose-600 hover:bg-rose-700 text-white transition-all shadow-xs cursor-pointer">
                        Konfirmasi Penolakan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Konfigurasi Durasi Maintenance Komputer (Per Jam s/d Per Hari) -->
    <div x-show="maintenanceModalOpen" x-transition.opacity.duration.200ms
        class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 bg-slate-900/60 backdrop-blur-xs overflow-y-auto"
        style="display: none;">
        <div class="bg-white rounded-2xl sm:rounded-3xl max-w-lg w-full p-5 sm:p-6 shadow-2xl border border-slate-100 space-y-4 max-h-[92vh] overflow-y-auto my-auto"
            @click.away="maintenanceModalOpen = false">

            <!-- Modal Header -->
            <div class="flex items-center gap-3">
                <div class="w-11 h-11 rounded-2xl bg-amber-100 text-amber-700 flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-base sm:text-lg font-bold text-slate-900">Matikan / Atur Maintenance Komputer</h3>
                    <p class="text-xs text-slate-500">
                        Unit: <strong class="text-amber-800 font-mono" x-text="maintComputerCode"></strong> (<span x-text="maintComputerName"></span>) — Status akan berubah menjadi <strong class="text-rose-600">MATI</strong>.
                    </p>
                </div>
            </div>

            <form method="POST" :action="'/lab/komputer/' + maintComputerId + '/toggle-maintenance'" class="space-y-4">
                @csrf

                <!-- Tab Pemilihan Kategori Durasi -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                        Pilih Mode Durasi <span class="text-amber-600">*</span>
                    </label>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                        <!-- Per Jam -->
                        <button type="button" @click="maintDurationType = 'hours'; maintDurationValue = 2"
                            class="px-3 py-2 rounded-xl text-xs font-bold transition-all text-center border cursor-pointer"
                            :class="maintDurationType === 'hours' ? 'bg-[#424dc4] text-white border-[#424dc4] shadow-xs' : 'bg-slate-50 text-slate-700 border-slate-200 hover:bg-slate-100'">
                            Per Jam
                        </button>
                        <!-- Per Hari -->
                        <button type="button" @click="maintDurationType = 'days'; maintDurationValue = 1"
                            class="px-3 py-2 rounded-xl text-xs font-bold transition-all text-center border cursor-pointer"
                            :class="maintDurationType === 'days' ? 'bg-[#424dc4] text-white border-[#424dc4] shadow-xs' : 'bg-slate-50 text-slate-700 border-slate-200 hover:bg-slate-100'">
                            Per Hari
                        </button>
                        <!-- Kustom -->
                        <button type="button" @click="maintDurationType = 'custom'"
                            class="px-3 py-2 rounded-xl text-xs font-bold transition-all text-center border cursor-pointer"
                            :class="maintDurationType === 'custom' ? 'bg-[#424dc4] text-white border-[#424dc4] shadow-xs' : 'bg-slate-50 text-slate-700 border-slate-200 hover:bg-slate-100'">
                            Kustom Selesai
                        </button>
                        <!-- Manual -->
                        <button type="button" @click="maintDurationType = 'indefinite'"
                            class="px-3 py-2 rounded-xl text-xs font-bold transition-all text-center border cursor-pointer"
                            :class="maintDurationType === 'indefinite' ? 'bg-[#424dc4] text-white border-[#424dc4] shadow-xs' : 'bg-slate-50 text-slate-700 border-slate-200 hover:bg-slate-100'">
                            Manual
                        </button>
                    </div>
                    <input type="hidden" name="duration_type" :value="maintDurationType">
                </div>

                <!-- Opsi Durasi: PER JAM -->
                <div x-show="maintDurationType === 'hours'" class="space-y-2 bg-slate-50 p-3.5 rounded-2xl border border-slate-200">
                    <label class="block text-xs font-semibold text-slate-700">Pilih Berapa Jam Pemeliharaan:</label>
                    <div class="grid grid-cols-5 gap-2">
                        <template x-for="hr in [1, 2, 4, 8, 12]" :key="hr">
                            <button type="button" @click="maintDurationValue = hr"
                                class="py-2 px-1 rounded-xl text-xs font-bold transition-all text-center border cursor-pointer"
                                :class="maintDurationValue == hr ? 'bg-amber-600 text-white border-amber-600 shadow-xs' : 'bg-white text-slate-700 border-slate-200 hover:bg-amber-50'">
                                <span x-text="hr + ' Jam'"></span>
                            </button>
                        </template>
                    </div>
                    <div class="pt-1 flex items-center gap-2">
                        <span class="text-xs text-slate-500">Atau jumlah jam lain:</span>
                        <input type="number" min="1" max="72" x-model.number="maintDurationValue" name="duration_value"
                            class="w-20 px-2 py-1 text-xs font-bold border border-slate-300 rounded-lg text-center bg-white focus:outline-none focus:border-amber-600">
                        <span class="text-xs text-slate-500 font-semibold">Jam</span>
                    </div>
                </div>

                <!-- Opsi Durasi: PER HARI -->
                <div x-show="maintDurationType === 'days'" class="space-y-2 bg-slate-50 p-3.5 rounded-2xl border border-slate-200" style="display: none;">
                    <label class="block text-xs font-semibold text-slate-700">Pilih Berapa Hari Pemeliharaan:</label>
                    <div class="grid grid-cols-4 gap-2">
                        <template x-for="day in [1, 2, 3, 7]" :key="day">
                            <button type="button" @click="maintDurationValue = day"
                                class="py-2 px-1 rounded-xl text-xs font-bold transition-all text-center border cursor-pointer"
                                :class="maintDurationValue == day ? 'bg-amber-600 text-white border-amber-600 shadow-xs' : 'bg-white text-slate-700 border-slate-200 hover:bg-amber-50'">
                                <span x-text="day + (day === 7 ? ' Hari (1 Mgg)' : ' Hari')"></span>
                            </button>
                        </template>
                    </div>
                    <div class="pt-1 flex items-center gap-2">
                        <span class="text-xs text-slate-500">Atau jumlah hari lain:</span>
                        <input type="number" min="1" max="30" x-model.number="maintDurationValue" name="duration_value"
                            class="w-20 px-2 py-1 text-xs font-bold border border-slate-300 rounded-lg text-center bg-white focus:outline-none focus:border-amber-600">
                        <span class="text-xs text-slate-500 font-semibold">Hari</span>
                    </div>
                </div>

                <!-- Opsi Durasi: KUSTOM TANGGAL & JAM -->
                <div x-show="maintDurationType === 'custom'" class="space-y-2 bg-slate-50 p-3.5 rounded-2xl border border-slate-200" style="display: none;">
                    <label class="block text-xs font-semibold text-slate-700">Tentukan Tanggal & Jam Selesai Pemeliharaan:</label>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                        <div>
                            <span class="block text-[11px] text-slate-500 mb-1">Tanggal Selesai:</span>
                            <input type="date" name="custom_until_date" x-model="maintCustomDate" min="{{ date('Y-m-d') }}"
                                class="w-full text-xs rounded-xl border border-slate-300 p-2.5 bg-white focus:outline-none focus:border-amber-600">
                        </div>
                        <div>
                            <span class="block text-[11px] text-slate-500 mb-1">Jam Selesai (WIB):</span>
                            <input type="time" name="custom_until_time" x-model="maintCustomTime"
                                class="w-full text-xs rounded-xl border border-slate-300 p-2.5 bg-white focus:outline-none focus:border-amber-600">
                        </div>
                    </div>
                </div>

                <!-- Opsi Durasi: MANUAL / TAK TERBATAS -->
                <div x-show="maintDurationType === 'indefinite'" class="p-3 bg-amber-50/80 border border-amber-200 rounded-2xl text-xs text-amber-900 space-y-1" style="display: none;">
                    <p class="font-bold flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-amber-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>Mode Maintenance Manual</span>
                    </p>
                    <p class="text-[11px] text-amber-800 leading-relaxed">
                        Unit PC akan tetap berstatus Maintenance tanpa batas waktu hingga Kepala Lab menekan tombol <strong>"Aktifkan Kembali PC"</strong>.
                    </p>
                </div>

                <!-- Alasan Maintenance -->
                <div class="space-y-2">
                    <label for="maintenance_reason" class="block text-xs font-bold text-slate-700">
                        Alasan / Catatan Pemeliharaan <span class="text-slate-400 font-normal">(Muncul pada user & jadwal)</span>
                    </label>
                    <!-- Preset chips -->
                    <div class="flex flex-wrap gap-1.5">
                        <button type="button" @click="maintReason = 'Perawatan hardware & update software sistem'"
                            class="text-[10px] px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-200 transition-colors">
                            Hardware & Update OS
                        </button>
                        <button type="button" @click="maintReason = 'Pembersihan heatsink CPU & ganti thermal paste GPU'"
                            class="text-[10px] px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-200 transition-colors">
                            Cleaning & Thermal Paste
                        </button>
                        <button type="button" @click="maintReason = 'Perbaikan kendala sistem jaringan & OS'"
                            class="text-[10px] px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-200 transition-colors">
                            Kendala Jaringan / OS
                        </button>
                        <button type="button" @click="maintReason = 'Instalasi library AI & CUDA Toolkit baru'"
                            class="text-[10px] px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-200 transition-colors">
                            Install Library AI / CUDA
                        </button>
                    </div>
                    <textarea id="maintenance_reason" name="maintenance_reason" rows="2" x-model="maintReason" required
                        placeholder="Tuliskan alasan pemeliharaan komputer..."
                        class="w-full text-xs rounded-xl border border-slate-300 p-3 focus:outline-none focus:border-amber-600 focus:ring-2 focus:ring-amber-600/20"></textarea>
                </div>

                <!-- Info Banner -->
                <div class="p-3 bg-amber-50 rounded-xl border border-amber-200 text-[11px] text-amber-900 flex items-start gap-2">
                    <svg class="w-4 h-4 text-amber-600 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <span>Mahasiswa tidak dapat mereservasi unit ini selama masa maintenance. Jika ada sesi billing yang berjalan, sesi tersebut akan dihentikan secara otomatis.</span>
                </div>

                <!-- Action Buttons -->
                <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                    <button type="button" @click="maintenanceModalOpen = false"
                        class="px-4 py-2.5 rounded-xl text-xs font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 border border-slate-200 transition-colors cursor-pointer">
                        Batal
                    </button>
                    <button type="submit"
                        class="px-5 py-2.5 rounded-xl text-xs font-bold bg-amber-600 hover:bg-amber-700 text-white transition-all shadow-sm shadow-amber-600/30 cursor-pointer inline-flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                        <span>Matikan Unit (Set Maintenance)</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

</body>

</html>
