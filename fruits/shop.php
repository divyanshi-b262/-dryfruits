<?php

// =====================================================
// DATABASE CONNECTION
// =====================================================

include 'includes/db.php';


// =====================================================
// SEARCH & FILTER
// =====================================================

$search = isset($_GET['search'])
    ? trim($_GET['search'])
    : '';

$category = isset($_GET['category'])
    ? intval($_GET['category'])
    : 0;


// =====================================================
// GET CATEGORIES
// =====================================================

$categoryStmt = $pdo->query("
    SELECT
        id,
        category_name
    FROM categories
    ORDER BY category_name ASC
");

$categories = $categoryStmt->fetchAll(PDO::FETCH_ASSOC);


// =====================================================
// GET PRODUCTS
// =====================================================

$sql = "
    SELECT
        p.*,
        c.category_name
    FROM products p

    INNER JOIN categories c
        ON p.category_id = c.id

    WHERE p.status = 'Active'
";

$params = [];


// =====================================================
// SEARCH
// =====================================================

if ($search !== '') {

    $sql .= "
        AND (
            p.product_name LIKE ?
            OR p.description LIKE ?
            OR c.category_name LIKE ?
        )
    ";

    $searchValue = '%' . $search . '%';

    $params[] = $searchValue;
    $params[] = $searchValue;
    $params[] = $searchValue;
}


// =====================================================
// CATEGORY
// =====================================================

if ($category > 0) {

    $sql .= "
        AND p.category_id = ?
    ";

    $params[] = $category;
}


// =====================================================
// ORDER
// =====================================================

$sql .= "
    ORDER BY p.created_at DESC
";


$stmt = $pdo->prepare($sql);

$stmt->execute($params);

$products = $stmt->fetchAll(PDO::FETCH_ASSOC);


// =====================================================
// HEADER
// =====================================================

include 'includes/header.php';

?>


<style>

/* =====================================================
   SHOP PAGE
===================================================== */

.shop-page {
    background: #FAF8F1;
    min-height: 100vh;
    padding-bottom: 70px;
}


/* =====================================================
   SHOP HEADER
===================================================== */

.shop-header {
    background:
        linear-gradient(
            135deg,
            #8B4513,
            #A0522D
        );

    padding: 85px 20px;

    text-align: center;

    color: white;

    margin-bottom: 35px;
}


.shop-header h1 {
    font-size: 42px;
    font-weight: 800;
    margin-bottom: 10px;
}


.shop-header p {
    margin: 0;
    color: #f5f5f5;
    font-size: 17px;
}


/* =====================================================
   SEARCH AREA
===================================================== */

.shop-search-box {
    background: white;

    padding: 20px;

    border-radius: 15px;

    box-shadow:
        0 5px 20px
        rgba(0,0,0,0.08);

    margin-bottom: 35px;
}


.search-input {
    height: 50px;

    border-radius: 10px 0 0 10px;

    border: 1px solid #ddd;

    padding-left: 18px;

    font-size: 15px;
}


.search-button {
    height: 50px;

    border: none;

    background: #8B4513;

    color: white;

    padding: 0 25px;

    border-radius: 0 10px 10px 0;

    font-weight: 600;
}


.search-button:hover {
    background: #FFD700;
}


/* =====================================================
   CATEGORY FILTER
===================================================== */

.category-filter {
    display: flex;

    gap: 10px;

    flex-wrap: wrap;

    margin-top: 15px;
}


.category-filter a {
    text-decoration: none;

    padding: 8px 17px;

    border-radius: 25px;

    background: #F3F0E5;

    color: #8B4513;

    font-size: 14px;

    font-weight: 600;

    transition: all .3s ease;
}


.category-filter a:hover,
.category-filter a.active {
    background: #8B4513;

    color: white;
}


/* =====================================================
   PRODUCT CARD
===================================================== */

.shop-product-card {
    background: white;

    border-radius: 18px;

    overflow: hidden;

    box-shadow:
        0 5px 20px
        rgba(0,0,0,0.08);

    transition:
        transform .3s ease,
        box-shadow .3s ease;

    margin-bottom: 25px;
}


.shop-product-card:hover {
    transform: translateY(-5px);

    box-shadow:
        0 15px 35px
        rgba(0,0,0,0.15);
}


/* =====================================================
   PRODUCT IMAGE
===================================================== */

.product-image-wrapper {
    height: 300px;

    background: #F8F5ED;

    overflow: hidden;

    position: relative;
}


.product-image-wrapper img {
    width: 100%;

    height: 100%;

    object-fit: cover;

    transition:
        transform .4s ease;
}


.shop-product-card:hover
.product-image-wrapper img {
    transform: scale(1.06);
}


/* =====================================================
   PRODUCT INFORMATION
===================================================== */

.product-information {
    padding: 30px;

    height: 100%;

    display: flex;

    flex-direction: column;

    justify-content: center;
}


/* =====================================================
   CATEGORY BADGE
===================================================== */

.product-category-badge {
    display: inline-block;

    width: fit-content;

    background: #8B4513;

    color: white;

    padding: 6px 14px;

    border-radius: 20px;

    font-size: 12px;

    font-weight: 600;

    margin-bottom: 12px;
}


/* =====================================================
   PRODUCT NAME
===================================================== */

.product-name {
    color: #8B4513;

    font-size: 28px;

    font-weight: 800;

    margin-bottom: 12px;
}


/* =====================================================
   PRODUCT DESCRIPTION
===================================================== */

.product-description {
    color: #666;

    font-size: 15px;

    line-height: 1.7;

    margin-bottom: 18px;
}


/* =====================================================
   PRODUCT PRICE
===================================================== */

.product-price {
    color: #FFD700;

    font-size: 28px;

    font-weight: 800;
}


/* =====================================================
   PRODUCT STOCK
===================================================== */

.product-stock {
    color: #6B3A2A;

    font-size: 13px;

    font-weight: 600;
}


/* =====================================================
   OUT OF STOCK
===================================================== */

.out-stock-badge {
    position: absolute;

    top: 15px;

    right: 15px;

    background: #8B0000;

    color: white;

    padding: 7px 14px;

    border-radius: 20px;

    font-size: 12px;

    font-weight: 600;
}


/* =====================================================
   ADD TO CART
===================================================== */

.add-cart-btn {
    width: 100%;

    border: none;

    background:
        linear-gradient(
            135deg,
            #8B4513,
            #6B3A2A
        );

    color: white;

    padding: 13px;

    border-radius: 10px;

    font-weight: 600;

    margin-top: 20px;

    transition: all .3s ease;
}


.add-cart-btn:hover {
    background: #FFD700;

    color: white;

    transform: translateY(-2px);
}


.add-cart-btn:disabled {
    background: #aaa;

    cursor: not-allowed;

    transform: none;
}


/* =====================================================
   EMPTY PRODUCTS
===================================================== */

.no-products {
    background: white;

    border-radius: 18px;

    padding: 70px 20px;

    text-align: center;

    box-shadow:
        0 5px 20px
        rgba(0,0,0,0.06);
}


.no-products i {
    font-size: 60px;

    color: #FFD700;

    margin-bottom: 20px;
}


.no-products h4 {
    color: #8B4513;

    font-weight: 700;
}


/* =====================================================
   MOBILE
===================================================== */

@media (max-width: 768px) {

    .shop-header {
        padding: 40px 15px;
    }

    .shop-header h1 {
        font-size: 30px;
    }

    .shop-header p {
        font-size: 14px;
    }

    .product-image-wrapper {
        height: 250px;
    }

    .product-information {
        padding: 20px;
    }

    .product-name {
        font-size: 22px;
    }

    .product-description {
        font-size: 14px;
    }

    .product-price {
        font-size: 23px;
    }

}


@media (max-width: 576px) {

    .search-input {
        border-radius: 10px;

        margin-bottom: 8px;
    }

    .search-button {
        width: 100%;

        border-radius: 10px;
    }

    .product-image-wrapper {
        height: 220px;
    }

}

</style>


<!-- =====================================================
     SHOP PAGE
===================================================== -->

<div class="shop-page">


    <!-- =================================================
         HEADER
    ================================================== -->

    <div class="shop-header">

        <div class="container">

            <h1>
                Our Dry Fruits
            </h1>

            <p>
                Premium quality dry fruits for a healthy lifestyle
            </p>

        </div>

    </div>


    <div class="container">


        <!-- =================================================
             SEARCH & FILTER
        ================================================== -->

        <div class="shop-search-box">

            <form
                method="GET"
                action="shop.php"
            >

                <div class="row g-2">


                    <!-- SEARCH -->

                    <div class="col-md-9">

                        <div class="input-group">

                            <input
                                type="text"
                                name="search"
                                class="form-control search-input"
                                placeholder="Search dry fruits..."
                                value="<?php
                                    echo htmlspecialchars(
                                        $search
                                    );
                                ?>"
                            >

                            <button
                                type="submit"
                                class="search-button"
                            >

                                <i class="fas fa-search me-2"></i>

                                Search

                            </button>

                        </div>

                    </div>


                    <!-- CATEGORY SELECT -->

                    <div class="col-md-3">

                        <select
                            name="category"
                            class="form-select"
                            style="
                                height:50px;
                                border-radius:10px;
                            "
                            onchange="this.form.submit()"
                        >

                            <option value="0">
                                All Categories
                            </option>


                            <?php foreach ($categories as $cat): ?>

                                <option
                                    value="<?php
                                        echo $cat['id'];
                                    ?>"
                                    <?php
                                    echo (
                                        $category ==
                                        $cat['id']
                                    )
                                        ? 'selected'
                                        : '';
                                    ?>
                                >

                                    <?php
                                    echo htmlspecialchars(
                                        $cat['category_name']
                                    );
                                    ?>

                                </option>

                            <?php endforeach; ?>

                        </select>

                    </div>

                </div>

            </form>


            <!-- CATEGORY BUTTONS -->

            <div class="category-filter">

                <a
                    href="shop.php"
                    class="<?php
                        echo $category == 0
                            ? 'active'
                            : '';
                    ?>"
                >
                    All
                </a>


                <?php foreach ($categories as $cat): ?>

                    <a
                        href="shop.php?category=<?php
                            echo $cat['id'];
                        ?><?php
                            echo $search !== ''
                                ? '&search=' .
                                  urlencode($search)
                                : '';
                        ?>"
                        class="<?php
                            echo $category ==
                                $cat['id']
                                ? 'active'
                                : '';
                        ?>"
                    >

                        <?php
                        echo htmlspecialchars(
                            $cat['category_name']
                        );
                        ?>

                    </a>

                <?php endforeach; ?>

            </div>

        </div>


        <!-- =================================================
             RESULT COUNT
        ================================================== -->

        <div
            class="
                d-flex
                justify-content-between
                align-items-center
                mb-4
            "
        >

            <h5
                class="fw-bold mb-0"
                style="color:#8B4513;"
            >

                <?php echo count($products); ?>

                Products Found

            </h5>


            <?php if ($search !== ''): ?>

                <span class="text-muted">

                    Search:

                    <strong>

                        <?php
                        echo htmlspecialchars($search);
                        ?>

                    </strong>

                </span>

            <?php endif; ?>

        </div>


        <!-- =================================================
             PRODUCTS
        ================================================== -->

        <?php if (!empty($products)): ?>


            <?php foreach ($products as $product): ?>


                <?php

                // =================================================
                // PRODUCT IMAGE
                // =================================================

                $productImage =
                    trim(
                        $product['image'] ?? ''
                    );


                if ($productImage === '') {

                    $productImage = 'default.jpg';

                } else {

                    $productImage =
                        basename($productImage);

                }


                $imagePath =
                    'assets/images/products/' .
                    $productImage;

                ?>


                <!-- PRODUCT -->

                <div class="shop-product-card">

                    <div class="row g-0 align-items-stretch">


                        <!-- =================================================
                             IMAGE LEFT
                        ================================================== -->

                        <div class="col-md-5">

                            <div
                                class="product-image-wrapper"
                            >

                                <!-- CATEGORY -->

                                <span
                                    class="
                                        product-category-badge
                                    "
                                >

                                    <?php
                                    echo htmlspecialchars(
                                        $product['category_name']
                                    );
                                    ?>

                                </span>


                                <!-- OUT OF STOCK -->

                                <?php if (
                                    (int)$product['stock'] <= 0
                                ): ?>

                                    <span
                                        class="
                                            out-stock-badge
                                        "
                                    >

                                        Out of Stock

                                    </span>

                                <?php endif; ?>


                                <!-- IMAGE -->

                                <img
                                    src="<?php
                                        echo htmlspecialchars(
                                            $imagePath
                                        );
                                    ?>"
                                    alt="<?php
                                        echo htmlspecialchars(
                                            $product['product_name']
                                        );
                                    ?>"
                                    onerror="
                                        this.src='assets/images/products/default.jpg';
                                    "
                                >

                            </div>

                        </div>


                        <!-- =================================================
                             INFORMATION RIGHT
                        ================================================== -->

                        <div class="col-md-7">

                            <div
                                class="
                                    product-information
                                "
                            >


                                <!-- PRODUCT NAME -->

                                <h2
                                    class="product-name"
                                >

                                    <?php
                                    echo htmlspecialchars(
                                        $product['product_name']
                                    );
                                    ?>

                                </h2>


                                <!-- DESCRIPTION -->

                                <p
                                    class="
                                        product-description
                                    "
                                >

                                    <?php

                                    $description =
                                        trim(
                                            $product['description']
                                            ?? ''
                                        );


                                    if ($description === '') {

                                        echo
                                            'Premium quality dry fruits, fresh and carefully selected for you.';

                                    } else {

                                        echo nl2br(
                                            htmlspecialchars(
                                                $description
                                            )
                                        );

                                    }

                                    ?>

                                </p>


                                <!-- PRICE + STOCK -->

                                <div
                                    class="
                                        d-flex
                                        justify-content-between
                                        align-items-center
                                        flex-wrap
                                        gap-2
                                    "
                                >

                                    <span
                                        class="
                                            product-price
                                        "
                                    >

                                        ₹<?php

                                        echo number_format(
                                            (float)
                                            $product['price'],
                                            2
                                        );

                                        ?>

                                    </span>


                                    <?php if (
                                        (int)$product['stock'] > 0
                                    ): ?>

                                        <span
                                            class="
                                                product-stock
                                            "
                                        >

                                            <i
                                                class="
                                                    fas
                                                    fa-check-circle
                                                    me-1
                                                "
                                            ></i>

                                            <?php

                                            echo (int)
                                                $product['stock'];

                                            ?>

                                            available

                                        </span>

                                    <?php endif; ?>

                                </div>


                                <!-- =================================================
                                     ADD TO CART
                                ================================================== -->

                                <?php if (
                                    (int)$product['stock'] > 0
                                ): ?>

                                    <form
                                        method="POST"
                                        action="cart.php"
                                    >

                                        <input
                                            type="hidden"
                                            name="product_id"
                                            value="<?php
                                                echo (int)
                                                $product['id'];
                                            ?>"
                                        >

                                        <input
                                            type="hidden"
                                            name="quantity"
                                            value="1"
                                        >


                                        <button
                                            type="submit"
                                            name="add_to_cart"
                                            class="add-cart-btn"
                                        >

                                            <i
                                                class="
                                                    fas
                                                    fa-shopping-cart
                                                    me-2
                                                "
                                            ></i>

                                            Add to Cart

                                        </button>

                                    </form>

                                <?php else: ?>

                                    <button
                                        type="button"
                                        class="add-cart-btn"
                                        disabled
                                    >

                                        <i
                                            class="
                                                fas
                                                fa-ban
                                                me-2
                                            "
                                        ></i>

                                        Out of Stock

                                    </button>

                                <?php endif; ?>


                            </div>

                        </div>

                    </div>

                </div>


            <?php endforeach; ?>


        <?php else: ?>


            <!-- =================================================
                 NO PRODUCTS
            ================================================== -->

            <div class="no-products">

                <i
                    class="
                        fas
                        fa-search
                    "
                ></i>


                <h4>
                    No Products Found
                </h4>


                <p class="text-muted">

                    We couldn't find any dry fruits
                    matching your search.

                </p>


                <a
                    href="shop.php"
                    class="btn btn-success"
                >

                    View All Products

                </a>

            </div>


        <?php endif; ?>


    </div>

</div>


<?php

include 'includes/footer.php';

?>