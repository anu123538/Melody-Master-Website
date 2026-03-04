<?php
session_start();
include 'db_connect.php';

// Authentication: Only Staff members can access this
if (!isset($_SESSION['role']) || strtolower(trim($_SESSION['role'])) !== 'staff') {
    header("Location: login.php");
    exit();
}

// 1. Fetch PENDING orders count
$order_query = "SELECT COUNT(*) as total FROM orders WHERE order_status = 'Pending'";
$order_res = $conn->query($order_query);
$order_count = ($order_res) ? $order_res->fetch_assoc()['total'] : 0;

// 2. Fetch low stock count (<= 5)
$stock_res = $conn->query("SELECT COUNT(*) as total FROM products WHERE stock_quantity <= 5");
$low_stock = ($stock_res) ? $stock_res->fetch_assoc()['total'] : 0;

// 3. Fetch Total Products count
$prod_res = $conn->query("SELECT COUNT(*) as total FROM products");
$total_products = ($prod_res) ? $prod_res->fetch_assoc()['total'] : 0;

// 4. Fetch Today's Orders count
$today = date('Y-m-d');
$today_res = $conn->query("SELECT COUNT(*) as total FROM orders WHERE DATE(order_date) = '$today'");
$today_orders = ($today_res) ? $today_res->fetch_assoc()['total'] : 0;

// Get display name from session
$display_name = isset($_SESSION['full_name']) ? $_SESSION['full_name'] : "Staff Member";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Staff Dashboard | Melody Masters</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root { 
            --gold: #e2b04a; 
            --gold-dark: #c19235;
            --bg: #0f172a; 
            --card: #1e293b; 
            --text: #f8fafc;
            --text-muted: #94a3b8;
        }

        body { 
            background: var(--bg); 
            color: var(--text); 
            font-family: 'Poppins', sans-serif; 
            display: flex; 
            margin: 0; 
        }
        
        /* Ensures Content doesn't overlap the fixed sidebar */
        .main-content { 
            margin-left: 260px; /* Same as Sidebar width */
            width: calc(100% - 260px); 
            padding: 40px; 
            box-sizing: border-box; 
            min-height: 100vh; 
        }
        
        .welcome-section { margin-bottom: 30px; }
        .welcome-section h1 { font-size: 2.2rem; margin: 0; color: #fff; }
        .welcome-section p { color: var(--text-muted); margin-top: 5px; }

        .action-bar { display: flex; gap: 15px; margin-bottom: 30px; }
        .btn-quick {
            padding: 12px 20px;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: 0.3s;
        }
        .btn-add { background: var(--gold); color: #0f172a; }
        .btn-review { background: rgba(226,176,74,0.1); color: var(--gold); border: 1px solid var(--gold); }
        .btn-quick:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(0,0,0,0.2); }

        .stat-grid { 
            display: grid; 
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); 
            gap: 20px; 
            margin-bottom: 40px; 
        }
        .stat-card { 
            background: var(--card); 
            padding: 25px; 
            border-radius: 20px; 
            border: 1px solid rgba(255,255,255,0.05);
            text-align: center;
            transition: 0.3s ease;
        }
        .stat-card:hover { border-color: var(--gold); transform: translateY(-5px); }
        .stat-card i { font-size: 30px; color: var(--gold); margin-bottom: 15px; opacity: 0.8; }
        .stat-card h3 { margin: 0; font-size: 28px; }
        .stat-card p { color: var(--text-muted); margin: 5px 0 0; font-size: 14px; }

        .table-container { 
            background: var(--card); 
            border-radius: 20px; 
            padding: 25px; 
            border: 1px solid rgba(255,255,255,0.05);
        }
        .table-container h2 { font-size: 1.5rem; margin-top: 0; color: var(--gold); display: flex; align-items: center; gap: 10px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th { text-align: left; padding: 12px; color: var(--text-muted); border-bottom: 1px solid rgba(255,255,255,0.1); font-size: 13px; text-transform: uppercase; }
        td { padding: 15px 12px; border-bottom: 1px solid rgba(255,255,255,0.05); }
        .badge-low { background: rgba(255, 107, 157, 0.1); color: #ff6b9d; padding: 4px 10px; border-radius: 6px; font-size: 12px; font-weight: 600; }
        .btn-update { color: var(--gold); text-decoration: none; font-size: 14px; font-weight: 600; }
        .btn-update:hover { text-decoration: underline; }
    </style>
</head>
<body>

<?php include 'staff_sidebar.php'; ?>

<div class="main-content">
    <div class="welcome-section">
        <h1>Hello, <?php echo htmlspecialchars($display_name); ?>!</h1>
        <p>Here's an overview of the store's current status.</p>
    </div>

    <div class="action-bar">
        <a href="add_product.php" class="btn-quick btn-add">
            <i class="fas fa-plus-circle"></i> Add New Product
        </a>
        <a href="manage_reviews.php" class="btn-quick btn-review">
            <i class="fas fa-star"></i> View Product Reviews
        </a>
    </div>

    <div class="stat-grid">
        <div class="stat-card">
            <i class="fas fa-clipboard-list"></i>
            <h3><?php echo $order_count; ?></h3>
            <p>Orders Awaiting Action</p>
        </div>

        <div class="stat-card">
            <i class="fas fa-exclamation-triangle"></i>
            <h3><?php echo $low_stock; ?></h3>
            <p>Low Stock Items</p>
        </div>

        <div class="stat-card">
            <i class="fas fa-music"></i>
            <h3><?php echo $total_products; ?></h3>
            <p>Total Instruments</p>
        </div>

        <div class="stat-card">
            <i class="fas fa-calendar-check"></i>
            <h3><?php echo $today_orders; ?></h3>
            <p>Today's Orders</p>
        </div>
    </div>

    <div class="table-container">
        <h2><i class="fas fa-shuttle-van"></i> Urgent Restock Needed</h2>
        <table>
            <thead>
                <tr>
                    <th>Product Name</th>
                    <th>Stock Quantity</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $low_stock_details = $conn->query("SELECT product_id, product_name, stock_quantity FROM products WHERE stock_quantity <= 5 ORDER BY stock_quantity ASC LIMIT 5");
                if ($low_stock_details && $low_stock_details->num_rows > 0):
                    while($row = $low_stock_details->fetch_assoc()):
                ?>
                <tr>
                    <td><strong><?php echo htmlspecialchars($row['product_name']); ?></strong></td>
                    <td><span class="badge-low"><?php echo $row['stock_quantity']; ?> units left</span></td>
                    <td>
                        <a href="edit_product.php?id=<?php echo $row['product_id']; ?>" class="btn-update">
                            <i class="fas fa-edit"></i> Update Stock
                        </a>
                    </td>
                </tr>
                <?php endwhile; else: ?>
                <tr>
                    <td colspan="3" style="text-align: center; color: var(--text-muted); padding: 20px;">All items are well-stocked!</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>