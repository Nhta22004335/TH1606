<?php
// Tên file: xl_dang_sp.php (Đã điều chỉnh logic và cấu trúc CSDL)

// Khởi tạo SESSION (Giả định đã được gọi ở môi trường lớn hơn)
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once "../../../config/database.php";

if (!isset($_SESSION['id_nguoi_dung'])) {
    header("Location: ../auth/dangnhap.php"); // Đảm bảo chuyển hướng đúng
    exit;
}

$pdo = ketnoicsdl();
$id_nguoi_dung = $_SESSION['id_nguoi_dung'] ?? '';

// Hàm gửi thông báo và chuyển hướng
function redirectWithMessage($message, $status = 'success') {
    // Chuyển hướng về trang quản lý BĐS cá nhân (sp_canhan)
    $url = 'trangchu.php?page=../moi_gioi/sp_canhan'; 
    echo "<script>alert('{$message}'); window.location.href='{$url}';</script>";
    exit;
}

// Thư mục upload
$upload_dir = "../../../storage/pictures/bds/";
// Đảm bảo thư mục tồn tại
if (!is_dir($upload_dir)) {
    if (!mkdir($upload_dir, 0777, true)) {
        redirectWithMessage("Lỗi hệ thống: Không thể tạo thư mục lưu trữ ảnh.", 'error');
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Lấy và làm sạch dữ liệu
    $tieu_de   = trim($_POST['tieu_de'] ?? '');
    $mo_ta     = trim($_POST['mo_ta'] ?? '');
    $loai      = trim($_POST['loai'] ?? '');      // Loại BĐS (căn hộ, nhà phố, ...)
    $khu_vuc   = trim($_POST['khu_vuc'] ?? '');    // Khu vực
    $gia       = (float)($_POST['gia'] ?? 0);
    $dien_tich = (float)($_POST['dien_tich'] ?? 0);
    $dia_chi   = trim($_POST['dia_chi'] ?? '');
    $hinh_thuc = trim($_POST['hinh_thuc'] ?? ''); // Hình thức (bán, thuê) - Dùng cho cột `loai` trong bảng BĐS

    // 0. VALIDATION CƠ BẢN
    if (empty($tieu_de) || empty($mo_ta) || empty($hinh_thuc) || $gia <= 0 || $dien_tich <= 0 || empty($dia_chi) || empty($khu_vuc)) {
        redirectWithMessage("Vui lòng điền đầy đủ và chính xác các thông tin bắt buộc.", 'error');
    }
    
    // Bắt đầu Transaction
    $pdo->beginTransaction();
    $error = false;
    $media_paths = ['images' => [], 'video' => null];

    try {
        
        // --- 1. CHÈN DỮ LIỆU CƠ BẢN VÀO bat_dong_san (Sử dụng RETURNING id) ---
        
        $sql_bds = "
            INSERT INTO bat_dong_san (id_nguoi_dung, tieu_de, mo_ta, loai, khu_vuc, gia, dien_tich, dia_chi, trang_thai)
            VALUES (:id_nguoi_dung, :tieu_de, :mo_ta, :loai, :khu_vuc, :gia, :dien_tich, :dia_chi, 'chuaduyet')
            RETURNING id
        ";

        $stmt = $pdo->prepare($sql_bds);

        $stmt->execute([
            ':id_nguoi_dung' => $id_nguoi_dung,
            ':tieu_de'       => $tieu_de,
            ':mo_ta'         => $mo_ta,
            ':loai'          => $loai,
            ':khu_vuc'       => $khu_vuc, 
            ':gia'           => $gia,
            ':dien_tich'     => $dien_tich,
            ':dia_chi'       => $dia_chi
        ]);
        
        // Lấy ID BĐS vừa tạo (Chỉ định rõ cột 'id' từ lệnh RETURNING)
        $id_bds_moi = $stmt->fetchColumn(); 
        
        if (empty($id_bds_moi)) {
            $error = "Lỗi hệ thống: Không thể lấy ID sản phẩm mới.";
            throw new Exception($error);
        }

        // --- 2. XỬ LÝ UPLOAD HÌNH ẢNH (Nhiều file) ---
        $hinh_anh_files = $_FILES['hinh_anh'] ?? null;
        if ($hinh_anh_files && $hinh_anh_files['name'][0] != '') {
            foreach ($hinh_anh_files['tmp_name'] as $i => $tmp_name) {
                if ($hinh_anh_files['error'][$i] === UPLOAD_ERR_OK) {
                    $file_ext = pathinfo($hinh_anh_files['name'][$i], PATHINFO_EXTENSION);
                    $filename = "img_" . $id_bds_moi . "_" . uniqid() . "." . $file_ext;
                    
                    if (move_uploaded_file($tmp_name, $upload_dir . $filename)) {
                        $media_paths['images'][] = $filename;
                    } else {
                        $error = "Lỗi upload ảnh file thứ " . ($i + 1);
                        throw new Exception($error);
                    }
                }
            }
        }

        // --- 3. XỬ LÝ UPLOAD VIDEO (Một file) ---
        if (isset($_FILES['video']) && $_FILES['video']['error'] === UPLOAD_ERR_OK) {
            $video_file = $_FILES['video'];
            $file_ext = pathinfo($video_file['name'], PATHINFO_EXTENSION);
            $video_name = "vid_" . $id_bds_moi . "_" . uniqid() . "." . $file_ext;

            if (move_uploaded_file($video_file['tmp_name'], $upload_dir . $video_name)) {
                $media_paths['video'] = $video_name;
            } else {
                $error = "Lỗi upload video";
                throw new Exception($error);
            }
        }

        // --- 4. CHÈN ĐƯỜNG DẪN MEDIA VÀO CSDL (Sử dụng cấu trúc bảng mới) ---
        
        // Chèn Hình ảnh vào bảng hinh_anh_bds (url)
        $sql_img = "INSERT INTO hinh_anh_bds (id_bds, url) VALUES (:id_bds, :url)";
        $stmt_img = $pdo->prepare($sql_img);
        foreach ($media_paths['images'] as $path) {
            $stmt_img->execute([':id_bds' => $id_bds_moi, ':url' => $path]);
        }
        
        // Chèn Video vào bảng video_bds (url)
        if ($media_paths['video']) {
            $sql_vid = "INSERT INTO video_bds (id_bds, url) VALUES (:id_bds, :url)";
            $stmt_vid = $pdo->prepare($sql_vid);
            $stmt_vid->execute([':id_bds' => $id_bds_moi, ':url' => $media_paths['video']]);
        }

        // Hoàn tất Transaction
        $pdo->commit();
        redirectWithMessage("Đăng sản phẩm thành công! Sản phẩm đang chờ quản trị viên duyệt.");

    } catch (Exception $e) {
        $pdo->rollBack();
        
        // Xóa các file đã upload nếu có lỗi xảy ra
        if (!empty($media_paths['images'])) {
            foreach ($media_paths['images'] as $path) {
                if (file_exists($upload_dir . $path)) @unlink($upload_dir . $path);
            }
        }
        if ($media_paths['video'] && file_exists($upload_dir . $media_paths['video'])) {
            @unlink($upload_dir . $media_paths['video']);
        }
        
        error_log("Lỗi xử lý đăng BĐS: " . $e->getMessage());
        redirectWithMessage("Lỗi: Không thể hoàn tất đăng sản phẩm. Chi tiết: " . $e->getMessage(), 'error');
    }

}
?>