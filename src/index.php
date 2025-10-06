<?php
session_start();
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

// Kiểm tra trạng thái đăng nhập
$is_logged_in = isset($_SESSION['name']); // Kiểm tra name trong session
$name_logged_in = $is_logged_in ? $_SESSION['name'] : ''; // Lấy name nếu đã đăng nhập
?>
<!doctype html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="./output.css" rel="stylesheet"> 
    <link href="https://cdn.jsdelivr.net/npm/flowbite@2.5.1/dist/flowbite.min.css" rel="stylesheet">
    <link rel="stylesheet" href="./fontawesome-free-6.6.0-web/fontawesome-free-6.6.0-web/css/all.min.css">
    <title>Bán hàng</title>
</head>

<body>
<header>
    <nav id="header" class="dark:bg-gray-900 w-full z-50 top-0 left-0 bg-red-400 fixed transition-all duration-500">
        <div class="max-w-screen-xl flex flex-wrap items-center justify-between mx-auto p-4">
        <div class="flex items-center space-x-3">
            <img src="../img/logo.png" alt="Logo" class="w-12 h-15 "> 
                <a href="#" class="flex items-center space-x-3 rtl:space-x-reverse">
                    <span class="self-center text-5xl font-semibold whitespace-nowrap text-white">Shoes</span>
                </a>
        </div>

            <div class="flex md:order-2 space-x-3 rtl:space-x-reverse justify-between items-center">
                <?php if ($is_logged_in): ?>
                    <!-- Hiển thị tên người dùng và nút đăng xuất khi đã đăng nhập -->
                    <a href="./myprofile/profile.php" class="text-white">
    Chào, <?php echo htmlspecialchars($name_logged_in ?? 'Khách'); ?>!
</a>
                    <a href="./logout/logout.php">
                        <button type="button" class="text-red-400 bg-white hover:bg-red-400 hover:text-white focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 text-center transition-all duration-500">Đăng xuất</button>
                    </a>
                <?php else: ?>
                    <!-- Hiển thị nút đăng nhập và đăng ký khi chưa đăng nhập -->
                    <a href="./login/sign_in.php">
                        <button type="button" class="text-red-400 bg-white hover:bg-red-400 hover:text-white focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 text-center transition-all duration-500">Đăng nhập</button>
                    </a>
                    <a href="./register/register.php">
                        <button type="button" class="text-red-400 bg-white hover:bg-red-400 hover:text-white focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 text-center transition-all duration-500">Đăng ký</button>
                    </a>
                <?php endif; ?>
            </div>

            <div class="items-center justify-between hidden w-full md:flex md:w-auto md:order-1" id="navbar-sticky">
                <ul class="flex flex-col p-4 md:p-0 mt-4 font-medium md:space-x-8 rtl:space-x-reverse md:flex-row md:mt-0 md:border-0">
                    <li><a href="./index.php" class="block py-2 px-3 text-white rounded md:hover:bg-transparent md:hover:text-red-100 md:p-0 dark:text-white dark:hover:text-white md:dark:hover:bg-transparent text-lg" aria-current="Home">Trang chủ</a></li>
                    <li><a href="#" class="block py-2 px-3 text-white rounded md:hover:bg-transparent md:hover:text-red-100 md:p-0 dark:text-white dark:hover:text-white md:dark:hover:bg-transparent text-lg">Chính sách</a></li>
                    <li><a href="#" class="block py-2 px-3 text-white rounded md:hover:bg-transparent md:p-0 dark:text-white dark:hover:text-white md:dark:hover:bg-transparent text-lg">Đối tác</a></li>
                    <li><a href="#" class="block py-2 px-3 text-white rounded md:hover:bg-transparent md:p-0 dark:text-white dark:hover:text-white md:dark:hover:bg-transparent text-lg">Hàng mới về</a></li>
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
    <section class="carousel pt-16"> <!-- Thêm pt-16 để có khoảng đệm trên -->
    <div id="default-carousel" class="relative w-full" data-carousel="slide">
        <div class="relative h-[300px] md:h-[800px] w-full overflow-hidden rounded-lg">
            <!-- Item 1 -->
            <div class="hidden duration-700 ease-in-out" data-carousel-item>
                <img src="../img/banner-website_master.webp" class="absolute inset-0 w-full h-full object-cover" alt="Banner 1">
            </div>
            <!-- Item 2 -->
            <div class="hidden duration-700 ease-in-out" data-carousel-item>
                <img src="../img/banner.jpg" class="absolute inset-0 w-full h-full object-cover" alt="Banner 2">
            </div>
            <!-- Item 3 -->
            <div class="hidden duration-700 ease-in-out" data-carousel-item>
                <img src="../img/banner-1.jpg" class="absolute block w-full -translate-x-1/2 -translate-y-1/2 top-1/2 left-1/2" alt="Banner 3">
            </div>
        </div>

        <!-- Slider indicators -->
        <div class="absolute z-30 flex -translate-x-1/2 bottom-5 left-1/2 space-x-3">
            <button type="button" class="w-3 h-3 rounded-full" aria-current="true" aria-label="Slide 1" data-carousel-slide-to="0"></button>
            <button type="button" class="w-3 h-3 rounded-full" aria-current="false" aria-label="Slide 2" data-carousel-slide-to="1"></button>
            <button type="button" class="w-3 h-3 rounded-full" aria-current="false" aria-label="Slide 3" data-carousel-slide-to="2"></button>
        </div>

        <!-- Slider controls -->
        <button type="button" class="absolute top-0 start-0 z-30 flex items-center justify-center h-full px-4 cursor-pointer group focus:outline-none" data-carousel-prev>
            <span class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-white/30 dark:bg-gray-800/30 group-hover:bg-white/50 group-focus:ring-4 group-focus:ring-white group-focus:outline-none">
                <svg class="w-4 h-4 text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 6 10">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 1 1 5l4 4" />
                </svg>
                <span class="sr-only">Previous</span>
            </span>
        </button>
        <button type="button" class="absolute top-0 end-0 z-30 flex items-center justify-center h-full px-4 cursor-pointer group focus:outline-none" data-carousel-next>
            <span class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-white/30 dark:bg-gray-800/30 group-hover:bg-white/50 group-focus:ring-4 group-focus:ring-white group-focus:outline-none">
                <svg class="w-4 h-4 text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 6 10">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 9 4-4-4-4" />
                </svg>
                <span class="sr-only">Next</span>
            </span>
        </button>
    </div>
