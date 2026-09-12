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
                <?php $__empty_1 = true; $__currentLoopData = $facilities; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $facility): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr class="hover:bg-gray-50">
                        <td class="border border-gray-200 p-3"><?php echo e($index + 1); ?></td>
                        <td class="border border-gray-200 p-3 font-semibold"><?php echo e($facility->nama_fasilitas); ?></td>
                        <td class="border border-gray-200 p-3"><?php echo e($facility->tipe); ?></td>
                        <td class="border border-gray-200 p-3"><?php echo e($facility->lokasi); ?></td>
                        <td class="border border-gray-200 p-3"><?php echo e($facility->kapasitas); ?> orang</td>
                        <td class="border border-gray-200 p-3"><?php echo e($facility->deskripsi ?? '-'); ?></td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="6" class="border border-gray-200 p-4 text-center text-gray-500">
                            Belum ada data fasilitas di database.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html><?php /**PATH C:\ProjekPPK-Kelompok2\resources\views/facilities/index.blade.php ENDPATH**/ ?>