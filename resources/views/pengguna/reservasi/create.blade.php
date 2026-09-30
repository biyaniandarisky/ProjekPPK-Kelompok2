@extends('layouts.app')

@section('content')
@php
    $jamMulaiOptions   = ['07:00','07:30','08:00','08:30','09:00','09:30','10:00','10:30','11:00','11:30','12:00','12:30','13:00','13:30','14:00','14:30','15:00','15:30','16:00','16:30','17:00','17:30','18:00','18:30','19:00','19:30'];
    $jamSelesaiOptions = ['07:30','08:00','08:30','09:00','09:30','10:00','10:30','11:00','11:30','12:00','12:30','13:00','13:30','14:00','14:30','15:00','15:30','16:00','16:30','17:00','17:30','18:00','18:30','19:00','19:30','20:00'];
@endphp

<div class="max-w-6xl mx-auto px-4 py-3 space-y-2.5"
    x-data="{
        facilityId: '{{ $facility->id ?? '' }}',
        tanggal: '{{ $tanggal }}',
        today: '{{ now()->toDateString() }}',
        besok: '{{ now()->addDay()->toDateString() }}',
        start: '{{ $startTime ?? '08:00' }}',
        end: '{{ $endTime ?? '08:30' }}',
        tujuan: '',
        slots: [],
        loading: false,
        selectingStart: true, // Marker acuan klik (true = pilih start, false = pilih end)

        init() {
            this.loadSlots();
            this.$watch('tanggal', () => this.loadSlots());
        },

        toMin(t) { let p = t.split(':'); return (parseInt(p[0]) * 60) + parseInt(p[1]); },
        toStr(m) {
            if (m > 1200) m = 1200;
            let h = Math.floor(m / 60), i = m % 60;
            return String(h).padStart(2, '0') + ':' + String(i).padStart(2, '0');
        },

        loadSlots() {
            if (!this.facilityId) { this.slots = []; return; }
            this.loading = true;
            fetch('/fasilitas/' + this.facilityId + '/ketersediaan?tanggal=' + this.tanggal)
                .then(r => r.json())
                .then(data => {
                    this.slots = (data.slots || []).map(s => {
                        return { start: s.start, end: s.end, available: s.is_available };
                    });
                })
                .catch(() => { this.slots = []; })
                .finally(() => { this.loading = false; });
        },

        get slotTersedia() { return this.slots.filter(s => s.available).length; },
        get slotTerisi() { return this.slots.filter(s => !s.available).length; },

        get durasiJam() {
            let d = (this.toMin(this.end) - this.toMin(this.start)) / 60;
            return d > 0 ? d : 0;
        },

        get rangeValid() { return this.toMin(this.end) > this.toMin(this.start); },

        get rangeTersedia() {
            if (!this.rangeValid || this.slots.length === 0) return false;
            let a = this.toMin(this.start), b = this.toMin(this.end);
            return this.slots
                .filter(s => this.toMin(s.start) >= a && this.toMin(s.end) <= b)
                .every(s => s.available);
        },

        get bisaDiajukan() {
            return this.facilityId && this.rangeValid && this.rangeTersedia && this.tujuan.trim().length > 0;
        },

        setDurasi(jam) { 
            this.end = this.toStr(this.toMin(this.start) + (jam * 60)); 
            this.selectingStart = false;
        },

        // LOGIKA BARU PILIH RANGE SLOT (KLIK AWAL & KLIK AKHIR)
        pilihSlot(s) {
            if (!s.available) return;

            let slotStartMin = this.toMin(s.start);
            let currentStartMin = this.toMin(this.start);

            // Jika sedang mode pilih awal ATAU klik jam yang lebih kecil dari jam mulai saat ini
            if (this.selectingStart || slotStartMin < currentStartMin) {
                this.start = s.start;
                this.end = s.end; // Default durasi 30 menit
                this.selectingStart = false; // Klik berikutnya untuk tentukan jam selesai
            } else {
                // Klik kedua: tentukan jam selesai
                let potentialEnd = s.end;
                let rangeValid = this.slots
                    .filter(item => this.toMin(item.start) >= currentStartMin && this.toMin(item.end) <= this.toMin(potentialEnd))
                    .every(item => item.available);

                if (rangeValid) {
                    this.end = potentialEnd;
                    this.selectingStart = true; // Reset kembali untuk memilih rentang baru jika diklik lagi
                } else {
                    // Jika ada slot bentrok di tengah-tengahnya, reset jam mulai ke slot baru yang diklik
                    this.start = s.start;
                    this.end = s.end;
                    this.selectingStart = false;
                }
            }
        },

        gantiFasilitas(id) {
            window.location.href = '{{ route('pengguna.reservasi.create') }}?facility_id=' + id + '&tanggal=' + this.tanggal;
        }
    }">

    <!-- Navigation Header Bar -->
    <div class="flex items-center justify-between border-b border-slate-200 pb-2">
        <a href="{{ route('landing') }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-slate-600 hover:text-slate-900 transition">
            <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
            </svg>
            Kembali ke Katalog
        </a>
        <h1 class="text-sm font-black text-slate-900 tracking-tight">Formulir Permohonan Reservasi</h1>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-5 gap-4">

        <!-- KIRI: Detail Fasilitas Ringkas -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-xl border border-slate-200 shadow-2xs overflow-hidden sticky top-16">
                @if($facility)
                    <div class="h-28 bg-slate-100 relative">
                        <img src="{{ $facility->foto }}" alt="{{ $facility->nama_fasilitas }}" class="w-full h-full object-cover">
                        <span class="absolute top-2 left-2 px-2 py-0.5 bg-[#0f2540]/90 text-white rounded text-[9px] font-black uppercase tracking-wider">{{ $facility->tipe }}</span>
                        <span class="absolute top-2 right-2 px-2 py-0.5 bg-emerald-600 text-white rounded text-[9px] font-black uppercase tracking-wider">Tersedia</span>
                    </div>

                    <div class="p-3 space-y-2 text-xs">
                        <div>
                            <h2 class="text-sm font-black text-slate-900 leading-snug">{{ $facility->nama_fasilitas }}</h2>
                            <p class="text-[11px] text-slate-500">📍 {{ $facility->lokasi }} &bull; Kapasitas: <span class="font-bold text-slate-700">{{ $facility->kapasitas }} Org</span></p>
                        </div>

                        <p class="text-[11px] text-slate-600 leading-tight line-clamp-2">{{ $facility->deskripsi }}</p>

                        <div class="bg-blue-50/70 border border-blue-100 rounded-lg p-2 space-y-1 text-[10px]">
                            <p class="font-black text-[#0f2540]">Ketentuan Pemakaian</p>
                            <ul class="list-disc list-inside text-slate-700 space-y-0.5 font-medium">
                                <li>Slot interval 30 menit (07.00 - 20.00 WIB).</li>
                                <li>Sistem memvalidasi bentrok jadwal secara otomatis.</li>
                                <li>Diverifikasi langsung oleh Tim Petugas Sarpras.</li>
                            </ul>
                        </div>
                    </div>
                @else
                    <div class="p-4 text-xs text-slate-500 space-y-1">
                        <h2 class="text-xs font-black text-slate-900">Belum Ada Fasilitas Dipilih</h2>
                        <p class="text-[11px] text-slate-500 leading-normal">Silakan pilih salah satu fasilitas pada formulir untuk memuat info ketersediaan slot.</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- KANAN: Formulir Compact -->
        <div class="lg:col-span-3">
            <div class="bg-white rounded-xl border border-slate-200 shadow-2xs p-4 space-y-3">

                <form action="{{ route('pengguna.reservasi.store') }}" method="POST" class="space-y-3 text-xs">
                    @csrf
                    <input type="hidden" name="facility_id" :value="facilityId">
                    <input type="hidden" name="tanggal" :value="tanggal">
                    <input type="hidden" name="start_time" :value="start">
                    <input type="hidden" name="end_time" :value="end">

                    <!-- 1. Pilih Fasilitas -->
                    <div>
                        <label class="block font-black text-slate-800 mb-1">1. Fasilitas yang Dipinjam <span class="text-rose-500">*</span></label>
                        <select @change="gantiFasilitas($event.target.value)"
                            class="w-full p-2 bg-slate-50 border border-slate-300 rounded-lg font-bold text-slate-900 focus:ring-2 focus:ring-[#0f2540] focus:outline-none text-xs">
                            <option value="">-- Pilih Fasilitas Kampus --</option>
                            @foreach($facilities as $f)
                                <option value="{{ $f->id }}" {{ ($facility && $facility->id === $f->id) ? 'selected' : '' }}>
                                    {{ $f->nama_fasilitas }} ({{ $f->lokasi }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- 2. Tanggal Penggunaan -->
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label class="block font-black text-slate-800">2. Tanggal Penggunaan <span class="text-rose-500">*</span></label>
                            <div class="flex gap-1 text-[10px]">
                                <button type="button" @click="tanggal = today"
                                    :class="tanggal === today ? 'bg-[#0f2540] text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                                    class="px-2 py-0.5 rounded-md font-bold transition">Hari Ini</button>
                                <button type="button" @click="tanggal = besok"
                                    :class="tanggal === besok ? 'bg-[#0f2540] text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                                    class="px-2 py-0.5 rounded-md font-bold transition">Besok</button>
                            </div>
                        </div>
                        <input type="date" x-model="tanggal" :min="today"
                            class="w-full p-2 bg-slate-50 border border-slate-300 rounded-lg text-slate-900 font-bold focus:ring-2 focus:ring-[#0f2540] focus:outline-none text-xs">
                    </div>

                    <!-- 3. Slot & Jam Pemakaian -->
                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <label class="block font-black text-slate-800">3. Pilih Slot &amp; Jam Pemakaian <span class="text-rose-500">*</span></label>
                            <span class="text-[11px] font-black text-[#0f2540]">Durasi: <span x-text="durasiJam"></span> Jam</span>
                        </div>

                        <!-- Durasi Cepat -->
                        <div class="flex items-center gap-1.5 text-[10px]">
                            <span class="font-bold text-slate-400 uppercase tracking-wider shrink-0">Durasi:</span>
                            <template x-for="d in [1, 1.5, 2, 3]" :key="d">
                                <button type="button" @click="setDurasi(d)"
                                    :class="durasiJam === d ? 'bg-[#0f2540] text-white border-[#0f2540]' : 'bg-white text-slate-600 border-slate-300 hover:bg-slate-50'"
                                    class="px-2 py-0.5 rounded-md border font-extrabold transition" x-text="d + ' J'"></button>
                            </template>
                        </div>

                        <!-- Dropdown Jam Mulai & Selesai -->
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="block font-bold text-slate-500 text-[10px] mb-0.5">Jam Mulai</label>
                                <select x-model="start" class="w-full p-1.5 bg-slate-50 border border-slate-300 rounded-lg text-slate-900 font-bold focus:outline-none text-xs">
                                    @foreach($jamMulaiOptions as $jam)
                                        <option value="{{ $jam }}">{{ $jam }} WIB</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block font-bold text-slate-500 text-[10px] mb-0.5">Jam Selesai</label>
                                <select x-model="end" class="w-full p-1.5 bg-slate-50 border border-slate-300 rounded-lg text-slate-900 font-bold focus:outline-none text-xs">
                                    @foreach($jamSelesaiOptions as $jam)
                                        <option value="{{ $jam }}">{{ $jam }} WIB</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- Grid Ketersediaan Slot (5 Kolom Pilihan Range) -->
                        <div class="border border-slate-200 rounded-lg p-2.5 space-y-2">
                            <div class="flex items-center justify-between text-[11px]">
                                <span class="font-bold text-slate-700">Pilih Rentang Jam Pemakaian (<span x-text="tanggal"></span>)</span>
                                <span class="text-[10px] text-slate-400 font-medium">Klik jam mulai, lalu klik jam selesai</span>
                            </div>

                            <p x-show="loading" class="text-center text-slate-400 py-2 text-[11px] font-bold">Memuat ketersediaan slot...</p>
                            <p x-show="!loading && slots.length === 0" class="text-center text-slate-400 py-2 text-[11px] font-bold">Pilih fasilitas untuk menampilkan slot.</p>

                            <div x-show="!loading && slots.length > 0" class="grid grid-cols-3 sm:grid-cols-5 gap-1.5 max-h-40 overflow-y-auto pr-1">
                                <template x-for="s in slots" :key="s.start">
                                    <button type="button" @click="pilihSlot(s)" :disabled="!s.available"
                                        :class="!s.available
                                            ? 'bg-rose-50 border-rose-200 text-rose-400 cursor-not-allowed'
                                            : (toMin(s.start) >= toMin(start) && toMin(s.end) <= toMin(end)
                                                ? 'bg-[#0f2540] border-[#0f2540] text-white shadow-2xs'
                                                : 'bg-white border-slate-200 text-slate-700 hover:border-[#0f2540]')"
                                        class="p-1 rounded-lg border text-center transition">
                                        <span class="block font-black text-[10px]" x-text="s.start"></span>
                                        <span class="block text-[8px] font-bold opacity-80" x-text="s.available ? (toMin(s.start) >= toMin(start) && toMin(s.end) <= toMin(end) ? 'Terpilih' : 'Tersedia') : 'Terisi'"></span>
                                    </button>
                                </template>
                            </div>

                            <div class="flex items-center justify-between text-[9px] font-bold pt-1 border-t border-slate-100">
                                <span class="text-emerald-600"><span x-text="slotTersedia"></span> Slot Tersedia</span>
                                <span class="text-rose-500"><span x-text="slotTerisi"></span> Slot Terisi</span>
                            </div>
                        </div>

                        <!-- Status Pilihan Slot -->
                        <div x-show="rangeValid && rangeTersedia" class="p-2 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-lg text-[11px] font-bold">
                            ✓ Slot <span x-text="start"></span> – <span x-text="end"></span> WIB (<span x-text="durasiJam"></span> Jam) berhasil diblok.
                        </div>
                        <div x-show="!rangeValid" class="p-2 bg-amber-50 border border-amber-200 text-amber-800 rounded-lg text-[11px] font-bold">
                            ⚠️ Jam selesai harus lebih besar dari jam mulai.
                        </div>
                        <div x-show="rangeValid && !rangeTersedia && slots.length > 0" class="p-2 bg-rose-50 border border-rose-200 text-rose-800 rounded-lg text-[11px] font-bold">
                            ❌ Ada slot terisi di dalam rentang waktu ini. Silakan pilih rentang waktu lain.
                        </div>
                    </div>

                    <!-- 4. Tujuan Penggunaan -->
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label class="block font-black text-slate-800">4. Tujuan Penggunaan Fasilitas <span class="text-rose-500">*</span></label>
                            <span class="text-[9px] font-bold text-slate-400"><span x-text="tujuan.length"></span>/200</span>
                        </div>
                        <textarea name="tujuan" x-model="tujuan" rows="2" maxlength="200" required
                            placeholder="Contoh: Rapat Kerja Himpunan, Seminar Riset, atau Kuliah Pengganti..."
                            class="w-full p-2 bg-slate-50 border border-slate-300 rounded-lg text-slate-900 focus:ring-2 focus:ring-[#0f2540] focus:outline-none text-xs"></textarea>
                    </div>

                    <!-- Aksi Form -->
                    <div class="flex gap-2 pt-2 border-t border-slate-100">
                        <a href="{{ route('landing') }}" class="w-1/3 py-2 text-center bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-lg transition text-xs">Batal</a>
                        <button type="submit" :disabled="!bisaDiajukan"
                            :class="bisaDiajukan ? 'bg-[#0f2540] hover:bg-[#0b1c31]' : 'bg-slate-300 cursor-not-allowed'"
                            class="w-2/3 py-2 text-white font-extrabold rounded-lg transition shadow-2xs text-xs">
                            Ajukan Permohonan Reservasi
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>
@endsection