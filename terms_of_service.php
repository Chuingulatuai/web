<?php if (!isset($_SESSION)) { session_start(); } ?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Điều khoản sử dụng - CarShop</title>
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
        <h1>Điều khoản sử dụng</h1>
        <p>Chào mừng quý khách đến với CarShop. Bằng việc truy cập và sử dụng website, quý khách đồng ý tuân thủ các điều khoản và điều kiện sau:</p>

        <h2>1. Quyền sở hữu trí tuệ</h2>
        <p>Tất cả nội dung, hình ảnh và tài liệu trên website này đều thuộc quyền sở hữu của CarShop và được bảo vệ bởi luật sở hữu trí tuệ.</p>

        <h2>2. Trách nhiệm của người dùng</h2>
        <ul>
            <li>Không sử dụng website cho các mục đích bất hợp pháp.</li>
            <li>Không đăng tải nội dung sai lệch, gây hiểu lầm hoặc vi phạm pháp luật.</li>
            <li>Bảo mật thông tin tài khoản cá nhân.</li>
        </ul>

        <h2>3. Giới hạn trách nhiệm</h2>
        <p>CarShop không chịu trách nhiệm cho bất kỳ thiệt hại nào phát sinh từ việc sử dụng hoặc không thể sử dụng website.</p>

        <h2>4. Thay đổi điều khoản</h2>
        <p>Chúng tôi có quyền thay đổi các điều khoản này bất kỳ lúc nào mà không cần thông báo trước. Việc tiếp tục sử dụng website sau khi có thay đổi đồng nghĩa với việc quý khách chấp nhận các điều khoản mới.</p>
    </div>
    <?php include 'main/footer/dichvu.php'; ?>
    <?php include 'chatbot.php'; ?>
    <?php include 'main/footer/footer.php'; ?>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js"></script>
</body>
</html>