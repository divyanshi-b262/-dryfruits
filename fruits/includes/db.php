<?php

// =====================================================
// DATABASE CONNECTION
// =====================================================

$host = 'localhost';
$dbname = 'tilawat_dry_fruits';
$username = 'root';
$password = '';

try {

    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password
    );

    $pdo->setAttribute(
        PDO::ATTR_ERRMODE,
        PDO::ERRMODE_EXCEPTION
    );

    $pdo->setAttribute(
        PDO::ATTR_DEFAULT_FETCH_MODE,
        PDO::FETCH_ASSOC
    );

} catch (PDOException $e) {

    die(
        "Connection failed: " .
        $e->getMessage()
    );

}


// =====================================================
// START SESSION
// =====================================================

if (session_status() === PHP_SESSION_NONE) {

    session_start();

}


// =====================================================
// BASE URL
// =====================================================

$base_url =
    'http://localhost/tilawat_dry_fruits/';


// =====================================================
// CART COUNT
// =====================================================

if (!function_exists('getCartCount')) {

    function getCartCount()
    {
        global $pdo;


        // Customer not logged in
        if (
            !isset($_SESSION['customer_id'])
        ) {

            return 0;

        }


        $customer_id =
            (int) $_SESSION['customer_id'];


        try {

            $stmt = $pdo->prepare("
                SELECT COALESCE(
                    SUM(quantity),
                    0
                )
                FROM cart
                WHERE customer_id = ?
            ");

            $stmt->execute([
                $customer_id
            ]);


            return (int) $stmt->fetchColumn();


        } catch (PDOException $e) {

            return 0;

        }
    }

}


// =====================================================
// WISHLIST COUNT
// =====================================================

if (!function_exists('getWishlistCount')) {

    function getWishlistCount()
    {
        global $pdo;


        // Customer not logged in
        if (
            !isset($_SESSION['customer_id'])
        ) {

            return 0;

        }


        $customer_id =
            (int) $_SESSION['customer_id'];


        try {

            $stmt = $pdo->prepare("
                SELECT COUNT(*)
                FROM wishlist
                WHERE customer_id = ?
            ");

            $stmt->execute([
                $customer_id
            ]);


            return (int) $stmt->fetchColumn();


        } catch (PDOException $e) {

            return 0;

        }
    }

}

?>