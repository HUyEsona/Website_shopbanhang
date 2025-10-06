
<?php
session_start();

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "cuoiky";

try {
    // Kết nối đến cơ sở dữ liệu
    $conn = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Kết nối thất bại: " . $e->getMessage());
}

// Kiểm tra trạng thái đăng nhập
$is_logged_in = isset($_SESSION['name']); // Kiểm tra name trong session
$name_logged_in = $is_logged_in ? $_SESSION['name'] : ''; // Lấy name nếu đã đăng nhập

// Lấy product_id từ query string
$product_id = isset($_GET['product_id']) ? intval($_GET['product_id']) : 0;

// Truy vấn thông tin sản phẩm từ cơ sở dữ liệu
try {
    $sql = "SELECT * FROM product WHERE product_id = :product_id"; // Sử dụng prepared statement
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':product_id', $product_id, PDO::PARAM_INT);
    $stmt->execute();

    // Truy vấn danh sách size có sẵn
    $sizesQuery = $conn->prepare("SELECT s.size FROM sizes s JOIN size_product sp ON s.size_id = sp.size_id WHERE sp.product_id = :product_id");
    $sizesQuery->execute(['product_id' => $product_id]);
    $availableSizes = $sizesQuery->fetchAll(PDO::FETCH_COLUMN);

    // Truy vấn danh sách color có sẵn
    $colorsQuery = $conn->prepare("SELECT c.color FROM colors c JOIN color_product cp ON c.color_id = cp.color_id WHERE cp.product_id = :product_id");
    $colorsQuery->execute(['product_id' => $product_id]);
    $availableColors = $colorsQuery->fetchAll(PDO::FETCH_COLUMN);

    if ($stmt->rowCount() > 0) {
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
    } else {
        echo "Sản phẩm không tồn tại.";
        exit; // Thoát nếu sản phẩm không tìm thấy
    }
} catch (PDOException $e) {
    echo "Lỗi truy vấn: " . $e->getMessage();
    exit; // Thoát nếu có lỗi trong truy vấn
}
?>

