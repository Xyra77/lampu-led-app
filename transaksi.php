<?php
require_once 'config/Database.php';
require_once 'config/helpers.php';
$database = new Database();
$db = $database->getConnection();
$pageTitle = 'Transaksi Penjualan';
$errors = [];
$kasirList = $db->query('SELECT id_kasir, nama_kasir FROM kasir ORDER BY nama_kasir')->fetchAll();
$produkList = $db->query('SELECT id, nama_lampu, watt, harga_terendah, jumlah_stock FROM lampu_led ORDER BY nama_lampu, watt')->fetchAll();
$metodeList = ['Tunai', 'QRIS', 'Transfer Bank', 'Kartu Debit', 'E-Wallet'];
$defaultDate = date('Y-m-d\TH:i');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $namaPelanggan = trim($_POST['nama_pelanggan'] ?? '');
    $idKasir = (int) ($_POST['id_kasir'] ?? 0);
    $metode = trim($_POST['metode_pembayaran'] ?? '');
    $tanggalInput = trim($_POST['tanggal_transaksi'] ?? '');
    $productIds = $_POST['products'] ?? [];
    $quantities = $_POST['quantities'] ?? [];

    if ($namaPelanggan === '') $errors[] = 'Nama pelanggan wajib diisi. Untuk pelanggan umum, isi "Umum".';
    if ($idKasir <= 0) $errors[] = 'Pilih kasir yang melayani transaksi.';
    if (!in_array($metode, $metodeList, true)) $errors[] = 'Pilih metode pembayaran yang valid.';
    $tanggal = DateTime::createFromFormat('!Y-m-d\TH:i', $tanggalInput) ?: DateTime::createFromFormat('Y-m-d\TH:i', $tanggalInput);
    if (!$tanggal) $errors[] = 'Tanggal transaksi tidak valid.';
    if (strlen($namaPelanggan) > 120) $errors[] = 'Nama pelanggan maksimal 120 karakter.';

    $items = [];
    if (is_array($productIds) && is_array($quantities)) {
        foreach ($productIds as $index => $rawId) {
            $productId = (int) $rawId;
            if ($productId <= 0) continue;
            $qty = (int) ($quantities[$index] ?? 0);
            if ($qty <= 0) {
                $errors[] = 'Jumlah produk harus minimal 1.';
                continue;
            }
            $items[$productId] = ($items[$productId] ?? 0) + $qty;
        }
    }
    if (!$items) $errors[] = 'Tambahkan minimal satu produk ke transaksi.';

    if (!$errors) {
        try {
            $db->beginTransaction();
            $checkCashier = $db->prepare('SELECT id_kasir FROM kasir WHERE id_kasir = :id');
            $checkCashier->execute([':id' => $idKasir]);
            if (!$checkCashier->fetchColumn()) throw new RuntimeException('Data kasir tidak ditemukan. Muat ulang halaman dan pilih kasir yang aktif.');

            $preparedItems = [];
            $totalBayar = 0;
            $selectProduct = $db->prepare('SELECT id, nama_lampu, watt, harga_terendah, jumlah_stock FROM lampu_led WHERE id = :id FOR UPDATE');
            foreach ($items as $productId => $qty) {
                $selectProduct->execute([':id' => $productId]);
                $product = $selectProduct->fetch();
                if (!$product) throw new RuntimeException('Salah satu produk tidak ditemukan.');
                if ((int) $product['jumlah_stock'] < $qty) {
                    throw new RuntimeException('Stok ' . $product['nama_lampu'] . ' tidak cukup. Stok tersedia: ' . $product['jumlah_stock'] . ', diminta: ' . $qty . '.');
                }
                $price = (int) $product['harga_terendah'];
                $subtotal = $price * $qty;
                $totalBayar += $subtotal;
                $preparedItems[] = ['id' => $productId, 'nama' => $product['nama_lampu'] . ' (' . $product['watt'] . ' W)', 'harga' => $price, 'jumlah' => $qty, 'subtotal' => $subtotal];
            }

            $insertHeader = $db->prepare('INSERT INTO transaksi (tanggal_transaksi, nama_pelanggan, id_kasir, metode_pembayaran, total_bayar) VALUES (:tanggal, :pelanggan, :kasir, :metode, :total)');
            $insertHeader->execute([
                ':tanggal' => $tanggal->format('Y-m-d H:i:s'),
                ':pelanggan' => $namaPelanggan,
                ':kasir' => $idKasir,
                ':metode' => $metode,
                ':total' => $totalBayar,
            ]);
            $transactionId = (int) $db->lastInsertId();
            $insertDetail = $db->prepare('INSERT INTO transaksi_detail (id_transaksi, id_lampu, nama_produk, harga_satuan, jumlah_produk, subtotal) VALUES (:transaksi, :lampu, :nama, :harga, :jumlah, :subtotal)');
            $decrementStock = $db->prepare('UPDATE lampu_led SET jumlah_stock = jumlah_stock - :jumlah_set WHERE id = :id AND jumlah_stock >= :jumlah_check');
            foreach ($preparedItems as $item) {
                $insertDetail->execute([':transaksi' => $transactionId, ':lampu' => $item['id'], ':nama' => $item['nama'], ':harga' => $item['harga'], ':jumlah' => $item['jumlah'], ':subtotal' => $item['subtotal']]);
                $decrementStock->execute([':jumlah_set' => $item['jumlah'], ':jumlah_check' => $item['jumlah'], ':id' => $item['id']]);
                if ($decrementStock->rowCount() !== 1) throw new RuntimeException('Stok berubah saat transaksi berlangsung. Silakan ulangi transaksi.');
            }
            $db->commit();
            redirectWithFlash('riwayat_transaksi.php', 'success', 'Transaksi #' . $transactionId . ' berhasil disimpan. Total bayar ' . rupiah($totalBayar) . '. Stok produk sudah diperbarui.');
        } catch (Throwable $exception) {
            if ($db->inTransaction()) $db->rollBack();
            $errors[] = $exception instanceof RuntimeException ? $exception->getMessage() : 'Transaksi gagal disimpan. Periksa koneksi database lalu coba lagi.';
        }
    }
}

