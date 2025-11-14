<?php
include './connect_db.php';
if (!isset($_SESSION)) {
    session_start();
}

// Validate and sanitize order ID
$order_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($order_id <= 0) {
    die("Mã đơn hàng không hợp lệ.");
}

// --- Fetch Order Data ---
// Fetch general order info
$stmt_order = mysqli_prepare($con, "SELECT * FROM `orders` WHERE `id` = ?");
mysqli_stmt_bind_param($stmt_order, 'i', $order_id);
mysqli_stmt_execute($stmt_order);
$orderResult = mysqli_stmt_get_result($stmt_order);
$order = mysqli_fetch_assoc($orderResult);

if (!$order) {
    die("Không tìm thấy đơn hàng.");
}

// Fetch order details
$stmt_detail = mysqli_prepare($con, "
    SELECT od.*, p.image AS product_image 
    FROM `orders_detail` od
    LEFT JOIN `product` p ON od.product_id = p.id
    WHERE od.order_id = ?");
mysqli_stmt_bind_param($stmt_detail, 'i', $order_id);
mysqli_stmt_execute($stmt_detail);
$orderDetailResult = mysqli_stmt_get_result($stmt_detail);

if (!$orderDetailResult) {
    die("Lỗi truy vấn chi tiết đơn hàng: " . mysqli_error($con));
}

$orderDetails = mysqli_fetch_all($orderDetailResult, MYSQLI_ASSOC);

// --- Helper Functions & Data Preparation ---
function format_currency($n) {
    return number_format($n, 0, ',', '.') . ' ₫';
}

$status_map = [
    0 => ['text' => 'Đang xử lý', 'class' => 'processing'],
    1 => ['text' => 'Đang giao hàng', 'class' => 'shipping'],
    2 => ['text' => 'Thành công', 'class' => 'completed'],
    3 => ['text' => 'Đã hủy', 'class' => 'cancelled']
];
$current_status = $order['status'];

$subtotal = 0;
foreach ($orderDetails as $detail) {
    $subtotal += $detail['price'] * $detail['quantity'];
}
$shipping_fee = 0; // Assuming free shipping for now
$grand_total = $subtotal + $shipping_fee;

?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi tiết đơn hàng #<?= $order_id ?> - CarShop</title>
    <link rel="icon" type="image/png" href="logo/logo.png">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Be Vietnam Pro', sans-serif;
            background-color: #f4f6f8;
        }
        .page-header {
            padding: 25px 0;
            text-align: center;
            background-color: #fff;
            border-bottom: 1px solid #e9ecef;
            margin-bottom: 30px;
        }
        .page-header h1 {
            font-weight: 700;
            color: #343a40;
        }
        .page-header .order-id {
            font-weight: 500;
            color: #6c757d;
        }
        .detail-card {
            background-color: #fff;
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 25px;
            border: 1px solid #e9ecef;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        }
        .detail-card h5 {
            font-weight: 600;
            margin-bottom: 20px;
            border-bottom: 1px solid #e9ecef;
            padding-bottom: 15px;
            display: flex;
            align-items: center;
        }
        .detail-card h5 i {
            margin-right: 10px;
            color: #007bff;
        }
        .info-row {
            display: flex;
            margin-bottom: 12px;
        }
        .info-row .icon {
            width: 30px;
            color: #6c757d;
        }
        .info-row .text {
            flex: 1;
            color: #343a40;
        }

        /* Order Status Tracker */
        .status-tracker {
            display: flex;
            justify-content: space-between;
            position: relative;
            margin: 20px 0 30px;
        }
        .status-tracker::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 3px;
            background-color: #e9ecef;
            transform: translateY(-50%);
            z-index: 1;
        }
        .status-step {
            position: relative;
            z-index: 2;
            text-align: center;
        }
        .status-dot {
            width: 20px;
            height: 20px;
            border-radius: 50%;
            background-color: #e9ecef;
            border: 3px solid #fff;
            margin: 0 auto 10px;
            transition: all 0.3s ease;
        }
        .status-label {
            font-size: 0.85rem;
            color: #6c757d;
        }
        .status-step.active .status-dot {
            background-color: #ffc107; /* Pending */
        }
        .status-step.completed .status-dot {
            background-color: #28a745; /* Completed */
        }
        .status-step.shipping .status-dot {
            background-color: #17a2b8; /* Shipping */
        }
        .status-step.cancelled .status-dot {
            background-color: #dc3545; /* Cancelled */
        }

        /* Product List */
        .product-item {
            display: flex;
            align-items: center;
            padding: 15px 0;
            border-bottom: 1px solid #e9ecef;
        }
        .product-item:last-child { border-bottom: none; }
        .product-item img {
            width: 65px;
            height: 65px;
            object-fit: cover;
            border-radius: 8px;
            margin-right: 15px;
        }
        .product-details { flex-grow: 1; }
        .product-details .name { font-weight: 600; color: #343a40; }
        .product-details .qty { font-size: 0.9rem; color: #6c757d; }
        .product-price { font-weight: 600; }

        /* Summary */
        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            font-size: 1.05rem;
        }
        .summary-row.grand-total {
            font-size: 1.3rem;
            font-weight: 700;
            border-top: 1px solid #e9ecef;
            padding-top: 15px;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <?php include 'main/header/pre-header.php'; ?>
    <?php include 'main/header/danhmuc.php'; ?>

    <div class="page-header">
        <div class="container">
            <h1>Chi Tiết Đơn Hàng</h1>
            <p class="order-id">Mã đơn hàng: #<?= htmlspecialchars($order['id']) ?> &bull; Ngày đặt: <?= date('d/m/Y', $order['created_time']) ?></p>
        </div>
    </div>

    <div class="container">
        <div class="row">
            <div class="col-lg-7">
                <!-- Order Status -->
                <div class="detail-card">
                    <h5><i class="bi bi-truck"></i>Trạng thái đơn hàng</h5>
                    <div class="status-tracker">
                        <div class="status-step <?= $current_status >= 0 ? 'active' : '' ?> <?= $current_status == 0 ? 'processing' : '' ?> <?= $current_status > 0 ? 'completed' : '' ?>">
                            <div class="status-dot"></div>
                            <div class="status-label">Đang xử lý</div>
                        </div>
                        <div class="status-step <?= $current_status >= 1 ? 'active' : '' ?> <?= $current_status == 1 ? 'shipping' : '' ?> <?= $current_status > 1 ? 'completed' : '' ?>">
                            <div class="status-dot"></div>
                            <div class="status-label">Đang giao</div>
                        </div>
                        <div class="status-step <?= $current_status >= 2 ? 'active completed' : '' ?>">
                            <div class="status-dot"></div>
                            <div class="status-label">Thành công</div>
                        </div>
                        <?php if ($current_status == 3): ?>
                        <div class="status-step active cancelled">
                            <div class="status-dot"></div>
                            <div class="status-label">Đã hủy</div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Shipping Info -->
                <div class="detail-card">
                    <h5><i class="bi bi-geo-alt-fill"></i>Thông tin giao hàng</h5>
                    <div class="info-row">
                        <span class="icon"><i class="bi bi-person"></i></span>
                        <span class="text"><?= htmlspecialchars($order['name']) ?></span>
                    </div>
                    <div class="info-row">
                        <span class="icon"><i class="bi bi-telephone"></i></span>
                        <span class="text"><?= htmlspecialchars($order['phone']) ?></span>
                    </div>
                    <div class="info-row">
                        <span class="icon"><i class="bi bi-envelope"></i></span>
                        <span class="text"><?= htmlspecialchars($order['email']) ?></span>
                    </div>
                    <div class="info-row">
                        <span class="icon"><i class="bi bi-pin-map"></i></span>
                        <span class="text"><?= htmlspecialchars($order['address']) ?></span>
                    </div>
                </div>

                <!-- Payment Info -->
                <div class="detail-card">
                    <h5><i class="bi bi-credit-card-fill"></i>Thông tin thanh toán</h5>
                    <div class="info-row">
                        <span class="icon"><i class="bi bi-wallet"></i></span>
                        <span class="text">Phương thức: <strong><?= htmlspecialchars($order['payment_method']) ?></strong></span>
                    </div>
                     <div class="info-row">
                        <span class="icon"><i class="bi bi-patch-check"></i></span>
                        <span class="text">Tình trạng: <strong>Thanh toán khi nhận hàng</strong></span>
                    </div>
                </div>
            </div>

            <div class="col-lg-5">
                <!-- Order Summary -->
                <div class="detail-card">
                    <h5><i class="bi bi-box-seam"></i>Tóm tắt đơn hàng</h5>
                    <div class="product-list">
                        <?php foreach ($orderDetails as $detail): ?>
                        <div class="product-item">
                            <img src="<?= htmlspecialchars($detail['product_image']) ?>" alt="<?= htmlspecialchars($detail['product_name']) ?>">
                            <div class="product-details">
                                <div class="name"><?= htmlspecialchars($detail['product_name']) ?></div>
                                <div class="qty">Số lượng: <?= htmlspecialchars($detail['quantity']) ?></div>
                            </div>
                            <div class="product-price"><?= format_currency($detail['price'] * $detail['quantity']) ?></div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="summary-section mt-3">
                        <div class="summary-row">
                            <span>Tạm tính</span>
                            <span><?= format_currency($subtotal) ?></span>
                        </div>
                        <div class="summary-row">
                            <span>Phí vận chuyển</span>
                            <span><?= format_currency($shipping_fee) ?></span>
                        </div>
                        <div class="summary-row grand-total">
                            <span>Tổng cộng</span>
                            <span><?= format_currency($grand_total) ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="text-center my-4">
            <a href="danhsachdathang.php" class="btn btn-primary"><i class="bi bi-arrow-left"></i> Quay lại lịch sử đơn hàng</a>
        </div>
    </div>

    <?php include 'main/footer/dichvu.php'; ?>
    <?php include 'chatbot.php'; ?>
    <?php include 'main/footer/footer.php'; ?>
</body>
</html>