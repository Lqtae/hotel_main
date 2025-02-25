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
        #backdrop {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            display: none;
            z-index: 10;
        }

        #searchDropdown {
            position: absolute;
            top: 100%;
            left: 0;
            width: 100%;
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
            max-height: 300px;
            overflow-y: auto;
            display: none;
            z-index: 20;
        }

        .search-item {
            padding: 12px;
            cursor: pointer;
            border-bottom: 1px solid #eee;
        }
        .search-item:last-child {
            border-bottom: none;
        }
        .search-item:hover {
            background: #f3f3f3;
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
            <div class="flex-grow">
                <label for="search" class="block text-gray-700 text-sm font-medium">ค้นหา:</label>
                <input type="text" id="search" placeholder="ค้นหาโรงแรมหรือจังหวัด" 
                       class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-black" 
                       onclick="openSearchDropdown()">
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

    <h2 class="text-2xl font-semibold mb-4">ที่พักแนะนำสำหรับท่านโดยเฉพาะ</h2>

        <div class="swiper hotelSwiper">
            <div class="tabs">
                <button class="tab-btn active" data-province="">กรุงเทพ</button>
                <button class="tab-btn" data-province="เชียงใหม่">เชียงใหม่</button>
                <button class="tab-btn" data-province="ชลบุรี">ชลบุรี</button>
                <button class="tab-btn" data-province="หัวหิน/ชะอำ">หัวหิน/ชะอำ</button>
            </div>

            <div class="swiper-wrapper">
                <?php
                $hotelsQuery = $pdo->query("
                    SELECT h.*, p.province_name, h.star_rating as avg_rating
                    FROM hotels h
                    LEFT JOIN provinces p ON h.province_id = p.province_id
                    ORDER BY h.star_rating DESC
                    LIMIT 8
                ");
                $hotels = $hotelsQuery->fetchAll(PDO::FETCH_ASSOC);

                foreach ($hotels as $hotel):
                    $hotelImageQuery = $pdo->prepare("SELECT image_path FROM hotel_images WHERE hotel_id = ? LIMIT 1");
                    $hotelImageQuery->execute([$hotel['hotel_id']]);
                    $hotelImage = $hotelImageQuery->fetchColumn();
                ?>
                <div class="swiper-slide">
                    <div class="bg-white rounded-lg shadow-md overflow-hidden">
                        <a href="hotel_detail.php?id=<?= $hotel['hotel_id']; ?>">
                            <img src="<?= $hotelImage ? htmlspecialchars($hotelImage) : 'img/default-hotel.jpg'; ?>"
                                 class="w-full h-52 object-cover">
                        </a>
                        <div class="p-4">
                            <div class="flex justify-between items-center">
                                <h3 class="text-lg font-semibold"><?= htmlspecialchars($hotel['hotel_name']); ?></h3>
                                <span class="bg-blue-500 text-white px-2 py-1 rounded-lg text-sm font-bold">
                                    <?= number_format($hotel['avg_rating'], 1); ?>
                                </span>
                            </div>
                            <p class="text-sm text-gray-500">📍 <?= htmlspecialchars($hotel['province_name']); ?></p>
                            <p class="text-red-500 font-semibold">THB <?= number_format($hotel['room_price'], 2); ?></p>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
                
            <div class="swiper-button-next"></div>
            <div class="swiper-button-prev"></div>
            <div class="swiper-pagination"></div>
        </div>
                


</main>

<footer class="w-full bg-white py-4 mt-8 shadow-md">
    <p class="text-black text-center text-sm">&copy; 2025 <a href="admin_dashboard.php" class="text-black hover:font-semibold">Where's Hotel</a></p>
</footer>

<script>
    document.getElementById('region').addEventListener('change', function() {
    const regionId = this.value;
    console.log("Selected Region ID:", regionId);

    const provinceSelect = document.getElementById('province');
    provinceSelect.innerHTML = '<option value="">-- เลือกจังหวัด --</option>';
    provinceSelect.disabled = !regionId;

    if (regionId) {
        fetch(`get.php?region_id=${regionId}`)
            .then(response => response.text())
            .then(data => {
                console.log("Fetched Data:", data);
                provinceSelect.innerHTML += data;
                provinceSelect.disabled = false;
            })
            .catch(error => console.error('Error loading provinces:', error));
    }
    performSearch();
});

    document.getElementById('search').addEventListener('input', performSearch);
    document.getElementById('province').addEventListener('change', performSearch);

    function openSearchDropdown() {
            document.getElementById('backdrop').style.display = 'block';
            document.getElementById('searchDropdown').style.display = 'block';
        }

        function closeSearchDropdown() {
            document.getElementById('backdrop').style.display = 'none';
            document.getElementById('searchDropdown').style.display = 'none';
        }

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
                                <p class="text-sm text-gray-500"> ${hotel.address}</p>
                                <p class="text-sm text-gray-500">⭐ ${hotel.star_rating} ดาว</p>
                                <p class="text-sm text-gray-700">${hotel.province_name}</p>
                            </div>
                        </div>
                    `;
                });
            });
    }

    document.addEventListener("DOMContentLoaded", loadTopHotels);
    document.addEventListener("DOMContentLoaded", function() {
    var swiper = new Swiper(".mySwiper", {
        loop: true, // ทำให้เลื่อนวนลูปได้
        autoplay: {
            delay: 3000, // เลื่อนอัตโนมัติทุก 3 วินาที
            disableOnInteraction: false,
        },
        navigation: {
            nextEl: ".swiper-button-next",
            prevEl: ".swiper-button-prev",
        },
        pagination: {
            el: ".swiper-pagination",
            clickable: true,
        },
    });
});

document.addEventListener("DOMContentLoaded", function () {
    const buttons = document.querySelectorAll(".tab-btn");

    buttons.forEach((button) => {
        button.addEventListener("click", function () {
            document.querySelector(".tab-btn.active").classList.remove("active");
            this.classList.add("active");

            const province = this.dataset.province;
            loadHotels(province);
        });
    });

    // โหลดข้อมูลโรงแรมครั้งแรก
    loadHotels("");
});

function loadHotels(province) {
    fetch(`get_hotels.php?province=${province}`)
        .then(response => response.text())
        .then(data => {
            document.getElementById("hotels-container").innerHTML = data;
        })
        .catch(error => console.error("Error:", error));
}

</script>

</body>
</html>
