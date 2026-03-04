<?php
session_start();
include 'db_connect.php';

// Authentication: Staff and Admin can both view reviews
if (!isset($_SESSION['role']) || (strtolower(trim($_SESSION['role'])) !== 'staff' && strtolower(trim($_SESSION['role'])) !== 'admin')) {
    header("Location: login.php");
    exit();
}

// Fetch all reviews with product names and user names
// Adjust table/column names if they differ in your database
$review_query = "SELECT r.*, p.product_name, u.full_name 
                 FROM reviews r 
                 JOIN products p ON r.product_id = p.product_id 
                 JOIN users u ON r.user_id = u.user_id 
                 ORDER BY r.review_date DESC";
$review_res = $conn->query($review_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Customer Reviews | Melody Masters</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        :root { 
            --gold: #e2b04a; 
            --bg: #0f172a; 
            --card: #1e293b; 
            --text: #f8fafc;
            --text-muted: #94a3b8;
        }

        body { 
            background: var(--bg); 
            color: var(--text); 
            font-family: 'Poppins', sans-serif; 
            margin: 0; 
            display: flex;
        }

        .main-content { 
            margin-left: 260px; 
            width: calc(100% - 260px); 
            padding: 40px; 
        }

        .review-card {
            background: var(--card);
            padding: 20px;
            border-radius: 15px;
            margin-bottom: 20px;
            border: 1px solid rgba(255,255,255,0.05);
        }

        .product-name { color: var(--gold); font-weight: 600; font-size: 1.1rem; }
        .customer-name { color: var(--text-muted); font-size: 0.9rem; }
        .rating { color: #ffca28; margin: 10px 0; }
        .review-text { margin-top: 10px; line-height: 1.6; }
        
        .header-section {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .back-btn {
            color: var(--text);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.9rem;
            opacity: 0.8;
        }
        .back-btn:hover { opacity: 1; color: var(--gold); }
    </style>
</head>
<body>

<?php include 'staff_sidebar.php'; ?>

<div class="main-content">
    <div class="header-section">
        <div>
            <h1 style="margin:0;">Customer Reviews</h1>
            <p style="color: var(--text-muted);">What musicians are saying about our instruments.</p>
        </div>
        <a href="staff_dashboard.php" class="back-btn">
            <i class="fas fa-arrow-left"></i> Back to Dashboard
        </a>
    </div>

    <?php if ($review_res && $review_res->num_rows > 0): ?>
        <?php while($row = $review_res->fetch_assoc()): ?>
            <div class="review-card">
                <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                    <div>
                        <span class="product-name"><?php echo htmlspecialchars($row['product_name']); ?></span>
                        <div class="customer-name">by <?php echo htmlspecialchars($row['full_name']); ?> on <?php echo date('M d, Y', strtotime($row['review_date'])); ?></div>
                    </div>
                    <div class="rating">
                        <?php 
                        for($i=1; $i<=5; $i++) {
                            echo $i <= $row['rating'] ? '<i class="fas fa-star"></i>' : '<i class="far fa-star"></i>';
                        }
                        ?>
                    </div>
                </div>
                <div class="review-text">
                    "<?php echo htmlspecialchars($row['comment']); ?>"
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div style="text-align: center; padding: 50px; opacity: 0.5;">
            <i class="fas fa-comment-slash" style="font-size: 3rem; margin-bottom: 20px;"></i>
            <p>No reviews found yet.</p>
        </div>
    <?php endif; ?>
</div>

</body>
</html>