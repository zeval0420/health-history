<?php
/** Report definitions. clinic_report_run() returns ['title','columns','rows'] used by both the HTML page and the CSV export. */

function clinic_report_names()
{
    return [
        'vaccinations_due'    => 'Vaccinations due / overdue',
        'medications_current' => 'Current medications',
        'record_search'       => 'Find teachers by record (condition, allergy, vaccine, ...)',
    ];
}

function clinic_report_run($conn, $name, array $p)
{
    $teacher = "CONCAT(t.last_name, ', ', t.first_name)";

    if ($name === 'vaccinations_due') {
        $days = max(0, min(3650, (int)($p['days'] ?? 30)));
        // Skip a due date when a later dose of the same vaccine has already been recorded
        $sql = "SELECT $teacher, t.employee_no, v.vaccine, v.dose_label, v.date_given, v.next_due,
                       IF(v.next_due < CURDATE(), 'Overdue', 'Due soon')
                FROM clinic_vaccinations v
                JOIN clinic_teachers t ON t.id = v.teacher_id AND t.deleted_at IS NULL
                WHERE v.deleted_at IS NULL AND v.next_due IS NOT NULL
                  AND v.next_due <= DATE_ADD(CURDATE(), INTERVAL ? DAY)
                  AND NOT EXISTS (SELECT 1 FROM clinic_vaccinations v2
                                  WHERE v2.teacher_id = v.teacher_id AND v2.vaccine = v.vaccine AND v2.deleted_at IS NULL
                                    AND v2.id <> v.id AND v2.date_given > COALESCE(v.date_given, '1000-01-01'))
                ORDER BY v.next_due, t.last_name";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $days);
        $cols = ['Teacher', 'Employee no.', 'Vaccine', 'Dose', 'Last given', 'Next due', 'Status'];
        $title = "Vaccinations due within $days days (and overdue)";
    } elseif ($name === 'medications_current') {
        $sql = "SELECT $teacher, t.employee_no, m.name, m.dosage, m.frequency, m.reason, m.start_date
                FROM clinic_medications m
                JOIN clinic_teachers t ON t.id = m.teacher_id AND t.deleted_at IS NULL
                WHERE m.deleted_at IS NULL AND (m.end_date IS NULL OR m.end_date >= CURDATE())
                ORDER BY t.last_name, t.first_name, m.name";
        $stmt = $conn->prepare($sql);
        $cols = ['Teacher', 'Employee no.', 'Medication', 'Dosage', 'Frequency', 'Reason', 'Started'];
        $title = 'Current medications (no stop date, or stop date not yet reached)';
    } elseif ($name === 'record_search') {
        $types = include(__DIR__ . '/clinic_record_types.php');
        $type = $p['type'] ?? '';
        $q = trim((string)($p['q'] ?? ''));
        if (!isset($types[$type]) || $q === '') {
            return ['title' => 'Choose a record type and enter text to search', 'columns' => [], 'rows' => []];
        }
        $cfg = $types[$type];
        $primary = $cfg['fields'][0]['name'];
        $dateField = null;
        foreach ($cfg['fields'] as $f) { if ($f['type'] === 'date') { $dateField = $f['name']; break; } }
        $dateSel = $dateField ? "r.`$dateField`" : "NULL";
        $like = '%' . addcslashes($q, '%_\\') . '%';
        $sql = "SELECT $teacher, t.employee_no, r.`$primary`, $dateSel, r.notes
                FROM `{$cfg['table']}` r
                JOIN clinic_teachers t ON t.id = r.teacher_id AND t.deleted_at IS NULL
                WHERE r.deleted_at IS NULL AND (r.`$primary` LIKE ? OR r.notes LIKE ?)
                ORDER BY t.last_name, t.first_name";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $like, $like);
        $cols = ['Teacher', 'Employee no.', $cfg['fields'][0]['label'], 'Date', 'Notes'];
        $title = $cfg['label'] . ' matching "' . $q . '"';
    } else {
        return ['title' => 'Unknown report', 'columns' => [], 'rows' => []];
    }

    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_NUM);
    return ['title' => $title, 'columns' => $cols, 'rows' => $rows];
}
