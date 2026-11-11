<?php
session_start();
include 'DatabaseConnection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$message = "";


if (isset($_POST['delete_schedule'])) {
    $schedule_id = (int) $_POST['schedule_id'];

    $stmt = $conn->prepare("
        DELETE FROM maintenance_schedules
        WHERE schedule_id = ?
    ");

    $stmt->bind_param("i", $schedule_id);

    if ($stmt->execute() && $stmt->affected_rows > 0) {
        header("Location: maintenance.php?deleted=1");
        exit();
    } else {
        $message = "Failed to delete maintenance schedule.";
    }
}

if (isset($_GET['deleted'])) {
    $message = "Maintenance schedule deleted successfully!";
}

// ADD MAINTENANCE SCHEDULE
if (isset($_POST['add_schedule'])) {
    $vehicle_id = (int) $_POST['vehicle_id'];
    $service_type = trim($_POST['service_type']);

    $interval_km = !empty($_POST['interval_km']) ? (int) $_POST['interval_km'] : null;
    $interval_days = !empty($_POST['interval_days']) ? (int) $_POST['interval_days'] : null;
    $next_due_odometer = !empty($_POST['next_due_odometer']) ? (int) $_POST['next_due_odometer'] : null;
    $next_due_date = !empty($_POST['next_due_date']) ? $_POST['next_due_date'] : null;

    if ($interval_km === null && $interval_days === null) {
        $message = "Please enter a KM or day interval.";
    } else {
        $stmt = $conn->prepare("
            INSERT INTO maintenance_schedules
            (vehicle_id, service_type, interval_km, interval_days,
             next_due_odometer, next_due_date, status)
            VALUES (?, ?, ?, ?, ?, ?, 'Upcoming')
        ");

        $stmt->bind_param(
            "isiiis",
            $vehicle_id,
            $service_type,
            $interval_km,
            $interval_days,
            $next_due_odometer,
            $next_due_date
        );

        if ($stmt->execute()) {
            $message = "Maintenance schedule added successfully!";
        } else {
            $message = "Failed to add maintenance schedule.";
        }
    }
}

// GET VEHICLES
$vehicles = $conn->query("
    SELECT vehicle_id, plate_number, make, model
    FROM vehicles
    ORDER BY vehicle_id DESC
");

// AUTOMATIC MAINTENANCE STATUS
$conn->query("
    UPDATE maintenance_schedules m
    JOIN vehicles v ON m.vehicle_id = v.vehicle_id
    SET m.status = CASE
        WHEN (
            (m.next_due_odometer IS NOT NULL
             AND v.odometer >= m.next_due_odometer)
            OR
            (m.next_due_date IS NOT NULL
             AND m.next_due_date <= CURDATE())
        )
        THEN 'Due'
        ELSE 'Upcoming'
    END
");

// GET MAINTENANCE RECORDS
$schedules = $conn->query("
    SELECT m.*, v.plate_number, v.odometer AS current_odometer
    FROM maintenance_schedules m
    JOIN vehicles v ON m.vehicle_id = v.vehicle_id
    ORDER BY m.schedule_id DESC
");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Maintenance Management</title>
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
    <a href="vehicles.php">Vehicles</a>
    <a href="mechanics.php">Mechanics</a>
    <a href="services.php">Service Jobs</a>
    <a href="parts.php">Parts</a>
    <a href="maintenance.php" class="active">Maintenance</a>
    <a href="logout.php">Logout</a>
</div>

<div class="main">

    <!-- Header styled like the Dashboard banner -->
    <div class="header-banner">
        <h2>Maintenance Management</h2>
        <p>Manage preventive maintenance schedules.</p>
    </div>

    <?php if ($message != "") { ?>
        <p><?php echo htmlspecialchars($message); ?></p>
    <?php } ?>

    <div class="card">
        <h3>Add Maintenance Schedule</h3>
        <br>

        <form method="POST">
            <select name="vehicle_id" required>
                <option value="">Select Vehicle</option>
                <?php while ($v = $vehicles->fetch_assoc()) { ?>
                    <option value="<?php echo $v['vehicle_id']; ?>">
                        <?php echo htmlspecialchars(
                            $v['plate_number'] . " - " .
                            $v['make'] . " " . $v['model']
                        ); ?>
                    </option>
                <?php } ?>
            </select>

            <input type="text" name="service_type" placeholder="Maintenance Type" required>
            <input type="number" name="interval_km" placeholder="Interval KM (Optional)" min="1">
            <input type="number" name="interval_days" placeholder="Interval Days (Optional)" min="1">
            <input type="number" name="next_due_odometer" placeholder="Next Due Odometer" min="0">
            <input type="date" name="next_due_date">

            <br><br>

            <button type="submit" name="add_schedule">
                Add Schedule
            </button>
        </form>
    </div>

    <br>

    <div class="card">
        <h3>Maintenance Records</h3>
        <br>

        <table width="100%" border="1" cellpadding="10">
            <tr>
                <th>ID</th>
                <th>Vehicle</th>
                <th>Service Type</th>
                <th>Interval KM</th>
                <th>Interval Days</th>
                <th>Next Due KM</th>
                <th>Next Due Date</th>
                <th>Status</th>
                <th>Action</th>
            </tr>

            <?php while ($row = $schedules->fetch_assoc()) { ?>
            <tr>
                <td><?php echo $row['schedule_id']; ?></td>
                <td><?php echo htmlspecialchars($row['plate_number']); ?></td>
                <td><?php echo htmlspecialchars($row['service_type']); ?></td>
                <td><?php echo $row['interval_km'] ?? '-'; ?></td>
                <td><?php echo $row['interval_days'] ?? '-'; ?></td>
                <td><?php echo $row['next_due_odometer'] ?? '-'; ?></td>
                <td><?php echo $row['next_due_date'] ?? '-'; ?></td>
                <td><?php echo htmlspecialchars($row['status']); ?></td>
                <td>
                    <a href="edit_maintenance.php?id=<?php echo $row['schedule_id']; ?>" style="margin-right: 10px;">Edit</a>
                    
                    <form method="POST" style="display:inline;"
                        onsubmit="return confirm('Are you sure you want to delete this maintenance schedule?');">
                        <input type="hidden" name="schedule_id" value="<?php echo $row['schedule_id']; ?>">
                        <button type="submit" name="delete_schedule"
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