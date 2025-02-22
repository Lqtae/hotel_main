<?php
require 'db.php';
require 'functions.php';

session_start();
check_admin();

// รับค่า hotel_id และตรวจสอบ
$hotelId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$hotelId) {
    die("Hotel ID is invalid.");
}

// ดึงข้อมูลโรงแรมและจังหวัด
$hotel = getHotelDetailsById($hotelId);
$provinces = getAllProvinces();

if (!$hotel) {
    die("ไม่พบข้อมูลโรงแรม");
}

// ตรวจสอบการอัปเดตข้อมูล
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    switch ($_POST['action']) {
        case 'update':
            $hotelName = $_POST['hotel_name'];
            $hotelAddress = $_POST['hotel_address'];
            $provinceId = $_POST['province_id'];

            $stmt = $pdo->prepare("UPDATE hotels SET hotel_name = ?, address = ?, province_id = ? WHERE hotel_id = ?");
            $success = $stmt->execute([$hotelName, $hotelAddress, $provinceId, $hotelId]);

            echo json_encode(["success" => $success]);
            exit;

        case 'upload':
            if (!empty($_FILES['hotel_image'])) {
                echo json_encode(uploadHotelImage($hotelId, $_FILES['hotel_image']));
                exit;
            }
            break;

        case 'delete':
            if (!empty($_POST['image_id'])) {
                echo json_encode(deleteHotelImage($_POST['image_id']));
                exit;
            }
            break;
    }
}

// ดึงรูปภาพโรงแรม
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
    <link rel="stylesheet" type="text/css" href="style.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>
<body class="bg-gray-100 min-h-screen flex flex-col">

    <header class="w-full bg-gray-100 py-6 shadow-md sticky top-0 z-10">
        <h1 class="text-black text-3xl font-bold text-center">แก้ไขข้อมูลโรงแรม</h1>
        <div class="absolute top-6 left-4">
            <a href="javascript:history.back()" class="text-gray-700 font-bold text-lg px-4 py-2 rounded-lg hover:text-blue-600">
            <i class="fa-solid fa-chevron-left"></i>
            </a>
        </div>
    </header>

    <main class="flex-grow">
        <div class="max-w-lg mx-auto mt-10 bg-white p-6 rounded-lg shadow-lg">
            <h2 class="text-2xl font-bold mb-4">แก้ไขข้อมูลโรงแรม</h2>

            <form id="editHotelForm">
                <input type="hidden" name="hotel_id" value="<?= $hotelId ?>">

                <label class="block text-gray-700 font-medium mt-2">ชื่อโรงแรม</label>
                <input type="text" name="hotel_name" id="hotel_name" value="<?= htmlspecialchars($hotel['hotel_name']) ?>" class="w-full border px-4 py-2 rounded-lg">

                <label class="block text-gray-700 font-medium mt-2">ที่อยู่</label>
                <textarea name="hotel_address" id="hotel_address" class="w-full h-24 border px-4 py-2 rounded-lg"><?= htmlspecialchars($hotel['address']) ?></textarea>

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

            <h2 class="text-2xl font-bold mb-4">อัปโหลดรูปภาพโรงแรม</h2>
            <form id="uploadForm" enctype="multipart/form-data">
                <div id="uploadBox" class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center cursor-pointer hover:border-blue-500 transition">
                        📷 <span class="text-gray-500">คลิกเพื่ออัปโหลด</span>
                        <input type="file" name="hotel_image"  class="hidden" id="hotel_image">
                </div>
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
            $("#editHotelForm").on("submit", function(e) {
                e.preventDefault();
                let formData = $(this).serialize() + "&action=update";
            
                $.post("edit_hotel.php?id=<?= $hotelId ?>", formData, function(response) {
                    let data = JSON.parse(response);
                    if (data.success) {
                        $("#updateStatus").text("บันทึกข้อมูลสำเร็จ!").fadeIn().delay(2000).fadeOut();
                    } else {
                        $("#updateStatus").text("เกิดข้อผิดพลาด: " + data.error).css("color", "red");
                    }
                });
            });

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
                        }
                    }
                });
            });

            $(document).on("click", ".delete-image", function() {
                let imageId = $(this).data("id");
                let imageContainer = $(this).closest(".image-container");
            
                $.post("edit_hotel.php?id=<?= $hotelId ?>", { action: "delete", image_id: imageId }, function(response) {
                    let data = JSON.parse(response);
                    if (data.success) {
                        imageContainer.fadeOut(300, function() { $(this).remove(); });
                    }
                });
            });
        });     

        document.addEventListener("DOMContentLoaded", function () {
            const fileInput = document.querySelector('#hotel_image');
            const previewContainer = document.querySelector('#imagePreview');
            const primaryImageInput = document.querySelector('#primaryImageIndex');
            const uploadBox = document.querySelector("#uploadBox");

            uploadBox.addEventListener("click", function () {
                fileInput.click();
            });
        });
    </script>

</body>
</html>