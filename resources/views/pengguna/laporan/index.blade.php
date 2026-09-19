@extends('layouts.app')

@section('content')
<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <!-- Breadcrumb / Back -->
    <a href="{{ route('pengguna.dashboard') }}" class="inline-flex items-center gap-1 text-xs font-bold text-slate-500 hover:text-slate-700 mb-4 transition">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
        </svg>
        Kembali ke Panel
    </a>

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-black text-slate-900">Laporan Kendala Saya</h1>
            <p class="text-sm text-slate-500">Status penanganan masalah fasilitas yang dikirim.</p>
        </div>
        <a href="{{ route('pengguna.laporan.create') }}"
            class="inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold rounded-xl shadow transition shrink-0">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            Laporkan Kendala Baru
        </a>
    </div>

    <!-- Table Card -->
    <div class="bg-white rounded-2xl shadow border border-slate-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-slate-50 text-slate-400 uppercase text-[10px] tracking-wider">
                    <tr>
                        <th class="py-3.5 px-6">Fasilitas</th>
                        <th class="py-3.5 px-6">Tanggal Lapor</th>
                        <th class="py-3.5 px-6">Rincian Kendala</th>
                        <th class="py-3.5 px-6">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($myReports as $l)
                        <tr class="hover:bg-slate-50/50 transition">
                            <!-- Fasilitas -->
                            <td class="py-4 px-6 font-bold text-slate-900">{{ $l->facility->nama_fasilitas ?? '-' }}</td>

                            <!-- Tanggal Lapor -->
                            <td class="py-4 px-6 text-slate-600 whitespace-nowrap">
                                {{ $l->created_at->format('d M Y') }}
                            </td>

                            <!-- Rincian Kendala: kategori + deskripsi asli dari form -->
                            <td class="py-4 px-6 max-w-sm">
                                @if($l->kategori_laporan)
                                    <span class="font-bold text-slate-800">[{{ $l->kategori_laporan }}]</span>
                                @endif
                                <span class="text-slate-600">{{ $l->deskripsi ?? '-' }}</span>
                            </td>

                            <!-- Status -->
                            <td class="py-4 px-6">
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold
                                    @if($l->status_laporan === 'selesai') bg-emerald-100 text-emerald-700
                                    @elseif($l->status_laporan === 'diproses') bg-blue-100 text-blue-700
                                    @elseif($l->status_laporan === 'ditolak') bg-rose-100 text-rose-700
                                    @else bg-amber-100 text-amber-700 @endif">
                                    @if($l->status_laporan === 'selesai') Selesai Ditangani
                                    @elseif($l->status_laporan === 'diproses') Diproses
                                    @elseif($l->status_laporan === 'ditolak') Ditolak
                                    @else Baru @endif
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="py-12 text-center text-slate-400">
                                Belum ada riwayat laporan kendala.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
