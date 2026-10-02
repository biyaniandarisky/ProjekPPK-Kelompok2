@extends('layouts.app')

@section('title', 'Reservasi Kampus — Pesan Fasilitas Kampus')

@section('content')
@php
    $user = auth()->user();
    $isGuest = !$user;
    $isPengguna = $user && $user->role === 'pengguna';

    $laporUrl = $isGuest
        ? route('login')
        : ($isPengguna
            ? route('pengguna.dashboard', ['lapor' => 1])
            : ($user->role === 'admin' ? route('admin.dashboard') : route('petugas.dashboard')));
@endphp

{{-- HERO --}}
<section class="relative bg-blue-900 text-white overflow-hidden">
    <img src="https://images.unsplash.com/photo-1523240795612-9a054b0db644?auto=format&fit=crop&w=1600&q=70"
         alt="" class="absolute inset-0 w-full h-full object-cover opacity-30">
    <div class="absolute inset-0 bg-gradient-to-r from-blue-900 via-blue-900/90 to-blue-900/50"></div>

    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-14 pb-24 sm:pt-16 sm:pb-28">
        <p class="text-xs font-bold tracking-wide text-emerald-400 uppercase">Sarana &amp; Prasarana Kampus</p>
        <h1 class="mt-2 max-w-xl text-3xl sm:text-4xl font-bold leading-tight tracking-tight">
            Pesan fasilitas kampus, cepat dan tanpa antre
        </h1>
        <p class="mt-3 max-w-xl text-sm text-blue-100 leading-relaxed">
            Cek ketersediaan ruang kelas, laboratorium, aula, dan lapangan secara real-time.
        </p>
    </div>
</section>

