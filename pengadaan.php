<?php
require_once 'config/Database.php';
require_once 'config/helpers.php';
$database = new Database();
$db = $database->getConnection();
$pageTitle = 'Pengadaan Barang';
$errors = [];
$supplierList = $db->query('SELECT id_supplier, nama_supplier FROM supplier ORDER BY nama_supplier')->fetchAll();
$produkList = $db->query('SELECT id, nama_lampu, watt, harga_terendah, jumlah_stock FROM lampu_led ORDER BY nama_lampu, watt')->fetchAll();
$defaultDate = date('Y-m-d\TH:i');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idSupplier = (int) ($_POST['id_supplier'] ?? 0);
    $tanggalInput = trim($_POST['tanggal_pembelian'] ?? '');
    $productIds = $_POST['products'] ?? [];
    $quantities = $_POST['quantities'] ?? [];
    $prices = $_POST['prices'] ?? [];
    $tanggal = DateTime::createFromFormat('!Y-m-d\TH:i', $tanggalInput) ?: DateTime::createFromFormat('Y-m-d\TH:i', $tanggalInput);

    if ($idSupplier <= 0) $errors[] = 'Pilih supplier untuk pengadaan barang.';
    if (!$tanggal) $errors[] = 'Tanggal pembelian tidak valid.';
    if (!is_array($productIds) || !is_array($quantities) || !is_array($prices)) $errors[] = 'Format daftar produk tidak valid.';

    $items = [];
    if (is_array($productIds) && is_array($quantities) && is_array($prices)) {
        foreach ($productIds as $index => $rawId) {
            $productId = (int) $rawId;
            if ($productId <= 0) continue;
            $qty = (int) ($quantities[$index] ?? 0);
            $price = (int) ($prices[$index] ?? 0);
            if ($qty <= 0) $errors[] = 'Jumlah barang pada pengadaan harus minimal 1.';
            if ($price <= 0) $errors[] = 'Harga satuan pengadaan harus lebih dari 0.';
            if ($qty > 0 && $price > 0) $items[] = ['id' => $productId, 'jumlah' => $qty, 'harga' => $price];
        }
    }
    if (!$items) $errors[] = 'Tambahkan minimal satu produk yang dibeli.';

    if (!$errors) {
        try {
            $db->beginTransaction();
            $checkSupplier = $db->prepare('SELECT id_supplier FROM supplier WHERE id_supplier = :id');
            $checkSupplier->execute([':id' => $idSupplier]);
            if (!$checkSupplier->fetchColumn()) throw new RuntimeException('Data supplier tidak ditemukan. Muat ulang halaman lalu pilih supplier yang aktif.');

            $selectProduct = $db->prepare('SELECT id, nama_lampu, watt FROM lampu_led WHERE id = :id FOR UPDATE');
            $preparedItems = [];
            $totalHarga = 0;
            foreach ($items as $item) {
                $selectProduct->execute([':id' => $item['id']]);
                $product = $selectProduct->fetch();
                if (!$product) throw new RuntimeException('Salah satu produk tidak ditemukan.');
                $subtotal = $item['jumlah'] * $item['harga'];
                $totalHarga += $subtotal;
                $preparedItems[] = ['id' => $item['id'], 'nama' => $product['nama_lampu'] . ' (' . $product['watt'] . ' W)', 'jumlah' => $item['jumlah'], 'harga' => $item['harga'], 'subtotal' => $subtotal];
            }

            $insertHeader = $db->prepare('INSERT INTO pembelian (tanggal_pembelian, id_supplier, total_harga) VALUES (:tanggal, :supplier, :total)');
            $insertHeader->execute([':tanggal' => $tanggal->format('Y-m-d H:i:s'), ':supplier' => $idSupplier, ':total' => $totalHarga]);
            $purchaseId = (int) $db->lastInsertId();
            $insertDetail = $db->prepare('INSERT INTO pembelian_detail (id_pembelian, id_lampu, nama_produk, jumlah, harga_satuan, subtotal) VALUES (:pembelian, :lampu, :nama, :jumlah, :harga, :subtotal)');
            $incrementStock = $db->prepare('UPDATE lampu_led SET jumlah_stock = jumlah_stock + :jumlah WHERE id = :id');
            foreach ($preparedItems as $item) {
                $insertDetail->execute([':pembelian' => $purchaseId, ':lampu' => $item['id'], ':nama' => $item['nama'], ':jumlah' => $item['jumlah'], ':harga' => $item['harga'], ':subtotal' => $item['subtotal']]);
                $incrementStock->execute([':jumlah' => $item['jumlah'], ':id' => $item['id']]);
            }
            $db->commit();
            redirectWithFlash('pengadaan.php', 'success', 'Pengadaan #' . $purchaseId . ' berhasil disimpan. Total pembelian ' . rupiah($totalHarga) . '. Stok produk sudah ditambahkan.');
        } catch (Throwable $exception) {
            if ($db->inTransaction()) $db->rollBack();
            $errors[] = $exception instanceof RuntimeException ? $exception->getMessage() : 'Pengadaan gagal disimpan. Periksa koneksi database lalu coba lagi.';
        }
    }
}

