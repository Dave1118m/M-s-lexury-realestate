<?php
/**
 * Hawassa API - Agents
 */
require_once '../includes/bootstrap.php';
header('Content-Type: application/json');

$stmt = db()->query("SELECT id, name, email, phone, avatar, created_at FROM users WHERE role = 'agent' ORDER BY name");
$agents = $stmt->fetchAll();

echo json_encode(['success' => true, 'agents' => $agents]);