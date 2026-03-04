<?php
session_start();
include 'db_connect.php';

// Authentication: Only Admin or Staff can edit products
if (!isset($_SESSION['role']) || (strtolower(trim($_SESSION['role'])) !== 'admin' && strtolower(trim($_SESSION['role'])) !== 'staff')) {
    header("Location: login.php");
    exit();
}

$message = "";

// 1. Fetch current product data to show in the form
if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $stmt = $conn->prepare("SELECT * FROM products WHERE product_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();
    
    if (!$product) {
        header("Location: manage_products.php");
        exit();
    }
} else {
    header("Location: manage_products.php");
    exit();
}

// 2. Handle the Update Request
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_product'])) {
    $name = mysqli_real_escape_string($conn, $_POST['product_name']);
    $brand = mysqli_real_escape_string($conn, $_POST['brand']);
    $price = (float)$_POST['price'];
    $stock = (int)$_POST['stock_quantity']; // Using your DB column name
    
    // Update query
    $update_stmt = $conn->prepare("UPDATE products SET product_name = ?, brand = ?, price = ?, stock_quantity = ? WHERE product_id = ?");
    $update_stmt->bind_param("ssdii", $name, $brand, $price, $stock, $id);
    
    if ($update_stmt->execute()) {
        $message = "<div class='msg success'><i class='fas fa-check-circle'></i> Product updated successfully!</div>";
        // Refresh local data to show updated values in form
        $product['product_name'] = $name;
        $product['brand'] = $brand;
        $product['price'] = $price;
        $product['stock_quantity'] = $stock;
    } else {
        $message = "<div class='msg error'><i class='fas fa-times-circle'></i> Update failed. Please try again.</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Product | Melody Masters</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --gold: #e2b04a; --bg: #0f172a; --card: #1e293b; --text: #f8fafc; }
        body { background: var(--bg); color: var(--text); font-family: 'Poppins', sans-serif; display: flex; margin: 0; }
        .main-content { margin-left: 260px; width: calc(100% - 260px); padding: 40px; display: flex; justify-content: center; align-items: center; min-height: 100vh; box-sizing: border-box; }
        
        .form-card { background: var(--card); padding: 40px; border-radius: 20px; width: 100%; max-width: 500px; border: 1px solid rgba(226,176,74,0.1); box-shadow: 0 10px 30px rgba(0,0,0,0.3); }
        h2 { color: var(--gold); text-align: center; margin-bottom: 30px; }
        
        label { color: #94a3b8; font-size: 14px; display: block; margin-bottom: 8px; }
        input { width: 100%; padding: 12px; margin-bottom: 20px; background: #0f172a; border: 1px solid #334155; color: white; border-radius: 10px; box-sizing: border-box; outline: none; }
        input:focus { border-color: var(--gold); }
        
        .btn-gold { background: var(--gold); color: #0f172a; border: none; padding: 14px; border-radius: 10px; font-weight: bold; cursor: pointer; width: 100%; font-size: 16px; transition: 0.3s; }
        .btn-gold:hover { transform: scale(1.02); opacity: 0.9; }
        
        .msg { padding: 15px; border-radius: 10px; margin-bottom: 20px; text-align: center; font-size: 14px; }
        .success { background: rgba(16, 185, 129, 0.1); color: #10b981; border: 1px solid #10b981; }
        .error { background: rgba(239, 68, 68, 0.1); color: #ef4444; border: 1px solid #ef4444; }
        
        .back-link { display: block; text-align: center; margin-top: 20px; color: #94a3b8; text-decoration: none; font-size: 14px; }
        .back-link:hover { color: var(--gold); }
    </style>
</head>
<body>

<?php include 'admin_sidebar.php'; ?>

<div class="main-content">
    <div class="form-card">
        <h2><i class="fas fa-edit"></i> Edit Product</h2>
        
        <?php echo $message; ?>
        
        <form method="POST">
            <label>Product Name</label>
            <input type="text" name="product_name" value="<?php echo htmlspecialchars($product['product_name']); ?>" required>
            
            <label>Brand</label>
            <input type="text" name="brand" value="<?php echo htmlspecialchars($product['brand']); ?>" required>
            
            <label>Price (Rs.)</label>
            <input type="number" step="0.01" name="price" value="<?php echo $product['price']; ?>" required>
            
            <label>Stock Quantity</label>
            <input type="number" name="stock_quantity" value="<?php echo $product['stock_quantity']; ?>" required>
            
            <button type="submit" name="update_product" class="btn-gold">Update Product Information</button>
        </form>
        
        <a href="manage_products.php" class="back-link">
            <i class="fas fa-arrow-left"></i> Cancel and Go Back
        </a>
    </div>
</div>

</body>
</html>