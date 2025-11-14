<?php
if (!isset($_SESSION)) {
    session_start();
}
include './connect_db.php';

// Credentials - should be the same as in dathang_momo.php
$secretKey = 'at67qH6mk8w5Y1nAyMoYKMWACiEi2bsa';

// Retrieve parameters from MoMo callback
$partnerCode = $_GET['partnerCode'] ?? null;
$orderId = $_GET['orderId'] ?? null;
$requestId = $_GET['requestId'] ?? null;
$amount = $_GET['amount'] ?? null;
$orderInfo = $_GET['orderInfo'] ?? null;
$orderType = $_GET['orderType'] ?? null;
$transId = $_GET['transId'] ?? null;
$resultCode = $_GET['resultCode'] ?? null;
$message = $_GET['message'] ?? null;
$payType = $_GET['payType'] ?? null;
$extraData = $_GET['extraData'] ?? null;
$signature = $_GET['signature'] ?? null;

// Retrieve session data
$momo_order_details = $_SESSION['momo_order_details'] ?? null;
$cart = $_SESSION['cart'] ?? [];

// --- SECURITY CHECK ---
// Create the raw hash string for signature verification
$rawHash = "accessKey=" . 'klm05TvNBzhg7h7j' . // Hardcoded access key
    "&amount=" . $amount .
    "&extraData=" . $extraData .
    "&message=" . $message .
    "&orderId=" . $orderId .
    "&orderInfo=" . $orderInfo .
    "&orderType=" . $orderType .
    "&partnerCode=" . $partnerCode .
    "&payType=" . $payType .
    "&requestId=" . $requestId .
    "&resultCode=" . $resultCode .
    "&transId=" . $transId;

$expectedSignature = hash_hmac("sha256", $rawHash, $secretKey);

if ($signature !== $expectedSignature || $resultCode != 0) {
    // Signature mismatch or payment failed
    $error_message = "Giao dịch không thành công hoặc chữ ký không hợp lệ. ";
    if ($resultCode == 1006) { // User cancelled transaction
        $error_message = "Bạn đã hủy giao dịch thanh toán.";
    } else {
        $error_message .= "Lỗi: " . ($message ?? 'Không xác định');
    }
    $_SESSION['momo_error'] = $error_message;
    header('Location: chitietgiohang.php');
    exit;
}

// --- PAYMENT SUCCESSFUL, PROCESS ORDER ---

// Double-check if we have the necessary info
if (empty($momo_order_details) || empty($cart)) {
    $_SESSION['momo_error'] = "Phiên làm việc đã hết hạn. Không thể hoàn tất đơn hàng.";
    header('Location: chitietgiohang.php');
    exit;
}

// Fetch product details again to be safe
$product_ids_in_cart = array_keys($cart);
$product_ids_string = implode(',', array_map('intval', $product_ids_in_cart));
$products_from_db = [];
$result = mysqli_query($con, "SELECT * FROM `product` WHERE `id` IN ($product_ids_string)");
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $products_from_db[$row['id']] = $row;
    }
}

// Start database transaction
mysqli_begin_transaction($con);

try {
    // 1. Insert into `orders` table
    $order_content_parts = [];
    foreach ($cart as $id => $item) {
        if (isset($products_from_db[$id])) {
            $order_content_parts[] = $products_from_db[$id]['name'] . ' (x' . $item['quantity'] . ')';
        }
    }
    $order_content_string = implode(", ", $order_content_parts);

    $stmt_order = mysqli_prepare($con, "INSERT INTO `orders` (`user_id`, `name`, `email`, `phone`, `address`, `content`, `payment_method`, `status`, `created_time`, `last_updated`, `momo_trans_id`) VALUES (?, ?, ?, ?, ?, ?, 'MoMo', 0, ?, ?, ?)");
    $time = time();
    mysqli_stmt_bind_param($stmt_order, 'isssssiis', 
        $momo_order_details['user_id'], 
        $momo_order_details['name'], 
        $momo_order_details['email'], 
        $momo_order_details['phone'], 
        $momo_order_details['address'], 
        $order_content_string, 
        $time, 
        $time,
        $transId
    );
    mysqli_stmt_execute($stmt_order);
    $new_order_id = mysqli_insert_id($con);

    if (!$new_order_id) {
        throw new Exception("Không thể tạo đơn hàng chính.");
    }

    // 2. Insert into `orders_detail` and update product quantity
    $stmt_detail = mysqli_prepare($con, "INSERT INTO `orders_detail` (`order_id`, `product_id`, `product_name`, `quantity`, `price`, `image`, `user_id`) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt_update_qty = mysqli_prepare($con, "UPDATE `product` SET `quantity` = `quantity` - ? WHERE `id` = ?");

    foreach ($cart as $id => $item) {
        if (isset($products_from_db[$id])) {
            $product = $products_from_db[$id];
            
            // Insert order detail
            mysqli_stmt_bind_param($stmt_detail, 'iisidsi', $new_order_id, $id, $product['name'], $item['quantity'], $product['price_new'], $product['image'], $momo_order_details['user_id']);
            mysqli_stmt_execute($stmt_detail);

            // Update product quantity
            mysqli_stmt_bind_param($stmt_update_qty, 'ii', $item['quantity'], $id);
            mysqli_stmt_execute($stmt_update_qty);
        }
    }

    // If all queries were successful, commit the transaction
    mysqli_commit($con);

    // 3. Clear session and redirect to success page
    unset($_SESSION['cart']);
    unset($_SESSION['momo_order_details']);
    unset($_SESSION['final_total_price']);
    $_SESSION['latest_order_id'] = $new_order_id;

    header('Location: order_success.php');
    exit;

} catch (Exception $e) {
    // Something went wrong, rollback the transaction
    mysqli_rollback($con);

    // Set error message and redirect
    $_SESSION['momo_error'] = "Đã có lỗi xảy ra khi lưu đơn hàng: " . $e->getMessage();
    header('Location: chitietgiohang.php');
    exit;
}
?>