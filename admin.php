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
    <head>
        <meta charset="UTF-8">
        <title>دخول الأدمن</title>
        <link rel="stylesheet" href="assets/css/style.css">
        <style>
            /* ---- تنسيق صفحة تسجيل الدخول ---- */
            body {
                background: linear-gradient(145deg, #eef2ff, #e2e8f0);
                display: flex;
                justify-content: center;
                align-items: center;
                min-height: 100vh;
                margin: 0;
                padding: 20px;
                font-family: 'Segoe UI', system-ui, sans-serif;
            }
            .login-card {
                background: rgba(255, 255, 255, 0.85);
                backdrop-filter: blur(12px);
                -webkit-backdrop-filter: blur(12px);
                padding: 40px 32px;
                border-radius: 40px;
                box-shadow: 0 20px 60px rgba(0,0,0,0.08);
                max-width: 420px;
                width: 100%;
                border: 1px solid rgba(255,255,255,0.5);
                text-align: center;
            }
            .login-card .lock-icon {
                font-size: 56px;
                display: block;
                margin-bottom: 10px;
            }
            .login-card h2 {
                font-size: 28px;
                font-weight: 800;
                color: #0f172a;
                margin: 0 0 6px 0;
            }
            .login-card .sub {
                color: #64748b;
                font-size: 16px;
                margin-bottom: 24px;
            }
            .login-card input[type="password"] {
                width: 100%;
                padding: 16px 20px;
                border-radius: 60px;
                border: 2px solid #e2e8f0;
                font-size: 18px;
                text-align: center;
                transition: 0.3s;
                background: #f8fafc;
                margin-bottom: 16px;
            }
            .login-card input[type="password"]:focus {
                border-color: #6366f1;
                outline: none;
                box-shadow: 0 0 0 4px rgba(99,102,241,0.12);
                background: #ffffff;
            }
            .login-card .btn-login {
                width: 100%;
                padding: 16px;
                background: linear-gradient(135deg, #6366f1, #8b5cf6);
                color: #fff;
                border: none;
                border-radius: 60px;
                font-size: 20px;
                font-weight: 700;
                cursor: pointer;
                transition: 0.3s;
            }
            .login-card .btn-login:hover {
                transform: scale(1.02);
                box-shadow: 0 8px 30px rgba(99,102,241,0.35);
            }
            .login-card .error-msg {
                color: #ef4444;
                background: #fee2e2;
                padding: 10px 16px;
                border-radius: 60px;
                margin-bottom: 16px;
                font-weight: 600;
                font-size: 14px;
            }
        </style>
    </head>
    <body>
        <div class="login-card">
            <span class="lock-icon">🔐</span>
            <h2>لوحة الإدارة</h2>
            <p class="sub">أدخل كلمة المرور للدخول</p>
            <?php if (isset($error)) echo "<div class='error-msg'>$error</div>"; ?>
            <form method="POST">
                <input type="password" name="password" placeholder="••••••••" required autofocus>
                <button type="submit" class="btn-login">🚪 دخول</button>
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
        $conn->query("INSERT INTO product_colors (product_id, color_name, color_label, image_path) VALUES ($pid, 'white', '#FFFFFF', '$target')");
        header('Location: admin.php');
        exit;
    }
}

