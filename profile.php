<?php
session_start();
include 'db_connect.php';

// --- AUTHENTICATION CHECK ---
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role']; 
$message = "";

// --- UPDATE LOGIC ---
if (isset($_POST['update_profile'])) {
    $full_name = mysqli_real_escape_string($conn, $_POST['full_name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $new_pass = $_POST['new_password'];
    $confirm_pass = $_POST['confirm_password'];

    // Update basic information
    $update_sql = "UPDATE users SET full_name = '$full_name', email = '$email' WHERE user_id = '$user_id'";
    if ($conn->query($update_sql)) {
        $_SESSION['full_name'] = $full_name;
        $message = "<div class='alert success'>Profile updated successfully!</div>";

        // Password update logic with length validation
        if (!empty($new_pass)) {
            if (strlen($new_pass) < 6) {
                $message = "<div class='alert error'>Password must be at least 6 characters long!</div>";
            } elseif ($new_pass === $confirm_pass) {
                $conn->query("UPDATE users SET password = '$new_pass' WHERE user_id = '$user_id'");
                $message = "<div class='alert success'>Profile and Password updated!</div>";
            } else {
                $message = "<div class='alert error'>Passwords do not match!</div>";
            }
        }
    }
}

$user_data = $conn->query("SELECT * FROM users WHERE user_id = '$user_id'")->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Profile | Melody Masters</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --bg-dark: #0f172a; --card-bg: #1e293b; --soft-gold: #e2b04a; --text-light: #f8fafc; }
        body { background: var(--bg-dark); color: var(--text-light); font-family: 'Poppins', sans-serif; display: flex; margin: 0; }
        
        .main-content { 
            margin-left: <?php echo ($role !== 'Customer') ? '260px' : '0'; ?>; 
            width: 100%; padding: 40px; display: flex; justify-content: center; 
        }

        .profile-card { background: var(--card-bg); padding: 40px; border-radius: 20px; width: 100%; max-width: 500px; border: 1px solid rgba(226,176,74,0.1); text-align: center; }
        .avatar { width: 80px; height: 80px; background: var(--soft-gold); border-radius: 50%; margin: 0 auto 15px; display: flex; align-items: center; justify-content: center; font-size: 30px; color: #0f172a; font-weight: bold; }
        
        .form-group { text-align: left; margin-bottom: 20px; position: relative; }
        label { display: block; color: var(--soft-gold); font-size: 12px; text-transform: uppercase; margin-bottom: 5px; }
        input { width: 100%; padding: 12px; background: #0f172a; border: 1px solid #334155; color: white; border-radius: 8px; box-sizing: border-box; }
        
        /* Eye icon styling */
        .toggle-password { position: absolute; right: 12px; top: 35px; cursor: pointer; color: #94a3b8; }

        .btn-update { background: var(--soft-gold); color: #0f172a; border: none; padding: 14px; border-radius: 8px; font-weight: bold; cursor: pointer; width: 100%; margin-top: 10px; }
        .alert { padding: 12px; border-radius: 8px; margin-bottom: 20px; font-size: 14px; }
        .success { background: rgba(16, 185, 129, 0.1); color: #10b981; border: 1px solid #10b981; }
        .error { background: rgba(239, 68, 68, 0.1); color: #ef4444; border: 1px solid #ef4444; }
    </style>
</head>
<body>

<?php 
if ($role === 'Admin') { include 'admin_sidebar.php'; } 
elseif ($role === 'Staff') { include 'staff_sidebar.php'; } 
else { include 'navbar.php'; }
?>

<div class="main-content">
    <div class="profile-card">
        <div class="avatar"><?php echo strtoupper(substr($user_data['full_name'], 0, 1)); ?></div>
        <h2 style="margin:0;">Profile Settings</h2>
        <p style="color: #94a3b8; font-size: 14px; margin-bottom: 25px;">Role: <span style="color: var(--soft-gold);"><?php echo $role; ?></span></p>

        <?php echo $message; ?>

        <form method="POST">
            <div class="form-group">
                <label>Full Name</label>
                <input type="text" name="full_name" value="<?php echo htmlspecialchars($user_data['full_name']); ?>" required>
            </div>
            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="email" value="<?php echo htmlspecialchars($user_data['email']); ?>" required>
            </div>
            <div class="form-group">
                <label>New Password (Optional)</label>
                <input type="password" id="pass" name="new_password" placeholder="••••••••">
                <i class="fas fa-eye toggle-password" onclick="togglePass('pass', this)"></i>
            </div>
            <div class="form-group">
                <label>Confirm Password</label>
                <input type="password" id="confirm_pass" name="confirm_password" placeholder="••••••••">
                <i class="fas fa-eye toggle-password" onclick="togglePass('confirm_pass', this)"></i>
            </div>
            <button type="submit" name="update_profile" class="btn-update">Update My Profile</button>
        </form>
    </div>
</div>

<script>
    // JavaScript to toggle password visibility
    function togglePass(id, icon) {
        const input = document.getElementById(id);
        if (input.type === "password") {
            input.type = "text";
            icon.classList.replace("fa-eye", "fa-eye-slash");
        } else {
            input.type = "password";
            icon.classList.replace("fa-eye-slash", "fa-eye");
        }
    }
</script>

</body>
</html>