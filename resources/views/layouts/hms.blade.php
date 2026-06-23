<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
      x-data="{
          sidebarOpen: localStorage.getItem('sidebarOpen') !== 'false',
          darkMode: localStorage.getItem('darkMode') !== 'false',
          toggleSidebar() { this.sidebarOpen = !this.sidebarOpen; localStorage.setItem('sidebarOpen', this.sidebarOpen) },
          toggleDark() { this.darkMode = !this.darkMode; localStorage.setItem('darkMode', this.darkMode) }
      }"
      :class="{ 'dark': darkMode }">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') — {{ config('app.name') }}</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50:'#eff6ff', 100:'#dbeafe', 200:'#bfdbfe', 300:'#93c5fd',
                            400:'#60a5fa', 500:'#3b82f6', 600:'#2563eb', 700:'#1d4ed8',
                            800:'#1e40af', 900:'#1e3a8a'
                        },
                        dark: {
                            950: '#020617',
                            900: '#0f172a',
                            800: '#1e293b',
                            750: '#1a2540',
                            700: '#334155',
                        }
                    },
                    boxShadow: {
                        'card': '0 1px 3px 0 rgba(0,0,0,0.4), 0 1px 2px -1px rgba(0,0,0,0.4)',
                        'card-hover': '0 4px 12px 0 rgba(0,0,0,0.5)',
                    }
                }
            }
        }
    </script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.default.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>

    <style>
        [x-cloak] { display: none !important; }

        /* ── Sidebar nav links ── */
        .sidebar-link {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 10px;
            border-radius: 8px;
            color: #64748b;           /* slate-500 – light mode default */
            font-size: 13.5px;
            font-weight: 500;
            transition: all 0.15s ease;
            position: relative;
            white-space: nowrap;
        }
        /* Light mode hover */
        .sidebar-link:hover {
            background: #f1f5f9;      /* slate-100 */
            color: #1e293b;           /* slate-800 */
        }
        /* Light mode active */
        .sidebar-link.active {
            background: #dbeafe;      /* blue-100 */
            color: #1d4ed8;           /* blue-700 */
            font-weight: 600;
        }
        .sidebar-link.active::before {
            content: '';
            position: absolute;
            left: 0;
            top: 20%;
            height: 60%;
            width: 3px;
            background: #2563eb;
            border-radius: 0 3px 3px 0;
        }
        /* Dark mode overrides */
        .dark .sidebar-link {
            color: #94a3b8;           /* slate-400 */
        }
        .dark .sidebar-link:hover {
            background: rgba(255,255,255,0.06);
            color: #e2e8f0;           /* slate-200 */
        }
        .dark .sidebar-link.active {
            background: rgba(59,130,246,0.15);
            color: #60a5fa;           /* blue-400 */
        }
        .dark .sidebar-link.active::before {
            background: #3b82f6;
        }
        .sidebar-group-label {
            padding: 14px 10px 4px;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            color: #94a3b8;           /* slate-400 – light mode */
        }
        .dark .sidebar-group-label {
            color: #475569;           /* slate-600 – dark mode */
        }

        /* ── Universal form field ── */
        .field {
            width: 100%;
            font-size: 0.875rem;
            line-height: 1.5;
            border-radius: 0.625rem;
            padding: 0.5rem 0.75rem;
            border: 1.5px solid #cbd5e1;   /* slate-300 */
            background: #ffffff;
            color: #1e293b;                /* slate-800 */
            outline: none;
            transition: border-color 0.15s, box-shadow 0.15s;
        }
        .field:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59,130,246,0.15);
        }
        .field::placeholder {
            color: #94a3b8;               /* slate-400 */
        }
        /* Dark mode */
        .dark .field {
            background: #0f172a;           /* dark-900 */
            border-color: #334155;         /* slate-700 */
            color: #e2e8f0;               /* slate-200 */
            color-scheme: dark;
        }
        .dark .field:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59,130,246,0.2);
        }
        .dark .field::placeholder {
            color: #475569;               /* slate-600 */
        }
        .field.error {
            border-color: #f87171;
        }
        /* Form label */
        .field-label {
            display: block;
            font-size: 0.8125rem;
            font-weight: 600;
            color: #475569;               /* slate-600 – light */
            margin-bottom: 0.375rem;
        }
        .dark .field-label {
            color: #94a3b8;               /* slate-400 – dark */
        }

        /* Secondary / Cancel button */
        .btn-cancel {
            display: inline-flex; align-items: center; justify-content: center;
            padding: 0.5625rem 1.25rem; font-size: 0.875rem; font-weight: 500;
            border-radius: 0.625rem; color: #475569; background: #f1f5f9;
            border: 1.5px solid #e2e8f0; text-decoration: none;
            transition: background-color 0.15s, color 0.15s, border-color 0.15s;
            white-space: nowrap;
        }
        .btn-cancel:hover { background: #e2e8f0; color: #1e293b; border-color: #cbd5e1; }
        .dark .btn-cancel { background: #1e293b; color: #94a3b8; border-color: #334155; }
        .dark .btn-cancel:hover { background: #334155; color: #e2e8f0; border-color: #475569; }

        /* Scrollbar styling */
        ::-webkit-scrollbar { width: 5px; height: 5px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #334155; border-radius: 99px; }
        ::-webkit-scrollbar-thumb:hover { background: #475569; }

        /* Stat card gradient icons */
        .icon-gradient-blue   { background: linear-gradient(135deg, #1d4ed8, #3b82f6); }
        .icon-gradient-green  { background: linear-gradient(135deg, #059669, #10b981); }
        .icon-gradient-purple { background: linear-gradient(135deg, #7c3aed, #a78bfa); }
        .icon-gradient-amber  { background: linear-gradient(135deg, #d97706, #fbbf24); }
        .icon-gradient-teal   { background: linear-gradient(135deg, #0d9488, #2dd4bf); }
        .icon-gradient-red    { background: linear-gradient(135deg, #dc2626, #f87171); }
        .icon-gradient-indigo { background: linear-gradient(135deg, #4338ca, #818cf8); }
        .icon-gradient-orange { background: linear-gradient(135deg, #ea580c, #fb923c); }

        /* Number ticker animation */
        @keyframes fadeUp { from { opacity: 0; transform: translateY(6px); } to { opacity: 1; transform: translateY(0); } }
        .fade-up { animation: fadeUp 0.3s ease forwards; }

        /* ── Tom Select — mirrors .field exactly ─────────────── */
        /* Tom Select copies the <select>'s classes (incl. "field") to .ts-wrapper,
           which would apply .field border/padding/bg a second time around the
           inner .ts-control — doubling the height. Reset them here. */
        .ts-wrapper, .ts-wrapper.field {
            width: 100%;
            border: none !important;
            padding: 0 !important;
            background: transparent !important;
            box-shadow: none !important;
            outline: none !important;
            line-height: normal;
        }

        /* Control box: same border/bg/padding/radius/font as .field */
        .ts-wrapper .ts-control {
            border: 1.5px solid #cbd5e1 !important;   /* = .field */
            border-radius: 0.625rem !important;        /* = .field */
            background: #ffffff !important;            /* = .field */
            padding: 0.5rem 2.25rem 0.5rem 0.75rem !important; /* = .field 0.5rem 0.75rem + right room for arrow */
            font-size: 0.875rem;                       /* = .field */
            line-height: 1.5;                          /* = .field */
            color: #1e293b;                            /* = .field */
            min-height: unset !important;              /* override Tom Select's built-in min-height */
            box-sizing: border-box !important;
            box-shadow: none !important;
            cursor: pointer;
            transition: border-color 0.15s, box-shadow 0.15s;
            display: flex;
            align-items: center;
        }
        .ts-wrapper.focus .ts-control {
            border-color: #3b82f6 !important;                    /* = .field:focus */
            box-shadow: 0 0 0 3px rgba(59,130,246,0.15) !important; /* = .field:focus */
        }

        /* Search input inside the control */
        .ts-control > input {
            font-size: 0.875rem !important;
            color: #1e293b !important;
            background: transparent !important;
            line-height: 1.5;
            padding: 0 !important;
            margin: 0 !important;
            flex: 1 1 auto;
            min-width: 2rem;
        }
        .ts-control > input::placeholder { color: #94a3b8 !important; } /* = .field::placeholder */

        /* Selected value text */
        .ts-control .item {
            font-size: 0.875rem;
            color: #1e293b;
            background: transparent !important;
            border: none !important;
            padding: 0 !important;
            margin: 0 !important;
        }

        /* Tom Select's own placeholder element */
        .ts-control .ts-placeholder { color: #94a3b8 !important; font-size: 0.875rem; }

        /* Caret arrow */
        .ts-wrapper.single .ts-control:after {
            border-color: #94a3b8 transparent transparent;
            border-width: 5px 4px 0;
            right: 0.75rem;
            margin-top: -3px;
        }
        .ts-wrapper.single.dropdown-active .ts-control:after {
            border-color: transparent transparent #94a3b8;
            border-width: 0 4px 5px;
            margin-top: -8px;
        }

        /* Dropdown panel */
        .ts-dropdown {
            border: 1.5px solid #cbd5e1 !important;
            border-top: none !important;
            border-radius: 0 0 0.625rem 0.625rem !important;
            background: #ffffff !important;
            font-size: 0.875rem;
            box-shadow: 0 8px 24px rgba(0,0,0,0.1) !important;
            margin-top: -1px;
            z-index: 9999;
        }
        .ts-dropdown .ts-dropdown-content { max-height: 260px; }
        .ts-dropdown .option {
            color: #1e293b;
            padding: 0.45rem 0.75rem;
            font-size: 0.875rem;
            cursor: pointer;
            transition: background 0.1s;
        }
        .ts-dropdown .option.active          { background: #eff6ff !important; color: #1d4ed8 !important; }
        .ts-dropdown .option.selected        { background: #dbeafe !important; color: #1d4ed8 !important; font-weight: 600; }
        .ts-dropdown .option.selected.active { background: #bfdbfe !important; color: #1d4ed8 !important; }
        .ts-dropdown .no-results             { color: #94a3b8; padding: 0.5rem 0.75rem; font-size: 0.875rem; font-style: italic; }

        /* Highlight matched text — bold color, no grey chip */
        .ts-dropdown .highlight, .ts-dropdown mark {
            background: transparent !important;
            color: #2563eb !important;
            font-weight: 700;
            padding: 0 !important;
        }

        /* ══ Dark mode — every value mirrors .dark .field ══ */
        .dark .ts-wrapper .ts-control {
            background: #0f172a !important;   /* = .dark .field */
            border-color: #334155 !important; /* = .dark .field */
            color: #e2e8f0;                   /* = .dark .field */
        }
        .dark .ts-wrapper.focus .ts-control {
            border-color: #3b82f6 !important;
            box-shadow: 0 0 0 3px rgba(59,130,246,0.2) !important; /* = .dark .field:focus */
        }
        .dark .ts-control > input               { color: #e2e8f0 !important; }
        .dark .ts-control > input::placeholder  { color: #475569 !important; } /* = .dark .field::placeholder */
        .dark .ts-control .item                 { color: #e2e8f0 !important; }
        .dark .ts-control .ts-placeholder       { color: #475569 !important; }
        .dark .ts-wrapper.single .ts-control:after                   { border-color: #64748b transparent transparent; }
        .dark .ts-wrapper.single.dropdown-active .ts-control:after   { border-color: transparent transparent #64748b; }
        .dark .ts-dropdown {
            background: #0f172a !important;   /* = .dark .field */
            border-color: #334155 !important;
            box-shadow: 0 8px 24px rgba(0,0,0,0.4) !important;
        }
        .dark .ts-dropdown .option                  { color: #e2e8f0; }
        .dark .ts-dropdown .option.active           { background: rgba(59,130,246,0.15) !important; color: #93c5fd !important; }
        .dark .ts-dropdown .option.selected         { background: rgba(59,130,246,0.25) !important; color: #60a5fa !important; }
        .dark .ts-dropdown .option.selected.active  { background: rgba(59,130,246,0.35) !important; color: #93c5fd !important; }
        .dark .ts-dropdown .no-results              { color: #475569; }
        .dark .ts-dropdown .highlight, .dark .ts-dropdown mark {
            background: transparent !important;
            color: #60a5fa !important;
            font-weight: 700;
        }
        /* ──────────────────────────────────────────────────────── */

        /* ── Dark-mode hover legibility safety net ───────────────
           Some inline buttons/links use light hover backgrounds
           (hover:bg-slate-50 / -100, hover:bg-gray-50 / -100) without a
           dark: variant, turning near-white on hover in dark mode and
           hiding their text. Map those to a subtle translucent overlay.
           Elements that DO declare a dark:hover:bg-* variant keep it
           (text colour is never affected by these rules). */
        .dark .hover\:bg-slate-50:hover,
        .dark .hover\:bg-gray-50:hover   { background-color: rgba(255,255,255,0.06) !important; }
        .dark .hover\:bg-slate-100:hover,
        .dark .hover\:bg-gray-100:hover  { background-color: rgba(255,255,255,0.09) !important; }
        .dark .hover\:bg-white:hover     { background-color: rgba(255,255,255,0.10) !important; }
        /* ──────────────────────────────────────────────────────── */
    </style>
</head>
<body class="bg-slate-100 dark:bg-dark-950 font-sans antialiased transition-colors duration-200">

<div class="flex h-screen overflow-hidden">

    {{-- ── Sidebar ── --}}
    <aside
        :class="sidebarOpen ? 'w-60' : 'w-[60px]'"
        class="hidden md:flex flex-col bg-white dark:bg-dark-900 border-r border-slate-200 dark:border-dark-700/50 transition-all duration-300 flex-shrink-0"
    >
        {{-- Logo --}}
        <div class="flex items-center gap-3 px-3.5 h-[60px] border-b border-slate-200 dark:border-dark-700/50 flex-shrink-0">
            <div class="w-8 h-8 rounded-lg icon-gradient-blue flex items-center justify-center flex-shrink-0 shadow-md">
                <svg class="w-4.5 h-4.5 text-white w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
            </div>
            <div x-show="sidebarOpen" x-cloak class="min-w-0">
                <p class="text-sm font-bold text-slate-800 dark:text-white leading-tight truncate">
                    {{ \App\Models\HospitalSetting::current()->hospital_name ?? config('app.name') }}
                </p>
                <p class="text-[10px] text-slate-400 dark:text-slate-500 font-medium">Hospital Management</p>
            </div>
        </div>

        {{-- Navigation --}}
        <nav class="flex-1 overflow-y-auto py-2 px-2 space-y-0.5">

            @can('view dashboard')
            <a href="{{ route('dashboard') }}" class="sidebar-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <svg class="w-[18px] h-[18px] flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h4a1 1 0 011 1v5a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM14 5a1 1 0 011-1h4a1 1 0 011 1v2a1 1 0 01-1 1h-4a1 1 0 01-1-1V5zM4 15a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1H5a1 1 0 01-1-1v-4zM14 13a1 1 0 011-1h4a1 1 0 011 1v6a1 1 0 01-1 1h-4a1 1 0 01-1-1v-6z"/>
                </svg>
                <span x-show="sidebarOpen" x-cloak>Dashboard</span>
            </a>
            @endcan

            @canany(['view tokens', 'view appointments', 'view opd'])
            @moduleany(['tokens', 'appointments', 'opd'])
            <div x-show="sidebarOpen" x-cloak class="sidebar-group-label">OPD</div>
            @endmoduleany
            @endcanany

            @can('view tokens')
            @module('tokens')
            <a href="{{ route('tokens.index') }}" class="sidebar-link {{ request()->routeIs('tokens.*') ? 'active' : '' }}">
                <svg class="w-[18px] h-[18px] flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A2 2 0 013 12V7a4 4 0 014-4z"/></svg>
                <span x-show="sidebarOpen" x-cloak>Tokens</span>
            </a>
            @endmodule
            @endcan

            @can('view appointments')
            @module('appointments')
            <a href="{{ route('appointments.index') }}" class="sidebar-link {{ request()->routeIs('appointments.*') ? 'active' : '' }}">
                <svg class="w-[18px] h-[18px] flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                <span x-show="sidebarOpen" x-cloak>Appointments</span>
            </a>
            @endmodule
            @endcan

            @can('view opd')
            @module('opd')
            <a href="{{ route('opd.index') }}" class="sidebar-link {{ request()->routeIs('opd.*') ? 'active' : '' }}">
                <svg class="w-[18px] h-[18px] flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                <span x-show="sidebarOpen" x-cloak>OPD Visits</span>
            </a>
            @endmodule
            @endcan

            @canany(['view ipd', 'view wards'])
            @moduleany(['ipd', 'wards'])
            <div x-show="sidebarOpen" x-cloak class="sidebar-group-label">IPD</div>
            @endmoduleany
            @endcanany

            @can('view ipd')
            @module('ipd')
            <a href="{{ route('ipd.index') }}" class="sidebar-link {{ request()->routeIs('ipd.*') ? 'active' : '' }}">
                <svg class="w-[18px] h-[18px] flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18M12 3v18"/></svg>
                <span x-show="sidebarOpen" x-cloak>Admissions</span>
            </a>
            @endmodule
            @endcan

            @can('view wards')
            @module('wards')
            <a href="{{ route('wards.index') }}" class="sidebar-link {{ request()->routeIs('wards.*') ? 'active' : '' }}">
                <svg class="w-[18px] h-[18px] flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5"/></svg>
                <span x-show="sidebarOpen" x-cloak>Wards & Beds</span>
            </a>
            @endmodule
            @endcan

            @can('view patients')
            @module('patients')
            <div x-show="sidebarOpen" x-cloak class="sidebar-group-label">Patients</div>
            <a href="{{ route('patients.index') }}" class="sidebar-link {{ request()->routeIs('patients.*') ? 'active' : '' }}">
                <svg class="w-[18px] h-[18px] flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0"/></svg>
                <span x-show="sidebarOpen" x-cloak>Patients</span>
            </a>
            @endmodule
            @endcan

            @canany(['view pharmacy', 'view purchases'])
            @module('pharmacy')
            <div x-show="sidebarOpen" x-cloak class="sidebar-group-label">Pharmacy</div>
            @endmodule
            @endcanany

            @module('pharmacy')
            @can('view pharmacy')
            <a href="{{ route('pharmacy.pos') }}" class="sidebar-link {{ request()->routeIs('pharmacy.pos') ? 'active' : '' }}">
                <svg class="w-[18px] h-[18px] flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                <span x-show="sidebarOpen" x-cloak>POS / Sale</span>
            </a>
            <a href="{{ route('medicines.index') }}" class="sidebar-link {{ request()->routeIs('medicines.*') ? 'active' : '' }}">
                <svg class="w-[18px] h-[18px] flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>
                <span x-show="sidebarOpen" x-cloak>Medicines</span>
            </a>
            <a href="{{ route('purchases.index') }}" class="sidebar-link {{ request()->routeIs('purchases.*') ? 'active' : '' }}">
                <svg class="w-[18px] h-[18px] flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                <span x-show="sidebarOpen" x-cloak>Purchases</span>
            </a>
            @endcan
            @endmodule

            @can('view laboratory')
            @module('laboratory')
            <div x-show="sidebarOpen" x-cloak class="sidebar-group-label">Laboratory</div>
            @endmodule
            @endcan

            @can('view laboratory')
            @module('laboratory')
            <a href="{{ route('lab.index') }}" class="sidebar-link {{ request()->routeIs('lab.*') && ! request()->routeIs('lab.tests.*') ? 'active' : '' }}">
                <svg class="w-[18px] h-[18px] flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v11.5A3.5 3.5 0 0012.5 18h0a3.5 3.5 0 003.5-3.5V3M9 3h6M9 7h6"/></svg>
                <span x-show="sidebarOpen" x-cloak>Lab Bookings</span>
            </a>
            <a href="{{ route('lab.tests.index') }}" class="sidebar-link {{ request()->routeIs('lab.tests.*') ? 'active' : '' }}">
                <svg class="w-[18px] h-[18px] flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>
                <span x-show="sidebarOpen" x-cloak>Lab Tests</span>
            </a>
            @endmodule
            @endcan

            @canany(['view expenses', 'view salaries', 'view reports'])
            @moduleany(['expenses', 'salaries', 'reports'])
            <div x-show="sidebarOpen" x-cloak class="sidebar-group-label">Finance</div>
            @endmoduleany
            @endcanany

            @can('view expenses')
            @module('expenses')
            <a href="{{ route('expenses.index') }}" class="sidebar-link {{ request()->routeIs('expenses.*') ? 'active' : '' }}">
                <svg class="w-[18px] h-[18px] flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/></svg>
                <span x-show="sidebarOpen" x-cloak>Expenses</span>
            </a>
            @endmodule
            @endcan

            @can('view salaries')
            @module('salaries')
            <a href="{{ route('salaries.index') }}" class="sidebar-link {{ request()->routeIs('salaries.*') ? 'active' : '' }}">
                <svg class="w-[18px] h-[18px] flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                <span x-show="sidebarOpen" x-cloak>Salaries</span>
            </a>
            @endmodule
            @endcan

            @can('view reports')
            @module('reports')
            <a href="{{ route('reports.index') }}" class="sidebar-link {{ request()->routeIs('reports.*') ? 'active' : '' }}">
                <svg class="w-[18px] h-[18px] flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <span x-show="sidebarOpen" x-cloak>Reports</span>
            </a>
            @endmodule
            @endcan

            @canany(['view doctors', 'view staff', 'view departments', 'view shifts', 'view settings'])
            <div x-show="sidebarOpen" x-cloak class="sidebar-group-label">Administration</div>
            @endcanany

            @can('view doctors')
            @module('doctors')
            <a href="{{ route('doctors.index') }}" class="sidebar-link {{ request()->routeIs('doctors.*') ? 'active' : '' }}">
                <svg class="w-[18px] h-[18px] flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                <span x-show="sidebarOpen" x-cloak>Doctors</span>
            </a>
            @endmodule
            @endcan

            @can('view staff')
            @module('staff')
            <a href="{{ route('staff.index') }}" class="sidebar-link {{ request()->routeIs('staff.*') ? 'active' : '' }}">
                <svg class="w-[18px] h-[18px] flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197"/></svg>
                <span x-show="sidebarOpen" x-cloak>Staff</span>
            </a>
            @endmodule
            @endcan

            @can('view departments')
            @module('departments')
            <a href="{{ route('departments.index') }}" class="sidebar-link {{ request()->routeIs('departments.*') ? 'active' : '' }}">
                <svg class="w-[18px] h-[18px] flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                <span x-show="sidebarOpen" x-cloak>Departments</span>
            </a>
            @endmodule
            @endcan

            @can('view shifts')
            @module('shifts')
            <a href="{{ route('shifts.index') }}" class="sidebar-link {{ request()->routeIs('shifts.*') ? 'active' : '' }}">
                <svg class="w-[18px] h-[18px] flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span x-show="sidebarOpen" x-cloak>Shifts</span>
            </a>
            @endmodule
            @endcan

            @can('view settings')
            <a href="{{ route('settings.index') }}" class="sidebar-link {{ request()->routeIs('settings.*') ? 'active' : '' }}">
                <svg class="w-[18px] h-[18px] flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                <span x-show="sidebarOpen" x-cloak>Settings</span>
            </a>
            @endcan

            @canany(['view users', 'view roles', 'view permissions'])
            <div x-show="sidebarOpen" x-cloak class="sidebar-group-label">Access Control</div>
            @endcanany

            @can('view users')
            <a href="{{ route('users.index') }}" class="sidebar-link {{ request()->routeIs('users.*') ? 'active' : '' }}">
                <svg class="w-[18px] h-[18px] flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6-1.13a4 4 0 10-4-4 4 4 0 004 4zm6 0a4 4 0 00-3-3.87"/></svg>
                <span x-show="sidebarOpen" x-cloak>Users</span>
            </a>
            @endcan

            @can('view roles')
            <a href="{{ route('roles.index') }}" class="sidebar-link {{ request()->routeIs('roles.*') ? 'active' : '' }}">
                <svg class="w-[18px] h-[18px] flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span x-show="sidebarOpen" x-cloak>Roles</span>
            </a>
            @endcan

            @can('view permissions')
            <a href="{{ route('permissions.index') }}" class="sidebar-link {{ request()->routeIs('permissions.*') ? 'active' : '' }}">
                <svg class="w-[18px] h-[18px] flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                <span x-show="sidebarOpen" x-cloak>Permissions</span>
            </a>
            @endcan
        </nav>

        {{-- Collapse toggle --}}
        <div class="border-t border-slate-200 dark:border-dark-700/50 p-2 flex-shrink-0">
            <button @click="toggleSidebar()"
                    class="w-full flex items-center justify-center p-2 rounded-lg text-slate-400 hover:bg-slate-100 dark:hover:bg-white/5 hover:text-slate-600 dark:hover:text-slate-200 transition-all">
                <svg x-show="sidebarOpen" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"/>
                </svg>
                <svg x-show="!sidebarOpen" x-cloak class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7"/>
                </svg>
            </button>
        </div>
    </aside>

    {{-- ── Main content ── --}}
    <div class="flex-1 flex flex-col overflow-hidden">

        {{-- Top header --}}
        <header class="h-[60px] bg-white dark:bg-dark-900 border-b border-slate-200 dark:border-dark-700/50 flex items-center justify-between px-4 md:px-5 flex-shrink-0">
            {{-- Left: breadcrumbs --}}
            <div class="flex items-center gap-3">
                <button @click="toggleSidebar()" class="p-1.5 rounded-lg text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-white/5 transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>
                <div class="text-sm text-slate-400 dark:text-slate-500 hidden sm:flex items-center gap-1.5">
                    @yield('breadcrumb')
                </div>
            </div>

            {{-- Right side --}}
            <div class="flex items-center gap-1.5">
                {{-- Dark mode toggle --}}
                <button @click="toggleDark()"
                        class="p-2 rounded-lg text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-white/5 transition-all"
                        :title="darkMode ? 'Switch to light mode' : 'Switch to dark mode'">
                    <svg x-show="!darkMode" class="w-4.5 h-4.5 w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                    </svg>
                    <svg x-show="darkMode" x-cloak class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                </button>

                {{-- Notifications --}}
                <div x-data="{ open: false }" class="relative">
                    <button @click="open = !open"
                            class="relative p-2 rounded-lg text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-white/5 transition-all">
                        <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                        </svg>
                        @if(auth()->user()->unreadNotifications->count() > 0)
                        <span class="absolute top-1.5 right-1.5 w-1.5 h-1.5 bg-red-500 rounded-full ring-2 ring-white dark:ring-dark-900"></span>
                        @endif
                    </button>
                    <div x-show="open" @click.outside="open = false" x-cloak
                         class="absolute right-0 mt-2 w-80 bg-white dark:bg-dark-800 rounded-xl shadow-xl border border-slate-200 dark:border-dark-700 z-50 overflow-hidden">
                        <div class="px-4 py-3 border-b border-slate-100 dark:border-dark-700 flex items-center justify-between">
                            <h3 class="text-sm font-semibold text-slate-800 dark:text-white">Notifications</h3>
                            @if(auth()->user()->unreadNotifications->count() > 0)
                            <span class="text-xs bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400 px-2 py-0.5 rounded-full font-medium">
                                {{ auth()->user()->unreadNotifications->count() }} new
                            </span>
                            @endif
                        </div>
                        <div class="max-h-64 overflow-y-auto divide-y divide-slate-50 dark:divide-dark-700">
                            @forelse(auth()->user()->unreadNotifications->take(5) as $notification)
                            <div class="px-4 py-3 hover:bg-slate-50 dark:hover:bg-white/5 text-sm text-slate-600 dark:text-slate-300">
                                {{ $notification->data['message'] ?? '' }}
                            </div>
                            @empty
                            <div class="px-4 py-8 text-center">
                                <svg class="w-8 h-8 mx-auto text-slate-300 dark:text-slate-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                                </svg>
                                <p class="text-xs text-slate-400">No new notifications</p>
                            </div>
                            @endforelse
                        </div>
                    </div>
                </div>

                {{-- Divider --}}
                <div class="w-px h-6 bg-slate-200 dark:bg-dark-700 mx-1"></div>

                {{-- User menu --}}
                <div x-data="{ open: false }" class="relative">
                    <button @click="open = !open" class="flex items-center gap-2.5 px-2 py-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-white/5 transition-all">
                        <div class="w-7 h-7 rounded-full icon-gradient-blue flex items-center justify-center text-white text-xs font-bold flex-shrink-0">
                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                        </div>
                        <div class="hidden sm:block text-left">
                            <p class="text-xs font-semibold text-slate-700 dark:text-slate-200 leading-tight">{{ auth()->user()->name }}</p>
                            <p class="text-[10px] text-slate-400 capitalize leading-tight">{{ str_replace('_', ' ', auth()->user()->user_type) }}</p>
                        </div>
                        <svg class="w-3.5 h-3.5 text-slate-400 hidden sm:block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                    <div x-show="open" @click.outside="open = false" x-cloak
                         class="absolute right-0 mt-2 w-52 bg-white dark:bg-dark-800 rounded-xl shadow-xl border border-slate-200 dark:border-dark-700 z-50 py-1.5 overflow-hidden">
                        <div class="px-4 py-2.5 border-b border-slate-100 dark:border-dark-700 mb-1">
                            <p class="text-xs font-semibold text-slate-700 dark:text-slate-200">{{ auth()->user()->name }}</p>
                            <p class="text-[10px] text-slate-400 capitalize">{{ auth()->user()->email }}</p>
                        </div>
                        @can('view settings')
                        <a href="{{ route('settings.index') }}" class="flex items-center gap-2.5 px-4 py-2 text-sm text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-white/5 transition-colors">
                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            Settings
                        </a>
                        @endcan
                        <div class="my-1 border-t border-slate-100 dark:border-dark-700"></div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="w-full flex items-center gap-2.5 px-4 py-2 text-sm text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                Sign Out
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </header>

        {{-- Page content --}}
        <main class="flex-1 overflow-y-auto p-4 md:p-5 bg-slate-50 dark:bg-dark-950">
            {{-- Flash messages --}}
            @if(session('success'))
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)" x-transition
                 class="mb-4 flex items-center gap-3 px-4 py-3 bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800/50 text-emerald-700 dark:text-emerald-400 rounded-xl text-sm font-medium">
                <svg class="w-5 h-5 flex-shrink-0 text-emerald-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                {{ session('success') }}
            </div>
            @endif
            @if(session('error'))
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" x-transition
                 class="mb-4 flex items-center gap-3 px-4 py-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800/50 text-red-600 dark:text-red-400 rounded-xl text-sm font-medium">
                <svg class="w-5 h-5 flex-shrink-0 text-red-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                {{ session('error') }}
            </div>
            @endif
            @if($errors->any())
            <div class="mb-4 px-4 py-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800/50 text-red-600 dark:text-red-400 rounded-xl text-sm">
                <ul class="list-disc list-inside space-y-1">
                    @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                </ul>
            </div>
            @endif

            @yield('content')
        </main>
    </div>
</div>

@stack('scripts')

<script>
(function () {
    'use strict';

    function initTomSelect(el) {
        // Guard: already done, opted out, disabled, or still inside an Alpine template
        if (el._tomselect || el.hasAttribute('data-no-tomselect') || el.disabled) return;
        if (el.closest('template')) return;

        // Small static selects (status, type, gender, shift …) keep the native
        // <select class="field"> look — identical to inputs. Only wrap with Tom
        // Select when there are enough options to benefit from search.
        if (el.options.length <= 6) return;

        // ─── Build settings ──────────────────────────────────────────────────
        // Extract placeholder from first empty <option> so Tom Select renders
        // it as a CSS placeholder on the input, not as a selectable .item.
        const firstOpt = el.options[0];
        const placeholder = (firstOpt && firstOpt.value === '') ? firstOpt.text : null;

        const settings = {
            create:           false,
            allowEmptyOption: false,   // empty option stays in DOM for form submit, not in dropdown
            placeholder:      placeholder || 'Select…',
            maxOptions:       null,
            onInitialize() {
                // Patch el.value setter so Alpine x-model programmatic assignments
                // (e.g. auto-filling department when a doctor is chosen) reflect
                // in the Tom Select UI immediately.
                const proto = Object.getOwnPropertyDescriptor(HTMLSelectElement.prototype, 'value');
                if (!proto) return;
                Object.defineProperty(el, 'value', {
                    configurable: true,
                    enumerable:   true,
                    get: ()    => proto.get.call(el),
                    set: (val) => {
                        proto.set.call(el, val);
                        const v = String(val ?? '');
                        if (ts.getValue() !== v) ts.setValue(v, true); // silent → no loop
                    },
                });
            },
        };

        const ts = new TomSelect(el, settings);
    }

    function initAll(root) {
        (root || document).querySelectorAll('select.field').forEach(initTomSelect);
    }

    // Use alpine:initialized so we run after Alpine has wired up all x-model
    // bindings — prevents race conditions on pages with Alpine components.
    document.addEventListener('alpine:initialized', () => {
        initAll();

        // Watch for elements added after first render (x-for rows, dynamic modals)
        // The setTimeout(0) lets Alpine finish its own post-add init before we wrap.
        const observer = new MutationObserver(mutations => {
            const pending = [];
            for (const m of mutations) {
                for (const node of m.addedNodes) {
                    if (node.nodeType !== 1) continue;
                    if (node.matches?.('select.field'))           pending.push(node);
                    node.querySelectorAll?.('select.field').forEach(n => pending.push(n));
                }
            }
            if (pending.length) setTimeout(() => pending.forEach(initTomSelect), 0);
        });
        observer.observe(document.body, { childList: true, subtree: true });
    });

    // Fallback: if Alpine isn't present (auth pages, plain forms)
    document.addEventListener('DOMContentLoaded', () => {
        if (!window.Alpine) initAll();
    });

    window.initTomSelect     = initTomSelect;
    window.initAllTomSelects = initAll;
})();
</script>
</body>
</html>
