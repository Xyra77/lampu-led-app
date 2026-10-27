<?php
require_once 'config/Database.php';
require_once 'config/helpers.php';
$database = new Database();
$db = $database->getConnection();
$id = (int) ($_GET['id'] ?? 0);
if ($id <= 0) redirectWithFlash('riwayat_transaksi.php', 'error', 'ID transaksi tidak valid.');
$stmt = $db->prepare('SELECT t.*, k.nama_kasir FROM transaksi t JOIN kasir k ON k.id_kasir = t.id_kasir WHERE t.id_transaksi = :id');
$stmt->execute([':id' => $id]);
$transaction = $stmt->fetch();
if (!$transaction) redirectWithFlash('riwayat_transaksi.php', 'error', 'Transaksi tidak ditemukan.');
$stmt = $db->prepare('SELECT * FROM transaksi_detail WHERE id_transaksi = :id ORDER BY id_detail');
$stmt->execute([':id' => $id]);
$items = $stmt->fetchAll();
$pageTitle = 'Detail Transaksi #' . $id;
require_once 'templates/header.php';
?>
<div class="section-heading"><div><h2>Detail Transaksi #<?= (int)$transaction['id_transaksi'] ?></h2><p>Rincian harga satuan, jumlah produk, subtotal, dan total bayar.</p></div><div class="inline-form"><button class="btn btn-secondary" type="button" onclick="window.print()">Cetak</button><a class="btn btn-ghost" href="riwayat_transaksi.php">Kembali</a></div></div>
<section class="card">
    <div class="form-grid">
        <ul class="summary-list"><li><span>ID Transaksi</span><strong>#<?= (int)$transaction['id_transaksi'] ?></strong></li><li><span>Tanggal Transaksi</span><strong><?= e(date('d-m-Y H:i', strtotime($transaction['tanggal_transaksi']))) ?></strong></li><li><span>Nama Pelanggan</span><strong><?= e($transaction['nama_pelanggan']) ?></strong></li></ul>
        <ul class="summary-list"><li><span>Nama Kasir</span><strong><?= e($transaction['nama_kasir']) ?></strong></li><li><span>Metode Pembayaran</span><strong><?= e($transaction['metode_pembayaran']) ?></strong></li><li><span>Total Bayar</span><strong><?= e(rupiah($transaction['total_bayar'])) ?></strong></li></ul>
    </div>
</section>
<section class="card"><h2>Rincian Produk</h2><div class="table-wrap"><table><thead><tr><th>Nama Produk</th><th>Harga Satuan</th><th>Jumlah Produk</th><th>Subtotal</th></tr></thead><tbody>
<?php foreach ($items as $item): ?><tr><td><?= e($item['nama_produk']) ?></td><td><?= e(rupiah($item['harga_satuan'])) ?></td><td><?= number_format((int)$item['jumlah_produk'], 0, ',', '.') ?></td><td><strong><?= e(rupiah($item['subtotal'])) ?></strong></td></tr><?php endforeach; ?>
<?php if (!$items): ?><tr><td colspan="4" class="empty">Tidak ada detail produk untuk transaksi ini.</td></tr><?php endif; ?>
</tbody><tfoot><tr><th colspan="3" style="text-align:right;padding:14px">Total Bayar</th><th style="padding:14px"><?= e(rupiah($transaction['total_bayar'])) ?></th></tr></tfoot></table></div></section>
<?php require_once 'templates/footer.php'; ?>
