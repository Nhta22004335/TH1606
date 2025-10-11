<?php
// Bắt đầu phiên
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once "../../config/database.php"; // Điều chỉnh đường dẫn nếu cần

// 1. Chỉ cho phép truy cập bằng phương thức POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    exit('Phương thức không hợp lệ.');
}

$pdo = ketnoicsdl();

// 2. Lấy dữ liệu từ form và ID người dùng
$id_nguoi_dung = $_SESSION['id_nguoi_dung'] ?? null;
$id_bds         = $_POST['id'] ?? null;
$tieu_de        = trim($_POST['tieu_de'] ?? 'chuacapnhat');
$hinh_thuc      = $_POST['hinh_thuc'] ?? 'Bán';
$gia            = $_POST['gia'] ?? 0;
$dien_tich      = $_POST['dien_tich'] ?? 0;
$loai           = $_POST['loai'] ?? 'chuacapnhat';
$khu_vuc        = trim($_POST['khu_vuc'] ?? 'chuacapnhat');
$dia_chi        = trim($_POST['dia_chi'] ?? 'chuacapnhat');
$mo_ta          = trim($_POST['mo_ta'] ?? 'chuacapnhat');

// 3. Validation dữ liệu
if (empty($id_nguoi_dung) || empty($id_bds) || empty($tieu_de) || !is_numeric($gia) || $gia < 0 || !is_numeric($dien_tich) || $dien_tich <= 0) {
    // Nếu dữ liệu quan trọng bị thiếu hoặc sai, chuyển hướng về form với thông báo lỗi
    header('Location: sua_san_pham.php?id=' . urlencode($id_bds) . '&status=error');
    exit();
}

try {
    // 4. Chuẩn bị câu lệnh UPDATE
    // QUAN TRỌNG: Thêm `id_nguoi_dung` vào mệnh đề WHERE để đảm bảo người dùng
    // không thể cập nhật sản phẩm của người khác.
    $sql = "UPDATE bat_dong_san SET 
                tieu_de = :tieu_de,
                mo_ta = :mo_ta,
                gia = :gia,
                dien_tich = :dien_tich,
                dia_chi = :dia_chi,
                loai = :loai,
                khu_vuc = :khu_vuc,
                hinh_thuc = :hinh_thuc
            WHERE id = :id AND id_nguoi_dung = :id_nguoi_dung";

    $stmt = $pdo->prepare($sql);

    // 5. Bind các tham số
    $params = [
        ':tieu_de'      => $tieu_de,
        ':mo_ta'        => $mo_ta,
        ':gia'          => $gia,
        ':dien_tich'    => $dien_tich,
        ':dia_chi'      => $dia_chi,
        ':loai'         => $loai,
        ':khu_vuc'      => $khu_vuc,
        ':hinh_thuc'    => $hinh_thuc,
        ':id'           => $id_bds,
        ':id_nguoi_dung'=> $id_nguoi_dung
    ];

    // 6. Thực thi và kiểm tra kết quả
    $stmt->execute($params);

    // rowCount() trả về số dòng bị ảnh hưởng. Nếu > 0 là thành công.
    if ($stmt->rowCount() > 0) {
        // Cập nhật thành công, chuyển hướng về form với thông báo success
        header('Location: ../views/quan_ly/trangchu.php?page=../moi_gioi/sp_canhan&id=' . urlencode($id_bds) . '&status=success');
    } else {
        // Có thể không có gì thay đổi, hoặc lỗi không tìm thấy (do sai id hoặc sai chủ)
        // Ta vẫn coi là "thành công" vì không có lỗi xảy ra
        header('Location: ../views/quan_ly/trangchu.php?page=../moi_gioi/sp_canhan.php&id=' . urlencode($id_bds) . '&status=success&message=nochange');
    }
    exit();

} catch (PDOException $e) {
    // Xử lý lỗi CSDL
    // Trong môi trường production, bạn nên ghi log lỗi thay vì hiển thị ra màn hình
    // die("Lỗi CSDL: " . $e->getMessage()); 
    header('Location: sua_san_pham.php?id=' . urlencode($id_bds) . '&status=error');
    exit();
}

?>