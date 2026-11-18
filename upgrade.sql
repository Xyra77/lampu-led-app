-- MIGRASI UNTUK INSTALASI LAMA
-- Jalankan sekali jika db_lampu_led sudah ada dari versi katalog CRUD sebelumnya.
-- Jika database masih baru, import database.sql dan jangan jalankan file ini.
USE db_lampu_led;

ALTER TABLE lampu_led
    ADD COLUMN jumlah_stock INT UNSIGNED NOT NULL DEFAULT 0 AFTER harga_label;

CREATE TABLE IF NOT EXISTS kasir (
    id_kasir INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nama_kasir VARCHAR(100) NOT NULL,
    no_telepon VARCHAR(30) NULL,
    dibuat_pada TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS pelanggan (
    id_pelanggan INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nama_pelanggan VARCHAR(120) NOT NULL,
    dibuat_pada TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_pelanggan_nama (nama_pelanggan)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT IGNORE INTO pelanggan (nama_pelanggan) VALUES ('Umum');

CREATE TABLE IF NOT EXISTS metode_pembayaran (
    id_metode INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nama_metode VARCHAR(30) NOT NULL,
    UNIQUE KEY uq_metode_nama (nama_metode)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT IGNORE INTO metode_pembayaran (nama_metode) VALUES ('Tunai'), ('QRIS'), ('Transfer Bank'), ('Kartu Debit'), ('E-Wallet');

CREATE TABLE IF NOT EXISTS supplier (
    id_supplier INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nama_supplier VARCHAR(120) NOT NULL,
    no_telepon VARCHAR(30) NULL,
    alamat VARCHAR(255) NULL,
    dibuat_pada TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS transaksi (
    id_transaksi BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tanggal_transaksi DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    id_pelanggan INT UNSIGNED NOT NULL,
    id_kasir INT UNSIGNED NOT NULL,
    id_metode INT UNSIGNED NOT NULL,
    total_bayar BIGINT UNSIGNED NOT NULL DEFAULT 0,
    dibuat_pada TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_transaksi_pelanggan FOREIGN KEY (id_pelanggan) REFERENCES pelanggan(id_pelanggan)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_transaksi_kasir FOREIGN KEY (id_kasir) REFERENCES kasir(id_kasir)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_transaksi_metode FOREIGN KEY (id_metode) REFERENCES metode_pembayaran(id_metode)
        ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS transaksi_detail (
    id_detail BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_transaksi BIGINT UNSIGNED NOT NULL,
    id_lampu INT NOT NULL,
    harga_satuan BIGINT UNSIGNED NOT NULL,
    jumlah_produk INT UNSIGNED NOT NULL,
    subtotal BIGINT UNSIGNED NOT NULL,
    CONSTRAINT fk_transaksi_detail_transaksi FOREIGN KEY (id_transaksi) REFERENCES transaksi(id_transaksi)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_transaksi_detail_lampu FOREIGN KEY (id_lampu) REFERENCES lampu_led(id)
        ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS pembelian (
    id_pembelian BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tanggal_pembelian DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    id_supplier INT UNSIGNED NOT NULL,
    total_harga BIGINT UNSIGNED NOT NULL DEFAULT 0,
    dibuat_pada TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_pembelian_supplier FOREIGN KEY (id_supplier) REFERENCES supplier(id_supplier)
        ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS pembelian_detail (
    id_detail_pembelian BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_pembelian BIGINT UNSIGNED NOT NULL,
    id_lampu INT NOT NULL,
    jumlah INT UNSIGNED NOT NULL,
    harga_satuan BIGINT UNSIGNED NOT NULL,
    subtotal BIGINT UNSIGNED NOT NULL,
    CONSTRAINT fk_pembelian_detail_pembelian FOREIGN KEY (id_pembelian) REFERENCES pembelian(id_pembelian)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_pembelian_detail_lampu FOREIGN KEY (id_lampu) REFERENCES lampu_led(id)
        ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
