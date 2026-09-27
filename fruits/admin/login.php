<?php
session_start();

include '../includes/db.php';

// If already logged in, go to dashboard
if (isset($_SESSION['admin_id'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = 'Please enter email and password.';
    } else {

        try {
            // Find admin by email
            $stmt = $pdo->prepare("SELECT * FROM admins WHERE email = ? LIMIT 1");
            $stmt->execute([$email]);
            $admin = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($admin) {

                /*
                 * Your database currently contains:
                 * Email: tilawat123@gmail.com
                 * Password: 12345
                 *
                 * This comparison works with the plain-text password.
                 */
                if ($password === $admin['password']) {

                    // Login successful
                    $_SESSION['admin_id'] = $admin['id'];
                    $_SESSION['admin_name'] = $admin['name'];

                    header('Location: dashboard.php');
                    exit;

                } else {
                    $error = 'Invalid email or password.';
                }

            } else {
                $error = 'Invalid email or password.';
            }

        } catch (PDOException $e) {
            $error = 'Database error. Please try again.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Tilawat Dry Fruits</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
        .login-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            padding: 40px;
        }
        .login-logo {
            text-align: center;
            margin-bottom: 30px;
        }
        .login-logo h3 {
            color: #333;
            font-weight: 700;
        }
        .login-logo p {
            color: #666;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="login-card">
                    <div class="login-logo">
                        <i class="fas fa-store fa-3x text-primary"></i>
                        <h3>Admin Panel</h3>
                        <p>Tilawat Dry Fruits Management</p>
                    </div>
                    
                    <?php if($error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">Email Address</label>
                            <input type="email" name="email" class="form-control" required 
                                   value="admin@tilawat.com">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" name="password" class="form-control" required 
                                   value="admin123">
                        </div>
                        <button type="submit" class="btn btn-primary w-100 py-2">
                            <i class="fas fa-sign-in-alt me-2"></i>Login
                        </button>
                    </form>
                    <p class="text-center text-muted mt-3 small">
                        <i class="fas fa-info-circle"></i> Default: admin@tilawat.com / admin123
                    </p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>