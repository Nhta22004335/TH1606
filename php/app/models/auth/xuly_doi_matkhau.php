<?php
header('Content-Type: application/json');
require_once '../../config.php'; // file kết nối database

// Nhận dữ liệu JSON từ client
$rawData = file_get_contents("php://input");
$data = json_decode($rawData, true);

$tendangnhap = trim($data['tendangnhap'] ?? '');
$matkhaucu = trim($data['matkhaucu'] ?? '');
$matkhaumoi = trim($data['matkhaumoi'] ?? '');

$response = ['success' => false, 'message' => ''];

try {
    $db = new Database();
    $conn = $db->connect();

    // Kiểm tra tài khoản tồn tại
    $stmt = $conn->prepare("SELECT * FROM taikhoan WHERE tendangnhap = :tendangnhap LIMIT 1");
    $stmt->execute(['tendangnhap' => $tendangnhap]);
    $tk = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$tk) {
        $response['message'] = 'Tài khoản không tồn tại!';
        echo json_encode($response);
        exit;
    }

    $command = "python ../../xuly_matkhau.py " . escapeshellarg($matkhaucu) . " " . escapeshellarg($tk['matkhau']);
    $result = shell_exec($command);

    if (trim($result) !== 'true') {
        $response['message'] = 'Mật khẩu cũ không đúng!';
        echo json_encode($response);
        exit;
    }

    if ($matkhaucu == $tk['matkhau']) {
        $response['message'] = 'Mật khẩu mới phải khác mật khẩu cũ!';
        echo json_encode($response);
        exit;
    }    

    $command = "python ../../xuly_matkhau.py " . escapeshellarg($matkhaumoi);
    $result = shell_exec($command);
    $mknew = trim($result);

    // Cập nhật mật khẩu mới vào database
    $stmt = $conn->prepare("UPDATE taikhoan SET matkhau = :matkhaumoi WHERE tendangnhap = :tendangnhap");
    $stmt->execute(['matkhaumoi' => $mknew, 'tendangnhap' => $tk['tendangnhap']]);

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
