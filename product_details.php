<?php
/**
 * PRODUCT DETAILS & VERIFIED REVIEWS PAGE
 */

include 'db_connect.php'; 
include 'navbar.php';

if (isset($_GET['id'])) {
    $product_id = mysqli_real_escape_string($conn, $_GET['id']);
    
    // Fetch product details including stock_quantity
    $sql = "SELECT p.*, c.category_name FROM products p 
            JOIN categories c ON p.category_id = c.category_id 
            WHERE p.product_id = '$product_id'";
    
    $result = $conn->query($sql);
    
    if ($result && $result->num_rows > 0) {
        $product = $result->fetch_assoc();
        // Get the available stock
        $available_stock = $product['stock_quantity']; 
    } else {
        echo "<div style='height:80vh; display:flex; align-items:center; justify-content:center; flex-direction:column; font-family:Poppins;'>
                <h1 style='color:#e2b04a;'>Oops! Product not found</h1>
                <a href='shop.php' style='color:white; text-decoration:none; background:#e2b04a; padding:10px 20px; border-radius:50px;'>Back to Shop</a>
              </div>";
        exit();
    }
} else {
    header("Location: shop.php");
    exit();
}

/**
 * PURCHASE VERIFICATION Logic (No changes here)
 */
$can_review = false;
if (isset($_SESSION['user_id'])) {
    $u_id = $_SESSION['user_id'];
    $p_id = $product['product_id'];
    
    $check_purchase = "SELECT oi.item_id FROM order_items oi 
                       JOIN orders o ON oi.order_id = o.order_id 
                       WHERE o.user_id = '$u_id' 
                       AND oi.product_id = '$p_id' 
                       AND o.order_status = 'Delivered'";
                       
    $purchase_res = $conn->query($check_purchase);
    
    if ($purchase_res && $purchase_res->num_rows > 0) {
        $can_review = true; 
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $product['product_name']; ?> | Melody Masters</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --gold: #e2b04a; --dark: #0f172a; --glass: rgba(255, 255, 255, 0.03); --border: rgba(255, 255, 255, 0.1); }
        body { font-family: 'Poppins', sans-serif; background: #0b0f1a; color: #f8fafc; margin: 0; line-height: 1.6; }

        .main-container { max-width: 1200px; margin: 50px auto; padding: 20px; }
        .product-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 50px; background: rgba(30, 41, 59, 0.5); padding: 40px; border-radius: 30px; border: 1px solid var(--border); backdrop-filter: blur(10px); }

        .image-card { border-radius: 20px; background: #161e2e; display: flex; align-items: center; justify-content: center; border: 1px solid var(--border); overflow: hidden; }
        .image-card img { width: 100%; height: auto; transition: 0.5s; }
        .image-card:hover img { transform: scale(1.05); }

        .brand-label { color: var(--gold); text-transform: uppercase; letter-spacing: 2px; font-weight: 600; font-size: 0.9rem; }
        .product-title { font-size: 2.8rem; margin: 10px 0; font-weight: 700; color: #fff; }
        .price-box { font-size: 2.2rem; color: #fff; font-weight: 600; margin: 20px 0; }
        
        .btn-premium { background: var(--gold); color: #0b0f1a; border: none; padding: 15px 30px; border-radius: 12px; font-weight: 700; cursor: pointer; transition: 0.3s; text-transform: uppercase; }
        .btn-premium:hover { transform: translateY(-3px); box-shadow: 0 10px 20px rgba(226, 176, 74, 0.2); }
        
        /* Out of stock specific style */
        .btn-out-stock { background: #334155; color: #94a3b8; cursor: not-allowed; }

        /* REVIEWS SECTION STYLING */
        .reviews-container { max-width: 1200px; margin: 40px auto; padding: 40px; background: rgba(30, 41, 59, 0.3); border-radius: 30px; border: 1px solid var(--border); }
        .review-form-card { background: #161e2e; padding: 30px; border-radius: 20px; margin-bottom: 40px; border: 1px solid var(--gold); }
        .review-form-card textarea { background: #0f172a; border: 1px solid #334155; color: white; width: 100%; padding: 15px; border-radius: 10px; margin-top: 15px; box-sizing: border-box; }
        
        .comment-item { background: var(--glass); padding: 25px; border-radius: 15px; margin-bottom: 20px; border: 1px solid var(--border); }
        .verified-badge { background: rgba(39, 174, 96, 0.1); color: #2ecc71; padding: 4px 12px; border-radius: 50px; font-size: 0.75rem; }
        .review-lock { background: rgba(231, 76, 60, 0.1); color: #ff7675; padding: 20px; border-radius: 15px; display: flex; align-items: center; gap: 15px; border: 1px solid rgba(231, 76, 60, 0.2); }

        .star-input { display: flex; gap: 8px; margin: 15px 0; cursor: pointer; }
        .star-input i { font-size: 1.6rem; color: #334155; transition: 0.2s; }
        .star-input i.active { color: var(--gold); }
    </style>
</head>
<body>

<div class="main-container">
    <div class="product-grid">
        <div class="image-section">
            <div class="image-card">
                <img src="uploads/<?php echo $product['product_image']; ?>" alt="Product">
            </div>
        </div>

        <div class="details-section">
            <span class="brand-label"><?php echo htmlspecialchars($product['brand'] ?? 'Premium Collection'); ?></span>
            <h1 class="product-title"><?php echo $product['product_name']; ?></h1>

            <?php if (!empty($product['specifications'])): ?>
                <ul style="list-style: none; padding: 0; margin: 20px 0; color: #cbd5e1; font-size: 0.95rem;">
                    <?php 
                    $spec_lines = explode("\n", $product['specifications']);
                    foreach ($spec_lines as $line): 
                        if (trim($line) != ""): ?>
                            <li style="margin-bottom: 8px; display: flex; align-items: flex-start; gap: 10px;">
                                <i class="fas fa-check-circle" style="color: var(--gold); margin-top: 5px; font-size: 0.8rem;"></i>
                                <span><?php echo htmlspecialchars($line); ?></span>
                            </li>
                        <?php endif; 
                    endforeach; ?>
                </ul>
            <?php endif; ?>

            <div style="margin: 15px 0; font-size: 0.9rem;">
                <?php if ($available_stock > 0): ?>
                    <span style="color: #4ade80;"><i class="fas fa-box-open"></i> In Stock: <?php echo $available_stock; ?> items</span>
                <?php else: ?>
                    <span style="color: #f87171;"><i class="fas fa-times-circle"></i> Out of Stock</span>
                <?php endif; ?>
            </div>

            <div class="price-box">£<?php echo number_format($product['price'], 2); ?></div>

            <form action="cart.php" method="POST" style="display:flex; gap:15px; margin-top:30px;">
                <input type="hidden" name="product_id" value="<?php echo $product['product_id']; ?>">
                
                <?php if ($available_stock > 0): ?>
                    <input type="number" name="quantity" value="1" min="1" max="<?php echo $available_stock; ?>" style="width:70px; background:#1e293b; color:white; border:1px solid var(--border); border-radius:10px; text-align:center;">
                    <button type="submit" name="add_to_cart" class="btn-premium">Add to Cart</button>
                <?php else: ?>
                    <button type="button" class="btn-premium btn-out-stock" disabled>Out of Stock</button>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <div class="reviews-container">
        <h2>Customer Experience</h2>
        <?php if ($can_review): ?>
            <div class="review-form-card">
                <form action="submit_review.php" method="POST">
                    <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
                    <label style="color: #94a3b8; font-size: 0.9rem;">Tap to Rate:</label>
                    <div class="star-input" id="star-selector">
                        <i class="fas fa-star active" data-value="1"></i>
                        <i class="fas fa-star active" data-value="2"></i>
                        <i class="fas fa-star active" data-value="3"></i>
                        <i class="fas fa-star active" data-value="4"></i>
                        <i class="fas fa-star active" data-value="5"></i>
                    </div>
                    <input type="hidden" name="rating" id="rating-value" value="5">
                    <textarea name="comment" required placeholder="Share your experience with this product..."></textarea>
                    <button type="submit" name="submit_review" class="btn-premium" style="margin-top:15px; width:auto; padding: 10px 40px; font-size: 0.9rem;">
                        Submit Review
                    </button>
                </form>
            </div>
        <?php else: ?>
            <div class="review-lock">
                <i class="fas fa-lock"></i>
                <span>Verified Purchase Required: You must buy and receive this item to leave a review.</span>
            </div>
        <?php endif; ?>

        <div class="comments-list" style="margin-top: 40px;">
            <?php
            $reviews_query = "SELECT r.*, u.full_name FROM reviews r 
                              JOIN users u ON r.user_id = u.user_id 
                              WHERE r.product_id = '$product_id' 
                              ORDER BY r.review_date DESC";
            $reviews = $conn->query($reviews_query);

            if ($reviews && $reviews->num_rows > 0):
                while($rev = $reviews->fetch_assoc()): ?>
                    <div class="comment-item">
                        <div style="display:flex; justify-content:space-between; align-items:center;">
                            <span style="font-weight:600; color:white;">
                                <?php echo htmlspecialchars($rev['full_name']); ?> 
                                <span class="verified-badge">Verified Buyer</span>
                            </span>
                            <div style="color: var(--gold);">
                                <?php for($i=1; $i<=5; $i++) echo ($i <= $rev['rating']) ? '★' : '☆'; ?>
                            </div>
                        </div>
                        <p style="margin-top:10px; color:#cbd5e1;">"<?php echo htmlspecialchars($rev['comment']); ?>"</p>
                        <small style="color:#64748b;"><?php echo date('M d, Y', strtotime($rev['review_date'])); ?></small>
                    </div>
                <?php endwhile;
            else: ?>
                <div style="text-align:center; padding: 40px; color:#64748b;">
                    <i class="far fa-comments" style="font-size: 3rem; margin-bottom: 10px;"></i>
                    <p>No reviews yet. Be the first to share your experience!</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>

<script>
document.querySelectorAll('#star-selector i').forEach(star => {
    star.addEventListener('click', function() {
        const value = this.getAttribute('data-value');
        document.getElementById('rating-value').value = value;
        document.querySelectorAll('#star-selector i').forEach(s => {
            s.classList.toggle('active', s.getAttribute('data-value') <= value);
        });
    });
});
</script>

</body>
</html>