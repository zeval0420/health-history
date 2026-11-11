<?php
include('../credentials/db_connection.php');
include('helperFiles/clinic_session_handler.php');
include('helperFiles/clinic_reports_data.php');
clinic_require_login();

$report = $_GET['report'] ?? '';
if (!isset(clinic_report_names()[$report])) { http_response_code(400); exit('Unknown report.'); }
$result = clinic_report_run($conn, $report, $_GET);

// Spreadsheet formula injection: neutralise cells that start with = + - @ or a control character
$safe = function ($v) {
    $v = (string)$v;
    return ($v !== '' && strpos("=+-@\t\r", $v[0]) !== false) ? "'" . $v : $v;
};

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="clinic_' . $report . '_' . date('Y-m-d') . '.csv"');
$out = fopen('php://output', 'w');
fwrite($out, "\xEF\xBB\xBF"); // BOM so Excel reads UTF-8
fputcsv($out, $result['columns']);
foreach ($result['rows'] as $row) { fputcsv($out, array_map($safe, $row)); }
fclose($out);
