<?php
session_start();
include('db.php');

$errors = array();

if (isset($_POST['reg_user'])) {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password_1 = $_POST['password_1'];
    $password_2 = $_POST['password_2'];

    if (empty($username)) {
        array_push($errors, "Username is required");
    }
    if (empty($email)) {
        array_push($errors, "Email is required");
    }
    if (empty($password_1)) {
        array_push($errors, "Password is required");
    }
    if ($password_1 != $password_2) {
        array_push($errors, "The two passwords do not match");
    }

    if (count($errors) > 0) {
        $_SESSION['errors'] = $errors;
        header("location: register.php");
        exit();
    }

    // Check if the username or email already exists
    $user_check_query = "SELECT * FROM user WHERE username = :username OR email = :email LIMIT 1";
    $stmt = $pdo->prepare($user_check_query);
    $stmt->execute(['username' => $username, 'email' => $email]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result) {
        if ($result['username'] === $username) {
            array_push($errors, "Username already exists");
        }
        if ($result['email'] === $email) {
            array_push($errors, "Email already exists");
        }
    }

    if (count($errors) == 0) {
        $password = md5($password_1); // Again, consider using `password_hash` here.

        $sql = "INSERT INTO user (username, email, password) VALUES (:username, :email, :password)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['username' => $username, 'email' => $email, 'password' => $password]);

        $_SESSION['username'] = $username;
        $_SESSION['success'] = "You are now logged in";
        header('location: index.php');
    } else {
        $_SESSION['errors'] = $errors;
        header("location: register.php");
    }
}
?>
