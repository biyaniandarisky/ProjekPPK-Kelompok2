@extends('layouts.app')

@section('content')
<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8"
    x-data="{
        filter: '{{ $activeStatus }}',
        detail: null,
        showDetail: false,
        buka(data) { this.detail = data; this.showDetail = true; }
    }">

    <!-- Kembali -->
    <a href="{{ route('pengguna.dashboard') }}" class="inline-flex items-center gap-1 text-xs font-bold text-slate-500 hover:text-slate-700 mb-4 transition">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
        </svg>
        Kembali ke Panel
    </a>

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-black text-slate-900">Reservasi Saya</h1>
            <p class="text-sm text-slate-500">Pantau status pengajuan peminjaman fasilitas kamu.</p>
        </div>
        <a href="{{ route('pengguna.reservasi.create') }}"
            class="inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-blue-900 hover:bg-blue-800 text-white text-xs font-bold rounded-xl shadow transition shrink-0">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            Ajukan Reservasi Baru
        </a>
    </div>

    <!-- Tab: Reservasi Saya / Laporan Saya -->
    <div class="grid grid-cols-2 gap-2 mb-5">
        <div class="flex items-center justify-center gap-2 py-3 bg-white border border-slate-200 border-b-2 border-b-blue-900 rounded-t-2xl text-xs font-black text-slate-900">
            Reservasi Saya
            <span class="px-2 py-0.5 rounded-full bg-blue-100 text-blue-900 text-[10px]">{{ $counts['all'] }}</span>
        </div>
        <a href="{{ route('pengguna.laporan.index') }}"
            class="flex items-center justify-center gap-2 py-3 bg-slate-100 hover:bg-slate-200 rounded-t-2xl text-xs font-black text-slate-500 transition">
            Laporan Saya
            <span class="px-2 py-0.5 rounded-full bg-slate-200 text-slate-600 text-[10px]">{{ $totalLaporan }}</span>
        </a>
    </div>

    <!-- Filter Status -->
    @php
        $filters = [
            'all'       => 'Semua Status',
            'pending'   => 'Menunggu',
            'approved'  => 'Disetujui',
            'rejected'  => 'Ditolak',
            'cancelled' => 'Dibatalkan',
        ];
    @endphp
    <div class="grid grid-cols-2 sm:grid-cols-5 gap-2 mb-6">
        @foreach($filters as $key => $label)
            <button type="button" @click="filter = '{{ $key }}'"
                :class="filter === '{{ $key }}' ? 'bg-blue-900 text-white border-blue-900 shadow' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50'"
                class="py-2.5 rounded-xl border text-xs font-bold transition">
                {{ $label }} ({{ $counts[$key] }})
            </button>
        @endforeach
    </div>

    <!-- Daftar Reservasi -->
    <div class="space-y-3">
        @forelse($myReservations as $r)
            @php
                $badge = match($r->status) {
                    'approved'  => ['Disetujui', 'bg-emerald-100 text-emerald-700'],
                    'rejected'  => ['Ditolak', 'bg-rose-100 text-rose-700'],
                    'cancelled' => ['Dibatalkan', 'bg-slate-200 text-slate-600'],
                    default     => ['Menunggu Persetujuan', 'bg-amber-100 text-amber-700'],
                };
                $detailJson = [
                    'fasilitas' => $r->facility->nama_fasilitas ?? '-',
                    'lokasi'    => $r->facility->lokasi ?? '-',
                    'kapasitas' => $r->facility->kapasitas ?? '-',
                    'tanggal'   => \Carbon\Carbon::parse($r->tanggal)->translatedFormat('d F Y'),
                    'waktu'     => \Carbon\Carbon::parse($r->start_time)->format('H:i') . ' - ' . \Carbon\Carbon::parse($r->end_time)->format('H:i') . ' WIB',
                    'tujuan'    => $r->tujuan,
                    'status'    => $badge[0],
                    'alasan'    => $r->alasan_batal,
                    'diajukan'  => $r->created_at ? $r->created_at->format('d M Y H:i') : '-',
                ];
            @endphp

            <div x-show="filter === 'all' || filter === '{{ $r->status }}'"
                class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <div class="space-y-2 text-xs">
                        <div class="flex flex-wrap items-center gap-2">
                            <h3 class="text-base font-black text-slate-900">{{ $r->facility->nama_fasilitas ?? '-' }}</h3>
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold {{ $badge[1] }}">{{ $badge[0] }}</span>
                        </div>
                        <div class="flex flex-wrap gap-x-6 gap-y-1 text-slate-600">
                            <span>{{ \Carbon\Carbon::parse($r->tanggal)->format('Y-m-d') }}</span>
                            <span>{{ \Carbon\Carbon::parse($r->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($r->end_time)->format('H:i') }} WIB</span>
                        </div>
                        <div class="flex flex-wrap gap-x-6 gap-y-1 text-slate-500">
                            <span>{{ $r->facility->lokasi ?? '-' }}</span>
                            <span class="italic">Tujuan: {{ \Illuminate\Support\Str::limit($r->tujuan, 45) }}</span>
                        </div>
                    </div>

                    <div class="flex gap-2 shrink-0">
                        <button type="button" @click='buka(@json($detailJson, JSON_HEX_APOS|JSON_HEX_QUOT))'
                            class="px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition">
                            Detail
                        </button>
                        @if(in_array($r->status, ['pending', 'approved']))
                            <form action="{{ route('pengguna.reservasi.cancel', $r->id) }}" method="POST"
                                onsubmit="return confirm('Batalkan reservasi ini?')">
                                @csrf
                                <button type="submit" class="px-3.5 py-2 bg-rose-50 hover:bg-rose-100 text-rose-600 border border-rose-200 text-xs font-bold rounded-xl transition">
                                    Batalkan
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="bg-white rounded-2xl border border-slate-200 py-12 text-center text-sm text-slate-400">
                Belum ada riwayat reservasi.
            </div>
        @endforelse

        <!-- Kosong per filter -->
        @if($myReservations->count() > 0)
            @foreach($filters as $key => $label)
                @if($key !== 'all' && $counts[$key] === 0)
                    <div x-show="filter === '{{ $key }}'" x-cloak
                        class="bg-white rounded-2xl border border-slate-200 py-12 text-center text-sm text-slate-400">
                        Tidak ada reservasi berstatus {{ $label }}.
                    </div>
                @endif
            @endforeach
        @endif
    </div>

    <!-- Modal Detail -->
    <div x-show="showDetail" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div @click.outside="showDetail = false" class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-100 space-y-4">
            <div class="flex justify-between items-center border-b border-slate-100 pb-3">
                <h3 class="font-black text-slate-900 text-base">Detail Reservasi</h3>
                <button type="button" @click="showDetail = false" class="text-slate-400 hover:text-slate-600">&times;</button>
            </div>

            <template x-if="detail">
                <div class="space-y-2.5 text-xs">
                    <div class="flex justify-between gap-4"><span class="font-bold text-slate-500">Fasilitas</span><span class="font-bold text-slate-900 text-right" x-text="detail.fasilitas"></span></div>
                    <div class="flex justify-between gap-4"><span class="font-bold text-slate-500">Lokasi</span><span class="text-slate-700 text-right" x-text="detail.lokasi"></span></div>
                    <div class="flex justify-between gap-4"><span class="font-bold text-slate-500">Kapasitas</span><span class="text-slate-700 text-right" x-text="detail.kapasitas + ' orang'"></span></div>
                    <div class="flex justify-between gap-4"><span class="font-bold text-slate-500">Tanggal</span><span class="text-slate-700 text-right" x-text="detail.tanggal"></span></div>
                    <div class="flex justify-between gap-4"><span class="font-bold text-slate-500">Waktu</span><span class="text-slate-700 text-right" x-text="detail.waktu"></span></div>
                    <div class="flex justify-between gap-4"><span class="font-bold text-slate-500">Status</span><span class="font-bold text-slate-900 text-right" x-text="detail.status"></span></div>
                    <div class="flex justify-between gap-4"><span class="font-bold text-slate-500">Diajukan</span><span class="text-slate-700 text-right" x-text="detail.diajukan"></span></div>
                    <div class="pt-2 border-t border-slate-100">
                        <p class="font-bold text-slate-500 mb-1">Tujuan Kegiatan</p>
                        <p class="text-slate-700" x-text="detail.tujuan"></p>
                    </div>
                    <div x-show="detail.alasan" class="p-3 bg-rose-50 border border-rose-100 rounded-xl">
                        <p class="font-bold text-rose-700 mb-1">Catatan Petugas</p>
                        <p class="text-rose-800" x-text="detail.alasan"></p>
                    </div>
                </div>
            </template>

            <div class="flex justify-end pt-2 border-t border-slate-100">
                <button type="button" @click="showDetail = false" class="bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold px-4 py-2 rounded-xl text-xs transition">Tutup</button>
            </div>
        </div>
    </div>
</div>
@endsection