</section>

<section class="list mt-10">
    <div class="container" style="padding: 0 100px;">
        <div class="flex justify-between items-center mb-4">
            <!-- Thanh tìm kiếm -->
            <form method="GET" action="index.php" id="advanced-search-form" class="flex space-x-2 w-full">
                <input type="text" name="name_product" placeholder="Tên sản phẩm" value="<?php echo isset($_GET['name_product']) ? htmlspecialchars($_GET['name_product']) : ''; ?>" class="border p-2 rounded-lg flex-1" />
              
                <input type="number" name="price_min" placeholder="Giá từ" value="<?php echo isset($_GET['price_min']) ? htmlspecialchars($_GET['price_min']) : ''; ?>" class="border p-2 rounded-lg w-24" min="0" />
                <input type="number" name="price_max" placeholder="Giá đến" value="<?php echo isset($_GET['price_max']) ? htmlspecialchars($_GET['price_max']) : ''; ?>" class="border p-2 rounded-lg w-24" min="0" />
                <select name="status" class="border p-2 rounded-lg">
                    <option value="">Tất cả trạng thái</option>
                    <option value="Còn hàng" <?php if(isset($_GET['status']) && $_GET['status'] == 'Còn hàng') echo 'selected'; ?>>Còn hàng</option>
                    <option value="available" <?php if(isset($_GET['status']) && $_GET['status'] == 'available') echo 'selected'; ?>>available</option>
                       <option value="Hết hàng" <?php if(isset($_GET['status']) && $_GET['status'] == 'Hết hàng') echo 'selected'; ?>>Hết hàng</option>
                </select>

                <select name="brand_id" class="border p-2 rounded-lg">
                    <option value="">Tất cả</option>
                    <option value="1" <?php if(isset($_GET['brand_id']) && $_GET['brand_id'] == '1') echo 'selected'; ?>>Nike</option>
                    <option value="2" <?php if(isset($_GET['brand_id']) && $_GET['brand_id'] == '2') echo 'selected'; ?>>Adidas</option>
                    <option value="3" <?php if(isset($_GET['brand_id']) && $_GET['brand_id'] == '3') echo 'selected'; ?>>Bitis</option>
                    <!-- Thêm các thương hiệu khác nếu cần -->
                </select>
              
                <button type="submit" class="bg-red-400 text-white px-4 rounded-lg hover:bg-red-600">Tìm kiếm</button>
            </form>

        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
    <?php
    
    // Kiểm tra xem người dùng đã đăng nhập hay chưa
    $isLoggedIn = isset($_SESSION['username']); // Giả định bạn lưu trữ username trong session

    // Build dynamic SQL query with filters
    $sql = "SELECT product_id, name_product, price, img1, status, description, brand_id FROM product WHERE 1=1";
    $params = [];

    if (!empty($_GET['name_product'])) {
        $sql .= " AND name_product LIKE :name_product";
        $params[':name_product'] = '%' . $_GET['name_product'] . '%';
    }
    if (!empty($_GET['brand_id'])) {
        $sql .= " AND brand_id = :brand_id";
        $params[':brand_id'] = $_GET['brand_id'];
    }
    if (!empty($_GET['price_min'])) {
        $sql .= " AND price >= :price_min";
        $params[':price_min'] = $_GET['price_min'];
    }
    if (!empty($_GET['price_max'])) {
        $sql .= " AND price <= :price_max";
        $params[':price_max'] = $_GET['price_max'];
    }
    if (isset($_GET['status']) && $_GET['status'] !== '') {
        $sql .= " AND status = :status";
        $params[':status'] = $_GET['status'];
    }
    if (!empty($_GET['description'])) {
        $sql .= " AND description LIKE :description";
        $params[':description'] = '%' . $_GET['description'] . '%';
    }

    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($products) {
        foreach ($products as $product) {
            $product_id = $product['product_id'];
            $name_product = $product['name_product'];
            $price = $product['price'];
            $img1 = $product['img1'];
            $status = $product['status'];
            $description = $product['description'];
            $brand_id = isset($product['brand_id']) ? $product['brand_id'] : ''; 
        
            // Tạo đường dẫn hình ảnh
            $image_path = '/img/' . htmlspecialchars($img1);
        
            // Hiển thị sản phẩm
            echo '
            <div class="border border-inherit product" data-brand="' . htmlspecialchars($brand_id) . '">
                <img class="h-auto max-w-full rounded-lg transition-all duration-500 transform hover:scale-105" 
                     src="' . $image_path . '" 
                     alt="Hình ảnh sản phẩm">
                <div class="content pl-5">
                    <p class="mt-1 font-bold">' . htmlspecialchars($name_product) . '</p>
                    <p class="mt-1">' . number_format($price) . ' VND</p>';
        
            // Kiểm tra trạng thái đăng nhập và hiển thị nút tương ứng
            echo '
                <div class="flex justify-between mt-5">';
 /////////////////////////////////////////////////////////////////////////////////////////////LỖI//////////////////////////////////////////////               
             /*if ($isLoggedIn) {
            // Người dùng đã đăng nhập, chuyển đến trang mua ngay
            echo '
                <a href="./src_phu/muangay.php?product_id=' . htmlspecialchars($product_id) . '" class="relative inline-flex items-center justify-center p-0.5 mb-2 me-2 overflow-hidden text-sm font-medium text-gray-900 rounded-lg group bg-gradient-to-br from-pink-500 to-orange-400 group-hover:from-pink-500 group-hover:to-orange-400 hover:text-white dark:text-white focus:ring-4 focus:outline-none focus:ring-pink-200 dark:focus:ring-pink-800">
                    <span class="relative px-5 py-2.5 transition-all ease-in duration-75 bg-white dark:bg-gray-900 rounded-md group-hover:bg-opacity-0">Mua ngay</span>
                </a>
                <a href="./src_phu/muangay.php?product_id=' . htmlspecialchars($product_id) . '" class="relative inline-flex items-center justify-center p-0.5 mb-2 me-2 overflow-hidden text-sm font-medium text-gray-900 rounded-lg group bg-gradient-to-br from-pink-500 to-orange-400 group-hover:from-pink-500 group-hover:to-orange-400 hover:text-white dark:text-white focus:ring-4 focus:outline-none focus:ring-pink-200 dark:focus:ring-pink-800">
                    <span class="relative px-5 py-2.5 transition-all ease-in duration-75 bg-white dark:bg-gray-900 rounded-md group-hover:bg-opacity-0">Thêm vào giỏ hàng</span>
                </a>';
        } else {
            // Người dùng chưa đăng nhập, chuyển đến trang đăng nhập cho cả "Mua ngay" và "Thêm vào giỏ hàng"
            echo '
                <a href="./src_phu/sign_in.php" class="relative inline-flex items-center justify-center p-0.5 mb-2 me-2 overflow-hidden text-sm font-medium text-gray-900 rounded-lg group bg-gradient-to-br from-pink-500 to-orange-400 group-hover:from-pink-500 group-hover:to-orange-400 hover:text-white dark:text-white focus:ring-4 focus:outline-none focus:ring-pink-200 dark:focus:ring-pink-800">
                    <span class="relative px-5 py-2.5 transition-all ease-in duration-75 bg-white dark:bg-gray-900 rounded-md group-hover:bg-opacity-0">Mua ngay</span>
                </a>
                <a href="./src_phu/sign_in.php" class="relative inline-flex items-center justify-center p-0.5 mb-2 me-2 overflow-hidden text-sm font-medium text-gray-900 rounded-lg group bg-gradient-to-br from-pink-500 to-orange-400 group-hover:from-pink-500 group-hover:to-orange-400 hover:text-white dark:text-white focus:ring-4 focus:outline-none focus:ring-pink-200 dark:focus:ring-pink-800">
                    <span class="relative px-5 py-2.5 transition-all ease-in duration-75 bg-white dark:bg-gray-900 rounded-md group-hover:bg-opacity-0">Thêm vào giỏ hàng</span>
                </a>';
        }

        echo '</div></div></div>';*/
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/*<a href="./src_phu/muangay.php?product_id=' . htmlspecialchars($product_id) . '" class="relative inline-flex items-center justify-center p-0.5 mb-2 me-2 overflow-hidden text-sm font-medium text-gray-900 rounded-lg group bg-gradient-to-br from-pink-500 to-orange-400 group-hover:from-pink-500 group-hover:to-orange-400 hover:text-white dark:text-white focus:ring-4 focus:outline-none focus:ring-pink-200 dark:focus:ring-pink-800 shadow-lg transition-transform transform hover:scale-105">
            <span class="text-lg font-bold uppercase relative px-6 py-3 transition-all ease-in duration-75 bg-white dark:bg-gray-900 rounded-md group-hover:bg-opacity-0">Mua ngay</span>
            <span class="text-xs font-normal mt-1 relative px-6 py-3 transition-all ease-in duration-75 bg-white dark:bg-gray-900 rounded-md group-hover:bg-opacity-0">(Giao tận nhà hoặc nhận tại cửa hàng)</span>
            </a>*/

          /*  <a href="./src_phu/muangay.php?product_id=' . htmlspecialchars($product_id) . '" class="relative inline-flex items-center justify-center p-0.5 mb-2 me-2 overflow-hidden text-sm font-medium text-gray-900 rounded-lg group bg-gradient-to-br from-pink-500 to-orange-400 group-hover:from-pink-500 group-hover:to-orange-400 hover:text-white dark:text-white focus:ring-4 focus:outline-none focus:ring-pink-200 dark:focus:ring-pink-800 shadow-lg transition-transform transform hover:scale-105">
                <span class="relative px-6 py-3 text-lg font-bold uppercase bg-white dark:bg-gray-900 rounded-md transition-all duration-75 group-hover:bg-opacity-0">Mua Ngay
                <span class="relative mt-1 text-xs font-normal bg-white dark:bg-gray-900 rounded-md transition-all duration-75 group-hover:bg-opacity-0">
                    (Giao tận nhà hoặc nhận tại cửa hàng)
                </span>
            </a>*/
        if ($isLoggedIn) {
            // Người dùng đã đăng nhập, chuyển đến trang mua ngay
            echo '
            <div class="flex items-center space-x-2">
            <!-- Mua Ngay Button -->
            <a href="./buynow/muangay.php?product_id=' . htmlspecialchars($product_id) . '" class= "relative inline-flex items-center justify-center p-0.5 mb-2 me-2 overflow-hidden text-sm font-medium text-white rounded-lg group bg-gradient-to-br from-pink-500 to-orange-400 group-hover:from-pink-500 group-hover:to-orange-400 hover:text-black dark:text-white focus:ring-4 focus:outline-none focus:ring-red-300 dark:focus:ring-pink-800 shadow-lg transition-transform transform hover:scale-105 mx-4">
                <span class="text-lg font-bold relative px-8 py-3 transition-all ease-in duration-75 hover:bg-white rounded-md ">MUA NGAY</span>
            </a>

            <!-- Add to Cart Button -->
            <a href="./buynow/muangay.php?product_id=' . htmlspecialchars($product_id) . '" class="relative inline-flex items-center justify-center p-0.5 mb-2 me-2 overflow-hidden text-sm font-medium text-gray-900 rounded-lg group bg-gradient-to-br from-pink-500 to-orange-400 group-hover:from-pink-500 group-hover:to-orange-400 hover:text-white dark:text-white focus:ring-4 focus:outline-none focus:ring-pink-200 dark:focus:ring-pink-800 shadow-lg transition-transform transform hover:scale-105 ">
                <span class="relative flex items-center px-6 py-4 transition-all ease-in duration-75 bg-white dark:bg-gray-900 rounded-md group-hover:bg-opacity-0 text-lg ">
                    <i class="fa-solid fa-cart-shopping "  ></i>
                </span>
            </a>

            </div>';
        } else {
            // Người dùng chưa đăng nhập, chuyển đến trang đăng nhập cho cả hai nút
            echo '
            <div class="flex items-center space-x-2">
            <a href="./login/sign_in.php" class= "relative inline-flex items-center justify-center p-0.5 mb-2 me-2 overflow-hidden text-sm font-medium text-white rounded-lg group bg-gradient-to-br from-pink-500 to-orange-400 group-hover:from-pink-500 group-hover:to-orange-400 hover:text-black dark:text-white focus:ring-4 focus:outline-none focus:ring-red-300 dark:focus:ring-pink-800 shadow-lg transition-transform transform hover:scale-105 mx-4">
                <span class="text-lg font-bold relative px-8 py-3 transition-all ease-in duration-75 hover:bg-white rounded-md ">MUA NGAY</span>
            </a>

        
            <a href="./login/sign_in.php" class="relative inline-flex items-center justify-center p-0.5 mb-2 me-2 overflow-hidden text-sm font-medium text-gray-900 rounded-lg group bg-gradient-to-br from-pink-500 to-orange-400 group-hover:from-pink-500 group-hover:to-orange-400 hover:text-white dark:text-white focus:ring-4 focus:outline-none focus:ring-pink-200 dark:focus:ring-pink-800 shadow-lg transition-transform transform hover:scale-105">
                <span class="relative flex items-center px-6 py-4 transition-all ease-in duration-75 bg-white dark:bg-gray-900 rounded-md group-hover:bg-opacity-0 text-lg ">
                    <i class="fa-solid fa-cart-shopping "  ></i>
                </span>
            </a>
            </div>';
        }
            echo '</div></div></div>';
        }
    } else {
        echo "<p>Không có sản phẩm nào.</p>";
    }

    // Đóng kết nối PDO
    $conn = null;
    ?>
