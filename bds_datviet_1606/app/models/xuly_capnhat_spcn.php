<?php
// FILE: ../../models/xuly_capnhat_spcn.php

// ==============================================================================
// 1. CẤU HÌNH PHẢN HỒI JSON & CHẶN LỖI PHP HIỂN THỊ
// ==============================================================================
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL); 
// Ghi lỗi vào log file riêng để kiểm tra:
ini_set('log_errors', 1);
ini_set('error_log', dirname(__FILE__) . '/phperror_ajax.log'); 

// Thiết lập header phản hồi là JSON
header('Content-Type: application/json');

// Hàm chuẩn hóa phản hồi lỗi JSON
function returnError(string $message, int $httpCode = 500) {
    // Ghi log lỗi để debug
    error_log("[AJAX Error] HTTP $httpCode - $message");
    // Thiết lập mã phản hồi HTTP
    http_response_code($httpCode);
    // Trả về JSON hợp lệ
    echo json_encode(['status' => 'error', 'message' => $message]);
    exit();
}

// Hàm chuẩn hóa phản hồi thành công JSON
function returnSuccess(string $message, array $extraData = []) {
    http_response_code(200);
    echo json_encode(array_merge(['status' => 'success', 'message' => $message], $extraData));
    exit();
}

// ==============================================================================
// 2. KẾT NỐI CSDL & KHỞI TẠO BIẾN CHUNG
// ==============================================================================
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

try {
    require_once "../../../config/database.php"; 
    $pdo = ketnoicsdl();
} catch (PDOException $e) {
    returnError('Lỗi kết nối CSDL: ' . $e->getMessage(), 500);
} catch (Exception $e) {
    returnError('Lỗi khởi tạo hệ thống.', 500);
}

// Lấy dữ liệu POST
$action = $_POST['action'] ?? null;
// id_bds là ID của bảng bat_dong_san (CẦN THIẾT cho cả 3 action)
$id_bds = $_POST['id_bds'] ?? null;
$id_nguoi_dung = $_SESSION['id_nguoi_dung'] ?? null;

// 3. KIỂM TRA BẢO MẬT CƠ BẢN VÀ ĐẦU VÀO
if (!$id_nguoi_dung) {
    returnError('Bạn chưa đăng nhập hoặc phiên đã hết hạn.', 401);
}
if (!preg_match('/^[0-9a-fA-F-]{36}$/', $id_bds)) {
    returnError('ID Bất động sản không hợp lệ.', 400);
}

// 4. KIỂM TRA QUYỀN SỞ HỮU BẮT BUỘC (Lấy cả ID tin đăng)
try {
    $stmt_check = $pdo->prepare("SELECT id FROM bai_dang WHERE id_bat_dong_san = :id_bds AND id_nguoi_dung = :id_nguoi_dung");
    $stmt_check->execute([':id_bds' => $id_bds, ':id_nguoi_dung' => $id_nguoi_dung]);
    $tin_dang = $stmt_check->fetch(PDO::FETCH_ASSOC);

    if (!$tin_dang) {
        returnError('Bạn không có quyền chỉnh sửa Tin đăng này.', 403);
    }
    $id_tin_dang = $tin_dang['id']; // ID của bảng bai_dang
} catch (PDOException $e) {
    returnError('Lỗi kiểm tra quyền sở hữu CSDL.', 500);
}


