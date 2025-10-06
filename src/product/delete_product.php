<?php
session_start();

// Kiểm tra xem người dùng đã đăng nhập với quyền admin chưa
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

// Kết nối cơ sở dữ liệu
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "cuoiky";

try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Kết nối thất bại: " . $e->getMessage());
}

// Kiểm tra nếu nhận được yêu cầu POST để xóa sản phẩm
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Kiểm tra nếu có product_id trong POST
    if (!empty($_POST['product_id'])) {
        $product_id = $_POST['product_id'];
        
        // In ra product_id để kiểm tra
        echo "Đang xóa sản phẩm với ID: " . $product_id;
        
        // Xóa sản phẩm từ bảng product dựa trên product_id
        $stmt = $pdo->prepare("DELETE FROM product WHERE product_id = :product_id");
        
        // Thực thi truy vấn
        if ($stmt->execute([':product_id' => $product_id])) {
            echo "Sản phẩm đã được xóa thành công.";
            header("Location: ../main/admin.php");
            exit();
        } else {
            echo "Lỗi: Không thể xóa sản phẩm.";
        }
    } else {
        echo "Không tìm thấy ID sản phẩm trong yêu cầu.";
    }
} else {
    echo "Yêu cầu không hợp lệ.";
}
?>
