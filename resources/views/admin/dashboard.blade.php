{{-- resources/views/admin/dashboard.blade.php  (SATU FILE, semua halaman) --}}
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Panel Admin - KampusReserve</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>[x-cloak]{display:none!important}</style>
</head>
@php
    $pendingCount = count($pendingUsers);
    $input = 'w-full px-4 py-3 border border-slate-200 rounded-xl bg-white text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500';
    $th    = 'px-6 py-4';
    $badge = ['aktif' => 'bg-emerald-100 text-emerald-700', 'dalam_perbaikan' => 'bg-amber-100 text-amber-700', 'nonaktif' => 'bg-rose-100 text-rose-700'];
    $barColors = ['Kerusakan' => 'bg-red-500', 'Kebersihan' => 'bg-amber-500', 'Fasilitas' => 'bg-blue-600', 'Lainnya' => 'bg-emerald-500'];
    $statusLabel = ['aktif' => 'Aktif', 'dalam_perbaikan' => 'Maintenance', 'nonaktif' => 'Nonaktif'];
    $maxOcc = max(1, max($occupancy ?: [1]));
    $menu = [
        'dashboard' => ['🏠', 'Dashboard',            'Dashboard Admin'],
        'petugas'   => ['👤', 'Akun Petugas',         'Akun Petugas'],
        'verifikasi'=> ['✅', 'Verifikasi Pengguna',  'Verifikasi Pengguna'],
        'fasilitas' => ['🏢', 'Data Fasilitas',       'Data Fasilitas'],
        'rekap'     => ['📊', 'Rekapitulasi & Ekspor','Rekapitulasi & Ekspor'],
    ];
    $stats = [
        ['TOTAL PENGGUNA', $totalUsers, 'border-blue-600'],
        ['RESERVASI BULAN INI', $reservationsThisMonth, 'border-emerald-500'],
        ['LAPORAN BULAN INI', $reportsThisMonth, 'border-amber-500'],
        ['PENDING VERIFIKASI', $pendingCount, 'border-red-500'],
    ];
@endphp
<body class="bg-slate-100 text-slate-800 antialiased"
      x-data="{
          page: '{{ request('page', session('page', 'dashboard')) }}',
          sidebar: false,
          modal: false, editing: null,
          blank: {nama_fasilitas:'', tipe:'Ruangan', lokasi:'', kapasitas:'', deskripsi:'', status:'aktif'},
          form: {},
          init() { this.form = {...this.blank} },
          newFacility() { this.editing = null; this.form = {...this.blank}; this.modal = true },
          editFacility(f) { this.editing = f.id; this.form = {...f}; this.modal = true },
          get facilityAction() { return this.editing ? '{{ url('admin/facilities') }}/' + this.editing : '{{ route('admin.facilities.store') }}' }
      }">

