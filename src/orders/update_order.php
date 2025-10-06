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
// Kiểm tra nếu yêu cầu là POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Lấy giá trị từ form
    $order_id = $_POST['order_id'];
    $status = $_POST['status'];

    try {
        // Chuẩn bị câu truy vấn SQL để cập nhật trạng thái
        $sql = "UPDATE orders SET status = :status WHERE order_id = :order_id;
                UPDATE orderdetails SET status = :status WHERE order_id = :order_id;";
        $stmt = $pdo->prepare($sql); // Sử dụng biến $pdo (kết nối cơ sở dữ liệu PDO)

        // Thực thi câu truy vấn với tham số
        $stmt->execute([
            ':status' => $status,
            ':order_id' => $order_id
        ]);

        // Điều hướng về trang quản lý với thông báo thành công
        header("Location:../main/admin.php?message=success");
        exit;

    } catch (PDOException $e) {
        // In ra lỗi nếu có
        echo "Lỗi khi cập nhật đơn hàng: " . $e->getMessage();
    }
}
?>
