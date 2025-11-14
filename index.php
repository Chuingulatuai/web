<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once './connect_db.php';

function e($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
function vnd($n){ return number_format((float)$n, 0, ',', '.') . ' ₫'; }
function pct($old,$new){ $old=(float)$old; $new=(float)$new; return ($old>0&&$new>0&&$new<$old)?round((1-$new/$old)*100):0; }

$cats    = mysqli_query($con, "SELECT id,name FROM menu_product ORDER BY id ASC");
$hot     = mysqli_query($con, "SELECT id,name,image,price,price_new,quantity FROM product ORDER BY (price-price_new) DESC, id DESC LIMIT 12");
$newest  = mysqli_query($con, "SELECT id,name,image,price,price_new,quantity FROM product ORDER BY id DESC LIMIT 12");
$youMay  = mysqli_query($con, "SELECT id,name,image,price,price_new,quantity FROM product ORDER BY RAND() LIMIT 12");

// Map danh mục theo tên để điều hướng brand strip
$catMap = [];
if ($cats) {
  mysqli_data_seek($cats, 0);
  while ($__c = mysqli_fetch_assoc($cats)) {
    $catMap[mb_strtolower($__c['name'], 'UTF-8')] = (int)$__c['id'];
  }
  mysqli_data_seek($cats, 0);
}
function brand_href($name, $map) {
  $key = mb_strtolower($name, 'UTF-8');
  if (isset($map[$key])) return 'danhsachsp.php?id='.(int)$map[$key];
  return 'products.php?q='.urlencode($name);
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <title>CarShop – Mua bán ô tô & phụ tùng chính hãng</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="icon" href="logo/logo.png">
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.3/font/bootstrap-icons.css?v=1.8.3">
  <link rel="stylesheet" href="slick/slick.css">
  <link rel="stylesheet" href="slick/slick-theme.css">
  <link rel='stylesheet' href='css/index.css'>
  <link rel='stylesheet' href='css/home.css'>
  <style>
    /* ===== BRAND STRIP STYLING ===== */
    .brand-strip {
      background: linear-gradient(90deg, #f9fafb 0%, #fff 50%, #f9fafb 100%);
      border-top: 1px solid #e5e7eb;
      border-bottom: 1px solid #e5e7eb;
      padding: 12px 0;
      overflow: hidden;
      margin: 20px 0;
      width: 100%;
    }

    .brand-strip .container {
      overflow: hidden;
      width: 100%;
      max-width: 100%;
      padding: 0 15px;
    }

    .brand-slider {
      display: flex;
      gap: 25px;
      animation: scrollBrandSlider 55s linear infinite;
      width: max-content;
      white-space: nowrap;
      padding: 0;
      margin: 0;
    }

    .brand-slider:hover {
      animation-play-state: paused;
    }

    .brand-item {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      flex-shrink: 0;
      height: 55px;
      min-width: 110px;
      text-decoration: none;
      transition: all 0.3s ease;
      padding: 0 5px;
    }

    .brand-item img {
      height: 42px;
      width: auto;
      max-width: 100px;
      object-fit: contain;
      opacity: 0.7;
      filter: grayscale(100%);
      transition: all 0.3s ease;
    }

    .brand-item:hover img {
      opacity: 1;
      filter: grayscale(0%);
      transform: scale(1.1);
    }

    @keyframes scrollBrandSlider {
      0% { transform: translateX(0); }
      100% { transform: translateX(calc(-100% - 25px)); }
    }

    @media (max-width: 768px) {
      .brand-slider { gap: 18px; animation: scrollBrandSlider 45s linear infinite; }
      .brand-item { min-width: 90px; height: 45px; }
      .brand-item img { height: 32px; }
    }

    @media (max-width: 480px) {
      .brand-strip { padding: 10px 0; margin: 15px 0; }
      .brand-slider { gap: 15px; animation: scrollBrandSlider 40s linear infinite; }
      .brand-item { min-width: 75px; height: 40px; }
      .brand-item img { height: 28px; }
    }
  </style>
</head>
<body>

<?php include 'main/header/pre-header.php'; ?>
<?php include 'main/header/danhmuc.php'; ?>

<div class="hero-top">
  <div class="container hero-inner">
    <div class="row align-items-center">
      <div class="col-lg-8">
        <div class="chips mb-2">
          <?php
          if ($cats && mysqli_num_rows($cats) > 0):
            mysqli_data_seek($cats, 0);
            $i = 0;
            while ($i < 6 && ($c = mysqli_fetch_assoc($cats))):
          ?>
              <a class="chip" href="danhsachsp.php?id=<?= (int)$c['id'] ?>">
                <span class="dot"></span><?= e($c['name']) ?>
              </a>
          <?php
              $i++;
            endwhile;
          else:
          ?>
            <span class="text-white-50 small">Đang cập nhật danh mục…</span>
          <?php endif; ?>
        </div>
      </div>
      <div class="col-lg-4 text-right d-none d-lg-block">
        <small class="text-white-50">Ưu đãi trả góp • Giao xe toàn quốc • CSKH 24/7</small>
      </div>
    </div>

    <div class="banner-wrap mt-2">
      <?php include 'main/header/banner.php'; ?>
    </div>

    <!-- NEW: Hero CTA Section -->
    <div class="hero-cta mt-4">
      <h2>Tìm chiếc xe của bạn ngay hôm nay</h2>
      <div class="cta-buttons">
        <a href="products.php" class="btn btn-primary btn-lg">
          <i class="bi bi-search"></i> Xem Xe Mới
        </a>
        <a href="products.php" class="btn btn-outline-light btn-lg">
          <i class="bi bi-calculator"></i> So Sánh Giá
        </a>
      </div>
    </div>
  </div>
</div>

<!-- Brand trust slider below hero -->
<div class="brand-strip">
  <div class="container">
    <div class="brand-slider">
      <?php
        $brands = [
          ['Vinfast','images/brands/vinfast.svg'],
          ['Mercedes','images/brands/mercedes.svg'],
          ['Toyota','images/brands/toyota.svg'],
          ['Honda','images/brands/honda.svg'],
          ['BMW','images/brands/bmw.svg'],
          ['Hyundai','images/brands/hyundai.svg'],
          ['Kia','images/brands/kia.svg'],
          ['Ford','images/brands/ford.svg'],
          ['Mazda','images/brands/mazda.svg'],
          ['Chevrolet','images/brands/chevrolet.svg'],
          ['Volvo','images/brands/volvo.svg'],
          ['Audi','images/brands/audi.svg'],
        ];
        $loop = array_merge($brands, $brands);
        foreach ($loop as $b) {
          $name=$b[0]; $img=$b[1]; $href = brand_href($name, $catMap);
          echo '<a class="brand-item" href="'.e($href).'" title="'.e($name).'">'
            .'<img src="'.e($img).'" alt="'.e($name).'">'
            .'</a>';
        }
      ?>
    </div>
  </div>
</div>

<div class="container">

  <!-- NEW: Quick Search Bar -->
  <div class="quick-search-section">
    <form class="quick-search-bar" id="quick-search-form">
      <div class="search-inputs">
        <div class="search-group">
          <i class="bi bi-search"></i>
          <input type="text" id="quick-search-input" name="q" placeholder="Nhập tên xe, hãng...">
        </div>
        <select class="search-select">
          <option>Tất cả danh mục</option>
          <option>Xe SUV</option>
          <option>Xe Sedan</option>
          <option>Xe Bán Tải</option>
        </select>
        <select class="search-select">
          <option>Giá: Tất cả</option>
          <option>Dưới 500 triệu</option>
          <option>500-700 triệu</option>
          <option>700-1 tỷ</option>
          <option>Trên 1 tỷ</option>
        </select>
        <button type="submit" class="btn btn-primary">
          <i class="bi bi-search"></i> Tìm kiếm
        </button>
      </div>
    </form>
  </div>

  <!-- Deal sốc hôm nay -->
  <div class="section">
    <div class="sec-head">
      <div>
        <div class="sec-title">🔥 Deal sốc hôm nay</div>
        <div class="sec-sub">Giảm mạnh, số lượng giới hạn</div>
      </div>
      <a class="see-all" href="products.php">Xem tất cả</a>
    </div>
    <div class="row">
      <?php while($p = mysqli_fetch_assoc($hot)): $d = pct($p['price'],$p['price_new']); ?>
      <div class="col-lg-3 col-md-6 col-sm-12 mb-4">
        <div class="card-prod">
          <a class="media-prod" href="chitietxe.php?id=<?= (int)$p['id'] ?>">
            <?php if($d>0): ?><div class="badge-sale">-<?= $d ?>%</div><?php endif; ?>
            <?php if((int)$p['quantity']<=3 && (int)$p['quantity']>0): ?><div class="badge-low">Sắp hết</div><?php endif; ?>
            <img loading="lazy" src="<?= e($p['image']) ?>" alt="<?= e($p['name']) ?>">
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
            <div class="card-footer-actions">
              <button class="btn-wishlist" type="button" title="Thêm yêu thích" aria-pressed="false">
                <i class="bi bi-heart"></i>
              </button>
              <button class="btn-compare" title="So sánh">
                <i class="bi bi-arrow-left-right"></i>
              </button>
              <button class="btn-contact" title="Liên hệ ngay">
                <i class="bi bi-telephone"></i>
              </button>
            </div>
          </div>
        </div>
      </div>
      <?php endwhile; ?>
    </div>
  </div>

  <!-- Mới cập nhật -->
  <div class="section">
    <div class="sec-head">
      <div>
        <div class="sec-title">✨ Mới cập nhật</div>
        <div class="sec-sub">Những mẫu vừa về showroom</div>
      </div>
      <a class="see-all" href="products.php">Xem tất cả</a>
    </div>
    <div class="row">
      <?php while($p = mysqli_fetch_assoc($newest)): $d = pct($p['price'],$p['price_new']); ?>
      <div class="col-lg-3 col-md-6 col-sm-12 mb-4">
        <div class="card-prod">
          <a class="media-prod" href="chitietxe.php?id=<?= (int)$p['id'] ?>">
            <?php if($d>0): ?><div class="badge-sale">-<?= $d ?>%</div><?php endif; ?>
            <img loading="lazy" src="<?= e($p['image']) ?>" alt="<?= e($p['name']) ?>">
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
            <div class="card-footer-actions">
              <button class="btn-wishlist" type="button" title="Thêm yêu thích" aria-pressed="false">
                <i class="bi bi-heart"></i>
              </button>
              <button class="btn-compare" title="So sánh">
                <i class="bi bi-arrow-left-right"></i>
              </button>
              <button class="btn-contact" title="Liên hệ ngay">
                <i class="bi bi-telephone"></i>
              </button>
            </div>
          </div>
        </div>
      </div>
      <?php endwhile; ?>
    </div>
  </div>

  <div class="section">
    <div class="sec-head">
      <div>
        <div class="sec-title">💡 Gợi ý cho bạn</div>
        <div class="sec-sub">Phù hợp đa nhu cầu – tầm giá</div>
      </div>
      <a class="see-all" href="products.php">Xem tất cả</a>
    </div>
    <div class="row">
      <?php while($p = mysqli_fetch_assoc($youMay)): 
        $d = pct($p['price'],$p['price_new']); 
        $qty = (int)$p['quantity'];
        $saveAmount = $d > 0 ? ($p['price'] - $p['price_new']) : 0;
      ?>
      <div class="col-lg-3 col-md-6 col-sm-12 mb-4">
        <div class="card-prod">
          <a class="media-prod" href="chitietxe.php?id=<?= (int)$p['id'] ?>">
            <?php if($d>0): ?><div class="badge-sale">-<?= $d ?>%</div><?php endif; ?>
            <?php if($qty>0): ?>
              <div class="stock-indicator <?= $qty<=5?'low':'' ?>">
                <span class="dot"></span>
                <span>Còn <?= $qty ?> xe</span>
              </div>
            <?php endif; ?>
            <img loading="lazy" src="<?= e($p['image']) ?>" alt="<?= e($p['name']) ?>">
            <div class="quick-view">
              <a class="quick-view-btn" href="chitietxe.php?id=<?= (int)$p['id'] ?>">
                <i class="bi bi-eye"></i> Xem nhanh
              </a>
            </div>
          </a>
          <div class="body-prod">
            <div class="cat-tag">
              <i class="bi bi-heart-fill"></i>
              <span>GỢI Ý</span>
            </div>
            
            <div class="name-prod"><?= e($p['name']) ?></div>
            
            <div class="rating-row">
              <div class="stars">
                <i class="bi bi-star-fill"></i>
                <i class="bi bi-star-fill"></i>
                <i class="bi bi-star-fill"></i>
                <i class="bi bi-star-fill"></i>
                <i class="bi bi-star-fill"></i>
              </div>
              <span>5.0 (<?= rand(20,80) ?> đánh giá)</span>
            </div>

            <div class="specs-mini">
              <span class="spec-tag"><i class="bi bi-person"></i> <?= rand(4,7) ?> chỗ</span>
              <span class="spec-tag"><i class="bi bi-gear"></i> Tự động</span>
              <span class="spec-tag"><i class="bi bi-shield-check"></i> 5⭐ NCAP</span>
            </div>
            
            <div class="price-section">
              <div class="price-row">
                <div class="price-new"><?= vnd($p['price_new']) ?></div>
                <?php if($d>0): ?><div class="price-old"><?= vnd($p['price']) ?></div><?php endif; ?>
                <?php if($saveAmount>0): ?><div class="save-amount">-<?= vnd($saveAmount) ?></div><?php endif; ?>
              </div>
              <div class="card-actions">
                <a class="btn btn-outline" href="chitietxe.php?id=<?= (int)$p['id'] ?>">
                  <i class="bi bi-info-circle"></i> Chi tiết
                </a>
                <a class="btn btn-buy" href="chitietxe.php?id=<?= (int)$p['id'] ?>">
                  <i class="bi bi-cart-plus"></i> Mua ngay
                </a>
              </div>
            </div>
            <div class="card-footer-actions">
              <button class="btn-wishlist" type="button" title="Thêm yêu thích" aria-pressed="false">
                <i class="bi bi-heart"></i>
              </button>
              <button class="btn-compare" title="So sánh">
                <i class="bi bi-arrow-left-right"></i>
              </button>
              <button class="btn-contact" title="Liên hệ ngay">
                <i class="bi bi-telephone"></i>
              </button>
            </div>
          </div>
        </div>
      </div>
      <?php endwhile; ?>
    </div>
  </div>

  <!-- Why Choose Us -->
  <div class="section">
    <div class="row">
      <div class="col-md-3 col-6 mb-3">
        <div class="why-card text-center"><i class="bi bi-award"></i><div class="why-title">Chính hãng</div><div class="text-muted small">Nguồn gốc rõ ràng</div></div>
      </div>
      <div class="col-md-3 col-6 mb-3">
        <div class="why-card text-center"><i class="bi bi-truck"></i><div class="why-title">Giao toàn quốc</div><div class="text-muted small">Nhanh, an toàn</div></div>
      </div>
      <div class="col-md-3 col-6 mb-3">
        <div class="why-card text-center"><i class="bi bi-credit-card"></i><div class="why-title">Trả góp</div><div class="text-muted small">Hồ sơ linh hoạt</div></div>
      </div>
      <div class="col-md-3 col-6 mb-3">
        <div class="why-card text-center"><i class="bi bi-headset"></i><div class="why-title">Hỗ trợ 24/7</div><div class="text-muted small">Tư vấn tận tâm</div></div>
      </div>
    </div>
  </div>

  <!-- Stats bar - IMPROVED -->
  <div class="section">
    <div class="row text-center stats-bar">
      <div class="col-6 col-md-3 mb-3">
        <div class="stat">
          <div class="stat-icon"><i class="bi bi-shop"></i></div>
          <div class="stat-content">
            <div class="num" data-target="50">0</div>
            <div class="lbl">Showroom/đại lý</div>
            <div class="stat-bar"><div class="bar-fill" style="width: 85%"></div></div>
          </div>
        </div>
      </div>
      <div class="col-6 col-md-3 mb-3">
        <div class="stat">
          <div class="stat-icon"><i class="bi bi-truck"></i></div>
          <div class="stat-content">
            <div class="num" data-target="1200">0</div>
            <div class="lbl">Xe giao thành công</div>
            <div class="stat-bar"><div class="bar-fill" style="width: 92%"></div></div>
          </div>
        </div>
      </div>
      <div class="col-6 col-md-3 mb-3">
        <div class="stat">
          <div class="stat-icon"><i class="bi bi-star"></i></div>
          <div class="stat-content">
            <div class="num" data-target="4.9">0</div>
            <div class="lbl">Điểm hài lòng</div>
            <div class="stat-bar"><div class="bar-fill" style="width: 98%"></div></div>
          </div>
        </div>
      </div>
      <div class="col-6 col-md-3 mb-3">
        <div class="stat">
          <div class="stat-icon"><i class="bi bi-telephone"></i></div>
          <div class="stat-content">
            <div class="num" data-target="24">0</div>
            <div class="lbl">Hỗ trợ 24/7</div>
            <div class="stat-bar"><div class="bar-fill" style="width: 100%"></div></div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Blog/news -->
  <div class="section">
    <div class="sec-head">
      <div>
        <div class="sec-title">📰 Tin tức & mẹo hay</div>
        <div class="sec-sub">Chia sẻ kinh nghiệm, cập nhật xu hướng</div>
      </div>
      <a class="see-all" href="#">Xem tất cả</a>
    </div>
    <div class="row blog-grid">
      <div class="col-md-4 mb-3">
        <a class="blog-card" href="#">
          <img src="images/banner/kia-connect-lineup.jpg" alt="blog1"/>
          <div class="overlay">
            <div class="cat">Công nghệ</div>
            <h3>Kết nối xe thông minh: những điều nên biết</h3>
          </div>
        </a>
      </div>
      <div class="col-md-4 mb-3">
        <a class="blog-card" href="#">
          <img src="images/banner/Mitsubishi-Triton-2024-VnE-1109-JPG.jpg" alt="blog2"/>
          <div class="overlay">
            <div class="cat">Lái xe an toàn</div>
            <h3>Mẹo giữ xe bền bỉ qua mùa mưa</h3>
          </div>
        </a>
      </div>
      <div class="col-md-4 mb-3">
        <a class="blog-card" href="#">
          <img src="images/banner/banner3.jpg" alt="blog3"/>
          <div class="overlay">
            <div class="cat">Xu hướng</div>
            <h3>Top mẫu xe đáng chú ý năm nay</h3>
          </div>
        </a>
      </div>
    </div>
  </div>

  <!-- Testimonials -->
  <div class="section">
    <div class="sec-head">
      <div>
        <div class="sec-title">⭐ Khách hàng nói gì</div>
        <div class="sec-sub">Đánh giá thực tế từ người dùng</div>
      </div>
    </div>
    <div class="row">
      <div class="col-md-4 mb-3">
        <div class="testi-card">
          <div class="stars mb-2">
            <i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i>
          </div>
          <p>Giao xe rất nhanh, đội ngũ tư vấn nhiệt tình. Trải nghiệm tuyệt vời!</p>
          <div class="user">Nguyễn H.</div>
        </div>
      </div>
      <div class="col-md-4 mb-3">
        <div class="testi-card">
          <div class="stars mb-2">
            <i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i>
          </div>
          <p>Mua trả góp đơn giản, thủ tục nhanh. Mức giá cạnh tranh.</p>
          <div class="user">Trần Q.</div>
        </div>
      </div>
      <div class="col-md-4 mb-3">
        <div class="testi-card">
          <div class="stars mb-2">
            <i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i>
          </div>
          <p>Dịch vụ hậu mãi chu đáo, hỗ trợ 24/7 khiến tôi rất yên tâm.</p>
          <div class="user">Minh T.</div>
        </div>
      </div>
    </div>
  </div>

  <!-- CTA test drive -->
  <div class="section">
    <div class="drive-cta">
      <div class="text">
        <h3>Đặt lịch lái thử miễn phí</h3>
        <p>Trải nghiệm thực tế trước khi quyết định.</p>
      </div>
      <a class="btn btn-buy" href="#" data-toggle="modal" data-target="#testDriveModal">Đặt lịch ngay</a>
    </div>
  </div>

  <div class="section">
    <div class="newsletter-cta">
      <div class="nl-wrap">
        <div class="nl-text">
          <div class="nl-title"><i class="bi bi-send"></i> Nhận ưu đãi độc quyền</div>
          <div class="nl-desc">Đăng ký để nhận voucher và thông tin mẫu xe mới mỗi tuần</div>
        </div>
        <form class="nl-form" onsubmit="alert('Cảm ơn bạn đã đăng ký!');return false;">
          <input type="email" placeholder="Email của bạn" required>
          <button type="submit">Đăng ký</button>
        </form>
      </div>
    </div>
  </div>

</div>

<?php include 'chatbot.php'; ?>
<?php include 'main/footer/dichvu.php'; ?>
<?php include 'main/footer/footer.php'; ?>



<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js"></script>
<script src="slick/slick.min.js"></script>
<script>
const catMap = <?= json_encode($catMap) ?>;
$(function(){
  // Quick search handler
  $('#quick-search-form').on('submit', function(e) {
    e.preventDefault();
    const query = $('#quick-search-input').val().trim();
    if (!query) return;

    const lowerCaseQuery = query.toLowerCase();
    if (catMap.hasOwnProperty(lowerCaseQuery)) {
      window.location.href = 'danhsachsp.php?id=' + catMap[lowerCaseQuery];
    } else {
      window.location.href = 'products.php?q=' + encodeURIComponent(query);
    }
  });

  // Brand slider - Pure CSS Animation
  console.log('Brand slider running with pure CSS animation');
  
  // Chips overflow hint toggle
  function updateChipsHint(){
    var el = document.querySelector('.hero-top .chips');
    if(!el) return;
    if(el.scrollWidth > el.clientWidth + 1){ el.classList.add('scrollable'); }
    else { el.classList.remove('scrollable'); }
  }
  updateChipsHint();
  window.addEventListener('resize', updateChipsHint);
  
  // Wishlist toggle
  function updateWishlistCount(){
    var count = document.querySelectorAll('.btn-wishlist.active').length;
    document.querySelectorAll('.js-wishlist-count').forEach(function(el){
      el.textContent = count;
    });
  }

  $(document).on('click', '.btn-wishlist', function(e){
    e.preventDefault();
    $(this).toggleClass('active');
    var isActive = $(this).hasClass('active');
    $(this).attr('aria-pressed', isActive);
    $(this).find('i').css('color', isActive ? '#e74c3c' : 'inherit');
    updateWishlistCount();
  });

  updateWishlistCount();
  
  // Compare button - AJAX version with widget refresh
  $(document).on('click', '.btn-compare', function(e){
    e.preventDefault();
    var button = $(this);
    // Prevent multiple clicks
    if (button.prop('disabled')) {
        return;
    }
    try {
      var productLink = button.closest('.card-prod').find('a.media-prod, a.btn-outline').first().attr('href');
      var match = productLink ? productLink.match(/id=(\d+)/) : null;
      if (match && match[1]) {
        var productId = match[1];
        
        $.ajax({
          url: 'request_handler.php',
          type: 'POST',
          data: {
            action: 'compare_add',
            product_id: productId
          },
          dataType: 'json',
          beforeSend: function() {
            button.prop('disabled', true).css('opacity', 0.6);
          },
          success: function(response) {
            if (response.success) {
              // Visual feedback on the button
              button.find('i').removeClass('bi-arrow-left-right').addClass('bi-check-lg');
              button.css('color', '#28a745');

              // Reload the compare widget content
              $('#compare-widget-container').load('get_compare_widget.php');

            } else {
              alert(response.message || 'Không thể thêm sản phẩm.');
              button.prop('disabled', false).css('opacity', 1);
            }
          },
          error: function() {
            alert('Lỗi kết nối. Vui lòng thử lại.');
            button.prop('disabled', false).css('opacity', 1);
          }
        });
      } else {
        alert('Lỗi: Không tìm thấy ID sản phẩm.');
        button.prop('disabled', false);
      }
    } catch (err) {
      console.error('Compare button error:', err);
      alert('Đã có lỗi xảy ra.');
      button.prop('disabled', false);
    }
  });
  
  // Contact button
  $(document).on('click', '.btn-contact', function(e){
    e.preventDefault();
    alert('Vui lòng gọi: 0123456789 hoặc liên hệ qua Zalo!');
  });
  
  // Animate stats numbers
  function animateStats(){
    document.querySelectorAll('.stats-bar .num').forEach(function(el){
      var target = parseFloat(el.getAttribute('data-target')) || 0;
      var isFloat = (target % 1 !== 0);
      var current = 0; var steps = 60; var i = 0;
      var timer = setInterval(function(){
        i++; current = target * (i/steps);
        el.textContent = isFloat ? current.toFixed(1) : Math.round(current).toLocaleString('vi-VN');
        if(i>=steps){ el.textContent = isFloat ? target.toFixed(1) : target.toLocaleString('vi-VN'); clearInterval(timer); }
      }, 16);
    });
  }
  
  // Trigger when stats in view
  var stats = document.querySelector('.stats-bar');
  if (stats) {
    var seen = false;
    var obs = new IntersectionObserver(function(entries){
      entries.forEach(function(e){ if(e.isIntersecting && !seen){ seen=true; animateStats(); obs.disconnect(); } });
    },{threshold:0.2});
    obs.observe(stats);
  }
  

  
  // test drive form handler
  window.submitTestDrive = function(e){
    e.preventDefault();
    var form = e.target;
    var name = form.querySelector('[name=name]').value.trim();
    var phone = form.querySelector('[name=phone]').value.trim();
    if(!name || !phone){ alert('Vui lòng nhập Họ tên và Số điện thoại'); return; }
    alert('Cảm ơn bạn! Chúng tôi sẽ liên hệ xác nhận lịch lái thử trong thời gian sớm nhất.');
    $('#testDriveModal').modal('hide');
    form.reset();
  }
});
</script>

<!-- Test Drive Modal -->
<div class="modal fade" id="testDriveModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Đặt lịch lái thử</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form onsubmit="submitTestDrive(event)">
        <div class="modal-body">
          <div class="form-group">
            <label>Họ và tên</label>
            <input type="text" name="name" class="form-control" required>
          </div>
          <div class="form-group">
            <label>Số điện thoại</label>
            <input type="tel" name="phone" class="form-control" required>
          </div>
          <div class="form-group">
            <label>Email (không bắt buộc)</label>
            <input type="email" name="email" class="form-control">
          </div>
          <div class="form-row">
            <div class="form-group col-md-6">
              <label>Thời gian</label>
              <input type="datetime-local" name="time" class="form-control" required>
            </div>
            <div class="form-group col-md-6">
              <label>Khu vực</label>
              <select name="city" class="form-control">
                <option>TP. Hồ Chí Minh</option>
                <option>Hà Nội</option>
                <option>Đà Nẵng</option>
                <option>Cần Thơ</option>
              </select>
            </div>
          </div>
          <div class="form-group">
            <label>Mẫu xe quan tâm</label>
            <input type="text" name="model" class="form-control" placeholder="VD: VF8, VF9, ...">
          </div>
          <div class="form-group">
            <label>Ghi chú</label>
            <textarea name="note" class="form-control" rows="3"></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Đóng</button>
          <button type="submit" class="btn btn-primary">Gửi yêu cầu</button>
        </div>
      </form>
    </div>
  </div>
</div>

<div id="compare-widget-container">
  <?php include 'compare-widget.php'; ?>
</div>

</body>
</html>
