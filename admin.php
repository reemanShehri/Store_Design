<?php
session_start();
require_once 'config.php';

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

// ===== إضافة منتج =====
if (isset($_POST['add_product'])) {
    $name = trim($_POST['product_name']);
    if ($name && isset($_FILES['product_image']) && $_FILES['product_image']['error'] === UPLOAD_ERR_OK) {
        $conn->query("INSERT INTO products (name) VALUES ('$name')");
        $pid = $conn->insert_id;
        $ext = pathinfo($_FILES['product_image']['name'], PATHINFO_EXTENSION);
        $target = PRODUCT_DIR . "product_{$pid}_white." . $ext;
        move_uploaded_file($_FILES['product_image']['tmp_name'], $target);
        $conn->query("INSERT INTO product_colors (product_id, color_name, color_label, image_path)
                      VALUES ($pid, 'white', '#FFFFFF', '$target')");
        header('Location: admin.php');
        exit;
    }
}

// ===== إضافة لون لمنتج =====
if (isset($_POST['add_color'])) {
    $pid = intval($_POST['product_id']);
    $color = trim($_POST['color_name']);
    $label = trim($_POST['color_label']);
    if ($pid && $color && $label && isset($_FILES['color_image']) && $_FILES['color_image']['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['color_image']['name'], PATHINFO_EXTENSION);
        $target = COLOR_DIR . "color_{$pid}_{$color}." . $ext;
        move_uploaded_file($_FILES['color_image']['tmp_name'], $target);
        $conn->query("INSERT INTO product_colors (product_id, color_name, color_label, image_path)
                      VALUES ($pid, '$color', '$label', '$target')");
        header('Location: admin.php');
        exit;
    }
}

// ===== إضافة تصميم =====
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

// ===== حذف منتج =====
if (isset($_GET['delete_product'])) {
    $id = intval($_GET['delete_product']);
    $conn->query("DELETE FROM products WHERE id = $id");
    header('Location: admin.php');
    exit;
}

// ===== جلب البيانات =====
$products = $conn->query("SELECT * FROM products ORDER BY id DESC");
$allColors = $conn->query("SELECT pc.*, p.name as product_name FROM product_colors pc JOIN products p ON pc.product_id = p.id ORDER BY p.id, pc.id");
$allDesigns = $conn->query("SELECT d.*, p.name as product_name FROM designs d JOIN products p ON d.product_id = p.id ORDER BY p.id, d.id");
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>لوحة الإدارة – تيشرت ستور</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="container">
    <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px;">
        <h2>👕 لوحة الإدارة</h2>
        <div style="display:flex; gap:10px;">
            <a href="index.php" class="btn-admin" style="background:linear-gradient(135deg,#22c55e,#16a34a);">🏠 العودة للموقع</a>
            <a href="?logout=1" class="btn-admin" style="background:linear-gradient(135deg,#ef4444,#dc2626);">🚪 تسجيل خروج</a>
        </div>
    </div>

    <!-- إضافة منتج -->
    <div style="background:#f8fafc; padding:20px; border-radius:16px; margin:24px 0;">
        <h3>➕ إضافة منتج جديد (باللون الأبيض)</h3>
        <form method="POST" enctype="multipart/form-data">
            <input type="text" name="product_name" placeholder="اسم المنتج" required style="padding:10px;border-radius:8px;border:1px solid #d1d5db;width:60%;">
            <input type="file" name="product_image" accept="image/*" required style="margin:10px 0;display:block;">
            <button type="submit" name="add_product" class="btn-order" style="padding:10px 30px;font-size:16px;width:auto;">➕ إضافة</button>
        </form>
    </div>

    <!-- إضافة لون -->
    <div style="background:#f8fafc; padding:20px; border-radius:16px; margin:24px 0;">
        <h3>🎨 إضافة لون لمنتج</h3>
        <form method="POST" enctype="multipart/form-data">
            <select name="product_id" required style="padding:10px;border-radius:8px;border:1px solid #d1d5db;width:40%;">
                <option value="">اختر المنتج</option>
                <?php $res = $conn->query("SELECT * FROM products ORDER BY name"); while($p=$res->fetch_assoc()): ?>
                    <option value="<?= $p['id'] ?>"><?= $p['name'] ?></option>
                <?php endwhile; ?>
            </select>
            <input type="text" name="color_name" placeholder="اسم اللون (مثل red)" required style="padding:10px;border-radius:8px;border:1px solid #d1d5db;width:20%;">
            <input type="text" name="color_label" placeholder="الرمز (#FF0000)" required style="padding:10px;border-radius:8px;border:1px solid #d1d5db;width:20%;">
            <input type="file" name="color_image" accept="image/*" required style="margin:10px 0;display:block;">
            <button type="submit" name="add_color" class="btn-order" style="padding:10px 30px;font-size:16px;width:auto;">🎨 إضافة لون</button>
        </form>
    </div>

    <!-- إضافة تصميم -->
    <div style="background:#f8fafc; padding:20px; border-radius:16px; margin:24px 0;">
        <h3>🖼 إضافة تصميم جاهز</h3>
        <form method="POST" enctype="multipart/form-data">
            <select name="product_id" required style="padding:10px;border-radius:8px;border:1px solid #d1d5db;width:40%;">
                <option value="">اختر المنتج</option>
                <?php $res = $conn->query("SELECT * FROM products ORDER BY name"); while($p=$res->fetch_assoc()): ?>
                    <option value="<?= $p['id'] ?>"><?= $p['name'] ?></option>
                <?php endwhile; ?>
            </select>
            <input type="text" name="design_name" placeholder="اسم التصميم" required style="padding:10px;border-radius:8px;border:1px solid #d1d5db;width:40%;">
            <input type="file" name="design_image" accept="image/*" required style="margin:10px 0;display:block;">
            <button type="submit" name="add_design" class="btn-order" style="padding:10px 30px;font-size:16px;width:auto;">🖼 إضافة تصميم</button>
        </form>
    </div>

    <!-- عرض المنتجات -->
    <h3>📦 المنتجات</h3>
    <div style="display:flex;flex-wrap:wrap;gap:16px;margin-bottom:30px;">
        <?php while($p=$products->fetch_assoc()): ?>
            <div style="background:#fff;padding:12px;border-radius:14px;box-shadow:0 2px 8px rgba(0,0,0,0.06);text-align:center;width:140px;">
                <img src="<?= PRODUCT_DIR . "product_{$p['id']}_white.png" ?>" style="width:100%;height:80px;object-fit:contain;border-radius:8px;" alt="">
                <strong><?= $p['name'] ?></strong>
                <br><a href="?delete_product=<?= $p['id'] ?>" onclick="return confirm('حذف المنتج؟')" style="color:red;font-size:13px;">🗑 حذف</a>
            </div>
        <?php endwhile; ?>
    </div>

    <!-- عرض الألوان -->
    <h3>🎨 الألوان المضافة</h3>
    <div style="display:flex;flex-wrap:wrap;gap:16px;margin-bottom:30px;">
        <?php while($c=$allColors->fetch_assoc()): ?>
            <div style="background:#fff;padding:10px;border-radius:14px;box-shadow:0 2px 8px rgba(0,0,0,0.06);text-align:center;width:120px;">
                <img src="<?= $c['image_path'] ?>" style="width:100%;height:70px;object-fit:contain;border-radius:8px;" alt="">
                <div style="font-weight:600;"><?= $c['color_name'] ?></div>
                <small style="color:#64748b;"><?= $c['product_name'] ?></small>
            </div>
        <?php endwhile; ?>
    </div>

    <!-- عرض التصاميم -->
    <h3>🖼 التصاميم المضافة</h3>
    <div style="display:flex;flex-wrap:wrap;gap:16px;">
        <?php while($d=$allDesigns->fetch_assoc()): ?>
            <div style="background:#fff;padding:10px;border-radius:14px;box-shadow:0 2px 8px rgba(0,0,0,0.06);text-align:center;width:120px;">
                <img src="<?= $d['image_path'] ?>" style="width:100%;height:70px;object-fit:contain;border-radius:8px;" alt="">
                <div style="font-weight:600;"><?= $d['name'] ?></div>
                <small style="color:#64748b;"><?= $d['product_name'] ?></small>
            </div>
        <?php endwhile; ?>
    </div>
</div>
</body>
</html>
