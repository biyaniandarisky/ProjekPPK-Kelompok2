@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8" 
    x-data="{ 
        showReservasiModal: {{ (isset($selectedFacilityId) && $selectedFacilityId) ? 'true' : 'false' }}, 
        showRiwayatReservasi: false, 
        closeModals() {
            this.showReservasiModal = false;
            this.showRiwayatReservasi = false;
            if (window.location.hash || window.location.search) {
                history.replaceState(null, null, window.location.pathname);
            }
        }
    }"
    x-init="
        const checkHash = () => {
            if (window.location.hash === '#reservasi-saya') { showRiwayatReservasi = true; }
        };
        checkHash();
        window.addEventListener('hashchange', checkHash);
    ">
    
    <!-- Title Section -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-black text-slate-900">Panel Mahasiswa / Dosen</h1>
            <p class="text-sm text-slate-500">Kelola reservasi dan laporan kendala fasilitas kamu di sini.</p>
        </div>
    </div>

    <!-- Statistik Cards -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl p-5 shadow border border-slate-100">
            <p class="text-xs font-bold text-slate-500">Total Reservasi</p>
            <p class="text-2xl font-black text-blue-900">{{ $stats['total_reservasi'] ?? 0 }}</p>
        </div>
        <div class="bg-white rounded-2xl p-5 shadow border border-slate-100">
            <p class="text-xs font-bold text-slate-500">Disetujui</p>
            <p class="text-2xl font-black text-emerald-600">{{ $stats['disetujui'] ?? 0 }}</p>
        </div>
        <div class="bg-white rounded-2xl p-5 shadow border border-slate-100">
            <p class="text-xs font-bold text-slate-500">Menunggu</p>
            <p class="text-2xl font-black text-amber-500">{{ $stats['menunggu'] ?? 0 }}</p>
        </div>
        <div class="bg-white rounded-2xl p-5 shadow border border-slate-100">
            <p class="text-xs font-bold text-slate-500">Total Laporan</p>
            <p class="text-2xl font-black text-rose-600">{{ $stats['total_laporan'] ?? 0 }}</p>
        </div>
    </div>

    <!-- Kartu Menu Aksi Utama -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Kartu 1: Ajukan Reservasi Baru -->
        <div @click="showReservasiModal = true" 
             class="group relative bg-white rounded-2xl p-6 shadow border border-slate-100 overflow-hidden cursor-pointer hover:shadow-lg transition duration-300 flex flex-col justify-between min-h-[220px]">
            <div class="absolute inset-0 bg-gradient-to-r from-blue-950/90 to-blue-900/80 z-10"></div>
            <img src="https://images.unsplash.com/photo-1497366216548-37526070297c?auto=format&fit=crop&w=800&q=80" 
                 alt="Fasilitas Kampus" 
                 class="absolute inset-0 w-full h-full object-cover group-hover:scale-105 transition duration-500">
            
            <div class="relative z-20 text-white space-y-2">
                <span class="bg-blue-500/30 text-blue-200 text-[10px] font-bold px-2.5 py-1 rounded-full uppercase tracking-wider border border-blue-400/30">
                    Layanan Fasilitas
                </span>
                <h2 class="text-xl font-black leading-snug">Ajukan Reservasi Baru</h2>
                <p class="text-xs text-slate-200 max-w-sm">Butuh ruangan kelas, lab, atau aula untuk kegiatan? Klik di sini untuk mengajukan pinjaman fasilitas.</p>
            </div>

            <div class="relative z-20 pt-4 flex items-center text-xs font-bold text-blue-200 group-hover:text-white transition">
                <span>Isi Form Ajuan</span>
                <svg class="w-4 h-4 ml-1 transform group-hover:translate-x-1 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                </svg>
            </div>
        </div>

        <!-- Kartu 2: Laporkan Kendala Fasilitas -->
        <a href="{{ route('pengguna.laporan.create') }}"
             class="group relative bg-white rounded-2xl p-6 shadow border border-slate-100 overflow-hidden cursor-pointer hover:shadow-lg transition duration-300 flex flex-col justify-between min-h-[220px]">
            <div class="absolute inset-0 bg-gradient-to-r from-rose-950/90 to-rose-900/80 z-10"></div>
            <img src="https://images.unsplash.com/photo-1581092160607-ee22621dd758?auto=format&fit=crop&w=800&q=80" 
                 alt="Perbaikan Fasilitas" 
                 class="absolute inset-0 w-full h-full object-cover group-hover:scale-105 transition duration-500">
            
            <div class="relative z-20 text-white space-y-2">
                <span class="bg-rose-500/30 text-rose-200 text-[10px] font-bold px-2.5 py-1 rounded-full uppercase tracking-wider border border-rose-400/30">
                    Layanan Pengaduan
                </span>
                <h2 class="text-xl font-black leading-snug">Laporkan Kendala Fasilitas</h2>
                <p class="text-xs text-slate-200 max-w-sm">AC mati, proyektor rusak, atau fasilitas kurang layak? Laporkan kendalamu agar tim teknis segera memperbaiki.</p>
            </div>

            <div class="relative z-20 pt-4 flex items-center text-xs font-bold text-rose-200 group-hover:text-white transition">
                <span>Isi Form Laporan</span>
                <svg class="w-4 h-4 ml-1 transform group-hover:translate-x-1 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                </svg>
            </div>
        </a>
    </div>

    <!-- Modal 1: Form Reservasi Baru -->
    <div x-show="showReservasiModal" 
        x-cloak
        class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0">
        
        <div @click.outside="closeModals()" 
            class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-100 relative">
            
            <div class="flex justify-between items-center mb-4">
                <h2 class="font-bold text-slate-800 text-base">Form Ajukan Reservasi</h2>
                <button @click="closeModals()" class="text-slate-400 hover:text-slate-600 p-1">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <form action="{{ route('pengguna.reservasi.store') }}" method="POST" class="space-y-3 text-xs">
                @csrf
                
                <!-- Searchable Dropdown Fasilitas -->
                <div x-data="{ 
                    open: false, 
                    search: '', 
                    selectedId: '{{ $selectedFacilityId ?? '' }}', 
                    facilities: [
                        @foreach($facilities as $facility)
                            { id: '{{ $facility->id }}', name: '{{ addslashes($facility->nama_fasilitas) }} ({{ addslashes($facility->lokasi) }})' },
                        @endforeach
                    ],
                    init() {
                        let found = this.facilities.find(f => f.id == this.selectedId);
                        if (found) this.search = found.name;
                    },
                    get filteredFacilities() {
                        if (!this.search || !this.open) return this.facilities;
                        return this.facilities.filter(f => f.name.toLowerCase().includes(this.search.toLowerCase()));
                    },
                    selectItem(item) {
                        this.selectedId = item.id;
                        this.search = item.name;
                        this.open = false;
                    }
                }" class="relative">
                    <label class="block font-bold text-slate-600 mb-1">Fasilitas</label>
                    
                    <input type="hidden" name="facility_id" :value="selectedId" required>

                    <div class="relative">
                        <input type="text" 
                            x-model="search" 
                            @focus="open = true"
                            @input="open = true"
                            placeholder="Ketik atau pilih fasilitas..." 
                            class="w-full p-2.5 bg-slate-50 border border-slate-300 rounded-xl text-xs text-slate-900 font-semibold focus:ring-2 focus:ring-blue-900 focus:outline-none pr-8">
                        
                        <div @click="open = !open" class="absolute right-3 top-3 text-slate-400 cursor-pointer">
                            ▼
                        </div>
                    </div>

                    <!-- Dropdown List -->
                    <div x-show="open" 
                        @click.outside="open = false" 
                        x-cloak
                        class="absolute z-50 w-full mt-1 bg-white border border-slate-200 rounded-xl shadow-lg max-h-48 overflow-y-auto">
                        <template x-for="item in filteredFacilities" :key="item.id">
                            <div @click="selectItem(item)"
                                class="p-2.5 hover:bg-blue-50 cursor-pointer text-xs font-medium text-slate-800 transition border-b border-slate-50 last:border-none">
                                <span x-text="item.name"></span>
                            </div>
                        </template>
                        <div x-show="filteredFacilities.length === 0" class="p-2.5 text-slate-400 text-xs text-center">
                            Fasilitas tidak ditemukan
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-3">
                    <div>
                        <label class="block font-bold text-slate-600 mb-1">Tanggal</label>
                        <input type="date" name="tanggal" class="w-full p-2.5 bg-slate-50 border border-slate-300 rounded-xl text-slate-900" required>
                    </div>
                    <div>
                        <label class="block font-bold text-slate-600 mb-1">Jam Mulai</label>
                        <input type="time" name="start_time" class="w-full p-2.5 bg-slate-50 border border-slate-300 rounded-xl text-slate-900" required>
                    </div>
                    <div>
                        <label class="block font-bold text-slate-600 mb-1">Jam Selesai</label>
                        <input type="time" name="end_time" class="w-full p-2.5 bg-slate-50 border border-slate-300 rounded-xl text-slate-900" required>
                    </div>
                </div>
                <div>
                    <label class="block font-bold text-slate-600 mb-1">Tujuan Penggunaan</label>
                    <textarea name="tujuan" rows="3" class="w-full p-2.5 bg-slate-50 border border-slate-300 rounded-xl text-slate-900" required></textarea>
                </div>
                <div class="flex gap-2 pt-2">
                    <button type="button" @click="closeModals()" class="w-1/2 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl transition">Batal</button>
                    <button type="submit" class="w-1/2 py-2.5 bg-blue-900 hover:bg-blue-800 text-white font-bold rounded-xl transition">Kirim Reservasi</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal 3: Riwayat Reservasi Saya -->
    <div x-show="showRiwayatReservasi" 
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div @click.outside="closeModals()" 
             class="bg-white rounded-2xl max-w-4xl w-full p-6 shadow-2xl border border-slate-100 space-y-4">
            <div class="flex justify-between items-center border-b pb-3">
                <h3 class="font-black text-slate-900 text-base">Reservasi Saya</h3>
                <button @click="closeModals()" class="text-slate-400 hover:text-slate-600">✕</button>
            </div>
            <div class="overflow-x-auto max-h-[60vh]">
                <table class="w-full text-xs text-left">
                    <thead class="bg-slate-50 text-slate-400 uppercase text-[10px]">
                        <tr>
                            <th class="py-3 px-4">Fasilitas</th>
                            <th class="py-3 px-4">Tanggal</th>
                            <th class="py-3 px-4">Jam</th>
                            <th class="py-3 px-4">Tujuan</th>
                            <th class="py-3 px-4">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($myReservations ?? [] as $r)
                            <tr>
                                <td class="py-3 px-4 font-bold">{{ $r->facility->nama_fasilitas ?? '-' }}</td>
                                <td class="py-3 px-4">{{ $r->tanggal }}</td>
                                <td class="py-3 px-4">{{ \Carbon\Carbon::parse($r->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($r->end_time)->format('H:i') }}</td>
                                <td class="py-3 px-4 max-w-xs">{{ $r->tujuan }}</td>
                                <td class="py-3 px-4">
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold
                                        @if(in_array(strtolower($r->status), ['approved', 'disetujui'])) bg-emerald-100 text-emerald-700
                                        @elseif(in_array(strtolower($r->status), ['pending', 'menunggu'])) bg-amber-100 text-amber-700
                                        @else bg-rose-100 text-rose-700 @endif">
                                        {{ ucfirst($r->status) }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="py-6 text-center text-slate-400">Belum ada riwayat reservasi.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection