<?php if (!isset($_SESSION)) { session_start(); } ?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giới thiệu - CarShop</title>
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
        <h1 class="mb-4">Giới thiệu về CarShop</h1>
        <p><strong>CarShop</strong> là một trong những nền tảng mua sắm xe trực tuyến hàng đầu Việt Nam. Chúng tôi mang đến cho khách hàng trải nghiệm mua sắm an toàn, tiện lợi và đa dạng các dòng xe từ các thương hiệu uy tín.</p>
        <p>Sứ mệnh của chúng tôi là kết nối người bán và người mua một cách hiệu quả, minh bạch và đáng tin cậy. CarShop không ngừng nỗ lực để cải thiện dịch vụ, mở rộng danh mục sản phẩm và áp dụng những công nghệ mới nhất để phục vụ khách hàng tốt hơn.</p>
        
        <h2 class="mt-5 mb-3">Giá trị cốt lõi</h2>
        <ul>
            <li><strong>Khách hàng là trọng tâm:</strong> Mọi hoạt động của chúng tôi đều hướng đến lợi ích và sự hài lòng của khách hàng.</li>
            <li><strong>Chất lượng và uy tín:</strong> Chúng tôi cam kết cung cấp những sản phẩm chính hãng, chất lượng cao cùng với thông tin rõ ràng, minh bạch.</li>
            <li><strong>Đổi mới và sáng tạo:</strong> Luôn tìm tòi, áp dụng công nghệ và ý tưởng mới để nâng cao trải nghiệm người dùng.</li>
            <li><strong>Phát triển bền vững:</strong> Xây dựng một nền tảng vững mạnh, phát triển song hành cùng với lợi ích của đối tác và cộng đồng.</li>
        </ul>

        <h2 class="mt-5 mb-3">Liên hệ</h2>
        <p>Mọi ý kiến đóng góp, vui lòng liên hệ với chúng tôi qua:</p>
        <p><strong>Email:</strong> support@carshop.com<br><strong>Hotline:</strong> 1900 1234</p>
    </div>
    <?php include 'main/footer/dichvu.php'; ?>
    <?php include 'chatbot.php'; ?>
    <?php include 'main/footer/footer.php'; ?>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js"></script>
</body>
</html>