<?php
class Lampu
{
    private $conn;
    private $table = "lampu_led";

    // Properti entitas
    public $id;
    public $merk;
    public $tipe;
    public $warna;
    public $nama_lampu;
    public $watt;
    public $harga_terendah;
    public $harga_label;
    public $jumlah_stock;
    public $data_asli;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    // READ semua data, dengan opsi filter pencarian
    public function getAll($keyword = "", $merk = "", $tipe = "")
    {
        $query = "SELECT * FROM " . $this->table . " WHERE 1=1";

        if (!empty($keyword)) {
            $query .= " AND nama_lampu LIKE :keyword";
        }
        if (!empty($merk)) {
            $query .= " AND merk = :merk";
        }
        if (!empty($tipe)) {
            $query .= " AND tipe = :tipe";
        }
        $query .= " ORDER BY id ASC, merk ASC, watt ASC";

        $stmt = $this->conn->prepare($query);

        if (!empty($keyword)) {
            $like = "%{$keyword}%";
            $stmt->bindParam(":keyword", $like);
        }
        if (!empty($merk)) {
            $stmt->bindParam(":merk", $merk);
        }
        if (!empty($tipe)) {
            $stmt->bindParam(":tipe", $tipe);
        }

        $stmt->execute();
        return $stmt;
    }

    // READ satu data berdasarkan id, hasil dipakai mengisi properti objek
    public function getById()
    {
        $query = "SELECT * FROM " . $this->table . " WHERE id = :id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->id, PDO::PARAM_INT);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $this->merk = $row['merk'];
            $this->tipe = $row['tipe'];
            $this->warna = $row['warna'];
            $this->nama_lampu = $row['nama_lampu'];
            $this->watt = $row['watt'];
            $this->harga_terendah = $row['harga_terendah'];
            $this->harga_label = $row['harga_label'];
            $this->jumlah_stock = $row['jumlah_stock'];
            $this->data_asli = $row['data_asli'];
        }
        return $row;
    }

    // CREATE data baru
    public function create()
    {
        $query = "INSERT INTO " . $this->table . "
            (merk, tipe, warna, nama_lampu, watt, harga_terendah, harga_label, jumlah_stock, data_asli)
            VALUES (:merk, :tipe, :warna, :nama_lampu, :watt, :harga_terendah, :harga_label, :jumlah_stock, :data_asli)";

        $stmt = $this->conn->prepare($query);

        $this->sanitize();

        $stmt->bindParam(":merk", $this->merk);
        $stmt->bindParam(":tipe", $this->tipe);
        $stmt->bindParam(":warna", $this->warna);
        $stmt->bindParam(":nama_lampu", $this->nama_lampu);
        $stmt->bindParam(":watt", $this->watt, PDO::PARAM_INT);
        $stmt->bindParam(":harga_terendah", $this->harga_terendah, PDO::PARAM_INT);
        $stmt->bindParam(":harga_label", $this->harga_label);
        $stmt->bindParam(":jumlah_stock", $this->jumlah_stock, PDO::PARAM_INT);
        $stmt->bindParam(":data_asli", $this->data_asli, PDO::PARAM_INT);

        return $stmt->execute();
    }

    // UPDATE data berdasarkan id
    public function update()
    {
        $query = "UPDATE " . $this->table . "
            SET merk = :merk,
                tipe = :tipe,
                warna = :warna,
                nama_lampu = :nama_lampu,
                watt = :watt,
                harga_terendah = :harga_terendah,
                harga_label = :harga_label,
                jumlah_stock = :jumlah_stock,
                data_asli = :data_asli
            WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        $this->sanitize();

        $stmt->bindParam(":merk", $this->merk);
        $stmt->bindParam(":tipe", $this->tipe);
        $stmt->bindParam(":warna", $this->warna);
        $stmt->bindParam(":nama_lampu", $this->nama_lampu);
        $stmt->bindParam(":watt", $this->watt, PDO::PARAM_INT);
        $stmt->bindParam(":harga_terendah", $this->harga_terendah, PDO::PARAM_INT);
        $stmt->bindParam(":harga_label", $this->harga_label);
        $stmt->bindParam(":jumlah_stock", $this->jumlah_stock, PDO::PARAM_INT);
        $stmt->bindParam(":data_asli", $this->data_asli, PDO::PARAM_INT);
        $stmt->bindParam(":id", $this->id, PDO::PARAM_INT);

        return $stmt->execute();
    }

    // DELETE data berdasarkan id
    public function delete()
    {
        $query = "DELETE FROM " . $this->table . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    // Ambil daftar merk unik, buat dropdown filter
    public function getDistinctMerk()
    {
        $stmt = $this->conn->query("SELECT DISTINCT merk FROM " . $this->table . " ORDER BY merk ASC");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    // Ambil daftar tipe unik, buat dropdown filter
    public function getDistinctTipe()
    {
        $stmt = $this->conn->query("SELECT DISTINCT tipe FROM " . $this->table . " ORDER BY tipe ASC");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    // Bersihkan input sebelum masuk query (lapisan tambahan selain prepared statement)
    private function sanitize()
    {
        $this->merk = htmlspecialchars(strip_tags(trim($this->merk)));
        $this->tipe = htmlspecialchars(strip_tags(trim($this->tipe)));
        $this->warna = htmlspecialchars(strip_tags(trim($this->warna)));
        $this->nama_lampu = htmlspecialchars(strip_tags(trim($this->nama_lampu)));
        $this->harga_label = htmlspecialchars(strip_tags(trim($this->harga_label)));
        $this->watt = (int) $this->watt;
        $this->harga_terendah = (int) $this->harga_terendah;
        $this->jumlah_stock = max(0, (int) $this->jumlah_stock);
        $this->data_asli = (int) $this->data_asli;
    }
}
