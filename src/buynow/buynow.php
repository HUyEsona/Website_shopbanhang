<?php
session_start();
try {
    $conn = new PDO("mysql:host=localhost;dbname=cuoiky", "root", password: "");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
$is_logged_in = isset($_SESSION['name']); // Kiểm tra name trong session
$name_logged_in = $is_logged_in ? $_SESSION['name'] : ''; // Lấy name nếu đã đăng nhập
// Khởi tạo các biến
$product = [];
$size = $color = $quantity = "";

// Kiểm tra phương thức GET
if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['product_id'])) {
    $product_id = (int)$_GET['product_id']; // Chuyển đổi sang số nguyên

    // Lấy thông tin sản phẩm từ cơ sở dữ liệu
    $stmt = $conn->prepare("SELECT name_product, price, img1 FROM product WHERE product_id = :product_id");
    $stmt->execute(['product_id' => $product_id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    // Lấy size, color, quantity từ query string
    $size = isset($_GET['size']) ? htmlspecialchars($_GET['size']) : 'Chưa chọn kích thước';
    $color = isset($_GET['color']) ? htmlspecialchars($_GET['color']) : 'Chưa chọn màu sắc';
    $quantity = isset($_GET['quantity']) ? (int)$_GET['quantity'] : 0;
}

// Kiểm tra phương thức POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Lấy dữ liệu từ biểu mẫu
    $name = htmlspecialchars(trim($_POST['name']));
    $email = htmlspecialchars(trim($_POST['email']));
    $address = htmlspecialchars(trim($_POST['address']));
    $phone = htmlspecialchars(trim($_POST['phone']));
    $birthday = htmlspecialchars(trim($_POST['birthday']));
    $voucher_code = htmlspecialchars(trim($_POST['voucher_code']));
    $note = htmlspecialchars(trim($_POST['note']));
    $payment_method = htmlspecialchars(trim($_POST['payment']));
    $price = (float) $_POST['price']; // Giá từ biểu mẫu
    $product_id = (int) $_POST['product_id'];
    $size = htmlspecialchars($_POST['size']);
    $color = htmlspecialchars($_POST['color']);
    $quantity = (int) $_POST['quantity'];
    $total = $quantity * $price; // Tính tổng tiền

    // Xử lý voucher (nếu có)
    $discount = 0;
    if (!empty($voucher_code)) {
        $stmt_voucher = $conn->prepare("SELECT price FROM vourcher WHERE vourcher_code = ? AND status = 1 AND ? BETWEEN day_start AND day_end AND ? >= price_min");
        $stmt_voucher->execute([$voucher_code, date('Y-m-d'), $total]);
        $voucher = $stmt_voucher->fetch();
        if ($voucher) {
            $discount = $voucher['price'];
        }
    }
    $final_total = $total - $discount;

    // Kiểm tra phương thức thanh toán
    if ($payment_method === 'Thanh toán momo') {
        // Lưu thông tin đơn hàng vào session để sử dụng sau khi thanh toán
        $_SESSION['order_data'] = [
            'username' => $name,
            'total' => $final_total,
            'note' => $note,
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'voucher_code' => $voucher_code,
            'payment' => $payment_method,
            'address' => $address,
            'size' => $size,
            'color' => $color,
            'product_name' => $product['name_product'],
            'product_id' => $product_id,
            'quantity' => $quantity,
            'price' => $price
        ];

        // Chuyển hướng đến xử lý MoMo
        header('Location: momo.php');
        exit();
    } else {
        // Thanh toán khi giao hàng - lưu đơn hàng ngay
        $sql = "INSERT INTO orders (username, date_create, total, note, status, name, email, phone, voucher_code, payment, address, size, color)
                VALUES (?, NOW(), ?, ?, 'Đã đặt hàng', ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        
        if ($stmt->execute([$name, $final_total, $note, $name, $email, $phone, $voucher_code, $payment_method, $address, $size, $color])) {
            echo "<script>alert('Đặt hàng thành công!'); window.location.href='../index.php';</script>";
        } else {
            echo "<script>alert('Lỗi khi đặt hàng!');</script>";
        }
    }
}

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="./output.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/flowbite@2.5.1/dist/flowbite.min.css" rel="stylesheet">
    <title>Thông tin đặt hàng</title>
</head>
<body>

<header>
    <nav id="header" class="dark:bg-gray-900 w-full z-50 top-0 left-0 bg-red-400 fixed transition-all duration-500">
        <div class="max-w-screen-xl flex flex-wrap items-center justify-between mx-auto p-4">
            <a href="#" class="flex items-center space-x-3 rtl:space-x-reverse">
                <span class="self-center text-4xl font-semibold whitespace-nowrap text-white">Bán hàng</span>
            </a>

            <div class="flex md:order-2 space-x-3 rtl:space-x-reverse justify-between items-center">
                <?php if ($is_logged_in): ?>
                    <!-- Hiển thị tên người dùng và nút đăng xuất khi đã đăng nhập -->
                    <a href="../myprofile/profile.php" class="text-white">Xin chào, <?php echo htmlspecialchars($name_logged_in); ?>!</a>
                    <a href="../logout/logout.php">
                        <button type="button" class="text-red-400 bg-white hover:bg-red-400 hover:text-white focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 text-center transition-all duration-500">Đăng xuất</button>
                    </a>
                <?php else: ?>
                    <!-- Hiển thị nút đăng nhập và đăng ký khi chưa đăng nhập -->
                    <a href="../login/sign_in.php">
                        <button type="button" class="text-red-400 bg-white hover:bg-red-400 hover:text-white focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 text-center transition-all duration-500">Đăng nhập</button>
                    </a>
                    <a href="../register/register.php">
                        <button type="button" class="text-red-400 bg-white hover:bg-red-400 hover:text-white focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 text-center transition-all duration-500">Đăng ký</button>
                    </a>
                <?php endif; ?>
            </div>

            <div class="items-center justify-between hidden w-full md:flex md:w-auto md:order-1" id="navbar-sticky">
                <ul class="flex flex-col p-4 md:p-0 mt-4 font-medium md:space-x-8 rtl:space-x-reverse md:flex-row md:mt-0 md:border-0">
                    <li><a href="../index.php" class="block py-2 px-3 text-white rounded md:bg-transparent md:text-white md:p-0 md:dark:text-white hover:text-red-100" aria-current="Home">Trang chủ</a></li>
                    <li><a href="#" class="block py-2 px-3 text-white rounded md:hover:bg-transparent md:hover:text-red-100 md:p-0 dark:text-white dark:hover:text-white md:dark:hover:bg-transparent">Chính sách</a></li>
                    <li><a href="#" class="block py-2 px-3 text-white rounded md:hover:bg-transparent md:p-0 dark:text-white dark:hover:text-white md:dark:hover:bg-transparent">Đối tác</a></li>
                    <li><a href="#" class="block py-2 px-3 text-white rounded md:hover:bg-transparent md:p-0 dark:text-white dark:hover:text-white md:dark:hover:bg-transparent">Hàng mới về</a></li>
                </ul>
            </div>

            <div class="flex md:hidden ml-right">
                <button id="menu-toggle" type="button" class="text-white focus:outline-none">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7" />
                    </svg>
                </button>
            </div>
        </div>

        <!-- Menu di động -->
        <div class="md:hidden hidden" id="mobile-menu">
            <ul class="flex flex-col p-4 space-y-2">
                <li><a href="#" class="block py-2 px-3 text-white rounded hover:bg-gray-700">Trang chủ</a></li>
                <li><a href="#" class="block py-2 px-3 text-white rounded hover:bg-gray-700">Hàng mới nhất</a></li>
                <li><a href="#" class="block py-2 px-3 text-white rounded hover:bg-gray-700">Đồ nam</a></li>
                <li><a href="#" class="block py-2 px-3 text-white rounded hover:bg-gray-700">Đồ nữ</a></li>
                <li><a href="#" class="block py-2 px-3 text-white rounded hover:bg-gray-700">Đồ trẻ em</a></li>
            </ul>
        </div>
    </nav>
</header>

<section class="bg-gray-50 dark:bg-gray-900 mt-10">
    <div class="py-8 px-4 mx-auto max-w-screen-xl lg:py-16 grid lg:grid-cols-2 gap-8 lg:gap-16">
        
        <!-- Cột trái: Thông tin sản phẩm -->
        <?php
        // Kiểm tra xem các biến đã được khởi tạo chưa
        $productName = isset($product['name_product']) ? htmlspecialchars($product['name_product']) : 'Không có tên sản phẩm';
        $productPrice = isset($product['price']) ? number_format($product['price']) . ' VND' : '0 VND';
        $productImg = isset($product['img1']) ? htmlspecialchars($product['img1']) : 'default-image.jpg'; // Hình ảnh mặc định

        $size = isset($size) ? htmlspecialchars($size) : 'Chưa chọn kích thước';
        $color = isset($color) ? htmlspecialchars($color) : 'Chưa chọn màu sắc';
        $quantity = isset($quantity) ? htmlspecialchars($quantity) : '0';

        // Tính tổng tiền nếu đã có giá và số lượng
        $total = isset($product['price']) && isset($quantity) ? number_format($quantity * $product['price']) . ' VND' : '0 VND';
        $total_price = isset($quantity) && isset($product['price']) ? $quantity * $product['price'] : 0; // Giá trị tổng tiền
        ?>

        <div class="flex flex-col justify-center">
            <h1 class="mb-4 text-2xl font-bold tracking-tight leading-none text-gray-900 md:text-2xl lg:text-2xl dark:text-white">Thông tin sản phẩm</h1>
            <div class="bg-white p-6 rounded-lg shadow-xl dark:bg-gray-800">
                <div class="mb-4">
                    <p class="text-lg font-bold text-gray-900 dark:text-white">Tên sản phẩm: </p>
                    <p class="text-gray-700 dark:text-gray-400"><?php echo $productName; ?></p>
                </div>
                <div class="mb-4">
                    <p class="text-lg font-bold text-gray-900 dark:text-white">Giá: </p>
                    <p class="text-gray-700 dark:text-gray-400"><?php echo $productPrice; ?></p>
                </div>
                <div class="mb-4">
                    <p class="text-lg font-bold text-gray-900 dark:text-white">Kích thước: </p>
                    <p class="text-gray-700 dark:text-gray-400"><?php echo $size; ?></p>
                </div>
                <div class="mb-4">
                    <p class="text-lg font-bold text-gray-900 dark:text-white">Màu sắc: </p>
                    <p class="text-gray-700 dark:text-gray-400"><?php echo $color; ?></p>
                </div>
                <div class="mb-4">
                    <p class="text-lg font-bold text-gray-900 dark:text-white">Số lượng: </p>
                    <p class="text-gray-700 dark:text-gray-400"><?php echo $quantity; ?></p>
                </div>
                <div class="mb-4">
                    <p class="text-lg font-bold text-gray-900 dark:text-white">Tổng tiền: </p>
                    <p class="text-gray-700 dark:text-gray-400"><?php echo $total; ?></p>
                </div>
                <div class="mb-4">
                    <p class="text-lg font-bold text-gray-900 dark:text-white">Hình ảnh </p>
                    <img src="./../../img/<?php echo $productImg; ?>" alt="<?php echo $productName; ?>" class="w-full h-auto rounded-lg">
                </div>
            </div>
        </div>

        <!-- Cột phải: Thông tin khách hàng -->
        <div>
            <div class="w-full lg:max-w-xl p-6 space-y-8 sm:p-8 bg-white rounded-lg shadow-xl dark:bg-gray-800">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Thông tin khách hàng</h2>
                <form class="space-y-4" method="POST" action="">
                    <input type="hidden" name="product_id" value="<?php echo isset($product_id) ? $product_id : ''; ?>">
                    <input type="hidden" name="size" value="<?php echo $size; ?>">
                    <input type="hidden" name="color" value="<?php echo $color; ?>">
                    <input type="hidden" name="quantity" value="<?php echo $quantity; ?>">
                    <input type="hidden" name="price" value="<?php echo isset($product['price']) ? $product['price'] : 0; ?>">

                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-900 dark:text-white">Tên của bạn</label>
                        <input type="text" id="name" name="name" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white" placeholder="Tên của bạn" required />
                    </div>
                    
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-900 dark:text-white">Email</label>
                        <input type="email" id="email" name="email" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white" placeholder="Email" required />
                    </div>
                    
                    <div>
                        <label for="address" class="block text-sm font-medium text-gray-900 dark:text-white">Địa chỉ</label>
                        <input type="text" id="address" name="address" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white" placeholder="Địa chỉ của bạn" required />
                    </div>
                    
                    <div>
                        <label for="phone" class="block text-sm font-medium text-gray-900 dark:text-white">Số điện thoại</label>
                        <input type="tel" id="phone" name="phone" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white" placeholder="Số điện thoại của bạn" required />
                    </div>

                    <div>
                        <label for="note" class="block text-sm font-medium text-gray-900 dark:text-white">Ghi chú</label>
                        <textarea id="note" name="note" rows="4" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white" placeholder="Ghi chú của bạn"></textarea>
                    </div>

                    <div>
                        <label for="payment" class="block text-sm font-medium text-gray-900 dark:text-white">Phương thức thanh toán</label>
                        <select id="payment" name="payment" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            <option value="Trả tiền khi giao hàng">Trả tiền khi giao hàng</option>
                            <option value="Thanh toán momo">Thanh toán MoMo</option>
                        </select>
                    </div>

                    <div>
                        <label for="birthday" class="block text-sm font-medium text-gray-900 dark:text-white">Ngày sinh</label>
                        <input type="date" id="birthday" name="birthday" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white" required />
                    </div>
                    
                    <div>
                        <label for="voucher_code" class="block text-sm font-medium text-gray-900 dark:text-white">Mã voucher</label>
                        <select id="voucher_code" name="voucher_code" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            <option value="">Chọn mã voucher</option>
                            <?php
                            // Lấy ngày hiện tại
                            $currentDate = date("Y-m-d");

                            // Truy vấn để lấy danh sách mã voucher
                            $sql_voucher = "SELECT vourcher_id, vourcher_code, day_start, day_end, price_min, price FROM vourcher WHERE status = 1";
                            $stmt_voucher = $conn->prepare($sql_voucher);
                            $stmt_voucher->execute();
                            $vouchers = $stmt_voucher->fetchAll(PDO::FETCH_ASSOC);
                            
                            // Hiển thị các voucher trong dropdown
                            foreach ($vouchers as $voucher) {
                                // So sánh ngày hiện tại với ngày bắt đầu và ngày kết thúc
                                if ($currentDate >= $voucher['day_start'] && $currentDate <= $voucher['day_end']) {
                                    // Kiểm tra xem tổng tiền có đủ điều kiện sử dụng voucher không
                                    if ($total_price >= $voucher['price_min']) {
                                        echo '<option value="' . htmlspecialchars($voucher['vourcher_code']) . '">' . htmlspecialchars($voucher['vourcher_code']) . ' (Giảm ' . number_format($voucher['price']) . ' VND)</option>';
                                    }
                                }
                            }
                            ?>
                        </select>
                    </div>

                    <div class="btn_buy" style="margin-top: 40px;">
                        <button type="submit" name="submit" class="w-full px-4 py-2 text-white bg-red-600 rounded-lg hover:bg-red-700 focus:outline-none focus:ring focus:ring-blue-300 dark:focus:ring-blue-800">
                            Đặt hàng ngay
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>

<footer class="bg-red-200 dark:bg-gray-900 mt-40">
    <div class="mx-auto w-full max-w-screen-xl">
      <div class="grid grid-cols-2 gap-8 px-4 py-6 lg:py-8 md:grid-cols-4">
        <div>
            <h2 class="mb-6 text-sm font-semibold text-red-700 uppercase dark:text-white">Về chúng tôi</h2>
            <ul class="text-red-700 dark:text-gray-700 font-medium">
                <li class="mb-4">
                    <a href="#" class=" hover:underline">About</a>
                </li>
                <li class="mb-4">
                    <a href="#" class="hover:underline">Careers</a>
                </li>
                <li class="mb-4">
                    <a href="#" class="hover:underline">Brand Center</a>
                </li>
                <li class="mb-4">
                    <a href="#" class="hover:underline">Blog</a>
                </li>
            </ul>
        </div>
        <div>
            <h2 class="mb-6 text-sm font-semibold text-red-700 uppercase dark:text-white">Thông tin</h2>
            <ul class="text-red-700 dark:text-gray-400 font-medium">
                <li class="mb-4">
                    <a href="#" class="hover:underline">Trạng thái đơn hàng</a>
                </li>
                <li class="mb-4">
                    <a href="#" class="hover:underline">Chính sách đổi trả</a>
                </li>
                <li class="mb-4">
                    <a href="#" class="hover:underline">Hình thức thanh toán</a>
                </li>
                <li class="mb-4">
                    <a href="#" class="hover:underline">Chính sách khách hàng thân thiết</a>
                </li>
            </ul>
        </div>
        <div>
            <h2 class="mb-6 text-sm font-semibold text-red-700 uppercase dark:text-white">Trợ giúp</h2>
            <ul class="text-red-700 dark:text-gray-400 font-medium">
                <li class="mb-4">
                    <a href="#" class="hover:underline">Tuyển dụng</a>
                </li>
                <li class="mb-4">
                    <a href="#" class="hover:underline">Liên hệ hợp tác</a>
                </li>
                <li class="mb-4">
                    <a href="#" class="hover:underline">Q&A</a>
                </li>
            </ul>
        </div>
        <div>
            <h2 class="mb-6 text-sm font-semibold text-red-700 uppercase dark:text-white">Hệ thống cửa hàng</h2>
            <ul class="text-red-700 dark:text-gray-400 font-medium">
                <li class="mb-4">
                    <a href="#" class="hover:underline">Chi nhánh 1: ABCXYZ</a>
                </li>
               
            </ul>
        </div>
    </div>
    <div class="px-4 py-6 bg-gray-100 dark:bg-gray-700 md:flex md:items-center md:justify-between">
        <span class="text-sm text-gray-500 dark:text-gray-300 sm:text-center">© 2023 <a href="#"></a>. All Rights Reserved.
        </span>
        <div class="flex mt-4 sm:justify-center md:mt-0 space-x-5 rtl:space-x-reverse">
            <a href="https://www.facebook.com/phuc.fckb" class="text-gray-400 hover:text-gray-900 dark:hover:text-white">
                  <svg class="w-4 h-4" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 8 19">
                        <path fill-rule="evenodd" d="M6.135 3H8V0H6.135a4.147 4.147 0 0 0-4.142 4.142V6H0v3h2v9.938h3V9h2.021l.592-3H5V3.591A.6.6 0 0 1 5.592 3h.543Z" clip-rule="evenodd"/>
                    </svg>
                  <span class="sr-only">Facebook page</span>
              </a>
              <a href="https://oj.vnoi.info/user/HyhTonHappy" class="text-gray-400 hover:text-gray-900 dark:hover:text-white">
                  <svg class="w-4 h-4" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 21 16">
                        <path d="M16.942 1.556a16.3 16.3 0 0 0-4.126-1.3 12.04 12.04 0 0 0-.529 1.1 15.175 15.175 0 0 0-4.573 0 11.585 11.585 0 0 0-.535-1.1 16.274 16.274 0 0 0-4.129 1.3A17.392 17.392 0 0 0 .182 13.218a15.785 15.785 0 0 0 4.963 2.521c.41-.564.773-1.16 1.084-1.785a10.63 10.63 0 0 1-1.706-.83c.143-.106.283-.217.418-.33a11.664 11.664 0 0 0 10.118 0c.137.113.277.224.418.33-.544.328-1.116.606-1.71.832a12.52 12.52 0 0 0 1.084 1.785 16.46 16.46 0 0 0 5.064-2.595 17.286 17.286 0 0 0-2.973-11.59ZM6.678 10.813a1.941 1.941 0 0 1-1.8-2.045 1.93 1.93 0 0 1 1.8-2.047 1.919 1.919 0 0 1 1.8 2.047 1.93 1.93 0 0 1-1.8    