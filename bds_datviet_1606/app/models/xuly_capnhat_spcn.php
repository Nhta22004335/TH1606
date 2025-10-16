<?php
// Cấu hình ban đầu
require_once "../../config/database.php";
$pdo = ketnoicsdl();

// // Bắt đầu phiên (cần ở đầu file)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$id_nguoi_dung = $_SESSION['id_nguoi_dung'] ?? null;
// Lấy ID từ POST cho Form Chính, hoặc từ POST cho AJAX
$id_bds = $_POST['id'] ?? $_POST['id_bds'] ?? null; 

// --- Kiểm tra bảo mật cơ bản ---
if (!$id_nguoi_dung || !$id_bds) {
    // Nếu là AJAX, trả về JSON lỗi
    if (isset($_FILES['file_anh'])) {
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'Lỗi xác thực hoặc thiếu ID BĐS.']);
        exit;
    }
    // Nếu là Form POST, chuyển hướng (Chỉ dừng script nếu thiếu ID quan trọng)
    exit;
}


// =================================================================
// 1. XỬ LÝ UPLOAD ẢNH (Dùng cho Request AJAX)
// (ĐÃ THÊM THÔNG BÁO LỖI CHI TIẾT HƠN VÀO PHẦN move_uploaded_file)
// =================================================================
if (isset($_FILES['file_anh'])) {
    header('Content-Type: application/json');

    $file = $_FILES['file_anh'];
    $uploadDir = '../../storage/pictures/bds/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

    $fileName = basename($file['name']);
    $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

    if (!in_array($ext, $allowed)) {
        echo json_encode(['status' => 'error', 'message' => 'Định dạng file không hợp lệ.']);
        exit;
    }
    
    // Tên file mới
    $newName = uniqid('bds_') . '.' . $ext;
    $filePath = $uploadDir . $newName;

    if (move_uploaded_file($file['tmp_name'], $filePath)) {
        try {
            // Kiểm tra lại quyền sở hữu BĐS trước khi insert ảnh (Bảo mật)
            $stmtCheck = $pdo->prepare("SELECT id FROM bat_dong_san WHERE id = :id_bds AND id_nguoi_dung = :id_nguoi_dung");
            $stmtCheck->execute([':id_bds' => $id_bds, ':id_nguoi_dung' => $id_nguoi_dung]);
            if ($stmtCheck->rowCount() === 0) {
                if (file_exists($filePath)) unlink($filePath); // Xóa file nếu không có quyền
                echo json_encode(['status' => 'error', 'message' => 'Bạn không có quyền upload ảnh cho sản phẩm này.']);
                exit;
            }

            // Lưu vào bảng hinh_anh_bds
            // LƯU Ý: Nếu bảng của bạn là PostgreSQL và dùng UUID, đảm bảo connection $pdo hỗ trợ tốt kiểu dữ liệu này.
            $sqlImg = "INSERT INTO hinh_anh_bds (id_bds, url, ngay_tao) VALUES (:id_bds, :url, NOW())";
            $stmtImg = $pdo->prepare($sqlImg);
            $stmtImg->execute([':id_bds' => $id_bds, ':url' => $newName]);

            echo json_encode(['status' => 'success', 'message' => 'Upload ảnh thành công!', 'filename' => $newName]);
            exit;
        } catch (PDOException $e) {
            if (file_exists($filePath)) unlink($filePath);
            echo json_encode(['status' => 'error', 'message' => 'Lỗi DB khi lưu ảnh: ' . $e->getMessage()]); // Thêm chi tiết lỗi DB
            exit;
        }
    } else {
        // Cung cấp thông tin chi tiết hơn về lỗi di chuyển file
        $php_error_code = $file['error'];
        $error_message = 'Lỗi di chuyển file lên server. Mã lỗi PHP: ' . $php_error_code;

        if ($php_error_code === UPLOAD_ERR_INI_SIZE || $php_error_code === UPLOAD_ERR_FORM_SIZE) {
            $error_message .= ' (Kích thước file quá lớn)';
        } elseif ($php_error_code === UPLOAD_ERR_NO_TMP_DIR) {
            $error_message .= ' (Thiếu thư mục tạm)';
        } elseif ($php_error_code !== UPLOAD_ERR_OK) {
             $error_message .= ' (Lỗi không xác định hoặc lỗi quyền ghi)';
        }

        echo json_encode(['status' => 'error', 'message' => $error_message]);
        exit;
    }
}


// =================================================================
// 2. XỬ LÝ FORM CẬP NHẬT THÔNG TIN
// (ĐÃ THÊM LỆNH EXIT; BẮT BUỘC SAU CHUYỂN HƯỚNG)
// =================================================================
$tieu_de = trim($_POST['tieu_de'] ?? '');
$hinh_thuc = $_POST['hinh_thuc'] ?? 'chuacapnhat';
$loai = $_POST['loai'] ?? 'chuacapnhat';
$gia = max(0, (float)($_POST['gia'] ?? 0));
$dien_tich = max(0.1, (float)($_POST['dien_tich'] ?? 0));
$khu_vuc = trim($_POST['khu_vuc'] ?? '');
$dia_chi = trim($_POST['dia_chi'] ?? '');
$mo_ta = trim($_POST['mo_ta'] ?? '');

// Nếu giá trị hinh_thuc hoặc loai không nằm trong constraint, đưa về 'chuacapnhat'
$allowed_hinh_thuc = ['ban', 'chothue', 'chuacapnhat'];
$allowed_loai = ['canho', 'nhapho', 'datnen', 'bietthu', 'chuacapnhat'];

if (!in_array($hinh_thuc, $allowed_hinh_thuc)) $hinh_thuc = 'chuacapnhat';
if (!in_array($loai, $allowed_loai)) $loai = 'chuacapnhat';

try {
    $sql = "UPDATE bat_dong_san SET 
                tieu_de = :tieu_de,
                hinh_thuc = :hinh_thuc,
                loai = :loai,
                gia = :gia,
                dien_tich = :dien_tich,
                khu_vuc = :khu_vuc,
                dia_chi = :dia_chi,
                mo_ta = :mo_ta,
                ngay_dang = NOW()
            WHERE id = :id AND id_nguoi_dung = :id_nguoi_dung"; 

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':tieu_de' => $tieu_de,
        ':hinh_thuc' => $hinh_thuc,
        ':loai' => $loai,
        ':gia' => $gia,
        ':dien_tich' => $dien_tich,
        ':khu_vuc' => $khu_vuc,
        ':dia_chi' => $dia_chi,
        ':mo_ta' => $mo_ta,
        ':id' => $id_bds,
        ':id_nguoi_dung' => $id_nguoi_dung
    ]);
if ($stmt->rowCount() > 0) {
    // Cập nhật thành công → thông báo và quay về trang sản phẩm cá nhân
    echo "<script>
        alert('Cập nhật sản phẩm thành công!');
        window.location.href = '../views/quan_ly/trangchu.php?page=../moi_gioi/sp_canhan';
    </script>";
    exit;
} else {
    // Cập nhật thất bại
    echo "<script>
        alert('Cập nhật thất bại. Kiểm tra ID sản phẩm và quyền sở hữu.');
        window.history.back();
    </script>";
    exit;
}


} catch (PDOException $e) {
    // Xử lý lỗi DB
    echo "<script>
        alert('Lỗi khi cập nhật: {$e->getMessage()}');
        window.history.back();
        </script>";
    exit; // BẮT BUỘC DỪNG SCRIPT
}