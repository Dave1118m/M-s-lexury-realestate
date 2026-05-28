<?php
/**
 * Hawassa Luxury Real Estate - Process Reservation Payment
 * Comprehensive validation for property reservations and purchases
 */
require_once '../includes/bootstrap.php';

header('Content-Type: application/json');

if (!is_logged_in()) {
    echo json_encode(['success' => false, 'error' => 'Please log in to reserve properties.']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$property_id = isset($data['property_id']) ? intval($data['property_id']) : 0;
$amount = isset($data['amount']) ? floatval($data['amount']) : 0.0;
$payment_method = isset($data['payment_method']) ? trim($data['payment_method']) : '';
$transaction_reference = isset($data['transaction_reference']) ? trim($data['transaction_reference']) : '';
$reservation_date = isset($data['reservation_date']) ? trim($data['reservation_date']) : '';
$user_id = $_SESSION['user_id'];

// ============================================================
// VALIDATION 1: Check all required fields are provided
// ============================================================
$validation_errors = [];

if (!$property_id) {
    $validation_errors[] = 'Invalid property ID.';
}

if ($amount <= 0) {
    $validation_errors[] = 'Invalid amount provided.';
}

if (empty($payment_method)) {
    $validation_errors[] = 'Payment method is required.';
}

if (empty($transaction_reference)) {
    $validation_errors[] = 'Transaction reference is required.';
}

if (empty($reservation_date)) {
    $validation_errors[] = 'Reservation date is required.';
}

if (!empty($validation_errors)) {
    echo json_encode([
        'success' => false,
        'error' => implode(' ', $validation_errors)
    ]);
    exit;
}

try {
    $db = db();

    // ============================================================
    // VALIDATION 2: Check reservation date is not in the past
    // ============================================================
    $date_validation = validate_reservation_date($reservation_date);
    if (!$date_validation['valid']) {
        echo json_encode([
            'success' => false,
            'error' => $date_validation['message']
        ]);
        exit;
    }

    // ============================================================
    // VALIDATION 3: Check property exists and is available
    // ============================================================
    $availability_check = is_property_available($property_id);
    if (!$availability_check['available']) {
        echo json_encode([
            'success' => false,
            'error' => $availability_check['message']
        ]);
        exit;
    }

    // ============================================================
    // VALIDATION 4: Check user hasn't already reserved this property
    // ============================================================
    if (user_has_active_reservation($user_id, $property_id)) {
        echo json_encode([
            'success' => false,
            'error' => 'You already have an active reservation for this property. Please cancel it first if you want to make a new reservation.'
        ]);
        exit;
    }

    // ============================================================
    // VALIDATION 5: Get full property details for reference
    // ============================================================
    $stmt = $db->prepare("SELECT status, title, price FROM properties WHERE id = ?");
    $stmt->execute([$property_id]);
    $property = $stmt->fetch();

    if (!$property) {
        echo json_encode(['success' => false, 'error' => 'Property not found.']);
        exit;
    }

    // Double-check property status
    if ($property['status'] !== 'active') {
        echo json_encode([
            'success' => false,
            'error' => 'This property is no longer available. Status: ' . $property['status']
        ]);
        exit;
    }

    // ============================================================
    // PROCESS: Create reservation within transaction
    // ============================================================
    $db->beginTransaction();

    try {
        // 1. Create reservation record
        $stmt_res = $db->prepare("
            INSERT INTO reservations 
            (user_id, property_id, reservation_date, payment_method, transaction_reference, hold_amount, status)
            VALUES (?, ?, ?, ?, ?, ?, 'active')
        ");
        $stmt_res->execute([$user_id, $property_id, $reservation_date, $payment_method, $transaction_reference, $amount]);
        $reservation_id = $db->lastInsertId();

        // 2. Create payment record linked to reservation
        $stmt_pay = $db->prepare("
            INSERT INTO payments 
            (user_id, property_id, reservation_id, amount, payment_method, transaction_reference, status)
            VALUES (?, ?, ?, ?, ?, ?, 'success')
        ");
        $stmt_pay->execute([$user_id, $property_id, $reservation_id, $amount, $payment_method, $transaction_reference]);

        // 3. Update property status to pending
        $stmt_upd = $db->prepare("UPDATE properties SET status = 'pending' WHERE id = ?");
        $stmt_upd->execute([$property_id]);

        $db->commit();

        // ============================================================
        // SUCCESS RESPONSE
        // ============================================================
        echo json_encode([
            'success' => true,
            'message' => 'Property reserved successfully!',
            'reservation_id' => $reservation_id,
            'reservation_code' => 'HW-RES-' . strtoupper(substr(md5(uniqid(rand(), true)), 0, 8)),
            'property_title' => $property['title'],
            'reservation_date' => $reservation_date,
            'amount' => $amount,
            'payment_method' => $payment_method,
            'reference' => $transaction_reference,
            'date' => date('M d, Y H:i')
        ]);

    } catch (PDOException $e) {
        $db->rollBack();
        echo json_encode([
            'success' => false,
            'error' => 'Database error during reservation process: ' . $e->getMessage()
        ]);
    }

} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Database connection error: ' . $e->getMessage()
    ]);
}
?>
