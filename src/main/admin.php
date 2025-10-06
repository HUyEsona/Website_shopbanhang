
<link rel="stylesheet" href="../fontawesome-free-6.6.0-web/fontawesome-free-6.6.0-web/css/all.min.css">
<?php
session_start();
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
// Lấy danh sách sản phẩm trong giỏ hàng của người dùng
$stmt = $conn->prepare("
SELECT c.cart_id, c.quantity, c.price, 
       p.name_product, s.size, col.color 
FROM carts c
JOIN product p ON c.product_id = p.product_id
JOIN sizes s ON c.size_id = s.size_id
JOIN colors col ON c.color_id = col.color_id
JOIN taikhoan on taikhoan.username = c.username
WHERE c.username = :username
");
// Lấy từ khóa tìm kiếm nếu có
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Truy vấn sản phẩm với thông tin size, color và brand_name
$sql = "
    SELECT p.*, b.brand_name 
    FROM product p
    LEFT JOIN brand b ON p.brand_id = b.brand_id
";

// Nếu có từ khóa tìm kiếm, thêm điều kiện WHERE
if ($search) {
    $sql .= " WHERE p.name_product LIKE :search";
}
$productsStmt = $conn->prepare($sql);

// Bind từ khóa tìm kiếm nếu có
if ($search) {
    $productsStmt->bindValue(':search', '%' . $search . '%');
}
$productsStmt->execute();
$products = $productsStmt->fetchAll(PDO::FETCH_ASSOC);

// Thêm sản phẩm
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_product'])) {
    $product_id = $_POST['product_id'];
    $name = $_POST['name_product'];
    $brand_id = $_POST['brand_id'];
    $status = $_POST['status'];
    $size_ids = $_POST['size_ids']; // Array of size IDs
    $color_ids = $_POST['color_ids']; // Array of color IDs

    // Cập nhật thông tin sản phẩm
    $stmt = $conn->prepare("UPDATE product SET name_product = :name, brand_id = :brand_id, status = :status WHERE product_id = :product_id");
    $stmt->execute([
        ':name' => $name,
        ':brand_id' => $brand_id,
        ':status' => $status,
        ':product_id' => $product_id,
    ]);

    // Cập nhật sizes và colors
    // Xóa tất cả size_product và color_product hiện tại và thêm mới
    $conn->prepare("DELETE FROM size_product WHERE product_id = :product_id")->execute([':product_id' => $product_id]);
    $conn->prepare("DELETE FROM color_product WHERE product_id = :product_id")->execute([':product_id' => $product_id]);

    // Thêm lại các size được chọn
    foreach ($size_ids as $size_id) {
        $conn->prepare("INSERT INTO size_product (product_id, size_id) VALUES (:product_id, :size_id)")
             ->execute([':product_id' => $product_id, ':size_id' => $size_id]);
    }

    // Thêm lại các color được chọn
    foreach ($color_ids as $color_id) {
        $conn->prepare("INSERT INTO color_product (product_id, color_id) VALUES (:product_id, :color_id)")
             ->execute([':product_id' => $product_id, ':color_id' => $color_id]);
    }

    // Hiển thị thông báo thành công
    $_SESSION['success'] = "Sản phẩm đã được cập nhật thành công!";
    header("Location: /main/admin.php"); // Reload the page to see the changes
    exit;
}

if (isset($_SESSION['error'])) {
    echo '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">';
    echo '<strong>Lỗi:</strong> ' . $_SESSION['error'];
    echo '</div>';
    unset($_SESSION['error']);
}

