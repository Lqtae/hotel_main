<?php
session_start();
require 'db.php';
require 'functions.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // ดึงข้อมูลรหัสผ่านเก่าจากฐานข้อมูล
    $stmt = $pdo->prepare("SELECT password FROM users WHERE user_id = :user_id");
    $stmt->execute(['user_id' => $user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user || !password_verify($current_password, $user['password'])) {
        $error = "Current password is incorrect.";
    } elseif ($new_password !== $confirm_password) {
        $error = "New password and confirm password do not match.";
    } else {
        // อัปเดตรหัสผ่านใหม่
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET password = :password WHERE user_id = :user_id");
        $stmt->execute(['password' => $hashed_password, 'user_id' => $user_id]);

        $_SESSION['success'] = "Password updated successfully!";
        header("Location: profile.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.0.0/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <header class="w-full bg-gray-100 py-6 shadow-md sticky top-0 z-10"> 
        <h1 class="text-black text-3xl font-bold text-center">Where's Hotel</h1>

        <div class="absolute top-6 left-4">
            <a href="javascript:history.back()" class="text-gray-700 font-bold text-lg px-4 py-2 rounded-lg hover:text-blue-600">
            <i class="fa-solid fa-chevron-left"></i>
            </a>
        </div>
    </header>
    <div class="max-w-md mx-auto mt-10 bg-white shadow-md rounded-lg p-6">
        <h2 class="text-xl font-semibold text-center">Change Password</h2>
        <?php if (isset($error)): ?>
            <p class="text-red-500 text-center"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>
        <form action="change_password.php" method="post">
            <div class="mt-4">
                <label class="block text-gray-700">Current Password</label>
                <input type="password" name="current_password" required class="w-full p-2 border rounded">
            </div>
            <div class="mt-4">
                <label class="block text-gray-700">New Password</label>
                <input type="password" name="new_password" required class="w-full p-2 border rounded">
            </div>
            <div class="mt-4">
                <label class="block text-gray-700">Confirm New Password</label>
                <input type="password" name="confirm_password" required class="w-full p-2 border rounded">
            </div>
            <div class="mt-4">
                <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded-md">Change Password</button>
                <a href="profile.php" class="ml-2 text-gray-500">Cancel</a>
            </div>
        </form>
    </div>
</body>
</html>
