<?php
// Kết nối cơ sở dữ liệu
require_once '../connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    var_dump($_POST); // In ra tất cả dữ liệu gửi từ form để kiểm tra
}

// Kiểm tra nếu form đã được gửi đi (POST)
if (isset($_POST['update_product'])) {
    // Lấy thông tin từ form
    $product_id = $_POST['product_id'];
    $name_product = $_POST['name_product'];
    $brand_name = $_POST['brand_name'];
    $price = $_POST['price'];
    $status = $_POST['status'];
    $description = $_POST['description'];

    // Gán brand_id dựa trên brand_name
    $brand_id = null;
    switch ($brand_name) {
        case 'Nike':
            $brand_id = 1;
            break;
        case 'Adidas':
            $brand_id = 2;
            break;
        case 'Bitis':
            $brand_id = 3;
            break;
        default:
            echo "<script>alert('Thương hiệu không hợp lệ!'); window.location.href = 'edit_product.php?id={$product_id}';</script>";
            exit;
    }

    // Lấy danh sách màu sắc và kích thước mới
    $color_ids = isset($_POST['color_ids']) ? $_POST['color_ids'] : [];
    $new_color = isset($_POST['new_color']) ? $_POST['new_color'] : '';
    $size_ids = isset($_POST['size_ids']) ? $_POST['size_ids'] : [];
    $new_size = isset($_POST['new_size']) ? $_POST['new_size'] : '';

    // Kiểm tra và xử lý hình ảnh (nếu có)
    $img1 = isset($_POST['img1']) ? $_POST['img1'] : '';
    $img2 = isset($_POST['img2']) ? $_POST['img2'] : '';
    $img3 = isset($_POST['img3']) ? $_POST['img3'] : '';
    
    if (isset($_FILES['image1']) && $_FILES['image1']['error'] === UPLOAD_ERR_OK) {
        $img1 = 'images/' . basename($_FILES['image1']['name']);
        move_uploaded_file($_FILES['image1']['tmp_name'], $img1);
    }
    if (isset($_FILES['image2']) && $_FILES['image2']['error'] === UPLOAD_ERR_OK) {
        $img2 = 'images/' . basename($_FILES['image2']['name']);
        move_uploaded_file($_FILES['image2']['tmp_name'], $img2);
    }
    if (isset($_FILES['image3']) && $_FILES['image3']['error'] === UPLOAD_ERR_OK) {
        $img3 = 'images/' . basename($_FILES['image3']['name']);
        move_uploaded_file($_FILES['image3']['tmp_name'], $img3);
    }

    // Bắt đầu giao dịch
    $conn->autocommit(FALSE); // Tắt tự động commit để bắt đầu giao dịch

    try {
        // Cập nhật thông tin sản phẩm vào cơ sở dữ liệu
        $stmt = $conn->prepare("UPDATE product SET 
            name_product = ?, 
            brand_id = ?, 
            price = ?, 
            status = ?, 
            description = ?, 
            img1 = ?, 
            img2 = ?, 
            img3 = ? 
            WHERE product_id = ?");
        
        $stmt->bind_param("sidsssssi", $name_product, $brand_id, $price, $status, $description, $img1, $img2, $img3, $product_id);
        $stmt->execute();

        // Xóa bản ghi cũ trong bảng liên kết color_product và size_product
        $stmt = $conn->prepare("DELETE FROM color_product WHERE product_id = ?");
        $stmt->bind_param("i", $product_id);
        $stmt->execute();

        $stmt = $conn->prepare("DELETE FROM size_product WHERE product_id = ?");
        $stmt->bind_param("i", $product_id);
        $stmt->execute();

        // Kiểm tra và xóa bản ghi không còn được sử dụng trong `colors` và `sizes`
        $stmt = $conn->prepare("DELETE FROM colors WHERE color_id NOT IN (SELECT DISTINCT color_id FROM color_product)");
        $stmt->execute();

        $stmt = $conn->prepare("DELETE FROM sizes WHERE size_id NOT IN (SELECT DISTINCT size_id FROM size_product)");
        $stmt->execute();

        // Thêm màu sắc mới nếu có
        if ($new_color) {
            // Kiểm tra màu sắc chưa tồn tại
            $stmt = $conn->prepare("SELECT color_id FROM colors WHERE color = ?");
            $stmt->bind_param("s", $new_color);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows === 0) {
                $stmt = $conn->prepare("INSERT INTO colors (color) VALUES (?)");
                $stmt->bind_param("s", $new_color);
                $stmt->execute();
                $new_color_id = $conn->insert_id;

                $stmt = $conn->prepare("INSERT INTO color_product (product_id, color_id) VALUES (?, ?)");
                $stmt->bind_param("ii", $product_id, $new_color_id);
                $stmt->execute();
            }
        }

        // Thêm kích thước mới nếu có
        if ($new_size) {
            // Kiểm tra kích thước chưa tồn tại
            $stmt = $conn->prepare("SELECT size_id FROM sizes WHERE size = ?");
            $stmt->bind_param("s", $new_size);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows === 0) {
                $stmt = $conn->prepare("INSERT INTO sizes (size) VALUES (?)");
                $stmt->bind_param("s", $new_size);
                $stmt->execute();
                $new_size_id = $conn->insert_id;

                $stmt = $conn->prepare("INSERT INTO size_product (product_id, size_id) VALUES (?, ?)");
                $stmt->bind_param("ii", $product_id, $new_size_id);
                $stmt->execute();
            }
        }

        // Thêm các màu sắc đã chọn từ form
        foreach ($color_ids as $color_id) {
            $stmt = $conn->prepare("INSERT INTO color_product (product_id, color_id) VALUES (?, ?)");
            $stmt->bind_param("ii", $product_id, $color_id);
            $stmt->execute();
        }

        // Thêm các kích thước đã chọn từ form
        foreach ($size_ids as $size_id) {
            $stmt = $conn->prepare("INSERT INTO size_product (product_id, size_id) VALUES (?, ?)");
            $stmt->bind_param("ii", $product_id, $size_id);
            $stmt->execute();
        }

        // Commit transaction
        $conn->commit();

        // Sau khi cập nhật thành công, điều hướng về trang admin
        echo "<script>alert('Sản phẩm đã được cập nhật thành công!'); window.location.href = '../main/admin.php';</script>";
    } catch (Exception $e) {
        // Rollback transaction nếu có lỗi
        $conn->rollback();
        // Nếu có lỗi, quay lại trang chỉnh sửa sản phẩm
        echo "<script>alert('Cập nhật sản phẩm thất bại: {$e->getMessage()}'); window.location.href = 'edit_product.php?id={$product_id}';</script>";
    } finally {
        $conn->autocommit(TRUE); // Bật lại tự động commit
    }
} else {
    // Nếu không có yêu cầu hợp lệ, điều hướng về trang quản lý sản phẩm
    echo "<script>alert('Yêu cầu không hợp lệ!'); window.location.href = '../main/admin.php';</script>";
}
?>
