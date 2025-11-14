<?php
if (!isset($_SESSION)) {
    session_start();
}
include './connect_db.php';

// 1. CHECK & FETCH CART
$cart = $_SESSION['cart'] ?? [];
if (empty($cart)) {
    header('Location: chitietgiohang.php');
    exit;
}

// Get user ID from session if available
$user_id = $_SESSION['user']['id'] ?? null;

$products_in_cart = [];
$total_price = 0;
$product_ids_in_cart = array_keys($cart);
$product_ids_string = implode(',', array_map('intval', $product_ids_in_cart));

$result = mysqli_query($con, "SELECT * FROM `product` WHERE `id` IN ($product_ids_string)");
while ($row = mysqli_fetch_assoc($result)) {
    $products_in_cart[$row['id']] = $row;
}

$order_content = [];
foreach ($cart as $id => $item) {
    if (isset($products_in_cart[$id])) {
        $product = $products_in_cart[$id];
        // Check for sufficient stock
        if ($item['quantity'] > $product['quantity']) {
            // Redirect back to cart with an error message
            $_SESSION['cart_error'] = "Sản phẩm \"" . $product['name'] . "\" không đủ số lượng. Chỉ còn " . $product['quantity'] . " sản phẩm.";
            header('Location: chitietgiohang.php');
            exit;
        }
        $total_price += $item['quantity'] * $product['price_new'];
        $order_content[] = "{$product['name']} (x{$item['quantity']})";
    }
}
$order_content_string = implode(", ", $order_content);

$errors = [];

