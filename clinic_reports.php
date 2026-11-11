<?php
include('../credentials/db_connection.php');
include('helperFiles/clinic_session_handler.php');
include('helperFiles/clinic_reports_data.php');
clinic_require_login();

$types = include('helperFiles/clinic_record_types.php');
$names = clinic_report_names();
$report = $_GET['report'] ?? 'vaccinations_due';
if (!isset($names[$report])) { $report = 'vaccinations_due'; }
$params = ['days' => $_GET['days'] ?? 30, 'type' => $_GET['type'] ?? 'conditions', 'q' => $_GET['q'] ?? ''];
$result = clinic_report_run($conn, $report, $params);
$e = fn($s) => htmlspecialchars((string)$s, ENT_QUOTES);

$pageTitle = 'Reports';
include('helperFiles/clinic_header.php');
?>
<h3>Reports</h3>
<form method="get" class="form-inline" style="margin-bottom:15px" id="report-form">
  <select name="report" id="report" class="form-control">
    <?php foreach ($names as $k => $label): ?><option value="<?= $e($k) ?>"<?= $k === $report ? ' selected' : '' ?>><?= $e($label) ?></option><?php endforeach; ?>
  </select>
  <span class="opt opt-vaccinations_due"> within <input type="number" name="days" min="0" max="3650" value="<?= $e($params['days']) ?>" class="form-control" style="width:90px"> days</span>
  <span class="opt opt-record_search">
    <select name="type" class="form-control">
      <?php foreach ($types as $k => $cfg): ?><option value="<?= $e($k) ?>"<?= $k === $params['type'] ? ' selected' : '' ?>><?= $e($cfg['label']) ?></option><?php endforeach; ?>
    </select>
    <input type="text" name="q" value="<?= $e($params['q']) ?>" class="form-control" placeholder="e.g. diabetes">
  </span>
  <button class="btn btn-primary">Run</button>
  <a class="btn btn-default" href="clinic_export.php?<?= $e(http_build_query(['report' => $report] + $params)) ?>">Download CSV</a>
</form>

<h4><?= $e($result['title']) ?> <small><?= count($result['rows']) ?> row(s)</small></h4>
<?php if ($result['columns']): ?>
<div class="table-responsive">
<table class="table table-striped table-bordered table-condensed">
  <thead><tr><?php foreach ($result['columns'] as $c): ?><th><?= $e($c) ?></th><?php endforeach; ?></tr></thead>
  <tbody>
  <?php foreach ($result['rows'] as $row): ?>
    <tr><?php foreach ($row as $cell): ?><td><?= $e($cell) ?></td><?php endforeach; ?></tr>
  <?php endforeach; ?>
  <?php if (!$result['rows']): ?><tr><td colspan="<?= count($result['columns']) ?>" class="text-muted text-center">Nothing found.</td></tr><?php endif; ?>
  </tbody>
</table>
</div>
<?php endif; ?>
<script>
function syncOpts() { $('.opt').hide(); $('.opt-' + $('#report').val()).show(); }
$('#report').on('change', syncOpts); syncOpts();
</script>
<?php include('helperFiles/clinic_footer.php'); ?>
