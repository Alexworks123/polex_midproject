<?php
session_start();
include 'DatabaseConnection.php';

$error = "";

if (isset($_POST['login'])) {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ? AND role = 'operator'");
    $stmt->bind_param("s", $email);
    $stmt->execute();

    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user['password'])) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['name'] = $user['name'];

            header("Location: dashboard.php");
            exit();
        }
    }

    $error = "Invalid email or password!";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Operator Login - Vehicle System</title>
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
        .login-card {
            background: white;
            width: 100%;
            max-width: 450px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            overflow: hidden;
            box-sizing: border-box;
        }
        /* Header Banner with background image and text overlay */
        .login-header-banner {
            position: relative;
            background: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.6)), url('https://images.unsplash.com/photo-1555215695-3004980ad54e?auto=format&fit=crop&w=800&q=80') center/cover no-repeat;
            padding: 40px 35px;
            color: white;
        }
        .login-header-banner h2 {
            margin: 0;
            margin-bottom: 5px;
            font-size: 26px;
            color: white;
        }
        .login-header-banner p.subtitle {
            margin: 0;
            font-size: 14px;
            color: #e0e0e0;
        }
        .login-content {
            padding: 30px 35px;
        }
        .login-card input[type="email"],
        .login-card input[type="password"] {
            width: 100%;
            padding: 12px 15px;
            margin-bottom: 20px;
            border: 1px solid #ddd;
            border-radius: 6px;
            box-sizing: border-box;
            font-size: 15px;
        }
        .login-card button {
            width: 100%;
            padding: 12px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
        }
        .login-card button:hover {
            background-color: #0056b3;
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

<div class="login-card">
    <!-- Header banner with background image containing the titles -->
    <div class="login-header-banner">
        <h2>Operator Login</h2>
        <p class="subtitle">Vehicle Maintenance System</p>
    </div>

    <!-- Form content area -->
    <div class="login-content">
        <?php if ($error != "") { ?>
            <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
        <?php } ?>

        <form method="POST">
            <input type="email" name="email" placeholder="Email" required><br>
            <input type="password" name="password" placeholder="Password" required><br>
            <button type="submit" name="login">Login</button>
        </form>

        <p style="margin-top: 25px; text-align: center; font-size: 14px; color: #555;">
            Don't have an account? <a href="register.php" style="color: #007bff; text-decoration: none; font-weight: bold;">Register</a>
        </p>
    </div>
</div>

</body>
</html>