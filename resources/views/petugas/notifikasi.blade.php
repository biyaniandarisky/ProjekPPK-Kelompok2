@extends('layouts.app')

@section('title', 'Notifikasi Petugas')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-4"
     x-data="{ filterTipe: 'semua' }">

    {{-- HEADER --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div class="flex items-center gap-3">
            <a href="{{ route('petugas.dashboard') }}"
               class="inline-flex items-center justify-center w-10 h-10 bg-white border border-slate-200 hover:border-blue-900 text-slate-600 hover:text-blue-900 rounded-xl transition shadow-sm"
               title="Kembali ke Dashboard">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <div>
                <h1 class="text-2xl font-black text-slate-900">Notifikasi</h1>
                <p class="text-xs text-slate-500">Permintaan baru yang perlu direspons.</p>
            </div>
        </div>

        {{-- Tab Filter --}}
        <div class="flex items-center gap-1.5 text-xs font-bold">
            @foreach(['semua' => 'Semua', 'reservasi' => 'Reservasi', 'laporan' => 'Laporan'] as $key => $label)
                <button @click="filterTipe = '{{ $key }}'"
                        :class="filterTipe === '{{ $key }}' ? 'bg-blue-900 text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                        class="px-3 py-1.5 rounded-xl transition">
                    {{ $label }}
                </button>
            @endforeach
        </div>
    </div>

    {{-- LIST --}}
    <div class="space-y-2">
        @forelse($notifikasi as $n)
            @php $item = $n['item']; @endphp

            @if($n['tipe'] === 'reservasi')
                <a href="{{ route('petugas.reservasi.index') }}"
                   x-show="filterTipe === 'semua' || filterTipe === 'reservasi'" x-cloak
                   class="flex items-start gap-3 p-4 rounded-2xl border border-slate-200 bg-white hover:border-slate-300 hover:shadow-md transition">
                    <div class="shrink-0 w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-start justify-between gap-2">
                            <h2 class="text-sm font-black text-slate-900">Reservasi baru: {{ $item->facility->nama_fasilitas ?? '-' }}</h2>
                            <span class="shrink-0 text-[10px] text-slate-400 font-medium">{{ $n['waktu']->diffForHumans() }}</span>
                        </div>
                        <p class="text-xs text-slate-500 mt-0.5">
                            {{ $item->user->name ?? '-' }} · {{ $item->tanggal->translatedFormat('d M Y') }} ·
                            {{ substr($item->start_time, 0, 5) }}–{{ substr($item->end_time, 0, 5) }}
                        </p>
                        <p class="text-xs text-slate-600 mt-1">{{ \Illuminate\Support\Str::limit($item->tujuan, 100) }}</p>
                        <span class="inline-block mt-2 px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200 uppercase tracking-wider">
                            Menunggu
                        </span>
                    </div>
                </a>
            @else
                <a href="{{ route('petugas.laporan.index') }}"
                   x-show="filterTipe === 'semua' || filterTipe === 'laporan'" x-cloak
                   class="flex items-start gap-3 p-4 rounded-2xl border border-slate-200 bg-white hover:border-slate-300 hover:shadow-md transition">
                    <div class="shrink-0 w-10 h-10 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-start justify-between gap-2">
                            <h2 class="text-sm font-black text-slate-900">Laporan baru: {{ $item->kategori_laporan }}</h2>
                            <span class="shrink-0 text-[10px] text-slate-400 font-medium">{{ $n['waktu']->diffForHumans() }}</span>
                        </div>
                        <p class="text-xs text-slate-500 mt-0.5">
                            {{ $item->facility->nama_fasilitas ?? '-' }} · {{ $item->user->name ?? '-' }}
                        </p>
                        <p class="text-xs text-slate-600 mt-1">{{ \Illuminate\Support\Str::limit($item->deskripsi, 100) }}</p>
                        <span class="inline-block mt-2 px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200 uppercase tracking-wider">
                            Baru
                        </span>
                    </div>
                </a>
            @endif
        @empty
            <div class="text-center py-16 bg-white rounded-2xl border border-slate-200">
                <div class="w-16 h-16 mx-auto rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mb-3">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.4-1.4A2 2 0 0118 14.2V11a6 6 0 10-12 0v3.2a2 2 0 01-.6 1.4L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                </div>
                <p class="text-sm font-bold text-slate-700">Tidak ada notifikasi</p>
                <p class="text-xs text-slate-400 mt-1">Notifikasi akan muncul saat ada permintaan baru</p>
            </div>
        @endforelse

        {{-- Empty per filter --}}
        @if($notifikasi->count() > 0)
            <div x-show="filterTipe === 'reservasi' && !{{ $notifikasi->where('tipe', 'reservasi')->count() }}" x-cloak
                 class="text-center py-12 bg-white rounded-2xl border border-slate-200">
                <p class="text-sm font-bold text-slate-700">Tidak ada notifikasi reservasi</p>
                <p class="text-xs text-slate-400 mt-1">Belum ada reservasi baru yang perlu ditinjau</p>
            </div>
            <div x-show="filterTipe === 'laporan' && !{{ $notifikasi->where('tipe', 'laporan')->count() }}" x-cloak
                 class="text-center py-12 bg-white rounded-2xl border border-slate-200">
                <p class="text-sm font-bold text-slate-700">Tidak ada notifikasi laporan</p>
                <p class="text-xs text-slate-400 mt-1">Belum ada laporan baru yang perlu ditindak</p>
            </div>
        @endif
    </div>
</div>
@endsection