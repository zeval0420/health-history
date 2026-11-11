<?php
include('../../credentials/db_connection.php');
include('../helperFiles/clinic_session_handler.php');
include('../helperFiles/clinic_audit.php');
include('../helperFiles/clinic_lookup.php');
clinic_require_login();
clinic_require_csrf();
header('Content-Type: application/json');

const TEACHER_FIELDS = ['employee_no', 'last_name', 'first_name', 'middle_name', 'position', 'email',
                        'dob', 'sex', 'department', 'contact', 'emergency_contact', 'blood_type', 'notes'];

function teacher_row($conn, $id)
{
    $stmt = $conn->prepare("SELECT * FROM clinic_teachers WHERE id = ? AND deleted_at IS NULL");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

$action = $_REQUEST['action'] ?? '';

if ($action === 'list') {
    $rows = [];
    $res = $conn->query("SELECT id, employee_no, last_name, first_name, middle_name, position, department, contact, source,
                                (SELECT COUNT(*) FROM clinic_medications m WHERE m.teacher_id = t.id AND m.deleted_at IS NULL)
                              + (SELECT COUNT(*) FROM clinic_conditions c WHERE c.teacher_id = t.id AND c.deleted_at IS NULL)
                              + (SELECT COUNT(*) FROM clinic_vaccinations v WHERE v.teacher_id = t.id AND v.deleted_at IS NULL) AS record_count
                         FROM clinic_teachers t WHERE t.deleted_at IS NULL ORDER BY last_name, first_name");
    while ($r = $res->fetch_assoc()) { $rows[] = $r; }
    echo json_encode(['data' => $rows]);
    exit();
}

if ($action === 'get') {
    $row = teacher_row($conn, (int)($_GET['id'] ?? 0));
    if (!$row) { clinic_deny(404, 'Teacher not found.'); }
    echo json_encode(['success' => true, 'teacher' => $row]);
    exit();
}

if ($action === 'save') {
    $id = (int)($_POST['id'] ?? 0);
    $v = [];
    foreach (TEACHER_FIELDS as $f) { $v[$f] = trim((string)($_POST[$f] ?? '')); }

    if ($v['last_name'] === '' || $v['first_name'] === '') { clinic_deny(422, 'Last name and first name are required.'); }
    if ($v['email'] !== '' && !filter_var($v['email'], FILTER_VALIDATE_EMAIL)) { clinic_deny(422, 'Email address is not valid.'); }
    if ($v['dob'] !== '') {
        $d = DateTime::createFromFormat('Y-m-d', $v['dob']);
        if (!$d || $d->format('Y-m-d') !== $v['dob'] || $d > new DateTime('today')) { clinic_deny(422, 'Date of birth is not valid.'); }
    }
    $empNo = $v['employee_no'] === '' ? null : $v['employee_no'];
    $dob = $v['dob'] === '' ? null : $v['dob'];
    $source = ($_POST['source'] ?? '') === 'reserve-a-lab' ? 'reserve-a-lab' : 'manual';

    // Same employee number (the unique key also covers removed rows).
    if ($empNo !== null) {
        $stmt = $conn->prepare("SELECT id, deleted_at FROM clinic_teachers WHERE employee_no = ? AND id <> ? LIMIT 1");
        $stmt->bind_param("si", $empNo, $id);
        $stmt->execute();
        if ($dup = $stmt->get_result()->fetch_assoc()) {
            clinic_deny(409, $dup['deleted_at'] ? 'A removed teacher record already uses this employee number.' : 'A teacher with this employee number already exists.');
        }
    }
    // Same name: warn once, saved if the user confirms (force=1).
    if (empty($_POST['force'])) {
        $stmt = $conn->prepare("SELECT id FROM clinic_teachers WHERE last_name = ? AND first_name = ? AND id <> ? AND deleted_at IS NULL LIMIT 1");
        $stmt->bind_param("ssi", $v['last_name'], $v['first_name'], $id);
        $stmt->execute();
        if ($stmt->get_result()->fetch_assoc()) {
            http_response_code(409);
            echo json_encode(['success' => false, 'status' => 'duplicate_name', 'message' => 'A teacher named ' . $v['last_name'] . ', ' . $v['first_name'] . ' already exists. Save anyway?']);
            exit();
        }
    }

    if ($id > 0) {
        $old = teacher_row($conn, $id);
        if (!$old) { clinic_deny(404, 'Teacher not found.'); }
        $stmt = $conn->prepare("UPDATE clinic_teachers SET employee_no=?, last_name=?, first_name=?, middle_name=?, position=?, email=?, dob=?, sex=?, department=?, contact=?, emergency_contact=?, blood_type=?, notes=? WHERE id=?");
        $stmt->bind_param("sssssssssssssi", $empNo, $v['last_name'], $v['first_name'], $v['middle_name'], $v['position'], $v['email'], $dob, $v['sex'], $v['department'], $v['contact'], $v['emergency_contact'], $v['blood_type'], $v['notes'], $id);
        $stmt->execute();
        clinic_audit($conn, 'clinic_teachers', $id, 'update', $old, teacher_row($conn, $id));
    } else {
        $uid = $_SESSION['clinic_user_id'];
        $stmt = $conn->prepare("INSERT INTO clinic_teachers (employee_no, source, last_name, first_name, middle_name, position, email, dob, sex, department, contact, emergency_contact, blood_type, notes, created_by) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
        $stmt->bind_param("ssssssssssssssi", $empNo, $source, $v['last_name'], $v['first_name'], $v['middle_name'], $v['position'], $v['email'], $dob, $v['sex'], $v['department'], $v['contact'], $v['emergency_contact'], $v['blood_type'], $v['notes'], $uid);
        $stmt->execute();
        $id = $conn->insert_id;
        clinic_audit($conn, 'clinic_teachers', $id, 'insert', null, teacher_row($conn, $id));
    }
    foreach (CLINIC_TEACHER_LOOKUPS as $cat) { clinic_lookup_touch($conn, $cat, $v[$cat]); }
    echo json_encode(['success' => true, 'id' => $id]);
    exit();
}

if ($action === 'delete') {
    $id = (int)($_POST['id'] ?? 0);
    $old = teacher_row($conn, $id);
    if (!$old) { clinic_deny(404, 'Teacher not found.'); }
    $stmt = $conn->prepare("UPDATE clinic_teachers SET deleted_at = NOW() WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    clinic_audit($conn, 'clinic_teachers', $id, 'delete', $old, null);
    echo json_encode(['success' => true]);
    exit();
}

clinic_deny(400, 'Unknown action.');
