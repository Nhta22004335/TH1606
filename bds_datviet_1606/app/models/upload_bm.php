<?php
session_start();
header('Content-Type: application/json');
require_once "../../config/database.php"; 
// Hàm trả về phản hồi JSON
function json_response($status, $message, $data = []) {
    echo json_encode(array_merge(['status' => $status, 'message' => $message], $data));
    exit;
}


// 2. Kiểm tra phương thức và ID người dùng/biểu mẫu
// if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
//     json_response('error', 'Phương thức không hợp lệ. Chỉ chấp nhận POST.');
// }

// // Lấy và kiểm tra ID người dùng và ID biểu mẫu
$id_nguoi_dung = $_SESSION['id_nguoi_dung'] ?? null;
$id_bm = $_POST['id_bm'] ?? null;



// if (is_null($id_nguoi_dung) || !is_numeric($id_nguoi_dung)) {
//     json_response('error', 'Bạn chưa đăng nhập hoặc ID người dùng không hợp lệ.');
// }

// if (is_null($id_bm) || !is_numeric($id_bm)) {
//     json_response('error', 'ID biểu mẫu không hợp lệ.');
// }

// 3. Xử lý Upload File
if (!isset($_FILES['tep_dk']) || $_FILES['tep_dk']['error'] !== UPLOAD_ERR_OK) {
    // Chi tiết lỗi upload
    $upload_errors = [
        UPLOAD_ERR_INI_SIZE   => 'Kích thước file quá lớn (quá giới hạn PHP).',
        UPLOAD_ERR_FORM_SIZE  => 'Kích thước file quá lớn (quá giới hạn form).',
        UPLOAD_ERR_PARTIAL    => 'File chỉ được upload một phần.',
        UPLOAD_ERR_NO_FILE    => 'Vui lòng chọn file.',
        UPLOAD_ERR_NO_TMP_DIR => 'Thiếu thư mục tạm trên server.',
        UPLOAD_ERR_CANT_WRITE => 'Không ghi được file vào đĩa.',
        UPLOAD_ERR_EXTENSION  => 'Lỗi do extension PHP dừng upload.',
    ];
    $error_code = $_FILES['tep_dk']['error'] ?? UPLOAD_ERR_NO_FILE;
    $message = $upload_errors[$error_code] ?? 'Lỗi upload không xác định.';
    json_response('error', $message);
}


$file = $_FILES['tep_dk'];
// Đường dẫn lưu trữ: ../../../storage/documents/
// __DIR__ là thư mục hiện tại (models). Cần đi lùi 3 cấp để đến thư mục gốc của storage
$upload_dir = __DIR__ . '/../../storage/documents/'; 
$allowed_types = ['application/pdf', 'image/jpeg', 'image/png']; // Chỉ cho phép PDF, JPG, PNG
$max_size = 5 * 1024 * 1024; // Giới hạn 5MB


// Tạo thư mục nếu chưa tồn tại
if (!is_dir($upload_dir)) {
    // Tạo thư mục với quyền 0777 (Nếu hệ thống cho phép)
    mkdir($upload_dir, 0777, true); 
}

// Kiểm tra loại tệp (MIME Type)
if (!in_array($file['type'], $allowed_types)) {
    json_response('error', 'Loại tệp không hợp lệ. Chỉ chấp nhận PDF, JPG, PNG. (File type: ' . $file['type'] . ')');
}

// Kiểm tra kích thước tệp
if ($file['size'] > $max_size) {
    json_response('error', 'Kích thước tệp vượt quá 5MB.');
}

// Tạo tên file duy nhất (đảm bảo an toàn và không bị ghi đè)
$file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
// Sử dụng uniqid() kết hợp với thời gian để tạo tên duy nhất
$unique_filename = uniqid('bm_', true) . '.' . strtolower($file_extension); 
$destination = $upload_dir . $unique_filename;

// Di chuyển tệp đã upload
if (!move_uploaded_file($file['tmp_name'], $destination)) {
    json_response('error', 'Lỗi khi lưu trữ tệp trên server.');
}

// 4. Cập nhật đường dẫn file vào CSDL
try {
    $pdo = ketnoicsdl();
    
    // Kiểm tra quyền: Đảm bảo người dùng hiện tại (ben_ban) có quyền cập nhật biểu mẫu này
    // Điều này ngăn người khác upload file cho biểu mẫu của người khác.
    $check_sql = "SELECT id FROM bieu_mau WHERE id = :id_bm AND ben_ban = :id_nguoi_dung";
    $check_stmt = $pdo->prepare($check_sql);
    $check_stmt->execute([':id_bm' => $id_bm, ':id_nguoi_dung' => $id_nguoi_dung]);
    
    if ($check_stmt->rowCount() === 0) {
        // Xóa file vừa upload nếu không có quyền
        if (file_exists($destination)) {
            unlink($destination);
        }
        json_response('error', 'Bạn không có quyền cập nhật biểu mẫu này.');
    }
    
    // Thực hiện cập nhật
    $update_sql = "UPDATE bieu_mau SET tep_dk = :filename, ngay_cn = NOW() WHERE id = :id_bm";
    $update_stmt = $pdo->prepare($update_sql);
    $update_stmt->execute([':filename' => $unique_filename, ':id_bm' => $id_bm]);

    // 5. Trả về phản hồi thành công
    json_response('success', 'Upload tệp đính kèm thành công và đã cập nhật CSDL.', ['filename' => $unique_filename]);

} catch (PDOException $e) {
    // Xóa file vừa upload nếu lỗi CSDL
    if (file_exists($destination)) {
        unlink($destination);
    }
    json_response('error', 'Lỗi CSDL: Không thể cập nhật thông tin biểu mẫu.');
} catch (Exception $e) {
    // Xóa file vừa upload nếu có lỗi chung
    if (file_exists($destination)) {
        unlink($destination);
    }
    json_response('error', 'Lỗi hệ thống không xác định.');
}

?>