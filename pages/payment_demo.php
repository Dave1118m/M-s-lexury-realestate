<?php
/**
 * Hawassa Luxury Real Estate - Interactive Reservation & Payment Portal
 */
require_once '../includes/header.php';

if (!is_logged_in()) {
    redirect('login.php');
}

$property_id = $_GET['property_id'] ?? null;
if (!$property_id) {
    echo '<section style="padding:6rem 0; text-align:center; min-height:50vh;">
            <div class="container">
                <h2 style="font-family:\'Playfair Display\', serif; font-size:2rem; color:var(--color-black);">Property ID missing</h2>
                <p style="margin-top:1rem;"><a href="search.php" class="btn btn-gold">Browse Properties</a></p>
            </div>
          </section>';
    require_once '../includes/footer.php';
    exit;
}

$property = get_property_by_id($property_id);
if (!$property) {
    echo '<section style="padding:6rem 0; text-align:center; min-height:50vh;">
            <div class="container">
                <h2 style="font-family:\'Playfair Display\', serif; font-size:2rem; color:var(--color-black);">Property not found</h2>
                <p style="margin-top:1rem;"><a href="search.php" class="btn btn-gold">Browse Properties</a></p>
            </div>
          </section>';
    require_once '../includes/footer.php';
    exit;
}

if ($property['status'] !== 'active') {
    echo '<section style="padding:6rem 0; text-align:center; min-height:50vh;">
            <div class="container">
                <h2 style="font-family:\'Playfair Display\', serif; font-size:2rem; color:#dc3545;">🔒 Property Already Reserved</h2>
                <p style="color:var(--color-gray-500); margin:1rem 0 2rem;">This luxury property is under contract or has already been reserved by another client.</p>
                <p><a href="property.php?slug=' . urlencode($property['slug']) . '" class="btn btn-outline">Go to Property Details</a></p>
            </div>
          </section>';
    require_once '../includes/footer.php';
    exit;
}

// Calculations
$price = $property['price'];
$reservation_fee = $price * 0.02; // 2% Hold Deposit
$admin_fee = 7500.00; // Flat administrative fee in Birr or equivalent (Let's make it flat 7,500 Birr / $150.00 equivalent depending on price, we'll format dynamically)
$total_due = $reservation_fee + $admin_fee;
?>

<style>
/* Checkout Layout Styles */
.checkout-wrapper {
    display: grid;
    grid-template-columns: 1fr 1.25fr;
    gap: 3rem;
    padding: 3rem 0;
    align-items: start;
}

@media (max-width: 991px) {
    .checkout-wrapper {
        grid-template-columns: 1fr;
        gap: 2rem;
    }
}

/* Glassmorphism Panels */
.checkout-panel {
    background: var(--color-white);
    border: 1px solid var(--color-gray-200);
    border-radius: 12px;
    padding: 2.5rem;
    box-shadow: var(--shadow-lg);
}

[data-theme="dark"] .checkout-panel {
    background: rgba(30, 30, 30, 0.6);
    border-color: rgba(200, 169, 110, 0.15);
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
}

/* Tab Selector */
.payment-tabs {
    display: flex;
    gap: 0.5rem;
    border-bottom: 2px solid var(--color-gray-200);
    margin-bottom: 2rem;
    padding-bottom: 0.5rem;
}

[data-theme="dark"] .payment-tabs {
    border-bottom-color: rgba(255, 255, 255, 0.1);
}

