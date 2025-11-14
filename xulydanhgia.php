<?php
if (!isset($_SESSION)) { session_start(); }
require_once './connect_db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // 1. Check if user is logged in
    if (!isset($_SESSION['user']['id'])) {
        die('Lỗi: Bạn cần đăng nhập để thực hiện chức năng này.');
    }

    // 2. Get and validate data
    $user_id = (int)$_SESSION['user']['id'];
    $product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
    $rating = isset($_POST['rating']) ? (int)$_POST['rating'] : 0;
    $comment = isset($_POST['comment']) ? trim($_POST['comment']) : '';

    if ($product_id <= 0 || $rating < 1 || $rating > 5 || empty($comment)) {
        die('Lỗi: Dữ liệu không hợp lệ. Vui lòng điền đầy đủ thông tin.');
    }

    // 3. Verify user has purchased the product
    $stmtOrder = $con->prepare(
        "SELECT od.order_id FROM `orders_detail` od
         JOIN `orders` o ON od.order_id = o.id
         WHERE od.product_id = ? AND o.user_id = ? AND o.status = 2" // status = 2 (đã giao)
    );
    $stmtOrder->bind_param("ii", $product_id, $user_id);
    $stmtOrder->execute();
    if ($stmtOrder->get_result()->num_rows == 0) {
        die('Lỗi: Bạn cần mua sản phẩm này để có thể đánh giá.');
    }

    // 4. Check if user has already reviewed this product
    $stmtCheck = $con->prepare("SELECT id FROM `reviews` WHERE product_id = ? AND user_id = ?");
    $stmtCheck->bind_param("ii", $product_id, $user_id);
    $stmtCheck->execute();
    if ($stmtCheck->get_result()->num_rows > 0) {
        // Optional: Allow updating review, but for now, just prevent new one
        die('Lỗi: Bạn đã đánh giá sản phẩm này rồi.');
    }

    // 5. Insert the new review
    $stmtInsert = $con->prepare(
        "INSERT INTO `reviews` (product_id, user_id, rating, comment, status, created_at) 
         VALUES (?, ?, ?, ?, 'pending', NOW())"
    );
    $stmtInsert->bind_param("iiis", $product_id, $user_id, $rating, $comment);
    
    if ($stmtInsert->execute()) {
        // Success
        header('Location: chitietxe.php?id=' . $product_id . '&review_success=1#reviews');
        exit;
    } else {
        // Fail
        die('Lỗi: Không thể gửi đánh giá. Vui lòng thử lại.');
    }

} else {
    // Not a POST request
    header('Location: /');
    exit;
}
?>