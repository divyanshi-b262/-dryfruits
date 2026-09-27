```php
<?php

session_start();

include '../includes/db.php';


/* =====================================================
   UPLOAD DIRECTORY
===================================================== */

$uploadDir = '../images/';

$message = '';
$error = '';



/* =====================================================
   ADD / UPDATE SLIDER
===================================================== */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $sliderId = isset($_POST['slider_id'])
        ? (int)$_POST['slider_id']
        : 0;

    $title = trim($_POST['title'] ?? '');

    $subtitle = trim($_POST['subtitle'] ?? '');

    $imageName = trim($_POST['old_image'] ?? '');


    /* ---------------------------------------------
       VALIDATION
    --------------------------------------------- */

    if ($title === '') {

        $error = 'Please enter a slider title.';

    } elseif ($subtitle === '') {

        $error = 'Please enter a slider subtitle.';

    }


    /* ---------------------------------------------
       IMAGE UPLOAD
    --------------------------------------------- */

    if (
        $error === '' &&
        isset($_FILES['image']) &&
        $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE
    ) {

        $file = $_FILES['image'];

        $allowedTypes = [
            'jpg',
            'jpeg',
            'png',
            'webp',
            'gif'
        ];

        $extension = strtolower(
            pathinfo(
                $file['name'],
                PATHINFO_EXTENSION
            )
        );


        if (!in_array($extension, $allowedTypes)) {

            $error =
                'Only JPG, JPEG, PNG, WEBP and GIF images are allowed.';

        } elseif ($file['size'] > 5 * 1024 * 1024) {

            $error =
                'Image size must be less than 5 MB.';

        } else {

            /*
            Generate unique image name
            */

            $newImageName =
                'slider_' .
                time() .
                '_' .
                uniqid() .
                '.' .
                $extension;


            $destination =
                $uploadDir .
                $newImageName;


            if (
                move_uploaded_file(
                    $file['tmp_name'],
                    $destination
                )
            ) {

                /*
                Delete old image when updating
                */

                if (
                    $sliderId > 0 &&
                    !empty($imageName)
                ) {

                    $oldImage =
                        basename($imageName);

                    $oldImagePath =
                        $uploadDir .
                        $oldImage;

                    if (
                        file_exists(
                            $oldImagePath
                        )
                    ) {

                        @unlink(
                            $oldImagePath
                        );

                    }

                }


                $imageName =
                    $newImageName;

            } else {

                $error =
                    'Unable to upload the image.';

            }

        }

    }


    /* ---------------------------------------------
       SAVE TO DATABASE
    --------------------------------------------- */

    if ($error === '') {

        try {

            if ($sliderId > 0) {

                /*
                UPDATE EXISTING SLIDER
                */

                $stmt = $pdo->prepare("
                    UPDATE slider
                    SET
                        image = ?,
                        title = ?,
                        subtitle = ?
                    WHERE id = ?
                ");

                $stmt->execute([
                    $imageName,
                    $title,
                    $subtitle,
                    $sliderId
                ]);

                $message =
                    'Slider updated successfully.';

            } else {

                /*
                ADD NEW SLIDER
                */

                $stmt = $pdo->prepare("
                    INSERT INTO slider
                    (
                        image,
                        title,
                        subtitle
                    )
                    VALUES
                    (
                        ?,
                        ?,
                        ?
                    )
                ");

                $stmt->execute([
                    $imageName,
                    $title,
                    $subtitle
                ]);

                $message =
                    'Slider added successfully.';

            }

        } catch (PDOException $e) {

            $error =
                'Database error: ' .
                $e->getMessage();

        }

    }

}



/* =====================================================
   DELETE SLIDER
===================================================== */

if (
    isset($_GET['delete']) &&
    is_numeric($_GET['delete'])
) {

    $deleteId =
        (int)$_GET['delete'];


    try {

        /*
        Get image name first
        */

        $stmt = $pdo->prepare("
            SELECT image
            FROM slider
            WHERE id = ?
            LIMIT 1
        ");

        $stmt->execute([
            $deleteId
        ]);

        $slider =
            $stmt->fetch(PDO::FETCH_ASSOC);


        /*
        Delete database record
        */

        $stmt = $pdo->prepare("
            DELETE FROM slider
            WHERE id = ?
        ");

        $stmt->execute([
            $deleteId
        ]);


        /*
        Delete image file
        */

        if (
            $slider &&
            !empty($slider['image'])
        ) {

            $imagePath =
                $uploadDir .
                basename(
                    $slider['image']
                );

            if (
                file_exists(
                    $imagePath
                )
            ) {

                @unlink(
                    $imagePath
                );

            }

        }


        $message =
            'Slider deleted successfully.';


    } catch (PDOException $e) {

        $error =
            'Unable to delete slider.';

    }

}



/* =====================================================
   GET SLIDERS
===================================================== */

$stmt = $pdo->query("
    SELECT
        id,
        image,
        title,
        subtitle
    FROM slider
    ORDER BY id ASC
");

$sliders =
    $stmt->fetchAll(
        PDO::FETCH_ASSOC
    );



/* =====================================================
   EDIT SLIDER
===================================================== */

$editSlider = null;

if (
    isset($_GET['edit']) &&
    is_numeric($_GET['edit'])
) {

    $editId =
        (int)$_GET['edit'];

    $stmt = $pdo->prepare("
        SELECT
            id,
            image,
            title,
            subtitle
        FROM slider
        WHERE id = ?
        LIMIT 1
    ");

    $stmt->execute([
        $editId
    ]);

    $editSlider =
        $stmt->fetch(
            PDO::FETCH_ASSOC
        );

}



include 'includes/header.php';

?>

<style>

/* =====================================================
   PAGE
===================================================== */

.slider-page {

    background:#fffaf7;

    min-height:100vh;

    padding:40px 25px 60px;

}


.slider-title {

    color:#7b2d26;

    font-weight:700;

    margin-bottom:30px;

}



/* =====================================================
   FORM CARD
===================================================== */

.slider-form-card {

    background:#fff;

    border-radius:12px;

    padding:25px;

    box-shadow:
        0 4px 18px
        rgba(0,0,0,0.08);

    margin-bottom:30px;

}


.slider-form-card h4 {

    color:#7b2d26;

    font-weight:700;

    margin-bottom:20px;

}


.form-label {

    color:#4d2924;

    font-weight:600;

}


.form-control {

    border:
        1px solid
        #d8b8ad;

    border-radius:7px;

    padding:11px;

}


.form-control:focus {

    border-color:#7b2d26;

    box-shadow:
        0 0 0
        0.2rem
        rgba(123,45,38,.15);

}



/* =====================================================
   IMAGE PREVIEW
===================================================== */

.slider-preview {

    width:100%;

    height:180px;

    object-fit:cover;

    border-radius:10px;

    border:
        1px solid
        #ead8d2;

    margin-bottom:15px;

}



/* =====================================================
   BUTTON
===================================================== */

.save-btn {

    background:#7b2d26;

    color:white;

    border:none;

    padding:11px 22px;

    border-radius:7px;

    font-weight:600;

}


.save-btn:hover {

    background:#5f211c;

    color:white;

}


.cancel-btn {

    background:#eee;

    color:#4d2924;

    padding:11px 22px;

    border-radius:7px;

    text-decoration:none;

    margin-left:8px;

}



/* =====================================================
   SLIDER CARDS
===================================================== */

.slider-card {

    background:white;

    border-radius:12px;

    overflow:hidden;

    box-shadow:
        0 4px 18px
        rgba(0,0,0,0.08);

    height:100%;

}


.slider-card img {

    width:100%;

    height:190px;

    object-fit:cover;

}


.slider-card-body {

    padding:20px;

}


.slider-card-body h5 {

    color:#7b2d26;

    font-weight:700;

}


.slider-card-body p {

    color:#666;

    font-size:14px;

    line-height:1.5;

}


.slider-number {

    display:inline-block;

    background:#7b2d26;

    color:white;

    padding:5px 12px;

    border-radius:20px;

    font-size:12px;

    margin-bottom:10px;

}


.edit-btn {

    background:#7b2d26;

    color:white;

    padding:7px 13px;

    border-radius:6px;

    text-decoration:none;

    font-size:13px;

}


.edit-btn:hover {

    background:#5f211c;

    color:white;

}


.delete-btn {

    background:#dc3545;

    color:white;

    padding:7px 13px;

    border-radius:6px;

    text-decoration:none;

    font-size:13px;

    margin-left:5px;

}


.delete-btn:hover {

    background:#b02a37;

    color:white;

}



/* =====================================================
   RESPONSIVE
===================================================== */

@media(max-width:768px) {

    .slider-page {

        padding:25px 15px 40px;

    }

}

</style>


<div class="slider-page">

    <div class="container-fluid">


        <!-- =================================================
             TITLE
        ================================================== -->

        <h2 class="slider-title">

            <i class="fas fa-images"></i>

            Slider Management

        </h2>



        <!-- =================================================
             MESSAGE
        ================================================== -->

        <?php if ($message): ?>

            <div class="alert alert-success">

                <i class="fas fa-check-circle"></i>

                <?php
                echo htmlspecialchars(
                    $message
                );
                ?>

            </div>

        <?php endif; ?>


        <?php if ($error): ?>

            <div class="alert alert-danger">

                <i class="fas fa-exclamation-circle"></i>

                <?php
                echo htmlspecialchars(
                    $error
                );
                ?>

            </div>

        <?php endif; ?>



        <!-- =================================================
             ADD / EDIT FORM
        ================================================== -->

        <div class="slider-form-card">

            <h4>

                <i class="fas fa-image"></i>

                <?php
                echo $editSlider
                    ? 'Change Slider'
                    : 'Add Slider';
                ?>

            </h4>


            <form
                method="POST"
                enctype="multipart/form-data"
            >


                <?php if ($editSlider): ?>

                    <input
                        type="hidden"
                        name="slider_id"
                        value="<?php
                        echo (int)
                            $editSlider['id'];
                        ?>"
                    >

                    <input
                        type="hidden"
                        name="old_image"
                        value="<?php
                        echo htmlspecialchars(
                            $editSlider['image']
                        );
                        ?>"
                    >

                <?php endif; ?>


                <div class="row g-4">


                    <!-- IMAGE -->

                    <div class="col-lg-5">

                        <label class="form-label">

                            Slider Image

                        </label>


                        <?php if (
                            $editSlider &&
                            !empty(
                                $editSlider['image']
                            )
                        ): ?>

                            <img
                                src="../images/<?php
                                echo htmlspecialchars(
                                    basename(
                                        $editSlider[
                                            'image'
                                        ]
                                    )
                                );
                                ?>"
                                class="slider-preview"
                                alt="Slider"
                            >

                        <?php endif; ?>


                        <input
                            type="file"
                            name="image"
                            class="form-control"
                            accept="
                                image/jpeg,
                                image/jpg,
                                image/png,
                                image/webp,
                                image/gif
                            "
                            <?php
                            echo $editSlider
                                ? ''
                                : 'required';
                            ?>
                        >


                        <small
                            class="text-muted"
                        >

                            Recommended size:
                            1920 × 600 pixels

                            <br>

                            Maximum size:
                            5 MB

                        </small>

                    </div>



                    <!-- TITLE / SUBTITLE -->

                    <div class="col-lg-7">


                        <div class="mb-3">

                            <label
                                class="form-label"
                            >

                                Slider Title

                            </label>


                            <input
                                type="text"
                                name="title"
                                class="form-control"
                                placeholder="
                                    Example:
                                    Premium Dry Fruits
                                "
                                value="<?php

                                echo $editSlider
                                    ? htmlspecialchars(
                                        $editSlider[
                                            'title'
                                        ]
                                    )
                                    : '';

                                ?>"
                                required
                            >

                        </div>



                        <div class="mb-3">

                            <label
                                class="form-label"
                            >

                                Slider Subtitle

                            </label>


                            <textarea
                                name="subtitle"
                                class="form-control"
                                rows="5"
                                placeholder="
                                    Example:
                                    Fresh & Premium Quality
                                    Dry Fruits Delivered
                                    to Your Doorstep
                                "
                                required
                            ><?php

                            echo $editSlider
                                ? htmlspecialchars(
                                    $editSlider[
                                        'subtitle'
                                    ]
                                )
                                : '';

                            ?></textarea>

                        </div>



                        <button
                            type="submit"
                            class="save-btn"
                        >

                            <i
                                class="fas fa-save"
                            ></i>

                            <?php
                            echo $editSlider
                                ? 'Update Slider'
                                : 'Add Slider';
                            ?>

                        </button>



                        <?php if ($editSlider): ?>

                            <a
                                href="slider.php"
                                class="cancel-btn"
                            >

                                Cancel

                            </a>

                        <?php endif; ?>


                    </div>

                </div>

            </form>

        </div>



        <!-- =================================================
             CURRENT SLIDERS
        ================================================== -->

        <h4
            class="slider-title"
            style="font-size:22px;"
        >

            <i class="fas fa-list"></i>

            Current Sliders

        </h4>



        <div class="row g-4">


            <?php if (!empty($sliders)): ?>


                <?php
                foreach (
                    $sliders as $index => $slider
                ):
                ?>


                    <div
                        class="col-md-6 col-lg-4"
                    >


                        <div
                            class="slider-card"
                        >


                            <!-- IMAGE -->

                            <?php if (
                                !empty(
                                    $slider['image']
                                )
                            ): ?>

                                <img
                                    src="../images/<?php
                                    echo htmlspecialchars(
                                        basename(
                                            $slider[
                                                'image'
                                            ]
                                        )
                                    );
                                    ?>"
                                    alt="<?php
                                    echo htmlspecialchars(
                                        $slider[
                                            'title'
                                        ]
                                    );
                                    ?>"
                                >

                            <?php else: ?>

                                <div
                                    style="
                                        height:190px;
                                        background:#f5f5f5;
                                        display:flex;
                                        align-items:center;
                                        justify-content:center;
                                    "
                                >

                                    <i
                                        class="
                                            fas
                                            fa-image
                                            fa-3x
                                            text-muted
                                        "
                                    ></i>

                                </div>

                            <?php endif; ?>



                            <!-- CONTENT -->

                            <div
                                class="slider-card-body"
                            >


                                <span
                                    class="slider-number"
                                >

                                    Slider
                                    <?php
                                    echo $index + 1;
                                    ?>

                                </span>


                                <h5>

                                    <?php
                                    echo htmlspecialchars(
                                        $slider[
                                            'title'
                                        ]
                                    );
                                    ?>

                                </h5>


                                <p>

                                    <?php
                                    echo htmlspecialchars(
                                        $slider[
                                            'subtitle'
                                        ]
                                    );
                                    ?>

                                </p>



                                <a
                                    href="slider.php?edit=<?php
                                    echo (int)
                                        $slider['id'];
                                    ?>"
                                    class="edit-btn"
                                >

                                    <i
                                        class="fas fa-edit"
                                    ></i>

                                    Change

                                </a>



                                <a
                                    href="slider.php?delete=<?php
                                    echo (int)
                                        $slider['id'];
                                    ?>"
                                    class="delete-btn"
                                    onclick="
                                        return confirm(
                                            'Are you sure you want to delete this slider?'
                                        );
                                    "
                                >

                                    <i
                                        class="fas fa-trash"
                                    ></i>

                                    Delete

                                </a>


                            </div>

                        </div>

                    </div>


                <?php endforeach; ?>


            <?php else: ?>


                <div class="col-12">

                    <div
                        class="
                            alert
                            alert-info
                        "
                    >

                        No sliders found.

                        Add your first slider
                        using the form above.

                    </div>

                </div>


            <?php endif; ?>


        </div>


    </div>

</div>


<?php

include 'includes/footer.php';

?>
```
