-- MIGRASI RELASI (nama pelanggan, metode pembayaran, nama produk -> pakai id)
-- Jalankan SEKALI jika db_lampu_led kamu sudah punya tabel transaksi versi sebelumnya
-- (kolom transaksi.nama_pelanggan dan transaksi.metode_pembayaran masih berupa teks).
-- Data transaksi lama ikut dipindahkan ke relasi baru. Cadangkan database dulu.
-- Jika database masih baru, import database.sql dan jangan jalankan file ini.
USE db_lampu_led;

CREATE TABLE IF NOT EXISTS pelanggan (
    id_pelanggan INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nama_pelanggan VARCHAR(120) NOT NULL,
    dibuat_pada TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_pelanggan_nama (nama_pelanggan)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS metode_pembayaran (
    id_metode INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nama_metode VARCHAR(30) NOT NULL,
    UNIQUE KEY uq_metode_nama (nama_metode)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Isi master dari data lama (urutan: metode standar dulu, lalu sisa yang pernah dipakai)
INSERT IGNORE INTO metode_pembayaran (nama_metode) VALUES ('Tunai'), ('QRIS'), ('Transfer Bank'), ('Kartu Debit'), ('E-Wallet');
INSERT IGNORE INTO metode_pembayaran (nama_metode) SELECT DISTINCT metode_pembayaran FROM transaksi;
INSERT IGNORE INTO pelanggan (nama_pelanggan) VALUES ('Umum');
INSERT IGNORE INTO pelanggan (nama_pelanggan) SELECT DISTINCT nama_pelanggan FROM transaksi;

-- Kolom FK sementara boleh NULL supaya baris lama bisa diisi dulu
ALTER TABLE transaksi
    ADD COLUMN id_pelanggan INT UNSIGNED NULL AFTER tanggal_transaksi,
    ADD COLUMN id_metode INT UNSIGNED NULL AFTER id_kasir;

UPDATE transaksi t JOIN pelanggan p ON p.nama_pelanggan = t.nama_pelanggan SET t.id_pelanggan = p.id_pelanggan;
UPDATE transaksi t JOIN metode_pembayaran m ON m.nama_metode = t.metode_pembayaran SET t.id_metode = m.id_metode;

-- Setelah semua terisi: wajib NOT NULL, pasang FK, buang kolom teks lama
ALTER TABLE transaksi
    MODIFY id_pelanggan INT UNSIGNED NOT NULL,
    MODIFY id_metode INT UNSIGNED NOT NULL,
    ADD CONSTRAINT fk_transaksi_pelanggan FOREIGN KEY (id_pelanggan) REFERENCES pelanggan(id_pelanggan)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    ADD CONSTRAINT fk_transaksi_metode FOREIGN KEY (id_metode) REFERENCES metode_pembayaran(id_metode)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    DROP COLUMN nama_pelanggan,
    DROP COLUMN metode_pembayaran;

-- nama produk cukup lewat id_lampu (relasi ke lampu_led), kolom salinan teks dibuang
ALTER TABLE transaksi_detail DROP COLUMN nama_produk;
ALTER TABLE pembelian_detail DROP COLUMN nama_produk;
