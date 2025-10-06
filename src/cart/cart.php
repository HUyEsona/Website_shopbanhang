<?php
session_start();

// Kiểm tra nếu người dùng đã đăng nhập
if (!isset($_SESSION['username'])) {
    die("<h1>Vui lòng đăng nhập để xem giỏ hàng.</h1>");
}

$username = $_SESSION['username'];
$is_logged_in = true; // Biến để xác định người dùng đã đăng nhập
$name_logged_in = htmlspecialchars($_SESSION['name']); // Tên người dùng đã đăng nhập

try {
    // Kết nối tới cơ sở dữ liệu
    $conn = new PDO("mysql:host=localhost;dbname=cuoiky", "root", "");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

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
    $stmt->execute(['username' => $username]);
    $cartItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Kết nối thất bại: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giỏ Hàng</title>
    <link href="./../output.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/flowbite@2.5.1/dist/flowbite.min.css" rel="stylesheet">
  
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


<main>
    <section class="carts pt-20">
        <div class="container mx-auto mt-4">
            <div class="">
                <img src="./../../img/banner_2.webp" alt="">
            </div>
            <h2 class="text-2xl font-bold mb-4">Giỏ Hàng của Bạn</h2>
            <!-- Hiển thị tổng số lượng sản phẩm trong giỏ hàng -->
            <h3 class="text-4xl font-bold mb-4">Tổng số lượng đơn hàng trong giỏ hàng: 
                <?php 
                // Đếm số lượng sản phẩm dựa trên cart_id
                $totalItems = count($cartItems); 
                echo $totalItems;
                ?> 
            </h3>
            <?php if (empty($cartItems)): ?>
                <p class="text-gray-500">Giỏ hàng của bạn đang trống.</p>
            <?php else: ?>
                <table class="min-w-full border border-gray-300">
                    <thead>
                        <tr class="bg-gray-100">
                            <th class="border px-4 py-2">Tên sản phẩm</th>
                            <th class="border px-4 py-2">Kích cỡ</th>
                            <th class="border px-4 py-2">Màu sắc</th>
                            <th class="border px-4 py-2">Số lượng</th>
                            <th class="border px-4 py-2">Giá</th>
                            <th class="border px-4 py-2">Tổng</th>
                            <th class="border px-4 py-2">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cartItems as $item): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="border px-4 py-2"><?php echo htmlspecialchars($item['name_product']); ?></td>
                                <td class="border px-4 py-2"><?php echo htmlspecialchars($item['size']); ?></td>
                                <td class="border px-4 py-2"><?php echo htmlspecialchars($item['color']); ?></td>
                                <td class="border px-4 py-2">
                                    <form method="post" action="update_quantity.php" class="flex items-center">
                                        <input type="hidden" name="cart_id" value="<?php echo htmlspecialchars($item['cart_id']); ?>">
                                        <input type="number" name="quantity" value="<?php echo htmlspecialchars($item['quantity']); ?>" min="1" class="border border-gray-300 rounded-md p-1 w-16">
                                        <button type="submit" class="ml-2 bg-blue-500 text-white px-2 py-1 rounded-md hover:bg-blue-600">Cập nhật</button>
                                    </form>
                                </td>
                                <td class="border px-4 py-2"><?php echo number_format(htmlspecialchars($item['price']), 0, ',', '.'); ?> VNĐ</td>
                                <td class="border px-4 py-2"><?php echo number_format(htmlspecialchars($item['price'] * $item['quantity']), 0, ',', '.'); ?> VNĐ</td>
                                <td class="border px-4 py-2">
                                    <form method="post" action="remove_from_cart.php">
                                        <input type="hidden" name="cart_id" value="<?php echo htmlspecialchars($item['cart_id']); ?>">
                                        <button type="submit" class="bg-red-500 text-white px-2 py-1 rounded-md hover:bg-red-600">Xóa</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <div class="text-right mt-4">
                    <a href="./buynowcart.php">
                        <button type="button" class="bg-green-500 text-white px-4 py-2 rounded-md hover:bg-green-600">Đặt hàng</button>
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </section>
</main>


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
                    <a href="../status_product/status_deli.php" class="hover:underline">Trạng thái đơn hàng</a>
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
                        <path d="M16.942 1.556a16.3 16.3 0 0 0-4.126-1.3 12.04 12.04 0 0 0-.529 1.1 15.175 15.175 0 0 0-4.573 0 11.585 11.585 0 0 0-.535-1.1 16.274 16.274 0 0 0-4.129 1.3A17.392 17.392 0 0 0 .182 13.218a15.785 15.785 0 0 0 4.963 2.521c.41-.564.773-1.16 1.084-1.785a10.63 10.63 0 0 1-1.706-.83c.143-.106.283-.217.418-.33a11.664 11.664 0 0 0 10.118 0c.137.113.277.224.418.33-.544.328-1.116.606-1.71.832a12.52 12.52 0 0 0 1.084 1.785 16.46 16.46 0 0 0 5.064-2.595 17.286 17.286 0 0 0-2.973-11.59ZM6.678 10.813a1.941 1.941 0 0 1-1.8-2.045 1.93 1.93 0 0 1 1.8-2.047 1.919 1.919 0 0 1 1.8 2.047 1.93 1.93 0 0 1-1.8 2.045Zm6.644 0a1.94 1.94 0 0 1-1.8-2.045 1.93 1.93 0 0 1 1.8-2.047 1.918 1.918 0 0 1 1.8 2.047 1.93 1.93 0 0 1-1.8 2.045Z"/>
                    </svg>
                  <span class="sr-only">Discord community</span>
              </a>
              <a href="#" class="text-gray-400 hover:text-gray-900 dark:hover:text-white">
                  <svg class="w-4 h-4" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 17">
                    <path fill-rule="evenodd" d="M20 1.892a8.178 8.178 0 0 1-2.355.635 4.074 4.074 0 0 0 1.8-2.235 8.344 8.344 0 0 1-2.605.98A4.13 4.13 0 0 0 13.85 0a4.068 4.068 0 0 0-4.1 4.038 4 4 0 0 0 .105.919A11.705 11.705 0 0 1 1.4.734a4.006 4.006 0 0 0 1.268 5.392 4.165 4.165 0 0 1-1.859-.5v.05A4.057 4.057 0 0 0 4.1 9.635a4.19 4.19 0 0 1-1.856.07 4.108 4.108 0 0 0 3.831 2.807A8.36 8.36 0 0 1 0 14.184 11.732 11.732 0 0 0 6.291 16 11.502 11.502 0 0 0 17.964 4.5c0-.177 0-.35-.012-.523A8.143 8.143 0 0 0 20 1.892Z" clip-rule="evenodd"/>
                </svg>
                  <span class="sr-only">Twitter page</span>
              </a>
              <a href="#" class="text-gray-400 hover:text-gray-900 dark:hover:text-white">
                  <svg class="w-4 h-4" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 .333A9.911 9.911 0 0 0 6.866 19.65c.5.092.678-.215.678-.477 0-.237-.01-1.017-.014-1.845-2.757.6-3.338-1.169-3.338-1.169a2.627 2.627 0 0 0-1.1-1.451c-.9-.615.07-.6.07-.6a2.084 2.084 0 0 1 1.518 1.021 2.11 2.11 0 0 0 2.884.823c.044-.503.268-.973.63-1.325-2.2-.25-4.516-1.1-4.516-4.9A3.832 3.832 0 0 1 4.7 7.068a3.56 3.56 0 0 1 .095-2.623s.832-.266 2.726 1.016a9.409 9.409 0 0 1 4.962 0c1.89-1.282 2.717-1.016 2.717-1.016.366.83.402 1.768.1 2.623a3.827 3.827 0 0 1 1.02 2.659c0 3.807-2.319 4.644-4.525 4.889a2.366 2.366 0 0 1 .673 1.834c0 1.326-.012 2.394-.012 2.72 0 .263.18.572.681.475A9.911 9.911 0 0 0 10 .333Z" clip-rule="evenodd"/>
                  </svg>
                  <span class="sr-only">GitHub account</span>
              </a>
              <a href="#" class="text-gray-400 hover:text-gray-900 dark:hover:text-white">
                  <svg class="w-4 h-4" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 0a10 10 0 1 0 10 10A10.009 10.009 0 0 0 10 0Zm6.613 4.614a8.523 8.523 0 0 1 1.93 5.32 20.094 20.094 0 0 0-5.949-.274c-.059-.149-.122-.292-.184-.441a23.879 23.879 0 0 0-.566-1.239 11.41 11.41 0 0 0 4.769-3.366ZM8 1.707a8.821 8.821 0 0 1 2-.238 8.5 8.5 0 0 1 5.664 2.152 9.608 9.608 0 0 1-4.476 3.087A45.758 45.758 0 0 0 8 1.707ZM1.642 8.262a8.57 8.57 0 0 1 4.73-5.981A53.998 53.998 0 0 1 9.54 7.222a32.078 32.078 0 0 1-7.9 1.04h.002Zm2.01 7.46a8.51 8.51 0 0 1-2.2-5.707v-.262a31.64 31.64 0 0 0 8.777-1.219c.243.477.477.964.692 1.449-.114.032-.227.067-.336.1a13.569 13.569 0 0 0-6.942 5.636l.009.003ZM10 18.556a8.508 8.508 0 0 1-5.243-1.8 11.717 11.717 0 0 1 6.7-5.332.509.509 0 0 1 .055-.02 35.65 35.65 0 0 1 1.819 6.476 8.476 8.476 0 0 1-3.331.676Zm4.772-1.462A37.232 37.232 0 0 0 13.113 11a12.513 12.513 0 0 1 5.321.364 8.56 8.56 0 0 1-3.66 5.73h-.002Z" clip-rule="evenodd"/>
                </svg>
                  <span class="sr-only">Dribbble account</span>
              </a>
        </div>
      </div>
    </div>
</footer>
</body>
</html>
