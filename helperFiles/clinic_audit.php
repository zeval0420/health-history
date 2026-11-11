<?php
/** Record a change. $old/$new are arrays (or null). Never throws: auditing must not break the save. */
function clinic_audit($conn, $table, $rowId, $action, $old = null, $new = null)
{
    $uid = $_SESSION['clinic_user_id'] ?? null;
    $o = $old === null ? null : json_encode($old, JSON_UNESCAPED_UNICODE);
    $n = $new === null ? null : json_encode($new, JSON_UNESCAPED_UNICODE);
    $stmt = $conn->prepare("INSERT INTO clinic_audit_log (user_id, table_name, row_id, action, old_json, new_json) VALUES (?, ?, ?, ?, ?, ?)");
    if ($stmt) {
        $stmt->bind_param("isisss", $uid, $table, $rowId, $action, $o, $n);
        $stmt->execute();
        $stmt->close();
    }
}