// Kiểm tra nếu có thông báo thành công từ session
if (isset($_SESSION['success'])) {
    echo '<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">';
    echo '<strong>Thành công:</strong> ' . $_SESSION['success'];
    echo '</div>';
    unset($_SESSION['success']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">

<div class="container mx-auto my-8">
<div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-center flex-grow">Admin Dashboard</h1>

        <!-- Nút đăng xuất -->
        <a href="../logout/logout.php">
            <button type="button" class="text-white  bg-red-400 hover:bg-white  hover:text-black focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 text-center transition-all duration-500">
                Đăng xuất
            </button>
        </a>
    </div>
    <!-- Notifications for New Orders -->
     <section>
     <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 mb-6" role="alert">
        <p class="font-bold">Thông báo</p>
        <?php if (!empty($newOrders)): ?>
            <p>Có đơn hàng mới đã được đặt:</p>
            <ul>
                <?php foreach ($newOrders as $order): ?>
                    <li>Mã đơn hàng: <?php echo htmlspecialchars($order['order_id']); ?> - Tổng tiền: <?php echo htmlspecialchars($order['total']); ?></li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p>Không có đơn hàng mới.</p>
        <?php endif; ?>
    </div>
     </section>
   
     <section>
    <!-- Add Product -->
    <div class="bg-white shadow-md rounded-lg p-6 mb-6">
        <h2 class="text-xl font-semibold mb-4">Thêm sản phẩm</h2>
        <form action="../product/add_product.php" method="POST" class="space-y-4" enctype="multipart/form-data">
            <!-- Product name input -->
            <input type="text" name="product_name" placeholder="Tên sản phẩm" required class="border w-full p-2 rounded">

            <!-- Product price input -->
            <input type="number" name="price" placeholder="Giá" required class="border w-full p-2 rounded">

            <!-- Color selection -->
            <label class="block font-semibold">Chọn màu sắc</label>
            <div class="space-y-2">
                <?php
                // Fetch available colors from the database
                $colorQuery = $conn->query("SELECT * FROM colors");
                $colors = $colorQuery->fetchAll(PDO::FETCH_ASSOC);
                foreach ($colors as $color): ?>
                    <div class="flex items-center">
                        <input type="checkbox" name="colors[]" value="<?php echo $color['color_id']; ?>" class="mr-2">
                        <label><?php echo htmlspecialchars($color['color']); ?></label>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Size selection -->
            <label class="block font-semibold">Chọn kích thước</label>
            <div class="space-y-2">
                <?php
                // Fetch available sizes from the database
                $sizeQuery = $conn->query("SELECT * FROM sizes");
                $sizes = $sizeQuery->fetchAll(PDO::FETCH_ASSOC);
                foreach ($sizes as $size): ?>
                    <div class="flex items-center">
                        <input type="checkbox" name="sizes[]" value="<?php echo $size['size_id']; ?>" class="mr-2">
                        <label><?php echo htmlspecialchars($size['size']); ?></label>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Product status input -->
            <input type="text" name="status" placeholder="Trạng thái" required class="border w-full p-2 rounded">

            <!-- Product image uploads -->
            <label class="block font-semibold">Tải lên hình ảnh sản phẩm</label>
            <input type="file" name="img1" accept="image/*" required class="border w-full p-2 rounded">
            <input type="file" name="img2" accept="image/*" required class="border w-full p-2 rounded">
            <input type="file" name="img3" accept="image/*" required class="border w-full p-2 rounded">

            <!-- Brand selection -->
            <label class="block font-semibold">Chọn thương hiệu</label>
            <select name="brand_id" required class="border w-full p-2 rounded">
                <?php
                // Fetch available brands from the database
                $brandQuery = $conn->query("SELECT * FROM brand");
                $brands = $brandQuery->fetchAll(PDO::FETCH_ASSOC);
                foreach ($brands as $brand): ?>
                    <option value="<?php echo $brand['brand_id']; ?>"><?php echo htmlspecialchars($brand['brand_name']); ?></option>
                <?php endforeach; ?>
            </select>

            <!-- Product description input -->
            <textarea name="description" placeholder="Mô tả sản phẩm" class="border w-full p-2 rounded"></textarea>

            <!-- Submit button -->
            <button type="submit" class="bg-blue-500 text-white py-2 px-4 rounded">Thêm sản phẩm</button>
        </form>
    </div>
</section>

     



    <!-- Search Product -->
    <div class="mb-6">
        <form action="" method="GET">
            <input type="text" name="search" placeholder="Tìm kiếm sản phẩm..." class="border p-2 rounded-md">
            <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded-md">Tìm kiếm</button>
        </form>
    </div>



    <!-- Edit Product -->
    <div class="bg-white shadow-md rounded-lg p-6 mb-6">
    <h2 class="text-xl font-semibold mb-4">Chỉnh sửa sản phẩm</h2>
    <form action="../product/update_product.php" method="POST" enctype="multipart/form-data">
        <?php foreach ($products as $product): ?>
            <div class="border-b border-gray-300 pb-4 mb-4">
                <h3 class="font-semibold mb-2">Sản phẩm: <?php echo htmlspecialchars($product['name_product']); ?></h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Các trường nhập thông tin sản phẩm -->
                    <div>
                        <label class="block mb-1">Tên sản phẩm:</label>
                        <input type="text" name="name_product[<?php echo $product['product_id']; ?>]" value="<?php echo htmlspecialchars($product['name_product']); ?>" class="border px-2 py-1 rounded w-full">
                    </div>
                    <div>
                        <label class="block mb-1">Thương hiệu:</label>
                        <input type="text" name="brand_name[<?php echo $product['product_id']; ?>]" value="<?php echo htmlspecialchars($product['brand_name']); ?>" class="border px-2 py-1 rounded w-full">
                    </div>
                    <div>
                        <label class="block mb-1">Giá tiền:</label>
                        <input type="text" name="price[<?php echo $product['product_id']; ?>]" value="<?php echo htmlspecialchars($product['price']); ?>" class="border px-2 py-1 rounded w-full">
                    </div>
                    <div>
                        <label class="block mb-1">Trạng thái:</label>
                        <select name="status[<?php echo $product['product_id']; ?>]" class="border px-2 py-1 rounded w-full">
                            <option value="Còn hàng" <?php echo $product['status'] == 'Còn hàng' ? 'selected' : ''; ?>>Còn hàng</option>
                            <option value="Hết hàng" <?php echo $product['status'] == 'Hết hàng' ? 'selected' : ''; ?>>Hết hàng</option>
                        </select>
                    </div>
                    
                    <!-- Màu sắc -->
                    <div>
                        <label class="block mb-1">Màu sắc:</label>
                        <div class="border px-2 py-1 rounded w-full">
                            <?php 
                            $colors = $conn->prepare("SELECT c.color, c.color_id FROM color_product cp JOIN colors c ON cp.color_id = c.color_id WHERE cp.product_id = :product_id");
                            $colors->execute([':product_id' => $product['product_id']]);
                            $colorList = $colors->fetchAll(PDO::FETCH_ASSOC);
                            foreach ($colorList as $color): ?>
                                <span class="inline-block bg-gray-200 rounded-full px-3 py-1 text-sm font-semibold text-gray-700 mr-2">
                                    <?php echo htmlspecialchars($color['color']); ?>
                                    <button type="button" onclick="removeColor(<?php echo $product['product_id']; ?>, '<?php echo htmlspecialchars($color['color']); ?>')" class="text-red-500 ml-1">x</button>
                                </span>
                            <?php endforeach; ?>
                        </div>
                        <input type="text" id="new_color_<?php echo $product['product_id']; ?>" placeholder="Thêm màu mới" class="border px-2 py-1 rounded w-full mt-2">
         <!-- Nút thêm màu mới -->
    <button type="button" onclick="addColor(<?php echo $product['product_id']; ?>)" class="bg-green-500 text-white px-4 py-2 rounded mt-2">Thêm màu</button>

                    </div>

                    <!-- Kích thước -->
                    <div>
                        <label class="block mb-1">Kích thước:</label>
                        <div class="border px-2 py-1 rounded w-full">
                            <?php 
                            $sizes = $conn->prepare("SELECT s.size, s.size_id FROM size_product sp JOIN sizes s ON sp.size_id = s.size_id WHERE sp.product_id = :product_id");
                            $sizes->execute([':product_id' => $product['product_id']]);
                            $sizeList = $sizes->fetchAll(PDO::FETCH_ASSOC);
                            foreach ($sizeList as $size): ?>
                                <span class="inline-block bg-gray-200 rounded-full px-3 py-1 text-sm font-semibold text-gray-700 mr-2">
                                    <?php echo htmlspecialchars($size['size']); ?>
                                    <button type="button" onclick="removeSize(<?php echo $product['product_id']; ?>, <?php echo $size['size_id']; ?>)" class="text-red-500 ml-1">x</button>
                                </span>
                            <?php endforeach; ?>
                        </div>
<!-- Ô nhập thêm kích thước mới -->
<input type="text" id="new_size_<?php echo $product['product_id']; ?>" placeholder="Thêm kích thước mới" class="border px-2 py-1 rounded w-full mt-2">

<!-- Nút thêm kích thước mới -->
<button type="button" onclick="addSize(<?php echo $product['product_id']; ?>)" class="bg-green-500 text-white px-4 py-2 rounded mt-2">Thêm kích thước</button>
                </div>
                    
                    <div>
                        <label class="block mb-1">Mô tả:</label>
                        <textarea name="description[<?php echo $product['product_id']; ?>]" class="border px-2 py-1 rounded w-full" rows="3"><?php echo htmlspecialchars($product['description']); ?></textarea>
                    </div>
                    
                    <!-- Hình ảnh -->
                    <?php for ($i = 1; $i <= 3; $i++): ?>
                        <div>
                            <label class="block mb-1">Hình ảnh <?php echo $i; ?>:</label>
                            <input type="text" name="img<?php echo $i; ?>[<?php echo $product['product_id']; ?>]" value="<?php echo htmlspecialchars($product["img$i"]); ?>" class="border px-2 py-1 rounded w-full">
                            <input type="file" name="image<?php echo $i; ?>[<?php echo $product['product_id']; ?>]" class="border px-2 py-1 rounded w-full mt-1">
                        </div>
                    <?php endfor; ?>
                </div>

                <input type="hidden" name="product_id[]" value="<?php echo htmlspecialchars($product['product_id']); ?>">
                
                <!-- Nút hành động cho mỗi sản phẩm -->
                <div class="flex mt-4">
                <button type="submit" name="update_product[<?php echo $product['product_id']; ?>]" class="bg-blue-500 text-white px-4 py-2 rounded-md mr-2">Cập nhật</button>
                <button type="submit" name="delete_product" formaction="../product/delete_product.php" class="bg-red-500 text-white px-4 py-2 rounded-md">Xóa</button>
                <input type="hidden" name="product_id" value="<?php echo htmlspecialchars($product['product_id']); ?>">                </div>
            </div>
        <?php endforeach; ?>
    </form>
</div>
<script>
    // Hàm loại bỏ màu sắc
    function addColor(productId) {
    const newColorInput = document.getElementById('new_color_' + productId);
    const newColor = newColorInput.value.trim();

    if (newColor === "") {
        alert("Vui lòng nhập màu mới.");
        return;
    }

    const formData = new FormData();
    formData.append('action', 'add_color');
    formData.append('product_id', productId);
    formData.append('color_name', newColor);

    fetch('../product/remove_color_size.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(data => {
        alert(data); // Hiển thị thông báo
        location.reload(); // Tải lại trang để cập nhật danh sách màu
    })
    .catch(error => {
        console.error('Error:', error);
        alert("Có lỗi xảy ra khi thêm màu.");
    });
}


    function removeColor(productId, colorName) {
    if (confirm("Bạn có chắc chắn muốn xóa màu này?")) {
        const formData = new FormData();
        formData.append('action', 'remove_color');
        formData.append('product_id', productId);
        formData.append('color_name', colorName);

        fetch('../product/remove_color_size.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(data => {
            alert(data); // Thông báo kết quả
            location.reload(); // Tải lại trang để cập nhật danh sách màu
        })
        .catch(error => {
            console.error('Error:', error);
            alert("Có lỗi xảy ra khi xóa màu.");
        });
    }
}
function addSize(productId) {
    const newSizeInput = document.getElementById('new_size_' + productId);
    const newSize = newSizeInput.value.trim();

    if (newSize === "") {
        alert("Vui lòng nhập kích thước mới.");
        return;
    }

    const formData = new FormData();
    formData.append('action', 'add_size');
    formData.append('product_id', productId);
    formData.append('size_name', newSize);

    fetch('../product/remove_color_size.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(data => {
        alert(data); // Hiển thị thông báo
        location.reload(); // Tải lại trang để cập nhật danh sách kích thước
    })
    .catch(error => {
        console.error('Error:', error);
        alert("Có lỗi xảy ra khi thêm kích thước.");
    });
}


    // Hàm loại bỏ kích thước
    function removeSize(productId, sizeId) {
        if (confirm("Bạn có chắc muốn xóa kích thước này không?")) {
            fetch('../product/remove_color_size.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: `action=remove_size&product_id=${productId}&size_id=${sizeId}`
            })
            .then(response => response.text())
            .then(data => {
                alert(data);
                location.reload();  // Tải lại trang sau khi xóa thành công
            })
            .catch(error => console.error('Error:', error));
        }
    }