// ==============================================================================
// 5. XỬ LÝ UPLOAD ẢNH (action: upload_image) 
// ==============================================================================
if ($action === 'upload_image') {
    $target_dir = "../../../storage/pictures/bds/";
    $file_anh = $_FILES['file_anh'] ?? null;

    if (!$file_anh || $file_anh['error'] !== UPLOAD_ERR_OK) {
        $error_code = $file_anh['error'] ?? 'Không có file';
        // Chi tiết lỗi upload
        $error_message = match($error_code) {
            UPLOAD_ERR_INI_SIZE => 'File quá lớn (giới hạn PHP).',
            UPLOAD_ERR_FORM_SIZE => 'File quá lớn (giới hạn FORM).',
            UPLOAD_ERR_NO_FILE => 'Không có file được chọn.',
            default => "Lỗi upload file (Mã: $error_code)."
        };
        returnError("Lỗi upload: $error_message", 400);
    }
    
    // Kiểm tra và tạo thư mục
    if (!is_dir($target_dir) && !mkdir($target_dir, 0777, true)) {
        returnError('❌ Không thể tạo thư mục lưu trữ ảnh. Vui lòng kiểm tra CHMOD.', 500);
    }

    $file_extension = strtolower(pathinfo($file_anh["name"], PATHINFO_EXTENSION));
    $new_file_name = uniqid('bds_', true) . '.' . $file_extension;
    $target_file = $target_dir . $new_file_name;

    try {
        // Kiểm tra MIME TYPE an toàn
        $allowed_types_mime = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $mime_type = $file_anh['type']; 
        if (function_exists('mime_content_type')) {
            $mime_type = mime_content_type($file_anh['tmp_name']);
        }
        
        if (!in_array($mime_type, $allowed_types_mime)) {
             throw new Exception("Chỉ chấp nhận file JPG, PNG, GIF & WEBP. (Loại file phát hiện: " . $mime_type . ").");
        }
        
        if ($file_anh["size"] > 10 * 1024 * 1024) { // 10MB
            throw new Exception("Kích thước file quá lớn. Tối đa 10MB.");
        }
        
        // Di chuyển file
        if (!move_uploaded_file($file_anh["tmp_name"], $target_file)) {
            throw new Exception("Lỗi khi di chuyển file. Kiểm tra lại quyền CHMOD thư mục.");
        }

        // LƯU THÔNG TIN ẢNH VÀO CSDL
        $pdo->beginTransaction();
        
        $sql = "INSERT INTO hinh_anh_bds (id_bds, url, kich_thuoc, ngay_tao) 
                VALUES (:id_bds, :url, :kich_thuoc, CURRENT_TIMESTAMP)";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':id_bds' => $id_bds,
            ':url' => $new_file_name,
            ':kich_thuoc' => $file_anh["size"] / 1024 
        ]);
        
        $last_insert_id = $pdo->lastInsertId(); // Lấy ID ảnh vừa thêm

        $pdo->commit();

        // PHẢN HỒI THÀNH CÔNG JSON
        returnSuccess('Ảnh đã được tải lên thành công!', [
            'filename' => $new_file_name,
            'image_id' => $last_insert_id, // Trả về ID ảnh để client xử lý DOM
            'url' => $new_file_name
        ]);

    } catch (Exception $e) { 
        if ($pdo->inTransaction()) { $pdo->rollBack(); }
        // Xóa file đã upload nếu có lỗi
        if (isset($target_file) && file_exists($target_file)) { unlink($target_file); }

        returnError('Lỗi upload. Chi tiết: ' . $e->getMessage(), 500);
    }
    
    exit();
}

// ==============================================================================
// 6. XỬ LÝ XÓA ẢNH (action: delete_image) 
// ==============================================================================
elseif ($action === 'delete_image') {
    $id_anh = $_POST['id_anh'] ?? null;

    if (!preg_match('/^[0-9a-fA-F-]{36}$/', $id_anh)) {
        returnError('ID ảnh không hợp lệ.', 400);
    }
    
    try {
        $pdo->beginTransaction();

        // 1. Lấy URL ảnh để xóa file
        $stmt_get_url = $pdo->prepare("SELECT url FROM hinh_anh_bds WHERE id = :id_anh AND id_bds = :id_bds");
        $stmt_get_url->execute([':id_anh' => $id_anh, ':id_bds' => $id_bds]);
        $image = $stmt_get_url->fetch(PDO::FETCH_ASSOC);

        if (!$image) {
            returnError('Ảnh không tồn tại hoặc không thuộc sản phẩm này.', 404);
        }

        $file_url = $image['url'];
        $file_path = "../../../storage/pictures/bds/" . $file_url;
        
        // 2. Xóa record trong CSDL
        $stmt_delete = $pdo->prepare("DELETE FROM hinh_anh_bds WHERE id = :id_anh AND id_bds = :id_bds");
        $stmt_delete->execute([':id_anh' => $id_anh, ':id_bds' => $id_bds]);

        $pdo->commit();

        // 3. Xóa file vật lý (Sau khi xóa DB để đảm bảo không bị lỗi mất file)
        if (file_exists($file_path)) {
            unlink($file_path);
        }

        returnSuccess('Ảnh đã được xóa thành công.');

    } catch (PDOException $e) {
        if ($pdo->inTransaction()) { $pdo->rollBack(); }
        returnError('Lỗi CSDL khi xóa ảnh.', 500);
    } catch (Exception $e) {
        returnError('Lỗi hệ thống khi xóa ảnh: ' . $e->getMessage(), 500);
    }
    exit();
}


