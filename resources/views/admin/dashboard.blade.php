@extends('layouts.app')

@section('content')
<main class="max-w-7xl mx-auto p-6 md:p-8 space-y-8 bg-slate-50/50 min-h-screen" x-data="{ activeTab: 'overview' }">

    <!-- Header Welcome Bar -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm">
        <div>
            <h1 class="text-xl font-bold text-slate-800 tracking-tight">Selamat Datang, Administrator 👋</h1>
            <p class="text-xs text-slate-500 mt-1">Kelola data pengguna, fasilitas, dan pantau rekapitulasi sistem KampusReserve.</p>
        </div>
        <div class="flex items-center gap-3">
            <div class="px-3 py-1.5 bg-blue-50 text-blue-700 border border-blue-100 rounded-lg text-xs font-semibold flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-blue-600 animate-pulse"></span>
                System Status: Active
            </div>
            <div class="text-xs font-medium text-slate-500 bg-slate-100 px-3 py-1.5 rounded-lg border border-slate-200/60">
                📅 {{ now()->translatedFormat('d F Y') }}
            </div>
        </div>
    </div>

    <!-- Section Stat Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Card Total Pengguna -->
        <div class="relative bg-white p-6 rounded-2xl shadow-sm border border-slate-200/80 flex flex-col justify-between overflow-hidden group hover:shadow-md transition">
            <div class="absolute -right-4 -bottom-4 opacity-5 text-blue-600 group-hover:scale-110 transition-transform">
                <svg class="w-32 h-32" fill="currentColor" viewBox="0 0 24 24"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
            </div>
            <div>
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">TOTAL PENGGUNA</span>
                    <span class="p-2 bg-blue-50 text-blue-600 rounded-lg">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    </span>
                </div>
                <div class="text-4xl font-extrabold text-slate-800 tracking-tight">{{ $totalUsers }}</div>
            </div>
            <div class="mt-4 pt-3 border-t border-slate-100 flex items-center text-[11px] font-medium text-slate-500">
                <span>Terdaftar di dalam sistem</span>
            </div>
        </div>

        <!-- Card Total Reservasi -->
        <div class="relative bg-white p-6 rounded-2xl shadow-sm border border-slate-200/80 flex flex-col justify-between overflow-hidden group hover:shadow-md transition">
            <div class="absolute -right-4 -bottom-4 opacity-5 text-emerald-600 group-hover:scale-110 transition-transform">
                <svg class="w-32 h-32" fill="currentColor" viewBox="0 0 24 24"><path d="M19 3h-1V1h-2v2H8V1H6v2H5c-1.11 0-1.99.9-1.99 2L3 19c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V8h14v11z"/></svg>
            </div>
            <div>
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">TOTAL RESERVASI</span>
                    <span class="p-2 bg-emerald-50 text-emerald-600 rounded-lg">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    </span>
                </div>
                <div class="text-4xl font-extrabold text-slate-800 tracking-tight">{{ $totalReservations }}</div>
            </div>
            <div class="mt-4 pt-3 border-t border-slate-100 flex items-center text-[11px] font-medium text-emerald-600">
                <span>Aktif & riwayat penggunaan</span>
            </div>
        </div>

        <!-- Card Total Laporan Kerusakan -->
        <div class="relative bg-white p-6 rounded-2xl shadow-sm border border-slate-200/80 flex flex-col justify-between overflow-hidden group hover:shadow-md transition">
            <div class="absolute -right-4 -bottom-4 opacity-5 text-rose-600 group-hover:scale-110 transition-transform">
                <svg class="w-32 h-32" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/></svg>
            </div>
            <div>
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">LAPORAN KERUSAKAN</span>
                    <span class="p-2 bg-rose-50 text-rose-600 rounded-lg">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    </span>
                </div>
                <div class="text-4xl font-extrabold text-slate-800 tracking-tight">{{ $totalReports }}</div>
            </div>
            <div class="mt-4 pt-3 border-t border-slate-100 flex items-center text-[11px] font-medium text-rose-600">
                <span>Perlu tindakan dari petugas</span>
            </div>
        </div>
    </div>

    <!-- TAB NAVIGATION BAR (WINDOWS / SAAS STYLE) -->
    <div class="bg-white p-1.5 rounded-2xl border border-slate-200/80 shadow-sm flex flex-wrap gap-1 text-xs font-semibold">
        <button @click="activeTab = 'overview'" 
                :class="activeTab === 'overview' ? 'bg-blue-600 text-white shadow-md shadow-blue-500/20' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900'" 
                class="px-5 py-3 rounded-xl transition-all duration-200 flex items-center gap-2">
            <span>📊</span> Rekap & Ekspor Data
        </button>
        <button @click="activeTab = 'verify'" 
                :class="activeTab === 'verify' ? 'bg-blue-600 text-white shadow-md shadow-blue-500/20' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900'" 
                class="px-5 py-3 rounded-xl transition-all duration-200 flex items-center gap-2 relative">
            <span>✅</span> Verifikasi Akun
            @if(count($pendingUsers) > 0)
                <span class="bg-rose-500 text-white text-[10px] px-2 py-0.5 rounded-full font-bold shadow-sm animate-pulse">{{ count($pendingUsers) }}</span>
            @endif
        </button>
        <button @click="activeTab = 'addUser'" 
                :class="activeTab === 'addUser' ? 'bg-blue-600 text-white shadow-md shadow-blue-500/20' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900'" 
                class="px-5 py-3 rounded-xl transition-all duration-200 flex items-center gap-2">
            <span>👤</span> Tambah Akun Baru
        </button>
        <button @click="activeTab = 'facilities'" 
                :class="activeTab === 'facilities' ? 'bg-blue-600 text-white shadow-md shadow-blue-500/20' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900'" 
                class="px-5 py-3 rounded-xl transition-all duration-200 flex items-center gap-2">
            <span>🏢</span> Kelola Data Fasilitas
        </button>
    </div>

    <!-- CONTAINER PANEL UTAMA -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 p-6">
        
        <!-- PANEL 1: REKAP OKUPANSI & EXPORT -->
        <div x-show="activeTab === 'overview'" x-cloak class="space-y-6">
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 border-b border-slate-100 pb-5">
                <div>
                    <h2 class="text-lg font-bold text-slate-800">Rekap Okupansi & Laporan Kerusakan</h2>
                    <p class="text-xs text-slate-500 mt-0.5">Ringkasan penggunaan fasilitas serta ekspor data lengkap dalam berbagai format.</p>
                </div>

                <!-- Export Action Buttons -->
                <div class="flex items-center gap-2">
                    <a href="{{ route('admin.rekap.export', array_merge(['format' => 'csv'], request()->query())) }}" class="px-4 py-2 bg-slate-100 border border-slate-200 rounded-xl text-xs font-bold text-slate-700 hover:bg-slate-200 transition flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg> CSV
                    </a>
                    <a href="{{ route('admin.rekap.export', array_merge(['format' => 'excel'], request()->query())) }}" class="px-4 py-2 bg-emerald-600 text-white rounded-xl text-xs font-bold hover:bg-emerald-700 transition shadow-sm shadow-emerald-600/20 flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3M3 17V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"/></svg> Excel
                    </a>
                    <a href="{{ route('admin.rekap.export', array_merge(['format' => 'pdf'], request()->query())) }}" class="px-4 py-2 bg-rose-600 text-white rounded-xl text-xs font-bold hover:bg-rose-700 transition shadow-sm shadow-rose-600/20 flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg> PDF
                    </a>
                </div>
            </div>

            <!-- Filter Tanggal & Presets -->
            <form method="GET" action="{{ route('admin.dashboard') }}" class="bg-slate-50/80 p-4 rounded-xl border border-slate-200/80 flex flex-wrap items-center justify-between gap-4 text-xs">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="font-bold text-slate-700">Periode:</span>
                    <input type="date" name="start_date" value="{{ request('start_date') }}" class="px-3 py-1.5 border border-slate-200 rounded-lg bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                    <span class="text-slate-400">s/d</span>
                    <input type="date" name="end_date" value="{{ request('end_date') }}" class="px-3 py-1.5 border border-slate-200 rounded-lg bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                    <button type="submit" class="bg-blue-600 text-white px-4 py-1.5 rounded-lg font-bold hover:bg-blue-700 transition shadow-sm">Terapkan Filter</button>
                    @if(request('start_date') || request('end_date'))
                        <a href="{{ route('admin.dashboard') }}" class="text-xs text-rose-600 font-semibold hover:underline ml-1">Reset Filter</a>
                    @endif
                </div>

                <div class="flex items-center gap-1.5">
                    <span class="text-slate-400 text-[11px] mr-1">Preset Rentang:</span>
                    <a href="{{ route('admin.dashboard', ['start_date' => now()->subDays(7)->format('Y-m-d'), 'end_date' => now()->format('Y-m-d')]) }}" class="px-2.5 py-1 bg-white border border-slate-200 rounded-lg text-slate-600 hover:bg-slate-100 font-medium transition">1 Minggu</a>
                    <a href="{{ route('admin.dashboard', ['start_date' => now()->subMonth()->format('Y-m-d'), 'end_date' => now()->format('Y-m-d')]) }}" class="px-2.5 py-1 bg-white border border-slate-200 rounded-lg text-slate-600 hover:bg-slate-100 font-medium transition">1 Bulan</a>
                    <a href="{{ route('admin.dashboard', ['start_date' => now()->subYear()->format('Y-m-d'), 'end_date' => now()->format('Y-m-d')]) }}" class="px-2.5 py-1 bg-white border border-slate-200 rounded-lg text-slate-600 hover:bg-slate-100 font-medium transition">1 Tahun</a>
                </div>
            </form>

            <div class="overflow-x-auto rounded-xl border border-slate-200/80">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="bg-slate-50/80 text-slate-500 border-b border-slate-200/80 uppercase text-[10px] font-bold tracking-wider">
                            <th class="py-3.5 px-4">Fasilitas</th>
                            <th class="py-3.5 px-4">Lokasi</th>
                            <th class="py-3.5 px-4">Total Reservasi (Okupansi)</th>
                            <th class="py-3.5 px-4">Total Laporan Kerusakan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($facilities as $f)
                        <tr class="hover:bg-slate-50/50 transition">
                            <td class="py-3 px-4 font-semibold text-slate-800">{{ $f->nama_fasilitas }}</td>
                            <td class="py-3 px-4 text-slate-500">{{ $f->lokasi }}</td>
                            <td class="py-3 px-4 text-slate-700 font-bold">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-100">
                                    {{ $f->reservations_count ?? 0 }} kali
    <!-- TAB 2: VERIFIKASI AKUN PENGGUNA -->
    <div x-show="activeTab === 'verifikasi'" x-cloak class="bg-white rounded-2xl p-6 shadow border border-slate-100">
        <h2 class="font-bold text-slate-800 mb-4 text-sm">Daftar Verifikasi Akun Pengguna</h2>
        <div class="overflow-x-auto">
            <table class="w-full text-xs">
                <thead>
                    <tr class="text-left text-slate-400 border-b uppercase text-[10px]">
                        <th class="py-2 pr-4">Nama</th>
                        <th class="py-2 pr-4">NIM / NIP</th>
                        <th class="py-2 pr-4">Email</th>
                        <th class="py-2 pr-4">No. HP</th>
                        <th class="py-2 pr-4">KTM / KTP</th>
                        <th class="py-2 pr-4">Role</th>
                        <th class="py-2 pr-4">Status</th>
                        <th class="py-2 pr-4">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($users as $u)
                        <tr>
                            <td class="py-3 pr-4 font-bold text-slate-800">{{ $u->name }}</td>
                            <td class="py-3 pr-4 text-slate-600">{{ $u->nim_nip ?? '-' }}</td>
                            <td class="py-3 pr-4 text-slate-600">{{ $u->email }}</td>
                            <td class="py-3 pr-4 text-slate-600">{{ $u->no_hp ?? '-' }}</td>
                            <td class="py-3 pr-4">
                                @if($u->ktm_path)
                                    <a href="{{ route('admin.users.ktm', $u->id) }}" target="_blank" rel="noopener" class="text-blue-700 font-bold hover:underline">Lihat berkas</a>
                                @else
                                    <span class="text-slate-400">-</span>
                                @endif
                            </td>
                            <td class="py-3 pr-4 text-slate-600">{{ ucfirst($u->role) }}</td>
                            <td class="py-3 pr-4">
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold
                                    @if($u->status_verifikasi == 'verified') bg-emerald-100 text-emerald-700
                                    @elseif($u->status_verifikasi == 'rejected') bg-rose-100 text-rose-700
                                    @else bg-amber-100 text-amber-700 @endif">
                                    {{ ucfirst($u->status_verifikasi) }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-rose-600 font-bold">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-rose-50 text-rose-700 border border-rose-100">
                                    {{ $f->reports_count ?? 0 }} laporan
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- PANEL 2: VERIFIKASI AKUN -->
        <div x-show="activeTab === 'verify'" x-cloak class="space-y-6">
            <div class="border-b border-slate-100 pb-4">
                <h2 class="text-lg font-bold text-slate-800">Permintaan Verifikasi Akun Pengguna</h2>
                <p class="text-xs text-slate-500 mt-0.5">Daftar pengguna baru yang memerlukan persetujuan administrator untuk mengakses sistem.</p>
            </div>

            <div class="overflow-x-auto rounded-xl border border-slate-200/80">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="bg-slate-50/80 text-slate-500 border-b border-slate-200/80 uppercase text-[10px] font-bold tracking-wider">
                            <th class="py-3.5 px-4">Nama Lengkap</th>
                            <th class="py-3.5 px-4">Email</th>
                            <th class="py-3.5 px-4">NIM / NIP</th>
                            <th class="py-3.5 px-4 text-right">Aksi Permohonan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($pendingUsers as $user)
                        <tr class="hover:bg-slate-50/50 transition">
                            <td class="py-3 px-4 font-semibold text-slate-800">{{ $user->name }}</td>
                            <td class="py-3 px-4 text-slate-500">{{ $user->email }}</td>
                            <td class="py-3 px-4 text-slate-500 font-mono">{{ $user->nip ?? '-' }}</td>
                            <td class="py-3 px-4 text-right">
                                <div class="flex justify-end gap-2">
                                    <form action="{{ route('admin.users.verify', $user->id) }}" method="POST">
                                        @csrf
                                        <button class="px-3 py-1.5 bg-emerald-600 text-white font-bold rounded-lg hover:bg-emerald-700 transition shadow-sm">Setujui</button>
                                    </form>
                                    <form action="{{ route('admin.users.reject', $user->id) }}" method="POST">
                                        @csrf
                                        <button class="px-3 py-1.5 bg-rose-600 text-white font-bold rounded-lg hover:bg-rose-700 transition shadow-sm">Tolak</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                    @empty
                        <tr><td colspan="8" class="py-6 text-center text-slate-400">Belum ada pengguna terdaftar.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- TAB 3: SEMUA RESERVASI -->
    <div x-show="activeTab === 'reservasi'" x-cloak class="bg-white rounded-2xl p-6 shadow border border-slate-100">
        <h2 class="font-bold text-slate-800 mb-4 text-sm">Rekapitulasi Semua Reservasi Fasilitas</h2>
        <div class="overflow-x-auto">
            <table class="w-full text-xs">
                <thead>
                    <tr class="text-left text-slate-400 border-b uppercase text-[10px]">
                        <th class="py-2 pr-4">Pemohon</th>
                        <th class="py-2 pr-4">Fasilitas</th>
                        <th class="py-2 pr-4">Tanggal</th>
                        <th class="py-2 pr-4">Jam</th>
                        <th class="py-2 pr-4">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($reservations as $r)
                        <tr>
                            <td colspan="4" class="py-8 text-center text-slate-400">
                                <div class="flex flex-col items-center justify-center gap-1">
                                    <span class="text-2xl">✨</span>
                                    <span>Tidak ada antrean verifikasi akun saat ini.</span>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- PANEL 3: TAMBAH AKUN BARU -->
        <div x-show="activeTab === 'addUser'" x-cloak class="space-y-6">
            <div class="border-b border-slate-100 pb-4">
                <h2 class="text-lg font-bold text-slate-800">Registrasi Akun Baru (Direct)</h2>
                <p class="text-xs text-slate-500 mt-0.5">Tambah akun Petugas, Dosen, Mahasiswa, atau Staf secara langsung tanpa melalui proses verifikasi pendaftaran.</p>
            </div>

            <form action="{{ route('admin.users.store') }}" method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-5 text-xs">
                @csrf
                <div class="space-y-1.5">
                    <label class="block font-semibold text-slate-700">Nama Lengkap</label>
                    <input type="text" name="name" placeholder="Masukkan nama lengkap" class="w-full p-3 bg-slate-50/50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500" required>
                </div>
                <div class="space-y-1.5">
                    <label class="block font-semibold text-slate-700">Role / Peran Akses</label>
                    <select name="role" class="w-full p-3 bg-slate-50/50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500" required>
                        <option value="petugas">Petugas Facility</option>
                        <option value="mahasiswa">Mahasiswa</option>
                        <option value="dosen">Dosen</option>
                        <option value="staf">Staf Kampus</option>
                    </select>
                </div>
                <div class="space-y-1.5">
                    <label class="block font-semibold text-slate-700">Alamat Email</label>
                    <input type="email" name="email" placeholder="nama@kampus.ac.id" class="w-full p-3 bg-slate-50/50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500" required>
                </div>
                <div class="space-y-1.5">
                    <label class="block font-semibold text-slate-700">NIP / NIM</label>
                    <input type="text" name="nip_nim" placeholder="Nomor Identitas Utama" class="w-full p-3 bg-slate-50/50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                </div>
                <div class="space-y-1.5">
                    <label class="block font-semibold text-slate-700">No. HP / Unit Divisi</label>
                    <input type="text" name="no_hp" placeholder="08xx-xxxx-xxxx / Divisi IT" class="w-full p-3 bg-slate-50/50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                </div>
                <div class="space-y-1.5">
                    <label class="block font-semibold text-slate-700">Password Awal</label>
                    <input type="password" name="password" placeholder="••••••••" class="w-full p-3 bg-slate-50/50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500" required>
                </div>
                <div class="md:col-span-2 flex justify-end pt-2">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold px-6 py-3 rounded-xl transition shadow-md shadow-blue-500/20">Simpan Akun Baru</button>
                </div>
            </form>
        </div>

        <!-- PANEL 4: KELOLA DATA FASILITAS -->
        <div x-show="activeTab === 'facilities'" x-cloak class="space-y-6">
            <div class="border-b border-slate-100 pb-4">
                <h2 class="text-lg font-bold text-slate-800">Master Data & Status Fasilitas</h2>
                <p class="text-xs text-slate-500 mt-0.5">Tambah fasilitas baru atau nonaktifkan fasilitas yang sedang tidak bisa dipinjam.</p>
            </div>

            <!-- Form Tambah Fasilitas -->
            <form action="{{ route('admin.facilities.store') }}" method="POST" class="grid grid-cols-1 md:grid-cols-4 gap-3 text-xs bg-slate-50/80 p-4 rounded-xl border border-slate-200/80">
                @csrf
                <input type="text" name="nama_fasilitas" placeholder="Nama Fasilitas" class="p-2.5 bg-white border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500" required>
                
                <select name="tipe" class="p-2.5 bg-white border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500" required>
                    <option value="" disabled selected>Pilih Tipe</option>
                    <option value="Ruangan">Ruangan</option>
                    <option value="Laboratorium">Laboratorium</option>
                    <option value="Olahraga">Olahraga</option>
                    <option value="Fasilitas Umum">Fasilitas Umum</option>
                </select>

                <input type="text" name="lokasi" placeholder="Lokasi Gedung / Lantai" class="p-2.5 bg-white border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500" required>
                
                <div class="flex gap-2">
                    <input type="number" name="kapasitas" placeholder="Kapasitas Orang" class="p-2.5 bg-white border border-slate-200 rounded-lg w-full focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500" required>
                    <button type="submit" class="bg-blue-600 text-white font-bold px-4 py-2.5 rounded-lg hover:bg-blue-700 transition shrink-0 shadow-sm">+ Tambah</button>
                </div>
            </form>

            <div class="overflow-x-auto rounded-xl border border-slate-200/80">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="bg-slate-50/80 text-slate-500 border-b border-slate-200/80 uppercase text-[10px] font-bold tracking-wider">
                            <th class="py-3.5 px-4">Fasilitas</th>
                            <th class="py-3.5 px-4">Tipe</th>
                            <th class="py-3.5 px-4">Lokasi Gedung</th>
                            <th class="py-3.5 px-4">Status Pemakaian</th>
                            <th class="py-3.5 px-4 text-right">Aksi Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($facilities as $f)
                        <tr class="hover:bg-slate-50/50 transition">
                            <td class="py-3 px-4 font-semibold text-slate-800">{{ $f->nama_fasilitas }}</td>
                            <td class="py-3 px-4 text-slate-500">{{ $f->tipe }}</td>
                            <td class="py-3 px-4 text-slate-500">{{ $f->lokasi }}</td>
                            <td class="py-3 px-4">
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold inline-flex items-center gap-1 {{ $f->status === 'aktif' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-rose-50 text-rose-700 border border-rose-200' }}">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $f->status === 'aktif' ? 'bg-emerald-500' : 'bg-rose-500' }}"></span>
                                    {{ ucfirst($f->status) }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-right">
                                <form action="{{ route('admin.facilities.toggle', $f->id) }}" method="POST" class="inline">
                                    @csrf
                                    <button class="text-xs font-bold px-3 py-1.5 border border-slate-200 rounded-lg hover:bg-slate-100 transition">
                                        {{ $f->status === 'aktif' ? 'Nonaktifkan' : 'Aktifkan' }}
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

    </div>

</main>
@endsection