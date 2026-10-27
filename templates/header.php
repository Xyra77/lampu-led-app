<?php
require_once __DIR__ . '/../config/helpers.php';
$currentPage = basename($_SERVER['PHP_SELF'] ?? 'index.php');
$navItems = [
    'index.php' => ['Produk & Stok', '💡'],
    'kasir.php' => ['Data Kasir', '🧑‍💼'],
    'supplier.php' => ['Supplier', '📦'],
    'transaksi.php' => ['Transaksi', '🧾'],
    'riwayat_transaksi.php' => ['Riwayat', '📋'],
    'pengadaan.php' => ['Pengadaan', '🚚'],
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? e($pageTitle) . ' | ' : '' ?>Lampu LED POS</title>
    <link rel="stylesheet" href="assets/style.css">
    <script src="assets/app.js" defer></script>
</head>
<body>
    <header class="topbar">
        <div class="container topbar-inner">
            <div>
                <a class="brand" href="index.php"><span class="brand-icon">💡</span><span><strong>LAMPU LED</strong><small>Point of Sale & Inventory</small></span></a>
            </div>
            <div class="topbar-caption">Manajemen penjualan dan persediaan</div>
        </div>
    </header>
    <nav class="main-nav">
        <div class="container nav-inner">
            <?php foreach ($navItems as $file => [$label, $icon]): ?>
                <a class="nav-link <?= $currentPage === $file ? 'active' : '' ?>" href="<?= e($file) ?>"><span><?= $icon ?></span><?= e($label) ?></a>
            <?php endforeach; ?>
        </div>
    </nav>
    <main class="container main-content">
        <?php if (!empty($_SESSION['flash'])): ?>
            <div class="alert alert-<?= e($_SESSION['flash']['type']) ?>">
                <?= e($_SESSION['flash']['message']) ?>
                <button type="button" class="alert-close" aria-label="Tutup" onclick="this.parentElement.remove()">×</button>
            </div>
            <?php unset($_SESSION['flash']); ?>
        <?php endif; ?>