</div>

</div>
</section>
  
    <div class="lits_more text-center mt-10 font-semibold text-red-400 text-xl">
        <a href="#">Còn nhiều hơn nữa, hãy khám phá nhé!</a>
    </div>

    
        <section class="map mt-10">
            <div class="container " style="padding: 0 100px;">

<div class="title text-center text-xl font-bold text-red-400 mb-4 md:text-4xl">
    <p>Hãy liên hệ với chúng tôi</p>
</div>

                <div class="contact_us">
                    <!-- drawer init and show -->
                    <div class="text-center">
                        <button class="text-white bg-red-400 hover:bg-red-800 focus:ring-4 focus:ring-red-400 font-medium rounded-lg text-sm px-5 py-2.5 mb-2 dark:bg-red-600 dark:hover:bg-red-700 focus:outline-none dark:focus:ring-red-800" type="button" data-drawer-target="drawer-contact" data-drawer-show="drawer-contact" aria-controls="drawer-contact">
                            Show contact form
                        </button>
                    </div>
                    <!-- drawer component -->
                    <div id="drawer-contact" class="fixed top-0 left-0 z-40 h-screen p-4 overflow-y-auto transition-transform -translate-x-full bg-white w-80 dark:bg-gray-800" tabindex="-1" aria-labelledby="drawer-contact-label">
                        <h5 id="drawer-label" class="inline-flex items-center mb-6 text-base font-semibold text-gray-500 uppercase dark:text-gray-400"><svg class="w-4 h-4 me-2.5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 16">
                                <path d="m10.036 8.278 9.258-7.79A1.979 1.979 0 0 0 18 0H2A1.987 1.987 0 0 0 .641.541l9.395 7.737Z" />
                                <path d="M11.241 9.817c-.36.275-.801.425-1.255.427-.428 0-.845-.138-1.187-.395L0 2.6V14a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V2.5l-8.759 7.317Z" />
                            </svg>Contact us</h5>
                        <button type="button" data-drawer-hide="drawer-contact" aria-controls="drawer-contact" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 absolute top-2.5 end-2.5 inline-flex items-center justify-center dark:hover:bg-gray-600 dark:hover:text-white">
                            <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
                            </svg>
                            <span class="sr-only">Close menu</span>
                        </button>
                        <form class="mb-6" action="./email/send_email.php" method="post">
    <div class="mb-6">
        <label for="email" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Your email</label>
        <input type="email" name="email" id="email" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-red-500 focus:border-red-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-red-500 dark:focus:border-red-500" placeholder="name@company.com" required />
    </div>
    <div class="mb-6">
        <label for="subject" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Subject</label>
        <input type="text" name="subject" id="subject" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-red-500 focus:border-red-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-red-500 dark:focus:border-red-500" placeholder="Let us know how we can help you" required />
    </div>
    <div class="mb-6">
        <label for="message" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Your message</label>
        <textarea name="message" id="message" rows="4" class="block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-red-500 focus:border-red-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-red-500 dark:focus:border-red-500" placeholder="Your message..."></textarea>
    </div>
    <button type="submit" class="text-white bg-red-700 hover:bg-red-800 w-full focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 mb-2 dark:bg-red-600 dark:hover:bg-red-700 focus:outline-none dark:focus:ring-red-800 block">Send message</button>
                        </form>

                        <p class="mb-2 text-sm text-gray-500 dark:text-gray-400">
                            <a href="#" class="hover:underline">info@company.com</a>
                        </p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            <a href="#" class="hover:underline">212-456-7890</a>
                        </p>
                    </div>
                </div>

                <div class="map_detail flex justify-center mt-4">
                    <div class="w-full max-w-xl md:max-w-full">
                        <div class="relative pb-[56.25%] h-0 overflow-hidden"> <!-- Aspect ratio container -->
                            <iframe class="absolute top-0 left-0 w-full h-full"
                                src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3918.2871471900667!2d106.61554297583898!3d10.865750757538079!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x31752b2a11844fb9%3A0xbed3d5f0a6d6e0fe!2zVHLGsOG7nW5nIMSQ4bqhaSBI4buNYyBHaWFvIFRow7RuZyBW4bqtbiBU4bqjaSBUaMOgbmggUGjhu5EgSOG7kyBDaMOtIE1pbmggKFVUSCkgLSBDxqEgc-G7nyAz!5e0!3m2!1svi!2sus!4v1726675831513!5m2!1svi!2sus"
                                frameborder="0" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade">
                            </iframe>
                        </div>
                    </div>
                </div>



            </div>
        </section>

        
