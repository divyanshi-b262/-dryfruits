<?php
session_start();

require_once '../includes/db.php';

/* =========================================================
   HANDLE ADD PRODUCT BEFORE ANY HTML OUTPUT
   ========================================================= */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $category_id  = isset($_POST['category_id']) ? (int)$_POST['category_id'] : 0;
    $product_name = trim($_POST['product_name'] ?? '');
    $description  = trim($_POST['description'] ?? '');
    $price        = trim($_POST['price'] ?? '');
    $stock        = isset($_POST['stock']) ? (int)$_POST['stock'] : 0;
    $status       = trim($_POST['status'] ?? 'Active');

    $errors = [];

    // Validation
    if ($category_id <= 0) {
        $errors[] = "Please select a category.";
    }

    if ($product_name === '') {
        $errors[] = "Product name is required.";
    }

    if ($price === '' || !is_numeric($price) || $price < 0) {
        $errors[] = "Please enter a valid price.";
    }

    if ($stock < 0) {
        $errors[] = "Stock cannot be negative.";
    }

    if (!in_array($status, ['Active', 'Inactive'], true)) {
        $status = 'Active';
    }

    /* =====================================================
       IMAGE UPLOAD
       ===================================================== */

    $image_name = '';

    if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {

        if ($_FILES['image']['error'] !== UPLOAD_ERR_OK) {
            $errors[] = "There was an error uploading the image.";
        } else {

            $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            $file_name = $_FILES['image']['name'];
            $file_tmp  = $_FILES['image']['tmp_name'];
            $file_size = $_FILES['image']['size'];

            $extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

            if (!in_array($extension, $allowed_extensions, true)) {
                $errors[] = "Only JPG, JPEG, PNG, GIF and WEBP images are allowed.";
            }

            if ($file_size > 5 * 1024 * 1024) {
                $errors[] = "Image size must be less than 5MB.";
            }

            if (empty($errors)) {

                $upload_dir = '../assets/images/products/';

                // Create directory if it does not exist
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }

                $image_name = time() . '_' . uniqid() . '.' . $extension;

                $upload_path = $upload_dir . $image_name;

                if (!move_uploaded_file($file_tmp, $upload_path)) {
                    $errors[] = "Failed to upload the product image.";
                    $image_name = '';
                }
            }
        }
    }

    /* =====================================================
       INSERT PRODUCT
       ===================================================== */

    if (empty($errors)) {

        try {

            $sql = "INSERT INTO products
                    (category_id, product_name, description, price, stock, image, status)
                    VALUES (?, ?, ?, ?, ?, ?, ?)";

            $stmt = $pdo->prepare($sql);

            $stmt->execute([
                $category_id,
                $product_name,
                $description,
                $price,
                $stock,
                $image_name,
                $status
            ]);

            $_SESSION['success'] = "Product added successfully.";

            // Redirect BEFORE including header.php
            header("Location: add-product.php");
            exit;

        } catch (PDOException $e) {

            // Delete uploaded image if database insert fails
            if (!empty($image_name)) {
                $uploaded_file = '../assets/images/products/' . $image_name;

                if (file_exists($uploaded_file)) {
                    unlink($uploaded_file);
                }
            }

            $errors[] = "Database error: " . $e->getMessage();
        }
    }

    if (!empty($errors)) {
        $_SESSION['errors'] = $errors;

        // Redirect BEFORE including header.php
        header("Location: add-product.php");
        exit;
    }
}


/* =========================================================
   GET CATEGORIES
   ========================================================= */

