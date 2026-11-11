<?php
session_start();
include 'DatabaseConnection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$error = "";
$success = "";

// DELETE SERVICE JOB
if (isset($_POST['delete_service'])) {
    $job_id = (int) $_POST['job_id'];

    $stmt = $conn->prepare("DELETE FROM service_jobs WHERE job_id = ?");
    $stmt->bind_param("i", $job_id);

    if ($stmt->execute()) {
        header("Location: services.php?deleted=1");
        exit();
    } else {
        $error = "Failed to delete service job.";
    }
}

if (isset($_GET['deleted'])) {
    $success = "Service job deleted successfully!";
}

// ADD SERVICE JOB
if (isset($_POST['add_service'])) {
    $vehicle_id = (int) $_POST['vehicle_id'];
    $mechanic_id = (int) $_POST['mechanic_id'];
    $service_date = $_POST['service_date'];
    $odometer = (int) $_POST['odometer'];
    $service_type = trim($_POST['service_type']);
    $description = trim($_POST['description']);
    $status = $_POST['status'];

    $stmt = $conn->prepare("INSERT INTO service_jobs 
        (vehicle_id, mechanic_id, service_date, odometer, service_type, description, status)
        VALUES (?, ?, ?, ?, ?, ?, ?)");

    $stmt->bind_param(
        "iiissss",
        $vehicle_id,
        $mechanic_id,
        $service_date,
        $odometer,
        $service_type,
        $description,
        $status
    );

    if ($stmt->execute()) {
        header("Location: services.php?success=1");
        exit();
    } else {
        $error = "Failed to add service job.";
    }
}

if (isset($_GET['success'])) {
    $success = "Service job added successfully!";
}

$vehicles = $conn->query("SELECT * FROM vehicles");
$mechanics = $conn->query("SELECT * FROM mechanics");

$services = $conn->query("
    SELECT service_jobs.*, vehicles.plate_number,
           vehicles.make, vehicles.model, mechanics.name AS mechanic_name
    FROM service_jobs
    INNER JOIN vehicles ON service_jobs.vehicle_id = vehicles.vehicle_id
    INNER JOIN mechanics ON service_jobs.mechanic_id = mechanics.mechanic_id
    ORDER BY service_jobs.job_id DESC
");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Service Jobs</title>
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
    <h1>Service Job Management</h1>

    <?php if ($error != "") { ?>
        <p style="color: red;"><?php echo htmlspecialchars($error); ?></p>
    <?php } ?>

    <?php if ($success != "") { ?>
        <p style="color: green;"><?php echo htmlspecialchars($success); ?></p>
    <?php } ?>

    <h2>Add Service Job</h2>

    <?php if ($vehicles->num_rows > 0 && $mechanics->num_rows > 0) { ?>
    <form method="POST">
        <select name="vehicle_id" required>
            <option value="">Select Vehicle</option>
            <?php while ($v = $vehicles->fetch_assoc()) { ?>
                <option value="<?php echo $v['vehicle_id']; ?>">
                    <?php echo htmlspecialchars($v['plate_number'] . " - " . $v['make'] . " " . $v['model']); ?>
                </option>
            <?php } ?>
        </select>

        <select name="mechanic_id" required>
            <option value="">Select Mechanic</option>
            <?php while ($m = $mechanics->fetch_assoc()) { ?>
                <option value="<?php echo $m['mechanic_id']; ?>">
                    <?php echo htmlspecialchars($m['name']); ?>
                </option>
            <?php } ?>
        </select>

        <input type="date" name="service_date" required>
        <input type="number" name="odometer" placeholder="Odometer Reading" min="0" required>
        <input type="text" name="service_type" placeholder="Service Type" required>
        <input type="text" name="description" placeholder="Description">

        <select name="status" required>
            <option value="Pending">Pending</option>
            <option value="In Progress">In Progress</option>
            <option value="Completed">Completed</option>
        </select>

        <button type="submit" name="add_service">Add Service Job</button>
    </form>
    <?php } else { ?>
        <p>Please add at least one vehicle and one mechanic first.</p>
    <?php } ?>

    <h2>Service Job Records</h2>

    <table width="100%" border="1" cellpadding="10">
        <tr>
            <th>ID</th>
            <th>Vehicle</th>
            <th>Mechanic</th>
            <th>Date</th>
            <th>Odometer</th>
            <th>Service Type</th>
            <th>Status</th>
            <th>Action</th>
        </tr>

        <?php while ($row = $services->fetch_assoc()) { ?>
        <tr>
            <td><?php echo $row['job_id']; ?></td>
            <td><?php echo htmlspecialchars($row['plate_number'] . " - " . $row['make'] . " " . $row['model']); ?></td>
            <td><?php echo htmlspecialchars($row['mechanic_name']); ?></td>
            <td><?php echo $row['service_date']; ?></td>
            <td><?php echo $row['odometer']; ?></td>
            <td><?php echo htmlspecialchars($row['service_type']); ?></td>
            <td><?php echo htmlspecialchars($row['status']); ?></td>
            <td>
                <a href="edit_service.php?id=<?php echo $row['job_id']; ?>" style="margin-right: 10px;">Edit</a>

                <form method="POST" style="display:inline;"
                    onsubmit="return confirm('Are you sure you want to delete this service job?');">
                    <input type="hidden" name="job_id" value="<?php echo $row['job_id']; ?>">
                    <button type="submit" name="delete_service"
                        style="background-color: #dc3545; color: white; border: none; padding: 5px 10px; cursor: pointer;">
                        Delete
                    </button>
                </form>
            </td>
        </tr>
        <?php } ?>
    </table>
</div>

</body>
</html>