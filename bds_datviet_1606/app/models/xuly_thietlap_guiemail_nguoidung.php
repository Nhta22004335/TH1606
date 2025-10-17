<?php
header('Content-Type: application/json'); 

require_once "../../config/email.php";

$response = [
    'status' => 'error',
    'message' => 'Đã có lỗi xảy ra.'
];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $tieu_de = $_POST['tieu_de'] ?? '';
    $noi_dung = $_POST['noi_dung'] ?? '';
    $email_nguoi_nhan = $_POST['email_nguoi_nhan'] ?? '';

    if (empty($tieu_de) || empty($noi_dung) || empty($email_nguoi_nhan)) {
        $response['message'] = 'Vui lòng nhập đầy đủ tiêu đề và nội dung.';
        echo json_encode($response);
        exit;
    }

    if (!filter_var($email_nguoi_nhan, FILTER_VALIDATE_EMAIL)) {
        $response['message'] = 'Địa chỉ email người nhận không hợp lệ.';
        echo json_encode($response);
        exit;
    }

    try {
        $mailer = createmailer();
        $mailer->addAddress($email_nguoi_nhan);
        $mailer->Subject = $tieu_de;
        $mailer->Body = nl2br(htmlspecialchars($noi_dung));
        $mailer->AltBody = htmlspecialchars($noi_dung);

        $mailer->send();


        $response['status'] = 'success';
        $response['message'] = "Đã gửi email thành công đến " . htmlspecialchars($email_nguoi_nhan);

    } catch (Exception $e) {

        $response['message'] = "Gửi email thất bại. Lỗi: " . $mailer->ErrorInfo;
    }

} else {
    $response['message'] = 'Phương thức không hợp lệ.';
}


echo json_encode($response);
exit;
?>