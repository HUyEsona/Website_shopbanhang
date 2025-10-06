<?php
// Khởi động phiên làm việc
session_start();

// Kết nối cơ sở dữ liệu sử dụng PDO
try {
    $conn = new PDO("mysql:host=localhost;dbname=cuoiky", "root", "");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Kết nối thất bại: " . $e->getMessage());
}

// Khởi tạo các biến
$username = isset($_SESSION['username']) ? htmlspecialchars(trim($_SESSION['username'])) : null; // Lấy username từ session
$cart_items = [];

// Truy vấn sản phẩm trong giỏ hàng theo username
if (!empty($username)) {
    $sql_carts = "SELECT c.product_id, c.quantity, c.size_id, c.color_id, p.name_product, c.price
                  FROM carts c
                  JOIN product p ON c.product_id = p.product_id
                  WHERE c.username = ?";
    
    $stmt_carts = $conn->prepare($sql_carts);
    $stmt_carts->execute([$username]);
    $cart_items = $stmt_carts->fetchAll(PDO::FETCH_ASSOC);



    
}

// Kiểm tra và xử lý khi đặt hàng
if ($_SERVER["REQUEST_METHOD"] == "POST" && !empty($cart_items)) {
    // Lấy dữ liệu từ biểu mẫu và vệ sinh đầu vào
    $name = htmlspecialchars(trim($_POST['name']));
    $email = htmlspecialchars(trim($_POST['email']));
    $address = htmlspecialchars(trim($_POST['address']));
    $phone = htmlspecialchars(trim($_POST['phone']));
    $note = htmlspecialchars(trim($_POST['note']));
    $payment = htmlspecialchars(trim($_POST['payment']));

    // Tìm order_id cao nhất và cộng thêm 1
    $sql_max_order = "SELECT MAX(order_id) as max_order_id FROM orders";
    $stmt_max = $conn->prepare($sql_max_order);
    $stmt_max->execute();
    $max_order = $stmt_max->fetch(PDO::FETCH_ASSOC);
    $new_order_id = ($max_order['max_order_id'] ?? 0) + 1; // Cộng thêm 1

    // Vòng lặp để thêm từng sản phẩm vào bảng orders
    foreach ($cart_items as $item) {
        // Lấy thông tin sản phẩm
        $product_id = $item['product_id'];
        $size_id = $item['size_id'];
        $color_id = $item['color_id'];
        $quantity = $item['quantity'];
        $price = $item['price'];

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

        // Tính tổng tiền cho sản phẩm
        $total_price = $item['price'];

        // Thêm đơn hàng vào cơ sở dữ liệu với trạng thái là "Đã đặt hàng"
        $sql = "INSERT INTO orders (order_id, username, date_create, total, note, status, name, email, phone, vourcher_code, voucher_id, payment, address, name_product, color, size, quantity)
                VALUES (?, ?, NOW(), ?, ?, 'Đã đặt hàng', ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($sql);
        if (!$stmt->execute([$new_order_id, $username, $total_price, $note, $name, $email, $phone, "0", 0, $payment, $address, $item['name_product'], $color, $size, $item['quantity']])) {
            echo "Lỗi khi thêm đơn hàng: " . $stmt->errorInfo()[2];
            exit;
        }


        // Đặt giá trị mặc định cho trạng thái chi tiết đơn hàng
        $status = 'Đang xử lý';
        // Chèn dữ liệu vào bảng orderdetails
        
        $stmt_orderdetails = $conn->prepare("INSERT INTO orderdetails (order_id, product_id, quantity, price, size_id, color_id, status) 
                             VALUES (:order_id, :product_id, :quantity, :price, :size_id, :color_id, ?)");
        $stmt_orderdetails->execute([
            ':order_id' => $new_order_id,
            ':product_id' => $product_id,
            ':quantity' => $quantity,
            ':price' => $price,
            ':size_id' => $size_id,
            ':color_id' => $color_id,
        ]);
        $orderdetails = $stmt_orderdetails->fetchAll(PDO::FETCH_ASSOC);
       
    

        
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
