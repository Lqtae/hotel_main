<?php // room_detail.php
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
    <link rel="stylesheet" type="text/css" href="style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.css">
    <link rel="icon" href="./img/icon.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg==" crossorigin="anonymous" referrerpolicy="no-referrer" />

    <style>
        /* เอฟเฟกต์ขยายเมื่อ hover */
        .image-container img {
            transition: transform 0.3s ease-in-out;
        }
        .image-container img:hover {
            transform: scale(1.1);
        }
    </style>
</head>
<body class="bg-gray-100 flex flex-col min-h-screen">

<header class="w-full bg-gray-100 py-6 shadow-md">
    <h1 class="text-black text-3xl font-bold text-center">รายละเอียดห้องพัก</h1>
</header>

<main class="w-full max-w-4xl mx-auto mt-8 px-4 flex-grow">

    <div class="absolute top-6 left-4">
        <a href="javascript:history.back()" class="text-gray-700 font-bold text-lg px-4 py-2 rounded-lg hover:text-blue-600">
        <i class="fa-solid fa-chevron-left"></i>
        </a>
    </div>

    <div class="bg-white shadow-md rounded-lg p-6">
        <h2 class="text-xl font-semibold"><?= htmlspecialchars($roomDetails['room_name']) ?></h2>
        <p class="text-gray-700"><?= nl2br(htmlspecialchars($roomDetails['room_description'])) ?></p>
        <p class="text-gray-900 font-bold mt-2">ราคา: <?= number_format($roomDetails['room_price'], 2) ?> บาท/คืน</p>
    </div>

    <div class="bg-white shadow-md rounded-lg mt-6 p-6">
        <h3 class="text-2xl font-bold mb-4">รูปภาพห้องพัก</h3>
        <?php if (!empty($roomImages)): ?>
            <div class="grid grid-cols-3 gap-3">
                <?php foreach ($roomImages as $image): ?>
                    <div class="image-container">
                        <img src="<?= htmlspecialchars($image['image_path']) ?>" alt="Room Image" class="w-full h-64 object-cover rounded-lg shadow">
                    </div>
                <?php endforeach; ?>
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

</body>
</html>