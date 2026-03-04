<?php
/**
 * Melody Masters - Complete Plain Text Login
 * Optimized with Soft Gold Premium Theme
 */
session_start();
include 'db_connect.php'; 

$error = "";

// --- 1. PHP LOGIN LOGIC (PLAIN TEXT) ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = mysqli_real_escape_string($conn, trim($_POST['email']));
    $password = $_POST['password']; 

    $stmt = $conn->prepare("SELECT user_id, full_name, password, role FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        if ($password === $user['password']) {
            session_regenerate_id(true); 
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['role'] = trim($user['role']); 

            // Role එක පරීක්ෂා කර අදාළ Dashboard එකට යැවීම
            $checkRole = strtolower($_SESSION['role']);
            
            if ($checkRole === 'admin') {
                header("Location: admin_dashboard.php");
            } 
            elseif ($checkRole === 'staff') {
                header("Location: staff_dashboard.php");
            } 
            else {
                // Customer කෙනෙක් නම් පමණක් Home Page (index.php) එකට යැවීම
                header("Location: index.php");
            }
            exit(); 
            
        } else { 
            $error = "Invalid password. Access denied."; 
        }
    } else { 
        $error = "Account not found. Please register first."; 
    }
    $stmt->close();
}

include 'navbar.php'; 
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Melody Masters</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --gold: #e2b04a; /* Elegant Soft Gold */
            --gold-gradient: linear-gradient(135deg, #e2b04a 0%, #c19235 100%);
            --bg: #0f172a;
            --card-bg: #1e293b;
            --input-bg: rgba(15, 23, 42, 0.6);
            --text-muted: #94a3b8;
        }

        body {
            margin: 0;
            background: var(--bg);
            font-family: 'Poppins', sans-serif;
            color: #fff;
            overflow-x: hidden;
        }

        /* Ambient Background Glow */
        .bg-glow {
            position: fixed;
            top: 50%; left: 50%;
            width: 600px; height: 600px;
            background: radial-gradient(circle, rgba(226, 176, 74, 0.05) 0%, transparent 70%);
            transform: translate(-50%, -50%);
            z-index: -1;
        }

        .page-container {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 85vh;
            padding: 20px;
        }

        .login-card {
            background: var(--card-bg);
            width: 100%;
            max-width: 420px;
            padding: 50px 40px;
            border-radius: 30px;
            border: 1px solid rgba(226, 176, 74, 0.15);
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.4);
            text-align: center;
            animation: fadeIn 0.8s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .login-card h1 {
            font-size: 2.2rem;
            margin-bottom: 8px;
            font-weight: 700;
            color: #fff;
            letter-spacing: 1px;
        }

        .subtitle { 
            color: var(--gold); 
            font-size: 0.9rem; 
            margin-bottom: 40px; 
            display: block; 
            text-transform: uppercase;
            letter-spacing: 2px;
            font-weight: 500;
        }

        .form-group { text-align: left; margin-bottom: 25px; }

        .form-group label {
            color: var(--text-muted); 
            font-size: 0.85rem; 
            font-weight: 500;
            margin-bottom: 10px; 
            display: block;
            margin-left: 5px;
        }

        .input-group { position: relative; }

        .input-group i {
            position: absolute; left: 18px; top: 50%;
            transform: translateY(-50%); 
            color: var(--gold);
            font-size: 1.1rem;
        }

        .input-group input {
            width: 100%; 
            padding: 15px 15px 15px 50px;
            background: var(--input-bg);
            border: 1px solid rgba(255, 255, 255, 0.05);
            border-radius: 15px; 
            color: #fff;
            box-sizing: border-box; 
            transition: 0.3s;
            outline: none;
        }

        .input-group input:focus {
            border-color: var(--gold); 
            background: rgba(255, 255, 255, 0.08);
            box-shadow: 0 0 15px rgba(226, 176, 74, 0.1);
        }

        .btn-submit {
            width: 100%; 
            padding: 16px;
            background: var(--gold-gradient); 
            color: white;
            border: none; 
            border-radius: 15px;
            font-weight: 700; 
            cursor: pointer;
            text-transform: uppercase; 
            transition: 0.3s;
            letter-spacing: 1px;
            margin-top: 10px;
        }

        .btn-submit:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(226, 176, 74, 0.25);
            filter: brightness(1.1);
        }

        .error-box {
            background: rgba(239, 68, 68, 0.08);
            color: #f87171; 
            padding: 14px;
            border-radius: 12px; 
            margin-bottom: 25px;
            border: 1px solid rgba(239, 68, 68, 0.2);
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .support-link {
            margin-top: 30px;
            color: var(--text-muted);
            font-size: 0.9rem;
        }

        .support-link a {
            color: var(--gold);
            text-decoration: none;
            font-weight: 600;
            transition: 0.3s;
        }

        .support-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="bg-glow"></div>
    <div class="page-container">
        <div class="login-card">
            <h1>Sign In</h1>
            <span class="subtitle">Melody Masters Console</span>

            <?php if($error): ?>
                <div class="error-box">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form action="login.php" method="POST">
                <div class="form-group">
                    <label>Email Address</label>
                    <div class="input-group">
                        <i class="fas fa-envelope"></i>
                        <input type="email" name="email" required placeholder="name@example.com">
                    </div>
                </div>

                <div class="form-group">
                    <label>Password</label>
                    <div class="input-group">
                        <i class="fas fa-lock"></i>
                        <input type="password" name="password" required placeholder="••••••••">
                    </div>
                </div>

                <button type="submit" name="login" class="btn-submit">LOGIN</button>
            </form>

            <div class="support-link">
                Don't have account? <a href="register.php">Register Here</a>
            </div>
        </div>
    </div>
    <?php include 'footer.php'; ?>
</body>
</html>