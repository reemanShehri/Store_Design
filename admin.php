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
// تحديد التبويب النشط
// ============================================================
$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'manage';

// ============================================================
// معالجة الإضافة والتعديل والحذف (نفس الكود السابق)
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
        header('Location: admin.php?tab=manage');
        exit;
    }
}

// --- تعديل منتج ---
if (isset($_POST['edit_product'])) {
    $id = intval($_POST['product_id']);
    $name = trim($_POST['product_name']);
    if ($id && $name) {
        $conn->query("UPDATE products SET name = '$name' WHERE id = $id");
        if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] === UPLOAD_ERR_OK) {
            $ext = pathinfo($_FILES['product_image']['name'], PATHINFO_EXTENSION);
            $target = PRODUCT_DIR . "product_{$id}_white." . $ext;
            move_uploaded_file($_FILES['product_image']['tmp_name'], $target);
            $conn->query("UPDATE product_colors SET image_path = '$target' WHERE product_id = $id AND color_name = 'white'");
        }
        header('Location: admin.php?tab=manage');
        exit;
    }
}

// --- حذف منتج ---
if (isset($_GET['delete_product'])) {
    $id = intval($_GET['delete_product']);
    $conn->query("DELETE FROM products WHERE id = $id");
    header('Location: admin.php?tab=manage');
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
        header('Location: admin.php?tab=manage');
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
        if (isset($_FILES['color_image']) && $_FILES['color_image']['error'] === UPLOAD_ERR_OK) {
            $ext = pathinfo($_FILES['color_image']['name'], PATHINFO_EXTENSION);
            $target = COLOR_DIR . "color_{$id}_" . uniqid() . "." . $ext;
            move_uploaded_file($_FILES['color_image']['tmp_name'], $target);
            $update .= ", image_path = '$target'";
        }
        $update .= " WHERE id = $id";
        $conn->query($update);
        header('Location: admin.php?tab=manage');
        exit;
    }
}

// --- حذف لون ---
if (isset($_GET['delete_color'])) {
    $id = intval($_GET['delete_color']);
    $conn->query("DELETE FROM product_colors WHERE id = $id");
    header('Location: admin.php?tab=manage');
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
        header('Location: admin.php?tab=manage');
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
        header('Location: admin.php?tab=manage');
        exit;
    }
}

// --- حذف تصميم ---
if (isset($_GET['delete_design'])) {
    $id = intval($_GET['delete_design']);
    $conn->query("DELETE FROM designs WHERE id = $id");
    header('Location: admin.php?tab=manage');
    exit;
}

// --- حذف طلب ---
if (isset($_GET['delete_order'])) {
    $id = intval($_GET['delete_order']);
    $conn->query("DELETE FROM orders WHERE id = $id");
    header('Location: admin.php?tab=orders');
    exit;
}

// ============================================================
// جلب البيانات حسب التبويب
// ============================================================
$products = $conn->query("SELECT * FROM products ORDER BY id DESC");
$allColors = $conn->query("SELECT pc.*, p.name as product_name FROM product_colors pc JOIN products p ON pc.product_id = p.id ORDER BY p.id, pc.id");
$allDesigns = $conn->query("SELECT d.*, p.name as product_name FROM designs d JOIN products p ON d.product_id = p.id ORDER BY p.id, d.id");

