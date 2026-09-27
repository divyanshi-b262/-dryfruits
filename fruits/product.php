<?php
include 'includes/db.php';
include 'includes/header.php';

$product_id = isset($_GET['id']) ? $_GET['id'] : 0;

$stmt = $pdo->prepare("
    SELECT p.*, c.category_name, s.shop_name, s.owner_name 
    FROM products p 
    JOIN categories c ON p.category_id = c.id 
    JOIN sellers s ON p.seller_id = s.id 
    WHERE p.id = ?
");
$stmt->execute([$product_id]);
$product = $stmt->fetch();

if(!$product) {
    header('Location: shop.php');
    exit;
}

// Get related products
$related = $pdo->prepare("
    SELECT * FROM products 
    WHERE category_id = ? AND id != ? AND status = 'Active' 
    LIMIT 4
");
$related->execute([$product['category_id'], $product_id]);
$related_products = $related->fetchAll();

// Get reviews
$reviews = $pdo->prepare("
    SELECT r.*, c.full_name 
    FROM reviews r 
    JOIN customers c ON r.customer_id = c.id 
    WHERE r.product_id = ? 
    ORDER BY r.created_at DESC
");
$reviews->execute([$product_id]);
$product_reviews = $reviews->fetchAll();

// Add to cart
if(isset($_GET['add'])) {
    if(!isset($_SESSION['customer_id'])) {
        header('Location: login.php');
        exit;
    }
    $cart_stmt = $pdo->prepare("INSERT INTO cart (customer_id, product_id, quantity) VALUES (?, ?, 1)");
    $cart_stmt->execute([$_SESSION['customer_id'], $product_id]);
    header('Location: cart.php');
    exit;
}
?>

<!-- Product Details -->
<section class="py-5">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                <li class="breadcrumb-item"><a href="shop.php">Shop</a></li>
                <li class="breadcrumb-item"><a href="shop.php?category=<?php echo $product['category_id']; ?>"><?php echo $product['category_name']; ?></a></li>
                <li class="breadcrumb-item active"><?php echo $product['product_name']; ?></li>
            </ol>
        </nav>

        <div class="row g-5">
            <!-- Product Image -->
            <div class="col-md-6">
                <img src="assets/images/products/<?php echo $product['image'] ?: 'default.jpg'; ?>" 
                     class="img-fluid rounded shadow" alt="<?php echo $product['product_name']; ?>">
                
                <?php if($product['stock'] < 1): ?>
                    <div class="alert alert-danger mt-3">Out of Stock</div>
                <?php endif; ?>
            </div>

            <!-- Product Info -->
            <div class="col-md-6">
                <h2 class="fw-bold"><?php echo $product['product_name']; ?></h2>
                <p class="text-muted">By <?php echo $product['shop_name']; ?></p>
                
                <div class="d-flex align-items-center gap-3 mb-3">
                    <span class="display-6 fw-bold text-warning">PKR <?php echo number_format($product['price'], 2); ?></span>
                    <?php if($product['stock'] > 0): ?>
                        <span class="badge bg-success">In Stock</span>
                    <?php endif; ?>
                </div>

                <div class="mb-4">
                    <h6>Description</h6>
                    <p><?php echo nl2br($product['description']); ?></p>
                </div>

                <div class="d-flex gap-3">
                    <?php if($product['stock'] > 0): ?>
                        <a href="product.php?id=<?php echo $product_id; ?>&add=1" class="btn btn-warning btn-lg px-5">
                            <i class="fas fa-shopping-cart me-2"></i>Add to Cart
                        </a>
                        <a href="wishlist.php?add=<?php echo $product_id; ?>" class="btn btn-outline-dark btn-lg">
                            <i class="fas fa-heart"></i>
                        </a>
                    <?php endif; ?>
                </div>

                <hr class="my-4">
                <div class="row g-3">
                    <div class="col-6">
                        <small class="text-muted">Category</small>
                        <p><?php echo $product['category_name']; ?></p>
                    </div>
                    <div class="col-6">
                        <small class="text-muted">Seller</small>
                        <p><?php echo $product['owner_name']; ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Reviews Section -->
        <div class="mt-5 pt-5 border-top">
            <h4>Customer Reviews</h4>
            <?php if(count($product_reviews) > 0): ?>
                <?php foreach($product_reviews as $review): ?>
                    <div class="review-item bg-light p-3 rounded mb-3">
                        <div class="d-flex justify-content-between">
                            <strong><?php echo $review['full_name']; ?></strong>
                            <span class="text-muted small"><?php echo date('M d, Y', strtotime($review['created_at'])); ?></span>
                        </div>
                        <div class="text-warning">
                            <?php echo str_repeat('★', $review['rating']) . str_repeat('☆', 5 - $review['rating']); ?>
                        </div>
                        <p><?php echo $review['review']; ?></p>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="text-muted">No reviews yet. Be the first to review!</p>
            <?php endif; ?>
        </div>

        <!-- Related Products -->
        <?php if(count($related_products) > 0): ?>
            <div class="mt-5 pt-5 border-top">
                <h4 class="mb-4">Related Products</h4>
                <div class="row g-4">
                    <?php foreach($related_products as $rel): ?>
                        <div class="col-6 col-md-3">
                            <div class="card product-card h-100 shadow-sm">
                                <img src="assets/images/products/<?php echo $rel['image'] ?: 'default.jpg'; ?>" 
                                     class="card-img-top p-2" alt="<?php echo $rel['product_name']; ?>">
                                <div class="card-body">
                                    <h6 class="card-title"><?php echo $rel['product_name']; ?></h6>
                                    <span class="fw-bold text-warning">PKR <?php echo number_format($rel['price'], 2); ?></span>
                                    <a href="product.php?id=<?php echo $rel['id']; ?>" class="btn btn-sm btn-dark w-100 mt-2">View</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php include 'includes/footer.php'; ?>