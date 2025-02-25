<?php
session_start();
require 'db.php';
require 'functions.php';

$errors = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']); 
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        $errors[] = "กรุณากรอกข้อมูลให้ครบถ้วน";
    } else {
        // ค้นหาผู้ใช้จากฐานข้อมูล
        $query = "SELECT user_id, username, email, password, user_role FROM users WHERE username = :username";
        $stmt = $pdo->prepare($query);
        $stmt->execute(['username' => $username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            if (!password_verify($password, $user['password'])) {
                $errors[] = "ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง";
            } else {
                // 🔹 ถ้า email เป็น 'nawaphol@gmail.com' ต้องยืนยัน PIN ก่อน
                if ($user['email'] === 'nawaphol@gmail.com') {
                    $_SESSION['temp_user'] = $username;
                    $_SESSION['temp_user_id'] = $user['user_id'];
                    $_SESSION['temp_user_role'] = $user['user_role'];
                    $_SESSION['show_pin_popup'] = true;
                    header("location: login.php");
                    exit();
                }

                // 🔹 ถ้าเป็น user ทั่วไป ให้เข้าสู่ระบบได้เลย
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['user_role'] = $user['user_role'];
                $_SESSION['success'] = "เข้าสู่ระบบสำเร็จ";
                
                header("location: index.php");
                exit();
            }
        } else {
            $errors[] = "ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง";
        }
    }
    
    $_SESSION['errors'] = $errors;
    header("location: login.php");
    exit();
}
?>