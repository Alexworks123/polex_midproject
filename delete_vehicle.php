
<?php
session_start();
include 'DatabaseConnection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (isset($_POST['delete'])) {
    $id = $_POST['vehicle_id'];

    $check = $conn->prepare("SELECT COUNT(*) AS total FROM service_jobs WHERE vehicle_id = ?");
    $check->bind_param("i", $id);
    $check->execute();
    $result = $check->get_result()->fetch_assoc();

    if ($result['total'] > 0) {
        echo "Cannot delete vehicle because it has service history.";
        exit();
    }

    $stmt = $conn->prepare("DELETE FROM vehicles WHERE vehicle_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    header("Location: vehicles.php");
    exit();
}
?>
