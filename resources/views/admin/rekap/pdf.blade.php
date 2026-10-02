<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Rekap KampusReserve</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; margin: 30px; color: #0f172a; }
        .header { border-bottom: 3px double #1e3a8a; padding-bottom: 12px; margin-bottom: 20px; text-align: center; }
        .header h1 { font-size: 20px; margin: 0 0 4px; color: #1e3a8a; text-transform: uppercase; letter-spacing: 1px; }
        .header h2 { font-size: 14px; margin: 0 0 4px; color: #334155; font-weight: normal; }
        .header p { margin: 4px 0 0; color: #64748b; font-size: 11px; }
        .info { display: flex; justify-content: space-between; margin-bottom: 16px; font-size: 11px; color: #475569; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #cbd5e1; padding: 8px 10px; text-align: left; }
        th { background: #f1f5f9; font-weight: bold; color: #1e3a8a; font-size: 11px; text-transform: uppercase; }
        tbody tr:nth-child(even) { background: #f8fafc; }
        .text-center { text-align: center; }
        .signature { margin-top: 40px; display: flex; justify-content: flex-end; }
        .signature-box { text-align: center; width: 220px; }
        .signature-box .line { margin-top: 60px; border-top: 1px solid #0f172a; padding-top: 4px; }
        .footer { margin-top: 30px; padding-top: 10px; border-top: 1px solid #cbd5e1; text-align: center; font-size: 10px; color: #94a3b8; }
        @media print { .noprint { display: none; } body { margin: 15px; } }
    </style>
</head>
<body>

    <button class="noprint" onclick="window.print()"
            style="margin-bottom:16px;padding:10px 20px;background:#1e3a8a;color:white;border:none;border-radius:8px;cursor:pointer;font-weight:bold;">
        Cetak / Simpan sebagai PDF
    </button>

    <div class="header">
        <h1>Rekapitulasi Okupansi &amp; Kerusakan Fasilitas</h1>
        <h2>Sistem Reservasi &amp; Pelaporan Fasilitas Kampus</h2>
        <p>Dicetak pada: {{ now()->translatedFormat('d F Y, H:i') }} WIB</p>
    </div>

    <div class="info">
        <div>
            <strong>Periode:</strong>
            {{ $startDate ? \Carbon\Carbon::parse($startDate)->translatedFormat('d F Y') : 'Awal' }}
            s/d
            {{ $endDate ? \Carbon\Carbon::parse($endDate)->translatedFormat('d F Y') : 'Sekarang' }}
        </div>
        <div>
            <strong>Total Fasilitas:</strong> {{ $rekap->count() }}
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th class="text-center" style="width:40px;">No</th>
                <th>Fasilitas</th>
                <th>Tipe</th>
                <th>Lokasi</th>
                <th class="text-center">Total Reservasi</th>
                <th class="text-center">Jam Terpakai</th>
                <th class="text-center">Okupansi</th>
                <th class="text-center">Kerusakan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rekap as $i => $r)
                <tr>
                    <td class="text-center">{{ $i + 1 }}</td>
                    <td><strong>{{ $r->nama_fasilitas }}</strong></td>
                    <td>{{ $r->tipe }}</td>
                    <td>{{ $r->lokasi }}</td>
                    <td class="text-center">{{ $r->reservations_count }}</td>
                    <td class="text-center">{{ $r->jam_terpakai }} jam</td>
                    <td class="text-center">{{ $r->okupansi }}%</td>
                    <td class="text-center">{{ $r->reports_count }}</td>
                </tr>
            @empty
                <tr><td colspan="8" class="text-center" style="padding:20px;color:#94a3b8;">Tidak ada data.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="signature">
        <div class="signature-box">
            <p>Mengetahui,</p>
            <p>Administrator Kampus</p>
            <div class="line"><strong>{{ auth()->user()->name ?? 'Admin' }}</strong></div>
        </div>
    </div>

    <div class="footer">
        Dokumen ini dicetak otomatis oleh Sistem Reservasi &amp; Pelaporan Fasilitas Kampus.
    </div>

</body>
</html>