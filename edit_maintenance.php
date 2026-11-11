
<?php
session_start();
include 'DatabaseConnection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: maintenance.php");
    exit();
}

$id = (int) $_GET['id'];

// UPDATE MAINTENANCE SCHEDULE
if (isset($_POST['update_schedule'])) {

    $service_type = $_POST['service_type'];
    $interval_km = !empty($_POST['interval_km']) ? $_POST['interval_km'] : null;
    $interval_days = !empty($_POST['interval_days']) ? $_POST['interval_days'] : null;
    $next_due_odometer = !empty($_POST['next_due_odometer']) ? $_POST['next_due_odometer'] : null;
    $next_due_date = !empty($_POST['next_due_date']) ? $_POST['next_due_date'] : null;

    if ($interval_km === null && $interval_days === null) {
        $message = "Please enter a KM or day interval.";
    } else {

        $stmt = $conn->prepare("
            UPDATE maintenance_schedules
            SET service_type = ?,
                interval_km = ?,
                interval_days = ?,
                next_due_odometer = ?,
                next_due_date = ?
            WHERE schedule_id = ?
        ");

        $stmt->bind_param(
            "siiisi",
            $service_type,
            $interval_km,
            $interval_days,
            $next_due_odometer,
            $next_due_date,
            $id
        );

        if ($stmt->execute()) {
            header("Location: maintenance.php?updated=1");
            exit();
        } else {
            $message = "Failed to update schedule.";
        }
    }
}

// GET SELECTED RECORD
$stmt = $conn->prepare("
    SELECT * FROM maintenance_schedules
    WHERE schedule_id = ?
");

$stmt->bind_param("i", $id);
$stmt->execute();

$schedule = $stmt->get_result()->fetch_assoc();

if (!$schedule) {
    die("Maintenance schedule not found.");
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Maintenance Schedule</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="sidebar">
    <h2>Vehicle System</h2>
    <a href="dashboard.php">Dashboard</a>
    <a href="vehicles.php">Vehicles</a>
    <a href="mechanics.php">Mechanics</a>
    <a href="services.php">Service Jobs</a>
    <a href="parts.php">Parts</a>
    <a href="maintenance.php" class="active">Maintenance</a>
    <a href="logout.php">Logout</a>
</div>

<div class="main">

    <div class="header">
        <h2>Edit Maintenance Schedule</h2>
        <p>Update your maintenance information.</p>
    </div>

    <?php if (isset($message)) { ?>
        <p><?php echo htmlspecialchars($message); ?></p>
    <?php } ?>

    <div class="card">

        <form method="POST">

            <label>Maintenance Type</label>
            <input type="text" name="service_type"
                value="<?php echo htmlspecialchars($schedule['service_type']); ?>"
                required>

            <label>Interval KM</label>
            <input type="number" name="interval_km"
                value="<?php echo htmlspecialchars($schedule['interval_km'] ?? ''); ?>"
                min="1">

            <label>Interval Days</label>
            <input type="number" name="interval_days"
                value="<?php echo htmlspecialchars($schedule['interval_days'] ?? ''); ?>"
                min="1">

            <label>Next Due Odometer</label>
            <input type="number" name="next_due_odometer"
                value="<?php echo htmlspecialchars($schedule['next_due_odometer'] ?? ''); ?>"
                min="0">

            <label>Next Due Date</label>
            <input type="date" name="next_due_date"
                value="<?php echo htmlspecialchars($schedule['next_due_date'] ?? ''); ?>">

            <br><br>

            <button type="submit" name="update_schedule">
                Update Schedule
            </button>

            <a href="maintenance.php">Cancel</a>

        </form>

    </div>

</div>

</body>
</html>
