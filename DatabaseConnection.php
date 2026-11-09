
<?php
$conn = new mysqli("localhost", "root", "", "vehicle_maintenance_db");

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}
?>
