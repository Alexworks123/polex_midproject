
<?php
session_start();
include 'DatabaseConnection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$id = $_GET['id'];

$stmt = $conn->prepare("SELECT * FROM vehicles WHERE vehicle_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$vehicle = $stmt->get_result()->fetch_assoc();

if (!$vehicle) {
    die("Vehicle not found.");
}

if (isset($_POST['update'])) {
    $plate = $_POST['plate_number'];
    $make = $_POST['make'];
    $model = $_POST['model'];
    $year = $_POST['year'];
    $odometer = $_POST['odometer'];

    $stmt = $conn->prepare("UPDATE vehicles SET plate_number=?, make=?, model=?, year=?, odometer=? WHERE vehicle_id=?");
    $stmt->bind_param("sssiii", $plate, $make, $model, $year, $odometer, $id);

    if ($stmt->execute()) {
        header("Location: vehicles.php");
        exit();
    }

    $error = "Update failed. Check the plate number.";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Vehicle</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<div class="main">
    <div class="card">
        <h2>Edit Vehicle</h2><br>

        <?php if (isset($error)) echo htmlspecialchars($error); ?>

        <form method="POST">
            <input type="text" name="plate_number" value="<?php echo htmlspecialchars($vehicle['plate_number']); ?>" required>
            <input type="text" name="make" value="<?php echo htmlspecialchars($vehicle['make']); ?>" required>
            <input type="text" name="model" value="<?php echo htmlspecialchars($vehicle['model']); ?>" required>
            <input type="number" name="year" value="<?php echo htmlspecialchars($vehicle['year'] ?? ''); ?>">
            <input type="number" name="odometer" value="<?php echo $vehicle['odometer']; ?>" min="0" required>

            <br><br>
            <button type="submit" name="update">Update Vehicle</button>
            <a href="vehicles.php">Cancel</a>
        </form>
    </div>
</div>

</body>
</html>
