<?php
include('../../credentials/db_connection.php');
include('../helperFiles/clinic_session_handler.php');
include('../helperFiles/clinic_lookup.php');
clinic_require_login();
header('Content-Type: application/json');

$cat = $_GET['cat'] ?? '';
$q = trim($_GET['q'] ?? '');
if (!in_array($cat, clinic_lookup_categories(), true) || $q === '') {
    echo json_encode(['results' => []]);
    exit();
}
$esc = addcslashes($q, '%_\\');
$like = '%' . $esc . '%';
$starts = $esc . '%';

// Prefix matches first, then most-used
$stmt = $conn->prepare("SELECT value FROM clinic_lookup WHERE category = ? AND value LIKE ? AND value <> ?
                        ORDER BY (value LIKE ?) DESC, use_count DESC, value LIMIT 10");
$stmt->bind_param("ssss", $cat, $like, $q, $starts);
$stmt->execute();
$res = $stmt->get_result();
$out = [];
while ($r = $res->fetch_assoc()) { $out[] = ['value' => $r['value']]; }
echo json_encode(['results' => $out]);
