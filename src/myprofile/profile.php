<?php
session_start();

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

// Kiểm tra nếu người dùng đã đăng nhập
if (!isset($_SESSION['username'])) {
    header('Location: ../login/sign_in.php');
    exit;
}

// Biến lưu thông báo
$success_message = "";
$error_message = "";

// Lấy thông tin người dùng từ cơ sở dữ liệu
$user_id = $_SESSION['username'];

// Cập nhật thông tin khi form được submit
if (isset($_POST['submit'])) {
    // Lấy dữ liệu từ form
    $name = $_POST['name'];
    $phone = $_POST['phone'];
    $birthday = $_POST['birthday'];

    // Câu lệnh SQL để cập nhật thông tin
    $stmt = $conn->prepare("UPDATE dangky SET name = :name, phone = :phone, birthday = :birthday WHERE username = :username");
    $stmt->bindParam(':name', $name, PDO::PARAM_STR);
    $stmt->bindParam(':phone', $phone, PDO::PARAM_STR);
    $stmt->bindParam(':birthday', $birthday, PDO::PARAM_STR);
    $stmt->bindParam(':username', $user_id, PDO::PARAM_STR);

    // Thực thi câu lệnh cập nhật
    if ($stmt->execute()) {
        $success_message = 'Cập nhật thông tin thành công!';
        // Tải lại thông tin người dùng sau khi cập nhật thành công
        $stmt = $conn->prepare("SELECT username, name, phone, birthday FROM dangky WHERE username = :username");
        $stmt->bindParam(':username', $user_id, PDO::PARAM_STR);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
    } else {
        $error_message = 'Đã xảy ra lỗi khi cập nhật thông tin';
    }
} else {
    // Lấy thông tin người dùng nếu chưa submit
    $stmt = $conn->prepare("SELECT username, name, phone, birthday FROM dangky WHERE username = :username");
    $stmt->bindParam(':username', $user_id, PDO::PARAM_STR);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="./output.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/flowbite@2.5.1/dist/flowbite.min.css" rel="stylesheet">
    <title>My Profile</title>
</head>
<body>

<main>
<section class="bg-gray-50 dark:bg-gray-900">
    <div class="py-8 px-4 mx-auto max-w-screen-xl lg:py-16 grid lg:grid-cols-2 gap-8 lg:gap-16">
        <div class="flex flex-col justify-center">
            <a href="" class="text-red-400 font-medium text-xl my-2 hover:underline"><p>Giỏ hàng của tôi</p></a>
            <a href="" class="text-red-400 font-medium text-xl my-2 hover:underline"><p>Trạng thái đơn hàng</p></a>
            <a href="./../index.php" class="mt-2 text-red-400 dark:text-blue-500 hover:underline font-medium text-xl inline-flex items-center">Trang chủ 
            </a>
        </div>
        <div>
            <div class="w-full lg:max-w-xl p-6 space-y-8 sm:p-8 bg-white rounded-lg shadow-xl dark:bg-gray-800">
                <div class="w-full max-w-lg mx-auto bg-white rounded-lg shadow-md p-6">
                    <h4 class="text-2xl font-bold text-center text-gray-800 mb-6">Your Profile</h4>

                    <?php if (!empty($success_message)): ?>
                        <div class="text-green-500 my-5 text-center"><?= htmlspecialchars($success_message) ?></div>
                    <?php elseif (!empty($error_message)): ?>
                        <div class="text-red-500 my-5 text-center"><?= htmlspecialchars($error_message) ?></div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="mb-4">
                            <label for="username" class="block text-sm font-medium text-gray-700">User Name*</label>
                            <input id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500" required type="text" disabled>
                        </div>
                        <div class="mb-4">
                            <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
                            <input id="name" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500" required type="text">
                        </div>
                        <div class="mb-4">
                            <label for="phone" class="block text-sm font-medium text-gray-700">Phone</label>
                            <input id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>" class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500" required type="text">
                        </div>

                        <div class="mb-4">
                            <label for="birthday" class="block text-sm font-medium text-gray-700">Birthday</label>
                            <input id="birthday" name="birthday" value="<?php echo htmlspecialchars($user['birthday']); ?>" class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500" type="text">
                        </div>

                        <div class="flex justify-center">
                            <button name="submit" type="submit" class="w-full py-2 px-4 bg-red-400 text-white font-semibold rounded-md shadow-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">Cập nhật tài khoản</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="my_profile flex items-center justify-center min-h-screen bg-gray-100"></section>
</main>

<footer class="bg-red-200 dark:bg-gray-900 mt-40"></footer>

<script src="https://cdn.jsdelivr.net/npm/flowbite@2.5.1/dist/flowbite.min.js"></script>
</body>
</html>
