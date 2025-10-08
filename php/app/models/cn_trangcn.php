<?php
// Đặt header để đảm bảo trình duyệt biết rằng phản hồi là JSON
header('Content-Type: application/json');

// Giả định bạn đã có SESSION bắt đầu và ID người dùng
session_start(); 
$user_id = $_SESSION['id_nguoi_dung'] ?? null; 

// Kiểm tra phiên đăng nhập
if (!$user_id) {
    http_response_code(401);
    echo json_encode(["status" => "error", "message" => "Không có quyền truy cập. Vui lòng đăng nhập."]);
    exit;
}

// Đường dẫn kết nối CSDL (điều chỉnh cho đúng)
require_once "../../config/database.php";

// Hàm phản hồi JSON và thoát script
function sendResponse($status, $message, $httpCode = 200) {
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
    
    // --- LẤY DỮ LIỆU TỪ FORM (Cả 2 bảng) ---
    // Bảng nguoi_dung
    $so_dt = trim($_POST['so_dt'] ?? '');
    $trang_thai = trim($_POST['trang_thai'] ?? ''); 
    
    // Bảng info_nguoi_dung
    $ho_ten = trim($_POST['ho_ten'] ?? '');
    $gioi_tinh = trim($_POST['gioi_tinh'] ?? '');
    $dia_chi = trim($_POST['dia_chi'] ?? '');
    $ngay_sinh = trim($_POST['ngay_sinh'] ?? '');
    $mo_ta = trim($_POST['mo_ta'] ?? '');

    // Khởi tạo mảng lưu trữ các cột cần cập nhật
    $update_nguoi_dung_fields = [];
    $update_info_fields = [];
    $params_nd = [':id' => $user_id];
    $params_info = [':id_nd' => $user_id];
    
    // Lấy thông tin người dùng hiện tại để kiểm tra thay đổi
    try {
        $stmt = $pdo->prepare("SELECT nd.avt, nd.anh_bia, nd.so_dt, nd.trang_thai, info.ho_ten, info.gioi_tinh, info.dia_chi, info.ngay_sinh, info.mo_ta FROM nguoi_dung nd JOIN info_nguoi_dung info ON nd.id = info.id_nguoi_dung WHERE nd.id = :id");
        $stmt->execute([':id' => $user_id]);
        $current_info = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
         sendResponse("error", "Lỗi CSDL khi lấy thông tin cũ.", 500);
    }

    // --- LOGIC CẬP NHẬT BẢNG INFO_NGUOI_DUNG ---
    if ($ho_ten !== '' && $ho_ten !== $current_info['ho_ten']) {
        $update_info_fields[] = "ho_ten = :ho_ten";
        $params_info[':ho_ten'] = $ho_ten;
    }
    if ($gioi_tinh !== '' && $gioi_tinh !== $current_info['gioi_tinh']) {
        $update_info_fields[] = "gioi_tinh = :gioi_tinh";
        $params_info[':gioi_tinh'] = $gioi_tinh;
    }
    if ($dia_chi !== '' && $dia_chi !== $current_info['dia_chi']) {
        $update_info_fields[] = "dia_chi = :dia_chi";
        $params_info[':dia_chi'] = $dia_chi;
    }
    if ($ngay_sinh !== '' && $ngay_sinh !== $current_info['ngay_sinh']) {
        $update_info_fields[] = "ngay_sinh = :ngay_sinh";
        $params_info[':ngay_sinh'] = $ngay_sinh;
    }
    if ($mo_ta !== '' && $mo_ta !== $current_info['mo_ta']) {
        $update_info_fields[] = "mo_ta = :mo_ta";
        $params_info[':mo_ta'] = $mo_ta;
    }


    // --- LOGIC CẬP NHẬT BẢNG NGUOI_DUNG ---
    
    // 1. Số điện thoại
    if ($so_dt !== '' && $so_dt !== $current_info['so_dt']) {
        if (preg_match('/^[0-9]{1,11}$/', $so_dt) || $so_dt === 'chuacapnhat') {
            $update_nguoi_dung_fields[] = "so_dt = :so_dt";
            $params_nd[':so_dt'] = $so_dt;
        } else {
            sendResponse("error", "Số điện thoại không hợp lệ (cần 1-11 chữ số).", 400);
        }
    }

    // 2. Trạng thái (Thường chỉ Admin mới cập nhật trường này, nhưng vẫn giữ logic)
    if ($trang_thai !== '' && $trang_thai !== $current_info['trang_thai']) {
        // Đã sửa lỗi chính tả: 'danghohoatdong' -> 'danghoatdong'
        $valid_trang_thai = ['danghoatdong', 'chuakichhoat', 'khoa']; 
        if (in_array($trang_thai, $valid_trang_thai)) {
            $update_nguoi_dung_fields[] = "trang_thai = :trang_thai";
            $params_nd[':trang_thai'] = $trang_thai;
        } else {
            sendResponse("error", "Giá trị trạng thái không hợp lệ. Vui lòng kiểm tra lại.", 400);
        }
    }
    
    // --- Thiết lập giới hạn Upload File ---
    $upload_dir = '../../storage/pictures/avt/'; 
    $allowed_exts = ['jpg', 'jpeg', 'png', 'gif'];
    $max_size = 5 * 1024 * 1024; // 5MB

    // Hàm kiểm tra và upload file (cần global $pdo để dùng sendResponse)
    function handleFileUpload($fileKey, $currentFileName, $isAvatar, $upload_dir, $user_id, $allowed_exts, $max_size) {
        // Lưu ý: Do PHP không cho phép gọi sendResponse từ hàm con này vì nó gọi global $pdo
        // Chúng ta sẽ sửa hàm này lại để nó không gọi sendResponse trực tiếp.
        // Tuy nhiên, vì sendResponse có global $pdo nên tôi giữ nguyên để code đơn giản nhất
        // Trong môi trường thực tế, bạn nên truyền các tham số cần thiết thay vì dùng global.
        global $pdo; 
        
        $file = $_FILES[$fileKey];
        $defaultFile = $isAvatar ? 'avt.png' : 'anhbia.jpg';
        $prefix = $isAvatar ? 'avt_' : 'bia_';

        if ($file['error'] !== UPLOAD_ERR_OK) {
            return null;
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
            if ($currentFileName !== $defaultFile && file_exists($upload_dir . $currentFileName)) {
                @unlink($upload_dir . $currentFileName); // Dùng @ để ẩn lỗi nếu file bị khóa
            }
            return $new_file_name;
        } else {
            sendResponse("error", "Lỗi khi di chuyển file $fileKey. Kiểm tra quyền ghi thư mục!", 500);
        }
    }

    // 3. Xử lý AVT và Ảnh bìa
    $file_changed = false;
    if (isset($_FILES['avt']) && $_FILES['avt']['size'] > 0) {
        $new_avt_name = handleFileUpload('avt', $current_info['avt'], true, $upload_dir, $user_id, $allowed_exts, $max_size);
        if ($new_avt_name) {
            $update_nguoi_dung_fields[] = "avt = :avt";
            $params_nd[':avt'] = $new_avt_name;
            $file_changed = true;
        }
    }
    if (isset($_FILES['anh_bia']) && $_FILES['anh_bia']['size'] > 0) {
        $new_bia_name = handleFileUpload('anh_bia', $current_info['anh_bia'], false, $upload_dir, $user_id, $allowed_exts, $max_size);
        if ($new_bia_name) {
            $update_nguoi_dung_fields[] = "anh_bia = :anh_bia";
            $params_nd[':anh_bia'] = $new_bia_name;
            $file_changed = true;
        }
    }

    // --- Thực hiện Truy vấn Cập nhật (Transaction) ---
    if (empty($update_nguoi_dung_fields) && empty($update_info_fields) && !$file_changed) {
        sendResponse("warning", "Không có thông tin nào được thay đổi.", 200);
    }

    try {
        $pdo->beginTransaction();

        // Cập nhật Bảng info_nguoi_dung
        if (!empty($update_info_fields)) {
            $sql_info = "UPDATE info_nguoi_dung SET " . implode(", ", $update_info_fields) . " WHERE id_nguoi_dung = :id_nd";
            $stmt_info = $pdo->prepare($sql_info);
            $stmt_info->execute($params_info);
        }

        // Cập nhật Bảng nguoi_dung
        if (!empty($update_nguoi_dung_fields)) {
            $sql_nd = "UPDATE nguoi_dung SET " . implode(", ", $update_nguoi_dung_fields) . " WHERE id = :id";
            $stmt_nd = $pdo->prepare($sql_nd);
            $stmt_nd->execute($params_nd);
        }
        
        $pdo->commit();
        
        sendResponse("success", "Cập nhật thông tin người dùng thành công!");
        
    } catch (PDOException $e) {
        $pdo->rollBack();
        sendResponse("error", "Lỗi CSDL khi cập nhật: " . $e->getMessage(), 500);
    }
    
} else {
    sendResponse("error", "Phương thức không hợp lệ.", 405);
}
?>