<?php
session_start();
require_once 'config.php';

// التحقق من تسجيل الدخول
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['password'])) {
    if ($_POST['password'] === $admin_password) {
        $_SESSION['admin_logged_in'] = true;
    } else {
        $error = 'كلمة مرور خاطئة';
    }
}
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: admin.php');
    exit;
}

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    // ... (نموذج تسجيل الدخول كما هو، غير مهم الآن) ...
    // للحفاظ على الاختصار، سأضع نسخة مختصرة، لكن يمكنك استخدام الكود السابق
    ?>
    <!DOCTYPE html>
    <html>
    <head><meta charset="UTF-8"><title>دخول الأدمن</title><link rel="stylesheet" href="assets/css/style.css"></head>
    <body>
    <div class="container" style="max-width:420px; margin-top:60px;">
        <h2 style="text-align:center;">🔐 دخول الأدمن</h2>
        <?php if (isset($error)) echo "<p style='color:red;text-align:center;'>$error</p>"; ?>
        <form method="POST">
            <input type="password" name="password" placeholder="كلمة المرور" required style="width:100%;padding:14px;margin:12px 0;border-radius:12px;border:1px solid #d1d5db;font-size:16px;">
            <button type="submit" class="btn-order" style="width:100%;">دخول</button>
        </form>
    </div>
    </body>
    </html>
    <?php
    exit;
}

// ============================================================
// معالجة الإضافة والتعديل والحذف
// ============================================================

// --- إضافة منتج ---
if (isset($_POST['add_product'])) {
    $name = trim($_POST['product_name']);
    if ($name && isset($_FILES['product_image']) && $_FILES['product_image']['error'] === UPLOAD_ERR_OK) {
        $conn->query("INSERT INTO products (name) VALUES ('$name')");
        $pid = $conn->insert_id;
        $ext = pathinfo($_FILES['product_image']['name'], PATHINFO_EXTENSION);
        $target = PRODUCT_DIR . "product_{$pid}_white." . $ext;
        move_uploaded_file($_FILES['product_image']['tmp_name'], $target);
        $conn->query("INSERT INTO product_colors (product_id, color_name, color_label, image_path) VALUES ($pid, 'white', '#FFFFFF', '$target')");
        header('Location: admin.php');
        exit;
    }
}

// --- تعديل منتج ---
if (isset($_POST['edit_product'])) {
    $id = intval($_POST['product_id']);
    $name = trim($_POST['product_name']);
    if ($id && $name) {
        $conn->query("UPDATE products SET name = '$name' WHERE id = $id");
        // إذا تم رفع صورة جديدة، نقوم بتحديث الصورة البيضاء
        if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] === UPLOAD_ERR_OK) {
            $ext = pathinfo($_FILES['product_image']['name'], PATHINFO_EXTENSION);
            $target = PRODUCT_DIR . "product_{$id}_white." . $ext;
            move_uploaded_file($_FILES['product_image']['tmp_name'], $target);
            // تحديث مسار الصورة في جدول الألوان (اللون الأبيض)
            $conn->query("UPDATE product_colors SET image_path = '$target' WHERE product_id = $id AND color_name = 'white'");
        }
        header('Location: admin.php');
        exit;
    }
}

// --- حذف منتج ---
if (isset($_GET['delete_product'])) {
    $id = intval($_GET['delete_product']);
    $conn->query("DELETE FROM products WHERE id = $id");
    header('Location: admin.php');
    exit;
}

