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

document.getElementById('province').addEventListener('change', performSearch);
document.getElementById('search').addEventListener('input', performSearch);

function performSearch() {
    let query = document.getElementById('search').value.trim();
    let region = document.getElementById('region').value;
    let province = document.getElementById('province').value;
    let dropdown = document.getElementById('searchDropdown');

    let url = `search.php?type=hotel&query=${encodeURIComponent(query)}&region=${encodeURIComponent(region)}&province=${encodeURIComponent(province)}`;

    fetch(url)
        .then(response => response.json())
        .then(data => {
            dropdown.innerHTML = '';
            dropdown.classList.remove('hidden');

            if (data.length === 0) {
                dropdown.innerHTML = '<div class="p-3 text-gray-500">ไม่พบผลลัพธ์</div>';
                return;
            }

            data.forEach(item => {
                let div = document.createElement('div');
                div.classList.add(
                    'search-item');

                div.innerHTML = `
                    <a href="hotel_detail.php?id=${item.hotel_id}" class="text-lg font-semibold text-blue-600 block">${item.hotel_name}</a>
                    <p class="text-gray-700 text-sm">📍 ${item.address}</p>
                    <p class="text-gray-700 text-sm">🏙 จังหวัด: ${item.province_name}</p>
                    <p class="text-gray-700 text-sm">🌎 ภาค: ${item.region_name}</p>
                `;

                div.addEventListener('click', function(event) {
                    event.preventDefault();
                    window.location.href = `hotel_detail.php?id=${item.hotel_id}`;
                });

                dropdown.appendChild(div);
            });
        })
        .catch(error => console.error('Error:', error));
}

document.addEventListener('click', function(event) {
    const dropdown = document.getElementById('searchDropdown');
    const searchBox = document.getElementById('search');

    if (!searchBox.contains(event.target) && !dropdown.contains(event.target)) {
        if (searchBox.value.trim() === "") {
            dropdown.classList.add('hidden');
        }
    }
});

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
        loop: true,
        autoplay: {
            delay: 3000,
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
    const userMenuBtn = document.getElementById("userMenuBtn");
    const userDropdown = document.getElementById("userDropdown");

    if (userMenuBtn && userDropdown) {
        userMenuBtn.addEventListener("click", function (event) {
            event.stopPropagation();
            userDropdown.classList.toggle("hidden"); // แสดง/ซ่อน Dropdown
        });

        document.addEventListener("click", function (event) {
            if (!userDropdown.contains(event.target) && !userMenuBtn.contains(event.target)) {
                userDropdown.classList.add("hidden"); // ซ่อน Dropdown ถ้าคลิกข้างนอก
            }
        });
    } else {
        console.error("Dropdown elements not found!");
    }
});

