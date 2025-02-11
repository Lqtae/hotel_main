<?php //upload_hotel_image.php
require 'db.php';

$hotelId = $_POST['hotel_id'] ?? null;
if (!$hotelId || !is_numeric($hotelId)) {
    echo json_encode(['success' => false, 'error' => 'Hotel ID is required and must be a valid number.']);
    exit;
}

if (isset($_FILES['hotel_image']) && $_FILES['hotel_image']['error'] == 0) {
    // กำหนดตำแหน่งเก็บไฟล์
    $uploadDir = '/hotel_main/src/img/';
    $fileName = basename($_FILES['hotel_image']['name']);
    $filePath = $uploadDir . $fileName;
    
    // ตรวจสอบว่าไฟล์เป็นประเภทที่อนุญาตหรือไม่ (ตัวอย่างเช่น jpg, png)
    $allowedTypes = ['image/jpeg', 'image/png'];
    if (!in_array($_FILES['hotel_image']['type'], $allowedTypes)) {
        echo json_encode(['success' => false, 'error' => 'Invalid file type.']);
        exit;
    }
    
    // อัปโหลดไฟล์
    if (move_uploaded_file($_FILES['hotel_image']['tmp_name'], $_SERVER['DOCUMENT_ROOT'] . $filePath)) {
        // บันทึกข้อมูลลงในฐานข้อมูล
        $stmt = $pdo->prepare("INSERT INTO hotel_images (hotel_id, image_path) VALUES (?, ?)");
        $stmt->execute([$hotelId, $filePath]);

        // ส่งข้อมูลกลับ
        echo json_encode(['success' => true, 'image_id' => $pdo->lastInsertId(), 'image_path' => $filePath]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to upload the image.']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'No image uploaded.']);
}
?>
