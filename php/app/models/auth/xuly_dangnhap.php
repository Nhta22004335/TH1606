<?php
date_default_timezone_set('Asia/Ho_Chi_Minh');

header('Content-Type: application/json');

$rawData = file_get_contents("php://input");
$data = json_decode($rawData, true);

require_once "../../../config/database.php";
$pdo = ketnoicsdl();

// $tendangnhap = trim($data['tendangnhap'] ?? '');
// $matkhau = trim($data['matkhau'] ?? '');

$ten_dang_nhap = 'nguyenvana';
$mat_khau = 'demo@123';

$sql = "SELECT * FROM nguoi_dung WHERE ten_dang_nhap = :ten_dang_nhap";
$stmt = $pdo->prepare($sql);
$stmt->execute([':ten_dang_nhap' => $ten_dang_nhap]);
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
print_r($result);


function kiemTraHoatDong($pdo, $id, $ten_dang_nhap) {
    $sql = "SELECT 1 FROM nguoi_dung WHERE id = :id AND trang_thai = 'danghoatdong' AND ten_dang_nhap = :ten_dang_nhap";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id' => $id, ':ten_dang_nhap' => $ten_dang_nhap]);
    return (bool) $stmt->fetchColumn();
}

$sql = "SELECT * FROM nguoi_dung WHERE ten_dang_nhap = :ten_dang_nhap LIMIT 1";
$stmt = $pdo->prepare($sql);
$stmt->execute([':ten_dang_nhap' => $ten_dang_nhap]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user) {
    $command = "/opt/venv/bin/python ../../helpers/xuly_matkhau.py " . escapeshellarg($mat_khau) . " " . escapeshellarg($user['mat_khau']);
    $result = shell_exec($command);

    if (trim($result) === 'true') {
        $kq = kiemTraHoatDong($pdo, $user['id'], $user['ten_dang_nhap']);
        if ($kq) echo "OK!"; else echo "NO!";
    }
}

// function luuPhienVaoCSDL($conn, $idtaikhoan) {
//     try {
//         $hethan = date('Y-m-d H:i:s', strtotime('+7 days')); // ví dụ cho 7 ngày
//         $token = bin2hex(random_bytes(16));

//         $stmt = $conn->prepare("INSERT INTO phien_dang_nhap (idtaikhoan, tokenphien, hethan) 
//                                 VALUES (:idtaikhoan, :tokenphien, :hethan) RETURNING idphien");

//         $stmt->bindParam(':idtaikhoan', $idtaikhoan, PDO::PARAM_INT);
//         $stmt->bindParam(':tokenphien', $token, PDO::PARAM_STR);
//         $stmt->bindParam(':hethan', $hethan, PDO::PARAM_STR);
//         $stmt->execute();

//         $idphien = $stmt->fetchColumn();

//         // Lưu vào cookie để tồn tại sau khi đóng trình duyệt
//         setcookie("tokenphien", $token, time() + (86400 * 7), "/", "", true, true); 
//         setcookie("idtaikhoan", $idtaikhoan, time() + (86400 * 7), "/", "", true, true);

//         // Nếu vẫn muốn giữ session cho các request hiện tại
//         $_SESSION['idphien'] = $idphien;
//         $_SESSION['tokenphien'] = $token;
//         $_SESSION['idtaikhoan'] = $idtaikhoan;

//     } catch (PDOException $e) {
//         echo json_encode([
//             'success' => false,
//             'message' => 'Phiên đã tồn tại hoặc đã hết hạn. Vui lòng đăng nhập lại!'
//         ]);
//         exit;
//     }
// }


        

?>