$historyQuery = "SELECT p.id_pembelian, p.tanggal_pembelian, p.total_harga, s.nama_supplier,
    GROUP_CONCAT(CONCAT(pd.nama_produk, ' × ', pd.jumlah) ORDER BY pd.id_detail_pembelian SEPARATOR ', ') AS daftar_barang,
    COALESCE(SUM(pd.jumlah), 0) AS total_jumlah
    FROM pembelian p
    JOIN supplier s ON s.id_supplier = p.id_supplier
    LEFT JOIN pembelian_detail pd ON pd.id_pembelian = p.id_pembelian
    GROUP BY p.id_pembelian, p.tanggal_pembelian, p.total_harga, s.nama_supplier
    ORDER BY p.id_pembelian DESC LIMIT 100";
$history = $db->query($historyQuery)->fetchAll();

function renderPurchaseProductOptions(array $produkList): void
{
    echo '<option value="">Pilih produk lampu LED</option>';
    foreach ($produkList as $produk) {
        echo '<option value="' . (int) $produk['id'] . '" data-price="' . (int) $produk['harga_terendah'] . '" data-stock="' . (int) $produk['jumlah_stock'] . '">' . e($produk['nama_lampu']) . ' · ' . (int)$produk['watt'] . ' W · stok ' . (int) $produk['jumlah_stock'] . '</option>';
    }
}
require_once 'templates/header.php';
?>
<div class="section-heading"><div><h2>Pengadaan Barang</h2><p>Catat pembelian dari supplier. Setiap pengadaan yang berhasil akan otomatis menambah stok produk.</p></div></div>
<?php if ($errors): ?><div class="alert alert-error"><ul><?php foreach ($errors as $error): ?><li><?= e($error) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
<?php if (!$supplierList): ?><div class="notice">Belum ada supplier. Tambahkan data supplier terlebih dahulu pada menu <a href="supplier.php"><strong>Supplier</strong></a>.</div><?php endif; ?>
<section class="card">
    <h2>Catat Pembelian Barang</h2><p class="muted">Harga satuan di sini adalah harga beli dari supplier, bukan harga jual produk.</p>
    <form method="POST" id="purchase-form">
        <div class="form-grid">
            <div class="form-group"><label for="id_supplier">Supplier</label><select id="id_supplier" name="id_supplier" required><option value="">Pilih supplier</option><?php foreach ($supplierList as $supplier): ?><option value="<?= (int)$supplier['id_supplier'] ?>" <?= (int)($_POST['id_supplier'] ?? 0) === (int)$supplier['id_supplier'] ? 'selected' : '' ?>><?= e($supplier['nama_supplier']) ?></option><?php endforeach; ?></select></div>
            <div class="form-group"><label for="tanggal_pembelian">Tanggal Pembelian</label><input type="datetime-local" id="tanggal_pembelian" name="tanggal_pembelian" value="<?= e($_POST['tanggal_pembelian'] ?? $defaultDate) ?>" required></div>
        </div>
        <div class="section-heading"><div><h2>Daftar Barang</h2><p>Pilih nama produk, jumlah yang dibeli, dan harga beli satuannya.</p></div><button type="button" class="btn btn-secondary" data-add-line="purchase-lines" data-template="purchase-line-template">+ Tambah Barang</button></div>
        <div class="line-items purchase-lines" id="purchase-lines" data-line-collection data-total-target="#purchase-total">
            <div class="line-item">
                <div class="form-group"><label>Nama Barang / Produk</label><select name="products[]" required><?php renderPurchaseProductOptions($produkList); ?></select></div>
                <div class="form-group"><label>Jumlah</label><input type="number" name="quantities[]" min="1" value="1" required></div>
                <div class="form-group"><label>Harga Satuan (Rp)</label><input type="number" name="prices[]" min="1" step="1" value="<?= e($_POST['prices'][0] ?? '') ?>" placeholder="Contoh: 15000" required></div>
                <button type="button" class="btn btn-ghost btn-remove" data-remove-line>Hapus</button>
                <div class="muted">Subtotal: <strong data-line-subtotal>Rp 0</strong></div>
            </div>
        </div>
        <template id="purchase-line-template"><div class="line-item">
            <div class="form-group"><label>Nama Barang / Produk</label><select name="products[]" required><?php renderPurchaseProductOptions($produkList); ?></select></div>
            <div class="form-group"><label>Jumlah</label><input type="number" name="quantities[]" min="1" value="1" required></div>
            <div class="form-group"><label>Harga Satuan (Rp)</label><input type="number" name="prices[]" min="1" step="1" placeholder="Contoh: 15000" required></div>
            <button type="button" class="btn btn-ghost btn-remove" data-remove-line>Hapus</button>
            <div class="muted">Subtotal: <strong data-line-subtotal>Rp 0</strong></div>
        </div></template>
        <div class="form-layout">
            <div class="muted">Nama barang dan nama produk disimpan sebagai snapshot di detail pengadaan, beserta jumlah, harga satuan, dan subtotal.</div>
            <div class="total-panel"><span>Total Harga Pembelian</span><strong id="purchase-total">Rp 0</strong><div class="form-actions"><button type="submit" class="btn btn-primary" <?= !$supplierList ? 'disabled' : '' ?>>Simpan Pengadaan</button><a href="pengadaan.php" class="btn btn-ghost">Reset</a></div></div>
        </div>
    </form>
