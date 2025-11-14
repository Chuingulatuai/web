<?php
if (!isset($_SESSION)) { session_start(); }
require_once './connect_db.php';

/* --------- Input an toàn --------- */
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) { header('Location: /'); exit; }

/* --------- Query sản phẩm --------- */
$stmt = $con->prepare("SELECT * FROM `product` WHERE `id` = ? LIMIT 1");
$stmt->bind_param("i", $id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();
if (!$product) { header('Location: /'); exit; }

/* --------- Gallery ảnh --------- */
$stmtG = $con->prepare("SELECT `id`,`product_id`,`path` FROM `image_library` WHERE `product_id` = ? ORDER BY id ASC");
$stmtG->bind_param("i", $id);
$stmtG->execute();
$gallery_rs = $stmtG->get_result();
$gallery = [];
while ($row = $gallery_rs->fetch_assoc()) { $gallery[] = $row; }

$isOutOfStock = ((int)$product['quantity']) <= 0;
$priceOld = (float)$product['price'];
$priceNew = (float)($product['price_new'] ?: $product['price']);
$youSave  = max(0, $priceOld - $priceNew);

function e($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
function vnd($n){ return number_format((float)$n, 0, ',', '.') . ' ₫'; }

/* ========= MÔ TẢ CHUYÊN NGHIỆP ========= */
$allowed = '<h2><h3><h4><p><br><ul><ol><li><b><strong><em>';
$raw = $product['content'] ?? '';
$sanitized = strip_tags($raw, $allowed);
function slugify($str){ $str = mb_strtolower($str, 'UTF-8'); $str = preg_replace('/[^\p{L}\p{N}]+/u','-',$str); return trim($str,'-'); }
$descHtml = preg_replace_callback('/<(h2|h3)>(.*?)<\/\1>/iu', function($m){
  $text = trim(strip_tags($m[2])); $id = slugify($text);
  return '<'.$m[1].' id="'.$id.'">'.$text.'</'.$m[1].'>';
}, $sanitized);

/* Điểm nổi bật tự trích */
function extract_highlights($html){
  $text = preg_replace('/\s+/u',' ', strip_tags($html));
  $highlights = [];

  // Prioritize key specifications
  $spec_patterns = [
      'Số chỗ ngồi: (\d+\s*người)',
      'Kiểu xe: ([^.\\\n]+)',
      'Kích thước tổng thể[^:]*:[^.\\\n]+', // Capture the whole dimension line
      'Chiều dài cơ sở[^:]*:[^.\\\n]+',
      'Hệ dẫn động: ([^.\\\n]+)',
      'Hộp số: ([^.\\\n]+)',
      'Loại động cơ: ([^.\\\n]+)',
      'Công suất cực đại: ([^.\\\n]+)',
      'Mô-men xoắn cực đại: ([^.\\\n]+)',
      'Tăng tốc 0-100 km/h: ([^.\\\n]+)',
      'Tốc độ tối đa: ([^.\\\n]+)',
      'Dung tích bình nhiên liệu: ([^.\\\n]+)',
      'Mức tiêu thụ nhiên liệu kết hợp: ([^.\\\n]+)',
      'Ngoại thất: ([^.\\\n]+)',
      'Nội thất: ([^.\\\n]+)',
      'An toàn & hỗ trợ lái: ([^.\\\n]+)',
      'Thể tích khoang hành lý: ([^.\\\n]+)',
      'mâm hợp kim ([^.\\\n]+)', // Specific for wheel size
      'Dung tích: ([^.\\\n]+)', // Engine displacement
      'Dung lượng pin: ([^.\\\n]+)', // For electric cars
      'Quãng đường chạy[^:]*:[^.\\\n]+', // Range for electric cars  
      'Sạc nhanh:[^.\\\n]+', // Fast charging
  ];

  foreach ($spec_patterns as $pattern) {
      if (preg_match('~' . $pattern . '~iu', $text, $matches)) {
          // If it's a key-value pair, take the whole match or the captured group
          $highlight = trim($matches[0]);
          // Avoid adding duplicates or very similar highlights
          $is_duplicate = false;
          foreach ($highlights as $existing_hl) {
              if (stripos($existing_hl, $highlight) !== false || stripos($highlight, $existing_hl) !== false) {
                  $is_duplicate = true;
                  break;
              }
          }
          if (!$is_duplicate) {
              $highlights[] = $highlight;
          }
          if (count($highlights) >= 8) break; // Get up to 8 key highlights
      }
  }

  // Fallback: if not enough specific highlights, use general sentences
  if (count($highlights) < 4) {
      $sentences = preg_split('/(?<=[.\!\?])\s+/u', $text, -1, PREG_SPLIT_NO_EMPTY);
      foreach ($sentences as $s) {
          $s = trim($s);
          if (mb_strlen($s) >= 25 && mb_strlen($s) <= 160) {
              // Add sentences that contain numbers or common spec keywords
              if (preg_match('/\d|km|kW|mm|inch|%|pin|động cơ|công suất|tốc độ|phạm vi|khoảng.*sáng|chỗ ngồi|kích thước|hộp số|dẫn động|lít/i', $s)) {
                  $highlights[] = $s;
              }
          }
          if (count($highlights) >= 8) break;
      }
  }
  
  // Ensure unique and limit to 8
  $highlights = array_unique($highlights);
  return array_slice($highlights, 0, 8);
}
$highlights = extract_highlights($descHtml);

// Cải tiến: Tách mô tả thành các phần cho Accordion
if (!function_exists('split_description_into_sections')) {
    function split_description_into_sections($html) {
        $trimmed_html = trim($html);
        if (empty(trim(strip_tags($trimmed_html)))) {
            return [];
        }
        if (!preg_match('/^<h2/i', $trimmed_html)) {
            $trimmed_html = '<h2>Tổng quan</h2>' . $trimmed_html;
        }
        $parts = preg_split('/(<h2[^>]*>.*?<\/h2>)/i', $trimmed_html, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
        
        $sections = [];
        for ($i = 0; $i < count($parts); $i++) {
            if (preg_match('/<h2[^>]*>(.*?)<\/h2>/i', $parts[$i], $matches)) {
                $title = trim($matches[1]);
                $content = isset($parts[$i+1]) ? $parts[$i+1] : '';
                $sections[] = ['title' => $title, 'content' => $content, 'id' => slugify($title)];
                $i++;
            }
        }
        return $sections;
    }
}
$descriptionSections = split_description_into_sections($descHtml);

// Cải tiến: Tự động làm nổi bật thông số trong mô tả (Nâng cấp v2)
function highlight_specs_inline($html) {
    $spec_patterns = [
        // Pattern => [icon, value_group_index, options]
        // Động cơ & Hiệu suất
        '/(Công suất cực đại|Công suất|Công suất & mô-men xoắn):[^\d]*([\d–-]+\s*(mã lực|hp|ps|kw))/i' => ['icon' => '⚡', 'group' => 2],
        '/(Mô-men xoắn cực đại|Mô-men xoắn):[^\d]*([\d–-]+\s*Nm)/i' => ['icon' => '⚙️', 'group' => 2],
        '/(Tăng tốc|Gia tốc)[^:]*:\s*([\d→\s\/kmh.,]+(giây|s))/i' => ['icon' => '⏱️', 'group' => 2],
        '~(Tốc độ tối đa):[^\d]*([\d\.]+\s*km/h)~i' => ['icon' => '🚀', 'group' => 2],
        '/(Loại động cơ):[^\d]*((?:AMG\s*)?[\d\.]*L?\s*V\d+\s*Biturbo|1 motor điện)/i' => ['icon' => '🔥', 'group' => 2],
        
        // Pin & Sạc
        '/(Pin|Dung lượng pin):[^\d]*([~\d\.,]+\s*kWh)/i' => ['icon' => '🔋', 'group' => 2],
        '/(Quãng đường chạy[^:]*):[^\d]*([~\d\.]+\s*km)/i' => ['icon' => '🛣️', 'group' => 2],
        '/(Sạc nhanh):[^\d]*([\d%→\s]+mất khoảng \d+ phút)/i' => ['icon' => '🔌', 'group' => 2],

        // Kích thước
        '/(Kích thước tổng thể):[^\d]*([~\d\.\s×,x]+mm)/i' => ['icon' => '📏', 'group' => 2],
        '/(\d+\s*chỗ ngồi)/i' => ['icon' => '👥', 'group' => 1, 'full_match' => true], // Special case
        '/(Dung tích khoang hành lý):[^\d]*([~\d\.]+\s*L)/i' => ['icon' => '🧳', 'group' => 2],
        
        // Nội thất & Tiện nghi
        '/(Âm thanh)\s*(Burmester®|\d+\s*loa)/i' => ['icon' => '🔊', 'group' => 0, 'full_match' => true],
        '/(Màn hình kép|Màn hình cảm ứng|Màn hình giải trí)\s*([~\d\.]+\s*inch)/i' => ['icon' => '🖥️', 'group' => 0, 'full_match' => true]
    ];

    $content = $html;
    foreach ($spec_patterns as $pattern => $details) {
        $content = preg_replace_callback($pattern, function($matches) use ($details) {
            $icon = $details['icon'];
            
            // Nếu là full_match, làm nổi bật toàn bộ chuỗi tìm thấy
            if (isset($details['full_match']) && $details['full_match'] === true) {
                $value = $matches[$details['group']];
                return '<span class="spec-highlight">' . $icon . ' ' . trim($value) . '</span>';
            }

            // Ngược lại, chỉ làm nổi bật giá trị
            $label = $matches[1];
            $value = $matches[$details['group']];
            return $label . ': <span class="spec-highlight">' . $icon . ' ' . trim($value) . '</span>';
        }, $content);
    }

    return $content;
}




/* ========= BÊN TRÁI: Video & Thông số nhanh ========= */
function first_youtube_embed($html){
  if(preg_match('~https?://(?:www\.)?youtube\.com/watch\?[^"\s]+~', $html, $m)){
    $url = $m[0];
    $q = parse_url($url, PHP_URL_QUERY) ?? '';
    parse_str($q, $params);
    if(!empty($params['v'])){
      $id = preg_replace('~[^A-Za-z0-9_-]~','', $params['v']);
      return 'https://www.youtube.com/embed/'.$id;
    }
  }
  if(preg_match('~https?://youtu\.be/([A-Za-z0-9_-]{6,})~', $html, $m2)){
    return 'https://www.youtube.com/embed/'.$m2[1];
  }
  return '';
}
$ytEmbed = first_youtube_embed($raw);

/* Bắt thông số nhanh từ nội dung (heuristic) */
function extract_specs_quick($text){
  $t = mb_strtolower(strip_tags($text),'UTF-8');
  $spec=[];
  if(preg_match('/công suất[^0-9]*([\d\.]{2,})\s*k?w/i',$t,$m)) $spec['Công suất']= $m[1].' kW';
  if(preg_match('/mô[\s-]?men xoắn[^0-9]*([\d\.]{2,})\s*n?m/i',$t,$m)) $spec['Mô-men xoắn']= $m[1].' Nm';
  if(preg_match('/tốc độ tối đa[^0-9]*([\d\.]{2,})\s*km\/?h/i',$t,$m)) $spec['Tốc độ tối đa']= $m[1].' km/h';
  if(preg_match('/0\s*-\s*100[^0-9]*([\d\.\,]{1,4})\s*s/i',$t,$m)) $spec['0–100 km/h']= str_replace(',','.',$m[1]).' s';
  if(preg_match('/quãng đường.*?([\d\.]{2,})\s*km/i',$t,$m)) $spec['Quãng đường']= $m[1].' km';
  if(preg_match('/kích thước[^0-9]*([\d\.\sx×x*]{7,})/iu',$t,$m)) $spec['Kích thước (DxRxC)']= trim($m[1]);
  if(preg_match('/khoảng sáng gầm[^0-9]*([\d\.]{2,})\s*mm/i',$t,$m)) $spec['Gầm xe']= $m[1].' mm';
  if(preg_match('/la-?zăng[^0-9]*([\d\.]{1,2})\s*inch/i',$t,$m)) $spec['Mâm']= $m[1].' inch';
  return $spec;
}
$quickSpecs = extract_specs_quick($raw);

/* ========= Query Đánh giá (Reviews) ========= */
$stmtRev = $con->prepare(
    "SELECT r.*, u.fullname 
     FROM `reviews` r 
     JOIN `users` u ON r.user_id = u.id 
     WHERE r.product_id = ? AND r.status = 'approved' 
     ORDER BY r.created_at DESC"
);
$stmtRev->bind_param("i", $id);
$stmtRev->execute();
$reviews_rs = $stmtRev->get_result();
$reviews = [];
$totalReviews = 0;
$avgRating = 0;
$ratingDistribution = [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0];
$sumRating = 0;

while ($row = $reviews_rs->fetch_assoc()) {
    $reviews[] = $row;
    if (isset($ratingDistribution[(int)$row['rating']])) {
        $ratingDistribution[(int)$row['rating']]++;
    }
    $sumRating += (int)$row['rating'];
}
$totalReviews = count($reviews);
if ($totalReviews > 0) {
    $avgRating = round($sumRating / $totalReviews, 1);
}

// Sắp xếp lại distribution để 5 sao lên đầu
krsort($ratingDistribution);

// Kiểm tra xem user đã mua sản phẩm này chưa để cho phép review
$userHasPurchased = false;
if (isset($_SESSION['user']['id'])) {
    $userId = (int)$_SESSION['user']['id'];
    $stmtOrder = $con->prepare(
        "SELECT od.order_id FROM `orders_detail` od
         JOIN `orders` o ON od.order_id = o.id
         WHERE od.product_id = ? AND o.user_id = ? AND o.status = 2" // Giả sử status = 2 là đã giao hàng
    );
    $stmtOrder->bind_param("ii", $id, $userId);
    $stmtOrder->execute();
    $order_rs = $stmtOrder->get_result();
    if ($order_rs->num_rows > 0) {
        $userHasPurchased = true;
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <title><?= e($product['name']) ?></title>
  <link rel="icon" type="image/png" sizes="32x32" href="logo/logo.png">

  <!-- Vendor -->
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js"></script>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
  <link rel="stylesheet" type="text/css" href="slick/slick.css"/>
  <link rel="stylesheet" type="text/css" href="slick/slick-theme.css"/>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/fancyapps/fancybox@3.5.7/dist/jquery.fancybox.min.css"/>
  <script type="text/javascript" src="slick/slick.min.js"></script>
  <script src="https://cdn.jsdelivr.net/gh/fancyapps/fancybox@3.5.7/dist/jquery.fancybox.min.js"></script>

  <!-- Style -->
  <link rel="stylesheet" href="css/app.css">
  <link rel="stylesheet" href="css/product-item.css">
  <link rel="stylesheet" href="./css/rate.css">
  <link rel="stylesheet" href="./css/reponsive.css">

  <!-- ================== Overlay UI ================== -->
  <style>
    :root{
      --accent:#0d6efd; --brand:#0d6efd;
      --bg:#f5f7fb; --surface:#fff; --muted:#6b7280;
      --border:#e5e7eb; --radius:14px; --shadow:0 10px 24px rgba(0,0,0,.08);
    }
    body{background:var(--bg); color:#0b0b0c; -webkit-font-smoothing:antialiased}
    .pdp-wrap{background:var(--surface); border:1px solid var(--border); border-radius:16px; box-shadow:var(--shadow);}
    .pdp-title{font-size:1.8rem; font-weight:800; margin-bottom:.25rem}
    .pdp-hr{border-top:2px solid var(--border); margin:.5rem 0 1rem}

    /* Gallery */
    .pdp-media{border-radius:12px; overflow:hidden; background:#f3f4f6}
    .pdp-media .ratio{position:relative; width:100%; padding-top:75%}
    .pdp-media img{position:absolute; inset:0; width:100%; height:100%; object-fit:cover; transition:transform .25s ease}
    .pdp-media:hover img{transform:scale(1.02)}
    .pdp-thumbs{display:flex; gap:.5rem; margin-top:.75rem; overflow:auto; padding-bottom:.25rem; scroll-snap-type:x mandatory; scrollbar-width:none; -ms-overflow-style:none}
    .pdp-thumbs::-webkit-scrollbar{display:none}
    .pdp-thumb{scroll-snap-align:start; width:84px; height:84px; border-radius:10px; border:1px solid var(--border); overflow:hidden; cursor:pointer; flex:0 0 auto}
    .pdp-thumb img{width:100%; height:100%; object-fit:cover}
    .pdp-thumb.active{outline:2px solid var(--accent); outline-offset:2px}

    /* Right sticky */
    @media (min-width: 992px){ .pdp-sticky{position:sticky; top:96px;} }

    /* Price block */
    .price-block{display:grid; grid-template-columns:1fr; row-gap:.35rem; margin:.5rem 0 1rem; font-variant-numeric:tabular-nums}
    .price-old{color:#6b7280}
    .price-old del{opacity:.9}
    .price-new{font-size:1.8rem; font-weight:800; color:#ef4444}
    .price-save{color:#16a34a; font-weight:700}

    .perk-list{list-style:none; padding:0; margin:0}
    .perk-list li{display:flex; align-items:flex-start; gap:.5rem; margin:.35rem 0; color:#111}
    .perk-list i{color:#10b981; margin-top:.15rem}
    .qty-wrap{max-width:180px}
    .btn-buy{background:var(--accent); border-color:var(--accent); color:#fff; font-weight:700; letter-spacing:.3px; padding:.85rem 1rem; border-radius:12px}
    .btn-buy:hover{filter:brightness(1.03)}
    .btn-outline-dark{border-radius:12px}
    .btn-disabled{background:#c53030; border-color:#c53030; cursor:not-allowed}

    .info-box{background:#fff7d0; border:1px solid #fde68a; color:#7a5a00; padding:.9rem 1rem; border-radius:12px; font-weight:700}

    /* Desc layout - Enhanced */
    .desc-layout{margin-top:2.5rem}
    .desc-layout .card-like{
      background:#fff;
      border:1px solid var(--border);
      border-radius:14px;
      box-shadow:0 2px 12px rgba(0,0,0,.06);
      transition:box-shadow .25s ease;
    }
    .desc-layout .card-like:hover{box-shadow:0 8px 24px rgba(0,0,0,.1)}
    
    .desc-header{
      display:flex;
      align-items:center;
      justify-content:space-between;
      padding:1.2rem 1.5rem;
      border-bottom:2px solid #f3f4f6;
      background:linear-gradient(to bottom, #fafbfc 0%, #fff 100%);
      border-radius:14px 14px 0 0;
    }
    .desc-header h5{
      display:flex;
      align-items:center;
      gap:.6rem;
      font-size:1.3rem;
      color:#111827;
    }
    .desc-header h5::before{
      content:'📝';
      font-size:1.4rem;
    }
    
    .desc-content{
      padding:1.75rem 1.5rem 2rem;
      max-width:78ch;
      line-height:1.85;
      color:#1f2937;
      font-size:1.02rem;
    }
    .desc-content p{
      margin-bottom:1.1rem;
      text-align:justify;
    }
    .desc-content p:first-of-type{
      font-size:1.08rem;
      color:#374151;
      font-weight:500;
    }
    .desc-content ul, .desc-content ol{
      margin:1rem 0 1.25rem 1.5rem;
      padding-left:.5rem;
    }
    .desc-content li{
      margin-bottom:.65rem;
      padding-left:.4rem;
    }
    .desc-content ul li{
      list-style-type:none;
      position:relative;
    }
    .desc-content ul li::before{
      content:'▸';
      position:absolute;
      left:-1.2rem;
      color:#0d6efd;
      font-weight:bold;
    }
    .desc-content h2, .desc-content h3, .desc-content h4{
      margin:2rem 0 .85rem;
      font-weight:800;
      color:#0f172a;
      scroll-margin-top:100px;
      position:relative;
    }
    .desc-content h2{
      font-size:1.45rem;
      padding-bottom:.5rem;
      border-bottom:3px solid #e0f2fe;
    }
    .desc-content h2::before{
      content:'';
      position:absolute;
      bottom:-3px;
      left:0;
      width:60px;
      height:3px;
      background:linear-gradient(90deg, #0d6efd, #06b6d4);
    }
    .desc-content h3{
      font-size:1.2rem;
      color:#1e293b;
      padding-left:.8rem;
      border-left:4px solid #0ea5e9;
    }
    .desc-content h4{
      font-size:1.08rem;
      color:#334155;
    }
    .desc-content strong, .desc-content b{
      color:#0f172a;
      font-weight:700;
    }
    .desc-content a{
      color:#0d6efd;
      text-decoration:underline;
      text-decoration-color:rgba(13,110,253,.3);
      text-underline-offset:2px;
      transition:all .2s;
    }
    .desc-content a:hover{
      color:#0a58ca;
      text-decoration-color:rgba(13,110,253,.8);
    }
    .desc-content code{
      background:#f1f5f9;
      padding:.15rem .4rem;
      border-radius:4px;
      font-size:.92em;
      color:#dc2626;
      font-family:Consolas, Monaco, 'Courier New', monospace;
    }
    
    #descBox[data-collapsed="true"] .desc-content{
      max-height:480px;
      overflow:hidden;
      position:relative;
    }
    #descBox[data-collapsed="true"] .desc-content::after{
      content:'';
      position:absolute;
      bottom:0;
      left:0;
      right:0;
      height:120px;
      background:linear-gradient(to bottom, transparent 0%, rgba(255,255,255,.85) 40%, rgba(255,255,255,1) 100%);
      pointer-events:none;
    }
    #descBox[data-collapsed="false"] .desc-content{
      animation:expandDesc .35s ease-out;
    }
    @keyframes expandDesc{
      from{max-height:480px; opacity:.8}
      to{max-height:10000px; opacity:1}
    }
    
    #toggleDesc{
      border-radius:999px;
      padding:.5rem 1.2rem;
      font-weight:600;
      font-size:.95rem;
      transition:all .25s;
      position:relative;
      overflow:hidden;
    }
    #toggleDesc::after{
      content:'';
      position:absolute;
      top:50%;
      left:50%;
      width:0;
      height:0;
      border-radius:50%;
      background:rgba(255,255,255,.3);
      transform:translate(-50%, -50%);
      transition:width .5s, height .5s;
    }
    #toggleDesc:hover::after{
      width:200px;
      height:200px;
    }
    #toggleDesc:hover{
      transform:translateY(-1px);
      box-shadow:0 4px 12px rgba(0,0,0,.15);
    }

    /* ==== Accordion Description ==== */
    .desc-accordion .card {
      border: none;
      border-bottom: 1px solid var(--border);
      border-radius: 0;
    }
    .desc-accordion .card:first-of-type {
      border-top: 2px solid #f3f4f6;
    }
    .desc-accordion .card:last-of-type {
      border-bottom: none;
    }
    .desc-accordion .card-header {
      background: #fff;
      padding: 0;
      border: none;
    }
    .desc-accordion .btn-link {
      width: 100%;
      text-align: left;
      padding: 1rem 1.5rem;
      font-weight: 700;
      font-size: 1.05rem;
      color: #1f2937;
      text-decoration: none;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    .desc-accordion .btn-link:hover {
      background: #f8fafc;
      color: var(--accent);
      text-decoration: none;
    }
    .desc-accordion .btn-link::after {
      content: '\f282'; /* Bootstrap Icon plus */
      font-family: 'bootstrap-icons';
      font-weight: bold !important;
      transition: transform .2s;
    }
    .desc-accordion .btn-link[aria-expanded="true"] {
        color: var(--accent);
    }
    .desc-accordion .btn-link[aria-expanded="true"]::after {
      content: '\f463'; /* Bootstrap Icon dash */
    }
    .desc-accordion .card-body {
      padding: 0 1.5rem 1.5rem;
    }

    .spec-highlight {
      display: inline-block;
      background-color: #eef2ff;
      color: #4338ca;
      padding: 3px 10px;
      border-radius: 8px;
      font-weight: 600;
      white-space: nowrap;
      vertical-align: baseline;
      margin-left: 4px;
      border: 1px solid #c7d2fe;
    }


    .summary-card, .toc-card, .specs-card, .calc-card{
      border:1px solid var(--border);
      border-radius:12px;
      background:#fff;
      box-shadow:0 4px 12px rgba(0,0,0,.06);
      transition:transform .25s, box-shadow .25s;
    }
    .summary-card:hover, .toc-card:hover{
      transform:translateY(-2px);
      box-shadow:0 8px 20px rgba(0,0,0,.12);
    }
    
    /* Summary card enhanced */
    .summary-card .h6{
      display:flex;
      align-items:center;
      gap:.5rem;
      color:#0f172a;
      font-size:1.1rem;
    }
    .summary-card .h6::before{
      content:'⭐';
      font-size:1.2rem;
    }
    .summary-list{
      list-style:none;
      padding:0;
      margin:0;
    }
    .summary-list li{
      display:flex;
      align-items:flex-start;
      gap:.65rem;
      padding:.6rem 0;
      border-bottom:1px dashed #e5e7eb;
      transition:background .2s;
    }
    .summary-list li:last-child{border-bottom:none}
    .summary-list li:hover{
      background:#f8fafc;
      padding-left:.5rem;
      margin-left:-.5rem;
    }
    .summary-list i{
      color:#10b981;
      font-size:1.1rem;
      margin-top:.15rem;
      flex-shrink:0;
    }
    .summary-list span{
      color:#374151;
      line-height:1.6;
    }
    
    /* TOC enhanced */
    .toc-card .h6{
      display:flex;
      align-items:center;
      gap:.5rem;
      color:#0f172a;
      font-size:1.1rem;
    }
    .toc-card .h6::before{
      content:'📑';
      font-size:1.2rem;
    }
    #tocList{
      list-style:none;
      counter-reset:toc-counter;
      padding:0;
      margin:0;
    }
    #tocList li{
      counter-increment:toc-counter;
      margin-bottom:.5rem;
    }
    #tocList li::before{
      content:counter(toc-counter) ".";
      font-weight:700;
      color:#0d6efd;
      margin-right:.5rem;
    }
    #tocList a{
      color:#374151;
      text-decoration:none;
      display:inline-block;
      padding:.35rem .6rem;
      border-radius:6px;
      transition:all .2s;
      position:relative;
    }
    #tocList a::before{
      content:'';
      position:absolute;
      left:0;
      top:50%;
      transform:translateY(-50%);
      width:0;
      height:2px;
      background:#0d6efd;
      transition:width .3s;
    }
    #tocList a:hover{
      background:#f1f5f9;
      color:#0d6efd;
      padding-left:1rem;
    }
    #tocList a:hover::before{
      width:12px;
    }

    /* Quick specs grid */
    .spec-grid{display:grid; grid-template-columns:repeat(2, minmax(0,1fr)); gap:.5rem}
    .spec-item{display:flex; gap:.5rem; padding:.5rem; border:1px dashed var(--border); border-radius:10px; align-items:center}
    .spec-item i{color:#0ea5e9}

    /* Calculator */
    .calc-row{display:grid; grid-template-columns:repeat(2, minmax(0,1fr)); gap:.5rem}
    @media (max-width:576px){ .calc-row{grid-template-columns:1fr} }
    .calc-result{font-weight:800; font-size:1.2rem}
    .muted{color:#6b7280}

    /* Stock badge */
    .stock-badge{display:inline-flex;align-items:center;gap:.4rem;padding:.25rem .6rem;border:1px solid var(--border);border-radius:999px;font-size:.85rem}
    .stock-badge.in{color:#16a34a}.stock-badge.out{color:#dc2626}

    /* ==== Bottom enhancements ==== */
    .related-wrap{}
    .related-slider .related-card{display:block;border:1px solid var(--border);border-radius:12px;background:#fff;overflow:hidden;transition:transform .18s, box-shadow .18s}
    .related-slider .related-card:hover{transform:translateY(-2px);box-shadow:var(--shadow)}
    .related-media{aspect-ratio:4/3;background:#f3f4f6}
    .related-media img{width:100%;height:100%;object-fit:cover}
    .related-name{font-size:.95rem;line-height:1.3;height:2.6rem;overflow:hidden}
    .related-price{font-weight:800}
    .related-slider .related-card {
      display: flex;
      flex-direction: column;
      height: 100%; /* Ensure card fills height */
    }
    .related-slider .related-card .p-2 {
      flex-grow: 1; /* Allow content to grow and push button down */
      display: flex;
      flex-direction: column;
    }
    .related-slider .related-card .related-actions {
      margin-top: auto; /* Push actions to the bottom */
    }

    .faq-wrap .card{border-radius:12px;border:1px solid var(--border);overflow:hidden}
    .faq-wrap .card + .card{margin-top:.5rem}

    .to-top{position:fixed;right:16px;bottom:96px;width:44px;height:44px;border-radius:999px;border:1px solid var(--border);background:#fff;box-shadow:0 8px 24px rgba(0,0,0,.12);display:grid;place-items:center;opacity:0;transition:.2s;z-index:1039}
    .to-top i{font-size:18px}
    .to-top.show{opacity:1}

    @media (max-width: 991.98px){
      .sticky-mobile-cta{position:fixed;left:0;right:0;bottom:0;background:rgba(255,255,255,.96);border-top:1px solid var(--border);box-shadow:0 -8px 24px rgba(0,0,0,.08);padding:.6rem .8rem;display:flex;align-items:center;gap:.75rem;z-index:1040}
      .sticky-mobile-cta .smc-price{font-weight:800;font-size:1.1rem}
      body{padding-bottom:72px}
    }

    /* ==== Reviews Section ==== */
    .reviews-wrap{
      background:#fff;
      border:1px solid var(--border);
      border-radius:14px;
      box-shadow:0 2px 12px rgba(0,0,0,.06);
      margin-top:2.5rem;
    }
    .reviews-header{
      padding:1.2rem 1.5rem;
      border-bottom:2px solid #f3f4f6;
    }
    .reviews-header h5{
      display:flex; align-items:center; gap:.6rem; font-size:1.3rem; color:#111827;
    }
    .reviews-header h5::before{ content:'🌟'; font-size:1.4rem; }

    .reviews-summary{
      display:grid;
      grid-template-columns: auto 1fr;
      gap: 1rem 2rem;
      align-items: center;
      padding: 1.5rem;
      border-bottom: 1px solid var(--border);
    }
    .rating-big-num{
      font-size: 3.5rem;
      font-weight: 800;
      color: #111827;
      line-height: 1;
    }
    .rating-stars-display{ color: #facc15; } /* text-yellow-400 */
    .rating-bars{ display: flex; flex-direction: column; gap: .25rem; }
    .rating-bar-row{ display: flex; align-items: center; gap: .75rem; }
    .rating-bar-label{ font-size: .875rem; color: var(--muted); white-space: nowrap; }
    .rating-bar-bg{ flex-grow: 1; height: 8px; background: #f3f4f6; border-radius: 99px; overflow: hidden; }
    .rating-bar-fill{ height: 100%; background: #facc15; border-radius: 99px; }
    .rating-bar-percent{ font-size: .875rem; color: var(--muted); min-width: 3rem; text-align: right; }

    .review-list{ padding: 1rem 0; }
    .review-item{
      padding: 1.5rem;
      border-bottom: 1px solid var(--border);
    }
    .review-item:last-child{ border-bottom: none; }
    .review-author{ font-weight: 700; }
    .review-date{ font-size: .875rem; color: var(--muted); }
    .review-comment{
      margin-top: .75rem;
      line-height: 1.7;
      color: #374151;
    }

    #review-form-wrap{
      padding: 1.5rem;
      background: #f8fafc;
      border-top: 1px solid var(--border);
    }
    .star-rating-input { display: flex; flex-direction: row-reverse; justify-content: flex-end; gap: .25rem; }
    .star-rating-input input { display: none; }
    .star-rating-input label { font-size: 1.75rem; color: #d1d5db; cursor: pointer; transition: color .2s; }
    .star-rating-input input:checked ~ label,
    .star-rating-input label:hover,
    .star-rating-input label:hover ~ label { color: #facc15; }

    .avg-rating-text{
      display: flex;
      align-items: center;
      gap: .5rem;
      color: var(--muted);
      cursor: pointer;
      transition: color .2s;
    }
    .avg-rating-text:hover{ color: #111; }
  </style>

  <meta property="og:title" content="<?= e($product['name']) ?>">
  <meta property="og:image" content="<?= e($product['image']) ?>">
  <meta name="description" content="<?= e(mb_substr(strip_tags($product['content']),0,160)) ?>">
</head>
<body>

<div id="fb-root"></div>
<script async defer crossorigin="anonymous" src="https://connect.facebook.net/vi_VN/sdk.js#xfbml=1&version=v6.0"></script>

<?php include 'main/header/pre-header.php'; ?>
<?php include 'main/header/danhmuc.php'; ?>

<section class="container my-4">
  <div class="pdp-wrap p-3 p-md-4">
    <div class="row">
      <!-- LEFT: Gallery + Video + Specs + Calculator -->
      <div class="col-lg-5 mb-3 mb-lg-0">
        <div class="pdp-media">
          <a href="<?= e($product['image']) ?>" data-fancybox="thumb-img" aria-label="Xem ảnh lớn">
            <div class="ratio"><img id="mainImage" src="<?= e($product['image']) ?>" alt="<?= e($product['name']) ?>"></div>
          </a>
        </div>
        <?php if(!empty($gallery)): ?>
        <div class="pdp-thumbs">
          <div class="pdp-thumb active"><img src="<?= e($product['image']) ?>" alt="thumb-1"></div>
          <?php foreach($gallery as $gi): ?>
            <div class="pdp-thumb"><img src="<?= e($gi['path']) ?>" alt="thumb-<?= (int)$gi['id'] ?>"></div>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <?php if($ytEmbed): ?>
        <div class="embed-responsive embed-responsive-16by9 mt-3 rounded overflow-hidden shadow-sm">
          <iframe class="embed-responsive-item" src="<?= e($ytEmbed) ?>" allowfullscreen loading="lazy" title="Video giới thiệu"></iframe>
        </div>
        <?php endif; ?>

        <?php if(!empty($quickSpecs)): ?>
        <div class="specs-card p-3 mt-3">
          <div class="d-flex align-items-center justify-content-between mb-2">
            <h6 class="mb-0 font-weight-bold">Thông số nhanh</h6>
            <span class="muted small">Nguồn: mô tả sản phẩm</span>
          </div>
          <div class="spec-grid">
            <?php foreach($quickSpecs as $k=>$v): ?>
              <div class="spec-item"><i class="bi bi-speedometer2"></i><div><div class="small muted"><?= e($k) ?></div><div><?= e($v) ?></div></div></div>
            <?php endforeach; ?>
          </div>
        </div>
        <?php endif; ?>

        <div class="calc-card p-3 mt-3">
          <h6 class="mb-2 font-weight-bold">Tính trả góp (ước tính)</h6>
          <div class="calc-row">
            <div>
              <label class="small muted">Giá xe</label>
              <input id="loanPrice" class="form-control" value="<?= vnd($priceNew) ?>" data-num="<?= (float)$priceNew ?>" readonly>
            </div>
            <div>
              <label class="small muted">% trả trước</label>
              <input id="loanDown" type="number" class="form-control" value="20" min="0" max="90">
            </div>
            <div>
              <label class="small muted">Lãi suất (%/năm)</label>
              <input id="loanRate" type="number" class="form-control" value="9.5" min="0" step="0.1">
            </div>
            <div>
              <label class="small muted">Kỳ hạn (tháng)</label>
              <input id="loanTerm" type="number" class="form-control" value="60" min="6" step="6">
            </div>
          </div>
          <div class="d-flex align-items-center justify-content-between mt-2">
            <div class="muted small">Kết quả mang tính tham khảo</div>
            <div class="calc-result" id="loanResult">—</div>
          </div>
        </div>
      </div>

      <!-- RIGHT: Summary -->
      <div class="col-lg-7">
        <div class="pdp-sticky">
          <div class="d-flex align-items-center" style="gap:.5rem">
            <h1 class="pdp-title mb-0"><?= e($product['name']) ?></h1>
            <span class="stock-badge <?= $isOutOfStock? 'out':'in' ?>">
              <i class="bi bi-<?= $isOutOfStock?'x-circle':'check-circle' ?>"></i>
              <?= $isOutOfStock?'Hết hàng':'Còn hàng' ?>
            </span>
          </div>
          <?php if($totalReviews > 0): ?>
          <div class="d-flex align-items-center mt-2" style="gap:1rem;">
            <a href="#reviews" class="avg-rating-text">
              <span class="rating-stars-display">
                <?php for($i=1; $i<=5; $i++): ?>
                  <i class="bi <?= $i <= $avgRating ? 'bi-star-fill' : ($i - 0.5 <= $avgRating ? 'bi-star-half' : 'bi-star') ?>"></i>
                <?php endfor; ?>
              </span>
              <span>(<?= $totalReviews ?> đánh giá)</span>
            </a>
          </div>
          <?php endif; ?>
          <div class="pdp-hr"></div>

          <form method="GET" action="cart.php" class="mb-3">
            <div class="price-block">
              <div class="price-old">Giá cũ: <span class="ml-1"><del><?= vnd($priceOld) ?></del></span></div>
              <div class="price-new">Giá bán: <?= vnd($priceNew) ?></div>
              <?php if($youSave>0): ?><div class="price-save">Tiết kiệm: <?= vnd($youSave) ?></div><?php endif; ?>
            </div>

            <h6 class="font-weight-bold mb-2">Khuyến mãi & Ưu đãi</h6>
            <ul class="perk-list mb-3">
              <li><i class="bi bi-check-circle-fill"></i> <span><b>1. Giá không kèm quà:</b> <?= vnd($priceOld) ?></span></li>
              <li><i class="bi bi-check-circle-fill"></i> <span><b>2. Mua với giá:</b> <?= vnd($priceNew) ?></span></li>
              <li><i class="bi bi-check-circle-fill"></i> Hỗ trợ trả góp 0% cho khoản vay lên đến 500 triệu</li>
              <li><i class="bi bi-check-circle-fill"></i> Tặng gói bảo hiểm 1 năm cho khách hàng mua xe mới</li>
              <li><i class="bi bi-check-circle-fill"></i> Ưu đãi bảo trì miễn phí trong 3 năm đầu</li>
            </ul>

            <div class="d-flex align-items-center mb-3">
              <label class="mr-3 mb-0 font-weight-bold" style="font-size:1.05rem">Số lượng</label>
              <div class="input-group qty-wrap">
                <div class="input-group-prepend">
                  <button class="btn btn-outline-secondary btn-spin btn-dec" type="button" aria-label="Giảm số lượng">−</button>
                </div>
                <input type="number" name="quantity" value="1" class="form-control text-center" min="1" max="<?= (int)$product['quantity'] ?>">
                <div class="input-group-append">
                  <button class="btn btn-outline-secondary btn-spin btn-inc" type="button" aria-label="Tăng số lượng">+</button>
                </div>
                <input type="hidden" name="id" value="<?= (int)$product['id'] ?>">
              </div>
            </div>

                        <div class="d-flex align-items-stretch" style="gap: .75rem;">
              <div style="flex: 1;">
                <?php if ($isOutOfStock): ?>
                  <button class="btn btn-buy btn-disabled btn-block mb-2 h-100" type="button" disabled>Hết hàng</button>
                <?php else: ?>
                  <?php if (isset($_SESSION['user'])): ?>
                    <button class="btn btn-buy btn-block mb-2 h-100" type="submit">Chọn mua</button>
                  <?php else: ?>
                    <a class="btn btn-buy btn-block mb-2 h-100 d-flex align-items-center justify-content-center" href="login.php">Đăng nhập để mua</a>
                  <?php endif; ?>
                <?php endif; ?>
              </div>
              <div style="flex: 1;">
                <a href="request_handler.php?action=compare_add&product_id=<?= (int)$product['id'] ?>" class="btn btn-outline-dark btn-block mb-2 h-100 d-flex align-items-center justify-content-center">
                    <i class="bi bi-bar-chart-fill"></i> So sánh
                </a>
              </div>
            </div>

          </form>
          <div class="mt-3"> <!-- Added margin-top for spacing -->
            <div class="fb-like" data-href="https://www.facebook.com/CarShop" data-layout="button" data-action="like" data-size="small" data-share="true"></div>
          </div>

          <div class="row">
            <div class="col-md-7">
              <h6 class="font-weight-bold mb-2">Thông tin bổ sung</h6>
              <ul class="m-0 pl-3">
                <li>Miễn phí giao hàng</li>
                <li>Đổi trả trong 30 ngày</li>
                <li>Hỗ trợ khách hàng 24/7</li>
                <li>Giảm giá cho đơn hàng đầu tiên</li>
                <li>Hỗ trợ bảo trì sau bán hàng</li>
              </ul>
            </div>
            <div class="col-md-5 mt-3 mt-md-0">
              <div class="info-box text-center">Giảm ngay 200.000đ cho khách hàng đăng ký thẻ thành viên Car Shop</div>
            </div>
          </div>
        </div>
      </div>
    </div><!-- /.row -->

    <!-- ===== MÔ TẢ CHUYÊN NGHIỆP ===== -->
    <div class="desc-layout mt-4">
      <div class="row">
        <div class="col-lg-8">
          <div class="card-like p-0">
            <div class="desc-header">
              <h5 class="mb-0 font-weight-bold">Mô tả sản phẩm</h5>
            </div>
            <?php if (!empty($descriptionSections)): ?>
            <div class="accordion desc-accordion" id="descriptionAccordion">
                <?php foreach ($descriptionSections as $index => $section): ?>
                <div class="card">
                    <div class="card-header" id="heading-<?= e($section['id']) ?>">
                        <h6 class="mb-0">
                            <button class="btn btn-link <?= $index > 0 ? 'collapsed' : '' ?>" type="button" data-toggle="collapse" data-target="#collapse-<?= e($section['id']) ?>" aria-expanded="<?= $index > 0 ? 'false' : 'true' ?>" aria-controls="collapse-<?= e($section['id']) ?>">
                                <?= e($section['title']) ?>
                            </button>
                        </h6>
                    </div>
                    <div id="collapse-<?= e($section['id']) ?>" class="collapse <?= $index > 0 ? '' : 'show' ?>" aria-labelledby="heading-<?= e($section['id']) ?>" data-parent="#descriptionAccordion">
                        <div class="card-body desc-content">
                            <?= highlight_specs_inline($section['content']) // Áp dụng làm nổi bật thông số ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
                <div class="desc-content p-3"><?= $descHtml ?></div>
            <?php endif; ?>
          </div>
        </div>
        <aside class="col-lg-4 mt-3 mt-lg-0">
          <div class="summary-card p-3">
            <div class="h6 font-weight-bold mb-2">Điểm nổi bật</div>
            <ul class="summary-list mb-0">
              <?php foreach ($highlights as $hl): ?>
                <li><i class="bi bi-check2-circle"></i><span><?= e($hl) ?></span></li>
              <?php endforeach; ?>
            </ul>
          </div>
          <div id="tocCard" class="toc-card p-3 mt-3" style="display:none">
            <div class="h6 font-weight-bold mb-2">Mục lục</div>
            <ol id="tocList" class="mb-0"></ol>
          </div>
        </aside>
      </div>
    </div>

    <?php
    /* Related products theo giá ±15% (fallback: mới nhất) */
    $min = max(0, $priceNew * 0.85);
    $max = $priceNew * 1.15;
    $stmtRel = $con->prepare("SELECT id,name,image,price_new FROM product WHERE id <> ? AND price_new BETWEEN ? AND ? ORDER BY id DESC LIMIT 12");
    $stmtRel->bind_param("idd", $id, $min, $max);
    $stmtRel->execute();
    $related = $stmtRel->get_result()->fetch_all(MYSQLI_ASSOC);
    if (!$related || count($related) < 4) {
      $stmtRel2 = $con->prepare("SELECT id,name,image,price_new FROM product WHERE id <> ? ORDER BY id DESC LIMIT 12");
      $stmtRel2->bind_param("i", $id);
      $stmtRel2->execute();
      $related = $stmtRel2->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    ?>

    <!-- ===== Đánh giá của khách hàng ===== -->
    <div id="reviews" class="reviews-wrap">
      <div class="reviews-header">
        <h5 class="mb-0 font-weight-bold">Đánh giá từ khách hàng</h5>
      </div>

      <?php if($totalReviews > 0): ?>
      <div class="reviews-summary">
        <div class="text-center">
          <div class="rating-big-num"><?= e(number_format($avgRating, 1)) ?></div>
          <div class="rating-stars-display">
            <?php for($i=1; $i<=5; $i++): ?>
              <i class="bi <?= $i <= $avgRating ? 'bi-star-fill' : ($i - 0.5 <= $avgRating ? 'bi-star-half' : 'bi-star') ?>"></i>
            <?php endfor; ?>
          </div>
          <div class="text-muted small mt-1">(<?= $totalReviews ?> đánh giá)</div>
        </div>
        <div class="rating-bars">
          <?php foreach($ratingDistribution as $star => $count): ?>
          <div class="rating-bar-row">
            <div class="rating-bar-label"><?= $star ?>&nbsp;sao</div>
            <div class="rating-bar-bg">
              <div class="rating-bar-fill" style="width: <?= $totalReviews > 0 ? ($count / $totalReviews * 100) : 0 ?>%;"></div>
            </div>
            <div class="rating-bar-percent"><?= $totalReviews > 0 ? round($count / $totalReviews * 100) : 0 ?>%</div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endif; ?>

      <div id="review-form-container" class="p-3 text-center border-bottom">
          <button class="btn btn-outline-primary" onclick="$('#review-form-wrap').slideToggle();">Viết đánh giá của bạn</button>
      </div>

      <div id="review-form-wrap" style="display: none;">
        <?php if($userHasPurchased): ?>
          <div class="p-3">
            <h6 class="font-weight-bold mb-3">Viết đánh giá của bạn</h6>
            <form action="xulydanhgia.php" method="POST">
              <input type="hidden" name="product_id" value="<?= (int)$product['id'] ?>">
              <div class="form-group">
                <label class="font-weight-bold">Xếp hạng của bạn</label>
                <div class="star-rating-input">
                  <input type="radio" id="star5" name="rating" value="5" required/><label for="star5" title="5 stars">★</label>
                  <input type="radio" id="star4" name="rating" value="4" /><label for="star4" title="4 stars">★</label>
                  <input type="radio" id="star3" name="rating" value="3" /><label for="star3" title="3 stars">★</label>
                  <input type="radio" id="star2" name="rating" value="2" /><label for="star2" title="2 stars">★</label>
                  <input type="radio" id="star1" name="rating" value="1" /><label for="star1" title="1 star">★</label>
                </div>
              </div>
              <div class="form-group">
                <label for="reviewComment" class="font-weight-bold">Bình luận của bạn</label>
                <textarea name="comment" id="reviewComment" rows="4" class="form-control" required placeholder="Sản phẩm này như thế nào? Chia sẻ cảm nhận của bạn nhé..."></textarea>
              </div>
              <button type="submit" class="btn btn-buy">Gửi đánh giá</button>
              <button type="button" class="btn btn-outline-secondary" onclick="$('#review-form-wrap').slideUp();">Hủy</button>
            </form>
          </div>
        <?php elseif(isset($_SESSION['user'])): ?>
          <div class="alert alert-warning m-3">Bạn cần mua sản phẩm này để có thể đánh giá.</div>
        <?php else: ?>
          <div class="alert alert-info m-3">Vui lòng <a href="login.php">đăng nhập</a> và mua sản phẩm để để lại đánh giá.</div>
        <?php endif; ?>
      </div>

      <div class="review-list">
        <?php if(empty($reviews)): ?>
          <div class="p-4 text-center text-muted">Chưa có đánh giá nào cho sản phẩm này. Hãy là người đầu tiên đánh giá!</div>
        <?php else: ?>
          <?php foreach($reviews as $review): ?>
          <div class="review-item">
            <div class="d-flex justify-content-between align-items-center">
              <div>
                <div class="review-author"><?= e($review['fullname']) ?></div>
                <div class="review-date"><?= date('d/m/Y', strtotime($review['created_at'])) ?></div>
              </div>
              <div class="rating-stars-display">
                <?php for($i=1; $i<=5; $i++): ?>
                  <i class="bi <?= $i <= $review['rating'] ? 'bi-star-fill' : 'bi-star' ?>"></i>
                <?php endfor; ?>
              </div>
            </div>
            <div class="review-comment mt-2"><?= nl2br(e($review['comment'])) ?></div>
          </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>

    <!-- ===== Xe tương tự ===== -->
    <div class="related-wrap mt-4">
      <div class="d-flex align-items-center justify-content-between mb-2">
        <h5 class="mb-0 font-weight-bold">Xe tương tự</h5>
        <a href="products.php" class="small">Xem tất cả</a>
      </div>
      <div class="related-slider">
        <?php foreach($related as $r): ?>
          <a class="related-card mr-2" href="chitietxe.php?id=<?= (int)$r['id'] ?>">
            <div class="related-media"><img loading="lazy" src="<?= e($r['image']) ?>" alt="<?= e($r['name']) ?>"></div>
            <div class="p-2">
              <div class="related-name"><?= e($r['name']) ?></div>
              <div class="related-price"><?= vnd($r['price_new'] ?: 0) ?></div>
              <div class="related-actions mt-2"> <!-- Added a div for actions -->
                <span class="btn btn-sm btn-primary btn-block">Xem chi tiết</span> <!-- Added a button -->
              </div>
            </div>
          </a>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- ===== FAQ ===== -->
    <div class="faq-wrap mt-4">
      <h5 class="font-weight-bold mb-2">Câu hỏi thường gặp</h5>
      <div id="faqAcc" class="accordion">
        <div class="card">
          <div class="card-header p-0" id="q1">
            <h6 class="mb-0"><button class="btn btn-link btn-block text-left p-3" data-toggle="collapse" data-target="#a1" aria-expanded="true" aria-controls="a1">Chính sách giao xe và thời gian dự kiến?</button></h6>
          </div>
          <div id="a1" class="collapse show" aria-labelledby="q1" data-parent="#faqAcc">
            <div class="card-body">Giao xe toàn quốc. Khu vực nội thành: 2–5 ngày làm việc, tỉnh: 5–10 ngày. Bạn sẽ được gọi xác nhận trước khi giao.</div>
          </div>
        </div>
        <div class="card">
          <div class="card-header p-0" id="q2">
            <h6 class="mb-0"><button class="btn btn-link btn-block text-left p-3" data-toggle="collapse" data-target="#a2">Bảo hành – bảo dưỡng như thế nào?</button></h6>
          </div>
          <div id="a2" class="collapse" aria-labelledby="q2" data-parent="#faqAcc">
            <div class="card-body">Bảo hành theo tiêu chuẩn hãng. Bảo dưỡng định kỳ tại hệ thống đối tác uỷ quyền, hỗ trợ đặt lịch online.</div>
          </div>
        </div>
        <div class="card">
          <div class="card-header p-0" id="q3">
            <h6 class="mb-0"><button class="btn btn-link btn-block text-left p-3" data-toggle="collapse" data-target="#a3">Hỗ trợ trả góp gồm những gì?</button></h6>
          </div>
          <div id="a3" class="collapse" aria-labelledby="q3" data-parent="#faqAcc">
            <div class="card-body">Hỗ trợ hồ sơ, tư vấn gói lãi suất, trả trước linh hoạt 10–50%, kỳ hạn 12–84 tháng. Có thể làm online 100%.</div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<?php include 'main/footer/dichvu.php'; ?>
<?php include 'chatbot.php'; ?>
<?php include 'main/footer/footer.php'; ?>

<!-- Sticky CTA (mobile only) -->
<div class="sticky-mobile-cta d-lg-none">
  <div class="smc-price"><?= vnd($priceNew) ?></div>
  <?php if($isOutOfStock): ?>
    <button class="btn btn-buy btn-disabled flex-grow-1" disabled>Hết hàng</button>
  <?php elseif(isset($_SESSION['user'])): ?>
    <button id="smcBuy" class="btn btn-buy flex-grow-1">Chọn mua</button>
  <?php else: ?>
    <a class="btn btn-buy flex-grow-1" href="login.php">Đăng nhập để mua</a>
  <?php endif; ?>
</div>

<!-- To top -->
<button id="toTop" class="to-top" aria-label="Lên đầu trang"><i class="bi bi-arrow-up"></i></button>

<!-- JS -->
<script>
$(function(){
  // Đổi ảnh chính
  $('.pdp-thumb').on('click', function(){
    $('.pdp-thumb').removeClass('active');
    $(this).addClass('active');
    var src = $(this).find('img').attr('src');
    $('#mainImage').attr('src', src).parent('a').attr('href', src);
  });

  // Stepper
  $('.btn-inc').click(function(){
    var $i = $(this).closest('.input-group').find('input[name="quantity"]');
    var v = parseInt($i.val(),10)||1, max=parseInt($i.attr('max'),10)||9999;
    if(v<max) $i.val(v+1).trigger('change');
  });
  $('.btn-dec').click(function(){
    var $i = $(this).closest('.input-group').find('input[name="quantity"]');
    var v = parseInt($i.val(),10)||1; if(v>1) $i.val(v-1).trigger('change');
  });



  // Mục lục
  (function(){
    const wrap = document.getElementById('descContent');
    const tocCard = document.getElementById('tocCard');
    const list = document.getElementById('tocList');
    if(!wrap||!list) return;
    const hs = wrap.querySelectorAll('h2, h3');
    if(!hs.length){ tocCard.style.display='none'; return; }
    tocCard.style.display='';
    hs.forEach((h,idx)=>{ if(!h.id) h.id='sec-'+(idx+1); const li=document.createElement('li'); const a=document.createElement('a'); a.href='#'+h.id; a.textContent=h.textContent.trim(); li.appendChild(a); list.appendChild(li); });
  })();

  // Tính trả góp
  (function(){
    const fmt = n => n.toLocaleString('vi-VN');
    const price = parseFloat(document.getElementById('loanPrice').dataset.num||'0');
    const down  = document.getElementById('loanDown');
    const rate  = document.getElementById('loanRate');
    const term  = document.getElementById('loanTerm');
    const out   = document.getElementById('loanResult');

    function calc(){
      const p0 = price;
      const dp = Math.min(Math.max(parseFloat(down.value||0),0),90)/100;
      const rY = Math.max(parseFloat(rate.value||0),0)/100;
      const n  = Math.max(parseInt(term.value||0,10),1);
      const P  = Math.max(p0*(1-dp),0);
      const r  = rY/12;
      let m = 0;
      if(r===0) m = P/n;
      else m = P*r / (1 - Math.pow(1+r, -n));
      out.textContent = m>0 ? (fmt(Math.round(m))+' ₫/tháng') : '—';
    }
    ['input','change'].forEach(ev=>{
      down.addEventListener(ev,calc); rate.addEventListener(ev,calc); term.addEventListener(ev,calc);
    });
    calc();
  })();

  // Slider "Xe tương tự" (slick)
  if ($.fn.slick) {
    $('.related-slider').slick({
      slidesToShow: 4, slidesToScroll: 1, arrows: true, dots: false,
      responsive: [
        {breakpoint: 1200, settings: {slidesToShow: 3}},
        {breakpoint: 768,  settings: {slidesToShow: 2}},
        {breakpoint: 480,  settings: {slidesToShow: 1}}
      ]
    });
    // Force a refresh after a short delay to ensure correct positioning
    setTimeout(function(){
        $('.related-slider').slick('setPosition');
    }, 200); // 200ms delay
  }

  // Sticky CTA (mobile) -> submit form mua
  $('#smcBuy').on('click', function(){
    const form = $('form[action="cart.php"]').first();
    if(form.length) form.trigger('submit');
  });

  // To top
  (function(){
    const b = document.getElementById('toTop');
    if(!b) return;
    window.addEventListener('scroll', ()=>{ b.classList.toggle('show', window.scrollY>300); });
    b.addEventListener('click', ()=> window.scrollTo({top:0, behavior:'smooth'}));
  })();
});
</script>

<!-- JSON-LD Product schema -->
<?php
$images = array_merge([$product['image']], array_map(function($g){ return $g['path']; }, $gallery));
$inStock = $isOutOfStock ? "https://schema.org/OutOfStock" : "https://schema.org/InStock";
$schema = [
  "@context" => "https://schema.org/",
  "@type" => "Product",
  "name" => $product['name'],
  "image" => $images,
  "description" => mb_substr(strip_tags($product['content']), 0, 250),
  "sku" => isset($product['sku']) ? $product['sku'] : (string)$product['id'],
  "brand" => ["@type"=>"Brand","name"=>"Car Shop"],
  "offers" => [
    "@type"=>"Offer",
    "priceCurrency"=>"VND",
    "price"=> (string)$priceNew,
    "availability"=>$inStock,
    "url"=> (isset($_SERVER['REQUEST_URI'])? $_SERVER['REQUEST_URI'] : '')
  ]
];
?>
<script type="application/ld+json"><?= json_encode($schema, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES) ?></script>

<?php include 'compare-widget.php'; ?>

</body>
</html>

