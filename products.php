<?php
if (!isset($_SESSION)) session_start();
$__page_start = microtime(true);
require_once './connect_db.php';

function e($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
function vnd($n){ return number_format((float)$n, 0, ',', '.') . ' ₫'; }
function pct($old,$new){ $old=(float)$old; $new=(float)$new; return ($old>0&&$new>0&&$new<$old)?round((1-$new/$old)*100):0; }

// Inputs
$q     = isset($_GET['q']) ? trim($_GET['q']) : '';
$cat   = isset($_GET['cat']) ? (int)$_GET['cat'] : 0; // menu_id
$sort  = isset($_GET['sort']) ? $_GET['sort'] : 'new';
$stock = isset($_GET['stock']) ? $_GET['stock'] : '';// '', 'in','out'
$min   = isset($_GET['min']) ? (int)$_GET['min'] : 0;
$max   = isset($_GET['max']) ? (int)$_GET['max'] : 0;
$page  = max(1, (int)($_GET['page'] ?? 1));
$per   = 12;
$offset= ($page-1)*$per;

// Build where clause
$where = [];
if ($q !== ''){
    $esc = mysqli_real_escape_string($con,$q);
    $where[] = "name LIKE '%$esc%'";
}
if ($cat > 0){
    $where[] = "menu_id = $cat";
}
if ($min > 0){ $where[] = "price_new >= $min"; }
if ($max > 0){ $where[] = "price_new <= $max"; }
if ($stock === 'in'){ $where[] = "quantity > 0"; }
if ($stock === 'out'){ $where[] = "quantity = 0"; }
$whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

// Sort
switch($sort){
  case 'price_asc':  $order = 'ORDER BY price_new ASC'; break;
  case 'price_desc': $order = 'ORDER BY price_new DESC'; break;
  case 'discount':   $order = 'ORDER BY (price-price_new) DESC, id DESC'; break;
  default:           $order = 'ORDER BY id DESC';
}

// Count total
$total_rs = mysqli_query($con, "SELECT COUNT(*) c FROM product $whereSql");
$total = (int)mysqli_fetch_assoc($total_rs)['c'];
$pages = max(1, (int)ceil($total/$per));

// Fetch data
$sql = "SELECT id,name,image,price,price_new,quantity,content FROM product $whereSql $order LIMIT $per OFFSET $offset";
$rs  = mysqli_query($con, $sql);

// Categories for filters
$cats = mysqli_query($con, "SELECT id,name FROM menu_product ORDER BY name ASC");
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <title>Tất cả xe - CarShop</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="icon" href="logo/logo.png">
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
  <link rel="stylesheet" href="slick/slick.css">
  <link rel="stylesheet" href="slick/slick-theme.css">
  <link rel="stylesheet" href="css/index.css">
  <style>
    .filter-bar{background:#fff;border:1px solid #e5e7eb;border-radius:14px;padding:12px;display:flex;flex-wrap:wrap;gap:8px;align-items:center}
    .filter-bar .form-control,.filter-bar .custom-select{height:42px}
    .cat-chip{display:inline-flex;align-items:center;border:1px solid #e5e7eb;border-radius:999px;padding:.25rem .7rem;background:#fff;gap:.4rem}
    .cat-chip.active{background:#0d6efd;color:#fff;border-color:#0d6efd}
    .cat-chip .dot{width:6px;height:6px;border-radius:50%;background:#22c55e}
    .pagination .page-link{border-radius:10px;margin:0 2px}
  </style>
</head>
<body>
  <?php include 'main/header/pre-header.php'; ?>
  <?php include 'main/header/danhmuc.php'; ?>

  <div class="container my-3">
    <div class="sec-head" style="margin-bottom:10px">
      <div>
        <div class="sec-title">Tất cả xe</div>
        <div class="sec-sub">Tổng hợp xe đang bán tại CarShop</div>
      </div>
    </div>
    <form class="filter-bar mb-3" method="get">
      <input class="form-control" style="flex:1;min-width:200px" type="search" name="q" value="<?= e($q) ?>" placeholder="Tìm kiếm theo tên xe...">
      <select class="custom-select" name="sort" style="width:180px">
        <option value="new" <?= $sort==='new'?'selected':'' ?>>Mới nhất</option>
        <option value="discount" <?= $sort==='discount'?'selected':'' ?>>Giảm mạnh</option>
        <option value="price_asc" <?= $sort==='price_asc'?'selected':'' ?>>Giá tăng dần</option>
        <option value="price_desc" <?= $sort==='price_desc'?'selected':'' ?>>Giá giảm dần</option>
      </select>
      <select class="custom-select" name="stock" style="width:160px">
        <option value="" <?= $stock===''?'selected':'' ?>>Tất cả trạng thái</option>
        <option value="in" <?= $stock==='in'?'selected':'' ?>>Còn hàng</option>
        <option value="out" <?= $stock==='out'?'selected':'' ?>>Hết hàng</option>
      </select>
      <input class="form-control" style="width:140px" type="number" name="min" value="<?= $min?:'' ?>" placeholder="Giá từ">
      <input class="form-control" style="width:140px" type="number" name="max" value="<?= $max?:'' ?>" placeholder="Đến">
      <button class="btn btn-primary" type="submit"><i class="bi bi-search"></i> Lọc</button>
      <?php if($cat>0): ?><input type="hidden" name="cat" value="<?= (int)$cat ?>"><?php endif; ?>
    </form>

    <?php $elapsed = round((microtime(true)-$__page_start)*1000); ?>
    <div class="text-muted mb-2 small">Tìm thấy <strong><?= number_format($total) ?></strong> xe (Trang <?= $page ?>/<?= $pages ?>) • <?= $elapsed ?> ms</div>

    <div class="mb-2">
      <?php if ($cats && mysqli_num_rows($cats)>0): while($c = mysqli_fetch_assoc($cats)): ?>
        <a class="cat-chip mb-1 <?= $cat==(int)$c['id']?'active':'' ?>" href="?<?= http_build_query(array_merge($_GET,[ 'cat'=>(int)$c['id'], 'page'=>1 ])) ?>">
          <span class="dot"></span> <?= e($c['name']) ?>
        </a>
      <?php endwhile; endif; ?>
      <?php if($cat>0): ?>
        <a class="cat-chip mb-1" href="?<?= http_build_query(array_merge($_GET,[ 'cat'=>0, 'page'=>1 ])) ?>"><i class="bi bi-x"></i> Bỏ lọc</a>
      <?php endif; ?>
    </div>

    <div class="row">
      <?php if($rs && mysqli_num_rows($rs)>0): while($p = mysqli_fetch_assoc($rs)): $d=pct($p['price'],$p['price_new']); ?>
        <div class="col-lg-3 col-md-4 col-sm-6 mb-3">
          <div class="card-prod h-100">
            <a class="media-prod" href="chitietxe.php?id=<?= (int)$p['id'] ?>">
              <?php if($d>0): ?><div class="badge-sale">-<?= $d ?>%</div><?php endif; ?>
              <?php if((int)$p['quantity']<=3 && (int)$p['quantity']>0): ?><div class="badge-low">Sắp hết</div><?php endif; ?>
              <img loading="lazy" src="<?= e($p['image']) ?>" alt="<?= e($p['name']) ?>">
              <div class="quick-view">
                <button type="button" class="quick-view-btn" data-toggle="modal" data-target="#quickViewModal"
                  data-id="<?= (int)$p['id'] ?>"
                  data-name="<?= e($p['name']) ?>"
                  data-image="<?= e($p['image']) ?>"
                  data-price="<?= vnd($p['price_new']) ?>"
                  data-oldprice="<?= vnd($p['price']) ?>"
                  data-qty="<?= (int)$p['quantity'] ?>"
                  data-desc="<?= e(mb_strimwidth(strip_tags($p['content']??''),0,160,'...','UTF-8')) ?>"
                >
                  <i class="bi bi-eye"></i> Xem nhanh
                </button>
              </div>
            </a>
            <div class="body-prod">
              <div class="name-prod mb-1"><?= e($p['name']) ?></div>
              <div class="price-row">
                <div class="price-new"><?= vnd($p['price_new']) ?></div>
                <?php if($d>0): ?><div class="price-old"><?= vnd($p['price']) ?></div><?php endif; ?>
              </div>
              <div class="card-actions">
                <a class="btn btn-outline" href="chitietxe.php?id=<?= (int)$p['id'] ?>"><i class="bi bi-eye"></i> Chi tiết</a>
                <a class="btn btn-buy" href="chitietxe.php?id=<?= (int)$p['id'] ?>"><i class="bi bi-bag"></i> Xem báo giá</a>
              </div>
            </div>
          </div>
        </div>
      <?php endwhile; else: ?>
        <div class="col-12 text-center text-muted py-5">Không tìm thấy xe phù hợp.</div>
      <?php endif; ?>
    </div>

    <?php if($pages>1): ?>
    <nav aria-label="pagination" class="mt-2">
      <ul class="pagination justify-content-center">
        <?php if($page>1): $qp=$_GET; $qp['page']=$page-1; ?>
          <li class="page-item"><a class="page-link" href="?<?= http_build_query($qp) ?>">&laquo;</a></li>
        <?php endif; ?>
        <?php for($i=1;$i<=$pages;$i++): $qp=$_GET; $qp['page']=$i; ?>
          <li class="page-item <?= $i==$page?'active':''?>"><a class="page-link" href="?<?= http_build_query($qp) ?>"><?= $i ?></a></li>
        <?php endfor; ?>
        <?php if($page<$pages): $qp=$_GET; $qp['page']=$page+1; ?>
          <li class="page-item"><a class="page-link" href="?<?= http_build_query($qp) ?>">&raquo;</a></li>
        <?php endif; ?>
      </ul>
    </nav>
    <?php endif; ?>
  </div>

  <?php include 'main/footer/dichvu.php'; ?>
  <?php include 'main/footer/footer.php'; ?>

  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js"></script>
  <script src="slick/slick.min.js"></script>
  <script>
    // Fill quick view modal
    document.addEventListener('click', function(e){
      var btn = e.target.closest('.quick-view-btn');
      if(!btn) return;
      var modal = document.getElementById('qvModal');
      if(!modal) return;
      modal.querySelector('.qv-title').textContent = btn.dataset.name || '';
      modal.querySelector('.qv-image').src = btn.dataset.image || '';
      modal.querySelector('.qv-price').textContent = btn.dataset.price || '';
      modal.querySelector('.qv-old').textContent = btn.dataset.oldprice || '';
      modal.querySelector('.qv-qty').textContent = (btn.dataset.qty||'0');
      modal.querySelector('.qv-desc').textContent = btn.dataset.desc || '';
      var link = modal.querySelector('.qv-link');
      if(link){ link.href = 'chitietxe.php?id=' + (btn.dataset.id||''); }
    });
  </script>

  <!-- Quick View Modal -->
  <div class="modal fade" id="quickViewModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
      <div class="modal-content" id="qvModal">
        <div class="modal-header">
          <h5 class="modal-title qv-title"></h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="col-md-6 mb-3"><img class="img-fluid rounded qv-image" alt=""></div>
            <div class="col-md-6">
              <div class="mb-2"><strong>Giá:</strong> <span class="qv-price"></span> <small class="text-muted"><s class="qv-old"></s></small></div>
              <div class="mb-2"><strong>Tồn kho:</strong> <span class="qv-qty"></span></div>
              <p class="qv-desc text-muted"></p>
              <a class="btn btn-primary qv-link" href="#">Mở chi tiết</a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

<?php include 'compare-widget.php'; ?>

</body>
</html>
