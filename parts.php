<?php
session_start();
include 'DatabaseConnection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$message = "";

// DELETE PART (Optional self-contained deletion or via external script)
if (isset($_POST['delete_part'])) {
    $part_id = (int) $_POST['part_id'];

    $stmt = $conn->prepare("DELETE FROM parts WHERE part_id = ?");
    $stmt->bind_param("i", $part_id);

    if ($stmt->execute() && $stmt->affected_rows > 0) {
        header("Location: parts.php?deleted=1");
        exit();
    } else {
        $message = "Failed to delete part.";
    }
}

if (isset($_GET['deleted'])) {
    $message = "Part deleted successfully!";
}

// ADD NEW PART
if (isset($_POST['add_part'])) {
    $part_name = trim($_POST['part_name']);
    $quantity = (int) $_POST['quantity'];
    $unit_cost = (float) $_POST['unit_cost'];

    if ($part_name !== '' && $quantity >= 0 && $unit_cost >= 0) {
        $stmt = $conn->prepare(
            "INSERT INTO parts (part_name, quantity, unit_cost)
             VALUES (?, ?, ?)"
        );

        $stmt->bind_param("sid", $part_name, $quantity, $unit_cost);

        if ($stmt->execute()) {
            $message = "Part added successfully!";
        } else {
            $message = "Failed to add part.";
        }
    } else {
        $message = "Please enter valid part information.";
    }
}

// GET ALL PARTS
$result = $conn->query("SELECT * FROM parts ORDER BY part_id DESC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Parts Management</title>
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
    <a href="parts.php" class="active">Parts</a>
    <a href="maintenance.php">Maintenance</a>
    <a href="logout.php">Logout</a>
</div>

<div class="main">

    <!-- Header styled like the Dashboard banner -->
    <div class="header-banner">
        <h2>Parts Management</h2>
        <p>Manage vehicle parts and inventory.</p>
    </div>

    <?php if ($message != "") { ?>
        <p><?php echo htmlspecialchars($message); ?></p>
    <?php } ?>

    <div class="card">
        <h3>Add New Part</h3>
        <br>

        <form method="POST">
            <input
                type="text"
                name="part_name"
                placeholder="Part Name"
                required
            >

            <input
                type="number"
                name="quantity"
                placeholder="Available Quantity"
                min="0"
                required
            >

            <input
                type="number"
                name="unit_cost"
                placeholder="Unit Cost"
                min="0"
                step="0.01"
                required
            >

            <br><br>

            <button type="submit" name="add_part">
                Add Part
            </button>
        </form>
    </div>

    <br>

    <div class="card">
        <h3>Parts Inventory</h3>
        <br>

        <table width="100%" border="1" cellpadding="10">
            <tr>
                <th>ID</th>
                <th>Part Name</th>
                <th>Available Quantity</th>
                <th>Unit Cost</th>
                <th>Total Value</th>
                <th>Action</th>
            </tr>

            <?php while ($row = $result->fetch_assoc()) { ?>
            <tr>
                <td><?php echo $row['part_id']; ?></td>
                <td><?php echo htmlspecialchars($row['part_name']); ?></td>
                <td><?php echo $row['quantity']; ?></td>
                <td>₱<?php echo number_format($row['unit_cost'], 2); ?></td>
                <td>₱<?php echo number_format($row['quantity'] * $row['unit_cost'], 2); ?></td>
                <td>
                    <a href="edit_part.php?id=<?php echo $row['part_id']; ?>" style="margin-right: 10px;">
                        Edit
                    </a>

                    <form method="POST" style="display:inline;"
                        onsubmit="return confirm('Are you sure you want to delete this part?');">
                        <input type="hidden" name="part_id" value="<?php echo $row['part_id']; ?>">
                        <button type="submit" name="delete_part"
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