<?php
// Đặt header để đảm bảo trình duyệt biết rằng phản hồi là JSON
header('Content-Type: application/json');

// Tắt hoàn toàn hiển thị lỗi PHP trên môi trường Production
// Để khắc phục lỗi "headers already sent"
// Tùy chọn: Bạn có thể bật lại trên môi trường Dev/Staging để debug
// error_reporting(0); 
// ini_set('display_errors', 0); 


// Giả định bạn đã có SESSION bắt đầu và ID người dùng
session_start(); 
// Đảm bảo sử dụng tên biến session CHÍNH XÁC như trong file gốc của bạn
$user_id = $_SESSION['id_nguoi_dung'] ?? null; 

// Kiểm tra phiên đăng nhập
if (!$user_id) {
    // Không sử dụng sendResponse ở đây vì nó gọi http_response_code() sau.
    http_response_code(401);
    echo json_encode(["status" => "error", "message" => "Không có quyền truy cập. Vui lòng đăng nhập."]);
    exit;
}

// Đường dẫn kết nối CSDL (điều chỉnh cho đúng)
require_once "../../config/database.php";

// Hàm phản hồi JSON và thoát script
function sendResponse($status, $message, $httpCode = 200) {
    // WARNING: Lỗi này xảy ra do output đã được tạo (thông báo lỗi/deprecated)
    // Nếu bạn không thể tắt hiển thị lỗi từ cấu hình server, 
    // bạn cần đảm bảo không có output nào trước sendResponse.
    if (!headers_sent()) {
        http_response_code($httpCode);
    }
    echo json_encode(["status" => $status, "message" => $message]);
    exit;
}

// 2. Kết nối CSDL
try {
    $pdo = ketnoicsdl();
} catch (PDOException $e) {
    sendResponse("error", "Lỗi kết nối CSDL: " . $e->getMessage(), 500);
}

// 3. Xử lý yêu cầu POST
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    
    // Sửa lỗi Deprecated: Dùng isset() hoặc toán tử ba ngôi để tránh truyền null vào trim()
    $so_dt = trim($_POST['so_dt'] ?? '');
    $trang_thai = trim($_POST['trang_thai'] ?? ''); 
    
    // Khởi tạo mảng lưu trữ các cột cần cập nhật và tham số
    $update_fields = [];
    $params = [':id' => $user_id];
    
    // Lấy thông tin người dùng hiện tại để kiểm tra AVT/Ảnh bìa cũ
    $stmt = $pdo->prepare("SELECT avt, anh_bia, so_dt, trang_thai FROM nguoi_dung WHERE id = :id");
    $stmt->execute([':id' => $user_id]);
    $current_info = $stmt->fetch(PDO::FETCH_ASSOC);

    // --- Cập nhật Số điện thoại ---
    // Kiểm tra nếu so_dt được gửi lên (không phải chuỗi rỗng) VÀ khác giá trị hiện tại
    if ($so_dt !== '' && $so_dt !== $current_info['so_dt']) {
        // Kiểm tra ràng buộc CHECK constraint: số điện thoại phải là chuỗi 1-11 số hoặc 'chuacapnhat'
        if (preg_match('/^[0-9]{1,11}$/', $so_dt) || $so_dt === 'chuacapnhat') {
            $update_fields[] = "so_dt = :so_dt";
            $params[':so_dt'] = $so_dt;
        } else {
            sendResponse("error", "Số điện thoại không hợp lệ (cần 1-11 chữ số).", 400);
        }
    }

    // --- Cập nhật Trạng thái ---
    // Kiểm tra nếu trang_thai được gửi lên (không phải chuỗi rỗng) VÀ khác giá trị hiện tại
    if ($trang_thai !== '' && $trang_thai !== $current_info['trang_thai']) {
        // Kiểm tra ràng buộc CHECK constraint: ('danghoatdong','chuakichhoat','khoa')
        $valid_trang_thai = ['danghohoatdong', 'chuakichhoat', 'khoa'];
        if (in_array($trang_thai, $valid_trang_thai)) {
            $update_fields[] = "trang_thai = :trang_thai";
            $params[':trang_thai'] = $trang_thai;
        } else {
            sendResponse("error", "Giá trị trạng thái không hợp lệ. Vui lòng kiểm tra lại.", 400);
        }
    }
    
    // --- Thiết lập giới hạn Upload File ---
    $upload_dir = '../../storage/pictures/avt/'; 
    $allowed_exts = ['jpg', 'jpeg', 'png', 'gif'];
    $max_size = 5 * 1024 * 1024; // 5MB

    // Hàm kiểm tra và upload file
    function handleFileUpload($fileKey, $currentFileName, $isAvatar, $upload_dir, $user_id, $allowed_exts, $max_size) {
        global $pdo;
        $file = $_FILES[$fileKey];
        $defaultFile = $isAvatar ? 'avt.png' : 'anhbia.jpg';
        $prefix = $isAvatar ? 'avt_' : 'bia_';

        if ($file['error'] !== UPLOAD_ERR_OK) {
            return null; // Không có file hoặc lỗi upload khác (ví dụ: kích thước vượt quá post_max_size)
        }

        $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($file_ext, $allowed_exts)) {
            sendResponse("error", "File $fileKey: Chỉ chấp nhận ảnh JPG, PNG, GIF.", 400);
        }
        if ($file['size'] > $max_size) {
            sendResponse("error", "File $fileKey: Kích thước file không được quá 5MB.", 400);
        }

        $new_file_name = $prefix . $user_id . "_" . time() . '.' . $file_ext;
        $dest_path = $upload_dir . $new_file_name;

        if (move_uploaded_file($file['tmp_name'], $dest_path)) {
            // Xóa file cũ (trừ file mặc định)
            if ($currentFileName !== $defaultFile && file_exists($upload_dir . $currentFileName)) {
                unlink($upload_dir . $currentFileName);
            }
            return $new_file_name;
        } else {
            sendResponse("error", "Lỗi khi di chuyển file $fileKey. Kiểm tra quyền ghi thư mục!", 500);
        }
    }

    // Xử lý AVT
    if (isset($_FILES['avt']) && $_FILES['avt']['size'] > 0) {
        $new_avt_name = handleFileUpload('avt', $current_info['avt'], true, $upload_dir, $user_id, $allowed_exts, $max_size);
        if ($new_avt_name) {
            $update_fields[] = "avt = :avt";
            $params[':avt'] = $new_avt_name;
        }
    }

    // Xử lý Ảnh bìa
    if (isset($_FILES['anh_bia']) && $_FILES['anh_bia']['size'] > 0) {
        $new_bia_name = handleFileUpload('anh_bia', $current_info['anh_bia'], false, $upload_dir, $user_id, $allowed_exts, $max_size);
        if ($new_bia_name) {
            $update_fields[] = "anh_bia = :anh_bia";
            $params[':anh_bia'] = $new_bia_name;
        }
    }

    // --- Thực hiện Truy vấn Cập nhật ---
    if (empty($update_fields)) {
        sendResponse("warning", "Không có thông tin nào được thay đổi.", 200);
    }

    $sql = "UPDATE nguoi_dung SET " . implode(", ", $update_fields) . " WHERE id = :id";
    
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        
        sendResponse("success", "Cập nhật thông tin người dùng thành công!");
    } catch (PDOException $e) {
        sendResponse("error", "Lỗi CSDL khi cập nhật: " . $e->getMessage(), 500);
    }
    
} else {
    sendResponse("error", "Phương thức không hợp lệ.", 405);
}
?>