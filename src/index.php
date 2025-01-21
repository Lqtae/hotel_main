<?php //index.php
require 'db.php';  

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
    <title>Live Hotel Search</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex flex-col">
<header class="w-full bg-gray-100 py-6 shadow-md">
        <h1 class="text-black text-3xl font-bold text-center">Where's Hotel</h1>
    </header>

    <main class="flex-1 w-full max-w-4xl mx-auto mt-8 px-4">
        <div class="bg-white shadow-md rounded-lg p-6">
            <div class="mb-4">
                <label for="search" class="block text-gray-700 text-sm font-medium">ค้นหา:</label>
                <input type="text" id="search" placeholder="ค้นหาโรงแรมหรือจังหวัด" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div class="mb-4">
                <label for="region" class="block text-gray-700 text-sm font-medium">เลือกภาค:</label>
                <select id="region" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-red-500">
                    <option value="">-- เลือกภาค --</option>
                    <?php foreach ($regions as $region): ?>
                        <option value="<?= $region['region_id']; ?>"><?= $region['region_name']; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-4">
                <label for="province" class="block text-gray-700 text-sm font-medium">เลือกจังหวัด:</label>
                <select id="province" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-red-500" disabled>
                    <option value="">-- เลือกจังหวัด --</option>
                </select>
            </div>

            <div id="results" class="mt-4 space-y-4">
                <p class="text-gray-500 text-sm">กรุณาใส่คำค้นหาที่ช่องด้านบน</p>
            </div>
        </div>
    </main>

    <footer class="w-full bg-white py-4 mt-8 shadow-md">
        <p class="text-black text-center text-sm">&copy; 2025 Hotel Search</p>
    </footer>

    <script>
        const provincesByRegion = <?php echo json_encode($provinces); ?>;

        document.getElementById('region').addEventListener('change', function() {
            const region = this.value;
            const provinceSelect = document.getElementById('province');

            provinceSelect.innerHTML = '<option value="">-- เลือกจังหวัด --</option>';

            if (region) {
                provincesByRegion.filter(province => province.region_id == region).forEach(province => {
                    const option = document.createElement('option');
                    option.value = province.province_id;
                    option.textContent = province.province_name;
                    provinceSelect.appendChild(option);
                });
                provinceSelect.disabled = false;
            } else {
                provinceSelect.disabled = true;
            }
        });
        document.getElementById('region').addEventListener('change', performSearch);
            document.getElementById('province').addEventListener('change', performSearch);
            document.getElementById('search').addEventListener('input', performSearch);

            function performSearch() {
                const query = document.getElementById('search').value.trim();
                const region = document.getElementById('region').value;
                const province = document.getElementById('province').value;

                if (query.length === 0 && region === "" && province === "") {
                    document.getElementById('results').innerHTML = '<p class="text-gray-500 text-sm">กรุณาใส่คำค้นหาที่ช่องด้านบน</p>';
                    return;
                }

                // ส่งข้อมูลไปยัง search.php เพื่อดึงผลลัพธ์ใหม่
                fetch(`search.php?query=${encodeURIComponent(query)}&region=${encodeURIComponent(region)}&province=${encodeURIComponent(province)}`)
                    .then(response => response.json())
                    .then(data => {
                        const resultsContainer = document.getElementById('results');
                        resultsContainer.innerHTML = ''; // เคลียร์ผลลัพธ์เก่า

                        if (data.length > 0) {
                            data.forEach(item => {
                                const resultItem = document.createElement('div');
                                resultItem.className = 'p-4 bg-gray-100 rounded-lg shadow-sm';
                                resultItem.innerHTML = `
                                    <a href="hotel_detail.php?id=${item.hotel_id}" class="text-xl font-semibold text-blue-600">${item.hotel_name}</a>
                                    <p class="text-gray-700">ที่อยู่: ${item.address}</p>
                                    <p class="text-gray-700">จังหวัด: ${item.province_name}</p>
                                    <p class="text-gray-700">ภาค: ${item.region_name}</p>
                                `;
                                resultsContainer.appendChild(resultItem);
                            });
                        } else {
                            resultsContainer.innerHTML = '<p class="text-gray-500 text-sm">ไม่พบผลลัพธ์</p>';
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching search results:', error);
                        document.getElementById('results').innerHTML = '<p class="text-red-500 text-sm">เกิดข้อผิดพลาดในการค้นหา</p>';
                    });
            }
    </script>
</body>
</html>
