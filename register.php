<?php
/**
 * Melody Masters - Premium Registration System
 * Title color changed to White
 */
include 'db_connect.php';
include 'navbar.php'; 

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $full_name = mysqli_real_escape_string($conn, trim($_POST['full_name']));
    $email     = mysqli_real_escape_string($conn, trim($_POST['email']));
    $contact   = mysqli_real_escape_string($conn, trim($_POST['contact_number']));
    $address   = mysqli_real_escape_string($conn, trim($_POST['address']));
    $password  = $_POST['password']; 

    $checkEmail = $conn->prepare("SELECT email FROM users WHERE email = ?");
    $checkEmail->bind_param("s", $email);
    $checkEmail->execute();
    $result = $checkEmail->get_result();

    if ($result->num_rows > 0) {
        $error = "This email is already registered. Please try to Login.";
    } 
    else if (strlen($password) < 8) {
        $error = "Security Policy: Password must be at least 8 characters long.";
    }
    else {
        $insertStmt = $conn->prepare("INSERT INTO users (full_name, email, password, role, contact_number, address) VALUES (?, ?, ?, 'Customer', ?, ?)");
        $insertStmt->bind_param("sssss", $full_name, $email, $password, $contact, $address);
        
        if ($insertStmt->execute()) {
            $success = "Registration successful! You can now <a href='login.php' style='color:#d4af37; font-weight:bold;'>Login</a>";
        } else {
            $error = "System Error: Registration failed.";
        }
        $insertStmt->close();
    }
    $checkEmail->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register | Melody Masters</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-dark: #1a1a2e;
            --secondary-dark: #16213e;
            --accent-gold: #d4af37; 
            --gradient-gold: linear-gradient(135deg, #d4af37 0%, #b8860b 100%);
            --input-bg: rgba(26, 26, 46, 0.8);
            --text-muted: #a0a0a0;
        }

        body {
            background: var(--primary-dark);
            margin: 0;
            font-family: 'Poppins', sans-serif;
            color: #fff;
            min-height: 100vh;
        }

        .page-wrapper {
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 60px 20px;
            background: radial-gradient(circle at center, rgba(212, 175, 55, 0.03) 0%, transparent 70%);
        }

        .register-card {
            background: var(--secondary-dark);
            width: 100%;
            max-width: 700px;
            padding: 45px 40px;
            border-radius: 25px;
            border: 1px solid rgba(212, 175, 55, 0.15);
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.5);
            animation: fadeIn 0.8s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .header-text { text-align: center; margin-bottom: 35px; }
        
        /* --- Create Account Title Color Changed to White --- */
        .header-text h2 { 
            font-size: 2.2rem; 
            color: #ffffff; /* මෙතැන සුදු පාට කළා */
            margin: 0;
            letter-spacing: 1px;
            font-weight: 700;
        }
        
        .header-text p { color: var(--text-muted); font-size: 0.85rem; text-transform: uppercase; letter-spacing: 2px; margin-top: 5px; }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .form-group { margin-bottom: 10px; }
        .form-group label { display: block; margin-bottom: 8px; font-size: 0.85rem; color: #ccc; margin-left: 5px; }

        .input-box { position: relative; }
        .input-box i {
            position: absolute; left: 15px; top: 50%;
            transform: translateY(-50%); color: var(--accent-gold);
        }

        .form-group input, .form-group textarea {
            width: 100%;
            padding: 14px 15px 14px 45px;
            background: var(--input-bg);
            border: 1px solid rgba(255, 255, 255, 0.05);
            border-radius: 12px;
            color: white;
            outline: none;
            transition: 0.3s;
            font-size: 0.9rem;
        }

        .form-group textarea { padding-left: 15px; height: 100px; resize: none; }
        .full-row { grid-column: span 2; }

        .form-group input:focus, .form-group textarea:focus {
            border-color: var(--accent-gold);
            background: rgba(255, 255, 255, 0.08);
        }

        .btn-container {
            grid-column: span 2; 
            margin-top: 25px;
            display: flex;
            justify-content: center;
        }

        .register-btn {
            width: 100%;
            padding: 16px;
            background: var(--gradient-gold);
            color: #fff; 
            border: none;
            border-radius: 15px;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            transition: 0.4s;
            text-align: center;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
        }

        .register-btn:hover {
            transform: translateY(-2px);
            filter: brightness(1.1);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.4);
        }

        .alert { padding: 14px; border-radius: 12px; margin-bottom: 25px; text-align: center; font-size: 0.9rem; }
        .alert-error { background: rgba(233, 69, 96, 0.1); color: #ff6b9d; border: 1px solid rgba(233, 69, 96, 0.2); }
        .alert-success { background: rgba(0, 255, 127, 0.1); color: #00ff7f; border: 1px solid rgba(0, 255, 127, 0.2); }

        .footer-link { text-align: center; margin-top: 30px; color: var(--text-muted); font-size: 0.9rem; }
        .footer-link a { color: var(--accent-gold); text-decoration: none; font-weight: 600; transition: 0.3s; }
        .footer-link a:hover { text-decoration: underline; }

        @media (max-width: 650px) {
            .form-grid { grid-template-columns: 1fr; }
            .full-row, .btn-container { grid-column: span 1; }
        }
    </style>
</head>
<body>

    <div class="page-wrapper">
        <div class="register-card">
            <div class="header-text">
                <h2>Create Account</h2>
                <p>Start your musical journey</p>
            </div>

            <?php if($error): ?>
                <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?php echo $error; ?></div>
            <?php endif; ?>

            <?php if($success): ?>
                <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?php echo $success; ?></div>
            <?php endif; ?>

            <form action="register.php" method="POST">
                <div class="form-grid">
                    <div class="form-group">
                        <label>Full Name</label>
                        <div class="input-box">
                            <i class="fas fa-user"></i>
                            <input type="text" name="full_name" placeholder="John Doe" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Email Address</label>
                        <div class="input-box">
                            <i class="fas fa-envelope"></i>
                            <input type="email" name="email" placeholder="john@example.com" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Password</label>
                        <div class="input-box">
                            <i class="fas fa-lock"></i>
                            <input type="password" name="password" placeholder="Min. 8 characters" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Contact Number</label>
                        <div class="input-box">
                            <i class="fas fa-phone"></i>
                            <input type="text" name="contact_number" placeholder="07XXXXXXXX" required>
                        </div>
                    </div>

                    <div class="form-group full-row">
                        <label>Shipping Address</label>
                        <textarea name="address" placeholder="Enter your street and city..." required></textarea>
                    </div>

                    <div class="btn-container">
                        <button type="submit" class="register-btn">Register Now</button>
                    </div>
                </div>
            </form>

            <div class="footer-link">
                Already have an account? <a href="login.php">Login here</a>
            </div>
        </div>
    </div>

    <?php include 'footer.php'; ?>

</body>
</html>