try {

    $stmt = $pdo->query("
        SELECT id, category_name
        FROM categories
        ORDER BY category_name ASC
    ");

    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {

    $categories = [];
    $_SESSION['errors'] = ["Unable to load categories: " . $e->getMessage()];
}


/* =========================================================
   SUCCESS / ERROR MESSAGES
   ========================================================= */

$success_message = $_SESSION['success'] ?? '';
$errors = $_SESSION['errors'] ?? [];

unset($_SESSION['success']);
unset($_SESSION['errors']);


/* =========================================================
   NOW INCLUDE HEADER
   ========================================================= */

include 'includes/header.php';

?>

<!DOCTYPE html>
<html lang="en">
<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Add Product</title>

    <style>

        body {
            background-color: #f8f4f1;
        }

        .add-product-container {
            max-width: 900px;
            margin: 40px auto;
            padding: 0 15px;
        }

        .product-card {
            background: #ffffff;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
        }

        .product-title {
            color: #7b2d26;
            font-weight: 700;
            margin-bottom: 25px;
        }

        .form-label {
            color: #5c2b24;
            font-weight: 600;
        }

        .form-control,
        .form-select {
            border: 1px solid #d8c2b8;
            border-radius: 8px;
            padding: 10px 12px;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #a0522d;
            box-shadow: 0 0 0 0.2rem rgba(160, 82, 45, 0.15);
        }

        .btn-add-product {
            background-color: #7b2d26;
            border: none;
            color: white;
            padding: 11px 25px;
            border-radius: 8px;
            font-weight: 600;
        }

        .btn-add-product:hover {
            background-color: #5f211c;
            color: white;
        }

        .btn-cancel {
            background-color: #8b6f47;
            color: white;
            border: none;
            padding: 11px 25px;
            border-radius: 8px;
            text-decoration: none;
            margin-left: 8px;
        }

        .btn-cancel:hover {
            background-color: #6f5737;
            color: white;
        }

    </style>

</head>

<body>

<div class="add-product-container">

    <div class="product-card">

        <h2 class="product-title">
            <i class="fas fa-box"></i> Add Product
        </h2>


        <?php if (!empty($success_message)): ?>

            <div class="alert alert-success">
                <?php echo htmlspecialchars($success_message); ?>
            </div>

        <?php endif; ?>


        <?php if (!empty($errors)): ?>

            <div class="alert alert-danger">

                <ul class="mb-0">

                    <?php foreach ($errors as $error): ?>

                        <li>
                            <?php echo htmlspecialchars($error); ?>
                        </li>

                    <?php endforeach; ?>

                </ul>

            </div>

        <?php endif; ?>


        <form method="POST"
              action=""
              enctype="multipart/form-data">

            <div class="row">

                <!-- CATEGORY -->

                <div class="col-md-6 mb-3">

                    <label class="form-label">
                        Category
                    </label>

                    <select name="category_id"
                            class="form-select"
                            required>

                        <option value="">
                            Select Category
                        </option>

                        <?php foreach ($categories as $category): ?>

                            <option value="<?php echo (int)$category['id']; ?>">

                                <?php echo htmlspecialchars($category['category_name']); ?>

                            </option>

                        <?php endforeach; ?>

                    </select>

                </div>


                <!-- PRODUCT NAME -->

                <div class="col-md-6 mb-3">

                    <label class="form-label">
                        Product Name
                    </label>

                    <input type="text"
                           name="product_name"
                           class="form-control"
                           placeholder="Enter product name"
                           required>

                </div>


                <!-- PRICE -->

                <div class="col-md-6 mb-3">

                    <label class="form-label">
                        Price
                    </label>

                    <input type="number"
                           name="price"
                           class="form-control"
                           placeholder="Enter price"
                           min="0"
                           step="0.01"
                           required>

                </div>


                <!-- STOCK -->

                <div class="col-md-6 mb-3">

                    <label class="form-label">
                        Stock
                    </label>

                    <input type="number"
                           name="stock"
                           class="form-control"
                           placeholder="Enter stock quantity"
                           min="0"
                           required>

                </div>


                <!-- STATUS -->

                <div class="col-md-6 mb-3">

                    <label class="form-label">
                        Status
                    </label>

                    <select name="status"
                            class="form-select">

                        <option value="Active">
                            Active
                        </option>

                        <option value="Inactive">
                            Inactive
                        </option>

                    </select>

                </div>


                <!-- IMAGE -->

                <div class="col-md-6 mb-3">

                    <label class="form-label">
                        Product Image
                    </label>

                    <input type="file"
                           name="image"
                           class="form-control"
                           accept=".jpg,.jpeg,.png,.gif,.webp">

                    <small class="text-muted">
                        JPG, JPEG, PNG, GIF or WEBP. Maximum 5MB.
                    </small>

                </div>


                <!-- DESCRIPTION -->

                <div class="col-12 mb-4">

                    <label class="form-label">
                        Description
                    </label>

                    <textarea name="description"
                              class="form-control"
                              rows="5"
                              placeholder="Enter product description"></textarea>

                </div>

            </div>


            <!-- BUTTONS -->

            <button type="submit"
                    class="btn btn-add-product">

                <i class="fas fa-plus"></i>
                Add Product

            </button>


            <a href="products.php"
               class="btn btn-cancel">

                Cancel

            </a>

        </form>

    </div>

</div>

</body>
</html>