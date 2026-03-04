<?php
ob_start();
session_start();
include 'db_connect.php';
include 'navbar.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Check if Order ID is provided in URL
if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$order_id = mysqli_real_escape_string($conn, $_GET['id']);

// Fetch order and customer details from the database
$sql = "SELECT o.*, u.full_name, u.email 
        FROM orders o 
        JOIN users u ON o.user_id = u.user_id 
        WHERE o.order_id = '$order_id'";
$result = $conn->query($sql);

if ($result->num_rows == 0) {
    echo "<div style='color:white; text-align:center; margin-top:50px;'><h2>Order Not Found!</h2></div>";
    exit();
}

$order = $result->fetch_assoc();

// Fetch items related to the order, including product type and digital file path
$items_sql = "SELECT oi.*, p.product_name, p.product_image, p.product_type, c.category_name, dp.file_path 
              FROM order_items oi 
              JOIN products p ON oi.product_id = p.product_id 
              JOIN categories c ON p.category_id = c.category_id
              LEFT JOIN digital_products dp ON p.product_id = dp.product_id
              WHERE oi.order_id = '$order_id'";
$items_result = $conn->query($items_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Order Receipt | Melody Masters</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* CSS variables for consistent premium dark theme */
        :root { --gold: #e2b04a; --bg: #0b0f1a; --glass: rgba(255, 255, 255, 0.03); --border: rgba(255, 255, 255, 0.1); --success: #4ade80; }
        body { font-family: 'Poppins', sans-serif; background: var(--bg); color: #f8fafc; margin: 0; }
        .receipt-container { max-width: 900px; margin: 50px auto; padding: 20px; }
        .premium-card { background: var(--glass); backdrop-filter: blur(20px); border: 1px solid var(--border); border-radius: 40px; padding: 50px; position: relative; }
        .paid-stamp { position: absolute; top: 40px; right: 40px; border: 3px solid var(--success); color: var(--success); padding: 10px 25px; border-radius: 10px; font-weight: 800; transform: rotate(15deg); font-size: 1.5rem; text-transform: uppercase; opacity: 0.8; }
        table { width: 100%; border-collapse: collapse; margin-top: 30px; }
        th { text-align: left; padding: 15px; color: #94a3b8; font-size: 0.8rem; text-transform: uppercase; border-bottom: 1px solid var(--border); }
        td { padding: 20px 15px; border-bottom: 1px solid rgba(255,255,255,0.05); }
        .btn-download { background: rgba(74, 222, 128, 0.1); color: var(--success); padding: 8px 16px; border-radius: 8px; text-decoration: none; font-size: 0.85rem; font-weight: 600; border: 1px solid var(--success); display: inline-flex; align-items: center; gap: 8px; margin-top: 8px; }
        .total-box { background: rgba(255,255,255,0.02); padding: 30px; border-radius: 20px; }
        .row { display: flex; justify-content: space-between; margin-bottom: 15px; }
        .grand-total { font-size: 1.8rem; font-weight: 700; color: var(--gold); border-top: 1px solid var(--border); padding-top: 20px; }
        .no-print { margin-top: 40px; text-align: center; }
        .btn-action { padding: 15px 35px; border-radius: 15px; font-weight: 700; cursor: pointer; border: none; text-decoration: none; margin: 0 10px; display: inline-block; }
        @media print { .no-print, nav, footer { display: none !important; } body { background: white; color: black; } .premium-card { border: none; background: white; } }
    </style>
</head>
<body>

<div class="receipt-container">
    <div class="premium-card">
        <div class="paid-stamp">Paid & Confirmed</div>
        <h1 style="color:var(--gold); margin:0;">MELODY MASTERS</h1>
        <p style="color:#94a3b8;">Premium Instruments & Sheet Music</p>

        <div style="display: flex; justify-content: space-between; margin-top: 40px;">
            <div>
                <label style="color: var(--gold); font-size: 0.7rem;">BILLED TO</label>
                <div style="font-size: 1.1rem; font-weight: 600;"><?php echo $order['full_name']; ?></div>
                <div style="color: #94a3b8;"><?php echo $order['email']; ?></div>
            </div>
            <div style="text-align: right;">
                <label style="color: var(--gold); font-size: 0.7rem;">ORDER DETAILS</label>
                <div style="font-size: 1.1rem; font-weight: 600;">#ORD-<?php echo str_pad($order_id, 5, '0', STR_PAD_LEFT); ?></div>
                <div style="color: #94a3b8;"><?php echo date('F d, Y', strtotime($order['order_date'])); ?></div>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Item Details</th>
                    <th>Qty</th>
                    <th>Price</th>
                    <th style="text-align: right;">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php while($item = $items_result->fetch_assoc()): ?>
                <tr>
                    <td>
                        <div style="font-weight: 600;"><?php echo $item['product_name']; ?></div>
                        <small style="color: #64748b;"><?php echo $item['category_name']; ?></small><br>
                        
                        <?php 
                        /* DIGITAL DOWNLOAD LOGIC: 
                           Directly linking to download.php instead of the file path 
                           to enforce the 5-download limit record in the database.
                        */
                        if ($item['product_type'] === 'Digital' && !empty($item['file_path'])) {
                            echo '<a href="download.php?product_id=' . $item['product_id'] . '" class="btn-download">
                                    <i class="fas fa-download"></i> Download PDF
                                  </a>';
                        }
                        ?>
                    </td>
                    <td>x<?php echo $item['quantity']; ?></td>
                    <td>£<?php echo number_format($item['unit_price'], 2); ?></td>
                    <td style="text-align: right; font-weight: 600;">£<?php echo number_format($item['unit_price'] * $item['quantity'], 2); ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <div style="display: grid; grid-template-columns: 1fr 300px; gap: 40px; margin-top: 40px;">
            <div style="color: #64748b; font-size: 0.85rem; border-left: 2px solid var(--gold); padding-left: 20px;">
                <i class="fas fa-shield-alt"></i> Secure Transaction: All digital downloads are accessible instantly after payment.
            </div>
            <div class="total-box">
                <div class="row">
                    <span>Subtotal</span>
                    <span>£<?php echo number_format($order['total_amount'] - $order['shipping_cost'], 2); ?></span>
                </div>
                <div class="row">
                    <span>Shipping</span>
                    <span>
                        <?php 
                        if ($order['shipping_cost'] == 0) {
                            echo "<b style='color:var(--success)'>FREE</b>";
                        } else {
                            echo "£" . number_format($order['shipping_cost'], 2);
                        }
                        ?>
                    </span>
                </div>
                <div class="row grand-total">
                    <span>Total</span>
                    <span>£<?php echo number_format($order['total_amount'], 2); ?></span>
                </div>
            </div>
        </div>
    </div>

    <div class="no-print">
        <button onclick="window.print()" class="btn-action" style="background:var(--gold); color:var(--bg);"><i class="fas fa-print"></i> Print Receipt</button>
        <a href="shop.php" class="btn-action" style="border: 1px solid var(--border); color:white;">Return to Shop</a>
    </div>
</div>

<?php include 'footer.php'; ?>
</body>
</html>