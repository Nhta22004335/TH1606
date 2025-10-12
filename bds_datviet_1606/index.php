<?php
// Luôn bắt đầu với các cài đặt session an toàn
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', isset($_SERVER['HTTPS'])); // Chỉ gửi cookie qua HTTPS

session_start();

require_once "config/database.php";
$pdo = ketnoicsdl();

// ==============================================================================
// BƯỚC 1: KHÔI PHỤC PHIÊN TỪ COOKIE "REMEMBER ME" (NẾU CẦN)
// Khối này chỉ chạy khi session hiện tại chưa tồn tại.
// ==============================================================================
if (!isset($_SESSION['id_nguoi_dung']) && isset($_COOKIE['selector'], $_COOKIE['verifier'])) {
    $selector = $_COOKIE['selector'];
    $verifier = $_COOKIE['verifier'];

    $stmt = $pdo->prepare(
        "SELECT id_nguoi_dung, verifier_hash 
         FROM phien_dang_nhap 
         WHERE selector = :selector AND het_han > NOW()"
    );
    $stmt->execute([':selector' => $selector]);

    if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        if (hash_equals($row['verifier_hash'], hash('sha256', $verifier))) {
            // Khôi phục session thành công
            session_regenerate_id(true); // Chống tấn công Session Fixation
            $_SESSION['id_nguoi_dung'] = $row['id_nguoi_dung'];
        } else {
            // Verifier sai -> có dấu hiệu tấn công, xóa token khỏi CSDL và cookie
            $delStmt = $pdo->prepare("DELETE FROM phien_dang_nhap WHERE selector = :selector");
            $delStmt->execute([':selector' => $selector]);
            clearAuthCookies();
        }
    } else {
        // Selector không tồn tại hoặc hết hạn -> Xóa cookie rác
        clearAuthCookies();
    }
}


// ==============================================================================
// BƯỚC 2: CỔNG KIỂM TRA XÁC THỰC DUY NHẤT
// Sau khi đã thử khôi phục từ cookie, đây là điểm kiểm tra cuối cùng.
// ==============================================================================
if (!isset($_SESSION['id_nguoi_dung'])) {
    // Nếu đến đây mà vẫn không có session, người dùng chắc chắn chưa được xác thực.
    header("Location: app/views/auth/dangnhap.html");
    exit;
}


// ==============================================================================
// BƯỚC 3: ĐÃ XÁC THỰC -> LẤY QUYỀN VÀ ĐIỀU HƯỚNG
// Logic này chỉ chạy khi người dùng đã được xác thực thành công.
// ==============================================================================
try {
    $id_nguoi_dung = $_SESSION['id_nguoi_dung'];
    $dsQuyen = ckQuyenTaiKhoan($pdo, $id_nguoi_dung);

    if (in_array('quantri', $dsQuyen) || in_array('moigioi', $dsQuyen)) {
        header("Location: app/views/quan_ly/trangchu.php");
        exit;
    } 
    else if (in_array('khachhang', $dsQuyen)) {
        header("Location: app/views/khach_hang/trangchu.php");
        exit;
    }
    else {
        // Người dùng đã đăng nhập nhưng không có vai trò hợp lệ.
        // Hủy phiên và yêu cầu đăng nhập lại.
        session_destroy();
        clearAuthCookies();
        header("Location: app/views/auth/dangnhap.html?error=unauthorized");
        exit;
    }
} catch (PDOException $e) {
    error_log("Lỗi CSDL khi kiểm tra quyền tại index.php: " . $e->getMessage());
    http_response_code(500);
    echo "Hệ thống đang gặp sự cố. Vui lòng thử lại sau.";
    exit;
}


// ==============================================================================
// KHAI BÁO CÁC HÀM TIỆN ÍCH
// ==============================================================================

/**
 * Xóa các cookie xác thực khỏi trình duyệt của người dùng.
 */
function clearAuthCookies() {
    // Đảm bảo cookie được xóa bằng cách đặt thời gian hết hạn trong quá khứ.
    $options = ['expires' => time() - 3600, 'path' => '/', 'httponly' => true];
    setcookie("selector", "", $options);
    setcookie("verifier", "", $options);
}

/**
 * Lấy danh sách vai trò (quyền) của một người dùng từ CSDL.
 */
function ckQuyenTaiKhoan($pdo, $id_nguoi_dung) {
    if (empty($id_nguoi_dung)) {
        return [];
    }
    $sql = "SELECT q.vai_tro
            FROM phan_quyen pq
            JOIN quyen q ON pq.id_quyen = q.id
            WHERE pq.id_nguoi_dung = :id_nguoi_dung";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id_nguoi_dung' => $id_nguoi_dung]);
    return $stmt->fetchAll(PDO::FETCH_COLUMN); 
}
?>