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

function getRoomsByHotelId($hotelId) {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT 
            hr.hotel_room_id,
            rt.room_type_name,
            rt.description,
            hr.price_per_night,
            ri.image_path
        FROM hotel_rooms hr
        JOIN room_types rt ON hr.room_type_id = rt.room_type_id
        LEFT JOIN room_images ri ON hr.hotel_room_id = ri.hotel_room_id
        WHERE hr.hotel_id = :hotel_id
    ");
    $stmt->bindParam(':hotel_id', $hotelId, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}


?>
