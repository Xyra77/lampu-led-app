LAMPU LED POS - PHP OOP + MySQL
==============================================

FITUR
-----
- Katalog produk lampu LED (CRUD, pencarian, filter merk/tipe, harga jual).
- Jumlah stok per produk.
- Data kasir dan supplier.
- Transaksi penjualan dengan beberapa produk, tanggal, pelanggan, kasir,
  metode pembayaran, harga satuan, jumlah, subtotal, dan total bayar.
- Riwayat transaksi beserta detail transaksi yang bisa dicetak.
- Pengadaan barang dari supplier dengan jumlah, harga satuan, subtotal,
  dan total pembelian. Stok bertambah otomatis setelah pengadaan berhasil.
- Stok berkurang otomatis setelah penjualan berhasil.
- Penjualan dan pengadaan memakai transaksi database supaya stok dan catatan
  tidak berubah setengah jalan jika penyimpanan gagal.
- Query SQL memakai PDO prepared statements.

STRUKTUR PENTING
----------------
config/Database.php        => koneksi PDO MySQL
config/helpers.php         => helper sesi, escaping output, format rupiah
models/Lampu.php           => model CRUD katalog produk
index.php                  => katalog produk dan ringkasan stok/penjualan
kasir.php                  => data kasir
supplier.php               => data supplier
transaksi.php              => form penjualan
riwayat_transaksi.php      => riwayat penjualan
detail_transaksi.php      => rincian harga, jumlah, dan subtotal transaksi
pengadaan.php              => form pengadaan dan riwayat pembelian
detail_pengadaan.php       => rincian barang dan harga satuan pembelian
database.sql               => instalasi baru, termasuk data awal 96 lampu LED
upgrade.sql                => migrasi dari versi katalog lama

CARA INSTALASI BARU (XAMPP / LARAGON)
-------------------------------------
1. Ekstrak folder lampu-led-app ke htdocs (XAMPP) atau www (Laragon).
2. Jalankan Apache dan MySQL.
3. Buka phpMyAdmin, pilih Import, lalu impor database.sql.
   PERHATIAN: database.sql ditujukan untuk instalasi baru dan menghapus tabel
   aplikasi dengan nama sama jika sebelumnya sudah ada. Cadangkan data dulu.
4. Jika username/password MySQL berbeda dari root tanpa password, ubah
   config/Database.php.
5. Buka http://localhost/lampu-led-app/index.php.
6. Tambahkan data kasir dan supplier. Lalu catat pengadaan untuk menambah stok.
   Setelah stok tersedia, transaksi penjualan bisa dilakukan.

UPGRADE DARI VERSI LAMA
-----------------------
1. Cadangkan database db_lampu_led terlebih dahulu.
2. Impor upgrade.sql satu kali lewat phpMyAdmin. File ini menambah kolom stok
   dan tabel baru tanpa menghapus 96 data produk yang sudah ada.
3. Ganti file aplikasi dengan versi ini, lalu buka kembali di browser.

CATATAN
-------
- Stok awal produk disetel 0; stok bertambah melalui menu Pengadaan.
- Harga jual di katalog menggunakan harga_terendah. Harga beli pengadaan
  dicatat terpisah karena harga dari supplier dapat berbeda.
- Sistem ini belum memakai login/otorisasi pengguna; gunakan di lingkungan
  lokal atau tambahkan autentikasi sebelum dipasang ke server publik.
