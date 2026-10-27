<?php
require_once "config/Database.php";
require_once "models/Lampu.php";

$database = new Database();
$db = $database->getConnection();

$lampu = new Lampu($db);

// Ambil parameter filter dari form GET
$keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : "";
$filterMerk = isset($_GET['merk']) ? trim($_GET['merk']) : "";
$filterTipe = isset($_GET['tipe']) ? trim($_GET['tipe']) : "";

$stmt = $lampu->getAll($keyword, $filterMerk, $filterTipe);
$jumlahProduk = (int) $db->query("SELECT COUNT(*) FROM lampu_led")->fetchColumn();
$totalStok = (int) $db->query("SELECT COALESCE(SUM(jumlah_stock), 0) FROM lampu_led")->fetchColumn();
$penjualanHariIni = (int) $db->query("SELECT COALESCE(SUM(total_bayar), 0) FROM transaksi WHERE DATE(tanggal_transaksi) = CURDATE()")->fetchColumn();
$daftarMerk = $lampu->getDistinctMerk();
$daftarTipe = $lampu->getDistinctTipe();

require_once "templates/header.php";
?>

<section class="stats-grid">
    <div class="stat-card"><span>Total Produk</span><strong><?= number_format($jumlahProduk, 0, ',', '.') ?></strong></div>
    <div class="stat-card"><span>Total Stok</span><strong><?= number_format($totalStok, 0, ',', '.') ?> unit</strong></div>
    <div class="stat-card"><span>Penjualan Hari Ini</span><strong><?= 'Rp ' . number_format($penjualanHariIni, 0, ',', '.') ?></strong></div>
</section>

<section class="card">
    <form method="GET" class="filter-form">
        <input
            type="text"
            name="keyword"
            placeholder="Cari nama lampu..."
            value="<?= htmlspecialchars($keyword) ?>"
        >
        <select name="merk">
            <option value="">Semua Merk</option>
            <?php foreach ($daftarMerk as $m): ?>
                <option value="<?= htmlspecialchars($m) ?>" <?= $filterMerk === $m ? 'selected' : '' ?>>
                    <?= htmlspecialchars($m) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <select name="tipe">
            <option value="">Semua Tipe</option>
            <?php foreach ($daftarTipe as $t): ?>
                <option value="<?= htmlspecialchars($t) ?>" <?= $filterTipe === $t ? 'selected' : '' ?>>
                    <?= htmlspecialchars($t) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="btn btn-secondary">Filter</button>
        <a href="index.php" class="btn btn-ghost">Reset</a>
        <a href="create.php" class="btn btn-primary btn-add">+ Tambah Data</a>
    </form>
</section>

<section class="card">
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nama Lampu</th>
                    <th>Merk</th>
                    <th>Tipe</th>
                    <th>Warna</th>
                    <th>Watt</th>
                    <th>Harga Jual</th>
                    <th>Stok</th>
                    <th>Data Asli</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php $total = 0; ?>
                <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)): $total++; ?>
                    <tr>
                        <td><?= $row['id'] ?></td>
                        <td><?= htmlspecialchars($row['nama_lampu']) ?></td>
                        <td><?= htmlspecialchars($row['merk']) ?></td>
                        <td><?= htmlspecialchars($row['tipe']) ?></td>
                        <td><?= htmlspecialchars($row['warna']) ?></td>
                        <td><?= $row['watt'] ?> W</td>
                        <td><?= htmlspecialchars($row['harga_label']) ?></td>
                        <td><span class="stock-pill <?= (int)$row['jumlah_stock'] <= 5 ? 'stock-low' : 'stock-ok' ?>"><?= number_format((int)$row['jumlah_stock'], 0, ',', '.') ?></span></td>
                        <td>
                            <?php if ($row['data_asli']): ?>
                                <span class="badge badge-yes">Ya</span>
                            <?php else: ?>
                                <span class="badge badge-no">Tidak</span>
                            <?php endif; ?>
                        </td>
                        <td class="aksi">
                            <a href="edit.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-secondary">Edit</a>
                            <a
                                href="delete.php?id=<?= $row['id'] ?>"
                                class="btn btn-sm btn-danger"
                                onclick="return confirm('Yakin mau hapus data \'<?= htmlspecialchars(addslashes($row['nama_lampu'])) ?>\'?');"
                            >Hapus</a>
                        </td>
                    </tr>
                <?php endwhile; ?>

                <?php if ($total === 0): ?>
                    <tr>
                        <td colspan="10" class="empty">Data tidak ditemukan.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <p class="total-info">Menampilkan <?= $total ?> data.</p>
</section>

<?php require_once "templates/footer.php"; ?>
