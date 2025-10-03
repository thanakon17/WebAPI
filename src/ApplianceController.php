<?php
// src/ApplianceController.php

require_once 'Database.php';
require_once 'Response.php';

class ApplianceController {
    private PDO $db;

    public function __construct() {
        try {
            // สร้างการเชื่อมต่อ DB
            $db_conn = (new Database())->getConnection();
            if ($db_conn instanceof PDO) {
                $this->db = $db_conn;
            } else {
                 // กรณี getConnection คืนค่า null (ไม่ควรเกิดขึ้นหากมีการโยน Exception ใน Database.php)
                Response::json(500, ["error" => "Internal Server Error: Database connection failed."]);
            }
        } catch (Exception $e) {
            // จัดการ Exception ที่มาจาก Database.php (Error 500)
            Response::json(500, ["error" => "Internal Server Error", "details" => $e->getMessage()]);
        }
    }

    // --- Helper Functions สำหรับ Validation ---

    /**
     * ตรวจสอบความถูกต้องของข้อมูล (เฉพาะฟิลด์ที่ต้องการ)
     * @param array $data ข้อมูลที่รับมาจาก Request Body
     * @param bool $is_update หากเป็น true จะตรวจสอบเฉพาะฟิลด์ที่มีใน $data
     * @return array หากมี error จะคืนค่าเป็น array ของ errors
     */
    private function validateData(array $data, bool $is_update = false): array {
        $errors = [];
        $required_fields = ['sku', 'name', 'brand', 'category', 'price', 'stock'];
        
        foreach ($required_fields as $field) {
            // ตรวจสอบฟิลด์ที่จำเป็นต้องมีในโหมด Create
            if (!$is_update && (!isset($data[$field]) || $data[$field] === '')) {
                $errors[$field] = "is required";
            }
        }

        // ตรวจสอบรูปแบบข้อมูล
        if (isset($data['price']) && (!is_numeric($data['price']) || $data['price'] < 0)) {
            $errors['price'] = "must be a non-negative number";
        }
        if (isset($data['stock']) && (!is_numeric($data['stock']) || $data['stock'] < 0 || floor($data['stock']) != $data['stock'])) {
            $errors['stock'] = "must be a non-negative integer";
        }
        if (isset($data['warranty_months']) && (!is_numeric($data['warranty_months']) || $data['warranty_months'] < 0 || floor($data['warranty_months']) != $data['warranty_months'])) {
            $errors['warranty_months'] = "must be a non-negative integer";
        }
        if (isset($data['energy_rating']) && (!is_numeric($data['energy_rating']) || $data['energy_rating'] < 1 || $data['energy_rating'] > 5)) {
            $errors['energy_rating'] = "must be an integer between 1 and 5";
        }

        return $errors;
    }

    /**
     * ตรวจสอบว่า SKU ซ้ำกันหรือไม่
     * @param string $sku SKU ที่จะตรวจสอบ
     * @param int|null $exclude_id ID ของสินค้าที่ต้องการยกเว้น (ใช้ในการ Update)
     * @return bool
     */
    private function isSkuDuplicate(string $sku, ?int $exclude_id = null): bool {
        $sql = "SELECT COUNT(*) FROM appliances WHERE sku = :sku " . ($exclude_id ? "AND id != :id" : "");
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':sku', $sku);
        if ($exclude_id) {
            $stmt->bindParam(':id', $exclude_id, PDO::PARAM_INT);
        }
        $stmt->execute();
        return (bool) $stmt->fetchColumn();
    }

    // --- CRUD Methods ---

    /**
     * [GET] ดึงรายการสินค้าทั้งหมด หรือค้นหา
     */
    public function index(): void {
        $sql = "SELECT * FROM appliances";
        $params = [];
        $where = [];
        
        // ตัวอย่างการกรอง/ค้นหา (ตามโจทย์: ?category=ทีวี&sort=price_asc)
        if (isset($_GET['category']) && $_GET['category']) {
            $where[] = "category = :category";
            $params[':category'] = $_GET['category'];
        }

        if (!empty($where)) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }

        // ตัวอย่างการจัดเรียง
        $sort_by = "created_at DESC";
        if (isset($_GET['sort'])) {
            $sort = strtolower($_GET['sort']);
            if ($sort === 'price_asc') {
                $sort_by = "price ASC";
            } elseif ($sort === 'price_desc') {
                $sort_by = "price DESC";
            } elseif ($sort === 'name_asc') {
                $sort_by = "name ASC";
            }
        }
        $sql .= " ORDER BY " . $sort_by;
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $appliances = $stmt->fetchAll();
            
            Response::json(200, ["data" => $appliances]);
        } catch (PDOException $e) {
            Response::json(500, ["error" => "Database Query Error", "details" => $e->getMessage()]);
        }
    }

    /**
     * [GET] ดึงสินค้า 1 รายการ
     * @param int $id ID สินค้า
     */
    public function show(int $id): void {
        $sql = "SELECT * FROM appliances WHERE id = :id";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $appliance = $stmt->fetch();

            if (!$appliance) {
                Response::json(404, ["error" => "Not found"]);
            }
            
            Response::json(200, ["data" => $appliance]);
        } catch (PDOException $e) {
             Response::json(500, ["error" => "Database Query Error", "details" => $e->getMessage()]);
        }
    }

    /**
     * [POST] สร้างสินค้าใหม่
     * @param array $data ข้อมูลที่รับมาจาก JSON Body
     */
    public function store(array $data): void {
        $errors = $this->validateData($data);
        if (!empty($errors)) {
            Response::json(400, ["error" => "Validation failed", "details" => $errors]);
        }

        if ($this->isSkuDuplicate($data['sku'])) {
            Response::json(409, ["error" => "SKU already exists"]);
        }

        $sql = "INSERT INTO appliances (sku, name, brand, category, price, stock, warranty_months, energy_rating) 
                VALUES (:sku, :name, :brand, :category, :price, :stock, :warranty_months, :energy_rating)";
        
        try {
            $stmt = $this->db->prepare($sql);

            // Bind values
            $stmt->bindParam(':sku', $data['sku']);
            $stmt->bindParam(':name', $data['name']);
            $stmt->bindParam(':brand', $data['brand']);
            $stmt->bindParam(':category', $data['category']);
            $stmt->bindParam(':price', $data['price']);
            $stmt->bindParam(':stock', $data['stock'], PDO::PARAM_INT);
            // ใช้ NULL สำหรับค่าที่อาจว่าง (เช่น energy_rating, warranty_months หากไม่ระบุ)
            $warranty = $data['warranty_months'] ?? 12; // ใช้ค่า default 12 หากไม่ส่งมา
            $energy = $data['energy_rating'] ?? null;
            $stmt->bindParam(':warranty_months', $warranty, PDO::PARAM_INT);
            $stmt->bindParam(':energy_rating', $energy, PDO::PARAM_INT);

            $stmt->execute();
            $last_id = $this->db->lastInsertId();

            // ดึงข้อมูลสินค้าที่สร้างใหม่มาแสดง
            $this->show((int) $last_id); // show() จะส่ง 200 OK แต่เราต้องใช้ 201 Created

        } catch (PDOException $e) {
            // กรณีเกิดข้อผิดพลาดอื่น ๆ (เช่น column ถูกตัด, DB error)
            Response::json(500, ["error" => "Failed to create appliance", "details" => $e->getMessage()]);
        }
    }

    /**
     * [PUT/PATCH] แก้ไขสินค้า
     * @param int $id ID สินค้าที่ต้องการแก้ไข
     * @param array $data ข้อมูลที่รับมาจาก JSON Body
     */
    public function update(int $id, array $data): void {
        // 1. ตรวจสอบว่ามีสินค้าหรือไม่
        $sql_check = "SELECT sku FROM appliances WHERE id = :id";
        $stmt_check = $this->db->prepare($sql_check);
        $stmt_check->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt_check->execute();
        $current_appliance = $stmt_check->fetch();
        
        if (!$current_appliance) {
            Response::json(404, ["error" => "Not found"]);
        }

        // 2. ตรวจสอบความถูกต้องของข้อมูลที่ส่งมา (validate only submitted fields)
        $errors = $this->validateData($data, true);
        if (!empty($errors)) {
            Response::json(400, ["error" => "Validation failed", "details" => $errors]);
        }

        // 3. ตรวจสอบ SKU ซ้ำ (เฉพาะเมื่อมีการส่งค่า sku มาและ sku นั้นไม่ซ้ำกับของตัวเอง)
        if (isset($data['sku']) && $data['sku'] !== $current_appliance['sku']) {
            if ($this->isSkuDuplicate($data['sku'], $id)) {
                Response::json(409, ["error" => "SKU already exists"]);
            }
        }
        
        // 4. สร้าง SET clause และ parameters สำหรับ UPDATE
        $set_parts = [];
        $update_params = [':id' => $id];
        $allowed_fields = ['sku', 'name', 'brand', 'category', 'price', 'stock', 'warranty_months', 'energy_rating'];

        foreach ($allowed_fields as $field) {
            if (isset($data[$field])) {
                $set_parts[] = "{$field} = :{$field}";
                $update_params[":{$field}"] = $data[$field];
                
                // สำหรับฟิลด์ที่อาจเป็น NULL (เช่น energy_rating)
                if ($field === 'energy_rating' && empty($data[$field])) {
                    $update_params[":{$field}"] = null;
                }
            }
        }
        
        if (empty($set_parts)) {
            // ถ้าไม่มีฟิลด์ใดถูกส่งมาเลย
            Response::json(400, ["error" => "No fields provided for update"]);
        }

        $sql = "UPDATE appliances SET " . implode(", ", $set_parts) . " WHERE id = :id";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($update_params);

            // ดึงข้อมูลล่าสุดมาแสดง
            $sql_fetch = "SELECT * FROM appliances WHERE id = :id";
            $stmt_fetch = $this->db->prepare($sql_fetch);
            $stmt_fetch->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt_fetch->execute();
            $updated_appliance = $stmt_fetch->fetch();

            Response::json(200, ["message" => "Updated", "data" => $updated_appliance]);
            
        } catch (PDOException $e) {
            Response::json(500, ["error" => "Failed to update appliance", "details" => $e->getMessage()]);
        }
    }

    /**
     * [DELETE] ลบสินค้า
     * @param int $id ID สินค้าที่ต้องการลบ
     */
    public function delete(int $id): void {
        // 1. ตรวจสอบว่ามีสินค้าหรือไม่
        $sql_check = "SELECT id FROM appliances WHERE id = :id";
        $stmt_check = $this->db->prepare($sql_check);
        $stmt_check->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt_check->execute();
        
        if (!$stmt_check->fetch()) {
            Response::json(404, ["error" => "Not found"]);
        }

        // 2. ดำเนินการลบ
        $sql_delete = "DELETE FROM appliances WHERE id = :id";
        
        try {
            $stmt_delete = $this->db->prepare($sql_delete);
            $stmt_delete->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt_delete->execute();

            Response::json(200, ["message" => "Deleted"]);
        } catch (PDOException $e) {
            Response::json(500, ["error" => "Failed to delete appliance", "details" => $e->getMessage()]);
        }
    }
}