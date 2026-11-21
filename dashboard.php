<?php
session_start();
if (!isset($_SESSION["student_id"])) {
    header("Location: index.php");
    exit();
}
require_once "config/database.php";

$student_id = (int) $_SESSION["student_id"];
$fullname = htmlspecialchars($_SESSION["fullname"]);

$stmt = $conn->prepare(
    "SELECT 
        COUNT(s.id) AS total,
        SUM(CASE WHEN LOWER(a.term) = 'midterm' THEN 1 ELSE 0 END) AS midterm,
        SUM(CASE WHEN LOWER(a.term) = 'finals' THEN 1 ELSE 0 END) AS finals
     FROM submissions s
     INNER JOIN activities a ON s.activity_id = a.id
     WHERE s.student_id = ?"
);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$stats = $stmt->get_result()->fetch_assoc();
$stmt->close();

$totalCount   = (int) ($stats["total"] ?? 0);
$midtermCount = (int) ($stats["midterm"] ?? 0);
$finalsCount  = (int) ($stats["finals"] ?? 0);

$recentStmt = $conn->prepare(
    "SELECT a.title, a.term, s.file_name, s.submitted_at
     FROM submissions s
     INNER JOIN activities a ON s.activity_id = a.id
     WHERE s.student_id = ?
     ORDER BY s.submitted_at DESC LIMIT 5"
);
$recentStmt->bind_param("i", $student_id);
$recentStmt->execute();
$recentList = $recentStmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="dashboard-body">
<div class="dashboard-container">
    <aside class="sidebar">
        <div class="sidebar-logo">
            <div class="logo-icon">S</div>
            <div><h2>Student Portal</h2><p>Activity System</p></div>
        </div>
        <div class="sidebar-menu">
            <p class="menu-title">MAIN MENU</p>
            <a href="dashboard.php" class="menu-link active"><span>▦</span> Dashboard</a>
            <a href="midterm.php" class="menu-link"><span>▤</span> Midterm Activities</a>
            <a href="finals.php" class="menu-link"><span>▤</span> Finals Activities</a>
            <a href="submissions.php" class="menu-link"><span>▣</span> My Submissions</a>
            <p class="menu-title">ACCOUNT</p>
            <a href="profile.php" class="menu-link"><span>◉</span> My Profile</a>
            <a href="logout.php" class="menu-link logout-link"><span>↪</span> Logout</a>
        </div>
        <div class="sidebar-footer">Student Activity Portal<br><span>Version 1.0</span></div>
    </aside>

    <main class="main-content">
        <header class="topbar">
            <div><h2>Dashboard</h2><p>Welcome to your student portal.</p></div>
            <div class="user-info">
                <div class="user-avatar">S</div>
                <div><strong><?= $fullname ?></strong><small>Student Account</small></div>
            </div>
        </header>

        <section class="welcome-card">
            <div>
                <h1>Hello, <?= $fullname ?>!</h1>
                <p>Welcome back! Manage your activities and monitor your submissions here.</p>
            </div>
            <div class="welcome-icon">🎓</div>
        </section>

        <section class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon blue">▤</div>
                <div><p>Total Activities</p><h2><?= $totalCount ?></h2><small>Available activities</small></div>
            </div>
            <div class="stat-card">
                <div class="stat-icon purple">◷</div>
                <div><p>Midterm Activities</p><h2><?= $midtermCount ?></h2><small>Midterm requirements</small></div>
            </div>
            <div class="stat-card">
                <div class="stat-icon orange">▣</div>
                <div><p>Finals Activities</p><h2><?= $finalsCount ?></h2><small>Final requirements</small></div>
            </div>
            <div class="stat-card">
                <div class="stat-icon green">✓</div>
                <div><p>My Submissions</p><h2><?= $totalCount ?></h2><small>Activities submitted</small></div>
            </div>
        </section>

        <section class="content-card recent-card">
            <div class="section-heading">
                <h3>Recent Submissions</h3>
                <a href="submissions.php">View All</a>
            </div>
            <?php if ($recentList->num_rows > 0): ?>
                <table style="width:100%; border-collapse:collapse; margin-top:15px;">
                    <thead>
                        <tr>
                            <th style="text-align:left; padding:10px;">Activity</th>
                            <th style="text-align:left; padding:10px;">Term</th>
                            <th style="text-align:left; padding:10px;">File Name</th>
                            <th style="text-align:left; padding:10px;">Date Submitted</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $recentList->fetch_assoc()): ?>
                            <tr style="border-top:1px solid #eee;">
                                <td style="padding:10px;"><?= htmlspecialchars($row["title"]) ?></td>
                                <td style="padding:10px;"><?= htmlspecialchars($row["term"]) ?></td>
                                <td style="padding:10px;"><?= htmlspecialchars($row["file_name"]) ?></td>
                                <td style="padding:10px;"><?= htmlspecialchars($row["submitted_at"]) ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty-state">
                    <div>📂</div>
                    <h4>No Submissions Yet</h4>
                    <p>Your uploaded activities will appear here.</p>
                </div>
            <?php endif; ?>
        </section>

        <footer class="dashboard-footer">© 2026 Student Activity Portal. All Rights Reserved.</footer>
    </main>
</div>
</body>
</html>