{{-- FILTER --}}
<section class="relative z-10 max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 -mt-12">
    <form action="{{ route('landing') }}" method="GET"
          class="bg-white rounded-2xl shadow-xl border border-slate-200 p-4 sm:p-5 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-[1.4fr_1.4fr_1fr_1fr_auto] gap-3 items-end text-xs">

        <div>
            <label class="block text-xs font-bold text-slate-700 mb-1.5" for="f-q">Fasilitas</label>
            <input id="f-q" type="text" name="q" value="{{ request('q') }}"
                   placeholder="Cari nama fasilitas..."
                   class="w-full h-10 px-3 bg-slate-50 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-900">
        </div>

        <div>
            <label class="block text-xs font-bold text-slate-700 mb-1.5" for="f-lokasi">Lokasi</label>
            <select id="f-lokasi" name="lokasi"
                    class="w-full h-10 px-3 bg-slate-50 border border-slate-200 rounded-lg text-sm focus:outline-none focus:border-blue-900">
                <option value="">Semua lokasi</option>
                @foreach($lokasiList ?? [] as $lok)
                    <option value="{{ $lok }}" @selected(request('lokasi') === $lok)>{{ $lok }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-xs font-bold text-slate-700 mb-1.5" for="f-kap">Kapasitas</label>
            <select id="f-kap" name="kapasitas"
                    class="w-full h-10 px-3 bg-slate-50 border border-slate-200 rounded-lg text-sm focus:outline-none focus:border-blue-900">
                <option value="">Semua</option>
                @foreach([10, 30, 50, 100, 200] as $k)
                    <option value="{{ $k }}" @selected((string) request('kapasitas') === (string) $k)>≥ {{ $k }} org</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-xs font-bold text-slate-700 mb-1.5" for="f-tgl">Tanggal</label>
            <input id="f-tgl" type="date" name="tanggal" value="{{ $tanggal }}" min="{{ $today }}"
                   class="w-full h-10 px-3 bg-slate-50 border border-slate-200 rounded-lg text-sm focus:outline-none focus:border-blue-900">
        </div>

        <button type="submit"
                class="h-10 px-6 inline-flex items-center justify-center gap-2 bg-blue-900 hover:bg-blue-800 text-white font-bold rounded-lg transition sm:col-span-2 lg:col-span-1 shadow-sm">
            Cari
        </button>
    </form>
</section>

{{-- DAFTAR FASILITAS --}}
<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-10 pb-6">
    <h2 class="text-xl font-bold text-slate-900 border-b border-slate-200 pb-3">Daftar Fasilitas Kampus</h2>

    @if($facilities->isEmpty())
        <div class="mt-8 bg-white border border-dashed border-slate-300 rounded-2xl py-14 text-center text-sm text-slate-500">
            Tidak ada fasilitas yang cocok.
        </div>
    @else
        <div class="mt-5 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
            @foreach($facilities as $fac)
                @php
                    $perbaikan = $fac->status === 'dalam_perbaikan';
                    $payload = [
                        'id' => $fac->id,
                        'nama' => $fac->nama_fasilitas,
                        'tipe' => $fac->tipe,
                        'lokasi' => $fac->lokasi,
                        'kapasitas' => $fac->kapasitas,
                        'foto' => $fac->foto_url,
                        'maintenance' => $perbaikan,
                    ];
                @endphp
                <article class="bg-white rounded-2xl border border-slate-200 shadow-sm hover:shadow-md transition overflow-hidden flex flex-col">
                    <div class="relative h-40 bg-gradient-to-br from-slate-200 to-slate-300">
                        @if($fac->foto_url)
                            <img src="{{ $fac->foto_url }}" alt="{{ $fac->nama_fasilitas }}" loading="lazy"
                                 class="w-full h-full object-cover {{ $perbaikan ? 'grayscale-[40%]' : '' }}">
                        @endif
                        <span class="absolute top-2 left-2 px-2 py-0.5 bg-white/95 text-slate-800 rounded text-[10px] font-bold shadow-sm">
                            {{ $fac->tipe }}
                        </span>
                        @if($perbaikan)
                            <span class="absolute top-2 right-2 px-2 py-0.5 bg-amber-500 text-white rounded text-[10px] font-bold shadow-sm">
                                Perbaikan
                            </span>
                        @endif
                    </div>

                    <div class="p-4 flex-1 flex flex-col">
                        <h3 class="font-bold text-slate-900 text-sm leading-snug">{{ $fac->nama_fasilitas }}</h3>
                        <p class="mt-1 text-xs text-slate-500 truncate">{{ $fac->lokasi }}</p>
                        <p class="text-xs text-slate-500">Kapasitas {{ $fac->kapasitas }} orang</p>

                        <button type="button" onclick="openFacilityModal(@js($payload))"
                                class="mt-3 w-full h-9 inline-flex items-center justify-center gap-1.5 bg-blue-900 hover:bg-blue-800 text-white text-xs font-bold rounded-lg transition">
                            Lihat Jadwal
                        </button>
                    </div>
                </article>
            @endforeach
        </div>
    @endif
</section>

{{-- BANNER LAPOR --}}
<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-4 pb-10">
    <div class="rounded-2xl bg-blue-900 text-white p-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4 shadow-lg">
        <div>
            <h3 class="font-bold text-base">Fasilitas Rusak atau Bermasalah?</h3>
            <p class="text-xs text-blue-100 mt-1">Laporkan agar segera diperbaiki.</p>
        </div>
        <a href="{{ $laporUrl }}"
           class="shrink-0 inline-flex items-center justify-center gap-1.5 px-5 h-10 bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold rounded-lg transition shadow-sm">
            Laporkan Masalah
        </a>
    </div>
</section>

{{-- MODAL JADWAL --}}
<div id="facilityModal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-6" role="dialog">
    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="closeFacilityModal()"></div>
    <div class="relative w-full max-w-2xl max-h-[92vh] flex flex-col bg-white rounded-2xl shadow-2xl overflow-hidden">
        <div class="flex items-center justify-between px-5 py-3.5 border-b border-slate-100 bg-slate-50">
            <h3 class="font-bold text-sm text-slate-900">Jadwal & Ketersediaan Slot</h3>
            <button type="button" onclick="closeFacilityModal()"
                    class="w-7 h-7 rounded-lg text-slate-400 hover:text-slate-700 hover:bg-slate-100 flex items-center justify-center">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <div class="flex-1 overflow-y-auto px-5 py-4 text-sm" id="modalBody"></div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const FACILITY_AVAIL_URL = '{{ route('fasilitas.ketersediaan', ['id' => '__ID__']) }}';
const FACILITY_INTENT_URL = '{{ route('booking.intent') }}';
const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]').content;
const TODAY = '{{ now()->toDateString() }}';
const IS_GUEST = {{ auth()->guest() ? 'true' : 'false' }};
const CAN_BOOK = {{ (auth()->guest() || (auth()->check() && auth()->user()->role === 'pengguna')) ? 'true' : 'false' }};

