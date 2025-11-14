<?php
// Start session
if (!isset($_SESSION)) {
    session_start();
}

// Redirect to login if user is not logged in
if (!isset($_SESSION['user']['id'])) {
    header('Location: login.php');
    exit;
}

// Include database connection
include './connect_db.php';

// Handle order cancellation if requested
if (isset($_GET['cancel_order'])) {
    $orderid_to_cancel = (int)$_GET['cancel_order'];
    $user_id_for_cancel = (int)$_SESSION['user']['id'];
    // Make sure the user can only cancel their own order
    mysqli_query($con, "UPDATE `orders` SET `status`=3 WHERE `id`='$orderid_to_cancel' AND `user_id` = '$user_id_for_cancel'");
    header("Location: danhsachdathang.php"); // Redirect back to the same page to prevent re-submission
    exit();
}

// Fetch user orders from the database
$userid = (int)$_SESSION['user']['id'];
$result = mysqli_query($con, "SELECT * FROM orders WHERE `user_id`= $userid ORDER BY created_time DESC");
$orders = mysqli_fetch_all($result, MYSQLI_ASSOC);

?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <title>Car Shop - Lịch sử đơn hàng</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="logo/logo.png">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/home.css">
    <link rel="stylesheet" href="css/index.css">
    <style>
        .page-wrap { min-height: 60vh; }
        .table-modern { box-shadow: 0 2px 15px rgba(0,0,0,0.08); }
        .table-modern th { background-color: #f8f9fa; }
        .status-badge { padding: .35em .65em; border-radius: .25rem; font-weight: 600; font-size: .8em; }
        .status-pending { background-color: #fff3cd; color: #856404; }
        .status-delivering { background-color: #d1ecf1; color: #0c5460; }
        .status-completed { background-color: #d4edda; color: #155724; }
        .status-canceled { background-color: #f8d7da; color: #721c24; }
    </style>
</head>
<body>
    <?php include 'main/header/pre-header.php'; ?>
    <?php include 'main/header/danhmuc.php'; ?>

    <section class="content my-4 page-wrap">
        <div class="container">
            <h1 class="text-center mb-4 font-weight-bold">Lịch sử đơn hàng</h1>
            <?php if (empty($orders)): ?>
                <div class="alert alert-info text-center">
                    <p class="mb-0">Bạn chưa có đơn hàng nào.</p>
                    <a href="index.php" class="btn btn-primary mt-2">Bắt đầu mua sắm</a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover table-modern">
                        <thead class="thead-light">
                            <tr>
                                <th>Mã Đơn</th>
                                <th>Ngày Đặt</th>
                                <th>Trạng Thái</th>
                                <th>Hành Động</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orders as $order): ?>
                                <tr>
                                    <td>#<?= $order['id']; ?></td>
                                    <td><?= date('d/m/Y H:i', $order['created_time']); ?></td>
                                    <td>
                                        <?php
                                        switch ($order['status']) {
                                            case 0: echo '<span class="status-badge status-pending">Đang xử lý</span>'; break;
                                            case 1: echo '<span class="status-badge status-delivering">Đang giao hàng</span>'; break;
                                            case 2: echo '<span class="status-badge status-completed">Thành công</span>'; break;
                                            case 3: echo '<span class="status-badge status-canceled">Bị hủy</span>'; break;
                                            default: echo '<span class="status-badge">Không xác định</span>'; break;
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <?php if ($order['status'] == 0): ?>
                                            <a href="?cancel_order=<?= $order['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Bạn có chắc muốn hủy đơn hàng này?');">Hủy</a>
                                        <?php endif; ?>
                                        <a href="order_detail.php?id=<?= $order['id']; ?>" class="btn btn-info btn-sm">Chi tiết</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </section>
    
    <?php include 'main/footer/dichvu.php'; ?>
    <?php include 'chatbot.php'; ?>
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