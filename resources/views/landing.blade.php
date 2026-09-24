@extends('layouts.app')

@section('title', 'Reservasi Kampus — Pesan Fasilitas Kampus')

@section('content')
@php
    $user       = auth()->user();
    $isGuest    = !$user;
    $isPengguna = $user && $user->role === 'pengguna';

    // Tujuan tombol "Laporkan Masalah" sesuai status pengunjung
    $laporUrl = $isGuest
        ? route('login')
        : ($isPengguna
            ? route('pengguna.dashboard', ['lapor' => 1])
            : ($user->isAdmin() ? route('admin.dashboard') : route('petugas.dashboard')));
@endphp

<div x-data="jadwalModal({
        availUrl: '{{ route('fasilitas.ketersediaan', ['id' => '__ID__']) }}',
        intentUrl: '{{ route('booking.intent') }}',
        tanggal: '{{ $tanggal }}',
        today: '{{ $today }}',
        isGuest: {{ $isGuest ? 'true' : 'false' }},
        canBook: {{ ($isGuest || $isPengguna) ? 'true' : 'false' }}
     })"
     @keydown.escape.window="close()">

    {{-- ===================== HERO ===================== --}}
    <section class="relative bg-[#0f2a6b] text-white overflow-hidden">
        <img src="https://images.unsplash.com/photo-1523240795612-9a054b0db644?auto=format&fit=crop&w=1600&q=70"
             alt="" class="absolute inset-0 w-full h-full object-cover opacity-30">
        <div class="absolute inset-0 bg-gradient-to-r from-[#0b1f5c] via-[#0f2a6b]/90 to-[#0f2a6b]/50"></div>

        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-14 pb-24 sm:pt-16 sm:pb-28">
            <p class="text-[11px] font-bold tracking-wide text-emerald-400">Sarana &amp; Prasarana Kampus</p>
            <h1 class="mt-2 max-w-xl text-3xl sm:text-4xl font-extrabold leading-tight tracking-tight">
                Pesan fasilitas kampus, cepat dan tanpa antre
            </h1>
            <p class="mt-3 max-w-xl text-xs sm:text-sm text-blue-100/90 leading-relaxed">
            </p>
        </div>
    </section>

    {{-- ===================== FILTER PENCARIAN ===================== --}}
    <section class="relative z-10 max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 -mt-12">
        <form action="{{ route('landing') }}" method="GET"
              class="bg-white rounded-2xl shadow-xl border border-slate-100 p-4 sm:p-5 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-[1.4fr_1.4fr_1fr_1fr_auto] gap-3 items-end text-xs">

            <div>
                <label class="block text-[11px] font-bold text-slate-700 mb-1.5" for="f-q">Fasilitas / Ruangan</label>
                <div class="relative">
                    <svg class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.3-4.3M17 10.5a6.5 6.5 0 11-13 0 6.5 6.5 0 0113 0z"/></svg>
                    <input id="f-q" type="text" name="q" value="{{ request('q') }}" placeholder="Mis. Ruang A101, Lab..."
                           class="w-full h-10 pl-9 pr-3 bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-900/20 focus:border-blue-900">
                </div>
            </div>

            <div>
                <label class="block text-[11px] font-bold text-slate-700 mb-1.5" for="f-lokasi">Lokasi / Gedung</label>
                <select id="f-lokasi" name="lokasi"
                        class="w-full h-10 px-3 bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-900/20 focus:border-blue-900">
                    <option value="">Semua lokasi / gedung</option>
                    @foreach($lokasiList as $lok)
                        <option value="{{ $lok }}" @selected(request('lokasi') === $lok)>{{ $lok }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-[11px] font-bold text-slate-700 mb-1.5" for="f-kap">Kapasitas</label>
                <select id="f-kap" name="kapasitas"
                        class="w-full h-10 px-3 bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-900/20 focus:border-blue-900">
                    <option value="">Semua</option>
                    @foreach([10, 30, 50, 100, 200] as $k)
                        <option value="{{ $k }}" @selected((string) request('kapasitas') === (string) $k)>≥ {{ $k }} orang</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-[11px] font-bold text-slate-700 mb-1.5" for="f-tgl">Tanggal</label>
                <input id="f-tgl" type="date" name="tanggal" value="{{ $tanggal }}" min="{{ $today }}"
                       class="w-full h-10 px-3 bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-900/20 focus:border-blue-900">
            </div>

            <button type="submit"
                    class="h-10 px-6 inline-flex items-center justify-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold rounded-lg transition sm:col-span-2 lg:col-span-1 shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.3-4.3M17 10.5a6.5 6.5 0 11-13 0 6.5 6.5 0 0113 0z"/></svg>
                Cari
            </button>
        </form>
    </section>

    {{-- ===================== DAFTAR FASILITAS ===================== --}}
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-10 pb-6">
        <h2 class="text-lg font-extrabold text-slate-900 border-b border-slate-200 pb-3">Daftar Semua Fasilitas Kampus</h2>

        @if($facilities->isEmpty())
            <div class="mt-8 bg-white border border-dashed border-slate-300 rounded-2xl py-14 text-center text-sm text-slate-500">
                Tidak ada fasilitas yang cocok dengan pencarian Anda.
            </div>
        @else
        <div class="mt-5 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
            @foreach($facilities as $fac)
                @php
                    $perbaikan = $fac->status === 'dalam_perbaikan';
                    $payload = [
                        'id'          => $fac->id,
                        'nama'        => $fac->nama_fasilitas,
                        'tipe'        => $fac->tipe,
                        'lokasi'      => $fac->lokasi,
                        'kapasitas'   => $fac->kapasitas,
                        'foto'        => $fac->foto,
                        'maintenance' => $perbaikan,
                    ];
                @endphp
                <article class="bg-white rounded-xl border border-slate-200 shadow-sm hover:shadow-md transition overflow-hidden flex flex-col">
                    <div class="relative h-36 bg-gradient-to-br from-slate-200 to-slate-300">
                        @if($fac->foto)
                            <img src="{{ $fac->foto }}" alt="{{ $fac->nama_fasilitas }}" loading="lazy"
                                 class="w-full h-full object-cover {{ $perbaikan ? 'grayscale-[40%]' : '' }}">
                        @endif
                        <span class="absolute top-2 left-2 px-2 py-0.5 bg-white/95 text-slate-800 rounded text-[10px] font-bold shadow-sm">{{ $fac->tipe }}</span>
                        @if($perbaikan)
                            <span class="absolute top-2 right-2 px-2 py-0.5 bg-amber-500 text-white rounded text-[10px] font-bold shadow-sm">Perbaikan</span>
                        @endif
                    </div>

                    <div class="p-4 flex-1 flex flex-col">
                        <div class="flex items-start justify-between gap-2">
                            <h3 class="font-extrabold text-slate-900 text-[13px] leading-snug">{{ $fac->nama_fasilitas }}</h3>
                            @unless($perbaikan)
                                <span class="shrink-0 px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-600 border border-emerald-200 text-[10px] font-bold">Aktif</span>
                            @endunless
                        </div>

                        <ul class="mt-2 space-y-1 text-[11px] text-slate-500">
                            <li class="flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17.7 16.7L13.4 21a2 2 0 01-2.8 0l-4.3-4.3a8 8 0 1111.4 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                <span class="truncate">{{ $fac->lokasi }}</span>
                            </li>
                            <li class="flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a4 4 0 00-3-3.9M9 20H2v-2a4 4 0 014-4h3a4 4 0 014 4v2zM15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                <span>Kapasitas: {{ $fac->kapasitas }} orang</span>
                            </li>
                        </ul>

                        <p class="mt-2 text-[11px] text-slate-500 leading-relaxed line-clamp-2 flex-1">{{ $fac->deskripsi }}</p>

                        <button type="button" @click="openFor(@js($payload))"
                                class="mt-3 w-full h-9 inline-flex items-center justify-center gap-1.5 border border-slate-200 hover:border-blue-900 hover:bg-slate-50 text-slate-800 text-[11px] font-bold rounded-lg transition">
                            <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 7v5l3 2"/></svg>
                            Lihat Jadwal
                        </button>
                    </div>
                </article>
            @endforeach
        </div>
        @endif
    </section>

{{-- ===================== KEUNGGULAN ===================== --}}
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-4 pb-10">
        {{-- Grid diatur 3 kolom dan terpusat (justify-center) --}}
        <div class="border-t border-slate-200 pt-8 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 justify-center">
            @php
                $fitur = [
                    [
                        'Cek ketersediaan real-time', 
                        'M15.5 14h-.79l-.28-.27A6.471 6.471 0 0016 9.5 6.5 6.5 0 109.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z'
                    ],
                    [
                        'Ajukan reservasi online', 
                        'M19 4h-1V2h-2v2H8V2H6v2H5c-1.11 0-1.99.9-1.99 2L3 20c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 16H5V10h14v10zm-8.5-2.5l-3.5-3.5 1.41-1.41L10.5 15.17l5.59-5.59L17.5 11l-7 7z'
                    ],
                    [
                        'Laporkan kerusakan', 
                        'M19.43 12.98c.04-.32.07-.64.07-.98s-.03-.66-.07-.98l2.11-1.65c.19-.15.24-.42.12-.64l-2-3.46c-.12-.22-.39-.3-.61-.22l-2.49 1c-.52-.4-1.08-.73-1.69-.98l-.38-2.65C14.46 2.18 14.25 2 14 2h-4c-.25 0-.46.18-.49.42l-.38 2.65c-.61.25-1.17.59-1.69.98l-2.49-1c-.23-.09-.49 0-.61.22l-2 3.46c-.13.22-.07.49.12.64l2.11 1.65c-.04.32-.07.65-.07.98s.03.66.07.98l-2.11 1.65c-.19.15-.24.42-.12.64l2 3.46c.12.22.39.3.61.22l2.49-1c.52.4 1.08.73 1.69.98l.38 2.65c.03.24.24.42.49.42h4c.25 0 .46-.18.49-.42l.38-2.65c.61-.25 1.17-.59 1.69-.98l2.49 1c.23.09.49 0 .61-.22l2-3.46c.12-.22.07-.49-.12-.64l-2.11-1.65zM12 10.5c.83 0 1.5.67 1.5 1.5s-.67 1.5-1.5 1.5-1.5-.67-1.5-1.5.67-1.5 1.5-1.5zm1 6h-2v-2h2v2z'
                    ],
                ];
            @endphp
            @foreach($fitur as [$judul, $ikon])
                <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-4 flex items-center gap-3">
                    <div class="w-9 h-9 rounded-lg bg-blue-50 text-blue-700 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24">
                            <path d="{{ $ikon }}"/>
                        </svg>
                    </div>
                    <h3 class="font-extrabold text-[13px] text-slate-900 leading-snug">{{ $judul }}</h3>
                </div>
            @endforeach
        </div>

        {{-- Banner laporan kerusakan --}}
        <div class="mt-6 rounded-xl bg-gradient-to-r from-[#0b1f4d] to-[#13306b] text-white p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4 shadow-lg">
            <div>
                <h3 class="font-extrabold text-sm">Fasilitas Rusak atau Bermasalah?</h3>
                <p class="text-[11px] text-blue-100/90 mt-0.5 max-w-xl">Laporkan kerusakan ruangan, proyektor, atau kebersihan agar segera diperbaiki.</p>
            </div>
            <a href="{{ $laporUrl }}"
               class="shrink-0 inline-flex items-center justify-center gap-1.5 px-5 h-10 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-extrabold rounded-lg transition shadow-sm">
                Laporkan Masalah
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
            </a>
        </div>
    </section>

    {{-- ===================== POPUP: JADWAL & KETERSEDIAAN SLOT ===================== --}}
    <div x-show="open" x-cloak
         x-effect="document.body.classList.toggle('overflow-hidden', open)"
         class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-6"
         role="dialog" aria-modal="true" aria-labelledby="jadwal-title">

        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-[2px]" @click="close()"></div>

        <div class="relative w-full max-w-2xl max-h-[92vh] flex flex-col bg-white rounded-2xl shadow-2xl overflow-hidden">

            {{-- Header --}}
            <div class="flex items-center justify-between px-5 py-3.5 border-b border-slate-100 bg-slate-50/60">
                <h3 id="jadwal-title" class="font-extrabold text-sm text-slate-900">Jadwal &amp; Ketersediaan Slot Fasilitas</h3>
                <button type="button" @click="close()" class="w-7 h-7 rounded-lg text-slate-400 hover:text-slate-700 hover:bg-slate-100 flex items-center justify-center" aria-label="Tutup">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <div class="flex-1 overflow-y-auto px-5 py-4 text-xs">
                <template x-if="fac">
                    <div class="space-y-4">
                        {{-- Ringkasan fasilitas --}}
                        <div class="flex items-center gap-3 p-3 rounded-xl border border-slate-200 bg-slate-50/60">
                            <div class="w-14 h-14 rounded-lg bg-slate-200 overflow-hidden shrink-0">
                                <img x-show="fac.foto" :src="fac.foto" :alt="fac.nama" class="w-full h-full object-cover">
                            </div>
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-1.5">
                                    <span class="font-extrabold text-slate-900 text-[13px]" x-text="fac.nama"></span>
                                    <span class="px-1.5 py-0.5 rounded bg-blue-100 text-blue-700 text-[9px] font-bold" x-text="fac.tipe"></span>
                                    <span x-show="!fac.maintenance" class="px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 text-[9px] font-bold">Siap Digunakan</span>
                                    <span x-show="fac.maintenance" class="px-1.5 py-0.5 rounded bg-amber-100 text-amber-700 text-[9px] font-bold">Dalam Perbaikan</span>
                                </div>
                                <p class="mt-1 text-[11px] text-slate-500">
                                    <span x-text="fac.lokasi"></span> &nbsp;•&nbsp; Kapasitas: <strong class="text-slate-700" x-text="fac.kapasitas + ' Orang'"></strong>
                                </p>
                            </div>
                        </div>

                        {{-- MODE PERBAIKAN: slot dikosongkan, hanya tombol Tutup --}}
                        <div x-show="fac.maintenance" class="rounded-xl border border-amber-200 bg-amber-50 p-6 text-center space-y-1.5">
                            <div class="mx-auto w-10 h-10 rounded-full bg-amber-100 text-amber-600 flex items-center justify-center">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.3 4.3a1 1 0 011.4 0l1.5 1.5a4 4 0 014.5 5.3l-9 9a2 2 0 01-2.8-2.8l9-9-1.6-1.6a1 1 0 010-1.4z"/></svg>
                            </div>
                            <p class="font-extrabold text-amber-800 text-[13px]">Fasilitas sedang dalam perbaikan</p>
                            <p class="text-amber-700 text-[11px]">Slot waktu dikosongkan dan fasilitas ini belum dapat dipesan.</p>
                        </div>

                        {{-- MODE NORMAL --}}
                        <div x-show="!fac.maintenance" class="space-y-4">
                            {{-- Tanggal + ringkasan --}}
                            <div class="rounded-xl border border-slate-200 p-3">
                                <div class="flex flex-wrap items-center justify-between gap-2">
                                    <label class="flex items-center gap-2 text-[11px] font-bold text-slate-700">
                                        <svg class="w-4 h-4 text-blue-800" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3M4 11h16M5 5h14a1 1 0 011 1v14a1 1 0 01-1 1H5a1 1 0 01-1-1V6a1 1 0 011-1z"/></svg>
                                        Pilih Tanggal:
                                        <input type="date" x-model="tanggal" :min="today" @change="load()"
                                               class="h-8 px-2 border border-slate-300 rounded-lg text-[11px] font-semibold text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-900/20">
                                    </label>
                                    <div class="flex items-center gap-2 text-[11px] font-bold">
                                        <span class="flex items-center gap-1 text-emerald-600"><i class="w-1.5 h-1.5 rounded-full bg-emerald-500 inline-block"></i><span x-text="availableCount + ' Tersedia'"></span></span>
                                        <span class="text-slate-300">|</span>
                                        <span class="flex items-center gap-1 text-slate-500"><i class="w-1.5 h-1.5 rounded-full bg-slate-400 inline-block"></i><span x-text="bookedCount + ' Terisi'"></span></span>
                                    </div>
                                </div>
                                <div class="mt-2.5 h-1.5 w-full rounded-full bg-slate-200 overflow-hidden">
                                    <div class="h-full rounded-full bg-emerald-500 transition-all duration-300" :style="'width:' + availablePct + '%'"></div>
                                </div>
                            </div>

                            {{-- Grid slot --}}
                            <div>
                                <div class="flex flex-wrap items-center justify-between gap-1 mb-2">
                                    <p class="flex items-center gap-1.5 text-[11px] font-bold text-slate-700">
                                        <svg class="w-4 h-4 text-blue-800" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 7v5l3 2"/></svg>
                                        Pilih Slot Waktu (Per 30 Menit, bisa pilih banyak):
                                    </p>
                                    <div class="flex items-center gap-2 text-[10px]">
                                        <button type="button" @click="resetSelection()" x-show="hasSelection" x-cloak class="font-bold text-rose-600 hover:underline">↺ Reset Pilihan</button>
                                        <span class="text-slate-400">Klik slot untuk memilih</span>
                                    </div>
                                </div>

                                <p x-show="loading" class="py-10 text-center text-slate-400 text-[11px]">Memuat slot waktu…</p>
                                <p x-show="error" x-cloak x-text="error" class="py-6 text-center text-rose-600 text-[11px] font-semibold"></p>

                                <div x-show="!loading && !error" class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                                    <template x-for="(s, i) in slots" :key="s.start">
                                        <button type="button" @click="toggle(i)" :disabled="!s.is_available"
                                                class="text-left rounded-lg border-2 px-2.5 py-1.5 transition focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-900/40"
                                                :class="isSelected(i)
                                                    ? 'bg-blue-600 border-blue-600 text-white shadow'
                                                    : (s.is_available
                                                        ? 'bg-white border-slate-200 hover:border-blue-500 text-slate-900'
                                                        : 'bg-slate-100 border-slate-100 text-slate-400 cursor-not-allowed')">
                                            <span class="block text-[11px] font-extrabold" x-text="s.start + ' - ' + s.end"></span>
                                            <span class="block text-[9px] font-semibold"
                                                  :class="isSelected(i) ? 'text-blue-100' : (s.is_available ? 'text-emerald-600' : 'text-slate-400')"
                                                  x-text="isSelected(i) ? 'Dipilih' : (s.is_booked ? 'Terisi' : (s.is_past ? 'Lewat' : 'Tersedia'))"></span>
                                        </button>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            {{-- Footer: hanya "Tutup" dan "Pesan" --}}
            <div class="px-5 py-3.5 border-t border-slate-100 bg-white space-y-2">
                <p x-show="actionError" x-cloak x-text="actionError" class="text-[11px] font-semibold text-rose-600"></p>
                <p x-show="isGuest && fac && !fac.maintenance && !hasSelection" x-cloak class="text-[10px] text-slate-400">
                    Anda masuk sebagai <strong>Pengunjung</strong>. Pilih slot lalu klik Pesan untuk lanjut ke halaman login atau daftar akun.
                </p>
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <span x-show="hasSelection" x-cloak
                          class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-blue-50 border border-blue-100 text-blue-800 text-[11px] font-bold"
                          x-text="selectionSummary"></span>
                    <div class="flex items-center gap-2 ml-auto">
                        <button type="button" @click="close()"
                                class="h-9 px-4 rounded-lg text-[11px] font-bold text-slate-600 hover:bg-slate-100 transition">Tutup</button>
                        <button type="button" @click="pesan()"
                                x-show="fac && !fac.maintenance" x-cloak
                                :disabled="!hasSelection || submitting || !canBook"
                                class="h-9 px-6 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-[11px] font-extrabold shadow transition disabled:opacity-40 disabled:cursor-not-allowed disabled:hover:bg-blue-600">
                            <span x-text="submitting ? 'Memproses…' : 'Pesan'"></span>
                        </button>
                    </div>
                </div>
                <p x-show="!canBook && fac && !fac.maintenance" x-cloak class="text-[10px] text-slate-400 text-right">Hanya akun Pengguna (Mahasiswa/Dosen/Staf) yang dapat memesan.</p>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('jadwalModal', (cfg) => ({
        // --- state ---
        open: false,
        fac: null,
        tanggal: cfg.tanggal,
        today: cfg.today,
        slots: [],
        from: null,          // indeks slot awal terpilih
        to: null,            // indeks slot akhir terpilih (inklusif)
        loading: false,
        error: '',
        actionError: '',
        submitting: false,
        isGuest: cfg.isGuest,
        canBook: cfg.canBook,
        _req: 0,

        // --- buka / tutup ---
        openFor(facility) {
            this.fac = facility;
            this.tanggal = cfg.tanggal;
            this.slots = [];
            this.error = '';
            this.actionError = '';
            this.submitting = false;
            this.resetSelection();
            this.open = true;
            if (!facility.maintenance) this.load();
        },
        close() {
            this.open = false;
        },

        // --- ambil ketersediaan dari server ---
        async load() {
            if (!this.fac || this.fac.maintenance) return;
            const token = ++this._req;
            this.loading = true;
            this.error = '';
            this.actionError = '';
            this.resetSelection();
            try {
                const url = cfg.availUrl.replace('__ID__', this.fac.id) + '?tanggal=' + encodeURIComponent(this.tanggal);
                const res = await fetch(url, { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' });
                if (!res.ok) throw new Error('HTTP ' + res.status);
                const data = await res.json();
                if (token !== this._req) return; // abaikan respons usang
                this.tanggal = data.tanggal;
                if (data.is_maintenance) {
                    this.fac.maintenance = true;
                    this.slots = [];
                } else {
                    this.slots = data.slots;
                }
            } catch (e) {
                if (token === this._req) this.error = 'Gagal memuat jadwal. Silakan coba lagi.';
            } finally {
                if (token === this._req) this.loading = false;
            }
        },

        // --- ringkasan ---
        get availableCount() { return this.slots.filter(s => s.is_available).length; },
        get bookedCount()    { return this.slots.filter(s => s.is_booked).length; },
        get availablePct()   { return this.slots.length ? Math.round(this.availableCount / this.slots.length * 100) : 0; },

        // --- pilihan slot (harus berurutan / kontinu) ---
        get hasSelection() { return this.from !== null; },
        get count()        { return this.hasSelection ? this.to - this.from + 1 : 0; },
        isSelected(i)      { return this.hasSelection && i >= this.from && i <= this.to; },
        resetSelection()   { this.from = null; this.to = null; },

        rangeAvailable(a, b) {
            for (let k = Math.min(a, b); k <= Math.max(a, b); k++) {
                if (!this.slots[k] || !this.slots[k].is_available) return false;
            }
            return true;
        },
        toggle(i) {
            this.actionError = '';
            if (!this.slots[i] || !this.slots[i].is_available) return;

            if (!this.hasSelection) { this.from = this.to = i; return; }

            if (this.isSelected(i)) {
                if (this.count === 1) { this.resetSelection(); return; }   // batal pilih
                if (i === this.from)  { this.from = i + 1; return; }       // kecilkan dari awal
                if (i === this.to)    { this.to = i - 1; return; }         // kecilkan dari akhir
                this.from = this.to = i;                                   // klik bagian tengah: mulai ulang
                return;
            }

            // Perluas rentang jika semua slot di antaranya tersedia
            if (i > this.to && this.rangeAvailable(this.to + 1, i))   { this.to = i; return; }
            if (i < this.from && this.rangeAvailable(i, this.from - 1)) { this.from = i; return; }

            // Tidak bisa disambung (ada slot terisi di antaranya): mulai pilihan baru
            this.from = this.to = i;
        },

        get startTime() { return this.hasSelection ? this.slots[this.from].start : ''; },
        get endTime()   { return this.hasSelection ? this.slots[this.to].end : ''; },
        get durasi() {
            const m = this.count * 30;
            if (m < 60) return m + ' Menit';
            const h = m / 60;
            return (Number.isInteger(h) ? h : String(h).replace('.', ',')) + ' Jam';
        },
        get selectionSummary() {
            return this.count + ' Slot Terpilih: ' + this.startTime + ' – ' + this.endTime + ' WIB (' + this.durasi + ')';
        },

        // --- klik "Pesan" ---
        async pesan() {
            if (!this.hasSelection || this.submitting) return;
            this.submitting = true;
            this.actionError = '';
            try {
                const res = await fetch(cfg.intentUrl, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({
                        facility_id: this.fac.id,
                        tanggal: this.tanggal,
                        start_time: this.startTime,
                        end_time: this.endTime,
                    }),
                });
                const data = await res.json().catch(() => ({}));
                if (!res.ok) {
                    this.actionError = data.message
                        || (data.errors ? Object.values(data.errors).flat()[0] : 'Gagal memproses pemesanan.');
                    this.submitting = false;
                    return;
                }
                // Pengunjung -> halaman login/daftar; Pengguna -> dashboard (form terisi otomatis)
                window.location.href = data.redirect;
            } catch (e) {
                this.actionError = 'Terjadi kesalahan jaringan. Silakan coba lagi.';
                this.submitting = false;
            }
        },
    }));
});
</script>
@endpush