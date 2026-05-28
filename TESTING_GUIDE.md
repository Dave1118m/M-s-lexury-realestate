# Property Reservation Validation System - Testing Guide

## Pre-Testing Checklist

### 1. Database Setup
- [ ] Run migration: `migrations/20260527_add_reservation_validation.sql`
- [ ] Verify tables exist: `reservations`, `saved_properties`, `saved_searches`, `payments`
- [ ] Check constraints: `UNIQUE KEY unique_active_reservation`
- [ ] Verify foreign keys are set up correctly

### 2. File Deployment
- [ ] `database.sql` - Updated with new tables
- [ ] `includes/functions.php` - Contains validation functions
- [ ] `api/process_payment.php` - Enhanced with validation logic
- [ ] `pages/payment_demo.php` - Includes date input field
- [ ] `admin/reservations.php` - New admin dashboard

### 3. System Configuration
- [ ] PHP error reporting enabled for debugging
- [ ] Database connection tested
- [ ] Session handling verified

---

## Functional Testing

### Test Group 1: Date Validation

#### Test 1.1: Past Date Rejection
**Objective**: Ensure system rejects past dates
1. Open property page and click "Reserve Property"
2. Attempt to set reservation date to today or earlier
3. Expected: Form validation should prevent submission (HTML5 date input min constraint)
4. Result: ✓ **PASS** / ✗ **FAIL**

**Verification SQL**:
```sql
-- No reservations should be created with past dates
SELECT COUNT(*) as past_reservations 
FROM reservations 
WHERE reservation_date < CURDATE();
```
Expected result: 0

#### Test 1.2: Future Date Acceptance
**Objective**: Ensure valid future dates are accepted
1. Open property page and click "Reserve Property"
2. Set reservation date to tomorrow
3. Complete payment process
4. Expected: Reservation should be created successfully
5. Result: ✓ **PASS** / ✗ **FAIL**

**Verification SQL**:
```sql
-- Check reservation was created with future date
SELECT * FROM reservations 
WHERE reservation_date = CURDATE() + INTERVAL 1 DAY 
AND status = 'active'
ORDER BY created_at DESC LIMIT 1;
```

#### Test 1.3: Maximum Date Validation
**Objective**: Prevent reservations more than 1 year in advance
1. Manipulate form to set date > 1 year from now
2. Attempt to submit
3. Expected: API should reject with error message
4. Result: ✓ **PASS** / ✗ **FAIL**

**Manual API Test**:
```bash
curl -X POST http://localhost/realstate/api/process_payment.php \
  -H "Content-Type: application/json" \
  -d '{
    "property_id": 1,
    "amount": 50000,
    "payment_method": "Credit/Debit Card",
    "transaction_reference": "TEST123",
    "reservation_date": "2027-06-01"
  }'
```
Expected: `{"success": false, "error": "Reservation date cannot be more than 1 year in the future."}`

---

### Test Group 2: Property Status Validation

#### Test 2.1: Active Property Reservation
**Objective**: Ensure active properties can be reserved
1. Verify property status is 'active' in database
2. Attempt to reserve
3. Expected: Reservation successful, property status changes to 'pending'
4. Result: ✓ **PASS** / ✗ **FAIL**

**Verification SQL**:
```sql
-- Check property status changed from active to pending
SELECT id, status FROM properties WHERE id = [PROPERTY_ID];
```

#### Test 2.2: Already Reserved Property Rejection
**Objective**: Prevent double-booking of reserved (pending) properties
1. User A reserves Property X successfully
2. User B attempts to reserve Property X
3. Expected: Error message "already reserved by another buyer"
4. Result: ✓ **PASS** / ✗ **FAIL**

