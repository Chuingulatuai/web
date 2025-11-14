<?php
session_start(); // Khởi tạo session nếu chưa có

// Xóa tất cả các session
$_SESSION = []; // Đặt biến $_SESSION thành một mảng rỗng

// Xóa cookie session nếu có, để đảm bảo session thực sự được hủy
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, 
        $params["path"], 
        $params["domain"], 
        $params["secure"], 
        $params["httponly"]
    );
}

// Hủy session hoàn toàn
session_destroy();

// Điều hướng người dùng về trang index.php
header('Location: index.php');
exit(); // Đảm bảo kết thúc script sau khi điều hướng