.payment-tab {
    flex: 1;
    background: none;
    border: none;
    padding: 0.75rem;
    font-family: var(--font-body);
    font-weight: 600;
    font-size: 0.85rem;
    color: var(--color-gray-500);
    cursor: pointer;
    border-radius: var(--radius-sm);
    transition: all 0.3s ease;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.payment-tab.active {
    color: var(--color-gold);
    background: rgba(200, 169, 110, 0.1);
}

.payment-tab:hover:not(.active) {
    color: var(--color-gold);
    background: rgba(200, 169, 110, 0.04);
}

/* Inputs & Forms */
.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1.5rem;
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-group.full-width {
    grid-column: span 2;
}

.form-group label {
    display: block;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    margin-bottom: 0.5rem;
    color: var(--color-gray-700);
}

.form-group input,
.form-group select {
    width: 100%;
    padding: 0.85rem 1rem;
    border: 1px solid var(--color-gray-300);
    border-radius: var(--radius-sm);
    font-family: var(--font-body);
    font-size: 0.95rem;
    background: var(--color-white);
    color: var(--color-gray-900);
    transition: border-color 0.3s ease;
}

.form-group input:focus {
    border-color: var(--color-gold);
    outline: none;
}

/* Virtual Credit Card Container */
.card-container {
    perspective: 1000px;
    width: 320px;
    height: 190px;
    margin: 0 auto 2rem;
}

.credit-card-preview {
    width: 100%;
    height: 100%;
    position: relative;
    transform-style: preserve-3d;
    transition: transform 0.8s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}

.credit-card-preview.flipped {
    transform: rotateY(180deg);
}

.card-face {
    position: absolute;
    width: 100%;
    height: 100%;
    backface-visibility: hidden;
    border-radius: 12px;
    padding: 1.5rem;
    color: #fff;
    font-family: 'Share Tech Mono', monospace, sans-serif;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.25);
    background: linear-gradient(135deg, #1e1b15 0%, #302619 50%, #15120e 100%);
    border: 1px solid rgba(200, 169, 110, 0.3);
}

/* Gold marble card pattern overlay */
.card-face::before {
    content: '';
    position: absolute;
    inset: 0;
    background: linear-gradient(45deg, transparent 40%, rgba(200, 169, 110, 0.1) 45%, rgba(200, 169, 110, 0.2) 50%, rgba(200, 169, 110, 0.1) 55%, transparent 60%);
    background-size: 200% 200%;
    animation: goldShine 6s linear infinite;
    pointer-events: none;
    border-radius: 12px;
}

@keyframes goldShine {
    0% { background-position: 200% 0; }
    100% { background-position: -200% 0; }
}

.card-face-back {
    transform: rotateY(180deg);
    padding: 1.5rem 0;
}

.card-chip {
    width: 40px;
    height: 30px;
    background: linear-gradient(135deg, #c8a96e 0%, #a8884e 100%);
    border-radius: 4px;
    border: 1px solid rgba(255, 255, 255, 0.2);
}

.card-number-label {
    font-size: 1.25rem;
    letter-spacing: 0.15em;
    text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.6);
    margin-top: 1rem;
}

.card-bottom {
    display: flex;
    justify-content: space-between;
    align-items: flex-end;
}

.card-name-label {
    text-transform: uppercase;
    font-size: 0.8rem;
    letter-spacing: 0.05em;
    max-width: 200px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.card-expiry-label {
    font-size: 0.8rem;
}

.card-stripe {
    background: #000;
    height: 40px;
    width: 100%;
    margin-top: 0.5rem;
}

.card-signature-area {
    margin: 1rem 1.5rem 0;
    display: flex;
    justify-content: flex-end;
    align-items: center;
    background: #fff;
    height: 35px;
    border-radius: 3px;
    padding-right: 0.5rem;
}

.card-cvv-label {
    color: #000;
    font-weight: bold;
    font-size: 0.95rem;
    letter-spacing: 0.05em;
}

/* Spinner Overlay */
.overlay-loading {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.85);
    z-index: 9999;
    align-items: center;
    justify-content: center;
    color: #fff;
    flex-direction: column;
}

.loading-spinner-wrapper {
    text-align: center;
    max-width: 450px;
    padding: 2rem;
}

.spinner-outer {
    position: relative;
    width: 80px;
    height: 80px;
    margin: 0 auto 2rem;
    border: 3px solid rgba(200, 169, 110, 0.1);
    border-top-color: var(--color-gold);
    border-radius: 50%;
    animation: loadingSpinner 1s linear infinite;
}

.spinner-outer::before {
    content: '';
    position: absolute;
    inset: 6px;
    border: 3px solid rgba(255, 255, 255, 0.05);
    border-bottom-color: #fff;
    border-radius: 50%;
    animation: loadingSpinner 0.5s linear infinite reverse;
}

@keyframes loadingSpinner {
    to { transform: rotate(360deg); }
}

.loading-steps {
    list-style: none;
    padding: 0;
    margin-top: 1.5rem;
    font-size: 0.95rem;
    line-height: 2;
}

.loading-steps li {
    opacity: 0.3;
    transition: opacity 0.5s ease, color 0.5s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
}

.loading-steps li.active {
    opacity: 1;
    color: var(--color-gold);
    font-weight: 500;
}

.loading-steps li.done {
    opacity: 0.8;
    color: #28a745;
}

/* Success Card */
.success-card {
    display: none;
    max-width: 650px;
    margin: 3rem auto;
    text-align: center;
    background: #fff;
    border: 1px solid var(--color-gray-200);
    border-radius: 12px;
    padding: 3rem;
    box-shadow: var(--shadow-xl);
}

[data-theme="dark"] .success-card {
    background: rgba(30, 30, 30, 0.8);
    border-color: rgba(200, 169, 110, 0.2);
    backdrop-filter: blur(10px);
}

.success-badge {
    width: 70px;
    height: 70px;
    background: rgba(40, 167, 69, 0.1);
    color: #28a745;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2.25rem;
    margin: 0 auto 1.5rem;
    border: 2px solid #28a745;
}

.receipt-box {
    background: var(--color-cream);
    border: 1px dashed var(--color-gray-300);
    border-radius: 8px;
    padding: 1.5rem;
    margin: 2rem 0;
    text-align: left;
}

[data-theme="dark"] .receipt-box {
    background: rgba(255, 255, 255, 0.03);
    border-color: rgba(255, 255, 255, 0.15);
}

.receipt-row {
    display: flex;
    justify-content: space-between;
    margin-bottom: 0.75rem;
    font-size: 0.9rem;
}

.receipt-row:last-child {
    margin-bottom: 0;
    padding-top: 0.75rem;
    border-top: 1px solid var(--color-gray-300);
    font-weight: bold;
}

[data-theme="dark"] .receipt-row:last-child {
    border-top-color: rgba(255, 255, 255, 0.1);
}
</style>

<div class="container">
    <!-- Checkout Interface -->
    <div id="checkoutPortal" class="checkout-wrapper">
        
        <!-- Left: Summary Panel -->
        <div class="checkout-panel">
            <h3 style="font-family:'Playfair Display', serif; font-size:1.75rem; margin-bottom:1.5rem;">Reservation Summary</h3>
            <div style="border-radius:8px; overflow:hidden; margin-bottom:1.5rem; position:relative; height:180px;">
                <img src="<?php echo $property['image_main'] ?: get_dynamic_property_image($property['id'], 0); ?>" alt="<?php echo htmlspecialchars($property['title']); ?>" style="width:100%; height:100%; object-fit:cover;">
                <span class="property-badge" style="position:absolute; top:0.75rem; left:0.75rem; background:var(--color-black);"><?php echo htmlspecialchars(ucfirst($property['type'])); ?></span>
            </div>
            
            <h4 style="font-size:1.15rem; margin-bottom:0.25rem; font-family:'Playfair Display', serif;"><?php echo htmlspecialchars($property['title']); ?></h4>
            <p style="color:var(--color-gray-500); font-size:0.85rem; margin-bottom:1.5rem;"><?php echo htmlspecialchars($property['address'] . ', ' . $property['city']); ?></p>
            
            <div style="border-top:1px solid var(--color-gray-200); padding-top:1rem; margin-top:1rem;">
                <div style="display:flex; justify-content:space-between; margin-bottom:0.75rem;">
                    <span>Property Price:</span>
                    <strong><?php echo format_price($price); ?></strong>
                </div>
                <div style="display:flex; justify-content:space-between; margin-bottom:0.75rem; color:var(--color-gold);">
                    <span>Hold Deposit (2%):</span>
                    <strong><?php echo format_price($reservation_fee); ?></strong>
                </div>
                <div style="display:flex; justify-content:space-between; margin-bottom:0.75rem; color:var(--color-gray-500); font-size:0.85rem;">
                    <span>Administrative Fee:</span>
                    <span><?php echo format_price($admin_fee); ?></span>
                </div>
                <div style="display:flex; justify-content:space-between; margin-top:1rem; padding-top:1rem; border-top:1px solid var(--color-gray-200); font-size:1.2rem; font-family:'Playfair Display', serif;">
                    <span>Total Due Now:</span>
                    <strong style="color:var(--color-gold);"><?php echo format_price($total_due); ?></strong>
                </div>
            </div>
            
            <div style="margin-top:2rem; padding:1rem; background:rgba(200, 169, 110, 0.05); border:1px solid rgba(200, 169, 110, 0.2); border-radius:6px; font-size:0.8rem; color:var(--color-gray-600); display:flex; gap:0.5rem; align-items:center;">
                <span>🔒</span>
                <span>Payments are secured with 256-bit SSL encryption. Once paid, the property will be locked in your name under pending status.</span>
            </div>
        </div>

        <!-- Right: Payment Portal Form -->
        <div class="checkout-panel">
            <div class="payment-tabs">
                <button type="button" class="payment-tab active" data-target="cardPanel">💳 Card</button>
                <button type="button" class="payment-tab" data-target="telePanel">📱 Telebirr</button>
                <button type="button" class="payment-tab" data-target="cbePanel">🏛️ CBE Birr</button>
            </div>

            <!-- Form Panels Container -->
            <form id="paymentForm" autocomplete="off">
                <!-- Hidden inputs for validation -->
                <input type="hidden" name="property_id" value="<?php echo $property['id']; ?>">
                <input type="hidden" name="amount" value="<?php echo $total_due; ?>">
                <input type="hidden" id="selectedMethod" name="payment_method" value="Credit/Debit Card">

                <!-- Reservation Date (applies to all payment methods) -->
                <div class="form-group full-width">
                    <label for="reservationDate">Intended Reservation Date *</label>
                    <input type="date" id="reservationDate" name="reservation_date" required 
                           min="<?php echo date('Y-m-d'); ?>" 
                           style="width: 100%; padding: 0.85rem 1rem; border: 1px solid var(--color-gray-300); border-radius: var(--radius-sm); font-family: var(--font-body); font-size: 0.95rem; background: var(--color-white); color: var(--color-gray-900); transition: border-color 0.3s ease;">
                    <small style="display: block; margin-top: 0.35rem; color: var(--color-gray-500); font-size: 0.8rem;">Select the date you intend to complete the property reservation. Cannot be a past date.</small>
                </div>

                <!-- 1. Credit Card Panel -->
                <div id="cardPanel" class="tab-panel">
                    <!-- Interactive Virtual Card -->
                    <div class="card-container">
                        <div id="cardPreview" class="credit-card-preview">
                            <!-- Card Front -->
                            <div class="card-face">
                                <div style="display:flex; justify-content:space-between; align-items:flex-start;">
                                    <div class="card-chip"></div>
                                    <span style="font-family:'Playfair Display', serif; font-style:italic; font-weight:bold; font-size:1rem; color:var(--color-gold);">HAWASSA</span>
                                </div>
                                <div id="cardNumLabel" class="card-number-label">•••• •••• •••• ••••</div>
                                <div class="card-bottom">
                                    <div>
                                        <div style="font-size:0.5rem; text-transform:uppercase; color:rgba(255,255,255,0.4); margin-bottom:0.15rem;">Card Holder</div>
                                        <div id="cardNameLabel" class="card-name-label">Your Name</div>
                                    </div>
                                    <div style="text-align:right;">
                                        <div style="font-size:0.5rem; text-transform:uppercase; color:rgba(255,255,255,0.4); margin-bottom:0.15rem;">Expires</div>
                                        <div id="cardExpiryLabel" class="card-expiry-label">MM/YY</div>
                                    </div>
                                </div>
                            </div>
                            <!-- Card Back -->
                            <div class="card-face card-face-back">
                                <div class="card-stripe"></div>
                                <div>
                                    <div style="font-size:0.5rem; text-transform:uppercase; color:rgba(255,255,255,0.4); margin-bottom:0.15rem; margin-right:1.5rem; text-align:right;">CVV</div>
                                    <div class="card-signature-area">
                                        <div id="cardCvvLabel" class="card-cvv-label">•••</div>
                                    </div>
                                </div>
                                <div style="padding:0 1.5rem; text-align:right; font-size:0.6rem; color:rgba(255,255,255,0.4); font-family:sans-serif;">Luxury Real Estate Hold Security</div>
                            </div>
                        </div>
                    </div>

                    <!-- Fields -->
                    <div class="form-row">
                        <div class="form-group full-width">
                            <label for="ccName">Cardholder Name</label>
                            <input type="text" id="ccName" placeholder="JOHN DOE" required>
                        </div>
                        <div class="form-group full-width">
                            <label for="ccNum">Card Number</label>
                            <input type="text" id="ccNum" placeholder="4111 2222 3333 4444" maxlength="19" required>
                        </div>
                        <div class="form-group">
                            <label for="ccExpiry">Expiration Date</label>
                            <input type="text" id="ccExpiry" placeholder="MM/YY" maxlength="5" required>
                        </div>
                        <div class="form-group">
                            <label for="ccCvv">CVV / CVC</label>
                            <input type="text" id="ccCvv" placeholder="123" maxlength="4" required>
                        </div>
                    </div>
                </div>

                <!-- 2. Telebirr Panel -->
                <div id="telePanel" class="tab-panel" style="display:none;">
                    <div style="text-align:center; margin-bottom:2rem;">
                        <img src="https://static.vecteezy.com/system/resources/previews/010/160/328/original/phone-icon-sign-symbol-design-free-png.png" alt="Telebirr" style="height:55px; width:auto; filter:hue-rotate(140deg); margin:0 auto 1rem;">
                        <h4 style="font-family:'Playfair Display', serif; font-size:1.2rem;">Telebirr Push Payment</h4>
                        <p style="font-size:0.85rem; color:var(--color-gray-500); max-width:300px; margin:0.5rem auto 0;">Send an instant payment request to your Telebirr registered mobile phone.</p>
                    </div>
                    <div class="form-group">
                        <label for="teleMobile">Telebirr Mobile Number</label>
                        <input type="tel" id="teleMobile" placeholder="e.g. 0912345678" pattern="^(09|[+][2][5][1][9])[0-9]{8}$">
                    </div>
                    
                    <!-- Simulated Verification -->
                    <div id="teleOtpBlock" style="display:none; background:var(--color-cream); border:1px solid var(--color-gray-200); border-radius:6px; padding:1.5rem; margin-top:1.5rem; text-align:center;">
                        <label style="font-weight:600; font-size:0.8rem; text-transform:uppercase; letter-spacing:0.05em; display:block; margin-bottom:0.5rem;">Enter OTP sent to your phone</label>
                        <input type="text" id="teleOtp" placeholder="Simulated OTP (enter 123456)" maxlength="6" style="text-align:center; letter-spacing:0.5em; font-size:1.2rem; width:180px; padding:0.5rem;">
                        <small style="display:block; color:var(--color-gray-500); margin-top:0.5rem;">Enter code <strong>123456</strong> to successfully verify.</small>
                    </div>
                </div>

                <!-- 3. CBE Birr / Bank Panel -->
                <div id="cbePanel" class="tab-panel" style="display:none;">
                    <div style="background:var(--color-cream); border:1px solid var(--color-gray-200); border-radius:8px; padding:1.5rem; margin-bottom:2rem; font-size:0.9rem;">
                        <strong style="color:var(--color-gold); font-size:1rem; display:block; margin-bottom:0.75rem; text-transform:uppercase; font-family:'Playfair Display', serif;">Hawassa CBE Account Details</strong>
                        <div style="margin-bottom:0.5rem;">Bank: <strong>Commercial Bank of Ethiopia (CBE)</strong></div>
                        <div style="margin-bottom:0.5rem;">Account Name: <strong>Hawassa Luxury Real Estate PLC</strong></div>
                        <div>Account Number: <strong>1000123456789</strong></div>
                    </div>
                    <p style="font-size:0.85rem; color:var(--color-gray-500); margin-bottom:1.5rem; line-height:1.6;">Transfer the exact Hold Deposit amount to the account above via CBE Birr mobile app or bank branch, then input your Transaction Reference Reference ID below to match the reservation.</p>
                    <div class="form-group">
                        <label for="cbeRef">Transaction Reference ID</label>
                        <input type="text" id="cbeRef" placeholder="e.g. FT26145XYZ7" style="text-transform:uppercase;">
                    </div>
                </div>

                <!-- Submit Button -->
                <button type="submit" id="submitPaymentBtn" class="btn btn-gold" style="width:100%; padding:1rem; font-size:1rem; font-weight:600; letter-spacing:0.05em; text-transform:uppercase; margin-top:1.5rem;">
                    Confirm & Pay <?php echo format_price($total_due); ?>
                </button>
            </form>
        </div>
    </div>

    <!-- Success Confirmation Interface -->
    <div id="successPortal" class="success-card">
        <div class="success-badge">✓</div>
        <h2 style="font-family:'Playfair Display', serif; font-size:2.25rem; color:#28a745; margin-bottom:0.5rem;">Reservation Successful!</h2>
        <p style="color:var(--color-gray-500); font-size:1.05rem;">The property has been locked under your name. A booking confirmation has been issued.</p>
        
        <!-- Receipt -->
        <div class="receipt-box">
            <h4 style="font-family:'Playfair Display', serif; font-size:1.15rem; margin-bottom:1.25rem; border-bottom:1px solid rgba(0,0,0,0.1); padding-bottom:0.5rem; color:var(--color-gold);">Official Receipt & Hold Detail</h4>
            
            <div class="receipt-row">
                <span>Receipt Reference:</span>
                <span id="rCode" style="font-weight:600;">HW-RES-2A58F9BC</span>
            </div>
            <div class="receipt-row">
                <span>Property:</span>
                <span id="rProperty" style="max-width:300px; text-align:right;">Luxury Penthouse with Central Park Views</span>
            </div>
            <div class="receipt-row">
                <span>Hold Duration:</span>
                <span>14 Days (Contract Pending)</span>
            </div>
            <div class="receipt-row">
                <span>Payment Method:</span>
                <span id="rMethod">Credit Card</span>
            </div>
            <div class="receipt-row">
                <span>Transaction ID:</span>
                <span id="rReference">45689123</span>
            </div>
            <div class="receipt-row">
                <span>Hold Deposit Paid:</span>
                <span id="rAmount" style="color:var(--color-gold); font-size:1.15rem;">$257,500.00</span>
            </div>
        </div>

        <div style="background:rgba(200,169,110,0.05); border:1px solid rgba(200,169,110,0.25); border-radius:8px; padding:1.25rem; margin-bottom:2rem; text-align:left; font-size:0.85rem; line-height:1.6;">
            <strong style="color:var(--color-gold); display:block; margin-bottom:0.25rem;">What happens next?</strong>
            Our executive sales desk will verify your deposit reference and contact you at your registered email address (<?php echo htmlspecialchars($_SESSION['user_email']); ?>) in the next 1-2 hours to arrange formal contract signing and verify ID documents.
        </div>

        <div style="display:flex; justify-content:center; gap:1.5rem;">
            <a href="dashboard.php" class="btn btn-gold" style="padding:0.75rem 1.5rem;">👤 Go to My Dashboard</a>
            <a id="successReturnBtn" href="search.php" class="btn btn-outline" style="padding:0.75rem 1.5rem;">← Return to Search</a>
        </div>
    </div>
</div>

<!-- Simulated Processing Overlay -->
<div id="loadingOverlay" class="overlay-loading">
    <div class="loading-spinner-wrapper">
        <div class="spinner-outer"></div>
        <h3 id="loadingTitle" style="font-family:'Playfair Display', serif; font-size:1.6rem; color:var(--color-gold); margin-bottom:0.5rem;">Securing Checkout Session</h3>
        <p id="loadingSubtitle" style="color:var(--color-gray-300); font-size:0.9rem;">Please do not close this window or refresh the page.</p>
        
        <ul class="loading-steps">
            <li id="step1" class="active">● Connecting to secure bank gateway...</li>
            <li id="step2">● Authenticating transaction signature...</li>
            <li id="step3">● Verifying holding funds...</li>
            <li id="step4">● Registering reservation and locking property...</li>
        </ul>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // -------------------------------------------------------------
    // Payment Tab Selector
    // -------------------------------------------------------------
    const tabs = document.querySelectorAll('.payment-tab');
    const panels = document.querySelectorAll('.tab-panel');
    const methodInput = document.getElementById('selectedMethod');
    const ccInputs = document.querySelectorAll('#cardPanel input');
    const teleMobile = document.getElementById('teleMobile');
    const cbeRef = document.getElementById('cbeRef');

    tabs.forEach(tab => {
        tab.addEventListener('click', function() {
            // Remove active states
            tabs.forEach(t => t.classList.remove('active'));
            panels.forEach(p => p.style.display = 'none');

            // Apply active states
            this.classList.add('active');
            const targetId = this.getAttribute('data-target');
            document.getElementById(targetId).style.display = 'block';

            // Set hidden payment method input
            if (targetId === 'cardPanel') {
                methodInput.value = 'Credit/Debit Card';
                setInputsRequired(ccInputs, true);
                teleMobile.required = false;
                cbeRef.required = false;
            } else if (targetId === 'telePanel') {
                methodInput.value = 'Telebirr Mobile Wallet';
                setInputsRequired(ccInputs, false);
                teleMobile.required = true;
                cbeRef.required = false;
            } else if (targetId === 'cbePanel') {
                methodInput.value = 'CBE Birr / Bank Transfer';
                setInputsRequired(ccInputs, false);
                teleMobile.required = false;
                cbeRef.required = true;
            }
        });
    });

    function setInputsRequired(inputs, isRequired) {
        inputs.forEach(input => {
            input.required = isRequired;
        });
    }

    // Initialize required fields
    setInputsRequired(ccInputs, true);
    teleMobile.required = false;
    cbeRef.required = false;


    // -------------------------------------------------------------
    // Card Focus / Flip Effects
    // -------------------------------------------------------------
    const cardPreview = document.getElementById('cardPreview');
    const ccCvv = document.getElementById('ccCvv');
    const ccName = document.getElementById('ccName');
    const ccNum = document.getElementById('ccNum');
    const ccExpiry = document.getElementById('ccExpiry');

    // Labels
    const cardNumLabel = document.getElementById('cardNumLabel');
    const cardNameLabel = document.getElementById('cardNameLabel');
    const cardExpiryLabel = document.getElementById('cardExpiryLabel');
    const cardCvvLabel = document.getElementById('cardCvvLabel');

    ccCvv.addEventListener('focus', () => cardPreview.classList.add('flipped'));
    ccCvv.addEventListener('blur', () => cardPreview.classList.remove('flipped'));

    ccName.addEventListener('input', (e) => {
        cardNameLabel.textContent = e.target.value.trim() ? e.target.value.toUpperCase() : 'YOUR NAME';
    });

    // Formatting credit card number input
    ccNum.addEventListener('input', (e) => {
        let value = e.target.value.replace(/\s+/g, '').replace(/[^0-9]/gi, '');
        let formatted = '';
        for (let i = 0; i < value.length; i++) {
            if (i > 0 && i % 4 === 0) {
                formatted += ' ';
            }
            formatted += value[i];
        }
        e.target.value = formatted;
        cardNumLabel.textContent = formatted || '•••• •••• •••• ••••';
    });

    // Formatting expiry date
    ccExpiry.addEventListener('input', (e) => {
        let value = e.target.value.replace(/\s+/g, '').replace(/[^0-9]/gi, '');
        if (value.length > 2) {
            e.target.value = value.substr(0, 2) + '/' + value.substr(2, 2);
        } else {
            e.target.value = value;
        }
        cardExpiryLabel.textContent = e.target.value || 'MM/YY';
    });

    ccCvv.addEventListener('input', (e) => {
        let value = e.target.value.replace(/\s+/g, '').replace(/[^0-9]/gi, '');
        e.target.value = value;
        cardCvvLabel.textContent = value || '•••';
    });


    // -------------------------------------------------------------
    // Simulated Push/OTP Step for Telebirr
    // -------------------------------------------------------------
    let isTeleOtpSent = false;
    const teleOtpBlock = document.getElementById('teleOtpBlock');
    const teleOtp = document.getElementById('teleOtp');


    // -------------------------------------------------------------
    // Form Submission & API Processing
    // -------------------------------------------------------------
    const paymentForm = document.getElementById('paymentForm');
    const loadingOverlay = document.getElementById('loadingOverlay');
    const checkoutPortal = document.getElementById('checkoutPortal');
    const successPortal = document.getElementById('successPortal');

    // Loading Step elements
    const step1 = document.getElementById('step1');
    const step2 = document.getElementById('step2');
    const step3 = document.getElementById('step3');
    const step4 = document.getElementById('step4');

    paymentForm.addEventListener('submit', function(e) {
        e.preventDefault();

        const method = methodInput.value;

        // Telebirr requires Otp verification simulation
        if (method === 'Telebirr Mobile Wallet' && !isTeleOtpSent) {
            // Trigger simulated push notification sending
            loadingOverlay.style.display = 'flex';
            setTimeout(() => {
                loadingOverlay.style.display = 'none';
                teleOtpBlock.style.display = 'block';
                teleOtp.required = true;
                isTeleOtpSent = true;
                alert('Verification PIN request sent successfully. Enter OTP code 123456 to complete check.');
            }, 1000);
            return;
        }

        if (method === 'Telebirr Mobile Wallet' && isTeleOtpSent && teleOtp.value !== '123456') {
            alert('Invalid verification OTP code. Enter 123456 to bypass simulation.');
            return;
        }

        // Gather variables for transaction reference logging
        let transaction_reference = '';
        if (method === 'Credit/Debit Card') {
            // Generate mock transaction reference
            transaction_reference = 'CC-' + Math.floor(Math.random() * 899999 + 100000);
        } else if (method === 'Telebirr Mobile Wallet') {
            transaction_reference = 'TB-' + Math.floor(Math.random() * 899999 + 100000);
        } else if (method === 'CBE Birr / Bank Transfer') {
            transaction_reference = cbeRef.value.trim().toUpperCase();
        }

        const payload = {
            property_id: document.querySelector('input[name="property_id"]').value,
            amount: document.querySelector('input[name="amount"]').value,
            payment_method: method,
            transaction_reference: transaction_reference,
            reservation_date: document.querySelector('input[name="reservation_date"]').value
        };

        // Render secure loading screen
        loadingOverlay.style.display = 'flex';
        resetLoadingSteps();

        // Step 1: Connecting
        setTimeout(() => {
            setStepActive(step1, false, true);
            setStepActive(step2, true);

            // Step 2: Authenticating
            setTimeout(() => {
                setStepActive(step2, false, true);
                setStepActive(step3, true);

                // Step 3: Verifying Funds
                setTimeout(() => {
                    setStepActive(step3, false, true);
                    setStepActive(step4, true);

                    // Step 4: Call backend API to log transaction and lock property status
                    fetch('../api/process_payment.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(payload)
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            setTimeout(() => {
                                setStepActive(step4, false, true);
                                
                                // Render Receipt Details
                                document.getElementById('rCode').textContent = data.reservation_code;
                                document.getElementById('rProperty').textContent = data.property_title;
                                document.getElementById('rMethod').textContent = data.payment_method;
                                document.getElementById('rReference').textContent = data.reference;
                                document.getElementById('rAmount').textContent = formatPriceText(data.amount);
                                
                                // Display reservation date in receipt if available
                                if (data.reservation_date) {
                                    const dateRow = document.createElement('div');
                                    dateRow.className = 'receipt-row';
                                    dateRow.innerHTML = '<span>Reservation Date:</span><span>' + new Date(data.reservation_date).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' }) + '</span>';
                                    const holdDurationRow = document.querySelector('.receipt-row');
                                    if (holdDurationRow && holdDurationRow.nextSibling) {
                                        holdDurationRow.nextSibling.parentNode.insertBefore(dateRow, holdDurationRow.nextSibling.nextSibling);
                                    }
                                }
                                
                                document.getElementById('successReturnBtn').href = 'property.php?slug=' + encodeURIComponent('<?php echo $property['slug']; ?>');

                                // Transition views
                                loadingOverlay.style.display = 'none';
                                checkoutPortal.style.display = 'none';
                                successPortal.style.display = 'block';

                                // Trigger CSS celebration (e.g. optional scroll reveal)
                                window.scrollTo({ top: 0, behavior: 'smooth' });
                            }, 1000);
                        } else {
                            loadingOverlay.style.display = 'none';
                            alert('Checkout failed: ' + data.error);
                        }
                    })
                    .catch(err => {
                        loadingOverlay.style.display = 'none';
                        alert('An unexpected system connection error occurred.');
                        console.error(err);
                    });

                }, 1200);
            }, 1000);
        }, 1200);
    });

    function setStepActive(stepElement, isActive, isDone = false) {
        if (isActive) {
            stepElement.classList.add('active');
        } else {
            stepElement.classList.remove('active');
        }
        if (isDone) {
            stepElement.classList.add('done');
            stepElement.innerHTML = stepElement.innerHTML.replace('●', '✓');
        }
    }

    function resetLoadingSteps() {
        const steps = [step1, step2, step3, step4];
        steps.forEach(s => {
            s.classList.remove('active', 'done');
            s.innerHTML = s.innerHTML.replace('✓', '●');
        });
        step1.classList.add('active');
    }

    function formatPriceText(price) {
        price = parseFloat(price);
        return '$' + price.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }
});
</script>

<?php require_once '../includes/footer.php'; ?>
