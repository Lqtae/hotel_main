<?php
session_start();
require 'db.php';
require 'functions.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = :user_id");
$stmt->execute(['user_id' => $user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    die("User not found.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.0.0/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="icon" href="./img/icon.png">
</head>
<body class="bg-gray-100 min-h-screen flex flex-col">
    <header class="w-full bg-gray-100 py-6 shadow-md sticky top-0 z-10"> 
        <h1 class="text-black text-3xl font-bold text-center">Where's Hotel</h1>

        <div class="absolute top-6 left-4">
            <a href="javascript:history.back()" class="text-gray-700 font-bold text-lg px-4 py-2 rounded-lg hover:text-blue-600">
            <i class="fa-solid fa-chevron-left"></i>
            </a>
        </div>
    </header>

<main class="w-full max-w-4xl mx-auto mt-2 px-4 flex-grow">
    <div class="max-w-lg mx-auto mt-10 bg-white shadow-lg rounded-lg p-8">
        <div class="flex items-center justify-center mb-6">
            <img src="<?= getProfileImage($user['image_path']) ?>" alt="Profile Image" class="w-48 h-48 object-cover rounded-full border-2 border-black">
        </div>
        <h2 class="text-3xl font-semibold text-center text-gray-700"><?= htmlspecialchars($user['first_name'] . " " . $user['last_name']) ?></h2>
        <p class="text-center text-sm text-gray-500 mb-4">@<?= htmlspecialchars($user['username']) ?></p>
        <div class="mt-6 space-y-4">
            <p><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
            <p><strong>Role:</strong> <?= htmlspecialchars($user['user_role']) ?></p>
            <p><strong>Joined:</strong> <?= htmlspecialchars($user['created_at']) ?></p>
            <p><strong>Password:</strong> ******** <a href="change_password.php" class="text-blue-500 hover:underline text-sm">(Change Password)</a></p>
        </div>
        <div class="mt-8 flex justify-center">
            <a href="edit_profile.php" class="w-full py-3 px-6 bg-black text-white border-2 border-black font-semibold rounded-md shadow-md text-center hover:bg-transparent hover:text-black">
                Edit Profile
            </a>
        </div>
    </div>
</main>

    <footer class="w-full bg-white py-4 mt-8 shadow-md">
        <p class="text-black text-center text-sm">
            &copy; 2025 Where's Hotel</a>
        </p>
    </footer>

</body>
</html>