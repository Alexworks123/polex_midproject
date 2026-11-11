<?php
session_start();
include 'DatabaseConnection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$error = "";
$success = "";
$job_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($job_id <= 0) {
    header("Location: services.php");
    exit();
}

// UPDATE SERVICE JOB
if (isset($_POST['update_service'])) {
    $vehicle_id = (int) $_POST['vehicle_id'];
    $mechanic_id = (int) $_POST['mechanic_id'];
    $service_date = $_POST['service_date'];
    $odometer = (int) $_POST['odometer'];
    $service_type = trim($_POST['service_type']);
    $description = trim($_POST['description']);
    $status = $_POST['status'];

    $stmt = $conn->prepare("
        UPDATE service_jobs 
        SET vehicle_id = ?, mechanic_id = ?, service_date = ?, odometer = ?, service_type = ?, description = ?, status = ?
        WHERE job_id = ?
    ");

    $stmt->bind_param(
        "iiissssi",
        $vehicle_id,
        $mechanic_id,
        $service_date,
        $odometer,
        $service_type,
        $description,
        $status,
        $job_id
    );

    if ($stmt->execute()) {
        header("Location: services.php?updated=1");
        exit();
    } else {
        $error = "Failed to update service job.";
    }
}

// FETCH EXISTING RECORD
$stmt = $conn->prepare("SELECT * FROM service_jobs WHERE job_id = ?");
$stmt->bind_param("i", $job_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: services.php");
    exit();
}

$job = $result->fetch_assoc();

// FETCH DROPDOWN DATA
$vehicles = $conn->query("SELECT * FROM vehicles");
$mechanics = $conn->query("SELECT * FROM mechanics");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Service Job</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="sidebar">
    <h2>Vehicle System</h2>
    <a href="dashboard.php">Dashboard</a>
    <a href="vehicles.php">Vehicles</a>
    <a href="mechanics.php">Mechanics</a>
    <a href="services.php" class="active">Service Jobs</a>
    <a href="parts.php">Parts</a>
    <a href="maintenance.php">Maintenance</a>
    <a href="logout.php">Logout</a>
</div>

<div class="main">
    <h1>Edit Service Job</h1>

    <?php if ($error != "") { ?>
        <p style="color: red;"><?php echo htmlspecialchars($error); ?></p>
    <?php } ?>

    <div class="card">
        <form method="POST">
            <label>Vehicle:</label>
            <select name="vehicle_id" required>
                <option value="">Select Vehicle</option>
                <?php while ($v = $vehicles->fetch_assoc()) { ?>
                    <option value="<?php echo $v['vehicle_id']; ?>" <?php echo ($v['vehicle_id'] == $job['vehicle_id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($v['plate_number'] . " - " . $v['make'] . " " . $v['model']); ?>
                    </option>
                <?php } ?>
            </select>

            <label>Mechanic:</label>
            <select name="mechanic_id" required>
                <option value="">Select Mechanic</option>
                <?php while ($m = $mechanics->fetch_assoc()) { ?>
                    <option value="<?php echo $m['mechanic_id']; ?>" <?php echo ($m['mechanic_id'] == $job['mechanic_id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($m['name']); ?>
                    </option>
                <?php } ?>
            </select>

            <label>Service Date:</label>
            <input type="date" name="service_date" value="<?php echo $job['service_date']; ?>" required>

            <label>Odometer Reading:</label>
            <input type="number" name="odometer" value="<?php echo $job['odometer']; ?>" min="0" required>

            <label>Service Type:</label>
            <input type="text" name="service_type" value="<?php echo htmlspecialchars($job['service_type']); ?>" required>

            <label>Description:</label>
            <input type="text" name="description" value="<?php echo htmlspecialchars($job['description']); ?>">

            <label>Status:</label>
            <select name="status" required>
                <option value="Pending" <?php echo ($job['status'] == 'Pending') ? 'selected' : ''; ?>>Pending</option>
                <option value="In Progress" <?php echo ($job['status'] == 'In Progress') ? 'selected' : ''; ?>>In Progress</option>
                <option value="Completed" <?php echo ($job['status'] == 'Completed') ? 'selected' : ''; ?>>Completed</option>
            </select>

            <br><br>
            <button type="submit" name="update_service">Update Service Job</button>
            <a href="services.php" style="margin-left: 10px;">Cancel</a>
        </form>
    </div>
</div>

</body>
</html>