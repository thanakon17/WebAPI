<?php
// public/index.php

// 1. กำหนด path และโหลดไฟล์ที่จำเป็น
define('BASE_PATH', dirname(__DIR__));
require_once BASE_PATH . '/src/ApplianceController.php';
require_once BASE_PATH . '/src/Response.php'; // โหลด Response Helper

// 2. จัดการ CORS Preflight (OPTIONS Request)
Response::setCORSHeaders();
Response::handlePreflight();

// 3. เตรียมข้อมูล Request
$method = $_SERVER['REQUEST_METHOD'];
$uri = trim($_SERVER['REQUEST_URI'], '/');
// แยกส่วน URI (เช่น /appliances/123)
$uri_parts = explode('/', $uri); 
// ดึงข้อมูล JSON Body สำหรับ POST, PUT, PATCH
$input = file_get_contents("php://input");
$data = json_decode($input, true) ?? [];

// 4. สร้าง Controller Instance
$controller = new ApplianceController();

// หา 'api' และ 'appliances' ใน $uri_parts
$api_index = array_search('api', $uri_parts);
if ($api_index !== false && isset($uri_parts[$api_index + 1]) && $uri_parts[$api_index + 1] === 'appliances') {
    $id = isset($uri_parts[$api_index + 2]) && is_numeric($uri_parts[$api_index + 2]) ? (int) $uri_parts[$api_index + 2] : null;

    switch ($method) {
        case 'GET':
            $id ? $controller->show($id) : $controller->index();
            break;
        case 'POST':
            $controller->store($data);
            break;
        case 'PUT':
        case 'PATCH':
            $id ? $controller->update($id, $data) : Response::json(400, ["error" => "Missing ID for Update operation"]);
            break;
        case 'DELETE':
            $id ? $controller->delete($id) : Response::json(400, ["error" => "Missing ID for Delete operation"]);
            break;
        default:
            Response::json(405, ["error" => "Method Not Allowed"]);
    }
} else {
    Response::json(404, ["error" => "Not Found: Invalid API endpoint"]);
}