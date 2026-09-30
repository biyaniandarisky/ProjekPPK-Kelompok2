@extends('layouts.app')

@section('content')
<div class="max-w-5xl mx-auto px-4 py-3 space-y-2.5"
    x-data="{
        filter: '{{ $activeStatus }}',
        detail: null,
        showDetail: false,
        buka(el) { 
            this.detail = JSON.parse(el.getAttribute('data-detail')); 
            this.showDetail = true; 
        }
    }">

    <!-- Top Navigation & Header Row (Satu Baris Compact) -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 border-b border-slate-200 pb-2">
        <div class="flex items-center gap-3">
            <a href="{{ route('pengguna.dashboard') }}" class="inline-flex items-center justify-center w-7 h-7 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-lg transition" title="Kembali ke Dashboard">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
            </a>
            <div>
                <h1 class="text-base font-black text-slate-900 tracking-tight leading-tight">Reservasi Saya</h1>
                <p class="text-[10px] text-slate-500">Pantau status pengajuan peminjaman fasilitas kamu.</p>
            </div>
        </div>

        <a href="{{ route('pengguna.reservasi.create') }}"
            class="inline-flex items-center justify-center gap-1.5 px-3 py-1.5 bg-[#0f2540] hover:bg-[#0b1c31] text-white text-[11px] font-bold rounded-lg shadow-xs transition shrink-0">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path>
            </svg>
            Ajukan Reservasi Baru
        </a>
    </div>

    <!-- FORM FILTER STATUS & PENCARIAN TANGGAL OTOMATIS -->
    <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-2">
        
        <!-- Filter Status Compact (Kiri) -->
        @php
            $filters = [
                'all'       => 'Semua Status',
                'pending'   => 'Menunggu',
                'approved'  => 'Disetujui',
                'rejected'  => 'Ditolak',
                'cancelled' => 'Dibatalkan',
            ];
        @endphp
        <div class="grid grid-cols-2 sm:grid-cols-5 gap-1.5 flex-1">
            @foreach($filters as $key => $label)
                <button type="button" @click="filter = '{{ $key }}'"
                    :class="filter === '{{ $key }}' ? 'bg-[#0f2540] text-white border-[#0f2540] shadow-xs' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50'"
                    class="py-1.5 px-2 rounded-lg border text-[11px] font-extrabold transition text-center truncate">
                    {{ $label }} ({{ $counts[$key] }})
                </button>
            @endforeach
        </div>

        <!-- Input Pencarian Tanggal (Dengan Label & Tombol Reset Berwarna) -->
        <form method="GET" action="{{ route('pengguna.reservasi.index') }}" class="flex items-center gap-1.5 shrink-0" id="formFilterTanggal">
            @if(request('status'))
                <input type="hidden" name="status" value="{{ request('status') }}">
            @endif
            
            <span class="text-[11px] font-bold text-slate-600">🔎 Tanggal:</span>

            <input type="date" name="tanggal" value="{{ $searchTanggal ?? '' }}"
                onchange="document.getElementById('formFilterTanggal').submit()"
                class="bg-white border border-slate-200 text-slate-700 text-[11px] font-bold rounded-lg px-2.5 py-1.5 focus:outline-none focus:border-blue-500 transition cursor-pointer"
                title="Pilih tanggal untuk langsung memfilter">

            @if(!empty($searchTanggal))
                <a href="{{ route('pengguna.reservasi.index') }}" class="px-2.5 py-1.5 bg-amber-500 hover:bg-amber-600 text-white text-[11px] font-bold rounded-lg transition shadow-xs" title="Reset Filter Tanggal">
                    Reset
                </a>
            @endif
        </form>

    </div>

    <!-- Daftar Reservasi Compact (Max Height Scroll) -->
    <div class="space-y-2 max-h-[calc(100vh-175px)] overflow-y-auto pr-1">
        @forelse($myReservations as $r)
            @php
                $badge = match($r->status) {
                    'approved'  => ['Disetujui', 'bg-emerald-100 text-emerald-800 border-emerald-200'],
                    'rejected'  => ['Ditolak', 'bg-rose-100 text-rose-800 border-rose-200'],
                    'cancelled' => ['Dibatalkan', 'bg-slate-200 text-slate-700 border-slate-300'],
                    default     => ['Menunggu Persetujuan', 'bg-amber-100 text-amber-800 border-amber-200'],
                };

                // Menentukan Waktu Verifikasi/Pembaruan Status oleh Petugas
                $verifiedAt = '-';
                if ($r->status !== 'pending') {
                    $verifiedAt = $r->updated_at ? \Carbon\Carbon::parse($r->updated_at)->translatedFormat('d M Y H:i') . ' WIB' : '-';
                }

                $detailArray = [
                    'id'           => $r->id,
                    'fasilitas'    => $r->facility->nama_fasilitas ?? '-',
                    'lokasi'       => $r->facility->lokasi ?? '-',
                    'kapasitas'    => $r->facility->kapasitas ?? '-',
                    'tanggal'      => \Carbon\Carbon::parse($r->tanggal)->translatedFormat('d F Y'),
                    'waktu'        => \Carbon\Carbon::parse($r->start_time)->format('H:i') . ' - ' . \Carbon\Carbon::parse($r->end_time)->format('H:i') . ' WIB',
                    'tujuan'       => $r->tujuan,
                    'status'       => $badge[0],
                    'statusCode'   => $r->status,
                    'alasan'       => $r->alasan_tolak ?? $r->alasan_batal ?? null,
                    'diajukan'     => $r->created_at ? \Carbon\Carbon::parse($r->created_at)->translatedFormat('d M Y H:i') . ' WIB' : '-',
                    'diverifikasi' => $verifiedAt,
                    'petugas'      => $r->petugas->name ?? $r->petugas->nama ?? null,
                ];

                // --- HITUNG LOGIKA BATAS PEMBATALAN (H-1 JAM / 60 MENIT) ---
                $tglStr = $r->tanggal instanceof \Carbon\Carbon ? $r->tanggal->format('Y-m-d') : $r->tanggal;
                $startDateTime = \Carbon\Carbon::parse($tglStr . ' ' . $r->start_time);
                $bisaDibatalkan = in_array($r->status, ['pending', 'approved']) && now()->diffInMinutes($startDateTime, false) >= 60;
            @endphp

            <div x-show="filter === 'all' || filter === '{{ $r->status }}'"
                class="bg-white rounded-xl border border-slate-200 shadow-2xs p-3 hover:border-slate-300 transition">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-2">
                    <div class="space-y-1 text-xs min-w-0 flex-1">
                        <div class="flex items-center gap-2">
                            <h2 class="text-xs font-black text-slate-900 truncate">{{ $r->facility->nama_fasilitas ?? '-' }}</h2>
                            <span class="px-2 py-0.5 rounded-full text-[9px] font-black border uppercase tracking-wider {{ $badge[1] }}">
                                {{ $badge[0] }}
                            </span>
                        </div>

                        <div class="flex flex-wrap items-center gap-x-4 gap-y-0.5 text-[11px] text-slate-600 font-medium">
                            <span class="font-bold text-slate-800">📅 {{ \Carbon\Carbon::parse($r->tanggal)->format('d M Y') }}</span>
                            <span>⏰ {{ \Carbon\Carbon::parse($r->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($r->end_time)->format('H:i') }} WIB</span>
                            <span>📍 {{ $r->facility->lokasi ?? '-' }}</span>
                        </div>

                        <p class="text-[10px] text-slate-500 truncate italic">
                            Tujuan: {{ \Illuminate\Support\Str::limit($r->tujuan, 50) }}
                        </p>
                    </div>

                    <!-- Tombol Aksi Ringkas -->
                    <div class="flex items-center gap-1.5 shrink-0 self-end md:self-center">
                        <!-- Detail -->
                        <button type="button" @click="buka($el)" data-detail="{{ json_encode($detailArray) }}"
                            class="px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 text-[11px] font-bold rounded-lg transition">
                            Detail
                        </button>

                        <!-- TOMBOL CETAK SURAT -->
                        @if($r->status === 'approved')
                            <a href="{{ route('pengguna.reservasi.cetak', $r->id) }}" target="_blank"
                                class="px-2.5 py-1 bg-emerald-50 hover:bg-emerald-100 text-emerald-600 border border-emerald-200 text-[11px] font-bold rounded-lg transition">
                                Cetak Surat
                            </a>
                        @endif

                        <!-- LOGIKA TOMBOL BATAL -->
                        @if($bisaDibatalkan)
                            <form action="{{ route('pengguna.reservasi.cancel', $r->id) }}" method="POST"
                                onsubmit="return confirm('Apakah Anda yakin ingin membatalkan reservasi ini?')">
                                @csrf
                                <button type="submit" class="px-2.5 py-1 bg-rose-50 hover:bg-rose-100 text-rose-600 border border-rose-200 text-[11px] font-bold rounded-lg transition">
                                    Batalkan
                                </button>
                            </form>
                        @elseif(in_array($r->status, ['pending', 'approved']))
                            <span class="text-[9px] font-bold text-slate-400 bg-slate-100 px-2 py-1 rounded-lg border border-slate-200 cursor-not-allowed" title="Pembatalan maksimal H-1 jam sebelum pemakaian">
                                Batas Batal Lewat
                            </span>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="bg-white rounded-xl border border-slate-200 py-8 text-center text-xs text-slate-400 font-medium">
                Belum ada riwayat reservasi.
            </div>
        @endforelse

        <!-- Pesan Kosong Saat Filter Terpilih Tidak Punya Data -->
        @if($myReservations->count() > 0)
            @foreach($filters as $key => $label)
                @if($key !== 'all' && $counts[$key] === 0)
                    <div x-show="filter === '{{ $key }}'" x-cloak
                        class="bg-white rounded-xl border border-slate-200 py-8 text-center text-xs text-slate-400 font-medium">
                        Tidak ada reservasi dengan status <strong>{{ $label }}</strong>.
                    </div>
                @endif
            @endforeach
        @endif
    </div>

    <!-- Modal Detail Reservasi -->
    <div x-show="showDetail" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div @click.outside="showDetail = false" class="bg-white rounded-2xl max-w-md w-full p-5 shadow-2xl border border-slate-100 space-y-3">
            
            <div class="flex justify-between items-center border-b border-slate-100 pb-2.5">
                <h3 class="font-black text-slate-900 text-sm flex items-center gap-1.5">
                    <span>Detail Reservasi</span>
                    <span class="text-xs font-normal text-slate-400" x-text="'#' + detail?.id"></span>
                </h3>
                <button type="button" @click="showDetail = false" class="text-slate-400 hover:text-slate-600 text-lg font-bold leading-none">&times;</button>
            </div>

            <template x-if="detail">
                <div class="space-y-2 text-xs">
                    <div class="flex justify-between gap-2 border-b border-slate-50 pb-1.5">
                        <span class="font-semibold text-slate-500">Fasilitas</span>
                        <span class="font-extrabold text-slate-900 text-right" x-text="detail.fasilitas"></span>
                    </div>

                    <div class="flex justify-between gap-2 border-b border-slate-50 pb-1.5">
                        <span class="font-semibold text-slate-500">Lokasi / Kapasitas</span>
                        <span class="text-slate-700 text-right font-medium" x-text="detail.lokasi + ' (' + detail.kapasitas + ' Org)'"></span>
                    </div>

                    <div class="flex justify-between gap-2 border-b border-slate-50 pb-1.5">
                        <span class="font-semibold text-slate-500">Tanggal Pelaksanaan</span>
                        <span class="text-slate-800 font-bold text-right" x-text="detail.tanggal"></span>
                    </div>

                    <div class="flex justify-between gap-2 border-b border-slate-50 pb-1.5">
                        <span class="font-semibold text-slate-500">Jam Sewa Slot</span>
                        <span class="text-slate-800 font-bold text-right" x-text="detail.waktu"></span>
                    </div>

                    <div class="flex justify-between gap-2 border-b border-slate-50 pb-1.5">
                        <span class="font-semibold text-slate-500">Status Saat Ini</span>
                        <span class="font-black text-right uppercase tracking-wider text-[10px] px-2 py-0.5 rounded-full"
                            :class="{
                                'bg-emerald-100 text-emerald-800': detail.statusCode === 'approved',
                                'bg-rose-100 text-rose-800': detail.statusCode === 'rejected',
                                'bg-slate-200 text-slate-700': detail.statusCode === 'cancelled',
                                'bg-amber-100 text-amber-800': detail.statusCode === 'pending'
                            }"
                            x-text="detail.status">
                        </span>
                    </div>

                    <!-- TIMELINE INFORMASI WAKTU -->
                    <div class="bg-slate-50 p-2.5 rounded-xl space-y-1.5 border border-slate-100 mt-2">
                        <div class="flex justify-between gap-2 text-[11px]">
                            <span class="text-slate-500 font-medium">Waktu Pengajuan:</span>
                            <span class="font-bold text-slate-700" x-text="detail.diajukan"></span>
                        </div>

                        <div class="flex justify-between gap-2 text-[11px]" x-show="detail.statusCode !== 'pending'">
                            <span class="text-slate-500 font-medium" x-text="detail.statusCode === 'approved' ? 'Waktu Disetujui:' : (detail.statusCode === 'rejected' ? 'Waktu Ditolak:' : 'Waktu Dibatalkan:')"></span>
                            <span class="font-bold text-slate-800" x-text="detail.diverifikasi"></span>
                        </div>

                        <div class="flex justify-between gap-2 text-[11px]" x-show="detail.petugas">
                            <span class="text-slate-500 font-medium">Petugas Pemroses:</span>
                            <span class="font-bold text-slate-800" x-text="detail.petugas"></span>
                        </div>
                    </div>

                    <!-- Tujuan Kegiatan -->
                    <div class="pt-1">
                        <p class="font-bold text-slate-600 mb-0.5 text-[11px]">Tujuan Kegiatan:</p>
                        <p class="text-slate-800 bg-slate-50 p-2 rounded-lg border border-slate-100 leading-relaxed text-[11px]" x-text="detail.tujuan"></p>
                    </div>

                    <!-- Catatan Petugas (Alasan Ditolak / Dibatalkan) -->
                    <div x-show="detail.alasan" class="p-2.5 bg-rose-50 border border-rose-200 rounded-xl space-y-0.5">
                        <p class="font-extrabold text-rose-800 text-[11px]">Catatan Petugas / Alasan:</p>
                        <p class="text-rose-900 text-[11px] leading-relaxed" x-text="detail.alasan"></p>
                    </div>
                </div>
            </template>

            <div class="flex justify-end pt-2 border-t border-slate-100">
                <button type="button" @click="showDetail = false" class="bg-slate-100 hover:bg-slate-200 text-slate-700 font-extrabold px-4 py-1.5 rounded-xl text-xs transition">
                    Tutup
                </button>
            </div>
        </div>
    </div>

</div>
@endsection