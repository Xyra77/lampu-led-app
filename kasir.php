<?php
require_once 'config/Database.php';
require_once 'config/helpers.php';
$database = new Database();
$db = $database->getConnection();
$pageTitle = 'Data Kasir';
$errors = [];
$editingId = (int) ($_GET['edit'] ?? $_POST['id_kasir'] ?? 0);
$editing = null;
if ($editingId > 0) {
    $editStmt = $db->prepare('SELECT * FROM kasir WHERE id_kasir = :id');
    $editStmt->execute([':id' => $editingId]);
    $editing = $editStmt->fetch() ?: null;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'create';
    $id = (int) ($_POST['id_kasir'] ?? 0);
    if ($action === 'delete') {
        try {
            $stmt = $db->prepare('DELETE FROM kasir WHERE id_kasir = :id');
            $stmt->execute([':id' => $id]);
            redirectWithFlash('kasir.php', 'success', 'Data kasir berhasil dihapus.');
        } catch (PDOException $e) {
            redirectWithFlash('kasir.php', 'error', 'Kasir tidak dapat dihapus karena sudah dipakai pada transaksi.');
        }
    }

    $nama = trim($_POST['nama_kasir'] ?? '');
    $telepon = trim($_POST['no_telepon'] ?? '');
    if ($nama === '') $errors[] = 'Nama kasir wajib diisi.';
    if (strlen($nama) > 100) $errors[] = 'Nama kasir maksimal 100 karakter.';
    if ($action === 'update' && $id <= 0) $errors[] = 'ID kasir untuk diperbarui tidak valid.';

    if (!$errors) {
        if ($action === 'update') {
            $stmt = $db->prepare('UPDATE kasir SET nama_kasir = :nama, no_telepon = :telepon WHERE id_kasir = :id');
            $stmt->execute([':nama' => $nama, ':telepon' => $telepon !== '' ? $telepon : null, ':id' => $id]);
            redirectWithFlash('kasir.php', 'success', 'Data kasir berhasil diperbarui.');
        }
        $stmt = $db->prepare('INSERT INTO kasir (nama_kasir, no_telepon) VALUES (:nama, :telepon)');
        $stmt->execute([':nama' => $nama, ':telepon' => $telepon !== '' ? $telepon : null]);
        redirectWithFlash('kasir.php', 'success', 'Data kasir berhasil ditambahkan.');
    }
}

$kasirList = $db->query('SELECT k.*, (SELECT COUNT(*) FROM transaksi t WHERE t.id_kasir = k.id_kasir) AS jumlah_transaksi FROM kasir k ORDER BY k.id_kasir DESC')->fetchAll();
require_once 'templates/header.php';
?>
<div class="section-heading"><div><h2>Data Kasir</h2><p>Kelola petugas yang melayani transaksi penjualan.</p></div></div>
<div class="form-layout">
    <section class="card form-card">
        <h2><?= $editing ? 'Edit Kasir' : 'Tambah Kasir' ?></h2><p class="muted"><?= $editing ? 'Perbarui informasi kasir.' : 'ID kasir dibuat otomatis oleh sistem.' ?></p>
        <?php if ($errors): ?><div class="alert alert-error"><ul><?php foreach ($errors as $error): ?><li><?= e($error) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
        <form method="POST" class="data-form">
            <input type="hidden" name="action" value="<?= $editing ? 'update' : 'create' ?>">
            <?php if ($editing): ?><input type="hidden" name="id_kasir" value="<?= (int)$editing['id_kasir'] ?>"><?php endif; ?>
            <div class="form-group"><label for="nama_kasir">Nama Kasir</label><input type="text" id="nama_kasir" name="nama_kasir" maxlength="100" value="<?= e($_POST['nama_kasir'] ?? ($editing['nama_kasir'] ?? '')) ?>" required></div>
            <div class="form-group"><label for="no_telepon">Nomor Telepon (opsional)</label><input type="tel" id="no_telepon" name="no_telepon" maxlength="30" value="<?= e($_POST['no_telepon'] ?? ($editing['no_telepon'] ?? '')) ?>" placeholder="08xxxxxxxxxx"></div>
            <div class="form-actions"><button class="btn btn-primary" type="submit"><?= $editing ? 'Simpan Perubahan' : '+ Simpan Kasir' ?></button><?php if ($editing): ?><a href="kasir.php" class="btn btn-ghost">Batal</a><?php endif; ?></div>
        </form>
    </section>
    <section class="card">
        <div class="section-heading"><div><h2>Daftar Kasir</h2><p><?= count($kasirList) ?> kasir terdaftar.</p></div></div>
        <div class="table-wrap"><table><thead><tr><th>ID</th><th>Nama Kasir</th><th>Telepon</th><th>Transaksi</th><th>Aksi</th></tr></thead><tbody>
        <?php foreach ($kasirList as $row): ?><tr><td>#<?= (int)$row['id_kasir'] ?></td><td><strong><?= e($row['nama_kasir']) ?></strong></td><td><?= e($row['no_telepon'] ?: '-') ?></td><td><?= number_format((int)$row['jumlah_transaksi'], 0, ',', '.') ?></td><td class="aksi"><a class="btn btn-secondary btn-sm" href="kasir.php?edit=<?= (int)$row['id_kasir'] ?>">Edit</a><?php if ((int)$row['jumlah_transaksi'] === 0): ?><form method="POST" onsubmit="return confirm('Hapus data kasir ini?')"><input type="hidden" name="action" value="delete"><input type="hidden" name="id_kasir" value="<?= (int)$row['id_kasir'] ?>"><button class="btn btn-danger btn-sm" type="submit">Hapus</button></form><?php endif; ?></td></tr><?php endforeach; ?>
        <?php if (!$kasirList): ?><tr><td colspan="5" class="empty">Belum ada kasir. Tambahkan kasir sebelum membuat transaksi.</td></tr><?php endif; ?>
        </tbody></table></div>
    </section>
</div>
<?php require_once 'templates/footer.php'; ?>
