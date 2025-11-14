<?php
// update_cart_ajax.php

if (!isset($_SESSION)) {
    session_start();
}

header('Content-Type: application/json');

include './connect_db.php';

$response = [
    'success' => false,
    'message' => 'Yêu cầu không hợp lệ.',
    'data' => null
];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode($response);
    exit;
}

$cart = $_SESSION['cart'] ?? [];
$product_id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$action = $_POST['action'] ?? '';

if (!$product_id || !$action) {
    echo json_encode($response);
    exit;
}

// Lấy thông tin sản phẩm từ DB
$stmt = mysqli_prepare($con, "SELECT `quantity`, `name`, `price`, `price_new` FROM `product` WHERE `id` = ?");
mysqli_stmt_bind_param($stmt, "i", $product_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$product = mysqli_fetch_assoc($result);

if (!$product) {
    $response['message'] = 'Sản phẩm không tồn tại.';
    echo json_encode($response);
    exit;
}

if ($action === 'update') {
    $new_quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 0;

    if ($new_quantity <= 0) {
        // Số lượng <= 0 thì coi như là xóa
        unset($cart[$product_id]);
        $response['message'] = "Đã xóa sản phẩm \"{$product['name']}\" khỏi giỏ hàng.";
    } elseif ($new_quantity > $product['quantity']) {
        $response['message'] = "Số lượng cho \"{$product['name']}\" vượt quá số lượng trong kho (còn {$product['quantity']}).";
        // Không thay đổi giỏ hàng, trả về số lượng hiện tại trong giỏ
        $new_quantity = $cart[$product_id]['quantity'];
    } else {
        $cart[$product_id]['quantity'] = $new_quantity;
        $response['message'] = "Cập nhật số lượng \"{$product['name']}\" thành công.";
    }
    $response['success'] = true;

} elseif ($action === 'delete') {
    if (isset($cart[$product_id])) {
        $product_name = $cart[$product_id]['name'];
        unset($cart[$product_id]);
        $response['success'] = true;
        $response['message'] = "Đã xóa sản phẩm \"$product_name\" khỏi giỏ hàng.";
    } else {
        $response['message'] = "Sản phẩm không có trong giỏ hàng.";
    }
}

// Cập nhật session
$_SESSION['cart'] = $cart;

// Tính toán lại tổng giá trị giỏ hàng và số lượng sản phẩm
$total_price = 0;
$item_count = 0;
$product_ids = array_keys($cart);

if (!empty($product_ids)) {
    $product_ids_string = implode(',', $product_ids);
    $result = mysqli_query($con, "SELECT `id`, `price_new` FROM `product` WHERE `id` IN ($product_ids_string)");
    $products_data = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $products_data[$row['id']] = $row;
    }

    foreach ($cart as $id => $item) {
        if (isset($products_data[$id])) {
            $total_price += $item['quantity'] * $products_data[$id]['price_new'];
            $item_count += $item['quantity'];
        }
    }
}

// Chuẩn bị dữ liệu trả về
$response['data'] = [
    'totalPrice' => $total_price,
    'totalPriceFormatted' => number_format($total_price) . 'đ',
    'itemCount' => $item_count
];

if (isset($new_quantity) && isset($cart[$product_id])) {
    $response['data']['itemTotalPrice'] = $cart[$product_id]['quantity'] * $product['price_new'];
    $response['data']['itemTotalPriceFormatted'] = number_format($response['data']['itemTotalPrice']) . 'đ';
    $response['data']['newQuantity'] = $cart[$product_id]['quantity'];
}

echo json_encode($response);
exit;
?>