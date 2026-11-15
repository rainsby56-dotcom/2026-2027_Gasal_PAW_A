<?php
require_once "koneksi.php";

$mulai   = $_GET['tanggal_mulai']   ?? '';
$selesai = $_GET['tanggal_selesai'] ?? '';

if (!tanggalValid($mulai) || !tanggalValid($selesai) || $mulai > $selesai) {
    http_response_code(400);
    die("Parameter tanggal tidak valid.");
}

list($rekap, $total_pendapatan, $total_pelanggan) = ambilLaporan($koneksi, $mulai, $selesai);

// HTTP Response Header: browser akan mengunduh respons sebagai file .xls
$namaFile = "Rekap_Penjualan_{$mulai}_sd_{$selesai}.xls";
header("Content-Type: application/vnd.ms-excel; charset=utf-8");
header("Content-Disposition: attachment; filename=\"$namaFile\"");
header("Pragma: no-cache");
header("Expires: 0");
?>
<html>
<head><meta charset="UTF-8"></head>
<body>
    <h3>Rekap Laporan Penjualan <?= htmlspecialchars($mulai) ?> sampai <?= htmlspecialchars($selesai) ?></h3>
    <table border="1">
        <tr style="background:#d9edf7;">
            <th>No</th><th>Total Pendapatan</th><th>Tanggal Transaksi</th>
        </tr>
        <?php $no = 1; foreach ($rekap as $row): ?>
        <tr>
            <td><?= $no++ ?></td>
            <td><?= (int)$row['total_harian'] ?></td>
            <td><?= date('d M Y', strtotime($row['tgl'])) ?></td>
        </tr>
        <?php endforeach; ?>
        <tr>
            <td colspan="2"><b>Jumlah Pelanggan Unik</b></td>
            <td><b><?= $total_pelanggan ?> Orang</b></td>
        </tr>
        <tr>
            <td colspan="2"><b>Total Pendapatan Keseluruhan</b></td>
            <td><b><?= $total_pendapatan ?></b></td>
        </tr>
    </table>
</body>
</html>
