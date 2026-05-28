<?php
require 'includes/mailer.php';
$res = send_email('mihrete99@gmail.com', 'Test Subject', 'This is a test');
echo "Send Result: " . ($res ? 'SUCCESS' : 'FAILED') . "\n";