// ===== إضافة لون =====
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
    <title>لوحة الإدارة – صمم منتجك</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        /* ============================================
           تنسيق لوحة الإدارة – احترافي فخم
           (بدون تغيير أي كود PHP أو id/name)
           ============================================ */

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: linear-gradient(145deg, #f0f4ff, #e2e8f0);
            font-family: 'Segoe UI', 'Tahoma', system-ui, sans-serif;
            padding: 30px 20px;
            min-height: 100vh;
            display: flex;
            justify-content: center;
        }

        /* ---- الحاوية الرئيسية ---- */
        .container {
            max-width: 1100px;
            width: 100%;
            background: rgba(255, 255, 255, 0.80);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            border-radius: 40px;
            padding: 32px 36px;
            box-shadow: 0 25px 60px rgba(0, 0, 0, 0.06);
            border: 1px solid rgba(255, 255, 255, 0.5);
        }

        /* ---- الهيدر ---- */
        .admin-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 14px;
            margin-bottom: 32px;
            padding-bottom: 18px;
            border-bottom: 2px solid #e9edf2;
        }

        .admin-header h2 {
            font-size: 28px;
            font-weight: 800;
            color: #0f172a;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .admin-header .header-actions {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }

        .admin-header .header-actions a {
            padding: 12px 28px;
            border-radius: 60px;
            font-weight: 700;
            font-size: 15px;
            text-decoration: none;
            transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
            display: inline-flex;
            align-items: center;
            gap: 8px;
            border: none;
        }

        .admin-header .header-actions .btn-home {
            background: linear-gradient(135deg, #22c55e, #16a34a);
            color: #ffffff;
            box-shadow: 0 4px 16px rgba(34, 197, 94, 0.25);
        }

        .admin-header .header-actions .btn-home:hover {
            transform: translateY(-3px) scale(1.02);
            box-shadow: 0 8px 28px rgba(34, 197, 94, 0.35);
        }

        .admin-header .header-actions .btn-logout {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            color: #ffffff;
            box-shadow: 0 4px 16px rgba(239, 68, 68, 0.25);
        }

        .admin-header .header-actions .btn-logout:hover {
            transform: translateY(-3px) scale(1.02);
            box-shadow: 0 8px 28px rgba(239, 68, 68, 0.35);
        }

        /* ---- بطاقات النماذج ---- */
        .form-card {
            background: #f8fafc;
            border-radius: 24px;
            padding: 24px 28px;
            margin-bottom: 28px;
            border: 1px solid #e9edf2;
            transition: box-shadow 0.3s ease;
        }

        .form-card:hover {
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.04);
        }

        .form-card h3 {
            font-size: 20px;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 18px;
        }

        .form-card form {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 14px;
        }

        .form-card form input[type="text"],
        .form-card form select {
            padding: 12px 18px;
            border-radius: 40px;
            border: 2px solid #d1d5db;
            font-size: 15px;
            background: #ffffff;
            transition: 0.3s;
            flex: 1 1 200px;
            min-width: 140px;
        }

        .form-card form input[type="text"]:focus,
        .form-card form select:focus {
            border-color: #6366f1;
            outline: none;
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.08);
        }

        .form-card form input[type="file"] {
            padding: 10px 0;
            font-size: 14px;
            color: #475569;
            flex: 1 1 180px;
        }

        .form-card form .btn-submit {
            padding: 12px 32px;
            border: none;
            border-radius: 60px;
            font-weight: 700;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            color: #ffffff;
            box-shadow: 0 4px 16px rgba(99, 102, 241, 0.25);
            flex: 0 0 auto;
        }

        .form-card form .btn-submit:hover {
            transform: translateY(-3px) scale(1.02);
            box-shadow: 0 8px 28px rgba(99, 102, 241, 0.35);
        }

        /* ---- قوائم العرض ---- */
        .section-title {
            font-size: 22px;
            font-weight: 700;
            color: #0f172a;
            margin: 32px 0 18px 0;
            padding-bottom: 6px;
            border-bottom: 2px solid #e9edf2;
        }

        .grid-list {
            display: flex;
            flex-wrap: wrap;
            gap: 18px;
            margin-bottom: 10px;
        }

        .grid-item {
            background: #ffffff;
            padding: 14px 12px;
            border-radius: 20px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.03);
            text-align: center;
            width: 140px;
            border: 1px solid #f1f5f9;
            transition: all 0.25s ease;
        }

        .grid-item:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.06);
        }

        .grid-item img {
            width: 100%;
            height: 80px;
            object-fit: contain;
            border-radius: 12px;
            background: #f8fafc;
        }

        .grid-item strong {
            display: block;
            font-size: 15px;
            color: #0f172a;
            margin: 6px 0 2px 0;
        }

        .grid-item .sub-label {
            font-size: 12px;
            color: #94a3b8;
            display: block;
            margin-bottom: 4px;
        }

        .grid-item .delete-link {
            color: #ef4444;
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            transition: color 0.2s;
        }

        .grid-item .delete-link:hover {
            color: #dc2626;
            text-decoration: underline;
        }

        /* ---- استجابة ---- */
        @media (max-width: 768px) {
            .container {
                padding: 20px 16px;
                border-radius: 28px;
            }
            .admin-header {
                flex-direction: column;
                align-items: stretch;
                gap: 12px;
            }
            .admin-header .header-actions {
                justify-content: center;
            }
            .form-card form {
                flex-direction: column;
                align-items: stretch;
            }
            .form-card form input[type="text"],
            .form-card form select,
            .form-card form input[type="file"] {
                flex: 1 1 100%;
                min-width: unset;
            }
            .form-card form .btn-submit {
                flex: 1 1 100%;
            }
            .grid-item {
                width: 120px;
            }
        }

        @media (max-width: 480px) {
            .container {
                padding: 12px;
                border-radius: 20px;
            }
            .admin-header h2 {
                font-size: 22px;
            }
            .admin-header .header-actions a {
                font-size: 13px;
                padding: 10px 18px;
            }
            .grid-item {
                width: 100px;
                padding: 10px 8px;
            }
            .grid-item img {
                height: 60px;
            }
        }
    </style>
