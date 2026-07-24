<?php
// ===== إعدادات قاعدة البيانات =====
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'tshirt_store';

// ===== كلمة مرور الأدمن =====
$admin_password = '123456';

// ===== رقم واتساب (بدون +) =====
$whatsapp_number = '970592401883';

// ===== مسارات المجلدات =====
define('UPLOAD_DIR', 'uploads/');
define('PRODUCT_DIR', UPLOAD_DIR . 'products/');
define('COLOR_DIR', UPLOAD_DIR . 'colors/');
define('DESIGN_DIR', UPLOAD_DIR . 'designs/');
define('USER_IMG_DIR', UPLOAD_DIR . 'user_images/');

// إنشاء المجلدات
foreach ([UPLOAD_DIR, PRODUCT_DIR, COLOR_DIR, DESIGN_DIR, USER_IMG_DIR] as $dir) {
    if (!is_dir($dir)) mkdir($dir, 0777, true);
}

// ===== الاتصال بقاعدة البيانات =====
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
if ($conn->connect_error) {
    die("❌ فشل الاتصال بقاعدة البيانات: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");
?>
