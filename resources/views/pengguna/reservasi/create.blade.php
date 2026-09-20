@extends('layouts.app')

@section('content')
@php
    $jamMulaiOptions  = ['07:00','07:30','08:00','08:30','09:00','09:30','10:00','10:30','11:00','11:30','12:00','12:30','13:00','13:30','14:00','14:30','15:00','15:30','16:00','16:30','17:00','17:30','18:00','18:30','19:00','19:30'];
    $jamSelesaiOptions = ['07:30','08:00','08:30','09:00','09:30','10:00','10:30','11:00','11:30','12:00','12:30','13:00','13:30','14:00','14:30','15:00','15:30','16:00','16:30','17:00','17:30','18:00','18:30','19:00','19:30','20:00'];
@endphp

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8"
    x-data="{
        facilityId: '{{ $facility->id ?? '' }}',
        tanggal: '{{ $tanggal }}',
        today: '{{ now()->toDateString() }}',
        besok: '{{ now()->addDay()->toDateString() }}',
        start: '{{ $startTime ?? '08:00' }}',
        end: '{{ $endTime ?? '08:30' }}',
        sesi: 'pagi',
        tujuan: '',
        slots: [],
        loading: false,

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

        get filteredSlots() {
            return this.slots.filter(s => {
                let m = this.toMin(s.start);
                if (this.sesi === 'pagi') return m < 720;
                if (this.sesi === 'siang') return m >= 720 && m < 960;
                return m >= 960;
            });
        },

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

        setDurasi(jam) { this.end = this.toStr(this.toMin(this.start) + (jam * 60)); },

        pilihSlot(s) {
            if (!s.available) return;
            let durasi = this.toMin(this.end) - this.toMin(this.start);
            if (durasi <= 0) durasi = 30;
            this.start = s.start;
            this.end = this.toStr(this.toMin(s.start) + durasi);
            if (!this.rangeTersedia) this.end = s.end;
        },

        gantiFasilitas(id) {
            window.location.href = '{{ route('pengguna.reservasi.create') }}?facility_id=' + id + '&tanggal=' + this.tanggal;
        }
    }">

    <!-- Kembali -->
    <a href="{{ route('landing') }}" class="inline-flex items-center gap-1 text-xs font-bold text-slate-500 hover:text-slate-700 mb-4 transition">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
        </svg>
        Kembali ke Katalog Fasilitas
    </a>

    <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">

        <!-- KIRI: Detail Fasilitas -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden sticky top-20">
                @if($facility)
                    <div class="h-44 bg-slate-100 relative">
                        <img src="{{ $facility->foto }}" alt="{{ $facility->nama_fasilitas }}" class="w-full h-full object-cover">
                        <span class="absolute top-2 left-2 px-2 py-0.5 bg-blue-900/90 text-white rounded text-[10px] font-bold">{{ $facility->tipe }}</span>
                        <span class="absolute top-2 right-2 px-2 py-0.5 bg-emerald-600 text-white rounded text-[10px] font-bold">Tersedia</span>
                    </div>
                    <div class="p-5 space-y-3 text-xs">
                        <div>
                            <h2 class="text-base font-black text-slate-900">{{ $facility->nama_fasilitas }}</h2>
                            <p class="text-slate-500 mt-1">{{ $facility->lokasi }}</p>
                            <p class="text-slate-500">Kapasitas: <span class="font-bold text-slate-700">{{ $facility->kapasitas }} orang</span></p>
                        </div>
                        <p class="text-slate-600 leading-relaxed">{{ $facility->deskripsi }}</p>

                        <div class="bg-blue-50 border border-blue-100 rounded-xl p-3 space-y-1.5">
                            <p class="font-black text-blue-900">Ketentuan Pemakaian</p>
                            <ul class="list-disc list-inside text-blue-800 space-y-1">
                                <li>Slot waktu 30 menit (07.00 - 20.00).</li>
                                <li>Sistem otomatis mengecek bentrok jadwal.</li>
                                <li>Permohonan diverifikasi oleh Petugas Sarpras.</li>
                            </ul>
                        </div>
                    </div>
                @else
                    <div class="p-5 text-xs text-slate-500">
                        <h2 class="text-base font-black text-slate-900 mb-2">Belum Ada Fasilitas Dipilih</h2>
                        <p>Silakan pilih fasilitas pada formulir di samping, atau kembali ke katalog untuk melihat jadwal ketersediaan slot terlebih dahulu.</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- KANAN: Formulir -->
        <div class="lg:col-span-3">
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-5">
                <div>
                    <h1 class="text-xl font-black text-slate-900">Formulir Reservasi Fasilitas</h1>
                    <p class="text-xs text-slate-500 mt-1">Tentukan tanggal, pilih slot waktu yang tersedia, dan tuliskan tujuan kegiatan.</p>
                </div>

                <form action="{{ route('pengguna.reservasi.store') }}" method="POST" class="space-y-5 text-xs">
                    @csrf
                    <input type="hidden" name="facility_id" :value="facilityId">
                    <input type="hidden" name="tanggal" :value="tanggal">
                    <input type="hidden" name="start_time" :value="start">
                    <input type="hidden" name="end_time" :value="end">

                    <!-- Pilih Fasilitas (jika belum dipilih dari katalog) -->
                    <div>
                        <label class="block font-black text-slate-700 mb-1.5">1. Fasilitas yang Dipinjam <span class="text-rose-500">*</span></label>
                        <select @change="gantiFasilitas($event.target.value)"
                            class="w-full p-2.5 bg-slate-50 border border-slate-300 rounded-xl font-semibold text-slate-900 focus:ring-2 focus:ring-blue-900 focus:outline-none">
                            <option value="">-- Pilih Fasilitas --</option>
                            @foreach($facilities as $f)
                                <option value="{{ $f->id }}" {{ ($facility && $facility->id === $f->id) ? 'selected' : '' }}>
                                    {{ $f->nama_fasilitas }} ({{ $f->lokasi }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Tanggal -->
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="block font-black text-slate-700">2. Tanggal Penggunaan <span class="text-rose-500">*</span></label>
                            <div class="flex gap-1.5">
                                <button type="button" @click="tanggal = today"
                                    :class="tanggal === today ? 'bg-blue-900 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                                    class="px-2.5 py-1 rounded-lg font-bold transition">Hari Ini</button>
                                <button type="button" @click="tanggal = besok"
                                    :class="tanggal === besok ? 'bg-blue-900 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                                    class="px-2.5 py-1 rounded-lg font-bold transition">Besok</button>
                            </div>
                        </div>
                        <input type="date" x-model="tanggal" :min="today"
                            class="w-full p-2.5 bg-slate-50 border border-slate-300 rounded-xl text-slate-900 font-semibold focus:ring-2 focus:ring-blue-900 focus:outline-none">
                    </div>

                    <!-- Slot & Jam -->
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <label class="block font-black text-slate-700">3. Pilih Slot &amp; Jam Pemakaian <span class="text-rose-500">*</span></label>
                            <span class="text-[10px] font-bold text-blue-900">Durasi: <span x-text="durasiJam"></span> Jam</span>
                        </div>

                        <!-- Durasi cepat -->
                        <div class="flex flex-wrap items-center gap-1.5">
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mr-1">Durasi Cepat</span>
                            <template x-for="d in [1, 1.5, 2, 3]" :key="d">
                                <button type="button" @click="setDurasi(d)"
                                    :class="durasiJam === d ? 'bg-blue-900 text-white border-blue-900' : 'bg-white text-slate-600 border-slate-300 hover:bg-slate-50'"
                                    class="px-2.5 py-1 rounded-lg border font-bold transition" x-text="d + ' Jam'"></button>
                            </template>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block font-bold text-slate-600 mb-1">Jam Mulai</label>
                                <select x-model="start" class="w-full p-2.5 bg-slate-50 border border-slate-300 rounded-xl text-slate-900 font-semibold focus:outline-none">
                                    @foreach($jamMulaiOptions as $jam)
                                        <option value="{{ $jam }}">{{ $jam }} WIB</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block font-bold text-slate-600 mb-1">Jam Selesai</label>
                                <select x-model="end" class="w-full p-2.5 bg-slate-50 border border-slate-300 rounded-xl text-slate-900 font-semibold focus:outline-none">
                                    @foreach($jamSelesaiOptions as $jam)
                                        <option value="{{ $jam }}">{{ $jam }} WIB</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- Grid Ketersediaan Slot -->
                        <div class="border border-slate-200 rounded-xl p-3 space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-slate-600">Ketersediaan Slot (<span x-text="tanggal"></span>)</span>
                                <div class="flex gap-1.5">
                                    <template x-for="s in ['pagi', 'siang', 'sore']" :key="s">
                                        <button type="button" @click="sesi = s"
                                            :class="sesi === s ? 'bg-blue-900 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                                            class="px-2.5 py-1 rounded-lg font-bold capitalize transition" x-text="s"></button>
                                    </template>
                                </div>
                            </div>

                            <p x-show="loading" class="text-center text-slate-400 py-4 font-bold">Memuat ketersediaan slot...</p>
                            <p x-show="!loading && slots.length === 0" class="text-center text-slate-400 py-4 font-bold">Pilih fasilitas terlebih dahulu untuk melihat slot.</p>

                            <div x-show="!loading && slots.length > 0" class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                                <template x-for="s in filteredSlots" :key="s.start">
                                    <button type="button" @click="pilihSlot(s)" :disabled="!s.available"
                                        :class="!s.available
                                            ? 'bg-rose-50 border-rose-200 text-rose-400 cursor-not-allowed'
                                            : (toMin(s.start) >= toMin(start) && toMin(s.end) <= toMin(end)
                                                ? 'bg-blue-900 border-blue-900 text-white shadow'
                                                : 'bg-white border-slate-200 text-slate-700 hover:border-blue-900')"
                                        class="p-2 rounded-xl border text-center transition">
                                        <span class="block font-black" x-text="s.start"></span>
                                        <span class="block text-[10px] font-bold opacity-80" x-text="s.available ? 'Tersedia' : 'Terisi'"></span>
                                    </button>
                                </template>
                            </div>

                            <div class="flex items-center justify-between text-[10px] font-bold pt-1 border-t border-slate-100">
                                <span class="text-emerald-600"><span x-text="slotTersedia"></span> Slot Tersedia</span>
                                <span class="text-rose-500"><span x-text="slotTerisi"></span> Slot Terisi</span>
                            </div>
                        </div>

                        <!-- Status pilihan -->
                        <div x-show="rangeValid && rangeTersedia" class="p-3 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl font-bold">
                            Slot waktu <span x-text="start"></span> – <span x-text="end"></span> WIB (<span x-text="durasiJam"></span> Jam) tersedia untuk <span x-text="tanggal"></span>.
                        </div>
                        <div x-show="!rangeValid" class="p-3 bg-amber-50 border border-amber-200 text-amber-800 rounded-xl font-bold">
                            Jam selesai harus lebih besar dari jam mulai.
                        </div>
                        <div x-show="rangeValid && !rangeTersedia && slots.length > 0" class="p-3 bg-rose-50 border border-rose-200 text-rose-800 rounded-xl font-bold">
                            Rentang waktu ini bentrok dengan reservasi yang sudah disetujui. Silakan pilih slot lain.
                        </div>
                    </div>

                    <!-- Tujuan -->
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="block font-black text-slate-700">4. Tujuan Penggunaan Fasilitas <span class="text-rose-500">*</span></label>
                            <span class="text-[10px] font-bold text-slate-400"><span x-text="tujuan.length"></span>/200</span>
                        </div>
                        <textarea name="tujuan" x-model="tujuan" rows="3" maxlength="200" required
                            placeholder="Contoh: Rapat Kerja Himpunan Mahasiswa, Seminar Riset, atau Kuliah Pengganti..."
                            class="w-full p-2.5 bg-slate-50 border border-slate-300 rounded-xl text-slate-900 focus:ring-2 focus:ring-blue-900 focus:outline-none"></textarea>
                        <p class="text-[10px] text-slate-400 mt-1">Jelaskan secara ringkas peruntukan kegiatan Anda.</p>
                    </div>

                    <!-- Aksi -->
                    <div class="flex gap-2 pt-2 border-t border-slate-100">
                        <a href="{{ route('landing') }}" class="w-1/3 py-2.5 text-center bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl transition">Batal</a>
                        <button type="submit" :disabled="!bisaDiajukan"
                            :class="bisaDiajukan ? 'bg-blue-900 hover:bg-blue-800' : 'bg-slate-300 cursor-not-allowed'"
                            class="w-2/3 py-2.5 text-white font-bold rounded-xl transition shadow">
                            Ajukan Permohonan Reservasi
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
