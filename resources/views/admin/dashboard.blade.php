@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8"
    x-data="{
        activeTab: 'overview', // Pilihan: 'overview', 'verifikasi', 'kelola', 'reservasi'
        showPetugasModal: false,
        showPenggunaModal: false,
        showFasilitasModal: false
    }">
    
    <!-- Header & Tombol Utama -->
    <div class="flex items-center justify-between flex-wrap gap-3">
        <div>
            <h1 class="text-2xl font-black text-slate-900">Panel Admin</h1>
            <p class="text-sm text-slate-500">Kelola pengguna, petugas, fasilitas, dan rekap okupansi.</p>
        </div>
        <a href="{{ route('admin.rekap.okupansi') }}" class="px-4 py-2 bg-blue-900 hover:bg-blue-800 text-white text-xs font-bold rounded-xl transition shadow">
            Export Rekap Okupansi (CSV)
        </a>
    </div>

    <!-- Statistik Ringkas (Selalu Muncul di Atas) -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl p-5 shadow border border-slate-100">
            <p class="text-xs font-bold text-slate-500">Total Pengguna</p>
            <p class="text-2xl font-black text-blue-900">{{ $users->count() }}</p>
        </div>
        <div class="bg-white rounded-2xl p-5 shadow border border-slate-100">
            <p class="text-xs font-bold text-slate-500">Total Fasilitas</p>
            <p class="text-2xl font-black text-emerald-600">{{ $facilities->count() }}</p>
        </div>
        <div class="bg-white rounded-2xl p-5 shadow border border-slate-100">
            <p class="text-xs font-bold text-slate-500">Total Reservasi</p>
            <p class="text-2xl font-black text-slate-800">{{ $reservations->count() }}</p>
        </div>
        <div class="bg-white rounded-2xl p-5 shadow border border-slate-100">
            <p class="text-xs font-bold text-slate-500">Total Laporan</p>
            <p class="text-2xl font-black text-rose-600">{{ $reports->count() }}</p>
        </div>
    </div>

    <!-- Navigasi Menu Tab Admin -->
    <div class="flex flex-wrap gap-2 border-b border-slate-200 pb-3">
        <button @click="activeTab = 'overview'" 
            :class="activeTab === 'overview' ? 'bg-blue-900 text-white shadow' : 'bg-white text-slate-600 hover:bg-slate-100'"
            class="px-4 py-2 text-xs font-bold rounded-xl transition">
            Ringkasan & Aksi Cepat
        </button>
        <button @click="activeTab = 'verifikasi'" 
            :class="activeTab === 'verifikasi' ? 'bg-blue-900 text-white shadow' : 'bg-white text-slate-600 hover:bg-slate-100'"
            class="px-4 py-2 text-xs font-bold rounded-xl transition">
            Verifikasi Akun Pengguna
        </button>
        <button @click="activeTab = 'reservasi'" 
            :class="activeTab === 'reservasi' ? 'bg-blue-900 text-white shadow' : 'bg-white text-slate-600 hover:bg-slate-100'"
            class="px-4 py-2 text-xs font-bold rounded-xl transition">
            Semua Reservasi
        </button>
    </div>

    <!-- TAB 1: OVERVIEW & AKSI (Pendaftaran Petugas, Pengguna, Fasilitas) -->
    <div x-show="activeTab === 'overview'" class="space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Card Menu 1 -->
            <div @click="showPetugasModal = true" class="bg-white rounded-2xl p-6 shadow border border-slate-100 cursor-pointer hover:border-emerald-500 transition group">
                <span class="bg-emerald-100 text-emerald-700 text-[10px] font-bold px-2.5 py-1 rounded-full">Manajemen Petugas</span>
                <h3 class="font-black text-slate-800 text-base mt-3 group-hover:text-emerald-600 transition">Daftarkan Petugas Sarana</h3>
                <p class="text-xs text-slate-500 mt-1">Tambah akun baru untuk petugas operasional fasilitas kampus.</p>
            </div>
            <!-- Card Menu 2 -->
            <div @click="showPenggunaModal = true" class="bg-white rounded-2xl p-6 shadow border border-slate-100 cursor-pointer hover:border-blue-500 transition group">
                <span class="bg-blue-100 text-blue-700 text-[10px] font-bold px-2.5 py-1 rounded-full">Manajemen Akun</span>
                <h3 class="font-black text-slate-800 text-base mt-3 group-hover:text-blue-600 transition">Tambah Akun Dosen/Staf</h3>
                <p class="text-xs text-slate-500 mt-1">Daftarkan akun khusus dosen atau staf yang langsung terverifikasi.</p>
            </div>
            <!-- Card Menu 3 -->
            <div @click="showFasilitasModal = true" class="bg-white rounded-2xl p-6 shadow border border-slate-100 cursor-pointer hover:border-slate-800 transition group">
                <span class="bg-slate-100 text-slate-700 text-[10px] font-bold px-2.5 py-1 rounded-full">Master Data</span>
                <h3 class="font-black text-slate-800 text-base mt-3 group-hover:text-slate-900 transition">Tambah Fasilitas Baru</h3>
                <p class="text-xs text-slate-500 mt-1">Tambahkan ruang kelas, laboratorium, atau sarana olahraga baru.</p>
            </div>
        </div>
    </div>

    <!-- TAB 2: VERIFIKASI AKUN PENGGUNA -->
    <div x-show="activeTab === 'verifikasi'" x-cloak class="bg-white rounded-2xl p-6 shadow border border-slate-100">
        <h2 class="font-bold text-slate-800 mb-4 text-sm">Daftar Verifikasi Akun Pengguna</h2>
        <div class="overflow-x-auto">
            <table class="w-full text-xs">
                <thead>
                    <tr class="text-left text-slate-400 border-b uppercase text-[10px]">
                        <th class="py-2 pr-4">Nama</th>
                        <th class="py-2 pr-4">Email</th>
                        <th class="py-2 pr-4">Role</th>
                        <th class="py-2 pr-4">Status</th>
                        <th class="py-2 pr-4">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($users as $u)
                        <tr>
                            <td class="py-3 pr-4 font-bold text-slate-800">{{ $u->name }}</td>
                            <td class="py-3 pr-4 text-slate-600">{{ $u->email }}</td>
                            <td class="py-3 pr-4 text-slate-600">{{ ucfirst($u->role) }}</td>
                            <td class="py-3 pr-4">
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold
                                    @if($u->status_verifikasi == 'verified') bg-emerald-100 text-emerald-700
                                    @elseif($u->status_verifikasi == 'rejected') bg-rose-100 text-rose-700
                                    @else bg-amber-100 text-amber-700 @endif">
                                    {{ ucfirst($u->status_verifikasi) }}
                                </span>
                            </td>
                            <td class="py-3 pr-4 flex gap-2">
                                @if($u->status_verifikasi == 'pending')
                                    <form action="{{ route('admin.users.verify', $u->id) }}" method="POST">
                                        @csrf
                                        <button class="px-3 py-1 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg font-bold">Verifikasi</button>
                                    </form>
                                    <form action="{{ route('admin.users.reject', $u->id) }}" method="POST">
                                        @csrf
                                        <button class="px-3 py-1 bg-rose-600 hover:bg-rose-700 text-white rounded-lg font-bold">Tolak</button>
                                    </form>
                                @else
                                    <span class="text-slate-400 italic">Selesai</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="py-6 text-center text-slate-400">Belum ada pengguna terdaftar.</td></tr>
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
                            <td class="py-3 pr-4 font-bold text-slate-800">{{ $r->user->name ?? '-' }}</td>
                            <td class="py-3 pr-4 text-slate-600">{{ $r->facility->nama_fasilitas ?? '-' }}</td>
                            <td class="py-3 pr-4 text-slate-600">{{ $r->tanggal }}</td>
                            <td class="py-3 pr-4 text-slate-600">{{ $r->start_time }} - {{ $r->end_time }}</td>
                            <td class="py-3 pr-4">
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700">
                                    {{ ucfirst($r->status) }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="py-6 text-center text-slate-400">Belum ada reservasi.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- MODAL 1: TAMBAH PETUGAS -->
    <div x-show="showPetugasModal" x-cloak class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div @click.outside="showPetugasModal = false" class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl relative space-y-4">
            <div class="flex justify-between items-center border-b pb-3">
                <h3 class="font-bold text-slate-800 text-sm">Daftarkan Petugas Sarana</h3>
                <button @click="showPetugasModal = false" class="text-slate-400 hover:text-slate-600">✕</button>
            </div>
            <form action="{{ route('admin.petugas.store') }}" method="POST" class="space-y-3 text-xs">
                @csrf
                <input type="text" name="name" placeholder="Nama Lengkap" class="w-full p-2.5 bg-slate-50 border border-slate-300 rounded-xl" required>
                <input type="email" name="email" placeholder="Email" class="w-full p-2.5 bg-slate-50 border border-slate-300 rounded-xl" required>
                <input type="password" name="password" placeholder="Password (default: password)" class="w-full p-2.5 bg-slate-50 border border-slate-300 rounded-xl">
                <button type="submit" class="w-full py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl transition">Simpan Petugas</button>
            </form>
        </div>
    </div>

    <!-- MODAL 2: TAMBAH PENGGUNA (DOSEN/STAF) -->
    <div x-show="showPenggunaModal" x-cloak class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div @click.outside="showPenggunaModal = false" class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl relative space-y-4">
            <div class="flex justify-between items-center border-b pb-3">
                <h3 class="font-bold text-slate-800 text-sm">Tambah Akun Dosen/Staf</h3>
                <button @click="showPenggunaModal = false" class="text-slate-400 hover:text-slate-600">✕</button>
            </div>
            <form action="{{ route('admin.pengguna.store') }}" method="POST" class="space-y-3 text-xs">
                @csrf
                <input type="text" name="name" placeholder="Nama Lengkap" class="w-full p-2.5 bg-slate-50 border border-slate-300 rounded-xl" required>
                <input type="email" name="email" placeholder="Email" class="w-full p-2.5 bg-slate-50 border border-slate-300 rounded-xl" required>
                <input type="password" name="password" placeholder="Password (default: password)" class="w-full p-2.5 bg-slate-50 border border-slate-300 rounded-xl">
                <button type="submit" class="w-full py-2.5 bg-blue-800 hover:bg-blue-900 text-white font-bold rounded-xl transition">Simpan Akun</button>
            </form>
        </div>
    </div>

    <!-- MODAL 3: TAMBAH FASILITAS -->
    <div x-show="showFasilitasModal" x-cloak class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div @click.outside="showFasilitasModal = false" class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl relative space-y-4">
            <div class="flex justify-between items-center border-b pb-3">
                <h3 class="font-bold text-slate-800 text-sm">Tambah Fasilitas Baru</h3>
                <button @click="showFasilitasModal = false" class="text-slate-400 hover:text-slate-600">✕</button>
            </div>
            <form action="{{ route('admin.facilities.store') }}" method="POST" class="space-y-3 text-xs">
                @csrf
                <input type="text" name="nama_fasilitas" placeholder="Nama Fasilitas" class="w-full p-2.5 bg-slate-50 border border-slate-300 rounded-xl" required>
                <select name="tipe" class="w-full p-2.5 bg-slate-50 border border-slate-300 rounded-xl" required>
                    <option value="">Pilih Tipe</option>
                    <option value="Ruangan">Ruangan</option>
                    <option value="Laboratorium">Laboratorium</option>
                    <option value="Olahraga">Olahraga</option>
                    <option value="Fasilitas Umum">Fasilitas Umum</option>
                </select>
                <input type="text" name="lokasi" placeholder="Lokasi / Gedung" class="w-full p-2.5 bg-slate-50 border border-slate-300 rounded-xl" required>
                <input type="number" name="kapasitas" placeholder="Kapasitas" min="1" class="w-full p-2.5 bg-slate-50 border border-slate-300 rounded-xl" required>
                <textarea name="deskripsi" placeholder="Deskripsi (opsional)" rows="2" class="w-full p-2.5 bg-slate-50 border border-slate-300 rounded-xl"></textarea>
                <button type="submit" class="w-full py-2.5 bg-slate-800 hover:bg-slate-900 text-white font-bold rounded-xl transition">Simpan Fasilitas</button>
            </form>
        </div>
    </div>

</div>
@endsection