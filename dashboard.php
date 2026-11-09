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
</head>
<body>

    <h1>Vehicle Maintenance System</h1>

    <h2>Welcome, <?php echo htmlspecialchars($_SESSION['name']); ?>!</h2>

    <p>You are logged in as an Operator.</p>

    <hr>

    <h3>Dashboard</h3>
    <p>Welcome to the Vehicle Preventive Maintenance and Service Management System.</p>

    <a href="logout.php">Logout</a>

</body>
</html>