let currentFacility = null;
let currentTanggal = TODAY;
let currentSlots = [];
let fromIdx = null;
let toIdx = null;

function openFacilityModal(payload) {
    currentFacility = payload;
    currentTanggal = TODAY;
    currentSlots = [];
    fromIdx = null;
    toIdx = null;
    document.getElementById('facilityModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
    renderModal();
    if (!payload.maintenance) loadSlots();
}

function closeFacilityModal() {
    document.getElementById('facilityModal').classList.add('hidden');
    document.body.style.overflow = '';
}

async function loadSlots() {
    if (!currentFacility || currentFacility.maintenance) return;
    document.getElementById('modalBody').innerHTML = '<p class="text-center text-slate-400 py-8">Memuat slot...</p>';
    try {
        const url = FACILITY_AVAIL_URL.replace('__ID__', currentFacility.id) + '?tanggal=' + currentTanggal;
        const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
        const data = await res.json();
        currentSlots = data.slots || [];
        currentFacility.maintenance = data.is_maintenance || false;
        renderModal();
    } catch (e) {
        document.getElementById('modalBody').innerHTML = '<p class="text-center text-rose-600 py-8">Gagal memuat slot.</p>';
    }
}

function renderModal() {
    const f = currentFacility;
    if (!f) return;
    const body = document.getElementById('modalBody');

    if (f.maintenance) {
        body.innerHTML = `
            <div class="rounded-2xl border border-amber-200 bg-amber-50 p-6 text-center space-y-2">
                <p class="font-bold text-amber-800">Fasilitas sedang dalam perbaikan</p>
                <p class="text-xs text-amber-700">Slot dikosongkan dan belum dapat dipesan.</p>
            </div>`;
        return;
    }

    const availableCount = currentSlots.filter(s => s.is_available).length;
    const bookedCount = currentSlots.filter(s => s.is_booked).length;
    const availablePct = currentSlots.length ? Math.round(availableCount / currentSlots.length * 100) : 0;

    let slotsHtml = '';
    currentSlots.forEach((s, i) => {
        const selected = fromIdx !== null && i >= fromIdx && i <= toIdx;
        const cls = !s.is_available
            ? 'bg-slate-100 border-slate-100 text-slate-400 cursor-not-allowed'
            : (selected ? 'bg-blue-900 border-blue-900 text-white shadow-sm' : 'bg-white border-slate-200 hover:border-blue-900 text-slate-900');
        const statusText = selected ? 'Dipilih' : (s.is_booked ? 'Terisi' : (s.is_past ? 'Lewat' : 'Tersedia'));
        slotsHtml += `
            <button type="button" onclick="toggleSlot(${i})" ${!s.is_available ? 'disabled' : ''}
                    class="text-left rounded-lg border-2 px-2.5 py-1.5 transition ${cls}">
                <span class="block text-xs font-bold">${s.start} - ${s.end}</span>
                <span class="block text-[10px] font-semibold opacity-80">${statusText}</span>
            </button>`;
    });

    const hasSelection = fromIdx !== null;
    const startTime = hasSelection ? currentSlots[fromIdx].start : '';
    const endTime = hasSelection ? currentSlots[toIdx].end : '';
    const count = hasSelection ? (toIdx - fromIdx + 1) : 0;
    const durasi = count * 30;

    body.innerHTML = `
        <div class="space-y-4">
            <div class="flex items-center gap-3 p-3 rounded-2xl border border-slate-200 bg-slate-50">
                ${f.foto ? `<img src="${f.foto}" class="w-14 h-14 rounded-lg object-cover shrink-0">` : ''}
                <div class="min-w-0">
                    <p class="font-bold text-slate-900">${f.nama}</p>
                    <p class="text-xs text-slate-500">${f.lokasi} · Kapasitas ${f.kapasitas} orang</p>
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200 p-3">
                <div class="flex flex-wrap items-center justify-between gap-2 mb-2">
                    <label class="text-xs font-bold text-slate-700">Pilih Tanggal:</label>
                    <input type="date" value="${currentTanggal}" min="${TODAY}"
                           onchange="currentTanggal = this.value; loadSlots()"
                           class="h-8 px-2 border border-slate-300 rounded-lg text-xs">
                </div>
                <div class="flex items-center gap-3 text-xs font-semibold">
                    <span class="text-emerald-600">${availableCount} Tersedia</span>
                    <span class="text-slate-300">|</span>
                    <span class="text-slate-500">${bookedCount} Terisi</span>
                </div>
                <div class="mt-2 h-1.5 w-full rounded-full bg-slate-200 overflow-hidden">
                    <div class="h-full rounded-full bg-emerald-500" style="width: ${availablePct}%"></div>
                </div>
            </div>

            <div>
                <p class="text-xs font-bold text-slate-700 mb-2">Pilih Slot Waktu (30 menit):</p>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">${slotsHtml}</div>
            </div>

            ${hasSelection ? `
                <div class="p-3 bg-blue-50 border border-blue-200 rounded-lg text-xs font-bold text-blue-900">
                    ${count} Slot: ${startTime} – ${endTime} WIB (${durasi} menit)
                </div>` : ''}

            <div class="flex flex-wrap items-center justify-between gap-2 pt-3 border-t border-slate-100">
                <button type="button" onclick="closeFacilityModal()"
                        class="px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-lg">
                    Tutup
                </button>
                <button type="button" onclick="pesan()"
                        ${(!hasSelection || !CAN_BOOK) ? 'disabled' : ''}
                        class="px-6 py-2 bg-blue-900 hover:bg-blue-800 text-white text-xs font-bold rounded-lg shadow-sm disabled:opacity-40 disabled:cursor-not-allowed transition">
                    Pesan
                </button>
            </div>
        </div>`;
}

function toggleSlot(i) {
    if (!currentSlots[i] || !currentSlots[i].is_available) return;

    if (fromIdx === null) {
        fromIdx = toIdx = i;
    } else if (i >= fromIdx && i <= toIdx) {
        if (toIdx - fromIdx === 0) { fromIdx = toIdx = null; }
        else if (i === fromIdx) { fromIdx = i + 1; }
        else if (i === toIdx) { toIdx = i - 1; }
        else { fromIdx = toIdx = i; }
    } else if (i > toIdx && rangeAvailable(toIdx + 1, i)) {
        toIdx = i;
    } else if (i < fromIdx && rangeAvailable(i, fromIdx - 1)) {
        fromIdx = i;
    } else {
        fromIdx = toIdx = i;
    }

    renderModal();
}

function rangeAvailable(a, b) {
    for (let k = Math.min(a, b); k <= Math.max(a, b); k++) {
        if (!currentSlots[k] || !currentSlots[k].is_available) return false;
    }
    return true;
}

async function pesan() {
    if (fromIdx === null) return;

    const payload = {
        facility_id: currentFacility.id,
        tanggal: currentTanggal,
        start_time: currentSlots[fromIdx].start,
        end_time: currentSlots[toIdx].end,
    };

    try {
        const res = await fetch(FACILITY_INTENT_URL, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': CSRF_TOKEN,
            },
            body: JSON.stringify(payload),
        });
        const data = await res.json();
        if (!res.ok) {
            alert(data.message || 'Gagal memproses.');
            return;
        }
        window.location.href = data.redirect;
    } catch (e) {
        alert('Terjadi kesalahan.');
    }
}

document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') closeFacilityModal();
});
</script>
@endpush