<?php 
include('db.php'); // เชื่อมต่อฐานข้อมูล
include('auth.php'); // ป้องกันการเข้าถึงโดยไม่ได้ล็อกอิน
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
</head>
<body>
    <h1>Welcome, <?php echo $_SESSION['username']; ?>!</h1>
    <p>This is the dashboard page. You can only see this if you are logged in.</p>
    <a href="logout.php">Logout</a>
</body>
</html>