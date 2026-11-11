<?php
include('../credentials/db_connection.php');
include('helperFiles/clinic_session_handler.php');
include('helperFiles/clinic_reports_data.php');
clinic_require_login();

$count = fn($sql) => (int)$conn->query($sql)->fetch_row()[0];
$teachers = $count("SELECT COUNT(*) FROM clinic_teachers WHERE deleted_at IS NULL");
$visits30 = $count("SELECT COUNT(*) FROM clinic_visits v JOIN clinic_teachers t ON t.id = v.teacher_id AND t.deleted_at IS NULL
                    WHERE v.deleted_at IS NULL AND v.visit_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)");
$due = clinic_report_run($conn, 'vaccinations_due', ['days' => 30]);
$overdue = count(array_filter($due['rows'], fn($r) => $r[6] === 'Overdue'));

$recent = $conn->query("SELECT t.id, CONCAT(t.last_name, ', ', t.first_name) AS name, v.visit_date, v.complaint, v.action_taken
                        FROM clinic_visits v JOIN clinic_teachers t ON t.id = v.teacher_id AND t.deleted_at IS NULL
                        WHERE v.deleted_at IS NULL ORDER BY v.visit_date DESC, v.id DESC LIMIT 8")->fetch_all(MYSQLI_ASSOC);
$e = fn($s) => htmlspecialchars((string)$s, ENT_QUOTES);

$pageTitle = 'Dashboard';
include('helperFiles/clinic_header.php');
?>
<h3>Dashboard</h3>
<div class="row">
  <div class="col-sm-3"><div class="panel panel-default"><div class="panel-body text-center"><h2 style="margin:0"><?= $teachers ?></h2>Teachers</div></div></div>
  <div class="col-sm-3"><div class="panel panel-default"><div class="panel-body text-center"><h2 style="margin:0"><?= $visits30 ?></h2>Visits, last 30 days</div></div></div>
  <div class="col-sm-3"><div class="panel panel-warning"><div class="panel-body text-center"><h2 style="margin:0"><?= count($due['rows']) ?></h2>Vaccines due in 30 days</div></div></div>
  <div class="col-sm-3"><div class="panel panel-danger"><div class="panel-body text-center"><h2 style="margin:0"><?= $overdue ?></h2>Overdue</div></div></div>
</div>
<p><a href="clinic_reports.php?report=vaccinations_due">View vaccinations due &rarr;</a></p>

<h4>Recent visits</h4>
<table class="table table-striped table-condensed">
  <thead><tr><th>Date</th><th>Teacher</th><th>Complaint</th><th>Action taken</th></tr></thead>
  <tbody>
  <?php foreach ($recent as $r): ?>
    <tr><td><?= $e($r['visit_date']) ?></td><td><a href="clinic_teacher.php?id=<?= (int)$r['id'] ?>"><?= $e($r['name']) ?></a></td><td><?= $e($r['complaint']) ?></td><td><?= $e($r['action_taken']) ?></td></tr>
  <?php endforeach; ?>
  <?php if (!$recent): ?><tr><td colspan="4" class="text-muted text-center">No visits recorded yet.</td></tr><?php endif; ?>
  </tbody>
</table>
<?php include('helperFiles/clinic_footer.php'); ?>
