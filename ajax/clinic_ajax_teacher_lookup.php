<?php
// Autofill source: reserve-a-lab's `accounts` table. READ-ONLY, and never selects the password column.
include('../../credentials/db_connection.php');
include('../helperFiles/clinic_session_handler.php');
clinic_require_login();
header('Content-Type: application/json');

// accounts.type values that count as teachers/staff to offer. Adjust here if reserve-a-lab uses others.
const CLINIC_ACCOUNT_TYPES = ['faculty', 'staff'];

$q = trim($_GET['q'] ?? '');
if (mb_strlen($q) < 2) {
    echo json_encode(['results' => []]);
    exit();
}
$like = '%' . addcslashes($q, '%_\\') . '%';
$starts = addcslashes($q, '%_\\') . '%';
$types = CLINIC_ACCOUNT_TYPES;
$ph = implode(',', array_fill(0, count($types), '?'));

$sql = "SELECT a.employeeID, a.firstname, a.middlename, a.lastname, a.position, a.email,
               (SELECT COUNT(*) FROM clinic_teachers t WHERE t.employee_no = a.employeeID AND t.deleted_at IS NULL) AS already
        FROM accounts a
        WHERE a.status = 'active' AND a.type IN ($ph)
          AND (a.employeeID LIKE ? OR a.lastname LIKE ? OR a.firstname LIKE ?
               OR CONCAT(a.firstname, ' ', a.lastname) LIKE ? OR CONCAT(a.lastname, ', ', a.firstname) LIKE ?)
        ORDER BY (a.lastname LIKE ?) DESC, a.lastname, a.firstname
        LIMIT 10";
$stmt = $conn->prepare($sql);
$params = array_merge($types, [$like, $starts, $starts, $like, $like, $starts]);
$stmt->bind_param(str_repeat('s', count($params)), ...$params);
$stmt->execute();
$res = $stmt->get_result();

$out = [];
while ($r = $res->fetch_assoc()) {
    $out[] = [
        'employee_no' => $r['employeeID'],
        'last_name'   => $r['lastname'],
        'first_name'  => $r['firstname'],
        'middle_name' => $r['middlename'],
        'position'    => $r['position'],
        'email'       => $r['email'],
        'already'     => (int)$r['already'] > 0,
        'label'       => $r['lastname'] . ', ' . $r['firstname'] . ($r['middlename'] !== '' ? ' ' . mb_substr($r['middlename'], 0, 1) . '.' : ''),
        'sub'         => $r['employeeID'] . ' · ' . $r['position'],
        'badge'       => (int)$r['already'] > 0 ? 'already added' : '',
    ];
}
echo json_encode(['results' => $out]);
