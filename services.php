<?php
session_start();
include 'DatabaseConnection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$message = "";

if (isset($_POST['delete_job'])) {
    $job_id = (int) $_POST['job_id'];

    $stmt = $conn->prepare("DELETE FROM service_jobs WHERE job_id = ?");
    $stmt->bind_param("i", $job_id);

    if ($stmt->execute() && $stmt->affected_rows > 0) {
        header("Location: services.php?deleted=1");
        exit();
    } else {
        $message = "Failed to delete service job.";
    }
}

if (isset($_GET['deleted'])) {
    $message = "Service job deleted successfully!";
}

if (isset($_POST['add_job'])) {
    $vehicle_id = (int) $_POST['vehicle_id'];
    $mechanic_id = (int) $_POST['mechanic_id'];
    $service_date = $_POST['service_date'];
    $odometer = (int) $_POST['odometer'];
    $service_type = trim($_POST['service_type']);
    $description = trim($_POST['description']);
    $status = trim($_POST['status']);

    if ($vehicle_id > 0 && $mechanic_id > 0 && !empty($service_date) && strtotime($service_date) !== false && $odometer >= 0 && $service_type !== '') {
        $valid_date = date('Y-m-d', strtotime($service_date));

        $stmt = $conn->prepare(
            "INSERT INTO service_jobs (vehicle_id, mechanic_id, service_date, odometer, service_type, description, status)
             VALUES (?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->bind_param("iisisss", $vehicle_id, $mechanic_id, $valid_date, $odometer, $service_type, $description, $status);

        if ($stmt->execute()) {
            $conn->query("UPDATE vehicles SET odometer = $odometer WHERE vehicle_id = $vehicle_id AND odometer < $odometer");
            $message = "Service job added successfully!";
        } else {
            $message = "Failed to add service job.";
        }
    } else {
        $message = "Please enter valid service job information and a valid date.";
    }
}

$vehicles = $conn->query("SELECT vehicle_id, plate_number, make, model FROM vehicles ORDER BY plate_number ASC");
$mechanics = $conn->query("SELECT mechanic_id, name FROM mechanics ORDER BY name ASC");

$query = "SELECT sj.*, v.plate_number, v.make, v.model, m.name AS mechanic_name 
          FROM service_jobs sj
          LEFT JOIN vehicles v ON sj.vehicle_id = v.vehicle_id
          LEFT JOIN mechanics m ON sj.mechanic_id = m.mechanic_id
          ORDER BY sj.job_id DESC";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Service Job Management</title>
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
    <a href="vehicles.php">Vehicles</a>
    <a href="mechanics.php">Mechanics</a>
    <a href="services.php" class="active">Service Jobs</a>
    <a href="parts.php">Parts</a>
    <a href="maintenance.php">Maintenance</a>
    <a href="logout.php">Logout</a>
</div>

<div class="main">

    <div class="header-banner">
        <h2>Service Job Management</h2>
        <p>Manage and track all vehicle service jobs.</p>
    </div>

    <?php if ($message != "") { ?>
        <div class="alert <?php echo (strpos($message, 'successfully') !== false) ? 'alert-success' : ''; ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php } ?>

    <div class="card">
        <h3>Add Service Job</h3>
        <br>

        <form method="POST">
            <select name="vehicle_id" required>
                <option value="">Select Vehicle</option>
                <?php while ($v = $vehicles->fetch_assoc()) { ?>
                    <option value="<?php echo $v['vehicle_id']; ?>">
                        <?php echo htmlspecialchars($v['plate_number'] . ' - ' . $v['make'] . ' ' . $v['model']); ?>
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

            <input type="number" name="odometer" placeholder="Odometer Reading (KM)" min="0" required>

            <input type="text" name="service_type" placeholder="Service Type (e.g. Change Oil)" required>

            <input type="text" name="description" placeholder="Description">

            <select name="status" required>
                <option value="Pending">Pending</option>
                <option value="In Progress">In Progress</option>
                <option value="Completed">Completed</option>
            </select>

            <br><br>

            <button type="submit" name="add_job">
                Add Service Job
            </button>
        </form>
    </div>

    <br>

    <div class="card">
        <h3>Service Job Records</h3>
        <br>

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

            <?php while ($row = $result->fetch_assoc()) { 
                $formatted_date = ($row['service_date'] && strtotime($row['service_date']) > 0) 
                    ? date('Y-m-d', strtotime($row['service_date'])) 
                    : 'N/A';
            ?>
            <tr>
                <td><?php echo $row['job_id']; ?></td>
                <td><?php echo htmlspecialchars($row['plate_number'] . ' - ' . $row['make'] . ' ' . $row['model']); ?></td>
                <td><?php echo htmlspecialchars($row['mechanic_name'] ?? 'Unassigned'); ?></td>
                <td><?php echo $formatted_date; ?></td>
                <td><?php echo number_format($row['odometer']); ?> km</td>
                <td><?php echo htmlspecialchars($row['service_type']); ?></td>
                <td><?php echo htmlspecialchars($row['status']); ?></td>
                <td>
                    <a href="edit_job.php?id=<?php echo $row['job_id']; ?>" style="margin-right: 10px;">
                        Edit
                    </a>

                    <form method="POST" style="display:inline;"
                        onsubmit="return confirm('Are you sure you want to delete this service job?');">
                        <input type="hidden" name="job_id" value="<?php echo $row['job_id']; ?>">
                        <button type="submit" name="delete_job"
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