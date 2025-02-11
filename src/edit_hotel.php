<?php //edit_hotel.php
require 'db.php';
require 'functions.php';

$hotelId = $_GET['id'] ?? null;
if (!$hotelId || !is_numeric($hotelId)) {
    echo "Hotel ID is required and must be a valid number.";
    exit;
}

// ดึงข้อมูลโรงแรม
$stmt = $pdo->prepare("SELECT * FROM hotels WHERE hotel_id = ?");
$stmt->execute([$hotelId]);
$hotel = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$hotel) {
    echo "ไม่พบข้อมูลโรงแรม";
    exit;
}

// ดึงข้อมูลจังหวัด
$stmt = $pdo->prepare("SELECT * FROM provinces ORDER BY province_name");
$stmt->execute();
$provinces = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'update') {
    $hotelName = $_POST['hotel_name'];
    $hotelAddress = $_POST['hotel_address'];
    $provinceId = $_POST['province_id'];

    $stmt = $pdo->prepare("UPDATE hotels SET hotel_name = ?, address = ?, province_id = ? WHERE hotel_id = ?");
    $success = $stmt->execute([$hotelName, $hotelAddress, $provinceId, $hotelId]);

    echo json_encode(["success" => $success]);
    exit;
}

// ตรวจสอบการอัปโหลดหรือการลบรูปภาพ
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'upload' && isset($_FILES['hotel_image'])) {
        echo json_encode(uploadHotelImage($hotelId, $_FILES['hotel_image']));
        exit;
    }

    if ($_POST['action'] === 'delete' && isset($_POST['image_id'])) {
        echo json_encode(deleteHotelImage($_POST['image_id']));
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'upload' && isset($_FILES['hotel_image'])) {
        echo json_encode(uploadHotelImage($hotelId, $_FILES['hotel_image']));
        exit;
    }

    if ($_POST['action'] === 'delete' && isset($_POST['image_id'])) {
        echo json_encode(deleteHotelImage($_POST['image_id']));
        exit;
    }

}

