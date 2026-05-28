<?php
/**
 * Hawassa API - Contact / Inquiry Form
 */
require_once '../includes/database.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$property_id = $_POST['property_id'] ?? null;
$name = $_POST['name'] ?? '';
$email = $_POST['email'] ?? '';
$phone = $_POST['phone'] ?? '';
$message = $_POST['message'] ?? '';

if (empty($name) || empty($email) || empty($message)) {
    http_response_code(400);
    echo json_encode(['error' => 'Please fill in all required fields.']);
    exit;
}

$stmt = db()->prepare("INSERT INTO inquiries (property_id, name, email, phone, message) VALUES (?,?,?,?,?)");
$stmt->execute([$property_id, $name, $email, $phone, $message]);

echo json_encode(['success' => true, 'message' => 'Thank you for your inquiry. We will contact you shortly.']);