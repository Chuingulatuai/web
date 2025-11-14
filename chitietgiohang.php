<?php
if (!isset($_SESSION)) {
    session_start();
}

include './connect_db.php';

$cart = $_SESSION['cart'] ?? [];

// Lấy thông tin sản phẩm trong giỏ hàng
$products_in_cart = [];
$total_price = 0;
$item_count = 0;
$product_ids_in_cart = !empty($cart) ? array_keys($cart) : [0];

$product_ids_string = implode(',', array_map('intval', $product_ids_in_cart));
$result = mysqli_query($con, "SELECT * FROM `product` WHERE `id` IN ($product_ids_string)");
while ($row = mysqli_fetch_assoc($result)) {
    $products_in_cart[$row['id']] = $row;
}

// Tính tổng tiền và số lượng
foreach ($cart as $id => $item) {
    if (isset($products_in_cart[$id])) {
        $total_price += $item['quantity'] * $products_in_cart[$id]['price_new'];
        $item_count += $item['quantity'];
    }
}

// Lấy sản phẩm cho mục "Có thể bạn sẽ thích"
$suggest_query = mysqli_query($con, "SELECT * FROM `product` WHERE `id` NOT IN ($product_ids_string) ORDER BY RAND() LIMIT 4");

?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <title>Giỏ hàng của bạn - CarShop</title>
    <meta name="description" content="Kiểm tra và thanh toán giỏ hàng của bạn tại CarShop.">
    <meta charset="UTF-8">
    <link rel="icon" type="image/png" href="logo/logo.png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- CSS & Fonts -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.3/font/bootstrap-icons.css?v=1.8.3">
    <link rel="stylesheet" href="css/home.css">
    <link rel="stylesheet" href="css/index.css">
    <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        body {
            background-color: #f4f6f8;
            font-family: 'Be Vietnam Pro', sans-serif;
        }
        .page-wrap {
            padding: 40px 0;
        }
        .section-title {
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 25px;
            color: #222;
        }
        .cart-header {
            font-size: 2.2rem;
            font-weight: 700;
            margin-bottom: 10px;
        }
        .cart-item-count-info {
            font-size: 1rem;
            color: #555;
            margin-bottom: 30px;
        }
        .cart-item {
            background: #fff;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            border: 1px solid #e9ecef;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        .cart-item.is-loading {
            opacity: 0.5;
            pointer-events: none;
        }
        .cart-item-img img {
            width: 110px;
            height: 110px;
            object-fit: cover;
            border-radius: 8px;
        }
        .cart-item-details {
            flex-grow: 1;
            padding: 0 25px;
        }
        .cart-item-details h5 {
            font-weight: 600;
            font-size: 1.1rem;
            margin-bottom: 5px;
        }
        .price-new {
            font-weight: 700;
            color: #d70018;
            font-size: 1.1rem;
        }
        .quantity-control {
            display: flex;
            align-items: center;
            width: 120px;
        }
        .quantity-btn {
            width: 38px; height: 38px; border: 1px solid #ced4da; background-color: #fff;
            cursor: pointer; font-size: 1.2rem; line-height: 35px; text-align: center;
            transition: background-color 0.2s; border-radius: 50%;
        }
        .quantity-btn:hover { background-color: #f8f9fa; }
        .quantity-input { width: 50px; height: 38px; text-align: center; border: none; font-weight: 600; font-size: 1.1rem; }
        .item-total-price { font-weight: 700; font-size: 1.2rem; width: 130px; text-align: right; color: #212529; }
        .delete-btn {
            color: #6c757d; font-size: 1.4rem; text-decoration: none; transition: all 0.2s;
            background: #f8f9fa; border-radius: 50%; width: 40px; height: 40px;
            display: inline-flex; align-items: center; justify-content: center;
        }
        .delete-btn:hover { color: #fff; background: #dc3545; }
        .summary-card {
            background: #fff; border-radius: 12px; padding: 25px; border: 1px solid #e9ecef;
            position: sticky; top: 20px;
        }
        .summary-card h4 { font-weight: 700; margin-bottom: 25px; }
        .summary-row { display: flex; justify-content: space-between; margin-bottom: 18px; font-size: 1rem; color: #495057; }
        .summary-row span:last-child { font-weight: 600; }
        .summary-total { font-weight: 700; font-size: 1.3rem; border-top: 1px solid #dee2e6; padding-top: 20px; color: #212529; }
        .checkout-btn {
            display: flex; align-items: center; justify-content: center; width: 100%; padding: 14px;
            font-size: 1.1rem; font-weight: 700; border-radius: 8px; margin-bottom: 12px; transition: all 0.3s; gap: 10px;
        }
        .checkout-btn:hover { transform: translateY(-2px); box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        .btn-cod { background-color: #007bff; color: #fff; }
        .btn-momo { background-color: #a50064; color: #fff; }
        .empty-cart { text-align: center; padding: 80px 20px; background: #fff; border-radius: 12px; }
        .empty-cart i { font-size: 5rem; color: #007bff; }
        .empty-cart h3 { margin-top: 20px; font-weight: 700; }
        .continue-shopping { margin-top: 20px; }
        
        /* Toast Notification */
        #toast-container { position: fixed; top: 20px; right: 20px; z-index: 1055; }
        .toast { min-width: 300px; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); opacity: 0; transition: opacity 0.3s, transform 0.3s; transform: translateY(-20px); }
        .toast.show { opacity: 1; transform: translateY(0); }
        .toast-header { font-weight: 600; }

        /* Added Content Sections */
        .extra-content-section { padding: 60px 0; }
        .trust-badge-section { background-color: #fff; padding: 40px 0; border-radius: 12px; margin-top: 40px; }
        .trust-badge {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
        }
        .trust-badge .bi {
            font-size: 2.5rem;
            color: #007bff;
            margin-bottom: 15px;
        }
        .trust-badge h6 { font-weight: 600; }
        .trust-badge p { font-size: 0.9rem; color: #6c757d; }

        .product-card {
            background: #fff; border-radius: 12px; border: 1px solid #e9ecef;
            transition: all 0.3s ease; text-decoration: none; color: #212529;
            display: block; overflow: hidden;
        }
        .product-card:hover { transform: translateY(-5px); box-shadow: 0 8px 25px rgba(0,0,0,0.08); }
        .product-card img { width: 100%; height: 200px; object-fit: cover; }
        .product-card-body { padding: 15px; }
        .product-card-body h5 { font-size: 1rem; font-weight: 600; margin-bottom: 10px; height: 40px; }
        .product-card-body .price-new { font-size: 1.1rem; }
    </style>
</head>

<body>
    <div id="toast-container" aria-live="polite" aria-atomic="true"></div>

    <?php include 'main/header/pre-header.php'; ?>
    <?php include 'main/header/danhmuc.php'; ?>

    <section class="content my-4 page-wrap">
        <div class="container">

            <?php
            // Display any errors related to MoMo or cart issues
            $error_message = $_SESSION['momo_error'] ?? $_SESSION['cart_error'] ?? null;
            if ($error_message): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle-fill"></i> <?= htmlspecialchars($error_message) ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            <?php 
                unset($_SESSION['momo_error']);
                unset($_SESSION['cart_error']);
            endif; 
            ?>

            <?php if (empty($cart)): ?>
                <div class="empty-cart">
                    <i class="bi bi-cart-x"></i>
                    <h3>Giỏ hàng của bạn đang trống</h3>
                    <p>Có vẻ như bạn chưa thêm sản phẩm nào. Hãy khám phá ngay!</p>
                    <a href="index.php" class="btn btn-primary btn-lg continue-shopping">Khám phá sản phẩm</a>
                </div>
            <?php else: ?>
                <h1 class="cart-header">Giỏ hàng</h1>
                <p class="cart-item-count-info">Bạn đang có <strong id="cart-item-count"><?= $item_count ?></strong> sản phẩm trong giỏ hàng.</p>
                <div class="row">
                    <div class="col-lg-8" id="cart-items-container">
                        <!-- Cart items will be listed here by PHP -->
                        <?php foreach ($cart as $id => $item):
                            if (!isset($products_in_cart[$id])) continue;
                            $product = $products_in_cart[$id];
                            $item_total = $item['quantity'] * $product['price_new'];
                        ?>
                            <div class="cart-item" id="cart-item-<?= $id ?>">
                                <div class="cart-item-img">
                                    <a href="chitietxe.php?id=<?= $id ?>"><img src="<?= htmlspecialchars($product['image']) ?>" alt="<?= htmlspecialchars($product['name']) ?>"></a>
                                </div>
                                <div class="cart-item-details">
                                    <h5><a href="chitietxe.php?id=<?= $id ?>" class="text-dark"><?= htmlspecialchars($product['name']) ?></a></h5>
                                    <span class="price-new"><?= number_format($product['price_new']) ?>đ</span>
                                    <?php if ($product['price'] > $product['price_new']): ?><small class="text-muted"><s><?= number_format($product['price']) ?>đ</s></small><?php endif; ?>
                                </div>
                                <div class="quantity-control">
                                    <button type="button" class="quantity-btn quantity-decrease" data-id="<?= $id ?>">-</button>
                                    <input type="number" class="quantity-input" value="<?= $item['quantity'] ?>" min="1" max="<?= $product['quantity'] ?>" data-id="<?= $id ?>">
                                    <button type="button" class="quantity-btn quantity-increase" data-id="<?= $id ?>">+</button>
                                </div>
                                <div class="item-total-price ml-4" id="item-total-<?= $id ?>"><?= number_format($item_total) ?>đ</div>
                                <button class="delete-btn ml-4" data-id="<?= $id ?>" title="Xóa sản phẩm"><i class="bi bi-trash3"></i></button>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="col-lg-4">
                        <div class="summary-card">
                            <h4>Tóm tắt đơn hàng</h4>
                            <div class="summary-row"><span>Tạm tính</span><span id="summary-subtotal"><?= number_format($total_price) ?>đ</span></div>
                            <div class="summary-row"><span>Phí vận chuyển</span><span>Miễn phí</span></div>
                            <div class="form-group"><label for="coupon">Mã giảm giá</label><div class="input-group coupon-form"><input type="text" class="form-control" id="coupon" placeholder="Nhập mã giảm giá"><div class="input-group-append"><button class="btn btn-outline-secondary" type="button">Áp dụng</button></div></div></div>
                            <hr class="my-4">
                            <div class="summary-row summary-total"><span>Tổng cộng</span><span id="summary-total"><?= number_format($total_price) ?>đ</span></div>
                            <?php if (isset($_SESSION['user'])): ?>
                                <a href="dathang.php" class="btn checkout-btn btn-cod"><i class="bi bi-truck"></i> Thanh toán COD</a>
                                <a href="dat_hang_momo.php?total_price=<?= $total_price ?>" class="btn checkout-btn btn-momo"><i class="bi bi-wallet2"></i> Thanh toán MoMo</a>
                            <?php else: ?>
                                <a href="login.php?redirect=chitietgiohang.php" class="btn btn-primary checkout-btn"><i class="bi bi-box-arrow-in-right"></i> Đăng nhập để thanh toán</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Added Content Section -->
    <section class="extra-content-section">
        <div class="container">
            <h2 class="section-title text-center">Có thể bạn sẽ thích</h2>
            <div class="row">
                <?php while($p = mysqli_fetch_assoc($suggest_query)): ?>
                <div class="col-lg-3 col-md-6 mb-4">
                    <a href="chitietxe.php?id=<?= (int)$p['id'] ?>" class="product-card">
                        <img src="<?= htmlspecialchars($p['image']) ?>" alt="<?= htmlspecialchars($p['name']) ?>">
                        <div class="product-card-body">
                            <h5><?= htmlspecialchars($p['name']) ?></h5>
                            <p class="price-new"><?= number_format($p['price_new']) ?>đ</p>
                        </div>
                    </a>
                </div>
                <?php endwhile; ?>
            </div>

            <div class="trust-badge-section">
                <div class="row">
                    <div class="col-md-4">
                        <div class="trust-badge">
                            <i class="bi bi-shield-check"></i>
                            <h6>Thanh toán an toàn</h6>
                            <p>Bảo mật thông tin thanh toán của bạn là ưu tiên hàng đầu của chúng tôi.</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="trust-badge">
                            <i class="bi bi-truck"></i>
                            <h6>Giao hàng toàn quốc</h6>
                            <p>Chúng tôi hỗ trợ giao xe tận nơi trên khắp các tỉnh thành Việt Nam.</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="trust-badge">
                            <i class="bi bi-headset"></i>
                            <h6>Hỗ trợ 24/7</h6>
                            <p>Đội ngũ tư vấn viên chuyên nghiệp luôn sẵn sàng hỗ trợ bạn.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php include 'chatbot.php'; ?>
    <?php include 'main/footer/dichvu.php'; ?>
    <?php include 'main/footer/footer.php'; ?>

    <div id="compare-widget-container">
      <?php include 'compare-widget.php'; ?>
    </div>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js"></script>
    <script src="js/main.js"></script>
    
    <script>
    // AJAX script for cart updates on this page
    $(document).ready(function() {
        let updateTimeout;

        function showToast(message, isSuccess) {
            const toastId = 'toast-' + Date.now();
            const toastHtml = `
                <div id="${toastId}" class="toast" role="alert" aria-live="assertive" aria-atomic="true" data-delay="3000">
                    <div class="toast-header">
                        <strong class="mr-auto">${isSuccess ? '<i class="bi bi-check-circle-fill text-success"></i> Thành công' : '<i class="bi bi-x-circle-fill text-danger"></i> Lỗi'}</strong>
                        <button type="button" class="ml-2 mb-1 close" data-dismiss="toast" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    </div>
                    <div class="toast-body">${message}</div>
                </div>`;
            $('#toast-container').append(toastHtml);
            const toastElement = $('#' + toastId);
            toastElement.toast('show');
            toastElement.on('hidden.bs.toast', function () { $(this).remove(); });
        }

        function updateCart(id, quantity, action) {
            const itemElement = $('#cart-item-' + id);
            itemElement.addClass('is-loading');

            $.ajax({
                url: 'update_cart_ajax.php',
                type: 'POST',
                data: { id, quantity, action },
                dataType: 'json',
                success: function(response) {
                    if(response.message) showToast(response.message, response.success);

                    if (response.success) {
                        // Update header cart count
                        $('.js-cart-count').text(response.data.itemCount);
                        
                        $('#summary-subtotal, #summary-total').text(response.data.totalPriceFormatted);
                        $('#cart-item-count').text(response.data.itemCount);

                        if (action === 'delete' || (action === 'update' && quantity <= 0)) {
                            itemElement.fadeOut(300, function() { $(this).remove(); });
                        } else {
                            $('#item-total-' + id).text(response.data.itemTotalPriceFormatted);
                            $('.quantity-input[data-id="' + id + '"]').val(response.data.newQuantity);
                        }
                        
                        if (response.data.itemCount === 0 && $('.cart-item').length <= 1) {
                            setTimeout(() => location.reload(), 350);
                        }
                    }
                },
                error: function() { showToast('Đã có lỗi xảy ra. Vui lòng thử lại.', false); },
                complete: function() { itemElement.removeClass('is-loading'); }
            });
        }

        $('.quantity-decrease').on('click', function() {
            const id = $(this).data('id');
            const input = $('.quantity-input[data-id="' + id + '"]');
            let quantity = parseInt(input.val()) - 1;
            if (quantity >= 0) {
                input.val(quantity);
                updateCart(id, quantity, 'update');
            }
        });

        $('.quantity-increase').on('click', function() {
            const id = $(this).data('id');
            const input = $('.quantity-input[data-id="' + id + '"]');
            let quantity = parseInt(input.val()) + 1;
            input.val(quantity);
            updateCart(id, quantity, 'update');
        });

        $('.quantity-input').on('input', function() {
            clearTimeout(updateTimeout);
            const id = $(this).data('id');
            const quantity = parseInt($(this).val());
            if (isNaN(quantity) || quantity < 1) return;
            updateTimeout = setTimeout(() => { updateCart(id, quantity, 'update'); }, 500);
        });

        $('.delete-btn').on('click', function(e) {
            e.preventDefault();
            const id = $(this).data('id');
            if (confirm('Bạn có chắc muốn xóa sản phẩm này khỏi giỏ hàng?')) {
                updateCart(id, 0, 'delete');
            }
        });
    });
    </script>
</body>
</html>