@extends('layouts.app')

@section('title', 'Daftar Fasilitas Kampus')

@section('content')
<div class="bg-white border-b border-slate-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <h1 class="text-2xl font-bold text-slate-900">Daftar Fasilitas Kampus</h1>
        <p class="text-sm text-slate-500 mt-1">{{ $facilities->total() }} fasilitas ditemukan</p>
    </div>
</div>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">

    {{-- FILTER --}}
    <form action="{{ route('facilities.index') }}" method="GET"
          class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm flex flex-wrap gap-3">
        <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari nama fasilitas..."
               class="flex-1 min-w-[200px] h-10 px-3 border border-slate-200 rounded-lg text-sm focus:outline-none focus:border-blue-900">

        <select name="tipe" class="h-10 px-3 border border-slate-200 rounded-lg text-sm bg-white focus:outline-none focus:border-blue-900">
            <option value="">Semua Tipe</option>
            @foreach(['Ruangan','Laboratorium','Olahraga','Fasilitas Umum'] as $t)
                <option value="{{ $t }}" @selected(request('tipe') === $t)>{{ $t }}</option>
            @endforeach
        </select>

        <select name="lokasi" class="h-10 px-3 border border-slate-200 rounded-lg text-sm bg-white focus:outline-none focus:border-blue-900">
            <option value="">Semua Lokasi</option>
            @foreach($lokasiList ?? [] as $lok)
                <option value="{{ $lok }}" @selected(request('lokasi') === $lok)>{{ $lok }}</option>
            @endforeach
        </select>

        <button type="submit" class="h-10 px-6 bg-blue-900 hover:bg-blue-800 text-white text-sm font-bold rounded-lg transition">
            Cari
        </button>

        @if(request()->hasAny(['q', 'tipe', 'lokasi']))
            <a href="{{ route('facilities.index') }}"
               class="h-10 px-4 inline-flex items-center bg-amber-500 hover:bg-amber-600 text-white text-sm font-bold rounded-lg transition">
                Reset
            </a>
        @endif
    </form>

    {{-- GRID --}}
    @if($facilities->isEmpty())
        <div class="bg-white rounded-2xl border border-slate-200 p-16 text-center">
            <p class="text-sm text-slate-500 font-medium">Tidak ada fasilitas yang cocok.</p>
            <a href="{{ route('facilities.index') }}" class="inline-block mt-3 text-sm font-bold text-blue-900 hover:underline">
                Reset pencarian →
            </a>
        </div>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
            @foreach($facilities as $fac)
                @php
                    $perbaikan = in_array($fac->status, ['dalam_perbaikan', 'selesai'], true);
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
                    <div class="relative h-40 bg-gradient-to-br from-blue-100 to-indigo-100">
                        @if($fac->foto_url)
                            <img src="{{ $fac->foto_url }}" alt="{{ $fac->nama_fasilitas }}"
                                 class="w-full h-full object-cover {{ $perbaikan ? 'grayscale-[40%]' : '' }}">
                        @endif
                        <span class="absolute top-2 left-2 px-2 py-0.5 bg-white/95 text-slate-800 rounded text-[10px] font-bold shadow-sm">
                            {{ $fac->tipe }}
                        </span>
                        <span class="absolute top-2 right-2 px-2 py-0.5 text-white rounded text-[10px] font-bold shadow-sm
                            {{ $perbaikan ? 'bg-amber-500' : 'bg-emerald-600' }}">
                            {{ $perbaikan ? 'Perbaikan' : 'Tersedia' }}
                        </span>
                    </div>

                    <div class="p-4 flex-1 flex flex-col">
                        <h3 class="font-bold text-slate-900 text-sm leading-snug">{{ $fac->nama_fasilitas }}</h3>
                        <p class="mt-1 text-xs text-slate-500 truncate">{{ $fac->lokasi }}</p>
                        <p class="text-xs text-slate-500">Kapasitas {{ $fac->kapasitas }} orang</p>

                        <button type="button" onclick="openFacilityModal(@js($payload))"
                                class="mt-3 w-full h-9 inline-flex items-center justify-center bg-blue-900 hover:bg-blue-800 text-white text-xs font-bold rounded-lg transition">
                            Lihat Jadwal
                        </button>
                    </div>
                </article>
            @endforeach
        </div>

        <div class="mt-8">{{ $facilities->links() }}</div>
    @endif
</div>

{{-- MODAL --}}
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
                <p class="text-xs text-amber-700">Slot dikosongkan.</p>
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
        if (!res.ok) { alert(data.message || 'Gagal memproses.'); return; }
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