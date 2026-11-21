<?php
session_start();
require_once "config/database.php";

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $username = trim($_POST["username"]);
    $password = $_POST["password"];

    $stmt = $conn->prepare(
        "SELECT id, fullname, username, password
         FROM students
         WHERE username = ?"
    );

    $stmt->bind_param("s", $username);
    $stmt->execute();

    $result = $stmt->get_result();

    if ($result->num_rows === 1) {

        $student = $result->fetch_assoc();

        if (password_verify($password, $student["password"])) {

            session_regenerate_id(true);

            $_SESSION["student_id"] = $student["id"];
            $_SESSION["fullname"] = $student["fullname"];
            $_SESSION["username"] = $student["username"];

            header("Location: dashboard.php");
            exit();

        } else {
            $message = "Incorrect username or password!";
        }

    } else {
        $message = "Incorrect username or password!";
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Activity Portal | Login</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<div class="login-container">

    <div class="login-box">

        <div class="logo">
            <div class="logo-icon">S</div>
            <h1>Student Portal</h1>
            <p>Activity Submission System</p>
        </div>

        <?php if ($message !== ""): ?>
            <div class="message error">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">

            <div class="input-group">
                <label>Username</label>
                <input type="text" name="username"
                       placeholder="Enter your username" required>
            </div>

            <div class="input-group">
                <label>Password</label>
                <input type="password" name="password"
                       placeholder="Enter your password" required>
            </div>

            <button type="submit" class="login-btn">
                Sign In
            </button>

        </form>

        <div class="register-link">
            Don't have an account?
            <a href="register.php">Create Account</a>
        </div>

        <div class="footer">
            © 2026 Student Activity Portal
        </div>

    </div>

</div>

</body>
</html>
