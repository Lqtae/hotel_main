<?php //hotel_detail.php
include 'db.php';  
include 'functions.php'; 

$id = $_GET['id'] ?? null;

if ($id) {
    $hotelDetails = getHotelDetailsById($id);
    if (!$hotelDetails) {
        echo "ไม่พบข้อมูลโรงแรมนี้";
        exit;
    }
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
<body class="bg-gray-100">

<header class="w-full bg-gray-100 py-6 shadow-md">
    <h1 class="text-black text-3xl font-bold text-center">Hotel Details</h1>
</header>

<main class="w-full max-w-4xl mx-auto mt-8 px-4">
    <div class="bg-white shadow-md rounded-lg p-6">
        <h2 class="text-xl font-semibold"><?= $hotelDetails['hotel_name'] ?? 'ไม่มีชื่อโรงแรม' ?></h2>
        <p class="text-gray-700">ที่อยู่: <?= $hotelDetails['address'] ?? 'ไม่มีที่อยู่' ?></p>
        <p class="text-gray-700">จังหวัด: <?= $hotelDetails['province_name'] ?? 'ไม่มีจังหวัด' ?></p>
        <p class="text-gray-700">ภาค: <?= $hotelDetails['region_name'] ?? 'ไม่มีภาค' ?></p>
        <p class="text-gray-700">รายละเอียด: <?= $hotelDetails['description'] ?? 'ไม่มีรายละเอียด' ?></p>
    </div>
</main>

</body>
</html>
