
<?php
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../includes/db.php';

/* =========================================================
   CHECK PRODUCT ID
   ========================================================= */
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Product ID is missing or invalid. Please open this page using the Edit button.");
}

$product_id = (int) $_GET['id'];


/* =========================================================
   FETCH PRODUCT
   ========================================================= */
try {

    $stmt = $pdo->prepare("
        SELECT p.*, c.category_name
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE p.id = ?
    ");

    $stmt->execute([$product_id]);

    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        die("Product not found.");
    }

} catch (PDOException $e) {

    die("Database Error: " . $e->getMessage());

}


/* =========================================================
   FETCH CATEGORIES
   ========================================================= */
try {

    $categoryStmt = $pdo->query("
        SELECT id, category_name
        FROM categories
        ORDER BY category_name ASC
    ");

    $categories = $categoryStmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {

    die("Category Database Error: " . $e->getMessage());

}


/* =========================================================
   UPDATE PRODUCT
   ========================================================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $product_name = trim($_POST['product_name'] ?? '');
    $category_id  = (int) ($_POST['category_id'] ?? 0);
    $description  = trim($_POST['description'] ?? '');
    $price        = $_POST['price'] ?? '';
    $stock        = $_POST['stock'] ?? '';
    $status       = $_POST['status'] ?? 'Active';

    /* -------------------------
       VALIDATION
       ------------------------- */

    if ($product_name === '') {
        $_SESSION['error'] = 'Product name is required.';
    }

    elseif ($category_id <= 0) {
        $_SESSION['error'] = 'Please select a category.';
    }

    elseif ($price === '' || !is_numeric($price) || $price < 0) {
        $_SESSION['error'] = 'Please enter a valid price.';
    }

    elseif ($stock === '' || !is_numeric($stock) || $stock < 0) {
        $_SESSION['error'] = 'Please enter a valid stock quantity.';
    }

    elseif (!in_array($status, ['Active', 'Inactive'], true)) {
        $_SESSION['error'] = 'Invalid product status.';
    }


    /* =====================================================
       IF NO VALIDATION ERROR
       ===================================================== */

    if (!isset($_SESSION['error'])) {

        $newImageName = $product['image'];
        $oldImageName = $product['image'];

        /* -------------------------
           IMAGE UPLOAD
           ------------------------- */

        if (
            isset($_FILES['image']) &&
            $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE
        ) {

            if ($_FILES['image']['error'] !== UPLOAD_ERR_OK) {

                $_SESSION['error'] = 'There was an error uploading the image.';

            } else {

                $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];

                $originalName = $_FILES['image']['name'];
                $tmpName      = $_FILES['image']['tmp_name'];
                $fileSize     = $_FILES['image']['size'];

                $extension = strtolower(
                    pathinfo($originalName, PATHINFO_EXTENSION)
                );

                /* Check extension */

                if (!in_array($extension, $allowedExtensions, true)) {

                    $_SESSION['error'] =
                        'Invalid image format. Use JPG, JPEG, PNG or WEBP.';

                }

                /* Check file size - 5 MB */

                elseif ($fileSize > 5 * 1024 * 1024) {

                    $_SESSION['error'] =
                        'Image size must be less than 5 MB.';

                }

                else {

                    $uploadDirectory = '../assets/images/products/';

                    /* Create directory if it doesn't exist */

                    if (!is_dir($uploadDirectory)) {
                        mkdir($uploadDirectory, 0777, true);
                    }

                    /* Generate unique filename */

                    $newImageName =
                        time() . '_' .
                        uniqid() . '.' .
                        $extension;

                    $destination =
                        $uploadDirectory . $newImageName;

                    if (!move_uploaded_file($tmpName, $destination)) {

                        $_SESSION['error'] =
                            'Unable to upload the new product image.';

                    }

                }
            }
        }


        /* =================================================
           UPDATE DATABASE
           ================================================= */

        if (!isset($_SESSION['error'])) {

            try {

                $updateStmt = $pdo->prepare("
                    UPDATE products
                    SET
                        product_name = ?,
                        category_id = ?,
                        description = ?,
                        price = ?,
                        stock = ?,
                        status = ?,
                        image = ?
                    WHERE id = ?
                ");

                $updateStmt->execute([
                    $product_name,
                    $category_id,
                    $description,
                    $price,
                    $stock,
                    $status,
                    $newImageName,
                    $product_id
                ]);


                /* -------------------------
                   DELETE OLD IMAGE
                   ------------------------- */

                if (
                    $newImageName !== $oldImageName &&
                    !empty($oldImageName)
                ) {

                    $oldImagePath =
                        '../assets/images/products/' .
                        basename($oldImageName);

                    if (
                        file_exists($oldImagePath) &&
                        is_file($oldImagePath)
                    ) {
                        unlink($oldImagePath);
                    }
                }


                $_SESSION['success'] =
                    'Product updated successfully!';

                /*
                 * IMPORTANT:
                 * Redirect to product.php because you
                 * said you do not have products.php.
                 */

                header('Location: product.php');
                exit;


            } catch (PDOException $e) {

                /*
                 * If a new image was uploaded but database
                 * update failed, remove the new image.
                 */

                if (
                    $newImageName !== $oldImageName &&
                    !empty($newImageName)
                ) {

                    $newImagePath =
                        '../assets/images/products/' .
                        basename($newImageName);

                    if (file_exists($newImagePath)) {
                        unlink($newImagePath);
                    }
                }

                $_SESSION['error'] =
                    'Database Error: ' . $e->getMessage();
            }
        }
    }
}


/* =========================================================
   GET SESSION MESSAGES
   ========================================================= */

$error = $_SESSION['error'] ?? '';
$success = $_SESSION['success'] ?? '';

unset($_SESSION['error']);
unset($_SESSION['success']);

?>
<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Edit Product</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"
    >

</head>

<body>

<div class="container py-5">

    <div class="row justify-content-center">

        <div class="col-lg-8">

            <div class="card shadow">

                <div class="card-header bg-dark text-white">

                    <h4 class="mb-0">
                        <i class="fas fa-edit"></i>
                        Edit Product
                    </h4>

                </div>


                <div class="card-body">

                    <!-- ERROR -->

                    <?php if (!empty($error)): ?>

                        <div class="alert alert-danger">
                            <?= htmlspecialchars($error) ?>
                        </div>

                    <?php endif; ?>


                    <!-- SUCCESS -->

                    <?php if (!empty($success)): ?>

                        <div class="alert alert-success">
                            <?= htmlspecialchars($success) ?>
                        </div>

                    <?php endif; ?>


                    <form
                        method="POST"
                        enctype="multipart/form-data"
                    >

                        <!-- PRODUCT NAME -->

                        <div class="mb-3">

                            <label class="form-label">
                                Product Name
                            </label>

                            <input
                                type="text"
                                name="product_name"
                                class="form-control"
                                value="<?= htmlspecialchars($product['product_name'] ?? '') ?>"
                                required
                            >

                        </div>


                        <!-- CATEGORY -->

                        <div class="mb-3">

                            <label class="form-label">
                                Category
                            </label>

                            <select
                                name="category_id"
                                class="form-select"
                                required
                            >

                                <option value="">
                                    Select Category
                                </option>

                                <?php foreach ($categories as $category): ?>

                                    <option
                                        value="<?= (int)$category['id'] ?>"
                                        <?= (
                                            (int)$product['category_id'] ===
                                            (int)$category['id']
                                        ) ? 'selected' : '' ?>
                                    >

                                        <?= htmlspecialchars(
                                            $category['category_name']
                                        ) ?>

                                    </option>

                                <?php endforeach; ?>

                            </select>

                        </div>


                        <!-- DESCRIPTION -->

                        <div class="mb-3">

                            <label class="form-label">
                                Description
                            </label>

                            <textarea
                                name="description"
                                class="form-control"
                                rows="5"
                            ><?= htmlspecialchars($product['description'] ?? '') ?></textarea>

                        </div>


                        <!-- PRICE -->

                        <div class="mb-3">

                            <label class="form-label">
                                Price
                            </label>

                            <input
                                type="number"
                                name="price"
                                class="form-control"
                                step="0.01"
                                min="0"
                                value="<?= htmlspecialchars($product['price'] ?? '') ?>"
                                required
                            >

                        </div>


                        <!-- STOCK -->

                        <div class="mb-3">

                            <label class="form-label">
                                Stock
                            </label>

                            <input
                                type="number"
                                name="stock"
                                class="form-control"
                                min="0"
                                value="<?= htmlspecialchars($product['stock'] ?? '') ?>"
                                required
                            >

                        </div>


                        <!-- STATUS -->

                        <div class="mb-3">

                            <label class="form-label">
                                Status
                            </label>

                            <select
                                name="status"
                                class="form-select"
                            >

                                <option
                                    value="Active"
                                    <?= (
                                        ($product['status'] ?? '') === 'Active'
                                    ) ? 'selected' : '' ?>
                                >
                                    Active
                                </option>

                                <option
                                    value="Inactive"
                                    <?= (
                                        ($product['status'] ?? '') === 'Inactive'
                                    ) ? 'selected' : '' ?>
                                >
                                    Inactive
                                </option>

                            </select>

                        </div>


                        <!-- CURRENT IMAGE -->

                        <div class="mb-3">

                            <label class="form-label">
                                Current Image
                            </label>

                            <br>

                            <?php if (!empty($product['image'])): ?>

                                <img
                                    src="../assets/images/products/<?= htmlspecialchars(basename($product['image'])) ?>"
                                    alt="Product Image"
                                    style="
                                        width:150px;
                                        height:150px;
                                        object-fit:cover;
                                        border-radius:8px;
                                        border:1px solid #ddd;
                                    "
                                >

                            <?php else: ?>

                                <p class="text-muted">
                                    No image available
                                </p>

                            <?php endif; ?>

                        </div>


                        <!-- NEW IMAGE -->

                        <div class="mb-4">

                            <label class="form-label">
                                Change Image
                            </label>

                            <input
                                type="file"
                                name="image"
                                class="form-control"
                                accept=".jpg,.jpeg,.png,.webp"
                            >

                            <small class="text-muted">
                                JPG, JPEG, PNG or WEBP. Maximum 5 MB.
                            </small>

                        </div>


                        <!-- BUTTONS -->

                        <div class="d-flex gap-2">

                            <button
                                type="submit"
                                class="btn btn-success"
                            >
                                <i class="fas fa-save"></i>
                                Update Product
                            </button>


                            <a
                                href="product.php"
                                class="btn btn-secondary"
                            >
                                <i class="fas fa-arrow-left"></i>
                                Back
                            </a>

                        </div>

                    </form>

                </div>

            </div>

        </div>

    </div>

</div>


<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js">
</script>

</body>
</html>
```

