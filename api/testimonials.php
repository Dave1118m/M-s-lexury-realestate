<?php
require_once '../includes/database.php';
require_once '../includes/auth.php';

if (!is_logged_in() || !is_admin()) {
    header('Location: ../pages/login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? 0;
    $name = $_POST['name'] ?? '';
    $position = $_POST['position'] ?? '';
    $content = $_POST['content'] ?? '';
    $rating = $_POST['rating'] ?? 5;
    
    $image_path = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $tmp_name = $_FILES['image']['tmp_name'];
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
            $new_name = uniqid('test_') . '.' . $ext;
            $dest = '../assets/images/uploads/testimonials/';
            if (!is_dir($dest)) mkdir($dest, 0777, true);
            if (move_uploaded_file($tmp_name, $dest . $new_name)) {
                $image_path = 'assets/images/uploads/testimonials/' . $new_name;
            }
        }
    }
    
    if ($id) {
        if ($image_path) {
            $stmt = db()->prepare("UPDATE testimonials SET name=?, position=?, content=?, rating=?, image=? WHERE id=?");
            $stmt->execute([$name, $position, $content, $rating, $image_path, $id]);
        } else {
            $stmt = db()->prepare("UPDATE testimonials SET name=?, position=?, content=?, rating=? WHERE id=?");
            $stmt->execute([$name, $position, $content, $rating, $id]);
        }
    } else {
        $stmt = db()->prepare("INSERT INTO testimonials (name, position, content, rating, image) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$name, $position, $content, $rating, $image_path]);
    }
    
    header('Location: ../admin/testimonials.php');
    exit;
}
?>