// ดึงข้อมูลรูปภาพโรงแรม
$stmt = $pdo->prepare("SELECT * FROM hotel_images WHERE hotel_id = ?");
$stmt->execute([$hotelId]);
$hotelImages = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>แก้ไขข้อมูลโรงแรม</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body class="bg-gray-100 min-h-screen flex flex-col">

    <header class="w-full bg-gray-100 py-6 shadow-md">
        <h1 class="text-black text-3xl font-bold text-center">แก้ไขข้อมูลโรงแรม</h1>
    </header>

    <main class="flex-grow">

        <div class="absolute top-6 left-4">
            <a href="javascript:history.back()" class="text-gray-700 font-bold text-lg px-4 py-2 rounded-lg hover:text-blue-600">
                &lt; Back
            </a>
        </div>

        <div class="max-w-lg mx-auto mt-10 bg-white p-6 rounded-lg shadow-lg">

            <!-- 🏨 หัวข้อแก้ไขข้อมูลโรงแรม -->
            <h2 class="text-2xl font-bold mb-4">แก้ไขข้อมูลโรงแรม</h2>

            <!-- ฟอร์มแก้ไขข้อมูลโรงแรม -->
            <form id="editHotelForm">
                <input type="hidden" name="hotel_id" value="<?= $hotelId ?>">

                <label class="block text-gray-700 font-medium mt-2">ชื่อโรงแรม</label>
                <input type="text" name="hotel_name" id="hotel_name" value="<?= htmlspecialchars($hotel['hotel_name'] ?? '') ?>" class="w-full border px-4 py-2 rounded-lg">

                <label class="block text-gray-700 font-medium mt-2">ที่อยู่</label>
                <textarea name="hotel_address" id="hotel_address" class="w-full h-24 border px-4 py-2 rounded-lg"><?= htmlspecialchars($hotel['address'] ?? '') ?></textarea>

                <label class="block text-gray-700 font-medium mt-2">จังหวัด</label>
                <select name="province_id" class="w-full border px-4 py-2 rounded-lg">
                    <?php foreach ($provinces as $province): ?>
                        <option value="<?= $province['province_id'] ?>" <?= ($province['province_id'] == $hotel['province_id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($province['province_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                
                <button type="submit" class="mt-4 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">บันทึกข้อมูล</button>
                <p id="updateStatus" class="mt-2 text-green-600"></p>
            </form>

            <hr class="my-6">

            <!-- 📸 หัวข้ออัปโหลดรูปภาพโรงแรม -->
            <h2 class="text-2xl font-bold mb-4">อัปโหลดรูปภาพโรงแรม</h2>
                <form id="uploadForm" enctype="multipart/form-data">
                    <input type="hidden" name="hotel_id" value="<?= $hotelId ?>">
                    <input type="file" name="hotel_image" id="hotel_image" class="w-full border px-4 py-2 rounded-lg">
                </form>
                <div id="imageGrid" class="grid grid-cols-2 gap-4 mt-4">
                    <?php foreach ($hotelImages as $image): ?>
                        <div class="relative image-container" data-id="<?= $image['image_id'] ?>">
                            <img src="<?= htmlspecialchars($image['image_path']) ?>" class="w-full h-48 object-cover rounded-md shadow">
                            <button class="absolute top-1 right-1 bg-red-500 text-white px-2 py-1 rounded-md text-xs hover:bg-red-600 delete-image" data-id="<?= $image['image_id'] ?>">ลบ</button>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
    </main>

    <footer class="w-full bg-white py-4 mt-8 shadow-md">
        <p class="text-black text-center text-sm">
            &copy; 2025 <a href="index.php" class="text-black hover:font-semibold">Where's Hotel</a>
         </p>
    </footer>

    <script>
       $(document).ready(function() {
            // 📌 อัปเดตข้อมูลโรงแรม
            $("#editHotelForm").on("submit", function(e) {
                e.preventDefault();
                let formData = $(this).serialize() + "&action=update";
            
                $.ajax({
                    url: "edit_hotel.php?id=<?= $hotelId ?>",
                    type: "POST",
                    data: formData,
                    success: function(response) {
                        let data = JSON.parse(response);
                        if (data.success) {
                            $("#updateStatus").text("บันทึกข้อมูลสำเร็จ!").fadeIn().delay(2000).fadeOut();
                        } else {
                            $("#updateStatus").text("เกิดข้อผิดพลาด: " + data.error).css("color", "red");
                        }
                    }
                });
            });
        
            // 📌 อัปโหลดรูปภาพเมื่อเลือกไฟล์
            $("#hotel_image").on("change", function() {
                let formData = new FormData($("#uploadForm")[0]);
                formData.append("action", "upload");
            
                $.ajax({
                    url: "edit_hotel.php?id=<?= $hotelId ?>",
                    type: "POST",
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: function(response) {
                        let data = JSON.parse(response);
                        if (data.success) {
                            $("#imageGrid").append(`
                                <div class="relative image-container" data-id="${data.image_id}">
                                    <img src="${data.image_path}" class="w-full h-48 object-cover rounded-md shadow">
                                    <button class="absolute top-1 right-1 bg-red-500 text-white px-2 py-1 rounded-md text-xs hover:bg-red-600 delete-image" data-id="${data.image_id}">ลบ</button>
                                </div>
                            `);
                        } else {
                            $("#uploadStatus").text(data.error);
                        }
                    }
                });
            });
        
            // 📌 ฟังก์ชันลบรูปภาพ
            $(document).on("click", ".delete-image", function() {
                let imageId = $(this).data("id");
                let imageContainer = $(this).closest(".image-container");
            
                $.ajax({
                    url: "edit_hotel.php?id=<?= $hotelId ?>",
                    type: "POST",
                    data: { action: "delete", image_id: imageId },
                    success: function(response) {
                        let data = JSON.parse(response);
                        if (data.success) {
                            imageContainer.fadeOut(300, function() { $(this).remove(); });
                        } else {
                            alert("ลบไม่สำเร็จ: " + data.error);
                        }
                    }
                });
            });
        });     
    </script>
</body>
</html>
