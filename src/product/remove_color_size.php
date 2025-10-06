<?php
$host = "localhost";
$database = "cuoiky";
$username = "root";
$password = "";

try {
    // Create a new PDO connection
    $conn = new PDO("mysql:host=$host;dbname=$database", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Connected successfully";
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $productId = $_POST['product_id'];
    if ($_POST['action'] == 'add_color') {
        $colorName = trim($_POST['color_name']);

        // Kiểm tra xem màu có tồn tại trong bảng `colors` chưa
        $stmt = $conn->prepare("SELECT color_id FROM colors WHERE LOWER(color) = LOWER(:color_name)");
        $stmt->execute([':color_name' => $colorName]);
        $color = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($color) {
            $colorId = $color['color_id'];
        } else {
            // Nếu màu chưa tồn tại, thêm màu mới vào bảng `colors`
            $insertColorStmt = $conn->prepare("INSERT INTO colors (color) VALUES (:color_name)");
            $insertColorStmt->execute([':color_name' => $colorName]);
            $colorId = $conn->lastInsertId();
        }

        // Thêm bản ghi vào bảng `color_product`
        $insertColorProductStmt = $conn->prepare("INSERT INTO color_product (product_id, color_id) VALUES (:product_id, :color_id)");
        $insertColorProductStmt->execute([':product_id' => $productId, ':color_id' => $colorId]);

        echo "Màu đã được thêm vào sản phẩm.";
    }
    elseif ($_POST['action'] == 'add_size') {
        $productId = $_POST['product_id'];
        $sizeName = strtolower(trim($_POST['size_name']));  // Lấy kích thước từ form (chú ý sử dụng `size_name` thay vì `size_id`)
    
        // Bước 1: Kiểm tra xem kích thước đã tồn tại trong bảng sizes chưa
        $stmt = $conn->prepare("SELECT size_id FROM sizes WHERE LOWER(size) = :size_name LIMIT 1");
        $stmt->execute([':size_name' => $sizeName]);
        $size = $stmt->fetch(PDO::FETCH_ASSOC);
    
        if ($size) {
            // Kích thước đã tồn tại, lấy size_id
            $sizeId = $size['size_id'];
    
            // Bước 2: Kiểm tra xem kích thước đã tồn tại trong bảng size_product chưa
            $checkStmt = $conn->prepare("SELECT 1 FROM size_product WHERE product_id = :product_id AND size_id = :size_id LIMIT 1");
            $checkStmt->execute([':product_id' => $productId, ':size_id' => $sizeId]);
            
            if ($checkStmt->fetch(PDO::FETCH_ASSOC)) {
                echo 'Kích thước này đã được thêm cho sản phẩm.';
            } else {
                // Bước 3: Thêm kích thước vào bảng size_product
                $insertStmt = $conn->prepare("INSERT INTO size_product (product_id, size_id) VALUES (:product_id, :size_id)");
                $insertStmt->execute([':product_id' => $productId, ':size_id' => $sizeId]);
    
                echo 'Thêm kích thước thành công!';
            }
        } else {
            // Kích thước không tồn tại trong bảng sizes
            echo 'Kích thước không hợp lệ hoặc không tồn tại.';
        }
    }
    elseif ($_POST['action'] == 'remove_color') {
        $colorName = trim($_POST['color_name']); // Lấy tên màu từ form (vd: "White")

        // Bước 1: Lấy `color_id` từ bảng `colors` dựa trên `color_name`
        $stmt = $conn->prepare("SELECT color_id FROM colors WHERE LOWER(color) = LOWER(:color_name) LIMIT 1");
        $stmt->execute([':color_name' => $colorName]);
        $color = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($color) {
            $colorId = $color['color_id'];

            // Bước 2: Xóa màu khỏi bảng `color_product` dựa trên `product_id` và `color_id`
            $deleteStmt = $conn->prepare("DELETE FROM color_product WHERE product_id = :product_id AND color_id = :color_id");
            $deleteStmt->execute([':product_id' => $productId, ':color_id' => $colorId]);

            echo "Màu đã được xóa.";
        } else {
            echo "Màu không tồn tại hoặc không tìm thấy.";
        }
    }
    // Removing size
    elseif ($_POST['action'] == 'remove_size') {
        $sizeName = strtolower(trim($_POST['size_id'])); // Assume size_name is passed from the form

        // Step 1: Retrieve size_id from sizes table
        $stmt = $conn->prepare("SELECT size_id FROM sizes WHERE LOWER(size) = :size_id LIMIT 1");
        $stmt->execute([':size_id' => $sizeName]);
        $size = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($size) {
            $sizeId = $size['size_id'];

            // Step 2: Delete the size from size_product table
            $deleteStmt = $conn->prepare("DELETE FROM size_product WHERE product_id = :product_id AND size_id = :size_id");
            $deleteStmt->execute([':product_id' => $productId, ':size_id' => $sizeId]);

            echo "Kích thước đã được xóa.";
        } else {
            echo "Kích thước không tồn tại hoặc không tìm thấy.";
        }
    }
}
?>