// ==============================================================================
// 7. XỬ LÝ CẬP NHẬT THÔNG TIN CHÍNH (action: update_data) 
// ==============================================================================
elseif ($action === 'update_data') {
    // 7a. Lấy và làm sạch dữ liệu
    $tieu_de = trim($_POST['tieu_de'] ?? '');
    $mo_ta = trim($_POST['mo_ta'] ?? '');
    $hinh_thuc = strtolower(trim($_POST['hinh_thuc'] ?? '')); // chuẩn hóa hình thức
    
    // Ép kiểu dữ liệu số và làm sạch
    // Dùng FILTER_VALIDATE_FLOAT để kiểm tra hợp lệ trước khi dùng giá trị
    $gia = filter_var($_POST['gia'] ?? 0, FILTER_VALIDATE_FLOAT);
    $dien_tich_dat = filter_var($_POST['dien_tich_dat'] ?? 0, FILTER_VALIDATE_FLOAT);
    $so_phong_ngu = intval($_POST['so_phong_ngu'] ?? 0);
    $so_phong_tam = intval($_POST['so_phong_tam'] ?? 0);
    
    $id_danh_muc = $_POST['id_danh_muc'] ?? null;
    $huong_nha = $_POST['huong_nha'] ?? null;
    $ma_tinh_thanh = $_POST['ma_tinh_thanh'] ?? null;
    $dia_chi_day_du = trim($_POST['dia_chi_day_du'] ?? '');
    
    // 7b. Kiểm tra dữ liệu bắt buộc
    if (empty($tieu_de) || empty($mo_ta) || $gia === false || $gia < 0 || $dien_tich_dat === false || $dien_tich_dat <= 0 || !preg_match('/^[0-9a-fA-F-]{36}$/', $id_danh_muc)) {
        returnError('Vui lòng điền đầy đủ Tiêu đề, Mô tả, Giá, Diện tích hợp lệ và chọn Danh mục.', 400);
    }
    
    // 7c. Bắt đầu cập nhật
    try {
        $pdo->beginTransaction();
        
        // Cập nhật bảng bat_dong_san (id_bds đã có)
        $sql_bds = "UPDATE bat_dong_san SET
                    id_danh_muc = :id_danh_muc,
                    dien_tich_dat = :dien_tich_dat,
                    so_phong_ngu = :so_phong_ngu,
                    so_phong_tam = :so_phong_tam,
                    huong_nha = :huong_nha,
                    ma_tinh_thanh = :ma_tinh_thanh,
                    dia_chi_day_du = :dia_chi_day_du,
                    ngay_cap_nhat = CURRENT_TIMESTAMP
                    WHERE id = :id_bds";

        $stmt_bds_update = $pdo->prepare($sql_bds);
        $stmt_bds_update->execute([
            ':id_danh_muc' => $id_danh_muc,
            ':dien_tich_dat' => $dien_tich_dat,
            ':so_phong_ngu' => $so_phong_ngu,
            ':so_phong_tam' => $so_phong_tam,
            ':huong_nha' => $huong_nha,
            ':ma_tinh_thanh' => $ma_tinh_thanh,
            ':dia_chi_day_du' => $dia_chi_day_du,
            ':id_bds' => $id_bds
        ]);

        // Cập nhật bảng bai_dang (id_tin_dang đã có)
        $sql_baidang = "UPDATE bai_dang SET
                        hinh_thuc = :hinh_thuc,
                        tieu_de = :tieu_de,
                        mo_ta = :mo_ta,
                        gia = :gia,
                        -- Đặt lại trạng thái thành 'chuaduyet' khi có thay đổi nội dung
                        trang_thai = 'chuaduyet',
                        ngay_cap_nhat = CURRENT_TIMESTAMP
                        WHERE id = :id_tin_dang";

        $stmt_baidang_update = $pdo->prepare($sql_baidang);
        $stmt_baidang_update->execute([
            ':hinh_thuc' => $hinh_thuc,
            ':tieu_de' => $tieu_de,
            ':mo_ta' => $mo_ta,
            ':gia' => $gia,
            ':id_tin_dang' => $id_tin_dang
        ]);

        $pdo->commit();
        
        // PHẢN HỒI THÀNH CÔNG JSON
        returnSuccess('✅ Cập nhật thông tin BĐS thành công. Tin đăng đã được đặt lại trạng thái "Chờ duyệt".');

    } catch (PDOException $e) {
        if ($pdo->inTransaction()) { $pdo->rollBack(); }
        error_log("Cập nhật Data Error: " . $e->getMessage());
        returnError('Lỗi CSDL khi cập nhật. Vui lòng kiểm tra log PHP.', 500);
    }
    
    exit();
}

// ==============================================================================
// 8. TRƯỜNG HỢP KHÔNG CÓ ACTION HỢP LỆ
// ==============================================================================
else {
    returnError('Hành động yêu cầu không hợp lệ.', 400);
}