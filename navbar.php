<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<nav class="premium-navbar">
    <div class="navbar-container">
        <div class="nav-brand">
            <div class="logo-icon">🎵</div>
            <div class="brand-text">
                <strong>Melody Masters</strong>
                <span class="tagline">Premium Instruments</span>
            </div>
        </div>
        
        <button class="mobile-toggle" onclick="toggleMobileMenu()">
            <span></span>
            <span></span>
            <span></span>
        </button>

        <div class="nav-menu" id="navMenu">
            <a href="index.php" class="nav-item">
                <span class="icon">🏠</span>
                <span>Home</span>
            </a>
            <a href="shop.php" class="nav-item">
                <span class="icon">🛍️</span>
                <span>Shop</span>
            </a>
            <a href="about_contact.php#contact" class="nav-item">
                <span class="icon">📞</span>
                <span>Contact</span>
            </a>

            <?php if(isset($_SESSION['role'])): ?>
                
                <?php if($_SESSION['role'] == 'Customer'): ?>
                    <a href="cart.php" class="nav-item">
                        <span class="icon">🛒</span>
                        <span>My Cart</span>
                    </a>
                    <a href="my_orders.php" class="nav-item">
                        <span class="icon">📦</span>
                        <span>My Orders</span>
                    </a>
                <?php endif; ?>

                <?php if($_SESSION['role'] == 'Admin'): ?>
                    <a href="admin_dashboard.php" class="nav-item admin-link">
                        <span class="icon">⚙️</span>
                        <span>Admin Panel</span>
                    </a>
                <?php endif; ?>

                <?php if($_SESSION['role'] == 'Staff'): ?>
                    <a href="staff_dashboard.php" class="nav-item staff-link">
                        <span class="icon">📊</span>
                        <span>Inventory</span>
                    </a>
                <?php endif; ?>

                <div class="nav-user-section">
                    <a href="profile.php" style="text-decoration: none; display: flex; align-items: center; gap: 10px;">
                        <div class="user-info">
                            <div class="user-avatar" title="View Profile">
                                <?php echo strtoupper(substr($_SESSION['full_name'], 0, 1)); ?>
                            </div>
                            <span class="user-name">Hi, <?php echo htmlspecialchars($_SESSION['full_name']); ?></span>
                        </div>
                    </a>
                    <a href="logout.php" class="btn-logout">
                        <span>Logout</span>
                        <span class="icon">🚪</span>
                    </a>
                </div>

            <?php else: ?>
                <a href="login.php" class="btn-login">
                    <span>Login</span>
                </a>
                <a href="register.php" class="btn-register">
                    <span>Register</span>
                </a>
            <?php endif; ?>
        </div>
    </div>
</nav>

<style>
    /* Styles remain exactly as you provided to maintain the 
       premium aesthetic of Melody Masters.
    */
    * { margin: 0; padding: 0; box-sizing: border-box; }

    :root {
        --primary-dark: #1a1a2e;
        --secondary-dark: #16213e;
        --accent-gold: #ffd700;
        --accent-red: #e94560;
        --text-light: #ffffff;
        --text-muted: #a0a0a0;
        --gradient-primary: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
        --gradient-gold: linear-gradient(135deg, #ffd700 0%, #ffed4e 100%);
        --gradient-red: linear-gradient(135deg, #e94560 0%, #ff6b9d 100%);
    }

    .premium-navbar {
        background: var(--gradient-primary);
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.4);
        position: sticky;
        top: 0;
        z-index: 1000;
        backdrop-filter: blur(10px);
        border-bottom: 2px solid rgba(255, 215, 0, 0.1);
    }

    .navbar-container {
        max-width: 1400px;
        margin: 0 auto;
        padding: 15px 5%;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .nav-brand {
        display: flex;
        align-items: center;
        gap: 12px;
        cursor: pointer;
        transition: transform 0.3s ease;
    }

    .nav-brand:hover { transform: scale(1.05); }

    .logo-icon { font-size: 2em; animation: float 3s ease-in-out infinite; }

    @keyframes float {
        0%, 100% { transform: translateY(0px); }
        50% { transform: translateY(-5px); }
    }

    .brand-text { display: flex; flex-direction: column; }

    .brand-text strong {
        font-size: 1.5em;
        background: var(--gradient-gold);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        letter-spacing: 1px;
        font-weight: 700;
    }

    .tagline { font-size: 0.7em; color: var(--text-muted); letter-spacing: 2px; text-transform: uppercase; }

    .mobile-toggle { display: none; flex-direction: column; gap: 5px; background: none; border: none; cursor: pointer; padding: 5px; }

    .mobile-toggle span { width: 25px; height: 3px; background: var(--accent-gold); border-radius: 3px; transition: all 0.3s ease; }

    .nav-menu { display: flex; align-items: center; gap: 10px; }

    .nav-item {
        color: var(--text-light);
        text-decoration: none;
        padding: 10px 18px;
        border-radius: 8px;
        font-size: 0.95em;
        font-weight: 500;
        display: flex;
        align-items: center;
        gap: 8px;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    .nav-item:hover { color: var(--accent-gold); transform: translateY(-2px); }

    .nav-item .icon { font-size: 1.2em; }

    .admin-link {
        background: rgba(255, 215, 0, 0.1);
        border: 2px solid var(--accent-gold);
        color: var(--accent-gold) !important;
    }

    .staff-link {
        background: rgba(52, 152, 219, 0.1);
        border: 2px solid #3498db;
        color: #3498db !important;
    }

    .nav-user-section {
        display: flex;
        align-items: center;
        gap: 15px;
        margin-left: 15px;
        padding-left: 15px;
        border-left: 1px solid rgba(255, 255, 255, 0.2);
    }

    .user-info { display: flex; align-items: center; gap: 10px; transition: opacity 0.3s; }
    .user-info:hover { opacity: 0.8; }

    .user-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: var(--gradient-gold);
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        color: var(--primary-dark);
        font-size: 1.2em;
        box-shadow: 0 3px 10px rgba(255, 215, 0, 0.3);
    }

    .user-name { color: var(--text-light); font-weight: 600; font-size: 0.95em; }

    .btn-login { background: rgba(255, 255, 255, 0.1); color: var(--text-light); border: 2px solid var(--text-light); }
    .btn-register { background: var(--gradient-gold); color: var(--primary-dark); }
    .btn-logout { background: var(--gradient-red); color: white; }

    /* Buttons Style (Common) */
    .btn-login, .btn-register, .btn-logout {
        padding: 10px 25px; border-radius: 25px; text-decoration: none; font-weight: 600;
        display: flex; align-items: center; gap: 8px; transition: all 0.3s ease;
    }

    @media (max-width: 968px) {
        .mobile-toggle { display: flex; }
        .nav-menu {
            position: fixed; top: 70px; left: -100%; width: 100%; height: calc(100vh - 70px);
            background: var(--gradient-primary); flex-direction: column; padding: 30px; transition: left 0.3s ease;
        }
        .nav-menu.active { left: 0; }
        .nav-user-section { width: 100%; flex-direction: column; align-items: flex-start; border-left: none; border-top: 2px solid rgba(255, 215, 0, 0.3); padding-top: 20px; }
    }
</style>

<script>
function toggleMobileMenu() {
    const navMenu = document.getElementById('navMenu');
    navMenu.classList.toggle('active');
}
</script>