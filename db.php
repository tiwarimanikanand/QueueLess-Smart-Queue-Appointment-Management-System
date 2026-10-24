<?php
// Local XAMPP connection. For deployment, use a dedicated DB user and strong password.
mysqli_report(MYSQLI_REPORT_OFF);
$conn = new mysqli("localhost", "root", "", "queueless_db");
if ($conn->connect_error) {
    http_response_code(500);
    die("Database connection failed. Check that MySQL is running and queueless_db exists.");
}
$conn->set_charset("utf8mb4");
?>
