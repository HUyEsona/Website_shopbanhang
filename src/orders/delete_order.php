<?php
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

// Xử lý yêu cầu xóa
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $order_id = $_POST['order_id'];

    // Kiểm tra và xóa đơn hàng
    $sql = "DELETE FROM orders WHERE order_id = :order_id";
    $statement = $pdo->prepare($sql);
    $statement->bindParam(':order_id', $order_id, PDO::PARAM_INT);

    if ($statement->execute()) {
        // Xóa thành công, chuyển hướng lại trang quản lý đơn hàng
        header("Location: ../main/admin.php?message=success");
        exit();
    } else {
        echo "Xóa đơn hàng thất bại.";
    }
}
?>
