<?php
ob_start();
session_start();
include 'db_connect.php';
include 'navbar.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo "<script>window.location.href='login.php';</script>";
    exit();
}

// Check if cart is empty
if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    echo "<script>alert('Your cart is empty!'); window.location.href='shop.php';</script>";
    exit();
}

$user_id = $_SESSION['user_id'];
$total = 0;
$cart_items = [];
$has_physical_item = false; 

foreach ($_SESSION['cart'] as $id => $qty) {
    $res = $conn->query("SELECT * FROM products WHERE product_id = '$id'");
    $p = $res->fetch_assoc();
    
    // Check product type for shipping and status logic
    if(isset($p['product_type']) && $p['product_type'] == 'Physical') {
        $has_physical_item = true;
    }

    $subtotal = $p['price'] * $qty;
    $total += $subtotal;
    $cart_items[] = ['id' => $id, 'name' => $p['product_name'], 'qty' => $qty, 'price' => $p['price'], 'sub' => $subtotal];
}

// Set shipping cost
if (!$has_physical_item) {
    $shipping = 0;
} else {
    $shipping = ($total > 100) ? 0 : 15.00;
}
$grand_total = $total + $shipping;

if (isset($_POST['place_order'])) {
    $address = mysqli_real_escape_string($conn, $_POST['address']);
    $payment = mysqli_real_escape_string($conn, $_POST['payment_method']);

    /**
     * UPDATED LOGIC: Automatic Status for Reviews.
     * If the order is strictly digital (Lyrics), set status to 'Delivered' immediately.
     * This allows users to write reviews right after purchase.
     */
    $order_status = ($has_physical_item) ? 'Pending' : 'Delivered';

    $conn->begin_transaction();
    try {
        // Insert order with the dynamic status
        $sql_order = "INSERT INTO orders (user_id, total_amount, shipping_cost, order_status) 
                      VALUES ('$user_id', '$grand_total', '$shipping', '$order_status')";
        $conn->query($sql_order);
        $order_id = $conn->insert_id;

        foreach ($cart_items as $item) {
            $p_id = $item['id'];
            $qty = $item['qty'];
            $u_price = $item['price'];
            $conn->query("INSERT INTO order_items (order_id, product_id, quantity, unit_price) VALUES ('$order_id', '$p_id', '$qty', '$u_price')");
            
            $conn->query("UPDATE products SET stock_quantity = stock_quantity - $qty WHERE product_id = '$p_id'");
        }

        $conn->commit();
        unset($_SESSION['cart']);
        echo "<script>window.location.href='order_success.php?id=$order_id';</script>";
    } catch (Exception $e) {
        $conn->rollback();
        $error = "Transaction Failed: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Checkout | Melody Masters</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Design remains untouched */
        :root { --gold: #e2b04a; --bg: #0b0f1a; --glass: rgba(255, 255, 255, 0.03); --border: rgba(255, 255, 255, 0.1); }
        body { font-family: 'Poppins', sans-serif; background: var(--bg); color: #f8fafc; margin: 0; }
        .checkout-wrapper { max-width: 1200px; margin: 60px auto; padding: 0 20px; display: grid; grid-template-columns: 1.5fr 1fr; gap: 40px; }
        .checkout-card { background: var(--glass); backdrop-filter: blur(15px); border: 1px solid var(--border); border-radius: 30px; padding: 40px; }
        .summary-card { background: linear-gradient(145deg, #1e293b, #0f172a); border-radius: 30px; padding: 40px; border: 1px solid var(--border); height: fit-content; }
        h2 { font-size: 1.8rem; margin-bottom: 30px; display: flex; align-items: center; gap: 15px; }
        h2 i { color: var(--gold); }
        .form-group { margin-bottom: 25px; }
        label { display: block; margin-bottom: 10px; color: #94a3b8; font-weight: 500; font-size: 0.9rem; }
        textarea, select { width: 100%; background: rgba(0,0,0,0.2); border: 1px solid var(--border); padding: 15px; border-radius: 12px; color: white; font-family: inherit; resize: none; transition: 0.3s; }
        textarea:focus, select:focus { border-color: var(--gold); outline: none; background: rgba(0,0,0,0.4); }
        .item-row { display: flex; justify-content: space-between; margin-bottom: 15px; font-size: 0.95rem; color: #cbd5e1; }
        .total-row { border-top: 1px solid var(--border); padding-top: 20px; margin-top: 20px; font-size: 1.5rem; font-weight: 700; color: var(--gold); display: flex; justify-content: space-between; }
        .btn-confirm { background: var(--gold); color: #0b0f1a; border: none; padding: 20px; width: 100%; border-radius: 15px; font-weight: 700; font-size: 1.1rem; cursor: pointer; transition: 0.3s; text-transform: uppercase; letter-spacing: 1px; margin-top: 30px; }
        .btn-confirm:hover { transform: translateY(-3px); box-shadow: 0 10px 20px rgba(226, 176, 74, 0.3); }
        .badge-qty { background: var(--gold); color: #000; padding: 2px 8px; border-radius: 6px; font-size: 0.7rem; font-weight: 700; margin-left: 5px; }
        .security-box { margin-top: 30px; padding: 20px; background: rgba(34, 197, 94, 0.05); border: 1px solid rgba(34, 197, 94, 0.2); border-radius: 15px; display: flex; gap: 15px; align-items: center; font-size: 0.85rem; color: #4ade80; }
        @media (max-width: 992px) { .checkout-wrapper { grid-template-columns: 1fr; } }
    </style>
</head>
<body>

<div class="checkout-wrapper">
    <div class="checkout-card">
        <h2><i class="fas fa-map-marker-alt"></i> Shipping & Delivery</h2>
        <form method="POST">
            <div class="form-group">
                <label>DELIVERY ADDRESS <?php echo !$has_physical_item ? '(Not Required for Digital Orders)' : ''; ?></label>
                <textarea name="address" rows="4" placeholder="House No, Street, City, Postal Code" <?php echo $has_physical_item ? 'required' : ''; ?>><?php echo !$has_physical_item ? 'Digital Delivery - No physical address needed.' : ''; ?></textarea>
            </div>

            <h2 style="margin-top: 50px;"><i class="fas fa-credit-card"></i> Payment Method</h2>
            <div class="form-group">
                <label>SELECT PREFERRED METHOD</label>
                <select name="payment_method">
                    <option value="COD">Cash on Delivery (Pay at Doorstep)</option>
                    <option value="Card" disabled>Credit / Debit Card (Coming Soon)</option>
                </select>
            </div>

            <button type="submit" name="place_order" class="btn-confirm">
                Confirm Order <i class="fas fa-arrow-right" style="margin-left: 10px;"></i>
            </button>
        </form>
    </div>

    <div class="summary-card">
        <h3 style="color: var(--gold); margin-top: 0; display: flex; justify-content: space-between;">
            Order Summary <span><i class="fas fa-shopping-bag"></i></span>
        </h3>
        <hr style="border: 0; border-top: 1px solid var(--border); margin: 20px 0;">
        
        <div style="max-height: 300px; overflow-y: auto; padding-right: 10px;">
            <?php foreach ($cart_items as $item): ?>
                <div class="item-row">
                    <span><?php echo $item['name']; ?> <span class="badge-qty">x<?php echo $item['qty']; ?></span></span>
                    <span style="color: white; font-weight: 600;">£<?php echo number_format($item['sub'], 2); ?></span>
                </div>
            <?php endforeach; ?>
        </div>

        <div style="margin-top: 30px;">
            <div class="item-row">
                <span>Items Subtotal</span>
                <span>£<?php echo number_format($total, 2); ?></span>
            </div>
            <div class="item-row">
                <span>Shipping & Handling</span>
                <span style="color: <?php echo ($shipping == 0) ? '#4ade80' : 'white'; ?>;">
                    <?php 
                    if (!$has_physical_item) { echo "Digital Delivery (FREE)"; }
                    else { echo ($shipping == 0) ? "FREE" : "£" . number_format($shipping, 2); }
                    ?>
                </span>
            </div>

            <div class="total-row">
                <span>Total</span>
                <span>£<?php echo number_format($grand_total, 2); ?></span>
            </div>
        </div>

        <div class="security-box">
            <i class="fas fa-shield-check" style="font-size: 1.5rem;"></i>
            <div>
                <strong>Secure Checkout</strong><br>
                Instant access to digital items after payment.
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>

</body>
</html>