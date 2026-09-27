<?php
session_start();

require_once '../includes/db.php';

// =====================================================
// ADD CATEGORY
// =====================================================

if (isset($_POST['add_category'])) {

    $category_name = trim($_POST['category_name']);

    if ($category_name == '') {

        $_SESSION['error'] = "Category name is required.";
        header("Location: categories.php");
        exit;
    }

    // Check duplicate category
    $check = $pdo->prepare("
        SELECT id 
        FROM categories 
        WHERE category_name = ?
    ");

    $check->execute([$category_name]);

    if ($check->fetch()) {

        $_SESSION['error'] = "This category already exists.";
        header("Location: categories.php");
        exit;
    }

    $category_image = '';

    // Upload image
    if (
        isset($_FILES['category_image']) &&
        $_FILES['category_image']['error'] === UPLOAD_ERR_OK
    ) {

        $upload_dir = "../assets/images/categories/";

        // Create folder if missing
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $extension = strtolower(
            pathinfo(
                $_FILES['category_image']['name'],
                PATHINFO_EXTENSION
            )
        );

        $allowed = ['jpg', 'jpeg', 'png', 'webp'];

        if (!in_array($extension, $allowed)) {

            $_SESSION['error'] =
                "Only JPG, JPEG, PNG and WEBP images are allowed.";

            header("Location: categories.php");
            exit;
        }

        $category_image =
            time() . "_" . uniqid() . "." . $extension;

        move_uploaded_file(
            $_FILES['category_image']['tmp_name'],
            $upload_dir . $category_image
        );
    }

    // Insert category
    $stmt = $pdo->prepare("
        INSERT INTO categories
        (category_name, category_image)
        VALUES (?, ?)
    ");

    $stmt->execute([
        $category_name,
        $category_image
    ]);

    $_SESSION['success'] =
        "Category added successfully.";

    header("Location: categories.php");
    exit;
}


// =====================================================
// UPDATE CATEGORY
// =====================================================

if (isset($_POST['update_category'])) {

    $id = intval($_POST['category_id']);

    $category_name =
        trim($_POST['category_name']);

    $old_image =
        $_POST['old_image'] ?? '';

    if ($category_name == '') {

        $_SESSION['error'] =
            "Category name is required.";

        header("Location: categories.php");
        exit;
    }

    // Check duplicate category
    $check = $pdo->prepare("
        SELECT id
        FROM categories
        WHERE category_name = ?
        AND id != ?
    ");

    $check->execute([
        $category_name,
        $id
    ]);

    if ($check->fetch()) {

        $_SESSION['error'] =
            "Another category with this name already exists.";

        header("Location: categories.php");
        exit;
    }

    $category_image = $old_image;

    // New image
    if (
        isset($_FILES['category_image']) &&
        $_FILES['category_image']['error'] === UPLOAD_ERR_OK
    ) {

        $upload_dir =
            "../assets/images/categories/";

        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $extension = strtolower(
            pathinfo(
                $_FILES['category_image']['name'],
                PATHINFO_EXTENSION
            )
        );

        $allowed = [
            'jpg',
            'jpeg',
            'png',
            'webp'
        ];

        if (!in_array($extension, $allowed)) {

            $_SESSION['error'] =
                "Only JPG, JPEG, PNG and WEBP images are allowed.";

            header("Location: categories.php");
            exit;
        }

        $new_image =
            time() . "_" . uniqid() . "." . $extension;

        if (
            move_uploaded_file(
                $_FILES['category_image']['tmp_name'],
                $upload_dir . $new_image
            )
        ) {

            // Delete old image
            if (!empty($old_image)) {

                $old_path =
                    $upload_dir . $old_image;

                if (file_exists($old_path)) {
                    unlink($old_path);
                }
            }

            $category_image = $new_image;
        }
    }

    // Update database
    $stmt = $pdo->prepare("
        UPDATE categories
        SET
            category_name = ?,
            category_image = ?
        WHERE id = ?
    ");

    $stmt->execute([
        $category_name,
        $category_image,
        $id
    ]);

    $_SESSION['success'] =
        "Category updated successfully.";

    header("Location: categories.php");
    exit;
}


// =====================================================
// DELETE CATEGORY
// =====================================================

if (isset($_GET['delete'])) {

    $id = intval($_GET['delete']);

    // Get category
    $stmt = $pdo->prepare("
        SELECT *
        FROM categories
        WHERE id = ?
    ");

    $stmt->execute([$id]);

    $category = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$category) {

        $_SESSION['error'] =
            "Category not found.";

        header("Location: categories.php");
        exit;
    }


    // Check products
    $stmt = $pdo->prepare("
        SELECT COUNT(*)
        FROM products
        WHERE category_id = ?
    ");

    $stmt->execute([$id]);

    $product_count =
        $stmt->fetchColumn();


    if ($product_count > 0) {

        $_SESSION['error'] =
            "This category cannot be deleted because products are using it.";

        header("Location: categories.php");
        exit;
    }


    // Delete image
    if (!empty($category['category_image'])) {

        $image_path =
            "../assets/images/categories/" .
            $category['category_image'];

        if (file_exists($image_path)) {
            unlink($image_path);
        }
    }


    // Delete category
    $stmt = $pdo->prepare("
        DELETE FROM categories
        WHERE id = ?
    ");

    $stmt->execute([$id]);

    $_SESSION['success'] =
        "Category deleted successfully.";

    header("Location: categories.php");
    exit;
}


// =====================================================
// FETCH CATEGORIES
// =====================================================

$stmt = $pdo->query("
    SELECT *
    FROM categories
    ORDER BY id DESC
");

$categories =
    $stmt->fetchAll(PDO::FETCH_ASSOC);


// =====================================================
// ADMIN HEADER
// =====================================================

include 'includes/header.php';

?>


<div class="container-fluid py-4">


    <!-- PAGE HEADER -->

    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>

            <h3 class="fw-bold mb-1">
                Categories
            </h3>

            <p class="text-muted mb-0">
                Manage your dry fruit categories
            </p>

        </div>


        <button
            class="btn btn-success"
            data-bs-toggle="modal"
            data-bs-target="#addCategoryModal">

            <i class="bi bi-plus-circle"></i>

            Add Category

        </button>

    </div>


    <!-- SUCCESS MESSAGE -->

    <?php if (isset($_SESSION['success'])): ?>

        <div class="alert alert-success alert-dismissible fade show">

            <i class="bi bi-check-circle"></i>

            <?php

            echo htmlspecialchars(
                $_SESSION['success']
            );

            unset($_SESSION['success']);

            ?>

            <button
                type="button"
                class="btn-close"
                data-bs-dismiss="alert">
            </button>

        </div>

    <?php endif; ?>


    <!-- ERROR MESSAGE -->

    <?php if (isset($_SESSION['error'])): ?>

        <div class="alert alert-danger alert-dismissible fade show">

            <i class="bi bi-exclamation-circle"></i>

            <?php

            echo htmlspecialchars(
                $_SESSION['error']
            );

            unset($_SESSION['error']);

            ?>

            <button
                type="button"
                class="btn-close"
                data-bs-dismiss="alert">
            </button>

        </div>

    <?php endif; ?>


    <!-- CATEGORY CARDS -->

    <div class="row g-4">

        <?php if (!empty($categories)): ?>

            <?php foreach ($categories as $category): ?>

                <div class="col-xl-3 col-lg-4 col-md-6">

                    <div class="card border-0 shadow-sm h-100">


                        <!-- IMAGE -->

                        <?php if (!empty($category['category_image'])): ?>

                            <img
                                src="../assets/images/categories/<?php
                                echo htmlspecialchars(
                                    $category['category_image']
                                );
                                ?>"
                                class="card-img-top"
                                style="
                                    height:200px;
                                    object-fit:cover;
                                "
                                alt="<?php
                                echo htmlspecialchars(
                                    $category['category_name']
                                );
                                ?>"
                            >

                        <?php else: ?>

                            <div
                                class="bg-light d-flex align-items-center justify-content-center"
                                style="height:200px;">

                                <i
                                    class="bi bi-image text-secondary"
                                    style="font-size:60px;">
                                </i>

                            </div>

                        <?php endif; ?>


                        <!-- CONTENT -->

                        <div class="card-body">

                            <h5 class="fw-bold">

                                <?php

                                echo htmlspecialchars(
                                    $category['category_name']
                                );

                                ?>

                            </h5>


                            <p class="text-muted small">

                                Category ID:
                                #<?php
                                echo $category['id'];
                                ?>

                            </p>


                            <div class="d-flex gap-2">


                                <!-- EDIT -->

                                <button
                                    class="btn btn-primary btn-sm"
                                    data-bs-toggle="modal"
                                    data-bs-target="#editCategory<?php
                                    echo $category['id'];
                                    ?>">

                                    <i class="bi bi-pencil"></i>

                                    Edit

                                </button>


                                <!-- DELETE -->

                                <a
                                    href="categories.php?delete=<?php
                                    echo $category['id'];
                                    ?>"
                                    class="btn btn-danger btn-sm"
                                    onclick="
                                    return confirm(
                                        'Are you sure you want to delete this category?'
                                    );
                                    "
                                >

                                    <i class="bi bi-trash"></i>

                                    Delete

                                </a>

                            </div>

                        </div>

                    </div>

                </div>


                <!-- =================================================
                     EDIT CATEGORY MODAL
                ================================================== -->

                <div
                    class="modal fade"
                    id="editCategory<?php
                    echo $category['id'];
                    ?>"
                    tabindex="-1"
                >

                    <div class="modal-dialog">

                        <div class="modal-content">


                            <form
                                method="POST"
                                enctype="multipart/form-data"
                            >


                                <div class="modal-header">

                                    <h5 class="modal-title">

                                        Edit Category

                                    </h5>

                                    <button
                                        type="button"
                                        class="btn-close"
                                        data-bs-dismiss="modal">
                                    </button>

                                </div>


                                <div class="modal-body">


                                    <input
                                        type="hidden"
                                        name="category_id"
                                        value="<?php
                                        echo $category['id'];
                                        ?>"
                                    >


                                    <input
                                        type="hidden"
                                        name="old_image"
                                        value="<?php
                                        echo htmlspecialchars(
                                            $category['category_image']
                                        );
                                        ?>"
                                    >


                                    <div class="mb-3">

                                        <label class="form-label">
                                            Category Name
                                        </label>

                                        <input
                                            type="text"
                                            name="category_name"
                                            class="form-control"
                                            value="<?php
                                            echo htmlspecialchars(
                                                $category['category_name']
                                            );
                                            ?>"
                                            required
                                        >

                                    </div>


                                    <div class="mb-3">

                                        <label class="form-label">
                                            Change Image
                                        </label>

                                        <input
                                            type="file"
                                            name="category_image"
                                            class="form-control"
                                            accept=".jpg,.jpeg,.png,.webp"
                                        >

                                    </div>


                                </div>


                                <div class="modal-footer">

                                    <button
                                        type="button"
                                        class="btn btn-secondary"
                                        data-bs-dismiss="modal"
                                    >
                                        Cancel
                                    </button>

                                    <button
                                        type="submit"
                                        name="update_category"
                                        class="btn btn-success"
                                    >

                                        Update Category

                                    </button>

                                </div>


                            </form>

                        </div>

                    </div>

                </div>


            <?php endforeach; ?>

        <?php else: ?>


            <!-- NO CATEGORIES -->

            <div class="col-12">

                <div class="text-center py-5">

                    <i
                        class="bi bi-folder2-open"
                        style="
                            font-size:60px;
                            color:#c49a35;
                        "
                    ></i>

                    <h5 class="mt-3">
                        No Categories Found
                    </h5>

                    <p class="text-muted">
                        Add your first dry fruit category.
                    </p>

                </div>

            </div>


        <?php endif; ?>

    </div>

</div>



<!-- =====================================================
     ADD CATEGORY MODAL
====================================================== -->

<div
    class="modal fade"
    id="addCategoryModal"
    tabindex="-1"
>

    <div class="modal-dialog">

        <div class="modal-content">


            <form
                method="POST"
                enctype="multipart/form-data"
            >


                <div class="modal-header">

                    <h5 class="modal-title">

                        <i class="bi bi-folder-plus"></i>

                        Add New Category

                    </h5>

                    <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="modal">
                    </button>

                </div>


                <div class="modal-body">


                    <div class="mb-3">

                        <label class="form-label fw-semibold">

                            Category Name

                        </label>

                        <input
                            type="text"
                            name="category_name"
                            class="form-control"
                            placeholder="Example: Almonds"
                            required
                        >

                    </div>


                    <div class="mb-3">

                        <label class="form-label fw-semibold">

                            Category Image

                        </label>

                        <input
                            type="file"
                            name="category_image"
                            class="form-control"
                            accept=".jpg,.jpeg,.png,.webp"
                        >

                        <small class="text-muted">

                            Allowed: JPG, JPEG, PNG, WEBP

                        </small>

                    </div>


                </div>


                <div class="modal-footer">

                    <button
                        type="button"
                        class="btn btn-secondary"
                        data-bs-dismiss="modal"
                    >

                        Cancel

                    </button>


                    <button
                        type="submit"
                        name="add_category"
                        class="btn btn-success"
                    >

                        <i class="bi bi-plus-circle"></i>

                        Add Category

                    </button>

                </div>


            </form>

        </div>

    </div>

</div>


<?php

include 'includes/footer.php';

?>