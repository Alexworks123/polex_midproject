
<?php
session_start();
include 'DatabaseConnection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$message = "";


if (isset($_POST['add_job_part'])) {

    $job_id = (int) $_POST['job_id'];
    $part_id = (int) $_POST['part_id'];
    $quantity = (int) $_POST['quantity'];

    if ($job_id <= 0 || $part_id <= 0 || $quantity <= 0) {
        $message = "Please enter valid information.";
    } else {

        try {
            $conn->begin_transaction();

            // CHECK SERVICE JOB
            $jobCheck = $conn->prepare(
                "SELECT job_id FROM service_jobs WHERE job_id = ?"
            );

            $jobCheck->bind_param("i", $job_id);
            $jobCheck->execute();

            if (!$jobCheck->get_result()->fetch_assoc()) {
                throw new Exception("Service job not found.");
            }

            // GET PART AND LOCK INVENTORY ROW
            $partCheck = $conn->prepare(
                "SELECT quantity, unit_cost
                 FROM parts
                 WHERE part_id = ?
                 FOR UPDATE"
            );

            $partCheck->bind_param("i", $part_id);
            $partCheck->execute();

            $part = $partCheck->get_result()->fetch_assoc();

            if (!$part) {
                throw new Exception("Part not found.");
            }

            // CHECK AVAILABLE STOCK
            if ($quantity > $part['quantity']) {
                throw new Exception("Not enough available stock!");
            }

            $unit_cost = $part['unit_cost'];

            // SAVE PART TO SERVICE JOB
            $stmt = $conn->prepare(
                "INSERT INTO job_parts
                 (job_id, part_id, quantity, unit_cost)
                 VALUES (?, ?, ?, ?)"
            );

            $stmt->bind_param(
                "iiid",
                $job_id,
                $part_id,
                $quantity,
                $unit_cost
            );

            $stmt->execute();

            // DEDUCT INVENTORY
            $update = $conn->prepare(
                "UPDATE parts
                 SET quantity = quantity - ?
                 WHERE part_id = ?"
            );

            $update->bind_param("ii", $quantity, $part_id);
            $update->execute();

            $conn->commit();

            $message = "Part successfully added to service job!";

        } catch (Throwable $e) {

            $conn->rollback();
            $message = $e->getMessage();
        }
    }
}

// GET SERVICE JOBS
$jobs = $conn->query("
    SELECT sj.job_id, sj.service_date, sj.service_type,
           v.plate_number, v.make, v.model
    FROM service_jobs sj
    JOIN vehicles v ON sj.vehicle_id = v.vehicle_id
    ORDER BY sj.job_id DESC
");

// GET AVAILABLE PARTS
$parts = $conn->query("
    SELECT part_id, part_name, quantity, unit_cost
    FROM parts
    WHERE quantity > 0
    ORDER BY part_name ASC
");

// GET PARTS USED IN SERVICE JOBS
$records = $conn->query("
    SELECT jp.quantity, jp.unit_cost,
           sj.job_id, sj.service_date,
           p.part_name, v.plate_number
    FROM job_parts jp
    JOIN service_jobs sj ON jp.job_id = sj.job_id
    JOIN parts p ON jp.part_id = p.part_id
    JOIN vehicles v ON sj.vehicle_id = v.vehicle_id
    ORDER BY jp.job_part_id DESC
");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Parts to Service Job</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="sidebar">
    <h2>Vehicle System</h2>

    <a href="dashboard.php">Dashboard</a>
    <a href="vehicles.php">Vehicles</a>
    <a href="mechanics.php">Mechanics</a>
    <a href="services.php">Service Jobs</a>
    <a href="parts.php" class="active">Parts</a>
    <a href="maintenance.php">Maintenance</a>
    <a href="logout.php">Logout</a>
</div>

<div class="main">

    <div class="header">
        <h2>Parts Used in Service Jobs</h2>
        <p>Assign vehicle parts to service records.</p>
    </div>

    <?php if ($message != "") { ?>
        <p><?php echo htmlspecialchars($message); ?></p>
        <br>
    <?php } ?>

    <div class="card">

        <h3>Add Part to Service Job</h3>
        <br>

        <form method="POST">

            <label>Select Service Job</label>

            <select name="job_id" required>
                <option value="">Select Service Job</option>

                <?php while ($job = $jobs->fetch_assoc()) { ?>

                    <option value="<?php echo $job['job_id']; ?>">
                        <?php echo htmlspecialchars(
                            "Job #" . $job['job_id'] . " - " .
                            $job['plate_number'] . " - " .
                            $job['service_type']
                        ); ?>
                    </option>

                <?php } ?>
            </select>

            <br><br>

            <label>Select Part</label>

            <select name="part_id" required>
                <option value="">Select Part</option>

                <?php while ($part = $parts->fetch_assoc()) { ?>

                    <option value="<?php echo $part['part_id']; ?>">
                        <?php echo htmlspecialchars(
                            $part['part_name'] .
                            " - Stock: " . $part['quantity'] .
                            " - ₱" . number_format($part['unit_cost'], 2)
                        ); ?>
                    </option>

                <?php } ?>
            </select>

            <br><br>

            <label>Quantity Used</label>

            <input
                type="number"
                name="quantity"
                placeholder="Enter Quantity"
                min="1"
                required
            >

            <br><br>

            <button type="submit" name="add_job_part">
                Add Part to Job
            </button>

        </form>

    </div>

    <br>

    <div class="card">

        <h3>Parts Used Records</h3>
        <br>

        <table width="100%" border="1" cellpadding="10">

            <tr>
                <th>Job ID</th>
                <th>Vehicle</th>
                <th>Part Name</th>
                <th>Quantity</th>
                <th>Unit Cost</th>
                <th>Total Cost</th>
                <th>Date</th>
            </tr>

            <?php while ($row = $records->fetch_assoc()) { ?>

            <tr>

                <td><?php echo $row['job_id']; ?></td>

                <td>
                    <?php echo htmlspecialchars($row['plate_number']); ?>
                </td>

                <td>
                    <?php echo htmlspecialchars($row['part_name']); ?>
                </td>

                <td><?php echo $row['quantity']; ?></td>

                <td>
                    ₱<?php echo number_format($row['unit_cost'], 2); ?>
                </td>

                <td>
                    ₱<?php echo number_format(
                        $row['quantity'] * $row['unit_cost'], 2
                    ); ?>
                </td>

                <td><?php echo $row['service_date']; ?></td>

            </tr>

            <?php } ?>

        </table>

    </div>

</div>

</body>
</html>
