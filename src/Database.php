<?php
// src/Database.php

class Database {
    private $host = "localhost";
    private $db_name = "webapi_demo"; // เปลี่ยนเป็นชื่อฐานข้อมูลที่คุณสร้าง [cite: 12]
    private $username = "root";       // ปกติคือ root ใน XAMPP
    private $password = "";           // ปกติไม่มีรหัสผ่านใน XAMPP
    private $conn;

    /**
     * รับการเชื่อมต่อฐานข้อมูล
     * @return PDO
     */
    public function connect() {
        $this->conn = null;
        try {
            $dsn = "mysql:host={$this->host};dbname={$this->db_name};charset=utf8mb4";
            $this->conn = new PDO($dsn, $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // กำหนดให้ throw Exception เมื่อมี Error
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC); // กำหนดผลลัพธ์เป็น array แบบเชื่อมโยง
        } catch(PDOException $exception) {
            // ใน API ควรส่ง Response 500 กลับไป
            http_response_code(500);
            echo json_encode(["error" => "Database connection error", "details" => $exception->getMessage()]);
            exit();
        }
        return $this->conn;
    }
}
?>