<?php
session_start();
require 'db.php';
require '../vendor/autoload.php'; // Path to autoload.php for Composer

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];

    $query = "SELECT user_id, username, email FROM users WHERE username = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        // Generate OTP
        $otp = rand(100000, 999999);  // Generate a random 6-digit OTP
        $_SESSION['otp'] = $otp;
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['email'] = $user['email']; // Save email in session

        // Calculate OTP expiry time (e.g., 10 minutes from now)
        $otp_expiry = date('Y-m-d H:i:s', strtotime('+10 minutes'));

        // Insert OTP and expiry into the database
        $update_query = "UPDATE users SET otp = ?, otp_expiry = ? WHERE user_id = ?";
        $update_stmt = $pdo->prepare($update_query);
        $update_stmt->execute([$otp, $otp_expiry, $user['user_id']]);

        // Send OTP via email
        $mail = new PHPMailer(true);

        try {
            // SMTP Server settings
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'whereshotel@gmail.com';  // Your email
            $mail->Password = 'pwcd mikg qvvz forh'; // Your email password or app password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            // Recipient
            $mail->setFrom('your_email@gmail.com', 'WhresHotel Password Reset');  // Your email and name
            $mail->addAddress($user['email'], $user['username']);  // Recipient's email and name

            // Content
            $mail->isHTML(true);  // Set email format to HTML
            $mail->Subject = 'Your OTP for Password Reset';
            $mail->Body    = "<div style='background: black; color: white; padding: 20px; text-align: center; font-family: Arial, sans-serif;'>
                <img src='https://cdn.discordapp.com/attachments/1335642059817619578/1344061865289973801/icon.png?ex=67bf8aa8&is=67be3928&hm=1faf401ac5078faf5a76ba06325c12222ecb0a54dfccaa362ee2b3b822c32109&' width='80'>
                <h2>Reset Password Verification Code</h2>
                <p>Enter this code on the identity verification screen:</p>
                <h1 style='color: white;'> $otp </h1>
                <p>This code will expire shortly. If you didn't try to log in, we recommend that you reset your password now.</p>
                </div>";

            // Send email
            $mail->send();
            $_SESSION['status'] = "OTP ถูกส่งไปยังอีเมลของคุณแล้ว";  // เพิ่มข้อความสถานะ
            header("Location: verify_otp.php");  // Redirect to OTP verification page
            exit();
        } catch (Exception $e) {
            $_SESSION['errors'] = ["Message could not be sent. Mailer Error: {$mail->ErrorInfo}"];
            header("Location: send_otp.php");  // Redirect back if email fails
            exit();
        }
    } else {
        $_SESSION['errors'] = ["No user found with that username."];
        header("Location: send_otp.php");
        exit();
    }

    
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Request OTP</title>
    <link rel="stylesheet" type="text/css" href="style.css">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.1.2/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">

    <div class="min-h-screen flex justify-center items-center">
        <div class="w-full max-w-md bg-white p-8 rounded-lg shadow-lg">
            <h2 class="text-2xl font-semibold text-center mb-4">Reset Your Password</h2>
            <form action="send_otp.php" method="POST">
                <div class="relative mb-4">
                    <input type="text" id="username" name="username" class="peer h-10 w-full border-b-2 border-gray-300 text-gray-900 placeholder-transparent focus:outline-none focus:border-black" placeholder="Username" required>
                    <label for="username" class="absolute left-0 -top-3.5 text-gray-600 text-sm transition-all peer-placeholder-shown:text-base peer-placeholder-shown:text-gray-400 peer-placeholder-shown:top-2 peer-focus:-top-3.5 peer-focus:text-gray-600 peer-focus:text-sm">Username</label>
                </div>

                <?php if (isset($_SESSION['errors'])): ?>
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
                    <button type="submit" class="w-full bg-black text-white py-2 border-2 border-black rounded-md hover:bg-transparent hover:text-black font-semibold">Request OTP</button>
                </div>

                <div class="mt-3">
                    <p class="text-center">Remember your password? <a href="login.php" class="text-blue-500 font-semibold">Login</a></p>
                </div>
            </form>
        </div>
    </div>

</body>
</html>
