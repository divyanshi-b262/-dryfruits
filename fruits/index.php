<?php
include 'includes/db.php';
include 'includes/header.php';


/*
|--------------------------------------------------------------------------
| IMAGE PATH FUNCTION
|--------------------------------------------------------------------------
*/

function getImagePath($imageName)
{
    $imageName = basename($imageName);

    $basePath = 'images/';
    $extensions = ['webp', 'jpg', 'jpeg', 'png', 'gif'];

    if (empty($imageName)) {
        return 'default.jpg';
    }

    $pathInfo = pathinfo($imageName);

    if (
        isset($pathInfo['extension']) &&
        in_array(strtolower($pathInfo['extension']), $extensions)
    ) {

        if (file_exists($basePath . $imageName)) {
            return $imageName;
        }

        return 'default.jpg';
    }

    foreach ($extensions as $extension) {

        if (
            file_exists(
                $basePath . $imageName . '.' . $extension
            )
        ) {
            return $imageName . '.' . $extension;
        }
    }

    return 'default.jpg';
}


/*
|--------------------------------------------------------------------------
| CATEGORIES
|--------------------------------------------------------------------------
| CHANGED ONLY THIS SECTION
|
| Categories now come directly from the database.
| Admin can add/edit/delete categories and the frontend
| will automatically display the changes.
|--------------------------------------------------------------------------
*/

$categories = [];

