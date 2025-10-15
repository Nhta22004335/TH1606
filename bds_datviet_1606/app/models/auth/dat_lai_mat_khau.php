<?php
// FILE: dat_lai_mat_khau.php

// Cài đặt session an toàn
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', isset($_SERVER['HTTPS']));

session_start();

// =================================================================
// BƯỚC 1: KIỂM TRA BẢO MẬT - XÁC THỰC PHIÊN LÀM VIỆC
// =================================================================
// Nếu không có token reset trong session, người dùng chưa được xác thực.
// Chuyển hướng họ về trang đăng nhập để tránh truy cập trái phép.
if (empty($_SESSION['reset_token']) || empty($_SESSION['reset_user_id'])) {
    // Tùy chọn: bạn có thể thêm một tham số để hiển thị thông báo lỗi trên trang đăng nhập
    header('Location: dangnhap.php?error=unauthorized');
    exit;
}

// =================================================================
// BƯỚC 2: XỬ LÝ KHI NGƯỜI DÙNG SUBMIT FORM MẬT KHẨU MỚI
// =================================================================
$error_message = '';
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    
    $mat_khau_moi = trim($_POST['mat_khau_moi'] ?? '');
    $xac_nhan_mat_khau = trim($_POST['xac_nhan_mat_khau'] ?? '');

    // --- Validation ---
    if (empty($mat_khau_moi) || empty($xac_nhan_mat_khau)) {
        $error_message = 'Vui lòng nhập đầy đủ mật khẩu mới và xác nhận mật khẩu.';
    } elseif (strlen($mat_khau_moi) < 8) {
        $error_message = 'Mật khẩu phải có ít nhất 8 ký tự.';
    } elseif ($mat_khau_moi !== $xac_nhan_mat_khau) {
        $error_message = 'Mật khẩu và xác nhận mật khẩu không khớp.';
    } else {
        // --- Xử lý Cập nhật CSDL ---
        try {
            require_once "../../../config/database.php";
            $pdo = ketnoicsdl();

            // Băm mật khẩu bằng thuật toán Argon2ID
            $mat_khau_hashed = password_hash($mat_khau_moi, PASSWORD_ARGON2ID);
            
            if (!$mat_khau_hashed) {
                 throw new Exception("Lỗi khi băm mật khẩu. Vui lòng kiểm tra cấu hình PHP.");
            }

            // Cập nhật mật khẩu mới cho người dùng
            $sql = "UPDATE nguoi_dung SET mat_khau = :mat_khau WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':mat_khau' => $mat_khau_hashed,
                ':id'       => $_SESSION['reset_user_id']
            ]);

            // Quan trọng: Xóa session sau khi đã sử dụng để tránh tấn công chiếm quyền
            unset($_SESSION['reset_token']);
            unset($_SESSION['reset_user_id']);
            unset($_SESSION['reset_email']); // Xóa luôn email nếu có

            // Thông báo và chuyển hướng
            echo "<script>
                    alert('Đặt lại mật khẩu thành công! Vui lòng đăng nhập với mật khẩu mới.');
                    window.location.href = '../../views/auth/dangnhap.html';
                  </script>";
            exit;

        } catch (Exception $e) {
            // Ghi lại lỗi thực tế
            error_log("Lỗi đặt lại mật khẩu: " . $e->getMessage());
            $error_message = 'Đã có lỗi hệ thống xảy ra. Vui lòng thử lại sau.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Zolux 4335 - Đặt lại Mật khẩu</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen flex items-center justify-center bg-gradient-to-r from-blue-100 via-white to-blue-50">
  <div class="w-full max-w-md bg-white rounded-2xl shadow-xl p-8">
    <h1 class="text-2xl font-bold text-center text-gray-800 mb-2">Tạo Mật khẩu Mới</h1>
    <p class="text-center text-gray-500 mb-6">Mật khẩu mới của bạn phải có ít nhất 8 ký tự.</p>
    
    <?php if (!empty($error_message)): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg relative mb-4" role="alert">
            <?= htmlspecialchars($error_message) ?>
        </div>
    <?php endif; ?>

    <form method="POST" id="formDatLaiMatKhau" class="space-y-5">
      <div>
        <label for="mat_khau_moi" class="block text-sm font-medium text-gray-600 mb-2">Mật khẩu mới</label>
        <input type="password" id="mat_khau_moi" name="mat_khau_moi" placeholder="••••••••"
          class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-transparent" required />
      </div>
      <div>
        <label for="xac_nhan_mat_khau" class="block text-sm font-medium text-gray-600 mb-2">Xác nhận mật khẩu mới</label>
        <input type="password" id="xac_nhan_mat_khau" name="xac_nhan_mat_khau" placeholder="••••••••"
          class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-transparent" required />
      </div>
      <button type="submit" id="btnDatLai"
        class="w-full bg-blue-600 text-white font-semibold py-3 rounded-xl hover:bg-blue-700 transition duration-300 shadow-md">
        Đặt lại Mật khẩu
      </button>
    </form>
  </div>

<script>
// JavaScript để validation cơ bản phía client
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('formDatLaiMatKhau');
    const newPass = document.getElementById('mat_khau_moi');
    const confirmPass = document.getElementById('xac_nhan_mat_khau');
    const submitButton = document.getElementById('btnDatLai');

    form.addEventListener('submit', function(event) {
        if (newPass.value.length < 8) {
            alert('Mật khẩu phải có ít nhất 8 ký tự.');
            event.preventDefault(); // Ngăn form submit
            return;
        }
        if (newPass.value !== confirmPass.value) {
            alert('Mật khẩu và xác nhận mật khẩu không khớp.');
            event.preventDefault(); // Ngăn form submit
            return;
        }
        
        // Vô hiệu hóa nút khi submit để tránh click đúp
        submitButton.disabled = true;
        submitButton.textContent = 'Đang xử lý...';
    });
});
</script>
</body>
</html>