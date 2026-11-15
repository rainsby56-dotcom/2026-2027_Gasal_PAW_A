<?php
require_once "koneksi.php";

$mulai   = $_GET['tanggal_mulai']   ?? '';
$selesai = $_GET['tanggal_selesai'] ?? '';

$labels = []; $data_total = []; $rekap = [];
$total_pendapatan = 0; $total_pelanggan = 0;
$error = '';
$tampil = false;

if ($mulai !== '' && $selesai !== '') {
    if (!tanggalValid($mulai) || !tanggalValid($selesai)) {
        $error = "Format tanggal tidak valid.";
    } elseif ($mulai > $selesai) {
        $error = "Tanggal mulai tidak boleh lebih besar dari tanggal selesai.";
    } else {
        $tampil = true;
        list($rekap, $total_pendapatan, $total_pelanggan) = ambilLaporan($koneksi, $mulai, $selesai);
        foreach ($rekap as $r) {
            $labels[]     = $r['tgl'];
            $data_total[] = (int)$r['total_harian'];
        }
    }
}

function rupiah($n) { return "Rp. " . number_format($n, 0, ',', '.'); }
function tglIndo($t) { return date('d M Y', strtotime($t)); }
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Laporan Penjualan dan Grafik</title>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<style>
    body { font-family: Arial, sans-serif; margin: 24px; color: #222; }
    h1 small, h2 small { font-size: 14px; color: #555; font-weight: normal; }
    form { display: flex; gap: 12px; align-items: center; flex-wrap: wrap; margin-bottom: 12px; }
    input[type=date] { padding: 6px; }
    .btn { padding: 8px 14px; border: 0; border-radius: 4px; color: #fff; cursor: pointer;
           text-decoration: none; display: inline-block; font-size: 14px; }
    .btn-primary { background: #0d6efd; }
    .btn-print   { background: #e8590c; }
    .btn-excel   { background: #198754; }
    .aksi { margin: 12px 0; display: flex; gap: 8px; }
    .error { background: #f8d7da; color: #842029; padding: 10px; border-radius: 4px; margin: 10px 0; }
    .chart-box { max-width: 800px; height: 320px; }
    table { border-collapse: collapse; width: 100%; margin-top: 15px; }
    th, td { border: 1px solid #bbb; padding: 8px; text-align: left; }
    thead th { background: #d9edf7; }
    td.angka { text-align: right; }
    .cards { display: flex; gap: 0; margin-top: 15px; max-width: 480px; }
    .card { flex: 1; border: 1px solid #bbb; }
    .card .judul { background: #d9edf7; padding: 8px; font-weight: bold; }
    .card .isi { padding: 10px; text-align: right; font-size: 16px; }
    .kosong { margin-top: 15px; color: #666; }

    @media print {
        .no-print { display: none !important; }
        body { margin: 0; }
        .chart-box { page-break-inside: avoid; }
        table, .cards { page-break-inside: avoid; }
    }
</style>
</head>
<body>

<!-- Form filter (disembunyikan saat cetak) -->
<div class="no-print">
    <h1>Filter Laporan Penjualan</h1>
    <form method="GET" action="laporan.php">
        <label>Dari:
            <input type="date" name="tanggal_mulai" value="<?= htmlspecialchars($mulai) ?>" required>
        </label>
        <label>Sampai:
            <input type="date" name="tanggal_selesai" value="<?= htmlspecialchars($selesai) ?>" required>
        </label>
        <button type="submit" class="btn btn-primary">Tampilkan</button>
    </form>
    <?php if ($error): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
</div>

<?php if ($tampil): ?>

    <h2>Rekap Laporan Penjualan
        <small><?= htmlspecialchars($mulai) ?> sampai <?= htmlspecialchars($selesai) ?></small>
    </h2>

    <!-- Tombol Cetak & Excel (disembunyikan saat cetak) -->
    <div class="aksi no-print">
        <button type="button" class="btn btn-print" onclick="window.print()">Cetak</button>
        <a class="btn btn-excel"
           href="export_excel.php?tanggal_mulai=<?= urlencode($mulai) ?>&tanggal_selesai=<?= urlencode($selesai) ?>">Excel</a>
    </div>

    <?php if (empty($rekap)): ?>
        <p class="kosong">Tidak ada transaksi pada rentang tanggal tersebut.</p>
    <?php else: ?>

        <!-- GRAFIK -->
        <div class="chart-box"><canvas id="myChart"></canvas></div>
        <script>
            const ctx = document.getElementById('myChart').getContext('2d');
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: <?= json_encode($labels) ?>,
                    datasets: [{
                        label: 'Total Pendapatan (Rp)',
                        data: <?= json_encode($data_total) ?>,
                        backgroundColor: 'rgba(13,110,253,0.6)',
                        borderColor: 'rgba(13,110,253,1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: false, // agar grafik langsung tampil saat dicetak
                    scales: {
                        x: { title: { display: true, text: 'Tanggal Transaksi' } },
                        y: {
                            beginAtZero: true,
                            title: { display: true, text: 'Total Pendapatan (Rp)' },
                            ticks: { callback: v => v.toLocaleString('id-ID') }
                        }
                    }
                }
            });
        </script>

        <!-- TABEL REKAPITULASI -->
        <table>
            <thead>
                <tr><th>No</th><th>Total Pendapatan</th><th>Tanggal Transaksi</th></tr>
            </thead>
            <tbody>
            <?php $no = 1; foreach ($rekap as $row): ?>
                <tr>
                    <td><?= $no++ ?></td>
                    <td><?= rupiah($row['total_harian']) ?></td>
                    <td><?= tglIndo($row['tgl']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>

        <!-- CARD RINGKASAN TOTAL -->
        <div class="cards">
            <div class="card">
                <div class="judul">Jumlah Pelanggan</div>
                <div class="isi"><?= $total_pelanggan ?> Orang</div>
            </div>
            <div class="card">
                <div class="judul">Jumlah Pendapatan</div>
                <div class="isi"><?= rupiah($total_pendapatan) ?></div>
            </div>
        </div>

    <?php endif; ?>
<?php endif; ?>

</body>
</html>
