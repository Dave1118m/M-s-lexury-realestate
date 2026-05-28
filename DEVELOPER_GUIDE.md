# Quick Reference: Validation System API

## For Developers

### Using the Validation Functions

#### 1. Validate a Reservation Date
```php
$result = validate_reservation_date('2026-06-15');

if ($result['valid']) {
    // Date is valid
} else {
    echo $result['message']; // "Reservation date cannot be in the past..."
}
```

#### 2. Check if Property is Available
```php
$availability = is_property_available($property_id);

if ($availability['available']) {
    // Property can be reserved
} else {
    echo $availability['message']; // "Property already sold" or similar
}
```

#### 3. Check for User's Existing Reservation
```php
if (user_has_active_reservation($user_id, $property_id)) {
    echo "User already has active reservation for this property";
}
```

#### 4. Create a New Reservation
```php
$payment_info = [
    'payment_method' => 'Credit Card',
    'transaction_reference' => 'CC-123456',
    'hold_amount' => 50000
];

$result = create_reservation(
    $user_id, 
    $property_id, 
    '2026-06-15', 
    $payment_info
);

if ($result['success']) {
    $reservation_id = $result['reservation_id'];
    echo "Reservation created: " . $result['message'];
} else {
    echo "Error: " . $result['message'];
}
```

#### 5. Get All Property Reservations
```php
$reservations = get_property_reservations($property_id, 'active');

foreach ($reservations as $res) {
    echo $res['name'] . " - " . $res['reservation_date'];
}
```

#### 6. Get User's Reservations
```php
$my_reservations = get_user_reservations($_SESSION['user_id'], 'active');

foreach ($my_reservations as $res) {
    echo $res['title'] . " - " . format_price($res['price']);
}
```

#### 7. Cancel a Reservation
```php
$result = cancel_reservation($reservation_id);

if ($result['success']) {
    echo "Reservation cancelled";
} else {
    echo "Error: " . $result['message'];
}
```

---

## API Endpoint: `/api/process_payment.php`

### Request Format
```json
{
    "property_id": 1,
    "amount": 50000.00,
    "payment_method": "Credit/Debit Card",
    "transaction_reference": "CC-123456",
    "reservation_date": "2026-06-15"
}
```

### Success Response
```json
{
    "success": true,
    "message": "Property reserved successfully!",
    "reservation_id": 42,
    "reservation_code": "HW-RES-A1B2C3D4",
    "property_title": "Luxury Penthouse",
    "reservation_date": "2026-06-15",
    "amount": 50000.00,
    "payment_method": "Credit/Debit Card",
    "reference": "CC-123456",
    "date": "May 27, 2026 14:30"
}
```

### Error Response
```json
{
    "success": false,
    "error": "Descriptive error message"
}
```

### Possible Error Messages
- `"Please log in to reserve properties."`
- `"Invalid property ID."`
- `"Invalid amount provided."`
- `"Payment method is required."`
- `"Reservation date is required."`
- `"Reservation date cannot be in the past. Please select a future date."`
- `"Reservation date cannot be more than 1 year in the future."`
- `"Property not found."`
- `"This property has already been sold."`
- `"This property is already reserved by another buyer."`
- `"You already have an active reservation for this property."`
- `"This property is not currently available for reservation."`
- `"Database error..."`

---

## Database Schema Quick Reference

### Reservations Table
```sql
CREATE TABLE reservations (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    property_id INT NOT NULL,
    reservation_date DATE NOT NULL,
    status ENUM('active', 'completed', 'cancelled') DEFAULT 'active',
    payment_method VARCHAR(50),
    transaction_reference VARCHAR(100),
    hold_amount DECIMAL(15,2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_active_reservation (property_id, status),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE
);
```

### Key Constraints
- **Unique**: Only one 'active' reservation per property
- **Foreign Keys**: Cascading delete with users and properties
- **Indexes**: user_status, property_status, reservation_date

---

## Common Queries

### Get all active reservations
```sql
SELECT r.*, p.title, u.name 
FROM reservations r 
JOIN properties p ON r.property_id = p.id 
JOIN users u ON r.user_id = u.id 
WHERE r.status = 'active' 
ORDER BY r.created_at DESC;
```

### Check if property is reserved
```sql
SELECT COUNT(*) as is_reserved 
FROM reservations 
WHERE property_id = ? AND status = 'active';
```

### Get user's reservations
```sql
SELECT r.*, p.title, p.price 
FROM reservations r 
JOIN properties p ON r.property_id = p.id 
WHERE r.user_id = ? AND r.status = 'active';
```

### Properties by status
```sql
SELECT status, COUNT(*) as count 
FROM properties 
GROUP BY status;
```

---

## Form Integration Example

```html
<form id="paymentForm" method="POST">
    <!-- Reservation Date Input (NEW) -->
    <div class="form-group">
        <label for="reservationDate">Reservation Date *</label>
        <input type="date" id="reservationDate" name="reservation_date" required 
               min="<?php echo date('Y-m-d'); ?>" 
               value="<?php echo date('Y-m-d', strtotime('+7 days')); ?>">
    </div>

    <!-- Other form fields... -->
    <input type="hidden" name="property_id" value="<?php echo $property['id']; ?>">
    <input type="hidden" name="amount" value="<?php echo $total_due; ?>">

    <!-- Submit -->
    <button type="submit">Complete Reservation</button>
</form>

<script>
document.getElementById('paymentForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const payload = {
        property_id: document.querySelector('input[name="property_id"]').value,
        amount: document.querySelector('input[name="amount"]').value,
        payment_method: 'Credit Card',
        transaction_reference: 'TX-' + Date.now(),
        reservation_date: document.querySelector('input[name="reservation_date"]').value
    };

    fetch('/api/process_payment.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            console.log('Reservation created:', data.reservation_id);
            // Show success page
        } else {
            alert('Error: ' + data.error);
        }
    });
});
</script>
```

---

## Testing Commands

### Test successful reservation
```bash
curl -X POST http://localhost/realstate/api/process_payment.php \
  -H "Content-Type: application/json" \
  -H "Cookie: PHPSESSID=YOUR_SESSION_ID" \
  -d '{
    "property_id": 1,
    "amount": 50000,
    "payment_method": "Credit Card",
    "transaction_reference": "TEST123",
    "reservation_date": "2026-06-15"
  }'
```

### Test past date rejection
```bash
curl -X POST http://localhost/realstate/api/process_payment.php \
  -H "Content-Type: application/json" \
  -H "Cookie: PHPSESSID=YOUR_SESSION_ID" \
  -d '{
    "property_id": 1,
    "amount": 50000,
    "payment_method": "Credit Card",
    "transaction_reference": "TEST123",
    "reservation_date": "2024-05-01"
  }'
# Expected: Error about past date
```

---

## Troubleshooting

### Issue: Validation functions not found
**Check**: functions.php is updated and requires are correct

### Issue: Unique constraint violation
**Check**: Database has reservation table with unique constraint

### Issue: Date validation not working
**Check**: validate_reservation_date() function exists and is called

### Issue: Property status not updating
**Check**: process_payment.php has UPDATE statement for properties table

---

## Documentation Files

- `IMPLEMENTATION_REPORT.md` - Complete implementation details
- `VALIDATION_SUMMARY.md` - Technical specifications
- `TESTING_GUIDE.md` - Testing procedures and test cases
- `README.md` - General project documentation

---

**Version**: 1.0
**Last Updated**: May 27, 2026
**Compatibility**: PHP 7.4+, MySQL 5.7+
