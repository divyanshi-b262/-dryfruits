```php
<?php

session_start();

include '../includes/db.php';

/*
|--------------------------------------------------------------------------
| ADMIN ORDERS PAGE
|--------------------------------------------------------------------------
| Shows all placed orders for the administrator.
|--------------------------------------------------------------------------
*/

/* =====================================================
   GET ALL ORDERS
===================================================== */

$stmt = $pdo->prepare("
    SELECT
        o.id AS order_id,
        o.customer_id,
        o.total_amount,
        o.payment_method,
        o.payment_status,
        o.order_status,

        c.full_name,
        c.email,
        c.phone,
        c.address

    FROM orders o

    LEFT JOIN customers c
        ON o.customer_id = c.id

    ORDER BY o.id DESC
");

$stmt->execute();

$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);


/* =====================================================
   GET ORDER ITEMS
===================================================== */

$orderItems = [];

if (!empty($orders)) {

    $orderIds = array_column($orders, 'order_id');

    $placeholders = implode(
        ',',
        array_fill(0, count($orderIds), '?')
    );

    $stmt = $pdo->prepare("
        SELECT
            oi.order_id,
            oi.product_id,
            oi.quantity,
            oi.price,
            p.product_name

        FROM order_items oi

        LEFT JOIN products p
            ON oi.product_id = p.id

        WHERE oi.order_id IN ($placeholders)

        ORDER BY oi.order_id DESC
    ");

    $stmt->execute($orderIds);

    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($items as $item) {

        $orderItems[$item['order_id']][] = $item;
    }
}


/* =====================================================
   INCLUDE ADMIN HEADER
===================================================== */

if (file_exists('includes/header.php')) {
    include 'includes/header.php';
}

?>

<style>

/* =====================================================
   ORDERS PAGE
===================================================== */

.orders-page {
    background: #fffaf7;
    min-height: 100vh;
    padding: 40px 20px 60px;
}

.orders-title {
    color: #7b2d26;
    font-weight: 700;
    margin-bottom: 30px;
}

.orders-card {
    background: #ffffff;
    border-radius: 12px;
    padding: 20px;
    box-shadow: 0 4px 18px rgba(0,0,0,0.08);
    margin-bottom: 25px;
}

/* =====================================================
   ORDER HEADER
===================================================== */

.order-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 15px;
    flex-wrap: wrap;

    border-bottom: 1px solid #ead8d2;
    padding-bottom: 15px;
    margin-bottom: 20px;
}

.order-id {
    color: #7b2d26;
    font-size: 20px;
    font-weight: 700;
}

.order-id span {
    color: #4d2924;
}

/* =====================================================
   CUSTOMER DETAILS
===================================================== */

.customer-box {
    background: #fff8f5;
    border: 1px solid #ead8d2;
    border-radius: 8px;
    padding: 15px;
    margin-bottom: 20px;
}

.customer-box h5 {
    color: #7b2d26;
    font-weight: 700;
    margin-bottom: 12px;
}

.customer-box p {
    margin-bottom: 6px;
    color: #4d2924;
}

/* =====================================================
   ORDER INFORMATION
===================================================== */

.order-info {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 15px;
    margin-bottom: 20px;
}

.info-box {
    background: #fffaf7;
    border: 1px solid #ead8d2;
    border-radius: 8px;
    padding: 14px;
}

.info-label {
    font-size: 13px;
    color: #777;
    margin-bottom: 5px;
}

.info-value {
    color: #4d2924;
    font-weight: 700;
}

/* =====================================================
   ITEMS TABLE
===================================================== */

.items-title {
    color: #7b2d26;
    font-size: 18px;
    font-weight: 700;
    margin-bottom: 12px;
}

.items-table {
    width: 100%;
    border-collapse: collapse;
}

.items-table th {
    background: #7b2d26;
    color: white;
    padding: 11px;
    text-align: left;
}

.items-table td {
    padding: 11px;
    border-bottom: 1px solid #eee;
    color: #4d2924;
}

.items-table tr:last-child td {
    border-bottom: none;
}

/* =====================================================
   BADGES
===================================================== */

.status-badge {
    display: inline-block;
    padding: 6px 11px;
    border-radius: 20px;
    font-size: 13px;
    font-weight: 600;
}

.status-placed {
    background: #fff0df;
    color: #a85d00;
}

.status-pending {
    background: #fff0df;
    color: #a85d00;
}

.status-paid {
    background: #e8f7ed;
    color: #26733f;
}

.status-delivered {
    background: #e8f7ed;
    color: #26733f;
}

.status-cancelled {
    background: #fde8e8;
    color: #a52626;
}

/* =====================================================
   ORDER TOTAL
===================================================== */

.order-total {
    text-align: right;
    margin-top: 15px;
    padding-top: 15px;
    border-top: 2px solid #ead8d2;
}

.order-total strong {
    color: #7b2d26;
    font-size: 20px;
}

/* =====================================================
   EMPTY ORDERS
===================================================== */

.no-orders {
    text-align: center;
    padding: 60px 20px;
    color: #777;
}

.no-orders i {
    font-size: 50px;
    color: #c9aaa1;
    margin-bottom: 15px;
}

/* =====================================================
   MOBILE
===================================================== */

@media(max-width: 991px) {

    .order-info {
        grid-template-columns: repeat(2, 1fr);
    }

}

@media(max-width: 767px) {

    .orders-page {
        padding: 25px 12px 40px;
    }

    .orders-card {
        padding: 15px;
    }

    .order-info {
        grid-template-columns: 1fr;
    }

    .items-table {
        font-size: 13px;
    }

    .items-table th,
    .items-table td {
        padding: 8px;
    }

    .order-header {
        display: block;
    }

    .order-header > div {
        margin-bottom: 8px;
    }

}

</style>


<div class="orders-page">

    <div class="container-fluid">

        <h2 class="orders-title">
            <i class="fas fa-shopping-bag"></i>
            All Orders
        </h2>


        <?php if (empty($orders)): ?>

            <div class="orders-card no-orders">

                <i class="fas fa-box-open"></i>

                <h4>No Orders Found</h4>

                <p>
                    No customer orders have been placed yet.
                </p>

            </div>

        <?php else: ?>


            <?php foreach ($orders as $order): ?>

                <div class="orders-card">


                    <!-- =================================
                         ORDER HEADER
                    ================================== -->

                    <div class="order-header">

                        <div class="order-id">

                            <i class="fas fa-receipt"></i>

                            Order ID:
                            <span>
                                #<?php
                                echo htmlspecialchars(
                                    $order['order_id']
                                );
                                ?>
                            </span>

                        </div>


                        <div>

                            <?php

                            $orderStatus =
                                $order['order_status'];

                            $orderStatusClass =
                                'status-placed';

                            if (
                                strtolower($orderStatus)
                                === 'delivered'
                            ) {
                                $orderStatusClass =
                                    'status-delivered';
                            }

                            if (
                                strtolower($orderStatus)
                                === 'cancelled'
                            ) {
                                $orderStatusClass =
                                    'status-cancelled';
                            }

                            ?>

                            <span class="status-badge
                                <?php
                                echo $orderStatusClass;
                                ?>">

                                <?php
                                echo htmlspecialchars(
                                    $orderStatus
                                );
                                ?>

                            </span>

                        </div>

                    </div>


                    <!-- =================================
                         CUSTOMER INFORMATION
                    ================================== -->

                    <div class="customer-box">

                        <h5>
                            <i class="fas fa-user"></i>
                            Customer Information
                        </h5>


                        <p>
                            <strong>Customer ID:</strong>

                            <?php
                            echo htmlspecialchars(
                                $order['customer_id']
                            );
                            ?>
                        </p>


                        <p>
                            <strong>Name:</strong>

                            <?php
                            echo htmlspecialchars(
                                $order['full_name'] ?? 'N/A'
                            );
                            ?>
                        </p>


                        <p>
                            <strong>Email:</strong>

                            <?php
                            echo htmlspecialchars(
                                $order['email'] ?? 'N/A'
                            );
                            ?>
                        </p>


                        <p>
                            <strong>Phone:</strong>

                            <?php
                            echo htmlspecialchars(
                                $order['phone'] ?? 'N/A'
                            );
                            ?>
                        </p>


                        <p>
                            <strong>Delivery Address:</strong>

                            <?php
                            echo nl2br(
                                htmlspecialchars(
                                    $order['address'] ?? 'N/A'
                                )
                            );
                            ?>
                        </p>

                    </div>


                    <!-- =================================
                         ORDER INFORMATION
                    ================================== -->

                    <div class="order-info">


                        <div class="info-box">

                            <div class="info-label">
                                Order ID
                            </div>

                            <div class="info-value">
                                #<?php
                                echo htmlspecialchars(
                                    $order['order_id']
                                );
                                ?>
                            </div>

                        </div>


                        <div class="info-box">

                            <div class="info-label">
                                Customer ID
                            </div>

                            <div class="info-value">
                                <?php
                                echo htmlspecialchars(
                                    $order['customer_id']
                                );
                                ?>
                            </div>

                        </div>


                        <div class="info-box">

                            <div class="info-label">
                                Payment Method
                            </div>

                            <div class="info-value">

                                <?php
                                echo htmlspecialchars(
                                    $order['payment_method']
                                );
                                ?>

                            </div>

                        </div>


                        <div class="info-box">

                            <div class="info-label">
                                Payment Status
                            </div>

                            <div class="info-value">

                                <?php

                                $paymentStatus =
                                    $order['payment_status'];

                                $paymentClass =
                                    'status-pending';

                                if (
                                    strtolower(
                                        $paymentStatus
                                    ) === 'paid'
                                ) {
                                    $paymentClass =
                                        'status-paid';
                                }

                                ?>

                                <span class="status-badge
                                    <?php
                                    echo $paymentClass;
                                    ?>">

                                    <?php
                                    echo htmlspecialchars(
                                        $paymentStatus
                                    );
                                    ?>

                                </span>

                            </div>

                        </div>

                    </div>


                    <!-- =================================
                         ORDER ITEMS
                    ================================== -->

                    <div class="items-title">

                        <i class="fas fa-box"></i>

                        Ordered Products

                    </div>


                    <div class="table-responsive">

                        <table class="items-table">

                            <thead>

                                <tr>

                                    <th>
                                        Product ID
                                    </th>

                                    <th>
                                        Product
                                    </th>

                                    <th>
                                        Quantity
                                    </th>

                                    <th>
                                        Price
                                    </th>

                                    <th>
                                        Subtotal
                                    </th>

                                </tr>

                            </thead>


                            <tbody>

                                <?php

                                $itemsForOrder =
                                    $orderItems[
                                        $order['order_id']
                                    ] ?? [];

                                ?>

                                <?php
                                if (
                                    !empty(
                                        $itemsForOrder
                                    )
                                ):
                                ?>

                                    <?php
                                    foreach (
                                        $itemsForOrder
                                        as $item
                                    ):
                                    ?>

                                        <tr>

                                            <td>

                                                #<?php
                                                echo htmlspecialchars(
                                                    $item[
                                                        'product_id'
                                                    ]
                                                );
                                                ?>

                                            </td>


                                            <td>

                                                <?php
                                                echo htmlspecialchars(
                                                    $item[
                                                        'product_name'
                                                    ]
                                                    ?? 'Product'
                                                );
                                                ?>

                                            </td>


                                            <td>

                                                <?php
                                                echo (int)
                                                    $item[
                                                        'quantity'
                                                    ];
                                                ?>

                                            </td>


                                            <td>

                                                ₹<?php
                                                echo number_format(
                                                    (float)
                                                    $item['price'],
                                                    2
                                                );
                                                ?>

                                            </td>


                                            <td>

                                                ₹<?php

                                                $subtotal =
                                                    (float)
                                                    $item['price']
                                                    *
                                                    (int)
                                                    $item['quantity'];

                                                echo number_format(
                                                    $subtotal,
                                                    2
                                                );

                                                ?>

                                            </td>

                                        </tr>

                                    <?php
                                    endforeach;
                                    ?>

                                <?php else: ?>

                                    <tr>

                                        <td
                                            colspan="5"
                                            class="text-center"
                                        >
                                            No order items found.
                                        </td>

                                    </tr>

                                <?php endif; ?>

                            </tbody>

                        </table>

                    </div>


                    <!-- =================================
                         TOTAL
                    ================================== -->

                    <div class="order-total">

                        Total Order Amount:

                        <strong>

                            ₹<?php
                            echo number_format(
                                (float)
                                $order['total_amount'],
                                2
                            );
                            ?>

                        </strong>

                    </div>


                </div>

            <?php endforeach; ?>


        <?php endif; ?>


    </div>

</div>


<?php

if (file_exists('includes/footer.php')) {
    include 'includes/footer.php';
}

?>
```
