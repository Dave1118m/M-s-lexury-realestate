<?php
/**
 * Hawassa Luxury Real Estate - Authentication Functions
 */
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/functions.php';

function register_user($name, $email, $password) {
    try {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = db()->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
        $stmt->execute([$name, $email, $hash]);
        return db()->lastInsertId();
    } catch (PDOException $e) {
        // Duplicate email (error code 23000 = integrity constraint violation)
        if ($e->getCode() == 23000) {
            return false;
        }
        throw $e;
    }
}

function auto_login_user($user_id) {
    $stmt = db()->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
    if ($user) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role'] = $user['role'];
        return true;
    }
    return false;
}

function login_user($email, $password) {
    $stmt = db()->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    if ($user && password_verify($password, $user['password'])) {
        // Direct login without 2FA for all roles
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role'] = $user['role'];
        return true;
    }
    return false;
}

function verify_2fa_code($user_id, $code) {
    $stmt = db()->prepare("SELECT * FROM users WHERE id = ? AND verification_code = ? AND verification_expires > NOW()");
    $stmt->execute([$user_id, $code]);
    $user = $stmt->fetch();
    
    if ($user) {
        // Clear code
        $update = db()->prepare("UPDATE users SET verification_code = NULL, verification_expires = NULL WHERE id = ?");
        $update->execute([$user['id']]);
        
        // Log in
        unset($_SESSION['pending_2fa_user_id']);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role'] = $user['role'];
        return true;
    }
    return false;
}

function logout_user() {
    session_destroy();
    redirect('../index.php');
}