</main>

<style>
body {
  font-family: 'Segoe UI', sans-serif;
  margin: 0;
  padding: 0;
  background: #f2f2f2;
}

.chat-widget {
  position: fixed;
  bottom: 80px;
  right: 20px;
  width: 300px;
  height: 400px;
  background: #fff;
  border-radius: 12px;
  box-shadow: 0 5px 15px rgba(0,0,0,0.2);
  display: none;
  flex-direction: column;
  overflow: hidden;
  z-index: 999;
}

.chat-header {
  background-color: #007bff;
  color: #fff;
  padding: 12px;
  font-weight: bold;
  text-align: center;
}

.chat-body {
  flex: 1;
  padding: 10px;
  overflow-y: auto;
  background-color: #f9f9f9;
}

.chat-message {
  margin: 6px 0;
  padding: 10px;
  border-radius: 15px;
  max-width: 80%;
  font-size: 14px;
  line-height: 1.4;
}

.chat-message.user {
  background-color: #dcf8c6;
  align-self: flex-end;
  text-align: right;
}

.chat-message.bot {
  background-color: #ececec;
  align-self: flex-start;
}

.chat-footer {
  display: flex;
  padding: 10px;
  border-top: 1px solid #ddd;
}

#chat-input {
  flex: 1;
  padding: 8px;
  border-radius: 8px;
  border: 1px solid #ccc;
}

