@extends('layouts.app')

@section('title', 'Dashboard Pengguna')

@section('content')
{{-- WELCOME BANNER --}}
<div class="bg-gradient-to-br from-blue-900 via-blue-800 to-blue-900 text-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <p class="text-[11px] font-bold text-blue-200 uppercase tracking-wider">Panel Pengguna</p>
        <h1 class="text-2xl md:text-3xl font-black mt-1">
            Halo, {{ explode(' ', auth()->user()->name)[0] }}
        </h1>
        <p class="text-sm text-blue-100 mt-1">
            Kelola reservasi dan laporan fasilitas kampus Anda di sini.
        </p>

        <div class="flex flex-wrap gap-3 mt-6">
            <a href="{{ route('pengguna.reservasi.create') }}"
               class="group inline-flex items-center gap-2 px-5 py-2.5 bg-white/10 hover:bg-white/20 border border-white/20 text-white text-sm font-bold rounded-xl transition backdrop-blur">
                <div class="w-8 h-8 rounded-lg bg-white/20 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                    </svg>
                </div>
                Ajukan Reservasi
            </a>
            <a href="{{ route('pengguna.laporan.create') }}"
               class="group inline-flex items-center gap-2 px-5 py-2.5 bg-rose-500 hover:bg-rose-600 text-white text-sm font-bold rounded-xl transition shadow-lg shadow-rose-500/20">
                <div class="w-8 h-8 rounded-lg bg-white/20 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
                Laporkan Kerusakan
            </a>
        </div>
    </div>
