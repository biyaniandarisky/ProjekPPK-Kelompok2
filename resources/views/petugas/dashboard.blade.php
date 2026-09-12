@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">
    <div>
        <h1 class="text-2xl font-black text-slate-900">Panel Petugas Sarana</h1>
        <p class="text-sm text-slate-500">Verifikasi reservasi, kelola laporan kendala, dan status fasilitas.</p>
    </div>

    <!-- Reservasi Menunggu -->
    <div class="bg-white rounded-2xl p-6 shadow border border-slate-100">
        <h2 class="font-bold text-slate-800 mb-4">Reservasi Menunggu Persetujuan</h2>
        <div class="overflow-x-auto">
            <table class="w-full text-xs">
                <thead>
                    <tr class="text-left text-slate-500 border-b">
                        <th class="py-2 pr-4">Pemohon</th>
                        <th class="py-2 pr-4">Fasilitas</th>
                        <th class="py-2 pr-4">Tanggal</th>
                        <th class="py-2 pr-4">Jam</th>
                        <th class="py-2 pr-4">Tujuan</th>
                        <th class="py-2 pr-4">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pendingReservations as $r)
                        <tr class="border-b border-slate-100">
                            <td class="py-2 pr-4 font-bold">{{ $r->user->name ?? '-' }}</td>
                            <td class="py-2 pr-4">{{ $r->facility->nama_fasilitas ?? '-' }}</td>
                            <td class="py-2 pr-4">{{ $r->tanggal }}</td>
                            <td class="py-2 pr-4">{{ $r->start_time }} - {{ $r->end_time }}</td>
                            <td class="py-2 pr-4">{{ $r->tujuan }}</td>
                            <td class="py-2 pr-4 flex gap-2">
                                <form action="{{ route('petugas.reservasi.approve', $r->id) }}" method="POST">
                                    @csrf
                                    <button class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg font-bold">Setujui</button>
                                </form>
                                <form action="{{ route('petugas.reservasi.reject', $r->id) }}" method="POST">
                                    @csrf
                                    <button class="px-3 py-1.5 bg-rose-600 hover:bg-rose-700 text-white rounded-lg font-bold">Tolak</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="py-4 text-center text-slate-400">Tidak ada reservasi menunggu.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Reservasi Disetujui -->
    <div class="bg-white rounded-2xl p-6 shadow border border-slate-100">
        <h2 class="font-bold text-slate-800 mb-4">Reservasi Disetujui</h2>
        <div class="overflow-x-auto">
            <table class="w-full text-xs">
                <thead>
                    <tr class="text-left text-slate-500 border-b">
                        <th class="py-2 pr-4">Pemohon</th>
                        <th class="py-2 pr-4">Fasilitas</th>
                        <th class="py-2 pr-4">Tanggal</th>
                        <th class="py-2 pr-4">Jam</th>
                        <th class="py-2 pr-4">Aksi Darurat</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($approvedReservations as $r)
                        <tr class="border-b border-slate-100">
                            <td class="py-2 pr-4 font-bold">{{ $r->user->name ?? '-' }}</td>
                            <td class="py-2 pr-4">{{ $r->facility->nama_fasilitas ?? '-' }}</td>
                            <td class="py-2 pr-4">{{ $r->tanggal }}</td>
                            <td class="py-2 pr-4">{{ $r->start_time }} - {{ $r->end_time }}</td>
                            <td class="py-2 pr-4">
                                <form action="{{ route('petugas.reservasi.emergency_cancel', $r->id) }}" method="POST" class="flex gap-2">
                                    @csrf
                                    <input type="text" name="alasan_batal" placeholder="Alasan pembatalan" class="p-1.5 bg-slate-50 border border-slate-300 rounded-lg text-[10px]" required>
                                    <button class="px-3 py-1.5 bg-amber-500 hover:bg-amber-600 text-white rounded-lg font-bold whitespace-nowrap">Batal Darurat</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="py-4 text-center text-slate-400">Belum ada reservasi disetujui.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Laporan Kendala -->
    <div class="bg-white rounded-2xl p-6 shadow border border-slate-100">
        <h2 class="font-bold text-slate-800 mb-4">Laporan Kendala Fasilitas</h2>
        <div class="overflow-x-auto">
            <table class="w-full text-xs">
                <thead>
                    <tr class="text-left text-slate-500 border-b">
                        <th class="py-2 pr-4">Pelapor</th>
                        <th class="py-2 pr-4">Fasilitas</th>
                        <th class="py-2 pr-4">Kategori</th>
                        <th class="py-2 pr-4">Status</th>
                        <th class="py-2 pr-4">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($reports as $r)
                        <tr class="border-b border-slate-100">
                            <td class="py-2 pr-4 font-bold">{{ $r->user->name ?? '-' }}</td>
                            <td class="py-2 pr-4">{{ $r->facility->nama_fasilitas ?? '-' }}</td>
                            <td class="py-2 pr-4">{{ $r->kategori_laporan }}</td>
                            <td class="py-2 pr-4">{{ ucfirst($r->status_laporan) }}</td>
                            <td class="py-2 pr-4 flex gap-2">
                                @if($r->status_laporan == 'baru')
                                    <form action="{{ route('petugas.laporan.process', $r->id) }}" method="POST">
                                        @csrf
                                        <button class="px-3 py-1.5 bg-blue-800 hover:bg-blue-900 text-white rounded-lg font-bold">Proses</button>
                                    </form>
                                @elseif($r->status_laporan == 'diproses')
                                    <form action="{{ route('petugas.laporan.resolve', $r->id) }}" method="POST" class="flex gap-2">
                                        @csrf
                                        <input type="text" name="catatan_resolusi" placeholder="Catatan perbaikan" class="p-1.5 bg-slate-50 border border-slate-300 rounded-lg text-[10px]" required>
                                        <button class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg font-bold whitespace-nowrap">Selesaikan</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="py-4 text-center text-slate-400">Belum ada laporan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Status Fasilitas -->
    <div class="bg-white rounded-2xl p-6 shadow border border-slate-100">
        <h2 class="font-bold text-slate-800 mb-4">Ubah Status Fasilitas</h2>
        <div class="overflow-x-auto">
            <table class="w-full text-xs">
                <thead>
                    <tr class="text-left text-slate-500 border-b">
                        <th class="py-2 pr-4">Fasilitas</th>
                        <th class="py-2 pr-4">Status Saat Ini</th>
                        <th class="py-2 pr-4">Ubah Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($facilities as $f)
                        <tr class="border-b border-slate-100">
                            <td class="py-2 pr-4 font-bold">{{ $f->nama_fasilitas }}</td>
                            <td class="py-2 pr-4">{{ ucfirst(str_replace('_', ' ', $f->status)) }}</td>
                            <td class="py-2 pr-4">
                                <form action="{{ route('petugas.fasilitas.status', $f->id) }}" method="POST" class="flex gap-2">
                                    @csrf
                                    <select name="status" class="p-1.5 bg-slate-50 border border-slate-300 rounded-lg text-[10px]">
                                        <option value="aktif" @selected($f->status=='aktif')>Aktif</option>
                                        <option value="dalam_perbaikan" @selected($f->status=='dalam_perbaikan')>Dalam Perbaikan</option>
                                        <option value="nonaktif" @selected($f->status=='nonaktif')>Nonaktif</option>
                                    </select>
                                    <button class="px-3 py-1.5 bg-blue-800 hover:bg-blue-900 text-white rounded-lg font-bold">Simpan</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
