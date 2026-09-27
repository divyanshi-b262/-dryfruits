<?php
if(!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Tilawat Dry Fruits</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: #f4f6f9;
        }
        .wrapper {
            display: flex;
            width: 100%;
        }
        #sidebar {
            min-width: 250px;
            max-width: 250px;
            background: #2c3e50;
            color: #fff;
            transition: all 0.3s;
            min-height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1000;
        }
        #sidebar .sidebar-header {
            padding: 20px;
            background: #233240;
            border-bottom: 1px solid #34495e;
        }
        #sidebar .sidebar-header h3 {
            font-size: 18px;
            margin: 0;
        }
        #sidebar ul.components {
            padding: 20px 0;
        }
        #sidebar ul li a {
            padding: 12px 20px;
            display: block;
            color: #b8c7ce;
            text-decoration: none;
            transition: 0.3s;
        }
        #sidebar ul li a:hover {
            color: #fff;
            background: #34495e;
        }
        #sidebar ul li a i {
            margin-right: 10px;
            width: 25px;
        }
        #sidebar ul li.active a {
            background: #34495e;
            color: #fff;
        }
        #content {
            margin-left: 250px;
            padding: 20px;
            width: 100%;
        }
        .navbar-custom {
            background: #fff;
            border-bottom: 1px solid #e5e5e5;
        }
        .stat-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            transition: 0.3s;
        }
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
        }
        @media (max-width: 768px) {
            #sidebar {
                margin-left: -250px;
            }
            #sidebar.active {
                margin-left: 0;
            }
            #content {
                margin-left: 0;
            }
        }
    </style>
</head>
<body>
<div class="wrapper">
    <!-- Sidebar -->
    <nav id="sidebar">
        <div class="sidebar-header text-center">
            <i class="fas fa-store fa-2x"></i>
            <h3 class="mt-2">Tilawat Admin</h3>
            <small class="text-muted"><?php echo $_SESSION['admin_name']; ?></small>
        </div>
        
        <ul class="components list-unstyled">
            <li class="<?php echo basename($_SERVER['PHP_SELF']) == 'dash.php' ? 'active' : ''; ?>">
                <a href="dash.php"><i class="fas fa-home"></i> Dashboard</a>
            </li>
           <!-- <li class="<?php echo basename($_SERVER['PHP_SELF']) == 'sellers.php' ? 'active' : ''; ?>">
                <a href="sellers.php"><i class="fas fa-store"></i> Sellers</a>
            </li>-->
            <li class="<?php echo in_array(basename($_SERVER['PHP_SELF']), ['products.php', 'add-product.php', 'edit-product.php']) ? 'active' : ''; ?>">
                <a href="products.php"><i class="fas fa-box"></i> Products</a>
            </li>
            <li class="<?php echo in_array(basename($_SERVER['PHP_SELF']), ['categories.php', 'add-category.php', 'edit-category.php']) ? 'active' : ''; ?>">
                <a href="categories.php"><i class="fas fa-tags"></i> Categories</a>
            </li>
            <li class="<?php echo basename($_SERVER['PHP_SELF']) == 'orders.php' ? 'active' : ''; ?>">
                <a href="order.php"><i class="fas fa-shopping-bag"></i> Orders</a>
            </li>
            <li class="<?php echo in_array(basename($_SERVER['PHP_SELF']), ['slider.php', 'add-slider.php']) ? 'active' : ''; ?>">
                <a href="slider.php"><i class="fas fa-images"></i> Slider</a>
            </li>
            <li class="<?php echo basename($_SERVER['PHP_SELF']) == 'messages.php' ? 'active' : ''; ?>">
                <a href="add-gifts.php"><i class="fas fa-envelope"></i> Gift-Packs</a>
            </li>
           <!-- <li class="<?php echo basename($_SERVER['PHP_SELF']) == 'settings.php' ? 'active' : ''; ?>">
                <a href="settings.php"><i class="fas fa-cog"></i> Settings</a>
            </li>-->
            <li>
                <a href="logout.php" class="text-danger"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </li>
        </ul>
    </nav>
    
    <!-- Page Content -->
    <div id="content">
        <!-- Top Navbar -->
        <nav class="navbar navbar-expand-lg navbar-custom rounded mb-4">
            <div class="container-fluid">
                <button type="button" id="sidebarCollapse" class="btn btn-dark">
                    <i class="fas fa-bars"></i>
                </button>
                <div class="ms-auto">
                    <span class="text-muted me-3">
                        <i class="fas fa-user"></i> <?php echo $_SESSION['admin_name']; ?>
                    </span>
                    <a href="logout.php" class="btn btn-danger btn-sm">
                        <i class="fas fa-sign-out-alt"></i>
                    </a>
                </div>
            </div>
        </nav>