<?php
// เชื่อมต่อฐานข้อมูล
require 'db.php';

// ดึงข้อมูลโรงแรมทั้งหมด
$hotels = $pdo->query("
    SELECT hotels.hotel_id, hotels.hotel_name, hotels.address, provinces.province_name
    FROM hotels
    LEFT JOIN provinces ON hotels.province_id = provinces.province_id
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
        // เพิ่มห้อง
        $hotelId = $_POST['hotel_id'];
        $roomTypeId = $_POST['room_type_id'];
        $roomName = $_POST['room_name'];
        $roomDescription = $_POST['room_description'];
        $roomPrice = $_POST['room_price'];

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
        header('Location: admin_dashboard.php');
        exit;
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
        <form method="POST">
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
            </div>
            <div class="mt-4 flex justify-end">
                <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded-lg">เพิ่มห้อง</button>
            </div>
        </form>
    </div>

    <!-- ตารางรายการโรงแรม -->
    <div class="bg-white shadow-md rounded-lg p-6 ">
        <h2 class="text-xl font-bold mb-4">รายการโรงแรม</h2>
        <table class="table-auto w-full border-collapse">
            <thead>
                <tr>
                    <th class="border px-4 py-2">#</th>
                    <th class="border px-4 py-2">ชื่อโรงแรม</th>
                    <th class="border px-4 py-2">ที่อยู่</th>
                    <th class="border px-4 py-2">จังหวัด</th>
                    <th class="border px-4 py-2">การจัดการ</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($hotels as $index => $hotel): ?>
                <tr>
                    <td class="border px-4 py-2 text-center"><?= $index + 1 ?></td>
                    <td class="border px-4 py-2"><?= htmlspecialchars($hotel['hotel_name']) ?></td>
                    <td class="border px-4 py-2"><?= htmlspecialchars($hotel['address']) ?></td>
                    <td class="border px-4 py-2"><?= htmlspecialchars($hotel['province_name']) ?></td>
                    <td class="border px-4 py-2 text-center flex gap-2 justify-center">
                        <!-- ปุ่มแก้ไข -->
                        <button type="button" class="bg-blue-600 text-white px-4 py-2 rounded-lg edit-button W-20" 
                            data-id="<?= $hotel['hotel_id'] ?>" 
                            data-name="<?= htmlspecialchars($hotel['hotel_name']) ?>" 
                            data-address="<?= htmlspecialchars($hotel['address']) ?>" 
                            data-province-id="<?= $hotel['province_name'] ?>">Edit</button>

                        <!-- ปุ่มลบ -->
                        <form method="POST" class="inline-block delete-form">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="hotel_id" value="<?= $hotel['hotel_id'] ?>">
                            <button type="button" class="bg-red-600 text-white px-4 py-2 rounded-lg delete-button W-20" data-id="<?= $hotel['hotel_id'] ?>">Delete</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</main>

<footer class="bg-white py-4 text-center text-black mt-8 shadow-md">
    <p>&copy; 2025 Admin Dashboard</p>
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
                <label for="editAddress" class="block text-gray-700">ที่อยู่</label>
                <input type="text" id="editAddress" name="address" class="w-full border px-4 py-2 rounded-lg" required>
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


<script>
    // เปิด Modal สำหรับแก้ไข
    document.querySelectorAll('.edit-button').forEach(button => {
        button.addEventListener('click', function () {
            const modal = document.getElementById('editModal');
            document.getElementById('editHotelId').value = this.getAttribute('data-id');
            document.getElementById('editHotelName').value = this.getAttribute('data-name');
            document.getElementById('editAddress').value = this.getAttribute('data-address');
            modal.classList.remove('hidden');
        });
    });

    // ปิด Modal
    document.getElementById('cancelEdit').addEventListener('click', function () {
        document.getElementById('editModal').classList.add('hidden');   
    });

    // เปิด Modal สำหรับยืนยันการลบ
    document.querySelectorAll('.delete-button').forEach(button => {
        button.addEventListener('click', function () {
            const modal = document.getElementById('deleteModal');
            document.getElementById('deleteHotelId').value = this.getAttribute('data-id');
            modal.classList.remove('hidden');
        });
    });

    // ปิด Modal ยืนยันการลบ
    document.getElementById('cancelDelete').addEventListener('click', function () {
        document.getElementById('deleteModal').classList.add('hidden');
    });
</script>
</body>
</html>

