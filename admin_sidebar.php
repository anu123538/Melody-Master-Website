<div class="sidebar">
    <div class="sidebar-brand">
        <i class="fas fa-music"></i> Melody Masters
    </div>
    <ul class="sidebar-menu">
        <li>
            <a href="admin_dashboard.php" class="<?= (basename($_SERVER['PHP_SELF']) == 'admin_dashboard.php') ? 'active' : '' ?>">
                <i class="fas fa-th-large"></i> Dashboard
            </a>
        </li>

        <li>
            <a href="manage_orders.php" class="<?= (basename($_SERVER['PHP_SELF']) == 'manage_orders.php') ? 'active' : '' ?>">
                <i class="fas fa-shopping-cart"></i> Manage Orders
            </a>
        </li>

        <li>
            <a href="add_product.php" class="<?= (basename($_SERVER['PHP_SELF']) == 'add_product.php') ? 'active' : '' ?>">
                <i class="fas fa-plus-circle"></i> Add Products
            </a>
        </li>
        <li>
            <a href="manage_products.php" class="<?= (basename($_SERVER['PHP_SELF']) == 'manage_products.php') ? 'active' : '' ?>">
                <i class="fas fa-guitar"></i> View Inventory
            </a>
        </li>

        <li>
            <a href="manage_customers.php" class="<?= (basename($_SERVER['PHP_SELF']) == 'manage_customers.php') ? 'active' : '' ?>">
                <i class="fas fa-users"></i> Manage Customers
            </a>
        </li>

        <li>
            <a href="manage_staff.php" class="<?= (basename($_SERVER['PHP_SELF']) == 'manage_staff.php') ? 'active' : '' ?>">
                <i class="fas fa-users-cog"></i> Manage Staff
            </a>
        </li>

        <li>
            <a href="manage_reviews.php" class="<?= (basename($_SERVER['PHP_SELF']) == 'manage_reviews.php') ? 'active' : '' ?>">
                <i class="fas fa-star"></i> Manage Reviews
            </a>
        </li>

        <li>
            <a href="add_staff.php" class="<?= (basename($_SERVER['PHP_SELF']) == 'add_staff.php') ? 'active' : '' ?>">
                <i class="fas fa-user-plus"></i> Add Staff
            </a>
        </li>

        <li>
            <a href="profile.php" class="<?= (basename($_SERVER['PHP_SELF']) == 'profile.php') ? 'active' : '' ?>">
                <i class="fas fa-user-circle"></i> My Profile
            </a>
        </li>

        <li style="border-top: 1px solid rgba(255,255,255,0.05); margin-top: 10px; padding-top: 10px;">
            <a href="index.php" target="_blank">
                <i class="fas fa-external-link-alt"></i> View Website
            </a>
        </li>
        
        <li style="margin-top: 20px;">
            <a href="logout.php" style="color: #ef4444;">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </li>
    </ul>
</div>

<style>
    .sidebar { 
        width: 260px; 
        height: 100vh; 
        background: #161e2e; 
        position: fixed; 
        left: 0; 
        top: 0; 
        padding: 20px; 
        border-right: 1px solid rgba(226,176,74,0.1); 
        z-index: 1000;
        box-sizing: border-box;
        font-family: 'Poppins', sans-serif;
    }

    .sidebar-brand { 
        font-size: 22px; 
        font-weight: bold; 
        color: #e2b04a; 
        margin-bottom: 40px; 
        display: flex; 
        align-items: center; 
        gap: 12px; 
    }

    .sidebar-menu { 
        list-style: none; 
        padding: 0; 
        margin: 0;
    }

    .sidebar-menu li { 
        margin-bottom: 8px; 
    }

    .sidebar-menu a { 
        color: #94a3b8; 
        text-decoration: none; 
        display: flex; 
        align-items: center; 
        gap: 15px; 
        padding: 12px 15px; 
        border-radius: 10px; 
        transition: all 0.3s ease; 
        font-size: 0.95em;
    }
    
    .sidebar-menu a:hover, .sidebar-menu a.active { 
        background: rgba(226, 176, 74, 0.15); 
        color: #e2b04a; 
    }
    
    .sidebar-menu a.active {
        border-left: 3px solid #e2b04a;
        padding-left: 12px;
    }
</style>