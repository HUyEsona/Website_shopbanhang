<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Kết nối PDO
    $host = "localhost";
    $database = "cuoiky";
    $username = "root";
    $password = "";
    try {
        $pdo = new PDO("mysql:host=$host;dbname=$database", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
        die("Connection failed: " . $e->getMessage());
    }

    // Duyệt qua từng sản phẩm được cập nhật
    foreach ($_POST['update_product'] as $productId => $value) {
        // Lấy dữ liệu từ form
        $nameProduct = $_POST['name_product'][$productId];
        $price = $_POST['price'][$productId];
        $status = $_POST['status'][$productId];
        $description = $_POST['description'][$productId];

        // Cập nhật thông tin sản phẩm (tên, giá, trạng thái, mô tả)
        $query = "UPDATE product SET name_product = ?, price = ?, status = ?, description = ? WHERE product_id = ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$nameProduct, $price, $status, $description, $productId]);

        // Cập nhật hình ảnh nếu có
        for ($i = 1; $i <= 3; $i++) {
            // Kiểm tra xem có tệp hình ảnh nào được tải lên không
            if (isset($_FILES["image$i"]) && $_FILES["image$i"]['error'] == 0) {
                // Lấy tên tệp
                $imageName = $_FILES["image$i"]["name"];
                // Đảm bảo lấy phần mở rộng tệp
                $imagePath = 'uploads/' . basename($imageName);

                // Di chuyển tệp tải lên từ thư mục tạm đến thư mục đích
                if (move_uploaded_file($_FILES["image$i"]["tmp_name"], $imagePath)) {
                    // Cập nhật đường dẫn hình ảnh vào cơ sở dữ liệu
                    $updateImageQuery = "UPDATE product SET img$i = ? WHERE product_id = ?";
                    $stmt = $pdo->prepare($updateImageQuery);
                    $stmt->execute([$imagePath, $productId]);
                }
            }
        }
    }

    // Quay lại trang sản phẩm với thông báo thành công
    header("Location: ../main/admin.php?message=success");
    exit;
}
?>
