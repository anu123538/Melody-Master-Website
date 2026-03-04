<?php
ob_start(); 
session_start();
include 'db_connect.php';
include 'navbar.php';

/**
 * 1. UPDATE QUANTITIES LOGIC (With Stock Validation)
 */
if (isset($_POST['update_cart'])) {
    foreach ($_POST['qty'] as $p_id => $new_qty) {
        $new_qty = (int)$new_qty;

        if ($new_qty <= 0) {
            unset($_SESSION['cart'][$p_id]);
        } else {
            // Fetch current stock from database for this product
            $stock_res = $conn->query("SELECT stock_quantity FROM products WHERE product_id = '$p_id'");
            $product_data = $stock_res->fetch_assoc();
            $available_stock = $product_data['stock_quantity'];

            // If requested quantity exceeds stock, set it to the maximum available
            if ($new_qty > $available_stock) {
                $_SESSION['cart'][$p_id] = $available_stock;
                $warning = "Quantity adjusted to maximum available stock.";
            } else {
                $_SESSION['cart'][$p_id] = $new_qty;
            }
        }
    }
    $msg = isset($warning) ? $warning : "Cart Updated Successfully";
    echo "<script>window.location.href='cart.php?msg=$msg';</script>";
    exit();
}

/**
 * 2. REMOVE ITEM LOGIC
 */
if (isset($_GET['remove'])) {
    $id = $_GET['remove'];
    unset($_SESSION['cart'][$id]);
    echo "<script>window.location.href='cart.php?msg=Item Removed';</script>";
    exit();
}

/**
 * 3. ADD TO CART LOGIC (With Stock Validation)
 */
