<?php
require 'db.php';

header('Content-Type: text/html; charset=utf-8');

if (isset($_GET['region_id'])) {
    $regionId = $_GET['region_id'];

    try {
        $stmt = $pdo->prepare("SELECT * FROM provinces WHERE region_id = ?");
        $stmt->execute([$regionId]);
        $provinces = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($provinces) {
            foreach ($provinces as $province) {
                echo '<option value="' . $province['province_id'] . '">' . $province['province_name'] . '</option>';
            }
        } else {
            echo '<option value="">-- ไม่มีข้อมูลจังหวัด --</option>';
        }
    } catch (PDOException $e) {
        echo '<option value="">-- SQL Error: ' . $e->getMessage() . ' --</option>';
    }
} else {
    echo '<option value="">-- region_id is not set --</option>';
}
?>