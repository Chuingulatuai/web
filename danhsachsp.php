<?php
if (!isset($_SESSION)) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <?php
    include './connect_db.php'; // Include database connection

    // Retrieve category name based on menu_id from the URL
    $menu_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    $query = "SELECT name FROM menu_product WHERE id = $menu_id";
    $result = mysqli_query($con, $query);
    $category_name = mysqli_fetch_assoc($result)['name'] ?? 'Danh Mục';
    ?>

    <title><?php echo htmlspecialchars($category_name); ?></title>
    <meta name="description" content="Chuyên cung cấp đầy đủ các loại xe ô tô đáp ứng theo nhu cầu của khách hàng.">
    <meta name="keywords" content="mua xe ô tô, xe ô tô bán chạy, xe ô tô giá rẻ, xe ô tô đã qua sử dụng">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- CSS và JS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="css/home.css">
    <link rel="stylesheet" href="fontawesome_free_5.13.0/css/all.css">
    <link rel="stylesheet" href="css/sach-moi-tuyen-chon.css">
    <link rel="stylesheet" href="css/car-new-selection.css">
    <link rel="stylesheet" href="css/responsive.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@100;300;400;500;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="slick/slick.css" />
    <link rel="stylesheet" type="text/css" href="slick/slick-theme.css" />
    <link rel="stylesheet" type="text/css" href="css/grid.css" />
    <link rel="icon" type="image/png" sizes="32x32" href="logo/logo.png">
    <link rel="manifest" href="favicon_io/site.webmanifest">
    <meta name="google-site-verification" content="urDZLDaX8wQZ_-x8ztGIyHqwUQh2KRHvH9FhfoGtiEw" />

    <style>
        img[alt="www.000webhost.com"] {
            display: none;
        }

        .card-item {
            position: relative;
            border: none;
            border-radius: 12px;
            margin-bottom: 20px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            background-color: white;
            text-align: center;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .card-item:hover {
            transform: translateY(-10px);
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.2);
        }

        .overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(255, 0, 0, 0.5);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            font-weight: bold;
            opacity: 0;
            transition: opacity 0.3s ease-in-out;
            z-index: 1;
        }

        .card-item.out-of-stock .overlay {
            opacity: 1;
            /* Show overlay if out of stock */
        }

        .anh {
            width: 100%;
            height: 200px;
            object-fit: cover;
            transition: transform 0.3s ease;
            border-top-left-radius: 12px;
            border-top-right-radius: 12px;
        }

        .image-container {
            position: relative;
            overflow: hidden;
        }

        .card-item:hover .anh {
            transform: scale(1.1);
        }

        .giamoi {
            font-size: 1.7rem;
            font-weight: bold;
            color: #FF5722;
            margin-bottom: 10px;
        }

        .giamoi::after {
            content: " VNĐ";
            font-size: 1rem;
        }

        .card-body {
            padding: 20px;
            text-align: center;
        }

        .card-title {
            font-size: 1.4rem;
            margin: 10px 0;
            font-weight: bold;
            color: #333;
        }

        .btn-success {
            background-color: #0094DA;
            border: none;
            color: white;
            padding: 12px 18px;
            border-radius: 8px;
            transition: background-color 0.3s, transform 0.3s;
            text-transform: uppercase;
            font-size: 0.9rem;
            box-shadow: 0 2px 10px rgba(0, 148, 218, 0.4);
        }

        .btn-success:hover {
            background-color: #FF5722;
            transform: scale(1.05);
        }

        @media (max-width: 768px) {
            .overlay {
                font-size: 1.2rem;
            }

            .giamoi {
                font-size: 1.3rem;
            }

            .card-title {
                font-size: 1.1rem;
            }
        }
    </style>
</head>

<body>
    <div id="fb-root"></div>
    <script async defer crossorigin="anonymous" src="https://connect.facebook.net/vi_VN/sdk.js#xfbml=1&version=v6.0"></script>

    <?php include 'main/header/pre-header.php'; ?>
    <?php include 'main/header/danhmuc.php'; ?>
    <?php include 'main/header/banner.php'; ?>

    <section class="content my-4">
        <div class="container">
            <h2 class="text-center mb-4">Sản Phẩm Mới Nhất</h2>
            <div class="noidung bg-white">
                <div class="items">
                    <div class="row">
                        <?php
                        $selectproduct = "SELECT * FROM `product` WHERE `menu_id` =" . $menu_id; // Use the sanitized menu_id
                        $result = mysqli_query($con, $selectproduct);
                        while ($row = mysqli_fetch_array($result)) {
                            $out_of_stock = $row['quantity'] <= 0; // Check stock status
                        ?>
                            <div class="col-lg-3 col-md-4 col-sm-6 col-xs-12 mb-4 d-flex justify-content-center">
                                <div class="card-item <?php echo $out_of_stock ? 'out-of-stock' : ''; ?>">
                                    <a href="chitietxe.php?id=<?php echo $row['id']; ?>" class="motsanpham" style="text-decoration: none; color: black;">
                                        <div class="image-container">
                                            <img class="card-img-top anh" src="<?php echo $row['image']; ?>" alt="<?php echo htmlspecialchars($row['name']); ?>">
                                            <?php if ($out_of_stock) { ?>
                                                <div class="overlay">Hết hàng</div>
                                            <?php } ?>
                                        </div>
                                        <div class="card-body noidungsp mt-3">
                                            <h5 class="card-title ten"><?php echo htmlspecialchars($row['name']); ?></h5>
                                            <div class="gia d-flex align-items-baseline mb-3 justify-content-center">
                                                <div class="giamoi"><?php echo number_format($row['price_new']); ?></div>
                                            </div>
                                            <a href="chitietxe.php?id=<?php echo $row['id']; ?>" class="btn btn-success mb-3">Chi tiết</a>
                                        </div>
                                    </a>
                                </div>
                            </div>
                        <?php
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="fixed-bottom">
        <div class="btn btn-warning float-right rounded-circle nutcuonlen" id="backtotop" style="background: #CF111A;">
            <i class="fa fa-chevron-up text-white"></i>
        </div>
    </div>

    <?php include 'main/footer/dichvu.php';
    include 'chatbot.php';
    ?>
    <?php include 'main/footer/footer.php'; ?>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js"></script>
    <script type="text/javascript" src="slick/slick.min.js"></script>
    <script type="text/javascript" src="http://ajax.aspnetcdn.com/ajax/jquery.validate/1.13.1/jquery.validate.min.js"></script>
    <script src="js/main.js"></script>

<?php include 'compare-widget.php'; ?>

</body>

</html>