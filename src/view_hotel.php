<?php 
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
    SELECT room_name, room_description, room_price 
    FROM hotel_rooms
    WHERE hotel_id = :hotel_id
");
$stmt->execute([':hotel_id' => $hotel_id]);
$rooms = $stmt->fetchAll(PDO::FETCH_ASSOC);
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

            <h2 class="text-xl font-bold mt-6 mb-2">ห้องพัก</h2>
            <table class="w-full border-collapse border border-gray-300">
                <thead>
                    <tr class="bg-gray-200">
                        <th class="border px-4 py-2">ชื่อห้อง</th>
                        <th class="border px-4 py-2">รายละเอียด</th>
                        <th class="border px-4 py-2">ราคา</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rooms as $room): ?>
                        <tr class="hover:bg-gray-100">
                            <td class="border px-4 py-2"><?= htmlspecialchars($room['room_name']) ?></td>
                            <td class="border px-4 py-2"><?= htmlspecialchars($room['room_description']) ?></td>
                            <td class="border px-4 py-2"><?= number_format($room['room_price'], 2) ?> บาท</td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($rooms)): ?>
                        <tr>
                            <td colspan="3" class="border px-4 py-2 text-center text-gray-500">ไม่มีข้อมูลห้องพัก</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>

    <footer class="w-full bg-white py-4 shadow-md">
        <p class="text-black text-center text-sm">
            &copy; 2025 <a href="index.php" class="text-black hover:font-semibold">Where's Hotel</a>
        </p>
    </footer>

</body>
</html>