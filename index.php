<?php
session_start();
require_once 'config.php';

// جلب جميع المنتجات مع أول لون (أبيض) لكل منتج
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

// جلب بيانات الألوان والتصاميم لكل منتج (لتمريرها إلى JavaScript)
$productData = [];
foreach ($products as $prod) {
    $pid = $prod['id'];
    // الألوان
    $colors = [];
    $cres = $conn->query("SELECT * FROM product_colors WHERE product_id = $pid ORDER BY id ASC");
    while ($c = $cres->fetch_assoc()) {
        $colors[] = $c;
    }
    // التصاميم
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
    <title>تيشرت ستور – صمم تيشرتك</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/fabric.js/5.3.1/fabric.min.js"></script>
</head>
<body>

<div class="container">
    <!-- رأس الصفحة -->
    <header class="header">
        <div class="logo">
            <span class="logo-icon">👕</span>
            <h1>تيشرت ستور</h1>
        </div>
        <a href="admin.php" class="btn-admin">🔐 لوحة الإدارة</a>
    </header>

    <!-- منطقة التصميم -->
    <div class="design-area" id="designArea">
        <canvas id="tshirt-canvas" width="500" height="600"></canvas>
        <div class="drag-overlay" id="dragOverlay">📁 اسحب الصورة هنا</div>
    </div>

    <!-- أدوات التحكم -->
    <div class="controls">
        <!-- اختيار المنتج -->
        <div class="control-group">
            <label>📦 المنتج:</label>
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

        <!-- اختيار اللون (يظهر بعد اختيار المنتج) -->
    <!-- في قسم الألوان، استبدل div.color-options بـ div.color-grid -->
<div class="control-group" id="colorGroup">
    <label>🎨 الألوان:</label>
    <div class="color-grid" id="colorOptions">
        <!-- سيتم تعبئتها بواسطة JavaScript كبطاقات -->
    </div>
</div>

        <!-- المقاس -->
        <div class="control-group">
            <label>📏 المقاس:</label>
            <div class="size-options">
                <button class="size-btn" data-size="S">S</button>
                <button class="size-btn" data-size="M">M</button>
                <button class="size-btn active" data-size="L">L</button>
                <button class="size-btn" data-size="XL">XL</button>
                <button class="size-btn" data-size="XXL">XXL</button>
            </div>
        </div>




        <!-- رفع الصورة -->
        <div class="control-group">
           <label for="uploadImage" class="btn-upload">📁 رفع تصميم</label>
<input type="file" id="uploadImage" accept="image/*" style="display:none;">
            <button id="removeImage" class="btn-remove">🗑 إزالة</button>
            <button id="clearAll" class="btn-clear">🧹 مسح الكل</button>
        </div>
    </div>

    <!-- التصاميم الجاهزة -->
    <div class="designs-section">
        <h3>🖼 تصاميم مقترحة</h3>
        <div class="designs-grid" id="designsGrid">
            <!-- سيتم تعبئتها بواسطة JavaScript -->
        </div>
    </div>

    <!-- أزرار الإجراءات -->
    <!-- <div class="actions">
        <button id="orderBtn" class="btn-order">📲 طلب عبر واتساب</button>
        <button id="exportBtn" class="btn-export">📥 تصدير PNG</button>
    </div> -->

    <!-- أضف أزراراً جديدة بعد أزرار التصدير -->
<div class="actions">
    <button id="orderBtn" class="btn-order">📲 طلب عبر واتساب</button>
    <button id="exportBtn" class="btn-export">📥 تصدير PNG</button>
    <button id="removeBgBtn" class="btn-remove-bg">🖼️ إزالة الخلفية</button>
    <!-- <button id="shareBtn" class="btn-share">📤 إرسال التصميم</button> -->
</div>
</div>



<script>
    // تمرير البيانات من PHP إلى JavaScript
    const productData = <?= json_encode($productData) ?>;
    const whatsappNumber = '<?= $whatsapp_number ?>';
</script>
<script src="assets/js/script.js"></script>
</body>
</html>
