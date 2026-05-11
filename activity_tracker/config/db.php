<?php
$conn = new mysqli("localhost", "root", "", "activity_tracker_db");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
