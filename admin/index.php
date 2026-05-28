<?php
require_once '../includes/auth.php';
if (is_logged_in() && (is_admin() || is_agent())) {
    redirect('dashboard.php');
}
redirect('../pages/login.php');