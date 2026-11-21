<?php
session_start();
require_once "config/database.php";

if (!isset($_SESSION["student_id"])) {
    header("Location: index.php");
    exit();
}

$message = "";
$messageType = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $title = trim($_POST["title"]);
    $description = trim($_POST["description"]);
    $studentId = $_SESSION["student_id"];

    $allowedExtensions = [
        "pdf", "doc", "docx", "ppt", "pptx",
        "xls", "xlsx", "jpg", "jpeg", "png", "zip"
    ];

    if (
        empty($title) ||
        !isset($_FILES["activity_file"]) ||
        $_FILES["activity_file"]["error"] !== UPLOAD_ERR_OK
    ) {
        $message = "Please complete all required fields and select a file.";
        $messageType = "error";

    } elseif ($_FILES["activity_file"]["size"] > 10 * 1024 * 1024) {

        $message = "File size must not exceed 10MB.";
        $messageType = "error";

    } else {

        $originalName = $_FILES["activity_file"]["name"];
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

        $allowedMimeTypes = [
            "pdf" => ["application/pdf"],
            "doc" => ["application/msword"],
            "docx" => ["application/vnd.openxmlformats-officedocument.wordprocessingml.document"],
            "ppt" => ["application/vnd.ms-powerpoint"],
            "pptx" => ["application/vnd.openxmlformats-officedocument.presentationml.presentation"],
            "xls" => ["application/vnd.ms-excel"],
            "xlsx" => ["application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"],
            "jpg" => ["image/jpeg"],
            "jpeg" => ["image/jpeg"],
            "png" => ["image/png"],
            "zip" => ["application/zip", "application/x-zip-compressed"]
        ];

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($_FILES["activity_file"]["tmp_name"]);

        if (
            !in_array($extension, $allowedExtensions) ||
            !in_array($mimeType, $allowedMimeTypes[$extension] ?? [])
        ) {

            $message = "Invalid file type. Please upload a supported file.";
            $messageType = "error";

        } else {

            $uploadDirectory = "uploads/finals/";

            if (!is_dir($uploadDirectory)) {
                mkdir($uploadDirectory, 0755, true);
            }

            $newFileName = bin2hex(random_bytes(16)) . "." . $extension;
            $filePath = $uploadDirectory . $newFileName;

            if (move_uploaded_file(
                $_FILES["activity_file"]["tmp_name"],
                $filePath
            )) {

                $conn->begin_transaction();

                try {

                    $term = "Finals";

                    $activityStmt = $conn->prepare(
                        "INSERT INTO activities (title, description, term)
                         VALUES (?, ?, ?)"
                    );

                    $activityStmt->bind_param(
                        "sss",
                        $title,
                        $description,
                        $term
                    );

                    $activityStmt->execute();

                    $activityId = $conn->insert_id;

                    $submissionStmt = $conn->prepare(
                        "INSERT INTO submissions
                         (student_id, activity_id, file_name, file_path)
                         VALUES (?, ?, ?, ?)"
                    );

                    $submissionStmt->bind_param(
                        "iiss",
                        $studentId,
                        $activityId,
                        $originalName,
                        $filePath
                    );

                    $submissionStmt->execute();

                    $conn->commit();

                    $message = "Finals activity uploaded successfully!";
                    $messageType = "success";

                    $activityStmt->close();
                    $submissionStmt->close();

                } catch (Exception $e) {

                    $conn->rollback();

                    if (file_exists($filePath)) {
                        unlink($filePath);
                    }

                    $message = "Upload failed. Please try again.";
                    $messageType = "error";
                }

            } else {

                $message = "Unable to save uploaded file.";
                $messageType = "error";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Finals Activities</title>

    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="dashboard-body">

<div class="dashboard-container">

    <aside class="sidebar">

        <div class="sidebar-logo">
            <div class="logo-icon">S</div>
            <div>
                <h2>Student Portal</h2>
                <p>Activity System</p>
            </div>
        </div>

        <div class="sidebar-menu">

            <p class="menu-title">MAIN MENU</p>

            <a href="dashboard.php" class="menu-link">
                <span>▦</span> Dashboard
            </a>

            <a href="midterm.php" class="menu-link">
                <span>▤</span> Midterm Activities
            </a>

            <a href="finals.php" class="menu-link active">
                <span>▤</span> Finals Activities
            </a>

            <a href="submissions.php" class="menu-link">
                <span>▣</span> My Submissions
            </a>

            <p class="menu-title">ACCOUNT</p>

            <a href="profile.php" class="menu-link">
                <span>◉</span> My Profile
            </a>

            <a href="logout.php" class="menu-link logout-link">
                <span>↪</span> Logout
            </a>

        </div>

        <div class="sidebar-footer">
            Student Activity Portal<br>
            <span>Version 1.0</span>
        </div>

    </aside>

    <main class="main-content">

        <header class="topbar">
            <div>
                <h2>Finals Activities</h2>
                <p>Upload and manage your final requirements.</p>
            </div>

            <div class="user-info">
                <div class="user-avatar">S</div>
                <div>
                    <strong><?php echo htmlspecialchars($_SESSION["fullname"]); ?></strong>
                    <small>Student Account</small>
                </div>
            </div>
        </header>

        <section class="welcome-card">
            <div>
                <h1>Final Requirements</h1>
                <p>Submit all your activities for the Finals period.</p>
            </div>

            <div class="welcome-icon">📝</div>
        </section>

        <section class="content-card upload-card">

            <div class="section-heading">
                <h3>Upload Finals Activity</h3>
            </div>

            <?php if ($message !== ""): ?>
                <div class="message <?php echo $messageType; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data">

                <div class="input-group">
                    <label>Activity Title</label>

                    <input
                        type="text"
                        name="title"
                        placeholder="Example: Finals Activity 1"
                        maxlength="150"
                        required
                    >
                </div>

                <div class="input-group">
                    <label>Description</label>

                    <textarea
                        name="description"
                        placeholder="Enter activity description..."
                        rows="4"
                    ></textarea>
                </div>

                <div class="input-group">
                    <label>Choose Activity File</label>

                    <input
                        type="file"
                        name="activity_file"
                        accept=".pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.jpg,.jpeg,.png,.zip"
                        required
                    >

                    <small class="file-hint">
                        Supported files: PDF, DOC, DOCX, PPT, PPTX,
                        XLS, XLSX, JPG, PNG, ZIP. Maximum 10MB.
                    </small>
                </div>

                <button type="submit" class="login-btn">
                    Upload Activity
                </button>

            </form>

        </section>

    </main>

</div>

</body>
</html>
