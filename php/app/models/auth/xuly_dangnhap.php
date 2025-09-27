<?php
session_start();
date_default_timezone_set('Asia/Ho_Chi_Minh');

header('Content-Type: application/json');

$rawData = file_get_contents("php://input");
$data = json_decode($rawData, true);

require_once "../../../config/database.php";
$pdo = ketnoicsdl();

$ten_dang_nhap = trim($data['tendangnhap'] ?? '');
$mat_khau = trim($data['matkhau'] ?? '');

$sql = "SELECT * FROM nguoi_dung WHERE ten_dang_nhap = :ten_dang_nhap LIMIT 1";
$stmt = $pdo->prepare($sql);
$stmt->execute([':ten_dang_nhap' => $ten_dang_nhap]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

function kiemTraHoatDong($pdo, $id, $ten_dang_nhap) {
    $sql = "SELECT 1 FROM nguoi_dung WHERE id = :id AND trang_thai = 'danghoatdong' AND ten_dang_nhap = :ten_dang_nhap";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id' => $id, ':ten_dang_nhap' => $ten_dang_nhap]);
    return (bool) $stmt->fetchColumn();
}

function luuPhienVaoCSDL($pdo, $id_nguoi_dung) {
    try {
        $het_han = date('Y-m-d H:i:s', strtotime('+7 days')); 
        $token_phien = bin2hex(random_bytes(16));

        $stmt = $pdo->prepare("INSERT INTO phien_dang_nhap (id_nguoi_dung, token_phien, het_han) 
                                VALUES (:id_nguoi_dung, :token_phien, :het_han) RETURNING id");

        $stmt->execute([':id_nguoi_dung' => $id_nguoi_dung, ':token_phien' => $token_phien, ':het_han' => $het_han]);

        $id = $stmt->fetchColumn();

        setcookie("token_phien", $token_phien, time() + (86400 * 7), "/", "", true, true); 
        setcookie("id_nguoi_dung", $id_nguoi_dung, time() + (86400 * 7), "/", "", true, true);

        $_SESSION['id_phien'] = $id;
        $_SESSION['token_phien'] = $token_phien;
        $_SESSION['id_nguoi_dung'] = $id_nguoi_dung;

    } catch (PDOException $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Phiên đã tồn tại hoặc đã hết hạn. Vui lòng đăng nhập lại!'
        ]);
        exit;
    }
}

function luuLichSuNguoiDung($pdo, $id_nguoi_dung, $loai_su_kien = 'dangnhap') {
    $stmt = $pdo->prepare("
        INSERT INTO lich_su_xac_thuc (id_nguoi_dung, loai_su_kien, dia_chi_ip, user_agent)
        VALUES (:id_nguoi_dung, :loai_su_kien, :dia_chi_ip, :user_agent)
        RETURNING id
    ");
    $stmt->execute([
        ':id_nguoi_dung' => $id_nguoi_dung,
        ':loai_su_kien'  => $loai_su_kien,
        ':dia_chi_ip'    => $_SERVER['REMOTE_ADDR'] ?? null,
        ':user_agent'    => $_SERVER['HTTP_USER_AGENT'] ?? null
    ]);
    $idlichsu = $stmt->fetchColumn();
    $_SESSION['id_lich_su'] = $idlichsu;
}

if ($user) {
    $command = "/opt/venv/bin/python ../../helpers/xuly_matkhau.py " . escapeshellarg($mat_khau) . " " . escapeshellarg($user['mat_khau']);
    $result = shell_exec($command);
    if (trim($result) === 'true') {
        $kq = kiemTraHoatDong($pdo, $user['id'], $user['ten_dang_nhap']);
        if (!$kq) {
            echo json_encode([
                'success' => false,
                'message' => 'Tài khoản tạm thời ngưng hoạt động hoặc bị khóa!'
            ]);
            exit;
        }
        luuPhienVaoCSDL($pdo, $user['id']);
        luuLichSuNguoiDung($pdo, $user['id']);
        echo json_encode([
            'success' => true,
            'message' => 'Đăng nhập thành công!'
        ]);
        exit;
    }
}
 

?>