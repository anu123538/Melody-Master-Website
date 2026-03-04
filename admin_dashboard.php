<?php
session_start();
include 'db_connect.php'; 

// Authentication check
if (!isset($_SESSION['role']) || strtolower(trim($_SESSION['role'])) !== 'admin') {
    header("Location: login.php");
    exit();
}

// --- DATA FETCHING ---
$income = $conn->query("SELECT SUM(total_amount) as total FROM orders WHERE order_status='Delivered'")->fetch_assoc();
$totalProds = $conn->query("SELECT COUNT(*) as count FROM products")->fetch_assoc();
$pendingOrders = $conn->query("SELECT COUNT(*) as count FROM orders WHERE order_status = 'Pending'")->fetch_assoc();
$staffCount = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'Staff'")->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard | Melody Masters</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --gold: #e2b04a; --bg: #0f172a; --card: #1e293b; --text: #f8fafc; }
        body { background: var(--bg); color: var(--text); font-family: 'Poppins', sans-serif; margin: 0; display: flex; }

        /* Sidebar සඳහා ඉඩ වෙන් කිරීම */
        .main-content { margin-left: 260px; width: calc(100% - 260px); padding: 40px; box-sizing: border-box; }
        
        /* Stats Cards Styling */
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; margin-bottom: 30px; }
        
        .stat-card { 
            background: var(--card); 
            padding: 25px; 
            border-radius: 15px; 
            border-bottom: 4px solid var(--gold); 
            transition: 0.3s;
        }
        .stat-card:hover { transform: translateY(-5px); }
        .stat-card h4 { margin: 0; color: #94a3b8; font-size: 14px; text-transform: uppercase; }
        .stat-card h2 { margin: 10px 0 0; font-size: 28px; }

        .glass-card { background: var(--card); border-radius: 15px; padding: 25px; margin-bottom: 25px; box-shadow: 0 10px 30px rgba(0,0,0,0.2); }
        
        .btn-gold { 
            background: var(--gold); color: #0f172a; border: none; 
            padding: 12px 20px; border-radius: 8px; font-weight: 600; 
            text-decoration: none; cursor: pointer; display: inline-block;
        }

        /* Print කරනකොට Sidebar එක පේන්න අවශ්‍ය නැති නිසා */
        @media print {
            .sidebar, .no-print { display: none !important; }
            .main-content { margin-left: 0; width: 100%; }
        }
    </style>
</head>
<body>

<?php include 'admin_sidebar.php'; ?>

<div class="main-content">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
        <div>
            <h2 style="margin:0;">Admin Overview</h2>
            <p style="color: #94a3b8; margin-top: 5px;">Summary of Melody Masters performance.</p>
        </div>
        <div class="no-print">
            <button onclick="window.print()" class="btn-gold"><i class="fas fa-print"></i> Generate Report</button>
        </div>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <h4>Total Revenue</h4>
            <h2 style="color: #10b981;">£<?php echo number_format($income['total'] ?? 0, 2); ?></h2>
        </div>
        <div class="stat-card" style="border-bottom-color: #f39c12;">
            <h4>Pending Orders</h4>
            <h2 style="color: #f39c12;"><?php echo $pendingOrders['count']; ?></h2>
        </div>
        <div class="stat-card" style="border-bottom-color: #3b82f6;">
            <h4>Products</h4>
            <h2 style="color: #3b82f6;"><?php echo $totalProds['count']; ?></h2>
        </div>
        <div class="stat-card" style="border-bottom-color: #ef4444;">
            <h4>Staff Members</h4>
            <h2 style="color: #ef4444;"><?php echo $staffCount['count']; ?></h2>
        </div>
    </div>

    <div class="glass-card">
        <h3 style="margin-top:0;"><i class="fas fa-chart-line" style="color: var(--gold);"></i> Sales Performance</h3>
        <canvas id="salesChart" height="100"></canvas>
    </div>
</div>

<script>
    new Chart(document.getElementById('salesChart'), {
        type: 'line',
        data: {
            labels: ['Week 1', 'Week 2', 'Week 3', 'Week 4'],
            datasets: [{ 
                label: 'Revenue', 
                data: [400, 900, 600, <?php echo $income['total'] ?? 0; ?>], 
                borderColor: '#e2b04a',
                backgroundColor: 'rgba(226, 176, 74, 0.1)',
                fill: true,
                tension: 0.4,
                borderWidth: 3,
                pointRadius: 4
            }]
        },
        options: { 
            responsive: true,
            plugins: { legend: { display: false } },
            scales: {
                y: { grid: { color: 'rgba(255,255,255,0.05)' }, ticks: { color: '#94a3b8' } },
                x: { grid: { display: false }, ticks: { color: '#94a3b8' } }
            }
        }
    });
</script>

</body>
</html>