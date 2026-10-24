<?php
session_start();
header("Content-Type: application/json; charset=utf-8");
require_once __DIR__ . "/db.php";

function respond($success, $message = "", $extra = []) {
    echo json_encode(array_merge(["success" => $success, "message" => $message], $extra));
    exit;
}
function input_data() {
    $raw = file_get_contents("php://input");
    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
}
function require_user() {
    if (empty($_SESSION["user"])) respond(false, "Please log in again.");
    return $_SESSION["user"];
}
function public_user($row) {
    return ["id" => (int)$row["id"], "name" => $row["name"], "username" => $row["username"], "role" => $row["role"]];
}
function priority_rank($p) {
    return $p === "Emergency" ? 0 : ($p === "Senior" ? 1 : 2);
}

$data = input_data();
$action = $_SERVER["REQUEST_METHOD"] === "GET" ? ($_GET["action"] ?? "") : ($data["action"] ?? "");

if ($action === "register") {
    $name = trim($data["name"] ?? "");
    $username = trim($data["username"] ?? "");
    $password = $data["password"] ?? "";
    if (mb_strlen($name) < 3 || mb_strlen($username) < 3 || strlen($password) < 8) respond(false, "Enter a valid name and username; password must be at least 8 characters.");
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO users (name, username, password, role) VALUES (?, ?, ?, 'patient')");
    if (!$stmt) respond(false, "Could not prepare registration.");
    $stmt->bind_param("sss", $name, $username, $hash);
    if (!$stmt->execute()) {
        if ($conn->errno === 1062) respond(false, "An account already exists with this mobile/email.");
        respond(false, "Registration failed.");
    }
    $user = ["id" => (int)$conn->insert_id, "name" => $name, "username" => $username, "role" => "patient"];
    session_regenerate_id(true); $_SESSION["user"] = $user;
    respond(true, "Registered successfully.", ["user" => $user]);
}

if ($action === "login" || $action === "admin_login") {
    $username = trim($data["username"] ?? "");
    $password = $data["password"] ?? "";
    $stmt = $conn->prepare("SELECT id, name, username, password, role FROM users WHERE username = ? LIMIT 1");
    $stmt->bind_param("s", $username); $stmt->execute();
    $result = $stmt->get_result(); $row = $result->fetch_assoc();
    if (!$row || !password_verify($password, $row["password"])) respond(false, "Invalid username or password.");
    if ($action === "admin_login" && $row["role"] !== "admin") respond(false, "This account does not have admin access.");
    if ($action === "login" && $row["role"] !== "patient") respond(false, "Please use the Admin Desk to log in.");
    session_regenerate_id(true); $_SESSION["user"] = public_user($row);
    respond(true, "Login successful.", ["user" => $_SESSION["user"]]);
}

if ($action === "logout") {
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), "", time() - 42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
    }
    session_destroy(); respond(true, "Logged out.");
}

$user = require_user();

