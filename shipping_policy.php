<?php if (!isset($_SESSION)) { session_start(); } ?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chính sách giao xe - CarShop</title>
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
        <h1 class="mb-4">Chính sách giao xe</h1>
        <p>CarShop cam kết mang đến dịch vụ giao xe chuyên nghiệp, an toàn và đúng hẹn. Dưới đây là các quy định về chính sách giao xe của chúng tôi:</p>

        <h2>1. Phạm vi giao xe</h2>
        <p>Chúng tôi hỗ trợ giao xe trên toàn quốc. Tùy vào địa điểm nhận xe, thời gian và chi phí có thể khác nhau.</p>

        <h2>2. Thời gian giao xe</h2>
        <ul>
            <li><strong>Đối với xe có sẵn:</strong> Giao hàng trong vòng 3-5 ngày làm việc.</li>
            <li><strong>Đối với xe đặt hàng:</strong> Thời gian giao hàng sẽ được thông báo cụ thể dựa trên thông tin từ nhà sản xuất.</li>
        </ul>

        <h2>3. Chi phí giao xe</h2>
        <p>Chi phí giao xe sẽ được tính toán dựa trên khoảng cách từ showroom gần nhất đến địa chỉ của quý khách. Mức phí chi tiết sẽ được thông báo khi xác nhận đơn hàng.</p>

        <h2>4. Quy trình giao nhận</h2>
        <ul>
            <li>Nhân viên CarShop sẽ liên hệ trước để xác nhận thời gian và địa điểm giao xe.</li>
            <li>Quý khách vui lòng kiểm tra kỹ tình trạng xe, giấy tờ liên quan trước khi ký nhận.</li>
            <li>Mọi thắc mắc hoặc vấn đề phát sinh trong quá trình giao nhận cần được ghi rõ trong biên bản bàn giao.</li>
        </ul>

        <p class="mt-4">CarShop luôn nỗ lực để quá trình giao nhận diễn ra suôn sẻ và làm hài lòng quý khách.</p>
    </div>
    <?php include 'main/footer/dichvu.php'; ?>
    <?php include 'chatbot.php'; ?>
    <?php include 'main/footer/footer.php'; ?>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js"></script>
</body>
</html>