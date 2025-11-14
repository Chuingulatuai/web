<?php
if (!isset($_SESSION)) { session_start(); }
require_once './connect_db.php';

// Lấy danh sách ID sản phẩm từ session
$compare_ids = isset($_SESSION['compare_list']) ? $_SESSION['compare_list'] : [];

$products = [];
$all_specs = [];

if (!empty($compare_ids)) {
    // Chuyển đổi mảng ID thành chuỗi để dùng trong câu lệnh IN
    $id_string = implode(',', array_map('intval', $compare_ids));

    // Truy vấn thông tin các sản phẩm
    $query = "SELECT id, name, image, price_new, content FROM `product` WHERE `id` IN ($id_string)";
    $result = $con->query($query);

    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $products[$row['id']] = $row;
        }
    }
}

// Hàm trích xuất thông số nhanh từ mô tả
if (!function_exists('extract_specs_quick')) {
    function extract_specs_quick($text){
      $t = mb_strtolower(strip_tags($text),'UTF-8');
      $spec=[];
      if(preg_match('/công suất[^0-9]*([\d\.]{2,})\s*(k?w|mã lực|hp|ps)/i',$t,$m)) $spec['Công suất']= $m[1].' '.$m[2];
      if(preg_match('/mô[\s-]?men xoắn[^0-9]*([\d\.]{2,})\s*n?m/i',$t,$m)) $spec['Mô-men xoắn']= $m[1].' Nm';
      if(preg_match('/tốc độ tối đa[^0-9]*([\d\.]{2,})\s*km\/?h/i',$t,$m)) $spec['Tốc độ tối đa']= $m[1].' km/h';
      if(preg_match('/0\s*[-\s]*\s*100[^0-9]*([\d\.\,]{1,4})\s*s/i',$t,$m)) $spec['Tăng tốc 0-100km/h']= str_replace(',','.',$m[1]).' s';
      if(preg_match('/quãng đường.*?([\d\.]{2,})\s*km/i',$t,$m)) $spec['Quãng đường (WLTP)']= $m[1].' km';
      if(preg_match('/dung lượng pin[^0-9]*([\d\.\,]{2,})\s*kwh/i',$t,$m)) $spec['Dung lượng pin']= $m[1].' kWh';
      if(preg_match('/kích thước[^\d:]*([\d\.\s,x×*]{7,})/iu',$t,$m)) $spec['Kích thước (DxRxC)']= trim($m[1]);
      if(preg_match('/chiều dài cơ sở[^\d:]*([\d\.\,]{3,})\s*mm/i',$t,$m)) $spec['Chiều dài cơ sở']= $m[1].' mm';
      if(preg_match('/khoảng sáng gầm[^0-9]*([\d\.]{2,})\s*mm/i',$t,$m)) $spec['Khoảng sáng gầm']= $m[1].' mm';
      if(preg_match('/la-?zăng[^0-9]*([\d\.]{1,2})\s*inch/i',$t,$m)) $spec['Mâm xe']= $m[1].' inch';
      if(preg_match('/số chỗ ngồi[^\d:]*(\d)/i',$t,$m)) $spec['Số chỗ ngồi']= $m[1].'';
      return $spec;
    }
}

// Lấy thông số cho từng sản phẩm và gộp vào danh sách tổng
foreach ($products as &$product) {
    $specs = extract_specs_quick($product['content']);
    $product['specs'] = $specs;
    foreach ($specs as $key => $value) {
        if (!in_array($key, $all_specs)) {
            $all_specs[] = $key;
        }
    }
}
unset($product); // Hủy tham chiếu

function vnd($n){ return number_format((float)$n, 0, ',', '.') . ' ₫'; }
function e($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }

?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>So sánh xe</title>
    <link rel="icon" type="image/png" sizes="32x32" href="logo/logo.png">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/app.css">
    <style>
        body { background-color: #f4f6f9; }
        .compare-table {
            background-color: #fff;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            overflow: hidden;
        }
        .compare-table th, .compare-table td {
            padding: 1rem;
            vertical-align: middle;
            text-align: center;
            border-left: 1px solid #dee2e6;
        }
        .compare-table th:first-child, .compare-table td:first-child {
            text-align: left;
            font-weight: 700;
            background-color: #f8f9fa;
            position: sticky;
            left: 0;
            z-index: 2;
            border-left: none;
        }
        .compare-table thead th {
            position: sticky;
            top: 0;
            background: #fff;
            z-index: 3;
            box-shadow: 0 2px 2px -1px rgba(0, 0, 0, 0.1);
        }
        .compare-table .product-name { font-size: 1.1rem; font-weight: 700; }
        .compare-table .product-price { font-size: 1.2rem; font-weight: 700; color: #c53030; }
        .compare-table .product-image { max-width: 150px; margin: 0 auto; border-radius: 8px; }
        .compare-table .spec-value { font-weight: 500; }
        .compare-table .spec-value.missing { color: #adb5bd; }
        .remove-btn-cell {
            height: 60px; /* Fixed height for the remove button cell */
        }
        .no-products {
            border: 2px dashed #e0e0e0;
            padding: 3rem;
            text-align: center;
            border-radius: 12px;
        }
    </style>
</head>
<body>

<?php include 'main/header/pre-header.php'; ?>
<?php include 'main/header/danhmuc.php'; ?>

<div class="container my-5">
    <h2 class="text-center mb-4 font-weight-bold">Bảng so sánh xe</h2>

    <?php if (empty($products)): ?>
        <div class="no-products bg-white">
            <h4 class="text-muted">Chưa có xe nào trong danh sách so sánh</h4>
            <p>Vui lòng quay lại trang sản phẩm và chọn xe để so sánh.</p>
            <a href="products.php" class="btn btn-primary">Xem tất cả xe</a>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table compare-table">
                <thead>
                    <tr>
                        <th style="width: 20%;">Sản phẩm</th>
                        <?php foreach ($products as $product): ?>
                            <th style="width: <?= 80 / count($products) ?>%;">
                                <a href="chitietxe.php?id=<?= e($product['id']) ?>">
                                    <img src="<?= e($product['image']) ?>" alt="<?= e($product['name']) ?>" class="product-image img-fluid">
                                </a>
                            </th>
                        <?php endforeach; ?>
                    </tr>
                    <tr>
                        <td>Tên xe</td>
                        <?php foreach ($products as $product): ?>
                            <td>
                                <a href="chitietxe.php?id=<?= e($product['id']) ?>" class="product-name"><?= e($product['name']) ?></a>
                            </td>
                        <?php endforeach; ?>
                    </tr>
                     <tr>
                        <td></td>
                        <?php foreach ($products as $product): ?>
                            <td class="remove-btn-cell">
                                <form action="request_handler.php" method="POST" class="d-inline">
                                    <input type="hidden" name="action" value="compare_remove">
                                    <input type="hidden" name="product_id" value="<?= e($product['id']) ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Xóa</button>
                                </form>
                            </td>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <tr style="background-color: #fff8e1;">
                        <td>Giá bán</td>
                        <?php foreach ($products as $product): ?>
                            <td class="product-price"><?= vnd($product['price_new']) ?></td>
                        <?php endforeach; ?>
                    </tr>
                    <?php foreach ($all_specs as $spec_key): ?>
                        <tr>
                            <td><?= e($spec_key) ?></td>
                            <?php foreach ($products as $product): ?>
                                <td>
                                    <?php if (isset($product['specs'][$spec_key])): ?>
                                        <span class="spec-value"><?= e($product['specs'][$spec_key]) ?></span>
                                    <?php else: ?>
                                        <span class="spec-value missing">-</span>
                                    <?php endif; ?>
                                </td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="text-center mt-4">
             <form action="request_handler.php" method="POST" class="d-inline">
                <input type="hidden" name="action" value="compare_clear">
                <button type="submit" class="btn btn-danger">Xóa tất cả so sánh</button>
            </form>
            <a href="products.php" class="btn btn-outline-secondary">Thêm xe khác</a>
        </div>
    <?php endif; ?>
</div>

<?php include 'main/footer/dichvu.php'; ?>
<?php include 'chatbot.php'; ?>
<?php include 'main/footer/footer.php'; ?>

</body>
</html>
