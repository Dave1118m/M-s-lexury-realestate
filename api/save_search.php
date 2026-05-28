<?php
require_once '../includes/bootstrap.php';

header('Content-Type: application/json');

if (!is_logged_in()) {
    echo json_encode(['success' => false, 'error' => 'Please log in to save searches.']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$search_name = $data['search_name'] ?? 'Saved Search ' . date('M d, Y');
$criteria = current($data['criteria'] ?? []); // Assuming criteria is passed as object

$user_id = $_SESSION['user_id'];

if (empty($data['criteria'])) {
    echo json_encode(['success' => false, 'error' => 'No search criteria provided.']);
    exit;
}

try {
    $stmt = db()->prepare("INSERT INTO saved_searches (user_id, search_name, criteria) VALUES (?, ?, ?)");
    $stmt->execute([$user_id, $search_name, json_encode($data['criteria'])]);
    echo json_encode(['success' => true, 'message' => 'Search saved successfully!']);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Database error.']);
}
?>
