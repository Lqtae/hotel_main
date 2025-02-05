<?php //view_hotel.php
require 'db.php';

if (!isset($_GET['id'])) {
    die("ไม่พบข้อมูลโรงแรม");
}

$hotel_id = $_GET['id'];

$stmt = $pdo->prepare("
    SELECT hotels.hotel_name, hotels.address, provinces.province_name
    FROM hotels
    LEFT JOIN provinces ON hotels.province_id = provinces.province_id
    WHERE hotels.hotel_id = :hotel_id
");
$stmt->execute([':hotel_id' => $hotel_id]);
$hotel = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$hotel) {
    die("ไม่พบข้อมูลโรงแรม");
}

$stmt = $pdo->prepare("
    SELECT hr.hotel_room_id, hr.room_name, hr.room_description, hr.room_price 
    FROM hotel_rooms hr
    WHERE hr.hotel_id = :hotel_id
");
$stmt->execute([':hotel_id' => $hotel_id]);
$rooms = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ดึงรูปภาพของแต่ละห้อง
$room_images = [];
foreach ($rooms as $room) {
    $stmt = $pdo->prepare("
        SELECT image_path FROM room_images WHERE hotel_room_id = :hotel_room_id
    ");
    $stmt->execute([':hotel_room_id' => $room['hotel_room_id']]);
    $room_images[$room['hotel_room_id']] = $stmt->fetchAll(PDO::FETCH_COLUMN);
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($hotel['hotel_name']) ?> - ข้อมูลโรงแรม</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex flex-col">

    <header class="w-full bg-gray-100 py-6 shadow-md">
        <h1 class="text-3xl font-bold text-center">View</h1>
    </header>

    <main class="flex-grow">
        <div class="absolute top-6 left-4">
            <a href="javascript:history.back()" class="text-gray-700 font-bold text-lg px-4 py-2 rounded-lg hover:text-blue-600">
                &lt; Back
            </a>
        </div>

        <div class="max-w-4xl mx-auto mt-8 p-6 bg-white shadow-md rounded-lg">
            <h1 class="text-2xl font-bold mb-4"><?= htmlspecialchars($hotel['hotel_name']) ?></h1>
            <p><strong>ที่อยู่:</strong> <?= htmlspecialchars($hotel['address']) ?></p>
            <p><strong>จังหวัด:</strong> <?= htmlspecialchars($hotel['province_name']) ?></p>

            <h2 class="text-xl font-bold mt-6 mb-4">ห้องพัก</h2>

            <?php if (!empty($rooms)): ?>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <?php foreach ($rooms as $room): ?>
                        <a href="edit_room.php?id=<?= $room['hotel_room_id'] ?>" class="block bg-white shadow-lg rounded-lg overflow-hidden hover:shadow-2xl transition-shadow duration-300">
                            <?php if (!empty($room_images[$room['hotel_room_id']])): ?>
                                <img src="<?= htmlspecialchars($room_images[$room['hotel_room_id']][0]) ?>" alt="Room Image" class="w-full h-40 object-cover">
                            <?php else: ?>
                                <div class="w-full h-40 bg-gray-200 flex items-center justify-center">
                                    <span class="text-gray-500">ไม่มีรูปภาพ</span>
                                </div>
                            <?php endif; ?>

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

    <footer class="w-full bg-white py-4 shadow-md">
        <p class="text-black text-center text-sm">
            &copy; 2025 <a href="index.php" class="text-black hover:font-semibold">Where's Hotel</a>
        </p>
    </footer>

</body>
</html>