#send-btn {
  background-color: #007bff;
  border: none;
  color: white;
  padding: 8px 12px;
  margin-left: 5px;
  border-radius: 8px;
  cursor: pointer;
}

.chat-toggle {
  position: fixed;
  bottom: 20px;
  right: 20px;
  background-color: #007bff;
  color: white;
  border: none;
  border-radius: 50%;
  width: 55px;
  height: 55px;
  font-size: 24px;
  box-shadow: 0 4px 10px rgba(0,0,0,0.3);
  cursor: pointer;
  z-index: 1000;
}
</style>

<?php
$is_logged_in = isset($_SESSION['username']);
$username = $is_logged_in ? $_SESSION['username'] : null;
?>

<!-- Chat Widget Start -->
<div class="chat-widget" style="display:none; position: fixed; bottom: 80px; right: 20px; width: 300px; height: 400px; background: #fff; border-radius: 12px; box-shadow: 0 5px 15px rgba(0,0,0,0.2); flex-direction: column; overflow: hidden; z-index: 999;">
  <div class="chat-header" style="background-color: #007bff; color: #fff; padding: 12px; font-weight: bold; text-align: center;">
    💬 Hỗ trợ tư vấn
  </div>
  <div class="chat-body" id="chat-body" style="flex: 1; padding: 10px; overflow-y: auto; background-color: #f9f9f9;">
    <div class="chat-message bot" style="margin: 6px 0; padding: 10px; border-radius: 15px; max-width: 80%; font-size: 14px; line-height: 1.4; background-color: #ececec; align-self: flex-start;">
      Xin chào! Mình có thể giúp gì cho bạn?
    </div>
  </div>
