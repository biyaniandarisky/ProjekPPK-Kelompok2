@extends('layouts.app')

@section('title', 'Antrian Laporan')

@section('content')
<style>
    .row-no { counter-reset: rowno; }
    .row-no > .row-item { counter-increment: rowno; }
    .row-no .no-cell::before { content: counter(rowno); }
</style>
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-4"
     x-data="{
        filterStatus: 'semua',
        searchQuery: '',
        modalSelesai: null,
        modalDetail: null,
        detail: null,
        matchSearch(haystack) {
            return haystack.toLowerCase().includes(this.searchQuery.toLowerCase());
        },
        bukaDetail(data) {
            this.detail = data;
            this.modalDetail = true;
        }
     }">

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
                <h1 class="text-2xl font-black text-slate-900">Antrian Laporan</h1>
                <p class="text-xs text-slate-500">Kelola laporan kendala dari pengguna.</p>
            </div>
        </div>
    </div>

    {{-- FILTER & SEARCH --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4 space-y-3">
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
            @foreach(['semua' => 'Semua', 'baru' => 'Baru', 'diproses' => 'Diproses', 'selesai' => 'Selesai'] as $key => $label)
                <button @click="filterStatus = '{{ $key }}'"
                        :class="filterStatus === '{{ $key }}' ? 'bg-blue-900 text-white border-blue-900 shadow-sm' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50'"
                        class="py-2.5 px-2 rounded-xl border text-xs font-bold transition text-center truncate">
                    {{ $label }}
                </button>
            @endforeach
        </div>

        <div class="relative pt-3 border-t border-slate-100">
            <svg class="w-4 h-4 text-slate-400 absolute left-3 top-[calc(50%+6px)] -translate-y-1/2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 10a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <input type="text" x-model="searchQuery" placeholder="Cari nama pelapor, fasilitas, atau kategori..."
                   class="w-full h-10 pl-10 pr-4 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:border-blue-900 transition">
        </div>
    </div>

    {{-- DAFTAR LAPORAN --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">

        {{-- Header Desktop --}}
        <div class="hidden lg:grid grid-cols-12 gap-3 px-5 py-3 bg-slate-50 border-b border-slate-200 text-[10px] font-bold text-slate-500 uppercase tracking-wider">
            <div class="col-span-1">No</div>
            <div class="col-span-3">Fasilitas</div>
            <div class="col-span-3">Pelapor</div>
            <div class="col-span-2">Kategori</div>
            <div class="col-span-2 text-right">Aksi</div>
            <div class="col-span-1 text-right">Detail</div>
        </div>

        {{-- Rows --}}
        <div class="divide-y divide-slate-100 row-no">
            @forelse($reports as $l)
                @php
                    $statusMap = [
                        'baru'     => ['Baru',     'bg-blue-50 text-blue-700 border-blue-200'],
                        'diproses' => ['Diproses', 'bg-amber-50 text-amber-700 border-amber-200'],
                        'selesai'  => ['Selesai',  'bg-emerald-50 text-emerald-700 border-emerald-200'],
                        'ditolak'  => ['Ditolak',  'bg-rose-50 text-rose-700 border-rose-200'],
                    ];
                    $badge = $statusMap[$l->status_laporan] ?? ['—', 'bg-slate-100 text-slate-600 border-slate-200'];
                    $haystack = collect([$l->user->name ?? '', $l->facility->nama_fasilitas ?? '', $l->kategori_laporan])->implode(' ');

                    $detailArray = [
                        'id'         => $l->id,
                        'fasilitas'  => $l->facility->nama_fasilitas ?? '-',
                        'lokasi'     => $l->facility->lokasi ?? '-',
                        'pelapor'    => $l->user->name ?? '-',
                        'email'      => $l->user->email ?? '-',
                        'kategori'   => $l->kategori_laporan ?? '-',
                        'deskripsi'  => $l->deskripsi,
                        'foto'       => $l->foto_url ?? null,
                        'status'     => $badge[0],
                        'statusCode' => $l->status_laporan ?? 'baru',
                        'catatan'    => $l->catatan_resolusi,
                        'diajukan'   => $l->created_at ? $l->created_at->translatedFormat('d M Y H:i') . ' WIB' : '-',
                        'petugas'    => $l->petugas->name ?? null,
                    ];
                @endphp

                <div x-show="(filterStatus === 'semua' || filterStatus === '{{ $l->status_laporan }}') && matchSearch(@js($haystack))"
                     class="row-item lg:grid lg:grid-cols-12 lg:gap-3 lg:items-center px-5 py-3.5 hover:bg-slate-50 transition">

                    {{-- Mobile: Card Layout --}}
                    <div class="lg:hidden space-y-3">
                        <div class="flex items-start justify-between gap-2">
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <p class="font-bold text-slate-900 text-sm truncate">{{ $l->facility->nama_fasilitas ?? '-' }}</p>
                                    <span class="text-[10px] text-slate-400 font-mono">No. <span class="no-cell"></span></span>
                                </div>
                                <p class="text-xs text-slate-500 mt-0.5">{{ $l->user->name ?? '-' }}</p>
                                <p class="text-xs text-slate-500">
                                    {{ $l->kategori_laporan }} · {{ $l->created_at->translatedFormat('d M Y') }}
                                </p>
                            </div>
                            <span class="shrink-0 px-2.5 py-1 rounded-full text-[10px] font-bold border uppercase tracking-wider {{ $badge[1] }}">
                                {{ $badge[0] }}
                            </span>
                        </div>

                        <div class="flex items-center gap-2 pt-3 border-t border-slate-100">
                            <button type="button" @click="bukaDetail({{ \Illuminate\Support\Js::from($detailArray) }})"
                                    class="flex-1 inline-flex items-center justify-center gap-1.5 px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-lg transition">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                                Detail
                            </button>

                            @if($l->status_laporan === 'baru')
                                <form action="{{ route('petugas.laporan.process', $l->id) }}" method="POST"
                                      onsubmit="return confirm('Proses laporan ini? Fasilitas akan otomatis ditandai Dalam Perbaikan.')" class="flex-1">
                                    @csrf
                                    <button type="submit" class="w-full inline-flex items-center justify-center gap-1.5 px-3 py-2 bg-blue-900 hover:bg-blue-800 text-white text-xs font-bold rounded-lg transition">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                        </svg>
                                        Proses
                                    </button>
                                </form>
                            @elseif($l->status_laporan === 'diproses')
                                <button type="button" @click="modalSelesai = {{ $l->id }}"
                                        class="flex-1 inline-flex items-center justify-center gap-1.5 px-3 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-lg transition">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    Selesaikan
                                </button>
                            @endif
                        </div>
                    </div>

                    {{-- Desktop: Row Layout --}}
                    <div class="hidden lg:contents">
                        {{-- ID --}}
                        <div class="col-span-1">
                            <span class="text-xs font-mono text-slate-500 no-cell"></span>
                        </div>

                        {{-- Fasilitas --}}
                        <div class="col-span-3 min-w-0">
                            <p class="font-bold text-slate-900 text-sm truncate">{{ $l->facility->nama_fasilitas ?? '-' }}</p>
                            <p class="text-[10px] text-slate-400 truncate">{{ $l->facility->lokasi ?? '-' }}</p>
                        </div>

                        {{-- Pelapor --}}
                        <div class="col-span-3 min-w-0">
                            <p class="text-xs font-semibold text-slate-700 truncate">{{ $l->user->name ?? '-' }}</p>
                            <p class="text-[10px] text-slate-400 truncate">{{ $l->user->email ?? '-' }}</p>
                        </div>

                        {{-- Kategori --}}
                        <div class="col-span-2">
                            <p class="text-xs font-semibold text-slate-700">{{ $l->kategori_laporan }}</p>
                            <p class="text-[10px] text-slate-400">{{ $l->created_at->translatedFormat('d M Y') }}</p>
                        </div>

                        {{-- Aksi --}}
                        <div class="col-span-2 flex items-center justify-end gap-1.5">
                            @if($l->status_laporan === 'baru')
                                <form action="{{ route('petugas.laporan.process', $l->id) }}" method="POST"
                                      onsubmit="return confirm('Proses laporan ini? Fasilitas akan otomatis ditandai Dalam Perbaikan.')">
                                    @csrf
                                    <button type="submit"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-blue-900 hover:bg-blue-800 text-white text-xs font-bold rounded-lg transition">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                        </svg>
                                        Proses
                                    </button>
                                </form>
                            @elseif($l->status_laporan === 'diproses')
                                <button type="button" @click="modalSelesai = {{ $l->id }}"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-lg transition">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    Selesaikan
                                </button>
                            @else
                                <span class="text-[10px] text-slate-400 italic">—</span>
                            @endif
                        </div>

                        {{-- Detail (Kolom Terpisah) --}}
                        <div class="col-span-1 flex items-center justify-end">
                            <button type="button" @click="bukaDetail({{ \Illuminate\Support\Js::from($detailArray) }})"
                                    class="inline-flex items-center justify-center w-8 h-8 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg transition"
                                    title="Lihat Detail">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            @empty
                <div class="px-5 py-16 text-center">
                    <div class="w-16 h-16 mx-auto rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mb-3">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </div>
                    <p class="text-sm font-bold text-slate-700">Belum ada laporan</p>
                    <p class="text-xs text-slate-400 mt-1">Laporan akan muncul saat pengguna melaporkan kerusakan</p>
                </div>
            @endforelse
        </div>
    </div>

    {{-- MODAL DETAIL --}}
    <div x-show="modalDetail" x-cloak
         x-effect="document.body.classList.toggle('overflow-hidden', modalDetail)"
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4"
         @keydown.escape.window="modalDetail = false">
        <div @click.outside="modalDetail = false"
             class="bg-white rounded-2xl max-w-lg w-full max-h-[90vh] overflow-y-auto shadow-2xl border border-slate-200">
            <div class="flex justify-between items-center px-6 py-4 border-b border-slate-100 sticky top-0 bg-white z-10">
                <h3 class="font-black text-slate-900 text-sm">
                    Detail Laporan
                </h3>
                <button type="button" @click="modalDetail = false"
                        class="p-1.5 text-slate-400 hover:text-slate-700 hover:bg-slate-100 rounded-lg transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <div class="p-6 space-y-3 text-sm">
                <template x-if="detail">
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
                            <span class="text-slate-500 font-semibold">Pelapor</span>
                            <span class="font-bold text-slate-900 text-right" x-text="detail.pelapor"></span>
                        </div>
                        <div class="flex justify-between gap-3 border-b border-slate-100 pb-2.5">
                            <span class="text-slate-500 font-semibold">Email</span>
                            <span class="text-slate-600 text-right text-xs" x-text="detail.email"></span>
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
                                      'bg-amber-50 text-amber-700': detail.statusCode === 'diproses',
                                      'bg-rose-50 text-rose-700': detail.statusCode === 'ditolak',
                                      'bg-blue-50 text-blue-700': detail.statusCode === 'baru'
                                  }"
                                  x-text="detail.status"></span>
                        </div>

                        <div>
                            <p class="text-xs font-bold text-slate-500 mb-1">Deskripsi Kendala:</p>
                            <p class="text-sm text-slate-800 bg-slate-50 p-3 rounded-xl leading-relaxed" x-text="detail.deskripsi"></p>
                        </div>

                        <div x-show="detail.foto" class="pt-1">
                            <p class="text-xs font-bold text-slate-500 mb-1">Foto Bukti:</p>
                            <img :src="detail.foto" alt="Foto laporan"
                                 class="w-full rounded-xl border border-slate-200 max-h-64 object-cover">
                        </div>

                        <div x-show="detail.catatan" class="p-3 bg-emerald-50 border border-emerald-200 rounded-xl">
                            <p class="text-xs font-black text-emerald-800 mb-1">Catatan Resolusi</p>
                            <p class="text-sm text-emerald-900 leading-relaxed" x-text="detail.catatan"></p>
                        </div>

                        <div class="bg-slate-50 p-3 rounded-xl space-y-2">
                            <div class="flex justify-between gap-3 text-xs">
                                <span class="text-slate-500 font-semibold">Dilaporkan:</span>
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

            <div class="flex justify-end px-6 py-4 border-t border-slate-100 bg-slate-50">
                <button type="button" @click="modalDetail = false"
                        class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-sm rounded-xl transition">
                    Tutup
                </button>
            </div>
        </div>
    </div>

    {{-- MODAL SELESAI --}}
    @foreach($reports as $l)
        @if($l->status_laporan === 'diproses')
            <div x-show="modalSelesai === {{ $l->id }}" x-cloak
                 x-effect="document.body.classList.toggle('overflow-hidden', modalSelesai !== null)"
                 class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4"
                 @keydown.escape.window="modalSelesai = null">
                <div @click.outside="modalSelesai = null"
                     class="bg-white rounded-2xl max-w-md w-full shadow-2xl border border-slate-200">
                    <div class="px-6 py-4 border-b border-slate-100">
                        <h3 class="font-black text-slate-900">Selesaikan Laporan</h3>
                        <p class="text-xs text-slate-500 mt-0.5">{{ $l->facility->nama_fasilitas ?? '-' }} · {{ $l->kategori_laporan }}</p>
                    </div>
                    <form action="{{ route('petugas.laporan.resolve', $l->id) }}" method="POST" class="p-6 space-y-4"
                          x-data="{ submitting: false }" @submit="submitting = true">
                        @csrf
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">
                                Catatan Perbaikan <span class="text-rose-500">*</span>
                            </label>
                            <textarea name="catatan_resolusi" rows="3" required maxlength="250"
                                      placeholder="Contoh: AC sudah diperbaiki dan diuji normal."
                                      class="w-full p-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:border-emerald-600 resize-none"></textarea>
                        </div>
                        <div class="flex justify-end gap-2 pt-2 border-t border-slate-100">
                            <button type="button" @click="modalSelesai = null"
                                    class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition">
                                Batal
                            </button>
                            <button type="submit" :disabled="submitting"
                                    class="inline-flex items-center gap-1.5 px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 disabled:opacity-60 text-white text-xs font-bold rounded-xl transition shadow-sm">
                                <svg x-show="submitting" x-cloak class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                                </svg>
                                <span x-text="submitting ? 'Memproses...' : 'Tandai Selesai'"></span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endif
    @endforeach
</div>
@endsection