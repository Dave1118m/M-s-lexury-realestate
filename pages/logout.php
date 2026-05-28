<?php
session_name('HAWASSA_SESSID');
session_start();
session_destroy();
header('Location: login.php');
exit;
?>
