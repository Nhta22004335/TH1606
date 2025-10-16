<?php
// FILE: xacnhan_otp_quen_matkhau.php

// Cài đặt session an toàn
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', isset($_SERVER['HTTPS']));

session_start();
date_default_timezone_set('Asia/Ho_Chi_Minh');

// --- HÀM TIỆN ÍCH ---
function json_response($success, $message, $http_code = 200) {
    http_response_code($http_code);
    header('Content-Type: application/json');
    echo json_encode(['success' => $success, 'message' => $message]);
    exit;
}

// =================================================================
// BẮT ĐẦU LOGIC XỬ LÝ API (CHỈ CHẠY KHI LÀ POST REQUEST)
// =================================================================
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // --- KẾT NỐI CSDL ---
    try {
        require_once "../../../config/database.php";
        $pdo = ketnoicsdl();
    } catch (Exception $e) {
        error_log("Lỗi kết nối CSDL: " . $e->getMessage());
        json_response(false, "Lỗi hệ thống, vui lòng thử lại sau.", 500);
    }

    // BƯỚC 1: Lấy và xác thực đầu vào
    $email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
    $otp_code = trim($_POST['otp'] ?? '');

    if (empty($email) || empty($otp_code)) {
        json_response(false, "Vui lòng nhập email và mã OTP.", 400);
    }
    if (!preg_match('/^[A-Z0-9]{6}$/', $otp_code)) {
        json_response(false, "Mã OTP không đúng định dạng.", 400);
    }

    // --- BƯỚC 2: TÌM VÀ XÁC THỰC YÊU CẦU OTP ---
    try {
        // Tìm yêu cầu OTP hợp lệ (chưa hết hạn và đang chờ xác nhận) cho email
        $sql = "SELECT otp_hash, user_data_json FROM yeu_cau_otp 
                WHERE email = :email AND trang_thai = 'choxacnhan' AND het_han > NOW() 
                ORDER BY bat_dau DESC LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':email' => $email]);
        $otp_request = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$otp_request) {
            json_response(false, "Mã OTP không hợp lệ hoặc đã hết hạn. Vui lòng yêu cầu mã mới.");
        }

        // So sánh OTP người dùng nhập với hash trong CSDL
        if (password_verify($otp_code, $otp_request['otp_hash'])) {
            // --- OTP CHÍNH XÁC - CHO PHÉP ĐẶT LẠI MẬT KHẨU ---
            $user_data = json_decode($otp_request['user_data_json'], true);
            if (empty($user_data['reset_token'])) {
                json_response(false, "Lỗi dữ liệu yêu cầu. Vui lòng thử lại từ đầu.", 500);
            }

            $pdo->beginTransaction();
            try {
                // Cập nhật trạng thái yêu cầu OTP thành 'daxacnhan'
                $sql_update_otp = "UPDATE yeu_cau_otp 
                                   SET trang_thai = 'daxacnhan', cap_nhat = CURRENT_TIMESTAMP 
                                   WHERE email = :email AND trang_thai = 'choxacnhan'";
                $stmt_update = $pdo->prepare($sql_update_otp);
                $stmt_update->execute([':email' => $email]);
                
                $pdo->commit();

                // Quan trọng: Lưu token reset vào session để trang sau có thể sử dụng
                $_SESSION['reset_token'] = $user_data['reset_token'];
                $_SESSION['reset_user_id'] = $user_data['user_id']; // Lưu luôn user_id để tiện
                
                // Dọn dẹp session email không cần thiết nữa
                unset($_SESSION['reset_email']);

                json_response(true, "Xác thực thành công! Bạn sẽ được chuyển đến trang đặt lại mật khẩu.");

            } catch (Exception $e) {
                $pdo->rollBack();
                error_log("Lỗi giao dịch khi xác nhận OTP: " . $e->getMessage());
                json_response(false, "Lỗi hệ thống khi xác nhận. Vui lòng thử lại.", 500);
            }

        } else {
            json_response(false, "Mã OTP không chính xác.");
        }
    } catch (PDOException $e) {
        error_log("Lỗi CSDL khi xác thực OTP: " . $e->getMessage());
        json_response(false, "Lỗi hệ thống. Vui lòng thử lại.", 500);
    }
}
// =================================================================
// KẾT THÚC LOGIC API. NẾU LÀ GET REQUEST, SẼ TIẾP TỤC RENDER HTML
// =================================================================
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Zolux 4335 - Xác nhận OTP</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen flex items-center justify-center bg-gradient-to-r from-blue-100 via-white to-blue-50">
  <div class="w-full max-w-md bg-white rounded-2xl shadow-xl p-8">
    <h1 class="text-2xl font-bold text-center text-gray-800 mb-2">Đặt lại mật khẩu</h1>
    <p class="text-center text-gray-500 mb-6">Một mã gồm 6 ký tự đã được gửi đến email của bạn để xác thực.</p>
    
    <div id="error-message" class="hidden bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg relative mb-4" role="alert"></div>

    <form id="formXacNhan" class="space-y-5">
      <div>
        <label for="otp" class="sr-only">Nhập mã OTP</label>
        <input type="text" id="otp" name="otp" placeholder="••••••" maxlength="6"
          class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-transparent text-center text-2xl tracking-[.5em]" required />
      </div>
      <button type="submit" id="btnXacNhan"
        class="w-full bg-blue-600 text-white font-semibold py-3 rounded-xl hover:bg-blue-700 transition duration-300 shadow-md disabled:bg-blue-300 disabled:cursor-not-allowed">
        Xác nhận
      </button>
    </form>
    <p class="text-sm text-gray-500 text-center mt-6">Chưa nhận được OTP? 
      <a href="quen_matkhau.php" class="text-blue-600 hover:underline">Yêu cầu lại</a>
    </p>
  </div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('formXacNhan');
    const otpInput = document.getElementById('otp');
    const submitButton = document.getElementById('btnXacNhan');
    const errorMessageDiv = document.getElementById('error-message');

    // Lấy email đã được lưu trong session từ PHP
    const email = '<?php echo htmlspecialchars($_SESSION["reset_email"] ?? "", ENT_QUOTES); ?>';

    if (!email) {
        errorMessageDiv.textContent = 'Lỗi: Không tìm thấy phiên làm việc. Vui lòng quay lại trang quên mật khẩu.';
        errorMessageDiv.classList.remove('hidden');
        submitButton.disabled = true;
    }

    form.addEventListener('submit', async function(event) {
        event.preventDefault();
        
        const otp = otpInput.value.trim().toUpperCase();
        
        submitButton.disabled = true;
        submitButton.textContent = 'Đang xử lý...';
        errorMessageDiv.classList.add('hidden');

        const formData = new FormData();
        formData.append('email', email);
        formData.append('otp', otp);

        try {
            const response = await fetch('', { 
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (result.success) {
                alert(result.message);
                // CHUYỂN HƯỚNG ĐẾN TRANG ĐẶT LẠI MẬT KHẨU MỚI
                window.location.href = 'dat_lai_mat_khau.php'; 
            } else {
                errorMessageDiv.textContent = result.message;
                errorMessageDiv.classList.remove('hidden');
            }
        } catch (error) {
            console.error('Lỗi Fetch:', error);
            errorMessageDiv.textContent = 'Đã xảy ra lỗi kết nối. Vui lòng thử lại.';
            errorMessageDiv.classList.remove('hidden');
        } finally {
            submitButton.disabled = false;
            submitButton.textContent = 'Xác nhận';
        }
    });
});
</script>
</body>
</html>