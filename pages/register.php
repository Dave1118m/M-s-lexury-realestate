<?php
require_once '../includes/bootstrap.php';

$error = '';
$success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } else {
        $new_user_id = register_user($name, $email, $password);
        if ($new_user_id) {
            if (auto_login_user($new_user_id)) {
                redirect('dashboard.php');
            } else {
                $error = 'Registration succeeded, but auto-login failed. Please sign in manually.';
            }
        } else {
            $error = 'Email already registered.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Luxury Real Estate</title>
    <meta name="description" content="Create your account to access exclusive luxury real estate listings and premium property services.">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/luxury.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        /* ===== Register Page — Split Layout ===== */
        *, *::before, *::after { box-sizing: border-box; }
        body { margin: 0; padding: 0; overflow-x: hidden; }

        .register-page {
            display: flex;
            min-height: 100vh;
            font-family: 'Inter', sans-serif;
        }

        /* --- Left: Unsplash Image Panel --- */
        .register-image-panel {
            flex: 1;
            position: relative;
            overflow: hidden;
            display: flex;
            align-items: flex-end;
            justify-content: flex-start;
            min-height: 100vh;
        }

        .register-image-panel img {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            z-index: 0;
            animation: slowZoom 25s ease-in-out infinite alternate;
        }

        @keyframes slowZoom {
            0%   { transform: scale(1); }
            100% { transform: scale(1.08); }
        }

        .register-image-panel::after {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(
                180deg,
                rgba(0, 0, 0, 0.15) 0%,
                rgba(0, 0, 0, 0.35) 50%,
                rgba(0, 0, 0, 0.75) 100%
            );
            z-index: 1;
        }

        .image-panel-content {
            position: relative;
            z-index: 2;
            padding: 3rem;
            color: #fff;
            max-width: 520px;
        }

        .image-panel-content .brand-logo {
            font-family: 'Playfair Display', serif;
            font-size: 1.75rem;
            font-weight: 700;
            letter-spacing: 0.04em;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.6rem;
        }

        .brand-logo .accent {
            color: #C9A96E;
        }

        .image-panel-content h2 {
            font-family: 'Playfair Display', serif;
            font-size: 2.5rem;
            font-weight: 600;
            line-height: 1.2;
            margin-bottom: 1rem;
            color: #fff;
        }

        .image-panel-content p {
            font-size: 1.05rem;
            line-height: 1.7;
            opacity: 0.85;
            margin-bottom: 2rem;
            color: rgba(255,255,255,0.9);
        }

        .image-stats {
            display: flex;
            gap: 2.5rem;
        }

        .stat-item {
            text-align: left;
        }

        .stat-number {
            font-family: 'Playfair Display', serif;
            font-size: 2rem;
            font-weight: 700;
            color: #C9A96E;
            display: block;
        }

        .stat-label {
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.12em;
            opacity: 0.7;
            margin-top: 0.25rem;
        }

        /* --- Right: Form Panel --- */
        .register-form-panel {
            flex: 0 0 520px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 3rem;
            background: #0D0D0D;
            position: relative;
            overflow-y: auto;
            overflow-x: hidden;
        }

        .register-form-panel::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 600px;
            height: 600px;
            background: radial-gradient(circle, rgba(201, 169, 110, 0.08) 0%, transparent 70%);
            pointer-events: none;
        }

        .register-form-wrapper {
            width: 100%;
            max-width: 400px;
            position: relative;
            z-index: 1;
            animation: fadeSlideUp 0.8s ease-out;
        }

        @keyframes fadeSlideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .register-form-wrapper .form-header {
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

        .form-header .back-link:hover {
            color: #C9A96E;
        }

        .form-header h1 {
            font-family: 'Playfair Display', serif;
            font-size: 2rem;
            font-weight: 600;
            color: #fff;
            margin-bottom: 0.5rem;
        }

        .form-header p {
            color: rgba(255,255,255,0.45);
            font-size: 0.95rem;
        }

        /* Form styles */
        .register-form .form-row {
            display: flex;
            gap: 1rem;
        }

        .register-form .field {
            margin-bottom: 1.25rem;
            flex: 1;
        }

        .register-form .field label {
            display: block;
            font-size: 0.8rem;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: rgba(255,255,255,0.55);
            margin-bottom: 0.5rem;
        }

        .register-form .field input {
            width: 100%;
            padding: 0.9rem 1rem;
            background: rgba(255,255,255,0.06);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 8px;
            color: #fff;
            font-family: 'Inter', sans-serif;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            outline: none;
        }

        .register-form .field input::placeholder {
            color: rgba(255,255,255,0.25);
        }

        .register-form .field input:focus {
            border-color: #C9A96E;
            background: rgba(201, 169, 110, 0.05);
            box-shadow: 0 0 0 3px rgba(201, 169, 110, 0.1);
        }

        .register-form .btn-register {
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
            margin-top: 0.5rem;
            position: relative;
            overflow: hidden;
        }

        .register-form .btn-register::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: 0.5s;
        }

        .register-form .btn-register:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(201, 169, 110, 0.35);
        }

        .register-form .btn-register:hover::before {
            left: 100%;
        }

        .register-form .btn-register:active {
            transform: translateY(0);
        }

        /* Divider */
        .form-divider {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin: 1.5rem 0;
        }

        .form-divider::before,
        .form-divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: rgba(255,255,255,0.1);
        }

        .form-divider span {
            font-size: 0.8rem;
            color: rgba(255,255,255,0.35);
            text-transform: uppercase;
            letter-spacing: 0.08em;
        }

        /* Footer link */
        .form-footer {
            text-align: center;
            margin-top: 2rem;
            color: rgba(255,255,255,0.45);
            font-size: 0.9rem;
        }

        .form-footer a {
            color: #C9A96E;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s;
        }

        .form-footer a:hover {
            color: #E0C185;
            text-decoration: underline;
        }

        /* Alerts */
        .register-alert {
            padding: 0.85rem 1rem;
            border-radius: 8px;
            font-size: 0.9rem;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .register-alert.error {
            background: rgba(220, 53, 69, 0.12);
            border: 1px solid rgba(220, 53, 69, 0.25);
            color: #f5a5a5;
        }

        .register-alert.success {
            background: rgba(40, 167, 69, 0.12);
            border: 1px solid rgba(40, 167, 69, 0.25);
            color: #88d8a0;
        }

        .register-alert.success a {
            color: #C9A96E;
            font-weight: 600;
        }

        /* Password strength indicator */
        .password-hint {
            font-size: 0.78rem;
            color: rgba(255,255,255,0.3);
            margin-top: 0.4rem;
        }

        /* --- Responsive --- */
        @media (max-width: 1024px) {
            .register-page {
                flex-direction: column;
            }

            .register-image-panel {
                min-height: 40vh;
                flex: none;
            }

            .register-form-panel {
                flex: none;
                width: 100%;
                padding: 2.5rem 1.5rem;
                overflow-x: hidden;
            }

            .image-panel-content h2 {
                font-size: 2rem;
            }

            .image-stats {
                gap: 1.5rem;
            }
        }

        @media (max-width: 480px) {
            .register-image-panel {
                min-height: 30vh;
            }

            .image-panel-content {
                padding: 1.5rem;
            }

            .image-panel-content h2 {
                font-size: 1.5rem;
            }

            .register-form .form-row {
                flex-direction: column;
                gap: 0;
            }

            .stat-number {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="register-page">
        <!-- Left: Image Panel -->
        <div class="register-image-panel">
            <img 
                src="https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?ixlib=rb-4.0.3&auto=format&fit=crop&w=2075&q=80" 
                alt="Luxury modern villa with pool"
                loading="eager"
            >
            <div class="image-panel-content">
                <div class="brand-logo">
                    ◆ <span>Hawassa<span class="accent">.</span></span>
                </div>
                <h2>Discover Your Dream Property</h2>
                <p>Join thousands of discerning buyers and sellers on the most exclusive luxury real estate platform. Gain access to off-market listings and premium property services.</p>
                <div class="image-stats">
                    <div class="stat-item">
                        <span class="stat-number">12K+</span>
                        <div class="stat-label">Luxury Listings</div>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number">$8.2B</span>
                        <div class="stat-label">Property Sold</div>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number">450+</span>
                        <div class="stat-label">Elite Agents</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right: Form Panel -->
        <div class="register-form-panel">
            <div class="register-form-wrapper">
                <div class="form-header">
                    <a href="../index.php" class="back-link">← Back to Home</a>
                    <h1>Create Account</h1>
                    <p>Join the Hawassa community of luxury real estate</p>
                </div>

                <?php if ($error): ?>
                    <div class="register-alert error">
                        <span>⚠</span> <?php echo $error; ?>
                    </div>
                <?php endif; ?>
                <?php if ($success): ?>
                    <div class="register-alert success">
                        <span>✓</span> <?php echo $success; ?>
                    </div>
                <?php endif; ?>

                <form method="POST" class="register-form" id="register-form">
                    <div class="field">
                        <label for="reg-name">Full Name</label>
                        <input type="text" id="reg-name" name="name" placeholder="Enter your full name" required>
                    </div>

                    <div class="field">
                        <label for="reg-email">Email Address</label>
                        <input type="email" id="reg-email" name="email" placeholder="your@email.com" required>
                    </div>

                    <div class="form-row">
                        <div class="field">
                            <label for="reg-password">Password</label>
                            <input type="password" id="reg-password" name="password" placeholder="Min 6 characters" required>
                        </div>
                        <div class="field">
                            <label for="reg-confirm">Confirm Password</label>
                            <input type="password" id="reg-confirm" name="confirm_password" placeholder="Re-enter password" required>
                        </div>
                    </div>
                    <div class="password-hint">Use at least 6 characters with a mix of letters and numbers</div>

                    <button type="submit" class="btn-register" id="btn-register">Create Account</button>
                </form>

                <div class="form-divider"><span>or</span></div>

                <p class="form-footer">Already have an account? <a href="login.php">Sign In</a></p>
            </div>
        </div>
    </div>
</body>
</html>