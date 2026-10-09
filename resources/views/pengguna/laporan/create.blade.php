@extends('layouts.app')

@section('title', 'Laporkan Kerusakan')

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-6"
     x-data="laporanForm({{ Illuminate\Support\Js::from($selectedFacilityId ?? '') }})">

    {{-- BACK --}}
    <a href="{{ route('pengguna.dashboard') }}"
       class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-slate-200 hover:border-blue-900 hover:bg-blue-50 text-slate-700 hover:text-blue-900 text-sm font-bold rounded-xl transition shadow-sm group mb-6">
        <svg class="w-4 h-4 group-hover:-translate-x-0.5 transition" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
        </svg>
        Kembali ke Panel
    </a>

    {{-- HEADER --}}
    <div class="mb-6">
        <h1 class="text-2xl md:text-3xl font-black text-slate-900">Laporkan Kerusakan Fasilitas</h1>
        <p class="text-sm text-slate-500 mt-1">Bantu kami menjaga fasilitas kampus tetap layak digunakan.</p>
    </div>

    {{-- ERROR --}}
    @if($errors->any())
        <div class="mb-5 px-4 py-3 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-sm">
            <div class="flex items-start gap-2">
                <svg class="w-4 h-4 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                <ul class="list-disc list-inside space-y-0.5 font-medium">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <form action="{{ route('pengguna.laporan.store') }}" method="POST" enctype="multipart/form-data"
          @submit="if (!confirm('Kirim laporan kerusakan ini? Pastikan data yang Anda isi sudah benar.')) { $event.preventDefault(); return; } submitting = true"
          class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200 space-y-6">
        @csrf

        {{-- 1. FASILITAS --}}
        <div class="relative">
            <label class="block text-sm font-bold text-slate-700 mb-1.5">
                Fasilitas / Ruangan <span class="text-rose-500">*</span>
            </label>

            <input type="hidden" name="facility_id" :value="selectedId" required>

            <div class="relative">
                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2M5 21H3m16 0h-5m-9 0h5M9 7h1m-1 4h1m-1 4h1m4-8h1m-1 4h1m-1 4h1M9 21v-4a1 1 0 011-1h4a1 1 0 011 1v4"/>
                    </svg>
                </span>
                <input type="text" x-model="search" @focus="open = true" @input="open = true"
                       placeholder="Cari nama ruangan atau fasilitas..."
                       class="w-full pl-10 pr-10 py-3 bg-slate-50 border border-slate-300 rounded-xl text-sm text-slate-900 font-medium focus:ring-2 focus:ring-blue-500/20 focus:border-blue-900 focus:outline-none">
                <div @click="open = !open" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 cursor-pointer select-none">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                    </svg>
                </div>
            </div>

            {{-- Dropdown --}}
            <div x-show="open" @click.outside="open = false" x-cloak
                 class="absolute z-50 w-full mt-1 bg-white border border-slate-200 rounded-xl shadow-lg max-h-56 overflow-y-auto">
                <template x-for="item in filteredFacilities" :key="item.id">
                    <div @click="selectItem(item)"
                         class="p-3 hover:bg-blue-50 cursor-pointer text-sm font-medium text-slate-800 transition border-b border-slate-50 last:border-none flex items-center justify-between">
                        <span x-text="item.nama_fasilitas"></span>
                        <span class="text-xs text-slate-400" x-text="item.lokasi"></span>
                    </div>
                </template>
                <div x-show="filteredFacilities.length === 0" class="p-3 text-slate-400 text-xs text-center">
                    Fasilitas tidak ditemukan
                </div>
            </div>

            {{-- Preview --}}
            <div x-show="selectedFacility" x-cloak
                 class="mt-3 flex items-center gap-3 p-3 bg-slate-50 border border-slate-200 rounded-xl">
                <div class="w-16 h-16 rounded-lg bg-slate-200 flex items-center justify-center shrink-0 overflow-hidden">
                    <template x-if="selectedFacility?.foto">
                        <img :src="selectedFacility.foto" class="w-full h-full object-cover" alt="">
                    </template>
                    <template x-if="!selectedFacility?.foto">
                        <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                    </template>
                </div>
                <div class="min-w-0">
                    <p class="font-bold text-slate-900 text-sm truncate" x-text="selectedFacility?.nama_fasilitas"></p>
                    <p class="text-xs text-slate-500">
                        <span x-text="selectedFacility?.lokasi"></span> · Kapasitas <span x-text="selectedFacility?.kapasitas"></span> orang
                    </p>
                </div>
            </div>
        </div>

        {{-- 2. KATEGORI --}}
        <div>
            <label class="block text-sm font-bold text-slate-700 mb-1.5">
                Kategori Kendala <span class="text-rose-500">*</span>
            </label>
            <input type="hidden" name="kategori_laporan" :value="kategori" required>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                <template x-for="opt in kategoriOptions" :key="opt">
                    <button type="button" @click="kategori = opt"
                            class="py-2.5 px-3 rounded-xl text-xs font-bold border transition flex items-center justify-center gap-1.5"
                            :class="kategori === opt
                                ? 'bg-blue-900 border-blue-900 text-white shadow-sm'
                                : 'bg-white border-slate-300 text-slate-600 hover:border-blue-900'">
                        <svg x-show="kategori === opt" class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span x-text="opt"></span>
                    </button>
                </template>
            </div>
        </div>

        {{-- 3. DESKRIPSI --}}
        <div>
            <label class="block text-sm font-bold text-slate-700 mb-1.5">
                Deskripsi Permasalahan <span class="text-rose-500">*</span>
            </label>
            <textarea name="deskripsi" x-model="deskripsi" maxlength="1000" rows="4" required
                      placeholder="Jelaskan detail kendala yang Anda temukan (min. 10 karakter)..."
                      class="w-full p-3 bg-slate-50 border border-slate-300 rounded-xl text-sm text-slate-900 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-900 focus:outline-none resize-none"></textarea>
            <div class="flex items-center justify-between mt-1">
                <p class="text-xs text-slate-400">Min. 10 · Maks. 1000 karakter</p>
                <p class="text-xs font-bold" :class="deskripsi.length > 1000 ? 'text-rose-500' : 'text-slate-400'">
                    <span x-text="deskripsi.length"></span>/1000
                </p>
            </div>
        </div>

        {{-- 4. FOTO --}}
        <div>
            <label class="block text-sm font-bold text-slate-700 mb-1.5">
                Foto Bukti <span class="text-slate-400 font-normal">(Sangat disarankan)</span>
            </label>
            <label class="inline-flex items-center gap-2 px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl cursor-pointer transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14M14 8h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                Unggah Foto
                <input type="file" name="foto" accept="image/jpeg,image/png,image/webp" class="hidden" @change="onFileChange($event)">
            </label>
            <p class="mt-1 text-xs text-slate-400">Format JPG, PNG, WEBP · Maks 2 MB</p>

            <div x-show="fileName" x-cloak
                 class="mt-3 flex items-center gap-2 text-xs font-bold text-slate-600 bg-emerald-50 border border-emerald-200 rounded-xl px-3 py-2">
                <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span x-text="fileName"></span>
            </div>
        </div>

        {{-- AKSI --}}
        <div class="flex items-center justify-between pt-4 border-t border-slate-100">
            <button type="button" @click="resetForm($event.target.closest('form'))"
                    class="text-xs font-bold text-slate-500 hover:text-slate-700 transition">
                Bersihkan
            </button>
            <button type="submit" :disabled="submitting"
                    class="inline-flex items-center gap-2 px-5 py-3 bg-rose-600 hover:bg-rose-700 disabled:opacity-60 text-white font-bold text-sm rounded-xl shadow-sm transition">
                <svg x-show="!submitting" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                </svg>
                <svg x-show="submitting" x-cloak class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                </svg>
                <span x-text="submitting ? 'Mengirim...' : 'Kirim Laporan'"></span>
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
function laporanForm(initialFacilityId) {
    return {
        open: false,
        search: '',
        selectedId: initialFacilityId || '',
        submitting: false,
        facilities: {{ Illuminate\Support\Js::from(
            $facilities->map(fn ($f) => [
                'id' => (string) $f->id,
                'nama_fasilitas' => $f->nama_fasilitas,
                'lokasi' => $f->lokasi,
                'kapasitas' => $f->kapasitas,
                'foto' => $f->foto_url ?? null,
            ])->values()->all()
        ) }},
        kategori: '',
        kategoriOptions: ['Kerusakan', 'Kebersihan', 'Fasilitas', 'Lainnya'],
        deskripsi: '',
        fileName: '',
        init() {
            if (this.selectedId) {
                const found = this.facilities.find(f => f.id == this.selectedId);
                if (found) this.search = found.nama_fasilitas;
            }
        },
        get filteredFacilities() {
            if (!this.search) return this.facilities;
            const q = this.search.toLowerCase();
            return this.facilities.filter(f => f.nama_fasilitas.toLowerCase().includes(q));
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
        },
        resetForm(formEl) {
            this.selectedId = '';
            this.search = '';
            this.kategori = '';
            this.deskripsi = '';
            this.fileName = '';
            if (formEl) formEl.reset();
        }
    }
}
</script>
@endpush