<?php //index.php
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
        
        <div class="bg-white shadow-md rounded-lg p-6">
        <div class="flex items-center mb-4 gap-4">

            <div class="flex-grow">
                <label for="search" class="block text-gray-700 text-sm font-medium">ค้นหา:</label>
                <input type="text" id="search" placeholder="ค้นหาโรงแรมหรือจังหวัด" 
                       class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-black">
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

        <div id="results" class="mt-4 space-y-4"></div>
    </div>

    <!-- โรงแรมยอดนิยม -->
    <div class="mt-8">
        <h2 class="text-2xl font-semibold mb-4">🏨 โรงแรมยอดนิยม</h2>
        <div id="top-hotels" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6"></div>
    </div>

</main>

<footer class="w-full bg-white py-4 mt-8 shadow-md">
    <p class="text-black text-center text-sm">&copy; 2025 <a href="admin_dashboard.php" class="text-black hover:font-semibold">Where's Hotel</a></p>
</footer>

<script>
    document.getElementById('region').addEventListener('change', function() {
    const regionId = this.value;
    console.log("Selected Region ID:", regionId); // ✨ เช็คค่า regionId ก่อน fetch

    const provinceSelect = document.getElementById('province');
    provinceSelect.innerHTML = '<option value="">-- เลือกจังหวัด --</option>';
    provinceSelect.disabled = !regionId;

    if (regionId) {
        fetch(`get.php?region_id=${regionId}`) // ใช้ไฟล์ get.php ตามที่แก้ไข
            .then(response => response.text()) // ✨ ใช้ text() แทน json() เพื่อดูผลลัพธ์
            .then(data => {
                console.log("Fetched Data:", data); // ✨ เช็คว่า fetch ได้ข้อมูลอะไรกลับมา
                provinceSelect.innerHTML += data;
                provinceSelect.disabled = false;
            })
            .catch(error => console.error('Error loading provinces:', error));
    }
    performSearch();
});

    document.getElementById('search').addEventListener('input', performSearch);
    document.getElementById('province').addEventListener('change', performSearch);

    function performSearch() {
        const query = document.getElementById('search').value.trim();
        const region = document.getElementById('region').value;
        const province = document.getElementById('province').value;

        fetch(`search.php?query=${query}&region=${region}&province=${province}`)
            .then(response => response.json())
            .then(data => {
                const resultsContainer = document.getElementById('results');
                resultsContainer.innerHTML = '';

                data.forEach(item => {
                    resultsContainer.innerHTML += `
                        <div class="p-4 bg-gray-100 rounded-lg shadow-sm">
                            <a href="hotel_detail.php?id=${item.hotel_id}" class="text-xl font-semibold text-blue-600">${item.hotel_name}</a>
                            <p class="text-gray-700">ที่อยู่: ${item.address}</p>
                            <p class="text-gray-700">จังหวัด: ${item.province_name}</p>
                            <p class="text-gray-700">ภาค: ${item.region_name}</p>
                        </div>
                    `;
                });
            });
    }

    function loadTopHotels() {
        fetch("search.php?query=&region=&province=")
            .then(response => response.json())
            .then(data => {
                const topHotelsContainer = document.getElementById('top-hotels');
                topHotelsContainer.innerHTML = '';
                data.slice(0, 6).forEach(hotel => {
                    topHotelsContainer.innerHTML += `
                        <div class="bg-white shadow-md rounded-lg overflow-hidden">
                            <img src="${hotel.image ? hotel.image : '/hotel_main/src/img/default_hotel.jpg'}" class="w-full h-40 object-cover">
                            <div class="p-4">
                                <h3 class="text-lg font-semibold text-blue-600">
                                    <a href="hotel_detail.php?id=${hotel.hotel_id}">${hotel.hotel_name}</a>
                                </h3>
                                <p class="text-sm text-gray-500">⭐ ${hotel.star_rating} ดาว</p>
                                <p class="text-sm text-gray-700">${hotel.province_name}</p>
                            </div>
                        </div>
                    `;
                });
            });
    }

    document.addEventListener("DOMContentLoaded", loadTopHotels);
</script>

</body>
</html>
