<?php
// Cấu hình ban đầu
require_once "../../../config/database.php";
$pdo = ketnoicsdl();

// Bắt đầu phiên (cần ở đầu file)
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
    // Nếu là Form POST, chuyển hướng
    header("Location: ../../views/moi_gioi/sp_canhan.php?status=error&message=Thiếu thông tin người dùng hoặc ID sản phẩm.");
    exit;
}


// =================================================================
// 1. XỬ LÝ UPLOAD ẢNH (Dùng cho Request AJAX)
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
            $sqlImg = "INSERT INTO hinh_anh_bds (id_bds, url, ngay_tao) VALUES (:id_bds, :url, NOW())";
            $stmtImg = $pdo->prepare($sqlImg);
            $stmtImg->execute([':id_bds' => $id_bds, ':url' => $newName]);

            echo json_encode(['status' => 'success', 'message' => 'Upload ảnh thành công!', 'filename' => $newName]);
            exit;
        } catch (PDOException $e) {
            if (file_exists($filePath)) unlink($filePath); 
            echo json_encode(['status' => 'error', 'message' => 'Lỗi DB khi lưu ảnh.']);
            exit;
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Lỗi di chuyển file lên server.']);
        exit;
    }
}


// =================================================================
// 2. XỬ LÝ FORM CẬP NHẬT THÔNG TIN (Dùng cho Request Form POST thông thường)
// =================================================================

// 1. Lấy và làm sạch dữ liệu
$tieu_de = trim($_POST['tieu_de'] ?? '');
$hinh_thuc = $_POST['hinh_thuc'] ?? '';
$loai = $_POST['loai'] ?? '';
$gia = max(0, (int)($_POST['gia'] ?? 0));
$dien_tich = max(0.1, (float)($_POST['dien_tich'] ?? 0));
$khu_vuc = trim($_POST['khu_vuc'] ?? '');
$dia_chi = trim($_POST['dia_chi'] ?? '');
$mo_ta = trim($_POST['mo_ta'] ?? '');


// 2. Cập nhật vào DB
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
                ngay_cap_nhat = NOW()
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

    // Chuyển hướng thành công (POST/Redirect/GET Pattern)
    header("Location: ../../views/moi_gioi/sua_san_pham.php?id=$id_bds&status=success&message=Cập nhật thông tin thành công.");
    exit;

} catch (PDOException $e) {
    header("Location: ../../views/moi_gioi/sua_san_pham.php?id=$id_bds&status=error&message=Lỗi cơ sở dữ liệu khi cập nhật thông tin.");
    exit;
}

?>