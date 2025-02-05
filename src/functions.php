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
            hr.room_name,
            hr.room_description,
            hr.room_price,
            GROUP_CONCAT(ri.image_path SEPARATOR ',') AS image_paths
        FROM hotel_rooms hr
        JOIN room_types rt ON hr.room_type_id = rt.room_type_id
        LEFT JOIN room_images ri ON hr.hotel_room_id = ri.hotel_room_id
        WHERE hr.hotel_id = :hotel_id
        GROUP BY hr.hotel_room_id
    ");
    $stmt->bindParam(':hotel_id', $hotelId, PDO::PARAM_INT);
    $stmt->execute();
    $rooms = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // แปลง image_paths ให้เป็นอาร์เรย์
    foreach ($rooms as &$room) {
        $room['image_paths'] = $room['image_paths'] ? explode(',', $room['image_paths']) : [];
    }

    return $rooms;
}


function getRoomImagesByRoomId($roomId) {
    global $pdo; 
    $stmt = $pdo->prepare("SELECT image_path FROM room_images WHERE hotel_room_id = :room_id");
    $stmt->execute(['room_id' => $roomId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getRoomDetailsById($room_id) {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT 
            r.hotel_room_id,
            r.room_name,
            r.room_description,
            r.room_price,
            h.hotel_id, 
            h.hotel_name,
            GROUP_CONCAT(ri.image_path SEPARATOR ',') AS image_paths
        FROM hotel_rooms r
        JOIN hotels h ON r.hotel_id = h.hotel_id
        LEFT JOIN room_images ri ON r.hotel_room_id = ri.hotel_room_id
        WHERE r.hotel_room_id = :room_id
        GROUP BY r.hotel_room_id
    ");
    $stmt->execute([':room_id' => $room_id]);
    $room = $stmt->fetch(PDO::FETCH_ASSOC);

    // แปลง image_paths เป็นอาร์เรย์
    if ($room) {
        $room['image_paths'] = $room['image_paths'] ? explode(',', $room['image_paths']) : [];
    }

    return $room;
}
?>
