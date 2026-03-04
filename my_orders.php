<?php
/**
 * MY ORDERS & DIGITAL DOWNLOADS PAGE
 */
include 'db_connect.php'; 
include 'navbar.php';

// Access Control
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

/**
 * DATA RETRIEVAL: 
 * Joining orders, order_items, and products.
 * We fetch the product_id to pass it to our download script.
 */
$sql = "SELECT o.*, oi.product_id, p.product_name, dp.file_path
        FROM orders o 
        JOIN order_items oi ON o.order_id = oi.order_id 
        JOIN products p ON oi.product_id = p.product_id 
        LEFT JOIN digital_products dp ON p.product_id = dp.product_id 
        WHERE o.user_id = '$user_id' 
        ORDER BY o.order_date DESC";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders | Melody Masters</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --primary: #0b0f1a; --accent: #e2b04a; --card: rgba(255,255,255,0.03); --success: #4ade80; }
        body { font-family: 'Poppins', sans-serif; background: var(--primary); color: white; margin: 0; }
        
        .order-history-container { max-width: 1000px; margin: 60px auto; padding: 20px; }
        .header-section { margin-bottom: 40px; border-left: 4px solid var(--accent); padding-left: 20px; }
        
        .order-card { 
            background: var(--card); border: 1px solid rgba(255,255,255,0.1); 
            border-radius: 20px; padding: 25px; margin-bottom: 20px; 
            display: flex; justify-content: space-between; align-items: center;
            transition: 0.3s ease;
        }
        .order-card:hover { background: rgba(255,255,255,0.06); transform: translateY(-3px); }
        
        .status-badge { 
            padding: 5px 15px; border-radius: 30px; font-size: 0.75rem; 
            font-weight: 700; text-transform: uppercase; letter-spacing: 1px;
        }

        .btn-group { display: flex; align-items: center; gap: 12px; }
        .btn-view { 
            background: var(--accent); color: var(--primary); 
            padding: 10px 25px; border-radius: 12px; text-decoration: none; 
            font-weight: 700; font-size: 0.9rem; transition: 0.3s;
        }
        .btn-download { 
            background: transparent; color: var(--success); border: 1px solid var(--success);
            padding: 9px 20px; border-radius: 12px; text-decoration: none; 
            font-weight: 600; font-size: 0.85rem; transition: 0.3s;
        }
        .btn-download:hover { background: var(--success); color: var(--primary); }
    </style>
</head>
<body>

<div class="order-history-container">
    <div class="header-section">
        <h1>My Purchase History</h1>
        <p style="color: #94a3b8;">Track your gear orders and access digital assets.</p>
    </div>

    <?php if ($result && $result->num_rows > 0): ?>
        <?php while($row = $result->fetch_assoc()): ?>
            <div class="order-card">
                <div class="order-info">
                    <div style="font-size: 1.1rem; font-weight: 700; color: var(--accent);">
                        Order #ORD-<?php echo str_pad($row['order_id'], 5, '0', STR_PAD_LEFT); ?>
                    </div>
                    <div style="font-size: 1rem; margin: 5px 0; color: #fff;">
                        <?php echo htmlspecialchars($row['product_name']); ?>
                    </div>
                    <div style="color: #94a3b8; font-size: 0.85rem;">
                        <i class="far fa-calendar-alt"></i> <?php echo date('M d, Y', strtotime($row['order_date'])); ?>
                    </div>
                    
                    <div style="margin-top: 15px;">
                        <?php 
                        // Note: Using 'status' or 'order_status' based on your DB column name
                        $status = isset($row['order_status']) ? $row['order_status'] : $row['status']; 
                        $color = ($status == 'Delivered' || $status == 'Completed') ? '#4ade80' : (($status == 'Shipped') ? '#3498db' : '#f1c40f');
                        ?>
                        <span class="status-badge" style="border: 1px solid <?php echo $color; ?>; color: <?php echo $color; ?>;">
                            <?php echo $status; ?>
                        </span>
                    </div>
                </div>

                <div style="text-align: right;">
                    <div style="margin-bottom: 15px;">
                        <div style="color: #94a3b8; font-size: 0.75rem; text-transform: uppercase;">Paid Amount</div>
                        <div style="font-size: 1.5rem; font-weight: 700;">£<?php echo number_format($row['total_amount'], 2); ?></div>
                    </div>

                    <div class="btn-group">
                        <?php if (!empty($row['file_path'])): ?>
                            <a href="download.php?product_id=<?php echo $row['product_id']; ?>" class="btn-download">
                                <i class="fas fa-cloud-download-alt"></i> Download Lyrics
                            </a>
                        <?php endif; ?>

                        <a href="order_success.php?id=<?php echo $row['order_id']; ?>" class="btn-view">Details</a>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div style="text-align: center; padding: 80px; background: var(--card); border-radius: 30px;">
            <i class="fas fa-shopping-bag" style="font-size: 3rem; color: #334155; margin-bottom: 20px;"></i>
            <h3>No orders found</h3>
            <a href="shop.php" class="btn-view" style="display:inline-block; margin-top:20px;">Go to Shop</a>
        </div>
    <?php endif; ?>
</div>

<?php include 'footer.php'; ?>
</body>
</html>