<?php
session_start();
include 'db_connect.php';

// 1. ACCESS CONTROL (Admin and Staff only)
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

// 2. DELETE LOGIC (Stays as you wrote it - Admin Only)
if (isset($_GET['delete_id']) && $current_role === 'admin') {
    $review_id = (int)$_GET['delete_id'];
    $stmt = $conn->prepare("DELETE FROM reviews WHERE review_id = ?");
    $stmt->bind_param("i", $review_id);
    
    if ($stmt->execute()) {
        $message = "<div class='alert success'><i class='fas fa-check-circle'></i> Review deleted successfully!</div>";
    } else {
        $message = "<div class='alert error'><i class='fas fa-times-circle'></i> Error: Could not delete.</div>";
    }
    $stmt->close();
}

// 3. DATA FETCHING
$query = "SELECT r.*, p.product_name, u.full_name 
          FROM reviews r 
          JOIN products p ON r.product_id = p.product_id 
          JOIN users u ON r.user_id = u.user_id 
          ORDER BY r.review_date DESC";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reviews Management | Melody Masters</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --gold: #e2b04a;
            --bg: #0f172a;
            --card: #1e293b;
            --text: #f8fafc;
            --danger: #ef4444;
            --border: rgba(255, 255, 255, 0.05);
        }

        body {
            background-color: var(--bg);
            color: var(--text);
            font-family: 'Poppins', sans-serif;
            margin: 0;
            display: flex; /* Keeps sidebar and content side-by-side */
            min-height: 100vh;
        }

        /* --- Sidebar Overrides (Ensures Logout is visible) --- */
        .sidebar { 
            width: 260px; 
            height: 100vh; 
            background: #161e2e; 
            position: fixed; 
            left: 0; 
            top: 0; 
            z-index: 1000;
            overflow-y: auto; /* IMPORTANT: Enables scrolling to see Logout */
            border-right: 1px solid rgba(226,176,74,0.1);
        }

        /* --- Main Content Layout --- */
        .main-content {
            margin-left: 260px; /* Space for sidebar */
            width: calc(100% - 260px);
            padding: 40px;
            box-sizing: border-box;
        }

        .page-header { margin-bottom: 30px; }
        .page-header h2 { margin: 0; font-size: 1.8rem; }
        
        .role-badge { 
            font-size: 10px; padding: 2px 8px; border-radius: 4px; 
            background: rgba(226,176,74,0.1); color: var(--gold); border: 1px solid var(--gold);
            text-transform: uppercase; vertical-align: middle; margin-left: 10px;
        }

        .glass-card {
            background: var(--card);
            border-radius: 20px;
            padding: 30px;
            border: 1px solid var(--border);
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }

        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; color: var(--gold); padding: 15px; border-bottom: 2px solid #334155; font-size: 0.85rem; text-transform: uppercase; }
        td { padding: 18px 15px; border-bottom: 1px solid var(--border); font-size: 0.95rem; }

        .btn-delete {
            color: var(--danger);
            background: rgba(239, 68, 68, 0.1);
            padding: 8px 12px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 0.85rem;
            transition: 0.3s;
            border: 1px solid rgba(239, 68, 68, 0.2);
        }
        .btn-delete:hover { background: var(--danger); color: white; }

        .alert { padding: 15px; border-radius: 12px; margin-bottom: 25px; display: flex; align-items: center; gap: 10px; }
        .success { background: rgba(16, 185, 129, 0.1); color: #10b981; border: 1px solid #10b981; }
        .error { background: rgba(239, 68, 68, 0.1); color: #ef4444; border: 1px solid #ef4444; }

        /* Responsive */
        @media (max-width: 1024px) {
            .sidebar { width: 70px; }
            .sidebar .sidebar-brand, .sidebar span { display: none; }
            .main-content { margin-left: 70px; width: calc(100% - 70px); }
        }
    </style>
</head>
<body>

<?php 
// 4. SIDEBAR SELECTION
if ($current_role === 'admin') {
    include 'admin_sidebar.php';
} else {
    include 'staff_sidebar.php';
}
?>

<div class="main-content">
    <div class="page-header">
        <h2>Customer Reviews <span class="role-badge"><?php echo $current_role; ?> View</span></h2>
        <p>Monitor feedback and ratings from your customers.</p>
    </div>

    <?php echo $message; ?>

    <div class="glass-card">
        <table>
            <thead>
                <tr>
                    <th>Customer & Product</th>
                    <th>Rating</th>
                    <th>Comment</th>
                    <th>Date</th>
                    <?php if($current_role === 'admin'): ?>
                        <th style="text-align: center;">Actions</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php if($result && $result->num_rows > 0): ?>
                    <?php while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td>
                            <div style="font-weight: 600; color: #fff;"><?php echo htmlspecialchars($row['full_name']); ?></div>
                            <div style="color: #94a3b8; font-size: 0.8rem;">on <?php echo htmlspecialchars($row['product_name']); ?></div>
                        </td>
                        <td style="color: var(--gold); letter-spacing: 2px;">
                            <?php 
                            for($i=1; $i<=5; $i++) {
                                echo ($i <= $row['rating']) ? '<i class="fas fa-star"></i>' : '<i class="far fa-star"></i>';
                            }
                            ?>
                        </td>
                        <td style="color: #cbd5e1; font-style: italic; line-height: 1.5;">
                            "<?php echo htmlspecialchars($row['comment']); ?>"
                        </td>
                        <td style="color: #64748b; font-size: 0.85rem;">
                            <?php echo date('M d, Y', strtotime($row['review_date'])); ?>
                        </td>
                        
                        <?php if($current_role === 'admin'): ?>
                        <td style="text-align: center;">
                            <a href="manage_reviews.php?delete_id=<?php echo $row['review_id']; ?>" 
                               class="btn-delete" 
                               onclick="return confirm('HCI Note: This action cannot be undone. Delete this feedback?')">
                                <i class="fas fa-trash-alt"></i> Delete
                            </a>
                        </td>
                        <?php endif; ?>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" style="text-align:center; padding: 60px; color: #64748b;">
                            <i class="far fa-comments" style="font-size: 3rem; display: block; margin-bottom: 15px; opacity: 0.3;"></i>
                            No reviews available at the moment.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>