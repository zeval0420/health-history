<?php
include('helperFiles/clinic_session_handler.php');
session_unset();
session_destroy();
header('Location: index.php');
exit();
