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
        <h1 class="text-2xl font-black text-slate-900">Laporan</h1>
        <p class="text-sm text-slate-500">
            Kelola laporan kendala dari pengguna. Status laporan tersambung dengan menu
            <a href="{{ route('petugas.fasilitas.index') }}" class="font-bold text-blue-900 hover:underline">Fasilitas</a>:
            laporan yang Diproses membuat fasilitas jadi Dalam Perbaikan, dan laporan yang Selesai mengembalikannya ke Aktif.
        </p>
    </div>

    <div class="bg-white rounded-2xl p-6 shadow border border-slate-100"
        x-data="{
            filterStatus: 'semua',
            searchQuery: '',
            matchSearch(haystack) {
                return haystack.toLowerCase().includes(this.searchQuery.toLowerCase());
            }
        }">

        <!-- Filter Status -->
        <div class="flex flex-wrap gap-2 mb-4">
            @foreach([
                'semua' => 'Semua Status',
                'baru' => 'Baru',
                'diproses' => 'Diproses',
                'selesai' => 'Selesai',
                'ditolak' => 'Ditolak',
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
            <input type="text" x-model="searchQuery" placeholder="Cari nama pelapor, fasilitas, atau kategori..."
                class="w-full pl-10 pr-4 py-3 bg-white border border-slate-200 rounded-2xl text-xs focus:outline-none focus:ring-2 focus:ring-blue-200">
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-xs">
                <thead>
                    <tr class="text-left text-slate-500 border-b text-sm font-bold">
                        <th class="py-2 pr-4">Pelapor</th>
                        <th class="py-2 pr-4">Fasilitas</th>
                        <th class="py-2 pr-4">Kategori</th>
                        <th class="py-2 pr-4">Status Laporan</th>
                        <th class="py-2 pr-4">Status Fasilitas</th>
                        <th class="py-2 pr-4">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($reports as $r)
                        @php
                            $facilityStatusBadge = match($r->facility->status ?? 'aktif') {
                                'dalam_perbaikan' => 'bg-amber-100 text-amber-700',
                                'nonaktif' => 'bg-rose-100 text-rose-700',
                                default => 'bg-emerald-100 text-emerald-700',
                            };
                            $searchHaystack = collect([
                                $r->user->name ?? '',
                                $r->facility->nama_fasilitas ?? '',
                                $r->kategori_laporan,
                            ])->implode(' ');
                        @endphp
                        <tr class="border-b border-slate-100"
                            x-show="(filterStatus === 'semua' || filterStatus === '{{ $r->status_laporan }}') && matchSearch('{{ addslashes($searchHaystack) }}')"
                            x-cloak>
                            <td class="py-2 pr-4 font-bold">{{ $r->user->name ?? '-' }}</td>
                            <td class="py-2 pr-4">{{ $r->facility->nama_fasilitas ?? '-' }}</td>
                            <td class="py-2 pr-4">{{ $r->kategori_laporan }}</td>
                            <td class="py-2 pr-4">{{ ucfirst($r->status_laporan) }}</td>
                            <td class="py-2 pr-4">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold whitespace-nowrap {{ $facilityStatusBadge }}">
                                    {{ ucfirst(str_replace('_', ' ', $r->facility->status ?? 'aktif')) }}
                                </span>
                            </td>
                            <td class="py-2 pr-4">
                                @if($r->status_laporan == 'baru')
                                    <form action="{{ route('petugas.laporan.process', $r->id) }}" method="POST">
                                        @csrf
                                        <button class="px-3 py-1.5 bg-blue-800 hover:bg-blue-900 text-white rounded-lg font-bold whitespace-nowrap">Proses</button>
                                    </form>
                                @elseif($r->status_laporan == 'diproses')
                                    <form action="{{ route('petugas.laporan.resolve', $r->id) }}" method="POST" class="flex gap-2">
                                        @csrf
                                        <input type="text" name="catatan_resolusi" placeholder="Catatan perbaikan" class="p-1.5 bg-slate-50 border border-slate-300 rounded-lg text-[10px]" required>
                                        <button class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg font-bold whitespace-nowrap">Selesaikan</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="py-4 text-center text-slate-400">Belum ada laporan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
