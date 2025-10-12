<?php
// Cài đặt session an toàn
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', isset($_SERVER['HTTPS'])); // true nếu dùng HTTPS

session_start();

date_default_timezone_set('Asia/Ho_Chi_Minh');
header('Content-Type: application/json');

// Hàm trả về JSON và kết thúc kịch bản
function json_response($success, $message, $http_code = 200) {
    http_response_code($http_code);
    echo json_encode(['success' => $success, 'message' => $message]);
    exit;
}

try {
    require_once "../../../config/database.php";
    $pdo = ketnoicsdl();
} catch (PDOException $e) {
    error_log("Database Connection Error: " . $e->getMessage());
    json_response(false, "Lỗi kết nối cơ sở dữ liệu.", 500);
}

$rawData = file_get_contents("php://input");
$data = json_decode($rawData, true);

if (!$data) {
    json_response(false, "Dữ liệu đầu vào không hợp lệ.", 400);
}

$ten_dang_nhap = trim($data['tendangnhap'] ?? '');
$mat_khau = $data['matkhau'] ?? ''; // Không trim mật khẩu
$remember_me = !empty($data['remember']);

if (empty($ten_dang_nhap) || empty($mat_khau)) {
    json_response(false, "Vui lòng nhập đầy đủ tên đăng nhập và mật khẩu.");
}

/**
 * SỬA 2: Lưu phiên "Remember Me" vào CSDL một cách an toàn
 */
function luuPhienRememberMe($pdo, $id_nguoi_dung) {
    try {
        $selector = bin2hex(random_bytes(16));
        $verifier = bin2hex(random_bytes(32));
        $verifier_hash = hash('sha256', $verifier);
        $het_han = date('Y-m-d H:i:s', strtotime('+30 days'));

        $stmt = $pdo->prepare(
            "INSERT INTO phien_dang_nhap (id_nguoi_dung, selector, verifier_hash, het_han) 
             VALUES (:id_nguoi_dung, :selector, :verifier_hash, :het_han)"
        );
        $stmt->execute([
            ':id_nguoi_dung' => $id_nguoi_dung, 
            ':selector' => $selector, 
            ':verifier_hash' => $verifier_hash, 
            ':het_han' => $het_han
        ]);
        
        $cookie_expiry = time() + (86400 * 30); // 30 ngày
        setcookie("selector", $selector, $cookie_expiry, "/", "", true, true);
        setcookie("verifier", $verifier, $cookie_expiry, "/", "", true, true);

    } catch (PDOException $e) {
        // SỬA 4: Xử lý lỗi an toàn
        error_log("Lỗi khi lưu phiên Remember Me: " . $e->getMessage());
        // Không cần thông báo cho người dùng, họ vẫn đăng nhập được session hiện tại
    }
}

/**
 * Lưu lại lịch sử hành động xác thực của người dùng
 */
function luuLichSuNguoiDung($pdo, $id_nguoi_dung, $ghi_chu) {
    try {
        $stmt = $pdo->prepare(
            "INSERT INTO lich_su_xac_thuc (id_nguoi_dung, loai_su_kien, dia_chi_ip, user_agent, ghi_chu)
             VALUES (:id_nguoi_dung, :loai_su_kien, :dia_chi_ip, :user_agent, :ghi_chu)
             RETURNING id"
        );
        $stmt->execute([
            ':id_nguoi_dung' => $id_nguoi_dung,
            ':loai_su_kien'  => 'dangnhap',
            ':dia_chi_ip'    => $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN',
            ':user_agent'    => $_SERVER['HTTP_USER_AGENT'] ?? 'UNKNOWN',
            ':ghi_chu'       => $ghi_chu
        ]);
        $idlichsu = $stmt->fetchColumn();

        $_SESSION['id_lich_su'] = $idlichsu;
        
        // SỬA 6: Sửa lỗi logic cookie
        // Lưu ý: Việc đặt cookie này có thể không cần thiết nếu bạn đã có session
        setcookie("id_lich_su", $idlichsu, 0, "/", "", true, true); // 0 = hết hạn khi đóng trình duyệt

    } catch (PDOException $e) {
        error_log("Lỗi khi lưu lịch sử người dùng: " . $e->getMessage());
    }
}

// SỬA 5: Tối ưu hóa truy vấn, lấy các cột cần thiết
$sql = "SELECT id, mat_khau, trang_thai FROM nguoi_dung WHERE ten_dang_nhap = :ten_dang_nhap LIMIT 1";
$stmt = $pdo->prepare($sql);
$stmt->execute([':ten_dang_nhap' => $ten_dang_nhap]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// SỬA 1: Sử dụng password_verify() thay vì shell_exec
if ($user && password_verify($mat_khau, $user['mat_khau'])) {

    // Kiểm tra trạng thái tài khoản
    if ($user['trang_thai'] !== 'danghoatdong') {
        luuLichSuNguoiDung($pdo, $user['id'], 'dangnhap_thatbai_bivohieuhoa');
        json_response(false, 'Tài khoản tạm thời ngưng hoạt động hoặc bị khóa!');
    }

    // Đăng nhập thành công, tái tạo session ID để chống Session Fixation
    session_regenerate_id(true);

    $_SESSION['id_nguoi_dung'] = $user['id'];

    luuLichSuNguoiDung($pdo, $user['id'], 'dangnhap_thanhcong');

    // SỬA 2: Xử lý "Remember Me" với Selector/Verifier
    if ($remember_me) {
        luuPhienRememberMe($pdo, $user['id']);
    }

    json_response(true, 'Đăng nhập thành công!');

} else {
    // SỬA 3: Xử lý khi đăng nhập thất bại
    // Ghi lại lịch sử đăng nhập thất bại nếu user tồn tại
    if ($user) {
        luuLichSuNguoiDung($pdo, $user['id'], 'dangnhap_thatbai_saimatkhau');
    }
    json_response(false, 'Tên đăng nhập hoặc mật khẩu không chính xác.');
}

?>