<div class="flex min-h-screen">

    {{-- ================= SIDEBAR ================= --}}
    <aside class="fixed inset-y-0 left-0 z-40 w-64 bg-[#0b2447] text-slate-200 flex flex-col transition-transform lg:translate-x-0"
           :class="sidebar ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'">
        <div class="h-16 px-6 flex items-center gap-3 border-b border-white/10 text-white font-bold">⚙️ Panel Admin</div>
        <nav class="flex-1 p-3 space-y-1.5 text-sm">
            @foreach($menu as $key => [$icon, $label])
                <button @click="page = '{{ $key }}'; sidebar = false"
                        :class="page === '{{ $key }}' ? 'bg-blue-600 text-white font-semibold' : 'hover:bg-white/10'"
                        class="w-full flex items-center gap-3 px-4 py-3 rounded-xl transition text-left">
                    <span>{{ $icon }}</span>{{ $label }}
                </button>
            @endforeach
        </nav>
        <form method="POST" action="{{ route('logout') }}" class="border-t border-white/10 p-3">
            @csrf
            <button class="w-full flex items-center gap-3 px-4 py-3 rounded-xl text-sm hover:bg-white/10 text-left">🚪 Logout</button>
        </form>
    </aside>

    {{-- ================= MAIN ================= --}}
    <div class="flex-1 lg:ml-64 min-w-0">

        {{-- Topbar --}}
        <header class="h-16 bg-white border-b border-slate-200 px-6 flex items-center justify-between sticky top-0 z-30">
            <div class="flex items-center gap-3">
                <button class="lg:hidden text-xl" @click="sidebar = !sidebar">☰</button>
                @foreach($menu as $key => [$icon, $label, $title])
                    <h2 x-show="page === '{{ $key }}'" x-cloak class="font-bold">{{ $title }}</h2>
                @endforeach
            </div>
            <div class="flex items-center gap-4">
                <button @click="page = 'verifikasi'" class="relative text-xl">🔔
                    @if($pendingCount > 0)
                        <span class="absolute -top-2 -right-2 bg-red-500 text-white text-[10px] font-bold w-5 h-5 rounded-full grid place-items-center">{{ $pendingCount }}</span>
                    @endif
                </button>
                <div class="w-10 h-10 rounded-full bg-red-500 text-white font-bold grid place-items-center">{{ strtoupper(substr(auth()->user()->name ?? 'A', 0, 1)) }}</div>
            </div>
        </header>

        <main class="p-6 lg:p-9">

            @if(session('success'))
                <div class="mb-5 px-4 py-3 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm">{{ session('success') }}</div>
            @endif
            @if($errors->any())
                <div class="mb-5 px-4 py-3 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-sm">{{ $errors->first() }}</div>
            @endif

            {{-- ============ PANEL: DASHBOARD ============ --}}
            <section x-show="page === 'dashboard'" x-cloak>
                <h1 class="text-3xl font-extrabold tracking-tight">Statistik Sistem</h1>
                <p class="text-slate-500 mt-1 mb-7">Rekapitulasi keseluruhan aktivitas sistem</p>

                <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5">
                    @foreach($stats as [$label, $value, $border])
                        <div class="bg-white rounded-2xl p-6 border-l-4 {{ $border }} shadow-sm">
                            <div class="text-xs font-semibold text-slate-500 tracking-wide">{{ $label }}</div>
                            <div class="mt-3 text-4xl font-extrabold text-[#0b2447]">{{ $value }}</div>
                        </div>
                    @endforeach
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-5 mt-5">
                    <div class="bg-white rounded-2xl p-6 shadow-sm">
                        <h3 class="font-bold mb-6">📈 Okupansi Fasilitas (Minggu Ini)</h3>
                        <div class="flex items-end justify-between gap-4 h-56">
                            @foreach($occupancy as $day => $count)
                                <div class="flex-1 flex flex-col items-center justify-end h-full gap-2">
                                    <div class="w-full bg-blue-600 rounded-t-lg" style="height: {{ round($count / $maxOcc * 100) }}%" title="{{ $count }} reservasi"></div>
                                    <span class="text-xs text-slate-500">{{ $day }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="bg-white rounded-2xl p-6 shadow-sm">
                        <h3 class="font-bold mb-5">🔧 Frekuensi Kerusakan</h3>
                        <div class="space-y-5">
                            @forelse($damageFreq as $kategori => $persen)
                                <div>
                                    <div class="flex justify-between text-sm mb-1.5"><span>{{ $kategori }}</span><span class="font-bold">{{ $persen }}%</span></div>
                                    <div class="h-2.5 rounded-full bg-slate-100"><div class="h-full rounded-full {{ $barColors[$kategori] ?? 'bg-slate-500' }}" style="width: {{ $persen }}%"></div></div>
                                </div>
                            @empty
                                <p class="text-sm text-slate-400">Belum ada laporan kerusakan.</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </section>

            {{-- ============ PANEL: AKUN PETUGAS ============ --}}
            <section x-show="page === 'petugas'" x-cloak>
                <h1 class="text-3xl font-extrabold tracking-tight">Manajemen Akun Petugas</h1>
                <p class="text-slate-500 mt-1 mb-7">Daftarkan akun petugas baru</p>

                <form action="{{ route('admin.petugas.store') }}" method="POST"
                      class="bg-white rounded-2xl shadow-sm p-7 grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-5">
                    @csrf
                    <input type="hidden" name="page" value="petugas">
                    <div><label class="block text-sm font-semibold mb-1.5">Nama Petugas <span class="text-red-500">*</span></label>
                        <input name="name" required placeholder="Nama lengkap" class="{{ $input }}"></div>
                    <div><label class="block text-sm font-semibold mb-1.5">NIP <span class="text-red-500">*</span></label>
                        <input name="nim_nip" required placeholder="Nomor induk petugas" class="{{ $input }}"></div>
                    <div><label class="block text-sm font-semibold mb-1.5">Email <span class="text-red-500">*</span></label>
                        <input type="email" name="email" required placeholder="email@kampus.ac.id" class="{{ $input }}"></div>
                    <div><label class="block text-sm font-semibold mb-1.5">No. HP</label>
                        <input name="no_hp" placeholder="08xxxxxxxxxx" class="{{ $input }}"></div>
                    <div><label class="block text-sm font-semibold mb-1.5">Unit / Divisi</label>
                        <select name="unit" class="{{ $input }}">@foreach(['Sarpras','IT','Kebersihan','Keamanan'] as $u)<option>{{ $u }}</option>@endforeach</select></div>
                    <div><label class="block text-sm font-semibold mb-1.5">Password Awal <span class="text-red-500">*</span></label>
                        <input type="password" name="password" required minlength="8" placeholder="min. 8 karakter" class="{{ $input }}"></div>
                    <div class="md:col-span-2 flex justify-end">
                        <button class="px-6 py-3 bg-[#0b2447] text-white text-sm font-semibold rounded-xl hover:bg-[#12306a] transition">Daftarkan Petugas</button>
                    </div>
                </form>

                <div class="bg-white rounded-2xl shadow-sm mt-6 overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="bg-slate-50 text-slate-500 text-xs uppercase tracking-wide">
                            <tr><th class="{{ $th }}">Nama</th><th class="{{ $th }}">NIP</th><th class="{{ $th }}">Email</th><th class="{{ $th }}">Unit</th><th class="{{ $th }}">Status</th><th class="{{ $th }}">Aksi</th></tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($petugas as $p)
                                <tr>
                                    <td class="{{ $th }} font-medium">{{ $p->name }}</td>
                                    <td class="{{ $th }}">{{ $p->nim_nip }}</td>
                                    <td class="{{ $th }}">{{ $p->email }}</td>
                                    <td class="{{ $th }}">{{ $p->unit }}</td>
                                    <td class="{{ $th }}"><span class="px-3 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-700">Aktif</span></td>
                                    <td class="{{ $th }}">
                                        <form action="{{ route('admin.petugas.destroy', $p->id) }}" method="POST" onsubmit="return confirm('Hapus akun ini?')">
                                            @csrf @method('DELETE')
                                            <button class="px-3 py-1.5 bg-red-500 text-white text-xs font-semibold rounded-lg hover:bg-red-600">Hapus</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="px-6 py-10 text-center text-slate-400">Belum ada akun petugas.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            {{-- ============ PANEL: VERIFIKASI PENGGUNA ============ --}}
            <section x-show="page === 'verifikasi'" x-cloak>
                <h1 class="text-3xl font-extrabold tracking-tight">Verifikasi Akun Pengguna</h1>
                <p class="text-slate-500 mt-1 mb-7">Verifikasi registrasi mandiri pengguna baru</p>

                <div class="bg-white rounded-2xl shadow-sm overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="bg-slate-50 text-slate-500 text-xs uppercase tracking-wide">
                            <tr><th class="{{ $th }}">Nama</th><th class="{{ $th }}">NIM/NIP</th><th class="{{ $th }}">Email</th><th class="{{ $th }}">Role</th><th class="{{ $th }}">Berkas</th><th class="{{ $th }}">Aksi</th></tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($pendingUsers as $u)
                                <tr>
                                    <td class="{{ $th }} font-medium">{{ $u->name }}</td>
                                    <td class="{{ $th }}">{{ $u->nim_nip ?? '-' }}</td>
                                    <td class="{{ $th }}">{{ $u->email }}</td>
                                    <td class="{{ $th }}">{{ ucfirst($u->role) }}</td>
                                    <td class="{{ $th }}">
                                        @if($u->ktm_path)
                                            <a href="{{ route('admin.users.ktm', $u->id) }}" target="_blank" class="text-blue-600 font-semibold hover:underline">Lihat KTM/KTP</a>
                                        @else <span class="text-slate-400">-</span> @endif
                                    </td>
                                    <td class="{{ $th }}">
                                        <div class="flex gap-2">
                                            <form action="{{ route('admin.users.verify', $u->id) }}" method="POST">@csrf
                                                <button class="px-3 py-1.5 bg-emerald-500 text-white text-xs font-semibold rounded-lg hover:bg-emerald-600">✓ Verifikasi</button></form>
                                            <form action="{{ route('admin.users.reject', $u->id) }}" method="POST">@csrf
                                                <button class="px-3 py-1.5 bg-red-500 text-white text-xs font-semibold rounded-lg hover:bg-red-600">✗ Tolak</button></form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="px-6 py-12 text-center text-slate-400">Tidak ada antrean verifikasi saat ini.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            {{-- ============ PANEL: DATA FASILITAS ============ --}}
            <section x-show="page === 'fasilitas'" x-cloak>
                <div class="flex items-start justify-between gap-4 mb-7">
                    <div>
                        <h1 class="text-3xl font-extrabold tracking-tight">Data Master Fasilitas</h1>
                        <p class="text-slate-500 mt-1">Kelola seluruh data fasilitas kampus</p>
                    </div>
                    <button @click="newFacility()" class="shrink-0 px-5 py-3 bg-[#0b2447] text-white text-sm font-semibold rounded-xl hover:bg-[#12306a]">+ Tambah Fasilitas</button>
                </div>

                <div class="bg-white rounded-2xl shadow-sm overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="bg-slate-50 text-slate-500 text-xs uppercase tracking-wide">
                            <tr><th class="{{ $th }}">Kode</th><th class="{{ $th }}">Nama</th><th class="{{ $th }}">Tipe</th><th class="{{ $th }}">Lokasi</th><th class="{{ $th }}">Kapasitas</th><th class="{{ $th }}">Status</th><th class="{{ $th }}">Aksi</th></tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($facilities as $f)
                                <tr>
                                    <td class="{{ $th }}">F{{ str_pad($f->id, 3, '0', STR_PAD_LEFT) }}</td>
                                    <td class="{{ $th }} font-medium">{{ $f->nama_fasilitas }}</td>
                                    <td class="{{ $th }}">{{ $f->tipe }}</td>
                                    <td class="{{ $th }}">{{ $f->lokasi }}</td>
                                    <td class="{{ $th }}">{{ $f->kapasitas }}</td>
                                    <td class="{{ $th }}"><span class="px-3 py-1 rounded-full text-xs font-semibold {{ $badge[$f->status] ?? $badge['nonaktif'] }}">{{ $statusLabel[$f->status] ?? $f->status }}</span></td>
                                    <td class="{{ $th }}">
                                        <div class="flex gap-2">
                                            <button type="button" @click="editFacility({{ Js::from($f->only(['id','nama_fasilitas','tipe','lokasi','kapasitas','deskripsi','status'])) }})"
                                                    class="px-3 py-1.5 border border-[#0b2447] text-xs font-semibold rounded-lg hover:bg-slate-50">Edit</button>
                                            <form action="{{ route('admin.facilities.toggle', $f->id) }}" method="POST">@csrf
                                                <button class="px-3 py-1.5 text-white text-xs font-semibold rounded-lg {{ $f->status === 'aktif' ? 'bg-red-500 hover:bg-red-600' : 'bg-emerald-500 hover:bg-emerald-600' }}">
                                                    {{ $f->status === 'aktif' ? 'Nonaktif' : 'Aktifkan' }}</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="px-6 py-12 text-center text-slate-400">Belum ada fasilitas terdaftar.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            {{-- ============ PANEL: REKAPITULASI & EKSPOR ============ --}}
            <section x-show="page === 'rekap'" x-cloak>
                <h1 class="text-3xl font-extrabold tracking-tight">Rekapitulasi & Ekspor</h1>
                <p class="text-slate-500 mt-1 mb-7">Rekap okupansi fasilitas dan frekuensi kerusakan</p>

                <form method="GET" action="{{ route('admin.dashboard') }}"
                      class="bg-white rounded-2xl shadow-sm p-7 grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-5">
                    <input type="hidden" name="page" value="rekap">
                    <div><label class="block text-sm font-semibold mb-1.5">Dari Tanggal</label>
                        <input type="date" name="start_date" value="{{ request('start_date') }}" class="{{ $input }}"></div>
                    <div><label class="block text-sm font-semibold mb-1.5">Sampai Tanggal</label>
                        <input type="date" name="end_date" value="{{ request('end_date') }}" class="{{ $input }}"></div>
                    <div><label class="block text-sm font-semibold mb-1.5">Fasilitas</label>
                        <select name="facility_id" class="{{ $input }}">
                            <option value="">Semua Fasilitas</option>
                            @foreach($facilities as $f)
                                <option value="{{ $f->id }}" @selected(request('facility_id') == $f->id)>{{ $f->nama_fasilitas }}</option>
                            @endforeach
                        </select></div>
                    <div class="md:col-span-2 flex flex-wrap justify-end gap-3">
                        <button type="submit" class="px-5 py-3 text-sm font-semibold rounded-xl bg-blue-600 text-white hover:bg-blue-700">Terapkan</button>
                        @foreach(['csv' => '📄 Ekspor CSV', 'excel' => '📊 Ekspor Excel', 'pdf' => '📕 Ekspor PDF'] as $fmt => $label)
                            <a href="{{ route('admin.rekap.export', array_merge(['format' => $fmt], request()->except('page'))) }}"
                               class="px-5 py-3 text-sm font-semibold rounded-xl border-2 border-[#0b2447] {{ $fmt === 'pdf' ? 'bg-[#0b2447] text-white' : 'text-[#0b2447] hover:bg-slate-50' }}">{{ $label }}</a>
                        @endforeach
                    </div>
                </form>

                <div class="bg-white rounded-2xl shadow-sm mt-6 overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="bg-slate-50 text-slate-500 text-xs uppercase tracking-wide">
                            <tr><th class="{{ $th }}">Fasilitas</th><th class="{{ $th }}">Total Reservasi</th><th class="{{ $th }}">Jam Terpakai</th><th class="{{ $th }}">Okupansi</th><th class="{{ $th }}">Kerusakan</th></tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($rekap as $r)
                                <tr>
                                    <td class="{{ $th }} font-medium">{{ $r->nama_fasilitas }}</td>
                                    <td class="{{ $th }}">{{ $r->reservations_count }}</td>
                                    <td class="{{ $th }}">{{ $r->jam_terpakai }} jam</td>
                                    <td class="{{ $th }}"><span class="px-3 py-1 rounded-full text-xs font-semibold {{ $r->okupansi >= 80 ? 'bg-amber-100 text-amber-700' : 'bg-emerald-100 text-emerald-700' }}">{{ $r->okupansi }}%</span></td>
                                    <td class="{{ $th }}">{{ $r->reports_count }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="px-6 py-12 text-center text-slate-400">Tidak ada data pada periode ini.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

        </main>
    </div>
</div>

{{-- ================= MODAL TAMBAH / EDIT FASILITAS ================= --}}
<div x-show="modal" x-cloak class="fixed inset-0 z-50 grid place-items-center p-4 bg-slate-900/50" @keydown.escape.window="modal = false">
    <div class="bg-white rounded-2xl w-full max-w-2xl max-h-[92vh] overflow-y-auto" @click.outside="modal = false">
        <div class="flex justify-between items-center px-7 py-5 border-b border-slate-100">
            <h3 class="font-bold text-lg" x-text="editing ? 'Edit Fasilitas' : 'Tambah Fasilitas'"></h3>
            <button @click="modal = false" class="text-slate-400 hover:text-slate-700 text-xl">✕</button>
        </div>
        <form :action="facilityAction" method="POST" enctype="multipart/form-data" class="p-7 grid grid-cols-1 md:grid-cols-2 gap-5">
            @csrf
            <input type="hidden" name="page" value="fasilitas">
            <template x-if="editing"><input type="hidden" name="_method" value="PUT"></template>
            <div><label class="block text-sm font-semibold mb-1.5">Nama Fasilitas</label>
                <input name="nama_fasilitas" x-model="form.nama_fasilitas" required placeholder="contoh: Aula A" class="{{ $input }}"></div>
            <div><label class="block text-sm font-semibold mb-1.5">Tipe</label>
                <select name="tipe" x-model="form.tipe" class="{{ $input }}">
                    @foreach(['Ruangan','Laboratorium','Olahraga','Fasilitas Umum'] as $t)<option>{{ $t }}</option>@endforeach
                </select></div>
            <div><label class="block text-sm font-semibold mb-1.5">Lokasi</label>
                <input name="lokasi" x-model="form.lokasi" required placeholder="Gedung / Lantai" class="{{ $input }}"></div>
            <div><label class="block text-sm font-semibold mb-1.5">Kapasitas</label>
                <input type="number" min="1" name="kapasitas" x-model="form.kapasitas" required placeholder="jumlah orang" class="{{ $input }}"></div>
            <div class="md:col-span-2"><label class="block text-sm font-semibold mb-1.5">Deskripsi</label>
                <textarea name="deskripsi" x-model="form.deskripsi" rows="3" placeholder="Keterangan tambahan..." class="{{ $input }}"></textarea></div>
            <div><label class="block text-sm font-semibold mb-1.5">Status</label>
                <select name="status" x-model="form.status" class="{{ $input }}">
                    <option value="aktif">Aktif</option><option value="dalam_perbaikan">Maintenance</option><option value="nonaktif">Nonaktif</option>
                </select></div>
            <div><label class="block text-sm font-semibold mb-1.5">Foto</label>
                <input type="file" name="foto" accept="image/*" class="w-full text-sm border-2 border-dashed border-slate-200 rounded-xl p-3 bg-slate-50"></div>
            <div class="md:col-span-2 flex justify-end gap-3 pt-2">
                <button type="button" @click="modal = false" class="px-5 py-3 text-sm font-semibold rounded-xl border border-slate-200 hover:bg-slate-50">Batal</button>
                <button class="px-6 py-3 text-sm font-semibold rounded-xl bg-blue-600 text-white hover:bg-blue-700">Simpan</button>
            </div>
        </form>
    </div>
</div>

</body>
</html>