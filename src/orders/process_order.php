<?php
// Khởi động phiên
session_start();

// Kết nối đến cơ sở dữ liệu bằng PDO
try {
    $conn = new PDO("mysql:host=localhost;dbname=cuoiky", "root", "");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Khởi tạo các biến
$username = isset($_SESSION['username']) ? htmlspecialchars(trim($_SESSION['username'])) : null;
$cart_items = [];

// Truy vấn sản phẩm từ giỏ hàng theo username
if (!empty($username)) {
    $sql_carts = "SELECT c.product_id, c.quantity, c.size_id, c.color_id, p.name_product, c.price
                  FROM carts c
                  JOIN product p ON c.product_id = p.product_id
                  WHERE c.username = ?";
    
    $stmt_carts = $conn->prepare($sql_carts);
    $stmt_carts->execute([$username]);
    $cart_items = $stmt_carts->fetchAll(PDO::FETCH_ASSOC);
}

// Kiểm tra và xử lý đặt hàng
if ($_SERVER["REQUEST_METHOD"] == "POST" && !empty($cart_items)) {
    // Lấy dữ liệu từ biểu mẫu và vệ sinh đầu vào
    $name = htmlspecialchars(trim($_POST['name']));
    $email = htmlspecialchars(trim($_POST['email']));
    $address = htmlspecialchars(trim($_POST['address']));
    $phone = htmlspecialchars(trim($_POST['phone']));
    $note = htmlspecialchars(trim($_POST['note']));
    $payment = htmlspecialchars(trim($_POST['payment']));

    // Lấy giá trị order_id lớn nhất hiện có và tạo order_id mới
    $sql_max_order_id = "SELECT IFNULL(MAX(order_id), 0) + 1 AS next_order_id FROM orders";
    $stmt_max_order_id = $conn->prepare($sql_max_order_id);
    $stmt_max_order_id->execute();
    $next_order_id = $stmt_max_order_id->fetchColumn();

    // Tính tổng giá trị đơn hàng
    $total_price = array_sum(array_column($cart_items, 'price'));

    // Chèn dữ liệu vào bảng orders (chỉ thực hiện một lần)
    $sql_orders = "INSERT INTO orders (order_id, username, date_create, total, note, status, name, email, phone, vourcher_code, voucher_id, payment, address)
                   VALUES (?, ?, NOW(), ?, ?, 'Đã đặt hàng', ?, ?, ?, '0', 0, ?, ?)";
    
    $stmt_orders = $conn->prepare($sql_orders);
    if (!$stmt_orders->execute([$next_order_id, $username, $total_price, $note, $name, $email, $phone, $payment, $address])) {
        echo "Lỗi khi thêm đơn hàng: " . $stmt_orders->errorInfo()[2];
        exit;
    }

    // Xử lý từng sản phẩm trong giỏ hàng và thêm vào bảng orderdetails
    foreach ($cart_items as $item) {
        // Lấy thông tin sản phẩm
        $product_id = $item['product_id'];
        $size_id = $item['size_id'];
        $color_id = $item['color_id'];

        // Lấy size từ bảng sizes
        $sql_size = "SELECT size FROM sizes WHERE size_id = ?";
        $stmt_size = $conn->prepare($sql_size);
        $stmt_size->execute([$size_id]);
        $size = $stmt_size->fetchColumn();

        // Lấy color từ bảng colors
        $sql_color = "SELECT color FROM colors WHERE color_id = ?";
        $stmt_color = $conn->prepare($sql_color);
        $stmt_color->execute([$color_id]);
        $color = $stmt_color->fetchColumn();

        // Lấy giá trị detail_id lớn nhất hiện có và tạo detail_id mới
        $sql_max_detail_id = "SELECT IFNULL(MAX(detail_id), 0) + 1 AS next_detail_id FROM orderdetails";
        $stmt_max_detail_id = $conn->prepare($sql_max_detail_id);
        $stmt_max_detail_id->execute();
        $next_detail_id = $stmt_max_detail_id->fetchColumn();

        // Đặt giá trị mặc định cho trạng thái chi tiết đơn hàng
        $status = 'Đang xử lý';

        // Chèn dữ liệu vào bảng orderdetails
        $sql_orderdetails = "INSERT INTO orderdetails (detail_id, order_id, product_id, quantity, price, size_id, color_id, status) 
                             VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt_orderdetails = $conn->prepare($sql_orderdetails);
        if (!$stmt_orderdetails->execute([$next_detail_id, $next_order_id, $product_id, $item['quantity'], $item['price'], $size_id, $color_id, $status])) {
            echo "Lỗi khi chèn dữ liệu vào bảng orderdetails: " . implode(" - ", $stmt_orderdetails->errorInfo());
            exit;
        }
    }

    // Xóa tất cả sản phẩm trong giỏ hàng sau khi đặt hàng thành công
    $sql_delete = "DELETE FROM carts WHERE username = ?";
    $stmt_delete = $conn->prepare($sql_delete);
    if ($stmt_delete->execute([$username])) {
        echo "<script>alert('Đặt hàng thành công!');</script>";
        echo "<script>setTimeout(function(){ window.location.href = '../index.php'; }, 3000);</script>";
    } else {
        echo "Lỗi khi xóa sản phẩm trong giỏ hàng: " . $stmt_delete->errorInfo()[2];
    }
} else {
    echo "<p>Giỏ hàng của bạn trống hoặc có lỗi xảy ra.</p>";
}
?>
