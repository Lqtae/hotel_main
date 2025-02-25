<?php
session_start();

// กำหนดค่าการเชื่อมต่อฐานข้อมูล
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "movie_db";

// สร้างการเชื่อมต่อ
$conn = new mysqli($servername, $username, $password, $dbname);

// ตรวจสอบการเชื่อมต่อ
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// ตรวจสอบว่าอีเมลถูกยืนยันแล้วในเซสชั่น
if (!isset($_SESSION['verified_email'])) {
    header("Location: send_otp.php"); // ถ้ายังไม่ได้ยืนยันอีเมลให้ไปที่หน้า send_otp.php
    exit();
}

$error_message = "";
$success_message = "";

// ถ้ามีการส่งข้อมูลจากฟอร์ม
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // รับค่าจากฟอร์ม
    $email = $_SESSION['verified_email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // ตรวจสอบว่ารหัสผ่านตรงกันหรือไม่
    if ($password !== $confirm_password) {
        $error_message = "รหัสผ่านทั้งสองช่องไม่ตรงกัน กรุณาลองอีกครั้ง";
    } elseif (strlen($password) < 8) {
        $error_message = "รหัสผ่านต้องมีอย่างน้อย 8 ตัว";
    } else {
        // แฮชรหัสผ่านใหม่
        $new_password = password_hash($password, PASSWORD_BCRYPT);

        // อัปเดตรหัสผ่านในฐานข้อมูล
        $sql = "UPDATE users SET password = ?, otp = NULL, otp_expiry = NULL WHERE email = ?";
        $stmt = $conn->prepare($sql);

        if ($stmt === false) {
            $error_message = "เกิดข้อผิดพลาดในการเตรียมคำสั่ง SQL: " . $conn->error;
        } else {
            $stmt->bind_param("ss", $new_password, $email);

            if ($stmt->execute()) {
                $success_message = "รีเซ็ตรหัสผ่านสำเร็จแล้ว! คุณจะถูกนำไปยังหน้าเข้าสู่ระบบในอีก 4 วินาที";
                unset($_SESSION['verified_email']); // ลบอีเมลที่ยืนยันแล้วในเซสชั่น
                echo "<script>setTimeout(function() { window.location.href = 'login.php'; }, 4000);</script>"; // รอ 4 วินาทีก่อนเปลี่ยนเส้นทาง
            } else {
                $error_message = "เกิดข้อผิดพลาดในการอัปเดตฐานข้อมูล: " . $stmt->error;
            }

            $stmt->close();
        }
    }
}

// ปิดการเชื่อมต่อฐานข้อมูล
$conn->close();
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GKTMovie - รีเซ็ตรหัสผ่าน</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="index.css">
    <link rel="icon" href="https://scontent-bkk1-1.xx.fbcdn.net/v/t1.15752-9/473622236_1774944236693414_4199905355043563465_n.png?_nc_cat=109&ccb=1-7&_nc_sid=9f807c&_nc_ohc=CRJA25RKqn0Q7kNvgF5a4Zk&_nc_zt=23&_nc_ht=scontent-bkk1-1.xx&oh=03_Q7cD1gEJl1VH4IW7a1UmDUCEV26DI2IlBfKNR6L6r8L9AiGzvA&oe=67B8518E">
    <script>
        function togglePasswordVisibility() {
            const passwordField = document.getElementById("password");
            const confirmPasswordField = document.getElementById("confirm_password");
            const type = passwordField.type === "password" ? "text" : "password";
            passwordField.type = type;
            confirmPasswordField.type = type;
        }

        function validatePasswords() {
            const password = document.getElementById("password").value;
            const confirmPassword = document.getElementById("confirm_password").value;

            // ตรวจสอบความยาวรหัสผ่าน (ต้องมีอย่างน้อย 8 ตัว)
            if (password.length < 8) {
                const errorModal = new bootstrap.Modal(document.getElementById('errorModal'));
                document.getElementById('errorMessage').innerText = 'รหัสผ่านต้องมีอย่างน้อย 8 ตัว';
                errorModal.show();
                return false;
            }

            // ตรวจสอบว่ามีอักษรภาษาไทยหรือไม่
            const thaiRegex = /[\u0E00-\u0E7F]/;
            if (thaiRegex.test(password)) {
                const errorModal = new bootstrap.Modal(document.getElementById('errorModal'));
                document.getElementById('errorMessage').innerText = 'รหัสผ่านไม่สามารถมีอักษรภาษาไทยได้';
                errorModal.show();
                return false;
            }

            // ตรวจสอบว่ารหัสผ่านตรงกันหรือไม่
            if (password !== confirmPassword) {
                const errorModal = new bootstrap.Modal(document.getElementById('errorModal'));
                document.getElementById('errorMessage').innerText = 'รหัสผ่านทั้งสองช่องไม่ตรงกัน กรุณาลองอีกครั้ง';
                errorModal.show();
                return false;
            }

            return true;
        }
    </script>
</head>
<body class="bg-dark text-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-4">
                <div class="card bg-dark text-light p-4">
                    <h2 class="text-center mb-4">รีเซ็ตรหัสผ่าน</h2>
                    <form action="reset_password.php" method="POST" onsubmit="return validatePasswords();">
                        <div class="mb-3">
                            <label for="password" class="form-label">รหัสผ่านใหม่</label>
                            <input type="password" name="password" id="password" class="form-control" placeholder="กรอกรหัสผ่านใหม่" required>
                        </div>
                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">ยืนยันรหัสผ่านใหม่</label>
                            <input type="password" name="confirm_password" id="confirm_password" class="form-control" placeholder="ยืนยันรหัสผ่านใหม่" required>
                        </div>
                        <div class="mb-3">
                            <input type="checkbox" onclick="togglePasswordVisibility()"> แสดงรหัสผ่าน
                        </div>
                        <button type="submit" class="btn btn-warning w-100">รีเซ็ตรหัสผ่าน</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Success Modal -->
    <div class="modal fade" id="successModal" tabindex="-1" aria-labelledby="successModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content bg-success text-light">
                <div class="modal-header">
                    <h5 class="modal-title" id="successModalLabel">สำเร็จ</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <?php echo $success_message; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Error Modal -->
    <div class="modal fade" id="errorModal" tabindex="-1" aria-labelledby="errorModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content bg-danger text-light">
                <div class="modal-header">
                    <h5 class="modal-title" id="errorModalLabel">ข้อผิดพลาด</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <span id="errorMessage"></span>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        <?php if (!empty($success_message)) : ?>
            var successModal = new bootstrap.Modal(document.getElementById('successModal'));
            successModal.show();
        <?php elseif (!empty($error_message)) : ?>
            var errorModal = new bootstrap.Modal(document.getElementById('errorModal'));
            errorModal.show();
        <?php endif; ?>
    </script>
</body>
</html>