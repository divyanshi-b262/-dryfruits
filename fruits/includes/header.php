<?php
// includes/header.php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tilawat Dry Fruits</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom CSS -->
    <style>
        /* Global Styles */
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding-top: 140px;
        }
        
        /* Navbar Styles - Brown/Amber/Maroon Theme */
        .navbar-custom {
            background: linear-gradient(135deg, #8B4513 0%, #A0522D 20%, #6B3A2A 40%, #8B0000 70%, #3E1A0A 100%);
            box-shadow: 0 4px 20px rgba(139, 69, 19, 0.5);
            transition: all 0.3s ease;
            padding: 0 !important;
        }
        
        .navbar-custom.scrolled {
            background: linear-gradient(135deg, #6B3410 0%, #8B4513 30%, #5A2D1A 60%, #6B0000 85%, #2A1005 100%);
            box-shadow: 0 4px 30px rgba(139, 69, 19, 0.6);
        }
        
        /* Override Bootstrap navbar collapse */
        .navbar-custom .navbar-collapse {
            flex-basis: 100%;
            flex-grow: 1;
            align-items: center;
        }
        
        /* Top Row - Brand */
        .top-row {
            padding: 15px 0 10px 0;
            border-bottom: 1px solid rgba(255,215,0,0.15);
            width: 100%;
        }
        
        /* Brand/Logo with Image */
        .navbar-brand {
            display: flex;
            align-items: center;
            font-size: 2.2rem;
            font-weight: 800;
            color: #FFD700 !important;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.4);
            letter-spacing: 2px;
            transition: transform 0.3s ease;
            padding: 0;
            margin: 0;
            gap: 15px;
        }
        
        .navbar-brand:hover {
            transform: scale(1.02);
            color: #FFD700 !important;
        }
        
        .navbar-brand .logo-img {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #FFD700;
            box-shadow: 0 4px 15px rgba(255,215,0,0.3);
            transition: all 0.3s ease;
        }
        
        .navbar-brand:hover .logo-img {
            transform: rotate(10deg) scale(1.05);
            box-shadow: 0 6px 25px rgba(255,215,0,0.5);
            border-color: #FFA500;
        }
        
        .navbar-brand .brand-text {
            display: flex;
            flex-direction: column;
        }
        
        .navbar-brand .brand-name {
            font-size: 2.2rem;
            font-weight: 800;
            color: #FFD700;
            line-height: 1;
        }
        
        .navbar-brand .brand-name i {
            color: #FFA500;
            margin-right: 8px;
            animation: pulse 2s infinite;
        }
        
        .navbar-brand .sub-title {
            font-size: 0.9rem;
            font-weight: 300;
            opacity: 0.85;
            letter-spacing: 3px;
            margin-top: 4px;
            color: #D2A679;
        }
        
        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.15); }
        }
        
        /* Top Row Right - Cart & Auth */
        .top-right-buttons {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 12px;
        }
        
        .btn-cart-top {
            background: rgba(255,215,0,0.15);
            color: #FFD700;
            border: 2px solid rgba(255,215,0,0.3);
            border-radius: 25px;
            padding: 10px 22px;
            font-weight: 600;
            transition: all 0.3s ease;
            position: relative;
            font-size: 1rem;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
        }
        
        .btn-cart-top:hover {
            background: rgba(255,215,0,0.3);
            color: #FFD700;
            transform: translateY(-3px);
            box-shadow: 0 6px 25px rgba(255,215,0,0.3);
            text-decoration: none;
            border-color: #FFD700;
        }
        
        .btn-cart-top i {
            margin-right: 8px;
        }
        
        .cart-badge-top {
            position: absolute;
            top: -8px;
            right: -8px;
            background: #FFD700;
            color: #3E1A0A;
            border-radius: 50%;
            padding: 3px 9px;
            font-size: 0.7rem;
            font-weight: 700;
            animation: bounce 1s infinite;
        }
        
        @keyframes bounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-5px); }
        }
        
        .btn-auth-top {
            background: rgba(255,215,0,0.15);
            color: #FFD700;
            border: 2px solid rgba(255,215,0,0.3);
            border-radius: 25px;
            padding: 10px 20px;
            font-weight: 500;
            transition: all 0.3s ease;
            font-size: 0.95rem;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
        }
        
        .btn-auth-top:hover {
            background: #FFD700;
            color: #3E1A0A;
            border-color: #FFD700;
            transform: translateY(-3px);
            box-shadow: 0 6px 25px rgba(255,215,0,0.4);
            text-decoration: none;
        }
        
        .btn-auth-top i {
            margin-right: 6px;
        }
        
        .btn-auth-register-top {
            background: #FFA500;
            color: #3E1A0A;
            border: 2px solid #FFA500;
            border-radius: 25px;
            padding: 10px 20px;
            font-weight: 600;
            transition: all 0.3s ease;
            font-size: 0.95rem;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
        }
        
        .btn-auth-register-top:hover {
            background: #FFB733;
            border-color: #FFB733;
            transform: translateY(-3px);
            box-shadow: 0 6px 25px rgba(255,165,0,0.5);
            color: #2A1005;
            text-decoration: none;
        }
        
        .btn-auth-register-top i {
            margin-right: 6px;
        }
        
        /* Bottom Row - Navigation */
        .bottom-row {
            padding: 0;
            background: rgba(0,0,0,0.25);
            width: 100%;
            border-top: 1px solid rgba(255,215,0,0.08);
        }
        
        .bottom-row .navbar-nav {
            padding: 0;
            margin: 0;
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            flex: 1;
        }
        
        .bottom-row .nav-link {
            color: rgba(255,215,0,0.85) !important;
            font-weight: 600;
            font-size: 1rem;
            padding: 12px 20px !important;
            transition: all 0.3s ease;
            position: relative;
            letter-spacing: 0.5px;
            border-radius: 0;
            border-bottom: 3px solid transparent;
        }
        
        .bottom-row .nav-link:hover {
            background: rgba(255,215,0,0.15);
            color: #FFD700 !important;
            border-bottom-color: #FFA500;
            transform: translateY(-2px);
            text-shadow: 0 0 20px rgba(255,215,0,0.3);
        }
        
        .bottom-row .nav-link.active {
            background: rgba(255,215,0,0.12);
            color: #FFD700 !important;
            border-bottom-color: #FFA500;
        }
        
        .bottom-row .nav-link i {
            margin-right: 8px;
            color: #FFA500;
        }
        
        /* Search Bar CSS - KEPT UNCHANGED */
        .search-form-bottom {
            position: relative;
            width: 400px;
            margin: 8px auto;
        }
        
        .search-form-bottom .form-control {
            border-radius: 25px;
            border: 2px solid rgba(255,215,0,0.3);
            background: rgba(0,0,0,0.3);
            color: #FFD700;
            padding: 8px 20px;
            width: 100%;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
            height: 42px;
        }
        
        .search-form-bottom .form-control::placeholder {
            color: rgba(255,215,0,0.6);
        }
        
        .search-form-bottom .form-control:focus {
            background: rgba(0,0,0,0.4);
            border-color: #FFA500;
            box-shadow: 0 0 25px rgba(255,165,0,0.2);
            color: #FFD700;
        }
        
        .search-form-bottom .btn {
            border-radius: 25px;
            background: #FFA500;
            color: #3E1A0A;
            font-weight: 600;
            padding: 6px 20px;
            border: none;
            transition: all 0.3s ease;
            position: absolute;
            right: 0;
            top: 0;
            height: 100%;
        }
        
        .search-form-bottom .btn:hover {
            background: #FFB733;
            transform: scale(1.05);
            box-shadow: 0 4px 25px rgba(255,165,0,0.4);
        }
        
        /* Bottom row wrapper - Centered layout */
        .bottom-row-wrapper {
            display: flex;
            flex-direction: column;
            align-items: center;
            width: 100%;
            padding: 5px 0;
        }
        
        /* Navigation wrapper */
        .nav-wrapper {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
        }
        
        /* Mobile Toggle */
        .navbar-toggler {
            border: 2px solid rgba(255,215,0,0.3);
            padding: 8px 12px;
            border-radius: 8px;
            transition: all 0.3s ease;
            margin: 8px 0;
        }
        
        .navbar-toggler:hover {
            background: rgba(255,215,0,0.15);
            border-color: #FFA500;
        }
        
        .navbar-toggler-icon {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 30 30'%3e%3cpath stroke='rgba(255,215,0,1)' stroke-linecap='round' stroke-miterlimit='10' stroke-width='2' d='M4 7h22M4 15h22M4 23h22'/%3e%3c/svg%3e");
        }
        
        /* Dropdown */
        .dropdown-menu {
            background: linear-gradient(135deg, #8B4513 0%, #6B3A2A 40%, #3E1A0A 100%);
            border: 1px solid rgba(255,215,0,0.2);
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.5);
            padding: 10px 0;
            margin-top: 0;
        }
        
        .dropdown-item {
            color: rgba(255,215,0,0.85) !important;
            font-weight: 500;
            padding: 10px 25px;
            transition: all 0.3s ease;
            border-radius: 8px;
            margin: 2px 8px;
        }
        
        .dropdown-item:hover {
            background: rgba(255,215,0,0.15);
            color: #FFD700 !important;
            transform: translateX(5px);
        }
        
        .dropdown-item i {
            margin-right: 10px;
            color: #FFA500;
        }
        
        /* Responsive */
        @media (max-width: 992px) {
            body {
                padding-top: 100px;
            }
            
            .navbar-brand .brand-name {
                font-size: 1.8rem;
            }
            
            .navbar-brand .logo-img {
                width: 50px;
                height: 50px;
            }
            
            .navbar-brand .sub-title {
                font-size: 0.7rem;
            }
            
            .top-right-buttons .btn-auth-top,
            .top-right-buttons .btn-auth-register-top {
                padding: 8px 14px;
                font-size: 0.85rem;
            }
            
            .btn-cart-top {
                padding: 8px 14px;
                font-size: 0.85rem;
            }
            
            .search-form-bottom {
                width: 100%;
                max-width: 400px;
                margin: 10px auto;
            }
            
            .bottom-row .nav-link {
                padding: 10px 16px !important;
                border-bottom: none;
                border-left: 3px solid transparent;
            }
            
            .bottom-row .nav-link:hover,
            .bottom-row .nav-link.active {
                border-bottom: none;
                border-left-color: #FFA500;
            }
            
            .dropdown-menu {
                background: rgba(62, 26, 10, 0.95);
            }
            
            .bottom-row-wrapper {
                flex-direction: column;
                align-items: center;
            }
            
            .nav-wrapper {
                flex-direction: column;
                width: 100%;
            }
            
            .navbar-collapse {
                width: 100%;
            }
            
            .bottom-row .navbar-nav {
                flex-direction: column;
                align-items: center;
                width: 100%;
            }
        }
        
        @media (max-width: 768px) {
            body {
                padding-top: 80px;
            }
            
            .navbar-brand .brand-name {
                font-size: 1.4rem;
            }
            
            .navbar-brand .logo-img {
                width: 40px;
                height: 40px;
            }
            
            .top-row {
                padding: 10px 0 8px 0;
            }
            
            .top-right-buttons {
                gap: 6px;
                flex-wrap: wrap;
                justify-content: flex-end;
            }
            
            .top-right-buttons .btn-auth-top,
            .top-right-buttons .btn-auth-register-top {
                padding: 6px 10px;
                font-size: 0.8rem;
            }
            
            .btn-cart-top {
                padding: 6px 10px;
                font-size: 0.8rem;
            }
            
            .cart-badge-top {
                padding: 2px 6px;
                font-size: 0.6rem;
            }
            
            .search-form-bottom {
                max-width: 300px;
            }
            
            .navbar-brand {
                gap: 10px;
            }
        }
        
        @media (max-width: 576px) {
            .navbar-brand .brand-name {
                font-size: 1.1rem;
            }
            
            .navbar-brand .logo-img {
                width: 35px;
                height: 35px;
            }
            
            .navbar-brand .sub-title {
                font-size: 0.55rem;
            }
            
            .navbar-brand {
                gap: 8px;
            }
        }
        
        /* Gradient Animation */
        .navbar-custom {
            background-size: 300% 300%;
            animation: gradientMove 8s ease infinite;
        }
        
        @keyframes gradientMove {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        
        /* User dropdown in top row */
        .dropdown-user .dropdown-menu {
            margin-top: 10px;
        }
        
        /* Fix for navbar collapsing */
        .navbar-custom .container-fluid {
            flex-wrap: wrap;
        }
    </style>
</head>
<body>
    <!-- Navbar - Two Row Layout -->
    <nav class="navbar navbar-expand-lg navbar-custom fixed-top">
        <div class="container-fluid">
            <!-- TOP ROW: Brand + Cart & Auth Buttons -->
            <div class="row w-100 top-row">
                <div class="col-6 col-md-6">
                    <!-- Brand/Logo with Image -->
                    <a class="navbar-brand" href="index.php">
                        <img src="images/logo.jpg" alt="Tilawat Dry Fruits" class="logo-img">
                        <div class="brand-text">
                            <span class="brand-name">
                                </i> Tilawat Dry Fruits
                            </span>
                            <span class="sub-title">Customer's trust our success</span>
                        </div>
                    </a>
                </div>
                
                <div class="col-6 col-md-6">
                    <div class="top-right-buttons">
                        <!-- Cart -->
                        <a href="cart.php" class="btn btn-cart-top">
                            <i class="fas fa-shopping-cart"></i> Cart
                            <span class="cart-badge-top"><?php echo getCartCount(); ?></span>
                        </a>
                        
                        <!-- Auth Buttons -->
                        <?php if(isset($_SESSION['customer_id'])): ?>
                            <div class="dropdown dropdown-user">
                                <button class="btn btn-auth-top dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                    <i class="fas fa-user"></i> <?php echo htmlspecialchars($_SESSION['customer_name'] ?? 'User'); ?>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                  <!---  <li><a class="dropdown-item" href="profile.php"><i class="fas fa-user-circle"></i> Profile</a></li>
                                    <li><a class="dropdown-item" href="orders.php"><i class="fas fa-box"></i> Orders</a></li>
                                    <li><a class="dropdown-item" href="wishlist.php"><i class="fas fa-heart"></i> Wishlist</a></li>
                                    <li><hr class="dropdown-divider" style="border-color: rgba(255,215,0,0.1);"></li>--->
                                    <li><a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                                </ul>
                            </div>
                        <?php else: ?>
                            <a href="login.php" class="btn btn-auth-top">
                                <i class="fas fa-sign-in-alt"></i> Login
                            </a>
                            <a href="register.php" class="btn btn-auth-register-top">
                                <i class="fas fa-user-plus"></i> Register
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- BOTTOM ROW: Navigation + Search (Centered) -->
            <div class="row w-100 bottom-row">
                <div class="col-12">
                    <div class="bottom-row-wrapper">

                        <!-- Mobile Toggle -->
                        <button class="navbar-toggler d-lg-none" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent">
                            <span class="navbar-toggler-icon"></span>
                        </button>
                        
                        <!-- Navbar Content (Collapsible) -->
                        <div class="collapse navbar-collapse" id="navbarContent">
                            <!-- Navigation Links - Centered -->
                            <ul class="navbar-nav">
                                <li class="nav-item">
                                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>" href="index.php">
                                        <i class="fas fa-home"></i> Home
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'shop.php' ? 'active' : ''; ?>" href="shop.php">
                                        <i class="fas fa-store"></i> Shop
                                    </a>
                                </li>
                                <li class="nav-item dropdown">
                                    <a class="nav-link dropdown-toggle" href="#" id="categoryDropdown" role="button" data-bs-toggle="dropdown">
                                        <i class="fas fa-th-list"></i> Categories
                                    </a>
                                    <ul class="dropdown-menu">
                                      <!--  <li><a class="dropdown-item" href="shop.php?category=1"><i class="fas fa-seedling"></i> Dry Fruits</a></li>-->
                                      <!--  <li><a class="dropdown-item" href="shop.php?category=2"><i class="fas fa-nut"></i> Nuts</a></li>-->
                                        <li><a class="dropdown-item" href="gift-packs.php?category=3"><i class="fas fa-gift"></i> Gift Packs</a></li>
                                       <!--  <li><hr class="dropdown-divider" style="border-color: rgba(255,215,0,0.1);"></li>-->
                                      <!--  <li><a class="dropdown-item" href="shop.php"><i class="fas fa-arrow-right"></i> View All</a></li>-->
                                    </ul>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'aboutus.php' ? 'active' : ''; ?>" href="about.html">
                                        <i class="fas fa-info-circle"></i> About
                                    </a>
                                </li>
                               <!-- <li class="nav-item">
                                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'contact.php' ? 'active' : ''; ?>" href="contact.php">
                                        <i class="fas fa-envelope"></i> Contact
                                    </a>
                                </li>-->
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- JavaScript for Scroll Effect -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Navbar scroll effect
            window.addEventListener('scroll', function() {
                const navbar = document.querySelector('.navbar-custom');
                if (window.scrollY > 50) {
                    navbar.classList.add('scrolled');
                } else {
                    navbar.classList.remove('scrolled');
                }
            });
        });
    </script>