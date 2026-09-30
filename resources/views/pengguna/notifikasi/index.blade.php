@extends('layouts.app')

@section('content')
<div class="max-w-5xl mx-auto px-4 py-4 space-y-3">

    <!-- HEADER & TAB FILTER (Satu Baris Ringkas) -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-200 pb-2.5">
        <div>
            <h1 class="text-base font-black text-slate-900 tracking-tight">Notifikasi Saya</h1>
            <p class="text-[10px] text-slate-500">Pemberitahuan terkini seputar reservasi & aduan fasilitas.</p>
        </div>

        <!-- TAB MENU KATEGORI COMPACT -->
        <div class="flex items-center gap-1.5 text-[11px] font-bold">
            <!-- Tab: Semua -->
            <a href="{{ route('pengguna.notifikasi.index') }}" 
               class="px-2.5 py-1 rounded-lg transition flex items-center gap-1.5 {{ request('kat') == null ? 'bg-[#0f2540] text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                <span>Semua</span>
                <span class="px-1.5 py-0.2 rounded-full text-[9px] {{ request('kat') == null ? 'bg-white/20 text-white' : 'bg-slate-200 text-slate-700' }}">
                    {{ $totalCount ?? 0 }}
                </span>
            </a>

            <!-- Tab: Reservasi -->
            <a href="{{ route('pengguna.notifikasi.index', ['kat' => 'reservasi']) }}" 
               class="px-2.5 py-1 rounded-lg transition flex items-center gap-1.5 {{ request('kat') == 'reservasi' ? 'bg-[#0f2540] text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                <span>📅 Reservasi</span>
                <span class="px-1.5 py-0.2 rounded-full text-[9px] {{ request('kat') == 'reservasi' ? 'bg-white/20 text-white' : 'bg-slate-200 text-slate-700' }}">
                    {{ $reservasiCount ?? 0 }}
                </span>
            </a>

            <!-- Tab: Laporan -->
            <a href="{{ route('pengguna.notifikasi.index', ['kat' => 'laporan']) }}" 
               class="px-2.5 py-1 rounded-lg transition flex items-center gap-1.5 {{ request('kat') == 'laporan' ? 'bg-[#0f2540] text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                <span>🛠️️ Perbaikan</span>
                <span class="px-1.5 py-0.2 rounded-full text-[9px] {{ request('kat') == 'laporan' ? 'bg-white/20 text-white' : 'bg-slate-200 text-slate-700' }}">
                    {{ $laporanCount ?? 0 }}
                </span>
            </a>
        </div>
    </div>

    <!-- LIST NOTIFIKASI COMPACT (Max-height dengan scrollbar internal jika data banyak) -->
    <div class="space-y-1.5 max-h-[calc(100vh-170px)] overflow-y-auto pr-1">
        @forelse($notifications as $n)
            <div class="p-2.5 rounded-xl border transition duration-150 flex items-center justify-between gap-2.5 bg-white border-slate-200 shadow-2xs hover:border-slate-300">
                
                <div class="flex items-center gap-2.5 min-w-0 flex-1">
                    <!-- Icon Ringkas -->
                    <div class="shrink-0">
                        @if(str_contains(strtolower($n->judul), 'disetujui') || str_contains(strtolower($n->judul), 'selesai'))
                            <div class="w-6 h-6 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            </div>
                        @elseif(str_contains(strtolower($n->judul), 'ditolak') || str_contains(strtolower($n->judul), 'batal'))
                            <div class="w-6 h-6 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                            </div>
                        @else
                            <div class="w-6 h-6 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                        @endif
                    </div>

                    <!-- Isi Pesan Ringkas -->
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between gap-2">
                            <h2 class="text-xs font-extrabold text-slate-900 truncate">{{ $n->judul }}</h2>
                            <span class="text-[9px] text-slate-400 font-medium shrink-0">{{ $n->created_at->diffForHumans() }}</span>
                        </div>
                        <p class="text-[11px] text-slate-600 font-medium truncate mt-0.5">{{ $n->pesan }}</p>
                    </div>
                </div>

                <!-- Tombol Cetak Dokumen Jika Reservasi Disetujui -->
                <div class="shrink-0 flex items-center">
                    @php
                        $isApprovedReservation = (
                            (str_contains(strtolower($n->judul), 'reservasi') || str_contains(strtolower($n->pesan), 'reservasi')) && 
                            (str_contains(strtolower($n->judul), 'disetujui') || str_contains(strtolower($n->pesan), 'disetujui'))
                        );
                    @endphp

                    @if($isApprovedReservation && $n->reservation_id)
                        <a href="{{ route('pengguna.reservasi.cetak', $n->reservation_id) }}" target="_blank"
                            class="px-2.5 py-1 bg-emerald-50 hover:bg-emerald-100 text-emerald-600 border border-emerald-200 text-[11px] font-bold rounded-lg transition shrink-0">
                            Cetak Surat
                        </a>
                    @endif
                </div>

            </div>
        @empty
            <div class="text-center py-8 bg-white rounded-xl border border-slate-200">
                <svg class="w-8 h-8 text-slate-300 mx-auto mb-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.4-1.4A2 2 0 0118 14.2V11a6 6 0 10-12 0v3.2a2 2 0 01-.6 1.4L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                </svg>
                <p class="text-xs text-slate-500 font-bold">Tidak ada notifikasi pada kategori ini.</p>
            </div>
        @endforelse
    </div>

</div>
@endsection