<div class="chat-footer" style="display: flex; flex-direction: column; gap: 8px; padding: 10px; border-top: 1px solid #ddd;">
    <?php if (!$is_logged_in): ?>
      <input type="text" id="chat-username" placeholder="Nhập tên của bạn" style="width: 100%; padding: 8px; border-radius: 8px; border: 1px solid #ccc;">
    <?php endif; ?>
    <input type="text" id="chat-input" placeholder="Nhập tin nhắn..." style="width: 100%; padding: 8px; border-radius: 8px; border: 1px solid #ccc;">
    <button id="send-btn" style="background-color: #007bff; border: none; color: white; padding: 8px 12px; border-radius: 8px; cursor: pointer; width: 100%;">Gửi</button>
  </div>
</div>

<button class="chat-toggle" id="chat-toggle" style="position: fixed; bottom: 20px; right: 20px; background-color: #007bff; color: white; border: none; border-radius: 50%; width: 55px; height: 55px; font-size: 24px; box-shadow: 0 4px 10px rgba(0,0,0,0.3); cursor: pointer; z-index: 1000;">💬</button>

<script>
const chatToggle = document.getElementById("chat-toggle");
const chatWidget = document.querySelector(".chat-widget");
const sendBtn = document.getElementById("send-btn");
const chatBody = document.getElementById("chat-body");
const chatInput = document.getElementById("chat-input");
const isLoggedIn = <?php echo json_encode($is_logged_in); ?>;
const loggedInUsername = <?php echo json_encode($username); ?>;

