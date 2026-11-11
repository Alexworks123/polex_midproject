<?php
session_start();
include 'DatabaseConnection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$message = "";

if (isset($_POST['delete_mechanic'])) {
    $mechanic_id = (int) $_POST['mechanic_id'];

    $stmt = $conn->prepare("DELETE FROM mechanics WHERE mechanic_id = ?");
    $stmt->bind_param("i", $mechanic_id);

    if ($stmt->execute() && $stmt->affected_rows > 0) {
        header("Location: mechanics.php?deleted=1");
        exit();
    } else {
        $message = "Failed to delete mechanic.";
    }
}

if (isset($_GET['deleted'])) {
    $message = "Mechanic deleted successfully!";
}

if (isset($_POST['add_mechanic'])) {
    $name = trim($_POST['name']);
    $phone = trim($_POST['phone']);

    if ($name !== '' && $phone !== '') {
        $stmt = $conn->prepare("INSERT INTO mechanics (name, phone) VALUES (?, ?)");
        $stmt->bind_param("ss", $name, $phone);

        if ($stmt->execute()) {
            $message = "Mechanic added successfully!";
        } else {
            $message = "Failed to add mechanic.";
        }
    } else {
        $message = "Please enter valid mechanic details.";
    }
}

$result = $conn->query("SELECT * FROM mechanics ORDER BY mechanic_id DESC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Mechanic Management</title>
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
    <a href="mechanics.php" class="active">Mechanics</a>
    <a href="services.php">Service Jobs</a>
    <a href="parts.php">Parts</a>
    <a href="maintenance.php">Maintenance</a>
    <a href="logout.php">Logout</a>
</div>

<div class="main">

    <div class="header-banner">
        <h2>Mechanic Management</h2>
        <p>Manage your registered mechanics.</p>
    </div>

    <?php if ($message != "") { ?>
        <div class="alert <?php echo (strpos($message, 'successfully') !== false) ? 'alert-success' : ''; ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php } ?>

    <div class="card">
        <h3>Add New Mechanic</h3>
        <br>

        <form method="POST">
            <input
                type="text"
                name="name"
                placeholder="Mechanic Name"
                required
            >

            <input
                type="text"
                name="phone"
                placeholder="Phone Number"
                required
            >

            <br><br>

            <button type="submit" name="add_mechanic">
                Add Mechanic
            </button>
        </form>
    </div>

    <br>

    <div class="card">
        <h3>Mechanic List</h3>
        <br>

        <table width="100%" border="1" cellpadding="10">
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Phone</th>
                <th>Action</th>
            </tr>

            <?php while ($row = $result->fetch_assoc()) { ?>
            <tr>
                <td><?php echo $row['mechanic_id']; ?></td>
                <td><?php echo htmlspecialchars($row['name']); ?></td>
                <td><?php echo htmlspecialchars($row['phone']); ?></td>
                <td>
                    <a href="edit_mechanic.php?id=<?php echo $row['mechanic_id']; ?>" style="margin-right: 10px;">
                        Edit
                    </a>

                    <form method="POST" style="display:inline;"
                        onsubmit="return confirm('Are you sure you want to delete this mechanic?');">
                        <input type="hidden" name="mechanic_id" value="<?php echo $row['mechanic_id']; ?>">
                        <button type="submit" name="delete_mechanic"
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