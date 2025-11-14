<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }

// Hàm kiểm tra AJAX
function is_ajax() {
    return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
}

// Hàm trả về JSON
function json_response($success, $message, $data = []) {
    header('Content-Type: application/json');
    echo json_encode(array_merge([
        'success' => $success,
        'message' => $message,
        'compare_count' => isset($_SESSION['compare_list']) ? count($_SESSION['compare_list']) : 0
    ], $data));
    exit;
}

// Hàm chuyển hướng an toàn
function safe_redirect($url) {
    header('Location: ' . $url);
    exit;
}

// Lấy URL của trang trước đó, nếu không có thì về trang chủ
$previous_page = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '/';

if (isset($_REQUEST['action'])) {
    $action = $_REQUEST['action'];

    switch ($action) {
        case 'compare_add':
            $product_id = isset($_REQUEST['product_id']) ? (int)$_REQUEST['product_id'] : 0;
            $message = 'Sản phẩm đã có trong danh sách so sánh.';
            $success = false;

            if ($product_id > 0) {
                if (!isset($_SESSION['compare_list'])) {
                    $_SESSION['compare_list'] = [];
                }

                if (count($_SESSION['compare_list']) >= 4) {
                    $message = 'Danh sách so sánh đã đầy (tối đa 4 sản phẩm).';
                    $success = false;
                } elseif (!in_array($product_id, $_SESSION['compare_list'])) {
                    $_SESSION['compare_list'][] = $product_id;
                    $message = 'Đã thêm sản phẩm vào danh sách so sánh.';
                    $success = true;
                }
            } else {
                $message = 'ID sản phẩm không hợp lệ.';
            }

            if (is_ajax()) {
                json_response($success, $message);
            }
            safe_redirect($previous_page);
            break;

        case 'compare_remove':
            $product_id = isset($_REQUEST['product_id']) ? (int)$_REQUEST['product_id'] : 0;
            if ($product_id > 0 && isset($_SESSION['compare_list'])) {
                $key = array_search($product_id, $_SESSION['compare_list']);
                if ($key !== false) {
                    unset($_SESSION['compare_list'][$key]);
                    $_SESSION['compare_list'] = array_values($_SESSION['compare_list']);
                }
            }
            
            if (is_ajax()) {
                json_response(true, 'Đã xóa sản phẩm khỏi danh sách so sánh.');
            }
            
            if (strpos($previous_page, 'sosanh.php') !== false && empty($_SESSION['compare_list'])) {
                safe_redirect('products.php');
            }
            safe_redirect($previous_page);
            break;

        case 'compare_clear':
            if (isset($_SESSION['compare_list'])) {
                unset($_SESSION['compare_list']);
            }
            
            if (is_ajax()) {
                json_response(true, 'Đã xóa toàn bộ danh sách so sánh.');
            }
            safe_redirect($previous_page);
            break;
        
        default:
            if (is_ajax()) {
                json_response(false, 'Hành động không hợp lệ.');
            }
            safe_redirect($previous_page);
            break;
    }
} else {
    if (is_ajax()) {
        json_response(false, 'Không có hành động nào được chỉ định.');
    }
    safe_redirect('/');
}
?>