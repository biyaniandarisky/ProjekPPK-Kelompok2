@extends('layouts.app')

@section('content')
<div class="space-y-8">
    <!-- Hero Traveloka Search -->
    <section class="bg-blue-900 text-white py-12 px-4 relative">
        <div class="max-w-7xl mx-auto space-y-6">
            <div class="max-w-2xl">
                <span class="px-3 py-1 bg-emerald-500/20 text-emerald-300 rounded-full text-xs font-bold border border-emerald-400/30">
                    Sistem Reservasi Resmi Kampus
                </span>
                <h1 class="text-3xl sm:text-4xl font-black tracking-tight mt-3">
                    Pinjam Ruang Kelas, Lab, & Sarana Olahraga Kampus
                </h1>
                <p class="text-blue-200 text-xs sm:text-sm mt-2">
                    Cek ketersediaan slot 30 menit secara real-time, bebas bentrok jadwal, dan ajukan peminjaman dengan cepat.
                </p>
            </div>

            <!-- Box Pencarian -->
            <div class="bg-white rounded-3xl p-5 shadow-2xl text-slate-800 border border-slate-100">
                <form action="{{ route('landing') }}" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4 text-xs">
                    <div>
                        <label class="block font-bold text-slate-600 mb-1">Cari Nama / Gedung</label>
                        <input type="text" name="q" value="{{ request('q') }}" placeholder="Contoh: Lab Komputer, Aula..." class="w-full p-2.5 bg-slate-50 border border-slate-300 rounded-xl focus:outline-none">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-600 mb-1">Kategori Fasilitas</label>
                        <select name="tipe" class="w-full p-2.5 bg-slate-50 border border-slate-300 rounded-xl focus:outline-none">
                            <option value="Semua">Semua Kategori</option>
                            <option value="Ruangan" {{ request('tipe') == 'Ruangan' ? 'selected' : '' }}>Ruang Kuliah</option>
                            <option value="Laboratorium" {{ request('tipe') == 'Laboratorium' ? 'selected' : '' }}>Laboratorium</option>
                            <option value="Olahraga" {{ request('tipe') == 'Olahraga' ? 'selected' : '' }}>Sarana Olahraga</option>
                            <option value="Fasilitas Umum" {{ request('tipe') == 'Fasilitas Umum' ? 'selected' : '' }}>Aula & Umum</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-bold text-slate-600 mb-1">Tanggal Kegiatan</label>
                        <input type="date" name="tanggal" value="{{ request('tanggal', date('Y-m-d', strtotime('+1 day'))) }}" class="w-full p-2.5 bg-slate-50 border border-slate-300 rounded-xl focus:outline-none">
                    </div>
                    <div class="flex items-end">
                        <button type="submit" class="w-full py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl shadow transition">
                            Cari Ketersediaan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </section>

    <!-- Daftar Fasilitas -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h2 class="text-xl font-black text-slate-900 mb-4">Katalog Fasilitas Siap Digunakan ({{ $facilities->count() }})</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($facilities as $fac)
                <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-sm flex flex-col justify-between">
                    <div>
                        <div class="h-44 bg-slate-100 relative">
                            <img src="{{ $fac->foto }}" alt="{{ $fac->nama_fasilitas }}" class="w-full h-full object-cover">
                            <span class="absolute top-2 left-2 px-2 py-0.5 bg-blue-900/90 text-white rounded text-[10px] font-bold">{{ $fac->tipe }}</span>
                        </div>
                        <div class="p-4 space-y-2 text-xs">
                            <h3 class="font-bold text-slate-900 text-base">{{ $fac->nama_fasilitas }}</h3>
                            <div class="text-slate-500">Lokasi: {{ $fac->lokasi }} • Kapasitas: {{ $fac->kapasitas }} orang</div>
                            <p class="text-slate-600 line-clamp-2">{{ $fac->deskripsi }}</p>
                        </div>
                    </div>
                    <div class="p-4 pt-0">
                        <a href="{{ route('pengguna.dashboard') }}" class="block w-full py-2.5 text-center bg-blue-900 hover:bg-blue-800 text-white font-bold text-xs rounded-xl transition">
                            Pesan Fasilitas Ini
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    </section>
</div>
@endsection