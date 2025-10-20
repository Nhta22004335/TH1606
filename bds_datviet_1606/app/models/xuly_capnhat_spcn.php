<?php
header('Content-Type: application/json');
ob_start();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once "../../config/database.php";

$response = ['status' => 'error', 'message' => 'Lỗi không xác định'];

try {
    $pdo = ketnoicsdl();
    $id_nguoi_dung = $_SESSION['id_nguoi_dung'] ?? null;
    $action = $_POST['action'] ?? '';

    if ($action === 'update_data') {
        $id_bds = $_POST['id_bds'] ?? null;
        $tieu_de = trim($_POST['tieu_de'] ?? '');
        $mo_ta = trim($_POST['mo_ta'] ?? '');
        $hinh_thuc = strtolower(trim($_POST['hinh_thuc'] ?? ''));
        $gia = filter_var($_POST['gia'] ?? 0, FILTER_VALIDATE_FLOAT);
        $id_danh_muc = $_POST['id_danh_muc'] ?? null;
        $dien_tich_dat = filter_var($_POST['dien_tich_dat'] ?? 0, FILTER_VALIDATE_FLOAT);
        $so_phong_ngu = intval($_POST['so_phong_ngu'] ?? 0);
        $so_phong_tam = intval($_POST['so_phong_tam'] ?? 0);
        $huong_nha = $_POST['huong_nha'] ?? null;
        $ma_tinh_thanh = $_POST['ma_tinh_thanh'] ?? null;
        $dia_chi_day_du = trim($_POST['dia_chi_day_du'] ?? '');

        if (!preg_match('/^[0-9a-fA-F-]{36}$/', $id_bds) || empty($tieu_de) || $gia === false || $gia < 0 || $dien_tich_dat === false || $dien_tich_dat <= 0) {
            $response = ['status' => 'error', 'message' => 'Dữ liệu đầu vào không hợp lệ'];
        } else {
            $pdo->beginTransaction();

            
            

                $sql_bds = "UPDATE bat_dong_san SET 
                            id_danh_muc = :id_danh_muc,
                            dien_tich_dat = :dien_tich_dat,
                            so_phong_ngu = :so_phong_ngu,
                            so_phong_tam = :so_phong_tam,
                            huong_nha = :huong_nha,
                            ma_tinh_thanh = :ma_tinh_thanh,
                            dia_chi_day_du = :dia_chi_day_du
                          WHERE id = :id_bds";
                $stmt_bds = $pdo->prepare($sql_bds);
                $stmt_bds->execute([
                    ':id_danh_muc' => $id_danh_muc,
                    ':dien_tich_dat' => $dien_tich_dat,
                    ':so_phong_ngu' => $so_phong_ngu,
                    ':so_phong_tam' => $so_phong_tam,
                    ':huong_nha' => $huong_nha,
                    ':ma_tinh_thanh' => $ma_tinh_thanh,
                    ':dia_chi_day_du' => $dia_chi_day_du,
                    ':id_bds' => $id_bds
                ]);

                $pdo->commit();
                $response = ['status' => 'success', 'message' => $id_bds];
            }
        
    } elseif ($action === 'upload_image') {
        if (!isset($_FILES['file_anh']) || $_FILES['file_anh']['error'] !== UPLOAD_ERR_OK) {
            $response = ['status' => 'error', 'message' => 'Không có file ảnh hoặc lỗi khi tải lên: ' . ($_FILES['file_anh']['error'] ?? 'Không xác định')];
        } else {
            $id_bds = $_POST['id_bds'] ?? null;
            if (!preg_match('/^[0-9a-fA-F-]{36}$/', $id_bds)) {
                $response = ['status' => 'error', 'message' => 'ID bất động sản không hợp lệ'];
            } else {
                $stmt_check = $pdo->prepare("SELECT id FROM bai_dang WHERE id_bat_dong_san = :id_bds AND id_nguoi_dung = :id_nguoi_dung LIMIT 1");
                $stmt_check->execute([':id_bds' => $id_bds, ':id_nguoi_dung' => $id_nguoi_dung]);
                if (!$stmt_check->fetch(PDO::FETCH_ASSOC)) {
                    $response = ['status' => 'error', 'message' => 'Bạn không có quyền chỉnh sửa bất động sản này'];
                } else {
                    $file = $_FILES['file_anh'];
                    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
                    if (!in_array($file['type'], $allowed_types)) {
                        $response = ['status' => 'error', 'message' => 'Định dạng file không được hỗ trợ. Chỉ chấp nhận JPG, PNG, GIF'];
                    } elseif ($file['size'] > 5 * 1024 * 1024) {
                        $response = ['status' => 'error', 'message' => 'File ảnh quá lớn. Giới hạn tối đa 5MB'];
                    } else {
                        $filename = uniqid() . '_' . preg_replace("/[^A-Za-z0-9.-]/", '', basename($file['name']));
                        $upload_dir = '../../storage/pictures/bds/';
                        $upload_path = $upload_dir . $filename;

                        if (!is_dir($upload_dir)) {
                            mkdir($upload_dir, 0775, true);
                            error_log("Tạo thư mục thành công: " . $upload_dir);
                        }
                        if (!is_writable($upload_dir)) {
                            $response = ['status' => 'error', 'message' => 'Thư mục lưu trữ không có quyền ghi. Vui lòng kiểm tra quyền thư mục: ' . $upload_dir];
                        } else {
                            if (!move_uploaded_file($file['tmp_name'], $upload_path)) {
                                $response = ['status' => 'error', 'message' => 'Lỗi khi lưu file ảnh lên server. Chi tiết: ' . error_get_last()['message']];
                            } else {
                                $pdo->beginTransaction();
                                $sql_img = "INSERT INTO hinh_anh_bds (id, id_bds, url, kich_thuoc, trang_thai, mo_ta, ngay_tao) 
                                            VALUES (gen_random_uuid(), :id_bds, :url, :kich_thuoc, 'binhthuong', 'Ảnh đại diện', CURRENT_TIMESTAMP)";
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
            }
        }
    } else {
        $response = ['status' => 'error', 'message' => 'Hành động không hợp lệ. Giá trị action nhận được: ' . $action];
    }
} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    $response = ['status' => 'error', 'message' => 'Lỗi CSDL: ' . $e->getMessage()];
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    $response = ['status' => 'error', 'message' => 'Lỗi hệ thống: ' . $e->getMessage()];
}

ob_end_clean();
echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
exit;
?>