<?php
require_once 'config/Database.php';
require_once 'config/helpers.php';
$database = new Database();
$db = $database->getConnection();
$pageTitle = 'Data Supplier';
$errors = [];
$editingId = (int) ($_GET['edit'] ?? $_POST['id_supplier'] ?? 0);
$editing = null;
if ($editingId > 0) {
    $editStmt = $db->prepare('SELECT * FROM supplier WHERE id_supplier = :id');
    $editStmt->execute([':id' => $editingId]);
    $editing = $editStmt->fetch() ?: null;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'create';
    $id = (int) ($_POST['id_supplier'] ?? 0);
    if ($action === 'delete') {
        try {
            $stmt = $db->prepare('DELETE FROM supplier WHERE id_supplier = :id');
            $stmt->execute([':id' => $id]);
            redirectWithFlash('supplier.php', 'success', 'Data supplier berhasil dihapus.');
        } catch (PDOException $e) {
            redirectWithFlash('supplier.php', 'error', 'Supplier tidak dapat dihapus karena sudah tercatat pada pengadaan barang.');
        }
    }

    $nama = trim($_POST['nama_supplier'] ?? '');
    $telepon = trim($_POST['no_telepon'] ?? '');
    $alamat = trim($_POST['alamat'] ?? '');
    if ($nama === '') $errors[] = 'Nama supplier wajib diisi.';
    if (strlen($nama) > 120) $errors[] = 'Nama supplier maksimal 120 karakter.';
    if (strlen($alamat) > 255) $errors[] = 'Alamat maksimal 255 karakter.';
    if ($action === 'update' && $id <= 0) $errors[] = 'ID supplier untuk diperbarui tidak valid.';

    if (!$errors) {
        if ($action === 'update') {
            $stmt = $db->prepare('UPDATE supplier SET nama_supplier = :nama, no_telepon = :telepon, alamat = :alamat WHERE id_supplier = :id');
            $stmt->execute([':nama' => $nama, ':telepon' => $telepon !== '' ? $telepon : null, ':alamat' => $alamat !== '' ? $alamat : null, ':id' => $id]);
            redirectWithFlash('supplier.php', 'success', 'Data supplier berhasil diperbarui.');
        }
        $stmt = $db->prepare('INSERT INTO supplier (nama_supplier, no_telepon, alamat) VALUES (:nama, :telepon, :alamat)');
        $stmt->execute([':nama' => $nama, ':telepon' => $telepon !== '' ? $telepon : null, ':alamat' => $alamat !== '' ? $alamat : null]);
        redirectWithFlash('supplier.php', 'success', 'Data supplier berhasil ditambahkan.');
    }
}

$supplierList = $db->query('SELECT s.*, (SELECT COUNT(*) FROM pembelian p WHERE p.id_supplier = s.id_supplier) AS jumlah_pembelian FROM supplier s ORDER BY s.id_supplier DESC')->fetchAll();
require_once 'templates/header.php';
?>
<div class="section-heading"><div><h2>Data Supplier</h2><p>Catat pemasok barang untuk pengadaan stok.</p></div></div>
<div class="form-layout">
    <section class="card form-card">
        <h2><?= $editing ? 'Edit Supplier' : 'Tambah Supplier' ?></h2><p class="muted"><?= $editing ? 'Perbarui informasi supplier.' : 'ID supplier dibuat otomatis oleh sistem.' ?></p>
        <?php if ($errors): ?><div class="alert alert-error"><ul><?php foreach ($errors as $error): ?><li><?= e($error) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
        <form method="POST" class="data-form">
            <input type="hidden" name="action" value="<?= $editing ? 'update' : 'create' ?>">
            <?php if ($editing): ?><input type="hidden" name="id_supplier" value="<?= (int)$editing['id_supplier'] ?>"><?php endif; ?>
            <div class="form-group"><label for="nama_supplier">Nama Supplier</label><input type="text" id="nama_supplier" name="nama_supplier" maxlength="120" value="<?= e($_POST['nama_supplier'] ?? ($editing['nama_supplier'] ?? '')) ?>" required></div>
            <div class="form-group"><label for="no_telepon">Nomor Telepon (opsional)</label><input type="tel" id="no_telepon" name="no_telepon" maxlength="30" value="<?= e($_POST['no_telepon'] ?? ($editing['no_telepon'] ?? '')) ?>"></div>
            <div class="form-group"><label for="alamat">Alamat (opsional)</label><textarea id="alamat" name="alamat" maxlength="255"><?= e($_POST['alamat'] ?? ($editing['alamat'] ?? '')) ?></textarea></div>
            <div class="form-actions"><button class="btn btn-primary" type="submit"><?= $editing ? 'Simpan Perubahan' : '+ Simpan Supplier' ?></button><?php if ($editing): ?><a href="supplier.php" class="btn btn-ghost">Batal</a><?php endif; ?></div>
        </form>
    </section>
    <section class="card">
        <div class="section-heading"><div><h2>Daftar Supplier</h2><p><?= count($supplierList) ?> supplier terdaftar.</p></div></div>
        <div class="table-wrap"><table><thead><tr><th>ID</th><th>Nama Supplier</th><th>Telepon</th><th>Alamat</th><th>Pengadaan</th><th>Aksi</th></tr></thead><tbody>
        <?php foreach ($supplierList as $row): ?><tr><td>#<?= (int)$row['id_supplier'] ?></td><td><strong><?= e($row['nama_supplier']) ?></strong></td><td><?= e($row['no_telepon'] ?: '-') ?></td><td><?= e($row['alamat'] ?: '-') ?></td><td><?= number_format((int)$row['jumlah_pembelian'], 0, ',', '.') ?></td><td class="aksi"><a class="btn btn-secondary btn-sm" href="supplier.php?edit=<?= (int)$row['id_supplier'] ?>">Edit</a><?php if ((int)$row['jumlah_pembelian'] === 0): ?><form method="POST" onsubmit="return confirm('Hapus data supplier ini?')"><input type="hidden" name="action" value="delete"><input type="hidden" name="id_supplier" value="<?= (int)$row['id_supplier'] ?>"><button class="btn btn-danger btn-sm" type="submit">Hapus</button></form><?php endif; ?></td></tr><?php endforeach; ?>
        <?php if (!$supplierList): ?><tr><td colspan="6" class="empty">Belum ada supplier. Tambahkan supplier sebelum mencatat pengadaan.</td></tr><?php endif; ?>
        </tbody></table></div>
    </section>
</div>
<?php require_once 'templates/footer.php'; ?>
