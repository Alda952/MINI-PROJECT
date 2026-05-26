<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "bansos_desa";

try {
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    $koneksi = mysqli_connect($host, $user, $pass, $db);
} catch (mysqli_sql_exception $e) {
    die("<div style='font-family: sans-serif; padding: 20px; background: #fee2e2; color: #991b1b; border: 1px solid #f87171; border-radius: 8px; max-width: 600px; margin: 40px auto;'>
        <h2 style='margin-top: 0;'>Koneksi Database Gagal</h2>
        <p>Aplikasi tidak dapat terhubung ke database <strong>{$db}</strong>.</p>
        <p><strong>Pesan Error:</strong> " . $e->getMessage() . "</p>
        <p><strong>Solusi:</strong> Pastikan Anda telah melakukan Import file <code>database.sql</code> ke dalam MySQL melalui phpMyAdmin sesuai instruksi.</p>
    </div>");
}
?>
