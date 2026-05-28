<?php
require_once '../includes/bootstrap.php';

header('Content-Type: application/json');

if (!is_logged_in()) {
    echo json_encode(['success' => false, 'error' => 'Please log in to save properties.']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$property_id = $data['property_id'] ?? 0;
$user_id = $_SESSION['user_id'];

if (!$property_id) {
    echo json_encode(['success' => false, 'error' => 'Invalid property ID.']);
    exit;
}

try {
    if (is_property_saved($user_id, $property_id)) {
        // Unsave
        $stmt = db()->prepare("DELETE FROM saved_properties WHERE user_id = ? AND property_id = ?");
        $stmt->execute([$user_id, $property_id]);
        echo json_encode(['success' => true, 'status' => 'unsaved']);
    } else {
        // Save
        $stmt = db()->prepare("INSERT INTO saved_properties (user_id, property_id) VALUES (?, ?)");
        $stmt->execute([$user_id, $property_id]);
        echo json_encode(['success' => true, 'status' => 'saved']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Database error.']);
}
?>
