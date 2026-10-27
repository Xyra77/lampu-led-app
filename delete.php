<?php
require_once 'config/helpers.php';
require_once 'config/Database.php';
require_once 'models/Lampu.php';

$database = new Database();
$db = $database->getConnection();
$lampu = new Lampu($db);
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id <= 0) {
    redirectWithFlash('index.php', 'error', 'ID produk tidak valid.');
}

try {
    $lampu->id = $id;
    if ($lampu->delete()) {
        redirectWithFlash('index.php', 'success', 'Data lampu berhasil dihapus.');
    }
    redirectWithFlash('index.php', 'error', 'Data lampu tidak ditemukan.');
} catch (PDOException $exception) {
    redirectWithFlash('index.php', 'error', 'Produk tidak dapat dihapus karena sudah tercatat pada transaksi atau pengadaan.');
}
