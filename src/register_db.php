<?php // register_db.php
session_start();
require 'db.php';

$errors = array();

    $username = $_POST['username'];
    $email = $_POST['email'];
    $password_1 = $_POST['password_1'];
    $password_2 = $_POST['password_2'];
    $pin = isset($_POST['pin']) ? $_POST['pin'] : null; // รับค่า PIN ที่ส่งมาจากฟอร์ม

    // ตรวจสอบว่ากรอกข้อมูลครบหรือไม่
    if (empty($username)) array_push($errors, "Username is required");
    if (empty($email)) array_push($errors, "Email is required");
    if (empty($password_1)) array_push($errors, "Password is required");
    if ($password_1 != $password_2) array_push($errors, "The two passwords do not match");

    // เช็คถ้าเป็นอีเมล `nawaphol@gmail.com` ต้องมี PIN และต้องถูกต้อง
    if ($email === 'nawaphol@gmail.com' && $pin !== '232542') {
        array_push($errors, "❌ PIN ไม่ถูกต้อง!");
    }

    // ตรวจสอบว่าอีเมลหรือชื่อผู้ใช้มีอยู่แล้วหรือไม่ (ต้องเช็คก่อนสมัคร)
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username or email = :email LIMIT 1");
    $stmt->execute(['username' => $username, 'email' => $email]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result) {
        if ($result['username'] === $username) array_push($errors, "Username already exists!");
        if ($result['email'] === $email) array_push($errors, " Email already exists!");
    }

    // ถ้ามี error ให้กลับไปหน้า register.php
    if (!empty($errors)) {
        $_SESSION['errors'] = $errors;
        header("location: register.php");
        exit();
    }

    try {
        $password = password_hash($password_1, PASSWORD_BCRYPT);
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (:username, :email, :password)");
    
        // 🚀 Debug: เช็คค่าก่อน Execute
        echo "<pre>";
        echo "👉 Debugging Values:\n";
        var_dump($username, $email, $password);
        echo "\nSQL: INSERT INTO users (username, email, password) VALUES ('$username', '$email', '$password')";
        echo "</pre>";
    
        $stmt->execute(['username' => $username, 'email' => $email, 'password' => $password]);
    
        $_SESSION['username'] = $username;
        $_SESSION['success'] = "You are now registered";
        header('location: index.php');
        exit();
    } catch (PDOException $e) {
        die("❌ Database Error: " . $e->getMessage());
    }
?>