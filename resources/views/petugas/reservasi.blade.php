@extends('layouts.app')

@section('title', 'Antrian Reservasi')

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
        modalTolak: null,
        modalBatal: null,
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
                <h1 class="text-2xl font-black text-slate-900">Antrian Reservasi</h1>
                <p class="text-xs text-slate-500">Daftar semua reservasi fasilitas beserta aksinya.</p>
            </div>
        </div>
    </div>

    {{-- FILTER & SEARCH --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4 space-y-3">
        <div class="grid grid-cols-3 sm:grid-cols-5 gap-2">
            @foreach(['semua' => 'Semua', 'pending' => 'Menunggu', 'approved' => 'Disetujui', 'rejected' => 'Ditolak', 'cancelled' => 'Dibatalkan'] as $key => $label)
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
            <input type="text" x-model="searchQuery" placeholder="Cari nama pemohon, ruangan, atau lokasi..."
                   class="w-full h-10 pl-10 pr-4 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:border-blue-900 transition">
        </div>
    </div>

    {{-- DAFTAR RESERVASI --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">

        {{-- Header Desktop --}}
        <div class="hidden lg:grid grid-cols-12 gap-3 px-5 py-3 bg-slate-50 border-b border-slate-200 text-[10px] font-bold text-slate-500 uppercase tracking-wider">
            <div class="col-span-1">No</div>
            <div class="col-span-3">Fasilitas</div>
            <div class="col-span-3">Pemohon</div>
            <div class="col-span-2">Jadwal</div>
            <div class="col-span-2 text-right">Aksi</div>
            <div class="col-span-1 text-right">Detail</div>
        </div>

        {{-- Rows --}}
        <div class="divide-y divide-slate-100 row-no">
            @forelse($reservations as $r)
                @php
                    $statusMap = [
                        'pending'   => ['Menunggu',   'bg-amber-50 text-amber-700 border-amber-200'],
                        'approved'  => ['Disetujui',  'bg-emerald-50 text-emerald-700 border-emerald-200'],
                        'rejected'  => ['Ditolak',    'bg-rose-50 text-rose-700 border-rose-200'],
                        'cancelled' => ['Dibatalkan', 'bg-slate-100 text-slate-600 border-slate-200'],
                    ];
                    $badge = $statusMap[$r->status] ?? ['—', 'bg-slate-100 text-slate-600 border-slate-200'];
                    $haystack = collect([$r->user->name ?? '', $r->facility->nama_fasilitas ?? '', $r->tujuan])->implode(' ');

                    $detailArray = [
                        'id'         => $r->id,
                        'fasilitas'  => $r->facility->nama_fasilitas ?? '-',
                        'lokasi'     => $r->facility->lokasi ?? '-',
                        'kapasitas'  => $r->facility->kapasitas ?? '-',
                        'pemohon'    => $r->user->name ?? '-',
                        'email'      => $r->user->email ?? '-',
                        'tanggal'    => $r->tanggal->translatedFormat('d F Y'),
                        'waktu'      => substr($r->start_time, 0, 5) . ' - ' . substr($r->end_time, 0, 5) . ' WIB',
                        'tujuan'     => $r->tujuan,
                        'status'     => $badge[0],
                        'statusCode' => $r->status,
                        'alasan'     => $r->alasan_tolak ?? $r->alasan_batal ?? null,
                        'diajukan'   => $r->created_at ? $r->created_at->translatedFormat('d M Y H:i') . ' WIB' : '-',
                        'petugas'    => $r->petugas->name ?? null,
                    ];
                @endphp

                <div x-show="(filterStatus === 'semua' || filterStatus === '{{ $r->status }}') && matchSearch(@js($haystack))"
                     class="row-item lg:grid lg:grid-cols-12 lg:gap-3 lg:items-center px-5 py-3.5 hover:bg-slate-50 transition">

                    {{-- Mobile: Card Layout --}}
                    <div class="lg:hidden space-y-3">
                        <div class="flex items-start justify-between gap-2">
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <p class="font-bold text-slate-900 text-sm truncate">{{ $r->facility->nama_fasilitas ?? '-' }}</p>
                                    <span class="text-[10px] text-slate-400 font-mono">No. <span class="no-cell"></span></span>
                                </div>
                                <p class="text-xs text-slate-500 mt-0.5">{{ $r->user->name ?? '-' }}</p>
                                <p class="text-xs text-slate-500">
                                    {{ $r->tanggal->translatedFormat('d M Y') }} · {{ substr($r->start_time, 0, 5) }}–{{ substr($r->end_time, 0, 5) }} WIB
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

                            @if($r->status === 'pending')
                                <form action="{{ route('petugas.reservasi.approve', $r->id) }}" method="POST"
                                      onsubmit="return confirm('Setujui reservasi ini?')" class="flex-1">
                                    @csrf
                                    <button type="submit" class="w-full inline-flex items-center justify-center gap-1.5 px-3 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-lg transition">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                        </svg>
                                        Setujui
                                    </button>
                                </form>
                                <button type="button" @click="modalTolak = {{ $r->id }}"
                                        class="flex-1 inline-flex items-center justify-center gap-1.5 px-3 py-2 bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold rounded-lg transition">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                    Tolak
                                </button>
                            @elseif($r->status === 'approved')
                                <button type="button" @click="modalBatal = {{ $r->id }}"
                                        class="flex-1 inline-flex items-center justify-center gap-1.5 px-3 py-2 bg-amber-500 hover:bg-amber-600 text-white text-xs font-bold rounded-lg transition">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                    Batal
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
                            <p class="font-bold text-slate-900 text-sm truncate">{{ $r->facility->nama_fasilitas ?? '-' }}</p>
                            <p class="text-[10px] text-slate-400 truncate">{{ $r->facility->lokasi ?? '-' }}</p>
                        </div>

                        {{-- Pemohon --}}
                        <div class="col-span-3 min-w-0">
                            <p class="text-xs font-semibold text-slate-700 truncate">{{ $r->user->name ?? '-' }}</p>
                            <p class="text-[10px] text-slate-400 truncate">{{ $r->user->email ?? '-' }}</p>
                        </div>

                        {{-- Jadwal --}}
                        <div class="col-span-2">
                            <p class="text-xs font-semibold text-slate-700">{{ $r->tanggal->translatedFormat('d M Y') }}</p>
                            <p class="text-[10px] text-slate-400">{{ substr($r->start_time, 0, 5) }}–{{ substr($r->end_time, 0, 5) }}</p>
                        </div>

                        {{-- Aksi (Setujui/Tolak/Batal) --}}
                        <div class="col-span-2 flex items-center justify-end gap-1.5">
                            @if($r->status === 'pending')
                                <form action="{{ route('petugas.reservasi.approve', $r->id) }}" method="POST"
                                      onsubmit="return confirm('Setujui reservasi ini?')">
                                    @csrf
                                    <button type="submit"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-lg transition">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                        </svg>
                                        Setujui
                                    </button>
                                </form>
                                <button type="button" @click="modalTolak = {{ $r->id }}"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold rounded-lg transition">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                    Tolak
                                </button>
                            @elseif($r->status === 'approved')
                                <button type="button" @click="modalBatal = {{ $r->id }}"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-amber-500 hover:bg-amber-600 text-white text-xs font-bold rounded-lg transition">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                    Batal
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
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <p class="text-sm font-bold text-slate-700">Belum ada reservasi</p>
                    <p class="text-xs text-slate-400 mt-1">Antrian akan muncul saat pengguna mengajukan reservasi</p>
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
                    Detail Reservasi
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
                            <span class="text-slate-500 font-semibold">Lokasi / Kapasitas</span>
                            <span class="text-slate-700 text-right font-bold" x-text="detail.lokasi + ' (' + detail.kapasitas + ' org)'"></span>
                        </div>
                        <div class="flex justify-between gap-3 border-b border-slate-100 pb-2.5">
                            <span class="text-slate-500 font-semibold">Pemohon</span>
                            <span class="font-bold text-slate-900 text-right" x-text="detail.pemohon"></span>
                        </div>
                        <div class="flex justify-between gap-3 border-b border-slate-100 pb-2.5">
                            <span class="text-slate-500 font-semibold">Email</span>
                            <span class="text-slate-600 text-right text-xs" x-text="detail.email"></span>
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
            </div>

            <div class="flex justify-end px-6 py-4 border-t border-slate-100 bg-slate-50">
                <button type="button" @click="modalDetail = false"
                        class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-sm rounded-xl transition">
                    Tutup
                </button>
            </div>
        </div>
    </div>

    {{-- MODAL TOLAK --}}
    @foreach($reservations as $r)
        @if($r->status === 'pending')
            <div x-show="modalTolak === {{ $r->id }}" x-cloak
                 x-effect="document.body.classList.toggle('overflow-hidden', modalTolak !== null)"
                 class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4"
                 @keydown.escape.window="modalTolak = null">
                <div @click.outside="modalTolak = null"
                     class="bg-white rounded-2xl max-w-md w-full shadow-2xl border border-slate-200">
                    <div class="px-6 py-4 border-b border-slate-100">
                        <h3 class="font-black text-slate-900">Tolak Reservasi</h3>
                        <p class="text-xs text-slate-500 mt-0.5">{{ $r->facility->nama_fasilitas ?? '-' }}</p>
                    </div>
                    <form action="{{ route('petugas.reservasi.reject', $r->id) }}" method="POST" class="p-6 space-y-4"
                          x-data="{ submitting: false }" @submit="submitting = true">
                        @csrf
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">
                                Alasan Penolakan <span class="text-rose-500">*</span>
                            </label>
                            <textarea name="alasan_tolak" rows="3" required maxlength="250"
                                      placeholder="Contoh: Jadwal bentrok dengan kegiatan resmi kampus."
                                      class="w-full p-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:border-rose-600 resize-none"></textarea>
                        </div>
                        <div class="flex justify-end gap-2 pt-2 border-t border-slate-100">
                            <button type="button" @click="modalTolak = null"
                                    class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition">
                                Batal
                            </button>
                            <button type="submit" :disabled="submitting"
                                    class="inline-flex items-center gap-1.5 px-5 py-2.5 bg-rose-600 hover:bg-rose-700 disabled:opacity-60 text-white text-xs font-bold rounded-xl transition shadow-sm">
                                <svg x-show="submitting" x-cloak class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                                </svg>
                                <span x-text="submitting ? 'Memproses...' : 'Tolak Reservasi'"></span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endif
    @endforeach

    {{-- MODAL BATAL DARURAT --}}
    @foreach($reservations as $r)
        @if($r->status === 'approved')
            <div x-show="modalBatal === {{ $r->id }}" x-cloak
                 x-effect="document.body.classList.toggle('overflow-hidden', modalBatal !== null)"
                 class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4"
                 @keydown.escape.window="modalBatal = null">
                <div @click.outside="modalBatal = null"
                     class="bg-white rounded-2xl max-w-md w-full shadow-2xl border border-slate-200">
                    <div class="px-6 py-4 border-b border-slate-100">
                        <h3 class="font-black text-slate-900">Batalkan Reservasi (Darurat)</h3>
                        <p class="text-xs text-slate-500 mt-0.5">{{ $r->facility->nama_fasilitas ?? '-' }}</p>
                    </div>
                    <div class="px-6 pt-4">
                        <div class="p-3 bg-amber-50 border border-amber-200 rounded-xl text-xs text-amber-800 font-medium">
                            Reservasi yang sudah disetujui akan dibatalkan paksa. Gunakan hanya untuk keadaan mendesak.
                        </div>
                    </div>
                    <form action="{{ route('petugas.reservasi.emergency_cancel', $r->id) }}" method="POST" class="p-6 space-y-4"
                          x-data="{ submitting: false }" @submit="submitting = true">
                        @csrf
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">
                                Alasan Pembatalan <span class="text-rose-500">*</span>
                            </label>
                            <textarea name="alasan_batal" rows="3" required maxlength="250"
                                      placeholder="Contoh: Ruangan dipakai untuk kegiatan mendadak."
                                      class="w-full p-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:border-amber-500 resize-none"></textarea>
                        </div>
                        <div class="flex justify-end gap-2 pt-2 border-t border-slate-100">
                            <button type="button" @click="modalBatal = null"
                                    class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition">
                                Batal
                            </button>
                            <button type="submit" :disabled="submitting"
                                    class="inline-flex items-center gap-1.5 px-5 py-2.5 bg-amber-500 hover:bg-amber-600 disabled:opacity-60 text-white text-xs font-bold rounded-xl transition shadow-sm">
                                <svg x-show="submitting" x-cloak class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                                </svg>
                                <span x-text="submitting ? 'Memproses...' : 'Batalkan Reservasi'"></span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endif
    @endforeach
</div>
@endsection