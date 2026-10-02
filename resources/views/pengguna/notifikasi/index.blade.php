@extends('layouts.app')

@section('title', 'Notifikasi Saya')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-4">

    {{-- HEADER + TAB --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h1 class="text-2xl font-black text-slate-900">Notifikasi Saya</h1>
            <p class="text-xs text-slate-500 mt-0.5">Pemberitahuan seputar reservasi & laporan.</p>
        </div>

        @php
            $tabs = [
                ['label' => 'Semua', 'kat' => null, 'count' => $totalCount ?? 0],
                ['label' => 'Reservasi', 'kat' => 'reservasi', 'count' => $reservasiCount ?? 0],
                ['label' => 'Laporan', 'kat' => 'laporan', 'count' => $laporanCount ?? 0],
            ];
        @endphp

        <div class="flex items-center gap-1.5 text-xs font-bold flex-wrap">
            @foreach($tabs as $tab)
                @php $isActive = request('kat') == $tab['kat']; @endphp
                <a href="{{ route('pengguna.notifikasi.index', $tab['kat'] ? ['kat' => $tab['kat']] : []) }}"
                   class="px-3 py-1.5 rounded-xl transition inline-flex items-center gap-1.5
                          {{ $isActive ? 'bg-blue-900 text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                    {{ $tab['label'] }}
                    <span class="px-1.5 py-0.5 rounded text-[10px] font-bold
                                 {{ $isActive ? 'bg-white/20 text-white' : 'bg-slate-200 text-slate-700' }}">
                        {{ $tab['count'] }}
                    </span>
                </a>
            @endforeach
        </div>
    </div>

    {{-- LIST --}}
    <div class="space-y-2">
        @forelse($notifications as $n)
            @php
                $judulLower = strtolower($n->judul);
                if (str_contains($judulLower, 'disetujui') || str_contains($judulLower, 'selesai')) {
                    $iconBg = 'bg-emerald-50 text-emerald-600';
                    $iconSvg = 'M5 13l4 4L19 7';
                } elseif (str_contains($judulLower, 'ditolak') || str_contains($judulLower, 'batal') || str_contains($judulLower, 'kedaluwarsa')) {
                    $iconBg = 'bg-rose-50 text-rose-600';
                    $iconSvg = 'M6 18L18 6M6 6l12 12';
                } else {
                    $iconBg = 'bg-blue-50 text-blue-600';
                    $iconSvg = 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z';
                }

                $isApprovedReservation = (
                    (str_contains($judulLower, 'reservasi') || str_contains(strtolower($n->pesan), 'reservasi')) &&
                    (str_contains($judulLower, 'disetujui') || str_contains(strtolower($n->pesan), 'disetujui'))
                );
            @endphp

            <div class="p-4 rounded-2xl border transition flex items-start gap-3 bg-white
                        {{ !$n->is_read ? 'border-l-4 border-l-blue-900 border-slate-200' : 'border-slate-200 hover:border-slate-300' }}">
                <div class="shrink-0 w-10 h-10 rounded-xl {{ $iconBg }} flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $iconSvg }}"/>
                    </svg>
                </div>

                <div class="flex-1 min-w-0">
                    <div class="flex items-start justify-between gap-2 mb-1">
                        <h2 class="text-sm font-black text-slate-900">{{ $n->judul }}</h2>
                        <span class="text-[10px] text-slate-400 font-medium shrink-0">
                            {{ $n->created_at->diffForHumans() }}
                        </span>
                    </div>
                    <p class="text-xs text-slate-600 leading-relaxed">{{ $n->pesan }}</p>

                    @if($isApprovedReservation && $n->reservation_id)
                        <a href="{{ route('pengguna.reservasi.cetak', $n->reservation_id) }}" target="_blank"
                           class="inline-flex items-center gap-1.5 mt-3 px-3 py-1.5 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-200 text-xs font-bold rounded-xl transition">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                            </svg>
                            Cetak Surat
                        </a>
                    @endif
                </div>
            </div>
        @empty
            <div class="text-center py-16 bg-white rounded-2xl border border-slate-200">
                <div class="w-16 h-16 mx-auto rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mb-3">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.4-1.4A2 2 0 0118 14.2V11a6 6 0 10-12 0v3.2a2 2 0 01-.6 1.4L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                </div>
                <p class="text-sm font-bold text-slate-700">Tidak ada notifikasi</p>
                <p class="text-xs text-slate-400 mt-1">Notifikasi akan muncul saat ada aktivitas</p>
            </div>
        @endforelse
    </div>
</div>
@endsection