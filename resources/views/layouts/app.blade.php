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
      x-data="{ showModalReservasi: false, showModalLaporan: false }">

    <!-- Navbar Bergaya Traveloka -->
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
                        <button @click="showModalLaporan = true" type="button" class="px-3 py-2 rounded-lg hover:bg-blue-800 transition">
                            Laporan Saya
                        </button>
                        <a href="{{ route('pengguna.dashboard') }}" class="px-3.5 py-2 bg-blue-800 hover:bg-blue-700 rounded-xl border border-blue-600/50 transition">
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

    <!-- Flash Alerts -->
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
        @if($errors->any())
            <div class="p-4 bg-rose-100 border border-rose-300 text-rose-900 rounded-xl text-xs font-bold mb-3">
                @foreach($errors->all() as $err)
                    <div>• {{ $err }}</div>
                @endforeach
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

    <!-- POP-UP MODAL: LAPORAN SAYA GLOBAL -->
    <div x-show="showModalLaporan" x-cloak class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" @click="showModalLaporan = false"></div>
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-xl transition-all sm:w-full sm:max-w-4xl p-6 border border-slate-100 space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <div>
                        <h3 class="text-base font-black text-slate-900">Laporan Kendala Saya</h3>
                        <p class="text-xs text-slate-500">Status penanganan masalah fasilitas yang dikirim.</p>
                    </div>
                    <button @click="showModalLaporan = false" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg hover:bg-slate-100">✕</button>
                </div>

                <div class="overflow-y-auto max-h-[60vh] border border-slate-100 rounded-xl">
                    <table class="w-full text-xs text-left">
                        <thead class="sticky top-0 bg-slate-50 border-b border-slate-100">
                            <tr class="text-slate-400 uppercase tracking-wider text-[10px]">
                                <th class="py-3 px-4">Fasilitas</th>
                                <th class="py-3 px-4">Tanggal Lapor</th>
                                <th class="py-3 px-4">Rincian Kendala</th>
                                <th class="py-3 px-4">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @php
                                $myReports = \App\Models\Report::where('user_id', auth()->id())->with('facility')->latest()->get();
                            @endphp
                            @forelse($myReports as $rep)
                                <tr class="hover:bg-slate-50/50">
                                    <td class="py-3 px-4 font-bold text-slate-800">{{ $rep->facility->nama_fasilitas ?? '-' }}</td>
                                    <td class="py-3 px-4 text-slate-600">{{ $rep->created_at->format('d M Y') }}</td>
                                    <td class="py-3 px-4 text-slate-600">{{ $rep->deskripsi_kendala }}</td>
                                    <td class="py-3 px-4">
                                        @if($rep->status_laporan === 'selesai')
                                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-700">Selesai Ditangani</span>
                                        @elseif($rep->status_laporan === 'diproses')
                                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-blue-100 text-blue-700">Diproses Admin</span>
                                        @else
                                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-100 text-amber-700">Baru</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="py-4 text-center text-slate-400">Belum ada laporan kendala.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="flex justify-end pt-2 border-t border-slate-100">
                    <button @click="showModalLaporan = false" type="button" class="bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold px-4 py-2 rounded-xl text-xs transition">Tutup</button>
                </div>
            </div>
        </div>
    </div>
    @endauth
</body>
</html>