
<?php
session_start();

if (!isset($_SESSION["student_id"])) {
    header("Location: index.php");
    exit();
}

require_once "config/database.php";

$student_id = (int) $_SESSION["student_id"];
$message = "";
$messageType = "";

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["delete_submission"])) {

    $submission_id = filter_input(INPUT_POST, "submission_id", FILTER_VALIDATE_INT);

    if (!$submission_id) {
        $message = "Invalid submission.";
        $messageType = "error";
    } else {

        $conn->begin_transaction();

        try {
            $deleteStmt = $conn->prepare(
                "SELECT id, activity_id, file_path
                 FROM submissions
                 WHERE id = ? AND student_id = ?
                 FOR UPDATE"
            );

            $deleteStmt->bind_param("ii", $submission_id, $student_id);
            $deleteStmt->execute();

            $submission = $deleteStmt->get_result()->fetch_assoc();
            $deleteStmt->close();

            if (!$submission) {
                throw new Exception("Submission not found.");
            }

            $activity_id = (int) $submission["activity_id"];
            $file_path = $submission["file_path"];

            $removeStmt = $conn->prepare(
                "DELETE FROM submissions
                 WHERE id = ? AND student_id = ?"
            );

            $removeStmt->bind_param("ii", $submission_id, $student_id);
            $removeStmt->execute();

            if ($removeStmt->affected_rows !== 1) {
                throw new Exception("Unable to delete submission.");
            }

            $removeStmt->close();

            $checkStmt = $conn->prepare(
                "SELECT COUNT(*) AS total
                 FROM submissions
                 WHERE activity_id = ?"
            );

            $checkStmt->bind_param("i", $activity_id);
            $checkStmt->execute();

            $remaining = $checkStmt->get_result()->fetch_assoc();
            $checkStmt->close();

            if ((int) $remaining["total"] === 0) {
                $activityStmt = $conn->prepare(
                    "DELETE FROM activities WHERE id = ?"
                );

                $activityStmt->bind_param("i", $activity_id);
                $activityStmt->execute();
                $activityStmt->close();
            }

            $conn->commit();

            if ($file_path !== "") {
                $baseDirectory = realpath(__DIR__ . "/uploads");
                $fullPath = realpath(__DIR__ . "/" . $file_path);

                if (
                    $baseDirectory !== false &&
                    $fullPath !== false &&
                    strpos($fullPath, $baseDirectory . DIRECTORY_SEPARATOR) === 0 &&
                    is_file($fullPath)
                ) {
                    unlink($fullPath);
                }
            }

            $message = "Submission deleted successfully!";
            $messageType = "success";

        } catch (Exception $e) {
            $conn->rollback();
            $message = $e->getMessage();
            $messageType = "error";
        }
    }
}

$sql = "SELECT submissions.id, activities.title, activities.term,
               submissions.file_name, submissions.submitted_at
        FROM submissions
        INNER JOIN activities
        ON submissions.activity_id = activities.id
        WHERE submissions.student_id = ?
        ORDER BY submissions.submitted_at DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Submissions</title>
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

            <a href="submissions.php" class="menu-link active">
                My Submissions
            </a>

            <p class="menu-title">ACCOUNT</p>

            <a href="profile.php" class="menu-link">
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
                <h1>My Submissions</h1>
                <p>View and manage your uploaded activities.</p>
            </div>
        </div>

        <div class="dashboard-card">

            <h2>Submission History</h2>

            <?php if ($message !== ""): ?>
                <div class="message <?= htmlspecialchars($messageType) ?>">
                    <?= htmlspecialchars($message) ?>
                </div>
            <?php endif; ?>

            <div style="overflow-x:auto;">

                <table style="width:100%; border-collapse:collapse; margin-top:20px;">

                    <thead>
                        <tr>
                            <th style="text-align:left; padding:12px;">Activity</th>
                            <th style="text-align:left; padding:12px;">Term</th>
                            <th style="text-align:left; padding:12px;">File Name</th>
                            <th style="text-align:left; padding:12px;">Date Submitted</th>
                            <th style="text-align:center; padding:12px;">Action</th>
                        </tr>
                    </thead>

                    <tbody>

                    <?php if ($result->num_rows > 0): ?>

                        <?php while ($row = $result->fetch_assoc()): ?>

                            <tr>
                                <td style="padding:12px;">
                                    <?= htmlspecialchars($row["title"]) ?>
                                </td>

                                <td style="padding:12px;">
                                    <?= htmlspecialchars($row["term"]) ?>
                                </td>

                                <td style="padding:12px;">
                                    <?= htmlspecialchars($row["file_name"]) ?>
                                </td>

                                <td style="padding:12px;">
                                    <?= htmlspecialchars($row["submitted_at"]) ?>
                                </td>

                                <td style="padding:12px; text-align:center;">

                                    <form method="POST"
                                          onsubmit="return confirm('Are you sure you want to delete this submission? This action cannot be undone.');">

                                        <input type="hidden"
                                               name="submission_id"
                                               value="<?= (int) $row["id"] ?>">

                                        <button type="submit"
                                                name="delete_submission"
                                                value="1"
                                                style="background:#dc2626; color:white; border:none; padding:9px 15px; border-radius:6px; cursor:pointer;">
                                            Delete
                                        </button>

                                    </form>

                                </td>
                            </tr>

                        <?php endwhile; ?>

                    <?php else: ?>

                        <tr>
                            <td colspan="5" style="padding:20px; text-align:center;">
                                No submissions yet.
                            </td>
                        </tr>

                    <?php endif; ?>

                    </tbody>

                </table>

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
