<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Fasilitas Kampus</title>
    <!-- Menggunakan CDN Tailwind CSS untuk tampilan cepat -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-8">
    <div class="max-w-5xl mx-auto bg-white p-6 rounded-lg shadow-md">
        <h1 class="text-2xl font-bold mb-6 text-gray-800">Daftar Fasilitas Kampus</h1>

        <table class="w-full border-collapse border border-gray-200">
            <thead>
                <tr class="bg-gray-50 text-left">
                    <th class="border border-gray-200 p-3">No</th>
                    <th class="border border-gray-200 p-3">Nama Fasilitas</th>
                    <th class="border border-gray-200 p-3">Tipe</th>
                    <th class="border border-gray-200 p-3">Lokasi</th>
                    <th class="border border-gray-200 p-3">Kapasitas</th>
                    <th class="border border-gray-200 p-3">Deskripsi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($facilities as $index => $facility)
                    <tr class="hover:bg-gray-50">
                        <td class="border border-gray-200 p-3">{{ $index + 1 }}</td>
                        <td class="border border-gray-200 p-3 font-semibold">{{ $facility->nama_fasilitas }}</td>
                        <td class="border border-gray-200 p-3">{{ $facility->tipe }}</td>
                        <td class="border border-gray-200 p-3">{{ $facility->lokasi }}</td>
                        <td class="border border-gray-200 p-3">{{ $facility->kapasitas }} orang</td>
                        <td class="border border-gray-200 p-3">{{ $facility->deskripsi ?? '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="border border-gray-200 p-4 text-center text-gray-500">
                            Belum ada data fasilitas di database.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</body>
</html>