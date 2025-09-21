<?php
require_once __DIR__ . '/database_mg.php'; // file kết nối PDO

$username = "admin";
$password = "123456"; // mật khẩu muốn đặt lại
$fullname = "Administrator";
$email = "admin@example.com";

try {
    // Hash mật khẩu mới
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

    // Kiểm tra xem admin đã tồn tại chưa
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        // Nếu tồn tại thì cập nhật lại mật khẩu + thông tin
        $upd = $pdo->prepare("
            UPDATE users 
            SET password = ?, fullname = ?, email = ?, created_at = NOW() 
            WHERE username = ?
        ");
        $upd->execute([$hashedPassword, $fullname, $email, $username]);
        echo "🔄 Đã reset mật khẩu admin về <b>$password</b>";
    } else {
        // Nếu chưa có thì tạo mới
        $ins = $pdo->prepare("
            INSERT INTO users (username, password, fullname, email, created_at)
            VALUES (?, ?, ?, ?, NOW())
        ");
        $ins->execute([$username, $hashedPassword, $fullname, $email]);
        echo "✅ Đã tạo tài khoản admin (mật khẩu: <b>$password</b>)";
    }
} catch (Exception $e) {
    echo "❌ Lỗi: " . $e->getMessage();
}
