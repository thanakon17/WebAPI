<?php
// src/ApplianceController.php

// ใช้ Response class
require_once __DIR__ . '/Response.php';
// ใช้ Database class
require_once __DIR__ . '/Database.php';

class ApplianceController {
    private $db;
    private $table_name = "appliances";

    public function __construct() {
        $database = new Database();
        $this->db = $database->connect();
    }

    /**
     * ตรวจสอบว่ามี SKU ซ้ำหรือไม่
     */
    private function findBySku($sku, $id_to_exclude = null) {
        $sql = "SELECT id FROM " . $this->table_name . " WHERE sku = :sku";
        if ($id_to_exclude) {
            $sql .= " AND id != :id_to_exclude";
        }
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':sku', $sku);
        if ($id_to_exclude) {
            $stmt->bindParam(':id_to_exclude', $id_to_exclude);
        }
        $stmt->execute();
        return $stmt->fetch();
    }

    /**
     * ตรวจสอบความถูกต้องของข้อมูล (Validation) สำหรับ POST/PUT/PATCH
     */
    private function validateData(array $data, bool $is_create = true): array {
        $errors = [];
        $required_fields = ['sku', 'name', 'brand', 'category', 'price'];

        // 1. ตรวจสอบฟิลด์ที่จำเป็นสำหรับการสร้าง
        if ($is_create) {
            foreach ($required_fields as $field) {
                if (!isset($data[$field]) || $data[$field] === '') {
                    $errors[$field] = ucfirst($field) . ' is required.';
                }
            }
        }
        
        // 2. ตรวจสอบประเภทข้อมูลและความถูกต้อง
        if (isset($data['price'])) {
            if (!is_numeric($data['price']) || $data['price'] < 0) {
                $errors['price'] = 'Price must be a non-negative number.';
            }
        }
        if (isset($data['stock'])) {
            if (!is_numeric($data['stock']) || $data['stock'] < 0 || floor($data['stock']) != $data['stock']) {
                $errors['stock'] = 'Stock must be a non-negative integer.';
            }
        }
        if (isset($data['sku']) && strlen($data['sku']) > 32) {
             $errors['sku'] = 'SKU cannot exceed 32 characters.';
        }

        return $errors;
    }

    // --- 1. READ (GET) ---
    public function read($id) {
        if ($id) {
            // GET /api/appliances/{id}
            $stmt = $this->db->prepare("SELECT * FROM " . $this->table_name . " WHERE id = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $product = $stmt->fetch();

            if ($product) {
                Response::success($product);
            } else {
                Response::notFound();
            }
        } else {
            // GET /api/appliances (ทั้งหมด/ค้นหา/กรอง)
            // ดึง Query Parameters
            $category = $_GET['category'] ?? null;
            $min_price = $_GET['min_price'] ?? null;
            $max_price = $_GET['max_price'] ?? null;
            $sort = $_GET['sort'] ?? 'id_asc';
            $page = (int)($_GET['page'] ?? 1);
            $per_page = (int)($_GET['per_page'] ?? 10);
            
            $where = [];
            $params = [];
            
            if ($category) {
                $where[] = "category = :category";
                $params[':category'] = $category;
            }
            if ($min_price && is_numeric($min_price)) {
                $where[] = "price >= :min_price";
                $params[':min_price'] = $min_price;
            }
            if ($max_price && is_numeric($max_price)) {
                $where[] = "price <= :max_price";
                $params[':max_price'] = $max_price;
            }

            $sql = "SELECT * FROM " . $this->table_name;
            if (!empty($where)) {
                $sql .= " WHERE " . implode(' AND ', $where);
            }

            // การเรียงลำดับ (Sort)
            $order_map = [
                'price_asc' => 'price ASC',
                'price_desc' => 'price DESC',
                'name_asc' => 'name ASC',
                'id_asc' => 'id ASC'
            ];
            $order_by = $order_map[$sort] ?? 'id ASC';
            $sql .= " ORDER BY " . $order_by;

            // Pagination
            $offset = ($page - 1) * $per_page;
            $sql .= " LIMIT :limit OFFSET :offset";
            $params[':limit'] = $per_page;
            $params[':offset'] = $offset;

            $stmt = $this->db->prepare($sql);
            foreach ($params as $key => &$val) {
                // ต้องใช้ bindValue เพราะการ binding ต้องรู้ type
                $type = is_int($val) ? PDO::PARAM_INT : (is_numeric($val) ? PDO::PARAM_STR : PDO::PARAM_STR);
                $stmt->bindValue($key, $val, $type);
            }
            
            $stmt->execute();
            $products = $stmt->fetchAll();
            
            // ควรเพิ่ม total count ด้วย แต่เพื่อความรวดเร็วจะส่งแค่ data
            Response::success(["data" => $products]);
        }
    }

    // --- 2. CREATE (POST) ---
    public function create($data) {
        // 1. Validation
        $validation_errors = $this->validateData($data, true);
        if (!empty($validation_errors)) {
            Response::badRequest($validation_errors); // 400 Bad Request
        }

        // 2. ตรวจสอบ SKU ซ้ำ (409 Conflict)
        if ($this->findBySku($data['sku'])) {
            Response::conflict();
        }

        // 3. INSERT (Prepared Statement)
        $fields = array_keys($data);
        $placeholders = array_map(fn($f) => ":$f", $fields);
        
        $sql = "INSERT INTO " . $this->table_name . " (" . implode(', ', $fields) . ") VALUES (" . implode(', ', $placeholders) . ")";
        $stmt = $this->db->prepare($sql);
        
        foreach ($data as $key => &$value) {
            $stmt->bindParam(":$key", $value);
        }

        if ($stmt->execute()) {
            $new_id = $this->db->lastInsertId();
            // ดึงข้อมูลสินค้าใหม่กลับมา
            $stmt_select = $this->db->prepare("SELECT * FROM " . $this->table_name . " WHERE id = ?");
            $stmt_select->execute([$new_id]);
            Response::created($stmt_select->fetch());
        } else {
            // กรณีเกิดข้อผิดพลาดอื่น ๆ ในฐานข้อมูล (เช่น 500 Internal Server Error)
            http_response_code(500);
            echo json_encode(["error" => "Could not create appliance"]);
            exit();
        }
    }
    
    // --- 3. UPDATE (PUT/PATCH) ---
    public function update($id, $data) {
        // 1. Validation
        if (empty($data)) {
            Response::badRequest(['body' => 'Request body cannot be empty']);
        }
        $validation_errors = $this->validateData($data, false); // ไม่บังคับ required field
        if (!empty($validation_errors)) {
            Response::badRequest($validation_errors); // 400 Bad Request
        }

        // 2. ตรวจสอบว่าสินค้ามีอยู่จริงหรือไม่ (404 Not Found)
        $stmt_check = $this->db->prepare("SELECT id FROM " . $this->table_name . " WHERE id = :id");
        $stmt_check->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt_check->execute();
        if (!$stmt_check->fetch()) {
            Response::notFound();
        }

        // 3. ตรวจสอบ SKU ซ้ำ หากมีการแก้ไข SKU (409 Conflict)
        if (isset($data['sku']) && $this->findBySku($data['sku'], $id)) {
            Response::conflict();
        }
        
        // 4. UPDATE (Prepared Statement)
        $set_parts = [];
        foreach ($data as $key => $value) {
            $set_parts[] = "$key = :$key";
        }
        
        $sql = "UPDATE " . $this->table_name . " SET " . implode(', ', $set_parts) . " WHERE id = :id";
        $stmt = $this->db->prepare($sql);

        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        foreach ($data as $key => &$value) {
            $stmt->bindParam(":$key", $value);
        }

        if ($stmt->execute()) {
            // ดึงข้อมูลสินค้าล่าสุดกลับมา
            $stmt_select = $this->db->prepare("SELECT * FROM " . $this->table_name . " WHERE id = ?");
            $stmt_select->execute([$id]);
            Response::success($stmt_select->fetch(), 'Updated');
        } else {
            http_response_code(500);
            echo json_encode(["error" => "Could not update appliance"]);
            exit();
        }
    }

    // --- 4. DELETE (DELETE) ---
    public function delete($id) {
        // 1. ตรวจสอบว่าสินค้ามีอยู่จริงหรือไม่ (404 Not Found)
        $stmt_check = $this->db->prepare("SELECT id FROM " . $this->table_name . " WHERE id = :id");
        $stmt_check->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt_check->execute();
        if (!$stmt_check->fetch()) {
            Response::notFound();
        }
        
        // 2. DELETE (Prepared Statement)
        $stmt = $this->db->prepare("DELETE FROM " . $this->table_name . " WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);

        if ($stmt->execute()) {
            Response::success(null, 'Deleted');
        } else {
            http_response_code(500);
            echo json_encode(["error" => "Could not delete appliance"]);
            exit();
        }
    }
}