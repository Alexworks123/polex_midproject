<?php
session_start();
include 'DatabaseConnection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$operator_name = isset($_SESSION['name']) ? $_SESSION['name'] : 'Operator';

$vehicle_result = $conn->query("SELECT COUNT(*) AS total FROM vehicles");
$total_vehicles = $vehicle_result->fetch_assoc()['total'] ?? 0;


$mechanic_result = $conn->query("SELECT COUNT(*) AS total FROM mechanics");
$total_mechanics = $mechanic_result->fetch_assoc()['total'] ?? 0;

$service_result = $conn->query("SELECT COUNT(*) AS total FROM service_jobs");
$total_services = $service_result->fetch_assoc()['total'] ?? 0;

$maintenance_result = $conn->query("SELECT COUNT(*) AS total FROM maintenance_schedules WHERE status = 'Due'");
$total_due_maintenance = $maintenance_result->fetch_assoc()['total'] ?? 0;
?>

<!DOCTYPE html>
<html>
<head>
    <title>Vehicle Maintenance Dashboard</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="sidebar">
    <h2>Vehicle Maintenance</h2>

    <a href="dashboard.php" class="active">Dashboard</a>
    <a href="vehicles.php">Vehicles</a>
    <a href="mechanics.php">Mechanics</a>
    <a href="services.php">Service Jobs</a>
    <a href="parts.php">Parts</a>
    <a href="maintenance.php">Maintenance</a>
    <a href="logout.php">Logout</a>
</div>

<div class="main">

    <div class="header">
        <h2>Welcome, <?php echo htmlspecialchars($operator_name); ?>!</h2>
        <p>Operator Dashboard</p>
    </div>

    <div class="cards">

        <div class="card">
            <h3>Total Vehicles</h3>
            <p><?php echo number_format($total_vehicles); ?></p>
        </div>

        <div class="card">
            <h3>Total Mechanics</h3>
            <p><?php echo number_format($total_mechanics); ?></p>
        </div>

        <div class="card">
            <h3>Service Jobs</h3>
            <p><?php echo number_format($total_services); ?></p>
        </div>

        <div class="card">
            <h3>Due Maintenance</h3>
            <p><?php echo number_format($total_due_maintenance); ?></p>
        </div>

    </div>

</div>

</body>
</html>