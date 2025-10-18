<?php
// File: ../../models/them_binh_luan.php
if (session_status() == PHP_SESSION_NONE) { session_start(); }
require_once "../../config/database.php";

header('Content-Type: application/json');
$response = ['success' => false, 'message' => 'Yêu cầu không hợp lệ.'];

// 1. Kiểm tra đăng nhập (Vẫn quan trọng để bảo mật)
$id_nguoi_dung = $_SESSION['id_nguoi_dung'] ?? null;
if (!$id_nguoi_dung) {
    $response['message'] = 'Vui lòng đăng nhập để bình luận.';
    echo json_encode($response);
    exit;
}

// 2. Lấy dữ liệu
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_bai_dang = $_POST['id_bai_dang'] ?? null;
    $noi_dung = trim($_POST['noi_dung'] ?? '');
    $id_cha = $_POST['id_cha'] ?? null;
    
    // ==========================================================
    // == LẤY THÔNG TIN USER TỪ POST THAY VÌ SESSION ==
    // ==========================================================
    $ho_ten_nguoi_dung = trim($_POST['ho_ten_nguoi_dung'] ?? 'Người dùng');
    $avt_nguoi_dung = trim($_POST['avt_nguoi_dung'] ?? 'avt.png');

    if (empty($id_bai_dang) || empty($noi_dung)) {
        $response['message'] = 'Nội dung bình luận không được để trống.';
        echo json_encode($response);
        exit;
    }
    
    if (empty($id_cha)) {
        $id_cha = null;
    }

    // 3. Insert vào Database
    try {
        $pdo = ketnoicsdl();
        
        $new_comment_id = $pdo->query("SELECT uuid_generate_v4()")->fetchColumn();
        $ngay_tao = date('Y-m-d H:i:s');

        $sql = "INSERT INTO binh_luan (id, id_bai_dang, id_nguoi_dung, id_cha, noi_dung, ngay_tao) 
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        
        if ($stmt->execute([$new_comment_id, $id_bai_dang, $id_nguoi_dung, $id_cha, $noi_dung, $ngay_tao])) {
            
            // ==========================================================
            // == TRẢ VỀ DỮ LIỆU ĐÃ LẤY TỪ POST ==
            // ==========================================================
            $response = [
                'success' => true,
                'message' => 'Đã gửi bình luận!',
                'newComment' => [
                    'id' => $new_comment_id,
                    'id_cha' => $id_cha,
                    'noi_dung' => $noi_dung,
                    'ngay_tao' => $ngay_tao,
                    'ho_ten' => $ho_ten_nguoi_dung, // Dữ liệu từ POST
                    'avt' => $avt_nguoi_dung     // Dữ liệu từ POST
                ]
            ];
            
        } else {
            $response['message'] = 'Không thể lưu bình luận vào CSDL.';
        }
    } catch (PDOException $e) {
        $response['message'] = 'Lỗi CSDL: ' . $e->getMessage();
    }
}

echo json_encode($response);
?>