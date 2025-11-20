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

$error_message = '';





// Kiểm tra khi form được submit
if (isset($_POST['submit'])) {
    // Kiểm tra xem có nhập tên người dùng và mật khẩu không
    if (empty($_POST['username']) || empty($_POST['password'])) {
        $error_message = 'Vui lòng nhập đầy đủ thông tin';
    } else {
        $username = $_POST['username'];
        $password = $_POST['password'];

        // Kiểm tra xem nếu là tài khoản admin
        if ($username === '1') {
          // Mật khẩu đã mã hóa cho tài khoản admin (vẫn giữ để tương thích)
          $hashed_admin_password = '$2y$10$nsAd7cG3knsmB5aTJDITJOGev6iCc8iX4BCqtCbW5OZjgk4fZZwZG';

          // HỖ TRỢ PHÁT TRIỂN: cho phép mật khẩu tạm thời 'admin123' để vào trang admin.
          // Lưu ý: Đây là bypass không an toàn — chỉ dùng trong môi trường phát triển.
          if ($password === 'admin123' || password_verify($password, $hashed_admin_password)) {
            // Đăng nhập admin thành công
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['name'] = 'Admin';
            header('Location: ../main/admin.php');
            exit(); // Đảm bảo script dừng lại sau khi chuyển hướng
          } else {
            $error_message = 'Mật khẩu sai';
          }
        }

        // Truy vấn để tìm thông tin tài khoản từ bảng taikhoan
        $sql = "SELECT * FROM taikhoan WHERE username = :username";
        $stmt = $conn->prepare($sql);
        $stmt->execute(['username' => $username]);

        if ($stmt->rowCount() == 0) {
            $error_message = 'Tài khoản không tồn tại';
        } else {
            // Lấy thông tin tài khoản từ cơ sở dữ liệu
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            $hashedPassword = $user['password'];

            // So sánh mật khẩu đã nhập với mật khẩu đã mã hóa
            if (!password_verify($password, $hashedPassword)) {
                $error_message = 'Mật khẩu sai';
            } else {
                // Truy vấn để lấy tên từ bảng dangky thông qua phép INNER JOIN với bảng taikhoan
                $sqlName = "SELECT dangky.name 
                            FROM dangky 
                            INNER JOIN taikhoan 
                            ON dangky.username = taikhoan.username 
                            WHERE taikhoan.username = :username";
                $stmtName = $conn->prepare($sqlName);
                $stmtName->execute(['username' => $username]);

                // Lấy kết quả từ truy vấn
                if ($stmtName->rowCount() > 0) {
                    $dangki = $stmtName->fetch(PDO::FETCH_ASSOC);
                    $name = $dangki['name'];

                    // Đăng nhập thành công
                    $_SESSION['username'] = $username;
                    $_SESSION['name'] = $name;
                    header('Location: ./../index.php');
                    exit(); // Đảm bảo script dừng lại sau khi chuyển hướng
                } else {
                    $error_message = 'Không tìm thấy thông tin người dùng trong bảng dangky';
                }
            }
        }
    }
}

// Đảm bảo kết nối được đóng
$conn = null;
?>




<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="./output.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/flowbite@2.5.1/dist/flowbite.min.css" rel="stylesheet">
    <title>Đăng Nhập</title>
