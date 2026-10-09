<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name', 'Reservasi Kampus'))</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Open Sans"', 'ui-sans-serif', 'system-ui', 'sans-serif'],
                    },
                }
            }
        }
    </script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>[x-cloak] { display: none !important; }</style>
</head>
<body class="bg-slate-50 text-slate-900 flex flex-col min-h-screen font-sans antialiased"
      x-data="{ showErrorModal: {{ $errors->any() ? 'true' : 'false' }} }">

    {{-- ============ NAVBAR UTAMA ============ --}}
    <header class="bg-white border-b border-slate-200 sticky top-0 z-40">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-14 flex items-center justify-between gap-3">

            {{-- Logo --}}
            <a href="{{ route('landing') }}" class="flex items-center gap-2 shrink-0">
                <div class="w-9 h-9 rounded-lg bg-blue-900 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 21h18M5 21V7l7-4 7 4v14M9 21v-6h6v6M9 11h.01M15 11h.01"/>
                    </svg>
                </div>
                <span class="font-extrabold text-base tracking-tight text-blue-900">
                    Reservasi<span class="text-emerald-600">Kampus</span>
                </span>
            </a>

            {{-- Navigation --}}
            <nav class="flex items-center gap-1.5 text-xs font-bold">

                {{-- ============ GUEST ============ --}}
                @guest
                    <a href="{{ route('landing') }}"
                       class="hidden sm:inline-block px-3 py-2 rounded-lg text-slate-600 hover:bg-slate-100 transition">
                        Cari Fasilitas
                    </a>
                @endguest

                {{-- ============ AUTHENTICATED ============ --}}
                @auth

                    {{-- ===== PENGGUNA ===== --}}
                    @if(auth()->user()->role === 'pengguna')
                        <a href="{{ route('landing') }}"
                        class="hidden sm:inline-block px-3 py-2 rounded-lg transition {{ request()->routeIs('landing') ? 'bg-blue-50 text-blue-900' : 'text-slate-600 hover:bg-slate-100' }}">
                            Cari Fasilitas
                        </a>
                        <a href="{{ route('pengguna.dashboard') }}"
                        class="px-3 py-2 rounded-lg transition {{ request()->routeIs('pengguna.dashboard') ? 'bg-blue-50 text-blue-900' : 'text-slate-600 hover:bg-slate-100' }}">
                            Dashboard
                        </a>
                        <a href="{{ route('pengguna.reservasi.index') }}"
                        class="hidden md:inline-block px-3 py-2 rounded-lg transition {{ request()->routeIs('pengguna.reservasi.*') ? 'bg-blue-50 text-blue-900' : 'text-slate-600 hover:bg-slate-100' }}">
                            Reservasi Saya
                        </a>
                        <a href="{{ route('pengguna.laporan.index') }}"
                        class="hidden md:inline-block px-3 py-2 rounded-lg transition {{ request()->routeIs('pengguna.laporan.*') ? 'bg-blue-50 text-blue-900' : 'text-slate-600 hover:bg-slate-100' }}">
                            Laporan Saya
                        </a>

                        {{-- Notif Bell Pengguna (Warna abu-abu standar seperti menu lain) --}}
                        @php
                            $userNotifCount = \App\Models\Notification::where('user_id', auth()->id())
                                ->where('is_read', false)->count();
                        @endphp
                        <a href="{{ route('pengguna.notifikasi.index') }}" aria-label="Notifikasi"
                        class="relative p-2 text-slate-600 hover:bg-slate-100 rounded-lg transition mx-0.5">
                            <svg class="w-5 h-5 text-slate-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.4-1.4A2 2 0 0118 14.2V11a6 6 0 10-12 0v3.2a2 2 0 01-.6 1.4L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                            </svg>
                            @if($userNotifCount > 0)
                                <span class="absolute -top-0.5 -right-0.5 min-w-[16px] h-4 px-1 rounded-full bg-rose-600 text-white text-[9px] font-black flex items-center justify-center">
                                    {{ $userNotifCount > 9 ? '9+' : $userNotifCount }}
                                </span>
                            @endif
                        </a>

                    {{-- ===== ADMIN ===== --}}
                    {{-- Catatan: Admin TIDAK ada menu di navbar.
                         Semua navigasi admin ada di sidebar.
                         Navbar hanya identitas + logout. --}}
                    @elseif(auth()->user()->role === 'admin')
                        {{-- Kosong — tidak ada menu --}}

                    {{-- ===== PETUGAS ===== --}}
                    @elseif(auth()->user()->role === 'petugas')
                        <a href="{{ route('petugas.dashboard') }}"
                           class="px-3 py-2 rounded-lg transition {{ request()->routeIs('petugas.dashboard') ? 'bg-blue-50 text-blue-900' : 'text-slate-600 hover:bg-slate-100' }}">
                            Dashboard
                        </a>
                        <a href="{{ route('petugas.reservasi.index') }}"
                           class="hidden md:inline-block px-3 py-2 rounded-lg transition {{ request()->routeIs('petugas.reservasi.*') ? 'bg-blue-50 text-blue-900' : 'text-slate-600 hover:bg-slate-100' }}">
                            Reservasi
                        </a>
                        <a href="{{ route('petugas.laporan.index') }}"
                           class="hidden md:inline-block px-3 py-2 rounded-lg transition {{ request()->routeIs('petugas.laporan.*') ? 'bg-blue-50 text-blue-900' : 'text-slate-600 hover:bg-slate-100' }}">
                            Laporan
                        </a>
                        <a href="{{ route('petugas.fasilitas.index') }}"
                           class="hidden md:inline-block px-3 py-2 rounded-lg transition {{ request()->routeIs('petugas.fasilitas.*') ? 'bg-blue-50 text-blue-900' : 'text-slate-600 hover:bg-slate-100' }}">
                            Fasilitas
                        </a>

                        {{-- Notif Bell Petugas --}}
                        <a href="{{ route('petugas.notifikasi.index') }}" aria-label="Notifikasi"
                           class="relative p-2 rounded-lg transition {{ request()->routeIs('petugas.notifikasi.*') ? 'bg-blue-50 text-blue-900' : 'text-slate-600 hover:bg-slate-100' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.4-1.4A2 2 0 0118 14.2V11a6 6 0 10-12 0v3.2a2 2 0 01-.6 1.4L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                            </svg>
                            @if(isset($notifCount) && $notifCount > 0)
                                <span class="absolute -top-0.5 -right-0.5 min-w-[16px] h-4 px-1 rounded-full bg-rose-600 text-white text-[9px] font-black flex items-center justify-center">
                                    {{ $notifCount > 9 ? '9+' : $notifCount }}
                                </span>
                            @endif
                        </a>
                    @endif

                    {{-- ===== IDENTITAS USER (shared semua role) ===== --}}
                    <div class="hidden lg:flex items-center gap-2 pl-2 ml-1 border-l border-slate-200">
                        <span class="w-7 h-7 rounded-full bg-blue-100 text-blue-900 flex items-center justify-center font-bold text-xs shrink-0">
                            {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
                        </span>
                        <span class="text-slate-700 font-semibold max-w-[120px] truncate">
                            {{ explode(' ', auth()->user()->name ?? 'User')[0] }}
                        </span>
                    </div>

                    {{-- ===== TOMBOL KELUAR (shared semua role) ===== --}}
                    <form action="{{ route('logout') }}" method="POST" class="inline ml-3">
                        @csrf
                        <button type="submit"
                                class="px-3 py-2 border border-rose-200 text-rose-600 hover:bg-rose-50 rounded-lg transition flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                            </svg>
                            <span class="hidden sm:inline">Keluar</span>
                        </button>
                    </form>

                {{-- ============ GUEST: LOGIN/REGISTER ============ --}}
                @else
                    <a href="{{ route('login') }}"
                       class="px-4 py-2 border border-slate-300 text-slate-800 hover:bg-slate-50 rounded-lg transition">
                        Login
                    </a>
                    <a href="{{ route('register') }}"
                       class="px-4 py-2 bg-blue-900 hover:bg-blue-800 text-white rounded-lg transition">
                        Register
                    </a>
                @endauth
            </nav>
        </div>
    </header>

    {{-- ============ FLASH MESSAGE ============ --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4 w-full">
        @if(session('success'))
            <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-lg text-xs font-bold mb-3 flex items-start gap-2">
                <svg class="w-4 h-4 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif
        @if(session('info'))
            <div class="p-4 bg-blue-50 border border-blue-200 text-blue-800 rounded-lg text-xs font-bold mb-3 flex items-start gap-2">
                <svg class="w-4 h-4 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span>{{ session('info') }}</span>
            </div>
        @endif
        @if(session('warning'))
            <div class="p-4 bg-amber-50 border border-amber-200 text-amber-800 rounded-lg text-xs font-bold mb-3 flex items-start gap-2">
                <svg class="w-4 h-4 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span>{{ session('warning') }}</span>
            </div>
        @endif
        @if(session('error'))
            <div class="p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-lg text-xs font-bold mb-3 flex items-start gap-2">
                <svg class="w-4 h-4 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span>{{ session('error') }}</span>
            </div>
        @endif
    </div>

    {{-- ============ MAIN CONTENT ============ --}}
    <main class="flex-1">
        @yield('content')
    </main>

    {{-- ============ FOOTER ============ --}}
    <footer class="bg-blue-950 text-white py-8 text-xs mt-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-3 text-blue-200">
            <p class="font-bold text-white">Reservasi Kampus</p>
            <p>&copy; {{ date('Y') }} Sistem Reservasi &amp; Pelaporan Fasilitas Kampus.</p>
        </div>
    </footer>

    {{-- ============ MODAL ERROR VALIDASI ============ --}}
    @if($errors->any())
        <div x-show="showErrorModal" x-cloak
             class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" @click="showErrorModal = false"></div>
            <div class="flex min-h-full items-center justify-center p-4">
                <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md p-6 border border-rose-100 space-y-4">
                    <div class="flex items-center gap-3 border-b border-slate-100 pb-3">
                        <div class="w-10 h-10 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center shrink-0">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-slate-900">Perhatian</h3>
                            <p class="text-xs text-slate-500">Silakan periksa kembali isian formulir Anda.</p>
                        </div>
                    </div>
                    <div class="space-y-2 py-2">
                        @foreach($errors->all() as $err)
                            <div class="flex items-start gap-2 text-xs font-semibold text-rose-800 bg-rose-50 p-3 rounded-lg border border-rose-100">
                                <span class="text-rose-500">•</span>
                                <span>{{ $err }}</span>
                            </div>
                        @endforeach
                    </div>
                    <div class="flex justify-end pt-2 border-t border-slate-100">
                        <button @click="showErrorModal = false" type="button"
                                class="px-5 py-2.5 bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs rounded-lg shadow transition">
                            Saya Mengerti
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @stack('scripts')
</body>
</html>