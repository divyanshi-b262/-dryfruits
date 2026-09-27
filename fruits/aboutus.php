<?php
// aboutus.php

include 'includes/header.php';
?>

<style>

    /* =========================
       ABOUT PAGE
    ========================== */

    .about-page {
        background-color: #fffaf5;
        min-height: 100vh;
    }

    .about-hero {
        background: linear-gradient(
            135deg,
            #6b2d1a,
            #8b4513
        );

        color: white;
        padding: 70px 20px;
        text-align: center;
    }

    .about-hero h1 {
        font-size: 46px;
        font-weight: bold;
        color: #f4c542;
        margin-bottom: 15px;
    }

    .about-hero p {
        font-size: 18px;
        max-width: 750px;
        margin: auto;
        line-height: 1.7;
    }

    .about-section {
        padding: 60px 20px;
    }

    .about-title {
        text-align: center;
        color: #6b2d1a;
        font-weight: bold;
        font-size: 32px;
        margin-bottom: 18px;
    }

    .about-title-line {
        width: 70px;
        height: 4px;
        background-color: #f4c542;
        margin: 0 auto 35px;
        border-radius: 10px;
    }

    .about-text {
        color: #5d4037;
        font-size: 16px;
        line-height: 1.8;
    }

    .about-card {
        background-color: white;
        border-radius: 12px;
        padding: 30px 25px;
        height: 100%;
        box-shadow: 0 5px 20px rgba(107,45,26,0.10);
        border-top: 4px solid #f4c542;
        transition: 0.3s;
    }

    .about-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(107,45,26,0.18);
    }

    .about-card i {
        font-size: 38px;
        color: #8b4513;
        margin-bottom: 18px;
    }

    .about-card h3 {
        color: #6b2d1a;
        font-size: 22px;
        font-weight: bold;
        margin-bottom: 12px;
    }

    .about-card p {
        color: #6d4c41;
        line-height: 1.7;
        margin-bottom: 0;
    }

    .mission-box {
        background: linear-gradient(
            135deg,
            #fff0d6,
            #fffaf5
        );

        border-left: 5px solid #8b4513;
        border-radius: 10px;
        padding: 30px;
        margin-top: 20px;
    }

    .mission-box h3 {
        color: #6b2d1a;
        font-weight: bold;
        margin-bottom: 15px;
    }

    .contact-box {
        background: linear-gradient(
            135deg,
            #6b2d1a,
            #8b4513
        );

        color: white;
        border-radius: 15px;
        padding: 45px 25px;
        text-align: center;
        margin-bottom: 50px;
    }

    .contact-box h2 {
        color: #f4c542;
        font-weight: bold;
        margin-bottom: 15px;
    }

    .contact-box p {
        font-size: 17px;
        line-height: 1.7;
    }

    .contact-box .btn {
        background-color: #f4c542;
        color: #4e260e;
        border: none;
        padding: 11px 25px;
        font-weight: bold;
        border-radius: 6px;
        margin-top: 10px;
    }

    .contact-box .btn:hover {
        background-color: #ffd95a;
        color: #4e260e;
    }

    @media (max-width: 768px) {

        .about-hero {
            padding: 50px 15px;
        }

        .about-hero h1 {
            font-size: 34px;
        }

        .about-hero p {
            font-size: 16px;
        }

        .about-section {
            padding: 40px 15px;
        }

        .about-title {
            font-size: 27px;
        }

    }

</style>


<div class="about-page">

    <!-- =========================
         HERO
    ========================== -->

    <section class="about-hero">

        <div class="container">

            <h1>
                About Tilawat Dry Fruits
            </h1>

            <p>
                Welcome to Tilawat Dry Fruits, your trusted place
                for quality dry fruits, healthy snacks and beautiful
                gift packs for every occasion.
            </p>

        </div>

    </section>


    <!-- =========================
         WHO WE ARE
    ========================== -->

    <section class="about-section">

        <div class="container">

            <h2 class="about-title">
                Who We Are
            </h2>

            <div class="about-title-line"></div>

            <div class="row justify-content-center">

                <div class="col-lg-9">

                    <p class="about-text text-center">
                        Tilawat Dry Fruits is dedicated to providing
                        customers with quality dry fruits and carefully
                        selected products. We aim to make it easy for
                        customers to find healthy products for everyday
                        use as well as special occasions.
                    </p>

                    <p class="about-text text-center">
                        From everyday dry fruits to specially prepared
                        gift packs, our goal is to provide products
                        that combine quality, value and convenience.
                    </p>

                </div>

            </div>

        </div>

    </section>


    <!-- =========================
         WHY CHOOSE US
    ========================== -->

    <section class="about-section pt-0">

        <div class="container">

            <h2 class="about-title">
                Why Choose Us
            </h2>

            <div class="about-title-line"></div>

            <div class="row g-4">

                <!-- QUALITY -->
                <div class="col-md-4">

                    <div class="about-card text-center">

                        <i class="fas fa-medal"></i>

                        <h3>
                            Quality Products
                        </h3>

                        <p>
                            We focus on providing quality dry fruits
                            and products for our customers.
                        </p>

                    </div>

                </div>


                <!-- VARIETY -->
                <div class="col-md-4">

                    <div class="about-card text-center">

                        <i class="fas fa-box-open"></i>

                        <h3>
                            Great Variety
                        </h3>

                        <p>
                            Explore a variety of dry fruits and gift
                            packs suitable for different needs and occasions.
                        </p>

                    </div>

                </div>


                <!-- TRUST -->
                <div class="col-md-4">

                    <div class="about-card text-center">

                        <i class="fas fa-heart"></i>

                        <h3>
                            Customer Trust
                        </h3>

                        <p>
                            We believe that customer satisfaction and
                            trust are an important part of our success.
                        </p>

                    </div>

                </div>

            </div>

        </div>

    </section>


    <!-- =========================
         OUR MISSION
    ========================== -->

    <section class="about-section pt-0">

        <div class="container">

            <h2 class="about-title">
                Our Mission
            </h2>

            <div class="about-title-line"></div>

            <div class="mission-box">

                <h3>
                    Quality, Trust and Convenience
                </h3>

                <p class="about-text mb-0">
                    Our mission is to make quality dry fruits and
                    gift packs easily accessible to our customers.
                    We want every customer to have a simple,
                    convenient and pleasant shopping experience
                    with Tilawat Dry Fruits.
                </p>

            </div>

        </div>

    </section>


    <!-- =========================
         CONTACT
    ========================== -->

    <section class="about-section pt-0">

        <div class="container">

            <div class="contact-box">

                <h2>
                    We'd Love to Hear From You
                </h2>

                <p>
                    Have a question about our products, gift packs
                    or orders? Feel free to get in touch with us.
                </p>

                <a
                    href="contact.php"
                    class="btn"
                >
                    <i class="fas fa-envelope"></i>
                    Contact Us
                </a>

            </div>

        </div>

    </section>

</div>


<?php
include 'includes/footer.php';
?>