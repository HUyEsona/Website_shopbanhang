<?php
session_start();
session_destroy(); // Hủy session
header('Location: ./../index.php'); // Chuyển hướng về trang chủ sau khi đăng xuất
exit();
?>