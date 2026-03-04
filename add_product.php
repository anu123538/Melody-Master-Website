<?php
session_start();
include 'db_connect.php';

// --- 1. Access Control (No changes) ---
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

// --- 2. LOGIC TO HANDLE FORM SUBMISSION ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_product'])) {
    
    $cat_id = $_POST['category_id'];

    // Logic: If user wants to add a NEW category
    if ($cat_id == "new" && !empty($_POST['new_category_name'])) {
        $new_cat_name = mysqli_real_escape_string($conn, $_POST['new_category_name']);
        
        // Check if category already exists to avoid duplicates
        $check = $conn->query("SELECT category_id FROM categories WHERE category_name = '$new_cat_name'");
        if ($check->num_rows > 0) {
            $row = $check->fetch_assoc();
            $cat_id = $row['category_id'];
        } else {
            // Insert the brand new category
            $conn->query("INSERT INTO categories (category_name) VALUES ('$new_cat_name')");
            $cat_id = $conn->insert_id; // This ID is now used for the product below
        }
    }

    $name = mysqli_real_escape_string($conn, $_POST['product_name']);
    $brand = mysqli_real_escape_string($conn, $_POST['brand']);
    $price = $_POST['price'];
    $stock = (int)$_POST['stock'];
    $spec = mysqli_real_escape_string($conn, $_POST['specification']);
    $p_type = $_POST['product_type']; 
    
    // Image Upload Logic
    $image_name = $_FILES['product_image']['name'];
    $target_dir = "uploads/";
    if (!is_dir($target_dir)) { mkdir($target_dir, 0777, true); }
    $target_file = $target_dir . basename($image_name);

    if (move_uploaded_file($_FILES['product_image']['tmp_name'], $target_file)) {
        
        // INSERT product with the new or selected $cat_id
        $stmt = $conn->prepare("INSERT INTO products (category_id, product_name, brand, price, stock_quantity, product_image, specifications, product_type) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isssisss", $cat_id, $name, $brand, $price, $stock, $image_name, $spec, $p_type);
        
        if ($stmt->execute()) {
            $new_id = $stmt->insert_id; 

            // Digital Asset logic
            if ($p_type == 'Digital' && isset($_FILES['digital_file'])) {
                $file_name = $_FILES['digital_file']['name'];
                $file_dir = "uploads/digital_assets/";
                if (!is_dir($file_dir)) { mkdir($file_dir, 0777, true); }
                
                $file_path = $file_dir . basename($file_name);
                if (move_uploaded_file($_FILES['digital_file']['tmp_name'], $file_path)) {
                    $conn->query("INSERT INTO digital_products (product_id, file_path) VALUES ('$new_id', '$file_path')");
                }
            }

            // Success Message - The page will reload and the new category will appear in the dropdown list
            $message = "<div style='background: rgba(16, 185, 129, 0.1); color: #10b981; padding: 15px; border-radius: 8px; border: 1px solid #10b981; margin-bottom: 20px;'><i class='fas fa-check-circle'></i> Product and New Category added successfully!</div>";
        } else {
            $message = "<div style='color: #ef4444;'>Database Error: Failed to save product.</div>";
        }
        $stmt->close();
    } else {
        $message = "<div style='color: #ef4444;'>Error: Image upload failed.</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Product | Melody Masters</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --gold: #e2b04a; --bg: #0f172a; --card: #1e293b; --text: #f8fafc; }
        body { background: var(--bg); color: var(--text); font-family: 'Poppins', sans-serif; display: flex; margin: 0; }
        .main-content { margin-left: 260px; width: calc(100% - 260px); padding: 40px; box-sizing: border-box; }
        .form-card { background: var(--card); padding: 30px; border-radius: 15px; max-width: 600px; margin: auto; border: 1px solid rgba(226,176,74,0.1); box-shadow: 0 10px 30px rgba(0,0,0,0.3); }
        input, select, textarea { width: 100%; padding: 12px; margin: 10px 0; background: #0f172a; border: 1px solid #334155; color: white; border-radius: 8px; box-sizing: border-box; outline: none; }
        input:focus, select:focus, textarea:focus { border-color: var(--gold); }
        label { font-size: 14px; color: #94a3b8; font-weight: 500; }
        .btn-gold { background: var(--gold); color: #0f172a; border: none; padding: 14px; border-radius: 8px; font-weight: bold; cursor: pointer; width: 100%; font-size: 16px; margin-top: 20px; transition: 0.3s; }
        .btn-gold:hover { background: #d1a03d; transform: translateY(-2px); }
        #new-cat-box { display: none; background: rgba(226,176,74,0.05); padding: 15px; border-radius: 8px; border: 1px dashed var(--gold); margin: 10px 0; }
    </style>
</head>
<body>

<?php 
if ($current_role === 'admin') { include 'admin_sidebar.php'; } 
else { include 'staff_sidebar.php'; }
?>

<div class="main-content">
    <div class="form-card">
        <div style="text-align: center; margin-bottom: 25px;">
            <h2 style="color: var(--gold); margin: 0;"><i class="fas fa-plus-circle"></i> Add New Product</h2>
            <p style="color: #94a3b8; font-size: 14px;">Logged in as: <?php echo ucfirst($current_role); ?></p>
        </div>

        <?php echo $message; ?>
        
        <form method="POST" enctype="multipart/form-data">
            <label>Product Name</label>
            <input type="text" name="product_name" placeholder="e.g. Yamaha F310" required>
            
            <label>Category</label>
            <select name="category_id" id="cat_select" onchange="toggleCategoryInput()" required>
                <option value="">Select Category</option>
                <?php 
                $cats = $conn->query("SELECT * FROM categories ORDER BY category_name ASC");
                while($c = $cats->fetch_assoc()) {
                    echo "<option value='{$c['category_id']}'>{$c['category_name']}</option>";
                }
                ?>
                <option value="new" style="color: var(--gold); font-weight: bold;">+ Add New Category...</option>
            </select>

            <div id="new-cat-box">
                <label>Enter New Category Name</label>
                <input type="text" name="new_category_name" id="new_cat_input" placeholder="e.g. Electric Guitars">
            </div>

            <label>Product Type</label>
            <select name="product_type" id="product_type" onchange="toggleDigitalField()" required>
                <option value="Physical">Physical Instrument</option>
                <option value="Digital">Digital (Lyrics PDF)</option>
            </select>

            <div id="digital_file_area" style="display:none; border: 1px dashed var(--gold); padding: 10px; margin-top: 10px;">
                <label style="color:var(--gold)">Upload PDF File</label>
                <input type="file" name="digital_file" accept=".pdf">
            </div>
            
            <label>Brand</label>
            <input type="text" name="brand" placeholder="e.g. Yamaha" required>
            
            <div style="display: flex; gap: 15px;">
                <div style="flex: 1;">
                    <label>Price (£)</label>
                    <input type="number" step="0.01" name="price" placeholder="0.00" required>
                </div>
                <div style="flex: 1;">
                    <label>Stock Quantity</label>
                    <input type="number" name="stock" placeholder="0" required>
                </div>
            </div>
            
            <label>Specifications</label>
            <textarea name="specification" rows="3" placeholder="Describe the instrument..."></textarea>
            
            <label>Product Display Image</label>
            <input type="file" name="product_image" accept="image/*" required>
            
            <button type="submit" name="add_product" class="btn-gold">Add Product to Inventory</button>
        </form>
    </div>
</div>

<script>
    function toggleCategoryInput() {
        var select = document.getElementById("cat_select");
        var inputBox = document.getElementById("new-cat-box");
        var inputField = document.getElementById("new_cat_input");
        if (select.value === "new") { 
            inputBox.style.display = "block";
            inputField.required = true;
            inputField.focus(); // Focus for better UX when "Add New" is selected
        } 
        else { 
            inputBox.style.display = "none";
            inputField.required = false;
        }
    }

    function toggleDigitalField() {
        var type = document.getElementById("product_type").value;
        var area = document.getElementById("digital_file_area");
        area.style.display = (type === "Digital") ? "block" : "none";
    }
</script>

</body>
</html>