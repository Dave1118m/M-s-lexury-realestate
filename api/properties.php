<?php
/**
 * Hawassa API - Properties CRUD (Admin)
 */
require_once '../includes/database.php';
require_once '../includes/auth.php';

header('Content-Type: application/json');

if (!is_logged_in() || (!is_admin() && !is_agent())) {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$is_agent = is_agent();
$user_id = $_SESSION['user_id'];

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        if (isset($_GET['id'])) {
            $stmt = db()->prepare("SELECT * FROM properties WHERE id = ?");
            $stmt->execute([$_GET['id']]);
            echo json_encode($stmt->fetch());
        } else {
            $stmt = db()->query("SELECT * FROM properties ORDER BY created_at DESC");
            echo json_encode($stmt->fetchAll());
        }
        break;

    case 'POST':
        $data = !empty($_POST) ? $_POST : json_decode(file_get_contents('php://input'), true);
        
        $image_main = null;
        if (isset($_FILES['image_main']) && $_FILES['image_main']['error'] === UPLOAD_ERR_OK) {
            $tmp_name = $_FILES['image_main']['tmp_name'];
            $name = basename($_FILES['image_main']['name']);
            $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
                $new_name = uniqid() . '.' . $ext;
                $dest = '../assets/images/uploads/properties/' . $new_name;
                if (move_uploaded_file($tmp_name, $dest)) {
                    $image_main = 'assets/images/uploads/properties/' . $new_name; // Save relative path
                }
            }
        }

        $floor_plan_image = null;
        if (isset($_FILES['floor_plan_image']) && $_FILES['floor_plan_image']['error'] === UPLOAD_ERR_OK) {
            $tmp_name = $_FILES['floor_plan_image']['tmp_name'];
            $name = basename($_FILES['floor_plan_image']['name']);
            $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'pdf'])) {
                $new_name = 'floorplan_' . uniqid() . '.' . $ext;
                $dest = '../assets/images/uploads/properties/' . $new_name;
                if (move_uploaded_file($tmp_name, $dest)) {
                    $floor_plan_image = 'assets/images/uploads/properties/' . $new_name;
                }
            }
        }

        if (!empty($data['id'])) {
            // Verify ownership if agent
            if ($is_agent) {
                $check = db()->prepare("SELECT agent_id FROM properties WHERE id = ?");
                $check->execute([$data['id']]);
                if ($check->fetchColumn() != $user_id) {
                    echo json_encode(['error' => 'Unauthorized to edit this property.']);
                    exit;
                }
                $data['agent_id'] = $user_id; // Enforce assigning to self
            }
            
            // Update existing property
            $sql = "UPDATE properties SET title=?, slug=?, description=?, price=?, type=?, property_type=?, bedrooms=?, bathrooms=?, area_sqft=?, address=?, city=?, state=?, zip=?, latitude=?, longitude=?, category_id=?, agent_id=?, featured=?, status=?, virtual_tour_url=?, video_url=?";
            $params = [
                $data['title'], slugify($data['title']), $data['description'], $data['price'], $data['type'],
                $data['property_type'], $data['bedrooms'], $data['bathrooms'], $data['area_sqft'],
                $data['address'], $data['city'], $data['state'], $data['zip'],
                $data['latitude'] ?? null, $data['longitude'] ?? null,
                $data['category_id'], $data['agent_id'], $data['featured'] ?? 0, $data['status'] ?? 'active',
                $data['virtual_tour_url'] ?? null, $data['video_url'] ?? null
            ];
            
            if ($image_main) {
                $sql .= ", image_main=?";
                $params[] = $image_main;
            }
            if ($floor_plan_image) {
                $sql .= ", floor_plan_image=?";
                $params[] = $floor_plan_image;
            }
            
            $sql .= " WHERE id=?";
            $params[] = $data['id'];
            
            $stmt = db()->prepare($sql);
            $stmt->execute($params);
            
            echo json_encode(['success' => true, 'id' => $data['id']]);
        } else {
            // Enforce assigning to self if agent
            if ($is_agent) {
                $data['agent_id'] = $user_id;
            }
            // Insert new property
            $stmt = db()->prepare("INSERT INTO properties (title, slug, description, price, type, property_type, bedrooms, bathrooms, area_sqft, address, city, state, zip, latitude, longitude, category_id, agent_id, featured, status, image_main, virtual_tour_url, video_url, floor_plan_image) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
            $stmt->execute([
                $data['title'], slugify($data['title']), $data['description'], $data['price'], $data['type'],
                $data['property_type'], $data['bedrooms'], $data['bathrooms'], $data['area_sqft'],
                $data['address'], $data['city'], $data['state'], $data['zip'],
                $data['latitude'] ?? null, $data['longitude'] ?? null,
                $data['category_id'], $data['agent_id'], $data['featured'] ?? 0, $data['status'] ?? 'active', $image_main,
                $data['virtual_tour_url'] ?? null, $data['video_url'] ?? null, $floor_plan_image
            ]);
            echo json_encode(['success' => true, 'id' => db()->lastInsertId()]);
        }
        break;

    case 'PUT':
        // PUT doesn't support multipart/form-data natively in PHP's $_POST, so we'll handle updates via POST with a special flag if needed.
        // For standard JSON PUT updates:
        $data = json_decode(file_get_contents('php://input'), true);
        if ($data && isset($data['id'])) {
            $stmt = db()->prepare("UPDATE properties SET title=?, description=?, price=?, type=?, property_type=?, bedrooms=?, bathrooms=?, area_sqft=?, address=?, city=?, state=?, zip=?, category_id=?, agent_id=?, featured=?, status=? WHERE id=?");
            $stmt->execute([
                $data['title'], $data['description'], $data['price'], $data['type'],
                $data['property_type'], $data['bedrooms'], $data['bathrooms'], $data['area_sqft'],
                $data['address'], $data['city'], $data['state'], $data['zip'],
                $data['category_id'], $data['agent_id'], $data['featured'] ?? 0, $data['status'] ?? 'active', $data['id']
            ]);
        }
        echo json_encode(['success' => true]);
        break;

    case 'DELETE':
        $id = $_GET['id'] ?? 0;
        
        if ($is_agent) {
            $check = db()->prepare("SELECT agent_id FROM properties WHERE id = ?");
            $check->execute([$id]);
            if ($check->fetchColumn() != $user_id) {
                echo json_encode(['error' => 'Unauthorized to delete this property.']);
                exit;
            }
        }
        
        $stmt = db()->prepare("DELETE FROM properties WHERE id = ?");
        $stmt->execute([$id]);
        echo json_encode(['success' => true]);
        break;

    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
}