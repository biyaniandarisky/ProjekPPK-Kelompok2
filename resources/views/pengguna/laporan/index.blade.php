@extends('layouts.app')

@section('title', 'Laporan Saya')

@section('content')
<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-4"
     x-data="{
        detail: null,
        showDetail: false,
        buka(data) {
            this.detail = data;
            this.showDetail = true;
        }
     }">

    {{-- HEADER --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div class="flex items-center gap-3">
            <a href="{{ route('pengguna.dashboard') }}"
               class="inline-flex items-center justify-center w-10 h-10 bg-white border border-slate-200 hover:border-rose-600 text-slate-600 hover:text-rose-600 rounded-xl transition shadow-sm"
               title="Kembali ke Dashboard">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <div>
                <h1 class="text-2xl font-black text-slate-900">Laporan Saya</h1>
                <p class="text-xs text-slate-500">Status penanganan laporan kerusakan.</p>
            </div>
        </div>
        <a href="{{ route('pengguna.laporan.create') }}"
           class="inline-flex items-center justify-center gap-1.5 px-5 py-2.5 bg-rose-600 hover:bg-rose-700 text-white text-sm font-bold rounded-xl shadow-sm transition shrink-0">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
            </svg>
            Lapor Kerusakan
        </a>
    </div>

    {{-- TABLE --}}
    @if($myReports->isEmpty())
        <div class="bg-white rounded-2xl border border-slate-200 py-16 text-center">
            <div class="w-16 h-16 mx-auto rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mb-3">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>
            <p class="text-sm font-bold text-slate-700">Belum ada laporan</p>
            <p class="text-xs text-slate-400 mt-1">Laporkan kerusakan fasilitas yang Anda temukan</p>
            <a href="{{ route('pengguna.laporan.create') }}"
               class="inline-block mt-4 px-5 py-2.5 bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold rounded-xl transition">
                Buat Laporan →
            </a>
        </div>
    @else
        <div class="space-y-3">

            {{-- Card List (bukan table) — konsisten dengan reservasi --}}
            @foreach($myReports as $l)
                @php
                    $badge = match($l->status_laporan ?? 'baru') {
                        'selesai'  => ['Selesai', 'bg-emerald-50 text-emerald-700 border-emerald-200'],
                        'diproses' => ['Diproses', 'bg-blue-50 text-blue-700 border-blue-200'],
                        'ditolak'  => ['Ditolak', 'bg-rose-50 text-rose-700 border-rose-200'],
                        default    => ['Baru', 'bg-amber-50 text-amber-700 border-amber-200'],
                    };

                    $detailArray = [
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
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4 hover:border-slate-300 hover:shadow-md transition cursor-pointer"
                     @click="buka({{ \Illuminate\Support\Js::from($detailArray) }})">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">

                        {{-- Info --}}
                        <div class="space-y-1.5 text-sm min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <h2 class="font-black text-slate-900 truncate">{{ $l->facility->nama_fasilitas ?? '-' }}</h2>
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold border uppercase tracking-wider {{ $badge[1] }}">
                                    {{ $badge[0] }}
                                </span>
                            </div>

                            <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-slate-600">
                                <span class="font-semibold text-slate-800 flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5a1.99 1.99 0 011.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.99 1.99 0 013 12V7a4 4 0 014-4z"/>
                                    </svg>
                                    {{ $l->kategori_laporan ?? '-' }}
                                </span>
                                <span class="flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                    {{ $l->facility->lokasi ?? '-' }}
                                </span>
                                <span class="flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                    {{ $l->created_at->translatedFormat('d M Y') }}
                                </span>
                            </div>

                            <p class="text-xs text-slate-500 italic">
                                {{ \Illuminate\Support\Str::limit($l->deskripsi, 70) }}
                            </p>
                        </div>

                        {{-- Tombol Aksi --}}
                        <div class="flex items-center gap-2 shrink-0 self-end md:self-center" @click.stop>
                            <button type="button"
                                    @click="buka({{ \Illuminate\Support\Js::from($detailArray) }})"
                                    class="inline-flex items-center gap-1.5 px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                                Detail
                            </button>
                        </div>
                    </div>
                </div>
            @endforeach

        </div>
    @endif

    {{-- MODAL DETAIL LAPORAN --}}
    <div x-show="showDetail" x-cloak
         x-effect="document.body.classList.toggle('overflow-hidden', showDetail)"
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4"
         @keydown.escape.window="showDetail = false">
        <div @click.outside="showDetail = false"
             class="bg-white rounded-2xl max-w-lg w-full max-h-[90vh] overflow-y-auto shadow-2xl border border-slate-200">

            {{-- HEADER MODAL --}}
            <div class="flex justify-between items-center px-6 py-4 border-b border-slate-100 sticky top-0 bg-white z-10">
                <h3 class="font-black text-slate-900 text-sm flex items-center gap-2">
                    <svg class="w-4 h-4 text-rose-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    Detail Laporan
                    <span class="text-xs font-normal text-slate-400" x-text="'#' + (detail?.id ?? '')"></span>
                </h3>
                <button type="button" @click="showDetail = false"
                        class="p-1.5 text-slate-400 hover:text-slate-700 hover:bg-slate-100 rounded-lg transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            {{-- BODY --}}
            <div class="p-6 space-y-4">
                <template x-if="detail">
                    <div class="space-y-3 text-sm">

                        {{-- Info Dasar --}}
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

                        {{-- Deskripsi --}}
                        <div>
                            <p class="text-xs font-bold text-slate-500 mb-1">Deskripsi Kerusakan:</p>
                            <p class="text-sm text-slate-800 bg-slate-50 p-3 rounded-xl leading-relaxed" x-text="detail.deskripsi"></p>
                        </div>

                        {{-- Foto --}}
                        <div x-show="detail.foto" class="pt-1">
                            <p class="text-xs font-bold text-slate-500 mb-1">Foto Bukti:</p>
                            <img :src="detail.foto" alt="Foto laporan"
                                 class="w-full rounded-xl border border-slate-200 max-h-64 object-cover">
                        </div>

                        {{-- Catatan --}}
                        <div x-show="detail.catatan" class="p-3 bg-emerald-50 border border-emerald-200 rounded-xl">
                            <p class="text-xs font-black text-emerald-800 mb-1">Catatan Petugas</p>
                            <p class="text-sm text-emerald-900 leading-relaxed" x-text="detail.catatan"></p>
                        </div>

                        {{-- Meta --}}
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

            {{-- FOOTER --}}
            <div class="flex justify-end px-6 py-4 border-t border-slate-100 bg-slate-50">
                <button type="button" @click="showDetail = false"
                        class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-sm rounded-xl transition">
                    Tutup
                </button>
            </div>
        </div>
    </div>
</div>
@endsection