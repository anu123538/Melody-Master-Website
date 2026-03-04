<?php
session_start();
include 'db_connect.php';

// Authentication check: Only Admin or Staff
if (!isset($_SESSION['role'])) {
    header("Location: login.php");
    exit();
}

$current_role = strtolower(trim($_SESSION['role']));

if ($current_role !== 'staff' && $current_role !== 'admin') {
    header("Location: login.php");
    exit();
}

$message = "";

// --- 1. SECURE STATUS UPDATE LOGIC ---
if (isset($_POST['update_status'])) {
    $order_id = $_POST['order_id'];
    $new_status = $_POST['status'];
    
    // Using Prepared Statement to prevent SQL Injection
    $stmt = $conn->prepare("UPDATE orders SET order_status = ? WHERE order_id = ?");
    $stmt->bind_param("si", $new_status, $order_id);
    
    if ($stmt->execute()) {
        $message = "Order #$order_id updated to $new_status successfully!";
    }
    $stmt->close();
}

// --- 2. SEARCH & FILTER LOGIC ---
$filter_query = " WHERE 1=1 "; 

if (isset($_GET['filter']) && !empty($_GET['filter'])) {
    $filter_type = $_GET['filter'];
    if ($filter_type == 'today') {
        $filter_query .= " AND DATE(o.order_date) = CURDATE() ";
    } elseif ($filter_type == 'week') {
        $filter_query .= " AND o.order_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) ";
    } elseif ($filter_type == 'month') {
        $filter_query .= " AND MONTH(o.order_date) = MONTH(CURDATE()) AND YEAR(o.order_date) = YEAR(CURDATE()) ";
    }
}

if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search = mysqli_real_escape_string($conn, $_GET['search']);
    $filter_query .= " AND (o.order_id = '$search' OR u.full_name LIKE '%$search%') ";
}

$sql = "SELECT o.*, u.full_name FROM orders o 
        JOIN users u ON o.user_id = u.user_id 
        $filter_query
        ORDER BY o.order_date DESC";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Orders | Melody Masters</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --gold: #e2b04a; --bg: #0f172a; --card: #1e293b; --text: #f8fafc; }
        body { background: var(--bg); color: var(--text); font-family: 'Poppins', sans-serif; display: flex; margin: 0; }
        
        /* Main Content alignment with Sidebar */
        .main-content { margin-left: 260px; width: calc(100% - 260px); padding: 40px; box-sizing: border-box; min-height: 100vh; }
        
        .glass-card { background: var(--card); padding: 25px; border-radius: 15px; border: 1px solid rgba(226,176,74,0.1); margin-bottom: 25px; }
        
        .filter-bar { display: flex; justify-content: space-between; align-items: center; gap: 15px; flex-wrap: wrap; }
        input, select { background: #0f172a; border: 1px solid #334155; color: white; padding: 10px; border-radius: 8px; }
        
        .btn-gold { background: var(--gold); color: #0f172a; border: none; padding: 10px 20px; border-radius: 8px; font-weight: bold; cursor: pointer; }
        .btn-gold:hover { background: #d1a03d; }

        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th { text-align: left; color: var(--gold); padding: 15px; border-bottom: 2px solid #334155; }
        td { padding: 15px; border-bottom: 1px solid rgba(255,255,255,0.05); }
        
        .status-pill { padding: 5px 12px; border-radius: 20px; font-size: 11px; font-weight: bold; }
        .msg-success { background: rgba(16, 185, 129, 0.1); color: #10b981; padding: 15px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #10b981; }
    </style>
</head>
<body>

<?php 
// --- DYNAMIC SIDEBAR LOGIC ---
if ($current_role === 'admin') {
    include 'admin_sidebar.php';
} else {
    include 'staff_sidebar.php';
}
?>

<div class="main-content">
    <div style="margin-bottom: 30px;">
        <h1 style="margin:0;"><i class="fas fa-shopping-basket" style="color: var(--gold);"></i> Order Management</h1>
        <p style="color: #94a3b8;">Monitor and update customer orders (Access: <?php echo ucfirst($current_role); ?>)</p>
    </div>

    <div class="glass-card">
        <form method="GET" class="filter-bar">
            <div style="display: flex; gap: 10px;">
                <input type="text" name="search" placeholder="Order ID or Customer Name..." value="<?php echo $_GET['search'] ?? ''; ?>" style="width: 250px;">
                <select name="filter">
                    <option value="">All Time</option>
                    <option value="today" <?php if(isset($_GET['filter']) && $_GET['filter'] == 'today') echo 'selected'; ?>>Today</option>
                    <option value="week" <?php if(isset($_GET['filter']) && $_GET['filter'] == 'week') echo 'selected'; ?>>Last 7 Days</option>
                    <option value="month" <?php if(isset($_GET['filter']) && $_GET['filter'] == 'month') echo 'selected'; ?>>This Month</option>
                </select>
                <button type="submit" class="btn-gold">Filter</button>
                <a href="manage_orders.php" style="color: #ef4444; text-decoration: none; padding-top: 10px; font-size: 14px; margin-left: 10px;">Reset</a>
            </div>
            <div style="font-weight: 600;">Result Count: <span style="color: var(--gold);"><?php echo $result->num_rows; ?></span></div>
        </form>
    </div>

    <?php if(!empty($message)) echo "<div class='msg-success'><i class='fas fa-check-circle'></i> $message</div>"; ?>

    <div class="glass-card" style="padding: 10px; overflow-x: auto;">
        <table>
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Customer</th>
                    <th>Date</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if($result->num_rows > 0): ?>
                    <?php while($row = $result->fetch_assoc()): 
                        $status = $row['order_status'];
                        $color = ($status == 'Pending') ? '#f39c12' : (($status == 'Shipped') ? '#3498db' : '#10b981');
                    ?>
                    <tr>
                        <td><span style="color: var(--gold); font-weight: bold;">#<?php echo $row['order_id']; ?></span></td>
                        <td><?php echo htmlspecialchars($row['full_name']); ?></td>
                        <td><?php echo date('d M Y', strtotime($row['order_date'])); ?></td>
                        <td>£<?php echo number_format($row['total_amount'], 2); ?></td>
                        <td>
                            <span class="status-pill" style="background: <?php echo $color; ?>22; color: <?php echo $color; ?>; border: 1px solid <?php echo $color; ?>;">
                                <?php echo $status; ?>
                            </span>
                        </td>
                        <td>
                            <form method="POST" style="display: flex; gap: 5px;">
                                <input type="hidden" name="order_id" value="<?php echo $row['order_id']; ?>">
                                <select name="status" style="padding: 5px; font-size: 12px; background: #1e293b;">
                                    <option value="Pending" <?php if($status == 'Pending') echo 'selected'; ?>>Pending</option>
                                    <option value="Shipped" <?php if($status == 'Shipped') echo 'selected'; ?>>Shipped</option>
                                    <option value="Delivered" <?php if($status == 'Delivered') echo 'selected'; ?>>Delivered</option>
                                </select>
                                <button type="submit" name="update_status" class="btn-gold" style="padding: 5px 10px; font-size: 11px;">Update</button>
                            </form>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="6" style="text-align:center; padding: 40px; color: #94a3b8;">No matching orders found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>