<?php
/**
 * Hawassa API - Users (Admin)
 */
require_once '../includes/database.php';
require_once '../includes/auth.php';

header('Content-Type: application/json');

if (!is_logged_in() || !is_admin()) {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        $stmt = db()->query("SELECT id, name, email, role, phone, created_at FROM users ORDER BY created_at DESC");
        echo json_encode($stmt->fetchAll());
        break;

    case 'POST':
        $data = json_decode(file_get_contents('php://input'), true);
        $stmt = db()->prepare("INSERT INTO users (name, email, password, role, phone) VALUES (?,?,?,?,?)");
        $stmt->execute([$data['name'], $data['email'], password_hash($data['password'], PASSWORD_DEFAULT), $data['role'] ?? 'user', $data['phone'] ?? '']);
        echo json_encode(['success' => true, 'id' => db()->lastInsertId()]);
        break;

    case 'DELETE':
        $id = $_GET['id'] ?? 0;
        $stmt = db()->prepare("DELETE FROM users WHERE id = ? AND id != ?");
        $stmt->execute([$id, $_SESSION['user_id']]);
        echo json_encode(['success' => true]);
        break;

    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
}