<?php

/**
 * Helper: Konversi pekerjaan string ke numerik
 */
function jobToNumeric($jobString) {
    $map = [
        'Pengangguran' => 1,
        'Buruh Harian' => 2,
        'Petani Kecil' => 3,
        'Pedagang' => 4,
        'Pegawai Tetap' => 5,
        'PNS' => 6,
        'Polisi' => 7
    ];
    return isset($map[$jobString]) ? $map[$jobString] : 0;
}

/**
 * Fungsi untuk menghitung jarak Euclidean antara dua titik data
 * Mengikuti rumus: d = sqrt( (x1-y1)^2 + (x2-y2)^2 + (x3-y3)^2 + (x4-y4)^2 )
 */
function euclideanDistance($data1, $data2) {
    $sum = 0;
    
    // Fitur: pekerjaan, tanggungan, jumlah_anak, penghasilan_per_bulan
    
    // 1. Hitung pekerjaan (dikonversi dari teks ke angka)
    $job1 = jobToNumeric($data1['pekerjaan']);
    $job2 = jobToNumeric($data2['pekerjaan']);
    $sum += pow($job1 - $job2, 2);
    
    // 2. Hitung sisanya
    $features = ['tanggungan', 'jumlah_anak', 'penghasilan_per_bulan'];
    
    foreach ($features as $f) {
        $sum += pow($data1[$f] - $data2[$f], 2);
    }
    
    return sqrt($sum);
}

/**
 * Fungsi utama algoritma kNN
 * @param array $trainingData : Data dari database
 * @param array $testData : Data inputan user
 * @param int $k : Nilai K
 * @return array : Hasil prediksi ('Layak' atau 'Tidak Layak') beserta daftar tetangga terdekat
 */
function predictKnn($trainingData, $testData, $k) {
    $distances = [];
    
    // 1. Hitung jarak dari testData ke seluruh trainingData
    foreach ($trainingData as $train) {
        $dist = euclideanDistance($train, $testData);
        $distances[] = [
            'jarak' => $dist,
            'label' => $train['status_kelayakan'],
            'data'  => $train
        ];
    }
    
    // 2. Urutkan berdasarkan jarak terkecil (Ascending)
    usort($distances, function($a, $b) {
        return $a['jarak'] <=> $b['jarak'];
    });
    
    // 3. Ambil K tetangga terdekat
    $nearestNeighbors = array_slice($distances, 0, $k);
    
    // 4. Lakukan Voting
    $votes = [
        'Layak' => 0,
        'Tidak Layak' => 0
    ];
    
    foreach ($nearestNeighbors as $neighbor) {
        $label = $neighbor['label'];
        if (isset($votes[$label])) {
            $votes[$label]++;
        } else {
            $votes[$label] = 1;
        }
    }
    
    // Tentukan label dengan vote terbanyak
    $predictedLabel = 'Layak';
    if ($votes['Tidak Layak'] > $votes['Layak']) {
        $predictedLabel = 'Tidak Layak';
    }
    
    return [
        'prediksi' => $predictedLabel,
        'tetangga' => $nearestNeighbors,
        'votes' => $votes
    ];
}
?>
