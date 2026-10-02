@extends('layouts.app')

@section('title', 'Dashboard Petugas')

@section('content')
{{-- HERO --}}
<div class="bg-gradient-to-br from-blue-900 via-blue-800 to-blue-900 text-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <p class="text-[11px] font-bold text-blue-200 uppercase tracking-wider">Panel Petugas</p>
        <h1 class="text-2xl md:text-3xl font-black mt-1">
            Halo, {{ explode(' ', auth()->user()->name)[0] }}
        </h1>
        <p class="text-sm text-blue-100 mt-1">
            Verifikasi reservasi, kelola laporan, dan atur status fasilitas.
        </p>
    </div>
</div>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6">

    {{-- STAT CARDS --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        @php
            $statCards = [
                [
                    'label' => 'Baru Masuk',
                    'value' => $stats['baru_masuk'] ?? 0,
                    'subtitle' => 'Reservasi + Laporan',
                    'gradient' => 'from-amber-400 to-amber-500',
                    'text_color' => 'text-amber-50',
                    'icon' => 'M15 17h5l-1.4-1.4A2 2 0 0118 14.2V11a6 6 0 10-12 0v3.2a2 2 0 01-.6 1.4L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9',
                    'link' => route('petugas.reservasi.index'),
                ],
                [
                    'label' => 'Masih Berjalan',
                    'value' => $stats['masih_berjalan'] ?? 0,
                    'subtitle' => 'Reservasi aktif',
                    'gradient' => 'from-blue-600 to-blue-800',
                    'text_color' => 'text-blue-100',
                    'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z',
                    'link' => route('petugas.reservasi.index'),
                ],
                [
                    'label' => 'Disetujui',
                    'value' => $stats['disetujui'] ?? 0,
                    'subtitle' => 'Total disetujui',
                    'gradient' => 'from-emerald-500 to-emerald-700',
                    'text_color' => 'text-emerald-50',
                    'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
                    'link' => route('petugas.reservasi.index'),
                ],
                [
                    'label' => 'Laporan Baru',
                    'value' => $stats['laporan_baru'] ?? 0,
                    'subtitle' => 'Perlu ditindak',
                    'gradient' => 'from-rose-500 to-rose-700',
                    'text_color' => 'text-rose-50',
                    'icon' => 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z',
                    'link' => route('petugas.laporan.index'),
                ],
            ];
        @endphp

        @foreach($statCards as $card)
            <a href="{{ $card['link'] }}"
               class="group flex items-center gap-3 rounded-2xl p-4 shadow-md bg-gradient-to-br {{ $card['gradient'] }} text-white hover:shadow-lg hover:-translate-y-0.5 transition-all duration-300">
                <div class="w-11 h-11 rounded-xl bg-white/25 flex items-center justify-center shrink-0 backdrop-blur">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $card['icon'] }}"/>
                    </svg>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-[11px] font-bold {{ $card['text_color'] }} truncate">{{ $card['label'] }}</p>
                    <p class="text-2xl font-black leading-none mt-0.5">{{ $card['value'] }}</p>
                    <p class="text-[10px] font-medium {{ $card['text_color'] }} mt-0.5">{{ $card['subtitle'] }}</p>
                </div>
                <svg class="w-4 h-4 {{ $card['text_color'] }} group-hover:translate-x-1 transition shrink-0"
                     fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                </svg>
            </a>
        @endforeach
    </div>

    {{-- PERLU TINDAKAN — 2 KOLOM --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">

        {{-- PERLU TINDAKAN: RESERVASI --}}
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h3 class="font-black text-slate-900 text-sm">Perlu Tindakan — Reservasi</h3>
                    <p class="text-[10px] text-slate-400 mt-0.5">Menunggu persetujuan</p>
                </div>
                <a href="{{ route('petugas.reservasi.index') }}"
                   class="group inline-flex items-center gap-1.5 px-2.5 py-1.5 text-[11px] font-bold text-blue-900 hover:bg-blue-50 rounded-lg transition">
                    <span>Lihat Semua</span>
                    <svg class="w-3.5 h-3.5 group-hover:translate-x-0.5 transition" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                    </svg>
                </a>
            </div>

            <div class="divide-y divide-slate-100">
                @forelse($reservasiMenunggu ?? [] as $r)
                    <a href="{{ route('petugas.reservasi.index') }}"
                       class="block px-5 py-3.5 hover:bg-slate-50 transition">
                        <div class="flex items-center gap-2 flex-wrap">
                            <p class="font-bold text-slate-900 text-sm truncate">{{ $r->facility->nama_fasilitas }}</p>
                            <span class="text-[9px] text-slate-400 font-mono">#{{ $r->id }}</span>
                        </div>
                        <p class="text-xs text-slate-500 mt-0.5">
                            {{ $r->user->name }} · {{ $r->tanggal->translatedFormat('d M Y') }} · {{ substr($r->start_time, 0, 5) }}–{{ substr($r->end_time, 0, 5) }}
                        </p>
                    </a>
                @empty
                    <div class="px-5 py-12 text-center">
                        <p class="text-sm font-bold text-slate-700">Tidak ada reservasi menunggu</p>
                        <p class="text-xs text-slate-400 mt-1">Semua reservasi sudah diproses</p>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- PERLU TINDAKAN: LAPORAN --}}
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h3 class="font-black text-slate-900 text-sm">Perlu Tindakan — Laporan</h3>
                    <p class="text-[10px] text-slate-400 mt-0.5">Laporan baru</p>
                </div>
                <a href="{{ route('petugas.laporan.index') }}"
                   class="group inline-flex items-center gap-1.5 px-2.5 py-1.5 text-[11px] font-bold text-blue-900 hover:bg-blue-50 rounded-lg transition">
                    <span>Lihat Semua</span>
                    <svg class="w-3.5 h-3.5 group-hover:translate-x-0.5 transition" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                    </svg>
                </a>
            </div>

            <div class="divide-y divide-slate-100">
                @forelse($laporanBaru ?? [] as $l)
                    <a href="{{ route('petugas.laporan.index') }}"
                       class="block px-5 py-3.5 hover:bg-slate-50 transition">
                        <div class="flex items-center gap-2 flex-wrap">
                            <p class="font-bold text-slate-900 text-sm truncate">{{ $l->facility->nama_fasilitas }} — {{ $l->kategori_laporan }}</p>
                            <span class="text-[9px] text-slate-400 font-mono">#{{ $l->id }}</span>
                        </div>
                        <p class="text-xs text-slate-500 mt-0.5">
                            {{ $l->user->name }} · {{ $l->created_at->diffForHumans() }}
                        </p>
                    </a>
                @empty
                    <div class="px-5 py-12 text-center">
                        <p class="text-sm font-bold text-slate-700">Tidak ada laporan baru</p>
                        <p class="text-xs text-slate-400 mt-1">Semua laporan sudah ditangani</p>
                    </div>
                @endforelse
            </div>
        </div>

    </div>

    {{-- SEDANG DIGUNAKAN — FULL WIDTH --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h3 class="font-black text-slate-900 text-sm">Sedang Digunakan</h3>
                <p class="text-[10px] text-slate-400 mt-0.5">Ruangan yang sedang ditempati saat ini</p>
            </div>
            <a href="{{ route('petugas.reservasi.index') }}"
               class="group inline-flex items-center gap-1.5 px-2.5 py-1.5 text-[11px] font-bold text-blue-900 hover:bg-blue-50 rounded-lg transition">
                <span>Lihat Semua</span>
                <svg class="w-3.5 h-3.5 group-hover:translate-x-0.5 transition" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                </svg>
            </a>
        </div>

        <div class="divide-y divide-slate-100">
            @forelse($sedangDigunakan ?? [] as $s)
                <a href="{{ route('petugas.reservasi.index') }}"
                   class="flex items-center gap-3 px-5 py-3.5 hover:bg-slate-50 transition">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 flex-wrap">
                            <p class="font-bold text-slate-900 text-sm truncate">{{ $s->facility->nama_fasilitas }}</p>
                            <span class="text-[9px] text-slate-400 font-mono">#{{ $s->id }}</span>
                        </div>
                        <p class="text-xs text-slate-500 mt-0.5">
                            {{ $s->user->name }} · {{ substr($s->start_time, 0, 5) }}–{{ substr($s->end_time, 0, 5) }} WIB
                        </p>
                    </div>
                    <span class="shrink-0 px-2.5 py-1 bg-emerald-50 text-emerald-700 border border-emerald-200 text-[10px] font-bold rounded-full uppercase tracking-wider">
                        Dipakai
                    </span>
                </a>
            @empty
                <div class="px-5 py-12 text-center">
                    <p class="text-sm font-bold text-slate-700">Tidak ada ruangan sedang digunakan</p>
                    <p class="text-xs text-slate-400 mt-1">Semua ruangan kosong saat ini</p>
                </div>
            @endforelse
        </div>
    </div>

    {{-- MENU UTAMA --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
        @php
            $menus = [
                [
                    'title' => 'Reservasi',
                    'badge' => 'Verifikasi Fasilitas',
                    'desc' => 'Kelola antrian reservasi: setujui, tolak, atau batalkan secara darurat.',
                    'link' => route('petugas.reservasi.index'),
                    'overlay' => 'from-blue-950/90 to-blue-900/80',
                    'badge_color' => 'bg-blue-500/30 text-blue-200 border-blue-400/30',
                    'image' => asset('images/reservasi.jpg'),
                    'cta' => 'Buka Daftar Reservasi',
                ],
                [
                    'title' => 'Laporan',
                    'badge' => 'Layanan Teknis',
                    'desc' => 'Proses laporan kendala yang masuk dari pengguna sampai selesai.',
                    'link' => route('petugas.laporan.index'),
                    'overlay' => 'from-rose-950/90 to-rose-900/80',
                    'badge_color' => 'bg-rose-500/30 text-rose-200 border-rose-400/30',
                    'image' => asset('images/laporan.jpg'),
                    'cta' => 'Buka Laporan Kendala',
                ],
                [
                    'title' => 'Fasilitas',
                    'badge' => 'Status Sarana',
                    'desc' => 'Ubah status fasilitas: Dalam Perbaikan, Selesai, atau Aktifkan kembali.',
                    'link' => route('petugas.fasilitas.index'),
                    'overlay' => 'from-emerald-950/90 to-emerald-900/80',
                    'badge_color' => 'bg-emerald-500/30 text-emerald-100 border-emerald-400/30',
                    'image' => asset('images/fasilitas.jpg'),
                    'cta' => 'Buka Daftar Fasilitas',
                ],
            ];
        @endphp

        @foreach($menus as $menu)
            <a href="{{ $menu['link'] }}"
               class="group relative bg-white rounded-2xl p-6 shadow border border-slate-200 overflow-hidden hover:shadow-lg transition duration-300 flex flex-col justify-between min-h-[220px]">
                <div class="absolute inset-0 bg-gradient-to-r {{ $menu['overlay'] }} z-10"></div>
                <img src="{{ $menu['image'] }}" alt="{{ $menu['title'] }}"
                     class="absolute inset-0 w-full h-full object-cover group-hover:scale-105 transition duration-500"
                     onerror="this.style.display='none'">

                <div class="relative z-20 text-white space-y-2">
                    <span class="inline-block {{ $menu['badge_color'] }} text-[10px] font-bold px-2.5 py-1 rounded-full uppercase tracking-wider border">
                        {{ $menu['badge'] }}
                    </span>
                    <h2 class="text-xl font-black leading-snug">{{ $menu['title'] }}</h2>
                    <p class="text-xs text-slate-200 max-w-sm">{{ $menu['desc'] }}</p>
                </div>

                <div class="relative z-20 pt-4 flex items-center text-xs font-bold text-white/80 group-hover:text-white transition">
                    <span>{{ $menu['cta'] }}</span>
                    <svg class="w-4 h-4 ml-1 group-hover:translate-x-1 transition" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                    </svg>
                </div>
            </a>
        @endforeach
    </div>
</div>
@endsection