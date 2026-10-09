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
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
            @foreach(['semua' => 'Semua', 'aktif' => 'Aktif', 'dalam_perbaikan' => 'Perbaikan', 'selesai' => 'Selesai'] as $key => $label)
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

    {{-- DAFTAR FASILITAS (TABEL) --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">

        <div class="hidden lg:grid grid-cols-12 gap-3 px-5 py-3 bg-slate-50 border-b border-slate-200 text-[10px] font-bold text-slate-500 uppercase tracking-wider">
            <div class="col-span-1">ID</div>
            <div class="col-span-4">Fasilitas</div>
            <div class="col-span-2">Tipe</div>
            <div class="col-span-2">Status</div>
            <div class="col-span-3 text-right">Aksi</div>
        </div>

        <div class="divide-y divide-slate-100">
            @forelse($facilities as $f)
                @php
                    $statusBadge = match($f->status) {
                        'dalam_perbaikan' => ['Dalam Perbaikan', 'bg-amber-50 text-amber-700 border-amber-200'],
                        'selesai'         => ['Selesai', 'bg-blue-50 text-blue-700 border-blue-200'],
                        'nonaktif'        => ['Nonaktif', 'bg-rose-50 text-rose-700 border-rose-200'],
                        default           => ['Aktif', 'bg-emerald-50 text-emerald-700 border-emerald-200'],
                    };
                    $haystack = collect(['F' . str_pad($f->id, 3, '0', STR_PAD_LEFT), $f->nama_fasilitas, $f->tipe, $f->lokasi])->implode(' ');
                @endphp

                <div x-show="(filterStatus === 'semua' || filterStatus === '{{ $f->status }}') && matchSearch(@js($haystack))"
                     class="lg:grid lg:grid-cols-12 lg:gap-3 lg:items-center px-5 py-3.5 hover:bg-slate-50 transition">

                    {{-- Mobile --}}
                    <div class="lg:hidden space-y-3">
                        <div class="flex items-start justify-between gap-2">
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <p class="font-bold text-slate-900 text-sm truncate">{{ $f->nama_fasilitas }}</p>
                                    <span class="text-[10px] text-slate-400 font-mono">F{{ str_pad($f->id, 3, '0', STR_PAD_LEFT) }}</span>
                                </div>
                                <p class="text-xs text-slate-500 mt-0.5">{{ $f->tipe }} · {{ $f->lokasi }}</p>
                                @if($f->laporan_terbuka_count > 0)
                                    <p class="text-[10px] font-bold text-blue-700 mt-1">{{ $f->laporan_terbuka_count }} laporan terbuka</p>
                                @endif
                            </div>
                            <span class="shrink-0 px-2.5 py-1 rounded-full text-[10px] font-bold border uppercase tracking-wider {{ $statusBadge[1] }}">{{ $statusBadge[0] }}</span>
                        </div>
                        <div class="flex items-center gap-2 pt-3 border-t border-slate-100 [&>*]:flex-1 [&_button]:w-full [&_button]:justify-center">
                            {{-- Alur: Aktif -> Perbaikan -> Selesai -> Aktifkan --}}
                            @if($f->status === 'aktif')
                                <form action="{{ route('petugas.fasilitas.status', $f->id) }}" method="POST"
                                      onsubmit="return confirm('Tandai {{ addslashes($f->nama_fasilitas) }} sebagai Dalam Perbaikan?')">
                                    @csrf
                                    <input type="hidden" name="status" value="dalam_perbaikan">
                                    <button class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-amber-500 hover:bg-amber-600 text-white text-xs font-bold rounded-lg transition">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                        Perbaikan
                                    </button>
                                </form>
                            @elseif($f->status === 'dalam_perbaikan')
                                <button type="button" @click="modalFasilitas = {{ $f->id }}"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-lg transition">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                    Selesai
                                </button>
                            @else
                                {{-- Status "selesai" (atau nonaktif lama): baru bisa diaktifkan --}}
                                <form action="{{ route('petugas.fasilitas.status', $f->id) }}" method="POST"
                                      onsubmit="return confirm('Aktifkan {{ addslashes($f->nama_fasilitas) }} agar bisa dipesan lagi?')">
                                    @csrf
                                    <input type="hidden" name="status" value="aktif">
                                    <button class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-blue-900 hover:bg-blue-800 text-white text-xs font-bold rounded-lg transition">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                        Aktifkan
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>

                    {{-- Desktop --}}
                    <div class="hidden lg:contents">
                        <div class="col-span-1"><span class="text-xs font-mono text-slate-500">F{{ str_pad($f->id, 3, '0', STR_PAD_LEFT) }}</span></div>
                        <div class="col-span-4 min-w-0">
                            <p class="font-bold text-slate-900 text-sm truncate">{{ $f->nama_fasilitas }}</p>
                            <p class="text-[10px] text-slate-400 truncate">
                                {{ $f->lokasi }}@if($f->laporan_terbuka_count > 0) · <span class="font-bold text-blue-700">{{ $f->laporan_terbuka_count }} laporan terbuka</span>@endif
                            </p>
                        </div>
                        <div class="col-span-2">
                            <p class="text-xs font-semibold text-slate-700 truncate">{{ $f->tipe }}</p>
                        </div>
                        <div class="col-span-2">
                            <span class="inline-block px-2.5 py-1 rounded-full text-[10px] font-bold border uppercase tracking-wider {{ $statusBadge[1] }}">{{ $statusBadge[0] }}</span>
                        </div>
                        <div class="col-span-3 flex items-center justify-end gap-1.5">
                            {{-- Alur: Aktif -> Perbaikan -> Selesai -> Aktifkan --}}
                            @if($f->status === 'aktif')
                                <form action="{{ route('petugas.fasilitas.status', $f->id) }}" method="POST"
                                      onsubmit="return confirm('Tandai {{ addslashes($f->nama_fasilitas) }} sebagai Dalam Perbaikan?')">
                                    @csrf
                                    <input type="hidden" name="status" value="dalam_perbaikan">
                                    <button class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-amber-500 hover:bg-amber-600 text-white text-xs font-bold rounded-lg transition">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                        Perbaikan
                                    </button>
                                </form>
                            @elseif($f->status === 'dalam_perbaikan')
                                <button type="button" @click="modalFasilitas = {{ $f->id }}"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-lg transition">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                    Selesai
                                </button>
                            @else
                                {{-- Status "selesai" (atau nonaktif lama): baru bisa diaktifkan --}}
                                <form action="{{ route('petugas.fasilitas.status', $f->id) }}" method="POST"
                                      onsubmit="return confirm('Aktifkan {{ addslashes($f->nama_fasilitas) }} agar bisa dipesan lagi?')">
                                    @csrf
                                    <input type="hidden" name="status" value="aktif">
                                    <button class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-blue-900 hover:bg-blue-800 text-white text-xs font-bold rounded-lg transition">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                        Aktifkan
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="px-5 py-16 text-center">
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
                            Fasilitas akan ditandai <strong>Selesai</strong>. Setelah itu tombol <strong>Aktifkan</strong> muncul agar fasilitas bisa dipesan lagi.
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