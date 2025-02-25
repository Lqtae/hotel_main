<?php // index.php
require 'db.php';
require 'functions.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$isAdmin = false;
$userData = null;

if (!empty($_SESSION['username'])) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$_SESSION['username']]);
    $userData = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($userData && isset($userData['user_role'])) {
        $isAdmin = ($userData['user_role'] === 'admin');
    }
}

$hotelImages = getHotelImages($pdo);
$regionsQuery = $pdo->query("SELECT * FROM regions");
$regions = $regionsQuery->fetchAll(PDO::FETCH_ASSOC);
$provincesQuery = $pdo->query("SELECT * FROM provinces");
$provinces = $provincesQuery->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Where's Hotel</title>
    <link rel="stylesheet" type="text/css" href="style.css">
    <link rel="icon" href="./img/icon.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>

    <style>
        #searchDropdown {
    width: 818px; /* ขยายให้กว้างขึ้น */
    max-height: 500px; /* กำหนดความสูงสูงสุด */
    overflow-y: auto; /* ให้สามารถเลื่อนเฉพาะ dropdown ได้ */
    border: 1px solid #ddd; /* เพิ่มเส้นขอบ */
    padding: 5px;
}

.search-item {
    padding: 12px;
    background: white;
    border-radius: 6px;
    margin-bottom: 5px;
    transition: background 0.2s ease-in-out;
}

.search-item:hover {
    background: #f1f1f1;
}

    </style>
    

</head>
<body class="bg-gray-100 min-h-screen flex flex-col">

<header class="w-full bg-gray-100 py-6 shadow-md sticky top-0 z-10"> 
        <h1 class="text-black text-3xl font-bold text-center">Where's Hotel</h1>
        <div class="absolute top-6 right-6">
            <?php if ($userData): ?>
                <?php if ($isAdmin): ?>
                    <a href="admin_dashboard.php" class="text-gray-700 font-bold text-lg px-4 py-2 hover:text-blue-600">
                        <i class="fa-solid fa-user-tie"></i> Admin
                    </a>
                <?php endif; ?>
                
                <div class="relative inline-block">
                    <button id="userMenuBtn" class="text-gray-700 text-lg px-4 py-2 hover:text-blue-600">
                        <i class="fa-solid fa-user"></i> <?= htmlspecialchars($userData['username']); ?>
                    </button>
                    <div id="userDropdown" class="hidden absolute right-0 mt-2 w-52 bg-white shadow-md rounded-lg">
                        <a href="profile.php" class="block px-4 py-2 text-gray-700 hover:bg-gray-200">Profile</a>
                        <a href="logout.php" class="block px-4 py-2 text-red-600 hover:bg-gray-200">Logout</a>
                    </div>
                </div>
            <?php else: ?>
                <a href="login.php" class="text-gray-700 font-bold text-lg px-4 py-2 hover:text-blue-600">
                    <i class="fa-solid fa-sign-in-alt"></i> Login
                </a>
            <?php endif; ?>
        </div>
    </header>

    <main class="flex-1 w-full max-w-4xl mx-auto mt-8 px-4">
        <div class="bg-white shadow-md rounded-lg mb-8">
            <div class="swiper mySwiper ">
                <div class="swiper-wrapper">
                    <?php foreach ($hotelImages as $image): ?>
                        <div class="swiper-slide">
                            <img src="<?= htmlspecialchars($image['image_path']) ?>" class="w-full h-96 object-cover rounded-lg">
                        </div>
                    <?php endforeach; ?>
                </div>
                    
                <div class="swiper-button-next"></div>
                <div class="swiper-button-prev"></div>
                <div class="swiper-pagination"></div>
            </div>
        </div>

        <div class="bg-white shadow-md rounded-lg p-6">
        <div class="flex items-center mb-4 gap-4">
            <div class="relative flex-grow">
                <label for="search" class="block text-gray-700 text-sm font-medium">ค้นหา:</label>
                <input type="text" id="search" placeholder="ค้นหาโรงแรมหรือจังหวัด"
                        class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-black">
                        <div id="searchDropdown"
                             class="absolute w-full bg-white rounded-lg shadow-lg max-h-60 overflow-y-auto hidden z-50 border border-gray-300">
                        </div>

            </div>

            <div>
                <label for="region" class="block text-gray-700 text-sm font-medium">เลือกภาค:</label>
                <select id="region" class="border border-gray-300 rounded-lg px-4 py-2 focus:ring-black">
                    <option value="">-- เลือกภาค --</option>
                    <?php foreach ($regions as $region): ?>
                        <option value="<?= $region['region_id']; ?>"><?= $region['region_name']; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label for="province" class="block text-gray-700 text-sm font-medium">เลือกจังหวัด:</label>
                <select id="province" class="border border-gray-300 rounded-lg px-4 py-2 focus:ring-black" disabled>
                    <option value="">-- เลือกจังหวัด --</option>
                </select>
            </div>
        </div>
        </div>

        <div class="mt-8">
        <h2 class="text-2xl font-semibold mb-4">ที่พักแนะนำสำหรับท่านโดยเฉพาะ</h2>
        <div id="top-hotels" class="grid grid-cols-2 gap-6"></div>
    </div>
    </main>

    <footer class="w-full bg-white py-4 mt-8 shadow-md">
        <p class="text-black text-center text-sm">&copy; 2025 <a href="admin_dashboard.php" class="text-black hover:font-semibold">Where's Hotel</a></p>
    </footer>

    <script src="script.js"></script>

</body>
</html>
