<?php
$host = "localhost";
$username = "root";
$password = "";
$dbname = "student_activity_portal"; // Check your .sql file for the exact database name

$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
