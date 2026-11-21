<?php
session_start();

if (!isset($_SESSION["student_id"])) {
    header("Location: index.php");
    exit();
}

require_once "config/database.php";

$student_id = $_SESSION["student_id"];

$sql = "SELECT id, fullname, username, email, created_at
        FROM students
        WHERE id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();

$student = $result->fetch_assoc();

if (!$student) {
    session_destroy();
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="dashboard-body">

<div class="dashboard-container">

    <aside class="sidebar">

        <div class="sidebar-logo">
            <h2>Student Portal</h2>
        </div>

        <nav class="sidebar-menu">

            <p class="menu-title">MAIN MENU</p>

            <a href="dashboard.php" class="menu-link">
                Dashboard
            </a>

            <a href="midterm.php" class="menu-link">
                Midterm Activities
            </a>

            <a href="finals.php" class="menu-link">
                Finals Activities
            </a>

            <a href="submissions.php" class="menu-link">
                My Submissions
            </a>

            <p class="menu-title">ACCOUNT</p>

            <a href="profile.php" class="menu-link active">
                My Profile
            </a>

            <a href="logout.php" class="menu-link logout-link">
                Logout
            </a>

        </nav>

        <div class="sidebar-footer">
            <p>Student Activity Portal</p>
        </div>

    </aside>

    <main class="main-content">

        <div class="topbar">
            <div>
                <h1>My Profile</h1>
                <p>View your personal account information.</p>
            </div>
        </div>

        <div class="dashboard-card">

            <h2>Student Information</h2>

            <div style="margin-top:25px;">

                <div style="margin-bottom:20px;">
                    <p style="color:#94a3b8; margin-bottom:6px;">Full Name</p>
                    <h3><?= htmlspecialchars($student["fullname"]) ?></h3>
                </div>

                <div style="margin-bottom:20px;">
                    <p style="color:#94a3b8; margin-bottom:6px;">Username</p>
                    <h3><?= htmlspecialchars($student["username"]) ?></h3>
                </div>

                <div style="margin-bottom:20px;">
                    <p style="color:#94a3b8; margin-bottom:6px;">Email Address</p>
                    <h3><?= htmlspecialchars($student["email"]) ?></h3>
                </div>

                <div style="margin-bottom:20px;">
                    <p style="color:#94a3b8; margin-bottom:6px;">Student ID</p>
                    <h3><?= htmlspecialchars($student["id"]) ?></h3>
                </div>

                <div style="margin-bottom:20px;">
                    <p style="color:#94a3b8; margin-bottom:6px;">Account Created</p>
                    <h3><?= htmlspecialchars($student["created_at"]) ?></h3>
                </div>

            </div>

        </div>

    </main>

</div>

</body>
</html>

<?php
$stmt->close();
$conn->close();
?>
