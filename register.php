<?php
include 'DatabaseConnection.php';

$message = "";

if (isset($_POST['register'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if ($name !== '' && $email !== '' && $password !== '') {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'operator')");
        $stmt->bind_param("sss", $name, $email, $password_hash);

        if ($stmt->execute()) {
            header("Location: login.php?registered=1");
            exit();
        } else {
            $message = "Registration failed. Email may already exist.";
        }
    } else {
        $message = "Please fill in all required fields.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Operator Registration - Vehicle System</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            background-color: #f4f6f9;
            font-family: Arial, sans-serif;
        }
        .register-card {
            background: white;
            width: 100%;
            max-width: 450px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            overflow: hidden;
            box-sizing: border-box;
        }
        /* Top Ferrari Image Header Banner */
        .register-header-banner {
            position: relative;
            background: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.6)), url('https://images.unsplash.com/photo-1583121274602-3e2820c69888?auto=format&fit=crop&w=800&q=80') center/cover no-repeat;
            padding: 40px 35px;
            color: white;
        }
        .register-header-banner h2 {
            margin: 0;
            margin-bottom: 5px;
            font-size: 26px;
            color: white;
        }
        .register-header-banner p.subtitle {
            margin: 0;
            font-size: 14px;
            color: #e0e0e0;
        }
        .register-content {
            padding: 30px 35px;
        }
        .register-card input[type="text"],
        .register-card input[type="email"],
        .register-card input[type="password"] {
            width: 100%;
            padding: 12px 15px;
            margin-bottom: 20px;
            border: 1px solid #ddd;
            border-radius: 6px;
            box-sizing: border-box;
            font-size: 15px;
        }
        .register-card button {
            width: 100%;
            padding: 12px;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
        }
        .register-card button:hover {
            background-color: #218838;
        }
        .error-message {
            color: #dc3545;
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            padding: 10px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-size: 14px;
            text-align: center;
        }
    </style>
</head>
<body>

<div class="register-card">
    <!-- Header banner with a Ferrari background image containing the titles -->
    <div class="register-header-banner">
        <h2>Operator Registration</h2>
        <p class="subtitle">Vehicle Maintenance System</p>
    </div>

    <!-- Form content area -->
    <div class="register-content">
        <?php if ($message != "") { ?>
            <div class="error-message"><?php echo htmlspecialchars($message); ?></div>
        <?php } ?>

        <form method="POST">
            <input type="text" name="name" placeholder="Full Name" required><br>
            <input type="email" name="email" placeholder="Email" required><br>
            <input type="password" name="password" placeholder="Password" required><br>
            <button type="submit" name="register">Register</button>
        </form>

        <p style="margin-top: 25px; text-align: center; font-size: 14px; color: #555;">
            Already have an account? <a href="login.php" style="color: #007bff; text-decoration: none; font-weight: bold;">Login</a>
        </p>
    </div>
</div>

</body>
</html>