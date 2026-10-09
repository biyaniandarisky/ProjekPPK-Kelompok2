@extends('layouts.app')

@section('title', 'Ajukan Reservasi')

@section('content')
@php
    $jamMulaiOptions   = ['07:00','07:30','08:00','08:30','09:00','09:30','10:00','10:30','11:00','11:30','12:00','12:30','13:00','13:30','14:00','14:30','15:00','15:30','16:00','16:30','17:00','17:30','18:00','18:30','19:00','19:30'];
    $jamSelesaiOptions = ['07:30','08:00','08:30','09:00','09:30','10:00','10:30','11:00','11:30','12:00','12:30','13:00','13:30','14:00','14:30','15:00','15:30','16:00','16:30','17:00','17:30','18:00','18:30','19:00','19:30','20:00'];
@endphp

<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-6"
     x-data="reservasiForm({
        facilityId: '{{ $facility->id ?? '' }}',
        tanggal: '{{ $tanggal }}',
        today: '{{ now()->toDateString() }}',
        besok: '{{ now()->addDay()->toDateString() }}',
        currentTime: '{{ now()->format('H:i') }}',
        start: '{{ $startTime ?? '08:00' }}',
        end: '{{ $endTime ?? '08:30' }}',
        availUrl: '{{ route('fasilitas.ketersediaan', ['id' => '__ID__']) }}',
        createUrl: '{{ route('pengguna.reservasi.create') }}'
     })">

    {{-- BACK --}}
    <a href="{{ route('landing') }}"
       class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-slate-200 hover:border-blue-900 hover:bg-blue-50 text-slate-700 hover:text-blue-900 text-sm font-bold rounded-xl transition shadow-sm group mb-6">
        <svg class="w-4 h-4 group-hover:-translate-x-0.5 transition" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
        </svg>
        Kembali ke Katalog
    </a>

    {{-- HEADER --}}
    <div class="mb-6">
        <h1 class="text-2xl md:text-3xl font-black text-slate-900">Ajukan Reservasi</h1>
        <p class="text-sm text-slate-500 mt-1">Isi data di bawah untuk mengajukan reservasi fasilitas kampus.</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-5 gap-5">

        {{-- KIRI: INFO FASILITAS --}}
        <div class="lg:col-span-2">
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden lg:sticky lg:top-20">
                @if($facility)
                    <div class="h-40 bg-gradient-to-br from-blue-100 to-indigo-100 relative">
                        @if($facility->foto_url)
                            <img src="{{ $facility->foto_url }}" alt="{{ $facility->nama_fasilitas }}"
                                 class="w-full h-full object-cover">
                        @endif
                        <span class="absolute top-3 left-3 px-2.5 py-1 bg-white/95 backdrop-blur text-[10px] font-bold text-slate-700 rounded-md shadow-sm uppercase tracking-wider">
                            {{ $facility->tipe }}
                        </span>
                        <span class="absolute top-3 right-3 px-2.5 py-1 text-[10px] font-bold text-white rounded-md shadow-sm
                            {{ $facility->status === 'aktif' ? 'bg-emerald-600' : 'bg-amber-500' }}">
                            {{ $facility->status === 'aktif' ? 'Tersedia' : 'Perbaikan' }}
                        </span>
                    </div>

                    <div class="p-5 space-y-4">
                        <div>
                            <h2 class="text-lg font-black text-slate-900 leading-snug">{{ $facility->nama_fasilitas }}</h2>
                            <p class="text-xs text-slate-500 mt-1.5 flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                {{ $facility->lokasi }}
                            </p>
                            <p class="text-xs text-slate-500 mt-1 flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                Kapasitas {{ $facility->kapasitas }} orang
                            </p>
                        </div>

                        @if($facility->deskripsi)
                            <p class="text-xs text-slate-600 leading-relaxed">{{ $facility->deskripsi }}</p>
                        @endif

                        <div class="bg-blue-50 border border-blue-100 rounded-xl p-3 space-y-1.5">
                            <p class="text-xs font-black text-blue-900">Ketentuan Pemakaian</p>
                            <ul class="list-disc list-inside text-xs text-slate-700 space-y-0.5">
                                <li>Slot interval 30 menit (07.00–20.00 WIB)</li>
                                <li>Durasi minimal 30 menit, maksimal 4 jam</li>
                                <li>Minimal pemesanan 2 jam sebelum waktu mulai</li>
                                <li>Diverifikasi oleh Petugas Sarpras</li>
                            </ul>
                        </div>
                    </div>
                @else
                    <div class="p-5 text-xs text-slate-500">
                        <h2 class="font-black text-slate-900 mb-2">Belum Ada Fasilitas Dipilih</h2>
                        <p class="leading-relaxed">Silakan pilih fasilitas pada formulir di samping untuk memuat info ketersediaan slot.</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- KANAN: FORMULIR --}}
        <div class="lg:col-span-3">
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
                <form action="{{ route('pengguna.reservasi.store') }}" method="POST" class="space-y-5"
                      x-data="{ submitting: false }"
                      @submit="if (!confirm('Ajukan reservasi ini? Pastikan jadwal dan tujuan sudah benar.')) { $event.preventDefault(); return; } submitting = true">
                    @csrf
                    <input type="hidden" name="facility_id" :value="facilityId">
                    <input type="hidden" name="tanggal" :value="tanggal">
                    <input type="hidden" name="start_time" :value="start">
                    <input type="hidden" name="end_time" :value="end">

                    {{-- 1. FASILITAS --}}
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-1.5">
                            1. Fasilitas yang Dipinjam <span class="text-rose-500">*</span>
                        </label>
                        <select @change="gantiFasilitas($event.target.value)"
                                class="w-full h-11 px-3 bg-slate-50 border border-slate-300 rounded-xl text-sm font-bold text-slate-900 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-900 focus:outline-none">
                            <option value="">— Pilih Fasilitas Kampus —</option>
                            @foreach($facilities as $f)
                                <option value="{{ $f->id }}" {{ ($facility && $facility->id === $f->id) ? 'selected' : '' }}>
                                    {{ $f->nama_fasilitas }} ({{ $f->lokasi }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- 2. TANGGAL --}}
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="block text-sm font-bold text-slate-700">
                                2. Tanggal Penggunaan <span class="text-rose-500">*</span>
                            </label>
                            <div class="flex gap-1.5 text-xs">
                                <button type="button" @click="tanggal = today"
                                        :class="tanggal === today ? 'bg-blue-900 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                                        class="px-3 py-1 rounded-md font-bold transition">Hari Ini</button>
                                <button type="button" @click="tanggal = besok"
                                        :class="tanggal === besok ? 'bg-blue-900 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                                        class="px-3 py-1 rounded-md font-bold transition">Besok</button>
                            </div>
                        </div>
                        <input type="date" x-model="tanggal" :min="today"
                               class="w-full h-11 px-3 bg-slate-50 border border-slate-300 rounded-xl text-sm font-bold text-slate-900 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-900 focus:outline-none">
                    </div>

                    {{-- 3. PILIH JAM PEMAKAIAN (SLOT GRID) --}}
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <label class="block text-sm font-bold text-slate-700">
                                3. Pilih Jam Pemakaian <span class="text-rose-500">*</span>
                            </label>
                            <span class="text-xs font-bold text-blue-900">
                                Durasi: <span x-text="durasiJam"></span> jam
                            </span>
                        </div>

                        {{-- Grid Slot Rentang Waktu (Ukuran 4 kolom agar pas dan luas) --}}
                        <div class="border border-slate-200 rounded-2xl p-4 space-y-3 bg-slate-50/50">
                            <div class="flex items-center justify-between text-xs">
                                <span class="font-bold text-slate-700">Klik slot untuk memilih rentang jam</span>
                                <span class="text-slate-400 font-medium" x-text="tanggal"></span>
                            </div>

                            <p x-show="loading" class="text-center text-slate-400 py-6 text-xs font-semibold">Memuat ketersediaan slot...</p>
                            <p x-show="!loading && slots.length === 0" class="text-center text-slate-400 py-6 text-xs font-semibold">Pilih fasilitas terlebih dahulu untuk menampilkan slot.</p>

                            {{-- Grid 4 kolom agar tampilannya luas dan rapi --}}
                            <div x-show="!loading && slots.length > 0"
                                 class="grid grid-cols-2 sm:grid-cols-4 gap-2.5 max-h-72 overflow-y-auto pr-1">
                                <template x-for="(s, idx) in slots" :key="idx">
                                    <button type="button" @click="pilihSlot(s)" :disabled="!s.available"
                                            :class="!s.available
                                                ? 'bg-slate-100/80 border-slate-200 text-slate-400 cursor-not-allowed'
                                                : (isSelected(s)
                                                    ? 'bg-blue-900 border-blue-900 text-white shadow-md'
                                                    : 'bg-white border-slate-200 text-slate-800 hover:border-blue-900 shadow-2xs')"
                                            class="p-3 rounded-xl border text-center transition flex flex-col justify-center items-center min-h-[52px]">
                                        <span class="block font-black text-xs" x-text="s.start + ' – ' + s.end"></span>
                                        <span class="block text-[10px] font-semibold mt-0.5 opacity-90"
                                              x-text="!s.available ? 'Terisi' : (isSelected(s) ? 'Dipilih' : 'Tersedia')"></span>
                                    </button>
                                </template>
                            </div>

                            <div class="flex items-center justify-between text-[11px] font-bold pt-2 border-t border-slate-200/60 text-slate-600">
                                <span class="text-emerald-600 flex items-center gap-1">● <span x-text="slotTersedia"></span> Slot Tersedia</span>
                                <span class="text-slate-400 flex items-center gap-1">● <span x-text="slotTerisi"></span> Slot Terisi</span>
                            </div>
                        </div>

                        {{-- Status Pilihan --}}
                        <div x-show="rangeValid && rangeTersedia" x-cloak
                             class="p-3 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl text-xs font-bold">
                            ✓ Rentang waktu <span x-text="start"></span> – <span x-text="end"></span> WIB (<span x-text="durasiJam"></span> jam) siap diajukan.
                        </div>
                        <div x-show="rangeValid && !rangeTersedia && slots.length > 0" x-cloak
                             class="p-3 bg-rose-50 border border-rose-200 text-rose-800 rounded-xl text-xs font-bold">
                            ✗ Ada slot terisi di dalam rentang waktu yang Anda pilih. Silakan pilih ulang.
                        </div>
                    </div>

                    {{-- 4. TUJUAN --}}
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="block text-sm font-bold text-slate-700">
                                4. Tujuan Penggunaan <span class="text-rose-500">*</span>
                            </label>
                            <span class="text-xs font-bold text-slate-400">
                                <span x-text="tujuan.length"></span>/500
                            </span>
                        </div>
                        <textarea name="tujuan" x-model="tujuan" rows="3" maxlength="500" required
                                  placeholder="Contoh: Kuliah Pengganti Pemrograman Web, Rapat Himpunan, Seminar Riset..."
                                  class="w-full p-3 bg-slate-50 border border-slate-300 rounded-xl text-sm text-slate-900 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-900 focus:outline-none resize-none"></textarea>
                        <p class="text-xs text-slate-400 mt-1">Minimal 5 karakter.</p>
                    </div>

                    {{-- AKSI --}}
                    <div class="flex gap-3 pt-3 border-t border-slate-100">
                        <a href="{{ route('landing') }}"
                           class="flex-1 py-3 text-center bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-sm rounded-xl transition">
                            Batal
                        </a>
                        <button type="submit" :disabled="!bisaDiajukan || submitting"
                                :class="bisaDiajukan && !submitting ? 'bg-blue-900 hover:bg-blue-800' : 'bg-slate-300 cursor-not-allowed'"
                                class="flex-1 py-3 text-white font-bold text-sm rounded-xl transition shadow-sm inline-flex items-center justify-center gap-2">
                            <svg x-show="submitting" x-cloak class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                            </svg>
                            <span x-text="submitting ? 'Mengirim...' : 'Ajukan Permohonan'"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('reservasiForm', (cfg) => ({
        facilityId: cfg.facilityId,
        tanggal: cfg.tanggal,
        today: cfg.today,
        besok: cfg.besok,
        start: cfg.start || '',
        end: cfg.end || '',
        tujuan: '',
        slots: [],
        loading: false,
        selectingStart: true,

        init() {
            this.loadSlots();
            this.$watch('tanggal', () => {
                this.start = '';
                this.end = '';
                this.selectingStart = true;
                this.loadSlots();
            });
        },

        toMin(t) {
            if (!t) return 0;
            const p = t.split(':');
            return parseInt(p[0]) * 60 + parseInt(p[1]);
        },

        loadSlots() {
            if (!this.facilityId) { this.slots = []; return; }
            this.loading = true;
            const url = cfg.availUrl.replace('__ID__', this.facilityId) + '?tanggal=' + this.tanggal;
            
            fetch(url, { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' })
                .then(r => r.json())
                .then(data => {
                    const isToday = (this.tanggal === this.today);
                    const [currH, currM] = cfg.currentTime.split(':').map(Number);
                    const currentTotalMin = (currH * 60) + currM;
                    const minAllowedMin = currentTotalMin + 120; // Buffer minimal 2 jam

                    this.slots = (data.slots || []).map(s => {
                        let isAvailable = s.is_available;

                        if (isToday && isAvailable) {
                            const [jam, menit] = s.start.split(':').map(Number);
                            const slotTotalMin = (jam * 60) + menit;
                            if (slotTotalMin < minAllowedMin) {
                                isAvailable = false;
                            }
                        }

                        return {
                            start: s.start,
                            end: s.end,
                            available: isAvailable
                        };
                    });
                })
                .catch(() => { this.slots = []; })
                .finally(() => { this.loading = false; });
        },

        get slotTersedia() { return this.slots.filter(s => s.available).length; },
        get slotTerisi() { return this.slots.filter(s => !s.available).length; },

        get durasiJam() {
            if (!this.start || !this.end) return 0;
            const d = (this.toMin(this.end) - this.toMin(this.start)) / 60;
            return d > 0 ? d : 0;
        },

        get rangeValid() {
            return this.start && this.end && this.toMin(this.end) > this.toMin(this.start);
        },

        get rangeTersedia() {
            if (!this.rangeValid || this.slots.length === 0) return false;
            const a = this.toMin(this.start);
            const b = this.toMin(this.end);
            return this.slots
                .filter(s => this.toMin(s.start) >= a && this.toMin(s.end) <= b)
                .every(s => s.available);
        },

        get bisaDiajukan() {
            return this.facilityId
                && this.rangeValid
                && this.rangeTersedia
                && this.tujuan.trim().length >= 5;
        },

        isSelected(s) {
            if (!this.start || !this.end) return false;
            return this.toMin(s.start) >= this.toMin(this.start)
                && this.toMin(s.end) <= this.toMin(this.end);
        },

        pilihSlot(s) {
            if (!s.available) return;
            const slotMin = this.toMin(s.start);
            const startMin = this.toMin(this.start);

            if (this.selectingStart || !this.start || slotMin < startMin) {
                this.start = s.start;
                this.end = s.end;
                this.selectingStart = false;
            } else {
                const valid = this.slots
                    .filter(item => this.toMin(item.start) >= startMin && this.toMin(item.end) <= this.toMin(s.end))
                    .every(item => item.available);

                if (valid) {
                    this.end = s.end;
                    this.selectingStart = true;
                } else {
                    this.start = s.start;
                    this.end = s.end;
                    this.selectingStart = false;
                }
            }
        },

        gantiFasilitas(id) {
            window.location.href = cfg.createUrl + '?facility_id=' + id + '&tanggal=' + this.tanggal;
        }
    }));
});
</script>
@endpush