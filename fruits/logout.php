
<?php
session_start();

/* Logout user */
$_SESSION = [];

/* Remove session cookie */
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();

    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

/* Destroy session */
session_destroy();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Logout - Tilawat Dry Fruits</title>

    <!-- Bootstrap -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <!-- Font Awesome -->
    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"
    >

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            background: #fffaf7;
            font-family: Arial, sans-serif;

            display: flex;
            align-items: center;
            justify-content: center;
        }

        .logout-container {
            width: 450px;
            max-width: 90%;
            background: white;

            padding: 45px 35px;
            text-align: center;

            border-radius: 16px;

            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);

            border-top: 6px solid #7b2d26;
        }

        .logout-icon {
            width: 80px;
            height: 80px;

            margin: 0 auto 22px;

            background: #7b2d26;
            color: white;

            border-radius: 50%;

            display: flex;
            align-items: center;
            justify-content: center;

            font-size: 32px;
        }

        .logout-container h2 {
            color: #7b2d26;
            font-size: 28px;
            font-weight: 700;

            margin-bottom: 12px;
        }

        .logout-container p {
            color: #666;
            font-size: 16px;
            margin-bottom: 28px;
        }

        .home-btn {
            display: inline-block;

            background: #7b2d26;
            color: white;

            padding: 12px 28px;

            border-radius: 8px;

            text-decoration: none;
            font-weight: 600;

            transition: 0.3s;
        }

        .home-btn:hover {
            background: #5e211c;
            color: white;
        }

        .home-btn i {
            margin-right: 7px;
        }
    </style>
</head>

<body>

    <div class="logout-container">

        <div class="logout-icon">
            <i class="fas fa-right-from-bracket"></i>
        </div>

        <h2>Logged Out Successfully</h2>

        <p>
            You have been successfully logged out of
            <strong>Tilawat Dry Fruits</strong>.
        </p>

        <a href="index.php" class="home-btn">
            <i class="fas fa-home"></i>
            Back to Home
        </a>

    </div>

</body>
</html>
```
