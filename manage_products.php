<?php
session_start();
include 'db_connect.php';

// Authentication: Only Admin OR Staff can access
if (!isset($_SESSION['role'])) {
    header("Location: login.php");
    exit();
}

$current_role = strtolower(trim($_SESSION['role']));

if ($current_role !== 'admin' && $current_role !== 'staff') {
    header("Location: login.php");
    exit();
}

$message = "";

// --- 1. LOW STOCK ALERT LOGIC ---
$low_stock_res = $conn->query("SELECT COUNT(*) as low_count FROM products WHERE stock_quantity <= 5 AND status = 'Active'");
$low_count = $low_stock_res->fetch_assoc()['low_count'];

// --- UPDATED DELETE (ARCHIVE) LOGIC ---
if (isset($_GET['delete_id']) && $current_role === 'admin') {
    $id = (int)$_GET['delete_id'];
    $stmt = $conn->prepare("UPDATE products SET status = 'Archived' WHERE product_id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        $message = "<div class='msg success'><i class='fas fa-check-circle'></i> Product removed successfully!</div>";
        $low_stock_res = $conn->query("SELECT COUNT(*) as low_count FROM products WHERE stock_quantity <= 5 AND status = 'Active'");
        $low_count = $low_stock_res->fetch_assoc()['low_count'];
    } else {
        $message = "<div class='msg error'>Error: Could not remove product.</div>";
    }
    $stmt->close();
}

// --- SEARCH & FILTER LOGIC ---
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : "";
$filter_low = isset($_GET['restock']) ? true : false; // නව Filter එක පරීක්ෂා කිරීම

