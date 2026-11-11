<?php
// Usage: set $pageTitle (optional), include this, output page, include clinic_footer.php.
$pageTitle = $pageTitle ?? 'Clinic Health History';
$cl_user = $_SESSION['clinic_full_name'] ?? '';
$cl_page = basename($_SERVER['PHP_SELF']);
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
<title><?= htmlspecialchars($pageTitle) ?></title>
<link rel="stylesheet" href="assets/vendor/bootstrap/css/bootstrap.min.css">
<link rel="stylesheet" href="css/clinic.css">
<script src="assets/vendor/jquery/jquery-3.3.1.min.js"></script>
<script>
// Send the CSRF token with every same-origin jQuery request.
$.ajaxPrefilter(function (o, orig, xhr) {
    var t = document.querySelector('meta[name="csrf-token"]');
    if (t && !o.crossDomain) { xhr.setRequestHeader('X-CSRF-Token', t.content); }
});
</script>
</head>
<body>
<?php if (clinic_is_logged_in()): ?>
<nav class="navbar navbar-default">
  <div class="container">
    <div class="navbar-header"><a class="navbar-brand" href="clinic_dashboard.php">Clinic Health History</a></div>
    <ul class="nav navbar-nav">
      <li<?= $cl_page === 'clinic_dashboard.php' ? ' class="active"' : '' ?>><a href="clinic_dashboard.php">Dashboard</a></li>
      <li<?= in_array($cl_page, ['clinic_teachers.php', 'clinic_teacher.php']) ? ' class="active"' : '' ?>><a href="clinic_teachers.php">Teachers</a></li>
      <li<?= $cl_page === 'clinic_reports.php' ? ' class="active"' : '' ?>><a href="clinic_reports.php">Reports</a></li>
    </ul>
    <p class="navbar-text navbar-right"><?= htmlspecialchars($cl_user) ?> · <a href="logout.php">Log out</a></p>
  </div>
</nav>
<?php endif; ?>
<div class="container">
