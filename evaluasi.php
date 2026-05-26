<?php
require_once 'koneksi.php';
require_once 'knn_core.php';

// Ambil semua data
$query = mysqli_query($koneksi, "SELECT * FROM bansos ORDER BY id ASC");
$allData = [];
while ($row = mysqli_fetch_assoc($query)) {
    $allData[] = $row;
}

$k_default = isset($_GET['k']) ? (int)$_GET['k'] : 5;
$total_data = count($allData);
$train_size = (int)($total_data * 0.8);

// Split data (80% Training, 20% Testing)
$trainingData = array_slice($allData, 0, $train_size);
$testData = array_slice($allData, $train_size);

$tp = 0; // True Positive (Aktual Layak, Prediksi Layak)
$tn = 0; // True Negative (Aktual Tidak Layak, Prediksi Tidak Layak)
$fp = 0; // False Positive (Aktual Tidak Layak, Prediksi Layak)
$fn = 0; // False Negative (Aktual Layak, Prediksi Tidak Layak)

$results = [];

foreach ($testData as $test) {
    $knnResult = predictKnn($trainingData, $test, $k_default);
    $prediksi = $knnResult['prediksi'];
    $aktual = $test['status_kelayakan'];
    
    if ($aktual == 'Layak' && $prediksi == 'Layak') {
        $tp++;
    } elseif ($aktual == 'Tidak Layak' && $prediksi == 'Tidak Layak') {
        $tn++;
    } elseif ($aktual == 'Tidak Layak' && $prediksi == 'Layak') {
        $fp++;
    } elseif ($aktual == 'Layak' && $prediksi == 'Tidak Layak') {
        $fn++;
    }
    
    $results[] = [
        'nama' => $test['nama'],
        'aktual' => $aktual,
        'prediksi' => $prediksi
    ];
}

