<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $otp = $_POST['otp'];

    // ตรวจสอบว่า OTP ที่กรอกตรงกับ OTP ที่เก็บใน session หรือไม่
    if (isset($_SESSION['otp']) && intval($_SESSION['otp']) === intval($otp)) {
        // OTP ถูกต้อง ให้ดำเนินการเปลี่ยนรหัสผ่าน
        header("Location: reset_password.php");  // ไปที่หน้า reset_password.php
        exit();
    } else {
        // OTP ไม่ถูกต้อง
        $_SESSION['errors'] = ["OTP ไม่ถูกต้อง"];
        header("Location: verify_otp.php");
        exit();
    }
}

function mask_email($email) {
    // แสดงอีเมลที่ซ่อนบางส่วน
    $email_parts = explode('@', $email);
    $username = $email_parts[0];
    $domain = $email_parts[1];
    
    // ซ่อนตัวอักษรบางตัวจากชื่อผู้ใช้
    $masked_username = substr($username, 0, 3) . str_repeat('*', strlen($username) - 3);
    
    return $masked_username . '@' . $domain;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify OTP</title>
    <link rel="stylesheet" type="text/css" href="style.css">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.1.2/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">

    <header class="w-full bg-gray-100 py-6 shadow-md sticky top-0 z-10"> 
        <h1 class="text-black text-3xl font-bold text-center">Where's Hotel</h1>
        <div class="absolute top-6 right-6">
    </header>

    <div class="min-h-screen flex justify-center items-center">
        <div class="w-full max-w-md bg-white p-8 rounded-lg shadow-lg">
            <h2 class="text-2xl font-semibold text-center mb-4">Verify OTP</h2>

            <!-- แสดงอีเมลที่ขอ OTP -->
            <?php if (isset($_SESSION['email'])): ?>
                <div class="mt-3 bg-blue-100 border-l-4 border-blue-500 text-blue-700 p-3">
                    <p>OTP ถูกส่งไปที่อีเมล: <?php echo mask_email($_SESSION['email']); ?></p>
                </div>
            <?php endif; ?>

            <form action="verify_otp.php" method="POST">
                <div class="relative mb-4 mt-4">
                    <input type="text" id="otp" name="otp" maxlength="6" class="peer h-10 w-full border-b-2 border-gray-300 text-gray-900 placeholder-transparent focus:outline-none focus:border-black" placeholder="Enter OTP">
                    <label for="otp" class="absolute left-0 -top-3.5 text-gray-600 text-sm transition-all peer-placeholder-shown:text-base peer-placeholder-shown:text-gray-400 peer-placeholder-shown:top-2 peer-focus:-top-3.5 peer-focus:text-gray-600 peer-focus:text-sm">OTP</label>
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
                    <button type="submit" class="w-full bg-black text-white py-2 rounded-md hover:bg-transparent hover:text-black font-semibold">Verify OTP</button>
                </div>

                <div class="mt-3">
                    <p class="text-center">
                        Did not receive OTP? 
                        <a href="send_otp.php" class="text-blue-500 font-semibold">Request again</a>
                    </p>
                </div>
            </form>
        </div>
    </div>

</body>  
</html>
