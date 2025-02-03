<?php 
include 'db.php';  
include 'functions.php'; 

$id = $_GET['id'] ?? null;

if ($id) {
    // ดึงข้อมูลโรงแรม
    $hotelDetails = getHotelDetailsById($id);
    if (!$hotelDetails) {
        echo "ไม่พบข้อมูลโรงแรมนี้";
        exit;
    }

    // ดึงข้อมูลประเภทห้องพัก
    $roomDetails = getRoomsByHotelId($id);
} else {
    echo "ไม่มี ID โรงแรมที่ถูกส่งมา";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hotel Details</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex flex-col min-h-screen">
<header class="w-full bg-gray-100 py-6 shadow-md">
    <h1 class="text-black text-3xl font-bold text-center">Hotel Details</h1>
</header>

<main class="w-full max-w-4xl mx-auto mt-8 px-4 flex-grow">

    <div class="absolute top-6 left-4">
        <a href="javascript:history.back()" class="text-gray-700 font-bold text-lg px-4 py-2 rounded-lg hover:text-blue-600">
            &lt; Back
        </a>
    </div>

    <div class="bg-white shadow-md rounded-lg p-6">
        <h2 class="text-xl font-semibold"><?= $hotelDetails['hotel_name'] ?? 'ไม่มีชื่อโรงแรม' ?></h2>
        <p class="text-gray-700">ที่อยู่: <?= $hotelDetails['address'] ?? 'ไม่มีที่อยู่' ?></p>
        <p class="text-gray-700">จังหวัด: <?= $hotelDetails['province_name'] ?? 'ไม่มีจังหวัด' ?></p>
        <p class="text-gray-700">ภาค: <?= $hotelDetails['region_name'] ?? 'ไม่มีภาค' ?></p>
        <p class="text-gray-700">รายละเอียด: <?= $hotelDetails['description'] ?? 'ไม่มีรายละเอียด' ?></p>
    </div>

    <div class="mt-8">
        <h3 class="text-2xl font-bold mb-4">ประเภทห้องพัก</h3>
        <?php if (!empty($roomDetails)): ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($roomDetails as $room): ?>
                    <div class="bg-white shadow-md rounded-lg overflow-hidden">
                        <?php 
                        $roomImages = getRoomImagesByRoomId($room['hotel_room_id']);
                        if (!empty($roomImages)): 
                        ?>
                            <div class="relative">
                                <img src="<?= $roomImages[0]['image_path'] ?>" alt="<?= $room['room_name'] ?>" class="w-full h-48 object-cover">
                            </div>
                        <?php else: ?>
                            <div class="w-full h-48 bg-gray-200 flex items-center justify-center text-gray-500">
                                ไม่มีรูปภาพ
                            </div>
                        <?php endif; ?>
                        <div class="p-4">
                            <h4 class="text-lg font-semibold"><?= $room['room_name'] ?></h4>
                            <p class="text-gray-700"><?= $room['room_description'] ?></p>
                            <p class="text-gray-900 font-bold mt-2">ราคา: <?= number_format($room['room_price'], 2) ?> บาท/คืน</p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="text-gray-700">ไม่มีข้อมูลประเภทห้องพัก</p>
        <?php endif; ?>
    </div>
</main>

<footer class="w-full bg-white py-4 mt-8 shadow-md">
    <p class="text-black text-center text-sm">
        &copy; 2025 <a href="admin_dashboard.php" class="text-black hover:font-semibold">Where's Hotel</a>
    </p>
</footer>
</body>
</html>