@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6"
    x-data="{
        filterStatus: 'semua',
        searchQuery: '',
        matchSearch(haystack) {
            return haystack.toLowerCase().includes(this.searchQuery.toLowerCase());
        }
    }">
    <div class="flex items-center gap-2">
        <a href="{{ route('petugas.dashboard') }}" class="text-xs font-bold text-blue-900 hover:underline flex items-center gap-1">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Kembali ke Panel Petugas
        </a>
    </div>

    <div>
        <h1 class="text-2xl font-black text-slate-900">Reservasi</h1>
        <p class="text-sm text-slate-500">Daftar semua reservasi fasilitas beserta status dan aksinya.</p>
    </div>

    <div class="space-y-4">
        <!-- Filter Status -->
        <div class="flex flex-wrap gap-2">
            @foreach([
                'semua' => 'Semua Status',
                'pending' => 'Menunggu',
                'approved' => 'Disetujui',
                'rejected' => 'Ditolak',
                'cancelled' => 'Dibatalkan',
            ] as $key => $label)
                <button @click="filterStatus = '{{ $key }}'"
                    :class="filterStatus === '{{ $key }}' ? 'bg-blue-700 text-white shadow' : 'bg-white text-slate-600 hover:bg-slate-100'"
                    class="px-4 py-2 text-xs font-bold rounded-full border border-slate-200 transition">
                    {{ $label }}
                </button>
            @endforeach
        </div>

        <!-- Search Bar -->
        <div class="relative">
            <svg class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 10a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <input type="text" x-model="searchQuery" placeholder="Cari nama pemohon, ruangan, tujuan atau ID reservasi..."
                class="w-full pl-10 pr-4 py-3 bg-white border border-slate-200 rounded-2xl text-xs focus:outline-none focus:ring-2 focus:ring-blue-200">
        </div>

        <!-- Antrian Reservasi -->
        @if($reservations->isEmpty())
            <div class="bg-white rounded-2xl border border-dashed border-slate-300 p-8 text-center text-xs text-slate-500">
                Belum ada reservasi yang masuk.
            </div>
        @else
            <div class="space-y-3">
                @foreach($reservations as $r)
                    @php
                        $statusMap = [
                            'pending'   => ['label' => 'Menunggu',   'badge' => 'bg-amber-100 text-amber-700'],
                            'approved'  => ['label' => 'Disetujui',  'badge' => 'bg-emerald-100 text-emerald-700'],
                            'rejected'  => ['label' => 'Ditolak',    'badge' => 'bg-rose-100 text-rose-700'],
                            'cancelled' => ['label' => 'Dibatalkan', 'badge' => 'bg-slate-200 text-slate-600'],
                        ];
                        $searchHaystack = collect([
                            $r->id,
                            $r->user->name ?? '',
                            $r->facility->nama_fasilitas ?? '',
                            $r->tujuan,
                        ])->implode(' ');
                    @endphp
                    <div
                        x-show="(filterStatus === 'semua' || filterStatus === '{{ $r->status }}') && matchSearch('{{ addslashes($searchHaystack) }}')"
                        x-cloak
                        class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4 space-y-3">
                        <div class="flex items-start justify-between gap-2 flex-wrap">
                            <div>
                                <div class="font-bold text-slate-800 text-sm">{{ $r->facility->nama_fasilitas ?? '-' }}</div>
                                <div class="text-xs text-slate-500">{{ $r->user->name ?? '-' }} &bull; ID #{{ $r->id }}</div>
                            </div>
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold whitespace-nowrap {{ $statusMap[$r->status]['badge'] ?? 'bg-slate-100 text-slate-600' }}">
                                {{ $statusMap[$r->status]['label'] ?? ucfirst($r->status) }}
                            </span>
                        </div>

                        <div class="text-xs text-slate-500">
                            {{ $r->tanggal->format('d M Y') }} &bull; {{ \Carbon\Carbon::parse($r->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($r->end_time)->format('H:i') }}
                        </div>
                        <p class="text-xs text-slate-600">{{ $r->tujuan }}</p>

                        @if($r->status === 'cancelled' && $r->alasan_batal)
                            <p class="text-[11px] text-slate-500 italic">Alasan pembatalan: {{ $r->alasan_batal }}</p>
                        @endif

                        @if($r->status === 'pending')
                            <div class="flex gap-2 pt-2 border-t border-slate-100">
                                <form action="{{ route('petugas.reservasi.approve', $r->id) }}" method="POST" class="flex-1">
                                    @csrf
                                    <button type="submit" class="w-full py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl transition">
                                        Setujui
                                    </button>
                                </form>
                                <form action="{{ route('petugas.reservasi.reject', $r->id) }}" method="POST" class="flex-1">
                                    @csrf
                                    <button type="submit" class="w-full py-2 bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold rounded-xl transition">
                                        Tolak
                                    </button>
                                </form>
                            </div>
                        @elseif($r->status === 'approved')
                            <form action="{{ route('petugas.reservasi.emergency_cancel', $r->id) }}" method="POST" class="flex gap-2 pt-2 border-t border-slate-100">
                                @csrf
                                <input type="text" name="alasan_batal" placeholder="Alasan pembatalan" required
                                    class="flex-1 p-2 bg-slate-50 border border-slate-300 rounded-lg text-[10px]">
                                <button class="px-3 py-2 bg-amber-500 hover:bg-amber-600 text-white rounded-lg text-xs font-bold whitespace-nowrap">
                                    Batal Darurat
                                </button>
                            </form>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection
