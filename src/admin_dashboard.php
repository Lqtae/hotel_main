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
        header('Location: admin_dashboard.php');
        exit;
    } elseif ($_POST['action'] === 'add_room') {
        $hotelId = $_POST['hotel_id'];
        $roomTypeId = $_POST['room_type_id'];
        $roomName = $_POST['room_name'];
        $roomDescription = $_POST['room_description'];
        $roomPrice = $_POST['room_price'];
    
        // เพิ่มข้อมูลห้องพักลงในตาราง hotel_rooms ก่อน
        $stmt = $pdo->prepare("
            INSERT INTO hotel_rooms (hotel_id, room_type_id, room_name, room_description, room_price)
            VALUES (:hotel_id, :room_type_id, :room_name, :room_description, :room_price)
        ");
        $stmt->execute([
            ':hotel_id' => $hotelId,
            ':room_type_id' => $roomTypeId,
            ':room_name' => $roomName,
            ':room_description' => $roomDescription,
            ':room_price' => $roomPrice,
        ]);
    
        // ดึงค่า room_id ที่เพิ่มล่าสุด
        $roomId = $pdo->lastInsertId();
    
        // ตรวจสอบว่าอัปโหลดไฟล์หรือไม่
        if (!empty($_FILES['room_image']['name'])) {
            $targetDir = __DIR__ . "/../src/img/"; // โฟลเดอร์ที่เก็บไฟล์
            $originalFileName = $_FILES['room_image']['name']; // ชื่อไฟล์เดิม
            $targetFilePath = $targetDir . $originalFileName;
            $imagePath = "/hotel_main/src/img/" . $originalFileName; // เก็บลง Database

            // อัปโหลดไฟล์
            if (move_uploaded_file($_FILES['room_image']['tmp_name'], $targetFilePath)) {
                // บันทึกลง Database
                $sql = "INSERT INTO room_images (hotel_room_id, image_path) VALUES (:hotel_room_id, :image_path)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    ':hotel_room_id' => $roomId, 
                    ':image_path' => $imagePath
                ]);
                header('Location: admin_dashboard.php');
                exit;
            } else {
                echo "เกิดข้อผิดพลาดในการอัปโหลดไฟล์";
            }
        } else {
            echo "กรุณาเลือกไฟล์รูปภาพ";
        }  
    } elseif ($_POST['action'] === 'delete') {
        // ลบโรงแรม
        $hotelId = $_POST['hotel_id'];
        $stmt = $pdo->prepare("DELETE FROM hotels WHERE hotel_id = :hotel_id");
        $stmt->execute([':hotel_id' => $hotelId]);
        header('Location: admin_dashboard.php');
        exit;
    } elseif ($_POST['action'] === 'edit') {
        // แก้ไขข้อมูลโรงแรม
        $hotelId = $_POST['hotel_id'];
        $hotelName = $_POST['hotel_name'];
        $address = $_POST['address'];
        $provinceId = $_POST['province_id'];

        $stmt = $pdo->prepare("
            UPDATE hotels
            SET hotel_name = :hotel_name, address = :address, province_id = :province_id
            WHERE hotel_id = :hotel_id
        ");
        $stmt->execute([
            ':hotel_name' => $hotelName,
            ':address' => $address,
            ':province_id' => $provinceId,
            ':hotel_id' => $hotelId,
        ]);
        header('Location: admin_dashboard.php');
        exit;
    } elseif ($_POST['action'] === 'edit_room') {
        $roomId = $_POST['room_id'];
        $roomName = $_POST['room_name'];
        $roomTypeId = $_POST['room_type_id'];
        $roomDescription = $_POST['room_description'];
        $roomPrice = $_POST['room_price'];
        $roomImage = $_POST['old_room_image']; // ค่ารูปเดิม
    
        if (!empty($_FILES['room_image']['name'])) {
            $uploadDir = '/hotel_main/src/img/';
            $fileTmpPath = $_FILES['room_image']['tmp_name'];
            $fileName = time() . '_' . basename($_FILES['room_image']['name']);
            $uploadPath = $uploadDir . $fileName;
    
            if (move_uploaded_file($fileTmpPath, __DIR__ . $uploadPath)) {
                $roomImage = $fileName;
            }
        }
    
        $stmt = $pdo->prepare("
            UPDATE hotel_rooms
            SET room_name = :room_name, room_type_id = :room_type_id, room_description = :room_description,
                room_price = :room_price, room_image = :room_image
            WHERE room_id = :room_id
        ");
        $stmt->execute([
            ':room_name' => $roomName,
            ':room_type_id' => $roomTypeId,
            ':room_description' => $roomDescription,
            ':room_price' => $roomPrice,
            ':room_image' => $roomImage,
            ':room_id' => $roomId,
        ]);
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
</head>
<body class="bg-gray-100">

<header class="w-full bg-gray-100 py-6 shadow-md">
    <h1 class="text-3xl font-bold text-center">Admin Dashboard</h1>
</header>

<main class="w-full max-w-4xl mx-auto mt-8 px-4">

    <div class="absolute top-6 left-4">
            <a href="javascript:history.back()" class="text-gray-700 font-bold text-lg px-4 py-2 rounded-lg hover:text-blue-600">
                &lt; Back
            </a>
    </div>

    <!-- ส่วนเพิ่มข้อมูลโรงแรม -->
    <div class="bg-white shadow-md rounded-lg p-6 mb-8">
        <h2 class="text-xl font-bold mb-4">เพิ่มข้อมูลโรงแรม</h2>
        <form method="POST">
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
            </div>
            <div class="mt-4 flex justify-end">
                <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded-lg">เพิ่มโรงแรม</button>
            </div>
        </form>
    </div>

    <!-- ส่วนเพิ่มข้อมูลห้อง -->
    <div class="bg-white shadow-md rounded-lg p-6 mb-8">
        <h2 class="text-xl font-bold mb-4">เพิ่มข้อมูลห้อง</h2>
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="action" value="add_room">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="hotel" class="block text-gray-700">โรงแรม:</label>
                    <select id="hotel" name="hotel_id" class="w-full border px-4 py-2 rounded-lg">
                        <?php foreach ($hotels as $hotel): ?>
                            <option value="<?= $hotel['hotel_id'] ?>"><?= $hotel['hotel_name'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label for="roomType" class="block text-gray-700">ประเภทห้อง:</label>
                    <select id="roomType" name="room_type_id" class="w-full border px-4 py-2 rounded-lg">
                        <?php foreach ($roomTypes as $roomType): ?>
                            <option value="<?= $roomType['room_type_id'] ?>"><?= $roomType['room_type_name'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label for="roomName" class="block text-gray-700">ชื่อห้อง:</label>
                    <input type="text" id="roomName" name="room_name" class="w-full border px-4 py-2 rounded-lg" required>
                </div>
                <div>
                    <label for="roomDescription" class="block text-gray-700">รายละเอียด:</label>
                    <textarea id="roomDescription" name="room_description" class="w-full border px-4 py-2 rounded-lg" required></textarea>
                </div>
                <div>
                    <label for="roomPrice" class="block text-gray-700">ราคา:</label>
                    <input type="number" id="roomPrice" name="room_price" class="w-full border px-4 py-2 rounded-lg" required>
                </div>
                <div>
                    <label for="roomImage" class="block text-gray-700">รูปภาพห้อง:</label>
                    <input type="file" id="roomImage" name="room_image" class="w-full border px-4 py-2 rounded-lg" accept="image/*" required>
                </div>
            </div>
            <div class="mt-4 flex justify-end">
                <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded-lg">เพิ่มห้อง</button>
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
                        <a href="view_hotel.php?id=<?= $hotel['hotel_id'] ?>" class="bg-blue-600 text-white px-4 py-2 rounded-lg">
                            View
                        </a>
                        <!-- ปุ่มแก้ไข -->
                        <button type="button" class="bg-green-600 text-white px-4 py-2 rounded-lg edit-button"
                            data-id="<?= $hotel['hotel_id'] ?>"
                            data-name="<?= htmlspecialchars($hotel['hotel_name']) ?>"
                            data-province-id="<?= $hotel['province_name'] ?>">
                            Edit
                        </button>
                        <!-- ปุ่มลบ -->
                        <form method="POST" class="inline-block delete-form">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="hotel_id" value="<?= $hotel['hotel_id'] ?>">
                            <button type="button" class="bg-red-600 text-white px-4 py-2 rounded-lg delete-button">
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

<div id="editModal" class="hidden fixed inset-0 bg-gray-800 bg-opacity-50 flex justify-center items-center">
    <div class="bg-white shadow-lg rounded-lg w-full max-w-md p-6">
        <h3 class="text-xl font-bold mb-4">แก้ไขข้อมูลโรงแรม</h3>
        <form method="POST">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="hotel_id" id="editHotelId">
            <div class="mb-4">
                <label for="editHotelName" class="block text-gray-700">ชื่อโรงแรม</label>
                <input type="text" id="editHotelName" name="hotel_name" class="w-full border px-4 py-2 rounded-lg" required>
            </div>
            <div class="mb-4">
                <label for="editProvince" class="block text-gray-700">จังหวัด</label>
                <select id="editProvince" name="province_id" class="w-full border px-4 py-2 rounded-lg">
                    <?php foreach ($provinces as $province): ?>
                        <option value="<?= $province['province_id'] ?>"><?= $province['province_name'] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="flex justify-end gap-2">
                <button type="button" id="cancelEdit" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700">ยกเลิก</button>
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-red-700">ยืนยัน</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal สำหรับแก้ไขข้อมูลห้องพัก -->
<div id="editRoomModal" class="hidden fixed inset-0 bg-gray-800 bg-opacity-50 flex justify-center items-center">
    <div class="bg-white shadow-lg rounded-lg w-full max-w-md p-6">
        <h3 class="text-xl font-bold mb-4">แก้ไขข้อมูลห้องพัก</h3>
        <form method="POST">
            <input type="hidden" name="action" value="edit_room">
            <input type="hidden" name="room_id" id="editRoomId">
            <div class="mb-4">
                <label for="editRoomName" class="block text-gray-700">ชื่อห้อง:</label>
                <input type="text" id="editRoomName" name="room_name" class="w-full border px-4 py-2 rounded-lg" required>
            </div>
            <div class="mb-4">
                <label for="editRoomType" class="block text-gray-700">ประเภทห้อง:</label>
                <select id="editRoomType" name="room_type_id" class="w-full border px-4 py-2 rounded-lg">
                    <?php foreach ($roomTypes as $roomType): ?>
                        <option value="<?= $roomType['room_type_id'] ?>"><?= $roomType['room_type_name'] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-4">
                <label for="editRoomDescription" class="block text-gray-700">รายละเอียด:</label>
                <textarea id="editRoomDescription" name="room_description" class="w-full border px-4 py-2 rounded-lg"></textarea>
            </div>
            <div class="mb-4">
                <label for="editRoomPrice" class="block text-gray-700">ราคา:</label>
                <input type="number" id="editRoomPrice" name="room_price" class="w-full border px-4 py-2 rounded-lg" required>
            </div>
            <div class="flex justify-end gap-2">
                <button type="button" id="cancelEditRoom" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700">ยกเลิก</button>
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">บันทึก</button>
            </div>
        </form>
    </div>
</div>

<script>
    // เปิด Modal สำหรับแก้ไขโรงแรม
    const editModal = document.getElementById('editModal');
    const editButtons = document.querySelectorAll('.edit-button');
    const cancelEdit = document.getElementById('cancelEdit');
    editButtons.forEach(button => {
        button.addEventListener('click', () => {
            document.getElementById('editHotelId').value = button.getAttribute('data-id');
            document.getElementById('editHotelName').value = button.getAttribute('data-name');
            document.getElementById('editProvince').value = button.getAttribute('data-province-id');
            editModal.classList.remove('hidden');
        });
    });
    cancelEdit.addEventListener('click', () => {
        editModal.classList.add('hidden');
    });

    // เปิด Modal สำหรับแก้ไขห้องพัก
    const editRoomModal = document.getElementById('editRoomModal');
    const editRoomButtons = document.querySelectorAll('.edit-room-button');
    const cancelEditRoom = document.getElementById('cancelEditRoom');
    editRoomButtons.forEach(button => {
        button.addEventListener('click', () => {
            document.getElementById('editRoomId').value = button.getAttribute('data-id');
            document.getElementById('editRoomName').value = button.getAttribute('data-name');
            document.getElementById('editRoomType').value = button.getAttribute('data-type');
            document.getElementById('editRoomDescription').value = button.getAttribute('data-description');
            document.getElementById('editRoomPrice').value = button.getAttribute('data-price');
            editRoomModal.classList.remove('hidden');
        });
    });
    cancelEditRoom.addEventListener('click', () => {
        editRoomModal.classList.add('hidden');
    });

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

    // เปิด Modal สำหรับแก้ไขข้อมูลห้องพัก
    document.querySelectorAll('.edit-room-button').forEach(button => {
    button.addEventListener('click', function () {
        const modal = document.getElementById('editRoomModal');
        const roomId = button.getAttribute('data-id');
        const roomName = button.getAttribute('data-name');
        const roomType = button.getAttribute('data-type');
        const roomDescription = button.getAttribute('data-description');
        const roomPrice = button.getAttribute('data-price');

        document.getElementById('editRoomId').value = roomId;
        document.getElementById('editRoomName').value = roomName;
        document.getElementById('editRoomType').value = roomType;
        document.getElementById('editRoomDescription').value = roomDescription;
        document.getElementById('editRoomPrice').value = roomPrice;

        modal.classList.remove('hidden');
    });
    });

    // ฟังก์ชันยกเลิกการแก้ไขห้องพัก
    cancelEditRoom.addEventListener('click', () => {
        editRoomModal.classList.add('hidden');
    });

</script>
</body>
</html>