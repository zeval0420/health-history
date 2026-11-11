<?php
include('../credentials/db_connection.php');
include('helperFiles/clinic_session_handler.php');
clinic_require_login();

$stmt = $conn->prepare("SELECT * FROM clinic_teachers WHERE id = ? AND deleted_at IS NULL");
$id = (int)($_GET['id'] ?? 0);
$stmt->bind_param("i", $id);
$stmt->execute();
$t = $stmt->get_result()->fetch_assoc();
if (!$t) { http_response_code(404); exit('Teacher not found.'); }
$types = include('helperFiles/clinic_record_types.php');
?><!DOCTYPE html>
<html lang="en"><head><meta charset="utf-8"><title>Health summary - <?= htmlspecialchars($t['last_name']) ?></title>
<style>
body { font: 13px/1.4 Arial, sans-serif; margin: 20px; color: #000; }
h2, h3, h4 { margin: 12px 0 4px; } .meta { color: #555; margin: 0; }
table { border-collapse: collapse; width: 100%; margin-bottom: 8px; }
th, td { border: 1px solid #999; padding: 3px 6px; text-align: left; vertical-align: top; }
th { background: #eee; } table.info th { width: 18%; }
@media print { .noprint { display: none; } }
</style></head><body>
<p class="noprint"><button onclick="window.print()">Print</button></p>
<?php include('templates/clinic_summary_template.php'); ?>
</body></html>
