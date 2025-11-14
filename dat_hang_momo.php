<?php
if (!isset($_SESSION)) {
    session_start();
}
include './connect_db.php';

// Get total price from URL, which was correctly calculated in the cart
$total_price = isset($_GET['total_price']) ? (float)$_GET['total_price'] : 0;

// If the form is submitted, store user info in session and redirect to payment processing
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and store order info
    $_SESSION['order_info'] = [
        'name' => trim($_POST['name'] ?? ''),
        'email' => trim($_POST['email'] ?? ''),
        'address' => trim($_POST['address'] ?? ''),
        'phone' => trim($_POST['phone'] ?? ''),
        'note' => trim($_POST['note'] ?? '')
    ];

    // Basic validation
    $errors = [];
    if (empty($_SESSION['order_info']['name'])) $errors[] = "Họ và tên là bắt buộc.";
    if (empty($_SESSION['order_info']['phone'])) $errors[] = "Số điện thoại là bắt buộc.";
    if (empty($_SESSION['order_info']['address'])) $errors[] = "Địa chỉ là bắt buộc.";
    if (empty($_SESSION['order_info']['email']) || !filter_var($_SESSION['order_info']['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Email không hợp lệ.";
    }

    if (empty($errors)) {
        // All good, redirect to the MoMo payment script
        header('Location: dathang_momo.php');
        exit();
    } 
    // If there are errors, the page will render again and display them

} else {
    // If it's a GET request, clear any previous order info
    unset($_SESSION['order_info']);
}

?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <title>Thông tin giao hàng - Thanh toán MoMo</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="logo/logo.png">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/home.css">
    <link rel="stylesheet" href="css/index.css">
    <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Be Vietnam Pro', sans-serif; background-color: #f4f6f8; }
        .checkout-header { padding: 40px 0; text-align: center; background-color: #fff; border-bottom: 1px solid #e0e0e0; }
        .checkout-header h1 { font-weight: 700; }
        .form-control { border-radius: 8px; padding: 22px 15px; border: 1px solid #ced4da; }
        .form-label { font-weight: 600; margin-bottom: 8px; }
        .order-summary { background-color: #fff; border-radius: 12px; padding: 25px; border: 1px solid #e0e0e0; position: sticky; top: 30px; }
        .order-summary h3 { font-weight: 700; border-bottom: 1px solid #e0e0e0; padding-bottom: 15px; margin-bottom: 20px; }
        .summary-total { border-top: 1px solid #e0e0e0; padding-top: 15px; margin-top: 10px; font-size: 1.3rem; font-weight: 700; }
        .btn-place-order { background-color: #a50064; color: #fff; font-weight: 700; padding: 15px; width: 100%; font-size: 1.2rem; border-radius: 8px; }
    </style>
</head>
<body>
    <?php include 'main/header/pre-header.php'; ?>
    <?php include 'main/header/danhmuc.php'; ?>

    <div class="checkout-header">
        <div class="container">
            <h1>Thông tin giao hàng</h1>
            <p class="lead">Bước 2: Cung cấp thông tin để thanh toán qua MoMo</p>
        </div>
    </div>

    <section class="content my-5">
        <div class="container">
            <!-- The form now submits to itself to process the data -->
            <form action="dat_hang_momo.php?total_price=<?= htmlspecialchars($total_price) ?>" method="POST">
                <div class="row">
                    <div class="col-lg-7">
                        <h3 class="mb-4 font-weight-bold">Chi tiết người nhận</h3>
                        <?php if (!empty($errors)): ?>
                            <div class="alert alert-danger">
                                <?php foreach ($errors as $error): ?>
                                    <p class="mb-0"><?= htmlspecialchars($error) ?></p>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                        <div class="row">
                            <div class="col-md-12 form-group">
                                <label for="name" class="form-label">Họ và tên</label>
                                <input type="text" class="form-control" id="name" name="name" required>
                            </div>
                            <div class="col-md-6 form-group">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                            <div class="col-md-6 form-group">
                                <label for="phone" class="form-label">Số điện thoại</label>
                                <input type="tel" class="form-control" id="phone" name="phone" required>
                            </div>
                            <div class="col-md-12 form-group">
                                <label for="address" class="form-label">Địa chỉ nhận hàng</label>
                                <input type="text" class="form-control" id="address" name="address" required>
                            </div>
                            <div class="col-md-12 form-group">
                                <label for="note" class="form-label">Ghi chú (tùy chọn)</label>
                                <textarea class="form-control" id="note" name="note" rows="3"></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-5">
                        <div class="order-summary">
                            <h3>Tóm tắt đơn hàng</h3>
                            <div class="summary-total">
                                <div class="d-flex justify-content-between">
                                    <span>Tổng cộng</span>
                                    <span><?= number_format($total_price) ?>đ</span>
                                </div>
                            </div>
                            <p class="text-muted mt-3">Bằng việc nhấn nút bên dưới, bạn sẽ được chuyển hướng đến cổng thanh toán MoMo.</p>
                            <button type="submit" class="btn btn-place-order mt-3">TIẾN HÀNH THANH TOÁN VỚI MOMO</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </section>

    <?php include 'main/footer/dichvu.php'; ?>
    <?php include 'main/footer/footer.php'; ?>
</body>
</html>
