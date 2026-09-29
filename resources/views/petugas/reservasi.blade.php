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
        <h1 class="text-2xl font-black text-slate-900">Reservasi</h1>
        <p class="text-sm text-slate-500">Daftar semua reservasi fasilitas beserta status dan aksinya.</p>
    </div>

    <div class="bg-white rounded-2xl p-6 shadow border border-slate-100"
        x-data="{
            filterStatus: 'semua',
            searchQuery: '',
            modalTolak: null,
            modalBatal: null,
            matchSearch(haystack) {
                return haystack.toLowerCase().includes(this.searchQuery.toLowerCase());
            }
        }">

        <!-- Filter Status -->
        <div class="flex flex-wrap gap-2 mb-4">
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
        <div class="relative mb-4">
            <svg class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 10a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <input type="text" x-model="searchQuery" placeholder="Cari nama pemohon, ruangan, tujuan atau ID reservasi..."
                class="w-full pl-10 pr-4 py-3 bg-white border border-slate-200 rounded-2xl text-xs focus:outline-none focus:ring-2 focus:ring-blue-200">
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-xs">
                <thead>
                    <tr class="text-left text-slate-500 border-b text-sm font-bold">
                        <th class="py-2 pr-4">Pemohon</th>
                        <th class="py-2 pr-4">Fasilitas</th>
                        <th class="py-2 pr-4">Jadwal</th>
                        <th class="py-2 pr-4">Tujuan</th>
                        <th class="py-2 pr-4">Status</th>
                        <th class="py-2 pr-4">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($reservations as $r)
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
                        <tr class="border-b border-slate-100 hover:bg-slate-50/70 transition align-top"
                            x-show="(filterStatus === 'semua' || filterStatus === '{{ $r->status }}') && matchSearch('{{ addslashes($searchHaystack) }}')"
                            x-cloak>
                            <td class="py-2 pr-4">
                                <div class="font-bold">{{ $r->user->name ?? '-' }}</div>
                                <div class="text-[10px] text-slate-400">ID #{{ $r->id }}</div>
                            </td>
                            <td class="py-2 pr-4">{{ $r->facility->nama_fasilitas ?? '-' }}</td>
                            <td class="py-2 pr-4 whitespace-nowrap">
                                <div>{{ $r->tanggal->format('d M Y') }}</div>
                                <div class="text-slate-500">{{ \Carbon\Carbon::parse($r->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($r->end_time)->format('H:i') }}</div>
                            </td>
                            <td class="py-2 pr-4 max-w-xs">
                                <div>{{ $r->tujuan }}</div>
                                @if($r->status === 'cancelled' && $r->alasan_batal)
                                    <div class="text-[11px] text-slate-500 italic mt-1">Alasan pembatalan: {{ $r->alasan_batal }}</div>
                                @endif
                                @if($r->status === 'rejected' && $r->alasan_tolak)
                                    <div class="text-[11px] text-slate-500 italic mt-1">Alasan penolakan: {{ $r->alasan_tolak }}</div>
                                @endif
                            </td>
                            <td class="py-2 pr-4">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold whitespace-nowrap {{ $statusMap[$r->status]['badge'] ?? 'bg-slate-100 text-slate-600' }}">
                                    {{ $statusMap[$r->status]['label'] ?? ucfirst($r->status) }}
                                </span>
                            </td>
                            <td class="py-2 pr-4">
                                @if($r->status === 'pending')
                                    <div class="flex gap-2">
                                        <form action="{{ route('petugas.reservasi.approve', $r->id) }}" method="POST"
                                              onsubmit="return confirm('Setujui reservasi #{{ $r->id }} untuk {{ addslashes($r->facility->nama_fasilitas ?? '-') }}?');">
                                            @csrf
                                            <button type="submit" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg font-bold whitespace-nowrap">
                                                Setujui
                                            </button>
                                        </form>
                                        <button type="button" @click="modalTolak = {{ $r->id }}"
                                            class="px-3 py-1.5 bg-rose-600 hover:bg-rose-700 text-white rounded-lg font-bold whitespace-nowrap">
                                            Tolak
                                        </button>
                                    </div>
                                @elseif($r->status === 'approved')
                                    <button type="button" @click="modalBatal = {{ $r->id }}"
                                        class="px-3 py-1.5 bg-amber-500 hover:bg-amber-600 text-white rounded-lg font-bold whitespace-nowrap">
                                        Batal Darurat
                                    </button>
                                @endif
                            </td>
                        </tr>

                        <!-- Modal: Tolak Reservasi (alasan wajib) -->
                        <tr x-show="modalTolak === {{ $r->id }}" x-cloak>
                            <td colspan="6" class="p-0">
                                <div class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
                                    <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" @click="modalTolak = null"></div>
                                    <div class="flex min-h-full items-center justify-center p-4">
                                        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md p-6 border border-slate-100 space-y-4 text-left">
                                            <div class="border-b border-slate-100 pb-3">
                                                <h3 class="text-base font-black text-slate-900">Tolak Reservasi</h3>
                                                <p class="text-xs text-slate-500">#{{ $r->id }} &bull; {{ $r->facility->nama_fasilitas ?? '-' }} &bull; {{ $r->user->name ?? '-' }}</p>
                                            </div>
                                            <form action="{{ route('petugas.reservasi.reject', $r->id) }}" method="POST" class="space-y-3">
                                                @csrf
                                                <div>
                                                    <label class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Alasan Penolakan (wajib)</label>
                                                    <textarea name="alasan_tolak" rows="3" required maxlength="250"
                                                        placeholder="Contoh: Jadwal bentrok dengan kegiatan resmi kampus."
                                                        class="mt-1 w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-rose-200"></textarea>
                                                </div>
                                                <div class="flex justify-end gap-2 pt-2 border-t border-slate-100">
                                                    <button type="button" @click="modalTolak = null"
                                                        class="bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold px-4 py-2 rounded-xl text-xs transition">
                                                        Batal
                                                    </button>
                                                    <button type="submit"
                                                        class="bg-rose-600 hover:bg-rose-700 text-white font-bold px-4 py-2 rounded-xl text-xs shadow transition">
                                                        Tolak Reservasi
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>

                        <!-- Modal: Batal Darurat (alasan wajib) -->
                        <tr x-show="modalBatal === {{ $r->id }}" x-cloak>
                            <td colspan="6" class="p-0">
                                <div class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
                                    <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" @click="modalBatal = null"></div>
                                    <div class="flex min-h-full items-center justify-center p-4">
                                        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md p-6 border border-slate-100 space-y-4 text-left">
                                            <div class="border-b border-slate-100 pb-3">
                                                <h3 class="text-base font-black text-slate-900">Batalkan Reservasi (Darurat)</h3>
                                                <p class="text-xs text-slate-500">#{{ $r->id }} &bull; {{ $r->facility->nama_fasilitas ?? '-' }} &bull; {{ $r->user->name ?? '-' }}</p>
                                            </div>
                                            <p class="text-xs text-amber-700 bg-amber-50 border border-amber-200 rounded-xl p-2.5">
                                                Reservasi yang sudah disetujui akan dibatalkan paksa. Gunakan hanya untuk keadaan mendesak.
                                            </p>
                                            <form action="{{ route('petugas.reservasi.emergency_cancel', $r->id) }}" method="POST" class="space-y-3">
                                                @csrf
                                                <div>
                                                    <label class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Alasan Pembatalan (wajib)</label>
                                                    <textarea name="alasan_batal" rows="3" required maxlength="250"
                                                        placeholder="Contoh: Ruangan dipakai untuk kegiatan mendadak dari pimpinan kampus."
                                                        class="mt-1 w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-amber-200"></textarea>
                                                </div>
                                                <div class="flex justify-end gap-2 pt-2 border-t border-slate-100">
                                                    <button type="button" @click="modalBatal = null"
                                                        class="bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold px-4 py-2 rounded-xl text-xs transition">
                                                        Batal
                                                    </button>
                                                    <button type="submit"
                                                        class="bg-amber-500 hover:bg-amber-600 text-white font-bold px-4 py-2 rounded-xl text-xs shadow transition">
                                                        Batalkan Reservasi
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="py-4 text-center text-slate-400">Belum ada reservasi yang masuk.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
