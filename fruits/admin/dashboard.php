<?php
session_start();
include 'includes/header.php';
include '../includes/db.php';

// Get counts
$total_sellers = $pdo->query("SELECT COUNT(*) FROM sellers")->fetchColumn();
$pending_sellers = $pdo->query("SELECT COUNT(*) FROM sellers WHERE status = 'Pending'")->fetchColumn();
$total_products = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
$total_customers = $pdo->query("SELECT COUNT(*) FROM customers")->fetchColumn();
$total_orders = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$pending_orders = $pdo->query("SELECT COUNT(*) FROM orders WHERE order_status = 'Pending'")->fetchColumn();
$total_messages = $pdo->query("SELECT COUNT(*) FROM contact_messages")->fetchColumn();

// Recent orders
$recent_orders = $pdo->query("
    SELECT o.*, c.full_name 
    FROM orders o 
    JOIN customers c ON o.customer_id = c.id 
    ORDER BY o.created_at DESC 
    LIMIT 5
")->fetchAll();

// Recent sellers
$recent_sellers = $pdo->query("
    SELECT * FROM sellers 
    ORDER BY created_at DESC 
    LIMIT 5
")->fetchAll();
?>

<div class="row">
    <div class="col-md-12">
        <h3 class="fw-bold">Dashboard</h3>
        <p class="text-muted">Welcome back, <?php echo $_SESSION['admin_name']; ?>!</p>
    </div>
</div>

<!-- Stats Cards -->
<div class="row g-4 mb-4">
    <div class="col-md-3 col-sm-6">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <p class="text-muted mb-1">Total Sellers</p>
                    <h3 class="fw-bold"><?php echo $total_sellers; ?></h3>
                    <small class="text-warning"><?php echo $pending_sellers; ?> pending</small>
                </div>
                <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                    <i class="fas fa-store"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <p class="text-muted mb-1">Total Products</p>
                    <h3 class="fw-bold"><?php echo $total_products; ?></h3>
                </div>
                <div class="stat-icon bg-success bg-opacity-10 text-success">
                    <i class="fas fa-box"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <p class="text-muted mb-1">Total Customers</p>
                    <h3 class="fw-bold"><?php echo $total_customers; ?></h3>
                </div>
                <div class="stat-icon bg-info bg-opacity-10 text-info">
                    <i class="fas fa-users"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <p class="text-muted mb-1">Total Orders</p>
                    <h3 class="fw-bold"><?php echo $total_orders; ?></h3>
                    <small class="text-warning"><?php echo $pending_orders; ?> pending</small>
                </div>
                <div class="stat-icon bg-warning bg-opacity-10 text-warning">
                    <i class="fas fa-shopping-bag"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Recent Orders -->
    <div class="col-md-7">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Recent Orders</h5>
                <a href="orders.php" class="btn btn-sm btn-primary">View All</a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Order #</th>
                                <th>Customer</th>
                                <th>Total</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($recent_orders as $order): ?>
                                <tr>
                                    <td>#<?php echo $order['id']; ?></td>
                                    <td><?php echo $order['full_name']; ?></td>
                                    <td>PKR <?php echo number_format($order['total_amount'], 2); ?></td>
                                    <td>
                                        <span class="badge bg-<?php echo $order['order_status'] == 'Pending' ? 'warning' : ($order['order_status'] == 'Completed' ? 'success' : 'danger'); ?>">
                                            <?php echo $order['order_status']; ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Recent Sellers -->
    <div class="col-md-5">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Recent Sellers</h5>
                <a href="sellers.php" class="btn btn-sm btn-primary">Manage</a>
            </div>
            <div class="card-body">
                <?php foreach($recent_sellers as $seller): ?>
                    <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-2">
                        <div>
                            <strong><?php echo $seller['shop_name']; ?></strong>
                            <br>
                            <small class="text-muted"><?php echo $seller['owner_name']; ?></small>
                        </div>
                        <span class="badge bg-<?php echo $seller['status'] == 'Approved' ? 'success' : ($seller['status'] == 'Pending' ? 'warning' : 'danger'); ?>">
                            <?php echo $seller['status']; ?>
                        </span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>