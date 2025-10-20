<?php
// Thiết lập header để trả về JSON
header('Content-Type: application/json');

// Đường dẫn tương đối đến database.php
require_once "../../../config/database.php"; 

// 1. Tích hợp thư viện email của bạn
require_once '../../../config/email.php';

$pdo = ketnoicsdl();
// Lấy dữ liệu JSON từ request body
$data = json_decode(file_get_contents('php://input'), true);

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($data)) {
    echo json_encode(['status' => 'error', 'message' => 'Yêu cầu không hợp lệ.']);
    exit;
}

$title = $data['title'] ?? 'Thông báo từ Quản trị viên';
$content = $data['content'] ?? 'Không có nội dung.';
$recipients_id = $data['recipients_id'] ?? [];
$send_to_all = $data['send_to_all'] ?? false;

try {
    // 2. Khởi tạo Mailer
    $mailer = createmailer();
    $mailer->isHTML(true); // Gửi dưới dạng HTML

    // 3. Lấy thông tin Email của người nhận
    if ($send_to_all) {
        $sql = "SELECT email, id FROM nguoi_dung WHERE email IS NOT NULL AND email != ''";
        $stmt = $pdo->query($sql);
    } else if (!empty($recipients_id)) {
        // Tạo chuỗi placeholders cho IN clause 
        $placeholders = implode(',', array_fill(0, count($recipients_id), '?'));
        $sql = "SELECT email, id FROM nguoi_dung WHERE id IN ({$placeholders}) AND email IS NOT NULL AND email != ''";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($recipients_id);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Không có người nhận được chọn.']);
        exit;
    }

    $recipients = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $sent_count = 0;
    $failed_emails = [];

    // 4. Lặp và gửi Email cho từng người nhận
    foreach ($recipients as $user) {
        $recipient_email = $user['email'];
        
        // Luôn xóa các địa chỉ cũ trước khi thêm địa chỉ mới
        $mailer->clearAddresses(); 
        
        // Thêm địa chỉ người nhận
        $mailer->addAddress($recipient_email); 
        
        // Thiết lập Tiêu đề và Nội dung
        $mailer->Subject = $title;
        
        // Tạo nội dung HTML cho email
        $html_body = "
            <html>
            <body style='font-family: Arial, sans-serif; line-height: 1.6;'>
                <h2 style='color: #4CAF50;'>{$title}</h2>
                <p>Kính gửi Quý khách hàng,</p>
                <div style='border-left: 3px solid #007bff; padding-left: 15px; margin: 20px 0; background-color: #f8f9fa; padding: 10px;'>
                    <p>{$content}</p>
                </div>
                <p>Trân trọng cảm ơn,</p>
                <p>Ban quản trị Hệ thống.</p>
            </body>
            </html>
        ";
        $mailer->Body = $html_body;

        // Tiến hành gửi
        if ($mailer->send()) {
            $sent_count++;
        } else {
            $failed_emails[] = $recipient_email;
            // Ghi log lỗi chi tiết nếu cần: $mailer->ErrorInfo
        }
    }

    $message = "Đã hoàn tất việc gửi Email. Thành công: {$sent_count} thư.";
    if (!empty($failed_emails)) {
        $message .= " Thất bại: " . count($failed_emails) . " thư. Vui lòng kiểm tra log.";
    }

    echo json_encode([
        'status' => 'success', 
        'message' => $message, 
        'sent_count' => $sent_count,
        'failed_emails' => $failed_emails
    ]);

} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Lỗi CSDL: ' . $e->getMessage()]);
} catch (Exception $e) {
    // Lỗi từ PHPMailer hoặc hàm createmailer()
    echo json_encode(['status' => 'error', 'message' => 'Lỗi Email Server/Hệ thống: ' . $e->getMessage()]);
}
?>