if ($action === "book") {
    if ($user["role"] !== "patient") respond(false, "Only patient accounts can book appointments.");
    $name = trim($data["patient_name"] ?? "");
    $doctor = trim($data["department_doctor"] ?? "");
    $priority = $data["priority"] ?? "General";
    $time = $data["appointment_time"] ?? "";
    if ($priority === "Emergency" && $time === "") { $time = date("H:i"); }
    $notes = trim($data["notes"] ?? "");
    $gps = trim($data["gps"] ?? "");
    if (mb_strlen($name) < 3 || $doctor === "" || !preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/', $time)) respond(false, "Please complete the name, doctor and appointment time.");
    if (!in_array($priority, ["General", "Senior", "Emergency"], true)) respond(false, "Invalid priority.");
    $stmt = $conn->prepare("SELECT id FROM appointments WHERE user_id = ? AND status = 'Waiting' LIMIT 1");
    $stmt->bind_param("i", $user["id"]); $stmt->execute();
    if ($stmt->get_result()->fetch_assoc()) respond(false, "You already have a waiting appointment. Cancel it or wait until it is processed.");
    $conn->begin_transaction();
    try {
        $result = $conn->query("SELECT COALESCE(MAX(token), 100) + 1 AS next_token FROM appointments FOR UPDATE");
        $token = (int)$result->fetch_assoc()["next_token"];
        $stmt = $conn->prepare("INSERT INTO appointments (token, user_id, patient_name, department_doctor, priority, appointment_time, notes, gps) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("iissssss", $token, $user["id"], $name, $doctor, $priority, $time, $notes, $gps);
        if (!$stmt->execute()) throw new Exception("Could not save appointment.");
        $id = (int)$conn->insert_id; $conn->commit();
        respond(true, "Appointment booked.", ["appointment" => ["id"=>$id, "token"=>$token]]);
    } catch (Throwable $e) {
        $conn->rollback(); respond(false, "Could not create token. Please try again.");
    }
}

if ($action === "queue") {
    $sql = "SELECT a.id, a.token, a.user_id, a.patient_name, a.department_doctor, a.priority, TIME_FORMAT(a.appointment_time, '%H:%i') AS appointment_time, a.notes, a.gps, a.status, a.created_at FROM appointments a WHERE a.status IN ('Waiting','In Consultation') ORDER BY FIELD(a.status,'In Consultation','Waiting'), FIELD(a.priority,'Emergency','Senior','General'), a.created_at ASC";
    $result = $conn->query($sql); $queue = [];
    while ($row = $result->fetch_assoc()) {
        $row["id"]=(int)$row["id"]; $row["token"]=(int)$row["token"]; $row["user_id"]=(int)$row["user_id"];
        if ($user["role"] !== "admin") { unset($row["user_id"], $row["gps"]); }
        $queue[] = $row;
    }
    $mine = null;
    if ($user["role"] === "patient") {
        $stmt = $conn->prepare("SELECT id, token, status FROM appointments WHERE user_id = ? AND status IN ('Waiting','In Consultation') ORDER BY created_at DESC LIMIT 1");
        $stmt->bind_param("i", $user["id"]); $stmt->execute(); $mine = $stmt->get_result()->fetch_assoc();
        if ($mine) { $mine["id"]=(int)$mine["id"]; $mine["token"]=(int)$mine["token"]; }
    }
    respond(true, "Queue loaded.", ["queue"=>$queue, "my_appointment"=>$mine]);
}

if ($action === "cancel") {
    if ($user["role"] !== "patient") respond(false, "Only patients can cancel their appointments.");
    $stmt = $conn->prepare("UPDATE appointments SET status='Cancelled' WHERE user_id=? AND status='Waiting'");
    $stmt->bind_param("i", $user["id"]); $stmt->execute();
    if ($stmt->affected_rows < 1) respond(false, "No waiting appointment found to cancel.");
    respond(true, "Appointment cancelled.");
}

if ($action === "next") {
    if ($user["role"] !== "admin") respond(false, "Admin access required.");

    // Keep the consultation transition atomic so rapid/double clicks cannot call the same patient twice.
    $conn->begin_transaction();
    try {
        $activeResult = $conn->query("SELECT id, token FROM appointments WHERE status='In Consultation' ORDER BY created_at ASC LIMIT 1 FOR UPDATE");
        $active = $activeResult ? $activeResult->fetch_assoc() : null;
        $completedToken = null;

        if ($active) {
            $complete = $conn->prepare("UPDATE appointments SET status='Completed' WHERE id=? AND status='In Consultation'");
            $activeId = (int)$active["id"];
            $complete->bind_param("i", $activeId);
            if (!$complete->execute()) throw new Exception("Could not complete current consultation.");
            $completedToken = (int)$active["token"];
        }

        $nextResult = $conn->query("SELECT id, token FROM appointments WHERE status='Waiting' ORDER BY FIELD(priority,'Emergency','Senior','General'), created_at ASC LIMIT 1 FOR UPDATE");
        $nextRow = $nextResult ? $nextResult->fetch_assoc() : null;

        if ($nextRow) {
            $call = $conn->prepare("UPDATE appointments SET status='In Consultation' WHERE id=? AND status='Waiting'");
            $nextId = (int)$nextRow["id"];
            $call->bind_param("i", $nextId);
            if (!$call->execute() || $call->affected_rows !== 1) throw new Exception("Could not call next patient.");
        }

        $conn->commit();
        if ($nextRow) {
            $message = "Token #" . (int)$nextRow["token"] . " is now In Consultation.";
            if ($completedToken !== null) $message = "Completed token #" . $completedToken . ". " . $message;
            respond(true, $message);
        }
        if ($completedToken !== null) respond(true, "Completed token #" . $completedToken . ". Queue is now empty.");
        respond(false, "Queue is empty.");
    } catch (Throwable $e) {
        $conn->rollback();
        respond(false, "Could not update the queue. Please try again.");
    }
}

if ($action === "feedback_list") {
    if ($user["role"] !== "admin") respond(false, "Admin access required.");

    // Read existing feedback records; no database schema changes are required.
    $sql = "SELECT f.id, f.rating, f.suggestion, f.created_at, u.name AS patient_name, u.username AS patient_username FROM feedback f INNER JOIN users u ON u.id = f.user_id ORDER BY f.created_at DESC, f.id DESC";
    $result = $conn->query($sql);
    if (!$result) respond(false, "Could not load patient feedback.");

    $feedback = [];
    while ($row = $result->fetch_assoc()) {
        $feedback[] = [
            "id" => (int)$row["id"],
            "rating" => (int)$row["rating"],
            "suggestion" => $row["suggestion"],
            "created_at" => $row["created_at"],
            "patient_name" => $row["patient_name"],
            "patient_username" => $row["patient_username"]
        ];
    }
    respond(true, "Feedback loaded.", ["feedback" => $feedback]);
}

if ($action === "feedback") {
    if ($user["role"] !== "patient") respond(false, "Only patients can submit feedback.");
    $rating = (int)($data["rating"] ?? 0); $suggestion = trim($data["suggestion"] ?? "");
    if ($rating < 1 || $rating > 5 || $suggestion === "") respond(false, "Choose a rating and enter your comments.");
    $stmt = $conn->prepare("INSERT INTO feedback (user_id, rating, suggestion) VALUES (?, ?, ?)");
    $stmt->bind_param("iis", $user["id"], $rating, $suggestion);
    if (!$stmt->execute()) respond(false, "Could not save feedback.");
    respond(true, "Feedback saved.");
}
respond(false, "Unknown action.");
?>