<!doctype html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="./../output.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/flowbite@2.5.1/dist/flowbite.min.css" rel="stylesheet">
    <title>Bán hàng</title>
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
    <section class="detail_product" style="padding-top: 70px;">
        <div class="container">
            <div class="banner w-full h-auto max-w-full md:object-cover">
                <img src="/img/banner_2.webp" alt="">
            </div>
            <div class="content pt-20 flex flex-col justify-center mt-20 md:flex-row md:justify-around">
                <div class="ml-20 img w-1/2 md:w-full">
                    <div class="grid gap-4">
                        <div class="main-img">
                            <img class="h-auto max-w-full rounded-lg cursor-pointer" src="/img/<?php echo htmlspecialchars($product['img1']); ?>" alt="">
                        </div>
                        <div class="grid grid-cols-4 gap-4">
                            <div>
                                <img class="w-full max-w-md md:max-w-none md:w-full cursor-pointer" src="/img/<?php echo htmlspecialchars($product['img2']); ?>" alt="">
                            </div>
                            <div>
                                <img class="h-auto max-w-full rounded-lg cursor-pointer" src="/img/<?php echo htmlspecialchars($product['img3']); ?>" alt="">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="content_title">
                    <div class="content_shoe text-left">
                        <h3 class="font-bold"><?php echo htmlspecialchars($product['name_product']); ?></h3>
                        <p class="mt-5">Giá: <?php echo number_format($product['price']); ?> VND</p>
                        <p class="mt-5">Tình trạng: <?php echo htmlspecialchars($product['status']); ?></p>
                    </div>

                    <?php if ($product['status'] === 'Còn hàng') : ?>
                        <h5 class="mt-5">Size</h5>
                        <div class="size grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4 mt-20">
                            <?php
                            $sizes = [39, 40, 41, 42, 43, 44, 45];
                            foreach ($sizes as $size) {
                                $disabled = in_array($size, $availableSizes) ? '' : 'disabled';
                                $classDisabled = $disabled ? 'bg-gray-200 cursor-not-allowed' : 'hover:bg-gray-100';

                                echo '<label class="block border border-gray-300 rounded-md text-center p-2 cursor-pointer ' . $classDisabled . '" ' . ($disabled ? '' : 'onclick="selectSize(' . $size . ')"') . '>
                                <input type="radio" name="size" value="' . $size . '" class="sr-only" ' . ($disabled ? 'disabled' : '') . '>' . $size . '
                                </label>';
                            }
                            ?>
                        </div>

                        <div class="color grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4 mt-20">
                            <?php
                            $colors = ['Red', 'Blue', 'White', 'Black', 'Brown'];
                            foreach ($colors as $color) {
                                $disabled = in_array($color, $availableColors) ? '' : 'disabled';
                                $classDisabled = $disabled ? 'bg-gray-200 cursor-not-allowed' : 'hover:bg-gray-100';

                                echo '<label class="block border border-gray-300 rounded-md text-center p-2 cursor-pointer ' . $classDisabled . '" ' . ($disabled ? '' : 'onclick="selectColor(\'' . $color . '\')"') . '>
                                <input type="radio" name="color" value="' . $color . '" class="sr-only" ' . ($disabled ? 'disabled' : '') . '>' . $color . '
                                </label>';
                            }
                            ?>
                        </div>

                        <div class="quantity_group mt-10">
                            <div class="mx-auto">
                                <div class="flex justify-center space-x-8">
                                    <div class="w-64">
                                        <div class="flex items-center">
                                            <button type="button" class="btn-number bg-red-400 text-gray-700 px-4 py-2 rounded-l" data-type="minus">
                                                <span class="text-xl">-</span>
                                            </button>
                                            <input id="quantityInput" class="input-number border text-center w-full py-2" value="1" type="number" min="1" max="9999999999999">
                                            <button type="button" class="btn-number bg-red-400 text-gray-700 px-4 py-2 rounded-r" data-type="plus">
                                                <span class="text-xl">+</span>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div id="error-message" class="text-red-500 mt-2 hidden"></div>
                        <div id="message" class="mt-2"></div> <!-- Message display here -->

                        <div class="btn-list flex flex-col md:flex-row justify-center items-center space-x-4 mt-5">
                            <!-- Mua ngay button -->
                            <div class="btn_buynow">
                                <button class="relative inline-flex items-center justify-center w-full md:w-auto px-6 py-3 mb-2 text-lg font-medium text-white bg-gradient-to-br from-pink-500 to-orange-400 rounded-lg shadow-md hover:from-pink-600 hover:to-orange-500 focus:ring-4 focus:outline-none focus:ring-pink-200 dark:focus:ring-pink-800 transition duration-200 ease-in-out" onclick="updateBuyNowLink();">
                                    Mua ngay
                                </button>
                            </div>
                            <!-- Thêm vào giỏ hàng button -->
                            <form id="addToCartForm" method="POST" action="../cart/addtocart.php">
                                <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
                                <input type="hidden" name="size" id="selectedSize" value="">
                                <input type="hidden" name="color" id="selectedColor" value="">
                                <input type="hidden" name="quantity" id="selectedQuantity" value="1">

                                <button type="submit" class="relative inline-flex items-center justify-center w-full md:w-auto px-6 py-3 mb-2 text-lg font-medium text-white bg-gradient-to-br from-green-500 to-teal-400 rounded-lg shadow-md hover:from-green-600 hover:to-teal-500 focus:ring-4 focus:outline-none focus:ring-green-200 dark:focus:ring-green-800 transition duration-200 ease-in-out">
                                    Thêm vào giỏ hàng
                                </button>
                            </form>
                        </div>
                    <?php else: ?>
                        <div class="mt-10 text-red-500 font-bold">
                            Sản phẩm này hiện đang hết hàng và không thể đặt mua.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <section class="detail mt-10">
        <div class="container">
            <div class="title">
                <h4 class="font-bold text-xl">Mô tả sản phẩm</h4>
            </div>
            <p><?php echo htmlspecialchars($product['description']); ?></p>
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
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flowbite@2.5.1/dist/flowbite.min.js"></script>
    <script>
        const menuToggle = document.getElementById('menu-toggle');
        const mobileMenu = document.getElementById('mobile-menu');

        menuToggle.addEventListener('click', () => {
            mobileMenu.classList.toggle('hidden');
        });
    </script>

    <script>
        const tabs = document.querySelectorAll('.link-item');
        const contents = document.querySelectorAll('.content');

        tabs.forEach(tab => {
            tab.addEventListener('click', function(event) {
                event.preventDefault(); // Ngăn chặn mặc định của thẻ <a>

                // Loại bỏ lớp active và ẩn tất cả nội dung
                tabs.forEach(item => item.classList.remove('bg-red-400', 'text-white'));
                contents.forEach(content => content.classList.add('hidden'));

                // Thêm lớp active cho tab được nhấn
                this.classList.add('bg-red-400', 'text-white');

                // Hiển thị nội dung tương ứng với tab
                const target = this.getAttribute('href').substring(1); // Lấy ID mà tab trỏ đến
                document.getElementById(target).classList.remove('hidden');
            });
        });
    </script>

<script>
    function openModal(imgElement) {
        const modal = document.getElementById("myModal");
        const modalImg = document.getElementById("modalImg");
        modalImg.src = imgElement.src;  // Lấy ảnh từ ảnh được nhấp
        modal.classList.remove("hidden");  // Hiển thị modal
    }

    function closeModal() {
        const modal = document.getElementById("myModal");
        modal.classList.add("hidden");  // Đóng modal
    }
</script>


