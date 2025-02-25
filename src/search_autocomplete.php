<?php
require 'db.php';

$query = isset($_GET['query']) ? trim($_GET['query']) : '';

if ($query !== '') {
    $stmt = $pdo->prepare("SELECT hotel_id, hotel_name FROM hotels WHERE hotel_name LIKE ? LIMIT 10");
    $stmt->execute(["%$query%"]);
    $hotels = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($hotels);
} else {
    echo json_encode([]);
}
?>
