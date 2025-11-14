<?php
include 'connect_db.php';
if (!isset($_SESSION)) {
    session_start();
}

// Kiểm tra sự tồn tại của 'id' trong GET
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = (int)$_GET['id']; // Chuyển đổi sang kiểu số nguyên để bảo mật
} else {
    // Nếu không có id hợp lệ, chuyển hướng về trang giỏ hàng hoặc trang khác
    header('location: ./chitietgiohang.php');
    exit();
}

$action = isset($_GET['action']) ? $_GET['action'] : 'add';
$quantity = isset($_GET['quantity']) ? (int)$_GET['quantity'] : 1;

if ($quantity <= 0) {
    $quantity = 1; // Đảm bảo số lượng tối thiểu là 1
}

// Fetch product details and available quantity
$sql = mysqli_query($con, "SELECT * FROM `product` WHERE `id` = $id");
$product = mysqli_fetch_assoc($sql);

// Kiểm tra xem sản phẩm có tồn tại không
if (!$product) {
    echo "<script>alert('Sản phẩm không tồn tại!');</script>";
    header('location: ./chitietgiohang.php');
    exit();
}

// Kiểm tra nếu số lượng yêu cầu vượt quá tồn kho
if ($quantity > $product['quantity']) {
    echo "<script>alert('Số lượng yêu cầu vượt quá tồn kho!');</script>";
    header('location: ./chitietgiohang.php');
    exit();
}

$item = [
    'id' => $product['id'],
    'name' => $product['name'],
    'image' => $product['image'],
    'price' => $product['price_new'],
    'quantity' => $quantity
];

if ($action === 'add') {
    if (isset($_SESSION['cart'][$id])) {
        $new_quantity = $_SESSION['cart'][$id]['quantity'] + $quantity;
        // Kiểm tra lại để đảm bảo không vượt quá tồn kho
        if ($new_quantity > $product['quantity']) {
            echo "<script>alert('Số lượng yêu cầu vượt quá tồn kho!');</script>";
        } else {
            $_SESSION['cart'][$id]['quantity'] = $new_quantity;
        }
    } else {
        $_SESSION['cart'][$id] = $item; // Thêm mới sản phẩm vào giỏ hàng
    }
} elseif ($action === 'update') {
    // Xác thực số lượng trước khi cập nhật
    if ($quantity > $product['quantity']) {
        echo "<script>alert('Số lượng yêu cầu vượt quá tồn kho!');</script>";
    } else {
        $_SESSION['cart'][$id]['quantity'] = $quantity; // Cập nhật số lượng
    }
} elseif ($action === 'delete') {
    unset($_SESSION['cart'][$id]); // Xóa sản phẩm khỏi giỏ hàng
}

// Chuyển hướng về trang chi tiết giỏ hàng
header('location: ./chitietgiohang.php');
?>