</head>
<body>
<div class="container">

    <!-- ===== الهيدر ===== -->
    <div class="admin-header">
        <h2>👕 لوحة الإدارة</h2>
        <div class="header-actions">
            <a href="index.php" class="btn-home">🏠 العودة للموقع</a>
            <a href="?logout=1" class="btn-logout">🚪 تسجيل خروج</a>
        </div>
    </div>

    <!-- ===== إضافة منتج ===== -->
    <div class="form-card">
        <h3>➕ إضافة منتج جديد (باللون الأبيض)</h3>
        <form method="POST" enctype="multipart/form-data">
            <input type="text" name="product_name" placeholder="اسم المنتج" required>
            <input type="file" name="product_image" accept="image/*" required>
            <button type="submit" name="add_product" class="btn-submit">➕ إضافة</button>
        </form>
    </div>

    <!-- ===== إضافة لون ===== -->
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

    <!-- ===== إضافة تصميم ===== -->
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

    <!-- ===== عرض المنتجات ===== -->
    <h3 class="section-title">📦 المنتجات</h3>
    <div class="grid-list">
        <?php while($p=$products->fetch_assoc()): ?>
            <div class="grid-item">
                <img src="<?= PRODUCT_DIR . "product_{$p['id']}_white.png" ?>" alt="<?= $p['name'] ?>">
                <strong><?= $p['name'] ?></strong>
                <a href="?delete_product=<?= $p['id'] ?>" onclick="return confirm('حذف المنتج؟')" class="delete-link">🗑 حذف</a>
            </div>
        <?php endwhile; ?>
    </div>

    <!-- ===== عرض الألوان ===== -->
    <h3 class="section-title">🎨 الألوان المضافة</h3>
    <div class="grid-list">
        <?php while($c=$allColors->fetch_assoc()): ?>
            <div class="grid-item">
                <img src="<?= $c['image_path'] ?>" alt="<?= $c['color_name'] ?>">
                <strong><?= $c['color_name'] ?></strong>
                <span class="sub-label"><?= $c['product_name'] ?></span>
            </div>
        <?php endwhile; ?>
    </div>

    <!-- ===== عرض التصاميم ===== -->
    <h3 class="section-title">🖼 التصاميم المضافة</h3>
    <div class="grid-list">
        <?php while($d=$allDesigns->fetch_assoc()): ?>
            <div class="grid-item">
                <img src="<?= $d['image_path'] ?>" alt="<?= $d['name'] ?>">
                <strong><?= $d['name'] ?></strong>
                <span class="sub-label"><?= $d['product_name'] ?></span>
            </div>
        <?php endwhile; ?>
    </div>

</div>
</body>
</html>
