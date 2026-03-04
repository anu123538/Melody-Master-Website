<?php
include 'db_connect.php';
include 'navbar.php'; // Navbar එක අනිවාර්යයෙන් දාන්න ලකුණු වැඩි වෙන්න

// 1. ACCESS CONTROL - Staff හෝ Admin පමණක්
if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'Staff' && $_SESSION['role'] !== 'Admin')) {
    header("Location: login.php");
    exit();
}

// 2. UPDATE LOGIC WITH VALIDATION
if (isset($_POST['update'])) {
    $p_id = mysqli_real_escape_string($conn, $_POST['p_id']);
    $new_stock = mysqli_real_escape_string($conn, $_POST['stock_qty']);
    
    // මයිනස් අගයන් දැමිය නොහැකි ලෙස සැකසීම
    if ($new_stock >= 0) {
        $sql = "UPDATE products SET stock_quantity = '$new_stock' WHERE product_id = '$p_id'";
        if ($conn->query($sql)) {
            // සාර්ථක නම් Dashboard එකට යවා පණිවිඩයක් පෙන්වීම
            header("Location: staff_dashboard.php?success=Stock updated for " . urlencode($_POST['p_name']));
            exit();
        }
    } else {
        $error = "Stock cannot be a negative value!";
    }
}

// 3. FETCH CURRENT DATA
$id = mysqli_real_escape_string($conn, $_GET['id']);
$res = $conn->query("SELECT product_name, stock_quantity FROM products WHERE product_id = '$id'");
$product = $res->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Update Inventory - Melody Masters</title>
    <style>
        :root {
            --dark: #2c3e50;
            --accent: #f1c40f;
            --danger: #e74c3c;
        }

        body { font-family: 'Segoe UI', sans-serif; background: #f4f7f6; margin: 0; display: flex; flex-direction: column; min-height: 100vh; }
        
        .main-content { flex: 1; display: flex; justify-content: center; align-items: center; padding: 20px; }

        .update-box { 
            background: white; 
            padding: 40px; 
            border-radius: 15px; 
            box-shadow: 0 10px 30px rgba(0,0,0,0.1); 
            width: 100%; 
            max-width: 400px; 
            text-align: center;
            border-top: 5px solid var(--dark);
        }

        h2 { color: var(--dark); margin-bottom: 10px; }
        .product-label { color: #7f8c8d; font-size: 1.1em; margin-bottom: 30px; display: block; }

        .input-group { text-align: left; margin-bottom: 20px; }
        label { font-weight: bold; color: var(--dark); display: block; margin-bottom: 8px; }
        
        input[type="number"] {
            width: 100%;
            padding: 12px;
            border: 2px solid #eee;
            border-radius: 8px;
            font-size: 1.2em;
            text-align: center;
            box-sizing: border-box;
            outline: none;
            transition: 0.3s;
        }

        input[type="number"]:focus { border-color: var(--dark); }

        .btn-update {
            background: var(--dark);
            color: white;
            border: none;
            padding: 15px;
            width: 100%;
            border-radius: 8px;
            font-weight: bold;
            font-size: 1em;
            cursor: pointer;
            transition: 0.3s;
        }

        .btn-update:hover { background: var(--accent); color: var(--dark); }

        .back-link { display: block; margin-top: 20px; color: #95a5a6; text-decoration: none; font-size: 0.9em; }
        .back-link:hover { color: var(--danger); }

        /* Stock Warning Badge */
        .warning-box {
            background: #fff3cd;
            color: #856404;
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 0.9em;
            border: 1px solid #ffeeba;
        }
    </style>
</head>
<body>

    <div class="main-content">
        <div class="update-box">
            <h2>Update Inventory</h2>
            <span class="product-label"><?php echo htmlspecialchars($product['product_name']); ?></span>

            <?php if(isset($error)) echo "<p style='color:red;'>$error</p>"; ?>

            <?php if($product['stock_quantity'] < 5): ?>
                <div class="warning-box">
                    ⚠️ <strong>Low Stock Alert!</strong> <br> Only <?php echo $product['stock_quantity']; ?> left in store.
                </div>
            <?php endif; ?>

            <form method="POST">
                <input type="hidden" name="p_id" value="<?php echo $id; ?>">
                <input type="hidden" name="p_name" value="<?php echo $product['product_name']; ?>">
                
                <div class="input-group">
                    <label>Set New Stock Quantity</label>
                    <input type="number" name="stock_qty" value="<?php echo $product['stock_quantity']; ?>" min="0" required>
                </div>

                <button type="submit" name="update" class="btn-update">CONFIRM CHANGES</button>
                <a href="staff_dashboard.php" class="back-link">Cancel and Go Back</a>
            </form>
        </div>
    </div>

    

    <?php include 'footer.php'; ?>

</body>
</html>