<?php
    // Cài đặt session an toàn
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_secure', isset($_SERVER['HTTPS']));

    session_start();

    date_default_timezone_set('Asia/Ho_Chi_Minh');
    header('Content-Type: application/json');

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
    $mat_khau = $data['matkhau'] ?? '';
    $remember_me = !empty($data['remember']);

    if (empty($ten_dang_nhap) || empty($mat_khau)) {
        json_response(false, "Vui lòng nhập đầy đủ tên đăng nhập và mật khẩu.");
    }

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
            
            $cookie_expiry = time() + (86400 * 30); 
            setcookie("selector", $selector, $cookie_expiry, "/", "", true, true);
            setcookie("verifier", $verifier, $cookie_expiry, "/", "", true, true);

        } catch (PDOException $e) {
            error_log("Lỗi khi lưu phiên Remember Me: " . $e->getMessage());
        }
    }

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
            
            setcookie("id_lich_su", $idlichsu, 0, "/", "", true, true); 

        } catch (PDOException $e) {
            error_log("Lỗi khi lưu lịch sử người dùng: " . $e->getMessage());
        }
    }

    $sql = "SELECT id, mat_khau, trang_thai FROM nguoi_dung WHERE ten_dang_nhap = :ten_dang_nhap LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':ten_dang_nhap' => $ten_dang_nhap]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($mat_khau, $user['mat_khau'])) {

        if ($user['trang_thai'] !== 'danghoatdong') {
            luuLichSuNguoiDung($pdo, $user['id'], 'dangnhap_thatbai_bivohieuhoa');
            json_response(false, 'Đăng nhập thất bại!');
        }

        session_regenerate_id(true);

        $_SESSION['id_nguoi_dung'] = $user['id'];

        luuLichSuNguoiDung($pdo, $user['id'], 'dangnhap_thanhcong');

        if ($remember_me) {
            luuPhienRememberMe($pdo, $user['id']);
        }

        json_response(true, 'Đăng nhập thành công!');

    } else {
        if ($user) {
            luuLichSuNguoiDung($pdo, $user['id'], 'dangnhap_thatbai_saimatkhau');
        }
        json_response(false, 'Đăng nhập thất bại!');
    }
?>