// --- إضافة لون ---
if (isset($_POST['add_color'])) {
    $pid = intval($_POST['product_id']);
    $color = trim($_POST['color_name']);
    $label = trim($_POST['color_label']);
    if ($pid && $color && $label && isset($_FILES['color_image']) && $_FILES['color_image']['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['color_image']['name'], PATHINFO_EXTENSION);
        $target = COLOR_DIR . "color_{$pid}_{$color}." . $ext;
        move_uploaded_file($_FILES['color_image']['tmp_name'], $target);
        $conn->query("INSERT INTO product_colors (product_id, color_name, color_label, image_path) VALUES ($pid, '$color', '$label', '$target')");
        header('Location: admin.php');
        exit;
    }
}

// --- تعديل لون ---
if (isset($_POST['edit_color'])) {
    $id = intval($_POST['color_id']);
    $color = trim($_POST['color_name']);
    $label = trim($_POST['color_label']);
    if ($id && $color && $label) {
        $update = "UPDATE product_colors SET color_name = '$color', color_label = '$label'";
        // إذا تم رفع صورة جديدة
        if (isset($_FILES['color_image']) && $_FILES['color_image']['error'] === UPLOAD_ERR_OK) {
            $ext = pathinfo($_FILES['color_image']['name'], PATHINFO_EXTENSION);
            $target = COLOR_DIR . "color_{$id}_" . uniqid() . "." . $ext;
            move_uploaded_file($_FILES['color_image']['tmp_name'], $target);
            $update .= ", image_path = '$target'";
        }
        $update .= " WHERE id = $id";
        $conn->query($update);
        header('Location: admin.php');
        exit;
    }
}

// --- حذف لون ---
if (isset($_GET['delete_color'])) {
    $id = intval($_GET['delete_color']);
    $conn->query("DELETE FROM product_colors WHERE id = $id");
    header('Location: admin.php');
    exit;
}

// --- إضافة تصميم ---
if (isset($_POST['add_design'])) {
    $pid = intval($_POST['product_id']);
    $name = trim($_POST['design_name']);
    if ($pid && $name && isset($_FILES['design_image']) && $_FILES['design_image']['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['design_image']['name'], PATHINFO_EXTENSION);
        $target = DESIGN_DIR . "design_{$pid}_" . uniqid() . "." . $ext;
        move_uploaded_file($_FILES['design_image']['tmp_name'], $target);
        $conn->query("INSERT INTO designs (product_id, name, image_path) VALUES ($pid, '$name', '$target')");
        header('Location: admin.php');
        exit;
    }
}

// --- تعديل تصميم ---
if (isset($_POST['edit_design'])) {
    $id = intval($_POST['design_id']);
    $name = trim($_POST['design_name']);
    if ($id && $name) {
        $update = "UPDATE designs SET name = '$name'";
        if (isset($_FILES['design_image']) && $_FILES['design_image']['error'] === UPLOAD_ERR_OK) {
            $ext = pathinfo($_FILES['design_image']['name'], PATHINFO_EXTENSION);
            $target = DESIGN_DIR . "design_{$id}_" . uniqid() . "." . $ext;
            move_uploaded_file($_FILES['design_image']['tmp_name'], $target);
            $update .= ", image_path = '$target'";
        }
        $update .= " WHERE id = $id";
        $conn->query($update);
        header('Location: admin.php');
        exit;
    }
}

// --- حذف تصميم ---
if (isset($_GET['delete_design'])) {
    $id = intval($_GET['delete_design']);
    $conn->query("DELETE FROM designs WHERE id = $id");
    header('Location: admin.php');
    exit;
}

// ============================================================
// جلب البيانات
// ============================================================
$products = $conn->query("SELECT * FROM products ORDER BY id DESC");
$allColors = $conn->query("SELECT pc.*, p.name as product_name FROM product_colors pc JOIN products p ON pc.product_id = p.id ORDER BY p.id, pc.id");
$allDesigns = $conn->query("SELECT d.*, p.name as product_name FROM designs d JOIN products p ON d.product_id = p.id ORDER BY p.id, d.id");

// ============================================================
// عرض الصفحة
// ============================================================
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>لوحة الإدارة – صمم منتجك</title>
    <link rel="stylesheet" href="assets/css/style.css">

<link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><rect x='10' y='20' width='80' height='70' rx='10' fill='%234f46e5'/><circle cx='50' cy='30' r='10' fill='white'/></svg>">

   <style>
        /* جميع التنسيقات الموجودة سابقاً (نفسها) */
        /* ... أضف هنا تنسيقات الـ CSS السابقة ... */
        /* سأضعها مختصرة للحفاظ على المساحة، لكن يمكنك إعادة استخدام تنسيقاتك السابقة */
        * { margin:0; padding:0; box-sizing:border-box; }
        body { background: linear-gradient(145deg, #f0f4ff, #e2e8f0); font-family: 'Segoe UI', system-ui, sans-serif; padding:30px 20px; min-height:100vh; display:flex; justify-content:center; }
        .container { max-width:1100px; width:100%; background:rgba(255,255,255,0.8); backdrop-filter:blur(8px); border-radius:40px; padding:32px 36px; box-shadow:0 25px 60px rgba(0,0,0,0.06); border:1px solid rgba(255,255,255,0.5); }
        .admin-header { display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:14px; margin-bottom:32px; padding-bottom:18px; border-bottom:2px solid #e9edf2; }
        .admin-header h2 { font-size:28px; font-weight:800; color:#0f172a; }
        .admin-header .header-actions { display:flex; gap:12px; flex-wrap:wrap; }
        .admin-header .header-actions a { padding:12px 28px; border-radius:60px; font-weight:700; font-size:15px; text-decoration:none; transition:all 0.3s; display:inline-flex; align-items:center; gap:8px; }
        .admin-header .header-actions .btn-home { background:linear-gradient(135deg,#22c55e,#16a34a); color:#fff; box-shadow:0 4px 16px rgba(34,197,94,0.25); }
        .admin-header .header-actions .btn-home:hover { transform:translateY(-3px) scale(1.02); box-shadow:0 8px 28px rgba(34,197,94,0.35); }
        .admin-header .header-actions .btn-logout { background:linear-gradient(135deg,#ef4444,#dc2626); color:#fff; box-shadow:0 4px 16px rgba(239,68,68,0.25); }
        .admin-header .header-actions .btn-logout:hover { transform:translateY(-3px) scale(1.02); box-shadow:0 8px 28px rgba(239,68,68,0.35); }
        .form-card { background:#f8fafc; border-radius:24px; padding:24px 28px; margin-bottom:28px; border:1px solid #e9edf2; transition:box-shadow 0.3s; }
        .form-card:hover { box-shadow:0 8px 24px rgba(0,0,0,0.04); }
        .form-card h3 { font-size:20px; font-weight:700; color:#0f172a; margin-bottom:18px; }
        .form-card form { display:flex; flex-wrap:wrap; align-items:center; gap:14px; }
        .form-card form input[type="text"], .form-card form select { padding:12px 18px; border-radius:40px; border:2px solid #d1d5db; font-size:15px; background:#fff; transition:0.3s; flex:1 1 200px; min-width:140px; }
        .form-card form input[type="text"]:focus, .form-card form select:focus { border-color:#6366f1; outline:none; box-shadow:0 0 0 4px rgba(99,102,241,0.08); }
        .form-card form input[type="file"] { padding:10px 0; font-size:14px; color:#475569; flex:1 1 180px; }
        .form-card form .btn-submit { padding:12px 32px; border:none; border-radius:60px; font-weight:700; font-size:16px; cursor:pointer; transition:all 0.3s; background:linear-gradient(135deg,#6366f1,#8b5cf6); color:#fff; box-shadow:0 4px 16px rgba(99,102,241,0.25); flex:0 0 auto; }
        .form-card form .btn-submit:hover { transform:translateY(-3px) scale(1.02); box-shadow:0 8px 28px rgba(99,102,241,0.35); }
        .section-title { font-size:22px; font-weight:700; color:#0f172a; margin:32px 0 18px 0; padding-bottom:6px; border-bottom:2px solid #e9edf2; }
        .grid-list { display:flex; flex-wrap:wrap; gap:18px; margin-bottom:10px; }
        .grid-item { background:#fff; padding:14px 12px; border-radius:20px; box-shadow:0 2px 8px rgba(0,0,0,0.03); text-align:center; width:140px; border:1px solid #f1f5f9; transition:all 0.25s; position:relative; }
        .grid-item:hover { transform:translateY(-4px); box-shadow:0 8px 24px rgba(0,0,0,0.06); }
        .grid-item img { width:100%; height:80px; object-fit:contain; border-radius:12px; background:#f8fafc; }
        .grid-item strong { display:block; font-size:15px; color:#0f172a; margin:6px 0 2px 0; }
        .grid-item .sub-label { font-size:12px; color:#94a3b8; display:block; margin-bottom:4px; }
        .grid-item .actions { display:flex; justify-content:center; gap:8px; margin-top:4px; }
        .grid-item .actions a { font-size:13px; font-weight:600; text-decoration:none; padding:4px 12px; border-radius:20px; transition:0.2s; }
        .grid-item .actions .edit-link { background:#e0e7ff; color:#4f46e5; }
        .grid-item .actions .edit-link:hover { background:#c7d2fe; }
        .grid-item .actions .delete-link { background:#fee2e2; color:#ef4444; }
        .grid-item .actions .delete-link:hover { background:#fecaca; }
        .edit-badge { position:absolute; top:-8px; right:-8px; background:#6366f1; color:#fff; font-size:10px; padding:2px 10px; border-radius:40px; font-weight:700; }
        @media (max-width:768px) { .container { padding:20px 16px; border-radius:28px; } .admin-header { flex-direction:column; align-items:stretch; } .admin-header .header-actions { justify-content:center; } .form-card form { flex-direction:column; align-items:stretch; } .form-card form input, .form-card form select, .form-card form input[type="file"] { flex:1 1 100%; min-width:unset; } .form-card form .btn-submit { flex:1 1 100%; } .grid-item { width:120px; } }
        @media (max-width:480px) { .container { padding:12px; border-radius:20px; } .admin-header h2 { font-size:22px; } .admin-header .header-actions a { font-size:13px; padding:10px 18px; } .grid-item { width:100px; padding:10px 8px; } .grid-item img { height:60px; } }
    </style>
</head>
<body>
<div class="container">

    <!-- الهيدر -->
    <div class="admin-header">
        <h2>👕 لوحة الإدارة</h2>
        <div class="header-actions">
            <a href="index.php" class="btn-home">🏠 العودة للموقع</a>
            <a href="?logout=1" class="btn-logout">🚪 تسجيل خروج</a>
        </div>
    </div>

    <!-- ============================================================ -->
    <!-- نموذج إضافة منتج -->
    <!-- ============================================================ -->
    <div class="form-card">
        <h3>➕ إضافة منتج جديد (باللون الأبيض)</h3>
        <form method="POST" enctype="multipart/form-data">
            <input type="text" name="product_name" placeholder="اسم المنتج" required>
            <input type="file" name="product_image" accept="image/*" required>
            <button type="submit" name="add_product" class="btn-submit">➕ إضافة</button>
        </form>
    </div>

    <!-- ============================================================ -->
    <!-- عرض المنتجات مع خيارات التعديل والحذف -->
    <!-- ============================================================ -->
    <h3 class="section-title">📦 المنتجات</h3>
    <div class="grid-list">
        <?php while($p=$products->fetch_assoc()): ?>
            <div class="grid-item">
                <img src="<?= PRODUCT_DIR . "product_{$p['id']}_white.png" ?>" alt="">
                <strong><?= $p['name'] ?></strong>
                <div class="actions">
                    <a href="?edit_product=<?= $p['id'] ?>" class="edit-link">✏️ تعديل</a>
                    <a href="?delete_product=<?= $p['id'] ?>" onclick="return confirm('حذف المنتج؟')" class="delete-link">🗑 حذف</a>
                </div>
                <?php if (isset($_GET['edit_product']) && $_GET['edit_product'] == $p['id']): ?>
                    <div style="margin-top:10px; background:#eef2ff; padding:12px; border-radius:16px; text-align:right;">
                        <form method="POST" enctype="multipart/form-data" style="display:flex; flex-wrap:wrap; gap:8px; align-items:center;">
                            <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
                            <input type="text" name="product_name" value="<?= $p['name'] ?>" required style="flex:2; min-width:120px; padding:8px 14px; border-radius:40px; border:2px solid #d1d5db;">
                            <input type="file" name="product_image" accept="image/*" style="flex:1; min-width:100px;">
                            <button type="submit" name="edit_product" class="btn-submit" style="padding:8px 20px; font-size:14px;">💾 حفظ</button>
                            <a href="admin.php" style="color:#ef4444; font-weight:600; font-size:13px;">إلغاء</a>
                        </form>
                    </div>
                <?php endif; ?>
            </div>
        <?php endwhile; ?>
    </div>

    <!-- ============================================================ -->
    <!-- نموذج إضافة لون -->
    <!-- ============================================================ -->
    <div class="form-card">
        <h3>🎨 إضافة لون لمنتج</h3>
        <form method="POST" enctype="multipart/form-data">
            <select name="product_id" required>
                <option value="">اختر المنتج</option>
                <?php $res = $conn->query("SELECT * FROM products ORDER BY name"); while($p=$res->fetch_assoc()): ?>
                    <option value="<?= $p['id'] ?>"><?= $p['name'] ?></option>
                <?php endwhile; ?>
            </select>
            <input type="text" name="color_name" placeholder="اسم اللون (مثل red)" required>
            <input type="text" name="color_label" placeholder="الرمز (#FF0000)" required>
            <input type="file" name="color_image" accept="image/*" required>
            <button type="submit" name="add_color" class="btn-submit">🎨 إضافة لون</button>
        </form>
    </div>

    <!-- ============================================================ -->
    <!-- عرض الألوان مع خيارات التعديل والحذف -->
    <!-- ============================================================ -->
    <h3 class="section-title">🎨 الألوان المضافة</h3>
    <div class="grid-list">
        <?php while($c=$allColors->fetch_assoc()): ?>
            <div class="grid-item">
                <img src="<?= $c['image_path'] ?>" alt="">
                <strong><?= $c['color_name'] ?></strong>
                <span class="sub-label"><?= $c['product_name'] ?></span>
                <div class="actions">
                    <a href="?edit_color=<?= $c['id'] ?>" class="edit-link">✏️ تعديل</a>
                    <a href="?delete_color=<?= $c['id'] ?>" onclick="return confirm('حذف اللون؟')" class="delete-link">🗑 حذف</a>
                </div>
                <?php if (isset($_GET['edit_color']) && $_GET['edit_color'] == $c['id']): ?>
                    <div style="margin-top:10px; background:#eef2ff; padding:12px; border-radius:16px; text-align:right;">
                        <form method="POST" enctype="multipart/form-data" style="display:flex; flex-wrap:wrap; gap:8px; align-items:center;">
                            <input type="hidden" name="color_id" value="<?= $c['id'] ?>">
                            <input type="text" name="color_name" value="<?= $c['color_name'] ?>" required style="flex:1; min-width:80px; padding:8px 14px; border-radius:40px; border:2px solid #d1d5db;">
                            <input type="text" name="color_label" value="<?= $c['color_label'] ?>" required style="flex:1; min-width:80px; padding:8px 14px; border-radius:40px; border:2px solid #d1d5db;">
                            <input type="file" name="color_image" accept="image/*" style="flex:1; min-width:100px;">
                            <button type="submit" name="edit_color" class="btn-submit" style="padding:8px 20px; font-size:14px;">💾 حفظ</button>
                            <a href="admin.php" style="color:#ef4444; font-weight:600; font-size:13px;">إلغاء</a>
                        </form>
                    </div>
                <?php endif; ?>
            </div>
        <?php endwhile; ?>
    </div>

    <!-- ============================================================ -->
    <!-- نموذج إضافة تصميم -->
    <!-- ============================================================ -->
    <div class="form-card">
        <h3>🖼 إضافة تصميم جاهز</h3>
        <form method="POST" enctype="multipart/form-data">
            <select name="product_id" required>
                <option value="">اختر المنتج</option>
                <?php $res = $conn->query("SELECT * FROM products ORDER BY name"); while($p=$res->fetch_assoc()): ?>
                    <option value="<?= $p['id'] ?>"><?= $p['name'] ?></option>
                <?php endwhile; ?>
            </select>
            <input type="text" name="design_name" placeholder="اسم التصميم" required>
            <input type="file" name="design_image" accept="image/*" required>
            <button type="submit" name="add_design" class="btn-submit">🖼 إضافة تصميم</button>
        </form>
    </div>

    <!-- ============================================================ -->
    <!-- عرض التصاميم مع خيارات التعديل والحذف -->
    <!-- ============================================================ -->
    <h3 class="section-title">🖼 التصاميم المضافة</h3>
    <div class="grid-list">
        <?php while($d=$allDesigns->fetch_assoc()): ?>
            <div class="grid-item">
                <img src="<?= $d['image_path'] ?>" alt="">
                <strong><?= $d['name'] ?></strong>
                <span class="sub-label"><?= $d['product_name'] ?></span>
                <div class="actions">
                    <a href="?edit_design=<?= $d['id'] ?>" class="edit-link">✏️ تعديل</a>
                    <a href="?delete_design=<?= $d['id'] ?>" onclick="return confirm('حذف التصميم؟')" class="delete-link">🗑 حذف</a>
                </div>
                <?php if (isset($_GET['edit_design']) && $_GET['edit_design'] == $d['id']): ?>
                    <div style="margin-top:10px; background:#eef2ff; padding:12px; border-radius:16px; text-align:right;">
                        <form method="POST" enctype="multipart/form-data" style="display:flex; flex-wrap:wrap; gap:8px; align-items:center;">
                            <input type="hidden" name="design_id" value="<?= $d['id'] ?>">
                            <input type="text" name="design_name" value="<?= $d['name'] ?>" required style="flex:2; min-width:120px; padding:8px 14px; border-radius:40px; border:2px solid #d1d5db;">
                            <input type="file" name="design_image" accept="image/*" style="flex:1; min-width:100px;">
                            <button type="submit" name="edit_design" class="btn-submit" style="padding:8px 20px; font-size:14px;">💾 حفظ</button>
                            <a href="admin.php" style="color:#ef4444; font-weight:600; font-size:13px;">إلغاء</a>
                        </form>
                    </div>
                <?php endif; ?>
            </div>
        <?php endwhile; ?>
    </div>

</div>
</body>
</html>
