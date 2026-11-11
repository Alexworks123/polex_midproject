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
    <style>
        /* Custom color styling for the header area you highlighted */
        .main .header {
            background: linear-gradient(135deg, #1e293b, #0f172a);
            color: white;
            padding: 25px 30px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            margin-bottom: 25px;
            border-bottom: 4px solid #3b82f6;
        }
        .main .header h2 {
            margin: 0 0 5px 0;
            color: #ffffff;
            font-size: 24px;
        }
        .main .header p {
            margin: 0;
            color: #94a3b8;
            font-size: 14px;
        }
    </style>
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