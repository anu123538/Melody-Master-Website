<?php 
include 'db_connect.php'; 
include 'navbar.php'; 

// Fetch categories from the database - LIMIT increased to 5 to include Accessories
$cat_res = $conn->query("SELECT * FROM categories LIMIT 5");

/**
 * UPDATED LOGIC: Fetch the 4 most recent ACTIVE products.
 * This ensures archived/deleted products are not shown in "New Arrivals".
 */
$featured_res = $conn->query("SELECT * FROM products WHERE status = 'Active' ORDER BY product_id DESC LIMIT 4");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Melody Masters - Premium Musical Instruments</title>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    
    <style>
        /* Your existing styles remain exactly the same */
        * { margin: 0; padding: 0; box-sizing: border-box; }

        :root {
            --primary: #1a1a2e;
            --secondary: #16213e;
            --accent: #e94560;
            --gold: #ffd700;
            --light: #f5f5f5;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: var(--light);
            overflow-x: hidden;
        }

        .hero {
            position: relative;
            background: linear-gradient(135deg, rgba(26,26,46,0.95), rgba(22,33,62,0.9)), 
                        url('https://images.unsplash.com/photo-1511379938547-c1f69419868d?auto=format&fit=crop&w=1920&q=80');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            height: 100vh;
            color: white;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            padding: 20px;
        }

        .hero h1 {
            font-size: 4.5em;
            font-weight: 700;
            margin-bottom: 20px;
            background: linear-gradient(135deg, #fff, var(--gold));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .hero p {
            font-size: 1.4em;
            margin-bottom: 35px;
            opacity: 0.95;
            font-weight: 300;
        }

        .btn-shop {
            display: inline-block;
            background: linear-gradient(135deg, var(--accent), #ff6b9d);
            color: white;
            padding: 18px 50px;
            text-decoration: none;
            border-radius: 50px;
            font-weight: 600;
            transition: all 0.4s ease;
            box-shadow: 0 10px 30px rgba(233,69,96,0.4);
        }

        .btn-shop:hover { transform: translateY(-5px); box-shadow: 0 15px 40px rgba(233,69,96,0.6); }

        .section { padding: 80px 5%; }
        .section-title { text-align: center; margin-bottom: 60px; }

        .section-title h2 {
            font-size: 3em;
            color: var(--primary);
            font-weight: 700;
            position: relative;
            display: inline-block;
        }

        .section-title h2::after {
            content: '';
            position: absolute;
            bottom: -15px;
            left: 50%;
            transform: translateX(-50%);
            width: 100px;
            height: 4px;
            background: linear-gradient(90deg, var(--accent), var(--gold));
        }

        .grid-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 35px;
            max-width: 1400px;
            margin: 0 auto;
        }

        .category-card {
            background: white;
            padding: 40px 30px;
            border-radius: 20px;
            text-align: center;
            box-shadow: 0 10px 40px rgba(0,0,0,0.08);
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            cursor: pointer;
            position: relative;
            overflow: hidden;
            border: 2px solid transparent;
        }

        .category-card:hover { transform: translateY(-15px); border-color: var(--gold); }
        .category-card i { font-size: 3em; margin-bottom: 15px; color: var(--accent); display: block; transition: 0.3s; }
        .category-card:hover i { color: var(--gold); transform: scale(1.1); }
        .category-card h3 { font-size: 1.8em; color: var(--primary); margin-bottom: 15px; }
        .category-card small { color: var(--accent); font-weight: 500; }

        .product-card {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 10px 40px rgba(0,0,0,0.08);
            transition: 0.4s;
        }

        .product-image-container { position: relative; height: 250px; background: #eee; }
        .product-card img { width: 100%; height: 100%; object-fit: cover; transition: 0.5s; }
        .product-card:hover img { transform: scale(1.1); }

        .product-info { padding: 25px; }
        .product-price { color: var(--accent); font-weight: 700; font-size: 1.8em; display: block; margin-bottom: 15px; }

        .btn-view {
            display: inline-block;
            background: var(--primary);
            color: white;
            padding: 12px 30px;
            text-decoration: none;
            border-radius: 25px;
            transition: 0.3s;
        }
        .btn-view:hover { background: var(--accent); }

        @media (max-width: 768px) {
            .hero h1 { font-size: 2.5em; }
            .grid-container { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

    <header class="hero">
        <div class="hero-content">
            <h1>Unleash Your Inner Musician</h1>
            <p>Explore the largest collection of professional instruments and sheet music</p>
            <a href="shop.php" class="btn-shop">START SHOPPING NOW</a>
        </div>
    </header>

    <section class="section">
        <div class="section-title">
            <h2>Explore Categories</h2>
        </div>
        <div class="grid-container">
            <?php while($cat = $cat_res->fetch_assoc()): ?>
                <a href="shop.php?cat_id=<?php echo $cat['category_id']; ?>" style="text-decoration:none;">
                    <div class="category-card">
                        <?php 
                        $cName = strtolower($cat['category_name']);
                        if(strpos($cName, 'guitar') !== false) $icon = "fa-guitar";
                        elseif(strpos($cName, 'piano') !== false || strpos($cName, 'keyboard') !== false) $icon = "fa-keyboard";
                        elseif(strpos($cName, 'drum') !== false) $icon = "fa-drum";
                        elseif(strpos($cName, 'accessories') !== false) $icon = "fa-screwdriver-wrench"; 
                        else $icon = "fa-music";
                        ?>
                        <i class="fas <?php echo $icon; ?>"></i>
                        <h3><?php echo htmlspecialchars($cat['category_name']); ?></h3>
                        <small>View Collection →</small>
                    </div>
                </a>
            <?php endwhile; ?>
        </div>
    </section>

    <section class="section" style="background: #f8f9fa;">
        <div class="section-title">
            <h2>New Arrivals</h2>
        </div>
        <div class="grid-container">
            <?php while($p = $featured_res->fetch_assoc()): ?>
                <div class="product-card">
                    <div class="product-image-container">
                        <img src="uploads/<?php echo htmlspecialchars($p['product_image']); ?>" alt="Product">
                    </div>
                    <div class="product-info">
                        <h4><?php echo htmlspecialchars($p['product_name']); ?></h4>
                        <span class="product-price">Rs. <?php echo number_format($p['price'], 2); ?></span>
                        <a href="product_details.php?id=<?php echo $p['product_id']; ?>" class="btn-view">View Product</a>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </section>

    <?php include 'footer.php'; ?>
</body>
</html>