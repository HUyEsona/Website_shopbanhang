<?php
// Kết nối cơ sở dữ liệu
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "cuoiky";

try {
    // Thiết lập kết nối PDO
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Kết nối thất bại: " . $e->getMessage());
}

// Xử lý form khi gửi
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Lấy dữ liệu từ form và đảm bảo các biến không bị null
    $vourcher_code = isset($_POST['vourcher_code']) ? $_POST['vourcher_code'] : '';
    $day_start = isset($_POST['day_start']) ? $_POST['day_start'] : '';
    $day_end = isset($_POST['day_end']) ? $_POST['day_end'] : '';
    $price_min = isset($_POST['price_min']) ? $_POST['price_min'] : 0;
    $price = isset($_POST['price']) ? $_POST['price'] : 0;
    $status = isset($_POST['status']) ? $_POST['status'] : 1; // Mặc định là hoạt động

    // Kiểm tra các trường quan trọng đã được nhập đầy đủ và hợp lệ
    if ($vourcher_code && $day_start && $day_end && $price_min >= 0 && $price >= 0) {
        try {
            // Lấy voucher_id cao nhất hiện tại trong bảng vourcher (nếu cần)
            $stmt = $pdo->prepare("SELECT MAX(vourcher_id) AS max_id FROM vourcher");
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            // Nếu không có voucher nào, thiết lập voucher_id là 1, ngược lại lấy ID cao nhất + 1
            $new_voucher_id = $result['max_id'] ? $result['max_id'] + 1 : 1;

            // Chuẩn bị câu truy vấn để chèn dữ liệu mới vào bảng
            $stmt = $pdo->prepare("INSERT INTO vourcher (vourcher_id, vourcher_code, day_start, day_end, price_min, price, status)
                                   VALUES (:vourcher_id, :vourcher_code, :day_start, :day_end, :price_min, :price, :status)");

            // Thực thi truy vấn
            $stmt->execute([
                ':vourcher_id' => $new_voucher_id,
                ':vourcher_code' => $vourcher_code,
                ':day_start' => $day_start,
                ':day_end' => $day_end,
                ':price_min' => $price_min,
                ':price' => $price,
                ':status' => $status
            ]);

            // Điều hướng về trang admin.php sau khi thêm thành công
            header('Location: ../main/admin.php');
            exit;
        } catch (PDOException $e) {
            echo "Lỗi: Không thể thêm voucher. Chi tiết lỗi: " . $e->getMessage();
        }
    } else {
        echo "Lỗi: Vui lòng kiểm tra lại dữ liệu nhập vào.";
    }
}
?>
