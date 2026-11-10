
<?php
session_start();
include 'DatabaseConnection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (isset($_POST['add_vehicle'])) {
    $plate = $_POST['plate_number'];
    $make = $_POST['make'];
    $model = $_POST['model'];
    $year = $_POST['year'];
    $odometer = $_POST['odometer'];
   

    $stmt = $conn->prepare("INSERT INTO vehicles (plate_number, make, model, year, odometer) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssii", $plate, $make, $model, $year, $odometer);

    if ($stmt->execute()) {
        $message = "Vehicle added successfully!";
    } else {
        $message = "Failed to add vehicle. Plate number may already exist.";
    }
}

$result = $conn->query("SELECT * FROM vehicles ORDER BY vehicle_id DESC");
?>

<!DOCTYPE html>
<html>
<head>
    <th>Action</th>
    <title>Vehicle Management</title>
 <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="sidebar">
    <h2>Vehicle Maintenance</h2>
    <a href="dashboard.php">Dashboard</a>
    <a href="vehicles.php" class="active">Vehicles</a>
    <a href="mechanics.php">Mechanics</a>
    <a href="services.php">Service Jobs</a>
    <a href="parts.php">Parts</a>
    <a href="maintenance.php">Maintenance</a>
    <a href="logout.php">Logout</a>
</div>

<div class="main">

    <div class="header">
        <h2>Vehicle Management</h2>
        <p>Manage your registered vehicles.</p>
    </div>

    <?php if (isset($message)) echo "<p>" . htmlspecialchars($message) . "</p><br>"; ?>

    <div class="card">
        <h3>Add New Vehicle</h3><br>

        <form method="POST">
            <input type="text" name="plate_number" placeholder="Plate Number / Fleet ID" required>
            <input type="text" name="make" placeholder="Vehicle Make" required>
            <input type="text" name="model" placeholder="Vehicle Model" required>
            <input type="number" name="year" placeholder="Year" min="1900" max="2100">
            <input type="number" name="odometer" placeholder="Odometer (KM)" min="0" value="0" required>
            <br><br>
            <button type="submit" name="add_vehicle">Add Vehicle</button>
        </form>
    </div>

    <br>

    <div class="card">
        <h3>Vehicle List</h3><br>

        <table width="100%" border="1" cellpadding="10">
            <tr>
                <th>ID</th>
                <th>Plate Number</th>
                <th>Make</th>
                <th>Model</th>
                <th>Year</th>
                <th>Odometer</th>
            </tr>

            <?php while ($row = $result->fetch_assoc()) { ?>
            <tr>
                <td><?php echo $row['vehicle_id']; ?></td>
                <td><?php echo htmlspecialchars($row['plate_number']); ?></td>
                <td><?php echo htmlspecialchars($row['make']); ?></td>
                <td><?php echo htmlspecialchars($row['model']); ?></td>
                <td><?php echo htmlspecialchars($row['year'] ?? ''); ?></td>
                <td><?php echo number_format($row['odometer']); ?> km</td>
                <td>
    <a href="edit_vehicle.php?id=<?php echo $row['vehicle_id']; ?>">Edit</a>
</td>

<form method="POST" action="delete_vehicle.php"
      onsubmit="return confirm('Are you sure you want to delete this vehicle?');">

    <input type="hidden" name="vehicle_id"
           value="<?php echo $row['vehicle_id']; ?>">

    <button type="submit" name="delete">Delete</button>

</form>

            </tr>
            <?php } ?>
        </table>
    </div>

</div>
</body>
</html>