</head>
<body>
<section class="bg-gray-50 dark:bg-gray-900">
    <div class="py-8 px-4 mx-auto max-w-screen-xl lg:py-16 grid lg:grid-cols-2 gap-8 lg:gap-16">
        <div class="flex flex-col justify-center">
          <img src="../../img/logo dk.png">
            <p class="mb-6 text-lg font-normal text-gray-500 lg:text-xl dark:text-gray-400">Đăng nhập tài khoản của bạn</p>
            <a href="./../index.php" class="text-red-400 dark:text-blue-500 hover:underline font-medium text-lg inline-flex items-center">Trang chủ 
                <svg class="w-3.5 h-3.5 ms-2 rtl:rotate-180" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 10">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M1 5h12m0 0L9 1m4 4L9 9"/>
                </svg>
            </a>
        </div>
        <div>
            <div class="w-full lg:max-w-xl p-6 space-y-8 sm:p-8 bg-white rounded-lg shadow-xl dark:bg-gray-800">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Đăng nhập</h2>
                <?php if ($error_message): ?>
                    <div class="text-red-500"><?= htmlspecialchars($error_message) ?></div>
                <?php endif; ?>
                <form class="mt-8 space-y-6" action="#" method="POST">
                    <div>
                        <label for="username" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Tên đăng nhập</label>
                        <input name="username" id="username" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-red-400 focus:border-red-400 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-red-400 dark:focus:border-red-400" placeholder="name@company.com" required />
                    </div>
                    <div>
                        <label for="password" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Mật khẩu</label>
                        <input type="password" name="password" id="password" placeholder="••••••••" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" required />
                    </div>
                    <div class="flex items-start">
                        <div class="flex items-center h-5">
                            <input id="remember" name="remember" type="checkbox" class="w-4 h-4 border-gray-300 rounded bg-gray-50 focus:ring-3 focus:ring-blue-300 dark:focus:ring-blue-600 dark:ring-offset-gray-800 dark:bg-gray-700 dark:border-gray-600" />
                        </div>
                        <div class="ms-3 text-sm">
                            <label for="remember" class="font-medium text-gray-500 dark:text-gray-400">Nhớ mật khẩu</label>
                        </div>
                        <a href="#" class="ms-auto text-sm font-medium text-red-400 hover:underline dark:text-red-400">Quên mật khẩu?</a>
                    </div>
                    <button name="submit" type="submit" class="w-full px-5 py-3 text-base font-medium text-center text-white bg-red-400 rounded-lg hover:bg-red-700 focus:ring-4 focus:ring-red-400 sm:w-auto dark:bg-red-400 dark:hover:bg-red-700 dark:focus:ring-red-800">Đăng nhập</button>
                    <div class="text-sm font-medium text-gray-900 dark:text-white">
                        Chưa có tài khoản? <a class="text-red-400 hover:underline dark:text-red-500">Tạo tài khoản</a>
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
            <h2
              class="mb-6 text-sm font-semibold text-red-700 uppercase dark:text-white"
            >
              Về chúng tôi
            </h2>
            <ul class="text-red-700 dark:text-gray-700 font-medium">
              <li class="mb-4">
                <a href="#" class="hover:underline">About</a>
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
            <h2
              class="mb-6 text-sm font-semibold text-red-700 uppercase dark:text-white"
            >
              Thông tin
            </h2>
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
                <a href="#" class="hover:underline"
                  >Chính sách khách hàng thân thiết</a
                >
              </li>
            </ul>
          </div>
          <div>
            <h2
              class="mb-6 text-sm font-semibold text-red-700 uppercase dark:text-white"
            >
              Trợ giúp
            </h2>
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
            <h2
              class="mb-6 text-sm font-semibold text-red-700 uppercase dark:text-white"
            >
              Hệ thống cửa hàng
            </h2>
            <ul class="text-red-700 dark:text-gray-400 font-medium">
              <li class="mb-4">
                <a href="#" class="hover:underline">Chi nhánh 1: ABCXYZ</a>
              </li>
            </ul>
          </div>
        </div>
        <div
          class="px-4 py-6 bg-gray-100 dark:bg-gray-700 md:flex md:items-center md:justify-between"
        >
          <span class="text-sm text-gray-500 dark:text-gray-300 sm:text-center"
            >© 2023 <a href="#"></a>. All Rights Reserved.
          </span>
          <div
            class="flex mt-4 sm:justify-center md:mt-0 space-x-5 rtl:space-x-reverse"
          >
            <a
              href="https://www.facebook.com/nguyen.gia.huy.66306"
              class="text-gray-400 hover:text-gray-900 dark:hover:text-white"
            >
              <svg
                class="w-4 h-4"
                aria-hidden="true"
                xmlns="http://www.w3.org/2000/svg"
                fill="currentColor"
                viewBox="0 0 8 19"
              >
                <path
                  fill-rule="evenodd"
                  d="M6.135 3H8V0H6.135a4.147 4.147 0 0 0-4.142 4.142V6H0v3h2v9.938h3V9h2.021l.592-3H5V3.591A.6.6 0 0 1 5.592 3h.543Z"
                  clip-rule="evenodd"
                />
              </svg>
              <span class="sr-only">Facebook page</span>
            </a>
            <a
              href="#"
              class="text-gray-400 hover:text-gray-900 dark:hover:text-white"
            >
              <svg
                class="w-4 h-4"
                aria-hidden="true"
                xmlns="http://www.w3.org/2000/svg"
                fill="currentColor"
                viewBox="0 0 21 16"
              >
                <path
                  d="M16.942 1.556a16.3 16.3 0 0 0-4.126-1.3 12.04 12.04 0 0 0-.529 1.1 15.175 15.175 0 0 0-4.573 0 11.585 11.585 0 0 0-.535-1.1 16.274 16.274 0 0 0-4.129 1.3A17.392 17.392 0 0 0 .182 13.218a15.785 15.785 0 0 0 4.963 2.521c.41-.564.773-1.16 1.084-1.785a10.63 10.63 0 0 1-1.706-.83c.143-.106.283-.217.418-.33a11.664 11.664 0 0 0 10.118 0c.137.113.277.224.418.33-.544.328-1.116.606-1.71.832a12.52 12.52 0 0 0 1.084 1.785 16.46 16.46 0 0 0 5.064-2.595 17.286 17.286 0 0 0-2.973-11.59ZM6.678 10.813a1.941 1.941 0 0 1-1.8-2.045 1.93 1.93 0 0 1 1.8-2.047 1.919 1.919 0 0 1 1.8 2.047 1.93 1.93 0 0 1-1.8 2.045Zm6.644 0a1.94 1.94 0 0 1-1.8-2.045 1.93 1.93 0 0 1 1.8-2.047 1.918 1.918 0 0 1 1.8 2.047 1.93 1.93 0 0 1-1.8 2.045Z"
                />
              </svg>
              <span class="sr-only">Discord community</span>
            </a>
            <a
              href="#"
              class="text-gray-400 hover:text-gray-900 dark:hover:text-white"
            >
              <svg
                class="w-4 h-4"
                aria-hidden="true"
                xmlns="http://www.w3.org/2000/svg"
                fill="currentColor"
                viewBox="0 0 20 17"
              >
                <path
                  fill-rule="evenodd"
                  d="M20 1.892a8.178 8.178 0 0 1-2.355.635 4.074 4.074 0 0 0 1.8-2.235 8.344 8.344 0 0 1-2.605.98A4.13 4.13 0 0 0 13.85 0a4.068 4.068 0 0 0-4.1 4.038 4 4 0 0 0 .105.919A11.705 11.705 0 0 1 1.4.734a4.006 4.006 0 0 0 1.268 5.392 4.165 4.165 0 0 1-1.859-.5v.05A4.057 4.057 0 0 0 4.1 9.635a4.19 4.19 0 0 1-1.856.07 4.108 4.108 0 0 0 3.831 2.807A8.36 8.36 0 0 1 0 14.184 11.732 11.732 0 0 0 6.291 16 11.502 11.502 0 0 0 17.964 4.5c0-.177 0-.35-.012-.523A8.143 8.143 0 0 0 20 1.892Z"
                  clip-rule="evenodd"
                />
              </svg>
              <span class="sr-only">Twitter page</span>
            </a>
            <a
              href="#"
              class="text-gray-400 hover:text-gray-900 dark:hover:text-white"
            >
              <svg
                class="w-4 h-4"
                aria-hidden="true"
                xmlns="http://www.w3.org/2000/svg"
                fill="currentColor"
                viewBox="0 0 20 20"
              >
                <path
                  fill-rule="evenodd"
                  d="M10 .333A9.911 9.911 0 0 0 6.866 19.65c.5.092.678-.215.678-.477 0-.237-.01-1.017-.014-1.845-2.757.6-3.338-1.169-3.338-1.169a2.627 2.627 0 0 0-1.1-1.451c-.9-.615.07-.6.07-.6a2.084 2.084 0 0 1 1.518 1.021 2.11 2.11 0 0 0 2.884.823c.044-.503.268-.973.63-1.325-2.2-.25-4.516-1.1-4.516-4.9A3.832 3.832 0 0 1 4.7 7.068a3.56 3.56 0 0 1 .095-2.623s.832-.266 2.726 1.016a9.409 9.409 0 0 1 4.962 0c1.89-1.282 2.717-1.016 2.717-1.016.366.83.402 1.768.1 2.623a3.827 3.827 0 0 1 1.02 2.659c0 3.807-2.319 4.644-4.525 4.889a2.366 2.366 0 0 1 .673 1.834c0 1.326-.012 2.394-.012 2.72 0 .263.18.572.681.475A9.911 9.911 0 0 0 10 .333Z"
                  clip-rule="evenodd"
                />
              </svg>
              <span class="sr-only">GitHub account</span>
            </a>
            <a
              href="#"
              class="text-gray-400 hover:text-gray-900 dark:hover:text-white"
            >
              <svg
                class="w-4 h-4"
                aria-hidden="true"
                xmlns="http://www.w3.org/2000/svg"
                fill="currentColor"
                viewBox="0 0 20 20"
              >
                <path
                  fill-rule="evenodd"
                  d="M10 0a10 10 0 1 0 10 10A10.009 10.009 0 0 0 10 0Zm6.613 4.614a8.523 8.523 0 0 1 1.93 5.32 20.094 20.094 0 0 0-5.949-.274c-.059-.149-.122-.292-.184-.441a23.879 23.879 0 0 0-.566-1.239 11.41 11.41 0 0 0 4.769-3.366ZM8 1.707a8.821 8.821 0 0 1 2-.238 8.5 8.5 0 0 1 5.664 2.152 9.608 9.608 0 0 1-4.476 3.087A45.758 45.758 0 0 0 8 1.707ZM1.642 8.262a8.57 8.57 0 0 1 4.73-5.981A53.998 53.998 0 0 1 9.54 7.222a32.078 32.078 0 0 1-7.9 1.04h.002Zm2.01 7.46a8.51 8.51 0 0 1-2.2-5.707v-.262a31.64 31.64 0 0 0 8.777-1.219c.243.477.477.964.692 1.449-.114.032-.227.067-.336.1a13.569 13.569 0 0 0-6.942 5.636l.009.003ZM10 18.556a8.508 8.508 0 0 1-5.243-1.8 11.717 11.717 0 0 1 6.7-5.332.509.509 0 0 1 .055-.02 35.65 35.65 0 0 1 1.819 6.476 8.476 8.476 0 0 1-3.331.676Zm4.772-1.462A37.232 37.232 0 0 0 13.113 11a12.513 12.513 0 0 1 5.321.364 8.56 8.56 0 0 1-3.66 5.73h-.002Z"
                  clip-rule="evenodd"
                />
              </svg>
              <span class="sr-only">Dribbble account</span>
            </a>
          </div>
        </div>
      </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/flowbite@2.5.1/dist/flowbite.min.js"></script>
  </body>
</html>
