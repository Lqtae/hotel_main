<?php // view_hotel.php
require 'db.php';
require 'functions.php';

if (!isset($_GET['id'])) {
    die("ไม่พบข้อมูลโรงแรม");
}

$hotel_id = $_GET['id'];

$hotel = getHotelDetailsById($hotel_id);
if (!$hotel) {
    die("ไม่พบข้อมูลโรงแรม");
}

$rooms = getRoomsByHotelId($hotel_id);
$room_images = [];
foreach ($rooms as $room) {
    $room_images[$room['hotel_room_id']] = getRoomImages($room['hotel_room_id']);
}

$roomTypes = getAllRoomTypes();

// ตรวจสอบการเพิ่มห้องพัก
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_room') {
    $roomData = [
        'room_type_id' => $_POST['room_type_id'] ?? '',
        'room_name' => $_POST['room_name'] ?? '',
        'room_description' => $_POST['room_description'] ?? '',
        'room_price' => $_POST['room_price'] ?? 0,
        'primary_image_index' => $_POST['primary_image_index'] ?? 0
    ];

    if (!empty($roomData['room_type_id']) && !empty($roomData['room_name']) && !empty($roomData['room_description']) && is_numeric($roomData['room_price'])) {
        $result = addRoom($hotel_id, $roomData, $_FILES);
        if ($result['success']) {
            header("Location: view_hotel.php?id=$hotel_id");
            exit;
        } else {
            echo "<script>alert('เกิดข้อผิดพลาด: " . $result['error'] . "');</script>";
        }
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

    <header class="w-full bg-gray-100 py-6 shadow-md sticky top-0 z-10">
        <h1 class="text-3xl font-bold text-center">View</h1>

        <div class="absolute top-6 left-4">
            <a href="javascript:history.back()" class="text-gray-700 font-bold text-lg px-4 py-2 rounded-lg hover:text-blue-600">
                &lt; Back
            </a>
        </div>
    </header>

    <main class="flex-grow">

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
                    <label class="block text-gray-700 font-semibold">อัปโหลดรูปภาพห้องพัก:</label>

                    <!-- ช่องอัปโหลด (ไม่แสดงรูปในนี้) -->
                    <div id="uploadBox" class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center cursor-pointer hover:border-blue-500 transition">
                        📷 <span class="text-gray-500">คลิกเพื่ออัปโหลด</span>
                        <input type="file" name="room_images[]" multiple accept="image/*" class="hidden" id="roomImages">
                    </div>

                    <!-- แสดงรูปที่อัปโหลด (แยกต่างหาก) -->
                    <div id="imagePreview" class="grid grid-cols-3 gap-3 mt-4"></div>

                    <!-- เก็บค่ารูปหลัก -->
                    <input type="hidden" name="primary_image_index" id="primaryImageIndex" value="0">
                </div>

                <button type="submit" class="w-full bg-blue-500 border-blue-500 border-2 text-white p-2 rounded-lg hover:bg-transparent hover:text-blue-600">
                    เพิ่มห้องพัก
                </button>
            </form>

            <hr class="my-6">

            <h2 class="text-xl font-bold mt-6 mb-4">ห้องพัก</h2>

            <?php if (!empty($rooms)): ?>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <?php foreach ($rooms as $room): ?>
                        <?php
                            // ดึงรูปหลักของห้องนี้
                            $stmt = $pdo->prepare("
                                SELECT image_path FROM room_images 
                                WHERE hotel_room_id = :hotel_room_id AND is_primary = 1
                                LIMIT 1
                            ");
                            $stmt->execute([':hotel_room_id' => $room['hotel_room_id']]);
                            $primaryImage = $stmt->fetchColumn();
                    
                            // ถ้าไม่มีรูปหลัก ใช้รูปแรกของห้องแทน
                            if (!$primaryImage && !empty($room_images[$room['hotel_room_id']])) {
                                $primaryImage = $room_images[$room['hotel_room_id']][0];
                            }
                        
                            // ถ้าไม่มีรูปภาพเลย ให้ใช้รูป `no_image.png`
                            if (!$primaryImage) {
                                $primaryImage = "/hotel_main/src/img/no_image.png";
                            }
                        ?>
                        <a href="edit_room.php?id=<?= $room['hotel_room_id'] ?>" 
                           class="block bg-white shadow-lg rounded-lg overflow-hidden hover:shadow-2xl transition-shadow duration-300">
                            <img src="<?= htmlspecialchars($primaryImage) ?>" alt="Room Image" class="w-full h-40 object-cover">
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

        document.addEventListener("DOMContentLoaded", function () {
            const fileInput = document.querySelector('#roomImages');
            const previewContainer = document.querySelector('#imagePreview');
            const primaryImageInput = document.querySelector('#primaryImageIndex');
            const uploadBox = document.querySelector("#uploadBox");

            // ปิดการแสดงรูปในกล่องอัปโหลด
            uploadBox.innerHTML = `<span class="text-gray-500">📷 คลิกเพื่ออัปโหลด</span>`;

            // เมื่อคลิกที่ช่องอัปโหลด
            uploadBox.addEventListener("click", function () {
                fileInput.click();
            });
        
            // เมื่ออัปโหลดไฟล์
            fileInput.addEventListener("change", function () {
                previewContainer.innerHTML = ""; // ล้างรูปเก่าที่เคยแสดง
            
                if (fileInput.files.length > 0) {
                    Array.from(fileInput.files).forEach((file, index) => {
                        const reader = new FileReader();
                        reader.onload = function (e) {
                            // สร้าง div ห่อรูป
                            const imgWrapper = document.createElement("div");
                            imgWrapper.classList.add("relative", "group", "border", "border-gray-200", "rounded-lg", "p-2");
                        
                            // รูปที่อัปโหลด
                            const img = document.createElement("img");
                            img.src = e.target.result;
                            img.classList.add("w-full", "h-24", "object-cover", "rounded-md");
                        
                            // ปุ่มตั้งเป็นรูปหลัก
                            const selectBtn = document.createElement("button");
                            selectBtn.innerText = index === 0 ? "⭐ รูปหลัก" : "ตั้งเป็นรูปหลัก";
                            selectBtn.classList.add("absolute", "top-1", "left-1", "bg-white", "text-xs", "p-1", "rounded", "shadow-md", "hover:bg-gray-100", "transition");
                        
                            // คลิกเพื่อเปลี่ยนรูปหลัก
                            selectBtn.addEventListener("click", function (e) {
                                e.preventDefault();
                                document.querySelectorAll("#imagePreview button").forEach(btn => btn.innerText = "ตั้งเป็นรูปหลัก");
                                selectBtn.innerText = "⭐ รูปหลัก";
                                primaryImageInput.value = index;
                            });
                        
                            imgWrapper.appendChild(img);
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