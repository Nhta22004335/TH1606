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

// 2. LẤY VÀ KIỂM TRA EMAIL TỪ YÊU CẦU GET
$email = trim($_GET['email'] ?? '');

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400); // Bad Request
    echo json_encode(['error' => 'Email không hợp lệ.']);
    exit;
}

// 3. TRUY VẤN CSDL ĐỂ KIỂM TRA EMAIL
try {
    // Sử dụng SELECT 1 để tối ưu hiệu suất, chỉ cần biết có tồn tại hay không
    $sql = "SELECT 1 FROM nguoi_dung WHERE email = :email LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':email' => $email]);

    // Nếu fetch() trả về một dòng (dù là '1'), nghĩa là email đã tồn tại
    $exists = $stmt->fetch() !== false;

    // 4. TRẢ VỀ KẾT QUẢ JSON
    echo json_encode(['exists' => $exists]);

} catch (PDOException $e) {
    error_log("Email check query error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Lỗi truy vấn cơ sở dữ liệu.']);
    exit;
}
?>
