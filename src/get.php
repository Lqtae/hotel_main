<?php //get.php
include 'db.php'; 

if (isset($_POST['region_id'])) {
     $regionId = $_POST['region_id']; 

     $query = "SELECT * FROM provinces WHERE region_id = ?";
     $stmt = $conn->prepare($query); 

     if ($stmt) {
          $stmt->bind_param('i', $regionId);
          $stmt->execute();
          $result = $stmt->get_result();

          if ($result->num_rows > 0) {
               echo '<option value="">-- เลือกจังหวัด --</option>';
               while ($row = $result->fetch_assoc()) {
                    echo '<option value="' . $row['province_id'] . '">' . $row['province_name'] . '</option>';
               }
          } else {
               echo '<option value="">-- ไม่มีข้อมูลจังหวัด --</option>';
          }

          $stmt->close(); 
     } else {
          echo '<option value="">-- SQL Error: ' . $conn->error . ' --</option>';
     }
} else {
     echo '<option value="">-- region_id is not set --</option>';
}