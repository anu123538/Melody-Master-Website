<?php
session_start();
include 'db_connect.php';

// --- AUTHENTICATION CHECK ---
// Ensure only logged-in users with 'Admin' role can access this management page
if (!isset($_SESSION['role']) || strtolower(trim($_SESSION['role'])) !== 'admin') {
    header("Location: login.php");
    exit();
}

$message = "";

// --- DELETE LOGIC REMOVED ---
// Customer deletion is disabled to maintain data integrity (Order History & Audit Logs)

// --- SEARCH AND FETCH LOGIC ---
$search_query = "";
if (isset($_GET['search'])) {
    // Sanitize search input to prevent SQL injection
    $search_query = mysqli_real_escape_string($conn, $_GET['search']);
    $query = "SELECT user_id, full_name, email FROM users 
              WHERE role = 'Customer' AND (full_name LIKE '%$search_query%' OR email LIKE '%$search_query%')
              ORDER BY user_id DESC";
} else {
    // Default query to fetch all customers if no search is performed
    $query = "SELECT user_id, full_name, email FROM users WHERE role = 'Customer' ORDER BY user_id DESC";
}
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Customers | Melody Masters</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --gold: #e2b04a; --bg: #0f172a; --card: #1e293b; --text: #f8fafc; --blue: #3b82f6; --red: #ef4444; }
        body { background: var(--bg); color: var(--text); font-family: 'Poppins', sans-serif; display: flex; margin: 0; }
        
        .main-content { margin-left: 260px; width: calc(100% - 260px); padding: 40px; box-sizing: border-box; min-height: 100vh; }
        
        .top-bar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        .search-form { display: flex; gap: 10px; margin-bottom: 30px; }
        .search-input { 
            flex: 1; padding: 12px 20px; border-radius: 10px; border: 1px solid #334155; 
            background: #161e2e; color: white; outline: none; transition: 0.3s;
        }
        .search-input:focus { border-color: var(--gold); }
        .btn-search { background: #334155; color: white; border: none; padding: 12px 20px; border-radius: 10px; cursor: pointer; transition: 0.3s; }
        .btn-search:hover { background: var(--gold); color: #0f172a; }

        .glass-card { background: var(--card); border-radius: 15px; padding: 30px; border: 1px solid rgba(226,176,74,0.1); box-shadow: 0 10px 30px rgba(0,0,0,0.2); }
        
        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; color: var(--gold); padding: 15px; border-bottom: 2px solid #334155; font-size: 14px; text-transform: uppercase; }
        td { padding: 15px; border-bottom: 1px solid rgba(255,255,255,0.05); font-size: 15px; }
        
        .action-btns { display: flex; gap: 20px; justify-content: center; }
        
        /* Edit Button Styling - Focused on Blue for Update Actions */
        .btn-edit { color: var(--blue); font-size: 18px; text-decoration: none; transition: 0.3s; }
        .btn-edit:hover { color: #60a5fa; transform: scale(1.2); }

        .msg { padding: 15px; border-radius: 10px; margin-bottom: 25px; text-align: center; }
        .success { background: rgba(16, 185, 129, 0.1); color: #10b981; border: 1px solid #10b981; }
        .error { background: rgba(239, 68, 68, 0.1); color: #ef4444; border: 1px solid #ef4444; }
    </style>
</head>
<body>

<?php include 'admin_sidebar.php'; ?>

<div class="main-content">
    <div class="top-bar">
        <div>
            <h2 style="margin:0;">Customer Management</h2>
            <p style="color: #94a3b8; margin: 5px 0 0;">Oversee and manage registered customer accounts.</p>
        </div>
    </div>

    <form method="GET" class="search-form">
        <input type="text" name="search" class="search-input" placeholder="Search by name or email..." value="<?php echo htmlspecialchars($search_query); ?>">
        <button type="submit" class="btn-search"><i class="fas fa-search"></i> Search</button>
        <?php if($search_query): ?>
            <a href="manage_customers.php" class="btn-search" style="text-decoration:none; background:#ef4444;">Clear Search</a>
        <?php endif; ?>
    </form>

    <?php echo $message; ?>

    <div class="glass-card">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Full Name</th>
                    <th>Email Address</th>
                    <th style="text-align: center;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if($result && $result->num_rows > 0): ?>
                    <?php while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td style="color: #64748b;">#<?php echo $row['user_id']; ?></td>
                        <td style="font-weight: 600;"><?php echo htmlspecialchars($row['full_name']); ?></td>
                        <td style="color: #94a3b8;"><?php echo htmlspecialchars($row['email']); ?></td>
                        <td>
                            <div class="action-btns">
                                <a href="edit_customer.php?id=<?php echo $row['user_id']; ?>" class="btn-edit" title="Edit Customer Details">
                                    <i class="fas fa-user-edit"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="4" style="text-align:center; padding:40px; color:#94a3b8;">No customers found in the database.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>