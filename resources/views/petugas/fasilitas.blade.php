@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
    <div class="flex items-center gap-2">
        <a href="{{ route('petugas.dashboard') }}" class="text-xs font-bold text-blue-900 hover:underline flex items-center gap-1">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Kembali ke Panel Petugas
        </a>
    </div>

    <div>
        <h1 class="text-2xl font-black text-slate-900">Fasilitas</h1>
        <p class="text-sm text-slate-500">
            Ubah status fasilitas di sini. Perubahan status otomatis tersambung ke halaman Laporan:
            menandai <b>Dalam Perbaikan</b> membuat laporan yang masih Baru berubah jadi Diproses, sedangkan
            <b>Selesai</b> menutup semua laporan terbuka dan mengembalikan fasilitas ke Aktif.
        </p>
    </div>

    <div class="bg-white rounded-2xl p-6 shadow border border-slate-100"
        x-data="{
            filterStatus: 'semua',
            searchQuery: '',
            modalFasilitas: null,
            matchSearch(haystack) {
                return haystack.toLowerCase().includes(this.searchQuery.toLowerCase());
            }
        }">

        <!-- Filter Status -->
        <div class="flex flex-wrap gap-2 mb-4">
            @foreach([
                'semua' => 'Semua Status',
                'aktif' => 'Aktif',
                'dalam_perbaikan' => 'Dalam Perbaikan',
            ] as $key => $label)
                <button @click="filterStatus = '{{ $key }}'"
                    :class="filterStatus === '{{ $key }}' ? 'bg-blue-700 text-white shadow' : 'bg-white text-slate-600 hover:bg-slate-100'"
                    class="px-4 py-2 text-xs font-bold rounded-full border border-slate-200 transition">
                    {{ $label }}
                </button>
            @endforeach
        </div>

        <!-- Search Bar -->
        <div class="relative mb-4">
            <svg class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 10a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <input type="text" x-model="searchQuery" placeholder="Cari nama fasilitas, tipe, atau lokasi..."
                class="w-full pl-10 pr-4 py-3 bg-white border border-slate-200 rounded-2xl text-xs focus:outline-none focus:ring-2 focus:ring-blue-200">
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-xs">
                <thead>
                    <tr class="text-left text-slate-500 border-b text-sm font-bold">
                        <th class="py-2 pr-4">Fasilitas</th>
                        <th class="py-2 pr-4">Tipe</th>
                        <th class="py-2 pr-4">Lokasi</th>
                        <th class="py-2 pr-4">Laporan Terbuka</th>
                        <th class="py-2 pr-4">Status Fasilitas</th>
                        <th class="py-2 pr-4">Ubah Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($facilities as $f)
                        @php
                            $statusBadge = match($f->status) {
                                'dalam_perbaikan' => 'bg-amber-100 text-amber-700',
                                'nonaktif' => 'bg-rose-100 text-rose-700',
                                default => 'bg-emerald-100 text-emerald-700',
                            };
                            $searchHaystack = collect([
                                $f->nama_fasilitas,
                                $f->tipe,
                                $f->lokasi,
                            ])->implode(' ');
                        @endphp
                        <tr class="border-b border-slate-100 align-top"
                            x-show="(filterStatus === 'semua' || filterStatus === '{{ $f->status }}') && matchSearch('{{ addslashes($searchHaystack) }}')"
                            x-cloak>
                            <td class="py-3 pr-4 font-bold text-slate-800">{{ $f->nama_fasilitas }}</td>
                            <td class="py-3 pr-4 text-slate-600">{{ $f->tipe }}</td>
                            <td class="py-3 pr-4 text-slate-600">{{ $f->lokasi }}</td>
                            <td class="py-3 pr-4">
                                @if($f->laporan_terbuka_count > 0)
                                    <a href="{{ route('petugas.laporan.index') }}"
                                       class="px-2 py-0.5 rounded text-[10px] font-bold bg-blue-100 text-blue-700 hover:bg-blue-200 whitespace-nowrap">
                                        {{ $f->laporan_terbuka_count }} laporan
                                    </a>
                                @else
                                    <span class="text-slate-400">Tidak ada</span>
                                @endif
                            </td>
                            <td class="py-3 pr-4">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold whitespace-nowrap {{ $statusBadge }}">
                                    {{ ucfirst(str_replace('_', ' ', $f->status)) }}
                                </span>
                            </td>
                            <td class="py-3 pr-4">
                                <div class="flex flex-wrap gap-2">
                                    @if($f->status !== 'dalam_perbaikan')
                                        <form action="{{ route('petugas.fasilitas.status', $f->id) }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="status" value="dalam_perbaikan">
                                            <button class="px-3 py-1.5 bg-amber-500 hover:bg-amber-600 text-white rounded-lg font-bold whitespace-nowrap">
                                                Dalam Perbaikan
                                            </button>
                                        </form>
                                    @endif

                                    @if($f->status === 'dalam_perbaikan')
                                        <button type="button"
                                            @click="modalFasilitas = {{ $f->id }}"
                                            class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg font-bold whitespace-nowrap">
                                            Selesai
                                        </button>
                                    @endif

                                    @if($f->status !== 'aktif')
                                        <form action="{{ route('petugas.fasilitas.status', $f->id) }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="status" value="aktif">
                                            <button class="px-3 py-1.5 bg-blue-800 hover:bg-blue-900 text-white rounded-lg font-bold whitespace-nowrap">
                                                Aktifkan Kembali
                                            </button>
                                        </form>
                                    @endif
                                </div>

                                <!-- Modal konfirmasi selesai + catatan perbaikan -->
                                <div x-show="modalFasilitas === {{ $f->id }}" x-cloak
                                     class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
                                    <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" @click="modalFasilitas = null"></div>
                                    <div class="flex min-h-full items-center justify-center p-4">
                                        <div class="relative transform overflow-hidden rounded-2xl bg-white shadow-2xl w-full max-w-md p-6 border border-slate-100 space-y-4 text-left">
                                            <div class="border-b border-slate-100 pb-3">
                                                <h3 class="text-base font-black text-slate-900">Selesaikan Perbaikan</h3>
                                                <p class="text-xs text-slate-500">{{ $f->nama_fasilitas }}</p>
                                            </div>

                                            <p class="text-xs text-slate-600">
                                                Fasilitas akan kembali <b>Aktif</b>.
                                                @if($f->laporan_terbuka_count > 0)
                                                    <b>{{ $f->laporan_terbuka_count }} laporan</b> yang masih terbuka otomatis ditandai <b>Selesai</b>.
                                                @else
                                                    Saat ini tidak ada laporan terbuka pada fasilitas ini.
                                                @endif
                                            </p>

                                            <form action="{{ route('petugas.fasilitas.status', $f->id) }}" method="POST" class="space-y-3">
                                                @csrf
                                                <input type="hidden" name="status" value="selesai">
                                                <div>
                                                    <label class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Catatan Perbaikan</label>
                                                    <input type="text" name="catatan_resolusi" maxlength="250"
                                                        placeholder="Contoh: AC sudah diganti dan diuji normal."
                                                        class="mt-1 w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-emerald-200">
                                                    <p class="text-[10px] text-slate-400 mt-1">Opsional. Catatan ini ikut tersimpan di laporan terkait.</p>
                                                </div>

                                                <div class="flex justify-end gap-2 pt-2 border-t border-slate-100">
                                                    <button type="button" @click="modalFasilitas = null"
                                                        class="bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold px-4 py-2 rounded-xl text-xs transition">
                                                        Batal
                                                    </button>
                                                    <button type="submit"
                                                        class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold px-4 py-2 rounded-xl text-xs shadow transition">
                                                        Tandai Selesai
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="py-4 text-center text-slate-400">Belum ada data fasilitas.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
