<?php //delete_hotel_image.php
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['image_id'])) {
    $imageId = $_POST['image_id'];

    // ดึงพาธรูปภาพจากฐานข้อมูล
    $stmt = $pdo->prepare("SELECT image_path FROM hotel_images WHERE image_id = :image_id");
    $stmt->execute([':image_id' => $imageId]);
    $image = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($image) {
        unlink(__DIR__ . $image['image_path']); // ลบไฟล์จากเซิร์ฟเวอร์
        $stmt = $pdo->prepare("DELETE FROM hotel_images WHERE image_id = :image_id");
        $stmt->execute([':image_id' => $imageId]);
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false, "error" => "ไม่พบรูปภาพ"]);
    }
}
?>