// جلب الطلبات مع أسماء المنتجات والتصاميم
$orders = $conn->query("
    SELECT o.*,
           p.name as product_name,
           d.name as design_name
    FROM orders o
    LEFT JOIN products p ON o.product_id = p.id
    LEFT JOIN designs d ON o.design_id = d.id
    ORDER BY o.id DESC
");

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
    <style>
        /* تنسيقات عامة (نفس السابق) */
        * { margin:0; padding:0; box-sizing:border-box; }
        body { background: linear-gradient(145deg, #f0f4ff, #e2e8f0); font-family: 'Segoe UI', system-ui, sans-serif; padding:30px 20px; min-height:100vh; display:flex; justify-content:center; }
        .container { max-width:1100px; width:100%; background:rgba(255,255,255,0.8); backdrop-filter:blur(8px); border-radius:40px; padding:32px 36px; box-shadow:0 25px 60px rgba(0,0,0,0.06); border:1px solid rgba(255,255,255,0.5); }
        .admin-header { display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:14px; margin-bottom:20px; padding-bottom:18px; border-bottom:2px solid #e9edf2; }
        .admin-header h2 { font-size:28px; font-weight:800; color:#0f172a; }
        .admin-header .header-actions { display:flex; gap:12px; flex-wrap:wrap; }
        .admin-header .header-actions a { padding:12px 28px; border-radius:60px; font-weight:700; font-size:15px; text-decoration:none; transition:all 0.3s; display:inline-flex; align-items:center; gap:8px; }
        .admin-header .header-actions .btn-home { background:linear-gradient(135deg,#22c55e,#16a34a); color:#fff; box-shadow:0 4px 16px rgba(34,197,94,0.25); }
        .admin-header .header-actions .btn-home:hover { transform:translateY(-3px) scale(1.02); }
        .admin-header .header-actions .btn-logout { background:linear-gradient(135deg,#ef4444,#dc2626); color:#fff; box-shadow:0 4px 16px rgba(239,68,68,0.25); }
        .admin-header .header-actions .btn-logout:hover { transform:translateY(-3px) scale(1.02); }

        /* شريط التبويبات */
        .tabs-nav { display:flex; gap:6px; margin-bottom:28px; border-bottom:2px solid #e9edf2; padding-bottom:0; flex-wrap:wrap; }
        .tabs-nav a { padding:12px 28px; border-radius:40px 40px 0 0; font-weight:700; font-size:16px; text-decoration:none; color:#64748b; background:transparent; transition:all 0.3s; border-bottom:4px solid transparent; }
        .tabs-nav a:hover { color:#0f172a; background:#f1f5f9; }
        .tabs-nav a.active { color:#4f46e5; background:#eef2ff; border-bottom-color:#4f46e5; }

        /* بطاقات النماذج وقوائم العرض (نفس السابق) */
        .form-card { background:#f8fafc; border-radius:24px; padding:24px 28px; margin-bottom:28px; border:1px solid #e9edf2; transition:box-shadow 0.3s; }
        .form-card:hover { box-shadow:0 8px 24px rgba(0,0,0,0.04); }
        .form-card h3 { font-size:20px; font-weight:700; color:#0f172a; margin-bottom:18px; }
        .form-card form { display:flex; flex-wrap:wrap; align-items:center; gap:14px; }
        .form-card form input[type="text"], .form-card form select { padding:12px 18px; border-radius:40px; border:2px solid #d1d5db; font-size:15px; background:#fff; transition:0.3s; flex:1 1 200px; min-width:140px; }
        .form-card form input[type="text"]:focus, .form-card form select:focus { border-color:#6366f1; outline:none; box-shadow:0 0 0 4px rgba(99,102,241,0.08); }
        .form-card form input[type="file"] { padding:10px 0; font-size:14px; color:#475569; flex:1 1 180px; }
        .form-card form .btn-submit { padding:12px 32px; border:none; border-radius:60px; font-weight:700; font-size:16px; cursor:pointer; transition:all 0.3s; background:linear-gradient(135deg,#6366f1,#8b5cf6); color:#fff; box-shadow:0 4px 16px rgba(99,102,241,0.25); flex:0 0 auto; }
        .form-card form .btn-submit:hover { transform:translateY(-3px) scale(1.02); }
        .section-title { font-size:22px; font-weight:700; color:#0f172a; margin:32px 0 18px 0; padding-bottom:6px; border-bottom:2px solid #e9edf2; }
        .grid-list { display:flex; flex-wrap:wrap; gap:18px; margin-bottom:10px; }
        .grid-item { background:#fff; padding:14px 12px; border-radius:20px; box-shadow:0 2px 8px rgba(0,0,0,0.03); text-align:center; width:140px; border:1px solid #f1f5f9; transition:all 0.25s; position:relative; }
        .grid-item:hover { transform:translateY(-4px); box-shadow:0 8px 24px rgba(0,0,0,0.06); }
        .grid-item img { width:100%; height:80px; object-fit:contain; border-radius:12px; background:#f8fafc; }
        .grid-item strong { display:block; font-size:15px; color:#0f172a; margin:6px 0 2px 0; }
        .grid-item .sub-label { font-size:12px; color:#94a3b8; display:block; margin-bottom:4px; }
        .grid-item .actions { display:flex; justify-content:center; gap:8px; margin-top:4px; flex-wrap:wrap; }
        .grid-item .actions a { font-size:13px; font-weight:600; text-decoration:none; padding:4px 12px; border-radius:20px; transition:0.2s; }
        .grid-item .actions .edit-link { background:#e0e7ff; color:#4f46e5; }
        .grid-item .actions .edit-link:hover { background:#c7d2fe; }
        .grid-item .actions .delete-link { background:#fee2e2; color:#ef4444; }
        .grid-item .actions .delete-link:hover { background:#fecaca; }

        /* جدول الطلبات */
        .table-wrapper { overflow-x:auto; border-radius:20px; border:1px solid #e9edf2; background:#fff; }
        table.orders-table { width:100%; border-collapse:collapse; font-size:15px; min-width:650px; }
        table.orders-table thead { background:#f8fafc; border-bottom:2px solid #e9edf2; }
        table.orders-table th { text-align:right; padding:14px 18px; font-weight:700; color:#0f172a; }
        table.orders-table td { padding:14px 18px; border-bottom:1px solid #f1f5f9; vertical-align:middle; }
        table.orders-table tbody tr:hover { background:#fafafa; }
        table.orders-table .order-img { width:50px; height:50px; object-fit:contain; border-radius:10px; background:#f8fafc; border:1px solid #e9edf2; }
        table.orders-table .order-badge { display:inline-block; background:#eef2ff; color:#4f46e5; padding:2px 12px; border-radius:40px; font-weight:600; font-size:12px; }
        table.orders-table .empty-msg { text-align:center; color:#94a3b8; padding:40px 0; font-size:18px; }

        @media (max-width:768px) { .container { padding:20px 16px; } .admin-header { flex-direction:column; align-items:stretch; } .admin-header .header-actions { justify-content:center; } .form-card form { flex-direction:column; align-items:stretch; } .form-card form input, .form-card form select { flex:1 1 100%; } .form-card form .btn-submit { flex:1 1 100%; } .grid-item { width:120px; } .tabs-nav a { padding:10px 16px; font-size:14px; } }
        @media (max-width:480px) { .container { padding:12px; } .admin-header h2 { font-size:22px; } .grid-item { width:100px; } .grid-item img { height:60px; } .tabs-nav a { font-size:12px; padding:8px 12px; } table.orders-table { font-size:13px; } table.orders-table th, table.orders-table td { padding:10px 12px; } .order-img { width:35px; height:35px; } }
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

    <!-- شريط التبويبات -->
    <div class="tabs-nav">
        <a href="?tab=manage" class="<?= $active_tab == 'manage' ? 'active' : '' ?>">📦 إدارة المنتجات</a>
        <a href="?tab=orders" class="<?= $active_tab == 'orders' ? 'active' : '' ?>">📋 الطلبات الواردة</a>
    </div>

    <!-- ============================================================ -->
    <!-- تبويب: إدارة المنتجات -->
    <!-- ============================================================ -->
    <?php if ($active_tab == 'manage'): ?>

        <!-- إضافة منتج -->
        <div class="form-card">
            <h3>➕ إضافة منتج جديد (باللون الأبيض)</h3>
            <form method="POST" enctype="multipart/form-data">
                <input type="text" name="product_name" placeholder="اسم المنتج" required>
                <input type="file" name="product_image" accept="image/*" required>
                <button type="submit" name="add_product" class="btn-submit">➕ إضافة</button>
            </form>
        </div>

        <!-- عرض المنتجات -->
        <h3 class="section-title">📦 المنتجات</h3>
        <div class="grid-list">
            <?php while($p=$products->fetch_assoc()): ?>
                <div class="grid-item">
                    <img src="<?= PRODUCT_DIR . "product_{$p['id']}_white.png" ?>" alt="">
                    <strong><?= $p['name'] ?></strong>
                    <div class="actions">
                        <a href="?tab=manage&edit_product=<?= $p['id'] ?>" class="edit-link">✏️ تعديل</a>
                        <a href="?tab=manage&delete_product=<?= $p['id'] ?>" onclick="return confirm('حذف المنتج؟')" class="delete-link">🗑 حذف</a>
                    </div>
                    <?php if (isset($_GET['edit_product']) && $_GET['edit_product'] == $p['id']): ?>
                        <div style="margin-top:10px; background:#eef2ff; padding:12px; border-radius:16px; text-align:right;">
                            <form method="POST" enctype="multipart/form-data" style="display:flex; flex-wrap:wrap; gap:8px; align-items:center;">
                                <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
                                <input type="text" name="product_name" value="<?= $p['name'] ?>" required style="flex:2; min-width:120px; padding:8px 14px; border-radius:40px; border:2px solid #d1d5db;">
                                <input type="file" name="product_image" accept="image/*" style="flex:1; min-width:100px;">
                                <button type="submit" name="edit_product" class="btn-submit" style="padding:8px 20px; font-size:14px;">💾 حفظ</button>
                                <a href="?tab=manage" style="color:#ef4444; font-weight:600; font-size:13px;">إلغاء</a>
                            </form>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endwhile; ?>
        </div>

        <!-- إضافة لون -->
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

        <!-- عرض الألوان -->
        <h3 class="section-title">🎨 الألوان المضافة</h3>
        <div class="grid-list">
            <?php while($c=$allColors->fetch_assoc()): ?>
                <div class="grid-item">
                    <img src="<?= $c['image_path'] ?>" alt="">
                    <strong><?= $c['color_name'] ?></strong>
                    <span class="sub-label"><?= $c['product_name'] ?></span>
                    <div class="actions">
                        <a href="?tab=manage&edit_color=<?= $c['id'] ?>" class="edit-link">✏️ تعديل</a>
                        <a href="?tab=manage&delete_color=<?= $c['id'] ?>" onclick="return confirm('حذف اللون؟')" class="delete-link">🗑 حذف</a>
                    </div>
                    <?php if (isset($_GET['edit_color']) && $_GET['edit_color'] == $c['id']): ?>
                        <div style="margin-top:10px; background:#eef2ff; padding:12px; border-radius:16px;">
                            <form method="POST" enctype="multipart/form-data" style="display:flex; flex-wrap:wrap; gap:8px; align-items:center;">
                                <input type="hidden" name="color_id" value="<?= $c['id'] ?>">
                                <input type="text" name="color_name" value="<?= $c['color_name'] ?>" required style="flex:1; min-width:80px; padding:8px 14px; border-radius:40px; border:2px solid #d1d5db;">
                                <input type="text" name="color_label" value="<?= $c['color_label'] ?>" required style="flex:1; min-width:80px; padding:8px 14px; border-radius:40px; border:2px solid #d1d5db;">
                                <input type="file" name="color_image" accept="image/*" style="flex:1; min-width:100px;">
                                <button type="submit" name="edit_color" class="btn-submit" style="padding:8px 20px; font-size:14px;">💾 حفظ</button>
                                <a href="?tab=manage" style="color:#ef4444; font-weight:600; font-size:13px;">إلغاء</a>
                            </form>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endwhile; ?>
        </div>

        <!-- إضافة تصميم -->
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

        <!-- عرض التصاميم -->
        <h3 class="section-title">🖼 التصاميم المضافة</h3>
        <div class="grid-list">
            <?php while($d=$allDesigns->fetch_assoc()): ?>
                <div class="grid-item">
                    <img src="<?= $d['image_path'] ?>" alt="">
                    <strong><?= $d['name'] ?></strong>
                    <span class="sub-label"><?= $d['product_name'] ?></span>
                    <div class="actions">
                        <a href="?tab=manage&edit_design=<?= $d['id'] ?>" class="edit-link">✏️ تعديل</a>
                        <a href="?tab=manage&delete_design=<?= $d['id'] ?>" onclick="return confirm('حذف التصميم؟')" class="delete-link">🗑 حذف</a>
                    </div>
                    <?php if (isset($_GET['edit_design']) && $_GET['edit_design'] == $d['id']): ?>
                        <div style="margin-top:10px; background:#eef2ff; padding:12px; border-radius:16px;">
                            <form method="POST" enctype="multipart/form-data" style="display:flex; flex-wrap:wrap; gap:8px; align-items:center;">
                                <input type="hidden" name="design_id" value="<?= $d['id'] ?>">
                                <input type="text" name="design_name" value="<?= $d['name'] ?>" required style="flex:2; min-width:120px; padding:8px 14px; border-radius:40px; border:2px solid #d1d5db;">
                                <input type="file" name="design_image" accept="image/*" style="flex:1; min-width:100px;">
                                <button type="submit" name="edit_design" class="btn-submit" style="padding:8px 20px; font-size:14px;">💾 حفظ</button>
                                <a href="?tab=manage" style="color:#ef4444; font-weight:600; font-size:13px;">إلغاء</a>
                            </form>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endwhile; ?>
        </div>

    <?php endif; ?>

    <!-- ============================================================ -->
    <!-- تبويب: الطلبات الواردة -->
    <!-- ============================================================ -->
    <?php if ($active_tab == 'orders'): ?>

        <div style="display:flex; justify-content:space-between; align-items:center; margin: 10px 0 20px 0; flex-wrap:wrap; gap:10px;">
            <h3 class="section-title" style="border-bottom:none; margin:0; padding:0;">📋 قائمة الطلبات</h3>
            <span style="background:#eef2ff; color:#4f46e5; padding:6px 18px; border-radius:40px; font-weight:700; font-size:14px;">
                إجمالي الطلبات: <?= $orders->num_rows ?>
            </span>
        </div>

        <div class="table-wrapper">
            <table class="orders-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>رقم الطلب</th>
                        <th>المنتج</th>
                        <th>اللون</th>
                        <th>المقاس</th>
                        <th>الصورة المرفوعة</th>
                        <th>التصميم</th>
                        <th>التاريخ</th>
                        <th>إجراء</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($orders->num_rows > 0): ?>
                        <?php while($row = $orders->fetch_assoc()): ?>
                            <tr>
                                <td><strong>#<?= $row['id'] ?></strong></td>
                                <td><span class="order-badge"><?= $row['order_number'] ?></span></td>
                                <td><?= htmlspecialchars($row['product_name'] ?? 'محذوف') ?></td>
                                <td><span style="display:inline-block; width:20px; height:20px; border-radius:50%; background:<?= $row['color_name'] ?>; border:1px solid #ddd; vertical-align:middle;"></span> <?= $row['color_name'] ?></td>
                                <td><strong><?= $row['size'] ?></strong></td>
                           <td>
    <?php if ($row['user_image']): ?>
        <img src="<?= $row['user_image'] ?>"
             style="width:60px; height:60px; object-fit:cover; border-radius:12px; border:1px solid #e9edf2; cursor:pointer;"
             alt="صورة مرفوعة"
             onclick="window.open(this.src, '_blank')"
             title="اضغط لتكبير الصورة">
    <?php else: ?>
        <span style="color:#94a3b8; font-size:13px;">🚫 لا توجد</span>
    <?php endif; ?>
</td>
<td>
    <?php if ($row['design_id']): ?>
        <?php
            // جلب صورة التصميم من قاعدة البيانات
            $design_img = '';
            $design_query = $conn->query("SELECT image_path FROM designs WHERE id = {$row['design_id']}");
            if ($design_query && $design_query->num_rows > 0) {
                $design_row = $design_query->fetch_assoc();
                $design_img = $design_row['image_path'];
            }
        ?>
        <?php if ($design_img): ?>
            <img src="<?= $design_img ?>"
                 style="width:60px; height:60px; object-fit:contain; border-radius:12px; border:1px solid #e9edf2; cursor:pointer;"
                 alt="تصميم"
                 onclick="window.open(this.src, '_blank')"
                 title="اضغط لتكبير الصورة">
        <?php else: ?>
            <span style="color:#94a3b8; font-size:13px;">📷 لا توجد صورة</span>
        <?php endif; ?>
    <?php else: ?>
        <span style="color:#94a3b8; font-size:13px;">🎨 تصميم مخصص</span>
    <?php endif; ?>
</td>

<td style="font-size:13px; color:#475569;"><?= date('Y-m-d', strtotime($row['created_at'])) ?></td>
                                <td>
                                    <a href="?tab=orders&delete_order=<?= $row['id'] ?>" onclick="return confirm('حذف هذا الطلب؟')" style="color:#ef4444; font-weight:600; text-decoration:none; padding:4px 12px; background:#fee2e2; border-radius:20px; display:inline-block;">🗑 حذف</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="9" class="empty-msg">📭 لا توجد طلبات حتى الآن</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    <?php endif; ?>

</div>
</body>
</html>
