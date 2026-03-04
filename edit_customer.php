<?php
session_start();
include 'db_connect.php';

// --- AUTHENTICATION CHECK ---
if (!isset($_SESSION['role']) || strtolower(trim($_SESSION['role'])) !== 'admin') {
    header("Location: login.php");
    exit();
}

$message = "";
$user_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// --- UPDATE LOGIC ---
if (isset($_POST['update_customer'])) {
    $full_name = mysqli_real_escape_string($conn, $_POST['full_name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);

    $update_sql = "UPDATE users SET full_name = '$full_name', email = '$email' WHERE user_id = '$user_id' AND role = 'Customer'";
    
    if ($conn->query($update_sql)) {
        $message = "<div class='alert success'>Customer details updated successfully!</div>";
    } else {
        $message = "<div class='alert error'>Error updating details. Please try again.</div>";
    }
}

// Fetch current details
$customer_query = $conn->query("SELECT * FROM users WHERE user_id = '$user_id' AND role = 'Customer'");
$customer = $customer_query->fetch_assoc();

if (!$customer) {
    die("Customer not found!");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Customer | Melody Masters</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --bg-dark: #0f172a; --card-bg: #1e293b; --soft-gold: #e2b04a; --text-light: #f8fafc; }
        body { background: var(--bg-dark); color: var(--text-light); font-family: 'Poppins', sans-serif; display: flex; margin: 0; }
        
        .main-content { margin-left: 260px; width: calc(100% - 260px); padding: 40px; display: flex; justify-content: center; align-items: center; min-height: 100vh; }
        .edit-card { background: var(--card-bg); padding: 40px; border-radius: 20px; width: 100%; max-width: 500px; border: 1px solid rgba(226,176,74,0.1); }
        
        .form-group { margin-bottom: 20px; }
        label { display: block; color: var(--soft-gold); font-size: 12px; text-transform: uppercase; margin-bottom: 5px; }
        input { width: 100%; padding: 12px; background: #0f172a; border: 1px solid #334155; color: white; border-radius: 8px; box-sizing: border-box; }
        
        .btn-container { display: flex; gap: 10px; margin-top: 20px; }
        .btn-save { background: var(--soft-gold); color: #0f172a; border: none; padding: 12px; border-radius: 8px; font-weight: bold; cursor: pointer; flex: 2; }
        .btn-back { background: #334155; color: white; text-decoration: none; padding: 12px; border-radius: 8px; text-align: center; flex: 1; font-size: 14px; }
        
        .alert { padding: 12px; border-radius: 8px; margin-bottom: 20px; font-size: 14px; text-align: center; }
        .success { background: rgba(16, 185, 129, 0.1); color: #10b981; border: 1px solid #10b981; }
        .error { background: rgba(239, 68, 68, 0.1); color: #ef4444; border: 1px solid #ef4444; }
    </style>
</head>
<body>

<?php include 'admin_sidebar.php'; ?>

<div class="main-content">
    <div class="edit-card">
        <h2 style="margin-top:0;"><i class="fas fa-user-edit"></i> Edit Customer</h2>
        <p style="color: #94a3b8; font-size: 14px; margin-bottom: 25px;">Update account information for <b>#<?php echo $user_id; ?></b></p>

        <?php echo $message; ?>

        <form method="POST">
            <div class="form-group">
                <label>Full Name</label>
                <input type="text" name="full_name" value="<?php echo htmlspecialchars($customer['full_name']); ?>" required>
            </div>
            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="email" value="<?php echo htmlspecialchars($customer['email']); ?>" required>
            </div>
            
            <div class="btn-container">
                <a href="manage_customers.php" class="btn-back">Cancel</a>
                <button type="submit" name="update_customer" class="btn-save">Save Changes</button>
            </div>
        </form>
    </div>
</div>

</body>
</html>