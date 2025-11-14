<?php
if (!isset($_SESSION)) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <title>Đặt hàng thành công - CarShop</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="logo/logo.png">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Be Vietnam Pro', sans-serif;
            background-color: #f4f6f8;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }
        .success-container {
            text-align: center;
            background: #fff;
            padding: 50px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        .success-container .bi-check-circle-fill {
            font-size: 5rem;
            color: #28a745;
        }
        .success-container h1 {
            font-weight: 700;
            margin-top: 20px;
            color: #333;
        }
        .success-container p {
            color: #666;
            font-size: 1.1rem;
        }
        .success-container .action-buttons {
            display: flex;
            justify-content: center;
            align-items: center; /* Ensures vertical alignment */
            gap: 15px; /* Modern way to space items */
            flex-wrap: wrap; /* Allows buttons to stack on small screens */
        }
        .success-container .action-buttons .btn {
            min-width: 210px; /* Ensures buttons have a uniform width */
            font-weight: 600;
            padding: 12px 20px;
        }
        /* Fallback for older browsers that don't support gap */
        @supports not (gap: 15px) {
            .success-container .action-buttons .btn:first-child {
                margin-right: 15px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-7">
                <div class="success-container">
                    <i class="bi bi-check-circle-fill"></i>
                    <h1>Đặt hàng thành công!</h1>
                    <p>Cảm ơn bạn đã tin tưởng và mua sắm tại CarShop.<br>Chúng tôi sẽ liên hệ với bạn để xác nhận đơn hàng trong thời gian sớm nhất.</p>
                    <p>Mã đơn hàng của bạn là: <strong>#<?= htmlspecialchars($_SESSION['latest_order_id'] ?? 'N/A') ?></strong></p>
                    <div class="action-buttons mt-4">
                        <a href="danhsachdathang.php" class="btn btn-outline-primary">Xem lịch sử đơn hàng</a>
                        <a href="index.php" class="btn btn-primary">Tiếp tục mua sắm</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
