@extends('layouts.app')

@section('title', 'Reservasi Saya')

@section('content')
<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-4"
     x-data="{
        filter: '{{ $activeStatus }}',
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
               class="inline-flex items-center justify-center w-10 h-10 bg-white border border-slate-200 hover:border-blue-900 text-slate-600 hover:text-blue-900 rounded-xl transition shadow-sm"
               title="Kembali ke Dashboard">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <div>
                <h1 class="text-2xl font-black text-slate-900">Reservasi Saya</h1>
                <p class="text-xs text-slate-500">Pantau status pengajuan peminjaman fasilitas Anda.</p>
            </div>
        </div>
        <a href="{{ route('pengguna.reservasi.create') }}"
           class="inline-flex items-center justify-center gap-1.5 px-5 py-2.5 bg-blue-900 hover:bg-blue-800 text-white text-sm font-bold rounded-xl shadow-sm transition shrink-0">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
            </svg>
            Ajukan Reservasi
        </a>
    </div>

    {{-- FILTER --}}
    @php
        $filters = [
            'all'       => 'Semua',
            'pending'   => 'Menunggu',
            'approved'  => 'Disetujui',
            'rejected'  => 'Ditolak',
            'cancelled' => 'Dibatalkan',
        ];
    @endphp

    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4 space-y-3">
        <div class="grid grid-cols-3 sm:grid-cols-5 gap-2">
            @foreach($filters as $key => $label)
                <button type="button" @click="filter = '{{ $key }}'"
                        :class="filter === '{{ $key }}' ? 'bg-blue-900 text-white border-blue-900 shadow-sm' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50'"
                        class="py-2.5 px-2 rounded-xl border text-xs font-bold transition text-center truncate">
                    {{ $label }} ({{ $counts[$key] }})
                </button>
            @endforeach
        </div>

        <form method="GET" action="{{ route('pengguna.reservasi.index') }}"
              class="flex flex-wrap items-center gap-2 pt-3 border-t border-slate-100">
            @if(request('status'))
                <input type="hidden" name="status" value="{{ request('status') }}">
            @endif

            <label class="text-xs font-bold text-slate-600">Filter Tanggal:</label>
            <input type="date" name="tanggal" value="{{ $searchTanggal ?? '' }}"
                   onchange="this.form.submit()"
                   class="h-9 px-3 bg-white border border-slate-200 text-slate-700 text-xs font-bold rounded-xl focus:outline-none focus:border-blue-900 transition cursor-pointer">

            @if(!empty($searchTanggal))
                <a href="{{ route('pengguna.reservasi.index') }}"
                   class="h-9 px-3 inline-flex items-center bg-amber-500 hover:bg-amber-600 text-white text-xs font-bold rounded-xl transition">
                    Reset
                </a>
            @endif
        </form>
    </div>

    {{-- DAFTAR RESERVASI --}}
    <div class="space-y-3">
        @forelse($myReservations as $r)
            @php
                $badge = match($r->status) {
                    'approved'  => ['Disetujui', 'bg-emerald-50 text-emerald-700 border-emerald-200'],
                    'rejected'  => ['Ditolak', 'bg-rose-50 text-rose-700 border-rose-200'],
                    'cancelled' => ['Dibatalkan', 'bg-slate-100 text-slate-600 border-slate-200'],
                    default     => ['Menunggu', 'bg-amber-50 text-amber-700 border-amber-200'],
                };

                $verifiedAt = '-';
                if ($r->status !== 'pending') {
                    $verifiedAt = $r->updated_at
                        ? \Carbon\Carbon::parse($r->updated_at)->translatedFormat('d M Y H:i') . ' WIB'
                        : '-';
                }

                $detailArray = [
                    'id'           => $r->id,
                    'fasilitas'    => $r->facility->nama_fasilitas ?? '-',
                    'lokasi'       => $r->facility->lokasi ?? '-',
                    'kapasitas'    => $r->facility->kapasitas ?? '-',
                    'tanggal'      => \Carbon\Carbon::parse($r->tanggal)->translatedFormat('d F Y'),
                    'waktu'        => substr($r->start_time, 0, 5) . ' - ' . substr($r->end_time, 0, 5) . ' WIB',
                    'tujuan'       => $r->tujuan,
                    'status'       => $badge[0],
                    'statusCode'   => $r->status,
                    'alasan'       => $r->alasan_tolak ?? $r->alasan_batal ?? null,
                    'diajukan'     => $r->created_at ? \Carbon\Carbon::parse($r->created_at)->translatedFormat('d M Y H:i') . ' WIB' : '-',
                    'diverifikasi' => $verifiedAt,
                    'petugas'      => $r->petugas->name ?? null,
                    'cetak_url'    => $r->status === 'approved' ? route('pengguna.reservasi.cetak', $r->id) : null,
                ];

                $tglStr = $r->tanggal instanceof \Carbon\Carbon ? $r->tanggal->format('Y-m-d') : $r->tanggal;
                $startDateTime = \Carbon\Carbon::parse($tglStr . ' ' . $r->start_time);
                $bisaDibatalkan = in_array($r->status, ['pending', 'approved'])
                    && now()->diffInMinutes($startDateTime, false) >= 120;
            @endphp

            <div x-show="filter === 'all' || filter === '{{ $r->status }}'"
                 class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4 hover:border-slate-300 hover:shadow-md transition cursor-pointer"
                 @click="buka({{ \Illuminate\Support\Js::from($detailArray) }})">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                    <div class="space-y-1.5 text-sm min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <h2 class="font-black text-slate-900 truncate">{{ $r->facility->nama_fasilitas ?? '-' }}</h2>
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold border uppercase tracking-wider {{ $badge[1] }}">
                                {{ $badge[0] }}
                            </span>
                        </div>

                        <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-slate-600">
                            <span class="font-semibold text-slate-800 flex items-center gap-1">
                                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                {{ \Carbon\Carbon::parse($r->tanggal)->format('d M Y') }}
                            </span>
                            <span class="flex items-center gap-1">
                                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                {{ substr($r->start_time, 0, 5) }}–{{ substr($r->end_time, 0, 5) }} WIB
                            </span>
                            <span class="flex items-center gap-1">
                                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                </svg>
                                {{ $r->facility->lokasi ?? '-' }}
                            </span>
                        </div>

                        <p class="text-xs text-slate-500 italic">
                            Tujuan: {{ \Illuminate\Support\Str::limit($r->tujuan, 60) }}
                        </p>
                    </div>

                    {{-- Tombol Aksi --}}
                    <div class="flex items-center gap-2 shrink-0 self-end md:self-center" @click.stop>
                        <button type="button"
                                @click="buka({{ \Illuminate\Support\Js::from($detailArray) }})"
                                class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition">
                            Detail
                        </button>

                        @if($bisaDibatalkan)
                            <form action="{{ route('pengguna.reservasi.cancel', $r->id) }}" method="POST"
                                  onsubmit="return confirm('Yakin ingin membatalkan reservasi ini?')">
                                @csrf
                                <button type="submit"
                                        class="px-4 py-2 bg-rose-50 hover:bg-rose-100 text-rose-600 border border-rose-200 text-xs font-bold rounded-xl transition">
                                    Batalkan
                                </button>
                            </form>
                        @elseif(in_array($r->status, ['pending', 'approved']))
                            <span class="text-[10px] font-bold text-slate-400 bg-slate-100 px-3 py-2 rounded-xl border border-slate-200 cursor-not-allowed"
                                  title="Pembatalan maksimal 2 jam sebelum pemakaian">
                                Batas Lewat
                            </span>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="bg-white rounded-2xl border border-slate-200 py-16 text-center">
                <div class="w-16 h-16 mx-auto rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mb-3">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                <p class="text-sm font-bold text-slate-700">Belum ada reservasi</p>
                <p class="text-xs text-slate-400 mt-1">Ajukan reservasi pertama Anda</p>
                <a href="{{ route('pengguna.reservasi.create') }}"
                   class="inline-block mt-4 px-5 py-2.5 bg-blue-900 hover:bg-blue-800 text-white text-xs font-bold rounded-xl transition">
                    Ajukan Reservasi →
                </a>
            </div>
        @endforelse

        {{-- Empty state per filter --}}
        @if($myReservations->count() > 0)
            @foreach($filters as $key => $label)
                @if($key !== 'all' && $counts[$key] === 0)
                    <div x-show="filter === '{{ $key }}'" x-cloak
                         class="bg-white rounded-2xl border border-slate-200 py-12 text-center">
                        <div class="w-14 h-14 mx-auto rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mb-3">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                            </svg>
                        </div>
                        <p class="text-sm font-bold text-slate-700">Tidak ada reservasi {{ strtolower($label) }}</p>
                        <p class="text-xs text-slate-400 mt-1">Coba ubah filter atau ajukan reservasi baru</p>
                    </div>
                @endif
            @endforeach
        @endif
    </div>

    {{-- MODAL DETAIL --}}
    <div x-show="showDetail" x-cloak
         x-effect="document.body.classList.toggle('overflow-hidden', showDetail)"
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4"
         @keydown.escape.window="showDetail = false">
        <div @click.outside="showDetail = false"
             class="bg-white rounded-2xl max-w-lg w-full max-h-[90vh] overflow-y-auto shadow-2xl border border-slate-200">
            <div class="flex justify-between items-center px-6 py-4 border-b border-slate-100 sticky top-0 bg-white z-10">
                <h3 class="font-black text-slate-900 text-sm">
                    Detail Reservasi
                    <span class="text-xs font-normal text-slate-400 ml-1" x-text="'#' + (detail?.id ?? '')"></span>
                </h3>
                <button type="button" @click="showDetail = false"
                        class="p-1.5 text-slate-400 hover:text-slate-700 hover:bg-slate-100 rounded-lg transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <div class="p-6 space-y-4">
                <template x-if="detail">
                    <div class="space-y-3 text-sm">
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
                            <div class="flex justify-between gap-3 text-xs" x-show="detail.statusCode !== 'pending'">
                                <span class="text-slate-500 font-semibold">Diproses:</span>
                                <span class="font-bold text-slate-800 text-right" x-text="detail.diverifikasi"></span>
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

            {{-- FOOTER: Cetak + Tutup --}}
            <div class="flex items-center justify-between gap-2 px-6 py-4 border-t border-slate-100 bg-slate-50">
                <div>
                    <template x-if="detail && detail.cetak_url">
                        <a :href="detail.cetak_url" target="_blank"
                           class="inline-flex items-center gap-2 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl transition shadow-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                            </svg>
                            Cetak Surat
                        </a>
                    </template>
                </div>
                <button type="button" @click="showDetail = false"
                        class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-sm rounded-xl transition">
                    Tutup
                </button>
            </div>
        </div>
    </div>
</div>
@endsection