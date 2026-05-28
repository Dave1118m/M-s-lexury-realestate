<?php
/**
 * Hawassa Luxury Real Estate - Helper Functions
 */


function get_setting($key) {
    $stmt = db()->prepare("SELECT setting_value FROM site_settings WHERE setting_key = ?");
    $stmt->execute([$key]);
    $row = $stmt->fetch();
    return $row ? $row['setting_value'] : '';
}

function get_properties($limit = 6, $featured = false) {
    $sql = "SELECT p.*, c.name AS category_name, u.name AS agent_name 
            FROM properties p 
            LEFT JOIN categories c ON p.category_id = c.id 
            LEFT JOIN users u ON p.agent_id = u.id 
            WHERE p.status = 'active'";
    if ($featured) $sql .= " AND p.featured = 1";
    $sql .= " ORDER BY p.created_at DESC LIMIT ?";
    $stmt = db()->prepare($sql);
    $stmt->execute([$limit]);
    return $stmt->fetchAll();
}

function get_property($slug) {
    $stmt = db()->prepare("
        SELECT p.*, c.name AS category_name, u.name AS agent_name, u.email AS agent_email, u.phone AS agent_phone
        FROM properties p 
        LEFT JOIN categories c ON p.category_id = c.id 
        LEFT JOIN users u ON p.agent_id = u.id 
        WHERE p.slug = ?
    ");
    $stmt->execute([$slug]);
    $property = $stmt->fetch();
    if ($property) {
        $stmt2 = db()->prepare("SELECT feature_name FROM property_features WHERE property_id = ?");
        $stmt2->execute([$property['id']]);
        $property['features'] = $stmt2->fetchAll(PDO::FETCH_COLUMN);
    }
    return $property;
}

function get_other_properties($exclude_id, $limit = 3) {
    $stmt = db()->prepare("
        SELECT p.*, c.name AS category_name 
        FROM properties p 
        LEFT JOIN categories c ON p.category_id = c.id 
        WHERE p.id != ? AND p.status = 'active'
        ORDER BY RAND() 
        LIMIT ?
    ");
    $stmt->bindValue(1, $exclude_id, PDO::PARAM_INT);
    $stmt->bindValue(2, $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

function get_agents($limit = 8) {
    $stmt = db()->prepare("SELECT * FROM users WHERE role = 'agent' LIMIT ?");
    $stmt->execute([$limit]);
    return $stmt->fetchAll();
}

function get_blog_posts($limit = 4, $category = null) {
    if ($category) {
        $stmt = db()->prepare("SELECT bp.*, u.name AS author_name FROM blog_posts bp LEFT JOIN users u ON bp.author_id = u.id WHERE bp.status = 'published' AND bp.category = ? ORDER BY bp.created_at DESC LIMIT ?");
        $stmt->bindValue(1, $category, PDO::PARAM_STR);
        $stmt->bindValue(2, $limit, PDO::PARAM_INT);
    } else {
        $stmt = db()->prepare("SELECT bp.*, u.name AS author_name FROM blog_posts bp LEFT JOIN users u ON bp.author_id = u.id WHERE bp.status = 'published' ORDER BY bp.created_at DESC LIMIT ?");
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
    }
    $stmt->execute();
    return $stmt->fetchAll();
}

function get_testimonials() {
    $stmt = db()->query("SELECT * FROM testimonials ORDER BY created_at DESC");
    return $stmt->fetchAll();
}

function get_categories() {
    $stmt = db()->query("SELECT * FROM categories ORDER BY name");
    return $stmt->fetchAll();
}

function is_logged_in() {
    return isset($_SESSION['user_id']);
}

function is_admin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

function redirect($url) {
    // If headers have already been sent (e.g., due to early output), fall back to JavaScript redirect
    if (!headers_sent()) {
        header("Location: $url");
    } else {
        echo "<script>window.location.href='" . htmlspecialchars($url, ENT_QUOTES) . "';</script>";
    }
    exit;
}

function sanitize($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

function format_price($price) {
    if ($price >= 1000000) {
        return '$' . number_format($price / 1000000, 1) . 'M';
    } elseif ($price >= 1000) {
        return '$' . number_format($price / 1000, 1) . 'K';
    }
    return '$' . number_format($price);
}

function slugify($text) {
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    $text = preg_replace('~[^-\w]+~', '', $text);
    $text = trim($text, '-');
    $text = preg_replace('~-+~', '-', $text);
    $text = strtolower($text);
    return $text ?: 'n-a';
}

function is_agent() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'agent';
}

function is_property_saved($user_id, $property_id) {
    $stmt = db()->prepare("SELECT id FROM saved_properties WHERE user_id = ? AND property_id = ?");
    $stmt->execute([$user_id, $property_id]);
    return $stmt->fetch() !== false;
}

function get_saved_properties($user_id) {
    $stmt = db()->prepare("
        SELECT p.*, c.name AS category_name 
        FROM properties p 
        JOIN saved_properties sp ON p.id = sp.property_id 
        LEFT JOIN categories c ON p.category_id = c.id 
        WHERE sp.user_id = ?
        ORDER BY sp.created_at DESC
    ");
    $stmt->execute([$user_id]);
    return $stmt->fetchAll();
}

function get_saved_searches($user_id) {
    $stmt = db()->prepare("
        SELECT * FROM saved_searches 
        WHERE user_id = ? 
        ORDER BY created_at DESC
    ");
    $stmt->execute([$user_id]);
    return $stmt->fetchAll();
}

function get_user_by_id($user_id) {
    $stmt = db()->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    return $stmt->fetch();
}

function get_properties_by_agent($agent_id) {
    $stmt = db()->prepare("SELECT * FROM properties WHERE agent_id = ? AND status = 'active'");
    $stmt->execute([$agent_id]);
    return $stmt->fetchAll();
}

function get_chats_by_agent($agent_id) {
    // Assuming a chat_messages table with columns: id, agent_id, client_name, message, created_at
    $stmt = db()->prepare("SELECT * FROM chat_messages WHERE agent_id = ? ORDER BY created_at DESC");
    $stmt->execute([$agent_id]);
    return $stmt->fetchAll();
}

function can_manage_lead($lead_id) {
    if (!is_logged_in()) return false;
    $user_id = $_SESSION['user_id'];
    $stmt = db()->prepare('SELECT agent_id FROM leads WHERE id = ?');
    $stmt->execute([$lead_id]);
    $row = $stmt->fetch();
    return $row && $row['agent_id'] == $user_id;
}


function get_property_by_id($id) {
    $stmt = db()->prepare("
        SELECT p.*, c.name AS category_name, u.name AS agent_name, u.email AS agent_email, u.phone AS agent_phone
        FROM properties p 
        LEFT JOIN categories c ON p.category_id = c.id 
        LEFT JOIN users u ON p.agent_id = u.id 
        WHERE p.id = ?
    ");
    $stmt->execute([$id]);
    $property = $stmt->fetch();
    if ($property) {
        $stmt2 = db()->prepare("SELECT feature_name FROM property_features WHERE property_id = ?");
        $stmt2->execute([$property['id']]);
        $property['features'] = $stmt2->fetchAll(PDO::FETCH_COLUMN);
    }
    return $property;
}

/**
 * ============================================================
 * RESERVATION AND PAYMENT VALIDATION FUNCTIONS
 * ============================================================
 */

/**
 * Check if reservation date is valid (not in the past)
 * @param string $reservation_date Date in format YYYY-MM-DD
 * @return array ['valid' => bool, 'message' => string]
 */
function validate_reservation_date($reservation_date) {
    $today = date('Y-m-d');
    
    if (empty($reservation_date)) {
        return [
            'valid' => false,
            'message' => 'Reservation date is required.'
        ];
    }
    
    if ($reservation_date < $today) {
        return [
            'valid' => false,
            'message' => 'Reservation date cannot be in the past. Please select a future date.'
        ];
    }
    
    // Optionally check if date is within a reasonable range (e.g., not more than 1 year in future)
    $max_date = date('Y-m-d', strtotime('+1 year'));
    if ($reservation_date > $max_date) {
        return [
            'valid' => false,
            'message' => 'Reservation date cannot be more than 1 year in the future.'
        ];
    }
    
    return [
        'valid' => true,
        'message' => 'Reservation date is valid.'
    ];
}

/**
 * Check if property is available for reservation/purchase
 * @param int $property_id Property ID
 * @return array ['available' => bool, 'status' => string, 'message' => string]
 */
function is_property_available($property_id) {
    $stmt = db()->prepare("SELECT status FROM properties WHERE id = ?");
    $stmt->execute([$property_id]);
    $property = $stmt->fetch();
    
    if (!$property) {
        return [
            'available' => false,
            'status' => 'not_found',
            'message' => 'Property not found.'
        ];
    }
    
    // Check property status
    if ($property['status'] === 'sold') {
        return [
            'available' => false,
            'status' => 'sold',
            'message' => 'This property has already been sold.'
        ];
    }
    
    if ($property['status'] === 'pending') {
        return [
            'available' => false,
            'status' => 'reserved',
            'message' => 'This property is already reserved by another buyer. Please try another property.'
        ];
    }
    
    if ($property['status'] !== 'active') {
        return [
            'available' => false,
            'status' => 'inactive',
            'message' => 'This property is not currently available for reservation.'
        ];
    }
    
    // Check for active reservation in reservations table
    $stmt = db()->prepare("
        SELECT id FROM reservations 
        WHERE property_id = ? AND status = 'active'
    ");
    $stmt->execute([$property_id]);
    
    if ($stmt->fetch()) {
        return [
            'available' => false,
            'status' => 'reserved',
            'message' => 'This property is already reserved by another buyer. Please try another property.'
        ];
    }
    
    return [
        'available' => true,
        'status' => 'active',
        'message' => 'Property is available for reservation.'
    ];
}

/**
 * Check if user has already reserved this property
 * @param int $user_id User ID
 * @param int $property_id Property ID
 * @return bool
 */
function user_has_active_reservation($user_id, $property_id) {
    $stmt = db()->prepare("
        SELECT id FROM reservations 
        WHERE user_id = ? AND property_id = ? AND status = 'active'
    ");
    $stmt->execute([$user_id, $property_id]);
    return $stmt->fetch() !== false;
}

/**
 * Get all active reservations for a property
 * @param int $property_id Property ID
 * @return array
 */
function get_property_reservations($property_id, $status = 'active') {
    $stmt = db()->prepare("
        SELECT r.*, u.name, u.email 
        FROM reservations r 
        JOIN users u ON r.user_id = u.id 
        WHERE r.property_id = ? AND r.status = ?
        ORDER BY r.created_at DESC
    ");
    $stmt->execute([$property_id, $status]);
    return $stmt->fetchAll();
}

/**
 * Get user's active reservations
 * @param int $user_id User ID
 * @return array
 */
function get_user_reservations($user_id, $status = 'active') {
    $stmt = db()->prepare("
        SELECT r.*, p.title, p.slug, p.price, p.image_main, p.status as property_status
        FROM reservations r 
        JOIN properties p ON r.property_id = p.id 
        WHERE r.user_id = ? AND r.status = ?
        ORDER BY r.created_at DESC
    ");
    $stmt->execute([$user_id, $status]);
    return $stmt->fetchAll();
}

/**
 * Create a new reservation
 * @param int $user_id User ID
 * @param int $property_id Property ID
 * @param string $reservation_date Reservation date (YYYY-MM-DD)
 * @param array $payment_info Payment info (method, reference, amount)
 * @return array ['success' => bool, 'reservation_id' => int|null, 'message' => string]
 */
function create_reservation($user_id, $property_id, $reservation_date, $payment_info = []) {
    try {
        // Validate reservation date
        $date_validation = validate_reservation_date($reservation_date);
        if (!$date_validation['valid']) {
            return [
                'success' => false,
                'reservation_id' => null,
                'message' => $date_validation['message']
            ];
        }
        
        // Check if property is available
        $availability = is_property_available($property_id);
        if (!$availability['available']) {
            return [
                'success' => false,
                'reservation_id' => null,
                'message' => $availability['message']
            ];
        }
        
        // Check if user already has active reservation for this property
        if (user_has_active_reservation($user_id, $property_id)) {
            return [
                'success' => false,
                'reservation_id' => null,
                'message' => 'You already have an active reservation for this property.'
            ];
        }
        
        // Create reservation
        $db = db();
        $payment_method = $payment_info['payment_method'] ?? null;
        $transaction_reference = $payment_info['transaction_reference'] ?? null;
        $hold_amount = $payment_info['hold_amount'] ?? null;
        
        $stmt = $db->prepare("
            INSERT INTO reservations 
            (user_id, property_id, reservation_date, payment_method, transaction_reference, hold_amount, status)
            VALUES (?, ?, ?, ?, ?, ?, 'active')
        ");
        
        $stmt->execute([$user_id, $property_id, $reservation_date, $payment_method, $transaction_reference, $hold_amount]);
        $reservation_id = $db->lastInsertId();
        
        return [
            'success' => true,
            'reservation_id' => $reservation_id,
            'message' => 'Reservation created successfully.'
        ];
    } catch (PDOException $e) {
        return [
            'success' => false,
            'reservation_id' => null,
            'message' => 'Database error: ' . $e->getMessage()
        ];
    }
}

/**
 * Cancel a reservation
 * @param int $reservation_id Reservation ID
 * @return array ['success' => bool, 'message' => string]
 */
function cancel_reservation($reservation_id) {
    try {
        $db = db();
        $stmt = $db->prepare("UPDATE reservations SET status = 'cancelled' WHERE id = ?");
        $stmt->execute([$reservation_id]);
        
        return [
            'success' => true,
            'message' => 'Reservation cancelled successfully.'
        ];
    } catch (PDOException $e) {
        return [
            'success' => false,
            'message' => 'Database error: ' . $e->getMessage()
        ];
    }
}


