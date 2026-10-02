<!DOCTYPE html>
<html lang="id"><head><meta charset="UTF-8"><title>Rekap KampusReserve</title>
<style>
 body{font-family:Arial,sans-serif;font-size:12px;margin:30px;color:#0f172a}
 h1{font-size:18px;margin:0 0 4px} p{margin:0 0 16px;color:#64748b}
 table{width:100%;border-collapse:collapse} th,td{border:1px solid #cbd5e1;padding:7px 9px;text-align:left}
 th{background:#f1f5f9} @media print{.noprint{display:none}}
</style></head><body>
<button class="noprint" onclick="window.print()">Cetak / Simpan PDF</button>
<h1>Rekapitulasi Okupansi &amp; Kerusakan Fasilitas</h1>
<p>Periode: {{ $startDate ?: 'awal' }} s/d {{ $endDate ?: 'sekarang' }}</p>
<table>
 <tr><th>Fasilitas</th><th>Tipe</th><th>Lokasi</th><th>Total Reservasi</th><th>Jam Terpakai</th><th>Okupansi</th><th>Kerusakan</th></tr>
 @foreach($rekap as $r)
  <tr><td>{{ $r->nama_fasilitas }}</td><td>{{ $r->tipe }}</td><td>{{ $r->lokasi }}</td>
      <td>{{ $r->reservations_count }}</td><td>{{ $r->jam_terpakai }} jam</td><td>{{ $r->okupansi }}%</td><td>{{ $r->reports_count }}</td></tr>
 @endforeach
</table></body></html>