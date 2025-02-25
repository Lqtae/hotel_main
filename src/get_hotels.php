<?php
require 'db.php';

$province = isset($_GET['province']) ? $_GET['province'] : '';

$sql = "SELECT h.*, p.province_name, h.star_rating as avg_rating 
        FROM hotels h
        LEFT JOIN provinces p ON h.province_id = p.province_id";

if (!empty($province)) {
    $sql .= " WHERE p.province_name = :province";
}

$sql .= " ORDER BY h.star_rating DESC LIMIT 8";

$stmt = $pdo->prepare($sql);

if (!empty($province)) {
    $stmt->bindParam(':province', $province, PDO::PARAM_STR);
}

$stmt->execute();
$hotels = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($hotels as $hotel):
    $hotelImageQuery = $pdo->prepare("SELECT image_path FROM hotel_images WHERE hotel_id = ? LIMIT 1");
    $hotelImageQuery->execute([$hotel['hotel_id']]);
    $hotelImage = $hotelImageQuery->fetchColumn();
?>
    <div class="swiper-slide">
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <a href="hotel_detail.php?id=<?= $hotel['hotel_id']; ?>">
                <img src="<?= $hotelImage ? htmlspecialchars($hotelImage) : 'img/default-hotel.jpg'; ?>" class="w-full h-52 object-cover">
            </a>
            <div class="p-4">
                <div class="flex justify-between items-center">
                    <h3 class="text-lg font-semibold"><?= htmlspecialchars($hotel['hotel_name']); ?></h3>
                    <span class="bg-blue-500 text-white px-2 py-1 rounded-lg text-sm font-bold">
                        <?= number_format($hotel['avg_rating'], 1); ?>
                    </span>
                </div>
                <p class="text-sm text-gray-500">📍 <?= htmlspecialchars($hotel['province_name']); ?></p>
                <p class="text-red-500 font-semibold">THB <?= number_format($hotel['price_per_night'], 2); ?></p>
            </div>
        </div>
    </div>
<?php endforeach; ?>
