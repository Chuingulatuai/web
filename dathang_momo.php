<?php
header('Content-type: text/html; charset=utf-8');
session_start();
include './connect_db.php';

// --- START: CORRECT PRICE CALCULATION ---
// Recalculate total price from the session cart for security and accuracy.
$total_price = 0;
$cart = $_SESSION['cart'] ?? [];

if (!empty($cart)) {
    $product_ids_in_cart = array_keys($cart);
    $product_ids_string = implode(',', array_map('intval', $product_ids_in_cart));

    $products_in_cart = [];
    $result = mysqli_query($con, "SELECT `id`, `price_new`, `quantity` FROM `product` WHERE `id` IN ($product_ids_string)");
    
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $products_in_cart[$row['id']] = $row;
        }
    }

    foreach ($cart as $id => $item) {
        if (isset($products_in_cart[$id])) {
            // Check for sufficient stock before proceeding
            if ($item['quantity'] > $products_in_cart[$id]['quantity']) {
                $_SESSION['momo_error'] = "Sản phẩm " . $products_in_cart[$id]['name'] . " không đủ số lượng.";
                header('Location: chitietgiohang.php'); // Redirect to cart
                exit;
            }
            $total_price += $item['quantity'] * $products_in_cart[$id]['price_new'];
        }
    }
}
// --- END: CORRECT PRICE CALCULATION ---


// Check MoMo transaction limits
if ($total_price > 50000000) {
    $_SESSION['momo_error'] = "Tổng số tiền vượt quá giới hạn thanh toán của MoMo (50,000,000đ). Vui lòng chọn phương thức thanh toán khác.";
    header('Location: chitietgiohang.php');
    exit;
}

// If cart is empty or total is invalid, redirect to cart page.
// MoMo requires a minimum of 1,000 VND.
if ($total_price < 1000) {
    $_SESSION['momo_error'] = "Giỏ hàng của bạn trống hoặc tổng tiền không hợp lệ để thanh toán.";
    header('Location: chitietgiohang.php');
    exit;
}

// Store the final calculated total price in session for the return URL to verify
$_SESSION['final_total_price'] = $total_price;


function execPostRequest($url, $data)
{
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt(
        $ch,
        CURLOPT_HTTPHEADER,
        array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($data)
        )
    );
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    $result = curl_exec($ch);
    curl_close($ch);
    return $result;
}

$endpoint = "https://test-payment.momo.vn/v2/gateway/api/create";

// Use test credentials from MoMo
$partnerCode = 'MOMOBKUN20180529';
$accessKey = 'klm05TvNBzhg7h7j';
$secretKey = 'at67qH6mk8w5Y1nAyMoYKMWACiEi2bsa';

// Order details
$orderInfo = "Thanh toán đơn hàng CarShop";
$amount = (string)$total_price; // Use the correctly calculated total price, ensure it is a string
$orderId = time() . ""; // Unique order ID for this transaction attempt

// Construct return URLs dynamically
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
$domainName = $_SERVER['HTTP_HOST'];
// IMPORTANT: Make sure the path is correct for your project structure
$returnPath = dirname($_SERVER['PHP_SELF']) . '/return_momo.php'; 
$redirectUrl = $protocol . $domainName . $returnPath;
$ipnUrl = $protocol . $domainName . $returnPath; 

$extraData = ""; // Optional extra data

$requestId = time() . "";
$requestType = "captureWallet"; // MoMo wallet payment

// Create the raw signature string
$rawHash = "accessKey=" . $accessKey . "&amount=" . $amount . "&extraData=" . $extraData . "&ipnUrl=" . $ipnUrl . "&orderId=" . $orderId . "&orderInfo=" . $orderInfo . "&partnerCode=" . $partnerCode . "&redirectUrl=" . $redirectUrl . "&requestId=" . $requestId . "&requestType=" . $requestType;

// Create the signature
$signature = hash_hmac("sha256", $rawHash, $secretKey);

// Data to be sent to MoMo
$data = array(
    'partnerCode' => $partnerCode,
    'partnerName' => "Test",
    "storeId" => "MomoTestStore",
    'requestId' => $requestId,
    'amount' => $amount,
    'orderId' => $orderId,
    'orderInfo' => $orderInfo,
    'redirectUrl' => $redirectUrl,
    'ipnUrl' => $ipnUrl,
    'lang' => 'vi',
    'extraData' => $extraData,
    'requestType' => $requestType,
    'signature' => $signature
);

// Send the request to MoMo
$result = execPostRequest($endpoint, json_encode($data));
$jsonResult = json_decode($result, true);

// Process the response
if (isset($jsonResult['payUrl'])) {
    // Before redirecting, save the temporary order details to session
    // This info will be used in return_momo.php to create the final order
    $_SESSION['momo_order_details'] = [
        'order_id' => $orderId, // The temporary ID sent to MoMo
        'user_id' => $_SESSION['user']['id'] ?? null,
        // IMPORTANT: You must get customer info from the form submitted to `dat_hang_momo.php`
        // This info is currently missing from the flow. For now, we leave it empty
        // and will need to fix `dat_hang_momo.php` next.
        'name' => $_SESSION['order_info']['name'] ?? 'Anonymous',
        'email' => $_SESSION['order_info']['email'] ?? '',
        'phone' => $_SESSION['order_info']['phone'] ?? '',
        'address' => $_SESSION['order_info']['address'] ?? '',
        'note' => $_SESSION['order_info']['note'] ?? ''
    ];
    
    header('Location: ' . $jsonResult['payUrl']);
    exit;
} else {
    // If MoMo returns an error, store it in session and redirect back to the cart
    $_SESSION['momo_error'] = $jsonResult['message'] ?? 'Thanh toán MoMo thất bại. Vui lòng thử lại.';
    // Redirecting to the cart page is safer than the checkout form
    header('Location: chitietgiohang.php');
    exit;
}
?>
