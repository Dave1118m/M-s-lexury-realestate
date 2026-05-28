<?php
require_once '../includes/database.php';
require_once '../includes/auth.php';

if (!is_logged_in() || (!is_admin() && !is_agent())) {
    header('Location: ../pages/login.php');
    exit;
}
$is_agent = is_agent();
$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? 0;
    $title = $_POST['title'] ?? '';
    $category = $_POST['category'] ?? 'General';
    $status = $_POST['status'] ?? 'published';
    $excerpt = $_POST['excerpt'] ?? '';
    $content = $_POST['content'] ?? ''; // Contains rich HTML from TinyMCE
    $image_url = $_POST['image_url'] ?? '';
    
    // Generate a unique slug
    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));
    
    // Ensure slug is unique if it's a new post
    if (!$id) {
        $check = db()->prepare("SELECT id FROM blog_posts WHERE slug = ?");
        $check->execute([$slug]);
        if ($check->fetch()) {
            $slug .= '-' . time();
        }
    }
    
    $final_image = null;
    
    // Check if PC file uploaded
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $tmp_name = $_FILES['image']['tmp_name'];
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
            $new_name = uniqid('blog_') . '.' . $ext;
            $dest = '../assets/images/uploads/blog/';
            if (!is_dir($dest)) mkdir($dest, 0777, true);
            if (move_uploaded_file($tmp_name, $dest . $new_name)) {
                $final_image = 'assets/images/uploads/blog/' . $new_name;
            }
        }
    } elseif ($image_url) {
        $final_image = $image_url;
    }
    
    if ($id) {
        if ($is_agent) {
            $check = db()->prepare("SELECT author_id FROM blog_posts WHERE id = ?");
            $check->execute([$id]);
            if ($check->fetchColumn() != $user_id) {
                die("Unauthorized to edit this post.");
            }
        }
        
        if ($final_image) {
            $stmt = db()->prepare("UPDATE blog_posts SET title=?, category=?, status=?, excerpt=?, content=?, image=? WHERE id=?");
            $stmt->execute([$title, $category, $status, $excerpt, $content, $final_image, $id]);
        } else {
            $stmt = db()->prepare("UPDATE blog_posts SET title=?, category=?, status=?, excerpt=?, content=? WHERE id=?");
            $stmt->execute([$title, $category, $status, $excerpt, $content, $id]);
        }
    } else {
        $author_id = $_SESSION['user_id'];
        $stmt = db()->prepare("INSERT INTO blog_posts (title, slug, category, status, excerpt, content, image, author_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$title, $slug, $category, $status, $excerpt, $content, $final_image, $author_id]);
    }
    
    header('Location: ../admin/blog.php');
    exit;
}
?>