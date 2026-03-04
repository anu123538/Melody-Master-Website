<div class="sidebar" style="width: 260px; height: 100vh; background: #0f172a; position: fixed; border-right: 1px solid rgba(226,176,74,0.1); padding-top: 30px; font-family: 'Poppins', sans-serif; left: 0; top: 0;">
    <h2 style="color: #e2b04a; text-align: center; margin-bottom: 40px;">
        <i class="fas fa-music"></i> Melody Masters
    </h2>
    
    <ul style="list-style: none; padding: 0;">
        <li style="padding: 15px 30px;">
            <a href="staff_dashboard.php" style="color: <?= (basename($_SERVER['PHP_SELF']) == 'staff_dashboard.php') ? '#e2b04a' : '#f8fafc' ?>; text-decoration: none; display: flex; align-items: center;">
                <i class="fas fa-th-large" style="width: 25px;"></i> Dashboard
            </a>
        </li>
        
        <li style="padding: 15px 30px;">
            <a href="manage_orders.php" style="color: <?= (basename($_SERVER['PHP_SELF']) == 'manage_orders.php') ? '#e2b04a' : '#f8fafc' ?>; text-decoration: none; display: flex; align-items: center;">
                <i class="fas fa-shopping-cart" style="width: 25px;"></i> Manage Orders
            </a>
        </li>
        
        <li style="padding: 15px 30px;">
            <a href="add_product.php" style="color: <?= (basename($_SERVER['PHP_SELF']) == 'add_product.php') ? '#e2b04a' : '#f8fafc' ?>; text-decoration: none; display: flex; align-items: center;">
                <i class="fas fa-plus-circle" style="width: 25px;"></i> Add Products
            </a>
        </li>
        
        <li style="padding: 15px 30px;">
            <a href="manage_products.php" style="color: <?= (basename($_SERVER['PHP_SELF']) == 'manage_products.php') ? '#e2b04a' : '#f8fafc' ?>; text-decoration: none; display: flex; align-items: center;">
                <i class="fas fa-guitar" style="width: 25px;"></i> View Inventory
            </a>
        </li>
        
        <li style="padding: 15px 30px;">
            <a href="profile.php" style="color: <?= (basename($_SERVER['PHP_SELF']) == 'profile.php') ? '#e2b04a' : '#f8fafc' ?>; text-decoration: none; display: flex; align-items: center;">
                <i class="fas fa-user-circle" style="width: 25px;"></i> My Profile
            </a>
        </li>

        <li style="padding: 15px 30px; border-top: 1px solid rgba(255,255,255,0.05); margin-top: 10px;">
            <a href="index.php" target="_blank" style="color: #94a3b8; text-decoration: none; display: flex; align-items: center; font-size: 14px;">
                <i class="fas fa-external-link-alt" style="width: 25px;"></i> View Website
            </a>
        </li>
        
        <li style="padding: 15px 30px; margin-top: 30px;">
            <a href="logout.php" style="color: #ef4444; text-decoration: none; font-weight: bold; display: flex; align-items: center;">
                <i class="fas fa-sign-out-alt" style="width: 25px;"></i> Logout
            </a>
        </li>
    </ul>
</div>