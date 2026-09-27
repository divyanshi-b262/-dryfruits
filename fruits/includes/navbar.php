<?php
$cart_count = getCartCount();
$wishlist_count = getWishlistCount();
?>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top">
    <div class="container">
        <a class="navbar-brand" href="index.php">
            <img src="assets/images/logo.png" alt="Tilawat Dry Fruits" height="50">
            <span class="fw-bold ms-2">Tilawat</span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto align-items-center">
                <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
                <li class="nav-item"><a class="nav-link" href="shop.php">Shop</a></li>
                <li class="nav-item"><a class="nav-link" href="about.php">About</a></li>
                <li class="nav-item"><a class="nav-link" href="contact.php">Contact</a></li>
                
                <?php if(isset($_SESSION['customer_id'])): ?>
                    <li class="nav-item">
                        <a class="nav-link position-relative" href="wishlist.php">
                            <i class="fas fa-heart"></i>
                            <span class="badge bg-danger position-absolute top-0 start-100 translate-middle">
                                <?php echo $wishlist_count; ?>
                            </span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link position-relative" href="cart.php">
                            <i class="fas fa-shopping-cart"></i>
                            <span class="badge bg-danger position-absolute top-0 start-100 translate-middle">
                                <?php echo $cart_count; ?>
                            </span>
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user-circle"></i> Account
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="profile.php"><i class="fas fa-user me-2"></i>Profile</a></li>
                            <li><a class="dropdown-item" href="orders.php"><i class="fas fa-box me-2"></i>Orders</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                        </ul>
                    </li>
                <?php else: ?>
                    <li class="nav-item"><a class="nav-link btn btn-warning text-dark px-3" href="login.php">Login</a></li>
                    <li class="nav-item"><a class="nav-link btn btn-outline-light px-3 ms-2" href="register.php">Register</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>