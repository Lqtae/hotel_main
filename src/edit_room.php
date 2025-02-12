<?php //edit_room.php 
require 'db.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("ไม่พบข้อมูลห้องพัก");
}

$room_id = intval($_GET['id']);

// ดึงข้อมูลห้องพัก
$stmt = $pdo->prepare("SELECT * FROM hotel_rooms WHERE hotel_room_id = :room_id");
$stmt->execute([':room_id' => $room_id]);
$room = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$room) {
    die("ไม่พบข้อมูลห้องพัก");
}

// ดึงรูปหลักของห้องพัก
$stmt = $pdo->prepare("SELECT image_path FROM room_images WHERE hotel_room_id = :room_id AND is_primary = 1 LIMIT 1");
$stmt->execute([':room_id' => $room_id]);
$primary_image = $stmt->fetchColumn();

// ถ้าไม่มีรูปหลัก ให้ใช้รูปแรกสุดแทน
if (!$primary_image) {
    $stmt = $pdo->prepare("SELECT image_path FROM room_images WHERE hotel_room_id = :room_id LIMIT 1");
    $stmt->execute([':room_id' => $room_id]);
    $primary_image = $stmt->fetchColumn();
}

// ดึงรูปภาพทั้งหมดของห้องพัก
$stmt = $pdo->prepare("SELECT image_path, is_primary FROM room_images WHERE hotel_room_id = :room_id");
$stmt->execute([':room_id' => $room_id]);
$room_images = $stmt->fetchAll(PDO::FETCH_ASSOC);

// อัปโหลดรูปใหม่
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_room'])) {
    $room_name = trim($_POST['room_name'] ?? '');
    $room_description = trim($_POST['room_description'] ?? '');
    $room_price = floatval($_POST['room_price'] ?? 0);
    $primary_index = intval($_POST['primary_image_index'] ?? 0);

    if ($room_name && $room_description && $room_price > 0) {
        $stmt = $pdo->prepare("UPDATE hotel_rooms SET room_name = :room_name, room_description = :room_description, room_price = :room_price WHERE hotel_room_id = :room_id");
        $stmt->execute([
            ':room_name' => $room_name,
            ':room_description' => $room_description,
            ':room_price' => $room_price,
            ':room_id' => $room_id
        ]);

    // อัปโหลดรูปภาพใหม่
    if (!empty($_FILES['room_images']['name'][0])) {
        $upload_dir = __DIR__ . "/../src/img/room_img/"; // โฟลเดอร์เก็บรูปภาพ
        $db_upload_path = "/hotel_main/src/img/room_img/"; // พาธที่จะบันทึกลง database
    
        // ตรวจสอบว่ามีโฟลเดอร์หรือไม่ ถ้าไม่มีให้สร้าง
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
    
        foreach ($_FILES['room_images']['tmp_name'] as $key => $tmp_name) {
            if ($_FILES['room_images']['error'][$key] === UPLOAD_ERR_OK) {
                $originalFileName = $_FILES['room_images']['name'][$key]; // ใช้ชื่อไฟล์เดิม
                $target_file = $upload_dir . $originalFileName; // พาธสำหรับเซิร์ฟเวอร์
                $db_image_path = $db_upload_path . $originalFileName; // พาธที่บันทึกใน database
            
                if (move_uploaded_file($tmp_name, $target_file)) {
                    $is_primary = ($key == $primary_index) ? 1 : 0;
                
                    if ($is_primary) {
                        $pdo->prepare("UPDATE room_images SET is_primary = 0 WHERE hotel_room_id = :room_id")->execute([':room_id' => $room_id]);
                    }
                
                    $stmt = $pdo->prepare("INSERT INTO room_images (hotel_room_id, image_path, is_primary) VALUES (:room_id, :image_path, :is_primary)");
                    $stmt->execute([
                        ':room_id' => $room_id,
                        ':image_path' => $db_image_path, // บันทึกพาธรูปแบบ `/hotel_main/src/img/room_img/ชื่อไฟล์เดิม.jpg`
                        ':is_primary' => $is_primary
                    ]);
                }
            }
        }
    }

        header("Location: edit_room.php?id=" . $room_id);
        exit();
    }
}

