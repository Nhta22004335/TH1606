<?php
header('Content-Type: application/json');
require_once "../../../config/database.php";
$pdo = ketnoicsdl();

// Nhận dữ liệu JSON từ client
$rawData = file_get_contents("php://input");
$data = json_decode($rawData, true);

$ten_dang_nhap = trim($data['tendangnhap'] ?? '');
$matkhaucu = trim($data['matkhaucu'] ?? '');
$matkhaumoi = trim($data['matkhaumoi'] ?? '');

$response = ['success' => false, 'message' => ''];

try {

    // Kiểm tra tài khoản tồn tại
    $stmt = $dpo->prepare("SELECT * FROM nguoi_dung WHERE ten_dang_nhap = :ten_dang_nhap LIMIT 1");
    $stmt->execute([':ten_dang_nhap' => $ten_dang_nhap]);
    $tk = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$tk) {
        $response['message'] = 'Tài khoản không tồn tại!';
        echo json_encode($response);
        exit;
    }

    $command = "/opt/venv/bin/python ../../helpers/xuly_matkhau.py " . escapeshellarg($matkhaucu) . " " . escapeshellarg($tk['mat_khau']);
    $result = shell_exec($command);

    if (trim($result) !== 'true') {
        $response['message'] = 'Mật khẩu cũ không đúng!';
        echo json_encode($response);
        exit;
    }

    if ($matkhaucu == $tk['mat_khau']) {
        $response['message'] = 'Mật khẩu mới phải khác mật khẩu cũ!';
        echo json_encode($response);
        exit;
    }    

    $command = "/opt/venv/bin/python ../../helpers/xuly_matkhau.py " . escapeshellarg($matkhaumoi);
    $result = shell_exec($command);
    $mknew = trim($result);

    // Cập nhật mật khẩu mới vào database
    $stmt = $conn->prepare("UPDATE nguoi_dung SET mat_khau = :mknew WHERE ten_dang_nhap = :ten_dang_nhap");
    $stmt->execute(['mknew' => $mknew, 'ten_dang_nhap' => $tk['ten_dang_nhap']]);

    $response['success'] = true;
    $response['message'] = 'Đổi mật khẩu thành công!';
    echo json_encode($response);
    exit;
} catch (PDOException $e) {
    $response['message'] = 'Lỗi server: ' . $e->getMessage();
    echo json_encode($response);
    exit;
}
?>
