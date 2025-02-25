<?php
session_start();
require 'db.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $pin = $_POST['pin'];

    if ($pin === "232542") { 
        if (isset($_SESSION['temp_user'])) {
            $_SESSION['username'] = $_SESSION['temp_user'];
            $_SESSION['user_id'] = $_SESSION['temp_user_id'];
            $_SESSION['user_role'] = $_SESSION['temp_user_role'];
            $_SESSION['success'] = "เข้าสู่ระบบสำเร็จ";

            unset($_SESSION['temp_user']);
            unset($_SESSION['temp_user_id']);
            unset($_SESSION['temp_user_role']);
            unset($_SESSION['show_pin_popup']);

            echo json_encode(["success" => true]);
            exit();
        }
    }

    echo json_encode(["success" => false]);
    exit();
}