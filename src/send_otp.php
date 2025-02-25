<?php
session_start();
require 'db.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php'; // Path to autoload.php for Composer

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];

    // Check if email exists in the database
    $sql = "SELECT users_id, email FROM users WHERE email = '$email'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        // Create OTP
        $otp = rand(100000, 999999); // 6-digit OTP

        // Set OTP expiry time (1 hour)
        $otp_expiry = date("Y-m-d H:i:s", strtotime("+1 hour"));

        // Update OTP and expiry time in the database
        $update_sql = "UPDATE users SET otp = '$otp', otp_expiry = '$otp_expiry' WHERE email = '$email'";
        if ($conn->query($update_sql) === TRUE) {
            // Send OTP to the email
            $mail = new PHPMailer(true);
            try {
                // SMTP Server settings
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'adminwhereshotel@gmail.com';  // Your email
                $mail->Password = 'Admin_hotel'; // Your email password or app password
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;

                // Recipient
                $mail->setFrom('your_email@gmail.com', 'Where Hotel'); // Your email
                $mail->addAddress($email); // Recipient's email

                // Content
                $mail->isHTML(true);
                $mail->Subject = 'OTP Password Reset';
                $mail->Body = "W-<strong>$otp</strong><br> is your OTP for password reset.";
                $mail->Body .= "<br>Please enter this OTP to verify your identity.";

                $mail->send();
                // Redirect to verify_otp.php with email and OTP in the URL
                header("Location: verify_otp.php?email=$email&otp=$otp");
                exit();
            } catch (Exception $e) {
                echo "<div class='alert alert-danger'>Message could not be sent. Mailer Error: {$mail->ErrorInfo}</div>";
            }
        } else {
            echo "<div class='alert alert-danger'>Error updating OTP in database.</div>";
        }
    } else {
        echo "<div class='alert alert-danger'>Email not found.</div>";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Where's hotel</title>
    <link rel="stylesheet" type="text/css" href="style.css">
    <link rel="icon" href="./img/icon.png">
</head>
<body class="bg-dark text-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-4">
                <div class="card bg-dark text-light p-4">
                    <h2 class="text-center mb-4">ส่ง OTP เพื่อรีเซ็ตรหัสผ่าน</h2>
                    <form action="send_otp.php" method="POST">
                        <div class="mb-3">
                            <label for="email" class="form-label">อีเมล</label>
                            <input type="email" name="email" id="email" class="form-control" placeholder="กรุณากรอกอีเมลของคุณ" required>
                        </div>
                        <button type="submit" class="btn btn-warning w-100">ส่ง OTP</button>
                    </form>
                    <div class="text-center mt-3">
                        <a href="login.php" class="text-warning">กลับสู่หน้าล็อกอิน</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer class="w-full bg-white py-4 mt-8 shadow-md">
        <p class="text-black text-center text-sm">&copy; 2025 Where's Hotel</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>