// 2. HANDLE FORM SUBMISSION
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and validate input
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $note = trim($_POST['note'] ?? '');
    $payment_method = trim($_POST['payment_method'] ?? 'COD');

    if (empty($name)) $errors[] = "Họ và tên là bắt buộc.";
    if (empty($phone)) $errors[] = "Số điện thoại là bắt buộc.";
    if (empty($address)) $errors[] = "Địa chỉ là bắt buộc.";
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Email không hợp lệ.";

    if (empty($errors)) {
        mysqli_begin_transaction($con);
        try {
            // Insert into `orders` table
            $stmt = mysqli_prepare($con, "INSERT INTO `orders` (`user_id`, `name`, `email`, `phone`, `address`, `content`, `payment_method`, `status`, `created_time`, `last_updated`) VALUES (?, ?, ?, ?, ?, ?, ?, 0, ?, ?)");
            $time = time();
            mysqli_stmt_bind_param($stmt, 'issssssii', $user_id, $name, $email, $phone, $address, $order_content_string, $payment_method, $time, $time);
            mysqli_stmt_execute($stmt);
            $order_id = mysqli_insert_id($con);

            if (!$order_id) {
                throw new Exception("Không thể tạo đơn hàng. Vui lòng thử lại.");
            }

            // Insert into `orders_detail` and update product quantity
            $stmt_detail = mysqli_prepare($con, "INSERT INTO `orders_detail` (`order_id`, `product_id`, `product_name`, `quantity`, `price`, `image`, `user_id`) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt_update_qty = mysqli_prepare($con, "UPDATE `product` SET `quantity` = `quantity` - ? WHERE `id` = ?");

            foreach ($cart as $id => $item) {
                if (isset($products_in_cart[$id])) {
                    $product = $products_in_cart[$id];
                    $product_name = $product['name'];
                    $product_image = $product['image'];
                    
                    // Insert order detail
                    mysqli_stmt_bind_param($stmt_detail, 'iisidsi', $order_id, $id, $product_name, $item['quantity'], $product['price_new'], $product_image, $user_id);
                    mysqli_stmt_execute($stmt_detail);

                    // Update product quantity
                    mysqli_stmt_bind_param($stmt_update_qty, 'ii', $item['quantity'], $id);
                    mysqli_stmt_execute($stmt_update_qty);
                }
            }

            mysqli_commit($con);

            // Clear cart and redirect to success page
            unset($_SESSION['cart']);
            $_SESSION['latest_order_id'] = $order_id;
            header('Location: order_success.php');
            exit;

        } catch (Exception $e) {
            mysqli_rollback($con);
            $errors[] = "Đã có lỗi xảy ra trong quá trình đặt hàng: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <title>Thanh toán - CarShop</title>
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
        .form-control, .custom-select { border-radius: 8px; padding: 22px 15px; border: 1px solid #ced4da; transition: all 0.3s ease; }
        .form-control:focus { border-color: #0056b3; box-shadow: 0 0 0 3px rgba(0,123,255,.15); }
        .form-label { font-weight: 600; margin-bottom: 8px; }
        .order-summary { background-color: #fff; border-radius: 12px; padding: 25px; border: 1px solid #e0e0e0; position: sticky; top: 30px; }
        .order-summary h3 { font-weight: 700; border-bottom: 1px solid #e0e0e0; padding-bottom: 15px; margin-bottom: 20px; }
        .summary-item { display: flex; align-items: center; margin-bottom: 15px; }
        .summary-item img { width: 60px; height: 60px; object-fit: cover; border-radius: 8px; margin-right: 15px; }
        .summary-item-details { flex-grow: 1; }
        .summary-item-details h6 { font-weight: 600; font-size: 0.95rem; margin: 0; }
        .summary-item-details span { font-size: 0.9rem; color: #6c757d; }
        .summary-item-price { font-weight: 600; }
        .summary-total { border-top: 1px solid #e0e0e0; padding-top: 15px; margin-top: 10px; }
        .summary-total .row { font-size: 1.1rem; }
        .summary-total .grand-total { font-size: 1.4rem; font-weight: 700; }
        .btn-place-order { background-color: #28a745; color: #fff; font-weight: 700; padding: 15px; width: 100%; font-size: 1.2rem; border-radius: 8px; transition: all 0.3s ease; }
        .btn-place-order:hover { background-color: #218838; transform: translateY(-2px); box-shadow: 0 4px 15px rgba(0,0,0,0.15); }
        .payment-methods .card { padding: 15px; border: 2px solid #e0e0e0; cursor: pointer; transition: all 0.2s ease; }
        .payment-methods .card.active { border-color: #007bff; background-color: #f8faff; }
        .payment-methods .card:hover { border-color: #007bff; }
    </style>
</head>
<body>
    <?php include 'main/header/pre-header.php'; ?>
    <?php include 'main/header/danhmuc.php'; ?>

    <div class="checkout-header">
        <div class="container">
            <h1>Thanh toán</h1>
        </div>
    </div>

    <section class="content my-5">
        <div class="container">
            <form action="dathang.php" method="POST">
                <div class="row">
                    <!-- Left Column: Shipping Info -->
                    <div class="col-lg-7">
                        <h3 class="mb-4 font-weight-bold">Thông tin giao hàng</h3>
                        <?php 
                        // Hiển thị lỗi từ MoMo (nếu có)
                        if (isset($_SESSION['momo_error'])): ?>
                            <div class="alert alert-danger">
                                <p class="mb-0"><?= htmlspecialchars($_SESSION['momo_error']) ?></p>
                            </div>
                        <?php 
                            unset($_SESSION['momo_error']);
                        endif; 
                        ?>
                        <?php if(!empty($errors)): ?>
                            <div class="alert alert-danger">
                                <?php foreach($errors as $error): ?>
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
                        <h3 class="mt-4 mb-4 font-weight-bold">Phương thức thanh toán</h3>
                        <div class="payment-methods">
                             <div class="card active">
                                <div class="custom-control custom-radio">
                                    <input type="radio" id="cod" name="payment_method" value="COD" class="custom-control-input" checked required>
                                    <label class="custom-control-label d-flex align-items-center" for="cod">
                                        <i class="bi bi-truck h4 mr-3"></i>
                                        <span>Thanh toán khi nhận hàng (COD)</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column: Order Summary -->
                    <div class="col-lg-5">
                        <div class="order-summary">
                            <h3>Đơn hàng của bạn</h3>
                            <?php foreach ($cart as $id => $item): 
                                if (!isset($products_in_cart[$id])) continue;
                                $product = $products_in_cart[$id];
                            ?>
                            <div class="summary-item">
                                <img src="<?= htmlspecialchars($product['image']) ?>" alt="<?= htmlspecialchars($product['name']) ?>">
                                <div class="summary-item-details">
                                    <h6><?= htmlspecialchars($product['name']) ?></h6>
                                    <span>Số lượng: <?= $item['quantity'] ?></span>
                                </div>
                                <div class="summary-item-price"><?= number_format($item['quantity'] * $product['price_new']) ?>đ</div>
                            </div>
                            <?php endforeach; ?>
                            <div class="summary-total">
                                <div class="row">
                                    <div class="col">Tạm tính</div>
                                    <div class="col text-right"><?= number_format($total_price) ?>đ</div>
                                </div>
                                <div class="row mt-2">
                                    <div class="col">Phí vận chuyển</div>
                                    <div class="col text-right">Miễn phí</div>
                                </div>
                                <div class="row mt-3 grand-total">
                                    <div class="col">Tổng cộng</div>
                                    <div class="col text-right"><?= number_format($total_price) ?>đ</div>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-place-order mt-4">HOÀN TẤT ĐƠN HÀNG</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </section>

    <?php include 'chatbot.php'; ?>
    <?php include 'main/footer/dichvu.php'; ?>
    <?php include 'main/footer/footer.php'; ?>

    <div id="compare-widget-container">
      <?php include 'compare-widget.php'; ?>
    </div>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js"></script>
    <script src="js/main.js"></script>
</body>
</html>