</div>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6"
     x-data="{
        modalDetail: false,
        detail: null,
        buka(data) {
            this.detail = data;
            this.modalDetail = true;
        }
     }">

    {{-- PERLU PERHATIAN --}}
    @if(isset($perluPerhatian) && $perluPerhatian->count() > 0)
        <div class="bg-amber-50 border-l-4 border-amber-500 rounded-2xl p-5 shadow-sm">
            <div class="flex items-start gap-3">
                <div class="w-10 h-10 rounded-xl bg-amber-100 text-amber-600 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
                <div class="flex-1">
                    <h3 class="font-black text-amber-900 text-sm mb-2">Perlu Perhatian</h3>
                    <div class="space-y-2">
                        @foreach($perluPerhatian as $item)
                            <div class="flex items-center justify-between gap-3 text-xs bg-white/60 rounded-lg p-2.5">
                                <span class="text-amber-800 font-medium">{{ $item['pesan'] }}</span>
                                <a href="{{ $item['link'] }}" class="text-xs font-bold text-amber-900 hover:underline whitespace-nowrap">
                                    {{ $item['aksi'] }} →
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- STAT CARDS --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
        @php
            $statCards = [
                [
                    'label' => 'Total Reservasi',
                    'value' => $stats['total_reservasi'] ?? 0,
                    'subtitle' => 'Aktif & riwayat',
                    'gradient' => 'from-blue-600 to-blue-800',
                    'text_color' => 'text-blue-100',
                    'icon' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z',
                    'link' => route('pengguna.reservasi.index'),
                ],
                [
                    'label' => 'Menunggu Approval',
                    'value' => $stats['menunggu'] ?? 0,
                    'subtitle' => 'Sedang ditinjau',
                    'gradient' => 'from-amber-400 to-amber-500',
                    'text_color' => 'text-amber-50',
                    'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z',
                    'link' => route('pengguna.reservasi.index', ['status' => 'pending']),
                ],
                [
                    'label' => 'Disetujui',
                    'value' => $stats['disetujui'] ?? 0,
                    'subtitle' => 'Siap digunakan',
                    'gradient' => 'from-emerald-500 to-emerald-700',
                    'text_color' => 'text-emerald-50',
                    'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
                    'link' => route('pengguna.reservasi.index', ['status' => 'approved']),
                ],
                [
                    'label' => 'Laporan Aktif',
                    'value' => $stats['total_laporan'] ?? 0,
                    'subtitle' => 'Sedang diproses',
                    'gradient' => 'from-rose-500 to-rose-700',
                    'text_color' => 'text-rose-50',
                    'icon' => 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z',
                    'link' => route('pengguna.laporan.index'),
                ],
            ];
        @endphp

        @foreach($statCards as $card)
            <a href="{{ $card['link'] }}"
               class="group flex items-center gap-4 rounded-2xl p-5 shadow-md bg-gradient-to-br {{ $card['gradient'] }} text-white hover:shadow-lg hover:-translate-y-0.5 transition-all duration-300">
                <div class="w-12 h-12 rounded-xl bg-white/25 flex items-center justify-center shrink-0 backdrop-blur">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $card['icon'] }}"/>
                    </svg>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-xs font-bold {{ $card['text_color'] }} truncate">{{ $card['label'] }}</p>
                    <p class="text-3xl font-black leading-none mt-0.5">{{ $card['value'] }}</p>
                    <p class="text-[10px] font-medium {{ $card['text_color'] }} mt-0.5">{{ $card['subtitle'] }}</p>
                </div>
                <svg class="w-4 h-4 {{ $card['text_color'] }} group-hover:translate-x-1 transition shrink-0"
                     fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                </svg>
            </a>
        @endforeach
    </div>

    {{-- RESERVASI TERDEKAT --}}
    @if(isset($reservasiTerdekat) && $reservasiTerdekat)
        @php
            $badge = match($reservasiTerdekat->status) {
                'approved'  => ['Disetujui', 'bg-emerald-50 text-emerald-700 border-emerald-200'],
                'rejected'  => ['Ditolak', 'bg-rose-50 text-rose-700 border-rose-200'],
                'cancelled' => ['Dibatalkan', 'bg-slate-100 text-slate-600 border-slate-200'],
                default     => ['Menunggu', 'bg-amber-50 text-amber-700 border-amber-200'],
            };

            $detailTerdekat = [
                'tipe'         => 'reservasi',
                'id'           => $reservasiTerdekat->id,
                'fasilitas'    => $reservasiTerdekat->facility->nama_fasilitas ?? '-',
                'lokasi'       => $reservasiTerdekat->facility->lokasi ?? '-',
                'kapasitas'    => $reservasiTerdekat->facility->kapasitas ?? '-',
                'tanggal'      => \Carbon\Carbon::parse($reservasiTerdekat->tanggal)->translatedFormat('d F Y'),
                'waktu'        => substr($reservasiTerdekat->start_time, 0, 5) . ' - ' . substr($reservasiTerdekat->end_time, 0, 5) . ' WIB',
                'tujuan'       => $reservasiTerdekat->tujuan,
                'status'       => $badge[0],
                'statusCode'   => $reservasiTerdekat->status,
                'alasan'       => $reservasiTerdekat->alasan_tolak ?? $reservasiTerdekat->alasan_batal ?? null,
                'diajukan'     => $reservasiTerdekat->created_at ? \Carbon\Carbon::parse($reservasiTerdekat->created_at)->translatedFormat('d M Y H:i') . ' WIB' : '-',
                'petugas'      => $reservasiTerdekat->petugas->name ?? null,
                'cetak_url'    => $reservasiTerdekat->status === 'approved' ? route('pengguna.reservasi.cetak', $reservasiTerdekat->id) : null,
            ];
        @endphp

        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 bg-slate-50 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <div class="w-2.5 h-2.5 rounded-full bg-blue-900"></div>
                    <h3 class="font-black text-slate-900 text-xs uppercase tracking-wider">Reservasi Terdekat</h3>
                </div>
                <span class="text-[10px] font-bold text-slate-400">Akan datang</span>
            </div>
            <div class="p-6">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div class="space-y-2">
                        <h4 class="font-black text-slate-900 text-lg">{{ $reservasiTerdekat->facility->nama_fasilitas }}</h4>
                        <div class="flex flex-wrap gap-x-4 gap-y-1 text-sm text-slate-600">
                            <span class="flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                {{ \Carbon\Carbon::parse($reservasiTerdekat->tanggal)->translatedFormat('l, d F Y') }}
                            </span>
                            <span class="flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                {{ substr($reservasiTerdekat->start_time, 0, 5) }}–{{ substr($reservasiTerdekat->end_time, 0, 5) }} WIB
                            </span>
                        </div>
                        <p class="text-xs text-slate-500 italic">Tujuan: {{ $reservasiTerdekat->tujuan }}</p>
                    </div>
                    <button type="button"
                            @click="buka({{ \Illuminate\Support\Js::from($detailTerdekat) }})"
                            class="shrink-0 px-5 py-2.5 bg-blue-900 hover:bg-blue-800 text-white text-xs font-bold rounded-xl transition shadow-sm">
                        Detail
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- DUA CARD: RESERVASI TERAKHIR + LAPORAN TERAKHIR --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
        {{-- Reservasi Terakhir --}}
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm">
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <div class="w-2.5 h-2.5 rounded-full bg-blue-900"></div>
                    <h3 class="font-black text-slate-900 text-xs uppercase tracking-wider">Reservasi Terakhir</h3>
                </div>
                <a href="{{ route('pengguna.reservasi.index') }}" class="text-xs font-bold text-blue-900 hover:underline">
                    Lihat Semua →
                </a>
            </div>
            <div class="divide-y divide-slate-100">
                @forelse($myReservations->take(3) as $r)
                    @php
                        $badge = match($r->status) {
                            'approved'  => ['Disetujui', 'bg-emerald-50 text-emerald-700 border-emerald-200'],
                            'rejected'  => ['Ditolak', 'bg-rose-50 text-rose-700 border-rose-200'],
                            'cancelled' => ['Dibatalkan', 'bg-slate-100 text-slate-600 border-slate-200'],
                            default     => ['Menunggu', 'bg-amber-50 text-amber-700 border-amber-200'],
                        };

                        $detailLast = [
                            'tipe'       => 'reservasi',
                            'id'         => $r->id,
                            'fasilitas'  => $r->facility->nama_fasilitas ?? '-',
                            'lokasi'     => $r->facility->lokasi ?? '-',
                            'kapasitas'  => $r->facility->kapasitas ?? '-',
                            'tanggal'    => \Carbon\Carbon::parse($r->tanggal)->translatedFormat('d F Y'),
                            'waktu'      => substr($r->start_time, 0, 5) . ' - ' . substr($r->end_time, 0, 5) . ' WIB',
                            'tujuan'     => $r->tujuan,
                            'status'     => $badge[0],
                            'statusCode' => $r->status,
                            'alasan'     => $r->alasan_tolak ?? $r->alasan_batal ?? null,
                            'diajukan'   => $r->created_at ? \Carbon\Carbon::parse($r->created_at)->translatedFormat('d M Y H:i') . ' WIB' : '-',
                            'petugas'    => $r->petugas->name ?? null,
                            'cetak_url'  => $r->status === 'approved' ? route('pengguna.reservasi.cetak', $r->id) : null,
                        ];
                    @endphp
                    <div class="px-6 py-4 flex items-center justify-between gap-3 hover:bg-slate-50 transition cursor-pointer"
                         @click="buka({{ \Illuminate\Support\Js::from($detailLast) }})">
                        <div class="min-w-0 flex-1">
                            <p class="font-bold text-slate-900 text-sm truncate">{{ $r->facility->nama_fasilitas }}</p>
                            <p class="text-xs text-slate-500 mt-0.5">
                                {{ \Carbon\Carbon::parse($r->tanggal)->translatedFormat('d M Y') }} ·
                                {{ substr($r->start_time, 0, 5) }}–{{ substr($r->end_time, 0, 5) }}
                            </p>
                        </div>
                        <span class="shrink-0 px-2.5 py-1 text-[10px] font-bold rounded border {{ $badge[1] }}">
                            {{ $badge[0] }}
                        </span>
                    </div>
                @empty
                    <div class="px-6 py-12 text-center">
                        <div class="w-14 h-14 mx-auto rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mb-3">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <p class="text-sm font-bold text-slate-700">Belum ada reservasi</p>
                        <p class="text-xs text-slate-400 mt-1">Ajukan reservasi pertama Anda</p>
                        <a href="{{ route('pengguna.reservasi.create') }}"
                           class="inline-block mt-3 text-xs font-bold text-blue-900 hover:underline">
                            Ajukan sekarang →
                        </a>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Laporan Terakhir --}}
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm">
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <div class="w-2.5 h-2.5 rounded-full bg-rose-600"></div>
                    <h3 class="font-black text-slate-900 text-xs uppercase tracking-wider">Laporan Terakhir</h3>
                </div>
                <a href="{{ route('pengguna.laporan.index') }}" class="text-xs font-bold text-rose-600 hover:underline">
                    Lihat Semua →
                </a>
            </div>
            <div class="divide-y divide-slate-100">
                @forelse(($myReports ?? collect())->take(3) as $l)
                    @php
                        $badge = match($l->status_laporan ?? 'baru') {
                            'selesai'  => ['Selesai', 'bg-emerald-50 text-emerald-700 border-emerald-200'],
                            'diproses' => ['Diproses', 'bg-blue-50 text-blue-700 border-blue-200'],
                            'ditolak'  => ['Ditolak', 'bg-rose-50 text-rose-700 border-rose-200'],
                            default    => ['Baru', 'bg-amber-50 text-amber-700 border-amber-200'],
                        };

                        $detailLaporan = [
                            'tipe'       => 'laporan',
                            'id'         => $l->id,
                            'fasilitas'  => $l->facility->nama_fasilitas ?? '-',
                            'lokasi'     => $l->facility->lokasi ?? '-',
                            'kategori'   => $l->kategori_laporan ?? '-',
                            'deskripsi'  => $l->deskripsi,
                            'foto'       => $l->foto_url ?? null,
                            'status'     => $badge[0],
                            'statusCode' => $l->status_laporan ?? 'baru',
                            'catatan'    => $l->catatan_resolusi,
                            'diajukan'   => $l->created_at ? \Carbon\Carbon::parse($l->created_at)->translatedFormat('d M Y H:i') . ' WIB' : '-',
                            'petugas'    => $l->petugas->name ?? null,
                        ];
                    @endphp
                    <div class="px-6 py-4 flex items-center justify-between gap-3 hover:bg-slate-50 transition cursor-pointer"
                         @click="buka({{ \Illuminate\Support\Js::from($detailLaporan) }})">
                        <div class="min-w-0 flex-1">
                            <p class="font-bold text-slate-900 text-sm truncate">{{ $l->facility->nama_fasilitas ?? 'Fasilitas' }}</p>
                            <p class="text-xs text-slate-500 mt-0.5">
                                {{ $l->kategori_laporan ?? '-' }} · {{ \Carbon\Carbon::parse($l->created_at)->translatedFormat('d M Y') }}
                            </p>
                        </div>
                        <span class="shrink-0 px-2.5 py-1 text-[10px] font-bold rounded border {{ $badge[1] }}">
                            {{ $badge[0] }}
                        </span>
                    </div>
                @empty
                    <div class="px-6 py-12 text-center">
                        <div class="w-14 h-14 mx-auto rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mb-3">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                        </div>
                        <p class="text-sm font-bold text-slate-700">Belum ada laporan</p>
                        <p class="text-xs text-slate-400 mt-1">Laporkan kerusakan fasilitas</p>
                        <a href="{{ route('pengguna.laporan.create') }}"
                           class="inline-block mt-3 text-xs font-bold text-rose-600 hover:underline">
                            Buat laporan →
                        </a>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- MODAL DETAIL (SHARED — sama seperti Reservasi Saya) --}}
    <div x-show="modalDetail" x-cloak
         x-effect="document.body.classList.toggle('overflow-hidden', modalDetail)"
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4"
         @keydown.escape.window="modalDetail = false">
        <div @click.outside="modalDetail = false"
             class="bg-white rounded-2xl max-w-lg w-full max-h-[90vh] overflow-y-auto shadow-2xl border border-slate-200">
            <div class="flex justify-between items-center px-6 py-4 border-b border-slate-100 sticky top-0 bg-white z-10">
                <h3 class="font-black text-slate-900 text-sm">
                    <span x-show="detail?.tipe === 'reservasi'">Detail Reservasi</span>
                    <span x-show="detail?.tipe === 'laporan'">Detail Laporan</span>
                    <span class="text-xs font-normal text-slate-400 ml-1" x-text="'#' + (detail?.id ?? '')"></span>
                </h3>
                <button type="button" @click="modalDetail = false"
                        class="p-1.5 text-slate-400 hover:text-slate-700 hover:bg-slate-100 rounded-lg transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <div class="p-6 space-y-4">
                <template x-if="detail">
                    <div class="space-y-3 text-sm">

                        {{-- DETAIL RESERVASI --}}
                        <template x-if="detail.tipe === 'reservasi'">
                            <div class="space-y-3">
                                <div class="flex justify-between gap-3 border-b border-slate-100 pb-2.5">
                                    <span class="text-slate-500 font-semibold">Fasilitas</span>
                                    <span class="font-black text-slate-900 text-right" x-text="detail.fasilitas"></span>
                                </div>
                                <div class="flex justify-between gap-3 border-b border-slate-100 pb-2.5">
                                    <span class="text-slate-500 font-semibold">Lokasi / Kapasitas</span>
                                    <span class="text-slate-700 text-right font-bold" x-text="detail.lokasi + ' (' + detail.kapasitas + ' org)'"></span>
                                </div>
                                <div class="flex justify-between gap-3 border-b border-slate-100 pb-2.5">
                                    <span class="text-slate-500 font-semibold">Tanggal</span>
                                    <span class="font-black text-slate-900 text-right" x-text="detail.tanggal"></span>
                                </div>
                                <div class="flex justify-between gap-3 border-b border-slate-100 pb-2.5">
                                    <span class="text-slate-500 font-semibold">Waktu</span>
                                    <span class="font-black text-slate-900 text-right" x-text="detail.waktu"></span>
                                </div>
                                <div class="flex justify-between gap-3 border-b border-slate-100 pb-2.5">
                                    <span class="text-slate-500 font-semibold">Status</span>
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider"
                                          :class="{
                                              'bg-emerald-50 text-emerald-700': detail.statusCode === 'approved',
                                              'bg-rose-50 text-rose-700': detail.statusCode === 'rejected',
                                              'bg-slate-100 text-slate-600': detail.statusCode === 'cancelled',
                                              'bg-amber-50 text-amber-700': detail.statusCode === 'pending'
                                          }"
                                          x-text="detail.status"></span>
                                </div>

                                <div class="bg-slate-50 p-3 rounded-xl space-y-2">
                                    <div class="flex justify-between gap-3 text-xs">
                                        <span class="text-slate-500 font-semibold">Diajukan:</span>
                                        <span class="font-bold text-slate-700 text-right" x-text="detail.diajukan"></span>
                                    </div>
                                    <div class="flex justify-between gap-3 text-xs" x-show="detail.petugas">
                                        <span class="text-slate-500 font-semibold">Petugas:</span>
                                        <span class="font-bold text-slate-800 text-right" x-text="detail.petugas"></span>
                                    </div>
                                </div>

                                <div>
                                    <p class="text-xs font-bold text-slate-500 mb-1">Tujuan Kegiatan:</p>
                                    <p class="text-sm text-slate-800 bg-slate-50 p-3 rounded-xl leading-relaxed" x-text="detail.tujuan"></p>
                                </div>

                                <div x-show="detail.alasan" class="p-3 bg-rose-50 border border-rose-200 rounded-xl">
                                    <p class="text-xs font-black text-rose-800 mb-1">Catatan Petugas</p>
                                    <p class="text-sm text-rose-900 leading-relaxed" x-text="detail.alasan"></p>
                                </div>
                            </div>
                        </template>

                        {{-- DETAIL LAPORAN --}}
                        <template x-if="detail.tipe === 'laporan'">
                            <div class="space-y-3">
                                <div class="flex justify-between gap-3 border-b border-slate-100 pb-2.5">
                                    <span class="text-slate-500 font-semibold">Fasilitas</span>
                                    <span class="font-black text-slate-900 text-right" x-text="detail.fasilitas"></span>
                                </div>
                                <div class="flex justify-between gap-3 border-b border-slate-100 pb-2.5">
                                    <span class="text-slate-500 font-semibold">Lokasi</span>
                                    <span class="text-slate-700 text-right font-bold" x-text="detail.lokasi"></span>
                                </div>
                                <div class="flex justify-between gap-3 border-b border-slate-100 pb-2.5">
                                    <span class="text-slate-500 font-semibold">Kategori</span>
                                    <span class="font-black text-slate-900 text-right" x-text="detail.kategori"></span>
                                </div>
                                <div class="flex justify-between gap-3 border-b border-slate-100 pb-2.5">
                                    <span class="text-slate-500 font-semibold">Status</span>
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider"
                                          :class="{
                                              'bg-emerald-50 text-emerald-700': detail.statusCode === 'selesai',
                                              'bg-blue-50 text-blue-700': detail.statusCode === 'diproses',
                                              'bg-rose-50 text-rose-700': detail.statusCode === 'ditolak',
                                              'bg-amber-50 text-amber-700': detail.statusCode === 'baru'
                                          }"
                                          x-text="detail.status"></span>
                                </div>

                                <div>
                                    <p class="text-xs font-bold text-slate-500 mb-1">Deskripsi:</p>
                                    <p class="text-sm text-slate-800 bg-slate-50 p-3 rounded-xl leading-relaxed" x-text="detail.deskripsi"></p>
                                </div>

                                <div x-show="detail.foto" class="pt-1">
                                    <p class="text-xs font-bold text-slate-500 mb-1">Foto Bukti:</p>
                                    <img :src="detail.foto" alt="Foto laporan"
                                         class="w-full rounded-xl border border-slate-200 max-h-64 object-cover">
                                </div>

                                <div x-show="detail.catatan" class="p-3 bg-emerald-50 border border-emerald-200 rounded-xl">
                                    <p class="text-xs font-black text-emerald-800 mb-1">Catatan Petugas</p>
                                    <p class="text-sm text-emerald-900 leading-relaxed" x-text="detail.catatan"></p>
                                </div>

                                <div class="bg-slate-50 p-3 rounded-xl space-y-2">
                                    <div class="flex justify-between gap-3 text-xs">
                                        <span class="text-slate-500 font-semibold">Diajukan:</span>
                                        <span class="font-bold text-slate-700 text-right" x-text="detail.diajukan"></span>
                                    </div>
                                    <div class="flex justify-between gap-3 text-xs" x-show="detail.petugas">
                                        <span class="text-slate-500 font-semibold">Petugas:</span>
                                        <span class="font-bold text-slate-800 text-right" x-text="detail.petugas"></span>
                                    </div>
                                </div>
                            </div>
                        </template>

                    </div>
                </template>
            </div>

            {{-- FOOTER: Cetak (jika approved) + Tutup --}}
            <div class="flex items-center justify-between gap-2 px-6 py-4 border-t border-slate-100 bg-slate-50">
                <div>
                    <template x-if="detail && detail.tipe === 'reservasi' && detail.cetak_url">
                        <a :href="detail.cetak_url" target="_blank"
                           class="inline-flex items-center gap-2 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl transition shadow-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                            </svg>
                            Cetak Surat
                        </a>
                    </template>
                </div>
                <button type="button" @click="modalDetail = false"
                        class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-sm rounded-xl transition">
                    Tutup
                </button>
            </div>
        </div>
    </div>
</div>
@endsection