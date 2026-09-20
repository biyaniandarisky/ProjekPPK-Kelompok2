@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8"
    x-data="laporanForm({{ Illuminate\Support\Js::from($selectedFacilityId ?? '') }})">

    <!-- Breadcrumb / Back -->
    <a href="{{ route('pengguna.dashboard') }}" class="inline-flex items-center gap-1 text-xs font-bold text-slate-500 hover:text-slate-700 mb-4 transition">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
        </svg>
        Kembali ke Panel
    </a>

    <!-- Header -->
    <div class="flex items-center gap-3 mb-6">
        <div class="w-11 h-11 rounded-2xl bg-rose-100 text-rose-600 flex items-center justify-center shrink-0">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
        </div>
        <div>
            <h1 class="text-2xl font-black text-slate-900">Laporkan Kendala Fasilitas</h1>
            <p class="text-sm text-slate-500">AC mati, proyektor rusak, atau fasilitas kurang layak? Laporkan di sini agar tim teknis segera memperbaiki.</p>
        </div>
    </div>

    <form action="{{ route('pengguna.laporan.store') }}" method="POST" enctype="multipart/form-data"
        @submit="submitting = true" class="bg-white rounded-2xl p-6 shadow border border-slate-100 space-y-6">
        @csrf

        <!-- Fasilitas / Ruangan yang Bermasalah -->
        <div class="relative">
            <label class="block text-sm font-bold text-slate-700 mb-1.5">Fasilitas / Ruangan yang Bermasalah <span class="text-rose-500">*</span></label>

            <input type="hidden" name="facility_id" :value="selectedId" required>

            <div class="relative">
                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2M5 21H3m16 0h-5m-9 0h5M9 7h1m-1 4h1m-1 4h1m4-8h1m-1 4h1m-1 4h1M9 21v-4a1 1 0 011-1h4a1 1 0 011 1v4"/>
                    </svg>
                </span>
                <input type="text"
                    x-model="search"
                    @focus="open = true"
                    @input="open = true"
                    placeholder="Cari nama ruangan atau fasilitas..."
                    class="w-full pl-10 pr-8 py-3 bg-slate-50 border border-slate-300 rounded-xl text-sm text-slate-900 font-semibold focus:ring-2 focus:ring-rose-500 focus:border-rose-500 focus:outline-none">
                <div @click="open = !open" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 cursor-pointer text-xs select-none">▼</div>
            </div>

            <div x-show="open"
                @click.outside="open = false"
                x-cloak
                class="absolute z-50 w-full mt-1 bg-white border border-slate-200 rounded-xl shadow-lg max-h-56 overflow-y-auto">
                <template x-for="item in filteredFacilities" :key="item.id">
                    <div @click="selectItem(item)"
                        class="p-3 hover:bg-rose-50 cursor-pointer text-sm font-medium text-slate-800 transition border-b border-slate-50 last:border-none flex items-center justify-between">
                        <span x-text="item.nama_fasilitas"></span>
                        <span class="text-xs text-slate-400" x-text="item.lokasi"></span>
                    </div>
                </template>
                <div x-show="filteredFacilities.length === 0" class="p-3 text-slate-400 text-xs text-center">
                    Fasilitas tidak ditemukan
                </div>
            </div>

            <!-- Preview kartu fasilitas terpilih -->
            <div x-show="selectedFacility" x-cloak class="mt-3 flex items-center gap-3 p-3 bg-slate-50 border border-slate-100 rounded-xl">
                <img :src="selectedFacility ? selectedFacility.foto : ''" class="w-16 h-16 rounded-lg object-cover shrink-0 bg-slate-200" alt="">
                <div class="min-w-0">
                    <p class="font-bold text-slate-900 text-sm truncate" x-text="selectedFacility ? selectedFacility.nama_fasilitas : ''"></p>
                    <p class="text-xs text-slate-500 flex items-center gap-1">
                        <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a2 2 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        <span x-text="selectedFacility ? selectedFacility.lokasi : ''"></span>
                        <span>&bull; Kapasitas <span x-text="selectedFacility ? selectedFacility.kapasitas : ''"></span> orang</span>
                    </p>
                </div>
            </div>
        </div>

        <!-- Kategori Kendala -->
        <div>
            <label class="block text-sm font-bold text-slate-700 mb-1.5">Kategori Kendala <span class="text-rose-500">*</span></label>
            <input type="hidden" name="kategori_laporan" :value="kategori" required>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                <template x-for="opt in kategoriOptions" :key="opt">
                    <button type="button" @click="kategori = opt"
                        class="py-2.5 px-3 rounded-xl text-xs font-bold border transition flex items-center justify-center gap-1.5"
                        :class="kategori === opt
                            ? 'bg-blue-600 border-blue-600 text-white shadow'
                            : 'bg-white border-slate-300 text-slate-600 hover:border-blue-300'">
                        <svg x-show="kategori === opt" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span x-text="opt"></span>
                    </button>
                </template>
            </div>
        </div>

        <!-- Deskripsi -->
        <div>
            <label class="block text-sm font-bold text-slate-700 mb-1.5">Deskripsi Permasalahan / Kerusakan <span class="text-rose-500">*</span></label>
            <textarea name="deskripsi" x-model="deskripsi" maxlength="150" rows="4" required
                placeholder="Jelaskan detail kendala (contoh: AC bagian depan mati, proyektor berkedip dan warna pudar, kran wastafel bocor, lampu padam di deretan tengah)..."
                class="w-full p-3 bg-slate-50 border border-slate-300 rounded-xl text-sm text-slate-900 focus:ring-2 focus:ring-rose-500 focus:border-rose-500 focus:outline-none resize-none"></textarea>
            <div class="flex items-center justify-between mt-1">
                <p class="text-xs text-slate-400">Deskripsi jelas memudahkan petugas membawa suku cadang yang tepat.</p>
                <p class="text-xs font-bold shrink-0 ml-2" :class="deskripsi.length >= 150 ? 'text-rose-500' : 'text-slate-400'">
                    <span x-text="deskripsi.length"></span>/150
                </p>
            </div>
        </div>

        <!-- Foto Bukti -->
        <div>
            <label class="block text-sm font-bold text-slate-700 mb-1.5">Foto Bukti Kondisi <span class="text-slate-400 font-medium">(Sangat Disarankan)</span></label>

            <div class="flex flex-wrap items-center gap-3">
                <label class="inline-flex items-center gap-2 px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl cursor-pointer transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14M14 8h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    Unggah Foto dari Perangkat
                    <input type="file" name="foto" accept="image/*" class="hidden" @change="onFileChange($event)">
                </label>

                <span class="text-xs text-slate-400">Atau gunakan foto simulasi:</span>
            </div>

            <div class="flex flex-wrap gap-2 mt-2">
                <template x-for="preset in simulasiPresets" :key="preset.label">
                    <button type="button" @click="useSimulasi(preset)"
                        class="px-3 py-1.5 rounded-full text-xs font-bold border transition"
                        :class="simulasiActive === preset.label
                            ? 'bg-amber-500 border-amber-500 text-white'
                            : 'bg-white border-slate-300 text-slate-600 hover:border-amber-400'"
                        x-text="preset.label"></button>
                </template>
            </div>

            <!-- Preview file asli -->
            <div x-show="fileName" x-cloak class="mt-3 flex items-center gap-2 text-xs font-semibold text-slate-600 bg-slate-50 border border-slate-100 rounded-xl px-3 py-2">
                <svg class="w-4 h-4 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span x-text="fileName"></span>
            </div>

            <!-- Info mode simulasi -->
            <div x-show="simulasiActive && !fileName" x-cloak class="mt-3 flex items-center gap-2 text-xs font-semibold text-amber-700 bg-amber-50 border border-amber-100 rounded-xl px-3 py-2">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Mode simulasi dipakai untuk mengisi kategori & deskripsi contoh — foto sungguhan tetap perlu diunggah manual jika tersedia.
            </div>
        </div>

        <!-- Footer Aksi -->
        <div class="flex items-center justify-between pt-2 border-t border-slate-100">
            <button type="button" @click="resetForm($event.target.closest('form'))"
                class="text-xs font-bold text-slate-500 hover:text-slate-700 transition">
                Bersihkan
            </button>
            <button type="submit" :disabled="submitting"
                class="inline-flex items-center gap-2 px-5 py-3 bg-amber-500 hover:bg-amber-600 disabled:opacity-60 text-white font-bold text-sm rounded-xl shadow transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                <span x-text="submitting ? 'Mengirim...' : 'Kirim Laporan Kerusakan'"></span>
            </button>
        </div>
    </form>
