<?php
include('helperFiles/clinic_session_handler.php');
if (clinic_is_logged_in()) { header('Location: clinic_dashboard.php'); exit(); }
$expired = !empty($_SESSION['session_expired']);
unset($_SESSION['session_expired']);
$pageTitle = 'Clinic Login';
include('helperFiles/clinic_header.php');
?>
<div class="login-box panel panel-default">
  <div class="panel-heading"><h3 class="panel-title">Clinic Health History — Sign in</h3></div>
  <div class="panel-body">
    <img class="login-logo" src="assets/design/logo/logo.webp" alt="School logo">
    <?php if ($expired): ?><div class="alert alert-warning">Your session expired. Please sign in again.</div><?php endif; ?>
    <div id="msg" class="alert alert-danger" style="display:none"></div>
    <form id="login-form">
      <div class="form-group"><label for="username">Username</label>
        <input type="text" class="form-control" id="username" name="username" autocomplete="username" required autofocus></div>
      <div class="form-group"><label for="password">Password</label>
        <input type="password" class="form-control" id="password" name="password" autocomplete="current-password" required></div>
      <button type="submit" class="btn btn-primary btn-block">Sign in</button>
    </form>
  </div>
</div>
<script>
$('#login-form').on('submit', function (e) {
    e.preventDefault();
    $.post('ajax/clinic_ajax_login.php', $(this).serialize(), null, 'json')
     .done(function (r) { if (r.success) { location.href = 'clinic_dashboard.php'; } else { $('#msg').text(r.message).show(); } })
     .fail(function (x) { var m = (x.responseJSON && x.responseJSON.message) || 'Login failed.'; $('#msg').text(m).show(); });
});
</script>
<?php include('helperFiles/clinic_footer.php'); ?>
