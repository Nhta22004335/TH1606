<?php
// FILE: xacnhan_otp_dangky.php

// --- CÀI ĐẶT & KHỞI TẠO ---
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', isset($_SERVER['HTTPS']));

session_start();
date_default_timezone_set('Asia/Ho_Chi_Minh');
header('Content-Type: application/json');

// --- HẰNG SỐ CẤU HÌNH ---
define('MAX_OTP_ATTEMPTS', 5); // Cho phép thử OTP sai tối đa 5 lần

// --- HÀM TIỆN ÍCH ---
function json_response($success, $message, $http_code = 200) {
    http_response_code($http_code);
    echo json_encode(['success' => $success, 'message' => $message]);
    exit;
}

// --- KẾT NỐI CSDL ---
try {
    require_once "../../../config/database.php";
    $pdo = ketnoicsdl();
} catch (Exception $e) {
    error_log("Lỗi kết nối CSDL: " . $e->getMessage());
    json_response(false, "Lỗi hệ thống, vui lòng thử lại sau.", 500);
}

// --- LUỒNG XỬ LÝ CHÍNH ---

// BƯỚC 1: Kiểm tra phương thức và dữ liệu đầu vào (Guard Clauses)
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    json_response(false, "Phương thức không hợp lệ.", 405);
}

$email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
$otp_code = trim($_POST['otp'] ?? '');

if (!$email || !$otp_code || !preg_match('/^[0-9]{6}$/', $otp_code)) {
    json_response(false, "Email hoặc mã OTP không hợp lệ.");
}

// --- BƯỚC 2: TÌM VÀ XÁC THỰC YÊU CẦU OTP ---
try {
    // Lấy yêu cầu OTP hợp lệ (chưa hết hạn, chưa thử quá nhiều lần)
    $sql_get_otp = "SELECT otp_hash, user_data_json, so_lan_thu_sai 
                    FROM yeu_cau_otp 
                    WHERE email = :email AND het_han > NOW() 
                    LIMIT 1";
    $stmt_get_otp = $pdo->prepare($sql_get_otp);
    $stmt_get_otp->execute([':email' => $email]);
    $otp_request = $stmt_get_otp->fetch(PDO::FETCH_ASSOC);

    if (!$otp_request) {
        json_response(false, "Mã OTP không hợp lệ hoặc đã hết hạn. Vui lòng thử lại từ đầu.");
    }

    // [BẢO MẬT] Chống brute-force: Nếu thử sai quá nhiều, xóa OTP và báo lỗi
    if ($otp_request['so_lan_thu_sai'] >= MAX_OTP_ATTEMPTS) {
        $pdo->prepare("DELETE FROM yeu_cau_otp WHERE email = :email")->execute([':email' => $email]);
        json_response(false, "Bạn đã nhập sai quá nhiều lần. Vui lòng lấy mã OTP mới.");
    }

    // --- BƯỚC 3: KIỂM TRA MÃ OTP ---
    if (!password_verify($otp_code, $otp_request['otp_hash'])) {
        // [BẢO MẬT] OTP sai, tăng bộ đếm số lần thử sai
        $sql_update_attempts = "UPDATE yeu_cau_otp SET so_lan_thu_sai = so_lan_thu_sai + 1 WHERE email = :email";
        $pdo->prepare($sql_update_attempts)->execute([':email' => $email]);
        
        $attempts_left = MAX_OTP_ATTEMPTS - ($otp_request['so_lan_thu_sai'] + 1);
        json_response(false, "Mã OTP không chính xác. Bạn còn {$attempts_left} lần thử.");
    }

    // --- BƯỚC 4: TẠO TÀI KHOẢN (OTP ĐÚNG) ---
    $user_data = json_decode($otp_request['user_data_json'], true);
    if (!$user_data) {
        json_response(false, "Lỗi dữ liệu đăng ký tạm thời. Vui lòng thử đăng ký lại.", 500);
    }
    
    // Bắt đầu một giao dịch để đảm bảo toàn vẹn dữ liệu
    $pdo->beginTransaction();
    try {
        // 4a. Thêm tài khoản vào bảng `nguoi_dung`
        $sql_insert_user = "INSERT INTO nguoi_dung (ten_dang_nhap, mat_khau, email, so_dt)
                            VALUES (:ten_dang_nhap, :mat_khau, :email, :so_dt)
                            RETURNING id";
        $stmt_user = $pdo->prepare($sql_insert_user);
        $stmt_user->execute([
            ':ten_dang_nhap' => $user_data['ten_dang_nhap'],
            ':mat_khau'      => $user_data['mat_khau_hashed'],
            ':email'         => $email,
            ':so_dt'         => $user_data['so_dt']
        ]);
        $new_user_id = $stmt_user->fetchColumn();

        // 4b. Thêm thông tin chi tiết vào bảng `info_nguoi_dung`
        $sql_insert_profile = "INSERT INTO info_nguoi_dung (id_nguoi_dung, ho_ten) 
                               VALUES (:id_nguoi_dung, :ho_ten)";
        $stmt_profile = $pdo->prepare($sql_insert_profile);
        $stmt_profile->execute([
            ':id_nguoi_dung' => $new_user_id,
            ':ho_ten'        => $user_data['ho_ten']
        ]);

        // 4c. [HIỆU NĂNG] Thêm quyền cho người dùng bằng subquery, gộp 2 câu lệnh thành 1
        $sql_insert_permission = "INSERT INTO phan_quyen (id_nguoi_dung, id_quyen)
                                  VALUES (:id_nguoi_dung, (SELECT id FROM quyen WHERE vai_tro = 'khachhang' LIMIT 1))
                                  ON CONFLICT (id_nguoi_dung, id_quyen) DO NOTHING";
        $stmt_permission = $pdo->prepare($sql_insert_permission);
        $stmt_permission->execute([':id_nguoi_dung' => $new_user_id]);

        // 4d. Xóa tất cả các yêu cầu OTP của email này (dọn dẹp)
        $sql_delete_otp = "DELETE FROM yeu_cau_otp WHERE email = :email";
        $pdo->prepare($sql_delete_otp)->execute([':email' => $email]);

        // Nếu tất cả thành công, xác nhận giao dịch
        $pdo->commit();

        json_response(true, "Xác thực thành công! Tài khoản của bạn đã được tạo.");

    } catch (Exception $e) {
        // Nếu có lỗi, hủy bỏ tất cả thay đổi
        $pdo->rollBack();
        error_log("Lỗi giao dịch khi tạo người dùng: " . $e->getMessage());
        json_response(false, "Lỗi khi tạo tài khoản. Vui lòng thử lại.", 500);
    }

} catch (PDOException $e) {
    error_log("Lỗi CSDL khi xác thực OTP: " . $e->getMessage());
    json_response(false, "Lỗi hệ thống khi xác thực. Vui lòng thử lại.", 500);
}
?>