</div>

<script>
    function laporanForm(initialFacilityId) {
        return {
            open: false,
            search: '',
            selectedId: initialFacilityId || '',
            submitting: false,
            facilities: {{ Illuminate\Support\Js::from(
                $facilities->map(function ($f) {
                    return [
                        'id' => (string) $f->id,
                        'nama_fasilitas' => $f->nama_fasilitas,
                        'lokasi' => $f->lokasi,
                        'kapasitas' => $f->kapasitas,
                        'foto' => $f->foto,
                    ];
                })->values()->all()
            ) }},
            kategori: '',
            kategoriOptions: ['Kerusakan', 'Kebersihan', 'Fasilitas', 'Lainnya'],
            deskripsi: '',
            fileName: '',
            simulasiActive: '',
            simulasiPresets: [
                { label: 'AC Rusak / Bocor', kategori: 'Kerusakan', deskripsi: 'AC di ruangan ini mati total / bocor dan air menetes ke lantai, perlu dicek segera.' },
                { label: 'Proyektor Mati', kategori: 'Kerusakan', deskripsi: 'Proyektor tidak menyala sama sekali saat dinyalakan, sudah dicoba ganti kabel HDMI.' },
                { label: 'Kursi Rusak', kategori: 'Fasilitas', deskripsi: 'Beberapa kursi di ruangan ini patah / goyang dan berisiko untuk diduduki.' },
                { label: 'Kran Bocor', kategori: 'Kebersihan', deskripsi: 'Kran wastafel bocor terus-menerus sehingga lantai sekitar basah dan licin.' },
            ],
            init() {
                if (this.selectedId) {
                    const found = this.facilities.find(f => f.id == this.selectedId);
                    if (found) this.search = found.nama_fasilitas;
                }
            },
            get filteredFacilities() {
                if (!this.search) return this.facilities;
                return this.facilities.filter(f => f.nama_fasilitas.toLowerCase().includes(this.search.toLowerCase()));
            },
            get selectedFacility() {
                return this.facilities.find(f => f.id == this.selectedId) || null;
            },
            selectItem(item) {
                this.selectedId = item.id;
                this.search = item.nama_fasilitas;
                this.open = false;
            },
            onFileChange(event) {
                const file = event.target.files[0];
                this.fileName = file ? file.name : '';
                if (file) this.simulasiActive = '';
            },
            useSimulasi(preset) {
                this.simulasiActive = preset.label;
                this.kategori = preset.kategori;
                this.deskripsi = preset.deskripsi;
            },
            resetForm(formEl) {
                this.selectedId = '';
                this.search = '';
                this.kategori = '';
                this.deskripsi = '';
                this.fileName = '';
                this.simulasiActive = '';
                if (formEl) formEl.reset();
            }
        }
    }
</script>
@endsection