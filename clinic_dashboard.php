<?php
include('../credentials/db_connection.php');
include('helperFiles/clinic_session_handler.php');
clinic_require_login();
$pageTitle = 'Dashboard';
include('helperFiles/clinic_header.php');
?>
<h3>Dashboard</h3>
<p class="text-muted">Teacher counts, upcoming vaccine due dates and recent visits will appear here.</p>
<?php include('helperFiles/clinic_footer.php'); ?>
