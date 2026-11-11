<?php
include('../credentials/db_connection.php');
include('helperFiles/clinic_session_handler.php');
clinic_require_login();

$id = (int)($_GET['id'] ?? 0);
$stmt = $conn->prepare("SELECT * FROM clinic_teachers WHERE id = ? AND deleted_at IS NULL");
$stmt->bind_param("i", $id);
$stmt->execute();
$t = $stmt->get_result()->fetch_assoc();
if (!$t) { header('Location: clinic_teachers.php'); exit(); }

$types = include('helperFiles/clinic_record_types.php');
$e = fn($s) => htmlspecialchars((string)$s, ENT_QUOTES);
$pageTitle = $t['last_name'] . ', ' . $t['first_name'];
include('helperFiles/clinic_header.php');
?>
<div class="clearfix" style="margin-bottom:10px">
  <a href="clinic_teachers.php">&larr; All teachers</a>
  <a class="btn btn-default btn-sm pull-right" href="clinic_print.php?id=<?= $id ?>" target="_blank">Print summary</a>
</div>
<div class="panel panel-default"><div class="panel-body">
  <h3 style="margin-top:0"><?= $e($t['last_name'] . ', ' . $t['first_name'] . ' ' . $t['middle_name']) ?></h3>
  <div class="row">
    <div class="col-sm-3"><strong>Employee no.</strong><br><?= $e($t['employee_no']) ?: '&mdash;' ?></div>
    <div class="col-sm-3"><strong>Position</strong><br><?= $e($t['position']) ?: '&mdash;' ?></div>
    <div class="col-sm-3"><strong>Date of birth</strong><br><?= $e($t['dob']) ?: '&mdash;' ?></div>
    <div class="col-sm-3"><strong>Blood type</strong><br><?= $e($t['blood_type']) ?: '&mdash;' ?></div>
  </div>
  <div class="row" style="margin-top:8px">
    <div class="col-sm-3"><strong>Department</strong><br><?= $e($t['department']) ?: '&mdash;' ?></div>
    <div class="col-sm-3"><strong>Contact</strong><br><?= $e($t['contact']) ?: '&mdash;' ?></div>
    <div class="col-sm-6"><strong>Emergency contact</strong><br><?= $e($t['emergency_contact']) ?: '&mdash;' ?></div>
  </div>
  <?php if ($t['notes'] !== null && $t['notes'] !== ''): ?>
  <p style="margin-top:8px"><strong>Notes</strong><br><?= nl2br($e($t['notes'])) ?></p>
  <?php endif; ?>
</div></div>

<ul class="nav nav-tabs" id="type-tabs"></ul>
<div class="tab-content" id="type-panes" style="padding-top:15px"></div>

<div class="modal fade" id="rec-modal" tabindex="-1">
  <div class="modal-dialog"><div class="modal-content">
    <form id="rec-form" autocomplete="off">
      <div class="modal-header"><button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title" id="rec-title"></h4></div>
      <div class="modal-body">
        <div id="rec-msg" class="alert alert-danger" style="display:none"></div>
        <div id="rec-fields"></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-primary">Save</button>
      </div>
    </form>
  </div></div>
</div>

<script>
window.CLINIC_TEACHER_ID = <?= $id ?>;
window.CLINIC_TYPES = <?= json_encode($types, JSON_HEX_TAG | JSON_HEX_AMP) ?>;
</script>
<script src="js/clinic_autocomplete.js"></script>
<script src="js/clinic_teacher.js"></script>
<?php include('helperFiles/clinic_footer.php'); ?>
