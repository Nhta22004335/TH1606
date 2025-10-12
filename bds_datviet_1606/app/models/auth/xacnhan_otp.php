<?php
// FILE: xacnhan_otp_dangky.php

// Cài đặt session an toàn
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', isset($_SERVER['HTTPS']));

session_start();
date_default_timezone_set('Asia/Ho_Chi_Minh');
header('Content-Type: application/json');

// --- KẾT NỐI CSDL ---
try {
    require_once "../../../config/database.php";
    $pdo = ketnoicsdl();
} catch (Exception $e) {
    error_log("Lỗi kết nối CSDL: " . $e->getMessage());
    json_response(false, "Lỗi hệ thống, vui lòng thử lại sau.", 500);
}

// --- HÀM TIỆN ÍCH ---
function json_response($success, $message, $http_code = 200) {
    http_response_code($http_code);
    echo json_encode(['success' => $success, 'message' => $message]);
    exit;
}

// --- LUỒNG XỬ LÝ CHÍNH ---
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    json_response(false, "Phương thức không hợp lệ.", 405);
}

// BƯỚC 1: Lấy và xác thực đầu vào
$email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
$otp_code = trim($_POST['otp'] ?? '');

if (empty($email) || empty($otp_code)) {
    json_response(false, "Vui lòng nhập email và mã OTP.");
}
if (!preg_match('/^[0-9]{6}$/', $otp_code)) {
    json_response(false, "Mã OTP phải là 6 chữ số.");
}

// --- BƯỚC 2: TÌM VÀ XÁC THỰC YÊU CẦU OTP ---
try {
    // Tìm yêu cầu OTP hợp lệ (chưa hết hạn) cho email
    $sql = "SELECT otp_hash, user_data_json FROM yeu_cau_otp WHERE email = :email AND het_han > NOW() LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':email' => $email]);
    $otp_request = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$otp_request) {
        json_response(false, "Mã OTP không hợp lệ hoặc đã hết hạn. Vui lòng thử lại.");
    }

    // So sánh OTP người dùng nhập với hash trong CSDL một cách an toàn
    if (password_verify($otp_code, $otp_request['otp_hash'])) {
        // --- OTP CHÍNH XÁC - TIẾN HÀNH TẠO NGƯỜI DÙNG ---

        $user_data = json_decode($otp_request['user_data_json'], true);
        if (!$user_data) {
            json_response(false, "Lỗi dữ liệu đăng ký tạm thời. Vui lòng thử đăng ký lại.", 500);
        }

        // Bắt đầu một giao dịch để đảm bảo toàn vẹn dữ liệu
        $pdo->beginTransaction();

        try {
            // 3a. Thêm tài khoản vào bảng `nguoi_dung`
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

            // 3b. Thêm thông tin chi tiết vào bảng `khach_hang` (hoặc bảng tương ứng)
            // Giả sử có một trigger tự động tạo bản ghi `khach_hang` khi `nguoi_dung` được tạo.
            // Nếu không, bạn phải INSERT thủ công. Ở đây ta dùng UPDATE.
            $sql_update_profile = "UPDATE info_nguoi_dung SET ho_ten = :ho_ten WHERE id_nguoi_dung = :id";
            $stmt_profile = $pdo->prepare($sql_update_profile);
            $stmt_profile->execute([
                ':ho_ten' => $user_data['ho_ten'],
                ':id'     => $new_user_id
            ]);

            // 3c. Xóa yêu cầu OTP đã sử dụng
            $sql_delete_otp = "DELETE FROM yeu_cau_otp WHERE email = :email";
            $stmt_delete = $pdo->prepare($sql_delete_otp);
            $stmt_delete->execute([':email' => $email]);

            // Nếu tất cả thành công, xác nhận giao dịch
            $pdo->commit();

            json_response(true, "Xác thực thành công! Tài khoản của bạn đã được tạo.");

        } catch (Exception $e) {
            // Nếu có lỗi, hủy bỏ tất cả thay đổi
            $pdo->rollBack();
            error_log("Lỗi giao dịch khi tạo người dùng: " . $e->getMessage());
            json_response(false, "Lỗi khi tạo tài khoản. Vui lòng thử lại.", 500);
        }

    } else {
        // OTP sai
        json_response(false, "Mã OTP không hợp lệ hoặc đã hết hạn. Vui lòng thử lại.");
    }

} catch (PDOException $e) {
    error_log("Lỗi CSDL khi xác thực OTP: " . $e->getMessage());
    json_response(false, "Lỗi hệ thống khi xác thực. Vui lòng thử lại.", 500);
}
?>