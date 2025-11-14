<?php
if (!isset($_SESSION)) { session_start(); }
require_once './connect_db.php';

function e($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }

$compare_ids_widget = isset($_SESSION['compare_list']) ? $_SESSION['compare_list'] : [];
$compare_products_widget = [];

if (!empty($compare_ids_widget)) {
    $id_string_widget = implode(',', array_map('intval', $compare_ids_widget));
    $query_widget = "SELECT id, name, image FROM `product` WHERE `id` IN ($id_string_widget)";
    $result_widget = $con->query($query_widget);

    if ($result_widget) {
        while ($row_widget = $result_widget->fetch_assoc()) {
            $compare_products_widget[] = $row_widget;
        }
    }
}
?>

<?php if (!empty($compare_products_widget)): ?>
<style>
    .compare-widget {
        position: fixed;
        bottom: 20px;
        left: 50%;
        transform: translateX(-50%);
        width: 90%;
        max-width: 800px;
        background-color: #2c3e50;
        color: #ecf0f1;
        border-radius: 12px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        display: flex;
        align-items: center;
        padding: 1rem;
        z-index: 1050;
        transition: transform 0.3s ease-in-out, opacity 0.3s ease-in-out;
        opacity: 0;
        transform: translateX(-50%) translateY(20px);
    }
    .compare-widget.show {
        opacity: 1;
        transform: translateX(-50%) translateY(0);
    }
    .compare-widget-title {
        font-weight: 700;
        margin-right: 1rem;
        font-size: 1rem;
        white-space: nowrap;
    }
    .compare-widget-items {
        display: flex;
        gap: 0.75rem;
        flex-grow: 1;
        overflow-x: auto;
        scrollbar-width: none; /* Firefox */
        -ms-overflow-style: none;  /* IE 10+ */
    }
    .compare-widget-items::-webkit-scrollbar { 
        display: none; /* Chrome, Safari, Opera*/ 
    }
    .compare-widget-item {
        position: relative;
        flex-shrink: 0;
    }
    .compare-widget-item img {
        width: 50px;
        height: 50px;
        border-radius: 8px;
        object-fit: cover;
        border: 2px solid #34495e;
    }
    .compare-widget-item .remove-item {
        position: absolute;
        top: -8px;
        right: -8px;
        width: 20px;
        height: 20px;
        background-color: #e74c3c;
        color: white;
        border-radius: 50%;
        border: none;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
        line-height: 1;
        cursor: pointer;
        opacity: 0.8;
        transition: opacity 0.2s;
    }
    .compare-widget-item .remove-item:hover { opacity: 1; }
    .compare-widget-actions {
        display: flex;
        gap: 0.5rem;
        margin-left: 1rem;
        white-space: nowrap;
    }
    @media (max-width: 768px) {
        .compare-widget { flex-direction: column; align-items: stretch; }
        .compare-widget-title { margin-bottom: 0.5rem; }
        .compare-widget-actions { margin-left: 0; margin-top: 0.75rem; justify-content: center;}
    }
</style>

<div id="compare-widget" class="compare-widget">
    <div class="compare-widget-title">Đang so sánh (<span id="compare-widget-count"><?= count($compare_products_widget) ?></span>/4)</div>
    <div class="compare-widget-items">
        <?php foreach ($compare_products_widget as $product): ?>
            <div class="compare-widget-item" data-product-id="<?= e($product['id']) ?>">
                <img src="<?= e($product['image']) ?>" alt="<?= e($product['name']) ?>" title="<?= e($product['name']) ?>">
                <a href="#" class="remove-item" data-product-id="<?= e($product['id']) ?>" title="Xóa">&times;</a>
            </div>
        <?php endforeach; ?>
    </div>
    <div class="compare-widget-actions">
        <a href="sosanh.php" class="btn btn-success btn-sm">So sánh ngay</a>
        <a href="#" class="btn btn-outline-light btn-sm" id="compare-clear-all">Xóa tất cả</a>
    </div>
</div>

<script>
(function() {
    var widget = document.getElementById('compare-widget');
    if (widget) {
        setTimeout(function() {
            widget.classList.add('show');
        }, 100);
    }
})();
</script>

<?php endif; ?>
