
<?php
session_start();
include 'DatabaseConnection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$id = $_GET['id'];

$stmt = $conn->prepare("SELECT * FROM mechanics WHERE mechanic_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$mechanic = $stmt->get_result()->fetch_assoc();

if (!$mechanic) {
    die("Mechanic not found!");
}

if (isset($_POST['update'])) {
    $name = $_POST['name'];
    $phone = $_POST['phone'];

    $stmt = $conn->prepare("UPDATE mechanics SET name=?, phone=? WHERE mechanic_id=?");
    $stmt->bind_param("ssi", $name, $phone, $id);

    if ($stmt->execute()) {
        header("Location: mechanics.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Mechanic</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="main">
    <div class="card">
        <h2>Edit Mechanic</h2><br>

        <form method="POST">
            <input type="text" name="name"
                value="<?php echo htmlspecialchars($mechanic['name']); ?>" required>

            <input type="text" name="phone"
                value="<?php echo htmlspecialchars($mechanic['phone'] ?? ''); ?>">

            <br><br>
            <button type="submit" name="update">Update Mechanic</button>
            <a href="mechanics.php">Cancel</a>
        </form>
    </div>
</div>

</body>
</html>
