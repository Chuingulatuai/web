<?php if (!isset($_SESSION)) { session_start(); } ?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chính sách bảo mật - CarShop</title>
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
        <h1 class="mb-4">Chính sách bảo mật</h1>
        <p>CarShop tôn trọng và cam kết bảo vệ thông tin cá nhân của quý khách. Chính sách này mô tả cách chúng tôi thu thập, sử dụng và bảo vệ thông tin của bạn.</p>

        <h2>1. Mục đích thu thập thông tin</h2>
        <p>Chúng tôi thu thập thông tin cá nhân để:</p>
        <ul>
            <li>Xử lý đơn hàng và cung cấp dịch vụ.</li>
            <li>Hỗ trợ khách hàng và giải đáp thắc mắc.</li>
            <li>Cải thiện chất lượng dịch vụ và trải nghiệm người dùng.</li>
            <li>Gửi thông tin về các chương trình khuyến mãi (nếu được sự đồng ý của khách hàng).</li>
        </ul>

        <h2>2. Phạm vi thu thập thông tin</h2>
        <p>Các thông tin chúng tôi có thể thu thập bao gồm: họ tên, số điện thoại, email, địa chỉ giao hàng.</p>

        <h2>3. Bảo mật thông tin</h2>
        <p>CarShop cam kết không chia sẻ, bán hoặc cho thuê thông tin cá nhân của quý khách cho bất kỳ bên thứ ba nào, ngoại trừ các trường hợp được pháp luật yêu cầu.</p>

        <h2>4. Quyền của khách hàng</h2>
        <p>Quý khách có quyền truy cập, chỉnh sửa hoặc xóa thông tin cá nhân của mình bất kỳ lúc nào bằng cách đăng nhập vào tài khoản trên website hoặc liên hệ với chúng tôi.</p>
    </div>
    <?php include 'main/footer/dichvu.php'; ?>
    <?php include 'chatbot.php'; ?>
    <?php include 'main/footer/footer.php'; ?>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js"></script>
</body>
</html>