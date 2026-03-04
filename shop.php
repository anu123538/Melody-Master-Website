<?php
include 'db_connect.php';
include 'navbar.php'; 

// 1. SEARCH, FILTER & SORT LOGIC
$where_clauses = [];

/**
 * MANDATORY FILTER: Only show active products to customers.
 * This ensures that products "deleted" (archived) by the admin 
 * do not appear in the shop.
 */
$where_clauses[] = "status = 'Active'";

if (isset($_GET['cat_id']) && !empty($_GET['cat_id'])) {
    $cat_id = mysqli_real_escape_string($conn, $_GET['cat_id']);
    $where_clauses[] = "category_id = '$cat_id'";
}

if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search = mysqli_real_escape_string($conn, $_GET['search']);
    $where_clauses[] = "(product_name LIKE '%$search%' OR brand LIKE '%$search%')";
}

$order_by = "ORDER BY product_id DESC"; 
if (isset($_GET['sort'])) {
    if ($_GET['sort'] == 'price_low') { $order_by = "ORDER BY price ASC"; }
    elseif ($_GET['sort'] == 'price_high') { $order_by = "ORDER BY price DESC"; }
}

$sql = "SELECT * FROM products";

/**
 * The logic will now combine 'status=Active' with other filters
 * using the WHERE clause.
 */
if (count($where_clauses) > 0) {
    $sql .= " WHERE " . implode(' AND ', $where_clauses);
}
$sql .= " $order_by";

