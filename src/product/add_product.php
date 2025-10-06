<?php
session_start();

// Kiểm tra nếu admin đã đăng nhập
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
    $conn = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Kết nối thất bại: " . $e->getMessage());
}

// Kiểm tra nếu form đã được submit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Lấy dữ liệu từ form
    $product_name = $_POST['product_name'];
    $price = $_POST['price'];
    $sizes = $_POST['sizes']; // Mảng kích thước
    $colors = $_POST['colors']; // Mảng màu sắc
    $description = $_POST['description'];
    $brand_id = $_POST['brand_id'];

    // Kiểm tra các trường bắt buộc
    if (empty($product_name) || empty($price) || empty($sizes) || empty($colors) || empty($brand_id)) {
        $_SESSION['error'] = "Vui lòng điền tất cả các trường bắt buộc!";
        header('Location: ../main/admin.php');
        exit;
    }

    // Đường dẫn thư mục lưu ảnh
    $target_dir = "/img/";
    $img1 = basename($_FILES['img1']['name']);
    $img2 = basename($_FILES['img2']['name']);
    $img3 = basename($_FILES['img3']['name']);

    // Kiểm tra lỗi upload ảnh
    $uploadOk = 1;
    foreach ($_FILES as $file) {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $_SESSION['error'] = "Có lỗi khi tải lên hình ảnh. Vui lòng thử lại!";
            $uploadOk = 0;
            break;
        }
    }

    if ($uploadOk) {
        // Di chuyển các file ảnh tới thư mục
        if (
            move_uploaded_file($_FILES['img1']['tmp_name'], $_SERVER['DOCUMENT_ROOT'] . $target_dir . $img1) &&
            move_uploaded_file($_FILES['img2']['tmp_name'], $_SERVER['DOCUMENT_ROOT'] . $target_dir . $img2) &&
            move_uploaded_file($_FILES['img3']['tmp_name'], $_SERVER['DOCUMENT_ROOT'] . $target_dir . $img3)
        ) {
            try {
                // Lấy product_id cao nhất và cộng thêm 1
                $maxIdQuery = $conn->query("SELECT MAX(product_id) AS max_id FROM product");
                $maxIdResult = $maxIdQuery->fetch(PDO::FETCH_ASSOC);
                $maxProductId = $maxIdResult['max_id'];
                $nextProductId = $maxProductId + 1;

                // Thêm sản phẩm vào bảng 'products'
                $sql = "INSERT INTO product (product_id, name_product, price, img1, img2, img3, status, description, brand_id) 
                        VALUES (:product_id, :name_product, :price, :img1, :img2, :img3, :status, :description, :brand_id)";
                $stmt = $conn->prepare($sql);

                // Ràng buộc các tham số
                $stmt->bindParam(':product_id', $nextProductId);
                $stmt->bindParam(':name_product', $product_name);
                $stmt->bindParam(':price', $price);
                $stmt->bindParam(':description', $description);
                $stmt->bindParam(':img1', $img1);
                $stmt->bindParam(':img2', $img2);
                $stmt->bindParam(':img3', $img3);
                $stmt->bindParam(':brand_id', $brand_id);

                // Gán trạng thái mặc định cho sản phẩm mới là "available"
                $status = 'available';
                $stmt->bindParam(':status', $status);

                // Thực thi câu truy vấn và kiểm tra nếu sản phẩm được thêm thành công
                if ($stmt->execute()) {
                    // Lấy product_id của sản phẩm vừa thêm
                    $product_id = $nextProductId;

                    // Thêm các kích thước vào bảng 'size_product'
                    foreach ($sizes as $size_id) {
                        $sizeStmt = $conn->prepare("INSERT INTO size_product (size_id, product_id) VALUES (:size_id, :product_id)");
                        $sizeStmt->execute([':product_id' => $product_id, ':size_id' => $size_id]);
                    }

                    // Thêm các màu sắc vào bảng 'color_product'
                    foreach ($colors as $color_id) {
                        $colorStmt = $conn->prepare("INSERT INTO color_product (color_id, product_id) VALUES (:color_id, :product_id)");
                        $colorStmt->execute([':product_id' => $product_id, ':color_id' => $color_id]);
                    }

                    $_SESSION['success'] = "Sản phẩm đã được thêm thành công!";
                } else {
                    $_SESSION['error'] = "Lỗi khi thêm sản phẩm: " . print_r($stmt->errorInfo(), true);
                }
            } catch (PDOException $e) {
                $_SESSION['error'] = "Lỗi khi thực thi câu truy vấn: " . $e->getMessage();
            }
        } else {
            $_SESSION['error'] = "Lỗi khi di chuyển hình ảnh vào thư mục lưu trữ.";
        }
    }

    // Chuyển hướng lại trang admin.php với thông báo lỗi hoặc thành công
    header('Location: ../main/admin.php');
    exit;
}
?>
