<?php
require 'db.php';
require 'functions.php';

session_start();
check_admin();
$errors = array();

if (isset($_POST['login_user'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    if (empty($username)) {
        array_push($errors, "Username is required");
    }

    if (empty($password)) {
        array_push($errors, "Password is required");
    }

    if (!empty($errors)) {
        $_SESSION['errors'] = $errors;
        header("location: login.php");
        exit();
    }

    $query = "SELECT user_id, username, email, password, user_role FROM users WHERE username = :username";
    $stmt = $pdo->prepare($query);
    $stmt->execute(['username' => $username]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result) {

        if (!password_verify($password, $result['password'])) {
            $_SESSION['errors'] = ["Invalid username or password"];
            header("location: login.php");
            exit();
        }

        if ($result['email'] === 'nawaphol@gmail.com') {
            $_SESSION['temp_user'] = $username;
            $_SESSION['show_pin_popup'] = true;
            header("location: login.php");
            exit();
        }

        $_SESSION['user_id'] = $result['user_id'];
        $_SESSION['username'] = $result['username'];
        $_SESSION['user_role'] = $result['user_role']; 
        $_SESSION['success'] = "You are now logged in";

        header("location: index.php");
        exit();
    } else {
        $_SESSION['errors'] = ["Invalid username or password"];
        header("location: login.php");
        exit();
    }
}
?>
