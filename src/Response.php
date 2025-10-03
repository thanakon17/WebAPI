<?php
// src/Response.php

class Response {
    /**
     * ตั้งค่า CORS Headers สำหรับการอนุญาตการเรียกจากโดเมนอื่น
     */
    public static function setCORSHeaders(): void {
        // อนุญาตให้เข้าถึงจากทุกที่ (*)
        header("Access-Control-Allow-Origin: *");
        // อนุญาต HTTP Methods ที่จำเป็นสำหรับ REST
        header("Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS");
        // อนุญาต Headers ที่ใช้บ่อย รวมถึง Content-Type
        header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
        // กำหนดอายุของ Preflight (OPTIONS) request
        header("Access-Control-Max-Age: 3600"); 
    }

    /**
     * ส่ง JSON Response พร้อม HTTP Status Code
     * @param int $status HTTP Status Code
     * @param array $data ข้อมูลที่จะแปลงเป็น JSON
     */
    public static function json(int $status, array $data): void {
        // ตั้ง HTTP Status Code
        http_response_code($status);
        // ตั้ง Header ว่าเนื้อหาเป็น JSON
        header('Content-Type: application/json; charset=utf-8');
        // แปลงข้อมูลเป็น JSON และส่งออก
        echo json_encode($data);
        exit;
    }

    /**
     * จัดการกับ CORS Preflight Request
     */
    public static function handlePreflight(): void {
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            self::setCORSHeaders();
            http_response_code(200);
            exit;
        }
    }
}