**Verification SQL**:
```sql
-- Check only one active reservation exists
SELECT COUNT(*) as active_res_count 
FROM reservations 
WHERE property_id = [PROPERTY_ID] 
AND status = 'active';
```
Expected: 1 (only first user's reservation)

#### Test 2.3: Sold Property Rejection
**Objective**: Prevent reservation of sold properties
1. Admin marks property as 'sold'
2. User attempts to reserve
3. Expected: Error message "property has already been sold"
4. Result: ✓ **PASS** / ✗ **FAIL**

**Manual Setup**:
```sql
UPDATE properties SET status = 'sold' WHERE id = [TEST_PROPERTY_ID];
```

#### Test 2.4: Property Status Transitions
**Objective**: Verify correct status flow: active → pending → sold
1. Start: Property status = 'active'
2. After reservation: Property status = 'pending'
3. After final payment: Property status = 'sold'
4. Result: ✓ **PASS** / ✗ **FAIL**

**Timeline Verification SQL**:
```sql
SELECT p.id, p.status, 
       COUNT(r.id) as reservation_count,
       MAX(r.created_at) as last_reservation
FROM properties p 
LEFT JOIN reservations r ON p.id = r.property_id
WHERE p.id = [PROPERTY_ID]
GROUP BY p.id;
```

---

### Test Group 3: User Constraint Validation

#### Test 3.1: Prevent Double-Booking Same Property
**Objective**: User cannot reserve same property twice
1. User A reserves Property X (Reservation created)
2. User A attempts to reserve Property X again
3. Expected: Error "already have active reservation for this property"
4. Result: ✓ **PASS** / ✗ **FAIL**

**Verification SQL**:
```sql
-- Check only one active reservation per user per property
SELECT user_id, property_id, COUNT(*) as res_count
FROM reservations 
WHERE status = 'active'
GROUP BY user_id, property_id
HAVING res_count > 1;
```
Expected: Empty result (no violations)

#### Test 3.2: Multiple Properties by Same User
**Objective**: User can reserve different properties
1. User A reserves Property X successfully
2. User A reserves Property Y successfully
3. Expected: Both reservations created successfully
4. Result: ✓ **PASS** / ✗ **FAIL**

**Verification SQL**:
```sql
-- Check user has multiple active reservations
SELECT user_id, COUNT(*) as property_count
FROM reservations 
WHERE user_id = [USER_ID] 
AND status = 'active'
GROUP BY user_id;
```

#### Test 3.3: Multiple Users - Same Property
**Objective**: Multiple users cannot reserve same property (already tested 2.2)
Additional verification with different timing:
1. User A reserve Property X at Time T1 (Success)
2. User B reserve Property X at Time T2 (Failure)
3. Expected: Only User A's reservation exists
4. Result: ✓ **PASS** / ✗ **FAIL**

---

### Test Group 4: Payment Processing

#### Test 4.1: Successful Payment and Reservation Creation
**Objective**: Complete end-to-end successful flow
1. Logged-in user navigates to active property
2. Sets valid future reservation date
3. Selects payment method and enters details
4. Submits form
5. Expected: 
   - Reservation record created
   - Payment record created
   - Property status changed to 'pending'
   - Success page displayed
6. Result: ✓ **PASS** / ✗ **FAIL**

**Verification SQL**:
```sql
-- Check all three records created
SELECT 
  'Reservation' as RecordType, COUNT(*) as Count 
FROM reservations WHERE property_id = [PROPERTY_ID]
UNION ALL
SELECT 'Payment', COUNT(*) FROM payments WHERE property_id = [PROPERTY_ID]
UNION ALL
SELECT 'Property Status', COUNT(*) FROM properties 
WHERE id = [PROPERTY_ID] AND status = 'pending';
```

#### Test 4.2: Payment Failure Rollback
**Objective**: Ensure transaction rollback on failure
1. Simulate database error during payment insertion
2. Expected: Both reservation and payment should not be created
3. Property status remains 'active'
4. Result: ✓ **PASS** / ✗ **FAIL**

---

### Test Group 5: Database Constraints

#### Test 5.1: Unique Constraint Verification
**Objective**: Database enforces only one active reservation per property
1. Attempt to manually insert duplicate active reservation for same property
2. Expected: Database error (duplicate key violation)
3. Result: ✓ **PASS** / ✗ **FAIL**

**Manual Test**:
```sql
-- This should fail with UNIQUE constraint error
INSERT INTO reservations 
(user_id, property_id, reservation_date, status)
VALUES 
(1, [PROPERTY_ID], CURDATE() + INTERVAL 2 DAY, 'active'),
(2, [PROPERTY_ID], CURDATE() + INTERVAL 3 DAY, 'active');
```

#### Test 5.2: Foreign Key Constraint
**Objective**: Verify foreign key relationships
1. Attempt to create reservation with non-existent user_id
2. Expected: Database error (foreign key constraint)
3. Result: ✓ **PASS** / ✗ **FAIL**

---

### Test Group 6: API Response Validation

#### Test 6.1: Error Response Format
**Objective**: API returns structured error responses
1. Submit invalid data to process_payment.php
2. Expected response:
```json
{
  "success": false,
  "error": "descriptive error message"
}
```
3. Result: ✓ **PASS** / ✗ **FAIL**

#### Test 6.2: Success Response Format
**Objective**: API returns complete success data
1. Submit valid reservation data
2. Expected response includes:
   - success: true
   - reservation_id
   - reservation_code
   - property_title
   - reservation_date
   - amount
   - payment_method
   - reference
   - date
3. Result: ✓ **PASS** / ✗ **FAIL**

---

## Edge Cases Testing

### Edge Case 1: Time Zone Issues
- [ ] Test reservation at midnight
- [ ] Verify date handling across time zones

### Edge Case 2: Concurrent Requests
- [ ] Two users simultaneously reserve same property
- [ ] Expected: One succeeds, one fails with "already reserved"

### Edge Case 3: Boundary Dates
- [ ] Test reservation exactly 1 year from today
- [ ] Test reservation 1 year + 1 day (should fail)

### Edge Case 4: Database Constraints
- [ ] Insert direct SQL to violate unique constraint
- [ ] Expected: Operation fails silently

---

## Performance Testing

### Performance Test 1: Query Performance
**Objective**: Queries execute within acceptable time
```sql
-- Test reservation lookup
SELECT COUNT(*) FROM reservations WHERE property_id = [PROPERTY_ID];
-- Expected: < 100ms

-- Test active reservations for user
SELECT COUNT(*) FROM reservations 
WHERE user_id = [USER_ID] AND status = 'active';
-- Expected: < 100ms
```

### Performance Test 2: Concurrent Reservations
- [ ] Test 10 simultaneous reservation attempts
- [ ] Verify database handles concurrency correctly
- [ ] Expected: One succeeds, others fail appropriately

---

## Security Testing

### Security Test 1: SQL Injection Prevention
- [ ] Attempt SQL injection in reservation date field
- [ ] Expected: Query fails safely

### Security Test 2: Authentication
- [ ] Attempt reservation without logging in
- [ ] Expected: Redirect to login page

### Security Test 3: Authorization
- [ ] User attempts to cancel other user's reservation
- [ ] Expected: Unauthorized error

---

## Admin Dashboard Testing

### Dashboard Test 1: View Reservations
- [ ] Open `/admin/reservations.php`
- [ ] Verify all active reservations display
- [ ] Check stats are accurate

### Dashboard Test 2: Property Inventory Status
- [ ] Verify property count by status
- [ ] Check calculations match database

### Dashboard Test 3: Validation Constraints Display
- [ ] Verify all constraints are documented
- [ ] Check test scenarios are clear

---

## Test Summary Template

| Test ID | Test Name | Status | Notes | Date |
|---------|-----------|--------|-------|------|
| 1.1 | Past Date Rejection | ✓/✗ | | |
| 1.2 | Future Date Acceptance | ✓/✗ | | |
| 1.3 | Maximum Date Validation | ✓/✗ | | |
| 2.1 | Active Property Reservation | ✓/✗ | | |
| 2.2 | Already Reserved Property Rejection | ✓/✗ | | |
| 2.3 | Sold Property Rejection | ✓/✗ | | |
| 2.4 | Property Status Transitions | ✓/✗ | | |
| 3.1 | Prevent Double-Booking | ✓/✗ | | |
| 3.2 | Multiple Properties by Same User | ✓/✗ | | |
| 3.3 | Multiple Users Same Property | ✓/✗ | | |
| 4.1 | Complete Payment & Reservation | ✓/✗ | | |
| 4.2 | Payment Failure Rollback | ✓/✗ | | |
| 5.1 | Unique Constraint Enforcement | ✓/✗ | | |
| 5.2 | Foreign Key Constraint | ✓/✗ | | |
| 6.1 | Error Response Format | ✓/✗ | | |
| 6.2 | Success Response Format | ✓/✗ | | |

---

## Regression Testing

After any updates:
- [ ] Re-run all tests
- [ ] Verify no new issues introduced
- [ ] Update test results

---

**Testing Started**: [DATE]
**Testing Completed**: [DATE]
**Tester**: [NAME]
**Overall Status**: [PASS/FAIL]