// อัปเดตภาพหลัก
if (isset($_POST['set_primary'])) {
    $image_path = $_POST['set_primary'];

    // อัปเดตรูปอื่นให้เป็น `is_primary = 0`
    $pdo->prepare("UPDATE room_images SET is_primary = 0 WHERE hotel_room_id = :room_id")->execute([':room_id' => $room_id]);

    // ตั้งค่าภาพที่เลือกเป็นรูปหลัก
    $pdo->prepare("UPDATE room_images SET is_primary = 1 WHERE hotel_room_id = :room_id AND image_path = :image_path")->execute([
        ':room_id' => $room_id,
        ':image_path' => $image_path
    ]);

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
    <link rel="icon" href="./img/icon.png">
</head>
<body class="bg-gray-100 min-h-screen flex flex-col">

    <header class="w-full bg-gray-100 py-6 shadow-md">
        <h1 class="text-3xl font-bold text-center">แก้ไขห้องพัก</h1>
        <div class="absolute top-6 left-4">
            <a href="javascript:history.back()" class="text-gray-700 font-bold text-lg px-4 py-2 rounded-lg hover:text-blue-600">
                &lt; Back
            </a>
        </div>
    </header>

    <main class="flex-grow flex justify-center items-center">
        <div class="max-w-3xl w-full bg-white mt-8 p-6 shadow-lg rounded-lg">
            <h1 class="text-2xl font-bold mb-4"><?= htmlspecialchars($room['room_name']) ?></h1>

            <form method="POST" enctype="multipart/form-data">
                <label class="block font-semibold">ชื่อห้อง</label>
                <input type="text" name="room_name" value="<?= htmlspecialchars($room['room_name']) ?>" required class="w-full px-4 py-2 border rounded-md mb-4">

                <label class="block font-semibold">รายละเอียด</label>
                <textarea name="room_description" required class="w-full h-24 px-4 py-2 border rounded-md mb-4"><?= htmlspecialchars($room['room_description']) ?></textarea>

                <label class="block font-semibold">ราคา (บาท)</label>
                <input type="number" name="room_price" value="<?= htmlspecialchars($room['room_price']) ?>" required class="w-full px-4 py-2 border rounded-md mb-4">

                <div class="mb-4">
                    <label class="block text-gray-700 font-semibold">อัปโหลดรูปภาพห้องพัก:</label>

                    <div id="uploadBox" class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center cursor-pointer hover:border-blue-500 transition">
                        📷 <span class="text-gray-500">คลิกเพื่ออัปโหลด</span>
                        <input type="file" name="room_images[]" multiple accept="image/*" class="hidden" id="roomImages">
                    </div>

                    <h2 class="text-xl font-bold mt-4">รูปที่อัปโหลด</h2>
                    <div id="imagePreview" class="grid grid-cols-2 gap-2 mt-4"></div>

                    <input type="hidden" name="primary_image_index" id="primaryImageIndex" value="0">
                </div>

                <button type="submit" name="update_room" class="w-full bg-blue-500 text-white py-2 rounded-md hover:bg-blue-600 transition">บันทึกการเปลี่ยนแปลง</button>
            </form>

            <h2 class="text-xl font-bold mt-6">รูปภาพห้องพัก</h2>
            
            <?php if (!empty($room_images)): ?>
                <div class="mt-4">
                    <h3 class="text-sm font-bold">รูปหลัก</h3>
                    <div class="mt-2 grid grid-cols-2 gap-2">
                        <img src="<?= htmlspecialchars($primary_image) ?>" class="w-full h-48 object-cover rounded-md shadow">
                    </div>

                    <h3 class="text-sm font-bold mt-4">รูปภาพทั้งหมด</h3>
                    <div class="grid grid-cols-2 gap-4 mt-4">
                        <?php foreach ($room_images as $image): ?>
                            <div class="relative group">
                                <img src="<?= htmlspecialchars($image['image_path']) ?>" class="w-full h-48 object-cover rounded-md shadow">

                                <form method="POST" class="absolute top-1 left-1">
                                    <input type="hidden" name="set_primary" value="<?= htmlspecialchars($image['image_path']) ?>">
                                    <button type="submit" class="bg-yellow-500 text-white px-2 py-1 rounded-md text-xs hover:bg-yellow-600">
                                        <?= $image['is_primary'] ? "⭐ รูปหลัก" : "ตั้งเป็นรูปหลัก" ?>
                                    </button>
                                </form>
                        
                                <form method="POST" class="absolute top-1 right-1">
                                    <input type="hidden" name="delete_image" value="<?= htmlspecialchars($image['image_path']) ?>">
                                    <button type="submit" class="bg-red-500 text-white px-2 py-1 rounded-md text-xs hover:bg-red-600">ลบ</button>
                                </form>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php else: ?>
                <p class="text-gray-500 mt-2">ไม่มีรูปภาพ</p>
            <?php endif; ?>

            <form method="POST" class="mt-6">
                <button type="submit" name="delete_room" class="w-full bg-red-500 text-white py-2 rounded-md hover:bg-red-600 transition">ลบห้องพัก</button>
            </form>
        </div>
    </main>

    <footer class="w-full bg-white py-4 mt-8 shadow-md">
        <p class="text-black text-center text-sm">
            &copy; 2025 <a href="index.php" class="text-black hover:font-semibold">Where's Hotel</a>
         </p>
    </footer>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const fileInput = document.querySelector('#roomImages');
            const previewContainer = document.querySelector('#imagePreview');
            const primaryImageInput = document.querySelector('#primaryImageIndex');
            const uploadBox = document.querySelector("#uploadBox");

            uploadBox.addEventListener("click", function () {
                fileInput.click();
            });

            fileInput.addEventListener("change", function () {
                previewContainer.innerHTML = ""; 
                if (fileInput.files.length > 0) {
                    Array.from(fileInput.files).forEach((file, index) => {
                        const reader = new FileReader();
                        reader.onload = function (e) {
                            const imgWrapper = document.createElement("div");
                            imgWrapper.classList.add("relative", "border", "rounded-lg", "p-2");

                            const img = document.createElement("img");
                            img.src = e.target.result;
                            img.classList.add("w-full", "h-36", "object-cover", "rounded-md");

                            const deleteBtn = document.createElement("button");
                            deleteBtn.innerText = "❌";
                            deleteBtn.classList.add("absolute", "top-1", "right-1", "bg-red-500", "text-white", "px-2", "py-1", "rounded-md", "text-xs", "hover:bg-red-600");

                            deleteBtn.addEventListener("click", function (e) {
                                e.preventDefault();
                                imgWrapper.remove();
                            });

                            const selectBtn = document.createElement("button");
                            selectBtn.innerText = index === 0 ? "⭐ รูปหลัก" : "ตั้งเป็นรูปหลัก";
                            selectBtn.classList.add("absolute", "top-1", "left-1", "bg-white", "text-xs", "p-1", "rounded", "shadow-md", "hover:bg-gray-100");

                            selectBtn.addEventListener("click", function (e) {
                                e.preventDefault();
                                document.querySelectorAll("#imagePreview button").forEach(btn => btn.innerText = "ตั้งเป็นรูปหลัก");
                                selectBtn.innerText = "⭐ รูปหลัก";
                                primaryImageInput.value = index;
                            });

                            imgWrapper.appendChild(img);
                            imgWrapper.appendChild(deleteBtn);
                            imgWrapper.appendChild(selectBtn);
                            previewContainer.appendChild(imgWrapper);
                        };
                        reader.readAsDataURL(file);
                    });
                }
            });
        });
    </script>

</body>
</html>