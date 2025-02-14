<?php
session_start();
include('db.php');

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

    if (count($errors) == 0) {
        $password = md5($password); // Note: Using MD5 for password hashing is not recommended. Consider using `password_hash` and `password_verify`.

        $query = "SELECT * FROM user WHERE username = :username AND password = :password";
        $stmt = $pdo->prepare($query);
        $stmt->execute(['username' => $username, 'password' => $password]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            $_SESSION['username'] = $username;
            $_SESSION['success'] = "You are now logged in";
            header("location: index.php");
        } else {
            array_push($errors, "Invalid username or password");
            $_SESSION['error'] = "Invalid username or password!";
            header("location: login.php");
        }
    } else {
        $_SESSION['error'] = "Username & password are required";
        header("location: login.php");
    }
}
?>
