<?php
// Run once from localhost/queueless/create_admin.php, then DELETE this file.
require_once __DIR__ . "/db.php";
$name = "QueueLess Admin";
$username = "admin";
$password = "ChangeMe123!";
$hash = password_hash($password, PASSWORD_DEFAULT);
$stmt = $conn->prepare("SELECT id FROM users WHERE username=? LIMIT 1");
$stmt->bind_param("s", $username); $stmt->execute();
if ($stmt->get_result()->fetch_assoc()) {
    echo "Admin account already exists. Delete create_admin.php now.";
} else {
    $stmt = $conn->prepare("INSERT INTO users (name, username, password, role) VALUES (?, ?, ?, 'admin')");
    $stmt->bind_param("sss", $name, $username, $hash);
    if ($stmt->execute()) echo "Admin created. Username: admin | Temporary password: ChangeMe123! — delete create_admin.php now.";
    else echo "Could not create admin account.";
}
?>
