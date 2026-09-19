<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name', 'KampusReserve') }}</title>
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
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
    <header class="bg-[#1e3a8a] text-white shadow-md sticky top-0 z-40">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <a href="{{ route('landing') }}" class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-white text-blue-900 font-black text-xl flex items-center justify-center shadow">K</div>
                <div>
                    <div class="font-extrabold text-base leading-tight">KampusReserve</div>
                    <div class="text-[10px] text-blue-200">Sistem Fasilitas Terpadu</div>
                </div>
            </a>

            <nav class="flex items-center gap-4 text-xs font-bold">
                <a href="{{ route('landing') }}" class="px-3 py-2 rounded-lg hover:bg-blue-800 transition">Cari Fasilitas</a>
                
                @auth
                    @if(auth()->user()->role === 'pengguna' || (!auth()->user()->isAdmin() && !auth()->user()->isPetugas()))
                        <button @click="showModalReservasi = true" type="button" class="px-3 py-2 rounded-lg hover:bg-blue-800 transition">
                            Reservasi Saya
                        </button>
                        <a href="{{ route('pengguna.laporan.index') }}" class="px-3 py-2 rounded-lg hover:bg-blue-800 transition">
                            Laporan Saya
                        </button>
                        
                        <!-- Panel Mahasiswa -->
                        <a href="{{ route('pengguna.dashboard') }}" class="px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl shadow transition">
                            Panel Mahasiswa
                        </a>
                    @elseif(auth()->user()->isAdmin())
                        <a href="{{ route('admin.dashboard') }}" class="px-3 py-2 bg-blue-950 rounded-lg">Panel Admin</a>
                    @elseif(auth()->user()->isPetugas())
                        <a href="{{ route('petugas.dashboard') }}" class="px-3 py-2 bg-emerald-600 rounded-lg">Panel Petugas</a>
                    @endif

                    <form action="{{ route('logout') }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="px-3 py-2 bg-rose-600 hover:bg-rose-700 rounded-xl text-white transition">Keluar</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl shadow transition">Login Satu Pintu</a>
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
    <footer class="bg-blue-950 text-white py-8 mt-12 text-xs border-t border-blue-900">
        <div class="max-w-7xl mx-auto px-4 text-center text-blue-300 space-y-2">
            <p>&copy; {{ date('Y') }} Sistem Fasilitas Kampus Terpadu. Dibangun dengan Laravel 11/12, MySQL, & Tailwind CSS.</p>
        </div>
    </footer>

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
</body>
</html>