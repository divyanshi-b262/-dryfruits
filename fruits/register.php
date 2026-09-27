```php
<?php
session_start();
include 'includes/db.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $password_input = $_POST['password'] ?? '';

    // Validation
    if (
        empty($full_name) ||
        empty($email) ||
        empty($phone) ||
        empty($address) ||
        empty($password_input)
    ) {
        $error = "Please fill all the fields.";
    }
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    }
    elseif (strlen($password_input) < 6) {
        $error = "Password must be at least 6 characters.";
    }
    else {

        try {

            // Check existing email
            $check = $pdo->prepare(
                "SELECT id FROM customers WHERE email = ? LIMIT 1"
            );
            $check->execute([$email]);

            if ($check->fetch()) {

                $error = "This email is already registered.";

            } else {

                // Hash password
                $password = password_hash(
                    $password_input,
                    PASSWORD_DEFAULT
                );

                // Insert customer
                $stmt = $pdo->prepare("
                    INSERT INTO customers
                    (full_name, email, phone, address, password)
                    VALUES (?, ?, ?, ?, ?)
                ");

                $stmt->execute([
                    $full_name,
                    $email,
                    $phone,
                    $address,
                    $password
                ]);

                // Redirect to login
                header("Location: login.php?registered=1");
                exit;
            }

        } catch (PDOException $e) {

            $error = "Registration failed: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Register - Tilawat Dry Fruits</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <link rel="stylesheet"
          href="assets/css/style.css">

</head>

<body class="bg-light">

<div class="container">

    <div class="row justify-content-center
                align-items-center min-vh-100">

        <div class="col-md-5">

            <div class="card shadow">

                <div class="card-body p-4">

                    <h3 class="text-center mb-2">
                        Create Account
                    </h3>

                    <p class="text-center text-muted mb-4">
                        Register for Tilawat Dry Fruits
                    </p>

                    <?php if (!empty($error)): ?>

                        <div class="alert alert-danger">
                            <?php echo htmlspecialchars($error); ?>
                        </div>

                    <?php endif; ?>


                    <form method="POST"
                          action="register.php">

                        <!-- Full Name -->
                        <div class="mb-3">

                            <label class="form-label">
                                Full Name
                            </label>

                            <input
                                type="text"
                                name="full_name"
                                class="form-control"
                                value="<?php echo htmlspecialchars($_POST['full_name'] ?? ''); ?>"
                                required
                            >

                        </div>


                        <!-- Email -->
                        <div class="mb-3">

                            <label class="form-label">
                                Email
                            </label>

                            <input
                                type="email"
                                name="email"
                                class="form-control"
                                value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                                required
                            >

                        </div>


                        <!-- Phone -->
                        <div class="mb-3">

                            <label class="form-label">
                                Phone
                            </label>

                            <input
                                type="text"
                                name="phone"
                                class="form-control"
                                value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>"
                                required
                            >

                        </div>


                        <!-- Address -->
                        <div class="mb-3">

                            <label class="form-label">
                                Address
                            </label>

                            <textarea
                                name="address"
                                class="form-control"
                                rows="3"
                                required
                            ><?php echo htmlspecialchars($_POST['address'] ?? ''); ?></textarea>

                        </div>


                        <!-- Password -->
                        <div class="mb-3">

                            <label class="form-label">
                                Password
                            </label>

                            <input
                                type="password"
                                name="password"
                                class="form-control"
                                minlength="6"
                                required
                            >

                        </div>


                        <!-- Register Button -->
                        <button
                            type="submit"
                            class="btn btn-warning w-100"
                        >
                            Register
                        </button>

                    </form>


                    <p class="text-center mt-3">

                        Already have an account?

                        <a href="login.php">
                            Login
                        </a>

                    </p>

                </div>

            </div>

        </div>

    </div>

</div>

</body>
</html>
```
