<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require '../../vendor/autoload.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Lấy dữ liệu từ form và kiểm tra tính hợp lệ
    $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
    $subject = filter_input(INPUT_POST, 'subject', FILTER_SANITIZE_STRING);
    $message = filter_input(INPUT_POST, 'message', FILTER_SANITIZE_STRING);

    if ($email && $subject && $message) {
        $mail = new PHPMailer(true);
        try {
            // Cấu hình server SMTP
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'tanphuchuynh2547@gmail.com'; // Email của bạn
            $mail->Password = 'qngb uioj ryrr hkzt'; // Mật khẩu ứng dụng
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            // Thông tin người gửi và người nhận
            $mail->setFrom($email, $email);
            $mail->addAddress('tanphuchuynh2547@gmail.com', 'Quản trị viên'); // Email của bạn để nhận phản hồi

            // Nội dung email
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = nl2br($message); // Chuyển đổi dòng mới thành HTML <br>

            // Gửi email
            $mail->send();
            echo "<script>alert('Tin nhắn đã được gửi thành công!'); window.location.href = '../index.php';</script>";
        } catch (Exception $e) {
            echo "<script>alert('Gửi tin nhắn thất bại: {$mail->ErrorInfo}'); window.location.href = '../index.php';</script>";
        }
    } else {
        echo "<script>alert('Vui lòng điền đầy đủ và hợp lệ các thông tin!'); window.location.href = '../index.php';</script>";
    }
} else {
    echo "<script>alert('Yêu cầu không hợp lệ.'); window.location.href = '../index.php';</script>";
}
