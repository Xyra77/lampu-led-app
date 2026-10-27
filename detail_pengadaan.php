<?php
require_once 'config/Database.php';
require_once 'config/helpers.php';
$database = new Database();
$db = $database->getConnection();
$id = (int) ($_GET['id'] ?? 0);
if ($id <= 0) redirectWithFlash('pengadaan.php', 'error', 'ID pembelian tidak valid.');
$stmt = $db->prepare('SELECT p.*, s.nama_supplier, s.no_telepon, s.alamat FROM pembelian p JOIN supplier s ON s.id_supplier = p.id_supplier WHERE p.id_pembelian = :id');
$stmt->execute([':id' => $id]);
$purchase = $stmt->fetch();
if (!$purchase) redirectWithFlash('pengadaan.php', 'error', 'Data pengadaan tidak ditemukan.');
$stmt = $db->prepare('SELECT * FROM pembelian_detail WHERE id_pembelian = :id ORDER BY id_detail_pembelian');
$stmt->execute([':id' => $id]);
$items = $stmt->fetchAll();
$pageTitle = 'Detail Pengadaan #' . $id;
require_once 'templates/header.php';
?>
<div class="section-heading"><div><h2>Detail Pengadaan #<?= (int)$purchase['id_pembelian'] ?></h2><p>Data barang, jumlah, harga satuan pembelian, dan total harga.</p></div><div class="inline-form"><button class="btn btn-secondary" type="button" onclick="window.print()">Cetak</button><a class="btn btn-ghost" href="pengadaan.php">Kembali</a></div></div>
<section class="card"><div class="form-grid"><ul class="summary-list"><li><span>ID Pembelian</span><strong>#<?= (int)$purchase['id_pembelian'] ?></strong></li><li><span>Tanggal Pembelian</span><strong><?= e(date('d-m-Y H:i', strtotime($purchase['tanggal_pembelian']))) ?></strong></li><li><span>ID Supplier</span><strong>#<?= (int)$purchase['id_supplier'] ?></strong></li></ul><ul class="summary-list"><li><span>Nama Supplier</span><strong><?= e($purchase['nama_supplier']) ?></strong></li><li><span>Telepon Supplier</span><strong><?= e($purchase['no_telepon'] ?: '-') ?></strong></li><li><span>Total Harga</span><strong><?= e(rupiah($purchase['total_harga'])) ?></strong></li></ul></div></section>
<section class="card"><h2>Barang yang Dibeli</h2><div class="table-wrap"><table><thead><tr><th>Nama Barang / Produk</th><th>Jumlah</th><th>Harga Satuan</th><th>Subtotal</th></tr></thead><tbody>
<?php foreach ($items as $item): ?><tr><td><?= e($item['nama_produk']) ?></td><td><?= number_format((int)$item['jumlah'], 0, ',', '.') ?></td><td><?= e(rupiah($item['harga_satuan'])) ?></td><td><strong><?= e(rupiah($item['subtotal'])) ?></strong></td></tr><?php endforeach; ?>
<?php if (!$items): ?><tr><td colspan="4" class="empty">Tidak ada rincian barang untuk pengadaan ini.</td></tr><?php endif; ?>
</tbody><tfoot><tr><th colspan="3" style="text-align:right;padding:14px">Total Harga</th><th style="padding:14px"><?= e(rupiah($purchase['total_harga'])) ?></th></tr></tfoot></table></div></section>
<?php require_once 'templates/footer.php'; ?>