if (isset($_POST['add_to_cart'])) {
    $p_id = $_POST['product_id'];
    $qty_to_add = (int)$_POST['quantity'];

    // Fetch current stock from database
    $stock_res = $conn->query("SELECT stock_quantity FROM products WHERE product_id = '$p_id'");
    $product_data = $stock_res->fetch_assoc();
    $available_stock = $product_data['stock_quantity'];

    // Calculate how many are already in the session cart
    $current_cart_qty = $_SESSION['cart'][$p_id] ?? 0;

    // Validate if the total exceeds available stock
    if (($current_cart_qty + $qty_to_add) > $available_stock) {
        echo "<script>alert('Only $available_stock items are available in stock.'); window.location.href='cart.php';</script>";
    } else {
        $_SESSION['cart'][$p_id] = $current_cart_qty + $qty_to_add;
        echo "<script>window.location.href='cart.php';</script>";
    }
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Your Bag | Melody Masters</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --gold: #e2b04a; --bg: #0b0f1a; --glass: rgba(255, 255, 255, 0.03); --border: rgba(255, 255, 255, 0.1); }
        body { font-family: 'Poppins', sans-serif; background: var(--bg); color: #f8fafc; margin: 0; }
        .wrapper { max-width: 1200px; margin: 50px auto; padding: 0 20px; display: grid; grid-template-columns: 1.8fr 1fr; gap: 40px; }
        .cart-container { background: var(--glass); backdrop-filter: blur(15px); border: 1px solid var(--border); border-radius: 30px; padding: 40px; }
        h2 { font-size: 2rem; margin-bottom: 30px; font-weight: 700; }
        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; padding-bottom: 20px; color: #94a3b8; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 1.5px; border-bottom: 1px solid var(--border); }
        td { padding: 25px 0; border-bottom: 1px solid rgba(255,255,255,0.05); }
        .item-info { display: flex; align-items: center; gap: 20px; }
        .item-img { width: 80px; height: 80px; object-fit: cover; border-radius: 15px; border: 1px solid var(--border); }
        .qty-input { background: #161e2e; border: 1px solid var(--border); color: white; padding: 8px; width: 60px; border-radius: 8px; text-align: center; }
        .btn-update { background: transparent; border: 1px solid var(--gold); color: var(--gold); padding: 10px 20px; border-radius: 10px; cursor: pointer; font-weight: 600; transition: 0.3s; margin-top: 20px; }
        .btn-update:hover { background: var(--gold); color: var(--bg); }
        .summary-card { background: linear-gradient(145deg, #1e293b, #0f172a); border-radius: 30px; padding: 40px; border: 1px solid var(--border); height: fit-content; position: sticky; top: 30px; }
        .summary-row { display: flex; justify-content: space-between; margin-bottom: 20px; font-size: 1.1rem; }
        .total-row { border-top: 1px solid var(--border); padding-top: 20px; margin-top: 20px; font-size: 1.8rem; font-weight: 700; color: var(--gold); }
        .btn-checkout { background: var(--gold); color: #0b0f1a; display: block; text-align: center; padding: 20px; border-radius: 15px; text-decoration: none; font-weight: 700; font-size: 1.1rem; margin-top: 30px; transition: 0.3s; text-transform: uppercase; letter-spacing: 1px; }
        .btn-checkout:hover { transform: translateY(-5px); box-shadow: 0 10px 25px rgba(226, 176, 74, 0.3); }
        .promo-box { background: rgba(226, 176, 74, 0.1); border: 1px dashed var(--gold); padding: 15px; border-radius: 15px; color: var(--gold); font-size: 0.9rem; margin-bottom: 30px; text-align: center; }
        .remove-btn { color: #ef4444; text-decoration: none; font-size: 1.2rem; transition: 0.3s; }
        .remove-btn:hover { color: #f87171; }
        @media (max-width: 992px) { .wrapper { grid-template-columns: 1fr; } }
    </style>
</head>
<body>

<div class="wrapper">
    <div class="cart-container">
        <h2>Shopping Bag</h2>

        <?php 
        if (!empty($_SESSION['cart'])): 
            $total = 0;
            $has_physical_item = false; 
        ?>
            <form method="POST">
                <table>
                    <thead>
                        <tr>
                            <th>Instrument</th>
                            <th>Price</th>
                            <th>Qty</th>
                            <th>Subtotal</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        foreach ($_SESSION['cart'] as $id => $qty): 
                            $res = $conn->query("SELECT * FROM products WHERE product_id = '$id'");
                            $product = $res->fetch_assoc();
                            if(!$product) continue;

                            // Identify if the product is physical or digital
                            if(isset($product['product_type']) && $product['product_type'] == 'Physical') {
                                $has_physical_item = true;
                            }

                            $subtotal = $product['price'] * $qty;
                            $total += $subtotal;
                        ?>
                        <tr>
                            <td>
                                <div class="item-info">
                                    <img src="uploads/<?php echo $product['product_image']; ?>" class="item-img">
                                    <div>
                                        <div style="font-weight:600; font-size:1.1rem;"><?php echo $product['product_name']; ?></div>
                                        <div style="color:var(--gold); font-size:0.8rem;"><?php echo $product['brand']; ?></div>
                                        <small style="color: #94a3b8;">In Stock: <?php echo $product['stock_quantity']; ?></small>
                                    </div>
                                </div>
                            </td>
                            <td>£<?php echo number_format($product['price'], 2); ?></td>
                            <td>
                                <input type="number" name="qty[<?php echo $id; ?>]" value="<?php echo $qty; ?>" min="1" max="<?php echo $product['stock_quantity']; ?>" class="qty-input">
                            </td>
                            <td style="font-weight:600;">£<?php echo number_format($subtotal, 2); ?></td>
                            <td>
                                <a href="cart.php?remove=<?php echo $id; ?>" class="remove-btn" onclick="return confirm('Remove this item?')">
                                    <i class="fas fa-times-circle"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <button type="submit" name="update_cart" class="btn-update">
                    <i class="fas fa-sync-alt"></i> Refresh Bag
                </button>
            </form>
        <?php else: ?>
            <div style="text-align: center; padding: 80px 0;">
                <i class="fas fa-shopping-cart" style="font-size: 4rem; opacity: 0.1; margin-bottom: 20px;"></i>
                <p style="color:#94a3b8;">Your bag is currently empty.</p>
                <a href="shop.php" class="btn-update" style="display:inline-block; text-decoration:none;">Start Shopping</a>
            </div>
        <?php endif; ?>
    </div>

    <?php if (!empty($_SESSION['cart'])): ?>
    <div class="summary-card">
        <h3 style="margin-top:0;">Order Summary</h3>
        
        <?php 
        $shipping_limit = 100;
        
        // Calculate shipping costs based on physical vs digital items
        if (!$has_physical_item) {
            $shipping = 0;
            $shipping_msg = "Digital Delivery (Free)";
        } else {
            $shipping = ($total > $shipping_limit) ? 0 : 15.00; 
            $shipping_msg = ($shipping == 0) ? "FREE" : "£" . number_format($shipping, 2);
        }
        ?>

        <div class="promo-box">
            <?php if($has_physical_item): ?>
                <?php if($total < $shipping_limit): ?>
                    <i class="fas fa-truck"></i> Add <b>£<?php echo ($shipping_limit - $total); ?></b> more for <b>FREE SHIPPING!</b>
                <?php else: ?>
                    <i class="fas fa-check-circle"></i> Qualified for <b>Free Shipping!</b>
                <?php endif; ?>
            <?php else: ?>
                <i class="fas fa-cloud-download-alt"></i> No shipping fees for digital items!
            <?php endif; ?>
        </div>

        <div class="summary-row">
            <span>Subtotal</span>
            <span>£<?php echo number_format($total, 2); ?></span>
        </div>
        <div class="summary-row">
            <span>Shipping</span>
            <span><?php echo $shipping_msg; ?></span>
        </div>

        <div class="summary-row total-row">
            <span>Total</span>
            <span>£<?php echo number_format($total + $shipping, 2); ?></span>
        </div>

        <a href="checkout.php" class="btn-checkout">Secure Checkout</a>
        
        <div style="margin-top:25px; text-align:center; opacity:0.5; font-size:0.8rem;">
            <i class="fas fa-shield-alt"></i> Payments are 100% Secure
        </div>
    </div>
    <?php endif; ?>
</div>

<?php include 'footer.php'; ?>

</body>
</html>