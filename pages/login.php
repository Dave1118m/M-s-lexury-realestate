<?php
require_once '../includes/bootstrap.php';

// Redirect if already logged in
if (is_logged_in()) {
    if (is_admin()) {
        redirect('../admin/dashboard.php');
    } elseif (is_agent()) {
        redirect('agent_dashboard.php');
    } else {
        redirect('dashboard.php');
    }
}


$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    $result = login_user($email, $password);
    if ($result === 'requires_2fa') {
        redirect('verify.php');
    } elseif ($result === true) {
        if (is_admin()) {
            redirect('../admin/dashboard.php');
        } elseif (is_agent()) {
            redirect('agent_dashboard.php');
        } else {
            redirect('dashboard.php');
        }
    } else {
        $error = 'Invalid email or password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In - Hawassa Luxury Real Estate</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/luxury.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        /* ===== Login Page — Split Layout ===== */
        *, *::before, *::after { box-sizing: border-box; }
        body { margin: 0; padding: 0; background-color: #0D0D0D; overflow-x: hidden; }

        .auth-page {
            display: flex;
            min-height: 100vh;
            font-family: 'Inter', sans-serif;
        }

        /* --- Left: Form Panel --- */
        .auth-form-panel {
            flex: 0 0 500px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 3rem;
            background: #0D0D0D;
            position: relative;
            z-index: 10;
        }

        .auth-form-panel::before {
            content: '';
            position: absolute;
            top: -30%;
            left: -30%;
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, rgba(201, 169, 110, 0.08) 0%, transparent 70%);
            pointer-events: none;
        }

        .auth-form-wrapper {
            width: 100%;
            max-width: 380px;
            animation: fadeIn 0.8s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .form-header {
            margin-bottom: 2.5rem;
        }

        .form-header .back-link {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            color: rgba(255,255,255,0.5);
            font-size: 0.85rem;
            text-decoration: none;
            margin-bottom: 2rem;
            transition: color 0.3s;
        }

        .form-header .back-link:hover { color: #C9A96E; }

        .form-header h1 {
            font-family: 'Playfair Display', serif;
            font-size: 2.2rem;
            font-weight: 600;
            color: #fff;
            margin-bottom: 0.5rem;
        }

        .form-header p {
            color: rgba(255,255,255,0.45);
            font-size: 0.95rem;
        }

        .field {
            margin-bottom: 1.25rem;
        }

        .field label {
            display: block;
            font-size: 0.8rem;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: rgba(255,255,255,0.55);
            margin-bottom: 0.5rem;
        }

        .field input {
            width: 100%;
            padding: 1rem;
            background: rgba(255,255,255,0.04);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 8px;
            color: #fff;
            font-family: 'Inter', sans-serif;
            font-size: 1rem;
            transition: all 0.3s ease;
            outline: none;
        }

        .field input:focus {
            border-color: #C9A96E;
            background: rgba(201, 169, 110, 0.05);
            box-shadow: 0 0 0 3px rgba(201, 169, 110, 0.1);
        }

        .btn-auth {
            width: 100%;
            padding: 1rem;
            background: linear-gradient(135deg, #C9A96E 0%, #B08D4C 100%);
            color: #0D0D0D;
            border: none;
            border-radius: 8px;
            font-family: 'Inter', sans-serif;
            font-size: 0.95rem;
            font-weight: 600;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 1rem;
            position: relative;
            overflow: hidden;
        }

        .btn-auth::before {
            content: '';
            position: absolute;
            top: 0; left: -100%; width: 100%; height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: 0.5s;
        }

        .btn-auth:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(201, 169, 110, 0.35);
        }

        .btn-auth:hover::before { left: 100%; }

        .auth-alert {
            padding: 0.85rem 1rem;
            border-radius: 8px;
            font-size: 0.9rem;
            margin-bottom: 1.5rem;
            background: rgba(220, 53, 69, 0.12);
            border: 1px solid rgba(220, 53, 69, 0.25);
            color: #f5a5a5;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .auth-footer {
            text-align: center;
            margin-top: 2rem;
            color: rgba(255,255,255,0.45);
            font-size: 0.9rem;
        }

        .auth-footer a {
            color: #C9A96E;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s;
        }
        .auth-footer a:hover { color: #E0C185; }

        /* --- Right: Image Panel --- */
        .auth-image-panel {
            flex: 1;
            position: relative;
            overflow: hidden;
        }

        .auth-image-panel img {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            animation: slowPan 30s linear infinite alternate;
        }

        @keyframes slowPan {
            0% { transform: scale(1.05) translate(0, 0); }
            100% { transform: scale(1.1) translate(-2%, -2%); }
        }

        .auth-image-panel::after {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(90deg, #0D0D0D 0%, rgba(13,13,13,0.2) 40%, transparent 100%);
        }

        @media (max-width: 900px) {
            .auth-page { flex-direction: column-reverse; }
            .auth-image-panel { min-height: 30vh; flex: none; }
            .auth-image-panel::after { background: linear-gradient(0deg, #0D0D0D 0%, transparent 100%); }
            .auth-form-panel { flex: none; width: 100%; padding: 2rem; overflow: hidden; }
        }

        @media (max-width: 480px) {
            .auth-form-panel { padding: 1.5rem; overflow: hidden; }
            .form-header h1 { font-size: 1.8rem; }
        }
    </style>
</head>
<body>
    <div class="auth-page">
        <!-- Left: Form Panel -->
        <div class="auth-form-panel">
            <div class="auth-form-wrapper">
                <div class="form-header">
                    <a href="../index.php" class="back-link">← Back to Home</a>
                    <h1>Welcome Back</h1>
                    <p>Sign in to your Hawassa account</p>
                </div>

                <?php if ($error): ?>
                    <div class="auth-alert"><span>⚠</span> <?php echo $error; ?></div>
                <?php endif; ?>

                <form method="POST">
                    <div class="field">
                        <label>Email Address</label>
                        <input type="email" name="email" required placeholder="your@email.com">
                    </div>
                    <div class="field">
                        <label>Password</label>
                        <input type="password" name="password" required placeholder="Enter your password">
                    </div>
                    <button type="submit" class="btn-auth">Sign In</button>
                </form>

                <p class="auth-footer">Don't have an account? <a href="register.php">Register Here</a></p>
            </div>
        </div>

        <!-- Right: Image Panel -->
        <div class="auth-image-panel">
            <img src="https://images.unsplash.com/photo-1613977257363-707ba9348227?ixlib=rb-4.0.3&auto=format&fit=crop&w=2070&q=80" alt="Luxury living room interior">
        </div>
    </div>
</body>
</html>