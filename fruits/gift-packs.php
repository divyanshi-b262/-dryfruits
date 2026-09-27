<?php 
session_start(); 
 
require_once 'includes/db.php'; 


/* 
|--------------------------------------------------------------------------
| ADD GIFT PACK TO CART
|--------------------------------------------------------------------------
*/

if (isset($_POST['add_gift_to_cart'])) {

    /* Customer must be logged in */
    if (!isset($_SESSION['customer_id'])) {

        header("Location: login.php");
        exit;
    }

    $customerId = (int)$_SESSION['customer_id'];
    $giftId = (int)($_POST['gift_pack_id'] ?? 0);

    if ($giftId <= 0) {

        header("Location: gift-packs.php");
        exit;
    }

    try {

        /*
        |--------------------------------------------------------------------------
        | Check Gift Pack
        |--------------------------------------------------------------------------
        */

        $stmt = $pdo->prepare("
            SELECT 
                id,
                stock,
                status
            FROM gift_packs
            WHERE id = ?
            LIMIT 1
        ");

        $stmt->execute([$giftId]);

        $giftPack = $stmt->fetch(PDO::FETCH_ASSOC);


        if (!$giftPack) {

            $_SESSION['gift_cart_error'] = "Gift pack not found.";

            header("Location: gift-packs.php");
            exit;
        }


        /*
        |--------------------------------------------------------------------------
        | Check Status
        |--------------------------------------------------------------------------
        */

        if ($giftPack['status'] !== 'Active') {

            $_SESSION['gift_cart_error'] = "This gift pack is not available.";

            header("Location: gift-packs.php");
            exit;
        }


        /*
        |--------------------------------------------------------------------------
        | Check Stock
        |--------------------------------------------------------------------------
        */

        if ((int)$giftPack['stock'] <= 0) {

            $_SESSION['gift_cart_error'] = "This gift pack is out of stock.";

            header("Location: gift-packs.php");
            exit;
        }


        /*
        |--------------------------------------------------------------------------
        | Check Whether Gift Pack Already Exists In Cart
        |--------------------------------------------------------------------------
        */

        $stmt = $pdo->prepare("
            SELECT 
                id,
                quantity
            FROM gift_cart
            WHERE customer_id = ?
            AND gift_pack_id = ?
            LIMIT 1
        ");

        $stmt->execute([
            $customerId,
            $giftId
        ]);

        $existingGift = $stmt->fetch(PDO::FETCH_ASSOC);


        /*
        |--------------------------------------------------------------------------
        | If Already In Cart
        |--------------------------------------------------------------------------
        */

        if ($existingGift) {

            $newQuantity = (int)$existingGift['quantity'] + 1;

            /*
            | Do not allow quantity greater than stock
            */

            if ($newQuantity > (int)$giftPack['stock']) {

                $newQuantity = (int)$giftPack['stock'];
            }


            $stmt = $pdo->prepare("
                UPDATE gift_cart
                SET quantity = ?
                WHERE id = ?
                AND customer_id = ?
            ");

            $stmt->execute([
                $newQuantity,
                $existingGift['id'],
                $customerId
            ]);

        } else {

            /*
            |--------------------------------------------------------------------------
            | Add New Gift Pack
            |--------------------------------------------------------------------------
            */

            $stmt = $pdo->prepare("
                INSERT INTO gift_cart
                (
                    customer_id,
                    gift_pack_id,
                    quantity
                )
                VALUES (?, ?, 1)
            ");

            $stmt->execute([
                $customerId,
                $giftId
            ]);
        }


        /*
        |--------------------------------------------------------------------------
        | Go To Cart
        |--------------------------------------------------------------------------
        */

        header("Location: cart.php");
        exit;


    } catch (PDOException $e) {

        $_SESSION['gift_cart_error'] =
            "Unable to add gift pack to cart.";

        header("Location: gift-packs.php");
        exit;
    }
}


/* 
|--------------------------------------------------------------------------
| Get Gift Packs
|--------------------------------------------------------------------------
| Gift packs are stored separately from normal products.
| Table: gift_packs
|--------------------------------------------------------------------------
*/

$giftPacks = []; 
$errorMessage = ''; 
 
try { 
 
    $stmt = $pdo->prepare(" 
        SELECT 
            id, 
            gift_name, 
            description, 
            price, 
            stock, 
            weight, 
            image, 
            status 
        FROM gift_packs 
        WHERE status = 'Active' 
        ORDER BY id DESC 
    "); 
 
    $stmt->execute(); 
 
    $giftPacks = $stmt->fetchAll(PDO::FETCH_ASSOC); 
 
} catch (PDOException $e) { 
 
    $giftPacks = []; 
    $errorMessage = "Unable to load gift packs."; 
}


/*
|--------------------------------------------------------------------------
| Show Add-To-Cart Error
|--------------------------------------------------------------------------
*/

if (isset($_SESSION['gift_cart_error'])) {

    $errorMessage = $_SESSION['gift_cart_error'];

    unset($_SESSION['gift_cart_error']);
}


/* 
|--------------------------------------------------------------------------
| Header 
|--------------------------------------------------------------------------
*/

include 'includes/header.php'; 
?> 
 
<style> 
 
/* ========================================================= 
   GIFT PACKS PAGE 
========================================================= */ 
 
body { 
    background-color: #fffaf5; 
} 
 
 
/* Main section */ 
 
.gift-packs-section { 
    padding: 110px 0 70px; 
} 
 
 
/* Heading */ 
 
.gift-heading { 
    text-align: center; 
    margin-bottom: 45px; 
} 
 
.gift-heading h1 { 
    color: #7b2d26; 
    font-size: 42px; 
    font-weight: 700; 
    margin-bottom: 10px; 
} 
 
.gift-heading h1 i { 
    color: #a0522d; 
    margin-right: 8px; 
} 
 
.gift-heading p { 
    color: #8b6f47; 
    font-size: 17px; 
    margin: 0; 
} 
 
 
/* Decorative line */ 
 
.gift-line { 
    width: 80px; 
    height: 4px; 
    background: #a0522d; 
    margin: 15px auto 0; 
    border-radius: 10px; 
} 
 
 
/* ========================================================= 
   GIFT CARD 
========================================================= */ 
 
.gift-card { 
    background: #ffffff; 
    border: 1px solid #ead8c8; 
    border-radius: 16px; 
    overflow: hidden; 
    height: 100%; 
    transition: all 0.3s ease; 
    box-shadow: 0 4px 15px rgba(123, 45, 38, 0.08); 
} 
 
.gift-card:hover { 
    transform: translateY(-7px); 
    box-shadow: 0 10px 28px rgba(123, 45, 38, 0.18); 
} 
 
 
/* Image */ 
 
.gift-image-wrapper { 
    height: 250px; 
    background: #f8eee6; 
    overflow: hidden; 
    position: relative; 
} 
 
.gift-image { 
    width: 100%; 
    height: 100%; 
    object-fit: cover; 
    transition: transform 0.4s ease; 
} 
 
.gift-card:hover .gift-image { 
    transform: scale(1.05); 
} 
 
 
/* No image */ 
 
.no-gift-image { 
    height: 100%; 
    display: flex; 
    align-items: center; 
    justify-content: center; 
    flex-direction: column; 
    color: #a0522d; 
} 
 
.no-gift-image i { 
    font-size: 55px; 
    margin-bottom: 10px; 
} 
 
 
/* Gift badge */ 
 
.gift-badge { 
    position: absolute; 
    top: 14px; 
    left: 14px; 
    background: #7b2d26; 
    color: #ffffff; 
    padding: 6px 13px; 
    border-radius: 20px; 
    font-size: 13px; 
    font-weight: 600; 
    z-index: 2; 
} 
 
 
/* ========================================================= 
   CARD CONTENT 
========================================================= */ 
 
.gift-card-body { 
    padding: 20px; 
} 
 
.gift-card-title { 
    color: #7b2d26; 
    font-size: 20px; 
    font-weight: 700; 
    margin-bottom: 8px; 
} 
 
.gift-description { 
    color: #777; 
    font-size: 14px; 
    line-height: 1.6; 
    min-height: 45px; 
    margin-bottom: 12px; 
} 
 
 
/* Weight */ 
 
.gift-weight { 
    color: #8b6f47; 
    font-size: 14px; 
    margin-bottom: 8px; 
} 
 
.gift-weight i { 
    color: #a0522d; 
    margin-right: 5px; 
} 
 
 
/* Price */ 
 
.gift-price { 
    color: #a0522d; 
    font-size: 22px; 
    font-weight: 700; 
    margin-bottom: 15px; 
} 
 
 
/* Stock */ 
 
.gift-stock { 
    font-size: 13px; 
    margin-bottom: 15px; 
} 
 
.in-stock { 
    color: #287a28; 
    font-weight: 600; 
} 
 
.out-stock { 
    color: #b02a37; 
    font-weight: 600; 
} 
 
 
/* Buttons */ 
 
.gift-buttons { 
    display: flex; 
    gap: 8px; 
} 
 
.btn-gift-view { 
    width: 100%; 
    background: #7b2d26; 
    border: 1px solid #7b2d26; 
    color: #ffffff; 
    border-radius: 8px; 
    padding: 10px 15px; 
    font-weight: 600; 
    text-decoration: none; 
    display: flex; 
    align-items: center; 
    justify-content: center; 
    transition: 0.3s; 
} 
 
.btn-gift-view:hover { 
    background: #a0522d; 
    border-color: #a0522d; 
    color: #ffffff; 
} 
 
 
/* ========================================================= 
   EMPTY STATE 
========================================================= */ 
 
.no-gifts { 
    text-align: center; 
    padding: 60px 20px; 
    background: #ffffff; 
    border-radius: 15px; 
    border: 1px solid #ead8c8; 
} 
 
.no-gifts i { 
    font-size: 60px; 
    color: #a0522d; 
    margin-bottom: 15px; 
} 
 
.no-gifts h3 { 
    color: #7b2d26; 
    font-weight: 700; 
} 
 
.no-gifts p { 
    color: #777; 
} 
 
 
/* Error */ 
 
.gift-error { 
    text-align: center; 
    padding: 20px; 
    background: #fff0ef; 
    border: 1px solid #e5b5b0; 
    color: #9b2d20; 
    border-radius: 10px; 
    margin-bottom: 30px; 
} 
 
 
/* ========================================================= 
   RESPONSIVE 
========================================================= */ 
 
@media (max-width: 768px) { 
 
    .gift-packs-section { 
        padding: 30px 0 50px; 
    } 
 
    .gift-heading h1 { 
        font-size: 32px; 
    } 
 
    .gift-heading p { 
        font-size: 15px; 
    } 
 
    .gift-image-wrapper { 
        height: 220px; 
    } 
} 
 
@media (max-width: 576px) { 
 
    .gift-heading { 
        margin-bottom: 30px; 
    } 
 
    .gift-heading h1 { 
        font-size: 28px; 
    } 
 
    .gift-image-wrapper { 
        height: 230px; 
    } 
 
    .gift-card-body { 
        padding: 16px; 
    } 
} 
 
</style> 
 
 
<!-- ========================================================= 
     GIFT PACKS SECTION 
========================================================= --> 
 
<section class="gift-packs-section"> 
 
    <div class="container"> 
 
        <!-- Heading --> 
 
        <div class="gift-heading"> 
 
            <h1> 
                <i class="fas fa-gift"></i> 
                GIFT PACKS 
            </h1> 
 
            <p> 
                Thoughtful gifts, packed with goodness. 
            </p> 
 
            <div class="gift-line"></div> 
 
        </div> 
 
 
        <!-- Database Error --> 
 
        <?php if ($errorMessage !== ''): ?> 
 
            <div class="gift-error"> 
 
                <i class="fas fa-exclamation-circle me-2"></i> 
 
                <?= htmlspecialchars($errorMessage) ?> 
 
            </div> 
 
        <?php endif; ?> 
 
 
        <?php if (!empty($giftPacks)): ?> 
 
            <div class="row g-4"> 
 
                <?php foreach ($giftPacks as $pack): ?> 
 
                    <?php 
 
                    /* 
                    |--------------------------------------------------------------------------
                    | Gift Pack Data 
                    |--------------------------------------------------------------------------
                    */ 
 
                    $giftId = (int)$pack['id']; 
 
                    $giftName = trim($pack['gift_name'] ?? ''); 
 
                    $image = trim($pack['image'] ?? ''); 
 
                    $description = trim($pack['description'] ?? ''); 
 
                    $weight = trim($pack['weight'] ?? ''); 
 
                    $price = (float)$pack['price']; 
 
                    $stock = (int)$pack['stock']; 
 
 
                    /* 
                    |--------------------------------------------------------------------------
                    | Image Path 
                    |--------------------------------------------------------------------------
                    */ 
 
                    $imagePath = ''; 
 
                    if ($image !== '') { 
 
                        $imagePath = 
                            'assets/images/gift-packs/' . 
                            basename($image); 
                    } 
 
 
                    /* 
                    |--------------------------------------------------------------------------
                    | Description 
                    |--------------------------------------------------------------------------
                    */ 
 
                    if ($description === '') { 
 
                        $description = 
                            'Premium quality dry fruits, beautifully packed for gifting.'; 
                    } 
 
 
                    if (strlen($description) > 100) { 
 
                        $description = 
                            substr($description, 0, 100) . '...'; 
                    } 
 
                    ?> 
 
 
                    <div class="col-lg-4 col-md-6 col-sm-6"> 
 
                        <div class="gift-card"> 
 
 
                            <!-- ================================================= 
                                 IMAGE 
                            ================================================== --> 
 
                            <div class="gift-image-wrapper"> 
 
                                <span class="gift-badge"> 
 
                                    <i class="fas fa-gift"></i> 
 
                                    Gift Pack 
 
                                </span> 
 
 
                                <?php if ( 
                                    !empty($imagePath) && 
                                    file_exists($imagePath) 
                                ): ?> 
 
                                    <img 
                                        src="<?= htmlspecialchars($imagePath) ?>" 
                                        alt="<?= htmlspecialchars($giftName) ?>" 
                                        class="gift-image" 
                                    > 
 
                                <?php else: ?> 
 
                                    <div class="no-gift-image"> 
 
                                        <i class="fas fa-gift"></i> 
 
                                        <span> 
                                            Gift Pack 
                                        </span> 
 
                                    </div> 
 
                                <?php endif; ?> 
 
                            </div> 
 
 
                            <!-- ================================================= 
                                 CONTENT 
                            ================================================== --> 
 
                            <div class="gift-card-body"> 
 
 
                                <!-- Name --> 
 
                                <h3 class="gift-card-title"> 
 
                                    <?= htmlspecialchars($giftName) ?> 
 
                                </h3> 
 
 
                                <!-- Description --> 
 
                                <p class="gift-description"> 
 
                                    <?= htmlspecialchars($description) ?> 
 
                                </p> 
 
 
                                <!-- Weight --> 
 
                                <?php if ($weight !== ''): ?> 
 
                                    <div class="gift-weight"> 
 
                                        <i class="fas fa-weight-hanging"></i> 
 
                                        <?= htmlspecialchars($weight) ?> 
 
                                    </div> 
 
                                <?php endif; ?> 
 
 
                                <!-- Price --> 
 
                                <div class="gift-price"> 
 
                                    ₹<?= number_format($price, 2) ?> 
 
                                </div> 
 
 
                                <!-- Stock --> 
 
                                <div class="gift-stock"> 
 
                                    <?php if ($stock > 0): ?> 
 
                                        <span class="in-stock"> 
 
                                            <i class="fas fa-check-circle"></i> 
 
                                            In Stock 
                                            (<?= $stock ?>) 
 
                                        </span> 
 
                                    <?php else: ?> 
 
                                        <span class="out-stock"> 
 
                                            <i class="fas fa-times-circle"></i> 
 
                                            Out of Stock 
 
                                        </span> 
 
                                    <?php endif; ?> 
 
                                </div> 
 
 
                                <!-- Button --> 
 
                                <div class="gift-buttons"> 
 
                                    <?php if ($stock > 0): ?> 
 
                                        <!-- 
                                        |--------------------------------------------------------------------------
                                        | REAL ADD TO CART FORM
                                        |--------------------------------------------------------------------------
                                        --> 
 
                                        <form 
                                            method="POST" 
                                            action="" 
                                            style="width:100%; margin:0;"
                                        > 
 
                                            <input 
                                                type="hidden" 
                                                name="gift_pack_id" 
                                                value="<?= $giftId ?>"
                                            > 
 
                                            <button 
                                                type="submit" 
                                                name="add_gift_to_cart" 
                                                class="btn-gift-view"
                                            > 
 
                                                <i class="fas fa-shopping-cart me-2"></i> 
 
                                                Add to Cart 
 
                                            </button> 
 
                                        </form> 
 
                                    <?php else: ?> 
 
                                        <button 
                                            type="button" 
                                            class="btn-gift-view"
                                            disabled
                                            style="opacity:0.6; cursor:not-allowed;"
                                        > 
 
                                            <i class="fas fa-times-circle me-2"></i> 
 
                                            Out of Stock 
 
                                        </button> 
 
                                    <?php endif; ?> 
 
                                </div> 
 
 
                            </div> 
 
                        </div> 
 
                    </div> 
 
 
                <?php endforeach; ?> 
 
            </div> 
 
 
        <?php else: ?> 
 
 
            <!-- ================================================= 
                 NO GIFT PACKS 
            ================================================== --> 
 
            <div class="no-gifts"> 
 
                <i class="fas fa-gift"></i> 
 
                <h3> 
                    Gift Packs Coming Soon 
                </h3> 
 
                <p> 
                    We are preparing beautiful dry fruit gift packs for you. 
                </p> 
 
            </div> 
 
 
        <?php endif; ?> 
 
 
    </div> 
 
</section> 
 
 
<?php 
 
/* 
|--------------------------------------------------------------------------
| Footer 
|--------------------------------------------------------------------------
*/ 
 
if (file_exists('includes/footer.php')) { 
 
    include 'includes/footer.php'; 
} 
 
?>