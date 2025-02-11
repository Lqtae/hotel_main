<?php // view_hotel.php
require 'db.php';

if (!isset($_GET['id'])) {
    die("ไม่พบข้อมูลโรงแรม");
}

$hotel_id = $_GET['id'];

$stmt = $pdo->prepare("
    SELECT hotels.hotel_id, hotels.hotel_name, hotels.address, provinces.province_name
    FROM hotels
    LEFT JOIN provinces ON hotels.province_id = provinces.province_id
    WHERE hotels.hotel_id = :hotel_id
");

$stmt->execute([':hotel_id' => $hotel_id]);
$hotel = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$hotel) {
    die("ไม่พบข้อมูลโรงแรม");
}

$stmt = $pdo->prepare("
    SELECT hr.hotel_room_id, hr.room_name, hr.room_description, hr.room_price 
    FROM hotel_rooms hr
    WHERE hr.hotel_id = :hotel_id
");
$stmt->execute([':hotel_id' => $hotel_id]);
$rooms = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ดึงรูปภาพของแต่ละห้อง
$room_images = [];
foreach ($rooms as $room) {
    $stmt = $pdo->prepare("
        SELECT image_path FROM room_images WHERE hotel_room_id = :hotel_room_id
    ");
    $stmt->execute([':hotel_room_id' => $room['hotel_room_id']]);
    $room_images[$room['hotel_room_id']] = $stmt->fetchAll(PDO::FETCH_COLUMN);
}

// ดึงข้อมูลประเภทห้อง
$stmt = $pdo->prepare("SELECT room_type_id, room_type_name FROM room_types");
$stmt->execute();
$roomTypes = $stmt->fetchAll(PDO::FETCH_ASSOC);


// ตรวจสอบการเพิ่มห้องพัก
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_room') {
    $roomTypeId = $_POST['room_type_id'] ?? '';
    $roomName = $_POST['room_name'] ?? '';
    $roomDescription = $_POST['room_description'] ?? '';
    $roomPrice = $_POST['room_price'] ?? 0;

    if (!empty($roomTypeId) && !empty($roomName) && !empty($roomDescription) && is_numeric($roomPrice)) {
        // เพิ่มข้อมูลห้องพักลงในฐานข้อมูล
        $stmt = $pdo->prepare("
            INSERT INTO hotel_rooms (hotel_id, room_type_id, room_name, room_description, room_price)
            VALUES (:hotel_id, :room_type_id, :room_name, :room_description, :room_price)
        ");
        $stmt->execute([
            ':hotel_id' => $hotel_id,
            ':room_type_id' => $roomTypeId,
            ':room_name' => $roomName,
            ':room_description' => $roomDescription,
            ':room_price' => $roomPrice,
        ]);

        // ดึงค่า room_id ที่เพิ่มล่าสุด
        $roomId = $pdo->lastInsertId();

        // ตรวจสอบและอัปโหลดรูปภาพ (รองรับหลายไฟล์)
        if (!empty($_FILES['room_images']['name'][0])) {
            $targetDir = __DIR__ . "/../src/img/room_img/";

            foreach ($_FILES['room_images']['name'] as $key => $fileName) {
                $targetFilePath = $targetDir . basename($fileName);
                $imagePath = "/hotel_main/src/img/room_img/" . basename($fileName);

                if (move_uploaded_file($_FILES['room_images']['tmp_name'][$key], $targetFilePath)) {
                    $stmt = $pdo->prepare("
                        INSERT INTO room_images (hotel_room_id, image_path)
                        VALUES (:hotel_room_id, :image_path)
                    ");
                    $stmt->execute([
                        ':hotel_room_id' => $roomId,
                        ':image_path' => $imagePath
                    ]);
                }
            }
        }

        // รีเฟรชหน้าเพื่อแสดงข้อมูลใหม่
        header("Location: view_hotel.php?id=$hotel_id");
        exit;
    } else {
        echo "<script>alert('กรุณากรอกข้อมูลให้ครบถ้วน');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($hotel['hotel_name']) ?> - ข้อมูลโรงแรม</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="icon" href="./img/icon.png">
</head>
<body class="bg-gray-100 min-h-screen flex flex-col">

    <header class="w-full bg-gray-100 py-6 shadow-md">
        <h1 class="text-3xl font-bold text-center">View</h1>
    </header>

    <main class="flex-grow">
        <div class="absolute top-6 left-4">
            <a href="javascript:history.back()" class="text-gray-700 font-bold text-lg px-4 py-2 rounded-lg hover:text-blue-600">
                &lt; Back
            </a>
        </div>

        <div class="max-w-4xl mx-auto mt-8 mb-8 p-6 bg-white shadow-md rounded-lg">
            <a href="edit_hotel.php?id=<?= $hotel['hotel_id'] ?>">
                <h1 class="text-2xl font-bold mb-4"><?= htmlspecialchars($hotel['hotel_name']) ?></h1>
            </a>
            <p><strong>ที่อยู่:</strong> <?= htmlspecialchars($hotel['address'] ?? 'ไม่มีที่อยู่') ?></p>
            <p><strong>จังหวัด:</strong> <?= htmlspecialchars($hotel['province_name']) ?></p>

            <hr class="my-6">
            
            <h2 class="text-xl font-bold mb-4">เพิ่มห้องพัก</h2>

            <form action="" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="add_room">
                
                <div class="mb-4">
                    <label class="block text-gray-700">ชื่อห้องพัก:</label>
                    <input type="text" name="room_name" required class="w-full p-2 border rounded-lg">
                </div>

                <div class="mb-4">
                    <label for="roomType" class="block text-gray-700">ประเภทห้อง:</label>
                    <select id="roomType" name="room_type_id" required class="w-full border px-4 py-2 rounded-lg">
                        <?php foreach ($roomTypes as $roomType): ?>
                            <option value="<?= $roomType['room_type_id'] ?>">
                                <?= htmlspecialchars($roomType['room_type_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700">รายละเอียด:</label>
                    <textarea name="room_description" required class="w-full p-2 border rounded-lg"></textarea>
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700">ราคาห้องพัก (บาท):</label>
                    <input type="number" name="room_price" required class="w-full p-2 border rounded-lg">
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700">อัปโหลดรูปภาพ:</label>
                    <input type="file" name="room_images[]" multiple accept="image/*" class="w-full p-2 border rounded-lg">
                </div>

                <button type="submit" class="w-full bg-blue-500 text-white p-2 rounded-lg hover:bg-blue-600">
                    เพิ่มห้องพัก
                </button>
            </form>

            <hr class="my-6">

            <h2 class="text-xl font-bold mt-6 mb-4">ห้องพัก</h2>

            <?php if (!empty($rooms)): ?>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <?php foreach ($rooms as $room): ?>
                        <a href="edit_room.php?id=<?= $room['hotel_room_id'] ?>" class="block bg-white shadow-lg rounded-lg overflow-hidden hover:shadow-2xl transition-shadow duration-300">
                            <img src="<?= htmlspecialchars($room_images[$room['hotel_room_id']][0] ?? '/hotel_main/src/img/no_image.png') ?>" alt="Room Image" class="w-full h-40 object-cover">
                            <div class="p-4">
                                <h3 class="text-lg font-bold"><?= htmlspecialchars($room['room_name']) ?></h3>
                                <p class="text-gray-600 text-sm mb-2"><?= htmlspecialchars($room['room_description']) ?></p>
                                <p class="text-gray-800 font-semibold"><?= number_format($room['room_price'], 2) ?> บาท</p>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="text-gray-500 text-center">ไม่มีข้อมูลห้องพัก</p>
            <?php endif; ?>
        </div>
    </main>

    <footer class="w-full bg-white py-4 mt-8 shadow-md">
        <p class="text-black text-center text-sm">
            &copy; 2025 <a href="index.php" class="text-black hover:font-semibold">Where's Hotel</a>
        </p>
    </footer>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const fileInput = document.querySelector('input[name="room_images[]"]');
            const previewContainer = document.createElement("div");
            previewContainer.classList.add("grid", "grid-cols-2", "gap-2", "mt-4");
            fileInput.parentNode.appendChild(previewContainer);
        
            fileInput.addEventListener("change", function () {
                previewContainer.innerHTML = "";
                if (fileInput.files) {
                    Array.from(fileInput.files).forEach(file => {
                        const reader = new FileReader();
                        reader.onload = function (e) {
                            const img = document.createElement("img");
                            img.src = e.target.result;
                            img.classList.add("w-36", "h-36", "object-cover", "rounded-md");
                            previewContainer.appendChild(img);
                        };
                        reader.readAsDataURL(file);
                    });
                }
            });
        });
    </script>

</body>
</html>