<?php
session_start();
include 'db_connect.php';

// Authentication: Only Admin access
if (!isset($_SESSION['role']) || strtolower(trim($_SESSION['role'])) !== 'admin') {
    header("Location: login.php");
    exit();
}

$message = "";

// Check if a valid ID is provided in the URL
if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    
    // Fetch current details of the staff member
    $stmt = $conn->prepare("SELECT full_name, email FROM users WHERE user_id = ? AND role = 'Staff'");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $staff = $result->fetch_assoc();
    
    // Redirect if staff record does not exist
    if (!$staff) {
        header("Location: manage_staff.php");
        exit();
    }
} else {
    header("Location: manage_staff.php");
    exit();
}

// Handle Update Request
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_staff'])) {
    $full_name = mysqli_real_escape_string($conn, $_POST['full_name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    
    // Secure update using Prepared Statement
    $update_stmt = $conn->prepare("UPDATE users SET full_name = ?, email = ? WHERE user_id = ? AND role = 'Staff'");
    $update_stmt->bind_param("ssi", $full_name, $email, $id);
    
    if ($update_stmt->execute()) {
        $message = "<div class='msg success'><i class='fas fa-check-circle'></i> Staff details updated successfully!</div>";
        // Update local variables to show updated data in the form fields
        $staff['full_name'] = $full_name;
        $staff['email'] = $email;
    } else {
        $message = "<div class='msg error'><i class='fas fa-times-circle'></i> Error: Could not update details.</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Staff | Melody Masters</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --gold: #e2b04a; --bg: #0f172a; --card: #1e293b; --text: #f8fafc; }
        body { background: var(--bg); color: var(--text); font-family: 'Poppins', sans-serif; display: flex; margin: 0; }
        .main-content { margin-left: 260px; width: calc(100% - 260px); padding: 40px; display: flex; justify-content: center; align-items: center; min-height: 100vh; box-sizing: border-box; }
        .form-card { background: var(--card); padding: 40px; border-radius: 20px; width: 100%; max-width: 450px; border: 1px solid rgba(226,176,74,0.1); }
        h2 { color: var(--gold); text-align: center; margin-bottom: 30px; }
        label { color: #94a3b8; font-size: 14px; display: block; margin-bottom: 8px; }
        input { width: 100%; padding: 12px; margin-bottom: 20px; background: #0f172a; border: 1px solid #334155; color: white; border-radius: 10px; box-sizing: border-box; }
        .btn-gold { background: var(--gold); color: #0f172a; border: none; padding: 14px; border-radius: 10px; font-weight: bold; cursor: pointer; width: 100%; font-size: 16px; }
        .msg { padding: 15px; border-radius: 10px; margin-bottom: 20px; text-align: center; }
        .success { background: rgba(16, 185, 129, 0.1); color: #10b981; border: 1px solid #10b981; }
        .error { background: rgba(239, 68, 68, 0.1); color: #ef4444; border: 1px solid #ef4444; }
    </style>
</head>
<body>

<?php include 'admin_sidebar.php'; ?>

<div class="main-content">
    <div class="form-card">
        <h2><i class="fas fa-user-edit"></i> Edit Staff</h2>
        
        <?php echo $message; ?>
        
        <form method="POST">
            <label>Full Name</label>
            <input type="text" name="full_name" value="<?php echo htmlspecialchars($staff['full_name']); ?>" required>
            
            <label>Email Address</label>
            <input type="email" name="email" value="<?php echo htmlspecialchars($staff['email']); ?>" required>
            
            <button type="submit" name="update_staff" class="btn-gold">Update Staff Member</button>
        </form>
        
        <div style="text-align: center; margin-top: 25px;">
            <a href="manage_staff.php" style="color: #94a3b8; text-decoration: none; font-size: 14px;">
                <i class="fas fa-arrow-left"></i> Back to Staff List
            </a>
        </div>
    </div>
</div>

</body>
</html>