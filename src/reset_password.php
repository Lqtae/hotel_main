<?php
session_start();

// เช็คว่า session ของ user_id มีอยู่หรือไม่
if (!isset($_SESSION['user_id'])) {
    header("Location: send_otp.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // ตรวจสอบข้อผิดพลาด
    $errors = [];

    if (strlen($new_password) < 6) {
        $errors[] = "Password must be at least 6 characters";
    }

    if ($new_password !== $confirm_password) {
        $errors[] = "The two passwords do not match";
    }

    if (empty($errors)) {
        // เปลี่ยนรหัสผ่านในฐานข้อมูล
        require 'db.php';
        $user_id = $_SESSION['user_id'];
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE user_id = ?");
        if ($stmt->execute([$hashed_password, $user_id])) {
            // ล้างค่า OTP และ user_id หลังจากรีเซ็ตรหัสผ่านแล้ว
            unset($_SESSION['otp']);
            unset($_SESSION['user_id']);
            header("Location: login.php");  // ไปที่หน้าล็อกอิน
            exit();
        } else {
            $errors[] = "ไม่สามารถเปลี่ยนรหัสผ่านได้";
        }
    }

    $_SESSION['errors'] = $errors;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <link rel="stylesheet" type="text/css" href="style.css">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.1.2/dist/tailwind.min.css" rel="stylesheet">
    <link rel="icon" href="./img/icon.png">
</head>
<body class="bg-gray-100">

    <div class="min-h-screen flex justify-center items-center">
        <div class="w-full max-w-md bg-white p-8 rounded-lg shadow-lg">
            <h2 class="text-2xl font-semibold text-center mb-4">Reset Your Password</h2>
            <form action="reset_password.php" method="POST">
                <!-- New Password -->
                <div class="relative mb-4">
                    <input type="password" id="new_password" name="new_password" class="peer h-10 w-full border-b-2 border-gray-300 text-gray-900 placeholder-transparent focus:outline-none focus:border-black" placeholder="New Password" required>
                    <label for="new_password" class="absolute left-0 -top-3.5 text-gray-600 text-sm transition-all peer-placeholder-shown:text-base peer-placeholder-shown:text-gray-400 peer-placeholder-shown:top-2 peer-focus:-top-3.5 peer-focus:text-gray-600 peer-focus:text-sm">New Password</label>
                </div>

                <!-- Confirm Password -->
                <div class="relative mb-4">
                    <input type="password" id="confirm_password" name="confirm_password" class="peer h-10 w-full border-b-2 border-gray-300 text-gray-900 placeholder-transparent focus:outline-none focus:border-black" placeholder="Confirm Password" required>
                    <label for="confirm_password" class="absolute left-0 -top-3.5 text-gray-600 text-sm transition-all peer-placeholder-shown:text-base peer-placeholder-shown:text-gray-400 peer-placeholder-shown:top-2 peer-focus:-top-3.5 peer-focus:text-gray-600 peer-focus:text-sm">Confirm Password</label>
                </div>

                <!-- แสดง Error ถ้ามี -->
                <?php if (isset($_SESSION['errors']) && !empty($_SESSION['errors'])): ?>
                    <div class="mt-3 bg-red-100 border-l-4 border-red-500 text-red-700 p-3">
                        <strong>Error:</strong>
                        <ul class="list-disc pl-5">
                            <?php 
                                foreach ($_SESSION['errors'] as $error) {
                                    echo "<li>$error</li>";
                                }
                                unset($_SESSION['errors']);
                            ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <div class="mt-8">
                    <button type="submit" class="w-full bg-black text-white py-2 rounded-md hover:bg-transparent hover:text-black font-semibold">Reset Password</button>
                </div>

                <div class="mt-3">
                    <p class="text-center">Remember your password? <a href="login.php" class="text-blue-500 font-semibold">Login</a></p>
                </div>
            </form>
        </div>
    </div>

</body>
</html>
