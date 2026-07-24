<?php
require_once 'config.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : null;
$color = isset($_POST['color']) ? trim($_POST['color']) : null;
$size = isset($_POST['size']) ? trim($_POST['size']) : null;
$design_id = isset($_POST['design_id']) ? intval($_POST['design_id']) : null;
$user_image = null;

if (!$product_id || !$color || !$size) {
    echo json_encode(['success' => false, 'message' => 'بيانات ناقصة']);
    exit;
}

// معالجة الصورة المرفوعة
if (isset($_FILES['user_image']) && $_FILES['user_image']['error'] === UPLOAD_ERR_OK) {
    $ext = pathinfo($_FILES['user_image']['name'], PATHINFO_EXTENSION);
    $target = USER_IMG_DIR . uniqid() . '.' . $ext;
    if (move_uploaded_file($_FILES['user_image']['tmp_name'], $target)) {
        $user_image = $target;
    } else {
        echo json_encode(['success' => false, 'message' => 'فشل رفع الصورة']);
        exit;
    }
} else {
    if (!$design_id) {
        echo json_encode(['success' => false, 'message' => 'يجب رفع صورة أو اختيار تصميم']);
        exit;
    }
}

$order_number = 'ORD-' . date('Ymd') . '-' . rand(1000, 9999);
$stmt = $conn->prepare("INSERT INTO orders (order_number, product_id, color_name, size, user_image, design_id) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->bind_param("sisssi", $order_number, $product_id, $color, $size, $user_image, $design_id);

if ($stmt->execute()) {
    echo json_encode([
        'success' => true,
        'order_number' => $order_number,
        'color_name' => $color
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'فشل حفظ الطلب']);
}
$stmt->close();
$conn->close();
?>
