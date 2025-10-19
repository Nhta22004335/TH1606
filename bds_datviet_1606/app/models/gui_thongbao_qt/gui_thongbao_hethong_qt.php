<?php
// Thiết lập header để trả về JSON
header('Content-Type: application/json');

// Bắt buộc phải khởi động session để sử dụng $_SESSION
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Đường dẫn tương đối đến database.php
require_once "../../../config/database.php"; 

$pdo = ketnoicsdl();
// Lấy dữ liệu JSON từ request body
$data = json_decode(file_get_contents('php://input'), true);

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($data)) {
    echo json_encode(['status' => 'error', 'message' => 'Yêu cầu không hợp lệ.']);
    exit;
}

// =================================================================
// BƯỚC 1: LẤY ID NGƯỜI GỬI (ADMIN) TỪ SESSION
// =================================================================
// Giả định ID người dùng đang đăng nhập được lưu trong $_SESSION['user_id']
$admin_id = $_SESSION['id_nguoi_dung'] ?? null;

if (!$admin_id) {
    echo json_encode(['status' => 'error', 'message' => 'Lỗi: Không tìm thấy ID người gửi. Vui lòng đăng nhập lại.']);
    exit;
}

$title = $data['title'] ?? 'Thông báo hệ thống';
$content = $data['content'] ?? 'Không có nội dung.';
$recipients_id = $data['recipients_id'] ?? [];
$send_to_all = $data['send_to_all'] ?? false;

// Loại thông báo cố định cho thông báo do Admin gửi
$notification_type = 'quantrivien'; 

try {
    // 1. Lấy danh sách ID người nhận cuối cùng (Giữ nguyên)
    $target_ids = [];
    if ($send_to_all) {
        $sql = "SELECT id FROM nguoi_dung";
        $stmt = $pdo->query($sql);
        $target_ids = $stmt->fetchAll(PDO::FETCH_COLUMN);
    } else if (!empty($recipients_id)) {
        $target_ids = $recipients_id;
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Không có người nhận được chọn.']);
        exit;
    }

    if (empty($target_ids)) {
        echo json_encode(['status' => 'success', 'message' => 'Không có người dùng để gửi.', 'sent_count' => 0]);
        exit;
    }

    $pdo->beginTransaction();
    $sent_count = 0;

    // 2. Chuẩn bị câu lệnh INSERT (Đã thêm id_nguoi_gui)
    $sql_insert = "
        INSERT INTO thong_bao (id_nguoi_gui, id_nguoi_dung, loai, tieu_de, noi_dung, trang_thai) 
        VALUES (?, ?, ?, ?, ?, 'chuaxem')
    ";
    $stmt_insert = $pdo->prepare($sql_insert);
    
    // 3. Thực hiện INSERT cho từng người dùng
    foreach ($target_ids as $user_id) {
        // Thứ tự các tham số: 
        // 1: id_nguoi_gui ($admin_id)
        // 2: id_nguoi_dung ($user_id)
        // 3: loai ($notification_type)
        // 4: tieu_de ($title)
        // 5: noi_dung ($content)
        if ($stmt_insert->execute([$admin_id, $user_id, $notification_type, $title, $content])) {
            $sent_count++;
        }
    }

    $pdo->commit();

    echo json_encode([
        'status' => 'success', 
        'message' => 'Đã lưu thông báo vào hộp thoại chat thành công.', 
        'sent_count' => $sent_count
    ]);

} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode(['status' => 'error', 'message' => 'Lỗi CSDL: ' . $e->getMessage()]);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Lỗi hệ thống: ' . $e->getMessage()]);
}
?>