<?php
// public/index.php

// กำหนดให้แสดง Error ในระหว่างการพัฒนา
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// โหลด Controller และ Response
require_once __DIR__ . '/../src/Response.php';
require_once __DIR__ . '/../src/ApplianceController.php';

// รองรับ CORS สำหรับ Preflight OPTIONS Request [cite: 9]
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, Authorization");
    http_response_code(200);
    exit();
}

$method = $_SERVER['REQUEST_METHOD'];
// ดึง path ที่ถูกส่งมาจาก .htaccess (เช่น 'appliances' หรือ 'appliances/3')
// **ค่าที่ได้ตอนนี้คือ 'appliances' หรือ 'appliances/3'**
$path = isset($_GET['path']) ? explode('/', trim($_GET['path'], '/')) : [];

// --- ส่วนที่ต้องแก้ไข/ปรับปรุง ---

// ตรวจสอบว่า path แรกคือ 'appliances' (ไม่ใช่ 'api' แล้ว)
// ตรวจสอบว่า $path ไม่ว่าง และ path[0] ต้องเป็น 'appliances' เท่านั้น
if (empty($path) || $path[0] !== 'appliances') { 
    Response::notFound('Endpoint not found'); 
}

// id จะเป็นค่าที่ 2 ใน path (path[0] คือ appliances, path[1] คือ id)
$id = isset($path[1]) ? (int)$path[1] : null; 

$controller = new ApplianceController();
$data = [];

// อ่าน JSON Body สำหรับ POST, PUT, PATCH
if (in_array($method, ['POST', 'PUT', 'PATCH'])) {
    $json_data = file_get_contents("php://input");
    $data = json_decode($json_data, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        Response::badRequest(['body' => 'Invalid JSON format']);
    }
}

// Routing Logic
switch ($method) {
    case 'GET':
        $controller->read($id);
        break;
    case 'POST':
        if ($id) Response::notFound(); // POST /api/appliances/{id} ไม่ถูกต้อง
        $controller->create($data);
        break;
    case 'PUT':
    case 'PATCH':
        if (!$id) Response::badRequest(['id' => 'Missing product ID for update']);
        $controller->update($id, $data);
        break;
    case 'DELETE':
        if (!$id) Response::badRequest(['id' => 'Missing product ID for delete']);
        $controller->delete($id);
        break;
    default:
        Response::notFound('Method not supported');
}
?>