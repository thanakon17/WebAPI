<?php
// src/Database.php

class Database {
    private string $host = "localhost";
    private string $db_name = "webapi_demo";
    private string $username = "root"; // เปลี่ยนหากใช้ user อื่น
    private string $password = "";     // เปลี่ยนหากมีการตั้งรหัสผ่าน
    private ?PDO $conn;

    /**
     * รับการเชื่อมต่อฐานข้อมูล
     * @return PDO|null
     */
    public function getConnection(): ?PDO {
        $this->conn = null;

        try {
            $this->conn = new PDO("mysql:host={$this->host};dbname={$this->db_name};charset=utf8mb4",
                                $this->username,
                                $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch(PDOException $exception) {
            // ใน Lab นี้ เราจะโยน Exception เพื่อให้ Controller/Router จัดการ Error 500
            throw new Exception("Database connection error: " . $exception->getMessage(), 500);
        }

        return $this->conn;
    }
}