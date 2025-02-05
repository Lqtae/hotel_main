<?php
require 'db.php';

if (!isset($_GET['id'])) {
    die("ไม่พบข้อมูลห้องพัก");
}

$room_id = $_GET['id'];

// ดึงข้อมูลห้องพัก
$stmt = $pdo->prepare("SELECT * FROM hotel_rooms WHERE hotel_room_id = :room_id");
$stmt->execute([':room_id' => $room_id]);
$room = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$room) {
    die("ไม่พบข้อมูลห้องพัก");
}

// ดึงรูปภาพห้องพัก
$stmt = $pdo->prepare("SELECT image_path FROM room_images WHERE hotel_room_id = :room_id");
$stmt->execute([':room_id' => $room_id]);
$room_images = $stmt->fetchAll(PDO::FETCH_COLUMN);

// อัปเดตข้อมูลห้องพัก
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $room_name = $_POST['room_name'];
    $room_description = $_POST['room_description'];
    $room_price = $_POST['room_price'];

    // อัปเดตข้อมูลห้อง
    $stmt = $pdo->prepare("UPDATE hotel_rooms SET room_name = :room_name, room_description = :room_description, room_price = :room_price WHERE hotel_room_id = :room_id");
    $stmt->execute([
        ':room_name' => $room_name,
        ':room_description' => $room_description,
        ':room_price' => $room_price,
        ':room_id' => $room_id
    ]);

    // อัปโหลดรูปภาพใหม่
    if (!empty($_FILES['room_images']['name'][0])) {
        $upload_dir = "hotel_main/src/img/";

        foreach ($_FILES['room_images']['tmp_name'] as $key => $tmp_name) {
            $file_name = basename($_FILES['room_images']['name'][$key]);
            $target_file = $upload_dir . $file_name;

            if (move_uploaded_file($tmp_name, $target_file)) {
                $stmt = $pdo->prepare("INSERT INTO room_images (hotel_room_id, image_path) VALUES (:room_id, :image_path)");
                $stmt->execute([':room_id' => $room_id, ':image_path' => $target_file]);
            }
        }
    }

    header("Location: view_hotel.php?id=" . $room['hotel_id']);
    exit();
}

// ลบรูปภาพ
if (isset($_POST['delete_image'])) {
    $image_path = $_POST['delete_image'];

    $stmt = $pdo->prepare("DELETE FROM room_images WHERE hotel_room_id = :room_id AND image_path = :image_path");
    $stmt->execute([':room_id' => $room_id, ':image_path' => $image_path]);

    // ลบไฟล์ออกจากโฟลเดอร์
    if (file_exists($image_path)) {
        unlink($image_path);
    }

    header("Location: edit_room.php?id=" . $room_id);
    exit();
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>แก้ไขห้องพัก - <?= htmlspecialchars($room['room_name']) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex flex-col">

    <header class="w-full bg-gray-100 py-6 shadow-md">
        <h1 class="text-3xl font-bold text-center">แก้ไขห้องพัก</h1>
    </header>

    <main class="flex-grow flex justify-center items-center">
        <div class="absolute top-6 left-4">
            <a href="javascript:history.back()" class="text-gray-700 font-bold text-lg px-4 py-2 rounded-lg hover:text-blue-600">
                &lt; Back
            </a>
        </div>

        <div class="max-w-lg w-full bg-white p-6 shadow-lg rounded-lg">
            <h2 class="text-2xl font-bold mb-4">แก้ไขข้อมูลห้อง</h2>

            <form method="POST" enctype="multipart/form-data">
                <label class="block font-semibold">ชื่อห้อง</label>
                <input type="text" name="room_name" value="<?= htmlspecialchars($room['room_name']) ?>" required class="w-full px-4 py-2 border rounded-md mb-4">

                <label class="block font-semibold">รายละเอียด</label>
                <textarea name="room_description" required class="w-full px-4 py-2 border rounded-md mb-4"><?= htmlspecialchars($room['room_description']) ?></textarea>

                <label class="block font-semibold">ราคา (บาท)</label>
                <input type="number" name="room_price" value="<?= htmlspecialchars($room['room_price']) ?>" required class="w-full px-4 py-2 border rounded-md mb-4">

                <label class="block font-semibold">อัปโหลดรูปภาพใหม่</label>
                <input type="file" name="room_images[]" multiple class="w-full px-4 py-2 border rounded-md mb-4">

                <button type="submit" class="w-full bg-blue-500 text-white py-2 rounded-md hover:bg-blue-600 transition">บันทึกการเปลี่ยนแปลง</button>
            </form>

            <h2 class="text-xl font-bold mt-6">รูปภาพห้องพัก</h2>
            <div class="grid grid-cols-2 gap-4 mt-4">
                <?php foreach ($room_images as $image): ?>
                    <div class="relative">
                        <img src="<?= htmlspecialchars($image) ?>" class="w-full h-32 object-cover rounded-md shadow">
                        <form method="POST" class="absolute top-1 right-1">
                            <input type="hidden" name="delete_image" value="<?= htmlspecialchars($image) ?>">
                            <button type="submit" class="bg-red-500 text-white px-2 py-1 rounded-md text-xs hover:bg-red-600">ลบ</button>
                        </form>
                    </div>
                <?php endforeach; ?>

                <?php if (empty($room_images)): ?>
                    <p class="text-gray-500 text-center col-span-2">ไม่มีรูปภาพ</p>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <footer class="w-full bg-white py-4 shadow-md">
        <p class="text-black text-center text-sm">
            &copy; 2025 <a href="index.php" class="text-black hover:font-semibold">Where's Hotel</a>
        </p>
    </footer>

</body>
</html>