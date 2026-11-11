<?php
// CLI only: php tools/clinic_create_user.php <username> "<Full Name>"
// Prompts for the password (hidden). Creates the clinic_users row, or resets the password if the username exists.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit(); }
if ($argc < 3) { fwrite(STDERR, "Usage: php tools/clinic_create_user.php <username> \"<Full Name>\"\n"); exit(1); }
include(__DIR__ . '/../../credentials/db_connection.php');

[$script, $username, $fullName] = $argv;
echo "Password: ";
system('stty -echo 2>/dev/null');
$pw = trim((string)fgets(STDIN));
system('stty echo 2>/dev/null');
echo "\n";
if (strlen($pw) < 8) { fwrite(STDERR, "Password must be at least 8 characters.\n"); exit(1); }

$hash = password_hash($pw, PASSWORD_DEFAULT);
$stmt = $conn->prepare("INSERT INTO clinic_users (username, password_hash, full_name) VALUES (?, ?, ?)
                        ON DUPLICATE KEY UPDATE password_hash = VALUES(password_hash), full_name = VALUES(full_name), active = 1");
$stmt->bind_param("sss", $username, $hash, $fullName);
$stmt->execute();
echo "User '$username' saved.\n";
