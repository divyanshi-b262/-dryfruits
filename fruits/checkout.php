<?php

session_start();

include 'includes/db.php';

/* =====================================================
   LOGIN CHECK
===================================================== */

if (!isset($_SESSION['customer_id'])) {
    header('Location: login.php');
    exit;
}

$customer_id = (int)$_SESSION['customer_id'];

$error = '';

/* =====================================================
   GET CUSTOMER
===================================================== */

$stmt = $pdo->prepare("
    SELECT
        id,
        full_name,
        email,
        phone,
        address
    FROM customers
    WHERE id = ?
    LIMIT 1
");

$stmt->execute([$customer_id]);

$customer = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$customer) {
    header('Location: login.php');
    exit;
}


/* =====================================================
   GET CART
===================================================== */

$stmt = $pdo->prepare("
    SELECT
        c.id AS cart_id,
        c.product_id,
        c.quantity,

        p.product_name,
        p.description,
        p.price,
        p.stock,
        p.image,
        p.status

    FROM cart c

    INNER JOIN products p
        ON c.product_id = p.id

    WHERE c.customer_id = ?

    ORDER BY c.id DESC
");

$stmt->execute([$customer_id]);

$cartItems = $stmt->fetchAll(PDO::FETCH_ASSOC);


/* =====================================================
   EMPTY CART
===================================================== */

if (empty($cartItems)) {

    header('Location: cart.php');
    exit;
}


/* =====================================================
   CALCULATE TOTAL
===================================================== */

$grandTotal = 0;
$totalItems = 0;

foreach ($cartItems as $item) {

    $quantity = (int)$item['quantity'];
    $price = (float)$item['price'];

    $grandTotal += $price * $quantity;
    $totalItems += $quantity;
}


/* =====================================================
   PLACE ORDER
===================================================== */

if (
    $_SERVER['REQUEST_METHOD'] === 'POST'
    &&
    isset($_POST['place_order'])
) {

    $full_name = trim($_POST['full_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');

    /* -----------------------------
       VALIDATION
    ----------------------------- */

    if ($full_name === '') {

        $error = 'Please enter your full name.';

    } elseif ($phone === '') {

        $error = 'Please enter your phone number.';

    } elseif ($address === '') {

        $error = 'Please enter your delivery address.';

    }


    /* =================================================
       CREATE ORDER
    ================================================= */

    if ($error === '') {

        try {

            $pdo->beginTransaction();


            /* -----------------------------------------
               GET CART AGAIN
            ----------------------------------------- */

            $stmt = $pdo->prepare("
                SELECT
                    c.id AS cart_id,
                    c.product_id,
                    c.quantity,

                    p.product_name,
                    p.price,
                    p.stock,
                    p.status

                FROM cart c

                INNER JOIN products p
                    ON c.product_id = p.id

                WHERE c.customer_id = ?

                FOR UPDATE
            ");

            $stmt->execute([$customer_id]);

            $orderItems = $stmt->fetchAll(PDO::FETCH_ASSOC);


            if (empty($orderItems)) {

                throw new Exception(
                    'Your cart is empty.'
                );
            }


            /* -----------------------------------------
               CHECK STOCK AGAIN
            ----------------------------------------- */

            $totalAmount = 0;

            foreach ($orderItems as $item) {

                $quantity = (int)$item['quantity'];
                $stock = (int)$item['stock'];
                $price = (float)$item['price'];


                if ($item['status'] !== 'Active') {

                    throw new Exception(
                        $item['product_name'] .
                        ' is no longer available.'
                    );
                }


                if ($quantity <= 0) {

                    throw new Exception(
                        'Invalid quantity for ' .
                        $item['product_name']
                    );
                }


                if ($quantity > $stock) {

                    throw new Exception(
                        'Only ' .
                        $stock .
                        ' item(s) of ' .
                        $item['product_name'] .
                        ' are available.'
                    );
                }


                $totalAmount +=
                    $price * $quantity;
            }


            /* -----------------------------------------
               UPDATE CUSTOMER DETAILS
            ----------------------------------------- */

            $stmt = $pdo->prepare("
                UPDATE customers
                SET
                    full_name = ?,
                    phone = ?,
                    address = ?
                WHERE id = ?
            ");

            $stmt->execute([
                $full_name,
                $phone,
                $address,
                $customer_id
            ]);


            /* -----------------------------------------
               CREATE ORDER
            ----------------------------------------- */

            $stmt = $pdo->prepare("
                INSERT INTO orders
                (
                    customer_id,
                    total_amount,
                    payment_method,
                    payment_status,
                    order_status
                )
                VALUES
                (
                    ?,
                    ?,
                    ?,
                    ?,
                    ?
                )
            ");

            $stmt->execute([
                $customer_id,
                $totalAmount,
                'Cash on Delivery',
                'Pending',
                'Placed'
            ]);


            $order_id = $pdo->lastInsertId();


            /* -----------------------------------------
               INSERT ORDER ITEMS
            ----------------------------------------- */

            $insertItem = $pdo->prepare("
                INSERT INTO order_items
                (
                    order_id,
                    product_id,
                    quantity,
                    price
                )
                VALUES
                (
                    ?,
                    ?,
                    ?,
                    ?
                )
            ");


            /* -----------------------------------------
               UPDATE STOCK
            ----------------------------------------- */

            $updateStock = $pdo->prepare("
                UPDATE products
                SET stock = stock - ?
                WHERE id = ?
            ");


            foreach ($orderItems as $item) {

                $insertItem->execute([
                    $order_id,
                    $item['product_id'],
                    $item['quantity'],
                    $item['price']
                ]);


                $updateStock->execute([
                    $item['quantity'],
                    $item['product_id']
                ]);
            }


            /* -----------------------------------------
               CLEAR CART
            ----------------------------------------- */

            $stmt = $pdo->prepare("
                DELETE FROM cart
                WHERE customer_id = ?
            ");

            $stmt->execute([
                $customer_id
            ]);


            /* -----------------------------------------
               FINISH TRANSACTION
            ----------------------------------------- */

            $pdo->commit();


            /* -----------------------------------------
               SAVE ORDER ID
            ----------------------------------------- */

            $_SESSION['last_order_id'] = $order_id;


            /* -----------------------------------------
               GO TO SUCCESS PAGE
            ----------------------------------------- */

            header('Location: order-success.php');
            exit;

        } catch (Exception $e) {

            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            $error = $e->getMessage();
        }
    }
}


include 'includes/header.php';

?>

<style>

.checkout-page {
    background:#fffaf7;
    min-height:75vh;
    padding:100px 0 60px;
}

.checkout-title {
    color:#7b2d26;
    font-weight:700;
    margin-bottom:30px;
}

.checkout-card {
    background:white;
    border:none;
    border-radius:12px;
    padding:25px;
    margin-bottom:25px;
    box-shadow:0 4px 18px rgba(0,0,0,0.08);
}

.checkout-card h4 {
    color:#7b2d26;
    font-weight:700;
    margin-bottom:20px;
}

.form-label {
    font-weight:600;
    color:#4d2924;
}

.form-control {
    border:1px solid #d8b8ad;
    border-radius:7px;
    padding:11px;
}

.form-control:focus {
    border-color:#7b2d26;
    box-shadow:0 0 0 0.2rem rgba(123,45,38,.15);
}

.product-row {
    display:flex;
    align-items:center;
    gap:15px;
    padding:15px 0;
    border-bottom:1px solid #eee;
}

.product-row:last-child {
    border-bottom:none;
}

.product-row img {
    width:70px;
    height:70px;
    object-fit:cover;
    border-radius:8px;
}

.product-name {
    font-weight:600;
    color:#4d2924;
}

.product-price {
    color:#7b2d26;
    font-weight:600;
}

.payment-box {
    border:2px solid #7b2d26;
    background:#fff8f5;
    border-radius:9px;
    padding:17px;
}

.payment-box label {
    font-weight:600;
    color:#4d2924;
}

.total-box {
    border-top:2px solid #ead8d2;
    padding-top:15px;
    margin-top:15px;

    display:flex;
    justify-content:space-between;

    color:#7b2d26;
    font-size:21px;
    font-weight:700;
}

.place-order-btn {
    width:100%;
    border:none;
    background:#7b2d26;
    color:white;
    padding:13px;
    border-radius:7px;
    font-size:17px;
    font-weight:600;
}

.place-order-btn:hover {
    background:#5f211c;
    color:white;
}

.checkout-link {
    display:block;
    width:100%;
    text-align:center;
    padding:12px;
    border:1px solid #7b2d26;
    color:#7b2d26;
    text-decoration:none;
    border-radius:7px;
    margin-top:10px;
    font-weight:600;
}

.checkout-link:hover {
    background:#7b2d26;
    color:white;
}

@media(max-width:767px) {

    .checkout-page {
        padding:25px 15px 40px;
    }

    .checkout-card {
        padding:18px;
    }

}

</style>


<div class="checkout-page">

<div class="container">

    <h2 class="checkout-title text-center">

        <i class="fas fa-shopping-bag"></i>

        Checkout

    </h2>


    <?php if ($error): ?>

        <div class="alert alert-danger">

            <i class="fas fa-exclamation-circle"></i>

            <?php
            echo htmlspecialchars($error);
            ?>

        </div>

    <?php endif; ?>


    <form method="POST">

        <div class="row">


            <!-- =========================================
                 CUSTOMER DETAILS
            ========================================== -->

            <div class="col-lg-7">

                <div class="checkout-card">

                    <h4>

                        <i class="fas fa-user"></i>

                        Delivery Details

                    </h4>


                    <div class="mb-3">

                        <label class="form-label">
                            Full Name
                        </label>

                        <input
                            type="text"
                            name="full_name"
                            class="form-control"
                            value="<?php
                            echo htmlspecialchars(
                                $customer['full_name'] ?? ''
                            );
                            ?>"
                            required
                        >

                    </div>


                    <div class="mb-3">

                        <label class="form-label">
                            Email
                        </label>

                        <input
                            type="email"
                            class="form-control"
                            value="<?php
                            echo htmlspecialchars(
                                $customer['email'] ?? ''
                            );
                            ?>"
                            readonly
                        >

                    </div>


                    <div class="mb-3">

                        <label class="form-label">
                            Phone Number
                        </label>

                        <input
                            type="text"
                            name="phone"
                            class="form-control"
                            value="<?php
                            echo htmlspecialchars(
                                $customer['phone'] ?? ''
                            );
                            ?>"
                            required
                        >

                    </div>


                    <div class="mb-3">

                        <label class="form-label">
                            Delivery Address
                        </label>

                        <textarea
                            name="address"
                            class="form-control"
                            rows="4"
                            required
                        ><?php
                        echo htmlspecialchars(
                            $customer['address'] ?? ''
                        );
                        ?></textarea>

                    </div>

                </div>


                <!-- =====================================
                     PAYMENT
                ====================================== -->

                <div class="checkout-card">

                    <h4>

                        <i class="fas fa-wallet"></i>

                        Payment Method

                    </h4>


                    <div class="payment-box">

                        <div class="form-check">

                            <input
                                type="radio"
                                name="payment_method"
                                value="Cash on Delivery"
                                id="cod"
                                class="form-check-input"
                                checked
                            >

                            <label
                                class="form-check-label"
                                for="cod"
                            >

                                <i class="fas fa-money-bill-wave"></i>

                                Cash on Delivery

                            </label>

                        </div>


                        <small class="text-muted">

                            Pay when your order is delivered.

                        </small>

                    </div>

                </div>

            </div>


            <!-- =========================================
                 ORDER SUMMARY
            ========================================== -->

            <div class="col-lg-5">

                <div class="checkout-card">

                    <h4>

                        <i class="fas fa-receipt"></i>

                        Order Summary

                    </h4>


                    <?php foreach ($cartItems as $item): ?>

                        <div class="product-row">


                            <?php if (!empty($item['image'])): ?>

                                <img
                                    src="assets/images/products/<?php
                                    echo htmlspecialchars(
                                        $item['image']
                                    );
                                    ?>"
                                    alt="<?php
                                    echo htmlspecialchars(
                                        $item['product_name']
                                    );
                                    ?>"
                                >

                            <?php else: ?>

                                <div
                                    style="
                                        width:70px;
                                        height:70px;
                                        background:#f5f5f5;
                                        border-radius:8px;
                                        display:flex;
                                        align-items:center;
                                        justify-content:center;
                                    "
                                >
                                    <i class="fas fa-image"></i>
                                </div>

                            <?php endif; ?>


                            <div class="flex-grow-1">

                                <div class="product-name">

                                    <?php
                                    echo htmlspecialchars(
                                        $item['product_name']
                                    );
                                    ?>

                                </div>


                                <small>

                                    Quantity:
                                    <?php
                                    echo (int)$item['quantity'];
                                    ?>

                                </small>


                                <div class="product-price">

                                    ₹<?php
                                    echo number_format(
                                        $item['price'] *
                                        $item['quantity'],
                                        2
                                    );
                                    ?>

                                </div>

                            </div>

                        </div>

                    <?php endforeach; ?>


                    <div class="mt-3">

                        <div class="d-flex justify-content-between">

                            <span>
                                Total Items
                            </span>

                            <strong>
                                <?php
                                echo $totalItems;
                                ?>
                            </strong>

                        </div>


                        <div class="total-box">

                            <span>
                                Total
                            </span>

                            <span>

                                ₹<?php
                                echo number_format(
                                    $grandTotal,
                                    2
                                );
                                ?>

                            </span>

                        </div>

                    </div>

                </div>


                <!-- =====================================
                     BUTTONS
                ====================================== -->

                <button
                    type="submit"
                    name="place_order"
                    class="place-order-btn"
                >

                    <i class="fas fa-check-circle"></i>

                    Place Order - Cash on Delivery

                </button>


                <a
                    href="cart.php"
                    class="checkout-link"
                >

                    <i class="fas fa-arrow-left"></i>

                    Back to Cart

                </a>


                <a
                    href="shop.php"
                    class="checkout-link"
                >

                    <i class="fas fa-shopping-bag"></i>

                    Continue Shopping

                </a>

            </div>

        </div>

    </form>

</div>

</div>


<?php include 'includes/footer.php'; ?>