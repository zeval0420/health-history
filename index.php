<?php
include('helperFiles/clinic_session_handler.php');
if (clinic_is_logged_in()) { header('Location: clinic_dashboard.php'); exit(); }
$expired = !empty($_SESSION['session_expired']);
unset($_SESSION['session_expired']);
$pageTitle = 'Clinic Login';
$bodyClass = 'auth-page';
include('helperFiles/clinic_header.php');
?>
<main class="auth">
  <section class="auth-brand" aria-hidden="true">
    <div class="auth-brand-inner">
      <img class="auth-logo" src="assets/design/logo/logo.webp" alt="">
      <p class="auth-kicker">School Clinic</p>
      <h1 class="auth-title">Health<br>History</h1>
      <p class="auth-tag">Records, check-ups and follow-ups for every teacher, in one calm place.</p>
    </div>
    <svg class="auth-pulse" viewBox="0 0 800 120" preserveAspectRatio="none">
      <path d="M0 60 H230 l18-10 l16 10 H300 l14 -8 l12 8 H380 l16 -52 l26 100 l20 -78 l14 30 H560 l14-9 l14 9 H800" pathLength="1"/>
    </svg>
  </section>

  <section class="auth-form-wrap">
    <div class="auth-card">
      <img class="auth-logo-sm" src="assets/design/logo/logo.webp" alt="School logo">
      <h2>Welcome back</h2>
      <p class="auth-sub">Sign in to open the clinic records.</p>
      <?php if ($expired): ?><div class="alert alert-warning">Your session expired. Please sign in again.</div><?php endif; ?>
      <div id="msg" class="alert alert-danger" role="alert" style="display:none"></div>
      <form id="login-form">
        <div class="auth-field">
          <input type="text" id="username" name="username" placeholder=" " autocomplete="username" required autofocus>
          <label for="username">Username</label>
        </div>
        <div class="auth-field">
          <input type="password" id="password" name="password" placeholder=" " autocomplete="current-password" required>
          <label for="password">Password</label>
          <button type="button" class="auth-eye" id="toggle-pw" aria-label="Show password">Show</button>
        </div>
        <button type="submit" class="auth-submit"><span>Sign in</span></button>
      </form>
      <p class="auth-foot">Authorized clinic staff only.</p>
    </div>
  </section>
</main>
<script>
$('#toggle-pw').on('click', function () {
    var i = $('#password'), show = i.attr('type') === 'password';
    i.attr('type', show ? 'text' : 'password'); $(this).text(show ? 'Hide' : 'Show');
});
$('#login-form').on('submit', function (e) {
    e.preventDefault();
    $('.auth-submit').addClass('busy').prop('disabled', true);
    $.post('ajax/clinic_ajax_login.php', $(this).serialize(), null, 'json')
     .done(function (r) { if (r.success) { location.href = 'clinic_dashboard.php'; } else { $('#msg').text(r.message).show(); $('.auth-submit').removeClass('busy').prop('disabled', false); } })
     .fail(function (x) { var m = (x.responseJSON && x.responseJSON.message) || 'Login failed.'; $('#msg').text(m).show(); $('.auth-submit').removeClass('busy').prop('disabled', false); });
});
</script>
<?php include('helperFiles/clinic_footer.php'); ?>