$result = $conn->query($sql);
$categories = $conn->query("SELECT * FROM categories");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Premium Shop | Melody Masters</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { 
            --gold: #e2b04a; 
            --gold-dark: #c19235;
            --bg: #0f172a; 
            --card: #1e293b; 
            --danger: #ef4444; 
            --success: #10b981;
            --text-muted: #94a3b8;
        }

        body { 
            font-family: 'Poppins', sans-serif; 
            background: var(--bg); 
            color: #f8fafc; 
            margin: 0; 
        }

        /* Header Banner */
        .shop-header {
            background: linear-gradient(rgba(15, 23, 42, 0.85), rgba(15, 23, 42, 0.95)), url('uploads/banner.jpg');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            text-align: center;
            padding: 100px 20px;
            border-bottom: 1px solid rgba(226, 176, 74, 0.2);
        }

        /* Sticky Search Bar */
        .search-container {
            background: rgba(30, 41, 59, 0.8);
            backdrop-filter: blur(20px);
            padding: 20px;
            display: flex;
            justify-content: center;
            gap: 15px;
            flex-wrap: wrap;
            position: sticky;
            top: 0;
            z-index: 1000;
            border-bottom: 1px solid rgba(226, 176, 74, 0.15);
        }

        .search-container input, .search-container select {
            background: #0f172a;
            border: 1px solid #334155;
            color: white;
            padding: 12px 18px;
            border-radius: 12px;
            outline: none;
            transition: 0.3s;
        }

        .search-container input:focus, .search-container select:focus { 
            border-color: var(--gold); 
            box-shadow: 0 0 10px rgba(226, 176, 74, 0.1); 
        }

        .btn-search {
            background: linear-gradient(135deg, var(--gold) 0%, var(--gold-dark) 100%);
            color: #0f172a;
            border: none;
            padding: 12px 30px;
            border-radius: 12px;
            cursor: pointer;
            font-weight: 600;
            transition: 0.3s;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .btn-search:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(226, 176, 74, 0.3); }

        /* Shop Wrapper */
        .shop-wrapper { padding: 60px 7%; }
        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 35px;
        }

        /* Premium Product Card */
        .p-card {
            background: var(--card);
            border-radius: 25px;
            padding: 25px;
            text-align: center;
            transition: 0.4s ease;
            border: 1px solid rgba(255,255,255,0.03);
            position: relative;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        .p-card:hover { 
            transform: translateY(-10px); 
            border-color: rgba(226, 176, 74, 0.4);
            box-shadow: 0 20px 40px rgba(0,0,0,0.4);
        }

        .p-card img { 
            width: 100%; 
            height: 220px; 
            object-fit: contain; 
            margin-bottom: 20px;
            transition: 0.4s; 
        }

        .p-card:hover img { transform: scale(1.05); }

        .type-tag {
            position: absolute;
            top: 15px;
            left: 15px;
            background: rgba(226, 176, 74, 0.1);
            color: var(--gold);
            font-size: 10px;
            padding: 4px 12px;
            border-radius: 20px;
            border: 1px solid rgba(226, 176, 74, 0.3);
            font-weight: 600;
            letter-spacing: 1px;
        }

        .rating-stars { color: var(--gold); margin: 8px 0; font-size: 14px; }
        .price { font-size: 26px; font-weight: 600; color: #fff; margin: 5px 0 15px; }
        
        .btn-view {
            display: block;
            background: transparent;
            color: var(--gold);
            padding: 14px;
            text-decoration: none;
            border-radius: 12px;
            font-weight: 600;
            border: 1px solid var(--gold);
            transition: 0.3s;
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 1px;
        }

        .btn-view:hover { 
            background: var(--gold); 
            color: #0f172a; 
            box-shadow: 0 10px 20px rgba(226, 176, 74, 0.2);
        }

        .low-stock { 
            color: var(--danger); 
            font-size: 12px; 
            font-weight: 600; 
            background: rgba(239, 68, 68, 0.1);
            padding: 4px 10px;
            border-radius: 8px;
            display: inline-block;
        }

        .in-stock {
            color: var(--success);
            font-size: 12px;
            font-weight: 600;
            opacity: 0.8;
        }
    </style>
</head>
<body>

    <div class="shop-header">
        <h1 style="margin:0; font-size: 3.5em; letter-spacing: 10px; color: #fff;">THE SHOWROOM</h1>
        <p style="color: var(--gold); font-size: 1.1em; letter-spacing: 3px; margin-top: 10px; text-transform: uppercase;">Curated Excellence in Every Note</p>
    </div>

    <div class="search-container">
        <form method="GET" style="display:contents;">
            <input type="text" name="search" placeholder="Search product or brand..." value="<?php echo $_GET['search'] ?? ''; ?>">
            
            <select name="cat_id">
                <option value="">All Categories</option>
                <?php while($cat = $categories->fetch_assoc()): ?>
                    <option value="<?php echo $cat['category_id']; ?>" <?php echo (isset($_GET['cat_id']) && $_GET['cat_id'] == $cat['category_id']) ? 'selected' : ''; ?>>
                        <?php echo $cat['category_name']; ?>
                    </option>
                <?php endwhile; ?>
            </select>

            <select name="sort" onchange="this.form.submit()">
                <option value="">Sort By</option>
                <option value="price_low" <?php echo (isset($_GET['sort']) && $_GET['sort'] == 'price_low') ? 'selected' : ''; ?>>Price: Low to High</option>
                <option value="price_high" <?php echo (isset($_GET['sort']) && $_GET['sort'] == 'price_high') ? 'selected' : ''; ?>>Price: High to Low</option>
            </select>

            <button type="submit" class="btn-search"><i class="fas fa-search"></i> Search</button>
            <a href="shop.php" style="text-decoration:none; color:var(--text-muted); font-size:12px; align-self:center; margin-left: 10px; text-transform: uppercase; letter-spacing: 1px;">Clear</a>
        </form>
    </div>

    <div class="shop-wrapper">
        <div class="product-grid">
            <?php if ($result->num_rows > 0): ?>
                <?php while($row = $result->fetch_assoc()): ?>
                    <div class="p-card">
                        <span class="type-tag"><?php echo strtoupper($row['product_type'] ?? 'Instrument'); ?></span>
                        
                        <img src="uploads/<?php echo $row['product_image']; ?>" alt="Instrument">
                        
                        <div style="color: var(--gold); font-size: 11px; text-transform: uppercase; letter-spacing: 2px; font-weight: 600;"><?php echo $row['brand']; ?></div>
                        <h3 style="margin: 8px 0; font-size: 1.3rem; color: #fff;"><?php echo $row['product_name']; ?></h3>
                        
                        <div class="rating-stars">
                            <?php 
                            $pid = $row['product_id'];
                            $rating_res = $conn->query("SELECT AVG(rating) as avg FROM reviews WHERE product_id = '$pid'");
                            $avg = round($rating_res->fetch_assoc()['avg'] ?? 0);
                            for($i=1; $i<=5; $i++) echo ($i <= $avg) ? "★" : "☆";
                            ?>
                        </div>

                        <div class="price">Rs. <?php echo number_format($row['price'], 2); ?></div>
                        
                        <div style="margin-bottom: 25px; height: 25px;">
                            <?php if($row['stock_quantity'] > 0 && $row['stock_quantity'] <= 5): ?>
                                <span class="low-stock"><i class="fas fa-history"></i> Limited: Only <?php echo $row['stock_quantity']; ?> left</span>
                            <?php elseif($row['stock_quantity'] == 0): ?>
                                <span style="color: var(--text-muted); font-size: 12px; font-weight: bold; text-transform: uppercase;">Sold Out</span>
                            <?php else: ?>
                                <span class="in-stock"><i class="fas fa-circle" style="font-size: 8px; margin-right: 5px;"></i> Available In Store</span>
                            <?php endif; ?>
                        </div>

                        <?php if($row['stock_quantity'] > 0): ?>
                            <a href="product_details.php?id=<?php echo $row['product_id']; ?>" class="btn-view">View Details</a>
                        <?php else: ?>
                            <button disabled style="width:100%; padding:14px; border:none; border-radius:12px; background:#1e293b; color: #475569; cursor:not-allowed; font-size: 0.85rem; font-weight: 600;">UNAVAILABLE</button>
                        <?php endif; ?>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div style="grid-column: 1 / -1; text-align: center; padding: 120px; background: var(--card); border-radius: 30px; border: 1px dashed rgba(226, 176, 74, 0.3);">
                    <i class="fas fa-music" style="font-size: 50px; color: var(--gold); margin-bottom: 20px; opacity: 0.5;"></i>
                    <h3 style="color: #fff;">No matching instruments found.</h3>
                    <p style="color: var(--text-muted);">Try adjusting your filters or search keywords.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php include 'footer.php'; ?>
</body>
</html>