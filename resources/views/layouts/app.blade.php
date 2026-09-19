<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Reservasi Kampus')</title>

    <!-- Font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'ui-sans-serif', 'system-ui', 'sans-serif'],
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
          showModalReservasi: false, 
          showErrorModal: {{ $errors->any() ? 'true' : 'false' }}
      }">

    <!-- Navbar -->
    <header class="bg-white text-slate-900 border-b border-slate-200 sticky top-0 z-40">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-14 flex items-center justify-between">
            <a href="{{ route('landing') }}" class="font-extrabold text-sm sm:text-base tracking-tight text-slate-900">Reservasi Kampus</a>

            <nav class="flex items-center gap-2 text-xs font-bold">
                @auth
                    @if(auth()->user()->role === 'pengguna' || (!auth()->user()->isAdmin() && !auth()->user()->isPetugas()))
                        <a href="{{ route('landing') }}" class="hidden sm:inline-block px-3 py-2 rounded-lg text-slate-600 hover:bg-slate-100 transition">Cari Fasilitas</a>
                        <button @click="showModalReservasi = true" type="button" class="px-3 py-2 rounded-lg text-slate-600 hover:bg-slate-100 transition">
                            Reservasi Saya
                        </button>
<<<<<<< HEAD
                        <a href="{{ route('pengguna.laporan.index') }}" class="px-3 py-2 rounded-lg hover:bg-blue-800 transition">
=======
                        <button @click="showModalLaporan = true" type="button" class="px-3 py-2 rounded-lg text-slate-600 hover:bg-slate-100 transition">
>>>>>>> feature/login-landingPage
                            Laporan Saya
                        </button>
                        <a href="{{ route('pengguna.dashboard') }}" class="px-3.5 py-2 bg-[#0f2540] hover:bg-[#0b1c31] text-white rounded-lg transition">
                            Panel Mahasiswa
                        </a>
                    @elseif(auth()->user()->isAdmin())
                        <a href="{{ route('landing') }}" class="hidden sm:inline-block px-3 py-2 rounded-lg text-slate-600 hover:bg-slate-100 transition">Beranda</a>
                        <a href="{{ route('admin.dashboard') }}" class="px-3.5 py-2 bg-[#0f2540] hover:bg-[#0b1c31] text-white rounded-lg transition">Panel Admin</a>
                    @elseif(auth()->user()->isPetugas())
                        <a href="{{ route('landing') }}" class="hidden sm:inline-block px-3 py-2 rounded-lg text-slate-600 hover:bg-slate-100 transition">Beranda</a>
                        <a href="{{ route('petugas.dashboard') }}" class="px-3.5 py-2 bg-[#0f2540] hover:bg-[#0b1c31] text-white rounded-lg transition">Panel Petugas</a>
                    @endif

                    <form action="{{ route('logout') }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="px-3.5 py-2 border border-rose-200 text-rose-600 hover:bg-rose-50 rounded-lg transition">Keluar</button>
                    </form>
                @else
                    {{-- Pengunjung (belum login): hanya Login & Registrasi Akun --}}
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
                
                <!-- Header Modal Error -->
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

                <!-- Isi Pesan Error -->
                <div class="space-y-2 py-2">
                    @foreach($errors->all() as $err)
                        <div class="flex items-start gap-2 text-xs font-semibold text-rose-800 bg-rose-50 p-3 rounded-xl border border-rose-100">
                            <span class="text-rose-500">•</span>
                            <span>{{ $err }}</span>
                        </div>
                    @endforeach
                </div>

                <!-- Tombol Tutup -->
                <div class="flex justify-end pt-2 border-t border-slate-100">
                    <button @click="showErrorModal = false" type="button" class="w-full sm:w-auto bg-rose-600 hover:bg-rose-700 text-white font-bold px-5 py-2.5 rounded-xl text-xs shadow transition">
                        Saya Mengerti
                    </button>
                </div>

            </div>
        </div>
    </div>
    @endif

    <!-- POP-UP MODAL: RESERVASI SAYA GLOBAL -->
    @auth
    <div x-show="showModalReservasi" x-cloak class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" @click="showModalReservasi = false"></div>
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-xl transition-all sm:w-full sm:max-w-4xl p-6 border border-slate-100 space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <div>
                        <h3 class="text-base font-black text-slate-900">Riwayat Reservasi Saya</h3>
                        <p class="text-xs text-slate-500">Daftar pengajuan peminjaman fasilitas kamu.</p>
                    </div>
                    <button @click="showModalReservasi = false" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg hover:bg-slate-100">✕</button>
                </div>

                <div class="overflow-y-auto max-h-[60vh] border border-slate-100 rounded-xl">
                    <table class="w-full text-xs text-left">
                        <thead class="sticky top-0 bg-slate-50 border-b border-slate-100">
                            <tr class="text-slate-400 uppercase tracking-wider text-[10px]">
                                <th class="py-3 px-4">Fasilitas</th>
                                <th class="py-3 px-4">Tanggal</th>
                                <th class="py-3 px-4">Waktu</th>
                                <th class="py-3 px-4">Tujuan</th>
                                <th class="py-3 px-4">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @php
                                $myReservations = \App\Models\Reservation::where('user_id', auth()->id())->with('facility')->latest()->get();
                            @endphp
                            @forelse($myReservations as $res)
                                <tr class="hover:bg-slate-50/50">
                                    <td class="py-3 px-4 font-bold text-slate-800">{{ $res->facility->nama_fasilitas ?? '-' }}</td>
                                    <td class="py-3 px-4 text-slate-600">{{ \Carbon\Carbon::parse($res->tanggal)->format('d M Y') }}</td>
                                    <td class="py-3 px-4 text-slate-600">{{ \Carbon\Carbon::parse($res->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($res->end_time)->format('H:i') }}</td>
                                    <td class="py-3 px-4 text-slate-600">{{ $res->tujuan }}</td>
                                    <td class="py-3 px-4">
                                        @if($res->status === 'approved')
                                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-700">Disetujui</span>
                                        @elseif($res->status === 'rejected')
                                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-rose-100 text-rose-700">Ditolak</span>
                                        @else
                                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-100 text-amber-700">Menunggu</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="py-4 text-center text-slate-400">Belum ada riwayat reservasi.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="flex justify-end pt-2 border-t border-slate-100">
                    <button @click="showModalReservasi = false" type="button" class="bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold px-4 py-2 rounded-xl text-xs transition">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    @endauth
    @stack('scripts')
</body>
</html>