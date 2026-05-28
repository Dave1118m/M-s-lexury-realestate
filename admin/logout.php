<?php
session_name('HAWASSA_SESSID');
session_start();
session_destroy();
header('Location: ../pages/login.php');
exit;