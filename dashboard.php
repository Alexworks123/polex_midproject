
<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
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
        <h2>Welcome, <?php echo htmlspecialchars($_SESSION['name']); ?>!</h2>
        <p>Operator Dashboard</p>
    </div>

    <div class="cards">

        <div class="card">
            <h3>Total Vehicles</h3>
            <p>0</p>
        </div>

        <div class="card">
            <h3>Total Mechanics</h3>
            <p>0</p>
        </div>

        <div class="card">
            <h3>Service Jobs</h3>
            <p>0</p>
        </div>

        <div class="card">
            <h3>Due Maintenance</h3>
            <p>0</p>
        </div>

    </div>

</div>

</body>
</html>
