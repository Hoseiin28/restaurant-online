<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || 
    !isset($_SERVER['HTTP_X_REQUESTED_WITH']) || 
    $_SERVER['HTTP_X_REQUESTED_WITH'] !== 'XMLHttpRequest') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'دسترسی غیرمجاز']);
    exit;
}

$food_id = isset($_POST['food_id']) ? (int)$_POST['food_id'] : 0;
$quantity = isset($_POST['quantity']) ? max(1, (int)$_POST['quantity']) : 1;

if ($food_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'غذا نامعتبر است']);
    exit;
}

$food = mysqli_fetch_assoc(mysqli_query($conn, 
    "SELECT id, name, price, is_available FROM foods WHERE id = $food_id"
));

if (!$food) {
    echo json_encode(['success' => false, 'message' => 'غذا یافت نشد']);
    exit;
}

if (!$food['is_available']) {
    echo json_encode(['success' => false, 'message' => 'این غذا در حال حاضر موجود نیست']);
    exit;
}

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

if (isset($_SESSION['cart'][$food_id])) {
    $_SESSION['cart'][$food_id]['quantity'] += $quantity;
} else {
    $_SESSION['cart'][$food_id] = [
        'id' => $food['id'],
        'name' => $food['name'],
        'price' => $food['price'],
        'quantity' => $quantity
    ];
}

$cart_count = 0;
foreach ($_SESSION['cart'] as $item) {
    $cart_count += $item['quantity'];
}

echo json_encode([
    'success' => true,
    'message' => "{$food['name']} به سبد خرید اضافه شد",
    'cart_count' => $cart_count,
    'item' => $_SESSION['cart'][$food_id]
]);