<?php
    require __DIR__.'/../vendor/PHPMailer-master/src/Exception.php';
    require  __DIR__.'/../vendor/PHPMailer-master/src/PHPMailer.php';
    require  __DIR__.'/../vendor/PHPMailer-master/src/SMTP.php';

    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception;

function createmailer(): PHPMailer {
    $mail = new PHPMailer(true);
    
    try {
        // Cấu hình cơ bản
        $mail->isSMTP();
        $mail->CharSet = 'UTF-8';
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'monkeystore.hotro.4335@gmail.com'; // Thay bằng email của bạn
        $mail->Password = 'ofkv yzxx ovkt jgqw'; // Mật khẩu ứng dụng (App Password Gmail)
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // Người gửi mặc định
        $mail->setFrom('monkeystore.hotro.4335@gmail.com', 'MonkeyStore Support');
        $mail->addReplyTo('monkeystore.hotro.4335@gmail.com', 'MonkeyStore Support');

    } catch (Exception $e) {
        echo 'Lỗi khi tạo đối tượng mailer: ', $e->getMessage();
    }

    return $mail;
}
?>