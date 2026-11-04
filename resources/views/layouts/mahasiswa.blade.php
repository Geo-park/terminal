<!DOCTYPE html>
<html lang="id" class="h-full bg-[#f8f9ff]">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Portal Mahasiswa') - TERMINAL</title>

    <!-- Favicon (Tab Icon) -->
    <link rel="icon" type="image/png" href="{{ asset('images/logo-informatika.png') }}">
    <link rel="shortcut icon" type="image/png" href="{{ asset('images/logo-informatika.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/logo-informatika.png') }}">

    <!-- Google Fonts: Plus Jakarta Sans (Font Bawaan) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400&display=swap"
        rel="stylesheet">

    <!-- Scripts & Styles via Vite -->
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

<body class="h-full antialiased selection:bg-[#e0e7ff] selection:text-[#424dc4]"
    x-data="{ 
        miniSidebar: false, 
        mobileMenuOpen: false, 
        profileMenuOpen: false, 
        notificationMenuOpen: false,
        allNotifsModalOpen: false,
        notifTab: 'unread',
        isOperatingHoursOver: {{ ($isOperatingHoursOver ?? false) ? 'true' : 'false' }},
        profileModalOpen: false,
        rulesModalOpen: false,
        profileTab: 'ringkasan',
        notifications: {{ Js::from($notifications ?? []) }},
        get unreadNotifs() {
            return this.notifications.filter(n => n.unread);
        },
        get readNotifs() {
            return this.notifications.filter(n => !n.unread);
        },
        get hasUnreadNotifs() {
            return this.unreadNotifs.length > 0;
        },
        markAsRead(id) {
            const item = this.notifications.find(n => n.id === id);
            if (item) item.unread = false;
        },
        markAllAsRead() {
            this.notifications.forEach(n => n.unread = false);
            this.notifTab = 'read';
        }
    }">
    <div class="min-h-full flex flex-col lg:flex-row">

        <!-- Mobile Sidebar Backdrop -->
        <div x-show="mobileMenuOpen" x-transition:enter="transition-opacity ease-linear duration-300"
            x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
            x-transition:leave="transition-opacity ease-linear duration-300" x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0" class="fixed inset-0 z-40 bg-slate-900/50 backdrop-blur-xs lg:hidden"
            @click="mobileMenuOpen = false" style="display: none;"></div>

        <!-- Sidebar Navigation (Collapsible to Mini Icon-Only on Click) -->
        <aside
            class="fixed inset-y-0 left-0 z-50 bg-white border-r border-[#e2e8f0] flex flex-col justify-between transition-all duration-300 ease-in-out lg:translate-x-0"
            :class="{
                'w-[260px]': !miniSidebar,
                'w-[76px]': miniSidebar,
                'translate-x-0': mobileMenuOpen,
                '-translate-x-full lg:translate-x-0': !mobileMenuOpen
            }">

            <div class="flex flex-col h-full">
                <!-- Brand Header dengan Tombol Icon yang Bisa Diklik -->
                <div class="h-20 flex items-center border-b border-[#f1f5f9] transition-all duration-300"
                    :class="miniSidebar ? 'justify-center px-2' : 'justify-between px-6'">

                    <div class="flex items-center gap-3">
                        <!-- Logo Informatika (Klik untuk buka/tutup teks sidebar) -->
                        <button type="button" @click="miniSidebar = !miniSidebar"
                            title="Klik untuk membuka / menutup teks sidebar"
                            class="w-10 h-10 flex items-center justify-center hover:scale-105 active:scale-95 transition-transform cursor-pointer shrink-0 focus:outline-none border-0 outline-none">
                            <img src="{{ asset('images/logo-informatika.png') }}" alt="Logo Informatika"
                                class="w-9 h-9 object-contain select-none pointer-events-none border-0 outline-none">
                        </button>

                        <!-- Teks Brand (Otomatis sembunyi saat sidebar mengecil jadi icon doang) -->
                        <div x-show="!miniSidebar" x-transition.opacity.duration.200ms
                            class="overflow-hidden whitespace-nowrap">
                            <span class="block text-base font-black tracking-tight text-[#0b1c30]">TERMINAL</span>
                            <span class="block text-[10px] font-semibold text-[#5c67de] tracking-wide uppercase">Riset & Inovasi Digital</span>
                        </div>
                    </div>

                    <!-- Tombol Tutup Mobile -->
                    <button class="lg:hidden p-1.5 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100"
                        @click="mobileMenuOpen = false">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Navigation Links -->
                <nav class="flex-1 py-6 space-y-1.5 overflow-y-auto transition-all duration-300"
                    :class="miniSidebar ? 'px-2' : 'px-4'">

                    <p x-show="!miniSidebar" x-transition.opacity.duration.150ms
                        class="px-3 pb-2 text-[11px] font-bold uppercase tracking-wider text-slate-400 whitespace-nowrap">
                        Menu Utama</p>

                    <!-- Dashboard -->
                    @php
                        $isDashboardActive = request()->routeIs('mahasiswa.dashboard');
                    @endphp
                    <a href="{{ route('mahasiswa.dashboard') }}" title="Dashboard"
                        class="relative flex items-center rounded-xl text-sm font-semibold transition-all duration-200 group border {{ $isDashboardActive ? 'text-[#424dc4] bg-[#eff4ff] border-[#dce9ff] shadow-xs font-bold hover:bg-[#e5edff]' : 'text-[#64748b] border-transparent hover:text-[#424dc4] hover:bg-[#f8faff] hover:border-slate-200/80 hover:translate-x-1' }}"
                        :class="miniSidebar ? 'justify-center p-3' : 'gap-3 px-3.5 py-3'">
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
                        <span x-show="!miniSidebar" x-transition.opacity.duration.150ms
                            class="whitespace-nowrap">Dashboard</span>
                        @if($isDashboardActive)
                            <span x-show="!miniSidebar" class="ml-auto flex items-center gap-1.5" title="Halaman Aktif">
                                <span class="w-1.5 h-1.5 rounded-full bg-[#424dc4] ring-4 ring-[#424dc4]/15"></span>
                            </span>
                        @endif
                    </a>

                    <!-- Buku Booking (Jadwal Reservasi) -->
                    @php
                        $isBukuBookingActive = request()->routeIs('mahasiswa.buku-booking*');
                    @endphp
                    <a href="{{ route('mahasiswa.buku-booking') }}" title="Buku Booking"
                        class="relative flex items-center rounded-xl text-sm font-semibold transition-all duration-200 group border {{ $isBukuBookingActive ? 'text-[#424dc4] bg-[#eff4ff] border-[#dce9ff] shadow-xs font-bold hover:bg-[#e5edff]' : 'text-[#64748b] border-transparent hover:text-[#424dc4] hover:bg-[#f8faff] hover:border-slate-200/80 hover:translate-x-1' }}"
                        :class="miniSidebar ? 'justify-center p-3' : 'gap-3 px-3.5 py-3'">
                        @if($isBukuBookingActive)
                            <span class="active-indicator absolute left-0 top-1.5 bottom-1.5 w-1.5 bg-[#424dc4] rounded-r-full shadow-sm shadow-[#424dc4]/40"></span>
                        @else
                            <span class="opacity-0 group-hover:opacity-40 transition-opacity absolute left-0 top-2 bottom-2 w-1 bg-[#424dc4] rounded-r-full"></span>
                        @endif
                        <svg class="w-5 h-5 shrink-0 transition-all duration-200 {{ $isBukuBookingActive ? 'text-[#424dc4]' : 'text-slate-400 group-hover:text-[#424dc4] group-hover:scale-110' }}"
                            fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                        <span x-show="!miniSidebar" x-transition.opacity.duration.150ms
                            class="whitespace-nowrap">Buku Booking</span>
                        @if($isBukuBookingActive)
                            <span x-show="!miniSidebar" class="ml-auto flex items-center gap-1.5" title="Halaman Aktif">
                                <span class="w-1.5 h-1.5 rounded-full bg-[#424dc4] ring-4 ring-[#424dc4]/15"></span>
                            </span>
                        @endif
                    </a>

                    <!-- Reservasi Komputer -->
                    @php
                        $isReservasiActive = request()->routeIs('mahasiswa.reservasi.*');
                    @endphp
                    <a href="{{ route('mahasiswa.reservasi.create') }}" title="Ajukan Reservasi"
                        class="relative flex items-center rounded-xl text-sm font-semibold transition-all duration-200 group border {{ $isReservasiActive ? 'text-[#424dc4] bg-[#eff4ff] border-[#dce9ff] shadow-xs font-bold hover:bg-[#e5edff]' : 'text-[#64748b] border-transparent hover:text-[#424dc4] hover:bg-[#f8faff] hover:border-slate-200/80 hover:translate-x-1' }}"
                        :class="miniSidebar ? 'justify-center p-3' : 'gap-3 px-3.5 py-3'">
                        @if($isReservasiActive)
                            <span class="active-indicator absolute left-0 top-1.5 bottom-1.5 w-1.5 bg-[#424dc4] rounded-r-full shadow-sm shadow-[#424dc4]/40"></span>
                        @else
                            <span class="opacity-0 group-hover:opacity-40 transition-opacity absolute left-0 top-2 bottom-2 w-1 bg-[#424dc4] rounded-r-full"></span>
                        @endif
                        <svg class="w-5 h-5 shrink-0 transition-all duration-200 {{ $isReservasiActive ? 'text-[#424dc4]' : 'text-slate-400 group-hover:text-[#424dc4] group-hover:scale-110' }}" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <span x-show="!miniSidebar" x-transition.opacity.duration.150ms class="whitespace-nowrap">Ajukan
                            Reservasi</span>
                        <span x-show="!miniSidebar"
                            class="ml-auto text-[10px] font-bold px-2 py-0.5 rounded-full transition-colors whitespace-nowrap {{ $isReservasiActive ? 'bg-[#424dc4] text-white shadow-xs' : 'bg-[#eff4ff] text-[#424dc4] border border-[#dce9ff] group-hover:bg-[#424dc4] group-hover:text-white' }}">
                            4 PC
                        </span>
                    </a>

                    <!-- Informasi Lab (Hanya muncul saat sidebar lebar) -->
                    <div x-show="!miniSidebar" x-transition.opacity.duration.150ms class="pt-4 pb-2">
                        <p class="px-3 pb-2 text-[11px] font-bold uppercase tracking-wider text-slate-400">Informasi Lab
                        </p>
                        <div class="px-3.5 py-3 rounded-xl bg-[#eff4ff] border border-[#dce9ff] text-xs space-y-1.5">
                            <div class="flex items-center justify-between text-[#424dc4] font-semibold">
                                <span>Jam Operasional</span>
                                <span
                                    class="px-1.5 py-0.5 rounded-sm bg-emerald-100 text-emerald-700 text-[10px] font-bold">BUKA</span>
                            </div>
                            <p class="text-slate-600 font-medium">Senin – Jumat<br><span
                                    class="text-[#0b1c30] font-bold">07.00 – 16.00 WIB</span></p>
                            <p class="text-[11px] text-slate-500 pt-1 border-t border-[#dce9ff]/70">Maks. 5 jam/sesi •
                                Kuota 2x/hari</p>
                        </div>
                    </div>
                </nav>
            </div>
        </aside>

        <!-- Main Content Area (Lebar menyesuaikan miniSidebar) -->
        <div class="flex-1 flex flex-col min-w-0 transition-all duration-300 ease-in-out"
            :class="miniSidebar ? 'lg:pl-[76px]' : 'lg:pl-[260px]'">

            <!-- Top Header Bar -->
            <header
                class="h-20 bg-white border-b border-[#e2e8f0] sticky top-0 z-30 px-4 sm:px-8 flex items-center justify-between gap-4">

                <!-- Left: Context Title & Mobile Menu Toggle Button -->
                <div class="flex items-center gap-3 sm:gap-4">
                    <button type="button" @click="mobileMenuOpen = true"
                        class="p-2 -ml-2 rounded-xl text-slate-600 hover:bg-slate-100 lg:hidden cursor-pointer"
                        title="Buka Menu">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                    <div>
                        <h1 id="topbar-page-title" class="text-base sm:text-xl font-bold text-[#0b1c30] tracking-tight">
                            @yield('page-title', 'Dashboard')
                        </h1>
                        <p class="hidden sm:block text-xs text-slate-500">Laboratorium Data Sains & Artificial
                            Intelligence</p>
                    </div>
                </div>

                <!-- Right: Status Badges, Notifications & Profile Flyout -->
                <div class="flex items-center gap-3 sm:gap-4">

                    <!-- Kuota Badge -->
                    <div
                        class="hidden sm:flex items-center gap-2 px-3 py-1.5 rounded-full bg-[#eff4ff] border border-[#dce9ff] text-xs">
                        <span class="w-2 h-2 rounded-full bg-[#424dc4]"></span>
                        <span class="text-slate-600 font-medium">Sisa Kuota Hari Ini:</span>
                        <span class="font-bold text-[#424dc4]">{{ $remainingQuotaToday ?? 2 }} / 2 Sesi</span>
                    </div>

                    <!-- Notification Bell Button & Dropdown -->
                    <div class="relative" @click.away="notificationMenuOpen = false">
                        <button type="button"
                            @click="notificationMenuOpen = !notificationMenuOpen; profileMenuOpen = false"
                            title="Pemberitahuan"
                            class="relative p-2.5 rounded-xl text-slate-500 hover:text-slate-700 hover:bg-slate-50 border border-transparent hover:border-[#e2e8f0] transition-all cursor-pointer">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                            </svg>
                            <template x-if="hasUnreadNotifs">
                                <span class="absolute top-2 right-2 flex h-2.5 w-2.5">
                                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-rose-400 opacity-75"></span>
                                    <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-rose-500 ring-2 ring-white"></span>
                                </span>
                            </template>
                        </button>

                        <!-- Notification Dropdown Menu: Hanya Belum Dibaca dan Sudah Dibaca -->
                        <div x-show="notificationMenuOpen" 
                            x-transition:enter="transition ease-out duration-150"
                            x-transition:enter-start="opacity-0 scale-95" 
                            x-transition:enter-end="opacity-100 scale-100"
                            x-transition:leave="transition ease-in duration-100"
                            x-transition:leave-start="opacity-100 scale-100" 
                            x-transition:leave-end="opacity-0 scale-95"
                            class="absolute right-0 mt-2 w-80 sm:w-96 max-w-[calc(100vw-1.5rem)] bg-white rounded-2xl shadow-xl border border-[#e2e8f0] py-3 z-50 elevation-3"
                            style="display: none;">

                            <!-- Notification Header -->
                            <div class="px-4 pb-2.5 border-b border-slate-100 flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <h3 class="text-xs font-bold text-[#0b1c30] tracking-wide">Pemberitahuan</h3>
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#eef2ff] text-[#424dc4]"
                                        x-text="(notifTab === 'unread' ? unreadNotifs.length : readNotifs.length) + ' Pesan'">
                                    </span>
                                </div>
                                <button type="button" @click="markAllAsRead()"
                                    class="text-[11px] font-semibold text-slate-400 hover:text-[#424dc4] transition-colors cursor-pointer">
                                    Tandai dibaca
                                </button>
                            </div>

                            <!-- Navigasi Tab: Hanya Belum Dibaca dan Sudah Dibaca -->
                            <div class="px-3 py-2 border-b border-slate-100 flex items-center gap-2 bg-slate-50/70">
                                <button type="button" @click="notifTab = 'unread'"
                                    :class="notifTab === 'unread' 
                                        ? 'bg-[#424dc4] text-white shadow-xs font-bold' 
                                        : 'bg-white text-slate-600 hover:bg-slate-100 font-medium border border-slate-200'"
                                    class="flex-1 py-1.5 px-3 rounded-xl text-xs transition-all flex items-center justify-center gap-1.5 cursor-pointer">
                                    <span>Belum Dibaca</span>
                                    <span :class="notifTab === 'unread' ? 'bg-white/20 text-white' : 'bg-[#eef2ff] text-[#424dc4]'"
                                        class="px-1.5 py-0.2 rounded-full text-[10px] font-bold"
                                        x-text="unreadNotifs.length">
                                    </span>
                                </button>
                                <button type="button" @click="notifTab = 'read'"
                                    :class="notifTab === 'read' 
                                        ? 'bg-[#424dc4] text-white shadow-xs font-bold' 
                                        : 'bg-white text-slate-600 hover:bg-slate-100 font-medium border border-slate-200'"
                                    class="flex-1 py-1.5 px-3 rounded-xl text-xs transition-all flex items-center justify-center gap-1.5 cursor-pointer">
                                    <span>Sudah Dibaca</span>
                                    <span :class="notifTab === 'read' ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-600'"
                                        class="px-1.5 py-0.2 rounded-full text-[10px] font-bold"
                                        x-text="readNotifs.length">
                                    </span>
                                </button>
                            </div>

                            <!-- Notification Items List (Maksimal 3 item terlihat, jika lebih dari tiga bisa scroll) -->
                            <div class="overflow-y-auto divide-y divide-slate-100 custom-scrollbar" style="max-height: 240px;">
                                <template x-for="notif in (notifTab === 'unread' ? unreadNotifs : readNotifs)" :key="notif.id">
                                    <div @click="markAsRead(notif.id)" 
                                        title="Klik untuk tandai sudah dibaca"
                                        class="p-3.5 hover:bg-slate-50/80 transition-colors flex items-start gap-3 cursor-pointer group"
                                        :class="notif.unread ? 'bg-[#eff4ff]/25' : ''">
                                        <!-- Icon -->
                                        <div class="shrink-0 w-8 h-8 rounded-xl flex items-center justify-center"
                                            :class="{
                                                'bg-indigo-50 text-indigo-600': notif.color === 'indigo',
                                                'bg-emerald-50 text-emerald-600': notif.color === 'emerald',
                                                'bg-amber-50 text-amber-600': notif.color === 'amber',
                                                'bg-blue-50 text-blue-600': notif.color === 'blue',
                                                'bg-slate-100 text-slate-600': notif.color === 'slate'
                                            }">
                                            <template x-if="notif.icon === 'monitor'">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                                </svg>
                                            </template>
                                            <template x-if="notif.icon === 'key'">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                                                </svg>
                                            </template>
                                            <template x-if="notif.icon === 'clock'">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                </svg>
                                            </template>
                                            <template x-if="notif.icon === 'ticket'">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/>
                                                </svg>
                                            </template>
                                            <template x-if="notif.icon === 'cloud'">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 00-9.78 2.096A4.001 4.001 0 003 15z"/>
                                                </svg>
                                            </template>
                                            <template x-if="notif.icon === 'history'">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                </svg>
                                            </template>
                                            <template x-if="!['monitor', 'key', 'clock', 'ticket', 'cloud', 'history'].includes(notif.icon)">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                </svg>
                                            </template>
                                        </div>
                                        <!-- Text -->
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center justify-between gap-1 mb-0.5">
                                                <p class="text-xs font-bold text-[#0b1c30] truncate" x-text="notif.title"></p>
                                                <span class="text-[10px] font-medium text-slate-400 whitespace-nowrap" x-text="notif.time"></span>
                                            </div>
                                            <p class="text-[11px] text-slate-600 leading-relaxed" x-text="notif.message"></p>
                                            <template x-if="notif.code">
                                                <div class="mt-1.5 inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md bg-emerald-50 text-emerald-700 font-mono font-bold text-xs">
                                                    <span x-text="'KODE: ' + notif.code"></span>
                                                </div>
                                            </template>
                                        </div>
                                        <!-- Dot indikator jika belum dibaca -->
                                        <template x-if="notif.unread">
                                            <span class="w-2 h-2 rounded-full bg-[#424dc4] shrink-0 mt-1.5" title="Belum dibaca"></span>
                                        </template>
                                    </div>
                                </template>

                                <!-- Notice Jam Operasional Selesai (Setelah 16.00 WIB) -->
                                <template x-if="isOperatingHoursOver">
                                    <div class="p-5 text-center bg-amber-50/70 border-b border-amber-100/80">
                                        <div class="w-10 h-10 mx-auto mb-2 rounded-2xl bg-amber-100 text-amber-700 flex items-center justify-center">
                                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                        </div>
                                        <h4 class="text-xs font-bold text-[#0b1c30]">Jam Operasional Selesai (16.00 WIB)</h4>
                                        <p class="text-[11px] text-slate-600 mt-1 leading-relaxed">
                                            Seluruh pesan harian otomatis dibersihkan setiap hari setelah jam operasional lab selesai. Pesan baru akan aktif kembali saat jam operasional dibuka (07.00 – 16.00 WIB).
                                        </p>
                                    </div>
                                </template>

                                <!-- Empty State Belum Dibaca -->
                                <div x-show="!isOperatingHoursOver && notifTab === 'unread' && unreadNotifs.length === 0" class="p-6 text-center text-slate-400">
                                    <div class="w-10 h-10 mx-auto mb-2 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center">
                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                        </svg>
                                    </div>
                                    <p class="text-xs font-bold text-slate-700">Semua pesan sudah dibaca</p>
                                    <p class="text-[11px] text-slate-400 mt-0.5">Tidak ada pesan baru yang belum dibaca</p>
                                </div>

                                <!-- Empty State Sudah Dibaca -->
                                <div x-show="!isOperatingHoursOver && notifTab === 'read' && readNotifs.length === 0" class="p-6 text-center text-slate-400">
                                    <div class="w-10 h-10 mx-auto mb-2 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center">
                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                                        </svg>
                                    </div>
                                    <p class="text-xs font-bold text-slate-700">Belum ada riwayat pesan</p>
                                    <p class="text-[11px] text-slate-400 mt-0.5">Pesan yang telah dibaca akan tersimpan di sini</p>
                                </div>
                            </div>

                            <!-- Footer -->
                            <div class="px-4 py-2.5 border-t border-slate-100 flex items-center justify-between text-xs bg-slate-50/50 rounded-b-2xl">
                                <button type="button" @click="allNotifsModalOpen = true; notificationMenuOpen = false"
                                    class="text-[11px] font-bold text-[#424dc4] bg-[#eff4ff] hover:bg-[#424dc4] hover:text-white border border-[#dce9ff] px-2.5 py-1 rounded-lg cursor-pointer flex items-center gap-1 transition-all shadow-2xs">
                                    <span>Buka Detail Pesan</span>
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                    </svg>
                                </button>
                                <div class="flex items-center gap-2">
                                    @if($isOperatingHoursOver ?? false)
                                        <a href="?simulasi=jam_kerja" data-no-spa title="Klik untuk simulasi jam kerja (07.00 - 16.00 WIB)"
                                            class="text-[10px] text-indigo-700 bg-indigo-50 hover:bg-indigo-100 border border-indigo-200 px-2 py-0.5 rounded-md font-semibold transition-all">
                                            Simulasi Jam Kerja
                                        </a>
                                    @else
                                        <a href="?simulasi=tutup" data-no-spa title="Klik untuk simulasi tutup operasional (pesan bersih)"
                                            class="text-[10px] text-amber-700 bg-amber-50 hover:bg-amber-100 border border-amber-200 px-2 py-0.5 rounded-md font-semibold transition-all">
                                            Simulasi Tutup
                                        </a>
                                    @endif
                                    <button type="button" @click="notificationMenuOpen = false"
                                        class="font-semibold text-slate-500 hover:text-slate-700 bg-slate-100 hover:bg-slate-200 border border-slate-200 px-2.5 py-1 rounded-lg cursor-pointer transition-all">
                                        Tutup
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Profile Dropdown -->
                    <div class="relative" @click.away="profileMenuOpen = false">
                        <button @click="profileMenuOpen = !profileMenuOpen; notificationMenuOpen = false"
                            type="button"
                            class="flex items-center gap-3 p-1.5 rounded-xl hover:bg-slate-50 border border-transparent hover:border-[#e2e8f0] transition-all cursor-pointer">
                            <div
                                class="w-9 h-9 rounded-full bg-gradient-to-tr from-[#424dc4] to-[#6063ee] text-white font-bold text-xs flex items-center justify-center shadow-xs">
                                {{ strtoupper(substr(auth()->user()->name ?? 'MS', 0, 2)) }}
                            </div>
                            <div class="hidden md:block text-left pr-1">
                                <span
                                    class="block text-xs font-bold text-[#0b1c30]">{{ auth()->user()->name ?? 'M. Subagja' }}</span>
                                <span
                                    class="block text-[11px] text-[#5c67de] font-semibold">{{ (auth()->user()->is_unlimited_session ?? false) ? 'VIP Unlimited ∞' : ucfirst(auth()->user()->role ?? 'Mahasiswa') }}</span>
                            </div>
                            <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                        <!-- Dropdown Menu -->
                        <div x-show="profileMenuOpen" x-transition:enter="transition ease-out duration-150"
                            x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                            x-transition:leave="transition ease-in duration-100"
                            x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                            class="absolute right-0 mt-2 w-60 max-w-[calc(100vw-1.5rem)] bg-white rounded-2xl shadow-xl border border-[#e2e8f0] py-2 z-50 elevation-3"
                            style="display: none;">

                            <div class="px-4 py-2 border-b border-slate-100">
                                <p class="text-xs font-bold text-[#0b1c30]">{{ auth()->user()->name ?? 'M. Subagja' }}</p>
                                <p class="text-[11px] text-slate-500 font-mono">NIM: {{ auth()->user()->nim ?? '22081010045' }}</p>
                                <p class="text-[11px] text-[#5c67de] font-medium pt-0.5">{{ auth()->user()->jurusan ?? 'Teknik Informatika' }}</p>
                            </div>

                            <button type="button" @click="profileModalOpen = true; profileMenuOpen = false"
                                class="w-full flex items-center gap-2.5 px-4 py-2.5 text-xs font-semibold text-slate-700 hover:bg-[#eff4ff] hover:text-[#424dc4] transition-colors cursor-pointer text-left">
                                <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                                Profil Saya
                            </button>

                            <button type="button" @click="rulesModalOpen = true; profileMenuOpen = false"
                                class="w-full flex items-center gap-2.5 px-4 py-2.5 text-xs font-semibold text-slate-700 hover:bg-[#eff4ff] hover:text-[#424dc4] transition-colors cursor-pointer text-left">
                                <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Aturan Laboratorium
                            </button>

                            <div class="border-t border-slate-100 my-1"></div>

                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit"
                                    class="w-full flex items-center gap-2.5 px-4 py-2 text-xs font-semibold text-rose-600 hover:bg-rose-50 transition-colors cursor-pointer">
                                    <svg class="w-4 h-4 text-rose-500" fill="none" viewBox="0 0 24 24"
                                        stroke="currentColor">
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

            <!-- Page Content Canvas -->
            <main class="flex-1 p-4 sm:p-6 lg:p-8 max-w-7xl w-full mx-auto space-y-6">
                @yield('content')
            </main>

            <!-- Footer -->
            <footer class="py-6 px-4 sm:px-8 border-t border-[#e2e8f0] bg-white text-center text-xs text-slate-500">
                <p>&copy; {{ date('Y') }} TERMINAL • Tempat Eksperimen Riset dan Inovasi Digital • Jam Operasional: 07.00 -
                    16.00 WIB</p>
            </footer>

        </div>
    </div>

    <!-- Modal: Semua Pesan & Pemberitahuan -->
    <div x-show="allNotifsModalOpen" 
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0" 
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100" 
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 sm:p-6"
        style="display: none;">

        <div @click.away="allNotifsModalOpen = false"
            x-show="allNotifsModalOpen"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95 translate-y-2"
            x-transition:enter-end="opacity-100 scale-100 translate-y-0"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100 translate-y-0"
            x-transition:leave-end="opacity-0 scale-95 translate-y-2"
            class="bg-white rounded-3xl shadow-2xl border border-slate-100 max-w-2xl w-full overflow-hidden elevation-3">

            <!-- Modal Header -->
            <div class="p-6 border-b border-slate-100 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-[#eff4ff] text-[#424dc4] flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-[#0b1c30]">Pusat Pesan & Pemberitahuan</h2>
                        <p class="text-xs text-slate-500">Pemberitahuan aktivitas, kode akses, dan pengumuman sistem laboratorium</p>
                    </div>
                </div>
                <button type="button" @click="allNotifsModalOpen = false"
                    class="w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-600 flex items-center justify-center transition-colors cursor-pointer">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Navigasi Tab Modal: Belum Dibaca vs Sudah Dibaca -->
            <div class="px-6 py-2.5 border-b border-slate-100 flex items-center justify-between bg-slate-50/70">
                <div class="flex items-center gap-2">
                    <button type="button" @click="notifTab = 'unread'"
                        :class="notifTab === 'unread' 
                            ? 'bg-[#424dc4] text-white shadow-xs font-bold' 
                            : 'bg-white text-slate-600 hover:bg-slate-100 font-medium border border-slate-200'"
                        class="py-1.5 px-3.5 rounded-xl text-xs transition-all flex items-center gap-1.5 cursor-pointer">
                        <span>Belum Dibaca</span>
                        <span :class="notifTab === 'unread' ? 'bg-white/20 text-white' : 'bg-[#eef2ff] text-[#424dc4]'"
                            class="px-1.5 py-0.2 rounded-full text-[10px] font-bold"
                            x-text="unreadNotifs.length">
                        </span>
                    </button>
                    <button type="button" @click="notifTab = 'read'"
                        :class="notifTab === 'read' 
                            ? 'bg-[#424dc4] text-white shadow-xs font-bold' 
                            : 'bg-white text-slate-600 hover:bg-slate-100 font-medium border border-slate-200'"
                        class="py-1.5 px-3.5 rounded-xl text-xs transition-all flex items-center gap-1.5 cursor-pointer">
                        <span>Sudah Dibaca</span>
                        <span :class="notifTab === 'read' ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-600'"
                            class="px-1.5 py-0.2 rounded-full text-[10px] font-bold"
                            x-text="readNotifs.length">
                        </span>
                    </button>
                </div>
                <button type="button" @click="markAllAsRead()"
                    class="text-xs font-bold text-[#424dc4] bg-[#eff4ff] hover:bg-[#424dc4] hover:text-white border border-[#dce9ff] px-3 py-1.5 rounded-lg transition-all cursor-pointer shadow-2xs">
                    Tandai Semua Dibaca
                </button>
            </div>

            <!-- Modal Message List (Scrollable) -->
            <div class="p-6 max-h-[50vh] overflow-y-auto space-y-3 divide-y divide-slate-100 custom-scrollbar">
                <template x-for="notif in (notifTab === 'unread' ? unreadNotifs : readNotifs)" :key="notif.id">
                    <div @click="markAsRead(notif.id)"
                        class="pt-3 first:pt-0 flex items-start gap-4 p-2 rounded-xl hover:bg-slate-50 transition-colors cursor-pointer"
                        :class="notif.unread ? 'bg-[#eff4ff]/25' : ''">
                        <div class="shrink-0 w-9 h-9 rounded-2xl flex items-center justify-center"
                            :class="{
                                'bg-indigo-50 text-indigo-600': notif.color === 'indigo',
                                'bg-emerald-50 text-emerald-600': notif.color === 'emerald',
                                'bg-amber-50 text-amber-600': notif.color === 'amber',
                                'bg-blue-50 text-blue-600': notif.color === 'blue',
                                'bg-slate-100 text-slate-600': notif.color === 'slate'
                            }">
                            <template x-if="notif.icon === 'monitor'">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            </template>
                            <template x-if="notif.icon === 'key'">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                            </template>
                            <template x-if="notif.icon === 'clock'">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </template>
                            <template x-if="notif.icon === 'ticket'">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/></svg>
                            </template>
                            <template x-if="notif.icon === 'cloud'">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 00-9.78 2.096A4.001 4.001 0 003 15z"/></svg>
                            </template>
                            <template x-if="notif.icon === 'history'">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </template>
                            <template x-if="!['monitor', 'key', 'clock', 'ticket', 'cloud', 'history'].includes(notif.icon)">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </template>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between gap-2 mb-1">
                                <h4 class="text-xs font-bold text-[#0b1c30]" x-text="notif.title"></h4>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-slate-100 text-slate-600" x-text="notif.time"></span>
                            </div>
                            <p class="text-xs text-slate-600 leading-relaxed" x-text="notif.message"></p>
                            <template x-if="notif.code">
                                <div class="mt-2 inline-flex items-center gap-2 px-3 py-1 rounded-lg bg-emerald-50 text-emerald-800 font-mono font-bold text-xs border border-emerald-200">
                                    <span x-text="'KODE AKSES: ' + notif.code"></span>
                                </div>
                            </template>
                        </div>
                        <template x-if="notif.unread">
                            <span class="w-2.5 h-2.5 rounded-full bg-[#424dc4] shrink-0 mt-2" title="Belum dibaca"></span>
                        </template>
                    </div>
                </template>

                <!-- Notice Jam Operasional Selesai di Modal -->
                <template x-if="isOperatingHoursOver">
                    <div class="py-10 text-center bg-amber-50/60 rounded-2xl border border-amber-100 p-6">
                        <div class="w-12 h-12 mx-auto mb-3 rounded-2xl bg-amber-100 text-amber-700 flex items-center justify-center">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <h3 class="text-sm font-bold text-[#0b1c30]">Jam Operasional Selesai (16.00 WIB)</h3>
                        <p class="text-xs text-slate-600 mt-1.5 max-w-md mx-auto leading-relaxed">
                            Sesuai aturan operasional laboratorium, seluruh pesan harian otomatis dibersihkan setiap hari setelah pukul 16.00 WIB. Pusat pesan akan aktif kembali besok saat jam kerja dimulai (07.00 – 16.00 WIB).
                        </p>
                        @if($isOperatingHoursOver ?? false)
                            <div class="mt-4">
                                <a href="?simulasi=jam_kerja" data-no-spa class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-semibold bg-[#424dc4] text-white hover:bg-[#363fa3] transition-colors shadow-xs">
                                    Aktifkan Mode Simulasi Jam Kerja (07.00 - 16.00)
                                </a>
                            </div>
                        @endif
                    </div>
                </template>

                <!-- Empty State Modal Belum Dibaca -->
                <div x-show="!isOperatingHoursOver && notifTab === 'unread' && unreadNotifs.length === 0" class="py-12 text-center text-slate-400">
                    <div class="w-12 h-12 mx-auto mb-2 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                    <p class="text-sm font-bold text-slate-700">Semua pesan sudah dibaca</p>
                    <p class="text-xs text-slate-400 mt-0.5">Tidak ada pemberitahuan baru yang belum dibaca.</p>
                </div>

                <!-- Empty State Modal Sudah Dibaca -->
                <div x-show="!isOperatingHoursOver && notifTab === 'read' && readNotifs.length === 0" class="py-12 text-center text-slate-400">
                    <p class="text-sm font-bold text-slate-700">Belum ada riwayat pesan</p>
                    <p class="text-xs text-slate-400 mt-0.5">Pesan yang telah Anda baca akan muncul di sini.</p>
                </div>
            </div>

            <!-- Footer Action -->
            <div class="p-6 border-t border-slate-100 flex items-center justify-between bg-slate-50/50">
                <span class="text-xs text-slate-400">TERMINAL Smart Notification Hub</span>
                <button type="button" @click="allNotifsModalOpen = false"
                    class="px-5 py-2.5 rounded-xl text-xs font-bold bg-[#424dc4] text-white hover:bg-[#363fa3] transition-colors shadow-xs cursor-pointer">
                    Tutup
                </button>
            </div>
        </div>
    </div>

    <!-- Modal: Profil Saya (Tampilan Rapi, Responsif & Fit di Layar) -->
    <div x-show="profileModalOpen" 
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0" 
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100" 
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-3 sm:p-4"
        style="display: none;">

        <div @click.away="profileModalOpen = false"
            x-show="profileModalOpen"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95 translate-y-2"
            x-transition:enter-end="opacity-100 scale-100 translate-y-0"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100 translate-y-0"
            x-transition:leave-end="opacity-0 scale-95 translate-y-2"
            class="bg-white rounded-2xl sm:rounded-3xl shadow-2xl border border-slate-100 max-w-lg w-full max-h-[90vh] flex flex-col overflow-hidden elevation-3 my-auto">

            <!-- Modal Header: Ringkas & Elegan -->
            <div class="relative bg-gradient-to-r from-[#3b47c0] via-[#424dc4] to-[#6063ee] p-4 sm:p-5 text-white shrink-0">
                <button type="button" @click="profileModalOpen = false"
                    class="absolute top-4 right-4 w-7 h-7 rounded-full bg-white/20 hover:bg-white/30 text-white flex items-center justify-center transition-colors cursor-pointer">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>

                <div class="flex items-center gap-3.5 pr-8">
                    <div class="w-12 h-12 rounded-xl bg-white text-[#424dc4] font-extrabold text-lg flex items-center justify-center shadow-md border-2 border-white/40 shrink-0">
                        {{ strtoupper(substr(auth()->user()->name ?? 'MS', 0, 2)) }}
                    </div>
                    <div class="min-w-0">
                        <div class="flex items-center gap-2">
                            <h2 class="text-base sm:text-lg font-bold tracking-tight truncate">{{ auth()->user()->name ?? 'M. Subagja' }}</h2>
                            <span class="px-2 py-0.5 rounded-full text-[9px] font-bold bg-emerald-400 text-emerald-950 uppercase shrink-0">Aktif</span>
                        </div>
                        <p class="text-white/80 text-xs font-mono">NIM: {{ auth()->user()->nim ?? '-' }}</p>
                        <p class="text-white/90 text-xs font-medium truncate">{{ auth()->user()->jurusan ?? '-' }}</p>
                    </div>
                </div>

                <div class="mt-3 pt-2.5 border-t border-white/20 flex items-center justify-between text-xs text-white/85">
                    <span class="inline-flex items-center gap-1.5 text-[11px]">
                        <svg class="w-3.5 h-3.5 text-white/80" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                        Profil Mahasiswa Terverifikasi
                    </span>
                    <span class="text-[10px] bg-white/20 px-2.5 py-0.5 rounded-full border border-white/25 font-semibold">Hanya Lihat</span>
                </div>
            </div>

            <!-- Modal Body: Scrollable & Terstruktur Rapi -->
            <div class="p-4 sm:p-5 space-y-4 overflow-y-auto flex-1 overscroll-contain">
                <!-- 4 Stat Aktivitas Lab -->
                <div>
                    <h3 class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-2">Statistik Aktivitas Lab</h3>
                    <div class="grid grid-cols-4 gap-2">
                        <div class="p-2 sm:p-2.5 rounded-xl bg-slate-50 border border-slate-100 text-center">
                            <span class="block text-base sm:text-lg font-bold text-[#0b1c30]">{{ $totalReservations ?? 0 }}</span>
                            <span class="block text-[10px] font-medium text-slate-500">Reservasi</span>
                        </div>
                        <div class="p-2 sm:p-2.5 rounded-xl bg-slate-50 border border-slate-100 text-center">
                            <span class="block text-base sm:text-lg font-bold text-emerald-600">{{ $totalSessionsCompleted ?? 0 }}</span>
                            <span class="block text-[10px] font-medium text-slate-500">Selesai</span>
                        </div>
                        <div class="p-2 sm:p-2.5 rounded-xl bg-slate-50 border border-slate-100 text-center">
                            <span class="block text-base sm:text-lg font-bold text-[#424dc4]">{{ $remainingQuotaToday ?? 2 }}/2</span>
                            <span class="block text-[10px] font-medium text-slate-500">Sisa Kuota</span>
                        </div>
                        <div class="p-2 sm:p-2.5 rounded-xl bg-slate-50 border border-slate-100 text-center">
                            <span class="block text-base sm:text-lg font-bold text-slate-700">0</span>
                            <span class="block text-[10px] font-medium text-slate-500">Pelanggaran</span>
                        </div>
                    </div>
                </div>

                <!-- Informasi Biodata Akun Mahasiswa -->
                <div>
                    <h3 class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-2">Biodata & Hak Akses</h3>
                    <div class="rounded-xl border border-slate-150 bg-white overflow-hidden text-xs divide-y divide-slate-100">
                        <div class="flex items-center justify-between px-3.5 py-2 hover:bg-slate-50/50">
                            <span class="text-slate-500">Nama Lengkap</span>
                            <span class="font-bold text-[#0b1c30] text-right">{{ auth()->user()->name ?? '-' }}</span>
                        </div>
                        <div class="flex items-center justify-between px-3.5 py-2 hover:bg-slate-50/50">
                            <span class="text-slate-500">NIM</span>
                            <span class="font-mono font-bold text-[#0b1c30] text-right">{{ auth()->user()->nim ?? '-' }}</span>
                        </div>
                        <div class="flex items-center justify-between px-3.5 py-2 hover:bg-slate-50/50">
                            <span class="text-slate-500">Program Studi / Jurusan</span>
                            <span class="font-semibold text-slate-800 text-right">{{ auth()->user()->jurusan ?? '-' }}</span>
                        </div>
                        <div class="flex items-center justify-between px-3.5 py-2 hover:bg-slate-50/50">
                            <span class="text-slate-500">Email Kampus</span>
                            <span class="font-semibold text-slate-800 text-right">{{ auth()->user()->email ?? '-' }}</span>
                        </div>
                        <div class="flex items-center justify-between px-3.5 py-2 hover:bg-slate-50/50">
                            <span class="text-slate-500">Peran Akun</span>
                            <span class="font-bold text-[#424dc4] bg-[#eff4ff] px-2 py-0.5 rounded-full text-[10px] border border-[#dce9ff]">
                                {{ (auth()->user()->is_unlimited_session ?? false) ? 'VIP Tester (Unlimited Sesi)' : 'Mahasiswa Reguler' }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between px-3.5 py-2 hover:bg-slate-50/50">
                            <span class="text-slate-500">Fasilitas Unit Lab</span>
                            <span class="font-semibold text-slate-700 text-right">PC-01 s/d PC-04</span>
                        </div>
                        <div class="flex items-center justify-between px-3.5 py-2 hover:bg-slate-50/50">
                            <span class="text-slate-500">Batas Durasi Sesi</span>
                            <span class="font-semibold text-slate-700 text-right">Maksimal 5 Jam / Sesi</span>
                        </div>
                    </div>
                </div>

                <!-- Info Singkronisasi -->
                <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-600 text-[11px] flex items-center gap-2">
                    <svg class="w-4 h-4 text-[#424dc4] shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>Data profil tersinkronisasi dengan sistem akademik kampus (Hanya Lihat).</span>
                </div>
            </div>

            <!-- Modal Footer: Selalu Terlihat di Layar -->
            <div class="px-5 py-3 bg-slate-50 border-t border-slate-100 flex items-center justify-between shrink-0">
                <span class="text-[11px] text-slate-400 font-mono">NIM: {{ auth()->user()->nim ?? '-' }}</span>
                <button type="button" @click="profileModalOpen = false"
                    class="px-5 py-2 rounded-xl text-xs font-bold bg-[#424dc4] hover:bg-[#363ea3] text-white transition-all shadow-xs cursor-pointer">
                    Tutup
                </button>
            </div>
        </div>
    </div>
        </div>
    </div>

    <!-- Modal: Aturan Laboratorium -->
    <div x-show="rulesModalOpen" 
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0" 
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100" 
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 sm:p-6"
        style="display: none;">

        <div @click.away="rulesModalOpen = false"
            x-show="rulesModalOpen"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95 translate-y-2"
            x-transition:enter-end="opacity-100 scale-100 translate-y-0"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100 translate-y-0"
            x-transition:leave-end="opacity-0 scale-95 translate-y-2"
            class="bg-white rounded-3xl shadow-2xl border border-slate-100 max-w-2xl w-full overflow-hidden elevation-3">

            <!-- Modal Header -->
            <div class="p-6 border-b border-slate-100 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-[#eff4ff] text-[#424dc4] flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-[#0b1c30]">Tata Tertib & SOP Laboratorium Komputer</h2>
                        <p class="text-xs text-slate-500">Laboratorium Data Sains & Artificial Intelligence</p>
                    </div>
                </div>
                <button type="button" @click="rulesModalOpen = false"
                    class="w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-600 flex items-center justify-center transition-colors cursor-pointer">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Rules Content -->
            <div class="p-6 max-h-[60vh] overflow-y-auto space-y-4">
                <div class="flex items-start gap-3.5 p-3.5 rounded-2xl bg-slate-50 border border-slate-100">
                    <span class="w-6 h-6 rounded-full bg-[#eef2ff] text-[#424dc4] font-bold text-xs flex items-center justify-center shrink-0">1</span>
                    <div>
                        <h4 class="text-xs font-bold text-[#0b1c30]">Jam Operasional Laboratorium</h4>
                        <p class="text-xs text-slate-600 mt-0.5 leading-relaxed">Laboratorium buka setiap hari kerja (Senin - Jumat) mulai pukul <strong>07.00 s/d 16.00 WIB</strong>. Seluruh sesi peminjaman wajib diakhiri sebelum jam operasional selesai.</p>
                    </div>
                </div>

                <div class="flex items-start gap-3.5 p-3.5 rounded-2xl bg-slate-50 border border-slate-100">
                    <span class="w-6 h-6 rounded-full bg-[#eef2ff] text-[#424dc4] font-bold text-xs flex items-center justify-center shrink-0">2</span>
                    <div>
                        <h4 class="text-xs font-bold text-[#0b1c30]">Maksimal Durasi Penggunaan (5 Jam/Sesi)</h4>
                        <p class="text-xs text-slate-600 mt-0.5 leading-relaxed">Demi pemerataan fasilitas, satu sesi peminjaman komputer memiliki batas durasi maksimal <strong>5 jam berturut-turut</strong>. Sistem billing akan otomatis logout saat waktu habis.</p>
                    </div>
                </div>

                <div class="flex items-start gap-3.5 p-3.5 rounded-2xl bg-slate-50 border border-slate-100">
                    <span class="w-6 h-6 rounded-full bg-[#eef2ff] text-[#424dc4] font-bold text-xs flex items-center justify-center shrink-0">3</span>
                    <div>
                        <h4 class="text-xs font-bold text-[#0b1c30]">Batas Kuota Harian (Maksimal 2 Sesi/Hari)</h4>
                        <p class="text-xs text-slate-600 mt-0.5 leading-relaxed">Setiap mahasiswa memiliki jatah maksimal <strong>2 sesi pemakaian komputer per hari</strong>. Kuota resmi terpotong saat check-in berhasil di unit PC terkait.</p>
                    </div>
                </div>

                <div class="flex items-start gap-3.5 p-3.5 rounded-2xl bg-slate-50 border border-slate-100">
                    <span class="w-6 h-6 rounded-full bg-[#eef2ff] text-[#424dc4] font-bold text-xs flex items-center justify-center shrink-0">4</span>
                    <div>
                        <h4 class="text-xs font-bold text-[#0b1c30]">16 Digit Token Akses & Input di Layar PC</h4>
                        <p class="text-xs text-slate-600 mt-0.5 leading-relaxed">Setelah reservasi disetujui, token akses 16 digit akan terbit di akun Anda. Mahasiswa memasukkan token tersebut langsung pada layar monitor komputer target di laboratorium Data Sains untuk membuka kunci Windows.</p>
                    </div>
                </div>

                <div class="flex items-start gap-3.5 p-3.5 rounded-2xl bg-slate-50 border border-slate-100">
                    <span class="w-6 h-6 rounded-full bg-[#eef2ff] text-[#424dc4] font-bold text-xs flex items-center justify-center shrink-0">5</span>
                    <div>
                        <h4 class="text-xs font-bold text-[#0b1c30]">Kebijakan Penyimpanan Data & Deep Freeze</h4>
                        <p class="text-xs text-slate-600 mt-0.5 leading-relaxed">Seluruh komputer lab dilengkapi sistem proteksi reset (Deep Freeze). Mahasiswa <strong>wajib menyimpan data pekerjaan ke Google Drive, GitHub, atau flashdisk pribadi</strong>. Pihak lab tidak bertanggung jawab atas data yang hilang setelah restart.</p>
                    </div>
                </div>

                <div class="flex items-start gap-3.5 p-3.5 rounded-2xl bg-slate-50 border border-slate-100">
                    <span class="w-6 h-6 rounded-full bg-[#eef2ff] text-[#424dc4] font-bold text-xs flex items-center justify-center shrink-0">6</span>
                    <div>
                        <h4 class="text-xs font-bold text-[#0b1c30]">Tata Tertib & Larangan di Ruang Lab</h4>
                        <p class="text-xs text-slate-600 mt-0.5 leading-relaxed">Dilarang membawa makanan dan minuman terbuka ke area meja komputer. Dilarang merusak, menukar periferal kabel/hardware PC, atau mengunduh software ilegal yang merugikan jaringan laboratorium.</p>
                    </div>
                </div>
            </div>

            <!-- Footer Action -->
            <div class="p-6 border-t border-slate-100 flex items-center justify-between bg-slate-50/50">
                <span class="text-xs text-slate-400">TERMINAL Standar Operasional Prosedur</span>
                <button type="button" @click="rulesModalOpen = false"
                    class="px-5 py-2.5 rounded-xl text-xs font-bold bg-[#424dc4] text-white hover:bg-[#363fa3] transition-colors shadow-xs cursor-pointer">
                    Saya Mengerti & Patuh
                </button>
            </div>
        </div>
    </div>

    <!-- Toast Global Sukses -->
    @if(session('success'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-[-10px] scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-y-0 scale-100"
            x-transition:leave-end="opacity-0 translate-y-[-10px] scale-95"
            class="fixed top-24 right-4 sm:right-8 z-50 max-w-md bg-white border border-emerald-200 shadow-xl rounded-2xl p-4 flex items-start gap-3.5 elevation-3"
            style="display: none;">
            <div class="w-8 h-8 rounded-xl bg-emerald-500 text-white flex items-center justify-center shrink-0 shadow-xs">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                </svg>
            </div>
            <div class="flex-1 pr-2">
                <h4 class="text-xs font-bold text-emerald-950">Berhasil!</h4>
                <p class="text-xs text-emerald-700 mt-0.5 leading-relaxed">{{ session('success') }}</p>
            </div>
            <button @click="show = false" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg transition-colors cursor-pointer">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    @endif

    @stack('scripts')
</body>

</html>