<script>
    window.onscroll = function() {
        const header = document.getElementById("header");
        const buttons = header.querySelectorAll("button");

        // Khi cuộn xuống 50px, đổi màu nền và màu chữ
        if (window.scrollY > 50) {
            // Nền header màu trắng với opacity 52%
            header.style.backgroundColor = "rgba(255, 255, 255, 0.52)";
            
            // Đổi màu chữ các link trong header
            header.querySelectorAll("a").forEach((el) => {
                el.classList.add("text-red-400");
                el.classList.remove("text-white");
            });

            header.querySelectorAll("span").forEach((el) => {
                el.classList.add("text-red-400");
                el.classList.remove("text-white");
            });

            // Đổi màu các nút
            buttons.forEach((btn) => {
                btn.classList.remove("text-red-400", "bg-white", "hover:bg-red-400", "hover:text-white");
                btn.classList.add("text-white", "bg-red-400", "hover:bg-white", "hover:text-red-400");
            });
        } else {
            // Khi cuộn lên lại thì đổi về màu mặc định bg-red-400
            header.classList.add("bg-red-400");
            header.style.backgroundColor = "";  // Remove inline style

            // Đổi lại màu chữ
            header.querySelectorAll("a").forEach((el) => {
                el.classList.remove("text-red-400");
                el.classList.add("text-white");
            });

            header.querySelectorAll("span").forEach((el) => {
                el.classList.add("text-white");
                el.classList.remove("text-red-400");
            });

            // Đổi lại màu các nút
            buttons.forEach((btn) => {
                btn.classList.add("text-red-400", "bg-white", "hover:bg-red-400", "hover:text-white");
                btn.classList.remove("text-white", "bg-red-400", "hover:bg-white", "hover:text-red-400");
            });
        }
    };
</script>



<script>
let selectedSize = null;
let selectedColor = null;

function selectSize(size) {
    selectedSize = size;
    document.querySelector('#selectedSize').value = size;  // Cập nhật size vào form
    document.querySelectorAll('input[name="size"]').forEach(input => {
        input.parentElement.classList.remove('border-red-400', 'bg-red-400');
    });
    event.currentTarget.classList.add('border-red-400', 'bg-red-400');
}

function selectColor(color) {
    selectedColor = color;
    document.querySelector('#selectedColor').value = color;  // Cập nhật color vào form
    document.querySelectorAll('input[name="color"]').forEach(input => {
        input.parentElement.classList.remove('border-red-400', 'bg-red-400');
    });
    event.currentTarget.classList.add('border-red-400', 'bg-red-400');
}

// Cập nhật số lượng khi người dùng thay đổi số lượng
document.querySelector('.input-number').addEventListener('input', function() {
    document.querySelector('#selectedQuantity').value = this.value;
});

function showError(message) {
    const errorMessageElement = document.getElementById('error-message');
    errorMessageElement.textContent = message;
    errorMessageElement.classList.remove('hidden'); // Show the error message
}

function hideError() {
    const errorMessageElement = document.getElementById('error-message');
    errorMessageElement.textContent = '';
    errorMessageElement.classList.add('hidden'); // Hide the error message
}

// Cập nhật liên kết "Mua ngay"
function updateBuyNowLink() {
    if (!selectedSize || !selectedColor) {
        showError('Vui lòng chọn size và màu trước khi mua.'); // Show error if not selected
        return; // Exit the function if validation fails
    }
    hideError(); // Hide error if everything is fine
    const quantity = document.querySelector('.input-number').value || 1; // Get quantity from input
    const link = `../buynow/buynow.php?product_id=<?php echo $product_id; ?>&size=${selectedSize}&color=${selectedColor}&quantity=${quantity}`;
    window.location.href = link; // Redirect to the constructed link
}

// Update add to cart form submission
document.getElementById('addToCartForm').addEventListener('submit', function(event) {
    if (!selectedSize || !selectedColor) {
        event.preventDefault(); // Prevent form submission
        showError('Vui lòng chọn size và màu trước khi thêm vào giỏ hàng.'); // Show error
    } else {
        hideError(); // Hide error if everything is fine
        event.preventDefault(); // Prevent normal submission for AJAX

        const formData = new FormData(this); // Get form data

        fetch('../cart/addtocart.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(data => {
            document.getElementById('message').innerHTML = data; // Display the server response
        })
        .catch(error => {
            console.error('Error:', error);
        });
    }
});
document.addEventListener('DOMContentLoaded', function() {
    const quantityInput = document.getElementById('quantityInput');
    const hiddenQuantityField = document.getElementById('selectedQuantity');

    // Update hidden quantity field
    function updateHiddenField() {
        hiddenQuantityField.value = quantityInput.value || 1; // Ensure it defaults to 1 if empty
    }

    quantityInput.addEventListener('input', updateHiddenField);

    // Handle button clicks for incrementing and decrementing
    document.querySelectorAll('.btn-number').forEach(button => {
        button.addEventListener('click', function() {
            let currentValue = parseInt(quantityInput.value);

            if (this.getAttribute('data-type') === 'plus') {
                currentValue++;
            } else if (this.getAttribute('data-type') === 'minus') {
                if (currentValue > 1) {
                    currentValue--;
                }
            }

            quantityInput.value = currentValue;
            updateHiddenField(); // Update the hidden quantity field
        });
    });

    // Initialize the hidden field with the starting quantity
    updateHiddenField();
});
document.querySelector('.input-number').addEventListener('input', updateBuyNowLink);
</script>


</body>

</html>
