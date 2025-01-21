<?php //functions.php
function getHotelDetailsById($id) {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT hotels.hotel_name, hotels.address, provinces.province_name, regions.region_name
        FROM hotels
        LEFT JOIN provinces ON hotels.province_id = provinces.province_id
        LEFT JOIN regions ON provinces.region_id = regions.region_id
        WHERE hotels.hotel_id = :id
    ");
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
}
?>
