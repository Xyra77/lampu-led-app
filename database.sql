CREATE DATABASE IF NOT EXISTS db_lampu_led CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE db_lampu_led;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS pembelian_detail;
DROP TABLE IF EXISTS pembelian;
DROP TABLE IF EXISTS transaksi_detail;
DROP TABLE IF EXISTS transaksi;
DROP TABLE IF EXISTS supplier;
DROP TABLE IF EXISTS kasir;
DROP TABLE IF EXISTS lampu_led;
SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE lampu_led (
    id INT AUTO_INCREMENT PRIMARY KEY,
    merk VARCHAR(50) NOT NULL,
    tipe VARCHAR(50) NOT NULL,
    warna VARCHAR(20) NOT NULL,
    nama_lampu VARCHAR(150) NOT NULL,
    watt INT NOT NULL,
    harga_terendah INT NOT NULL,
    harga_label VARCHAR(30) NOT NULL,
    jumlah_stock INT UNSIGNED NOT NULL DEFAULT 0,
    data_asli TINYINT(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE kasir (
    id_kasir INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nama_kasir VARCHAR(100) NOT NULL,
    no_telepon VARCHAR(30) NULL,
    dibuat_pada TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE supplier (
    id_supplier INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nama_supplier VARCHAR(120) NOT NULL,
    no_telepon VARCHAR(30) NULL,
    alamat VARCHAR(255) NULL,
    dibuat_pada TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE transaksi (
    id_transaksi BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tanggal_transaksi DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    nama_pelanggan VARCHAR(120) NOT NULL,
    id_kasir INT UNSIGNED NOT NULL,
    metode_pembayaran VARCHAR(30) NOT NULL,
    total_bayar BIGINT UNSIGNED NOT NULL DEFAULT 0,
    dibuat_pada TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_transaksi_kasir FOREIGN KEY (id_kasir) REFERENCES kasir(id_kasir)
        ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE transaksi_detail (
    id_detail BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_transaksi BIGINT UNSIGNED NOT NULL,
    id_lampu INT NOT NULL,
    nama_produk VARCHAR(150) NOT NULL,
    harga_satuan BIGINT UNSIGNED NOT NULL,
    jumlah_produk INT UNSIGNED NOT NULL,
    subtotal BIGINT UNSIGNED NOT NULL,
    CONSTRAINT fk_transaksi_detail_transaksi FOREIGN KEY (id_transaksi) REFERENCES transaksi(id_transaksi)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_transaksi_detail_lampu FOREIGN KEY (id_lampu) REFERENCES lampu_led(id)
        ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE pembelian (
    id_pembelian BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tanggal_pembelian DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    id_supplier INT UNSIGNED NOT NULL,
    total_harga BIGINT UNSIGNED NOT NULL DEFAULT 0,
    dibuat_pada TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_pembelian_supplier FOREIGN KEY (id_supplier) REFERENCES supplier(id_supplier)
        ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE pembelian_detail (
    id_detail_pembelian BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_pembelian BIGINT UNSIGNED NOT NULL,
    id_lampu INT NOT NULL,
    nama_produk VARCHAR(150) NOT NULL,
    jumlah INT UNSIGNED NOT NULL,
    harga_satuan BIGINT UNSIGNED NOT NULL,
    subtotal BIGINT UNSIGNED NOT NULL,
    CONSTRAINT fk_pembelian_detail_pembelian FOREIGN KEY (id_pembelian) REFERENCES pembelian(id_pembelian)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_pembelian_detail_lampu FOREIGN KEY (id_lampu) REFERENCES lampu_led(id)
        ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO lampu_led (id, merk, tipe, warna, nama_lampu, watt, harga_terendah, harga_label, data_asli) VALUES
(1, 'Philips', 'Bulb', 'putih', 'LED Philips Bulb (putih)', 3, 20000, 'Rp20.000', 0),
(2, 'Philips', 'Bulb', 'putih', 'LED Philips Bulb (putih)', 5, 22000, 'Rp22.000', 0),
(3, 'Philips', 'Bulb', 'putih', 'LED Philips Bulb (putih)', 7, 25000, 'Rp25.000', 0),
(4, 'Philips', 'Bulb', 'putih', 'LED Philips Bulb (putih)', 9, 28000, 'Rp28.000', 0),
(5, 'Philips', 'Bulb', 'putih', 'LED Philips Bulb (putih)', 10, 30000, 'Rp30.000', 1),
(6, 'Philips', 'Bulb', 'putih', 'LED Philips Bulb (putih)', 12, 42000, 'Rp42.000', 0),
(7, 'Philips', 'Bulb', 'putih', 'LED Philips Bulb (putih)', 13, 55000, 'Rp55.000', 0),
(8, 'Philips', 'Bulb', 'putih', 'LED Philips Bulb (putih)', 15, 60000, 'Rp60.000', 0),
(9, 'Philips', 'Bulb', 'putih', 'LED Philips Bulb (putih)', 18, 72000, 'Rp72.000', 0),
(10, 'Philips', 'Bulb', 'putih', 'LED Philips Bulb (putih)', 20, 82000, 'Rp82.000', 0),
(11, 'Philips', 'Bulb', 'kuning', 'LED Philips Bulb (kuning)', 3, 25000, 'Rp25.000', 0),
(12, 'Philips', 'Bulb', 'kuning', 'LED Philips Bulb (kuning)', 5, 27000, 'Rp27.000', 0),
(13, 'Philips', 'Bulb', 'kuning', 'LED Philips Bulb (kuning)', 7, 30000, 'Rp30.000', 0),
(14, 'Philips', 'Bulb', 'kuning', 'LED Philips Bulb (kuning)', 9, 33000, 'Rp33.000', 0),
(15, 'Philips', 'Bulb', 'kuning', 'LED Philips Bulb (kuning)', 10, 35000, 'Rp35.000', 0),
(16, 'Philips', 'Bulb', 'kuning', 'LED Philips Bulb (kuning)', 12, 47000, 'Rp47.000', 0),
(17, 'Philips', 'Bulb', 'kuning', 'LED Philips Bulb (kuning)', 13, 60000, 'Rp60.000', 0),
(18, 'Philips', 'Bulb', 'kuning', 'LED Philips Bulb (kuning)', 15, 65000, 'Rp65.000', 0),
(19, 'Philips', 'Bulb', 'kuning', 'LED Philips Bulb (kuning)', 18, 77000, 'Rp77.000', 0),
(20, 'Philips', 'Bulb', 'kuning', 'LED Philips Bulb (kuning)', 20, 87000, 'Rp87.000', 0),
(21, 'Hannochs', 'Bohlam', 'putih', 'LED Hannochs Bohlam (putih)', 3, 13000, 'Rp13.000', 0),
(22, 'Hannochs', 'Bohlam', 'putih', 'LED Hannochs Bohlam (putih)', 5, 15000, 'Rp15.000', 0),
(23, 'Hannochs', 'Bohlam', 'putih', 'LED Hannochs Bohlam (putih)', 7, 17000, 'Rp17.000', 0),
(24, 'Hannochs', 'Bohlam', 'putih', 'LED Hannochs Bohlam (putih)', 9, 19000, 'Rp19.000', 0),
(25, 'Hannochs', 'Bohlam', 'putih', 'LED Hannochs Bohlam (putih)', 10, 20000, 'Rp20.000', 1),
(26, 'Hannochs', 'Bohlam', 'putih', 'LED Hannochs Bohlam (putih)', 12, 22000, 'Rp22.000', 0),
(27, 'Hannochs', 'Bohlam', 'putih', 'LED Hannochs Bohlam (putih)', 15, 25000, 'Rp25.000', 0),
(28, 'Hannochs', 'Bohlam', 'putih', 'LED Hannochs Bohlam (putih)', 20, 30000, 'Rp30.000', 0),
(29, 'Hannochs', 'Bohlam', 'putih', 'LED Hannochs Bohlam (putih)', 24, 34000, 'Rp34.000', 0),
(30, 'Hannochs', 'Bohlam', 'putih', 'LED Hannochs Bohlam (putih)', 30, 40000, 'Rp40.000', 0),
(31, 'Hannochs', 'Bohlam', 'kuning', 'LED Hannochs Bohlam (kuning)', 3, 15000, 'Rp15.000', 0),
(32, 'Hannochs', 'Bohlam', 'kuning', 'LED Hannochs Bohlam (kuning)', 5, 17000, 'Rp17.000', 0),
(33, 'Hannochs', 'Bohlam', 'kuning', 'LED Hannochs Bohlam (kuning)', 7, 19000, 'Rp19.000', 0),
(34, 'Hannochs', 'Bohlam', 'kuning', 'LED Hannochs Bohlam (kuning)', 9, 21000, 'Rp21.000', 0),
(35, 'Hannochs', 'Bohlam', 'kuning', 'LED Hannochs Bohlam (kuning)', 10, 22000, 'Rp22.000', 0),
(36, 'Hannochs', 'Bohlam', 'kuning', 'LED Hannochs Bohlam (kuning)', 12, 24000, 'Rp24.000', 0),
(37, 'Hannochs', 'Bohlam', 'kuning', 'LED Hannochs Bohlam (kuning)', 15, 27000, 'Rp27.000', 0),
(38, 'Hannochs', 'Bohlam', 'kuning', 'LED Hannochs Bohlam (kuning)', 20, 32000, 'Rp32.000', 0),
(39, 'Hannochs', 'Bohlam', 'kuning', 'LED Hannochs Bohlam (kuning)', 24, 36000, 'Rp36.000', 0),
(40, 'Hannochs', 'Bohlam', 'kuning', 'LED Hannochs Bohlam (kuning)', 30, 42000, 'Rp42.000', 0),
(41, 'Panasonic', 'Neo Bulb', 'putih', 'LED Panasonic Neo Bulb (putih)', 3, 15000, 'Rp15.000', 0),
(42, 'Panasonic', 'Neo Bulb', 'putih', 'LED Panasonic Neo Bulb (putih)', 5, 21000, 'Rp21.000', 0),
(43, 'Panasonic', 'Neo Bulb', 'putih', 'LED Panasonic Neo Bulb (putih)', 7, 27000, 'Rp27.000', 0),
(44, 'Panasonic', 'Neo Bulb', 'putih', 'LED Panasonic Neo Bulb (putih)', 9, 33000, 'Rp33.000', 0),
(45, 'Panasonic', 'Neo Bulb', 'putih', 'LED Panasonic Neo Bulb (putih)', 11, 39000, 'Rp39.000', 0),
(46, 'Panasonic', 'Neo Bulb', 'putih', 'LED Panasonic Neo Bulb (putih)', 13, 45000, 'Rp45.000', 0),
(47, 'Panasonic', 'Neo Bulb', 'putih', 'LED Panasonic Neo Bulb (putih)', 15, 51000, 'Rp51.000', 0),
(48, 'Panasonic', 'Neo Bulb', 'putih', 'LED Panasonic Neo Bulb (putih)', 17, 57000, 'Rp57.000', 0),
(49, 'Panasonic', 'Neo Bulb', 'kuning', 'LED Panasonic Neo Bulb (kuning)', 3, 20000, 'Rp20.000', 0),
(50, 'Panasonic', 'Neo Bulb', 'kuning', 'LED Panasonic Neo Bulb (kuning)', 5, 26000, 'Rp26.000', 0),
(51, 'Panasonic', 'Neo Bulb', 'kuning', 'LED Panasonic Neo Bulb (kuning)', 7, 32000, 'Rp32.000', 0),
(52, 'Panasonic', 'Neo Bulb', 'kuning', 'LED Panasonic Neo Bulb (kuning)', 9, 38000, 'Rp38.000', 0),
(53, 'Panasonic', 'Neo Bulb', 'kuning', 'LED Panasonic Neo Bulb (kuning)', 11, 44000, 'Rp44.000', 0),
(54, 'Panasonic', 'Neo Bulb', 'kuning', 'LED Panasonic Neo Bulb (kuning)', 13, 50000, 'Rp50.000', 1),
(55, 'Panasonic', 'Neo Bulb', 'kuning', 'LED Panasonic Neo Bulb (kuning)', 15, 56000, 'Rp56.000', 0),
(56, 'Panasonic', 'Neo Bulb', 'kuning', 'LED Panasonic Neo Bulb (kuning)', 17, 62000, 'Rp62.000', 0),
(57, 'In-Lite', 'Capsule', 'putih', 'LED In-Lite Capsule (putih)', 5, 14000, 'Rp14.000', 0),
(58, 'In-Lite', 'Capsule', 'putih', 'LED In-Lite Capsule (putih)', 10, 20000, 'Rp20.000', 1),
(59, 'In-Lite', 'Capsule', 'putih', 'LED In-Lite Capsule (putih)', 15, 27000, 'Rp27.000', 0),
(60, 'In-Lite', 'Capsule', 'putih', 'LED In-Lite Capsule (putih)', 20, 34000, 'Rp34.000', 0),
(61, 'In-Lite', 'Capsule', 'putih', 'LED In-Lite Capsule (putih)', 25, 42000, 'Rp42.000', 0),
(62, 'In-Lite', 'Capsule', 'putih', 'LED In-Lite Capsule (putih)', 30, 50000, 'Rp50.000', 0),
(63, 'In-Lite', 'Capsule', 'putih', 'LED In-Lite Capsule (putih)', 40, 66000, 'Rp66.000', 0),
(64, 'In-Lite', 'Capsule', 'putih', 'LED In-Lite Capsule (putih)', 50, 80000, 'Rp80.000', 0),
(65, 'In-Lite', 'Capsule', 'putih', 'LED In-Lite Capsule (putih)', 80, 160000, 'Rp160.000', 0),
(66, 'Meval', 'Capsule', 'putih', 'LED Meval Capsule (putih)', 5, 19000, 'Rp19.000', 0),
(67, 'Meval', 'Capsule', 'putih', 'LED Meval Capsule (putih)', 10, 26000, 'Rp26.000', 0),
(68, 'Meval', 'Capsule', 'putih', 'LED Meval Capsule (putih)', 15, 33000, 'Rp33.000', 0),
(69, 'Meval', 'Capsule', 'putih', 'LED Meval Capsule (putih)', 20, 40000, 'Rp40.000', 1),
(70, 'Meval', 'Capsule', 'putih', 'LED Meval Capsule (putih)', 25, 47000, 'Rp47.000', 0),
(71, 'Meval', 'Capsule', 'putih', 'LED Meval Capsule (putih)', 30, 54000, 'Rp54.000', 0),
(72, 'Meval', 'Capsule', 'putih', 'LED Meval Capsule (putih)', 40, 68000, 'Rp68.000', 0),
(73, 'Meval', 'Capsule', 'putih', 'LED Meval Capsule (putih)', 50, 82000, 'Rp82.000', 0),
(74, 'Osram', 'Bulb', 'putih', 'LED Osram Bulb (putih)', 3, 11000, 'Rp11.000', 0),
(75, 'Osram', 'Bulb', 'putih', 'LED Osram Bulb (putih)', 5, 14000, 'Rp14.000', 0),
(76, 'Osram', 'Bulb', 'putih', 'LED Osram Bulb (putih)', 7, 17000, 'Rp17.000', 0),
(77, 'Osram', 'Bulb', 'putih', 'LED Osram Bulb (putih)', 9, 20000, 'Rp20.000', 0),
(78, 'Osram', 'Bulb', 'putih', 'LED Osram Bulb (putih)', 12, 25000, 'Rp25.000', 0),
(79, 'Osram', 'Bulb', 'putih', 'LED Osram Bulb (putih)', 14, 28000, 'Rp28.000', 0),
(80, 'Osram', 'Bulb', 'putih', 'LED Osram Bulb (putih)', 18, 35000, 'Rp35.000', 0),
(81, 'Osram', 'Bulb', 'putih', 'LED Osram Bulb (putih)', 20, 38000, 'Rp38.000', 0),
(82, 'Osram', 'Bulb', 'kuning', 'LED Osram Bulb (kuning)', 3, 11000, 'Rp11.000', 0),
(83, 'Osram', 'Bulb', 'kuning', 'LED Osram Bulb (kuning)', 5, 14000, 'Rp14.000', 0),
(84, 'Osram', 'Bulb', 'kuning', 'LED Osram Bulb (kuning)', 7, 17000, 'Rp17.000', 0),
(85, 'Osram', 'Bulb', 'kuning', 'LED Osram Bulb (kuning)', 9, 20000, 'Rp20.000', 0),
(86, 'Osram', 'Bulb', 'kuning', 'LED Osram Bulb (kuning)', 12, 25000, 'Rp25.000', 0),
(87, 'Osram', 'Bulb', 'kuning', 'LED Osram Bulb (kuning)', 14, 28000, 'Rp28.000', 0),
(88, 'Osram', 'Bulb', 'kuning', 'LED Osram Bulb (kuning)', 18, 35000, 'Rp35.000', 0),
(89, 'Osram', 'Bulb', 'kuning', 'LED Osram Bulb (kuning)', 20, 38000, 'Rp38.000', 0),
(90, 'Surya', 'Bohlam', 'putih', 'LED Surya Bohlam (putih)', 5, 10000, 'Rp10.000', 0),
(91, 'Surya', 'Bohlam', 'putih', 'LED Surya Bohlam (putih)', 7, 12000, 'Rp12.000', 0),
(92, 'Surya', 'Bohlam', 'putih', 'LED Surya Bohlam (putih)', 9, 14000, 'Rp14.000', 0),
(93, 'Surya', 'Bohlam', 'putih', 'LED Surya Bohlam (putih)', 12, 17000, 'Rp17.000', 0),
(94, 'Surya', 'Bohlam', 'putih', 'LED Surya Bohlam (putih)', 15, 20000, 'Rp20.000', 0),
(95, 'Surya', 'Bohlam', 'putih', 'LED Surya Bohlam (putih)', 18, 23000, 'Rp23.000', 0),
(96, 'Surya', 'Bohlam', 'putih', 'LED Surya Bohlam (putih)', 24, 29000, 'Rp29.000', 0);
