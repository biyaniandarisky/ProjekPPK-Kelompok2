@extends('layouts.app')

@section('title', 'Panel Admin')

@section('content')
@php
    use Illuminate\Support\Js;
    use Illuminate\Support\Facades\Route;

    $pendingCount = count($pendingUsers);

    // Daftar pengguna (non-petugas). Dikirim dari controller sebagai $pengguna.
    // Fallback: semua user selain admin & petugas (termasuk role NULL).
    $penggunaList = $pengguna ?? \App\Models\User::where(function ($q) {
        $q->whereNull('role')->orWhereNotIn('role', ['admin', 'petugas']);
    })->latest()->get();

    // Helper tampilan status verifikasi
    $svLabel = function ($v) {
        $v = strtolower((string) $v);
        if ($v === '') return 'Aktif';
        if ($v === 'pending') return 'Pending';
        if (str_contains($v, 'tolak') || str_contains($v, 'reject')) return 'Ditolak';
        return 'Terverifikasi';
    };
    $svClass = function ($v) {
        $v = strtolower((string) $v);
        if ($v === 'pending') return 'bg-amber-50 text-amber-700';
        if (str_contains($v, 'tolak') || str_contains($v, 'reject')) return 'bg-rose-50 text-rose-700';
        return 'bg-emerald-50 text-emerald-700';
    };
    $totalPetugas  = count($petugas);
    $totalPengguna = count($penggunaList);

    $input = 'w-full h-11 px-4 border border-slate-200 rounded-lg bg-white text-sm text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-900 transition';

    $badge = [
        'aktif'            => 'bg-emerald-50 text-emerald-700',
        'dalam_perbaikan'  => 'bg-amber-50 text-amber-700',
        'selesai'          => 'bg-blue-50 text-blue-700',
        'nonaktif'         => 'bg-rose-50 text-rose-700',
    ];

    $barColors = [
        'Kerusakan'  => 'bg-rose-600',
        'Kebersihan' => 'bg-amber-500',
        'Fasilitas'  => 'bg-blue-900',
        'Lainnya'    => 'bg-emerald-600',
    ];

    $statusLabel = [
        'aktif'            => 'Aktif',
        'dalam_perbaikan'  => 'Maintenance',
        'selesai'          => 'Selesai Diperbaiki',
        'nonaktif'         => 'Nonaktif',
    ];

    $maxOcc = max(1, max($occupancy ?: [1]));

    // ---- Rekap ----
    $rekapList    = $rekap ?? collect();
    $sumReservasi = $rekapList->sum('reservations_count');
    $sumJam       = round($rekapList->sum('jam_terpakai'), 1);
    $avgOkupansi  = $rekapList->count() ? (int) round($rekapList->avg('okupansi')) : 0;
    $sumLaporan   = $rekapList->sum('reports_count');

    // Nama route export. Cek dengan: php artisan route:list --name=export
    // Kalau route-nya tidak ada, tombol ekspor otomatis disembunyikan (tidak error).
    $exportRoute  = 'admin.export';
    $exportQuery  = request()->only(['start_date', 'end_date', 'facility_id']);

    // Menu sidebar
    $menu = [
        'dashboard'   => ['Dashboard', 'Dashboard Admin'],
        'tambah_akun' => ['Tambah Akun', 'Tambah Akun'],
        'data_akun'   => ['Data Akun', 'Data Akun'],
        'verifikasi'  => ['Verifikasi Pengguna', 'Verifikasi Pengguna'],
        'fasilitas'   => ['Data Fasilitas', 'Data Fasilitas'],
        'rekap'       => ['Rekapitulasi', 'Rekapitulasi & Ekspor'],
    ];

    // Kartu statistik
    $statCards = [
        [
            'label' => 'Total Pengguna',
            'value' => $totalUsers,
            'subtitle' => 'Terdaftar di sistem',
            'page' => 'data_akun',
            'gradient' => 'from-blue-600 to-blue-800',
            'text_color' => 'text-blue-100',
            'icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z',
        ],
        [
            'label' => 'Reservasi Bulan Ini',
            'value' => $reservationsThisMonth,
            'subtitle' => 'Aktif & riwayat',
            'page' => 'rekap',
            'gradient' => 'from-emerald-500 to-emerald-700',
            'text_color' => 'text-emerald-50',
            'icon' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z',
        ],
        [
            'label' => 'Laporan Bulan Ini',
            'value' => $reportsThisMonth,
            'subtitle' => 'Perlu ditindak',
            'page' => 'rekap',
            'gradient' => 'from-amber-400 to-amber-500',
            'text_color' => 'text-amber-50',
            'icon' => 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z',
        ],
        [
            'label' => 'Pending Verifikasi',
            'value' => $pendingCount,
            'subtitle' => 'Menunggu approval',
            'page' => 'verifikasi',
            'gradient' => 'from-rose-500 to-rose-700',
            'text_color' => 'text-rose-50',
            'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z',
        ],
    ];

    // Kartu pintasan di dashboard
    $shortcuts = [
        [
            'title' => 'Tambah Akun',
            'badge' => 'Manajemen User',
            'desc' => 'Daftarkan akun petugas atau pengguna baru langsung tanpa verifikasi.',
            'page' => 'tambah_akun',
            'overlay' => 'from-blue-950/90 to-blue-900/80',
            'badge_color' => 'bg-blue-500/30 text-blue-200 border-blue-400/30',
            'image' => asset('images/reservasi.jpg'),
            'cta' => 'Tambah Akun',
        ],
        [
            'title' => 'Data Akun',
            'badge' => 'Daftar User',
            'desc' => 'Lihat seluruh akun petugas dan pengguna yang terdaftar.',
            'page' => 'data_akun',
            'overlay' => 'from-indigo-950/90 to-indigo-900/80',
            'badge_color' => 'bg-indigo-500/30 text-indigo-200 border-indigo-400/30',
            'image' => asset('images/reservasi.jpg'),
            'cta' => 'Buka Data Akun',
        ],
        [
            'title' => 'Verifikasi Pengguna',
            'badge' => 'Approval Akun',
            'desc' => 'Verifikasi atau tolak registrasi mandiri pengguna baru.',
            'page' => 'verifikasi',
            'overlay' => 'from-amber-950/90 to-amber-900/80',
            'badge_color' => 'bg-amber-500/30 text-amber-200 border-amber-400/30',
            'image' => asset('images/laporan.jpg'),
            'cta' => 'Buka Verifikasi',
        ],
        [
            'title' => 'Data Fasilitas',
            'badge' => 'Master Data',
            'desc' => 'Kelola seluruh data fasilitas kampus: tambah, edit, nonaktifkan.',
            'page' => 'fasilitas',
            'overlay' => 'from-emerald-950/90 to-emerald-900/80',
            'badge_color' => 'bg-emerald-500/30 text-emerald-100 border-emerald-400/30',
            'image' => asset('images/fasilitas.jpg'),
            'cta' => 'Buka Data Fasilitas',
        ],
    ];
