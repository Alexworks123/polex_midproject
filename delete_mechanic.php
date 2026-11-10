
<?php
session_start();
include 'DatabaseConnection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (isset($_POST['delete'])) {
    $id = $_POST['mechanic_id'];

    $check = $conn->prepare("SELECT COUNT(*) AS total FROM service_jobs WHERE mechanic_id = ?");
    $check->bind_param("i", $id);
    $check->execute();
    $result = $check->get_result()->fetch_assoc();

    if ($result['total'] > 0) {
        die("Cannot delete mechanic because they have service records.");
    }

    $stmt = $conn->prepare("DELETE FROM mechanics WHERE mechanic_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    header("Location: mechanics.php");
    exit();
}
?>
