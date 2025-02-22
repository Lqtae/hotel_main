<?php // verify_pin.php
session_start();
require 'db.php';
require 'functions.php';

check_admin();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $pin = $_POST['pin'];

    if ($pin === "232542") { 
        if (isset($_SESSION['temp_user'])) {
            $username = $_SESSION['temp_user'];

            $query = "SELECT user_id, user_role FROM users WHERE username = :username";
            $stmt = $pdo->prepare($query);
            $stmt->execute(['username' => $username]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($result) {
                $_SESSION['username'] = $username;
                $_SESSION['user_id'] = $result['user_id'];
                $_SESSION['user_role'] = $result['user_role'];
                $_SESSION['success'] = "You are now logged in";

                unset($_SESSION['temp_user']);
                unset($_SESSION['show_pin_popup']);

                echo json_encode(["success" => true]);
                exit();
            }
        }
    }

    echo json_encode(["success" => false]);
    exit();
}
?>
