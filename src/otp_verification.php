<?php
session_start();

require 'db.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php';  // ใช้ Composer สำหรับ PHPMailer

// ฟังก์ชันส่ง OTP ผ่านอีเมล
function send_otp_email($email, $new_otp) {
    $mail = new PHPMailer(true);
    try {
        // ตั้งค่า SMTP
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'adm1n.gkt@gmail.com';
        $mail->Password = 'egmh juza pkmz pmga'; // ใช้รหัสผ่านแอป (App Password)
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // ตั้งค่าอีเมล
        $mail->setFrom('adm1n.gkt@gmail.com', 'GKTMovie');
        $mail->addAddress($email);
        $mail->isHTML(true);
        $mail->Subject = 'OTP Password Reset';
        $mail->Body = "OTP ของคุณคือ: <strong>$new_otp</strong><br>กรุณากรอก OTP นี้เพื่อยืนยันตัวตนของคุณ";

        // ส่งอีเมล
        $mail->send();
        return true;
    } catch (Exception $e) {
        return false;
    }
}

// ตัวแปรแจ้งเตือน
$success_message = "";
$error_message = "";

// ตรวจสอบคำขอ POST สำหรับยืนยัน OTP
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['otp'])) {
    $email = $_POST['email'];
    $otp = $_POST['otp'];

    // ค้นหา OTP จากฐานข้อมูล
    $sql = "SELECT otp, otp_expiry FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        if ($user['otp'] == $otp && strtotime($user['otp_expiry']) > time()) {
            // OTP ถูกต้องและยังไม่หมดอายุ
            $_SESSION['verified_email'] = $email;
            header("Location: reset_password.php");
            exit();
        } else {
            $error_message = "OTP ไม่ถูกต้อง หรือหมดอายุแล้ว กรุณาลองใหม่";
        }
    } else {
        $error_message = "ไม่พบอีเมลนี้ในระบบ กรุณาลองใหม่";
    }
    $stmt->close();
}

// ตรวจสอบคำขอ GET สำหรับการขอ OTP ใหม่
if (isset($_GET['request_otp']) && !empty($_GET['email'])) {
    $email = $_GET['email'];

    // สร้าง OTP ใหม่
    $new_otp = rand(100000, 999999);
    $expiry = date('Y-m-d H:i:s', strtotime('+15 minutes'));

    // อัปเดต OTP ในฐานข้อมูล
    $sql = "UPDATE users SET otp = ?, otp_expiry = ? WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $new_otp, $expiry, $email);
    $stmt->execute();

    // ส่ง OTP ใหม่
    if (send_otp_email($email, $new_otp)) {
        $success_message = "OTP ใหม่ถูกส่งไปยังอีเมลของคุณแล้ว";
    } else {
        $error_message = "ไม่สามารถส่ง OTP ได้ในขณะนี้ กรุณาลองใหม่ภายหลัง";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GKTMovie - ยืนยัน OTP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="index.css">
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;700&display=swap" rel="stylesheet">
    <link rel="icon" href="https://icons.iconarchive.com/icons/alecive/flatwoken/512/Apps-Player-Video-icon.png">
</head>
<body class="bg-dark text-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-4">
                <div class="card bg-dark text-light p-4">
                    <h2 class="text-center mb-4">ยืนยัน OTP</h2>

                    <?php 
                    $email = htmlspecialchars($_GET['email'] ?? $_POST['email'] ?? '');
                    if (!empty($email)): 
                    ?>
                        <p class="text-center">อีเมลที่ขอ OTP: <strong><?php echo $email; ?></strong></p>
                    <?php endif; ?>

                    <!-- แสดงข้อความแจ้งเตือน -->
                    <?php if (!empty($error_message)): ?>
                        <div class="alert alert-danger text-center"><?php echo $error_message; ?></div>
                    <?php elseif (!empty($success_message)): ?>
                        <div class="alert alert-success text-center"><?php echo $success_message; ?></div>
                    <?php endif; ?>

                    <!-- ฟอร์มยืนยัน OTP -->
                    <form action="otp_verification.php" method="POST">
                        <input type="hidden" name="email" value="<?php echo $email; ?>">
                        <div class="mb-3">
                            <label for="otp" class="form-label">กรอก OTP</label>
                            <input type="text" name="otp" id="otp" class="form-control" placeholder="กรอก OTP ของคุณ" required>
                        </div>
                        <button type="submit" class="btn btn-warning w-100">ยืนยัน OTP</button>
                    </form>

                    <!-- ปุ่มขอ OTP ใหม่ -->
                    <div class="mt-3 text-center">
                        <form action="otp_verification.php" method="GET">
                            <input type="hidden" name="request_otp" value="1">
                            <input type="hidden" name="email" value="<?php echo $email; ?>">
                            <button type="submit" class="btn btn-link text-warning">ขอ OTP ใหม่</button>
                        </form>
                    </div>

                </div>
            </div>
        </div>
    </div>
</body>
</html>