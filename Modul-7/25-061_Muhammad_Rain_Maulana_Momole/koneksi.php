<?php
// Koneksi database MySQL - sesuaikan dengan environment Anda
$host = "localhost";
$user = "root";
$pass = "";
$db   = "penjualan";

$koneksi = mysqli_connect($host, $user, $pass, $db);
if (!$koneksi) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}
mysqli_set_charset($koneksi, "utf8mb4");

// Fungsi bantu: ambil data rekap & ringkasan berdasarkan rentang tanggal
function ambilLaporan($koneksi, $mulai, $selesai) {
    $rekap = [];
    $total_pendapatan = 0;

    // Agregasi pendapatan harian
    $sql = "SELECT DATE(waktu_transaksi) AS tgl, SUM(total) AS total_harian
            FROM transaksi
            WHERE DATE(waktu_transaksi) BETWEEN ? AND ?
            GROUP BY DATE(waktu_transaksi)
            ORDER BY tgl ASC";
    $stmt = mysqli_prepare($koneksi, $sql);
    mysqli_stmt_bind_param($stmt, "ss", $mulai, $selesai);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    while ($r = mysqli_fetch_assoc($res)) {
        $rekap[] = $r;
        $total_pendapatan += (int)$r['total_harian'];
    }
    mysqli_stmt_close($stmt);

    // Jumlah pelanggan unik
    $sql2 = "SELECT COUNT(DISTINCT pelanggan_id) AS jml
             FROM transaksi
             WHERE DATE(waktu_transaksi) BETWEEN ? AND ?";
    $stmt2 = mysqli_prepare($koneksi, $sql2);
    mysqli_stmt_bind_param($stmt2, "ss", $mulai, $selesai);
    mysqli_stmt_execute($stmt2);
    $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt2));
    $total_pelanggan = (int)($row['jml'] ?? 0);
    mysqli_stmt_close($stmt2);

    return [$rekap, $total_pendapatan, $total_pelanggan];
}

// Validasi format tanggal YYYY-MM-DD
function tanggalValid($t) {
    $d = DateTime::createFromFormat('Y-m-d', $t);
    return $d && $d->format('Y-m-d') === $t;
}
