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

function getPrimaryRoomImage($pdo, $hotel_room_id) {
    // ค้นหารูปหลักของห้องพักที่มี is_primary = 1
    $stmt = $pdo->prepare("
        SELECT image_path FROM room_images 
        WHERE hotel_room_id = :hotel_room_id AND is_primary = 1
        LIMIT 1
    ");
    $stmt->execute([':hotel_room_id' => $hotel_room_id]);
    $primaryImage = $stmt->fetchColumn();

    // ถ้าไม่มีรูปหลัก ให้ดึงรูปแรกของห้องพักแทน
    if (!$primaryImage) {
        $stmt = $pdo->prepare("
            SELECT image_path FROM room_images 
            WHERE hotel_room_id = :hotel_room_id
            ORDER BY image_id ASC
            LIMIT 1
        ");
        $stmt->execute([':hotel_room_id' => $hotel_room_id]);
        $primaryImage = $stmt->fetchColumn();
    }

    // ถ้าไม่มีรูปภาพเลย ให้ใช้รูป `no_image.png`
    return $primaryImage ?: "/hotel_main/src/img/no_image.png";
}

function getAllHotels() {
    global $pdo;
    $stmt = $pdo->query("
        SELECT hotels.hotel_id, hotels.hotel_name, hotels.address, 
               provinces.province_name, regions.region_name,
               GROUP_CONCAT(hotel_rooms.room_name SEPARATOR ', ') AS room_names
        FROM hotels
        LEFT JOIN provinces ON hotels.province_id = provinces.province_id
        LEFT JOIN regions ON provinces.region_id = regions.region_id
        LEFT JOIN hotel_rooms ON hotels.hotel_id = hotel_rooms.hotel_id
        GROUP BY hotels.hotel_id
    ");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}


// ดึงข้อมูลจังหวัดทั้งหมด
function getAllProvinces() {
    global $pdo;
    return $pdo->query("SELECT * FROM provinces")->fetchAll(PDO::FETCH_ASSOC);
}

// ดึงข้อมูลประเภทห้องทั้งหมด
function getAllRoomTypes() {
    global $pdo;
    return $pdo->query("SELECT * FROM room_types")->fetchAll(PDO::FETCH_ASSOC);
}

// เพิ่มโรงแรม
function addHotel($hotelName, $address, $provinceId, $hotelImage) {
    global $pdo;
    
    $stmt = $pdo->prepare("
        INSERT INTO hotels (hotel_name, address, province_id)
        VALUES (:hotel_name, :address, :province_id)
    ");
    $stmt->execute([
        ':hotel_name' => $hotelName,
        ':address' => $address,
        ':province_id' => $provinceId,
    ]);

    $hotelId = $pdo->lastInsertId();

    if (!empty($hotelImage['name'])) {
        return uploadHotelImage($hotelId, $hotelImage);
    }

    return ['success' => true, 'hotel_id' => $hotelId];
}

// ลบโรงแรม
function deleteHotel($hotelId) {
    global $pdo;
    $stmt = $pdo->prepare("DELETE FROM hotels WHERE hotel_id = :hotel_id");
    $stmt->execute([':hotel_id' => $hotelId]);
    return ['success' => true];
}

function addRoom($hotel_id, $roomData, $fileData) {
    global $pdo;

    try {
        // เพิ่มข้อมูลห้องพักลงในฐานข้อมูล
        $stmt = $pdo->prepare("
            INSERT INTO hotel_rooms (hotel_id, room_type_id, room_name, room_description, room_price)
            VALUES (:hotel_id, :room_type_id, :room_name, :room_description, :room_price)
        ");
        $stmt->execute([
            ':hotel_id' => $hotel_id,
            ':room_type_id' => $roomData['room_type_id'],
            ':room_name' => $roomData['room_name'],
            ':room_description' => $roomData['room_description'],
            ':room_price' => $roomData['room_price'],
        ]);
        $roomId = $pdo->lastInsertId(); // ดึง ID ห้องที่เพิ่งเพิ่ม

        // ตรวจสอบว่ามีการอัปโหลดไฟล์หรือไม่
        if (!empty($fileData['room_images']['name'][0])) {
            $targetDir = $_SERVER['DOCUMENT_ROOT'] . "/hotel_main/src/img/room_img/";
            $uploadedImages = [];

            foreach ($fileData['room_images']['name'] as $key => $fileName) {
                $targetFilePath = $targetDir . basename($fileName);
                $imagePath = "/hotel_main/src/img/room_img/" . basename($fileName);

                // อัปโหลดไฟล์ไปยังเซิร์ฟเวอร์
                if (move_uploaded_file($fileData['room_images']['tmp_name'][$key], $targetFilePath)) {
                    $uploadedImages[] = $imagePath;
                }
            }

            // บันทึกเส้นทางไฟล์รูปภาพลงฐานข้อมูล
            foreach ($uploadedImages as $index => $imagePath) {
                $isPrimary = ($index == $roomData['primary_image_index']) ? 1 : 0;

                $stmt = $pdo->prepare("
                    INSERT INTO room_images (hotel_room_id, image_path, is_primary)
                    VALUES (:hotel_room_id, :image_path, :is_primary)
                ");
                $stmt->execute([
                    ':hotel_room_id' => $roomId,
                    ':image_path' => $imagePath,
                    ':is_primary' => $isPrimary
                ]);
            }
        }

        return ['success' => true, 'room_id' => $roomId];

    } catch (Exception $e) {
        return ['success' => false, 'error' => $e->getMessage()];
    }
}

// ฟังก์ชันดึงรูปภาพของห้องพัก
function getRoomImages($hotel_room_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT image_path FROM room_images WHERE hotel_room_id = :hotel_room_id");
    $stmt->execute([':hotel_room_id' => $hotel_room_id]);
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

// ฟังก์ชันลบไฟล์ภาพจากโฟลเดอร์
function deleteImageFile($image_path) {
    $full_path = __DIR__ . "/.." . $image_path;
    if (file_exists($full_path)) {
        unlink($full_path);
    }
}

// ฟังก์ชันลบรูปภาพจากฐานข้อมูลและไฟล์เซิร์ฟเวอร์
function deleteRoomImage($pdo, $room_id, $image_path) {
    deleteImageFile($image_path); // ลบไฟล์ภาพจริงจากเซิร์ฟเวอร์

    $stmt = $pdo->prepare("DELETE FROM room_images WHERE hotel_room_id = :room_id AND image_path = :image_path");
    return $stmt->execute([
        ':room_id' => $room_id,
        ':image_path' => $image_path
    ]);
}

// ฟังก์ชันลบห้องพักและรูปภาพทั้งหมดที่เกี่ยวข้อง
function deleteRoom($pdo, $room_id) {
    // ดึงรายการรูปภาพทั้งหมดของห้อง
    $stmt = $pdo->prepare("SELECT image_path FROM room_images WHERE hotel_room_id = :room_id");
    $stmt->execute([':room_id' => $room_id]);
    $images = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // ลบไฟล์ภาพทั้งหมด
    foreach ($images as $image_path) {
        deleteImageFile($image_path);
    }

    // ลบข้อมูลรูปภาพจากฐานข้อมูล
    $pdo->prepare("DELETE FROM room_images WHERE hotel_room_id = :room_id")->execute([':room_id' => $room_id]);

    // ลบข้อมูลห้องพักจากฐานข้อมูล
    return $pdo->prepare("DELETE FROM hotel_rooms WHERE hotel_room_id = :room_id")->execute([':room_id' => $room_id]);
}

function check_admin() {
    if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
        header("Location: index.php");
        exit();
    }
}

function getHotelImage($pdo, $hotel_id) {
    $stmt = $pdo->prepare("SELECT image_path FROM hotel_images WHERE hotel_id = ? LIMIT 1");
    $stmt->execute([$hotel_id]);
    $image = $stmt->fetchColumn();

    return $image ? htmlspecialchars($image) : "/hotel_main/src/img/hotel_img/default.jpg";
}

function getHotelImages($pdo) {
    $stmt = $pdo->query("SELECT image_path FROM hotel_images ORDER BY RAND() LIMIT 5"); // สุ่ม 5 รูป
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

?>