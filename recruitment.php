<?php if (!isset($_SESSION)) { session_start(); } ?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tuyển dụng - CarShop</title>
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
        <h1 class="mb-4">Cơ hội nghề nghiệp tại CarShop</h1>
        <p>Chào mừng bạn đến với trang tuyển dụng của <strong>CarShop</strong>. Chúng tôi luôn tìm kiếm những tài năng đam mê, nhiệt huyết và có mong muốn đóng góp vào sự phát triển của ngành thương mại điện tử ô tô tại Việt Nam.</p>

        <h2 class="mt-5 mb-3">Vị trí đang tuyển</h2>
        <div class="accordion" id="recruitmentAccordion">
            <div class="card">
                <div class="card-header" id="headingOne">
                    <h3 class="mb-0">
                        <button class="btn btn-link btn-block text-left" type="button" data-toggle="collapse" data-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                            Chuyên viên Tư vấn Bán hàng
                        </button>
                    </h3>
                </div>
                <div id="collapseOne" class="collapse show" aria-labelledby="headingOne" data-parent="#recruitmentAccordion">
                    <div class="card-body">
                        <p><strong>Mô tả công việc:</strong></p>
                        <ul>
                            <li>Tư vấn, giới thiệu sản phẩm xe cho khách hàng.</li>
                            <li>Hỗ trợ khách hàng hoàn tất thủ tục mua xe.</li>
                            <li>Chăm sóc khách hàng sau bán hàng.</li>
                        </ul>
                        <p><strong>Yêu cầu:</strong></p>
                        <ul>
                            <li>Có ít nhất 1 năm kinh nghiệm trong lĩnh vực bán hàng ô tô.</li>
                            <li>Kỹ năng giao tiếp, đàm phán tốt.</li>
                            <li>Đam mê xe và có kiến thức về các dòng xe phổ thông.</li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="card">
                <div class="card-header" id="headingTwo">
                    <h3 class="mb-0">
                        <button class="btn btn-link btn-block text-left collapsed" type="button" data-toggle="collapse" data-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                            Nhân viên Marketing Online
                        </button>
                    </h3>
                </div>
                <div id="collapseTwo" class="collapse" aria-labelledby="headingTwo" data-parent="#recruitmentAccordion">
                    <div class="card-body">
                        <p><strong>Mô tả công việc:</strong></p>
                        <ul>
                            <li>Lập kế hoạch và triển khai các chiến dịch marketing trên các kênh online (Facebook, Google, ...).</li>
                            <li>Quản lý và phát triển nội dung cho website, fanpage.</li>
                            <li>Phân tích, đo lường hiệu quả các chiến dịch.</li>
                        </ul>
                        <p><strong>Yêu cầu:</strong></p>
                        <ul>
                            <li>Có kinh nghiệm trong lĩnh vực Digital Marketing.</li>
                            <li>Sáng tạo, năng động và có khả năng làm việc độc lập.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <h2 class="mt-5 mb-3">Cách thức ứng tuyển</h2>
        <p>Ứng viên quan tâm vui lòng gửi CV về địa chỉ email: <strong>tuyendung@carshop.com</strong> với tiêu đề "[Vị trí ứng tuyển] - [Họ và tên]".</p>
        <p>Chúng tôi sẽ liên hệ với những ứng viên phù hợp trong thời gian sớm nhất.</p>
    </div>
    <?php include 'main/footer/dichvu.php'; ?>
    <?php include 'chatbot.php'; ?>
    <?php include 'main/footer/footer.php'; ?>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js"></script>
</body>
</html>