</script>


<!-- Thêm voucher -->
<div class="bg-white p-10 rounded-lg shadow-xl w-full max-w-lg mb-10">
        <h2 class="text-3xl font-semibold mb-8 text-center">Thêm Voucher Mới</h2>
        <form action="../voucher/add_voucher.php" method="POST">
            <!-- Mã Voucher -->
            <div class="mb-6">
                <label for="vourcher_code" class="block text-gray-700 font-medium mb-2">Mã Voucher</label>
                <input type="text" id="vourcher_code" name="vourcher_code" required
                       class="border border-gray-300 px-4 py-3 rounded-lg w-full">
            </div>

            <!-- Ngày Bắt Đầu -->
            <div class="mb-6">
                <label for="day_start" class="block text-gray-700 font-medium mb-2">Ngày Bắt Đầu</label>
                <input type="date" id="day_start" name="day_start" required
                       class="border border-gray-300 px-4 py-3 rounded-lg w-full">
            </div>

            <!-- Ngày Kết Thúc -->
            <div class="mb-6">
                <label for="day_end" class="block text-gray-700 font-medium mb-2">Ngày Kết Thúc</label>
                <input type="date" id="day_end" name="day_end" required
                       class="border border-gray-300 px-4 py-3 rounded-lg w-full">
            </div>

            <!-- Giá trị tối thiểu -->
            <div class="mb-6">
                <label for="price_min" class="block text-gray-700 font-medium mb-2">Giá trị đơn hàng tối thiểu</label>
                <input type="number" step="0.01" id="price_min" name="price_min" required
                       class="border border-gray-300 px-4 py-3 rounded-lg w-full">
            </div>

            <!-- Giá trị Voucher -->
            <div class="mb-6">
                <label for="price" class="block text-gray-700 font-medium mb-2">Giá trị giảm giá</label>
                <input type="number" step="0.01" id="price" name="price" required
                       class="border border-gray-300 px-4 py-3 rounded-lg w-full">
            </div>

            <!-- Trạng Thái -->
            <div class="mb-6">
                <label for="status" class="block text-gray-700 font-medium mb-2">Trạng thái</label>
                <select id="status" name="status" class="border border-gray-300 px-4 py-3 rounded-lg w-full">
                    <option value="1" selected>Hoạt động</option>
                    <option value="0">Không hoạt động</option>
                </select>
            </div>

            <!-- Nút Thêm Voucher -->
            <div class="flex justify-center mt-8">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-8 py-3 rounded-lg text-lg">
                    Thêm Voucher
                </button>
            </div>
        </form>
    </div>




