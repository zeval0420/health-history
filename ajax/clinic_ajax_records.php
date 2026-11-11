<?php
// Generic CRUD for every record type in helperFiles/clinic_record_types.php
include('../../credentials/db_connection.php');
include('../helperFiles/clinic_session_handler.php');
include('../helperFiles/clinic_audit.php');
include('../helperFiles/clinic_lookup.php');
clinic_require_login();
clinic_require_csrf();
header('Content-Type: application/json');

$types = include('../helperFiles/clinic_record_types.php');
$type = $_REQUEST['type'] ?? '';
if (!isset($types[$type])) { clinic_deny(400, 'Unknown record type.'); }
$cfg = $types[$type];
$table = $cfg['table'];            // from trusted config, never from input
$fields = $cfg['fields'];
$action = $_REQUEST['action'] ?? '';

function rec_row($conn, $table, $id)
{
    $stmt = $conn->prepare("SELECT * FROM `$table` WHERE id = ? AND deleted_at IS NULL");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

if ($action === 'list') {
    $tid = (int)($_GET['teacher_id'] ?? 0);
    $order = preg_replace('/[^a-z_]/', '', $cfg['order']);
    $stmt = $conn->prepare("SELECT * FROM `$table` WHERE teacher_id = ? AND deleted_at IS NULL ORDER BY `$order` DESC, id DESC");
    $stmt->bind_param("i", $tid);
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    echo json_encode(['success' => true, 'rows' => $rows]);
    exit();
}

if ($action === 'save') {
    $id = (int)($_POST['id'] ?? 0);
    $tid = (int)($_POST['teacher_id'] ?? 0);

    $t = $conn->prepare("SELECT id FROM clinic_teachers WHERE id = ? AND deleted_at IS NULL");
    $t->bind_param("i", $tid);
    $t->execute();
    if (!$t->get_result()->fetch_assoc()) { clinic_deny(404, 'Teacher not found.'); }

    $vals = [];
    foreach ($fields as $f) {
        $v = trim((string)($_POST[$f['name']] ?? ''));
        if (!empty($f['required']) && $v === '') { clinic_deny(422, $f['label'] . ' is required.'); }
        if ($f['type'] === 'date') {
            if ($v !== '') {
                $d = DateTime::createFromFormat('Y-m-d', $v);
                if (!$d || $d->format('Y-m-d') !== $v) { clinic_deny(422, $f['label'] . ' is not a valid date.'); }
            }
            $v = $v === '' ? null : $v;
        } elseif (mb_strlen($v) > ($f['max'] ?? 255)) {
            clinic_deny(422, $f['label'] . ' is too long (max ' . ($f['max'] ?? 255) . ' characters).');
        }
        $vals[$f['name']] = $v;
    }
    $vals['notes'] = trim((string)($_POST['notes'] ?? ''));
    if (mb_strlen($vals['notes']) > 5000) { clinic_deny(422, 'Notes are too long.'); }

    $cols = array_keys($vals);
    $params = array_values($vals);
    if ($id > 0) {
        $old = rec_row($conn, $table, $id);
        if (!$old || (int)$old['teacher_id'] !== $tid) { clinic_deny(404, 'Record not found.'); }
        $set = implode(', ', array_map(fn($c) => "`$c` = ?", $cols));
        $stmt = $conn->prepare("UPDATE `$table` SET $set WHERE id = ?");
        $params[] = $id;
        $stmt->bind_param(str_repeat('s', count($cols)) . 'i', ...$params);
        $stmt->execute();
        clinic_audit($conn, $table, $id, 'update', $old, rec_row($conn, $table, $id));
    } else {
        $cols[] = 'teacher_id';  $params[] = $tid;
        $cols[] = 'created_by';  $params[] = $_SESSION['clinic_user_id'];
        $names = implode(', ', array_map(fn($c) => "`$c`", $cols));
        $marks = implode(', ', array_fill(0, count($cols), '?'));
        $stmt = $conn->prepare("INSERT INTO `$table` ($names) VALUES ($marks)");
        $stmt->bind_param(str_repeat('s', count($cols) - 2) . 'ii', ...$params);
        $stmt->execute();
        $id = $conn->insert_id;
        clinic_audit($conn, $table, $id, 'insert', null, rec_row($conn, $table, $id));
    }

    foreach ($fields as $f) {
        if (!empty($f['lookup'])) { clinic_lookup_touch($conn, $f['lookup'], $vals[$f['name']]); }
    }
    echo json_encode(['success' => true, 'id' => $id]);
    exit();
}

if ($action === 'delete') {
    $id = (int)($_POST['id'] ?? 0);
    $old = rec_row($conn, $table, $id);
    if (!$old) { clinic_deny(404, 'Record not found.'); }
    $stmt = $conn->prepare("UPDATE `$table` SET deleted_at = NOW() WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    clinic_audit($conn, $table, $id, 'delete', $old, null);
    echo json_encode(['success' => true]);
    exit();
}

clinic_deny(400, 'Unknown action.');
