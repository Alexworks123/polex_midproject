<?php
session_start();
include 'DatabaseConnection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$message = "";

if (isset($_POST['delete_vehicle'])) {
    $vehicle_id = (int) $_POST['vehicle_id'];

    $stmt = $conn->prepare("DELETE FROM vehicles WHERE vehicle_id = ?");
    $stmt->bind_param("i", $vehicle_id);

    if ($stmt->execute() && $stmt->affected_rows > 0) {
        header("Location: vehicles.php?deleted=1");
        exit();
    } else {
        $message = "Failed to delete vehicle.";
    }
}

if (isset($_GET['deleted'])) {
    $message = "Vehicle deleted successfully!";
}

if (isset($_POST['add_vehicle'])) {
    $plate_number = trim($_POST['plate_number']);
    $make = trim($_POST['make']);
    $model = trim($_POST['model']);
    $year = (int) $_POST['year'];
    $odometer = (int) $_POST['odometer'];

    if ($plate_number !== '' && $make !== '' && $model !== '' && $year > 1900 && $odometer >= 0) {
        $check_stmt = $conn->prepare("SELECT vehicle_id FROM vehicles WHERE plate_number = ?");
        $check_stmt->bind_param("s", $plate_number);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();

        if ($check_result->num_rows > 0) {
            $message = "Error: Plate Number / Fleet ID already exists! (BR1 Violation)";
        } else {
            $stmt = $conn->prepare(
                "INSERT INTO vehicles (plate_number, make, model, year, odometer)
                 VALUES (?, ?, ?, ?, ?)"
            );
            $stmt->bind_param("sssii", $plate_number, $make, $model, $year, $odometer);

            if ($stmt->execute()) {
                $message = "Vehicle added successfully!";
            } else {
                $message = "Failed to add vehicle.";
            }
        }
    } else {
        $message = "Please enter valid vehicle information.";
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
        .header-banner {
            background-color: #10192d;
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
            color: #94a3b8;
        }

        .alert {
            padding: 12px 20px;
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
            border-radius: 6px;
            margin-bottom: 20px;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border-color: #c3e6cb;
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

    <div class="header-banner">
        <h2>Vehicle Management</h2>
        <p>Manage your registered vehicles.</p>
    </div>

    <?php if ($message != "") { ?>
        <div class="alert <?php echo (strpos($message, 'successfully') !== false) ? 'alert-success' : ''; ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php } ?>

    <div class="card">
        <h3>Add New Vehicle</h3>
        <br>

        <form method="POST">
            <input
                type="text"
                name="plate_number"
                placeholder="Plate Number / Fleet ID"
                required
            >

            <input
                type="text"
                name="make"
                placeholder="Vehicle Make"
                required
            >

            <input
                type="text"
                name="model"
                placeholder="Vehicle Model"
                required
            >

            <input
                type="number"
                name="year"
                placeholder="Year"
                min="1900"
                max="2099"
                required
            >

            <input
                type="number"
                name="odometer"
                placeholder="Odometer (KM)"
                min="0"
                required
            >

            <br><br>

            <button type="submit" name="add_vehicle">
                Add Vehicle
            </button>
        </form>
    </div>

    <br>

    <div class="card">
        <h3>Vehicle List</h3>
        <br>

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
                <td><?php echo $row['year']; ?></td>
                <td><?php echo number_format($row['odometer']); ?> km</td>
                <td>
                    <a href="edit_vehicle.php?id=<?php echo $row['vehicle_id']; ?>" style="margin-right: 10px;">
                        Edit
                    </a>

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