<?php
// Kết nối đến cơ sở dữ liệu
try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Kết nối thất bại: " . $e->getMessage());
}

// Lấy dữ liệu đơn hàng từ bảng orders
$sql = 'SELECT o.order_id, d.name, o.total, o.status, o.quantity, o.date_create
            FROM orders o 
            JOIN dangky d ON o.username = d.username';
$statement = $pdo->prepare($sql);
$statement->execute();

// Lưu kết quả vào biến $orders
$orders = $statement->fetchAll(PDO::FETCH_ASSOC);
?>

<!-- Quản lý Đơn hàng -->
<div class="bg-white shadow-md rounded-lg p-6 mb-6">
    <h2 class="text-xl font-semibold mb-4">Quản lý đơn hàng</h2>
    <table class="min-w-full border border-gray-300">
        <thead>
            <tr class="bg-gray-100">
                <th class="border px-4 py-2">Mã đơn hàng</th>
                <th class="border px-4 py-2">Người dùng</th>
                <th class="border px-4 py-2">Giá niêm yết</th>
                <th class="border px-4 py-2">Trạng thái</th>
                <th class="border px-4 py-2">Hành động</th>
                <th class="border px-4 py-2">Ngày đặt</th>
                <th class="border px-4 py-2">Số lượng</th>
                <th class="border px-4 py-2">Tổng tiền</th>
                <th class="border px-4 py-2">Xuất hóa đơn</th>
            </tr>
        </thead>
        <tbody>
    

    <?php if (!empty($orders)): ?>
        <?php foreach ($orders as $order): ?>
            <tr>
                <td class="border px-4 py-2"><?php echo htmlspecialchars($order['order_id']); ?>
            <!-- Nút Xóa -->
                    <form action="../orders/delete_order.php" method="POST" class="inline-block ml-2">
                    <input type="hidden" name="order_id" value="<?php echo htmlspecialchars($order['order_id']); ?>">
                    <button type="submit" class="bg-red-500 text-white px-2 py-1 rounded-md" onclick="return confirm('Bạn có chắc chắn muốn xóa đơn hàng này?');">Xóa</button>
                </form>
                </td>
                <td class="border px-4 py-2"><?php echo htmlspecialchars($order['name']); ?></td>
                <td class="border px-4 py-2"><?php echo htmlspecialchars($order['total']); ?> VND</td>
                <td class="border px-4 py-2"><?php echo htmlspecialchars($order['status']); ?></td>
                <td class="border px-4 py-2">
                    <form action="../orders/update_order.php" method="POST" class="inline">
                        <input type="hidden" name="order_id" value="<?php echo htmlspecialchars($order['order_id']); ?>">
                        <select name="status" class="border p-1 rounded-md">
                            <option value="Đang chờ" <?php if ($order['status'] == 'Đang chờ') echo 'selected'; ?>>Đang chờ</option>
                            <option value="Đã gửi" <?php if ($order['status'] == 'Đã gửi') echo 'selected'; ?>>Đã gửi</option>
                            <option value="Đang vận chuyển" <?php if ($order['status'] == 'Đang vận chuyển') echo 'selected'; ?>>Đang vận chuyển</option>
                            <option value="Đã giao" <?php if ($order['status'] == 'Đã giao') echo 'selected'; ?>>Đã giao</option>
                        </select>
                        <button type="submit" class="bg-blue-500 text-white px-2 py-1 rounded-md" onclick="return confirm('Đã cập nhật thành công');">Cập nhật</button>
                    </form>
                </td>
                <td class="border px-4 py-2"><?php echo htmlspecialchars($order['date_create']); ?></td>
                <td class="border px-4 py-2 text-center"><?php echo htmlspecialchars($order['quantity']); ?></td>
                <td class="border px-4 py-2 text-right">
                    <?php 
                        // Tính tổng tiền (quantity * total)
                        $total_money = $order['quantity'] * $order['total'];
                        echo htmlspecialchars(number_format($total_money, 2)); // Định dạng số tiền
                    ?> VND
                </td>
                <td class="border px-4 py-2 text-center align-middle">
                    <!-- Nút In hóa đơn -->
                    <form action="export_orders.php" method="GET" target="_blank" class="inline">
                        <input type="hidden" name="order_id" value="<?php echo htmlspecialchars($order['order_id']); ?>">
                        <button type="submit" class="inline-flex items-center text-black border border-black text-xl px-3 py-1 rounded-md hover:bg-black hover:text-white transition duration-200">
                            <span>In</span>
                            <i class="fa-solid fa-print ml-2"></i>
                        </button>
                    </form>
                </td>





            </form>
        </td>
            </tr>
        <?php endforeach; ?>
    <?php else: ?>
        <tr>
            <td colspan="6" class="border px-4 py-2 text-center">Không có đơn hàng nào.</td>
        </tr>
    <?php endif; ?>
</tbody>
</table>

    <!-- Nút xuất ra Excel -->
        <form action="export_orders.php" method="POST" class="mt-10 flex justify-end">
        <button type="submit" class="bg-green-500 text-white text-lg uppercase font-bold px-6 py-3 rounded-md hover:bg-green-600 hover:text-black transition duration-200">
            Xuất ra Excel
        </button>
    </form>

</div>




</div>

</body>
</html>
