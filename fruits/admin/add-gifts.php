<?php
session_start();

require_once '../includes/db.php';

/*
|--------------------------------------------------------------------------
| Messages
|--------------------------------------------------------------------------
*/
$success = '';
$error = '';

/*
|--------------------------------------------------------------------------
| Create upload directory
|--------------------------------------------------------------------------
*/
$uploadDir = '../assets/images/gift-packs/';

if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

/*
|--------------------------------------------------------------------------
| ADD GIFT PACK
|--------------------------------------------------------------------------
*/
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_gift_pack'])) {

    $gift_name   = trim($_POST['gift_name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price       = trim($_POST['price'] ?? '');
    $stock       = trim($_POST['stock'] ?? '');
    $weight      = trim($_POST['weight'] ?? '');
    $status      = $_POST['status'] ?? 'Active';

    if ($gift_name === '') {
        $error = 'Please enter gift pack name.';
    } elseif ($price === '' || !is_numeric($price) || $price < 0) {
        $error = 'Please enter a valid price.';
    } elseif ($stock === '' || !is_numeric($stock) || $stock < 0) {
        $error = 'Please enter a valid stock quantity.';
    } elseif (!in_array($status, ['Active', 'Inactive'], true)) {
        $error = 'Invalid status.';
    }

    /*
    |--------------------------------------------------------------------------
    | Image Upload
    |--------------------------------------------------------------------------
    */
    $imageName = null;

    if ($error === '' && isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {

        if ($_FILES['image']['error'] !== UPLOAD_ERR_OK) {
            $error = 'There was an error uploading the image.';
        } elseif ($_FILES['image']['size'] > 5 * 1024 * 1024) {
            $error = 'Image size must be less than 5 MB.';
        } else {

            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

            $originalName = $_FILES['image']['name'];
            $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

            if (!in_array($extension, $allowedExtensions, true)) {
                $error = 'Only JPG, JPEG, PNG, GIF and WEBP images are allowed.';
            } else {

                $imageName = time() . '_' . uniqid() . '.' . $extension;
                $imagePath = $uploadDir . $imageName;

                if (!move_uploaded_file($_FILES['image']['tmp_name'], $imagePath)) {
                    $error = 'Failed to upload image.';
                    $imageName = null;
                }
            }
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Insert Gift Pack
    |--------------------------------------------------------------------------
    */
    if ($error === '') {

        try {

            $stmt = $pdo->prepare("
                INSERT INTO gift_packs
                (
                    gift_name,
                    description,
                    price,
                    stock,
                    weight,
                    image,
                    status
                )
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");

            $stmt->execute([
                $gift_name,
                $description,
                $price,
                $stock,
                $weight,
                $imageName,
                $status
            ]);

            $success = 'Gift pack added successfully.';

        } catch (PDOException $e) {

            // Remove uploaded image if database insert fails
            if ($imageName !== null) {
                $uploadedFile = $uploadDir . $imageName;

                if (file_exists($uploadedFile)) {
                    unlink($uploadedFile);
                }
            }

            $error = 'Database error: ' . $e->getMessage();
        }
    }
}

/*
|--------------------------------------------------------------------------
| DELETE GIFT PACK
|--------------------------------------------------------------------------
*/
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_gift_pack'])) {

    $gift_id = (int)($_POST['gift_id'] ?? 0);

    if ($gift_id <= 0) {

        $error = 'Invalid gift pack ID.';

    } else {

        try {

            // Get image before deleting
            $stmt = $pdo->prepare("
                SELECT image
                FROM gift_packs
                WHERE id = ?
            ");

            $stmt->execute([$gift_id]);

            $giftPack = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$giftPack) {

                $error = 'Gift pack not found.';

            } else {

                // Delete database record
                $deleteStmt = $pdo->prepare("
                    DELETE FROM gift_packs
                    WHERE id = ?
                ");

                $deleteStmt->execute([$gift_id]);

                // Delete image
                if (!empty($giftPack['image'])) {

                    $imagePath = $uploadDir . basename($giftPack['image']);

                    if (file_exists($imagePath) && is_file($imagePath)) {
                        unlink($imagePath);
                    }
                }

                $success = 'Gift pack deleted successfully.';
            }

        } catch (PDOException $e) {

            $error = 'Database error: ' . $e->getMessage();
        }
    }
}

/*
|--------------------------------------------------------------------------
| FETCH GIFT PACKS
|--------------------------------------------------------------------------
*/
try {

    $stmt = $pdo->query("
        SELECT
            id,
            gift_name,
            description,
            price,
            stock,
            weight,
            image,
            status,
            created_at
        FROM gift_packs
        ORDER BY id DESC
    ");

    $giftPacks = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {

    $giftPacks = [];
    $error = 'Unable to load gift packs: ' . $e->getMessage();
}

/*
|--------------------------------------------------------------------------
| NOW INCLUDE HEADER
|--------------------------------------------------------------------------
| Header is included AFTER POST processing so redirects/headers are not
| affected by output from the header file.
|--------------------------------------------------------------------------
*/
include 'includes/header.php';
?>

<style>
    body {
        background: #fffaf5;
    }

    .gift-page {
        padding: 30px 0 50px;
    }

    .gift-title {
        color: #7b2d26;
        font-weight: 700;
        margin-bottom: 5px;
    }

    .gift-subtitle {
        color: #8b6f47;
        margin-bottom: 25px;
    }

    .gift-card {
        background: #ffffff;
        border: 1px solid #ead8c8;
        border-radius: 14px;
        box-shadow: 0 4px 15px rgba(123, 45, 38, 0.08);
        padding: 25px;
        margin-bottom: 30px;
    }

    .gift-card h4 {
        color: #7b2d26;
        font-weight: 700;
        margin-bottom: 20px;
    }

    .form-label {
        color: #7b2d26;
        font-weight: 600;
    }

    .form-control,
    .form-select {
        border: 1px solid #d9c1ad;
        border-radius: 8px;
    }

    .form-control:focus,
    .form-select:focus {
        border-color: #a0522d;
        box-shadow: 0 0 0 0.2rem rgba(160, 82, 45, 0.15);
    }

    .btn-gift {
        background: #7b2d26;
        color: #fff;
        border: none;
        padding: 10px 22px;
        border-radius: 8px;
    }

    .btn-gift:hover {
        background: #a0522d;
        color: #fff;
    }

    .gift-table-card {
        background: #fff;
        border: 1px solid #ead8c8;
        border-radius: 14px;
        padding: 20px;
        box-shadow: 0 4px 15px rgba(123, 45, 38, 0.08);
    }

    .table thead th {
        background: #7b2d26;
        color: #fff;
        border: none;
        vertical-align: middle;
    }

    .table td {
        vertical-align: middle;
    }

    .gift-image {
        width: 75px;
        height: 75px;
        object-fit: cover;
        border-radius: 10px;
        border: 1px solid #ead8c8;
    }

    .no-image {
        width: 75px;
        height: 75px;
        border-radius: 10px;
        background: #f8eee6;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #8b6f47;
        font-size: 12px;
    }

    .status-active {
        background: #e7f6e7;
        color: #287a28;
        padding: 5px 10px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
    }

    .status-inactive {
        background: #f8e5e2;
        color: #9b2d20;
        padding: 5px 10px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
    }

    .btn-edit {
        background: #a0522d;
        color: #fff;
        border: none;
    }

    .btn-edit:hover {
        background: #7b2d26;
        color: #fff;
    }

    .btn-delete {
        background: #b02a37;
        color: #fff;
        border: none;
    }

    .btn-delete:hover {
        background: #8f1f2a;
        color: #fff;
    }

    @media (max-width: 768px) {

        .gift-page {
            padding: 20px 10px 40px;
        }

        .gift-card,
        .gift-table-card {
            padding: 15px;
        }

        .table-responsive {
            overflow-x: auto;
        }
    }
</style>

<div class="container gift-page">

    <h2 class="gift-title">
        <i class="fas fa-gift me-2"></i>
        Gift Packs
    </h2>

    <p class="gift-subtitle">
        Add and manage your gift packs separately from normal products.
    </p>

    <?php if ($success !== ''): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?= htmlspecialchars($success) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if ($error !== ''): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?= htmlspecialchars($error) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- =========================================================
         ADD GIFT PACK
    ========================================================== -->

    <div class="gift-card">

        <h4>
            <i class="fas fa-plus-circle me-2"></i>
            Add New Gift Pack
        </h4>

        <form method="POST" enctype="multipart/form-data">

            <div class="row">

                <div class="col-md-6 mb-3">

                    <label class="form-label">
                        Gift Pack Name
                    </label>

                    <input
                        type="text"
                        name="gift_name"
                        class="form-control"
                        placeholder="Enter gift pack name"
                        required
                    >

                </div>

                <div class="col-md-3 mb-3">

                    <label class="form-label">
                        Price
                    </label>

                    <input
                        type="number"
                        name="price"
                        class="form-control"
                        step="0.01"
                        min="0"
                        placeholder="Enter price"
                        required
                    >

                </div>

                <div class="col-md-3 mb-3">

                    <label class="form-label">
                        Stock
                    </label>

                    <input
                        type="number"
                        name="stock"
                        class="form-control"
                        min="0"
                        placeholder="Enter stock"
                        required
                    >

                </div>

                <div class="col-md-4 mb-3">

                    <label class="form-label">
                        Weight
                    </label>

                    <input
                        type="text"
                        name="weight"
                        class="form-control"
                        placeholder="Example: 500g / 1kg"
                    >

                </div>

                <div class="col-md-4 mb-3">

                    <label class="form-label">
                        Status
                    </label>

                    <select name="status" class="form-select">

                        <option value="Active">
                            Active
                        </option>

                        <option value="Inactive">
                            Inactive
                        </option>

                    </select>

                </div>

                <div class="col-md-4 mb-3">

                    <label class="form-label">
                        Gift Pack Image
                    </label>

                    <input
                        type="file"
                        name="image"
                        class="form-control"
                        accept=".jpg,.jpeg,.png,.gif,.webp"
                    >

                </div>

                <div class="col-12 mb-3">

                    <label class="form-label">
                        Description
                    </label>

                    <textarea
                        name="description"
                        class="form-control"
                        rows="4"
                        placeholder="Enter gift pack description"
                    ></textarea>

                </div>

            </div>

            <button
                type="submit"
                name="add_gift_pack"
                class="btn btn-gift"
            >
                <i class="fas fa-plus me-1"></i>
                Add Gift Pack
            </button>

        </form>

    </div>


    <!-- =========================================================
         GIFT PACK LIST
    ========================================================== -->

    <div class="gift-table-card">

        <h4 class="gift-title mb-3">
            <i class="fas fa-box-open me-2"></i>
            All Gift Packs
        </h4>

        <?php if (empty($giftPacks)): ?>

            <div class="alert alert-info">
                No gift packs have been added yet.
            </div>

        <?php else: ?>

            <div class="table-responsive">

                <table class="table table-bordered table-hover align-middle">

                    <thead>

                        <tr>
                            <th>#</th>
                            <th>Image</th>
                            <th>Gift Name</th>
                            <th>Weight</th>
                            <th>Price</th>
                            <th>Stock</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>

                    </thead>

                    <tbody>

                    <?php foreach ($giftPacks as $gift): ?>

                        <tr>

                            <td>
                                <?= (int)$gift['id'] ?>
                            </td>

                            <td>

                                <?php if (!empty($gift['image'])): ?>

                                    <img
                                        src="../assets/images/gift-packs/<?= htmlspecialchars(basename($gift['image'])) ?>"
                                        class="gift-image"
                                        alt="<?= htmlspecialchars($gift['gift_name']) ?>"
                                    >

                                <?php else: ?>

                                    <div class="no-image">
                                        No Image
                                    </div>

                                <?php endif; ?>

                            </td>

                            <td>

                                <strong>
                                    <?= htmlspecialchars($gift['gift_name']) ?>
                                </strong>

                                <?php if (!empty($gift['description'])): ?>

                                    <div class="small text-muted mt-1">
                                        <?= htmlspecialchars($gift['description']) ?>
                                    </div>

                                <?php endif; ?>

                            </td>

                            <td>
                                <?= htmlspecialchars($gift['weight'] ?? '') ?>
                            </td>

                            <td>
                                ₹<?= number_format((float)$gift['price'], 2) ?>
                            </td>

                            <td>
                                <?= (int)$gift['stock'] ?>
                            </td>

                            <td>

                                <?php if ($gift['status'] === 'Active'): ?>

                                    <span class="status-active">
                                        Active
                                    </span>

                                <?php else: ?>

                                    <span class="status-inactive">
                                        Inactive
                                    </span>

                                <?php endif; ?>

                            </td>

                            <td>

                                <div class="d-flex gap-1">

                                    <a
                                        href="edit-gift-pack.php?id=<?= (int)$gift['id'] ?>"
                                        class="btn btn-edit btn-sm"
                                    >
                                        <i class="fas fa-edit"></i>
                                        Edit
                                    </a>

                                    <form
                                        method="POST"
                                        onsubmit="return confirm('Are you sure you want to delete this gift pack?');"
                                    >

                                        <input
                                            type="hidden"
                                            name="gift_id"
                                            value="<?= (int)$gift['id'] ?>"
                                        >

                                        <button
                                            type="submit"
                                            name="delete_gift_pack"
                                            class="btn btn-delete btn-sm"
                                        >
                                            <i class="fas fa-trash"></i>
                                            Delete
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

<?php include 'includes/footer.php'; ?>