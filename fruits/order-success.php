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


/* =====================================================
   GET ORDER ID
===================================================== */

$order_id = isset($_SESSION['last_order_id'])
    ? (int)$_SESSION['last_order_id']
    : 0;


if ($order_id <= 0) {

    header('Location: shop.php');
    exit;
}


/* =====================================================
   GET ORDER
===================================================== */

$stmt = $pdo->prepare("
    SELECT

        o.id,
        o.total_amount,
        o.payment_method,
        o.payment_status,
        o.order_status,
        o.created_at,

        c.full_name,
        c.email,
        c.phone,
        c.address

    FROM orders o

    INNER JOIN customers c
        ON o.customer_id = c.id

    WHERE o.id = ?
    AND o.customer_id = ?

    LIMIT 1
");

$stmt->execute([
    $order_id,
    $customer_id
]);

$order = $stmt->fetch(PDO::FETCH_ASSOC);


if (!$order) {

    unset($_SESSION['last_order_id']);

    header('Location: shop.php');
    exit;
}


/* =====================================================
   GET ORDER ITEMS
===================================================== */

$stmt = $pdo->prepare("
    SELECT

        oi.quantity,
        oi.price,

        p.product_name,
        p.image

    FROM order_items oi

    INNER JOIN products p
        ON oi.product_id = p.id

    WHERE oi.order_id = ?

    ORDER BY oi.id ASC
");

$stmt->execute([$order_id]);

$orderItems = $stmt->fetchAll(PDO::FETCH_ASSOC);


/* =====================================================
   REMOVE SESSION ORDER ID
===================================================== */

unset($_SESSION['last_order_id']);


include 'includes/header.php';

?>

<style>

.success-page {
    background:#fffaf7;
    min-height:75vh;
    padding:50px 15px 70px;
}

.success-card {
    max-width:850px;
    margin:auto;

    background:white;

    border-radius:15px;

    padding:35px;

    box-shadow:
        0 5px 25px
        rgba(0,0,0,0.08);
}

.success-icon {

    width:80px;
    height:80px;

    border-radius:50%;

    background:#7b2d26;
    color:white;

    display:flex;
    align-items:center;
    justify-content:center;

    font-size:40px;

    margin:0 auto 20px;
}

.success-title {

    text-align:center;

    color:#7b2d26;

    font-weight:700;
}

.success-message {

    text-align:center;

    color:#555;

    margin-bottom:30px;
}

.info-box {

    background:#fff8f5;

    border-radius:10px;

    padding:20px;

    margin-bottom:25px;
}

.info-row {

    display:flex;

    justify-content:space-between;

    padding:9px 0;

    border-bottom:1px solid #ead8d2;
}

.info-row:last-child {
    border-bottom:none;
}

.info-row strong {
    color:#4d2924;
}

.status {
    color:#7b2d26;
    font-weight:700;
}

.delivery-box {

    background:#fff8f5;

    padding:20px;

    border-radius:10px;

    margin-bottom:25px;
}

.delivery-box h5 {

    color:#7b2d26;

    font-weight:700;
}

.item-row {

    display:flex;

    align-items:center;

    gap:15px;

    padding:15px 0;

    border-bottom:1px solid #eee;
}

.item-row:last-child {
    border-bottom:none;
}

.item-row img {

    width:65px;
    height:65px;

    object-fit:cover;

    border-radius:8px;
}

.item-name {

    font-weight:600;

    color:#4d2924;
}

.item-price {

    color:#7b2d26;

    font-weight:600;
}

.final-total {

    border-top:2px solid #ead8d2;

    margin-top:10px;

    padding-top:15px;

    display:flex;

    justify-content:space-between;

    color:#7b2d26;

    font-size:21px;

    font-weight:700;
}

.success-buttons {

    display:flex;

    gap:12px;

    margin-top:30px;
}

.success-buttons a {

    flex:1;

    text-align:center;

    text-decoration:none;

    padding:12px;

    border-radius:7px;

    font-weight:600;
}

.shop-btn {

    background:#7b2d26;

    color:white;
}

.shop-btn:hover {

    background:#5f211c;

    color:white;
}

.home-btn {

    border:1px solid #7b2d26;

    color:#7b2d26;
}

.home-btn:hover {

    background:#7b2d26;

    color:white;
}


@media(max-width:600px) {

    .success-card {
        padding:20px;
    }

    .success-buttons {
        flex-direction:column;
    }

    .info-row {
        flex-direction:column;
        gap:3px;
    }

}

</style>


<div class="success-page">

<div class="success-card">


    <!-- SUCCESS ICON -->

    <div class="success-icon">

        <i class="fas fa-check"></i>

    </div>


    <h2 class="success-title">

        Order Placed Successfully!

    </h2>


    <p class="success-message">

        Thank you for shopping with
        <strong>Tilawat Dry Fruits</strong>.

        Your Cash on Delivery order has been placed successfully.

    </p>


    <!-- =========================================
         ORDER INFORMATION
    ========================================== -->

    <div class="info-box">

        <div class="info-row">

            <strong>
                Order ID
            </strong>

            <span>
                #<?php
                echo (int)$order['id'];
                ?>
            </span>

        </div>


        <div class="info-row">

            <strong>
                Order Date
            </strong>

            <span>

                <?php

                echo date(
                    'd M Y, h:i A',
                    strtotime(
                        $order['created_at']
                    )
                );

                ?>

            </span>

        </div>


        <div class="info-row">

            <strong>
                Payment Method
            </strong>

            <span>

                <?php
                echo htmlspecialchars(
                    $order['payment_method']
                );
                ?>

            </span>

        </div>


        <div class="info-row">

            <strong>
                Payment Status
            </strong>

            <span class="status">

                <?php
                echo htmlspecialchars(
                    $order['payment_status']
                );
                ?>

            </span>

        </div>


        <div class="info-row">

            <strong>
                Order Status
            </strong>

            <span class="status">

                <?php
                echo htmlspecialchars(
                    $order['order_status']
                );
                ?>

            </span>

        </div>

    </div>


    <!-- =========================================
         DELIVERY DETAILS
    ========================================== -->

    <div class="delivery-box">

        <h5>

            <i class="fas fa-map-marker-alt"></i>

            Delivery Details

        </h5>


        <p class="mb-1">

            <strong>

                <?php
                echo htmlspecialchars(
                    $order['full_name']
                );
                ?>

            </strong>

        </p>


        <p class="mb-1">

            <i class="fas fa-phone"></i>

            <?php
            echo htmlspecialchars(
                $order['phone']
            );
            ?>

        </p>


        <p class="mb-0">

            <i class="fas fa-home"></i>

            <?php
            echo nl2br(
                htmlspecialchars(
                    $order['address']
                )
            );
            ?>

        </p>

    </div>


    <!-- =========================================
         ORDERED PRODUCTS
    ========================================== -->

    <h4 style="color:#7b2d26;">

        <i class="fas fa-shopping-bag"></i>

        Ordered Products

    </h4>


    <?php foreach ($orderItems as $item): ?>

        <div class="item-row">


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
                        width:65px;
                        height:65px;
                        border-radius:8px;
                        background:#f5f5f5;
                        display:flex;
                        align-items:center;
                        justify-content:center;
                    "
                >

                    <i class="fas fa-image"></i>

                </div>

            <?php endif; ?>


            <div class="flex-grow-1">

                <div class="item-name">

                    <?php
                    echo htmlspecialchars(
                        $item['product_name']
                    );
                    ?>

                </div>


                <div>

                    Quantity:
                    <?php
                    echo (int)$item['quantity'];
                    ?>

                </div>


                <div class="item-price">

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


    <!-- =========================================
         FINAL TOTAL
    ========================================== -->

    <div class="final-total">

        <span>
            Total Amount
        </span>

        <span>

            ₹<?php

            echo number_format(
                $order['total_amount'],
                2
            );

            ?>

        </span>

    </div>


    <!-- =========================================
         BUTTONS
    ========================================== -->

    <div class="success-buttons">

        <a
            href="shop.php"
            class="shop-btn"
        >

            <i class="fas fa-shopping-bag"></i>

            Continue Shopping

        </a>


        <a
            href="index.php"
            class="home-btn"
        >

            <i class="fas fa-home"></i>

            Go to Home

        </a>

    </div>


</div>

</div>


<?php include 'includes/footer.php'; ?>