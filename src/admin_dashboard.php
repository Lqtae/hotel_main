<?php //admin_dashboard.php
// เชื่อมต่อฐานข้อมูล
require 'db.php';

// ดึงข้อมูลโรงแรมทั้งหมด
$hotels = $pdo->query("
    SELECT hotels.hotel_id, hotels.hotel_name, hotels.address, provinces.province_name,
           GROUP_CONCAT(hotel_rooms.room_name SEPARATOR ', ') AS room_names
    FROM hotels
    LEFT JOIN provinces ON hotels.province_id = provinces.province_id
    LEFT JOIN hotel_rooms ON hotels.hotel_id = hotel_rooms.hotel_id
    GROUP BY hotels.hotel_id
")->fetchAll(PDO::FETCH_ASSOC);

// ดึงข้อมูลจังหวัดทั้งหมด
$provinces = $pdo->query("SELECT * FROM provinces")->fetchAll(PDO::FETCH_ASSOC);

// ดึงข้อมูลประเภทห้องทั้งหมด
$roomTypes = $pdo->query("SELECT * FROM room_types")->fetchAll(PDO::FETCH_ASSOC);

// จัดการคำขอ POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($_POST['action'] === 'add_hotel') {
        // เพิ่มโรงแรม
        $hotelName = $_POST['hotel_name'];
        $address = $_POST['address'];
        $provinceId = $_POST['province_id'];

        $stmt = $pdo->prepare("
            INSERT INTO hotels (hotel_name, address, province_id)
            VALUES (:hotel_name, :address, :province_id)
        ");
        $stmt->execute([
            ':hotel_name' => $hotelName,
            ':address' => $address,
            ':province_id' => $provinceId,
        ]);

        $hotelId = $pdo->lastInsertId();

        if (!empty($_FILES['hotel_image']['name'])) {
            $targetDir = __DIR__ . "/../src/img/hotel_img/"; // โฟลเดอร์เก็บรูปภาพ
            $originalFileName = $_FILES['hotel_image']['name']; // ชื่อไฟล์เดิม
            $targetFilePath = $targetDir . $originalFileName;
            $imagePath = "/hotel_main/src/img/hotel_img/" . $originalFileName; // พาธเก็บใน Database
    
            // อัปโหลดไฟล์
            if (move_uploaded_file($_FILES['hotel_image']['tmp_name'], $targetFilePath)) {
                // บันทึกลง Database
                $sql = "INSERT INTO hotel_images (hotel_id, image_path) VALUES (:hotel_id, :image_path)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    ':hotel_id' => $hotelId,
                    ':image_path' => $imagePath
                ]);
            } else {
                echo "เกิดข้อผิดพลาดในการอัปโหลดไฟล์";
            }
        } 

        header('Location: admin_dashboard.php');
        exit;

    } elseif ($_POST['action'] === 'delete') {
        // ลบโรงแรม
        $hotelId = $_POST['hotel_id'];
        $stmt = $pdo->prepare("DELETE FROM hotels WHERE hotel_id = :hotel_id");
        $stmt->execute([':hotel_id' => $hotelId]);
        header('Location: admin_dashboard.php');
        exit;
    }     
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="icon" href="./img/icon.png">
</head>
<body class="bg-gray-100 flex flex-col min-h-screen">

<header class="w-full bg-gray-100 py-6 shadow-md sticky top-0 z-10"> 
    <h1 class="text-3xl font-bold text-center">Admin Dashboard</h1>

    <div class="absolute top-6 left-4">
            <a href="javascript:history.back()" class="text-gray-700 font-bold text-lg px-4 py-2 rounded-lg hover:text-blue-600">
                &lt; Back
            </a>
    </div>

    <div class="absolute top-6 right-4">
            <a href="index.php" class="text-gray-700 font-bold text-lg px-4 py-2 rounded-lg hover:text-blue-600">
                index
            </a>
    </div>
</header>

<main class="w-full max-w-4xl mx-auto mt-8 px-4 flex-grow">

    <!-- ส่วนเพิ่มข้อมูลโรงแรม -->
    <div class="bg-white shadow-md rounded-lg p-6 mb-8">
        <h2 class="text-xl font-bold mb-4">เพิ่มข้อมูลโรงแรม</h2>
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="action" value="add_hotel">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="hotelName" class="block text-gray-700">ชื่อโรงแรม:</label>
                    <input type="text" id="hotelName" name="hotel_name" class="w-full border px-4 py-2 rounded-lg" required>
                </div>
                <div>
                    <label for="address" class="block text-gray-700">ที่อยู่:</label>
                    <input type="text" id="address" name="address" class="w-full border px-4 py-2 rounded-lg" required>
                </div>
                <div>
                    <label for="province" class="block text-gray-700">จังหวัด:</label>
                    <select id="province" name="province_id" class="w-full border px-4 py-2 rounded-lg">
                        <?php foreach ($provinces as $province): ?>
                            <option value="<?= $province['province_id'] ?>"><?= $province['province_name'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label for="hotelImage" class="block text-gray-700">เลือกรูปภาพ:</label>
                    <input type="file" id="hotelImage" name="hotel_image" class="w-full border px-4 py-2 rounded-lg" accept="image/*" required>
                    <img id="hotelImagePreview" class="mt-2 hidden w-32 h-32 object-cover border rounded-lg" />
                </div>
            </div>
                <div class="mt-4 flex justify-end">
                <button type="submit" class="bg-green-600 border-2 border-green-600 text-white px-4 py-2 rounded-lg hover:bg-transparent hover:text-green-600">เพิ่มโรงแรม</button>
                </div>
        </form>
    </div>

    <!-- ส่วนค้นหา -->
    <div class="bg-white shadow-md rounded-lg p-6 mb-8">
    <h2 class="text-xl font-bold mb-4">ค้นหาโรงแรม</h2>
    <input type="text" id="searchInput" placeholder="พิมพ์ชื่อโรงแรมเพื่อค้นหา..."
           class="w-full border px-4 py-2 rounded-lg">
    </div>

    <!-- ตารางรายการโรงแรม -->
    <div class="bg-white shadow-md rounded-lg p-6 ">
    <h2 class="text-xl font-bold mb-4">รายการโรงแรม</h2>
    <table class="table-auto w-full border-collapse" id = "hotelTable">
        <thead>
            <tr>
                <th class="border px-4 py-2">#</th>
                <th class="border px-4 py-2">ชื่อโรงแรม</th>
                <th class="border px-4 py-2">จังหวัด</th>
                <th class="border px-4 py-2">จัดการ</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($hotels as $index => $hotel): ?>
                <tr>
                    <td class="border px-4 py-2 text-center"><?= $index + 1 ?></td>
                    <td class="border px-4 py-2"><?= htmlspecialchars($hotel['hotel_name']) ?></td>
                    <td class="border px-4 py-2"><?= htmlspecialchars($hotel['province_name']) ?></td>
                    <td class="border px-4 py-2 text-center flex gap-2 justify-center">
                        <!-- ปุ่มดูรายละเอียด -->
                        <a href="view_hotel.php?id=<?= $hotel['hotel_id'] ?>" class="bg-blue-600 border-blue-600 border-2 text-white px-4 py-2 rounded-lg hover:bg-transparent hover:text-blue-600">
                            View
                        </a>
                        

                        <!-- ปุ่มลบ -->
                        <form method="POST" class="inline-block delete-form">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="hotel_id" value="<?= $hotel['hotel_id'] ?>">
                            <button type="button" class="bg-red-600 border-2 border-red-600 text-white px-4 py-2 rounded-lg delete-button hover:bg-transparent hover:text-red-600">
                                Delete
                            </button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    </div>
</main>

    <footer class="w-full bg-white py-4 mt-8 shadow-md">
        <p class="text-black text-center text-sm">
            &copy; 2025 <a href="index.php" class="text-black hover:font-semibold">Where's Hotel</a>
        </p>
    </footer>

<script>
    // ฟังก์ชันยืนยันการลบ
    const deleteButtons = document.querySelectorAll('.delete-button');
    deleteButtons.forEach(button => {
        button.addEventListener('click', () => {
            if (confirm('คุณแน่ใจหรือไม่ว่าต้องการลบรายการนี้?')) {
                button.parentElement.submit();
            }
        });
    });

    // ฟังก์ชันค้นหา
    const searchInput = document.getElementById('searchInput');
    const hotelTable = document.getElementById('hotelTable');
    searchInput.addEventListener('input', () => {
        const searchValue = searchInput.value.toLowerCase();
        Array.from(hotelTable.querySelectorAll('tbody tr')).forEach(row => {
            const hotelName = row.children[1].textContent.toLowerCase();
            row.style.display = hotelName.includes(searchValue) ? '' : 'none';
        });
    });

    document.addEventListener("DOMContentLoaded", function () {
    function previewImage(input, previewId) {
        const preview = document.getElementById(previewId);
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function (e) {
                preview.src = e.target.result;
                preview.classList.remove("hidden");
            };
            reader.readAsDataURL(input.files[0]);
        } else {
            preview.classList.add("hidden");
        }
    }

    // สำหรับการเลือกไฟล์รูปภาพของโรงแรม
    document.getElementById("hotelImage").addEventListener("change", function () {
        previewImage(this, "hotelImagePreview");
    });

    // สำหรับการเลือกไฟล์รูปภาพของห้อง
    document.getElementById("roomImage").addEventListener("change", function () {
        previewImage(this, "roomImagePreview");
    });
    });
</script>
</body>
</html>