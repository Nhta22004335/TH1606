<?php
// File: xl_dang_sp.php

// Khởi tạo SESSION
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once "../../../config/database.php";

if (!isset($_SESSION['id_nguoi_dung'])) {
    header("Location: ../auth/dangnhap.php");
    exit;
}

$pdo = ketnoicsdl();
$id_nguoi_dung = $_SESSION['id_nguoi_dung'] ?? '';

// Hàm redirect với thông báo
function redirectWithMessage($message, $type = 'success') {
    $url = 'trangchu.php?page=../moi_gioi/sp_canhan';
    echo "<script>alert('".addslashes($message)."'); window.location.href='{$url}';</script>";
    exit;
}

// Thư mục upload
$upload_dir = "../../../storage/pictures/bds/";
if (!is_dir($upload_dir)) {
    if (!mkdir($upload_dir, 0777, true)) {
        redirectWithMessage("Lỗi hệ thống: Không thể tạo thư mục lưu trữ.", 'error');
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Lấy dữ liệu
    $tieu_de   = trim($_POST['tieu_de'] ?? '');
    $mo_ta     = trim($_POST['mo_ta'] ?? '');
    $loai      = trim($_POST['loai'] ?? '');
    $khu_vuc   = trim($_POST['khu_vuc'] ?? '');
    $gia       = (float)($_POST['gia'] ?? 0);
    $dien_tich = (float)($_POST['dien_tich'] ?? 0);
    $dia_chi   = trim($_POST['dia_chi'] ?? '');
    $hinh_thuc = trim($_POST['hinh_thuc'] ?? '');

    // VALIDATION cơ bản
    if (!$tieu_de || !$mo_ta || !$loai || !$khu_vuc || !$gia || !$dien_tich || !$dia_chi || !$hinh_thuc) {
        redirectWithMessage("Vui lòng điền đầy đủ các trường bắt buộc.", 'error');
    }

    $pdo->beginTransaction();
    $media_paths = ['images' => [], 'video' => null];

    try {
        // 1. Chèn BĐS mới
        $sql = "INSERT INTO bat_dong_san (id_nguoi_dung, tieu_de, mo_ta, loai, khu_vuc, gia, dien_tich, dia_chi, trang_thai)
                VALUES (:id_nguoi_dung, :tieu_de, :mo_ta, :loai, :khu_vuc, :gia, :dien_tich, :dia_chi, 'chuaduyet')
                RETURNING id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':id_nguoi_dung' => $id_nguoi_dung,
            ':tieu_de' => $tieu_de,
            ':mo_ta' => $mo_ta,
            ':loai' => $loai,
            ':khu_vuc' => $khu_vuc,
            ':gia' => $gia,
            ':dien_tich' => $dien_tich,
            ':dia_chi' => $dia_chi
        ]);
        $id_bds = $stmt->fetchColumn();
        if (!$id_bds) throw new Exception("Không lấy được ID sản phẩm mới.");

        // 2. Upload nhiều hình ảnh
        if (!empty($_FILES['hinh_anh']['name'][0])) {
            foreach ($_FILES['hinh_anh']['tmp_name'] as $i => $tmp_name) {
                if ($_FILES['hinh_anh']['error'][$i] === UPLOAD_ERR_OK) {
                    $ext = pathinfo($_FILES['hinh_anh']['name'][$i], PATHINFO_EXTENSION);
                    $filename = "img_{$id_bds}_" . uniqid() . "." . $ext;
                    if (!move_uploaded_file($tmp_name, $upload_dir.$filename)) {
                        throw new Exception("Upload ảnh thứ ".($i+1)." thất bại.");
                    }
                    $media_paths['images'][] = $filename;
                }
            }
        }

        // 3. Upload 1 video
        if (isset($_FILES['video']) && $_FILES['video']['error'] === UPLOAD_ERR_OK) {
            $ext = pathinfo($_FILES['video']['name'], PATHINFO_EXTENSION);
            $video_name = "vid_{$id_bds}_" . uniqid() . "." . $ext;
            if (!move_uploaded_file($_FILES['video']['tmp_name'], $upload_dir.$video_name)) {
                throw new Exception("Upload video thất bại.");
            }
            $media_paths['video'] = $video_name;
        }

        // 4. Chèn hình ảnh vào CSDL
        $stmt_img = $pdo->prepare("INSERT INTO hinh_anh_bds (id_bds, url) VALUES (:id_bds, :url)");
        foreach ($media_paths['images'] as $img) {
            $stmt_img->execute([':id_bds' => $id_bds, ':url' => $img]);
        }

        // 5. Chèn video vào CSDL
        if ($media_paths['video']) {
            $stmt_vid = $pdo->prepare("INSERT INTO video_bds (id_bds, url) VALUES (:id_bds, :url)");
            $stmt_vid->execute([':id_bds' => $id_bds, ':url' => $media_paths['video']]);
        }

        $pdo->commit();
        redirectWithMessage("Đăng sản phẩm thành công! Sản phẩm đang chờ duyệt.");

    } catch (Exception $e) {
        $pdo->rollBack();

        // Xóa file đã upload nếu lỗi
        foreach ($media_paths['images'] as $img) {
            if (file_exists($upload_dir.$img)) @unlink($upload_dir.$img);
        }
        if ($media_paths['video'] && file_exists($upload_dir.$media_paths['video'])) @unlink($upload_dir.$media_paths['video']);

        error_log("Lỗi đăng BĐS: ".$e->getMessage());
        redirectWithMessage("Lỗi: Không thể đăng sản phẩm. Chi tiết: ".$e->getMessage(), 'error');
    }
}
?>
