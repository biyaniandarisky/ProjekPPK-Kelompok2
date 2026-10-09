<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Surat Persetujuan Reservasi #{{ $reservation->id }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script>
        tailwind.config = { theme: { extend: { fontFamily: { sans: ['"Open Sans"', 'ui-sans-serif', 'system-ui', 'sans-serif'] } } } }
    </script>
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; padding: 0 !important; }
            .print-card { border: none !important; box-shadow: none !important; padding: 0 !important; }
        }
    </style>
</head>
<body class="bg-slate-100 min-h-screen py-8 px-4 font-sans text-slate-900">

    <!-- BAR NAVIGASI CETAK (TIDAK TERIKUT SAAT DICETAK) -->
    <div class="max-w-3xl mx-auto mb-4 flex items-center justify-between no-print">
        <a href="{{ route('pengguna.reservasi.index') }}" class="text-xs font-bold text-slate-600 hover:text-slate-900 transition flex items-center gap-1">
            &larr; Kembali ke Daftar Reservasi
        </a>
        <button onclick="window.print()" class="px-4 py-2 bg-[#0f2540] hover:bg-[#0b1c31] text-white text-xs font-black rounded-lg shadow-xs transition flex items-center gap-1.5">
            🖨️ Cetak / Simpan PDF
        </button>
    </div>

    <!-- DOKUMEN SURAT RESMI -->
    <div class="max-w-3xl mx-auto bg-white border border-slate-200 rounded-2xl p-8 shadow-lg print-card space-y-6">

        <!-- KOP SURAT -->
        <div class="border-b-2 border-slate-900 pb-4 text-center space-y-1">
            <h3 class="text-xs font-bold uppercase tracking-widest text-slate-600">Kementerian Pendidikan, Kebudayaan, Riset, dan Teknologi</h3>
            <h1 class="text-lg font-black uppercase text-slate-900 tracking-wide">UPT SARANA DAN PRASARANA KAMPUS</h1>
            <p class="text-[11px] text-slate-500">Gedung Rektorat Lt. 1, Jl. Prof. Soedarto, SH &bull; Surel: sarpras@kampus.ac.id</p>
        </div>

        <!-- JUDUL DOKUMEN -->
        <div class="text-center space-y-1">
            <h2 class="text-base font-black uppercase tracking-wider text-slate-900 underline decoration-2">SURAT BUKTI PERSETUJUAN RESERVASI</h2>
            <p class="text-xs font-bold text-slate-500">Nomor: PERMIT/{{ date('Y/m', strtotime($reservation->created_at)) }}/{{ str_pad($reservation->id, 4, '0', STR_PAD_LEFT) }}</p>
        </div>

        <!-- ISI DETAIL -->
        <div class="space-y-4 text-xs">
            <p class="leading-relaxed">Dengan ini menerangkan bahwa permohonan reservasi sarana dan prasarana kampus yang diajukan oleh civitas akademika berikut telah <strong>DISETUJUI</strong>:</p>

            <!-- A. PEMOHON -->
            <div class="space-y-1.5">
                <h4 class="font-black text-slate-900 uppercase tracking-wider text-[11px] bg-slate-100 p-1.5 rounded">A. Detail Pemohon</h4>
                <table class="w-full text-slate-800">
                    <tr>
                        <td class="w-40 py-1 font-semibold text-slate-600">Nama Pemohon</td>
                        <td class="py-1 font-extrabold">: {{ $reservation->user->name ?? $reservation->user->nama ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="py-1 font-semibold text-slate-600">Email Pemohon</td>
                        <td class="py-1 font-bold">: {{ $reservation->user->email ?? '-' }}</td>
                    </tr>
                </table>
            </div>

            <!-- B. FASILITAS & WAKTU -->
            <div class="space-y-1.5">
                <h4 class="font-black text-slate-900 uppercase tracking-wider text-[11px] bg-slate-100 p-1.5 rounded">B. Detail Fasilitas &amp; Waktu Pemakaian</h4>
                <table class="w-full text-slate-800">
                    <tr>
                        <td class="w-40 py-1 font-semibold text-slate-600">Nama Fasilitas</td>
                        <td class="py-1 font-extrabold text-slate-900">: {{ $reservation->facility->nama_fasilitas ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="py-1 font-semibold text-slate-600">Lokasi / Kapasitas</td>
                        <td class="py-1 font-bold">: {{ $reservation->facility->lokasi ?? '-' }} ({{ $reservation->facility->kapasitas ?? '-' }} Orang)</td>
                    </tr>
                    <tr>
                        <td class="py-1 font-semibold text-slate-600">Tanggal Pemakaian</td>
                        <td class="py-1 font-extrabold text-emerald-800">: {{ \Carbon\Carbon::parse($reservation->tanggal)->translatedFormat('l, d F Y') }}</td>
                    </tr>
                    <tr>
                        <td class="py-1 font-semibold text-slate-600">Waktu Pemakaian</td>
                        <td class="py-1 font-extrabold text-emerald-800">: {{ \Carbon\Carbon::parse($reservation->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($reservation->end_time)->format('H:i') }} WIB</td>
                    </tr>
                    <tr>
                        <td class="py-1 font-semibold text-slate-600">Tujuan Kegiatan</td>
                        <td class="py-1 font-bold">: {{ $reservation->tujuan }}</td>
                    </tr>
                </table>
            </div>

            <!-- C. KETENTUAN -->
            <div class="space-y-1">
                <h4 class="font-black text-slate-900 uppercase tracking-wider text-[11px] bg-slate-100 p-1.5 rounded">C. Ketentuan Pemakaian</h4>
                <ol class="list-decimal list-inside text-slate-700 space-y-0.5 pt-1">
                    <li>Wajib menunjukkan bukti surat persetujuan ini kepada petugas piket sebelum pemakaian.</li>
                    <li>Pemohon bertanggung jawab penuh atas kebersihan dan keutuhan fasilitas selama masa peminjaman.</li>
                    <li>Segala bentuk kerusakan inventaris wajib dilaporkan dan diganti sesuai ketentuan berlaku.</li>
                </ol>
            </div>
        </div>

        <!-- TANDA TANGAN & PENGESAHAN -->
        <div class="pt-6 flex justify-between items-end border-t border-slate-100">
            <div class="text-center space-y-2">
                <div class="w-24 h-24 border-2 border-slate-900 rounded-xl flex items-center justify-center p-1 bg-slate-50 mx-auto">
                    <span class="text-[9px] font-black text-slate-400 text-center uppercase tracking-widest leading-tight">VERIFIED<br>VALID PERMIT</span>
                </div>
                <p class="text-[9px] text-slate-400 font-bold">Persetujuan Sah Digital</p>
            </div>

            <div class="text-right space-y-1 text-xs">
                <p class="text-slate-600">Disetujui pada: <strong>{{ \Carbon\Carbon::parse($reservation->updated_at)->translatedFormat('d F Y, H:i') }} WIB</strong></p>
                <p class="font-bold text-slate-800">Petugas Pengelola Sarpras,</p>
                <div class="h-14"></div>
                <p class="font-black text-slate-900 underline">{{ $reservation->petugas->name ?? $reservation->petugas->nama ?? 'Tim Verifikasi Sarpras' }}</p>
                <p class="text-[10px] text-slate-500">NIP. {{ $reservation->petugas->nim_nip ?? '—' }}</p>
            </div>
        </div>

    </div>

</body>
</html>