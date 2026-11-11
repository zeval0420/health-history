<?php
/** Autocomplete categories used by the teacher form (record-type categories come from clinic_record_types.php). */
const CLINIC_TEACHER_LOOKUPS = ['position', 'department', 'sex', 'blood_type'];

function clinic_lookup_categories()
{
    $cats = CLINIC_TEACHER_LOOKUPS;
    foreach (include(__DIR__ . '/clinic_record_types.php') as $cfg) {
        foreach ($cfg['fields'] as $f) {
            if (!empty($f['lookup'])) { $cats[] = $f['lookup']; }
        }
    }
    return array_values(array_unique($cats));
}

/** Remember a typed value so it is suggested next time. Never throws. */
function clinic_lookup_touch($conn, $category, $value)
{
    $value = trim((string)$value);
    if ($value === '' || mb_strlen($value) > 255 || !in_array($category, clinic_lookup_categories(), true)) { return; }
    $stmt = $conn->prepare("INSERT INTO clinic_lookup (category, value) VALUES (?, ?)
                            ON DUPLICATE KEY UPDATE use_count = use_count + 1, last_used = NOW()");
    if ($stmt) {
        $stmt->bind_param("ss", $category, $value);
        $stmt->execute();
        $stmt->close();
    }
}
