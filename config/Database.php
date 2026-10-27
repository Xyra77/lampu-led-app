<?php
/**
 * Class Database
 * Bertanggung jawab untuk membuat koneksi ke MySQL pakai PDO.
 * Ganti $username / $password kalau MySQL kamu beda dari default XAMPP.
 */
class Database
{
    private $host = "127.0.0.1";
    private $port = 3306;
    private $db_name = "db_lampu_led";
    private $username = "root";
    private $password = "";
    public $conn;

    public function getConnection()
    {
        $this->conn = null;

        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";port=" . $this->port . ";dbname=" . $this->db_name . ";charset=utf8mb4",
                $this->username,
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die("Koneksi database gagal: " . $e->getMessage());
        }

        return $this->conn;
    }
}
