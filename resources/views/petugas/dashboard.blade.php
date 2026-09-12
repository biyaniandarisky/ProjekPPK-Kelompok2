@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">
    <div>
        <h1 class="text-2xl font-black text-slate-900">Panel Petugas Sarana</h1>
        <p class="text-sm text-slate-500">Verifikasi reservasi, kelola laporan kendala, dan status fasilitas.</p>
    </div>

   {{-- ================= RESERVASI PENDING ================= --}}
<section id="reservasi-pending" class="scroll-mt-20 space-y-4">
    <h2 class="text-lg font-black text-slate-900">Reservasi Menunggu Persetujuan</h2>

    @if($pendingReservations->isEmpty())
        <div class="bg-white rounded-2xl border border-dashed border-slate-300 p-8 text-center text-xs text-slate-500">
            Tidak ada reservasi yang menunggu persetujuan saat ini.
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @foreach($pendingReservations as $r)
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4 space-y-3">
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <div class="font-bold text-slate-800 text-sm">{{ $r->facility->nama_fasilitas ?? '-' }}</div>
                            <div class="text-xs text-slate-500">{{ $r->user->name ?? '-' }}</div>
                        </div>
                        <span class="px-2 py-0.5 bg-amber-100 text-amber-700 rounded text-[10px] font-bold whitespace-nowrap">Menunggu</span>
                    </div>

                    <div class="text-xs text-slate-500">
                        {{ $r->tanggal->format('d M Y') }} • {{ \Carbon\Carbon::parse($r->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($r->end_time)->format('H:i') }}
                    </div>
                    <p class="text-xs text-slate-600">{{ $r->tujuan }}</p>

                    <div class="flex gap-2 pt-2 border-t border-slate-100">
                        <form action="{{ route('petugas.reservasi.approve', $r->id) }}" method="POST" class="flex-1">
                            @csrf
                            <button type="submit" class="w-full py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl transition">
                                Setujui
                            </button>
                        </form>
                        <form action="{{ route('petugas.reservasi.reject', $r->id) }}" method="POST" class="flex-1">
                            @csrf
                            <button type="submit" class="w-full py-2 bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold rounded-xl transition">
                                Tolak
                            </button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</section>

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
