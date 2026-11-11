
<?php
session_start();
include 'DatabaseConnection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// GET PART ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid Part ID!");
}

$id = (int) $_GET['id'];

// GET PART INFORMATION
$stmt = $conn->prepare("SELECT * FROM parts WHERE part_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();

$part = $stmt->get_result()->fetch_assoc();

if (!$part) {
    die("Part not found!");
}

// UPDATE PART
if (isset($_POST['update'])) {

    $part_name = trim($_POST['part_name']);
    $quantity = (int) $_POST['quantity'];
    $unit_cost = (float) $_POST['unit_cost'];

    if ($part_name !== '' && $quantity >= 0 && $unit_cost >= 0) {

        $update = $conn->prepare(
            "UPDATE parts
             SET part_name = ?, quantity = ?, unit_cost = ?
             WHERE part_id = ?"
        );

        $update->bind_param(
            "sidi",
            $part_name,
            $quantity,
            $unit_cost,
            $id
        );

        if ($update->execute()) {
            header("Location: parts.php");
            exit();
        } else {
            $error = "Failed to update part.";
        }

    } else {
        $error = "Please enter valid information.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Part</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="main">

    <div class="card">

        <h2>Edit Part</h2>
        <br>

        <?php if (isset($error)) { ?>
            <p><?php echo htmlspecialchars($error); ?></p>
        <?php } ?>

        <form method="POST">

            <input
                type="text"
                name="part_name"
                placeholder="Part Name"
                value="<?php echo htmlspecialchars($part['part_name']); ?>"
                required
            >

            <input
                type="number"
                name="quantity"
                placeholder="Available Quantity"
                min="0"
                value="<?php echo $part['quantity']; ?>"
                required
            >

            <input
                type="number"
                name="unit_cost"
                placeholder="Unit Cost"
                min="0"
                step="0.01"
                value="<?php echo $part['unit_cost']; ?>"
                required
            >

            <br><br>

            <button type="submit" name="update">
                Update Part
            </button>

            <a href="parts.php">Cancel</a>

        </form>

    </div>

</div>

</body>
</html>