function renderProductOptions(array $produkList): void
{
    echo '<option value="">Pilih produk lampu LED</option>';
    foreach ($produkList as $produk) {
        $stock = (int) $produk['jumlah_stock'];
        $disabled = $stock < 1 ? ' disabled' : '';
        echo '<option value="' . (int) $produk['id'] . '" data-price="' . (int) $produk['harga_terendah'] . '" data-stock="' . $stock . '"' . $disabled . '>'
            . e($produk['nama_lampu']) . ' · ' . (int)$produk['watt'] . ' W · ' . rupiah($produk['harga_terendah']) . ' · stok ' . $stock . '</option>';
    }
}
require_once 'templates/header.php';
?>
<div class="section-heading"><div><h2>Transaksi Penjualan</h2><p>Buat transaksi untuk satu atau beberapa jenis produk. Stok akan berkurang setelah transaksi berhasil.</p></div><a class="btn btn-ghost" href="riwayat_transaksi.php">Lihat Riwayat</a></div>
<?php if ($errors): ?><div class="alert alert-error"><ul><?php foreach ($errors as $error): ?><li><?= e($error) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
<?php if (!$kasirList): ?><div class="notice">Belum ada data kasir. Tambahkan kasir terlebih dahulu di menu <a href="kasir.php"><strong>Data Kasir</strong></a>.</div><?php endif; ?>
<?php if (!array_filter($produkList, fn($product) => (int)$product['jumlah_stock'] > 0)): ?><div class="notice">Stok produk masih kosong. Catat pengadaan di menu <a href="pengadaan.php"><strong>Pengadaan</strong></a> sebelum melakukan penjualan.</div><?php endif; ?>
<section class="card">
<form method="POST" id="sale-form">
    <div class="form-grid">
        <div class="form-group"><label for="nama_pelanggan">Nama Pelanggan</label><input type="text" id="nama_pelanggan" name="nama_pelanggan" maxlength="120" value="<?= e($_POST['nama_pelanggan'] ?? 'Umum') ?>" required></div>
        <div class="form-group"><label for="id_kasir">Nama Kasir</label><select id="id_kasir" name="id_kasir" required><option value="">Pilih kasir</option><?php foreach ($kasirList as $kasir): ?><option value="<?= (int)$kasir['id_kasir'] ?>" <?= (int)($_POST['id_kasir'] ?? 0) === (int)$kasir['id_kasir'] ? 'selected' : '' ?>><?= e($kasir['nama_kasir']) ?></option><?php endforeach; ?></select></div>
        <div class="form-group"><label for="tanggal_transaksi">Tanggal Transaksi</label><input type="datetime-local" id="tanggal_transaksi" name="tanggal_transaksi" value="<?= e($_POST['tanggal_transaksi'] ?? $defaultDate) ?>" required></div>
        <div class="form-group"><label for="metode_pembayaran">Metode Pembayaran</label><select id="metode_pembayaran" name="metode_pembayaran" required><option value="">Pilih metode</option><?php foreach ($metodeList as $metode): ?><option value="<?= e($metode) ?>" <?= ($_POST['metode_pembayaran'] ?? '') === $metode ? 'selected' : '' ?>><?= e($metode) ?></option><?php endforeach; ?></select></div>
    </div>
    <div class="section-heading"><div><h2>Produk yang Dibeli</h2><p>Harga satuan diambil dari katalog produk.</p></div><button type="button" class="btn btn-secondary" data-add-line="sales-lines" data-template="sales-line-template">+ Tambah Produk</button></div>
    <div class="line-items" id="sales-lines" data-line-collection data-total-target="#sale-total">
        <div class="line-item">
            <div class="form-group"><label>Nama Produk</label><select name="products[]" required><?php renderProductOptions($produkList); ?></select><small class="muted" data-product-note></small></div>
            <div class="form-group"><label>Jumlah</label><input type="number" name="quantities[]" min="1" value="1" required></div>
            <div class="form-group"><label>Subtotal</label><strong data-line-subtotal>Rp 0</strong></div>
            <button type="button" class="btn btn-ghost btn-remove" data-remove-line aria-label="Hapus produk">Hapus</button>
        </div>
    </div>
    <template id="sales-line-template"><div class="line-item">
        <div class="form-group"><label>Nama Produk</label><select name="products[]" required><?php renderProductOptions($produkList); ?></select><small class="muted" data-product-note></small></div>
        <div class="form-group"><label>Jumlah</label><input type="number" name="quantities[]" min="1" value="1" required></div>
        <div class="form-group"><label>Subtotal</label><strong data-line-subtotal>Rp 0</strong></div>
        <button type="button" class="btn btn-ghost btn-remove" data-remove-line>Hapus</button>
    </div></template>
    <div class="form-layout">
        <div class="muted">Pastikan jumlah yang dimasukkan tidak melebihi stok tersedia. Total final dihitung ulang oleh server sebelum transaksi disimpan.</div>
        <div class="total-panel"><span>Total Bayar</span><strong id="sale-total">Rp 0</strong><div class="form-actions"><button type="submit" class="btn btn-primary" <?= !$kasirList || !array_filter($produkList, fn($product) => (int)$product['jumlah_stock'] > 0) ? 'disabled' : '' ?>>Simpan Transaksi</button><a href="transaksi.php" class="btn btn-ghost">Reset</a></div></div>
    </div>
</form>
</section>
<?php require_once 'templates/footer.php'; ?>
