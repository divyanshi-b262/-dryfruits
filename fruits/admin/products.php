<?php

session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../includes/db.php';


/* =========================================================
   DELETE PRODUCT
   ========================================================= */

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_product'])) {

    $product_id = isset($_POST['product_id'])
        ? (int) $_POST['product_id']
        : 0;

    if ($product_id <= 0) {

        $_SESSION['error'] = 'Invalid product ID.';
        header('Location: product.php');
        exit;
    }

    try {

        /* Get product image */

        $stmt = $pdo->prepare("
            SELECT image
            FROM products
            WHERE id = ?
        ");

        $stmt->execute([$product_id]);

        $product = $stmt->fetch(PDO::FETCH_ASSOC);


        if (!$product) {

            $_SESSION['error'] = 'Product not found.';
            header('Location: product.php');
            exit;
        }


        /* Delete product */

        $stmt = $pdo->prepare("
            DELETE FROM products
            WHERE id = ?
        ");

        $stmt->execute([$product_id]);


        /* Delete image */

        if (!empty($product['image'])) {

            $imageName = basename($product['image']);

            $imagePath = '../assets/images/products/' . $imageName;

            if (file_exists($imagePath)) {

                unlink($imagePath);
            }
        }


        $_SESSION['success'] = 'Product deleted successfully.';

        header('Location: product.php');
        exit;


    } catch (PDOException $e) {

        $_SESSION['error'] =
            'Unable to delete product: ' . $e->getMessage();

        header('Location: product.php');
        exit;
    }
}


/* =========================================================
   FETCH PRODUCTS
   ========================================================= */

try {

    $stmt = $pdo->query("
        SELECT
            p.id,
            p.product_name,
            p.category_id,
            p.description,
            p.price,
            p.stock,
            p.image,
            p.status,
            p.created_at,
            c.category_name

        FROM products p

        LEFT JOIN categories c
            ON p.category_id = c.id

        ORDER BY p.id DESC
    ");

    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {

    die(
        '<div style="
            margin:30px;
            padding:20px;
            background:#f8d7da;
            color:#721c24;
            border:1px solid #f5c6cb;
            border-radius:8px;
            font-family:Arial;
        ">
            <h3>Database Error</h3>
            <p>' .
            htmlspecialchars($e->getMessage()) .
            '</p>
        </div>'
    );
}


/* =========================================================
   MESSAGES
   ========================================================= */

$success = $_SESSION['success'] ?? '';
$error   = $_SESSION['error'] ?? '';

unset($_SESSION['success']);
unset($_SESSION['error']);


/* =========================================================
   HEADER
   ========================================================= */

include 'includes/header.php';

?>


<div class="container-fluid">

    <!-- PAGE HEADER -->

    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>

            <h4 class="fw-bold mb-1">
                Products
            </h4>

            <p class="text-muted mb-0">
                Manage your products.
            </p>

        </div>


        <a
            href="add-product.php"
            class="btn btn-primary"
        >

            <i class="fas fa-plus me-2"></i>

            Add Product

        </a>

    </div>


    <!-- SUCCESS -->

    <?php if (!empty($success)): ?>

        <div class="alert alert-success alert-dismissible fade show">

            <i class="fas fa-check-circle me-2"></i>

            <?= htmlspecialchars($success) ?>

            <button
                type="button"
                class="btn-close"
                data-bs-dismiss="alert"
            ></button>

        </div>

    <?php endif; ?>


    <!-- ERROR -->

    <?php if (!empty($error)): ?>

        <div class="alert alert-danger alert-dismissible fade show">

            <i class="fas fa-exclamation-circle me-2"></i>

            <?= htmlspecialchars($error) ?>

            <button
                type="button"
                class="btn-close"
                data-bs-dismiss="alert"
            ></button>

        </div>

    <?php endif; ?>


    <!-- PRODUCTS -->

    <div class="card border-0 shadow-sm">

        <div class="card-header bg-white">

            <div class="d-flex justify-content-between align-items-center">

                <h5 class="mb-0 fw-bold">

                    <i class="fas fa-box me-2"></i>

                    All Products

                </h5>


                <span class="badge bg-primary">

                    <?= count($products) ?>

                    Products

                </span>

            </div>

        </div>


        <div class="card-body p-0">

            <?php if (empty($products)): ?>

                <div class="text-center py-5">

                    <i class="fas fa-box-open fa-3x text-muted mb-3"></i>

                    <h5>
                        No Products Found
                    </h5>

                    <p class="text-muted">
                        Add your first product to get started.
                    </p>

                    <a
                        href="add-product.php"
                        class="btn btn-primary"
                    >

                        <i class="fas fa-plus me-2"></i>

                        Add Product

                    </a>

                </div>

            <?php else: ?>

                <div class="table-responsive">

                    <table class="table table-hover align-middle mb-0">

                        <thead class="table-light">

                            <tr>

                                <th>ID</th>

                                <th>Image</th>

                                <th>Product</th>

                                <th>Category</th>

                                <th>Price</th>

                                <th>Stock</th>

                                <th>Status</th>

                                <th class="text-center">
                                    Actions
                                </th>

                            </tr>

                        </thead>


                        <tbody>

                        <?php foreach ($products as $product): ?>

                            <?php

                            $productId =
                                (int) $product['id'];

                            $productImage =
                                trim($product['image'] ?? '');

                            ?>


                            <tr>

                                <!-- ID -->

                                <td>

                                    <strong>
                                        <?= $productId ?>
                                    </strong>

                                </td>


                                <!-- IMAGE -->

                                <td>

                                    <?php if (!empty($productImage)): ?>

                                        <img
                                            src="../assets/images/products/<?= htmlspecialchars(basename($productImage)) ?>"
                                            alt="<?= htmlspecialchars($product['product_name']) ?>"
                                            width="70"
                                            height="70"
                                            style="
                                                object-fit:cover;
                                                border-radius:8px;
                                                border:1px solid #ddd;
                                            "
                                        >

                                    <?php else: ?>

                                        <div
                                            style="
                                                width:70px;
                                                height:70px;
                                                display:flex;
                                                align-items:center;
                                                justify-content:center;
                                                background:#f5f5f5;
                                                border-radius:8px;
                                            "
                                        >

                                            <i class="fas fa-image text-muted"></i>

                                        </div>

                                    <?php endif; ?>

                                </td>


                                <!-- PRODUCT -->

                                <td>

                                    <strong>

                                        <?= htmlspecialchars(
                                            $product['product_name']
                                        ) ?>

                                    </strong>


                                    <?php if (!empty($product['description'])): ?>

                                        <div
                                            class="text-muted small"
                                            style="max-width:250px;"
                                        >

                                            <?php

                                            $description =
                                                trim(
                                                    $product['description']
                                                );

                                            if (strlen($description) > 80) {

                                                echo htmlspecialchars(
                                                    substr(
                                                        $description,
                                                        0,
                                                        80
                                                    )
                                                ) . '...';

                                            } else {

                                                echo htmlspecialchars(
                                                    $description
                                                );
                                            }

                                            ?>

                                        </div>

                                    <?php endif; ?>

                                </td>


                                <!-- CATEGORY -->

                                <td>

                                    <?php if (!empty($product['category_name'])): ?>

                                        <span class="badge bg-light text-dark">

                                            <?= htmlspecialchars(
                                                $product['category_name']
                                            ) ?>

                                        </span>

                                    <?php else: ?>

                                        <span class="text-muted">
                                            No Category
                                        </span>

                                    <?php endif; ?>

                                </td>


                                <!-- PRICE -->

                                <td>

                                    <strong>

                                        ₹<?= number_format(
                                            (float) $product['price'],
                                            2
                                        ) ?>

                                    </strong>

                                </td>


                                <!-- STOCK -->

                                <td>

                                    <?php

                                    $stock =
                                        (int) $product['stock'];

                                    ?>


                                    <?php if ($stock <= 0): ?>

                                        <span class="badge bg-danger">
                                            Out of Stock
                                        </span>

                                    <?php elseif ($stock <= 10): ?>

                                        <span class="badge bg-warning text-dark">

                                            <?= $stock ?>

                                        </span>

                                    <?php else: ?>

                                        <span class="badge bg-success">

                                            <?= $stock ?>

                                        </span>

                                    <?php endif; ?>

                                </td>


                                <!-- STATUS -->

                                <td>

                                    <?php if (
                                        isset($product['status']) &&
                                        $product['status'] === 'Active'
                                    ): ?>

                                        <span class="badge bg-success">
                                            Active
                                        </span>

                                    <?php else: ?>

                                        <span class="badge bg-secondary">
                                            Inactive
                                        </span>

                                    <?php endif; ?>

                                </td>


                                <!-- ACTIONS -->

                                <td class="text-center">

                                    <div
                                        class="d-flex justify-content-center gap-2"
                                    >


                                        <!-- EDIT -->

                                        <a
                                            href="edit-product.php?id=<?= $productId ?>"
                                            class="btn btn-primary btn-sm"
                                            title="Edit Product"
                                        >

                                            <i class="fas fa-edit"></i>

                                        </a>


                                        <!-- DELETE -->

                                        <form
                                            method="POST"
                                            action="product.php"
                                            style="display:inline;"
                                            onsubmit="return confirm('Are you sure you want to delete this product?');"
                                        >

                                            <input
                                                type="hidden"
                                                name="product_id"
                                                value="<?= $productId ?>"
                                            >


                                            <button
                                                type="submit"
                                                name="delete_product"
                                                value="1"
                                                class="btn btn-danger btn-sm"
                                                title="Delete Product"
                                            >

                                                <i class="fas fa-trash"></i>

                                            </button>

                                        </form>

                                    </div>

                                </td>

                            </tr>

                        <?php endforeach; ?>

                        </tbody>

                    </table>

                </div>

            <?php endif; ?>

        </div>

    </div>

</div>


<?php include 'includes/footer.php'; ?>