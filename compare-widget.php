<?php
// This file should be included at the end of the body tag

$compare_ids_widget = isset($_SESSION['compare_list']) ? $_SESSION['compare_list'] : [];
$compare_products_widget = [];

if (!empty($compare_ids_widget)) {
    // Ensure the database connection is available
    if (!isset($con) || !$con) {
        // Include connect_db.php if $con is not set. 
        // Using require_once to avoid re-including if it's already there.
        require_once __DIR__ . './../connect_db.php';
    }

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
    <div class="compare-widget-title">Đang so sánh (<?= count($compare_products_widget) ?>/4)</div>
    <div class="compare-widget-items">
        <?php foreach ($compare_products_widget as $product): ?>
            <div class="compare-widget-item">
                <img src="<?= e($product['image']) ?>" alt="<?= e($product['name']) ?>" title="<?= e($product['name']) ?>">
                <form action="request_handler.php" method="POST" class="d-inline">
                    <input type="hidden" name="action" value="compare_remove">
                    <input type="hidden" name="product_id" value="<?= e($product['id']) ?>">
                    <button type="submit" class="remove-item" title="Xóa">&times;</button>
                </form>
            </div>
        <?php endforeach; ?>
    </div>
    <div class="compare-widget-actions">
        <a href="sosanh.php" class="btn btn-success btn-sm">So sánh ngay</a>
        <form action="request_handler.php" method="POST" class="d-inline">
            <input type="hidden" name="action" value="compare_clear">
            <button type="submit" class="btn btn-outline-light btn-sm">Xóa tất cả</button>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var widget = document.getElementById('compare-widget');
    if (widget) {
        // Use a short delay to allow the page to render first
        setTimeout(function() {
            widget.classList.add('show');
        }, 300);
    }
});
</script>

<?php endif; ?>
