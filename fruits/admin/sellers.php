<?php
session_start();
include 'includes/header.php';
include '../includes/db.php';

// Approve seller
if(isset($_GET['approve'])) {
    $stmt = $pdo->prepare("UPDATE sellers SET status = 'Approved' WHERE id = ?");
    $stmt->execute([$_GET['approve']]);
    $_SESSION['success'] = 'Seller approved successfully!';
    header('Location: sellers.php');
    exit;
}

// Reject seller
if(isset($_GET['reject'])) {
    $stmt = $pdo->prepare("UPDATE sellers SET status = 'Rejected' WHERE id = ?");
    $stmt->execute([$_GET['reject']]);
    $_SESSION['success'] = 'Seller rejected!';
    header('Location: sellers.php');
    exit;
}

// Delete seller
if(isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM sellers WHERE id = ?");
    $stmt->execute([$_GET['delete']]);
    $_SESSION['success'] = 'Seller deleted successfully!';
    header('Location: sellers.php');
    exit;
}

// Get all sellers
$sellers = $pdo->query("SELECT * FROM sellers ORDER BY created_at DESC")->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold">Manage Sellers</h4>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Shop Name</th>
                        <th>Owner</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($sellers as $seller): ?>
                        <tr>
                            <td>#<?php echo $seller['id']; ?></td>
                            <td><?php echo $seller['shop_name']; ?></td>
                            <td><?php echo $seller['owner_name']; ?></td>
                            <td><?php echo $seller['email']; ?></td>
                            <td><?php echo $seller['phone']; ?></td>
                            <td>
                                <span class="badge bg-<?php echo $seller['status'] == 'Approved' ? 'success' : ($seller['status'] == 'Pending' ? 'warning' : 'danger'); ?>">
                                    <?php echo $seller['status']; ?>
                                </span>
                            </td>
                            <td>
                                <?php if($seller['status'] == 'Pending'): ?>
                                    <a href="sellers.php?approve=<?php echo $seller['id']; ?>" class="btn btn-success btn-sm">
                                        <i class="fas fa-check"></i>
                                    </a>
                                    <a href="sellers.php?reject=<?php echo $seller['id']; ?>" class="btn btn-danger btn-sm">
                                        <i class="fas fa-times"></i>
                                    </a>
                                <?php endif; ?>
                                <a href="sellers.php?delete=<?php echo $seller['id']; ?>" class="btn btn-dark btn-sm delete-btn">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>