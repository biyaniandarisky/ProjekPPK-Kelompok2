@extends('layouts.app')

@section('title', 'Status Fasilitas')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-4"
     x-data="{
        filterStatus: 'semua',
        searchQuery: '',
        modalFasilitas: null,
        matchSearch(haystack) {
            return haystack.toLowerCase().includes(this.searchQuery.toLowerCase());
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
                <h1 class="text-2xl font-black text-slate-900">Status Fasilitas</h1>
                <p class="text-xs text-slate-500">Ubah status fasilitas. Perubahan tersambung ke halaman Laporan.</p>
            </div>
        </div>
    </div>

    {{-- FILTER & SEARCH --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4 space-y-3">
        <div class="grid grid-cols-3 gap-2">
            @foreach(['semua' => 'Semua', 'aktif' => 'Aktif', 'dalam_perbaikan' => 'Perbaikan'] as $key => $label)
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
            <input type="text" x-model="searchQuery" placeholder="Cari nama fasilitas, tipe, atau lokasi..."
                   class="w-full h-10 pl-10 pr-4 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:border-blue-900 transition">
        </div>
    </div>

    {{-- DAFTAR FASILITAS --}}
    <div class="space-y-3">
        @forelse($facilities as $f)
            @php
                $statusBadge = match($f->status) {
                    'dalam_perbaikan' => ['Dalam Perbaikan', 'bg-amber-50 text-amber-700 border-amber-200'],
                    'nonaktif'        => ['Nonaktif', 'bg-rose-50 text-rose-700 border-rose-200'],
                    default           => ['Aktif', 'bg-emerald-50 text-emerald-700 border-emerald-200'],
                };
                $haystack = collect([$f->nama_fasilitas, $f->tipe, $f->lokasi])->implode(' ');
            @endphp

            <div x-show="(filterStatus === 'semua' || filterStatus === '{{ $f->status }}') && matchSearch(@js($haystack))"
                 class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4 hover:border-slate-300 hover:shadow-md transition">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                    <div class="space-y-1.5 text-sm min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <h2 class="font-black text-slate-900 truncate">{{ $f->nama_fasilitas }}</h2>
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold border uppercase tracking-wider {{ $statusBadge[1] }}">
                                {{ $statusBadge[0] }}
                            </span>
                        </div>

                        <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-slate-600">
                            <span class="flex items-center gap-1">
                                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5a1.99 1.99 0 011.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.99 1.99 0 013 12V7a4 4 0 014-4z"/>
                                </svg>
                                {{ $f->tipe }}
                            </span>
                            <span class="flex items-center gap-1">
                                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                {{ $f->lokasi }}
                            </span>
                            @if($f->laporan_terbuka_count > 0)
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg bg-blue-50 text-blue-700 border border-blue-200 text-[10px] font-bold">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01"/>
                                    </svg>
                                    {{ $f->laporan_terbuka_count }} laporan terbuka
                                </span>
                            @endif
                        </div>
                    </div>

                    <div class="flex items-center gap-2 shrink-0 self-end md:self-center">
                        @if($f->status !== 'dalam_perbaikan')
                            <form action="{{ route('petugas.fasilitas.status', $f->id) }}" method="POST"
                                  onsubmit="return confirm('Tandai {{ addslashes($f->nama_fasilitas) }} sebagai Dalam Perbaikan?')">
                                @csrf
                                <input type="hidden" name="status" value="dalam_perbaikan">
                                <button class="inline-flex items-center gap-1.5 px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white text-xs font-bold rounded-xl transition shadow-sm">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                    </svg>
                                    Perbaikan
                                </button>
                            </form>
                        @endif

                        @if($f->status === 'dalam_perbaikan')
                            <button type="button" @click="modalFasilitas = {{ $f->id }}"
                                    class="inline-flex items-center gap-1.5 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl transition shadow-sm">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                </svg>
                                Selesai
                            </button>
                        @endif

                        @if($f->status !== 'aktif')
                            <form action="{{ route('petugas.fasilitas.status', $f->id) }}" method="POST"
                                  onsubmit="return confirm('Aktifkan kembali {{ addslashes($f->nama_fasilitas) }}?')">
                                @csrf
                                <input type="hidden" name="status" value="aktif">
                                <button class="inline-flex items-center gap-1.5 px-4 py-2 bg-blue-900 hover:bg-blue-800 text-white text-xs font-bold rounded-xl transition shadow-sm">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                    </svg>
                                    Aktifkan
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="bg-white rounded-2xl border border-slate-200 py-16 text-center">
                <div class="w-16 h-16 mx-auto rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mb-3">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                </div>
                <p class="text-sm font-bold text-slate-700">Belum ada fasilitas</p>
                <p class="text-xs text-slate-400 mt-1">Fasilitas akan muncul setelah admin menambahkannya</p>
            </div>
        @endforelse
    </div>

    {{-- MODAL SELESAI --}}
    @foreach($facilities as $f)
        @if($f->status === 'dalam_perbaikan')
            <div x-show="modalFasilitas === {{ $f->id }}" x-cloak
                 x-effect="document.body.classList.toggle('overflow-hidden', modalFasilitas !== null)"
                 class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4"
                 @keydown.escape.window="modalFasilitas = null">
                <div @click.outside="modalFasilitas = null"
                     class="bg-white rounded-2xl max-w-md w-full shadow-2xl border border-slate-200">
                    <div class="px-6 py-4 border-b border-slate-100">
                        <h3 class="font-black text-slate-900">Selesaikan Perbaikan</h3>
                        <p class="text-xs text-slate-500 mt-0.5">{{ $f->nama_fasilitas }}</p>
                    </div>
                    <div class="px-6 pt-4">
                        <div class="p-3 bg-emerald-50 border border-emerald-200 rounded-xl text-xs text-emerald-800 leading-relaxed">
                            Fasilitas akan kembali <strong>Aktif</strong>.
                            @if($f->laporan_terbuka_count > 0)
                                <strong>{{ $f->laporan_terbuka_count }} laporan</strong> yang masih terbuka otomatis ditandai <strong>Selesai</strong>.
                            @else
                                Saat ini tidak ada laporan terbuka pada fasilitas ini.
                            @endif
                        </div>
                    </div>
                    <form action="{{ route('petugas.fasilitas.status', $f->id) }}" method="POST" class="p-6 space-y-4"
                          x-data="{ submitting: false }" @submit="submitting = true">
                        @csrf
                        <input type="hidden" name="status" value="selesai">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">Catatan Perbaikan (opsional)</label>
                            <input type="text" name="catatan_resolusi" maxlength="250"
                                   placeholder="Contoh: AC sudah diganti dan diuji normal."
                                   class="w-full p-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:border-emerald-600">
                            <p class="text-[10px] text-slate-400 mt-1">Catatan ini ikut tersimpan di laporan terkait.</p>
                        </div>
                        <div class="flex justify-end gap-2 pt-2 border-t border-slate-100">
                            <button type="button" @click="modalFasilitas = null"
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