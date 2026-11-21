<?php
require_once "config/database.php";

$message = "";
$messageType = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $fullname = trim($_POST["fullname"]);
    $username = trim($_POST["username"]);
    $email = trim($_POST["email"]);
    $password = $_POST["password"];
    $confirm_password = $_POST["confirm_password"];

    if ($password !== $confirm_password) {

        $message = "Passwords do not match!";
        $messageType = "error";

    } elseif (strlen($password) < 8) {

        $message = "Password must be at least 8 characters!";
        $messageType = "error";

    } else {

        $check = $conn->prepare(
            "SELECT id FROM students WHERE username = ? OR email = ?"
        );

        $check->bind_param("ss", $username, $email);
        $check->execute();
        $result = $check->get_result();

        if ($result->num_rows > 0) {

            $message = "Username or email already exists!";
            $messageType = "error";

        } else {

            $hashedPassword = password_hash(
                $password,
                PASSWORD_DEFAULT
            );

            $stmt = $conn->prepare(
                "INSERT INTO students (fullname, username, email, password)
                 VALUES (?, ?, ?, ?)"
            );

            $stmt->bind_param(
                "ssss",
                $fullname,
                $username,
                $email,
                $hashedPassword
            );

            if ($stmt->execute()) {
                $message = "Registration successful! You can now login.";
                $messageType = "success";
            } else {
                $message = "Registration failed. Please try again.";
                $messageType = "error";
            }

            $stmt->close();
        }

        $check->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Registration</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<div class="login-container">

    <div class="login-box">

        <div class="logo">
            <div class="logo-icon">S</div>
            <h1>Create Account</h1>
            <p>Join the Student Activity Portal</p>
        </div>

        <?php if ($message !== ""): ?>
            <div class="message <?php echo $messageType; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">

            <div class="input-group">
                <label>Full Name</label>
                <input type="text" name="fullname"
                       placeholder="Enter your full name"
                       maxlength="150" required>
            </div>

            <div class="input-group">
                <label>Username</label>
                <input type="text" name="username"
                       placeholder="Create a username"
                       maxlength="100" required>
            </div>

            <div class="input-group">
                <label>Email Address</label>
                <input type="email" name="email"
                       placeholder="Enter your email"
                       maxlength="150" required>
            </div>

            <div class="input-group">
                <label>Password</label>
                <input type="password" name="password"
                       placeholder="Create a password"
                       minlength="8" required>
            </div>

            <div class="input-group">
                <label>Confirm Password</label>
                <input type="password" name="confirm_password"
                       placeholder="Confirm your password"
                       minlength="8" required>
            </div>

            <button type="submit" class="login-btn">
                Create Account
            </button>

        </form>

        <div class="register-link">
            Already have an account?
            <a href="index.php">Sign In</a>
        </div>

        <div class="footer">
            © 2026 Student Activity Portal
        </div>

    </div>

</div>

</body>
</html>
