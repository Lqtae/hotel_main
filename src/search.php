<?php
require 'db.php';

$query = isset($_GET['query']) ? $_GET['query'] : '';
$region = isset($_GET['region']) ? $_GET['region'] : '';
$province = isset($_GET['province']) ? $_GET['province'] : '';

$sql = "SELECT hotels.hotel_id, hotels.hotel_name, hotels.address, provinces.province_name, regions.region_name
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

$stmt = $pdo->prepare($sql);
$stmt->execute($params);

$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

header('Content-Type: application/json');
echo json_encode($results);
?>