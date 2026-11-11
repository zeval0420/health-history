<?php
// Printable health summary. Expects $t (teacher row), $types (record config), $conn.
$e = fn($s) => htmlspecialchars((string)$s, ENT_QUOTES);
?>
<h2>Health Summary</h2>
<p class="meta">Printed <?= date('Y-m-d H:i') ?> by <?= $e($_SESSION['clinic_full_name'] ?? '') ?></p>
<h3><?= $e($t['last_name'] . ', ' . $t['first_name'] . ' ' . $t['middle_name']) ?></h3>
<table class="info">
  <tr><th>Employee no.</th><td><?= $e($t['employee_no']) ?></td><th>Position</th><td><?= $e($t['position']) ?></td></tr>
  <tr><th>Date of birth</th><td><?= $e($t['dob']) ?></td><th>Sex</th><td><?= $e($t['sex']) ?></td></tr>
  <tr><th>Blood type</th><td><?= $e($t['blood_type']) ?></td><th>Department</th><td><?= $e($t['department']) ?></td></tr>
  <tr><th>Contact</th><td><?= $e($t['contact']) ?></td><th>Emergency contact</th><td><?= $e($t['emergency_contact']) ?></td></tr>
</table>
<?php if ((string)$t['notes'] !== ''): ?><p><strong>Notes:</strong> <?= nl2br($e($t['notes'])) ?></p><?php endif; ?>

<?php foreach ($types as $key => $cfg):
    $order = preg_replace('/[^a-z_]/', '', $cfg['order']);
    $stmt = $conn->prepare("SELECT * FROM `{$cfg['table']}` WHERE teacher_id = ? AND deleted_at IS NULL ORDER BY `$order` DESC, id DESC");
    $stmt->bind_param("i", $t['id']);
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    if (!$rows) { continue; } ?>
<h4><?= $e($cfg['label']) ?></h4>
<table class="rec">
  <thead><tr><?php foreach ($cfg['fields'] as $f): ?><th><?= $e($f['label']) ?></th><?php endforeach; ?><th>Notes</th></tr></thead>
  <tbody>
  <?php foreach ($rows as $r): ?>
    <tr><?php foreach ($cfg['fields'] as $f): ?><td><?= $e($r[$f['name']]) ?></td><?php endforeach; ?><td><?= $e($r['notes']) ?></td></tr>
  <?php endforeach; ?>
  </tbody>
</table>
<?php endforeach; ?>
