<?php if (!isset($_SESSION)) { session_start(); } ?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chính sách bảo hành & đổi trả - CarShop</title>
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
        <h1 class="mb-4">Chính sách bảo hành & đổi trả</h1>
        <p>CarShop cam kết mang đến cho khách hàng những sản phẩm chất lượng và dịch vụ hậu mãi chu đáo. Dưới đây là chính sách bảo hành và đổi trả của chúng tôi:</p>

        <h2>1. Chính sách bảo hành</h2>
        <p>Tất cả các xe mua tại CarShop đều được hưởng chính sách bảo hành chính hãng từ nhà sản xuất. Thời gian và điều kiện bảo hành tuân thủ theo quy định của từng hãng xe.</p>

        <h2>2. Điều kiện đổi trả</h2>
        <p>CarShop hỗ trợ đổi trả trong các trường hợp sau:</p>
        <ul>
            <li>Xe giao không đúng mẫu mã, chủng loại so với đơn đặt hàng.</li>
            <li>Xe bị lỗi kỹ thuật do nhà sản xuất và đã được xác nhận bởi trung tâm bảo hành ủy quyền.</li>
        </ul>

        <h2>3. Quy trình đổi trả</h2>
        <ul>
            <li>Quý khách vui lòng thông báo cho CarShop trong vòng 24 giờ kể từ khi nhận xe.</li>
            <li>Sản phẩm đổi trả phải còn nguyên trạng, chưa qua sử dụng và đầy đủ giấy tờ.</li>
            <li>CarShop sẽ tiến hành thẩm định và thông báo kết quả cho quý khách trong thời gian sớm nhất.</li>
        </ul>

        <p class="mt-4">Để biết thêm chi tiết, vui lòng liên hệ bộ phận chăm sóc khách hàng của chúng tôi qua hotline <strong>1900 1234</strong>.</p>
    </div>
    <?php include 'main/footer/dichvu.php'; ?>
    <?php include 'chatbot.php'; ?>
    <?php include 'main/footer/footer.php'; ?>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js"></script>
</body>
</html>