@endphp

<div x-data="{
        page: '{{ request('page', session('page', 'dashboard')) }}',
        sidebar: false,
        modal: false,
        editing: null,
        confirmDeletePetugas: null,
        confirmDeletePengguna: null,
        submittingFacility: false,
        blank: { nama_fasilitas:'', tipe:'Ruangan', lokasi:'', kapasitas:'', deskripsi:'', status:'aktif' },
        form: {},
        init() {
            this.form = { ...this.blank };
            this.$watch('sidebar', v => document.body.classList.toggle('overflow-hidden', v));
            // Setiap pindah halaman, langsung kembali ke atas
            this.$watch('page', () => window.scrollTo({ top: 0, behavior: 'auto' }));
        },
        go(target) {
            this.page = target;
            this.sidebar = false;
        },
        newFacility() {
            this.editing = null;
            this.form = { ...this.blank };
            this.modal = true;
        },
        editFacility(f) {
            this.editing = f.id;
            this.form = { ...f };
            this.modal = true;
        },
        get facilityAction() {
            return this.editing
                ? '{{ url('admin/facilities') }}/' + this.editing
                : '{{ route('admin.facilities.store') }}'
        }
     }"
     class="min-h-screen bg-slate-50">

    {{-- OVERLAY MOBILE --}}
    <div x-show="sidebar" x-cloak @click="sidebar = false"
         x-transition:enter="transition-opacity ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-40 lg:hidden"></div>

    {{-- SIDEBAR --}}
    <aside class="fixed inset-y-0 left-0 top-14 z-30 w-64 bg-blue-900 text-slate-200 flex flex-col transition-transform duration-300 lg:translate-x-0"
           :class="sidebar ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'">

        <div class="px-5 py-4 border-b border-white/10">
            <p class="text-[10px] font-bold text-blue-300 uppercase tracking-wider">Panel Admin</p>
            <p class="text-sm font-black text-white mt-0.5">Kelola Sistem</p>
        </div>

        <nav class="flex-1 p-3 space-y-1 text-sm overflow-y-auto">
            @foreach($menu as $key => [$label, $title])
                <button @click="go('{{ $key }}')"
                        :class="page === '{{ $key }}' ? 'bg-white/15 text-white font-bold' : 'text-blue-100 hover:bg-white/10'"
                        class="w-full flex items-center gap-3 px-3.5 py-2.5 rounded-lg transition text-left">
                    @switch($key)
                        @case('dashboard')
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                            @break
                        @case('tambah_akun')
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                            @break
                        @case('data_akun')
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            @break
                        @case('verifikasi')
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            @break
                        @case('fasilitas')
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                            @break
                        @case('rekap')
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                            @break
                    @endswitch
                    <span class="flex-1">{{ $label }}</span>
                    @if($key === 'verifikasi' && $pendingCount > 0)
                        <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-rose-500 text-white">{{ $pendingCount }}</span>
                    @endif
                </button>
            @endforeach
        </nav>

        <div class="px-5 py-3 border-t border-white/10">
            <p class="text-[10px] text-blue-300 leading-relaxed">
                Sistem Reservasi &amp; Pelaporan Fasilitas Kampus
            </p>
        </div>
    </aside>

    {{-- MAIN CONTENT --}}
    <div class="lg:ml-64 pt-14">
        <main class="p-4 sm:p-5 lg:p-6 space-y-5">

            {{-- FLASH --}}
            @if(session('success'))
                <div class="px-4 py-3 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm font-medium flex items-start gap-2">
                    <svg class="w-4 h-4 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if($errors->any())
                <div class="px-4 py-3 rounded-lg bg-rose-50 border border-rose-200 text-rose-800 text-sm font-medium">
                    <ul class="list-disc list-inside space-y-0.5">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- ============ DASHBOARD ============ --}}
            <section x-show="page === 'dashboard'" x-cloak class="space-y-6">
                <div>
                    <h1 class="text-2xl font-black text-slate-900">Dashboard Admin</h1>
                    <p class="text-sm text-slate-500 mt-1">Rekapitulasi keseluruhan aktivitas sistem</p>
                </div>

                <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                    @foreach($statCards as $card)
                        <button type="button" @click="go('{{ $card['page'] }}')"
                                class="group w-full text-left flex items-center gap-3 rounded-2xl p-4 shadow-md bg-gradient-to-br {{ $card['gradient'] }} text-white transition duration-200 hover:shadow-xl hover:-translate-y-0.5 focus:outline-none">
                            <div class="w-11 h-11 rounded-xl bg-white/25 flex items-center justify-center shrink-0 backdrop-blur">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $card['icon'] }}"/>
                                </svg>
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="text-[11px] font-bold {{ $card['text_color'] }} truncate">{{ $card['label'] }}</p>
                                <p class="text-2xl font-black leading-none mt-0.5">{{ $card['value'] }}</p>
                                <p class="text-[10px] font-medium {{ $card['text_color'] }} mt-0.5">{{ $card['subtitle'] }}</p>
                            </div>
                            <svg class="w-4 h-4 shrink-0 opacity-60 group-hover:opacity-100 group-hover:translate-x-0.5 transition" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                            </svg>
                        </button>
                    @endforeach
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
                    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
                        <div class="flex items-center justify-between mb-6">
                            <h3 class="font-black text-slate-900">Okupansi Fasilitas</h3>
                            <span class="text-xs font-bold text-slate-400">Minggu ini</span>
                        </div>
                        <div class="flex items-end justify-between gap-2 h-40">
                            @foreach($occupancy as $day => $count)
                                <div class="flex-1 flex flex-col items-center justify-end h-full gap-2 group">
                                    <span class="text-[10px] font-bold text-slate-600 opacity-0 group-hover:opacity-100 transition">{{ $count }}</span>
                                    <div class="w-full bg-blue-900 hover:bg-blue-800 rounded-t-lg transition-all duration-300 min-h-[4px]"
                                         style="height: {{ $count > 0 ? max(4, round($count / $maxOcc * 100)) : 4 }}%"
                                         title="{{ $count }} reservasi"></div>
                                    <span class="text-xs font-bold text-slate-500">{{ $day }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
                        <div class="flex items-center justify-between mb-5">
                            <h3 class="font-black text-slate-900">Frekuensi Kerusakan</h3>
                            <span class="text-xs font-bold text-slate-400">Per kategori</span>
                        </div>
                        <div class="space-y-4">
                            @forelse($damageFreq as $kategori => $persen)
                                <div>
                                    <div class="flex justify-between text-sm mb-1.5">
                                        <span class="text-slate-700 font-bold">{{ $kategori }}</span>
                                        <span class="font-black text-slate-900">{{ $persen }}%</span>
                                    </div>
                                    <div class="h-2 rounded-full bg-slate-100 overflow-hidden">
                                        <div class="h-full rounded-full {{ $barColors[$kategori] ?? 'bg-slate-500' }} transition-all duration-500"
                                             style="width: {{ $persen }}%"></div>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-8">
                                    <p class="text-sm text-slate-400">Belum ada laporan kerusakan.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-5">
                    @foreach($shortcuts as $item)
                        <button type="button" @click="go('{{ $item['page'] }}')"
                                class="group relative bg-white rounded-2xl p-6 shadow border border-slate-200 overflow-hidden hover:shadow-lg transition duration-300 flex flex-col justify-between min-h-[220px] text-left">
                            <div class="absolute inset-0 bg-gradient-to-r {{ $item['overlay'] }} z-10"></div>
                            <img src="{{ $item['image'] }}" alt="{{ $item['title'] }}"
                                 class="absolute inset-0 w-full h-full object-cover group-hover:scale-105 transition duration-500"
                                 onerror="this.style.display='none'">

                            <div class="relative z-20 text-white space-y-2">
                                <span class="inline-block {{ $item['badge_color'] }} text-[10px] font-bold px-2.5 py-1 rounded-full uppercase tracking-wider border">
                                    {{ $item['badge'] }}
                                </span>
                                <h2 class="text-xl font-black leading-snug">{{ $item['title'] }}</h2>
                                <p class="text-xs text-slate-200 max-w-sm">{{ $item['desc'] }}</p>
                            </div>

                            <div class="relative z-20 pt-4 flex items-center text-xs font-bold text-white/80 group-hover:text-white transition">
                                <span>{{ $item['cta'] }}</span>
                                <svg class="w-4 h-4 ml-1 group-hover:translate-x-1 transition" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                                </svg>
                            </div>
                        </button>
                    @endforeach
                </div>
            </section>

            {{-- ============ TAMBAH AKUN ============ --}}
            <section x-show="page === 'tambah_akun'" x-cloak class="space-y-6">
                <div>
                    <h1 class="text-2xl font-black text-slate-900">Tambah Akun</h1>
                    <p class="text-sm text-slate-500 mt-1">Daftarkan akun petugas atau pengguna baru tanpa proses verifikasi</p>
                </div>

                <form method="POST"
                      x-data="{ role: 'petugas', submitting: false }" @submit="submitting = true"
                      :action="role === 'petugas' ? '{{ route('admin.petugas.store') }}' : '{{ route('admin.pengguna.store') }}'"
                      class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-5">
                    @csrf
                    <input type="hidden" name="page" value="data_akun">

                    <div class="md:col-span-2">
                        <label class="block text-sm font-bold text-slate-700 mb-1.5">Jenis Akun <span class="text-rose-500">*</span></label>
                        <div class="inline-flex p-1 rounded-lg bg-slate-100 gap-1">
                            <button type="button" @click="role = 'petugas'"
                                    :class="role === 'petugas' ? 'bg-blue-900 text-white shadow-sm' : 'text-slate-600 hover:bg-white'"
                                    class="px-5 py-2 rounded-md text-sm font-bold transition">
                                Petugas
                            </button>
                            <button type="button" @click="role = 'pengguna'"
                                    :class="role === 'pengguna' ? 'bg-blue-900 text-white shadow-sm' : 'text-slate-600 hover:bg-white'"
                                    class="px-5 py-2 rounded-md text-sm font-bold transition">
                                Pengguna
                            </button>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-1.5" x-text="role === 'petugas' ? 'Nama Petugas *' : 'Nama Pengguna *'"></label>
                        <input name="name" required placeholder="Nama lengkap" class="{{ $input }}">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-1.5" x-text="role === 'petugas' ? 'NIP *' : 'NIM / NIP *'"></label>
                        <input name="nim_nip" required
                               :placeholder="role === 'petugas' ? 'Nomor induk petugas' : 'NIM atau NIP pengguna'"
                               class="{{ $input }}">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-1.5">Email <span class="text-rose-500">*</span></label>
                        <input type="email" name="email" required placeholder="email@kampus.ac.id" class="{{ $input }}">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-1.5">No. HP</label>
                        <input name="no_hp" placeholder="08xxxxxxxxxx" class="{{ $input }}">
                    </div>

                    <div x-show="role === 'petugas'">
                        <label class="block text-sm font-bold text-slate-700 mb-1.5">Unit / Divisi</label>
                        <select name="unit" :disabled="role !== 'petugas'" class="{{ $input }}">
                            @foreach(['Sarpras','IT','Kebersihan','Keamanan'] as $u)
                                <option>{{ $u }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-1.5">Password Awal <span class="text-rose-500">*</span></label>
                        <input type="password" name="password" required minlength="8" placeholder="Min. 8 karakter" class="{{ $input }}">
                    </div>

                    <div class="md:col-span-2 flex justify-end">
                        <button type="submit" :disabled="submitting"
                                class="inline-flex items-center gap-2 px-5 py-2.5 bg-blue-900 hover:bg-blue-800 disabled:opacity-60 text-white text-sm font-bold rounded-lg transition shadow-sm">
                            <span x-text="submitting ? 'Menyimpan...' : (role === 'petugas' ? 'Daftarkan Petugas' : 'Daftarkan Pengguna')"></span>
                        </button>
                    </div>
                </form>
            </section>

            {{-- ============ DATA AKUN ============ --}}
            <section x-show="page === 'data_akun'" x-cloak
                     x-data="{ tab: 'semua', q: '' }"
                     class="space-y-6">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h1 class="text-2xl font-black text-slate-900">Data Akun</h1>
                        <p class="text-sm text-slate-500 mt-1">Daftar akun petugas dan pengguna yang terdaftar</p>
                    </div>
                    <button type="button" @click="go('tambah_akun')"
                            class="shrink-0 inline-flex items-center gap-2 px-5 py-2.5 bg-blue-900 hover:bg-blue-800 text-white text-sm font-bold rounded-lg transition shadow-sm">
                        Tambah Akun
                    </button>
                </div>

                <div class="flex flex-col sm:flex-row sm:items-center gap-3">
                    <div class="inline-flex p-1 rounded-lg bg-slate-100 gap-1 self-start">
                        <button type="button" @click="tab = 'semua'"
                                :class="tab === 'semua' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-500 hover:text-slate-800'"
                                class="px-4 py-1.5 rounded-md text-xs font-bold transition">
                            Semua ({{ $totalPetugas + $totalPengguna }})
                        </button>
                        <button type="button" @click="tab = 'petugas'"
                                :class="tab === 'petugas' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-500 hover:text-slate-800'"
                                class="px-4 py-1.5 rounded-md text-xs font-bold transition">
                            Petugas ({{ $totalPetugas }})
                        </button>
                        <button type="button" @click="tab = 'pengguna'"
                                :class="tab === 'pengguna' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-500 hover:text-slate-800'"
                                class="px-4 py-1.5 rounded-md text-xs font-bold transition">
                            Pengguna ({{ $totalPengguna }})
                        </button>
                    </div>
                    <input type="search" x-model="q" placeholder="Cari nama, email, atau NIM/NIP..."
                           class="{{ $input }} sm:max-w-xs sm:ml-auto">
                </div>

                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                    <div class="hidden md:block overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead class="bg-slate-50 border-b border-slate-200">
                                <tr>
                                    <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wide">Nama</th>
                                    <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wide">NIM/NIP</th>
                                    <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wide">Email</th>
                                    <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wide">Jenis</th>
                                    <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wide">Unit</th>
                                    <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wide">Status</th>
                                    <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wide text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                {{-- Petugas --}}
                                @foreach($petugas as $p)
                                    <tr class="hover:bg-slate-50"
                                        data-s="{{ mb_strtolower($p->name.' '.$p->email.' '.$p->nim_nip) }}"
                                        x-show="(tab === 'semua' || tab === 'petugas') && $el.dataset.s.includes(q.toLowerCase())">
                                        <td class="px-6 py-4 font-bold text-slate-900">{{ $p->name }}</td>
                                        <td class="px-6 py-4 text-slate-600 font-mono text-xs">{{ $p->nim_nip }}</td>
                                        <td class="px-6 py-4 text-slate-600">{{ $p->email }}</td>
                                        <td class="px-6 py-4">
                                            <span class="px-2.5 py-1 rounded text-[10px] font-bold bg-blue-50 text-blue-700">Petugas</span>
                                        </td>
                                        <td class="px-6 py-4 text-slate-600">{{ $p->unit ?? '—' }}</td>
                                        <td class="px-6 py-4">
                                            <span class="px-2.5 py-1 rounded text-[10px] font-bold bg-emerald-50 text-emerald-700">Aktif</span>
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            <button type="button" @click="confirmDeletePetugas = {{ $p->id }}"
                                                    class="px-3 py-1.5 bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold rounded-lg transition">
                                                Hapus
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach

                                {{-- Pengguna --}}
                                @foreach($penggunaList as $u)
                                    <tr class="hover:bg-slate-50"
                                        data-s="{{ mb_strtolower($u->name.' '.$u->email.' '.($u->nim_nip ?? '')) }}"
                                        x-show="(tab === 'semua' || tab === 'pengguna') && $el.dataset.s.includes(q.toLowerCase())">
                                        <td class="px-6 py-4 font-bold text-slate-900">{{ $u->name }}</td>
                                        <td class="px-6 py-4 text-slate-600 font-mono text-xs">{{ $u->nim_nip ?? '—' }}</td>
                                        <td class="px-6 py-4 text-slate-600">{{ $u->email }}</td>
                                        <td class="px-6 py-4">
                                            <span class="px-2.5 py-1 rounded text-[10px] font-bold bg-emerald-50 text-emerald-700">Pengguna</span>
                                        </td>
                                        <td class="px-6 py-4 text-slate-400">—</td>
                                        <td class="px-6 py-4">
                                            <span class="px-2.5 py-1 rounded text-[10px] font-bold {{ $svClass($u->status_verifikasi ?? null) }}">
                                                {{ $svLabel($u->status_verifikasi ?? null) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-right space-x-2">
                                            @if($u->ktm_path ?? null)
                                                <a href="{{ route('admin.users.ktm', $u->id) }}" target="_blank"
                                                   class="text-blue-900 font-bold hover:underline text-xs">Lihat KTM</a>
                                            @endif
                                            <button type="button" @click="confirmDeletePengguna = {{ $u->id }}"
                                                    class="px-3 py-1.5 bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold rounded-lg transition">
                                                Hapus
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach

                                @if($totalPetugas + $totalPengguna === 0)
                                    <tr>
                                        <td colspan="7" class="px-6 py-16 text-center">
                                            <svg class="w-12 h-12 mx-auto text-slate-300 mb-3" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            </svg>
                                            <p class="text-sm font-bold text-slate-700">Belum ada akun</p>
                                            <p class="text-xs text-slate-400 mt-1">Tambahkan akun lewat menu Tambah Akun</p>
                                        </td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>

                    {{-- Mobile --}}
                    <div class="md:hidden divide-y divide-slate-100">
                        @foreach($petugas as $p)
                            <div class="p-4 space-y-2"
                                 data-s="{{ mb_strtolower($p->name.' '.$p->email.' '.$p->nim_nip) }}"
                                 x-show="(tab === 'semua' || tab === 'petugas') && $el.dataset.s.includes(q.toLowerCase())">
                                <div class="flex items-center justify-between gap-2">
                                    <p class="font-bold text-slate-900 text-sm">{{ $p->name }}</p>
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-blue-50 text-blue-700">Petugas</span>
                                </div>
                                <p class="text-xs text-slate-500 font-mono">{{ $p->nim_nip }}</p>
                                <p class="text-xs text-slate-500 break-all">{{ $p->email }}</p>
                                <p class="text-xs text-slate-500">Unit: {{ $p->unit ?? '—' }}</p>
                                <button type="button" @click="confirmDeletePetugas = {{ $p->id }}"
                                        class="w-full mt-2 px-3 py-2 bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold rounded-lg transition">
                                    Hapus
                                </button>
                            </div>
                        @endforeach

                        @foreach($penggunaList as $u)
                            <div class="p-4 space-y-2"
                                 data-s="{{ mb_strtolower($u->name.' '.$u->email.' '.($u->nim_nip ?? '')) }}"
                                 x-show="(tab === 'semua' || tab === 'pengguna') && $el.dataset.s.includes(q.toLowerCase())">
                                <div class="flex items-center justify-between gap-2">
                                    <p class="font-bold text-slate-900 text-sm">{{ $u->name }}</p>
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-emerald-700">Pengguna</span>
                                </div>
                                <p class="text-xs text-slate-500 font-mono">{{ $u->nim_nip ?? '—' }}</p>
                                <p class="text-xs text-slate-500 break-all">{{ $u->email }}</p>
                                <p class="text-xs text-slate-500">Status:
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $svClass($u->status_verifikasi ?? null) }}">{{ $svLabel($u->status_verifikasi ?? null) }}</span>
                                </p>
                                @if($u->ktm_path ?? null)
                                    <a href="{{ route('admin.users.ktm', $u->id) }}" target="_blank"
                                       class="inline-block text-blue-900 font-bold hover:underline text-xs">Lihat KTM</a>
                                @endif
                                <button type="button" @click="confirmDeletePengguna = {{ $u->id }}"
                                        class="w-full mt-2 px-3 py-2 bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold rounded-lg transition">
                                    Hapus
                                </button>
                            </div>
                        @endforeach

                        @if($totalPetugas + $totalPengguna === 0)
                            <div class="p-8 text-center">
                                <p class="text-sm font-bold text-slate-700">Belum ada akun</p>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Modal Konfirmasi Hapus Petugas --}}
                @foreach($petugas as $p)
                    <div x-show="confirmDeletePetugas === {{ $p->id }}" x-cloak
                         class="fixed inset-0 z-50 flex items-center justify-center p-4">
                        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" @click="confirmDeletePetugas = null"></div>
                        <div class="relative bg-white rounded-2xl shadow-2xl max-w-sm w-full p-6 space-y-4">
                            <h3 class="font-black text-slate-900">Hapus Akun Petugas?</h3>
                            <p class="text-sm text-slate-500">Akun <strong>{{ $p->name }}</strong> akan dihapus permanen.</p>
                            <div class="flex justify-end gap-2 pt-2">
                                <button type="button" @click="confirmDeletePetugas = null" class="px-4 py-2 bg-slate-100 rounded-lg text-sm font-bold">Batal</button>
                                <form action="{{ route('admin.petugas.destroy', $p->id) }}" method="POST">
                                    @csrf @method('DELETE')
                                    <input type="hidden" name="page" value="data_akun">
                                    <button class="px-4 py-2 bg-rose-600 text-white rounded-lg text-sm font-bold">Ya, Hapus</button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach

                {{-- Modal Konfirmasi Hapus Pengguna --}}
                @foreach($penggunaList as $u)
                    <div x-show="confirmDeletePengguna === {{ $u->id }}" x-cloak
                         class="fixed inset-0 z-50 flex items-center justify-center p-4">
                        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" @click="confirmDeletePengguna = null"></div>
                        <div class="relative bg-white rounded-2xl shadow-2xl max-w-sm w-full p-6 space-y-4">
                            <h3 class="font-black text-slate-900">Hapus Akun Pengguna?</h3>
                            <p class="text-sm text-slate-500">Akun <strong>{{ $u->name }}</strong> akan dihapus permanen.</p>
                            <div class="flex justify-end gap-2 pt-2">
                                <button type="button" @click="confirmDeletePengguna = null" class="px-4 py-2 bg-slate-100 rounded-lg text-sm font-bold">Batal</button>
                                <form action="{{ route('admin.pengguna.destroy', $u->id) }}" method="POST">
                                    @csrf @method('DELETE')
                                    <input type="hidden" name="page" value="data_akun">
                                    <button class="px-4 py-2 bg-rose-600 text-white rounded-lg text-sm font-bold">Ya, Hapus</button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            </section>

            {{-- ============ VERIFIKASI ============ --}}
            <section x-show="page === 'verifikasi'" x-cloak
                     x-data="{ rejectId: null }"
                     class="space-y-6">
                <div>
                    <h1 class="text-2xl font-black text-slate-900">Verifikasi Pengguna</h1>
                    <p class="text-sm text-slate-500 mt-1">Verifikasi registrasi mandiri pengguna baru</p>
                </div>

                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden p-6">
                    @if($pendingUsers->isEmpty())
                        <p class="text-center text-sm text-slate-500 py-8">Tidak ada antrean verifikasi.</p>
                    @else
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm text-left">
                                <thead class="bg-slate-50 border-b">
                                    <tr>
                                        <th class="px-4 py-3">Nama</th>
                                        <th class="px-4 py-3">NIM/NIP</th>
                                        <th class="px-4 py-3">Email</th>
                                        <th class="px-4 py-3 text-right">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($pendingUsers as $u)
                                        <tr class="border-b">
                                            <td class="px-4 py-3 font-bold">{{ $u->name }}</td>
                                            <td class="px-4 py-3 font-mono text-xs">{{ $u->nim_nip }}</td>
                                            <td class="px-4 py-3">{{ $u->email }}</td>
                                            <td class="px-4 py-3 text-right whitespace-nowrap space-x-2">
                                                @if($u->ktm_path ?? null)
                                                    <a href="{{ route('admin.users.ktm', $u->id) }}" target="_blank"
                                                       class="text-blue-900 font-bold hover:underline text-xs">Lihat KTM</a>
                                                @endif
                                                <form action="{{ route('admin.users.verify', $u->id) }}" method="POST" class="inline">
                                                    @csrf
                                                    <button class="px-3 py-1 bg-emerald-600 text-white text-xs rounded">Verifikasi</button>
                                                </form>
                                                <button type="button" @click="rejectId = {{ $u->id }}"
                                                        class="px-3 py-1 bg-rose-600 text-white text-xs rounded">Tolak</button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>

                {{-- Modal Tolak (alasan wajib diisi, sesuai validasi controller) --}}
                @foreach($pendingUsers as $u)
                    <div x-show="rejectId === {{ $u->id }}" x-cloak
                         class="fixed inset-0 z-50 flex items-center justify-center p-4">
                        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" @click="rejectId = null"></div>
                        <div class="relative bg-white rounded-2xl shadow-2xl max-w-md w-full p-6 space-y-4">
                            <h3 class="font-black text-slate-900">Tolak Pengajuan?</h3>
                            <p class="text-sm text-slate-500">Akun <strong>{{ $u->name }}</strong> akan ditolak. Alasan akan dikirim sebagai notifikasi.</p>
                            <form action="{{ route('admin.users.reject', $u->id) }}" method="POST" class="space-y-4">
                                @csrf
                                <textarea name="alasan_tolak" required maxlength="250" rows="3"
                                          placeholder="Contoh: Foto KTM tidak terbaca"
                                          class="w-full px-4 py-3 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-900"></textarea>
                                <div class="flex justify-end gap-2">
                                    <button type="button" @click="rejectId = null" class="px-4 py-2 bg-slate-100 rounded-lg text-sm font-bold">Batal</button>
                                    <button class="px-4 py-2 bg-rose-600 text-white rounded-lg text-sm font-bold">Ya, Tolak</button>
                                </div>
                            </form>
                        </div>
                    </div>
                @endforeach
            </section>

            {{-- ============ FASILITAS ============ --}}
            <section x-show="page === 'fasilitas'" x-cloak class="space-y-6">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h1 class="text-2xl font-black text-slate-900">Data Fasilitas</h1>
                        <p class="text-sm text-slate-500 mt-1">Kelola seluruh data fasilitas kampus</p>
                    </div>
                    <button @click="newFacility()"
                            class="px-5 py-2.5 bg-blue-900 hover:bg-blue-800 text-white text-sm font-bold rounded-lg transition shadow-sm">
                        Tambah
                    </button>
                </div>

                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden p-6">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead class="bg-slate-50 border-b">
                                <tr>
                                    <th class="px-4 py-3">Nama</th>
                                    <th class="px-4 py-3">Tipe</th>
                                    <th class="px-4 py-3">Lokasi</th>
                                    <th class="px-4 py-3">Status</th>
                                    <th class="px-4 py-3 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($facilities as $f)
                                    <tr class="border-b">
                                        <td class="px-4 py-3 font-bold">{{ $f->nama_fasilitas }}</td>
                                        <td class="px-4 py-3">{{ $f->tipe }}</td>
                                        <td class="px-4 py-3">{{ $f->lokasi }}</td>
                                        <td class="px-4 py-3">
                                            <span class="px-2 py-0.5 rounded text-xs font-bold {{ $badge[$f->status] ?? '' }}">{{ $statusLabel[$f->status] ?? $f->status }}</span>
                                        </td>
                                        <td class="px-4 py-3 text-right space-x-2">
                                            <button @click="editFacility({{ Js::from($f->only(['id','nama_fasilitas','tipe','lokasi','kapasitas','deskripsi','status'])) }})" class="px-3 py-1 bg-slate-100 text-slate-700 text-xs rounded font-bold">Edit</button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="text-center py-6 text-slate-400">Belum ada fasilitas</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            {{-- ============ REKAP ============ --}}
            <section x-show="page === 'rekap'" x-cloak class="space-y-6">
                <div>
                    <h1 class="text-2xl font-black text-slate-900">Rekapitulasi & Ekspor</h1>
                    <p class="text-sm text-slate-500 mt-1">Rekap okupansi fasilitas dan frekuensi kerusakan</p>
                </div>

                {{-- Filter: nama field harus sama dengan yang dibaca controller (start_date, end_date, facility_id) --}}
                <form method="GET" action="{{ route('admin.dashboard') }}"
                      class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-4">
                    <input type="hidden" name="page" value="rekap">

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-1.5">Dari Tanggal</label>
                            <input type="date" name="start_date" value="{{ request('start_date') }}" class="{{ $input }}">
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-1.5">Sampai Tanggal</label>
                            <input type="date" name="end_date" value="{{ request('end_date') }}" class="{{ $input }}">
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-1.5">Fasilitas</label>
                            <select name="facility_id" class="{{ $input }}">
                                <option value="">Semua Fasilitas</option>
                                @foreach($facilities as $f)
                                    <option value="{{ $f->id }}" @selected((string) request('facility_id') === (string) $f->id)>{{ $f->nama_fasilitas }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <p class="text-xs text-slate-500">
                            Periode: <strong class="text-slate-700">{{ $periode['start'] ?? '-' }}</strong>
                            s/d <strong class="text-slate-700">{{ $periode['end'] ?? '-' }}</strong>
                        </p>
                        <div class="flex flex-wrap gap-2">
                            <a href="{{ route('admin.dashboard', ['page' => 'rekap']) }}"
                               class="px-4 py-2 bg-slate-100 text-slate-700 text-sm font-bold rounded-lg">Reset</a>
                            <button type="submit" class="px-4 py-2 bg-blue-900 text-white text-sm font-bold rounded-lg">Terapkan Filter</button>
                        </div>
                    </div>
                </form>

                {{-- Ringkasan --}}
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4">
                        <p class="text-[11px] font-bold text-slate-500">Total Reservasi</p>
                        <p class="text-2xl font-black text-slate-900 mt-1">{{ $sumReservasi }}</p>
                    </div>
                    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4">
                        <p class="text-[11px] font-bold text-slate-500">Jam Terpakai</p>
                        <p class="text-2xl font-black text-slate-900 mt-1">{{ $sumJam }}</p>
                    </div>
                    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4">
                        <p class="text-[11px] font-bold text-slate-500">Rata-rata Okupansi</p>
                        <p class="text-2xl font-black text-slate-900 mt-1">{{ $avgOkupansi }}%</p>
                    </div>
                    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4">
                        <p class="text-[11px] font-bold text-slate-500">Total Laporan</p>
                        <p class="text-2xl font-black text-slate-900 mt-1">{{ $sumLaporan }}</p>
                    </div>
                </div>

                {{-- Tabel rekap per fasilitas --}}
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-200 flex flex-wrap items-center justify-between gap-3">
                        <h3 class="font-black text-slate-900">Rekap per Fasilitas</h3>
                        @if(Route::has($exportRoute))
                            <div class="flex flex-wrap gap-2">
                                <a href="{{ route($exportRoute, array_merge(['format' => 'csv'], $exportQuery)) }}"
                                   class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-lg transition">CSV</a>
                                <a href="{{ route($exportRoute, array_merge(['format' => 'excel'], $exportQuery)) }}"
                                   class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-lg transition">Excel</a>
                                <a href="{{ route($exportRoute, array_merge(['format' => 'pdf'], $exportQuery)) }}" target="_blank"
                                   class="px-3 py-1.5 bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold rounded-lg transition">PDF</a>
                            </div>
                        @endif
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead class="bg-slate-50 border-b border-slate-200">
                                <tr>
                                    <th class="px-6 py-3 text-xs font-bold text-slate-500 uppercase tracking-wide">Fasilitas</th>
                                    <th class="px-6 py-3 text-xs font-bold text-slate-500 uppercase tracking-wide">Tipe</th>
                                    <th class="px-6 py-3 text-xs font-bold text-slate-500 uppercase tracking-wide text-right">Reservasi</th>
                                    <th class="px-6 py-3 text-xs font-bold text-slate-500 uppercase tracking-wide text-right">Jam</th>
                                    <th class="px-6 py-3 text-xs font-bold text-slate-500 uppercase tracking-wide">Okupansi</th>
                                    <th class="px-6 py-3 text-xs font-bold text-slate-500 uppercase tracking-wide text-right">Laporan</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse($rekapList as $f)
                                    <tr class="hover:bg-slate-50">
                                        <td class="px-6 py-3">
                                            <p class="font-bold text-slate-900">{{ $f->nama_fasilitas }}</p>
                                            <p class="text-xs text-slate-400">{{ $f->lokasi }}</p>
                                        </td>
                                        <td class="px-6 py-3 text-slate-600">{{ $f->tipe }}</td>
                                        <td class="px-6 py-3 text-right font-bold text-slate-900">{{ $f->reservations_count }}</td>
                                        <td class="px-6 py-3 text-right text-slate-600">{{ $f->jam_terpakai }}</td>
                                        <td class="px-6 py-3 min-w-[160px]">
                                            <div class="flex items-center gap-2">
                                                <div class="flex-1 h-2 rounded-full bg-slate-100 overflow-hidden">
                                                    <div class="h-full rounded-full bg-blue-900" style="width: {{ $f->okupansi }}%"></div>
                                                </div>
                                                <span class="text-xs font-black text-slate-700 w-9 text-right">{{ $f->okupansi }}%</span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-3 text-right font-bold {{ $f->reports_count > 0 ? 'text-rose-600' : 'text-slate-400' }}">{{ $f->reports_count }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-6 py-12 text-center text-sm text-slate-400">Tidak ada data untuk filter ini.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

        </main>
    </div>

    {{-- MODAL FASILITAS --}}
    <div x-show="modal" x-cloak class="fixed inset-0 z-50 grid place-items-center p-4 bg-slate-900/50 backdrop-blur-sm">
        <div class="bg-white rounded-2xl w-full max-w-2xl max-h-[92vh] overflow-y-auto shadow-2xl p-6" @click.outside="modal = false">
            <h3 class="font-black text-lg text-slate-900 mb-4" x-text="editing ? 'Edit Fasilitas' : 'Tambah Fasilitas'"></h3>
            <form :action="facilityAction" method="POST" enctype="multipart/form-data" @submit="submittingFacility = true" class="space-y-4">
                @csrf
                <input type="hidden" name="page" value="fasilitas">
                <template x-if="editing"><input type="hidden" name="_method" value="PUT"></template>

                <div>
                    <label class="block text-sm font-bold mb-1">Nama Fasilitas</label>
                    <input name="nama_fasilitas" x-model="form.nama_fasilitas" required class="{{ $input }}">
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-bold mb-1">Tipe</label>
                        <select name="tipe" x-model="form.tipe" class="{{ $input }}">
                            @foreach(['Ruangan','Laboratorium','Olahraga','Fasilitas Umum'] as $t)
                                <option>{{ $t }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-bold mb-1">Status</label>
                        {{-- Wajib: controller memvalidasi 'status' required --}}
                        <select name="status" x-model="form.status" class="{{ $input }}">
                            @foreach($statusLabel as $val => $lbl)
                                <option value="{{ $val }}">{{ $lbl }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-bold mb-1">Kapasitas</label>
                        <input type="number" min="1" name="kapasitas" x-model="form.kapasitas" required class="{{ $input }}">
                    </div>
                    <div>
                        <label class="block text-sm font-bold mb-1">Lokasi</label>
                        <input name="lokasi" x-model="form.lokasi" required class="{{ $input }}">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-bold mb-1">Deskripsi</label>
                    <textarea name="deskripsi" x-model="form.deskripsi" rows="3"
                              class="w-full px-4 py-3 border border-slate-200 rounded-lg bg-white text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-900 transition"></textarea>
                </div>
                <div>
                    <label class="block text-sm font-bold mb-1">Foto <span class="font-normal text-slate-400">(opsional, maks 2 MB)</span></label>
                    <input type="file" name="foto" accept="image/*"
                           class="block w-full text-sm text-slate-600 file:mr-3 file:px-4 file:py-2 file:rounded-lg file:border-0 file:bg-slate-100 file:text-slate-700 file:font-bold">
                </div>
                <div class="flex justify-end gap-2 pt-4">
                    <button type="button" @click="modal = false" class="px-4 py-2 bg-slate-100 rounded-lg text-sm font-bold">Batal</button>
                    <button type="submit" :disabled="submittingFacility" class="px-4 py-2 bg-blue-900 text-white rounded-lg text-sm font-bold disabled:opacity-60">
                        <span x-text="submittingFacility ? 'Menyimpan...' : 'Simpan'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection