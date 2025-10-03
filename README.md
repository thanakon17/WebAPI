Lab10 - Web API
สร้างฐานข้อมูล MySQL
<img width="856" height="98" alt="image" src="https://github.com/user-attachments/assets/b16adb10-5a3c-4844-9c81-2d321ea85a48" />

ตารางข้อมูลสินค้า (products)
<img width="1915" height="454" alt="Screenshot 2025-10-03 160414" src="https://github.com/user-attachments/assets/5d6a7fcd-2b38-4070-bb8c-bd7a3fbc2243" />

ผลลัพธ์
ดูสินค้าทั้งหมด

GET http://localhost/lab10-WebAPI/api.php
image
ดูสินค้า 1 รายการ

GET http://localhost/lab10-api/api.php?id=1
image
เพิ่มสินค้า (POST)

POST http://localhost/lab10-api/api.php
{
  "name": "Test Shoes",
  "brand": "TestBrand",
  "price": 1999,
  "stock": 5,
  "description": "รองเท้าทดสอบ",
  "image_url": "https://placehold.co/400x400/e74c3c/ffffff?text=Test+Shoes"
}
image
แก้ไขสินค้า (PUT)

PUT http://localhost/lab10-api/api.php?id=1
{
  "name": "Updated Shoes",
  "brand": "NewBrand",
  "price": 2999,
  "stock": 10,
  "description": "แก้ไขแล้ว",
  "image_url": "https://placehold.co/400x400/e74c3c/ffffff?text=Updated Shoes"
}
image
ลบสินค้า (DELETE)

DELETE http://localhost/lab10-WebAPI/api.php?id=1
image
