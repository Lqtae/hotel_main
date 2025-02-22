<?php
require 'db.php';

$query = isset($_GET['query']) ? $_GET['query'] : '';
$region = isset($_GET['region']) ? $_GET['region'] : '';
$province = isset($_GET['province']) ? $_GET['province'] : '';

$sql = "SELECT hotels.hotel_id, hotels.hotel_name, hotels.address, 
               provinces.province_name, regions.region_name, hotels.star_rating,
               (SELECT image_path FROM hotel_images WHERE hotel_images.hotel_id = hotels.hotel_id LIMIT 1) AS image
        FROM hotels
        JOIN provinces ON hotels.province_id = provinces.province_id
        JOIN regions ON provinces.region_id = regions.region_id";

$conditions = [];
$params = [];

if (!empty($query)) {
    $conditions[] = "(hotels.hotel_name LIKE :query OR provinces.province_name LIKE :query)";
    $params[':query'] = "%$query%";
}

if (!empty($region)) {
    $conditions[] = "regions.region_id = :region";
    $params[':region'] = $region;
}

if (!empty($province)) {
    $conditions[] = "provinces.province_id = :province";
    $params[':province'] = $province;
}

if ($conditions) {
    $sql .= " WHERE " . implode(' AND ', $conditions);
}

$sql .= " ORDER BY hotels.star_rating DESC, hotels.hotel_name ASC LIMIT 50";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

header('Content-Type: application/json');
echo json_encode($results);
?>