<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name', 'Reservasi Kampus'))</title>

    <!-- Google Font: Roboto -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700;900&display=swap" rel="stylesheet">

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Roboto', 'ui-sans-serif', 'system-ui', 'sans-serif'],
                    },
                    colors: {
                        navy: '#1e3a8a',
                        brand: '#10b981',
                    }
                }
            }
        }
    </script>
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-slate-50 text-slate-900 flex flex-col min-h-screen font-sans antialiased"
      x-data="{
          showErrorModal: {{ $errors->any() ? 'true' : 'false' }}
      }">

    <!-- Navbar -->
    <header class="bg-white text-slate-900 border-b border-slate-200 sticky top-0 z-40">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-14 flex items-center justify-between gap-3">

            {{-- Logo --}}
            <a href="{{ route('landing') }}" class="flex items-center gap-2 shrink-0">
                <div class="w-9 h-9 rounded-lg bg-[#0f2540] flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 21h18M5 21V7l7-4 7 4v14M9 21v-6h6v6M9 11h.01M15 11h.01"/>
                    </svg>
                </div>
                <span class="font-extrabold text-base tracking-tight text-[#0f2540]">
                    Reservasi<span class="text-emerald-600">Kampus</span>
                </span>
            </a>

            <nav class="flex items-center gap-1.5 text-xs font-bold">

                {{-- Menu "Cari Fasilitas" HANYA untuk pengunjung (belum login) --}}
                @guest
                    <a href="{{ route('landing') }}" class="hidden sm:inline-block px-3 py-2 rounded-lg text-slate-600 hover:bg-slate-100 transition">Cari Fasilitas</a>
                @endguest

                @auth
                    @if(auth()->user()->role === 'pengguna' || (!auth()->user()->isAdmin() && !auth()->user()->isPetugas()))
                        {{-- ===== MENU PENGGUNA ===== --}}
                        <a href="{{ route('landing') }}"
                           class="hidden sm:inline-block px-3 py-2 rounded-lg transition {{ request()->routeIs('landing') ? 'bg-blue-50 text-blue-700' : 'text-slate-600 hover:bg-slate-100' }}">
                            Cari Fasilitas
                        </a>
                        <a href="{{ route('pengguna.dashboard') }}"
                           class="px-3 py-2 rounded-lg transition {{ request()->routeIs('pengguna.dashboard') ? 'bg-blue-50 text-blue-700' : 'text-slate-600 hover:bg-slate-100' }}">
                            Dashboard
                        </a>
                        <a href="{{ route('pengguna.reservasi.index') }}"
                           class="px-3 py-2 rounded-lg transition {{ request()->routeIs('pengguna.reservasi.*') ? 'bg-blue-50 text-blue-700' : 'text-slate-600 hover:bg-slate-100' }}">
                            Reservasi Saya
                        </a>
                        <a href="{{ route('pengguna.laporan.index') }}"
                           class="px-3 py-2 rounded-lg transition {{ request()->routeIs('pengguna.laporan.*') ? 'bg-blue-50 text-blue-700' : 'text-slate-600 hover:bg-slate-100' }}">
                            Laporan Saya
                        </a>

                    @elseif(auth()->user()->isAdmin())
                        {{-- ===== MENU ADMIN (tanpa Cari Fasilitas) ===== --}}
                        <a href="{{ route('admin.dashboard') }}"
                           class="px-3 py-2 rounded-lg transition {{ request()->routeIs('admin.dashboard') ? 'bg-blue-50 text-blue-700' : 'text-slate-600 hover:bg-slate-100' }}">
                            Dashboard
                        </a>
                        <a href="{{ route('admin.rekap.okupansi') }}"
                           class="hidden md:inline-block px-3 py-2 rounded-lg transition {{ request()->routeIs('admin.rekap.*') ? 'bg-blue-50 text-blue-700' : 'text-slate-600 hover:bg-slate-100' }}">
                            Rekap
                        </a>
                        <a href="{{ route('admin.dashboard') }}#kelola-fasilitas"
                           class="hidden md:inline-block px-3 py-2 rounded-lg text-slate-600 hover:bg-slate-100 transition">
                            Fasilitas
                        </a>

                    @elseif(auth()->user()->isPetugas())
                        {{-- ===== MENU PETUGAS (tanpa Cari Fasilitas) ===== --}}
                        <a href="{{ route('petugas.dashboard') }}"
                           class="px-3 py-2 rounded-lg transition {{ request()->routeIs('petugas.dashboard') ? 'bg-blue-50 text-blue-700' : 'text-slate-600 hover:bg-slate-100' }}">
                            Dashboard
                        </a>
                        <a href="{{ route('petugas.reservasi.index') }}"
                           class="hidden md:inline-block px-3 py-2 rounded-lg transition {{ request()->routeIs('petugas.reservasi.*') ? 'bg-slate-100 text-blue-900' : 'text-slate-600 hover:bg-slate-100' }}">
                            Reservasi
                        </a>
                        <a href="{{ route('petugas.laporan.index') }}"
                           class="hidden md:inline-block px-3 py-2 rounded-lg transition {{ request()->routeIs('petugas.laporan.*') ? 'bg-slate-100 text-blue-900' : 'text-slate-600 hover:bg-slate-100' }}">
                            Laporan
                        </a>
                        <a href="{{ route('petugas.fasilitas.index') }}"
                           class="hidden md:inline-block px-3 py-2 rounded-lg transition {{ request()->routeIs('petugas.fasilitas.*') ? 'bg-slate-100 text-blue-900' : 'text-slate-600 hover:bg-slate-100' }}">
                            Fasilitas
                        </a>

                        {{-- Lonceng notifikasi --}}
                        <a href="{{ route('petugas.notifikasi.index') }}" aria-label="Notifikasi"
                           class="relative p-2 rounded-lg transition {{ request()->routeIs('petugas.notifikasi.*') ? 'bg-slate-100 text-blue-900' : 'text-slate-600 hover:bg-slate-100' }}">
                            <svg style="width:18px;height:18px" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.4-1.4A2 2 0 0118 14.2V11a6 6 0 10-12 0v3.2a2 2 0 01-.6 1.4L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                            </svg>
                            @if($notifCount > 0)
                                <span class="absolute -top-0.5 -right-0.5 min-w-[16px] h-4 px-1 rounded-full bg-rose-600 text-white text-[9px] font-black flex items-center justify-center">
                                    {{ $notifCount > 9 ? '9+' : $notifCount }}
                                </span>
                            @endif
                        </a>
                    @endif

                    {{-- ===== IDENTITAS USER ===== --}}
                    <div class="hidden lg:flex items-center gap-2 pl-2 ml-1 border-l border-slate-200">
                        <span class="w-7 h-7 rounded-full bg-slate-100 text-slate-700 flex items-center justify-center font-black text-[10px] shrink-0">
                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                        </span>
                        <span class="text-slate-700 font-semibold max-w-[120px] truncate">
                            {{ explode(' ', auth()->user()->name)[0] }}
                        </span>
                    </div>

                    {{-- ===== TOMBOL LOGOUT ===== --}}
                    <form action="{{ route('logout') }}" method="POST" class="inline ml-3">
                        @csrf
                        <button type="submit" class="px-3 py-2 border border-rose-200 text-rose-600 hover:bg-rose-50 rounded-lg transition flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                            </svg>
                            <span class="hidden sm:inline">Keluar</span>
                        </button>
                    </form>
                @else
                    {{-- ===== PENGUNJUNG (BELUM LOGIN) ===== --}}
                    <a href="{{ route('login') }}" class="px-4 py-2 border border-slate-300 text-slate-800 hover:bg-slate-50 rounded-lg transition">Login</a>
                    <a href="{{ route('register') }}" class="px-4 py-2 bg-[#0f2540] hover:bg-[#0b1c31] text-white rounded-lg transition">Register</a>
                @endauth
            </nav>
        </div>
    </header>

    <!-- Flash Alerts (Sukses & Info saja) -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4 w-full">
        @if(session('success'))
            <div class="p-4 bg-emerald-100 border border-emerald-300 text-emerald-900 rounded-xl text-xs font-bold mb-3">
                {{ session('success') }}
            </div>
        @endif
        @if(session('info'))
            <div class="p-4 bg-blue-100 border border-blue-300 text-blue-900 rounded-xl text-xs font-bold mb-3">
                {{ session('info') }}
            </div>
        @endif
    </div>

    <!-- Main Content -->
    <main class="flex-1">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-[#0f1f3d] text-white py-8 text-xs">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-3 text-blue-200">
            <p class="font-bold text-white">Reservasi Kampus</p>
            <p>&copy; {{ date('Y') }} Sistem Reservasi &amp; Pelaporan Fasilitas Kampus Terpadu.</p>
        </div>
    </footer>

    <!-- Notifikasi Cookies & Session -->
    <div x-data="{
            show: false,
            detail: false,
            init() { this.show = !document.cookie.split('; ').some(c => c.startsWith('cookie_consent=')); },
            accept() {
                document.cookie = 'cookie_consent=1; max-age=' + (60*60*24*365) + '; path=/; SameSite=Lax';
                this.show = false;
            }
         }"
         x-show="show" x-cloak
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-y-4"
         x-transition:enter-end="opacity-100 translate-y-0"
         class="fixed bottom-4 left-4 right-4 sm:left-auto sm:right-6 sm:max-w-sm z-[60] bg-white border border-slate-200 rounded-2xl shadow-2xl p-4 text-xs text-slate-600"
         role="dialog" aria-label="Pemberitahuan cookies dan session">
        <div class="flex items-start gap-3">
            <div class="w-8 h-8 rounded-full bg-amber-100 text-amber-600 flex items-center justify-center shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div class="space-y-2">
                <p class="font-bold text-slate-900 text-sm">Website ini menggunakan Cookies &amp; Session</p>
                <p>Kami memakai <strong>cookies</strong> dan <strong>session</strong> agar login Anda tetap aman, pilihan slot pemesanan tidak hilang saat berpindah halaman, dan formulir terlindungi dari serangan.</p>

                <div x-show="detail" x-cloak class="bg-slate-50 border border-slate-200 rounded-xl p-3 space-y-1.5">
                    <p><strong class="text-slate-800">{{ config('session.cookie') }}</strong> &ndash; cookie session untuk status login &amp; pilihan slot.</p>
                    <p><strong class="text-slate-800">XSRF-TOKEN</strong> &ndash; cookie keamanan (perlindungan CSRF).</p>
                    <p><strong class="text-slate-800">cookie_consent</strong> &ndash; menyimpan pilihan Anda atas pemberitahuan ini.</p>
                    <p class="text-slate-400">Session berakhir otomatis setelah {{ config('session.lifetime') }} menit tidak aktif.</p>
                </div>

                <div class="flex items-center gap-2 pt-1">
                    <button @click="accept()" type="button" class="px-4 py-2 bg-[#0f2540] hover:bg-[#0b1c31] text-white font-bold rounded-lg transition">Mengerti</button>
                    <button @click="detail = !detail" type="button" class="px-3 py-2 text-blue-700 hover:underline font-semibold" x-text="detail ? 'Sembunyikan' : 'Lihat detail'"></button>
                </div>
            </div>
        </div>
    </div>

    <!-- POP-UP MODAL: PERINGATAN / ERROR VALIDASI -->
    @if($errors->any())
    <div x-show="showErrorModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" @click="showErrorModal = false"></div>
        <div class="flex min-h-full items-center justify-center p-4 text-center">
            <div class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all w-full max-w-md p-6 border border-rose-100 space-y-4">

                <div class="flex items-center gap-3 border-b border-slate-100 pb-3">
                    <div class="w-10 h-10 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center shrink-0">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-base font-black text-slate-900">Perhatian / Kendala</h3>
                        <p class="text-xs text-slate-500">Silakan periksa kembali isian formulir Anda.</p>
                    </div>
                </div>

                <div class="space-y-2 py-2">
                    @foreach($errors->all() as $err)
                        <div class="flex items-start gap-2 text-xs font-semibold text-rose-800 bg-rose-50 p-3 rounded-xl border border-rose-100">
                            <span class="text-rose-500">•</span>
                            <span>{{ $err }}</span>
                        </div>
                    @endforeach
                </div>

                <div class="flex justify-end pt-2 border-t border-slate-100">
                    <button @click="showErrorModal = false" type="button" class="w-full sm:w-auto bg-rose-600 hover:bg-rose-700 text-white font-bold px-5 py-2.5 rounded-xl text-xs shadow transition">
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