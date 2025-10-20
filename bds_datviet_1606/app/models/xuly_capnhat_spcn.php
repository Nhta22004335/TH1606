<?php
// BƯỚC 1: CẤU HÌNH LỖI (Quan trọng để ngăn lỗi PHP Warning rò rỉ ra JSON)
ini_set('display_errors', 0); // Tắt hiển thị lỗi ra output
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING); // Vẫn ghi log nhưng không hiển thị Warnings/Notices

header('Content-Type: application/json');
ob_start(); // Bắt đầu buffer

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once "../../config/database.php";

$response = ['status' => 'error', 'message' => 'Lỗi không xác định'];

try {
    $pdo = ketnoicsdl();
    $id_nguoi_dung = $_SESSION['id_nguoi_dung'] ?? null;
    $action = $_POST['action'] ?? '';

    if (empty($id_nguoi_dung)) {
        throw new Exception("Người dùng chưa đăng nhập.");
    }

    // =========================================================
    // ACTION: CẬP NHẬT DỮ LIỆU (bat_dong_san)
    // ... (Giữ nguyên logic update_data vì không liên quan đến lỗi upload ảnh)
    // =========================================================
    if ($action === 'update_data') {
    $id_bds = $_POST['id_bds'] ?? null;
    
    // Hàm hỗ trợ chuyển đổi chuỗi rỗng thành NULL (cho các trường NUMERIC)
    function clean_numeric_input($value) {
        // Nếu giá trị rỗng (bao gồm cả chuỗi rỗng) hoặc là 0
        if (empty($value) && $value !== 0 && $value !== '0') {
            return NULL; 
        }
        // Ép kiểu float (sử dụng NULL nếu không hợp lệ)
        return filter_var($value, FILTER_VALIDATE_FLOAT);
    }
    
    // Lấy và lọc dữ liệu từ form (CHÚ TRỌNG CHUYỂN ĐỔI CHUỖI RỖNG THÀNH NULL)
    $id_danh_muc = $_POST['id_danh_muc'] ?? null;
    $dien_tich_dat = clean_numeric_input($_POST['dien_tich_dat'] ?? 0); 
    $dien_tich_su_dung = clean_numeric_input($_POST['dien_tich_su_dung'] ?? NULL); // Có thể NULL
    $mat_tien = clean_numeric_input($_POST['mat_tien'] ?? NULL); // Có thể NULL
    $duong_vao = clean_numeric_input($_POST['duong_vao'] ?? NULL); // Có thể NULL
    $vi_do = clean_numeric_input($_POST['vi_do'] ?? NULL); // Có thể NULL
    $kinh_do = clean_numeric_input($_POST['kinh_do'] ?? NULL); // Có thể NULL
    
    // Cấu trúc nhà (thường set về 0 nếu trống)
    $so_tang = intval($_POST['so_tang'] ?? 0);
    $so_phong_ngu = intval($_POST['so_phong_ngu'] ?? 0);
    $so_phong_tam = intval($_POST['so_phong_tam'] ?? 0);

    // Các trường khác
    $huong_nha = $_POST['huong_nha'] ?? null;
    $thong_tin_phap_ly = trim($_POST['thong_tin_phap_ly'] ?? '');
    $ma_tinh_thanh = $_POST['ma_tinh_thanh'] ?? null;
    $dia_chi_day_du = trim($_POST['dia_chi_day_du'] ?? '');
    
    
    // Kiểm tra ràng buộc bắt buộc (đã được làm sạch)
    // dien_tich_dat phải có giá trị và > 0.
    if (!preg_match('/^[0-9a-fA-F-]{36}$/', $id_bds) || $dien_tich_dat === false || $dien_tich_dat <= 0 || empty($dia_chi_day_du)) {
        $response = ['status' => 'error', 'message' => 'Dữ liệu đầu vào không hợp lệ hoặc thiếu thông tin bắt buộc (ID, Diện tích đất, Địa chỉ).'];
    } else {
        $pdo->beginTransaction();

        // 1. Kiểm tra quyền sở hữu BĐS (giữ nguyên)
        $stmt_check_owner = $pdo->prepare("SELECT id FROM bat_dong_san WHERE id = :id_bds AND id_chu_so_huu = :id_nguoi_dung LIMIT 1");
        $stmt_check_owner->execute([':id_bds' => $id_bds, ':id_nguoi_dung' => $id_nguoi_dung]);
        if (!$stmt_check_owner->fetch(PDO::FETCH_ASSOC)) {
            $response = ['status' => 'error', 'message' => 'Bạn không có quyền chỉnh sửa bất động sản này.'];
            $pdo->rollBack();
        } else {
            // 2. Cập nhật BĐS (Giữ nguyên câu lệnh SQL)
            $sql_bds = "UPDATE bat_dong_san SET 
                        id_danh_muc = :id_danh_muc,
                        dien_tich_dat = :dien_tich_dat,
                        dien_tich_su_dung = :dien_tich_su_dung,
                        mat_tien = :mat_tien,
                        duong_vao = :duong_vao,
                        huong_nha = :huong_nha,
                        so_tang = :so_tang,
                        so_phong_ngu = :so_phong_ngu,
                        so_phong_tam = :so_phong_tam,
                        thong_tin_phap_ly = :thong_tin_phap_ly,
                        ma_tinh_thanh = :ma_tinh_thanh,
                        dia_chi_day_du = :dia_chi_day_du,
                        vi_do = :vi_do,
                        kinh_do = :kinh_do,
                        ngay_cap_nhat = CURRENT_TIMESTAMP
                      WHERE id = :id_bds";
            
            $stmt_bds = $pdo->prepare($sql_bds);
            $stmt_bds->execute([
                // Đã làm sạch ở bước trên, giá trị truyền vào sẽ là float hoặc NULL
                ':id_danh_muc' => $id_danh_muc,
                ':dien_tich_dat' => $dien_tich_dat,
                ':dien_tich_su_dung' => $dien_tich_su_dung,
                ':mat_tien' => $mat_tien,
                ':duong_vao' => $duong_vao,
                ':huong_nha' => $huong_nha,
                ':so_tang' => $so_tang,
                ':so_phong_ngu' => $so_phong_ngu,
                ':so_phong_tam' => $so_phong_tam,
                ':thong_tin_phap_ly' => $thong_tin_phap_ly,
                ':ma_tinh_thanh' => $ma_tinh_thanh,
                ':dia_chi_day_du' => $dia_chi_day_du,
                ':vi_do' => $vi_do,
                ':kinh_do' => $kinh_do,
                ':id_bds' => $id_bds
            ]);

            $pdo->commit();
            $response = ['status' => 'success', 'message' => 'Cập nhật BĐS thành công!', 'id' => $id_bds];
        }
    }
}
    // =========================================================
    // ACTION: UPLOAD HÌNH ẢNH (hinh_anh_bds)
    // =========================================================
    elseif ($action === 'upload_image') {
        $id_bds = $_POST['id_bds'] ?? null;
        
        if (!isset($_FILES['file_anh']) || $_FILES['file_anh']['error'] !== UPLOAD_ERR_OK) {
            $response = ['status' => 'error', 'message' => 'Không có file ảnh hoặc lỗi khi tải lên: ' . ($_FILES['file_anh']['error'] ?? 'Không xác định')];
        } elseif (!preg_match('/^[0-9a-fA-F-]{36}$/', $id_bds)) {
            $response = ['status' => 'error', 'message' => 'ID bất động sản không hợp lệ'];
        } else {
            // 1. Kiểm tra quyền sở hữu BĐS (dựa trên id_chu_so_huu)
            $stmt_check = $pdo->prepare("SELECT id FROM bat_dong_san WHERE id = :id_bds AND id_chu_so_huu = :id_nguoi_dung LIMIT 1");
            $stmt_check->execute([':id_bds' => $id_bds, ':id_nguoi_dung' => $id_nguoi_dung]);
            
            if (!$stmt_check->fetch(PDO::FETCH_ASSOC)) {
                $response = ['status' => 'error', 'message' => 'Bạn không có quyền thêm ảnh cho bất động sản này'];
            } else {
                $file = $_FILES['file_anh'];
                
                
                $filename = uniqid() . '_' . preg_replace("/[^A-Za-z0-9._-]/", '', basename($file['name']));
                $upload_dir = '../../storage/pictures/bds/';
                $upload_path = $upload_dir . $filename;

                if (!is_dir($upload_dir)) {
                    if (!mkdir($upload_dir, 0775, true) && !is_dir($upload_dir)) {
                        throw new \RuntimeException(sprintf('Directory "%s" was not created', $upload_dir));
                    }
                }
                
                if (!is_writable($upload_dir)) {
                    $response = ['status' => 'error', 'message' => 'Thư mục lưu trữ không có quyền ghi.'];
                } elseif (!move_uploaded_file($file['tmp_name'], $upload_path)) {
                    $response = ['status' => 'error', 'message' => 'Lỗi khi lưu file ảnh lên server.'];
                } else {
                    // 2. Thêm vào bảng hinh_anh_bds
                    $pdo->beginTransaction();
                    $sql_img = "INSERT INTO hinh_anh_bds (id_bds, url, kich_thuoc, trang_thai, mo_ta) 
                                 VALUES (:id_bds, :url, :kich_thuoc, 'binhthuong', 'Ảnh tải lên')";
                    
                    $stmt_img = $pdo->prepare($sql_img);
                    $stmt_img->execute([
                        ':id_bds' => $id_bds,
                        ':url' => $filename,
                        ':kich_thuoc' => round($file['size'] / 1024 / 1024, 2)
                    ]);

                    $pdo->commit();
                    $response = ['status' => 'success', 'message' => 'Tải ảnh thành công', 'filename' => $filename];
                }
            }
        }
    } 
    // =========================================================
    // ACTION KHÔNG HỢP LỆ
    // =========================================================
    else {
        $response = ['status' => 'error', 'message' => 'Hành động không hợp lệ.'];
    }
} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    $response = ['status' => 'error', 'message' => 'Lỗi CSDL: ' . $e->getMessage()];
} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    $response = ['status' => 'error', 'message' => 'Lỗi hệ thống: ' . $e->getMessage()];
}

// BƯỚC CUỐI: KIỂM TRA VÀ XÓA BUFFER. BẢO VỆ OUTPUT.
$output = ob_get_clean();

if (!empty($output)) {
    error_log("Non-JSON output detected in xuly_capnhat_spcn.php: " . $output);
    
    // Ghi đè response nếu buffer chứa lỗi không phải JSON
    if ($response['status'] !== 'success') {
         $response['message'] = $response['message'] . " (Lỗi chi tiết có thể do PHP Warning/Notice, vui lòng kiểm tra log server)";
         $response['debug_output'] = substr($output, 0, 255); 
    }
}

echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
exit;
?>