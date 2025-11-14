<?php if (!isset($_SESSION)) { session_start(); } ?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Phương thức thanh toán - CarShop</title>
    <link rel="icon" type="image/png" href="logo/logo.png">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/app.css">
    <style>.content-wrap { padding: 60px 0; min-height: 60vh; }</style>
</head>
<body>
    <?php include 'main/header/pre-header.php'; ?>
    <?php include 'main/header/danhmuc.php'; ?>
    <div class="container content-wrap">
        <h1 class="mb-4">Phương thức thanh toán</h1>
        <p>CarShop hỗ trợ nhiều phương thức thanh toán linh hoạt, giúp quý khách thuận tiện hơn trong việc mua sắm:</p>

        <div class="method">
            <h4>1. Thanh toán khi nhận xe (COD)</h4>
            <p>Quý khách sẽ thanh toán tiền mặt cho nhân viên giao hàng ngay khi nhận được xe. Vui lòng kiểm tra kỹ sản phẩm trước khi thanh toán.</p>
        </div>

        <div class="method">
            <h4>2. Chuyển khoản ngân hàng</h4>
            <p>Quý khách có thể chuyển khoản vào tài khoản ngân hàng của CarShop. Thông tin chi tiết sẽ được cung cấp khi quý khách chọn phương thức này lúc đặt hàng.</p>
        </div>

        <div class="method">
            <h4>3. Thanh toán qua cổng thanh toán MoMo</h4>
            <p>Chúng tôi tích hợp cổng thanh toán MoMo, cho phép quý khách thanh toán nhanh chóng và an toàn bằng ví điện tử MoMo.</p>
        </div>

        <div class="method">
            <h4>4. Thanh toán bằng thẻ quốc tế (Visa, MasterCard)</h4>
            <p>CarShop chấp nhận thanh toán bằng các loại thẻ tín dụng và ghi nợ quốc tế phổ biến.</p>
        </div>

        <p class="mt-4">Mọi giao dịch đều được bảo mật và mã hóa để đảm bảo an toàn cho thông tin của quý khách.</p>
    </div>
    <?php include 'main/footer/dichvu.php'; ?>
    <?php include 'chatbot.php'; ?>
    <?php include 'main/footer/footer.php'; ?>
</body>
</html>