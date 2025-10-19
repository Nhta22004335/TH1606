<?php
// Bắt đầu phiên làm việc
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


// Kiểm tra người dùng đã đăng nhập chưa
$id_chu_so_huu = $_SESSION['id_nguoi_dung'] ?? null;
if (!$id_chu_so_huu) {
    exit('Lỗi: Bạn cần đăng nhập để thực hiện hành động này.');
}

// 3. XỬ LÝ DỮ LIỆU VÀ TRUY VẤN CSDL
// ===================================
require_once "../../config/database.php"; // Điều chỉnh đường dẫn nếu cần
$pdo = ketnoicsdl();

// Lấy ID sản phẩm từ form
$id_bds = $_GET['id'] ?? null;

try {
    // Chuẩn bị câu lệnh DELETE
    // CỰC KỲ QUAN TRỌNG: Thêm `id_nguoi_dung` vào mệnh đề WHERE
    // để đảm bảo người dùng chỉ có thể xóa sản phẩm của chính họ.
    $sql = "DELETE FROM bat_dong_san WHERE id = :id_bds AND id_chu_so_huu = :id_chu_so_huu";
    
    $stmt = $pdo->prepare($sql);
    
    $params = [
        ':id_bds' => $id_bds,
        ':id_chu_so_huu' => $id_chu_so_huu
    ];
    
    // Thực thi câu lệnh
    $stmt->execute($params);
    
    // 4. KIỂM TRA KẾT QUẢ VÀ CHUYỂN HƯỚNG
    // ===================================
    header('Location: ../views/quan_ly/trangchu.php?page=../moi_gioi/sp_canhan&id=' . urlencode($id_bds) . '&status=success');
    // rowCount() trả về số dòng đã bị xóa. Nếu > 0 là thành công.
    if ($stmt->rowCount() > 0) {
        // Xóa thành công, chuyển hướng về trang danh sách với thông báo success
        header('Location: ../views/quan_ly/trangchu.php?page=../moi_gioi/sp_canhan&id=' . urlencode($id_bds) . '&delete_status=success');
        // header('Location: sanpham_canhan.php?delete_status=success');
    } else {
        // Không có dòng nào bị xóa (có thể do ID không tồn tại hoặc không đúng chủ sở hữu)
        header('Location: ../views/quan_ly/trangchu.php?page=../moi_gioi/sp_canhan.php&id=' . urlencode($id_bds) . '&delete_status=unsuccess&message=nochange');
    }
    exit();

} catch (PDOException $e) {
    // Xử lý các lỗi liên quan đến CSDL
    // Trong môi trường production, bạn nên ghi lỗi này vào file log thay vì hiển thị ra màn hình
    // error_log("Lỗi xóa BĐS: " . $e->getMessage());
    header('Location: sanpham_canhan.php?delete_status=error_db');
    exit();
}
?>