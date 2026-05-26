<?php
require_once 'koneksi.php';
require_once 'knn_core.php';

$hasil_prediksi = null;
$error = null;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama = $_POST['nama'];
    $pekerjaan = $_POST['pekerjaan'];
    $tanggungan = (int)$_POST['tanggungan'];
    $jumlah_anak = (int)$_POST['jumlah_anak'];
    $penghasilan = (float)$_POST['penghasilan'];
    $k = (int)$_POST['k'];
    
    // Ambil data training dari database
    $query = mysqli_query($koneksi, "SELECT * FROM bansos");
    $trainingData = [];
    while ($row = mysqli_fetch_assoc($query)) {
        $trainingData[] = $row;
    }
    
    if (count($trainingData) < $k) {
        $error = "Data training di database kurang dari nilai K. Silakan tambahkan data training terlebih dahulu.";
    } else {
        // Bentuk array data test
        $testData = [
            'pekerjaan' => $pekerjaan,
            'tanggungan' => $tanggungan,
            'jumlah_anak' => $jumlah_anak,
            'penghasilan_per_bulan' => $penghasilan
        ];
        
        // Lakukan prediksi
        $hasil_prediksi = predictKnn($trainingData, $testData, $k);
        $label_prediksi = $hasil_prediksi['prediksi'];
        
        // Simpan hasil ke database
        $stmt = mysqli_prepare($koneksi, "INSERT INTO hasil_prediksi (nama, pekerjaan, tanggungan, jumlah_anak, penghasilan_per_bulan, hasil_prediksi, nilai_k) VALUES (?, ?, ?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "ssiiisi", $nama, $pekerjaan, $tanggungan, $jumlah_anak, $penghasilan, $label_prediksi, $k);
        mysqli_stmt_execute($stmt);
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prediksi Kelayakan Bansos - kNN</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8fafc;
        }
        .glass {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
    </style>
</head>
<body class="text-slate-800 antialiased min-h-screen flex flex-col">

    <!-- Navbar -->
    <nav class="bg-indigo-600 text-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <div class="flex items-center">
                    <span class="font-bold text-xl tracking-tight">kNN Bansos Desa</span>
                </div>
                <div class="flex space-x-4">
                    <a href="index.php" class="bg-indigo-700 px-3 py-2 rounded-md text-sm font-medium">Prediksi</a>
                    <a href="evaluasi.php" class="hover:bg-indigo-500 px-3 py-2 rounded-md text-sm font-medium transition">Evaluasi Model</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="flex-grow container mx-auto px-4 py-8">
        <div class="max-w-4xl mx-auto grid grid-cols-1 md:grid-cols-2 gap-8">
            
            <!-- Form Section -->
            <div class="glass p-8 rounded-2xl shadow-xl">
                <h2 class="text-2xl font-bold mb-6 text-indigo-900">Form Prediksi</h2>
                
                <?php if ($error): ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                        <span class="block sm:inline"><?php echo $error; ?></span>
                    </div>
                <?php endif; ?>

                <form method="POST" action="">
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-slate-700 mb-2">Nama Lengkap</label>
                        <input type="text" name="nama" required class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition">
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-slate-700 mb-2">Pekerjaan</label>
                        <select name="pekerjaan" required class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none transition bg-white">
                            <option value="Pengangguran">Pengangguran</option>
                            <option value="Buruh Harian">Buruh Harian</option>
                            <option value="Petani Kecil">Petani Kecil</option>
                            <option value="Pedagang">Pedagang</option>
                            <option value="Pegawai Tetap">Pegawai Tetap</option>
                            <option value="PNS">PNS</option>
                            <option value="Polisi">Polisi</option>
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Jumlah Tanggungan</label>
                            <input type="number" name="tanggungan" min="0" required class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none transition">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Jumlah Anak</label>
                            <input type="number" name="jumlah_anak" min="0" required class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none transition">
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-slate-700 mb-2">Penghasilan Per Bulan (Rp)</label>
                        <input type="number" name="penghasilan" min="0" required class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none transition" placeholder="Contoh: 1500000">
                    </div>

                    <div class="mb-6">
                        <label class="block text-sm font-medium text-slate-700 mb-2">Nilai K (Tetangga Terdekat)</label>
                        <select name="k" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none transition bg-white">
                            <option value="3">3</option>
                            <option value="5" selected>5</option>
                            <option value="7">7</option>
                            <option value="9">9</option>
                        </select>
                    </div>

                    <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-4 rounded-lg transition duration-200 transform hover:scale-[1.02]">
                        Prediksi Sekarang
                    </button>
                </form>
            </div>

            <!-- Result Section -->
            <div class="space-y-6">
                <?php if ($hasil_prediksi): ?>
                    <div class="glass p-8 rounded-2xl shadow-xl text-center transform transition-all duration-500 animate-fade-in">
                        <h3 class="text-lg font-medium text-slate-500 mb-2">Hasil Prediksi Untuk:</h3>
                        <p class="text-2xl font-bold text-slate-800 mb-6"><?php echo htmlspecialchars($nama); ?></p>
                        
                        <?php if ($hasil_prediksi['prediksi'] == 'Layak'): ?>
                            <div class="inline-block bg-green-100 text-green-800 border-2 border-green-500 rounded-full px-8 py-3 text-2xl font-bold tracking-wider mb-4 shadow-sm">
                                LAYAK BANSOS
                            </div>
                        <?php else: ?>
                            <div class="inline-block bg-red-100 text-red-800 border-2 border-red-500 rounded-full px-8 py-3 text-2xl font-bold tracking-wider mb-4 shadow-sm">
                                TIDAK LAYAK
                            </div>
                        <?php endif; ?>
                        
                        <p class="text-sm text-slate-500 mt-4">
                            Berdasarkan <?php echo $k; ?> tetangga terdekat 
                            <br>(Layak: <?php echo $hasil_prediksi['votes']['Layak']; ?> | Tidak Layak: <?php echo $hasil_prediksi['votes']['Tidak Layak']; ?>)
                        </p>
                    </div>

                    <!-- Details Table -->
                    <div class="glass p-6 rounded-2xl shadow-xl overflow-hidden">
                        <h4 class="font-bold text-slate-700 mb-4">Detail <?php echo $k; ?> Tetangga Terdekat</h4>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm text-left">
                                <thead class="text-xs text-slate-500 uppercase bg-slate-50">
                                    <tr>
                                        <th class="px-4 py-3">Nama</th>
                                        <th class="px-4 py-3">Jarak</th>
                                        <th class="px-4 py-3">Label</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($hasil_prediksi['tetangga'] as $neighbor): ?>
                                        <tr class="border-b">
                                            <td class="px-4 py-3 font-medium text-slate-800"><?php echo htmlspecialchars($neighbor['data']['nama']); ?></td>
                                            <td class="px-4 py-3 text-slate-600"><?php echo number_format($neighbor['jarak'], 2); ?></td>
                                            <td class="px-4 py-3">
                                                <?php if ($neighbor['label'] == 'Layak'): ?>
                                                    <span class="bg-green-100 text-green-800 px-2 py-1 rounded text-xs font-semibold">Layak</span>
                                                <?php else: ?>
                                                    <span class="bg-red-100 text-red-800 px-2 py-1 rounded text-xs font-semibold">Tidak</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="glass p-8 rounded-2xl shadow-xl h-full flex items-center justify-center border-dashed border-2 border-slate-300">
                        <div class="text-center text-slate-400">
                            <svg class="mx-auto h-16 w-16 mb-4 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z" />
                            </svg>
                            <p class="text-lg">Isi form di samping untuk melihat hasil prediksi.</p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

        </div>
    </main>

</body>
</html>