chatToggle.addEventListener("click", () => {
  chatWidget.style.display = chatWidget.style.display === "flex" ? "none" : "flex";
});

function fetchMessages() {
  fetch('get_messages.php')
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        chatBody.innerHTML = '';
        data.messages.forEach(msg => {
          const msgDiv = document.createElement('div');
          msgDiv.classList.add('chat-message');
          msgDiv.classList.add(msg.username === loggedInUsername ? 'user' : 'bot');
          const time = new Date(msg.created_at).toLocaleTimeString();
          msgDiv.innerHTML = `<strong>${msg.username}</strong> [${time}]: ${msg.message}`;
          chatBody.appendChild(msgDiv);
        });
        chatBody.scrollTop = chatBody.scrollHeight;
      }
    })
    .catch(err => console.error('Error fetching messages:', err));
}

sendBtn.addEventListener("click", sendMessage);
chatInput.addEventListener("keypress", function (e) {
  if (e.key === 'Enter') sendMessage();
});

function sendMessage() {
  let username = loggedInUsername;
  if (!isLoggedIn) {
    const usernameInput = document.getElementById('chat-username');
    username = usernameInput.value.trim();
    if (!username) {
      alert('Vui lòng nhập tên của bạn');
      return;
    }
  }
  const message = chatInput.value.trim();
  if (!message) return;

  fetch('send_message.php', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({ username, message })
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      chatInput.value = '';
      if (!isLoggedIn) {
        document.getElementById('chat-username').value = '';
      }
      fetchMessages();
    } else {
      alert('Gửi tin nhắn thất bại: ' + (data.error || 'Lỗi không xác định'));
    }
  })
  .catch(err => {
    alert('Lỗi khi gửi tin nhắn');
    console.error('Error:', err);
  });
}

