<?php
require_once '../includes/database.php';
require_once '../includes/auth.php';

if (!is_logged_in() || !is_admin()) {
    header('Location: ../pages/login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? 0;
    $title = $_POST['title'] ?? '';
    $content = $_POST['content'] ?? '';
    $discount_percentage = $_POST['discount_percentage'] ?? 0;
    $status = $_POST['status'] ?? 'active';
    $property_id = !empty($_POST['property_id']) ? $_POST['property_id'] : null;
    
    if ($id) {
        $stmt = db()->prepare("UPDATE announcements SET title=?, content=?, discount_percentage=?, status=?, property_id=? WHERE id=?");
        $stmt->execute([$title, $content, $discount_percentage, $status, $property_id, $id]);
    } else {
        $stmt = db()->prepare("INSERT INTO announcements (title, content, discount_percentage, status, property_id) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$title, $content, $discount_percentage, $status, $property_id]);
    }
    
    header('Location: ../admin/announcements.php');
    exit;
}
?>
