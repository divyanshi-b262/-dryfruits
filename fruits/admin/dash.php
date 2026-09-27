```php
<?php

session_start();

include '../includes/db.php';

/* =====================================================
   DASHBOARD COUNTS
===================================================== */

/* Products */

$stmt = $pdo->query("
    SELECT COUNT(*) 
    FROM products
");

$totalProducts = $stmt->fetchColumn();


/* Categories */

$stmt = $pdo->query("
    SELECT COUNT(*) 
    FROM categories
");

$totalCategories = $stmt->fetchColumn();


/* Customers */

$stmt = $pdo->query("
    SELECT COUNT(*) 
    FROM customers
");

$totalCustomers = $stmt->fetchColumn();


/* Orders */

$stmt = $pdo->query("
    SELECT COUNT(*) 
    FROM orders
");

$totalOrders = $stmt->fetchColumn();


/* Gift Packs */

$totalGiftPacks = 0;

try {

    $stmt = $pdo->query("
        SELECT COUNT(*)
        FROM gift_packs
    ");

    $totalGiftPacks = $stmt->fetchColumn();

} catch (PDOException $e) {

    $totalGiftPacks = 0;
}


include 'includes/header.php';

?>

<style>

.dashboard-page {
    background: #fffaf7;
    min-height: 100vh;
    padding: 40px 25px;
}

.dashboard-title {
    color: #7b2d26;
    font-weight: 700;
    margin-bottom: 30px;
}


/* =====================================================
   DASHBOARD CARDS
===================================================== */

.dashboard-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 20px;
}

.dashboard-card {
    background: #ffffff;
    border-radius: 12px;
    padding: 25px;
    box-shadow: 0 4px 18px rgba(0,0,0,0.08);
    border-left: 5px solid #7b2d26;
}

.dashboard-card i {
    font-size: 28px;
    color: #7b2d26;
    margin-bottom: 15px;
}

.dashboard-card h5 {
    color: #555;
    margin-bottom: 8px;
}

.dashboard-card h2 {
    color: #7b2d26;
    font-weight: 700;
    margin: 0;
}


/* =====================================================
   QUICK LINKS
===================================================== */

.quick-links {
    background: white;
    border-radius: 12px;
    padding: 25px;
    margin-top: 30px;
    box-shadow: 0 4px 18px rgba(0,0,0,0.08);
}

.quick-links h4 {
    color: #7b2d26;
    font-weight: 700;
    margin-bottom: 20px;
}

.quick-btn {
    display: inline-block;
    background: #7b2d26;
    color: white;
    text-decoration: none;
    padding: 11px 18px;
    border-radius: 7px;
    margin-right: 10px;
    margin-bottom: 10px;
}

.quick-btn:hover {
    background: #5f211c;
    color: white;
}


/* =====================================================
   RESPONSIVE
===================================================== */

@media(max-width: 992px) {

    .dashboard-grid {
        grid-template-columns: repeat(2, 1fr);
    }

}

@media(max-width: 600px) {

    .dashboard-page {
        padding: 25px 15px;
    }

    .dashboard-grid {
        grid-template-columns: 1fr;
    }

}

</style>


<div class="dashboard-page">

    <div class="container-fluid">

        <h2 class="dashboard-title">

            <i class="fas fa-tachometer-alt"></i>

            Admin Dashboard

        </h2>


        <!-- =========================================
             DASHBOARD CARDS
        ========================================== -->

        <div class="dashboard-grid">


            <!-- PRODUCTS -->

            <div class="dashboard-card">

                <i class="fas fa-box"></i>

                <h5>
                    Total Products
                </h5>

                <h2>
                    <?php
                    echo (int)$totalProducts;
                    ?>
                </h2>

            </div>


            <!-- CATEGORIES -->

            <div class="dashboard-card">

                <i class="fas fa-layer-group"></i>

                <h5>
                    Total Categories
                </h5>

                <h2>
                    <?php
                    echo (int)$totalCategories;
                    ?>
                </h2>

            </div>


            <!-- CUSTOMERS -->

            <div class="dashboard-card">

                <i class="fas fa-users"></i>

                <h5>
                    Total Customers
                </h5>

                <h2>
                    <?php
                    echo (int)$totalCustomers;
                    ?>
                </h2>

            </div>


            <!-- ORDERS -->

            <div class="dashboard-card">

                <i class="fas fa-shopping-bag"></i>

                <h5>
                    Total Orders
                </h5>

                <h2>
                    <?php
                    echo (int)$totalOrders;
                    ?>
                </h2>

            </div>


            <!-- GIFT PACKS -->

            <div class="dashboard-card">

                <i class="fas fa-gift"></i>

                <h5>
                    Gift Packs
                </h5>

                <h2>
                    <?php
                    echo (int)$totalGiftPacks;
                    ?>
                </h2>

            </div>


        </div>


        <!-- =========================================
             QUICK LINKS
        ========================================== -->

        <div class="quick-links">

            <h4>
                <i class="fas fa-bolt"></i>
                Quick Actions
            </h4>


            <a
                href="add-product.php"
                class="quick-btn"
            >
                <i class="fas fa-plus"></i>
                Add Product
            </a>


            <a
                href="products.php"
                class="quick-btn"
            >
                <i class="fas fa-box"></i>
                Manage Products
            </a>


            <a
                href="orders.php"
                class="quick-btn"
            >
                <i class="fas fa-shopping-bag"></i>
                View Orders
            </a>


            <a
                href="categories.php"
                class="quick-btn"
            >
                <i class="fas fa-layer-group"></i>
                Categories
            </a>


            <a
                href="gift-packs.php"
                class="quick-btn"
            >
                <i class="fas fa-gift"></i>
                Gift Packs
            </a>

        </div>

    </div>

</div>


<?php

include 'includes/footer.php';

?>
```