fetchMessages();
setInterval(fetchMessages, 3000);
</script>

    <footer class="bg-red-200 dark:bg-gray-900 mt-40">
    <div class="mx-auto w-full max-w-screen-xl">
      <div class="grid grid-cols-2 gap-8 px-4 py-6 lg:py-8 md:grid-cols-4">
        <div>
            <h2 class="mb-6 text-sm font-semibold text-red-700 uppercase dark:text-white">Về chúng tôi</h2>
            <ul class="text-red-700 dark:text-gray-700 font-medium">
                <li class="mb-4">
                    <a href="./Footer/about.php" class=" hover:underline">About</a>
                </li>
                <li class="mb-4">
                    <a href="./Footer/careers.php" class="hover:underline">Careers</a>
                </li>
                <li class="mb-4">
                    <a href="./Footer/brand_center.php" class="hover:underline">Brand Center</a>
                </li>
                <li class="mb-4">
                    <a href="./Footer/blog.php" class="hover:underline">Blog</a>
                </li>
            </ul>
        </div>
        <div>
            <h2 class="mb-6 text-sm font-semibold text-red-700 uppercase dark:text-white">Thông tin</h2>
            <ul class="text-red-700 dark:text-gray-400 font-medium">
                <?php
            if ($isLoggedIn) {
                // Người dùng đã đăng nhập, chuyển đến trang mua ngay
                echo '
                    <li class="mb-4">
                    <a href="./status_product/status_deli.php" class="hover:underline">Trạng thái đơn hàng</a>
                    </li>
                    ';
                    
            } else {
                // Người dùng chưa đăng nhập, chuyển đến trang đăng nhập
                echo '
                    <li class="mb-4">
                    <a href="./login/sign_in.php" class="hover:underline">Trạng thái đơn hàng</a>
                    </li>';
            }
            ?>
                
                <li class="mb-4">
                    <a href="./Footer/chinhsachdoitra.php" class="hover:underline">Chính sách đổi trả</a>
                </li>
                <li class="mb-4">
                    <a href="./Footer/hinhthucthanhtoan.php" class="hover:underline">Hình thức thanh toán</a>
                </li>
                <li class="mb-4">
                    <a href="./Footer/chinhsachkhachhangthanthiet.php" class="hover:underline">Chính sách khách hàng thân thiết</a>
                </li>
            </ul>
        </div>
        <div>
            <h2 class="mb-6 text-sm font-semibold text-red-700 uppercase dark:text-white">Trợ giúp</h2>
            <ul class="text-red-700 dark:text-gray-400 font-medium">
                <li class="mb-4">
                    <a href="./Footer/tuyendung.php" class="hover:underline">Tuyển dụng</a>
                </li>
                <li class="mb-4">
                    <a href="./Footer/lienhehoptac.php" class="hover:underline">Liên hệ hợp tác</a>
                </li>
                <li class="mb-4">
                    <a href="./Footer/Q&A.php" class="hover:underline">Q&A</a>
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







<?php
// Giả định kết nối cơ sở dữ liệu ở đây
// Kiểm tra nếu người dùng đã đăng nhập
$is_logged_in = isset($_SESSION['username']);
$username = $is_logged_in ? $_SESSION['username'] : null;

// Nếu người dùng đã đăng nhập, lấy sản phẩm trong giỏ hàng
if ($is_logged_in) {
    try {
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
            WHERE c.username = :username
        ");
        $stmt->execute(['username' => $username]);
        $cartItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $totalItems = count($cartItems);
        
    } catch (PDOException $e) {
        die("Kết nối thất bại: " . $e->getMessage());
    }
} else {
    // Người dùng chưa đăng nhập, giỏ hàng trống
    $totalItems = 0;
}
?>

<div id="cart" class="fixed bottom-5 left-5 bg-gray-100 border border-gray-300 p-4 rounded-lg shadow-lg flex flex-col items-center justify-center hover:scale-105 ">
    <a href="./cart/cart.php">
        <button class="flex items-center justify-center w-16 h-16 bg-red-600 text-white rounded-full hover:bg-white hover:text-black border border-red-400 transition-all duration-300 shadow-lg focus:outline-none">
            <!-- Icon giỏ hàng -->
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5H3m4 8l-1.5 6h11L17 13m0 0H7m0 0l1.5 6h11L17 13" />
            </svg>
            <!-- Hiển thị huy hiệu với số lượng sản phẩm trong giỏ hàng -->
            <span class="absolute top-0 right-0 w-8 h-8 bg-white text-red-500 rounded-full text-xl font-bold flex items-center justify-center border-2" style="background-color:yellow;border-color:black; transform: translate(50%, -50%);">
                <?php 
                // Tính tổng số sản phẩm trong giỏ hàng

                    echo $totalItems; 
                    ?>
            </span>
        </button>
        <p class="mt-2 text-sm text-gray-700">Giỏ hàng</p>
    </a>
</div>


    <script src="https://cdn.jsdelivr.net/npm/flowbite@2.5.1/dist/flowbite.min.js"></script>
    <script>
        const menuToggle = document.getElementById('menu-toggle');
        const mobileMenu = document.getElementById('mobile-menu');

        menuToggle.addEventListener('click', () => {
            mobileMenu.classList.toggle('hidden');
        });
    </script>

    <script>
        // Lấy tất cả các link tab
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


</body>
</html>