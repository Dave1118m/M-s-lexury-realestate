<?php
/**
 * Hawassa API - Property Search
 */
require_once '../includes/database.php';
require_once '../includes/functions.php';
header('Content-Type: application/json');

$q = $_GET['q'] ?? '';
$type = $_GET['type'] ?? '';
$category = $_GET['category'] ?? '';
$property_type = $_GET['property_type'] ?? '';
$min_price = $_GET['min_price'] ?? '';
$max_price = $_GET['max_price'] ?? '';
$bedrooms = $_GET['bedrooms'] ?? '';
$bathrooms = $_GET['bathrooms'] ?? '';
$sort = $_GET['sort'] ?? 'newest';

$sql = "SELECT * FROM properties WHERE status = 'active'";
$params = [];

if ($q) {
    $sql .= " AND (title LIKE ? OR address LIKE ? OR city LIKE ? OR state LIKE ?)";
    $search = "%$q%";
    $params = array_merge($params, [$search, $search, $search, $search]);
}
if ($type) {
    $sql .= " AND type = ?";
    $params[] = $type;
}
if ($category) {
    $sql .= " AND category_id = (SELECT id FROM categories WHERE slug = ?)";
    $params[] = $category;
}
if ($property_type) {
    $sql .= " AND property_type = ?";
    $params[] = $property_type;
}
if ($min_price) {
    $sql .= " AND price >= ?";
    $params[] = (int)$min_price;
}
if ($max_price) {
    $sql .= " AND price <= ?";
    $params[] = (int)$max_price;
}
if ($bedrooms) {
    $sql .= " AND bedrooms >= ?";
    $params[] = (int)$bedrooms;
}
if ($bathrooms) {
    $sql .= " AND bathrooms >= ?";
    $params[] = (int)$bathrooms;
}

// Sorting
switch ($sort) {
    case 'price_asc': $sql .= " ORDER BY price ASC"; break;
    case 'price_desc': $sql .= " ORDER BY price DESC"; break;
    default: $sql .= " ORDER BY created_at DESC";
}

$stmt = db()->prepare($sql);
$stmt->execute($params);
$properties = $stmt->fetchAll();

// Add dynamic fallback images if not present
foreach ($properties as &$prop) {
    if (empty($prop['image_main'])) {
        $prop['image_main'] = get_dynamic_property_image($prop['id']);
    }
}
unset($prop);

echo json_encode([
    'success' => true,
    'total' => count($properties),
    'properties' => $properties
]);