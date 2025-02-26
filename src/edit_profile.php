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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $email = $_POST['email'];
    $profile_image = $_FILES['profile_image'] ?? null;
    $target_file = $user['image_path'];

    if (!empty($profile_image['name'])) {
        $upload_dir = __DIR__ . '/../src/img/user_img/';
        $target_file = $upload_dir . basename($profile_image["name"]);
        if (move_uploaded_file($profile_image["tmp_name"], $target_file)) {
            $target_file = "/hotel_main/src/img/user_img/" . basename($profile_image["name"]);
        } else {
            die("Error uploading file.");
        }
    }

    $stmt = $pdo->prepare("UPDATE users SET first_name = :first_name, last_name = :last_name, email = :email, image_path = :profile_image WHERE user_id = :user_id");
    $stmt->execute([
        'first_name' => $first_name,
        'last_name' => $last_name,
        'email' => $email,
        'profile_image' => $target_file,
        'user_id' => $user_id
    ]);

    $_SESSION['success'] = "Profile updated successfully!";
    header("Location: profile.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile</title>
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
        <h2 class="text-2xl font-semibold text-center text-gray-700">Edit Your Profile</h2>
        <form action="edit_profile.php" method="post" enctype="multipart/form-data" class="mt-6">
            <div class="mb-4">
                <label for="first_name" class="block text-sm font-medium text-gray-700">First Name</label>
                <input type="text" name="first_name" id="first_name" value="<?= htmlspecialchars($user['first_name']) ?>" class="w-full p-3 mt-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
            <div class="mb-4">
                <label for="last_name" class="block text-sm font-medium text-gray-700">Last Name</label>
                <input type="text" name="last_name" id="last_name" value="<?= htmlspecialchars($user['last_name']) ?>" class="w-full p-3 mt-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
            <div class="mb-4">
                <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                <input type="email" name="email" id="email" value="<?= htmlspecialchars($user['email']) ?>" class="w-full p-3 mt-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
            <div class="mb-4">
                <label for="profile_image" class="block text-sm font-medium text-gray-700">Profile Image</label>
                <input type="file" name="profile_image" id="profile_image" class="w-full p-3 mt-2 border border-gray-300 rounded-md shadow-sm">
                <?php if (!empty($user['image_path'])) : ?>
                    <img src="<?= getProfileImage($user['image_path']) ?>" alt="Profile Image" class="mt-4 w-48 h-48 object-cover rounded-full border border-gray-300">
                <?php endif; ?>
            </div>
            <div class="flex justify-between items-center">
                <button type="submit" class="w-full py-3 px-6 bg-black border-2 border-black text-white font-semibold rounded-md shadow-md hover:bg-transparent hover:text-black">
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</main>
    <footer class="w-full bg-white py-4 mt-8 shadow-md">
        <p class="text-black text-center text-sm">
            &copy; 2025 Where's Hotel</a>
        </p>
    </footer>

</body>
</html>