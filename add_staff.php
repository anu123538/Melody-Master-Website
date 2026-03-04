<?php
session_start();
include 'db_connect.php';

// Authentication: Strictly only Admin can access this page
if (!isset($_SESSION['role']) || strtolower(trim($_SESSION['role'])) !== 'admin') {
    header("Location: login.php");
    exit();
}

$message = "";

// --- LOGIC TO REGISTER NEW STAFF ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['register_staff'])) {
    
    // Collect and sanitize input
    $full_name = mysqli_real_escape_string($conn, $_POST['full_name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password']; 
    $role = 'Staff';

    // Check if the email is already registered
    $check_email = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
    $check_email->bind_param("s", $email);
    $check_email->execute();
    $result = $check_email->get_result();

    if ($result->num_rows > 0) {
        $message = "<div class='msg error'><i class='fas fa-exclamation-triangle'></i> Error: This email is already assigned!</div>";
    } else {
        // Insert staff into the users table using Prepared Statements
        $stmt = $conn->prepare("INSERT INTO users (full_name, email, password, role) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $full_name, $email, $password, $role);
        
        if ($stmt->execute()) {
            $message = "<div class='msg success'><i class='fas fa-check-circle'></i> Staff account for <b>$full_name</b> created successfully!</div>";
        } else {
            $message = "<div class='msg error'><i class='fas fa-times-circle'></i> Error: Failed to create staff account.</div>";
        }
        $stmt->close();
    }
    $check_email->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Staff | Melody Masters</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --gold: #e2b04a; --bg: #0f172a; --card: #1e293b; --text: #f8fafc; }
        body { background: var(--bg); color: var(--text); font-family: 'Poppins', sans-serif; display: flex; margin: 0; }
        
        /* Layout for Sidebar Integration */
        .main-content { margin-left: 260px; width: calc(100% - 260px); padding: 40px; display: flex; justify-content: center; align-items: center; min-height: 100vh; box-sizing: border-box; }
        
        .form-card { 
            background: var(--card); 
            padding: 40px; 
            border-radius: 20px; 
            width: 100%; 
            max-width: 450px; 
            border: 1px solid rgba(226,176,74,0.1); 
            box-shadow: 0 10px 40px rgba(0,0,0,0.3); 
        }
        
        h2 { color: var(--gold); text-align: center; margin-bottom: 30px; font-weight: 600; }
        
        label { color: #94a3b8; font-size: 14px; display: block; margin-bottom: 8px; }
        
        input { 
            width: 100%; padding: 12px 15px; margin-bottom: 20px; 
            background: #0f172a; border: 1px solid #334155; 
            color: white; border-radius: 10px; box-sizing: border-box; 
            transition: 0.3s ease;
        }
        input:focus { border-color: var(--gold); outline: none; background: rgba(226,176,74,0.05); }
        
        .btn-gold { 
            background: var(--gold); color: #0f172a; border: none; padding: 14px; 
            border-radius: 10px; font-weight: bold; cursor: pointer; width: 100%; 
            font-size: 16px; transition: 0.3s;
        }
        .btn-gold:hover { transform: translateY(-3px); box-shadow: 0 8px 20px rgba(226,176,74,0.3); }
        
        /* Status Message Styling */
        .msg { padding: 15px; border-radius: 10px; margin-bottom: 20px; text-align: center; font-size: 14px; }
        .success { background: rgba(16, 185, 129, 0.1); color: #10b981; border: 1px solid #10b981; }
        .error { background: rgba(239, 68, 68, 0.1); color: #ef4444; border: 1px solid #ef4444; }
    </style>
</head>
<body>

<?php include 'admin_sidebar.php'; ?>

<div class="main-content">
    <div class="form-card">
        <h2><i class="fas fa-user-plus"></i> Add New Staff</h2>
        
        <?php echo $message; ?>
        
        <form method="POST">
            <label>Full Name</label>
            <input type="text" name="full_name" placeholder="Enter Full Name" required>
            
            <label>Email Address</label>
            <input type="email" name="email" placeholder="staff@melodymasters.com" required>
            
            <label>Temporary Password</label>
            <input type="password" name="password" placeholder="Create a password" required>
            
            <button type="submit" name="register_staff" class="btn-gold">Register Staff Member</button>
        </form>
        
        <div style="text-align: center; margin-top: 25px;">
            <a href="admin_dashboard.php" style="color: #94a3b8; text-decoration: none; font-size: 13px;">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>
    </div>
</div>

</body>
</html>