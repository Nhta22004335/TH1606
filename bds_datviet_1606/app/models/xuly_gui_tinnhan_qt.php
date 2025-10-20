<?php
// Bắt đầu session để lấy ID admin
session_start();

require_once "../../config/database.php"; // Đảm bảo đường dẫn này đúng
$pdo = ketnoicsdl();

// --- 1. LẤY THAM SỐ TỪ URL VÀ SESSION ---
$id_hop_thoai = $_GET['id'] ?? null; // Lấy ID hội thoại từ URL (do fetch gửi)
$id_admin_dang_nhap = $_SESSION['id_nguoi_dung'] ?? null; // ID của Admin

if (!$id_hop_thoai || !$id_admin_dang_nhap) {
    http_response_code(400); 
    echo json_encode(['error' => 'Yêu cầu không hợp lệ hoặc thiếu thông tin.']);
    exit;
}

$thong_bao = "";

// --- 2. XỬ LÝ GỬI FORM (AJAX/FETCH) ---
// File này chỉ xử lý POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $noi_dung = trim($_POST['noi_dung'] ?? '');
    $anh_path_db = null;

    if (isset($_FILES['anh_tn']) && $_FILES['anh_tn']['error'] == 0) {
        $upload_dir = '../../../uploads/chat_images/'; 
        if (!is_dir($upload_dir)) { @mkdir($upload_dir, 0755, true); }
        $file_name = uniqid() . '-' . basename($_FILES['anh_tn']['name']);
        $target_file = $upload_dir . $file_name;
        $check = @getimagesize($_FILES['anh_tn']['tmp_name']);
        
        if ($check !== false) {
            if (move_uploaded_file($_FILES['anh_tn']['tmp_name'], $target_file)) {
                $anh_path_db = '/uploads/chat_images/' . $file_name; 
            } else { $thong_bao = "Lỗi khi di chuyển file ảnh."; }
        } else { $thong_bao = "File không phải là ảnh."; }
    }

    if ((!empty($noi_dung) || $anh_path_db) && empty($thong_bao)) {
        try {
            $sql_insert = "INSERT INTO tin_nhan (id_hop_thoai, nguoi_gui, noi_dung, anh_tn, tg_gui)
                           VALUES (:id_hop_thoai, :nguoi_gui, :noi_dung, :anh_tn, NOW())
                           RETURNING tg_gui, (SELECT ten_dang_nhap FROM nguoi_dung WHERE id = :nguoi_gui) AS ten_nguoi_gui";
            
            $stmt_insert = $pdo->prepare($sql_insert);
            $stmt_insert->execute([
                ':id_hop_thoai' => $id_hop_thoai,
                ':nguoi_gui' => $id_admin_dang_nhap,
                ':noi_dung' => !empty($noi_dung) ? $noi_dung : null,
                ':anh_tn' => $anh_path_db
            ]);

            $new_message = $stmt_insert->fetch(PDO::FETCH_ASSOC);
            $tg_gui_str = date('H:i, d/m/Y', strtotime($new_message['tg_gui']));
            $ten_nguoi_gui = $new_message['ten_nguoi_gui'];

            // --- Trả về HTML của tin nhắn mới cho fetch ---
            ob_start();
            ?>
            <div class="flex justify-end group">
                <div class="max-w-xs lg:max-w-md p-3 bg-indigo-600 text-white rounded-l-lg rounded-br-lg shadow-md">
                    <div class="font-semibold text-xs text-indigo-200 mb-1">
                        <ion-icon name="checkmark-circle-outline"></ion-icon>
                         Bạn (<?= htmlspecialchars($ten_nguoi_gui) ?>)
                    </div>
                    <?php if (!empty($anh_path_db)): ?>
                        <img src="<?= htmlspecialchars($anh_path_db) ?>" alt="Hình ảnh" class="rounded-md mb-2 max-w-full h-auto">
                    <?php endif; ?>
                    <?php if (!empty($noi_dung)): ?>
                        <p class="text-sm"><?= htmlspecialchars($noi_dung) ?></p>
                    <?php endif; ?>
                    <div class="flex justify-end items-center mt-1 space-x-2">
                        <span class="text-xs text-indigo-200"><?= $tg_gui_str ?></span>
                    </div>
                </div>
            </div>
            <?php
            echo ob_get_clean(); 
            exit; 

        } catch (PDOException $e) { $thong_bao = "Lỗi CSDL: " . $e->getMessage(); }
    }
    
    // Nếu có lỗi (upload hoặc CSDL), gửi về thông báo lỗi
    if (!empty($thong_bao)) {
        http_response_code(400); 
        echo json_encode(['error' => $thong_bao]);
        exit;
    }
} else {
    // Nếu không phải là POST, báo lỗi
    http_response_code(405); // Method Not Allowed
    echo json_encode(['error' => 'Phương thức không hợp lệ.']);
    exit;
}
?>