<?php
require_once 'config.php';
header('Content-Type: application/json');

// إذا لم يكن هناك ملف مرفوع
if (!isset($_FILES['design_image']) || $_FILES['design_image']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'message' => 'لا يوجد ملف أو خطأ في الرفع']);
    exit;
}

$product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
$design_name = isset($_POST['design_name']) ? trim($_POST['design_name']) : 'تصميم بدون اسم';

if (!$product_id) {
    echo json_encode(['success' => false, 'message' => 'معرف المنتج مطلوب']);
    exit;
}

$ext = pathinfo($_FILES['design_image']['name'], PATHINFO_EXTENSION);
$new_name = 'design_' . time() . '_' . rand(1000, 9999) . '.' . $ext;
$target = DESIGN_DIR . $new_name;

if (move_uploaded_file($_FILES['design_image']['tmp_name'], $target)) {
    // حفظ في قاعدة البيانات
    $stmt = $conn->prepare("INSERT INTO designs (product_id, name, image_path) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $product_id, $design_name, $target);
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'design_id' => $stmt->insert_id,
            'design_name' => $design_name,
            'image_path' => $target
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'فشل حفظ البيانات: ' . $stmt->error]);
    }
    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'فشل نقل الملف إلى المجلد']);
}

$conn->close();
?>
