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
        $quantity = (int)$_POST['quantity'];

        // Kiểm tra nếu số lượng hợp lệ
        if ($quantity < 1) {
            die("Số lượng phải lớn hơn hoặc bằng 1.");
        }

        // Cập nhật số lượng trong giỏ hàng
        $stmt = $conn->prepare("UPDATE carts SET quantity = :quantity WHERE cart_id = :cart_id AND username = :username");
        $stmt->execute([
            'quantity' => $quantity,
            'cart_id' => $cart_id,
            'username' => $username
        ]);

        // Chuyển hướng về giỏ hàng
        header("Location: cart.php");
        exit;
    }
} catch (PDOException $e) {
    die("Cập nhật không thành công: " . $e->getMessage());
}
