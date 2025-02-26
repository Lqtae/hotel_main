<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.1.2/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <link rel="icon" href="./img/icon.png">
</head>
<body class="bg-gray-100 min-h-screen flex flex-col">

    <!-- Header -->
    <header class="w-full bg-gray-100 py-6 shadow-md sticky top-0 z-10"> 
        <h1 class="text-black text-3xl font-bold text-center">Contact Admin</h1>
        <div class="absolute top-6 left-4">
            <a href="javascript:history.back()" class="text-gray-700 font-bold text-lg px-4 py-2 rounded-lg hover:text-blue-600">
                <i class="fa-solid fa-chevron-left"></i>
            </a>
        </div>
    </header>

    <!-- Main Content -->
    <main class="w-full max-w-4xl mx-auto mt-8 px-4 flex-grow">
        <div class="bg-white shadow-lg rounded-lg p-6">
            <h2 class="text-2xl font-semibold text-center text-gray-700 mb-6">Admin Team</h2>

            <!-- Admin 1 -->
            <div class="flex items-center h-36 p-4 bg-gray-50 rounded-lg mb-6 shadow-md">
                <img src="./img/user_img/Admin1.jpg" alt="Admin 1" class="w-24 h-24 object-cover rounded-full border-2 border-gray-400">
                <div class="ml-4">
                    <h3 class="text-lg font-semibold text-gray-800">Nawaphol Khantongkhum</h3>
                    <p class="text-sm text-gray-600">Email: nawaphol.kh@rmuti.ac.th</p>
                    <div class="mt-2">
                        <a href="https://www.facebook.com/latae.nawaphol/" class="text-blue-500 hover:text-blue-700 mx-1"><i class="fab fa-facebook"></i></a>
                        <a href="https://github.com/Lqtae" class="text-black hover:text-blue-600 mx-1"><i class="fab fa-github"></i></a>
                        <a href="https://www.instagram.com/_lqtae.tt/" class="text-red-500 hover:text-red-700 mx-1"><i class="fab fa-instagram"></i></a>
                    </div>
                </div>
                <a href="mailto:nawaphol.kh@rmuti.ac.th" class="ml-auto px-4 py-2 bg-black text-white rounded-lg text-sm hover:bg-gray-800">
                    Contact
                </a>
            </div>

            <!-- Admin 2 -->
            <div class="flex items-center h-36 mb-4 p-4 bg-gray-50 rounded-lg shadow-md">
                <img src="./img/user_img/Admin2.jpg" alt="Admin 2" class="w-24 h-24 object-cover rounded-full border-2 border-gray-400">
                <div class="ml-4">
                    <h3 class="text-lg font-semibold text-gray-800">Waraporn Chitsue</h3>
                    <p class="text-sm text-gray-600">Email: waraporn.cs@rmuti.ac.th</p>
                    <div class="mt-2">
                        <a href="#" class="text-blue-500 hover:text-blue-700 mx-1"><i class="fab fa-facebook"></i></a>
                        <a href="#" class="text-blue-400 hover:text-blue-600 mx-1"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="text-red-500 hover:text-red-700 mx-1"><i class="fab fa-instagram"></i></a>
                    </div>
                </div>
                <a href="mailto:admin2@example.com" class="ml-auto px-4 py-2 bg-black text-white rounded-lg text-sm hover:bg-gray-800">
                    Contact
                </a>
            </div>

            <h2 class="text-2xl font-semibold text-center text-gray-700 mb-6">About</h2>
            <div class="flex items-center mt-4 h-36 p-4 bg-gray-50 rounded-lg shadow-md">
                

            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="w-full bg-white py-4 mt-8 shadow-md">
        <p class="text-black text-center text-sm">
            &copy; 2025 Where's Hotel
        </p>
    </footer>

</body>
</html>