try {

    $categoryStmt = $pdo->query(
        "SELECT
            id,
            category_name,
            category_image
         FROM categories
         ORDER BY category_name ASC"
    );

    $categories = $categoryStmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {

    $categories = [];

}


/*
|--------------------------------------------------------------------------
| FEATURED PRODUCTS
|--------------------------------------------------------------------------
*/

$products = [];

try {

    $products = $pdo->query(
        "SELECT
            p.*,
            c.category_name,
            s.shop_name
         FROM products p
         JOIN categories c
            ON p.category_id = c.id
         JOIN sellers s
            ON p.seller_id = s.id
         WHERE p.status = 'Active'
         ORDER BY p.created_at DESC
         LIMIT 8"
    )->fetchAll(PDO::FETCH_ASSOC);


} catch (PDOException $e) {

    $products = [];

}


/*
|--------------------------------------------------------------------------
| SLIDER
|--------------------------------------------------------------------------
*/

$sliders = [];

try {

    $sliders = $pdo->query(
        "SELECT * FROM slider"
    )->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {

    $sliders = [];

}


/*
|--------------------------------------------------------------------------
| DEFAULT SLIDER
|--------------------------------------------------------------------------
*/

if (count($sliders) == 0) {

    $sliders = [

        [
            'image' => 'alm 2',
            'title' => 'Premium Dry Fruits',
            'subtitle' =>
                'Fresh & Premium Quality Dry Fruits Delivered to Your Doorstep'
        ],

        [
            'image' => '31986.jpg',
            'title' => 'Healthy Snacking',
            'subtitle' =>
                'Nutritious and Delicious Dry Fruits for a Healthy Lifestyle'
        ],

        [
            'image' => '31990.jpg',
            'title' => 'Gift Packs Available',
            'subtitle' =>
                'Perfect Gifts for Your Loved Ones'
        ],

        [
            'image' => '31985.jpg',
            'title' => 'Organic & Natural',
            'subtitle' =>
                '100% Natural and Organic Dry Fruits'
        ]

    ];

}

?>


<style>

/*
|--------------------------------------------------------------------------
| SLIDER
|--------------------------------------------------------------------------
*/

.slider-section {

    margin-top: 0;

    position: relative;

}


.slider-section .carousel-item {

    height: 460px;

    position: relative;

}


.slider-section .carousel-item::before {

    content: '';

    position: absolute;

    top: 0;

    left: 0;

    right: 0;

    bottom: 0;

    background:
        linear-gradient(
            to right,
            rgba(0,0,0,0.6),
            rgba(0,0,0,0.2)
        );

    z-index: 1;

}


.slider-section .carousel-item img {

    height: 100%;

    width: 100%;

    object-fit: cover;

}


.slider-section .carousel-caption {

    z-index: 2;

    bottom: 20%;

    text-align: left;

    padding: 30px 40px;

    background: rgba(0,0,0,0.4);

    border-radius: 15px;

    backdrop-filter: blur(5px);

    max-width: 500px;

    left: 10%;

    animation:
        slideInLeft 1s ease;

}


@keyframes slideInLeft {

    from {

        opacity: 0;

        transform:
            translateX(-50px);

    }

    to {

        opacity: 1;

        transform:
            translateX(0);

    }

}


.slider-section .carousel-caption h2 {

    font-size: 2.5rem;

    font-weight: 800;

    color: #ffd700;

    text-shadow:
        2px 2px 4px rgba(0,0,0,0.5);

    margin-bottom: 10px;

}


.slider-section .carousel-caption p {

    font-size: 1.1rem;

    color: #fff;

    text-shadow:
        1px 1px 3px rgba(0,0,0,0.5);

    margin-bottom: 20px;

    line-height: 1.5;

}


.slider-section .carousel-caption .btn-shop {

    background:
        linear-gradient(
            135deg,
            #ffd700 0%,
            #f5a623 100%
        );

    color: #333;

    border: none;

    padding: 12px 30px;

    font-size: 1rem;

    font-weight: 700;

    border-radius: 50px;

    transition: all 0.3s ease;

    text-transform: uppercase;

    letter-spacing: 1px;

    box-shadow:
        0 4px 20px rgba(255,215,0,0.4);

}


.slider-section .carousel-caption .btn-shop:hover {

    transform:
        translateY(-3px) scale(1.05);

    box-shadow:
        0 8px 30px rgba(255,215,0,0.6);

}


/*
|--------------------------------------------------------------------------
| SLIDER INDICATORS
|--------------------------------------------------------------------------
*/

.slider-section .carousel-indicators {

    bottom: 15px;

    z-index: 3;

}


.slider-section .carousel-indicators button {

    width: 12px;

    height: 12px;

    border-radius: 50%;

    border: 2px solid #fff;

    background: transparent;

    margin: 0 6px;

}


.slider-section .carousel-indicators button.active {

    background: #ffd700;

    border-color: #ffd700;

    transform: scale(1.2);

}


/*
|--------------------------------------------------------------------------
| SLIDER CONTROLS
|--------------------------------------------------------------------------
*/

.slider-section .carousel-control-prev,
.slider-section .carousel-control-next {

    z-index: 3;

    width: 45px;

    height: 45px;

    top: 50%;

    transform:
        translateY(-50%);

    background:
        rgba(0,0,0,0.3);

    border-radius: 50%;

    opacity: 0;

}


.slider-section:hover
.carousel-control-prev,
.slider-section:hover
.carousel-control-next {

    opacity: 1;

}


/*
|--------------------------------------------------------------------------
| PREMIUM BADGE
|--------------------------------------------------------------------------
*/

.slider-badge {

    position: absolute;

    top: 15px;

    right: 15px;

    z-index: 3;

    background:
        rgba(255,215,0,0.9);

    color: #333;

    padding: 5px 15px;

    border-radius: 20px;

    font-weight: 700;

    font-size: 0.8rem;

}


/*
|--------------------------------------------------------------------------
| CATEGORY CARDS
|--------------------------------------------------------------------------
*/

.category-card {

    transition: all 0.3s ease;

    border-radius: 15px;

    overflow: hidden;

    background: #fff;

    cursor: pointer;

    height: 100%;

}


.category-card:hover {

    transform:
        translateY(-8px);

    box-shadow:
        0 10px 25px
        rgba(0,0,0,0.15)
        !important;

}


.category-image {

    width: 120px;

    height: 120px;

    object-fit: cover;

    border: 4px solid #f5a623;

    transition: all 0.3s ease;

}


.category-card:hover
.category-image {

    transform: scale(1.08);

    border-color: #ffd700;

}


.category-card .card-title {

    color: #333;

    font-weight: 700;

    margin-bottom: 0;

}


/*
|--------------------------------------------------------------------------
| PRODUCT CARDS
|--------------------------------------------------------------------------
*/

.product-card {

    transition: all 0.3s ease;

    border-radius: 12px;

    overflow: hidden;

}


.product-card:hover {

    transform:
        translateY(-5px);

    box-shadow:
        0 10px 25px
        rgba(0,0,0,0.12)
        !important;

}


.product-card img {

    transition:
        transform 0.3s ease;

}


.product-card:hover img {

    transform: scale(1.05);

}


/*
|--------------------------------------------------------------------------
| RESPONSIVE
|--------------------------------------------------------------------------
*/

@media (max-width: 992px) {

    .slider-section
    .carousel-item {

        height: 280px;

    }


    .slider-section
    .carousel-caption {

        bottom: 15%;

        padding: 20px 30px;

        max-width: 400px;

        left: 5%;

    }


    .slider-section
    .carousel-caption h2 {

        font-size: 2rem;

    }

}


@media (max-width: 768px) {

    .slider-section
    .carousel-item {

        height: 220px;

    }


    .slider-section
    .carousel-caption {

        bottom: 10%;

        padding: 15px 20px;

        max-width: 90%;

        left: 5%;

        text-align: center;

    }


    .slider-section
    .carousel-caption h2 {

        font-size: 1.5rem;

    }


    .slider-section
    .carousel-caption p {

        font-size: 0.8rem;

    }


    .slider-section
    .carousel-control-prev,
    .slider-section
    .carousel-control-next {

        display: none;

    }


    .slider-badge {

        display: none;

    }

}


@media (max-width: 576px) {

    .slider-section
    .carousel-item {

        height: 180px;

    }


    .slider-section
    .carousel-caption {

        padding: 10px 15px;

    }


    .slider-section
    .carousel-caption h2 {

        font-size: 1.2rem;

    }


    .slider-section
    .carousel-caption p {

        font-size: 0.7rem;

        margin-bottom: 8px;

    }


    .slider-section
    .carousel-caption .btn-shop {

        padding: 6px 15px;

        font-size: 0.7rem;

    }


    .category-image {

        width: 100px;

        height: 100px;

    }

}

</style>


<!-- =========================================================
     SLIDER
========================================================= -->

<section class="slider-section">

    <div
        id="mainSlider"
        class="carousel slide"
        data-bs-ride="carousel"
        data-bs-interval="3000"
    >


        <!-- Indicators -->

        <div class="carousel-indicators">

            <?php foreach (
                $sliders as $index => $slide
            ): ?>

                <button
                    type="button"
                    data-bs-target="#mainSlider"
                    data-bs-slide-to="<?php
                    echo $index;
                    ?>"
                    class="<?php
                    echo $index == 0
                        ? 'active'
                        : '';
                    ?>"
                ></button>

            <?php endforeach; ?>

        </div>


        <!-- Slides -->

        <div class="carousel-inner">

            <?php foreach (
                $sliders as $index => $slide
            ): ?>

                <?php

                $imageName =
                    str_replace(
                        ['images/', 'images\\'],
                        '',
                        $slide['image']
                    );

                $imagePath =
                    getImagePath($imageName);

                ?>

                <div
                    class="carousel-item
                    <?php
                    echo $index == 0
                        ? 'active'
                        : '';
                    ?>"
                >

                    <img
                        src="images/<?php
                        echo htmlspecialchars(
                            $imagePath
                        );
                        ?>"
                        alt="<?php
                        echo htmlspecialchars(
                            $slide['title']
                        );
                        ?>"
                    >


                    <div
                        class="carousel-caption d-md-block"
                    >

                        <h2>

                            <?php
                            echo htmlspecialchars(
                                $slide['title']
                            );
                            ?>

                        </h2>


                        <p>

                            <?php
                            echo htmlspecialchars(
                                $slide['subtitle']
                            );
                            ?>

                        </p>


                        <a
                            href="shop.php"
                            class="btn btn-shop"
                        >

                            <i
                                class="fas fa-shopping-bag me-2"
                            ></i>

                            Shop Now

                        </a>

                    </div>

                </div>

            <?php endforeach; ?>

        </div>


        <!-- Previous -->

        <button
            class="carousel-control-prev"
            type="button"
            data-bs-target="#mainSlider"
            data-bs-slide="prev"
        >

            <span
                class="carousel-control-prev-icon"
            ></span>

        </button>


        <!-- Next -->

        <button
            class="carousel-control-next"
            type="button"
            data-bs-target="#mainSlider"
            data-bs-slide="next"
        >

            <span
                class="carousel-control-next-icon"
            ></span>

        </button>


        <!-- Badge -->

        <div class="slider-badge">

            <i
                class="fas fa-star me-1"
            ></i>

            Premium Quality

        </div>

    </div>

</section>


<!-- =========================================================
     DYNAMIC CATEGORIES
========================================================= -->

<section class="py-5">

    <div class="container">


        <h2
            class="text-center mb-5 fw-bold"
            style="color:#667eea;"
        >

            Shop by Category

        </h2>


        <div
            class="row g-4 justify-content-center"
        >


            <?php if (!empty($categories)): ?>


                <?php foreach (
                    $categories as $category
                ): ?>


                    <div
                        class="col-6 col-md-4 col-lg-3 col-xl-2"
                    >


                        <a
                            href="shop.php?category=<?php
                            echo urlencode(
                                $category['id']
                            );
                            ?>"
                            class="text-decoration-none"
                        >


                            <div
                                class="
                                    card
                                    border-0
                                    shadow-sm
                                    text-center
                                    category-card
                                "
                            >


                                <!-- CATEGORY IMAGE -->

                                <?php if (
                                    !empty(
                                        $category['category_image']
                                    )
                                ): ?>

                                    <img
                                        src="assets/images/categories/<?php
                                        echo htmlspecialchars(
                                            basename(
                                                $category[
                                                    'category_image'
                                                ]
                                            )
                                        );
                                        ?>"
                                        class="
                                            rounded-circle
                                            mx-auto
                                            mt-3
                                            category-image
                                        "
                                        alt="<?php
                                        echo htmlspecialchars(
                                            $category[
                                                'category_name'
                                            ]
                                        );
                                        ?>"
                                        onerror="
                                            this.src='images/default.jpg';
                                        "
                                    >

                                <?php else: ?>

                                    <img
                                        src="images/default.jpg"
                                        class="
                                            rounded-circle
                                            mx-auto
                                            mt-3
                                            category-image
                                        "
                                        alt="<?php
                                        echo htmlspecialchars(
                                            $category[
                                                'category_name'
                                            ]
                                        );
                                        ?>"
                                    >

                                <?php endif; ?>


                                <div
                                    class="card-body"
                                >

                                    <h6
                                        class="card-title"
                                    >

                                        <?php
                                        echo htmlspecialchars(
                                            $category[
                                                'category_name'
                                            ]
                                        );
                                        ?>

                                    </h6>

                                </div>


                            </div>

                        </a>

                    </div>


                <?php endforeach; ?>


            <?php else: ?>


                <div
                    class="col-12 text-center"
                >

                    <p class="text-muted">

                        No categories available.

                    </p>

                </div>


            <?php endif; ?>


        </div>

    </div>

</section>


<!-- =========================================================
     FEATURED PRODUCTS
========================================================= 

<section class="py-5 bg-light">

    <div class="container">


        <div
            class="
                d-flex
                justify-content-between
                align-items-center
                mb-4
            "
        >

            <h2
                class="fw-bold"
                style="color:#667eea;"
            >

                Featured Products

            </h2>


            <a
                href="shop.php"
                class="btn btn-outline-dark"
            >

                View All →

            </a>

        </div>


        <div class="row g-4">


            <?php if (
                count($products) > 0
            ): ?>


                <?php foreach (
                    $products as $product
                ): ?>


                    <div
                        class="col-6 col-md-4 col-lg-3"
                    >


                        <div
                            class="
                                card
                                product-card
                                h-100
                                shadow-sm
                            "
                        >


                            <?php

                            $prodImage =
                                $product['image']
                                ?: 'default.jpg';

                            $prodImage =
                                str_replace(
                                    [
                                        'images/',
                                        'images\\'
                                    ],
                                    '',
                                    $prodImage
                                );

                            ?>


                            <img
                                src="images/<?php
                                echo htmlspecialchars(
                                    getImagePath(
                                        $prodImage
                                    )
                                );
                                ?>"
                                class="
                                    card-img-top
                                    p-3
                                "
                                alt="<?php
                                echo htmlspecialchars(
                                    $product[
                                        'product_name'
                                    ]
                                );
                                ?>"
                                style="
                                    height:200px;
                                    object-fit:contain;
                                "
                            >


                            <div
                                class="card-body"
                            >

                                <h6
                                    class="card-title"
                                >

                                    <?php
                                    echo htmlspecialchars(
                                        $product[
                                            'product_name'
                                        ]
                                    );
                                    ?>

                                </h6>


                                <small
                                    class="
                                        text-muted
                                        d-block
                                    "
                                >

                                    <?php
                                    echo htmlspecialchars(
                                        $product[
                                            'category_name'
                                        ]
                                    );
                                    ?>

                                </small>


                                <small
                                    class="text-muted"
                                >

                                    <?php
                                    echo htmlspecialchars(
                                        $product[
                                            'shop_name'
                                        ]
                                    );
                                    ?>

                                </small>


                                <div
                                    class="
                                        d-flex
                                        justify-content-between
                                        align-items-center
                                        mt-2
                                    "
                                >

                                    <span
                                        class="
                                            fw-bold
                                            text-warning
                                        "
                                    >

                                        PKR

                                        <?php
                                        echo number_format(
                                            $product[
                                                'price'
                                            ],
                                            2
                                        );
                                        ?>

                                    </span>


                                    <a
                                        href="product.php?id=<?php
                                        echo urlencode(
                                            $product['id']
                                        );
                                        ?>"
                                        class="
                                            btn
                                            btn-sm
                                            btn-dark
                                        "
                                    >

                                        View

                                    </a>

                                </div>


                            </div>

                        </div>

                    </div>


                <?php endforeach; ?>


            <?php else: ?>


                <div
                    class="col-12 text-center"
                >

                    <p class="text-muted">

                        No products available.

                    </p>

                </div>


            <?php endif; ?>


        </div>

    </div>

</section>


<!-- =========================================================
     BENEFITS
========================================================= 

<section class="py-5">

    <div class="container">

        <div
            class="row g-4 text-center"
        >


            <div class="col-md-3">

                <i
                    class="
                        fas
                        fa-truck
                        fa-3x
                        text-warning
                        mb-3
                    "
                ></i>

                <h6>
                    Free Delivery
                </h6>

                <small
                    class="text-muted"
                >

                    On orders above PKR 2000

                </small>

            </div>


            <div class="col-md-3">

                <i
                    class="
                        fas
                        fa-award
                        fa-3x
                        text-warning
                        mb-3
                    "
                ></i>

                <h6>
                    Premium Quality
                </h6>

                <small
                    class="text-muted"
                >

                    100% genuine products

                </small>

            </div>


            <div class="col-md-3">

                <i
                    class="
                        fas
                        fa-undo
                        fa-3x
                        text-warning
                        mb-3
                    "
                ></i>

                <h6>
                    Easy Returns
                </h6>

                <small
                    class="text-muted"
                >

                    7 days return policy

                </small>

            </div>


            <div class="col-md-3">

                <i
                    class="
                        fas
                        fa-headset
                        fa-3x
                        text-warning
                        mb-3
                    "
                ></i>

                <h6>
                    24/7 Support
                </h6>

                <small
                    class="text-muted"
                >

                    Dedicated customer service

                </small>

            </div>


        </div>

    </div>

</section>-->


<?php

include 'includes/footer.php';

?>