$total_test = count($testData);
$accuracy = $total_test > 0 ? ($tp + $tn) / $total_test * 100 : 0;
$precision = ($tp + $fp) > 0 ? ($tp / ($tp + $fp)) * 100 : 0;
$recall = ($tp + $fn) > 0 ? ($tp / ($tp + $fn)) * 100 : 0;

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Evaluasi Model kNN - Bansos</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f8fafc; }
        .glass { background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(10px); border: 1px solid rgba(255, 255, 255, 0.2); }
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
                    <a href="index.php" class="hover:bg-indigo-500 px-3 py-2 rounded-md text-sm font-medium transition">Prediksi</a>
                    <a href="evaluasi.php" class="bg-indigo-700 px-3 py-2 rounded-md text-sm font-medium">Evaluasi Model</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="flex-grow container mx-auto px-4 py-8">
        
        <div class="max-w-5xl mx-auto">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-3xl font-bold text-indigo-900">Evaluasi Model k-Nearest Neighbors</h2>
                <form method="GET" class="flex items-center space-x-2">
                    <label class="text-sm font-medium text-slate-700">Nilai K:</label>
                    <select name="k" class="border border-slate-300 rounded px-2 py-1 bg-white outline-none" onchange="this.form.submit()">
                        <option value="3" <?php if($k_default==3) echo 'selected'; ?>>3</option>
                        <option value="5" <?php if($k_default==5) echo 'selected'; ?>>5</option>
                        <option value="7" <?php if($k_default==7) echo 'selected'; ?>>7</option>
                        <option value="9" <?php if($k_default==9) echo 'selected'; ?>>9</option>
                    </select>
                </form>
            </div>

            <!-- Stats Overview -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
                <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-100 flex flex-col items-center justify-center">
                    <span class="text-slate-500 text-sm font-semibold mb-1">Total Dataset</span>
                    <span class="text-3xl font-bold text-indigo-600"><?php echo $total_data; ?></span>
                </div>
                <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-100 flex flex-col items-center justify-center">
                    <span class="text-slate-500 text-sm font-semibold mb-1">Data Training (80%)</span>
                    <span class="text-3xl font-bold text-indigo-600"><?php echo count($trainingData); ?></span>
                </div>
                <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-100 flex flex-col items-center justify-center">
                    <span class="text-slate-500 text-sm font-semibold mb-1">Data Testing (20%)</span>
                    <span class="text-3xl font-bold text-indigo-600"><?php echo count($testData); ?></span>
                </div>
                <div class="bg-indigo-600 text-white p-6 rounded-xl shadow-md flex flex-col items-center justify-center">
                    <span class="text-indigo-100 text-sm font-semibold mb-1">Accuracy Model</span>
                    <span class="text-3xl font-bold"><?php echo number_format($accuracy, 1); ?>%</span>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <!-- Confusion Matrix -->
                <div class="glass p-8 rounded-2xl shadow-lg">
                    <h3 class="text-xl font-bold text-slate-800 mb-6 border-b pb-2">Confusion Matrix</h3>
                    
                    <table class="w-full text-center border-collapse">
                        <thead>
                            <tr>
                                <th class="p-2"></th>
                                <th class="p-2 border bg-slate-50 text-indigo-800">Prediksi Layak</th>
                                <th class="p-2 border bg-slate-50 text-red-800">Prediksi Tidak Layak</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <th class="p-2 border bg-slate-50 text-indigo-800 text-left">Aktual Layak</th>
                                <td class="p-4 border bg-green-50 text-2xl font-bold text-green-700">
                                    <?php echo $tp; ?><br><span class="text-xs text-green-600 font-normal">True Positive (TP)</span>
                                </td>
                                <td class="p-4 border bg-red-50 text-2xl font-bold text-red-700">
                                    <?php echo $fn; ?><br><span class="text-xs text-red-600 font-normal">False Negative (FN)</span>
                                </td>
                            </tr>
                            <tr>
                                <th class="p-2 border bg-slate-50 text-red-800 text-left">Aktual Tidak Layak</th>
                                <td class="p-4 border bg-red-50 text-2xl font-bold text-red-700">
                                    <?php echo $fp; ?><br><span class="text-xs text-red-600 font-normal">False Positive (FP)</span>
                                </td>
                                <td class="p-4 border bg-green-50 text-2xl font-bold text-green-700">
                                    <?php echo $tn; ?><br><span class="text-xs text-green-600 font-normal">True Negative (TN)</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <div class="mt-8 space-y-4">
                        <div>
                            <div class="flex justify-between mb-1">
                                <span class="text-sm font-medium text-slate-700">Precision (Ketepatan)</span>
                                <span class="text-sm font-medium text-indigo-600"><?php echo number_format($precision, 2); ?>%</span>
                            </div>
                            <div class="w-full bg-slate-200 rounded-full h-2.5">
                                <div class="bg-indigo-600 h-2.5 rounded-full" style="width: <?php echo $precision; ?>%"></div>
                            </div>
                            <p class="text-xs text-slate-500 mt-1">TP / (TP + FP)</p>
                        </div>

                        <div>
                            <div class="flex justify-between mb-1">
                                <span class="text-sm font-medium text-slate-700">Recall (Sensitivitas)</span>
                                <span class="text-sm font-medium text-indigo-600"><?php echo number_format($recall, 2); ?>%</span>
                            </div>
                            <div class="w-full bg-slate-200 rounded-full h-2.5">
                                <div class="bg-indigo-600 h-2.5 rounded-full" style="width: <?php echo $recall; ?>%"></div>
                            </div>
                            <p class="text-xs text-slate-500 mt-1">TP / (TP + FN)</p>
                        </div>
                    </div>
                </div>

                <!-- Test Results Table -->
                <div class="glass p-8 rounded-2xl shadow-lg">
                    <h3 class="text-xl font-bold text-slate-800 mb-6 border-b pb-2">Detail Data Testing</h3>
                    <div class="overflow-y-auto max-h-[450px]">
                        <table class="w-full text-sm text-left">
                            <thead class="text-xs text-slate-500 uppercase bg-slate-50 sticky top-0">
                                <tr>
                                    <th class="px-4 py-3">Nama</th>
                                    <th class="px-4 py-3">Aktual</th>
                                    <th class="px-4 py-3">Prediksi</th>
                                    <th class="px-4 py-3 text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($results as $res): ?>
                                    <tr class="border-b hover:bg-slate-50">
                                        <td class="px-4 py-3 font-medium text-slate-700"><?php echo htmlspecialchars($res['nama']); ?></td>
                                        <td class="px-4 py-3"><?php echo $res['aktual']; ?></td>
                                        <td class="px-4 py-3"><?php echo $res['prediksi']; ?></td>
                                        <td class="px-4 py-3 text-center">
                                            <?php if ($res['aktual'] == $res['prediksi']): ?>
                                                <span class="text-green-500">
                                                    <svg class="w-5 h-5 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                                </span>
                                            <?php else: ?>
                                                <span class="text-red-500">
                                                    <svg class="w-5 h-5 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
        </div>
    </main>

</body>
</html>
