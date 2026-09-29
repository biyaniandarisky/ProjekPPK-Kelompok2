@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
    <div class="flex items-center gap-2">
        <a href="{{ route('petugas.dashboard') }}" class="text-xs font-bold text-blue-900 hover:underline flex items-center gap-1">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Kembali ke Panel Petugas
        </a>
    </div>

    <div>
        <h1 class="text-2xl font-black text-slate-900">Notifikasi</h1>
        <p class="text-sm text-slate-500">Permintaan baru yang perlu direspons: reservasi yang menunggu dan laporan kendala yang baru masuk.</p>
    </div>

    <div class="bg-white rounded-2xl p-6 shadow border border-slate-100"
        x-data="{ filterTipe: 'semua' }">

        <!-- Filter Jenis -->
        <div class="flex flex-wrap gap-2 mb-4">
            @foreach([
                'semua' => 'Semua',
                'reservasi' => 'Reservasi',
                'laporan' => 'Laporan',
            ] as $key => $label)
                <button @click="filterTipe = '{{ $key }}'"
                    :class="filterTipe === '{{ $key }}' ? 'bg-blue-700 text-white shadow' : 'bg-white text-slate-600 hover:bg-slate-100'"
                    class="px-4 py-2 text-xs font-bold rounded-full border border-slate-200 transition">
                    {{ $label }}
                </button>
            @endforeach
        </div>

        @if($notifikasi->isEmpty())
            <div class="rounded-2xl border border-dashed border-slate-300 p-8 text-center text-xs text-slate-500">
                Tidak ada notifikasi baru.
            </div>
        @else
            <div class="divide-y divide-slate-100">
                @foreach($notifikasi as $n)
                    @php $item = $n['item']; @endphp

                    @if($n['tipe'] === 'reservasi')
                        <a href="{{ route('petugas.reservasi.index') }}"
                           x-show="filterTipe === 'semua' || filterTipe === 'reservasi'" x-cloak
                           class="flex items-start justify-between gap-3 py-3 px-2 -mx-2 rounded-xl hover:bg-slate-50 transition">
                            <div class="flex items-start gap-3">
                                <div class="w-8 h-8 rounded-full bg-amber-100 text-amber-700 flex items-center justify-center shrink-0">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-xs font-bold text-slate-800">Reservasi baru: {{ $item->facility->nama_fasilitas ?? '-' }}</p>
                                    <p class="text-[11px] text-slate-500">
                                        {{ $item->user->name ?? '-' }} &bull;
                                        {{ $item->tanggal->format('d M Y') }},
                                        {{ \Carbon\Carbon::parse($item->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($item->end_time)->format('H:i') }}
                                    </p>
                                    <p class="text-[11px] text-slate-600 mt-0.5">{{ $item->tujuan }}</p>
                                </div>
                            </div>
                            <div class="text-right shrink-0">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-700">Menunggu</span>
                                <p class="text-[10px] text-slate-400 mt-1">{{ $n['waktu']->diffForHumans() }}</p>
                            </div>
                        </a>
                    @else
                        <a href="{{ route('petugas.laporan.index') }}"
                           x-show="filterTipe === 'semua' || filterTipe === 'laporan'" x-cloak
                           class="flex items-start justify-between gap-3 py-3 px-2 -mx-2 rounded-xl hover:bg-slate-50 transition">
                            <div class="flex items-start gap-3">
                                <div class="w-8 h-8 rounded-full bg-rose-100 text-rose-700 flex items-center justify-center shrink-0">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-xs font-bold text-slate-800">Laporan baru: {{ $item->kategori_laporan }}</p>
                                    <p class="text-[11px] text-slate-500">{{ $item->facility->nama_fasilitas ?? '-' }} &bull; {{ $item->user->name ?? '-' }}</p>
                                    <p class="text-[11px] text-slate-600 mt-0.5">{{ \Illuminate\Support\Str::limit($item->deskripsi, 100) }}</p>
                                </div>
                            </div>
                            <div class="text-right shrink-0">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-blue-100 text-blue-700">Baru</span>
                                <p class="text-[10px] text-slate-400 mt-1">{{ $n['waktu']->diffForHumans() }}</p>
                            </div>
                        </a>
                    @endif
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection
