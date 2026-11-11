
<?php
session_start();
include 'DatabaseConnection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (isset($_POST['delete'])) {

    $id = (int) $_POST['part_id'];

    if ($id <= 0) {
        die("Invalid Part ID!");
    }

    // CHECK IF PART IS USED IN A SERVICE JOB
    $check = $conn->prepare(
        "SELECT COUNT(*) AS total
         FROM job_parts
         WHERE part_id = ?"
    );

    $check->bind_param("i", $id);
    $check->execute();

    $result = $check->get_result()->fetch_assoc();

    if ($result['total'] > 0) {
        echo "Cannot delete this part because it is already used in a service job.";
        echo "<br><br><a href='parts.php'>Go Back</a>";
        exit();
    }

    // DELETE PART
    $stmt = $conn->prepare(
        "DELETE FROM parts WHERE part_id = ?"
    );

    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        header("Location: parts.php");
        exit();
    } else {
        echo "Failed to delete part.";
    }

} else {
    header("Location: parts.php");
    exit();
}
?>
