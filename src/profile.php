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
    <link rel="icon" href="./img/icon.png">
</head>
<body class="bg-gray-100">

    <div class="max-w-lg mx-auto mt-10 bg-white shadow-lg rounded-lg p-8">
        <div class="flex items-center justify-center mb-6">
            <img src="<?= getProfileImage($user['image_path']) ?>" alt="Profile Image" class="w-64 h-64 object-cover rounded-full border-2 border-black">
        </div>
        <h2 class="text-3xl font-semibold text-center text-gray-700"><?= htmlspecialchars($user['first_name'] . " " . $user['last_name']) ?></h2>
        <p class="text-center text-sm text-gray-500 mb-4">@<?= htmlspecialchars($user['username']) ?></p>
        <div class="mt-6 space-y-4">
            <p><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
            <p><strong>Role:</strong> <?= htmlspecialchars($user['user_role']) ?></p>
            <p><strong>Joined:</strong> <?= htmlspecialchars($user['created_at']) ?></p>
        </div>
        <div class="mt-8 flex justify-center">
            <a href="edit_profile.php" class="w-full py-3 px-6 bg-black text-white font-semibold rounded-md shadow-md text-center hover:bg-black focus:outline-none focus:ring-2 focus:ring-indigo-500">
                Edit Profile
            </a>
        </div>
    </div>

</body>
</html>