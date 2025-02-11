<?php
include 'db.php';
include 'functions.php';

$id = $_GET['id'] ?? null;

if (!$id) {
    echo "ไม่มี ID โรงแรมที่ถูกส่งมา";
    exit;
}

// ดึงข้อมูลโรงแรม
$hotelDetails = getHotelDetailsById($id);
if (!$hotelDetails) {
    echo "ไม่พบข้อมูลโรงแรมนี้";
    exit;
}

// ดึงข้อมูลห้องพัก
$roomDetails = getRoomsByHotelId($id);
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>รายละเอียดโรงแรม</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="icon" href="./img/icon.png">
</head>
<body class="bg-gray-100 flex flex-col min-h-screen">

<header class="w-full bg-gray-100 py-6 shadow-md">
    <h1 class="text-black text-3xl font-bold text-center">รายละเอียดโรงแรม</h1>
</header>

<main class="w-full max-w-4xl mx-auto mt-8 px-4 flex-grow">

    <div class="absolute top-6 left-4">
        <a href="javascript:history.back()" class="text-gray-700 font-bold text-lg px-4 py-2 rounded-lg hover:text-blue-600">
            &lt; Back
        </a>
    </div>

    <!-- แสดงรูปภาพโรงแรม -->
    <?php if (!empty($hotelDetails['images'])): ?>
        <div class="mb-6">
        <?php foreach ($hotelDetails['images'] as $image): ?>
            <img src="<?= htmlspecialchars($image['image_path']) ?>" alt="Hotel Image" class="w-full h-96 object-cover rounded-lg shadow-md">
        <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="w-full h-64 bg-gray-200 flex items-center justify-center text-gray-500 rounded-lg shadow-md">
            ไม่มีรูปภาพโรงแรม
        </div>
    <?php endif; ?>

    <div class="max-w-4xl mx-auto mt-8 p-6 bg-white shadow-md rounded-lg">
        <h2 class="text-xl font-semibold"><?= htmlspecialchars($hotelDetails['hotel_name'] ?? 'ไม่มีชื่อโรงแรม') ?></h2>
        <p class="text-gray-700">ที่อยู่: <?= htmlspecialchars($hotelDetails['address'] ?? 'ไม่มีที่อยู่') ?></p>
        <p class="text-gray-700">จังหวัด: <?= htmlspecialchars($hotelDetails['province_name'] ?? 'ไม่มีจังหวัด') ?></p>
        
    <div class="mt-8">
        <h3 class="text-2xl font-bold mb-4">ห้องพัก</h3>
        <?php if (!empty($roomDetails)): ?>
            <div class="grid grid-cols-1 sm:grid-cols-2  gap-6">
                <?php foreach ($roomDetails as $room): ?>
                    <a href="room_detail.php?id=<?= $room['hotel_room_id'] ?>" class="hover:shadow-2xl transition-shadow duration-300">
                    <div class="bg-white shadow-md rounded-lg overflow-hidden">
                        <?php 
                        $roomImages = getRoomImagesByRoomId($room['hotel_room_id']);
                        if (!empty($roomImages)): 
                        ?>
                            <div class="relative">
                                <img src="<?= htmlspecialchars($roomImages[0]['image_path']) ?>" alt="<?= htmlspecialchars($room['room_name']) ?>" class="w-full h-48 object-cover">
                            </div>
                        <?php else: ?>
                            <div class="w-full h-48 bg-gray-200 flex items-center justify-center text-gray-500">
                                ไม่มีรูปภาพ
                            </div>
                        <?php endif; ?>
                        <div class="p-4">
                            <h4 class="text-lg font-semibold"><?= htmlspecialchars($room['room_name']) ?></h4>
                            <p class="text-gray-700"><?= htmlspecialchars($room['room_description']) ?></p>
                            <p class="text-gray-900 font-bold mt-2">ราคา: <?= number_format($room['room_price'], 2) ?> บาท/คืน</p>
                        </div>
                    </div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="text-gray-700">ไม่มีข้อมูลห้องพัก</p>
        <?php endif; ?>
    </div>
    </div>
</main>

<footer class="w-full bg-white py-4 mt-8 shadow-md">
    <p class="text-black text-center text-sm">
        &copy; 2025 <a href="index.php" class="text-black hover:font-semibold">Where's Hotel</a>
    </p>
</footer>

</body>
</html>