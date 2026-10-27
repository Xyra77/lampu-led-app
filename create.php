<?php
require_once "config/helpers.php";
require_once "config/Database.php";
require_once "models/Lampu.php";

$database = new Database();
$db = $database->getConnection();

$lampu = new Lampu($db);

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $lampu->merk = $_POST['merk'] ?? '';
    $lampu->tipe = $_POST['tipe'] ?? '';
    $lampu->warna = $_POST['warna'] ?? '';
    $lampu->watt = $_POST['watt'] ?? 0;
    $lampu->harga_terendah = $_POST['harga_terendah'] ?? 0;
    $lampu->jumlah_stock = $_POST['jumlah_stock'] ?? 0;
    $lampu->data_asli = isset($_POST['data_asli']) ? 1 : 0;

    // Validasi sederhana
    if ($lampu->merk === '') $errors[] = "Merk wajib diisi.";
    if ($lampu->tipe === '') $errors[] = "Tipe wajib diisi.";
    if ($lampu->warna === '') $errors[] = "Warna wajib diisi.";
    if ((int) $lampu->watt <= 0) $errors[] = "Watt harus lebih dari 0.";
    if ((int) $lampu->harga_terendah <= 0) $errors[] = "Harga harus lebih dari 0.";
    if ((int) $lampu->jumlah_stock < 0) $errors[] = "Stok tidak boleh negatif.";

    if (empty($errors)) {
        // Generate nama_lampu & harga_label otomatis
        $lampu->nama_lampu = "LED {$lampu->merk} {$lampu->tipe} ({$lampu->warna})";
        $lampu->harga_label = "Rp" . number_format((int) $lampu->harga_terendah, 0, ',', '.');

        if ($lampu->create()) {
            $_SESSION['flash'] = ['type' => 'success', 'message' => 'Data lampu berhasil ditambahkan.'];
            header("Location: index.php");
            exit;
        } else {
            $errors[] = "Gagal menyimpan data, coba lagi.";
        }
    }
}

require_once "templates/header.php";
?>

<section class="card form-card">
    <h2>Tambah Data Lampu</h2>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-error">
            <ul>
                <?php foreach ($errors as $e): ?>
                    <li><?= htmlspecialchars($e) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="POST" class="data-form">
        <div class="form-group">
            <label for="merk">Merk</label>
            <input type="text" id="merk" name="merk" value="<?= htmlspecialchars($_POST['merk'] ?? '') ?>" required>
        </div>
        <div class="form-group">
            <label for="tipe">Tipe</label>
            <input type="text" id="tipe" name="tipe" placeholder="Bulb / Bohlam / Capsule / Neo Bulb" value="<?= htmlspecialchars($_POST['tipe'] ?? '') ?>" required>
        </div>
        <div class="form-group">
            <label for="warna">Warna</label>
            <select id="warna" name="warna" required>
                <option value="">-- pilih warna --</option>
                <option value="putih" <?= (($_POST['warna'] ?? '') === 'putih') ? 'selected' : '' ?>>Putih</option>
                <option value="kuning" <?= (($_POST['warna'] ?? '') === 'kuning') ? 'selected' : '' ?>>Kuning</option>
            </select>
        </div>
        <div class="form-group">
            <label for="watt">Watt</label>
            <input type="number" id="watt" name="watt" min="1" value="<?= htmlspecialchars($_POST['watt'] ?? '') ?>" required>
        </div>
        <div class="form-group">
            <label for="harga_terendah">Harga (Rp)</label>
            <input type="number" id="harga_terendah" name="harga_terendah" min="1" value="<?= htmlspecialchars($_POST['harga_terendah'] ?? '') ?>" required>
        </div>
        <div class="form-group">
            <label for="jumlah_stock">Jumlah Stok Awal</label>
            <input type="number" id="jumlah_stock" name="jumlah_stock" min="0" value="<?= htmlspecialchars($_POST['jumlah_stock'] ?? '0') ?>" required>
        </div>
        <div class="form-group form-check">
            <label>
                <input type="checkbox" name="data_asli" value="1" <?= isset($_POST['data_asli']) ? 'checked' : '' ?>>
                Data asli (bukan hasil estimasi)
            </label>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Simpan</button>
            <a href="index.php" class="btn btn-ghost">Batal</a>
        </div>
    </form>
</section>

<?php require_once "templates/footer.php"; ?>
