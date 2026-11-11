<?php
session_start();
include 'DatabaseConnection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$message = "";

// DELETE VEHICLE
if (isset($_POST['delete_vehicle'])) {
    $vehicle_id = (int) $_POST['vehicle_id'];

    $stmt = $conn->prepare("DELETE FROM vehicles WHERE vehicle_id = ?");
    $stmt->bind_param("i", $vehicle_id);

    if ($stmt->execute()) {
        header("Location: vehicles.php?deleted=1");
        exit();
    } else {
        $message = "Failed to delete vehicle. It may be linked to service jobs or maintenance schedules.";
    }
}

if (isset($_GET['deleted'])) {
    $message = "Vehicle deleted successfully!";
}

// ADD VEHICLE
if (isset($_POST['add_vehicle'])) {
    $plate = trim($_POST['plate_number']);
    $make = trim($_POST['make']);
    $model = trim($_POST['model']);
    $year = !empty($_POST['year']) ? (int) $_POST['year'] : null;
    $odometer = (int) $_POST['odometer'];

    if ($plate !== '' && $make !== '' && $model !== '') {
        $stmt = $conn->prepare("INSERT INTO vehicles (plate_number, make, model, year, odometer) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssii", $plate, $make, $model, $year, $odometer);

        if ($stmt->execute()) {
            $message = "Vehicle added successfully!";
        } else {
            $message = "Failed to add vehicle. Plate number may already exist.";
        }
    } else {
        $message = "Please fill in all required fields.";
    }
}

$result = $conn->query("SELECT * FROM vehicles ORDER BY vehicle_id DESC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Vehicle Management</title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* Header Banner Style (matching Dashboard design) */
        .header-banner {
            background-color: #10192d; /* Dark blue/navy shade */
            color: #ffffff;
            padding: 24px 30px;
            border-radius: 12px;
            margin-bottom: 24px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .header-banner h2 {
            margin: 0;
            font-size: 24px;
            font-weight: 700;
            color: #ffffff;
        }

        .header-banner p {
            margin: 6px 0 0 0;
            font-size: 14px;
            color: #94a3b8; /* Muted gray text */
        }
    </style>
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

    <!-- Header styled like the Dashboard banner -->
    <div class="header-banner">
        <h2>Vehicle Management</h2>
        <p>Manage your registered vehicles.</p>
    </div>

    <?php if ($message != "") { ?>
        <p><?php echo htmlspecialchars($message); ?></p><br>
    <?php } ?>

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
                <th>Action</th>
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
                    <a href="edit_vehicle.php?id=<?php echo $row['vehicle_id']; ?>" style="margin-right: 10px;">Edit</a>

                    <form method="POST" style="display:inline;"
                        onsubmit="return confirm('Are you sure you want to delete this vehicle?');">
                        <input type="hidden" name="vehicle_id" value="<?php echo $row['vehicle_id']; ?>">
                        <button type="submit" name="delete_vehicle"
                            style="background-color: #dc3545; color: white; border: none; padding: 5px 10px; cursor: pointer;">
                            Delete
                        </button>
                    </form>
                </td>
            </tr>
            <?php } ?>
        </table>
    </div>

</div>
</body>
</html>