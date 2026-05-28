<?php
require_once '../includes/bootstrap.php';

    // If a user is already logged in, skip verification
    if (is_logged_in()) {
        if (is_admin()) {
            redirect('../admin/dashboard.php');
        } elseif (is_agent()) {
            redirect('agent_dashboard.php');
        } else {
            redirect('dashboard.php');
        }
    }

    if (!isset($_SESSION['pending_2fa_user_id'])) {
        redirect('login.php');
    }

$pending_user_id = $_SESSION['pending_2fa_user_id'];
$stmt = db()->prepare("SELECT verification_code FROM users WHERE id = ?");
$stmt->execute([$pending_user_id]);
$dev_code = $stmt->fetchColumn();

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = $_POST['code'] ?? '';
    
    if (verify_2fa_code($_SESSION['pending_2fa_user_id'], $code)) {
        if (is_admin()) {
            redirect('../admin/dashboard.php');
        } elseif (is_agent()) {
            redirect('agent_dashboard.php');
        } else {
            redirect('dashboard.php');
        }
    } else {
        $error = 'Invalid or expired verification code.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Login - Hawassa</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/luxury.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
</head>
<body>
    <section class="auth-section">
        <div class="auth-form">
            <h2>Two-Factor Authentication</h2>
            <p>Please enter the 6-digit code we sent to your email.</p>
            <?php if ($dev_code): ?>
                <div style="background: rgba(212, 175, 55, 0.1); border: 1px solid var(--color-gold); color: var(--color-gold); padding: 0.75rem; border-radius: 8px; margin-bottom: 1.5rem; text-align: center; font-size: 0.9rem; font-family: 'Inter', sans-serif;">
                    <strong>Local Dev Mode:</strong> Your verification code is <code><?php echo $dev_code; ?></code>
                </div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            <form method="POST">
                <div class="form-group">
                    <label>Verification Code</label>
                    <input type="text" name="code" required maxlength="6" pattern="\d{6}" placeholder="123456" style="text-align: center; font-size: 1.5rem; letter-spacing: 5px;">
                </div>
                <button type="submit" class="btn btn-gold">Verify & Sign In</button>
            </form>
            <p class="auth-footer"><a href="login.php">Back to Login</a></p>
        </div>
    </section>
</body>
</html>
