<?php
require_once 'config/Database.php';
require_once 'config/helpers.php';
$database = new Database();
$db = $database->getConnection();
$pageTitle = 'Riwayat Transaksi';
$query = "SELECT t.id_transaksi, t.tanggal_transaksi, t.nama_pelanggan, t.metode_pembayaran, t.total_bayar,
    k.nama_kasir,
    GROUP_CONCAT(CONCAT(td.nama_produk, ' × ', td.jumlah_produk) ORDER BY td.id_detail SEPARATOR ', ') AS daftar_produk,
    COALESCE(SUM(td.jumlah_produk), 0) AS total_produk
    FROM transaksi t
    JOIN kasir k ON k.id_kasir = t.id_kasir
    LEFT JOIN transaksi_detail td ON td.id_transaksi = t.id_transaksi
    GROUP BY t.id_transaksi, t.tanggal_transaksi, t.nama_pelanggan, t.metode_pembayaran, t.total_bayar, k.nama_kasir
    ORDER BY t.id_transaksi DESC LIMIT 100";
$transactions = $db->query($query)->fetchAll();
$totalHariIni = (int) $db->query('SELECT COALESCE(SUM(total_bayar), 0) FROM transaksi WHERE DATE(tanggal_transaksi) = CURDATE()')->fetchColumn();
$countToday = (int) $db->query('SELECT COUNT(*) FROM transaksi WHERE DATE(tanggal_transaksi) = CURDATE()')->fetchColumn();
require_once 'templates/header.php';
?>
<div class="section-heading"><div><h2>Riwayat Transaksi</h2><p>Daftar penjualan yang sudah tersimpan, termasuk kasir dan metode pembayaran.</p></div><a href="transaksi.php" class="btn btn-primary">+ Transaksi Baru</a></div>
<section class="stats-grid"><div class="stat-card"><span>Transaksi Hari Ini</span><strong><?= number_format($countToday, 0, ',', '.') ?></strong></div><div class="stat-card"><span>Total Penjualan Hari Ini</span><strong><?= e(rupiah($totalHariIni)) ?></strong></div><div class="stat-card"><span>Riwayat Ditampilkan</span><strong><?= number_format(count($transactions), 0, ',', '.') ?></strong></div></section>
<section class="card">
    <div class="table-wrap"><table><thead><tr><th>ID Transaksi</th><th>Tanggal</th><th>Pelanggan</th><th>Nama Kasir</th><th>Metode Pembayaran</th><th>Produk / Jumlah</th><th>Total Bayar</th><th>Detail</th></tr></thead><tbody>
    <?php foreach ($transactions as $row): ?><tr><td><strong>#<?= (int)$row['id_transaksi'] ?></strong></td><td><?= e(date('d-m-Y H:i', strtotime($row['tanggal_transaksi']))) ?></td><td><?= e($row['nama_pelanggan']) ?></td><td><?= e($row['nama_kasir']) ?></td><td><span class="badge badge-no" style="background:#eaf1ff;color:#1d4ed8"><?= e($row['metode_pembayaran']) ?></span></td><td><?= e($row['daftar_produk'] ?: '-') ?><br><small class="muted"><?= number_format((int)$row['total_produk'], 0, ',', '.') ?> unit</small></td><td><strong><?= e(rupiah($row['total_bayar'])) ?></strong></td><td><a class="btn btn-secondary btn-sm" href="detail_transaksi.php?id=<?= (int)$row['id_transaksi'] ?>">Lihat Detail</a></td></tr><?php endforeach; ?>
    <?php if (!$transactions): ?><tr><td colspan="8" class="empty">Belum ada transaksi. Buat transaksi pertama dari menu Transaksi.</td></tr><?php endif; ?>
    </tbody></table></div>
</section>
<?php require_once 'templates/footer.php'; ?>
