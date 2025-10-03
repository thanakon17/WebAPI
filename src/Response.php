<?php
// src/Response.php

class Response {
    /**
     * ตั้งค่า Header และส่ง JSON Response
     */
    private static function send(int $status, array $data): void {
        header("Access-Control-Allow-Origin: *"); // รองรับ CORS [cite: 9]
        header("Content-Type: application/json; charset=utf-8"); // กำหนด Content-Type เป็น JSON [cite: 58, 117]
        http_response_code($status);
        echo json_encode($data);
        exit();
    }

    public static function success($data, $message = 'OK'): void {
        $response = is_array($data) ? $data : ["data" => $data];
        self::send(200, $response); // 200 OK [cite: 62, 79, 105, 110]
    }

    public static function created($data, $message = 'Created'): void {
        self::send(201, ["message" => $message, "data" => $data]); // 201 Created [cite: 96]
    }

    public static function notFound($message = 'Not found'): void {
        self::send(404, ["error" => $message]); // 404 Not Found [cite: 81, 107, 112]
    }

    public static function badRequest($details, $message = 'Validation failed'): void {
        self::send(400, ["error" => $message, "details" => $details]); // 400 Bad Request [cite: 98, 107]
    }

    public static function conflict($message = 'SKU already exists'): void {
        self::send(409, ["error" => $message]); // 409 Conflict (sku ซ้ำ) [cite: 100, 107]
    }
}
?>