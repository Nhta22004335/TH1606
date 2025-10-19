<?php
// File: xl_dang_sp.php

// Khởi tạo SESSION
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Giả định file database.php có hàm ketnoicsdl() trả về đối tượng PDO
require_once "../../../config/database.php";

// Kiểm tra đăng nhập
if (!isset($_SESSION['id_nguoi_dung'])) {
    header("Location: ../auth/dangnhap.php");
    exit;
}

$pdo = ketnoicsdl();
$id_nguoi_dung = $_SESSION['id_nguoi_dung'] ?? '';

// Hàm redirect với thông báo
function redirectWithMessage($message, $type = 'success') {
    $safe_message = addslashes(str_replace(["\r", "\n"], ' ', $message)); 
    $url = 'trangchu.php?page=../moi_gioi/sp_canhan';
    echo "<script>alert('{$safe_message}'); window.location.href='{$url}';</script>";
    exit;
}

// Thư mục upload
$upload_dir = "../../../storage/pictures/bds/";
if (!is_dir($upload_dir)) {
    if (!mkdir($upload_dir, 0777, true)) {
        redirectWithMessage("Lỗi hệ thống: Không thể tạo thư mục lưu trữ media.", 'error');
    }
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Lấy dữ liệu CÓ SẴN từ FORM
    $tieu_de        = trim($_POST['tieu_de'] ?? '');
    $mo_ta          = trim($_POST['mo_ta'] ?? '');
    
    // THÔNG SỐ & VỊ TRÍ
    $id_danh_muc    = trim($_POST['id_danh_muc'] ?? ''); // LẤY GIÁ TRỊ THỰC TẾ TỪ FORM
    $dien_tich_dat  = (float)($_POST['dien_tich_dat'] ?? 0); 
    $so_phong_ngu   = (int)($_POST['so_phong_ngu'] ?? 0);
    $so_phong_tam   = (int)($_POST['so_phong_tam'] ?? 0);
    $huong_nha      = trim($_POST['huong_nha'] ?? '');
    $thong_tin_phap_ly = trim($_POST['thong_tin_phap_ly'] ?? '');
    $ma_tinh_thanh  = trim($_POST['ma_tinh_thanh'] ?? ''); 
    $dia_chi_day_du = trim($_POST['dia_chi_day_du'] ?? '');
    
    
    // GIÁ TRỊ MẶC ĐỊNH CHO CÁC CỘT KHÔNG CÓ TRONG FORM
    $ma_quan_huyen = NULL;
    $ma_phuong_xa = NULL;
    $dien_tich_su_dung = NULL;
    $mat_tien = NULL;
    $duong_vao = NULL;
    $so_tang = 0;
    $vi_do = NULL;
    $kinh_do = NULL;
    $dac_diem_chi_tiet = '{}'; // JSONB mặc định là empty object

    // VALIDATION cơ bản (BỔ SUNG kiểm tra id_danh_muc)
    if (!$tieu_de || !$mo_ta || !$id_danh_muc || !$ma_tinh_thanh || $dien_tich_dat <= 0 || !$dia_chi_day_du) {
        redirectWithMessage("Vui lòng điền đầy đủ và chính xác các trường bắt buộc (Tiêu đề, Mô tả, Loại BĐS, Diện tích đất, Khu vực, Địa chỉ).", 'error');
    }
    
    // Kiểm tra có hình ảnh được tải lên không
    if (empty($_FILES['hinh_anh']['name'][0])) {
        redirectWithMessage("Vui lòng tải lên ít nhất một hình ảnh cho sản phẩm.", 'error');
    }
    

    $pdo->beginTransaction();
    $media_paths = ['images' => [], 'video' => null]; 
    
    try {
        // Cập nhật SQL INSERT để bao gồm tất cả các cột mới
        $sql = "INSERT INTO bat_dong_san 
                (id_chu_so_huu, id_danh_muc, trang_thai,
                 dia_chi_day_du, ma_tinh_thanh, ma_quan_huyen, ma_phuong_xa, vi_do, kinh_do, 
                 dien_tich_dat, dien_tich_su_dung, mat_tien, duong_vao, huong_nha, 
                 so_tang, so_phong_ngu, so_phong_tam, thong_tin_phap_ly, dac_diem_chi_tiet)
                VALUES 
                (:id_chu_so_huu, :id_danh_muc, 'chuaduyet',
                 :dia_chi_day_du, :ma_tinh_thanh, :ma_quan_huyen, :ma_phuong_xa, :vi_do, :kinh_do, 
                 :dien_tich_dat, :dien_tich_su_dung, :mat_tien, :duong_vao, :huong_nha, 
                 :so_tang, :so_phong_ngu, :so_phong_tam, :thong_tin_phap_ly, :dac_diem_chi_tiet)
                RETURNING id"; 
        
        $stmt = $pdo->prepare($sql);

        $stmt->execute([
            ':id_chu_so_huu' => $id_nguoi_dung, 
            ':id_danh_muc' => $id_danh_muc, // Sử dụng giá trị thực tế từ form
           
          
            ':dia_chi_day_du' => $dia_chi_day_du,
            ':ma_tinh_thanh' => $ma_tinh_thanh,
            ':ma_quan_huyen' => $ma_quan_huyen,
            ':ma_phuong_xa' => $ma_phuong_xa,
            ':vi_do' => $vi_do,
            ':kinh_do' => $kinh_do,
            ':dien_tich_dat' => $dien_tich_dat,
            ':dien_tich_su_dung' => $dien_tich_su_dung,
            ':mat_tien' => $mat_tien,
            ':duong_vao' => $duong_vao,
            ':huong_nha' => $huong_nha,
            ':so_tang' => $so_tang,
            ':so_phong_ngu' => $so_phong_ngu,
            ':so_phong_tam' => $so_phong_tam,
            ':thong_tin_phap_ly' => $thong_tin_phap_ly,
            ':dac_diem_chi_tiet' => $dac_diem_chi_tiet
        ]);
    
        $id_bds = $stmt->fetchColumn(); 
        if (!$id_bds) throw new Exception("Không lấy được ID sản phẩm mới. (DB Error)");

        // Logic Upload nhiều hình ảnh
        $max_images = 5; 
        $count = 0;
        
        if (isset($_FILES['hinh_anh']) && is_array($_FILES['hinh_anh']['tmp_name'])) {
            foreach ($_FILES['hinh_anh']['tmp_name'] as $i => $tmp_name) {
                if ($count >= $max_images) break; 
                
                if ($_FILES['hinh_anh']['error'][$i] === UPLOAD_ERR_OK) {
                    $finfo = finfo_open(FILEINFO_MIME_TYPE);
                    $mime_type = finfo_file($finfo, $tmp_name);
                    finfo_close($finfo);

                    if (strpos($mime_type, 'image/') === 0) {
                        $ext = pathinfo($_FILES['hinh_anh']['name'][$i], PATHINFO_EXTENSION);
                        $filename = "img_{$id_bds}_" . uniqid() . "." . $ext;
                        
                        if (!move_uploaded_file($tmp_name, $upload_dir.$filename)) {
                            throw new Exception("Upload ảnh thứ ".($i+1)." thất bại khi di chuyển file.");
                        }
                        $media_paths['images'][] = $filename;
                        $count++;
                    } 
                } else if ($_FILES['hinh_anh']['error'][$i] !== UPLOAD_ERR_NO_FILE) {
                     throw new Exception("Lỗi khi tải ảnh thứ ".($i+1).": Mã lỗi ".$_FILES['hinh_anh']['error'][$i]);
                }
            }
        }
        
        if (empty($media_paths['images'])) {
            throw new Exception("Không có hình ảnh hợp lệ nào được tải lên.");
        }
        
        // Chèn hình ảnh vào CSDL (BỔ SUNG cột is_dai_dien)
        // Giả định bảng hinh_anh_bds có cột is_dai_dien
        $stmt_img = $pdo->prepare("INSERT INTO hinh_anh_bds (id_bds, url) VALUES (:id_bds, :url)");
        foreach ($media_paths['images'] as $i => $img) {
            $stmt_img->execute([
                ':id_bds' => $id_bds, 
                ':url' => $img
               
            ]);
        }
        
        $pdo->commit();
        redirectWithMessage("Đăng sản phẩm thành công! Sản phẩm đang chờ duyệt.");

    } catch (Exception $e) {
        $pdo->rollBack();

        // Xóa file đã upload nếu lỗi
        foreach ($media_paths['images'] as $img) {
            if (file_exists($upload_dir.$img)) @unlink($upload_dir.$img);
        }

        error_log("Lỗi đăng BĐS: ".$e->getMessage());
        redirectWithMessage("Lỗi: Không thể đăng sản phẩm. Vui lòng thử lại. Chi tiết kỹ thuật: ".$e->getMessage(), 'error');
    }
} else {
    redirectWithMessage("Phương thức yêu cầu không hợp lệ.", 'error');
}
?>