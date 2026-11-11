<?php
include('../../credentials/db_connection.php');
include('../helperFiles/clinic_session_handler.php');
clinic_require_csrf();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    clinic_deny(405, 'POST required.');
}
$wait = clinic_throttle_wait('login');
if ($wait > 0) {
    clinic_deny(429, 'Too many attempts. Please try again in ' . ($wait >= 60 ? ceil($wait / 60) . ' minute(s)' : $wait . ' second(s)') . '.');
}

$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

$stmt = $conn->prepare("SELECT id, password_hash, full_name, role FROM clinic_users WHERE username = ? AND active = 1 LIMIT 1");
$stmt->bind_param("s", $username);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Verify against a dummy hash when the user is unknown, so timing does not reveal valid usernames.
$hash = $user['password_hash'] ?? password_hash('clinic-dummy', PASSWORD_DEFAULT);
if (!$user || !password_verify($password, $hash)) {
    clinic_throttle_hit('login');
    clinic_deny(401, 'Invalid username or password.');
}

clinic_throttle_clear('login');
session_regenerate_id(true);
$_SESSION['clinic_user_id'] = (int)$user['id'];
$_SESSION['clinic_full_name'] = $user['full_name'];
$_SESSION['clinic_role'] = $user['role'];
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));

$upd = $conn->prepare("UPDATE clinic_users SET last_login_at = NOW() WHERE id = ?");
$upd->bind_param("i", $user['id']);
$upd->execute();

echo json_encode(['success' => true]);
