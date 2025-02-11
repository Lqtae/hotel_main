<?php
include 'db.php';
include 'functions.php';

$room_id = $_GET['id'] ?? null;

if (!$room_id) {
    echo "ไม่มี ID ห้องพักที่ถูกส่งมา";
    exit;
}

// ดึงข้อมูลห้องพัก
$roomDetails = getRoomDetailsById($room_id);
if (!$roomDetails) {
    echo "ไม่พบข้อมูลห้องพักนี้";
    exit;
}

// ดึงรูปภาพทั้งหมดของห้องพัก
$roomImages = getRoomImagesByRoomId($room_id);
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($roomDetails['room_name']) ?> - รายละเอียดห้องพัก</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.css">
    <link rel="icon" href="./img/icon.png">
</head>
<body class="bg-gray-100 flex flex-col min-h-screen">

<header class="w-full bg-gray-100 py-6 shadow-md">
    <h1 class="text-black text-3xl font-bold text-center">รายละเอียดห้องพัก</h1>
</header>

<main class="w-full max-w-4xl mx-auto mt-8 px-4 flex-grow">

    <div class="absolute top-6 left-4">
        <a href="javascript:history.back()" class="text-gray-700 font-bold text-lg px-4 py-2 rounded-lg hover:text-blue-600">
            &lt; Back
        </a>
    </div>

    <div class="bg-white shadow-md rounded-lg p-6">
        <h2 class="text-xl font-semibold"><?= htmlspecialchars($roomDetails['room_name']) ?></h2>
        <p class="text-gray-700"><?= htmlspecialchars($roomDetails['room_description']) ?></p>
        <p class="text-gray-900 font-bold mt-2">ราคา: <?= number_format($roomDetails['room_price'], 2) ?> บาท/คืน</p>
    </div>

    <div class="mt-6">
        <h3 class="text-2xl font-bold mb-4">รูปภาพห้องพัก</h3>
        <?php if (!empty($roomImages)): ?>
            <div class="swiper mySwiper">
                <div class="swiper-wrapper">
                    <?php foreach ($roomImages as $image): ?>
                        <div class="swiper-slide">
                            <img src="<?= htmlspecialchars($image['image_path']) ?>" alt="Room Image" class="w-full h-64 object-cover rounded-lg shadow">
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="swiper-button-next"></div>
                <div class="swiper-button-prev"></div>
                <div class="swiper-pagination"></div>
            </div>
        <?php else: ?>
            <p class="text-gray-700">ไม่มีรูปภาพสำหรับห้องพักนี้</p>
        <?php endif; ?>
    </div>

</main>

<footer class="w-full bg-white py-4 mt-8 shadow-md">
    <p class="text-black text-center text-sm">
        &copy; 2025 <a href="index.php" class="text-black hover:font-semibold">Where's Hotel</a>
    </p>
</footer>

<script>
    var swiper = new Swiper(".mySwiper", {
        loop: true,
        navigation: {
            nextEl: ".swiper-button-next",
            prevEl: ".swiper-button-prev",
        },
        pagination: {
            el: ".swiper-pagination",
            clickable: true,
        },
    });
</script>

</body>
</html>
