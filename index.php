<?php
session_start();
require_once 'config.php';

// جلب المنتجات (نفس الكود السابق)
$products = [];
$result = $conn->query("
    SELECT p.*,
           (SELECT image_path FROM product_colors WHERE product_id = p.id ORDER BY id ASC LIMIT 1) AS default_image
    FROM products p
    ORDER BY p.id DESC
");
while ($row = $result->fetch_assoc()) {
    $products[] = $row;
}

$productData = [];
foreach ($products as $prod) {
    $pid = $prod['id'];
    $colors = [];
    $cres = $conn->query("SELECT * FROM product_colors WHERE product_id = $pid ORDER BY id ASC");
    while ($c = $cres->fetch_assoc()) {
        $colors[] = $c;
    }
    $designs = [];
    $dres = $conn->query("SELECT * FROM designs WHERE product_id = $pid ORDER BY id DESC");
    while ($d = $dres->fetch_assoc()) {
        $designs[] = $d;
    }
    $productData[$pid] = [
        'id' => $pid,
        'name' => $prod['name'],
        'colors' => $colors,
        'designs' => $designs
    ];
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>صمم منتجك – تصميم تيشرتات احترافي</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/fabric.js/5.3.1/fabric.min.js"></script>
<link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><rect x='10' y='20' width='80' height='70' rx='10' fill='%234f46e5'/><circle cx='50' cy='30' r='10' fill='white'/></svg>">

</head>
<body>

<!-- ========== الهيدر الجديد ========== -->
<header class="main-header">
    <div class="header-inner">
        <div class="logo-area">
            <div class="logo-icon">
                <!-- أيقونة SVG أنيقة (قميص) -->
                <svg viewBox="0 0 60 60" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <rect width="60" height="60" rx="16" fill="#4f46e5" />
                    <path d="M18 16C18 14.8954 18.8954 14 20 14H40C41.1046 14 42 14.8954 42 16V44C42 45.1046 41.1046 46 40 46H20C18.8954 46 18 45.1046 18 44V16Z" fill="white" />
                    <path d="M24 20H36V24H24V20Z" fill="#4f46e5" />
                    <path d="M24 28H36V32H24V28Z" fill="#4f46e5" />
                    <path d="M24 36H36V40H24V36Z" fill="#4f46e5" />
                    <circle cx="30" cy="40" r="2" fill="#4f46e5" />
                </svg>
            </div>
            <div class="logo-text">
                <h1>صمم منتجك</h1>
                <span>Design Your Product</span>
            </div>
        </div>
        <a href="admin.php" class="admin-gear" title="لوحة الإدارة">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="3" />
                <path d="M12 1v2M12 21v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M1 12h2M21 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42" />
            </svg>
        </a>
    </div>
</header>

<!-- ========== البانر الإرشادي ========== -->
<div class="hero-banner">
    <div class="hero-content">
        <p class="hero-title">🎨 اختر منتجك، اسحب اللون المفضل، أضف تصميمك الخاص، وانطلق!</p>
        <p class="hero-sub">اسحب &amp; افلت &bull; غيّر الألوان &bull; أضف صورك &bull; صمم بكل حرية</p>
    </div>
</div>

<!-- ========== المحتوى الرئيسي ========== -->
<main class="main-content">
    <div class="container">

        <!-- منطقة التصميم -->
        <div class="design-area" id="designArea">
            <canvas id="tshirt-canvas" width="500" height="600"></canvas>
            <div class="drag-overlay" id="dragOverlay">
                <span>📁</span>
                <span>اسحب الصورة هنا</span>
            </div>
        </div>

        <!-- أدوات التحكم (كما هي تماماً) -->
        <div class="controls">
            <!-- المنتج -->
            <div class="control-group">
                <label>📦 المنتج</label>
                <div class="product-grid" id="productGrid">
                    <?php foreach ($products as $index => $prod): ?>
                        <div class="product-card <?= $index === 0 ? 'active' : '' ?>"
                             data-product-id="<?= $prod['id'] ?>">
                            <img src="<?= $prod['default_image'] ?: 'assets/default.png' ?>"
                                 alt="<?= htmlspecialchars($prod['name']) ?>">
                            <span><?= htmlspecialchars($prod['name']) ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- الألوان -->
            <div class="control-group" id="colorGroup">
                <label>🎨 الألوان</label>
                <div class="color-grid" id="colorOptions"></div>
            </div>

            <!-- المقاس -->
            <div class="control-group">
                <label>📏 المقاس</label>
                <div class="size-options">
                    <button class="size-btn" data-size="S">S</button>
                    <button class="size-btn" data-size="M">M</button>
                    <button class="size-btn active" data-size="L">L</button>
                    <button class="size-btn" data-size="XL">XL</button>
                    <button class="size-btn" data-size="XXL">XXL</button>
                </div>
            </div>

            <!-- رفع تصميم -->
            <div class="control-group">
                <label for="uploadImage" class="btn-upload">📁 رفع تصميم</label>
                <input type="file" id="uploadImage" accept="image/*" style="display:none;">
                <button id="removeImage" class="btn-remove">🗑 إزالة</button>
                <button id="clearAll" class="btn-clear">🧹 مسح الكل</button>
            </div>
        </div>

        <!-- التصاميم المقترحة -->
        <div class="designs-section">
            <h3 class="section-title">🖼 تصاميم مقترحة</h3>
            <div class="designs-grid" id="designsGrid"></div>
        </div>

        <!-- أزرار الإجراءات (لم نغير أي شيء) -->
        <div class="actions">
            <button id="orderBtn" class="btn-order">📲 طلب عبر واتساب</button>
            <button id="exportBtn" class="btn-export">📥 تصدير PNG</button>
            <a href="https://www.remove.bg/upload" target="_blank" class="btn-remove-bg">🖼️ إزالة الخلفية</a>
        </div>

    </div>
</main>

<!-- ========== الفوتر الجديد ========== -->
<footer class="main-footer">
    <div class="footer-inner">
        <div class="footer-col">
            <div class="footer-logo">
                <span>👕</span>
                <span>صمم منتجك</span>
            </div>
            <p class="footer-desc">منصتك المفضلة لتصميم المنتجات الرقمية بكل إبداع وسهولة.</p>
        </div>
        <div class="footer-col">
            <h4>روابط سريعة</h4>
            <ul>
                <li><a href="index.php">الصفحة الرئيسية</a></li>
                <li><a href="admin.php">لوحة الإدارة</a></li>
                <li><a href="https://www.remove.bg/upload" target="_blank">إزالة الخلفية</a></li>
            </ul>
        </div>
        <div class="footer-col">
            <h4>تواصل معنا</h4>
            <ul>
                <li><a href="https://wa.me/<?= $whatsapp_number ?>" target="_blank">واتساب</a></li>
                <li><a href="mailto:info@yourstore.com">البريد الإلكتروني</a></li>
            </ul>
        </div>
    </div>
    <div class="footer-bottom">
        <p>&copy; 2025 <strong>صمم منتجك</strong> – جميع الحقوق محفوظة. صُنع بحب باستخدام PHP, JavaScript &amp; Fabric.js</p>
    </div>
</footer>

<script>
    const productData = <?= json_encode($productData) ?>;
    const whatsappNumber = '<?= $whatsapp_number ?>';
</script>
<script src="assets/js/script.js"></script>
</body>
</html>
