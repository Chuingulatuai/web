<?php
if (!isset($_SESSION)) { session_start(); }
require_once './connect_db.php';

header('Content-Type: application/json');

// Hàm gửi phản hồi JSON và thoát
function json_response($success, $message, $data = []) {
    echo json_encode(['success' => $success, 'message' => $message, 'data' => $data]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(false, 'Yêu cầu không hợp lệ.');
}

// 1. Kiểm tra người dùng đã đăng nhập chưa
if (!isset($_SESSION['user']['id'])) {
    json_response(false, 'Bạn cần đăng nhập để gửi đánh giá.');
}
$user_id = (int)$_SESSION['user']['id'];

// 2. Lấy và xác thực dữ liệu
$product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
$rating = isset($_POST['rating']) ? (int)$_POST['rating'] : 0;
$comment = trim($_POST['comment'] ?? '');

if ($product_id <= 0) {
    json_response(false, 'Sản phẩm không hợp lệ.');
}
if ($rating < 1 || $rating > 5) {
    json_response(false, 'Vui lòng chọn số sao xếp hạng.');
}
if (mb_strlen($comment) < 10) {
    json_response(false, 'Bình luận cần có ít nhất 10 ký tự.');
}

/*
// =========================================================================
// PHẦN KIỂM TRA NÂNG CAO & LƯU DATABASE (SẼ ĐƯỢC KÍCH HOẠT SAU)
// =========================================================================

// 3. (Quan trọng) Kiểm tra xem người dùng đã mua sản phẩm này chưa
// Cần một query vào bảng `orders` và `order_detail` để xác thực
$has_purchased = false;
// Ví dụ query:
// $stmt_check = $con->prepare("SELECT od.id FROM orders o JOIN order_detail od ON o.id = od.order_id WHERE o.user_id = ? AND od.product_id = ? AND o.status = 'Đã giao' LIMIT 1");
// $stmt_check->bind_param("ii", $user_id, $product_id);
// $stmt_check->execute();
// if ($stmt_check->get_result()->num_rows > 0) {
//     $has_purchased = true;
// }
// if (!$has_purchased) {
//     json_response(false, 'Bạn chỉ có thể đánh giá sản phẩm đã mua thành công.');
// }

// 4. Lưu vào cơ sở dữ liệu
try {
    $stmt = $con->prepare(
        "INSERT INTO reviews (product_id, user_id, rating, comment, created_at) VALUES (?, ?, ?, ?, NOW())"
    );
    $stmt->bind_param("iiis", $product_id, $user_id, $rating, $comment);
    $stmt->execute();
    json_response(true, 'Cảm ơn bạn đã gửi đánh giá!');
} catch (Exception $e) {
    // Log lỗi
    json_response(false, 'Đã có lỗi xảy ra. Vui lòng thử lại sau.');
}
*/

// Phản hồi tạm thời cho đến khi có DB
json_response(true, 'Cảm ơn bạn đã gửi đánh giá! Đánh giá của bạn đang chờ được duyệt.');

