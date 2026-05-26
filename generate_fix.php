<?php

$sql = "CREATE DATABASE IF NOT EXISTS bansos_desa;\nUSE bansos_desa;\n\n";

$sql .= "CREATE TABLE IF NOT EXISTS bansos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    nama_prov VARCHAR(100) NOT NULL,
    nama_kab VARCHAR(100) NOT NULL,
    pekerjaan VARCHAR(100) NOT NULL,
    tanggungan INT NOT NULL,
    jumlah_anak INT NOT NULL,
    penghasilan_per_bulan DECIMAL(12,2) NOT NULL,
    status_kelayakan ENUM('Layak','Tidak Layak') NOT NULL,
    alasan VARCHAR(255) NOT NULL
);\n\n";

$sql .= "TRUNCATE TABLE bansos;\n\n";
$sql .= "INSERT INTO bansos (id, penghasilan_per_bulan, nama, nama_prov, nama_kab, pekerjaan, tanggungan, jumlah_anak, status_kelayakan, alasan) VALUES\n";

$names_data = [
    [1, 375000, 'ABIDIN'],
    [2, 225000, 'JUBAEDAH'],
    [3, 375000, 'ELMINAWATI'],
    [4, 600000, 'BAIQ EKA ANGGRAINI'],
    [5, 375000, 'RUMIAH'],
    [6, 725000, 'NIKMAH'],
    [7, 600000, 'SUWARDI'],
    [8, 875000, 'NARIMIN'],
    [9, 450000, 'HAIRUNI'],
    [10, 1425000, 'JUMAKYAH'],
    [11, 600000, 'SAKNAH'],
    [12, 600000, 'RETIAH'],
    [13, 975000, 'BAIQ MARTINI'],
    [14, 600000, 'SAHRAH'],
    [15, 500000, 'NURUL AINI'],
    [16, 225000, 'MAESARAH'],
    [17, 725000, 'LUKMAN SURYADI'],
    [18, 600000, 'KARTINI'],
    [19, 225000, 'RIANA'],
    [20, 375000, 'MISRAH'],
    [21, 375000, 'ENDANG FITRIANI'],
    [22, 225000, 'MANIAH'],
    [23, 725000, 'ANISAH'],
    [24, 375000, 'FARIDAH'],
    [25, 375000, 'MURIAH'],
    [26, 975000, 'SAHDAN'],
    [27, 375000, 'SENIWATI'],
    [28, 225000, 'AWANAH'],
    [29, 725000, 'FITRIANINGSIH'],
    [30, 1100000, 'RANI MAHARANI'],
    [31, 600000, 'DENI RAMEDAN'],
    [32, 750000, 'SITI JUMA INAH'],
    [33, 600000, 'ERNAWATI'],
    [34, 950000, 'SARISAH'],
    [35, 225000, 'SAINAH'],
    [36, 1200000, 'HARTUTI'],
    [37, 225000, 'BAIQ HERAWATI'],
    [38, 1200000, 'MUNISAH'],
    [39, 600000, 'MURNAH'],
    [40, 375000, 'SALMIAH'],
    [41, 875000, 'SAKILAWATI'],
    [42, 825000, 'SALATIAH'],
    [43, 600000, 'MULIANI'],
    [44, 600000, 'ISTIHARAH'],
    [45, 225000, 'MARTIAH'],
    [46, 1100000, 'SITI HAJAR'],
    [47, 225000, 'HUDRIAH'],
    [48, 375000, 'MAHNIM'],
    [49, 375000, 'LALU RUSLAN'],
    [50, 600000, 'SRI HANDAYANI'],
    [51, 375000, 'SAKNAH'],
    [52, 1325000, 'SURIANI'],
    [53, 600000, 'SAPURAH'],
    [54, 500000, 'MUNAWARAH'],
    [55, 600000, 'SUMIATI'],
    [56, 1100000, 'SARAKYAH'],
    [57, 600000, 'MUHIR'],
    [58, 1200000, 'MASIAH'],
    [59, 1200000, 'NYAMAH'],
    [60, 1325000, 'MISLAH'],
    [61, 225000, 'NURHAYATI'],
    [62, 600000, 'SAKMAH'],
    [63, 600000, 'AHMAD SAHRI']
];

$jobs = ["Pengangguran", "Buruh Harian", "Petani Kecil", "Pedagang", "Pegawai Tetap", "PNS", "Polisi"];

$values = [];
foreach ($names_data as $row) {
    $id = $row[0];
    $penghasilan = $row[1];
    $nama = $row[2];
    $prov = 'NUSA TENGGARA BARAT';
    $kab = 'KOTA MATARAM';
    
    // Auto generate proper logic
    if ($penghasilan < 500000) {
        $pekerjaan_idx = rand(0, 1); 
    } elseif ($penghasilan < 1000000) {
        $pekerjaan_idx = rand(1, 3); 
    } else {
        $pekerjaan_idx = rand(3, 5);
    }
    $pekerjaan = $jobs[$pekerjaan_idx];
    $pekerjaan_score = $pekerjaan_idx + 1;
    
    $tanggungan = rand(1, 6);
    $jumlah_anak = $tanggungan > 1 ? rand(0, $tanggungan - 1) : 0;
    
    $score = ($penghasilan / 1000000) - ($tanggungan * 0.6) - ($jumlah_anak * 0.2) + ($pekerjaan_score * 0.4);
    $status = $score < 1.0 ? 'Layak' : 'Tidak Layak';
    $alasan = 'Data Latih ML';
    
    $values[] = "($id, $penghasilan, '$nama', '$prov', '$kab', '$pekerjaan', $tanggungan, $jumlah_anak, '$status', '$alasan')";
}

$sql .= implode(",\n", $values) . ";\n";

file_put_contents(__DIR__ . '/bansos_desa_fix.sql', $sql);
echo "Berhasil";