</section>
<section class="card">
    <div class="section-heading"><div><h2>Riwayat Pengadaan</h2><p>100 pengadaan terakhir ditampilkan.</p></div></div>
    <div class="table-wrap"><table><thead><tr><th>ID Pembelian</th><th>Tanggal Pembelian</th><th>Nama Supplier</th><th>Nama Barang / Produk</th><th>Jumlah</th><th>Total Harga</th></tr></thead><tbody>
    <?php foreach ($history as $row): ?><tr><td><a href="detail_pengadaan.php?id=<?= (int)$row['id_pembelian'] ?>"><strong>#<?= (int)$row['id_pembelian'] ?></strong></a></td><td><?= e(date('d-m-Y H:i', strtotime($row['tanggal_pembelian']))) ?></td><td><?= e($row['nama_supplier']) ?></td><td><?= e($row['daftar_barang'] ?: '-') ?></td><td><?= number_format((int)$row['total_jumlah'], 0, ',', '.') ?></td><td><strong><?= e(rupiah($row['total_harga'])) ?></strong></td></tr><?php endforeach; ?>
    <?php if (!$history): ?><tr><td colspan="6" class="empty">Belum ada pengadaan barang.</td></tr><?php endif; ?>
    </tbody></table></div>
</section>
<?php require_once 'templates/footer.php'; ?>
