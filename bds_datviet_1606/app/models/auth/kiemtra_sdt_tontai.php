<?php
// Thiết lập header để trả về JSON
header('Content-Type: application/json');

// 1. KẾT NỐI CSDL VÀ KIỂM TRA LỖI
try {
    require_once "../../../config/database.php";
    $pdo = ketnoicsdl();
} catch (PDOException $e) {
    error_log("Database Connection Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Lỗi kết nối cơ sở dữ liệu.']);
    exit;
}

// 2. LẤY VÀ KIỂM TRA SỐ ĐIỆN THOẠI TỪ YÊU CẦU GET
$sdt = trim($_GET['sodienthoai'] ?? '');

// Regex kiểm tra SĐT Việt Nam hợp lệ (10 số, bắt đầu bằng 0)
if (empty($sdt) || !preg_match('/^(0[2-9][0-9]{8})$/', $sdt)) {
    http_response_code(400); // Bad Request
    echo json_encode(['error' => 'Số điện thoại không hợp lệ.']);
    exit;
}

// 3. TRUY VẤN CSDL ĐỂ KIỂM TRA SĐT
try {
    $sql = "SELECT 1 FROM nguoi_dung WHERE so_dt = :sodienthoai LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':sodienthoai' => $sdt]);

    // Nếu fetch() trả về một dòng, nghĩa là SĐT đã tồn tại
    $exists = $stmt->fetch() !== false;

    // 4. TRẢ VỀ KẾT QUẢ JSON
    echo json_encode(['exists' => $exists]);

} catch (PDOException $e) {
    error_log("Phone check query error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Lỗi truy vấn cơ sở dữ liệu.']);
    exit;
}
?>
