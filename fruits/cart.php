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
   ADD NORMAL PRODUCT TO CART
===================================================== */

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {

    $product_id = isset($_POST['product_id'])
        ? (int)$_POST['product_id']
        : 0;

    $quantity = isset($_POST['quantity'])
        ? (float)$_POST['quantity']
        : 1;

    $weight_unit = isset($_POST['weight_unit'])
        ? strtolower(trim($_POST['weight_unit']))
        : 'kg';

    if (!in_array($weight_unit, ['kg', 'g'])) {
        $weight_unit = 'kg';
    }

    if ($product_id <= 0) {
        $_SESSION['cart_error'] = 'Invalid product.';
        header('Location: shop.php');
        exit;
    }

    if ($quantity <= 0) {
        $quantity = 1;
    }


    /* GET PRODUCT */

    $stmt = $pdo->prepare("
        SELECT id, product_name, stock, status
        FROM products
        WHERE id = ?
        LIMIT 1
    ");

    $stmt->execute([$product_id]);

    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        $_SESSION['cart_error'] = 'Product not found.';
        header('Location: shop.php');
        exit;
    }

    if ($product['status'] !== 'Active') {
        $_SESSION['cart_error'] = 'This product is not available.';
        header('Location: shop.php');
        exit;
    }

    $stock = (float)$product['stock'];

    /*
       Stock is treated as KG.
       Convert grams into KG for stock checking.
    */

    $quantityInKg = ($weight_unit === 'g')
        ? ($quantity / 1000)
        : $quantity;

    if ($quantityInKg <= 0) {
        $_SESSION['cart_error'] = 'Invalid weight.';
        header('Location: shop.php');
        exit;
    }

    if ($quantityInKg > $stock) {
        $_SESSION['cart_error'] =
            'Requested weight is greater than available stock.';
        header('Location: shop.php');
        exit;
    }


    /* CHECK EXISTING CART ITEM */

    $stmt = $pdo->prepare("
        SELECT id, quantity, weight_unit
        FROM cart
        WHERE customer_id = ?
        AND product_id = ?
        LIMIT 1
    ");

    $stmt->execute([
        $customer_id,
        $product_id
    ]);

    $existing = $stmt->fetch(PDO::FETCH_ASSOC);


    if ($existing) {

        $existingQuantity = (float)$existing['quantity'];

        if ($existing['weight_unit'] === 'g') {
            $existingQuantityKg = $existingQuantity / 1000;
        } else {
            $existingQuantityKg = $existingQuantity;
        }

        $newQuantityKg = $existingQuantityKg + $quantityInKg;

        if ($newQuantityKg > $stock) {

            $_SESSION['cart_error'] =
                'You cannot add more than the available stock.';

            header('Location: shop.php');
            exit;
        }


        if ($weight_unit === 'g') {
            $newQuantity = $newQuantityKg * 1000;
        } else {
            $newQuantity = $newQuantityKg;
        }


        $stmt = $pdo->prepare("
            UPDATE cart
            SET quantity = ?, weight_unit = ?
            WHERE id = ?
            AND customer_id = ?
        ");

        $stmt->execute([
            $newQuantity,
            $weight_unit,
            $existing['id'],
            $customer_id
        ]);

    } else {

        $stmt = $pdo->prepare("
            INSERT INTO cart
            (
                customer_id,
                product_id,
                quantity,
                weight_unit
            )
            VALUES
            (
                ?,
                ?,
                ?,
                ?
            )
        ");

        $stmt->execute([
            $customer_id,
            $product_id,
            $quantity,
            $weight_unit
        ]);
    }


    $_SESSION['cart_success'] =
        'Product added to cart successfully.';

    header('Location: cart.php');
    exit;
}


/* =====================================================
   UPDATE NORMAL PRODUCT CART
===================================================== */

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_cart'])) {

    $cart_id = isset($_POST['cart_id'])
        ? (int)$_POST['cart_id']
        : 0;

    $quantity = isset($_POST['quantity'])
        ? (float)$_POST['quantity']
        : 1;

    $weight_unit = isset($_POST['weight_unit'])
        ? strtolower(trim($_POST['weight_unit']))
        : 'kg';


    if (!in_array($weight_unit, ['kg', 'g'])) {
        $weight_unit = 'kg';
    }


    if ($cart_id <= 0) {

        $_SESSION['cart_error'] =
            'Invalid cart item.';

        header('Location: cart.php');
        exit;
    }


    if ($quantity <= 0) {
        $quantity = 1;
    }


    /* GET CART ITEM + PRODUCT STOCK */

    $stmt = $pdo->prepare("
        SELECT
            c.id,
            c.product_id,
            p.stock,
            p.status
        FROM cart c
        INNER JOIN products p
            ON c.product_id = p.id
        WHERE c.id = ?
        AND c.customer_id = ?
        LIMIT 1
    ");

    $stmt->execute([
        $cart_id,
        $customer_id
    ]);

    $cartItem = $stmt->fetch(PDO::FETCH_ASSOC);


    if (!$cartItem) {

        $_SESSION['cart_error'] =
            'Cart item not found.';

        header('Location: cart.php');
        exit;
    }


    if ($cartItem['status'] !== 'Active') {

        $_SESSION['cart_error'] =
            'This product is no longer available.';

        header('Location: cart.php');
        exit;
    }


    $stock = (float)$cartItem['stock'];


    $quantityInKg = ($weight_unit === 'g')
        ? ($quantity / 1000)
        : $quantity;


    if ($quantityInKg > $stock) {

        $_SESSION['cart_error'] =
            'Quantity cannot be greater than available stock.';

        header('Location: cart.php');
        exit;
    }


    $stmt = $pdo->prepare("
        UPDATE cart
        SET quantity = ?, weight_unit = ?
        WHERE id = ?
        AND customer_id = ?
    ");

    $stmt->execute([
        $quantity,
        $weight_unit,
        $cart_id,
        $customer_id
    ]);


    $_SESSION['cart_success'] =
        'Cart updated successfully.';

    header('Location: cart.php');
    exit;
}


/* =====================================================
   UPDATE GIFT PACK CART
===================================================== */

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_gift_cart'])) {

    $gift_cart_id = isset($_POST['gift_cart_id'])
        ? (int)$_POST['gift_cart_id']
        : 0;

    $quantity = isset($_POST['gift_quantity'])
        ? (int)$_POST['gift_quantity']
        : 1;


    if ($gift_cart_id <= 0) {

        $_SESSION['cart_error'] =
            'Invalid gift pack.';

        header('Location: cart.php');
        exit;
    }


    if ($quantity <= 0) {
        $quantity = 1;
    }


    /* GET GIFT PACK STOCK */

    $stmt = $pdo->prepare("
        SELECT
            gc.id,
            gc.gift_pack_id,
            gp.stock,
            gp.status
        FROM gift_cart gc
        INNER JOIN gift_packs gp
            ON gc.gift_pack_id = gp.id
        WHERE gc.id = ?
        AND gc.customer_id = ?
        LIMIT 1
    ");

    $stmt->execute([
        $gift_cart_id,
        $customer_id
    ]);

    $giftItem = $stmt->fetch(PDO::FETCH_ASSOC);


    if (!$giftItem) {

        $_SESSION['cart_error'] =
            'Gift pack not found in cart.';

        header('Location: cart.php');
        exit;
    }


    if ($giftItem['status'] !== 'Active') {

        $_SESSION['cart_error'] =
            'This gift pack is no longer available.';

        header('Location: cart.php');
        exit;
    }


    $stock = (int)$giftItem['stock'];


    if ($quantity > $stock) {

        $_SESSION['cart_error'] =
            'Gift pack quantity cannot be greater than available stock.';

        header('Location: cart.php');
        exit;
    }


    $stmt = $pdo->prepare("
        UPDATE gift_cart
        SET quantity = ?
        WHERE id = ?
        AND customer_id = ?
    ");

    $stmt->execute([
        $quantity,
        $gift_cart_id,
        $customer_id
    ]);


    $_SESSION['cart_success'] =
        'Gift pack cart updated successfully.';

    header('Location: cart.php');
    exit;
}


/* =====================================================
   REMOVE NORMAL PRODUCT
===================================================== */

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_cart'])) {

    $cart_id = isset($_POST['cart_id'])
        ? (int)$_POST['cart_id']
        : 0;


    if ($cart_id > 0) {

        $stmt = $pdo->prepare("
            DELETE FROM cart
            WHERE id = ?
            AND customer_id = ?
        ");

        $stmt->execute([
            $cart_id,
            $customer_id
        ]);
    }


    $_SESSION['cart_success'] =
        'Product removed from cart.';

    header('Location: cart.php');
    exit;
}


/* =====================================================
   REMOVE GIFT PACK
===================================================== */

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_gift_cart'])) {

    $gift_cart_id = isset($_POST['gift_cart_id'])
        ? (int)$_POST['gift_cart_id']
        : 0;


    if ($gift_cart_id > 0) {

        $stmt = $pdo->prepare("
            DELETE FROM gift_cart
            WHERE id = ?
            AND customer_id = ?
        ");

        $stmt->execute([
            $gift_cart_id,
            $customer_id
        ]);
    }


    $_SESSION['cart_success'] =
        'Gift pack removed from cart.';

    header('Location: cart.php');
    exit;
}


/* =====================================================
   GET NORMAL CART ITEMS
===================================================== */

$stmt = $pdo->prepare("
    SELECT
        c.id AS cart_id,
        c.product_id,
        c.quantity,
        c.weight_unit,

        p.product_name,
        p.description,
        p.price,
        p.stock,
        p.image,
        p.status,

        cat.category_name

    FROM cart c

    INNER JOIN products p
        ON c.product_id = p.id

    LEFT JOIN categories cat
        ON p.category_id = cat.id

    WHERE c.customer_id = ?

    ORDER BY c.id DESC
");

$stmt->execute([$customer_id]);

$normalCartItems = $stmt->fetchAll(PDO::FETCH_ASSOC);


/* =====================================================
   GET GIFT CART ITEMS
===================================================== */

$stmt = $pdo->prepare("
    SELECT
        gc.id AS gift_cart_id,
        gc.gift_pack_id,
        gc.quantity,

        gp.gift_name,
        gp.description,
        gp.price,
        gp.stock,
        gp.weight,
        gp.image,
        gp.status

    FROM gift_cart gc

    INNER JOIN gift_packs gp
        ON gc.gift_pack_id = gp.id

    WHERE gc.customer_id = ?

    ORDER BY gc.id DESC
");

$stmt->execute([$customer_id]);

$giftCartItems = $stmt->fetchAll(PDO::FETCH_ASSOC);


/* =====================================================
   COMBINE NORMAL PRODUCTS + GIFT PACKS
===================================================== */

$cartItems = [];


/* NORMAL PRODUCTS */

foreach ($normalCartItems as $item) {

    $item['cart_type'] = 'product';

    $cartItems[] = $item;
}


/* GIFT PACKS */

foreach ($giftCartItems as $item) {

    $item['cart_type'] = 'gift';

    $cartItems[] = $item;
}


/* =====================================================
   CALCULATE TOTAL
===================================================== */

$grandTotal = 0;
$totalItems = 0;


/* NORMAL PRODUCT TOTAL */

foreach ($normalCartItems as $item) {

    $price = (float)$item['price'];

    $quantity = (float)$item['quantity'];

    $unit = strtolower(
        $item['weight_unit'] ?? 'kg'
    );


    /*
       Convert grams to KG.
    */

    $quantityInKg = ($unit === 'g')
        ? ($quantity / 1000)
        : $quantity;


    $itemTotal =
        $price * $quantityInKg;


    $grandTotal += $itemTotal;

    $totalItems += $quantityInKg;
}


/* GIFT PACK TOTAL */

foreach ($giftCartItems as $item) {

    $price = (float)$item['price'];

    $quantity = (int)$item['quantity'];

    $itemTotal =
        $price * $quantity;


    $grandTotal += $itemTotal;

    /*
       Gift packs are counted by pieces,
       not KG, because each gift pack is
       a separate pack.
    */
}


/* =====================================================
   HEADER
===================================================== */

include 'includes/header.php';

?>

<style>

/* =====================================================
   MY CART HEADING
===================================================== */

.cart-heading {
    margin-top: 65px;
}

.cart-heading h2 {
    font-size: 32px;
    font-weight: 700;
    color: #7b2d26;
    letter-spacing: 0.5px;
    margin-bottom: 8px;
}

.cart-heading h2 i {
    color: #a0522d;
    margin-right: 8px;
}

.cart-heading p {
    color: #8b6f47;
    font-size: 15px;
    margin-bottom: 0;
}


/* =====================================================
   CART PRODUCT IMAGE
===================================================== */

.cart-product-image {
    width: 120px;
    height: 120px;
    object-fit: cover;
    border-radius: 10px;
}


/* =====================================================
   CART CARD
===================================================== */

.cart-card {
    border: none;
    border-radius: 12px;
}


/* =====================================================
   ORDER SUMMARY
===================================================== */

.order-summary {
    border: none;
    border-radius: 12px;
}


/* =====================================================
   WEIGHT BOX
===================================================== */

.weight-box {
    display: flex;
    gap: 8px;
    max-width: 280px;
}

.weight-box input {
    flex: 1;
}

.weight-box select {
    width: 90px;
}


/* =====================================================
   GIFT PACK LABEL
===================================================== */

.gift-label {
    display: inline-block;
    background: #a0522d;
    color: white;
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    margin-bottom: 7px;
}


/* =====================================================
   MOBILE
===================================================== */

@media (max-width: 767px) {

    .cart-heading {
        margin-top: 20px;
    }

    .cart-heading h2 {
        font-size: 27px;
    }

    .cart-product-image {
        width: 110px;
        height: 110px;
    }

}

</style>


<div class="container pt-4 pb-3">


    <!-- MY CART HEADING -->

    <div class="text-center mb-4 cart-heading">

        <h2>
            <i class="fas fa-shopping-cart"></i>
            My Cart
        </h2>

        <p>
            Select the required quantity in KG or grams.
        </p>

    </div>


    <!-- SUCCESS MESSAGE -->

    <?php if (isset($_SESSION['cart_success'])): ?>

        <div class="alert alert-success">

            <?php

            echo htmlspecialchars(
                $_SESSION['cart_success']
            );

            unset($_SESSION['cart_success']);

            ?>

        </div>

    <?php endif; ?>


    <!-- ERROR MESSAGE -->

    <?php if (isset($_SESSION['cart_error'])): ?>

        <div class="alert alert-danger">

            <?php

            echo htmlspecialchars(
                $_SESSION['cart_error']
            );

            unset($_SESSION['cart_error']);

            ?>

        </div>

    <?php endif; ?>


    <?php if (empty($cartItems)): ?>


        <!-- EMPTY CART -->

        <div class="text-center py-5">

            <i
                class="fas fa-shopping-cart"
                style="font-size:70px;color:#a0522d;"
            ></i>

            <h3 class="mt-3">
                Your Cart is Empty
            </h3>

            <p class="text-muted">
                Add some dry fruits or gift packs to your cart.
            </p>

            <a
                href="shop.php"
                class="btn"
                style="
                    background:#7b2d26;
                    color:white;
                    border-radius:6px;
                "
            >
                Continue Shopping
            </a>

        </div>


    <?php else: ?>


        <div class="row">


            <!-- CART PRODUCTS -->

            <div class="col-lg-8">

                <?php foreach ($cartItems as $item): ?>


                    <?php if ($item['cart_type'] === 'product'): ?>


                        <!-- =================================================
                             NORMAL PRODUCT
                        ================================================= -->

                        <?php

                        $price = (float)$item['price'];

                        $quantity = (float)$item['quantity'];

                        $unit = strtolower(
                            $item['weight_unit'] ?? 'kg'
                        );


                        $quantityInKg = ($unit === 'g')
                            ? ($quantity / 1000)
                            : $quantity;


                        $itemTotal =
                            $price * $quantityInKg;

                        ?>


                        <div class="card mb-3 shadow-sm cart-card">

                            <div class="card-body">

                                <div class="row align-items-center">


                                    <!-- IMAGE -->

                                    <div class="col-md-3 text-center">

                                        <?php if (!empty($item['image'])): ?>

                                            <img
                                                src="assets/images/products/<?php echo htmlspecialchars($item['image']); ?>"
                                                alt="<?php echo htmlspecialchars($item['product_name']); ?>"
                                                class="cart-product-image"
                                            >

                                        <?php else: ?>

                                            <div
                                                style="
                                                    width:120px;
                                                    height:120px;
                                                    background:#f5f5f5;
                                                    display:flex;
                                                    align-items:center;
                                                    justify-content:center;
                                                    margin:auto;
                                                    border-radius:10px;
                                                "
                                            >
                                                No Image
                                            </div>

                                        <?php endif; ?>

                                    </div>


                                    <!-- PRODUCT DETAILS -->

                                    <div class="col-md-5">

                                        <small style="color:#a0522d;">

                                            <?php

                                            echo htmlspecialchars(
                                                $item['category_name'] ?? ''
                                            );

                                            ?>

                                        </small>


                                        <h5 class="mt-1">

                                            <?php

                                            echo htmlspecialchars(
                                                $item['product_name']
                                            );

                                            ?>

                                        </h5>


                                        <p class="text-muted mb-1">

                                            <?php

                                            echo htmlspecialchars(
                                                $item['description'] ?? ''
                                            );

                                            ?>

                                        </p>


                                        <strong style="color:#7b2d26;">

                                            ₹<?php

                                            echo number_format(
                                                $price,
                                                2
                                            );

                                            ?>

                                        </strong>

                                        <small>
                                            / kg
                                        </small>


                                        <!-- WEIGHT -->

                                        <form
                                            method="POST"
                                            class="mt-3"
                                        >

                                            <input
                                                type="hidden"
                                                name="cart_id"
                                                value="<?php echo (int)$item['cart_id']; ?>"
                                            >


                                            <div class="weight-box">

                                                <input
                                                    type="number"
                                                    name="quantity"
                                                    value="<?php echo rtrim(rtrim(number_format($quantity, 3, '.', ''), '0'), '.'); ?>"
                                                    min="0.001"
                                                    step="0.001"
                                                    class="form-control weight-input"
                                                    data-price="<?php echo $price; ?>"
                                                    data-total-id="total-<?php echo (int)$item['cart_id']; ?>"
                                                    required
                                                >


                                                <select
                                                    name="weight_unit"
                                                    class="form-select unit-select"
                                                    data-price="<?php echo $price; ?>"
                                                    data-total-id="total-<?php echo (int)$item['cart_id']; ?>"
                                                >

                                                    <option
                                                        value="kg"
                                                        <?php echo ($unit === 'kg') ? 'selected' : ''; ?>
                                                    >
                                                        KG
                                                    </option>

                                                    <option
                                                        value="g"
                                                        <?php echo ($unit === 'g') ? 'selected' : ''; ?>
                                                    >
                                                        G
                                                    </option>

                                                </select>

                                            </div>


                                            <button
                                                type="submit"
                                                name="update_cart"
                                                class="btn mt-2"
                                                style="
                                                    background:#7b2d26;
                                                    color:white;
                                                "
                                            >
                                                Update
                                            </button>


                                            <div class="mt-2">

                                                <small class="text-muted">
                                                    Available stock:
                                                    <?php echo (float)$item['stock']; ?> KG
                                                </small>

                                            </div>

                                        </form>

                                    </div>


                                    <!-- PRICE -->

                                    <div class="col-md-4 text-md-end mt-3 mt-md-0">

                                        <small class="text-muted">
                                            Total Price
                                        </small>

                                        <h5
                                            style="color:#7b2d26;"
                                            id="total-<?php echo (int)$item['cart_id']; ?>"
                                        >

                                            ₹<?php

                                            echo number_format(
                                                $itemTotal,
                                                2
                                            );

                                            ?>

                                        </h5>


                                        <small class="text-muted d-block">

                                            <?php

                                            echo rtrim(
                                                rtrim(
                                                    number_format(
                                                        $quantity,
                                                        3,
                                                        '.',
                                                        ''
                                                    ),
                                                    '0'
                                                ),
                                                '.'
                                            );

                                            ?>

                                            <?php echo strtoupper($unit); ?>

                                        </small>


                                        <!-- REMOVE -->

                                        <form
                                            method="POST"
                                            class="mt-2"
                                        >

                                            <input
                                                type="hidden"
                                                name="cart_id"
                                                value="<?php echo (int)$item['cart_id']; ?>"
                                            >

                                            <button
                                                type="submit"
                                                name="remove_cart"
                                                class="btn btn-danger"
                                                onclick="return confirm('Are you sure you want to remove this product?');"
                                            >

                                                <i class="fas fa-trash"></i>
                                                Remove

                                            </button>

                                        </form>

                                    </div>

                                </div>

                            </div>

                        </div>


                    <?php else: ?>


                        <!-- =================================================
                             GIFT PACK
                        ================================================= -->

                        <?php

                        $giftPrice =
                            (float)$item['price'];

                        $giftQuantity =
                            (int)$item['quantity'];

                        $giftTotal =
                            $giftPrice * $giftQuantity;

                        ?>


                        <div class="card mb-3 shadow-sm cart-card">

                            <div class="card-body">

                                <div class="row align-items-center">


                                    <!-- GIFT IMAGE -->

                                    <div class="col-md-3 text-center">

                                        <?php if (!empty($item['image'])): ?>

                                            <img
                                                src="assets/images/gift-packs/<?php echo htmlspecialchars($item['image']); ?>"
                                                alt="<?php echo htmlspecialchars($item['gift_name']); ?>"
                                                class="cart-product-image"
                                            >

                                        <?php else: ?>

                                            <div
                                                style="
                                                    width:120px;
                                                    height:120px;
                                                    background:#f5f5f5;
                                                    display:flex;
                                                    align-items:center;
                                                    justify-content:center;
                                                    margin:auto;
                                                    border-radius:10px;
                                                "
                                            >
                                                No Image
                                            </div>

                                        <?php endif; ?>

                                    </div>


                                    <!-- GIFT DETAILS -->

                                    <div class="col-md-5">

                                        <span class="gift-label">
                                            <i class="fas fa-gift"></i>
                                            Gift Pack
                                        </span>


                                        <h5 class="mt-1">

                                            <?php

                                            echo htmlspecialchars(
                                                $item['gift_name']
                                            );

                                            ?>

                                        </h5>


                                        <p class="text-muted mb-2">

                                            <?php

                                            echo htmlspecialchars(
                                                $item['description'] ?? ''
                                            );

                                            ?>

                                        </p>


                                        <strong style="color:#7b2d26;">

                                            ₹<?php

                                            echo number_format(
                                                $giftPrice,
                                                2
                                            );

                                            ?>

                                        </strong>


                                        <?php if (!empty($item['weight'])): ?>

                                            <div class="mt-1">

                                                <small class="text-muted">

                                                    Weight:
                                                    <?php
                                                    echo htmlspecialchars(
                                                        $item['weight']
                                                    );
                                                    ?>

                                                </small>

                                            </div>

                                        <?php endif; ?>


                                        <!-- GIFT QUANTITY -->

                                        <form
                                            method="POST"
                                            class="mt-3"
                                        >

                                            <input
                                                type="hidden"
                                                name="gift_cart_id"
                                                value="<?php echo (int)$item['gift_cart_id']; ?>"
                                            >


                                            <div
                                                style="
                                                    display:flex;
                                                    gap:8px;
                                                    max-width:180px;
                                                "
                                            >

                                                <input
                                                    type="number"
                                                    name="gift_quantity"
                                                    value="<?php echo $giftQuantity; ?>"
                                                    min="1"
                                                    max="<?php echo (int)$item['stock']; ?>"
                                                    step="1"
                                                    class="form-control"
                                                    required
                                                >

                                            </div>


                                            <button
                                                type="submit"
                                                name="update_gift_cart"
                                                class="btn mt-2"
                                                style="
                                                    background:#7b2d26;
                                                    color:white;
                                                "
                                            >
                                                Update
                                            </button>


                                            <div class="mt-2">

                                                <small class="text-muted">

                                                    Available stock:
                                                    <?php echo (int)$item['stock']; ?>
                                                    packs

                                                </small>

                                            </div>

                                        </form>

                                    </div>


                                    <!-- GIFT PRICE -->

                                    <div class="col-md-4 text-md-end mt-3 mt-md-0">

                                        <small class="text-muted">
                                            Total Price
                                        </small>


                                        <h5
                                            style="color:#7b2d26;"
                                            id="gift-total-<?php echo (int)$item['gift_cart_id']; ?>"
                                        >

                                            ₹<?php

                                            echo number_format(
                                                $giftTotal,
                                                2
                                            );

                                            ?>

                                        </h5>


                                        <small class="text-muted d-block">

                                            <?php echo $giftQuantity; ?>

                                            <?php echo ($giftQuantity == 1)
                                                ? 'Pack'
                                                : 'Packs'; ?>

                                        </small>


                                        <!-- REMOVE GIFT -->

                                        <form
                                            method="POST"
                                            class="mt-2"
                                        >

                                            <input
                                                type="hidden"
                                                name="gift_cart_id"
                                                value="<?php echo (int)$item['gift_cart_id']; ?>"
                                            >


                                            <button
                                                type="submit"
                                                name="remove_gift_cart"
                                                class="btn btn-danger"
                                                onclick="return confirm('Are you sure you want to remove this gift pack?');"
                                            >

                                                <i class="fas fa-trash"></i>
                                                Remove

                                            </button>

                                        </form>

                                    </div>

                                </div>

                            </div>

                        </div>


                    <?php endif; ?>


                <?php endforeach; ?>

            </div>


            <!-- ORDER SUMMARY -->

            <div class="col-lg-4">

                <div class="card shadow-sm order-summary">

                    <div class="card-body">

                        <h4 class="mb-4">
                            Order Summary
                        </h4>


                        <div
                            class="d-flex justify-content-between mb-3"
                        >

                            <span>
                                Total Items
                            </span>

                            <strong>

                                <?php

                                $normalItemCount = count($normalCartItems);

                                $giftItemCount = count($giftCartItems);

                                echo $normalItemCount + $giftItemCount;

                                ?>

                            </strong>

                        </div>


                        <div
                            class="d-flex justify-content-between mb-3"
                        >

                            <span>
                                Product Weight
                            </span>

                            <strong>

                                <?php

                                echo rtrim(
                                    rtrim(
                                        number_format(
                                            $totalItems,
                                            3,
                                            '.',
                                            ''
                                        ),
                                        '0'
                                    ),
                                    '.'
                                );

                                ?>

                                KG

                            </strong>

                        </div>


                        <hr>


                        <div
                            class="d-flex justify-content-between"
                        >

                            <strong>
                                Total
                            </strong>

                            <strong
                                style="
                                    color:#7b2d26;
                                    font-size:22px;
                                "
                            >

                                ₹<?php

                                echo number_format(
                                    $grandTotal,
                                    2
                                );

                                ?>

                            </strong>

                        </div>


                        <a
                            href="checkout.php"
                            class="btn w-100 mt-4"
                            style="
                                background:#7b2d26;
                                color:white;
                            "
                        >
                            Proceed to Checkout
                        </a>


                        <a
                            href="shop.php"
                            class="btn w-100 mt-2"
                            style="
                                border:1px solid #7b2d26;
                                color:#7b2d26;
                            "
                        >
                            Continue Shopping
                        </a>

                    </div>

                </div>

            </div>

        </div>


    <?php endif; ?>

</div>


<script>

/* =====================================================
   LIVE PRICE CALCULATION FOR NORMAL PRODUCTS
===================================================== */

document.addEventListener('DOMContentLoaded', function () {

    const inputs =
        document.querySelectorAll('.weight-input');

    inputs.forEach(function (input) {

        const totalId =
            input.getAttribute('data-total-id');

        const price =
            parseFloat(input.getAttribute('data-price'));

        const unitSelect =
            input.parentElement.querySelector('.unit-select');


        function calculatePrice() {

            let quantity =
                parseFloat(input.value) || 0;

            let unit =
                unitSelect.value;

            let quantityInKg =
                quantity;


            if (unit === 'g') {

                quantityInKg =
                    quantity / 1000;
            }


            let total =
                price * quantityInKg;


            const totalElement =
                document.getElementById(totalId);


            if (totalElement) {

                totalElement.innerHTML =
                    '₹' +
                    total.toLocaleString(
                        'en-IN',
                        {
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2
                        }
                    );
            }
        }


        input.addEventListener(
            'input',
            calculatePrice
        );


        unitSelect.addEventListener(
            'change',
            calculatePrice
        );

    });

});

</script>


<?php include 'includes/footer.php'; ?>