<?php //functions.php
function getHotelDetailsById($hotelId) {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT h.*, p.province_name
        FROM hotels h
        LEFT JOIN provinces p ON h.province_id = p.province_id
        WHERE h.hotel_id = :hotel_id
    ");
    $stmt->execute([':hotel_id' => $hotelId]);
    $hotel = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($hotel) {
        // ดึงรูปภาพของโรงแรมจาก hotel_images
        $stmt = $pdo->prepare("SELECT image_path FROM hotel_images WHERE hotel_id = :hotel_id");
        $stmt->execute([':hotel_id' => $hotelId]);
        $hotel['images'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    return $hotel;
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

function uploadHotelImage($hotelId, $file) {
    global $pdo;
    
    if (!$hotelId || !is_numeric($hotelId)) {
        return ['success' => false, 'error' => 'Hotel ID ไม่ถูกต้อง'];
    }

    $uploadDir = '/hotel_main/src/img/hotel_img/';
    $fileName = basename($file['name']);
    $filePath = $uploadDir . $fileName;
    $allowedTypes = ['image/jpeg', 'image/png'];

    if (!in_array($file['type'], $allowedTypes)) {
        return ['success' => false, 'error' => 'ประเภทไฟล์ไม่รองรับ'];
    }

    if (move_uploaded_file($file['tmp_name'], $_SERVER['DOCUMENT_ROOT'] . $filePath)) {
        $stmt = $pdo->prepare("INSERT INTO hotel_images (hotel_id, image_path) VALUES (?, ?)");
        $stmt->execute([$hotelId, $filePath]);

        return ['success' => true, 'image_id' => $pdo->lastInsertId(), 'image_path' => $filePath];
    } else {
        return ['success' => false, 'error' => 'อัปโหลดล้มเหลว'];
    }
}

// ฟังก์ชันลบรูปภาพ
function deleteHotelImage($imageId) {
    global $pdo;

    $stmt = $pdo->prepare("SELECT image_path FROM hotel_images WHERE image_id = ?");
    $stmt->execute([$imageId]);
    $image = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($image) {
        unlink($_SERVER['DOCUMENT_ROOT'] . $image['image_path']);
        $stmt = $pdo->prepare("DELETE FROM hotel_images WHERE image_id = ?");
        $stmt->execute([$imageId]);
        return ['success' => true];
    } else {
        return ['success' => false, 'error' => 'ไม่พบรูปภาพ'];
    }
}
?>