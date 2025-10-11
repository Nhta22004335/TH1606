<?php
session_start();

// 1. Cấu hình và Kết nối CSDL
require_once __DIR__ . '/../config/database.php';
$pdo = ketnoicsdl();

// --- LOGIC XÁC THỰC VÀ PHỤC HỒI SESSION ---
// (Giữ nguyên logic kiểm tra cookie của bạn)
if (!isset($_SESSION['id_nguoi_dung']) && isset($_COOKIE['token_phien'], $_COOKIE['id_nguoi_dung'])) {
    $stmt = $pdo->prepare("SELECT id, id_nguoi_dung, token_phien FROM phien_dang_nhap WHERE id_nguoi_dung = :id_nguoi_dung AND token_phien = :token_phien AND het_han > NOW()");
    $stmt->execute([':id_nguoi_dung' => $_COOKIE['id_nguoi_dung'], ':token_phien' => $_COOKIE['token_phien']]);
    if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $_SESSION['id_phien']      = $row['id'];
        $_SESSION['id_nguoi_dung'] = $row['id_nguoi_dung'];
    }
}


// 2. LẤY URL YÊU CẦU TỪ TRÌNH DUYỆT
// parse_url sẽ loại bỏ các tham số query (ví dụ: ?id=123)
$request_uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);


// 3. LOGIC ĐỊNH TUYẾN (ROUTER)
switch ($request_uri) {
    // --- Các route không cần đăng nhập ---
    case '/dangnhap.html':
        // Di chuyển file dangnhap.html vào thư mục public
        require __DIR__ . '/dangnhap.html';
        break;

    // --- Route gốc (/) ---
    case '/':
        // Nếu chưa đăng nhập, chuyển đến trang đăng nhập
        if (empty($_SESSION['id_phien'])) {
            header('Location: /dangnhap.html');
            exit;
        }

        // Nếu đã đăng nhập, phân quyền và chuyển hướng
        $id_nguoi_dung = $_SESSION['id_nguoi_dung'];
        $dsQuyen = ckQuyenTaiKhoan($pdo, $id_nguoi_dung);

        if (in_array('quantri', $dsQuyen) || in_array('moigioi', $dsQuyen)) {
            header('Location: /quan-ly/trang-chu');
            exit;
        } else if (in_array('khachhang', $dsQuyen)) {
            header('Location: /khach-hang/trang-chu');
            exit;
        } else {
            // Trường hợp không có quyền hợp lệ
            header('Location: /dangnhap.html');
            exit;
        }
        break; // Dù có exit vẫn nên có break

    // --- Các route cần đăng nhập ---
    case '/quan-ly/trang-chu':
        // Yêu cầu file view tương ứng từ thư mục app
        require __DIR__ . '/../app/views/quan_ly/trangchu.php';
        break;

    case '/khach-hang/trang-chu':
        require __DIR__ . '/../app/views/khach_hang/trangchu_kh.php';
        break;

    // Thêm các case khác cho các trang của bạn ở đây
    // Ví dụ:
    // case '/quan-ly/san-pham':
    //     require __DIR__ . '/../app/views/quan_ly/san_pham.php';
    //     break;

    // --- Route mặc định cho các URL không khớp ---
    default:
        http_response_code(404); // Gửi mã lỗi 404
        require __DIR__ . '/../app/views/errors/404.php'; // Tải trang lỗi 404
        break;
}

// --- HÀM HỖ TRỢ ---
// Hàm này có thể được chuyển vào một file helper riêng để code gọn hơn
function ckQuyenTaiKhoan($pdo, $id_nguoi_dung) {
    $sql = "SELECT q.vai_tro FROM phan_quyen pq JOIN quyen q ON pq.id_quyen = q.id WHERE pq.id_nguoi_dung = :id_nguoi_dung";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id_nguoi_dung', $id_nguoi_dung, PDO::PARAM_INT); // Nên dùng PARAM_INT nếu id là số
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

?>