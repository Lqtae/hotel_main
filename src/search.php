<?php //search.php
require 'db.php';

header('Content-Type: application/json');

$query = $_GET['query'] ?? '';
$region = $_GET['region'] ?? '';
$province = $_GET['province'] ?? '';

// เพิ่มการ debug สำหรับตรวจสอบค่าที่ได้รับ
error_log('Query: ' . $query);
error_log('Region: ' . $region);
error_log('Province: ' . $province);

$sql = "SELECT hotels.hotel_id, hotels.hotel_name, hotels.address, provinces.province_name, regions.region_name
        FROM hotels
        INNER JOIN provinces ON hotels.province_id = provinces.province_id
        INNER JOIN regions ON provinces.region_id = regions.region_id
        WHERE hotels.hotel_name LIKE :query OR provinces.province_name LIKE :query";

$params = ['query' => "%$query%"];

if ($region) {
    $sql .= " AND regions.region_name = :region";
    $params['region'] = $region;
}

if ($province) {
    $sql .= " AND provinces.province_name = :province";
    $params['province'] = $province;
}

// เพิ่มการ debug SQL query
error_log('SQL: ' . $sql);

$stmt = $pdo->prepare($sql);
$stmt->execute($params);

$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ส่งผลลัพธ์กลับไปเป็น JSON
echo json_encode($data);
?>
