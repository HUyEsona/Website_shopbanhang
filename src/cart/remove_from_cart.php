<?php
session_start();

// Kiểm tra nếu người dùng đã đăng nhập
if (!isset($_SESSION['username'])) {
    die("Vui lòng đăng nhập để thực hiện thao tác này.");
}

$username = $_SESSION['username'];

try {
    // Kết nối tới cơ sở dữ liệu
    $conn = new PDO("mysql:host=localhost;dbname=cuoiky", "root", "");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $cart_id = (int)$_POST['cart_id'];

        // Xóa sản phẩm khỏi giỏ hàng
        $stmt = $conn->prepare("DELETE FROM carts WHERE cart_id = :cart_id AND username = :username");
        $stmt->execute([
            'cart_id' => $cart_id,
            'username' => $username
        ]);

        // Chuyển hướng về giỏ hàng
        header("Location: cart.php");
        exit;
    }
} catch (PDOException $e) {
    die("Xóa sản phẩm không thành công: " . $e->getMessage());
}
