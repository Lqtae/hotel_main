<?php 
include 'db.php';  
include 'functions.php'; 

$room_id = $_GET['id'] ?? null;

if ($room_id) {
    // ดึงข้อมูลห้องพัก
    $room = getRoomDetailsById($room_id);
    if (!$room) {
        echo "ไม่พบข้อมูลห้องพักนี้";
        exit;
    }
} else {
    echo "ไม่มี ID ห้องพักที่ถูกส่งมา";
    exit;
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($room['room_type_name']) ?> - รายละเอียดห้องพัก</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex flex-col min-h-screen">

<header class="w-full bg-gray-100 py-6 shadow-md">
    <h1 class="text-black text-3xl font-bold text-center">Room Details</h1>
</header>

<main class="w-full max-w-3xl mx-auto mt-8 px-4 flex-grow">
    <!-- ปุ่มย้อนกลับ -->
    <div class="absolute top-6 left-4">
        <a href="hotel_details.php?id=<?= $room['hotel_id'] ?>" class="text-gray-700 font-bold text-lg px-4 py-2 rounded-lg hover:text-blue-600">
            &lt; Back
        </a>
    </div>

    <!-- ข้อมูลห้องพัก -->
    <div class="bg-white shadow-md rounded-lg p-6">
        <!-- รูปภาพห้อง -->
        <?php if (!empty($room['image_url'])): ?>
            <img src="<?= htmlspecialchars($room['image_url']) ?>" alt="<?= htmlspecialchars($room['room_type_name']) ?>" class="w-full h-64 object-cover rounded-lg">
        <?php else: ?>
            <div class="w-full h-64 bg-gray-200 flex items-center justify-center text-gray-500 rounded-lg">
                ไม่มีรูปภาพ
            </div>
        <?php endif; ?>

        <h2 class="text-2xl font-semibold mt-4"><?= htmlspecialchars($room['room_type_name']) ?></h2>
        <p class="text-gray-700 mt-2"><?= htmlspecialchars($room['room_description']) ?></p>
        <p class="text-gray-900 font-bold mt-4">ราคา: <?= number_format($room['room_price'], 2) ?> บาท/คืน</p>

        <p class="text-gray-600 mt-4">โรงแรม: 
            <a href="hotel_details.php?id=<?= $room['hotel_id'] ?>" class="text-blue-500 hover:underline">
                <?= htmlspecialchars($room['hotel_name']) ?>
            </a>
        </p>
    </div>
</main>

<footer class="w-full bg-white py-4 mt-8 shadow-md">
    <p class="text-black text-center text-sm">
        &copy; 2025 <a href="admin_dashboard.php" class="text-black hover:font-semibold">Where's Hotel</a>
    </p>
</footer>

</body>
</html>