if ($filter_low) {
    // Restock click කළ විට අඩු බඩු පමණක් පෙන්වයි
    $query = "SELECT * FROM products WHERE status = 'Active' AND stock_quantity <= 5 ORDER BY stock_quantity ASC";
} elseif ($search != "") {
    $query = "SELECT * FROM products WHERE (product_name LIKE '%$search%' OR brand LIKE '%$search%') 
              AND status = 'Active' ORDER BY product_id DESC";
} else {
    $query = "SELECT * FROM products WHERE status = 'Active' ORDER BY product_id DESC";
}
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Inventory Management | Melody Masters</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --gold: #e2b04a; --bg: #0f172a; --card: #1e293b; --text: #f8fafc; }
        body { background: var(--bg); color: var(--text); font-family: 'Poppins', sans-serif; display: flex; margin: 0; }
        
        .main-content { margin-left: 260px; width: calc(100% - 260px); padding: 40px; box-sizing: border-box; min-height: 100vh; }
        .top-section { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        
        .search-box { display: flex; gap: 10px; flex: 1; max-width: 500px; margin-bottom: 25px; }
        .search-input { 
            flex: 1; padding: 12px; border-radius: 8px; border: 1px solid #334155; 
            background: #161e2e; color: white; outline: none; 
        }
        .btn-search { background: #334155; color: white; border: none; padding: 10px 20px; border-radius: 8px; cursor: pointer; transition: 0.3s; }
        .btn-search:hover { background: var(--gold); color: #0f172a; }

        .glass-card { background: var(--card); border-radius: 15px; padding: 25px; border: 1px solid rgba(226,176,74,0.1); box-shadow: 0 10px 30px rgba(0,0,0,0.2); }
        
        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; color: var(--gold); padding: 15px; border-bottom: 2px solid #334155; font-size: 14px; text-transform: uppercase; }
        td { padding: 15px; border-bottom: 1px solid rgba(255,255,255,0.05); font-size: 15px; }
        
        .stock-badge { padding: 6px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; display: inline-block; }
        .low-stock { background: rgba(239, 68, 68, 0.1); color: #ef4444; border: 1px solid #ef4444; }
        .in-stock { background: rgba(16, 185, 129, 0.1); color: #10b981; border: 1px solid #10b981; }
        
        .btn-add { background: var(--gold); color: #0f172a; text-decoration: none; padding: 12px 20px; border-radius: 8px; font-weight: bold; transition: 0.3s; }
        .btn-add:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(226,176,74,0.3); }

        .msg { padding: 15px; border-radius: 10px; margin-bottom: 20px; text-align: center; font-size: 14px; }
        .success { background: rgba(16, 185, 129, 0.1); color: #10b981; border: 1px solid #10b981; }
        .error { background: rgba(239, 68, 68, 0.1); color: #ef4444; border: 1px solid #ef4444; }

        .low-stock-alert {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.3);
            border-left: 5px solid #ef4444;
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 15px;
        }

        .btn-restock {
            background: #ef4444;
            color: white;
            text-decoration: none;
            padding: 8px 16px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 600;
            transition: 0.3s;
            display: flex;
            align-items: center;
            gap: 8px;
            border: none;
            white-space: nowrap;
        }

        .btn-restock:hover {
            background: #dc2626;
            transform: scale(1.05);
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
        }
        
        .action-link { font-size: 18px; transition: 0.3s; text-decoration: none; }
        .action-link:hover { transform: scale(1.2); display: inline-block; }
    </style>
</head>
<body>

<?php 
if ($current_role === 'admin') { include 'admin_sidebar.php'; } 
else { include 'staff_sidebar.php'; }
?>

<div class="main-content">
    <div class="top-section">
        <div>
            <h2 style="margin:0;"><i class="fas fa-guitar" style="color:var(--gold);"></i> View Inventory</h2>
            <p style="color: #94a3b8; margin-top: 5px;">Monitor instrument stock levels and pricing (Access: <?php echo ucfirst($current_role); ?>)</p>
        </div>
        <?php if($current_role === 'admin'): ?>
            <a href="add_product.php" class="btn-add"><i class="fas fa-plus-circle"></i> Add New Product</a>
        <?php endif; ?>
    </div>

    <form method="GET" class="search-box">
        <input type="text" name="search" class="search-input" placeholder="Search by name or brand..." value="<?php echo htmlspecialchars($search); ?>">
        <button type="submit" class="btn-search"><i class="fas fa-search"></i> Search</button>
        <?php if($search || $filter_low): ?>
            <a href="manage_products.php" style="color:#ef4444; text-decoration:none; align-self:center; margin-left:10px;">Clear</a>
        <?php endif; ?>
    </form>

    <?php echo $message; ?>

    <?php if($low_count > 0): ?>
        <div class="low-stock-alert">
            <div style="display: flex; align-items: center; gap: 15px;">
                <i class="fas fa-exclamation-triangle" style="color: #ef4444; font-size: 20px;"></i>
                <div>
                    <strong style="color: #ef4444; display: block;">Inventory Warning!</strong>
                    <span style="color: #cbd5e1; font-size: 14px;">There are <strong><?php echo $low_count; ?></strong> items running low on stock.</span>
                </div>
            </div>
            <a href="manage_products.php?restock=1#inventory-table" class="btn-restock">
                <i class="fas fa-sync-alt"></i> Restock Now
            </a>
        </div>
    <?php endif; ?>

    <div class="glass-card" id="inventory-table">
        <?php if($filter_low): ?>
            <div style="margin-bottom: 15px; color: #ef4444; font-size: 14px; font-weight: 600;">
                <i class="fas fa-filter"></i> Showing only low stock items
            </div>
        <?php endif; ?>

        <table>
            <thead>
                <tr>
                    <th>Product Name</th>
                    <th>Brand</th>
                    <th>Price</th>
                    <th>Stock Status</th>
                    <th style="text-align: center;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if($result->num_rows > 0): ?>
                    <?php while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td style="font-weight:600;"><?php echo htmlspecialchars($row['product_name']); ?></td>
                        <td style="color: #94a3b8;"><?php echo htmlspecialchars($row['brand']); ?></td>
                        <td style="color:var(--gold); font-weight:bold;">
                            Rs. <?php echo number_format($row['price'], 2); ?>
                        </td>
                        <td>
                            <?php if($row['stock_quantity'] <= 5): ?>
                                <span class="stock-badge low-stock">
                                    <i class="fas fa-exclamation-triangle"></i> Low: <?php echo $row['stock_quantity']; ?> Left
                                </span>
                            <?php else: ?>
                                <span class="stock-badge in-stock">
                                    <i class="fas fa-check"></i> <?php echo $row['stock_quantity']; ?> in Stock
                                </span>
                            <?php endif; ?>
                        </td>
                        <td style="text-align: center;">
                            <a href="edit_product.php?id=<?php echo $row['product_id']; ?>" 
                               class="action-link" style="color:#3b82f6; margin-right:15px;" title="Edit Product">
                                <i class="fas fa-edit"></i>
                            </a>
                            <?php if($current_role === 'admin'): ?>
                                <a href="manage_products.php?delete_id=<?php echo $row['product_id']; ?>" 
                                   class="action-link" style="color:#ef4444;" 
                                   onclick="return confirm('Are you sure?')" title="Delete Product">
                                    <i class="fas fa-trash-alt"></i>
                                </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="5" style="text-align: center; padding: 40px; color: #94a3b8;">No products found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>