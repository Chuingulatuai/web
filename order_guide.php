<?php if (!isset($_SESSION)) { session_start(); } ?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hướng dẫn đặt hàng - CarShop</title>
    <link rel="icon" type="image/png" href="logo/logo.png">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/app.css">
    <link rel="stylesheet" href="css/policy.css">
</head>
<body>
    <?php include 'main/header/pre-header.php'; ?>
    <?php include 'main/header/danhmuc.php'; ?>
    <div class="policy-container">
        <h1 class="mb-4">Hướng dẫn đặt hàng</h1>
        <p>Việc mua xe tại CarShop thật đơn giản. Quý khách chỉ cần thực hiện theo các bước sau:</p>

        <div id="order-steps">
            <div class="step">
                <h2>Bước 1: Tìm kiếm sản phẩm</h2>
                <p>Sử dụng thanh tìm kiếm hoặc duyệt qua các danh mục để tìm chiếc xe bạn mong muốn.</p>
            </div>
            <div class="step">
                <h2>Bước 2: Thêm vào giỏ hàng</h2>
                <p>Khi đã chọn được xe ưng ý, nhấn nút "Thêm vào giỏ hàng".</p>
            </div>
            <div class="step">
                <h2>Bước 3: Kiểm tra giỏ hàng và đặt hàng</h2>
                <p>Vào giỏ hàng để xem lại danh sách sản phẩm, sau đó nhấn "Tiến hành đặt hàng".</p>
            </div>
            <div class="step">
                <h2>Bước 4: Điền thông tin và thanh toán</h2>
                <p>Cung cấp thông tin nhận hàng, chọn phương thức thanh toán và hoàn tất đơn hàng.</p>
            </div>
            <div class="step">
                <h2>Bước 5: Nhận xe</h2>
                <p>CarShop sẽ liên hệ và giao xe đến địa chỉ bạn đã cung cấp.</p>
            </div>
        </div>

        <p class="mt-4">Nếu có bất kỳ thắc mắc nào, đừng ngần ngại liên hệ với chúng tôi qua hotline <strong>1900 1234</strong> để được hỗ trợ.</p>
    </div>
    <?php include 'main/footer/dichvu.php'; ?>
    <?php include 'chatbot.php'; ?>
    <?php include 'main/footer/footer.php'; ?>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js"></script>
</body>
</html>