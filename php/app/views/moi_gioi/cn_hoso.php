<?php
require_once "../../../config/database.php";

// Bắt đầu phiên SESSION nếu chưa bắt đầu (Cần thiết để lấy $_SESSION['id_nguoi_dung'])
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$pdo = ketnoicsdl();

$id_ben_ban = $_SESSION['id_nguoi_dung'] ?? '';
if (!$id_ben_ban) {
  header("Location: ../auth/dangnhap.html");
  exit;
}

$error = '';
$success = '';

// Xử lý form POST
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['tao_hoso'])) {
  $tieu_de = trim($_POST['tieu_de'] ?? '');
  $loai = $_POST['loai'] ?? '';
  $ben_mua = $_POST['ben_mua'] ?? '';
  
    if (empty($tieu_de) || empty($loai) || empty($ben_mua)) {
        $error = "Vui lòng điền đầy đủ các trường bắt buộc.";
    } else {
        // Xử lý upload tệp đính kèm
        $tep_dk = null;
        if (isset($_FILES['tep_dk']) && $_FILES['tep_dk']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = "../../../storage/documents/";
            
            // Đảm bảo thư mục tồn tại
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $originalFileName = basename($_FILES["tep_dk"]["name"]);
            $fileName = time() . "_" . preg_replace("/[^a-zA-Z0-9\.]/", "_", $originalFileName); // Bảo mật: Loại bỏ ký tự đặc biệt
            $filePath = $uploadDir . $fileName;
            
            if (move_uploaded_file($_FILES["tep_dk"]["tmp_name"], $filePath)) {
                $tep_dk = $fileName;
            } else {
                $error = "Lỗi khi upload tệp đính kèm. Kiểm tra quyền ghi thư mục!";
            }
        }
        
        if (!$error) {
            try {
                $stmt = $pdo->prepare("
                    INSERT INTO bieu_mau (tieu_de, loai, ben_mua, ben_ban, tep_dk, trang_thai, ngay_tao)
                    VALUES (:tieu_de, :loai, :ben_mua, :ben_ban, :tep_dk, 'choduyet', CURRENT_TIMESTAMP)
                ");
                $stmt->execute([
                    ':tieu_de' => $tieu_de,
                    ':loai' => $loai,
                    ':ben_mua' => $ben_mua,
                    ':ben_ban' => $id_ben_ban,
                    ':tep_dk' => $tep_dk
                ]);
            
                $success = "Tạo hồ sơ thành công! Đang chờ duyệt.";
                // Bạn có thể chuyển hướng sau khi hiển thị thông báo
                // echo "<script>window.location.href='trangchu.php?page=../moi_gioi/cn_hoso';</script>";
            
            } catch (PDOException $e) {
                $error = "Lỗi CSDL: " . $e->getMessage();
            }
        }
    }
}

// Lấy danh sách người dùng để chọn 'Người mua' (Đã sửa lỗi PDO)
try {
    // 1. Sử dụng prepare() cho truy vấn có tham số
    $sql_users = "
        SELECT 
        info.id_nguoi_dung, 
        info.ho_ten, 
        nd.email 
    FROM 
        info_nguoi_dung info 
    JOIN 
        nguoi_dung nd ON info.id_nguoi_dung = nd.id
    JOIN 
        phan_quyen pq ON nd.id = pq.id_nguoi_dung  -- JOIN qua bảng trung gian phan_quyen
    JOIN 
        quyen q ON pq.id_quyen = q.id              -- JOIN đến bảng quyen để lấy vai trò
    WHERE 
        nd.id != :ben_ban_id 
        AND q.vai_tro = 'khachhang'               -- Lọc chỉ Khách hàng
    ORDER BY
        info.ho_ten ASC;
    ";
    
    $usersStmt = $pdo->prepare($sql_users);
    
    // 2. Sử dụng execute() và cung cấp mảng tham số
    $usersStmt->execute([':ben_ban_id' => $id_ben_ban]);
    
    $nguoi_mua = $usersStmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    // Xử lý lỗi CSDL nếu cần
    // Thay vì exit, có thể gán $nguoi_mua = [] và hiển thị thông báo lỗi thân thiện hơn
    $nguoi_mua = [];
    // Lưu ý: Lỗi Fatal error ban đầu là do sử dụng query() sai cách, không phải lỗi kết nối
}

?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tạo hồ sơ mới</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        .form-input {
            transition: all 0.2s;
        }
        .form-input:focus {
            border-color: #3b82f6; /* blue-500 */
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.5); /* ring blue */
        }
    </style>
</head>
<body>
    <div class="max-w-xl mx-auto bg-white p-8 rounded-xl shadow-2xl border border-gray-100 mt-4">
        
        <h1 class="text-2xl font-bold text-gray-700 mb-6 border-b pb-3 flex items-center gap-3">
            <i class="fa-solid fa-file-signature text-blue-500"></i> Tạo hồ sơ/ biểu mẫu mới
        </h1>

        <?php if ($success): ?>
            <div class="p-4 mb-4 text-sm text-green-700 bg-green-100 rounded-lg flex items-center gap-2" role="alert">
                <i class="fa-solid fa-check-circle"></i>
                <span class="font-medium"><?= htmlspecialchars($success) ?></span>
            </div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="p-4 mb-4 text-sm text-red-700 bg-red-100 rounded-lg flex items-center gap-2" role="alert">
                <i class="fa-solid fa-triangle-exclamation"></i>
                <span class="font-medium"><?= htmlspecialchars($error) ?></span>
            </div>
        <?php endif; ?>

        <form action="" method="post" enctype="multipart/form-data" class="space-y-6">
            
            <div>
                <label for="tieu_de" class="block font-semibold text-gray-700 mb-1">
                    <i class="fa-solid fa-heading text-blue-400 mr-1"></i> Tiêu đề Hồ sơ <span class="text-red-500">*</span>
                </label>
                <input type="text" id="tieu_de" name="tieu_de" class="w-full outline-none border border-gray-300 px-4 py-2 rounded-lg bg-gray-50 form-input shadow-sm" required placeholder="Ví dụ: Hợp đồng mua bán BĐS số 001">
            </div>
            
            <div class="grid md:grid-cols-2 gap-6">
                <div>
                    <label for="loai" class="block font-semibold text-gray-700 mb-1">
                        <i class="fa-solid fa-list-alt text-blue-400 mr-1"></i> Loại Hồ sơ <span class="text-red-500">*</span>
                    </label>
                    <select id="loai" name="loai" class="w-full border border-gray-300 outline-none px-4 py-2 rounded-lg bg-white form-input shadow-sm appearance-none" required>
                        <option value="">-- Chọn loại hồ sơ --</option>
                        <option value="hosomuaban">Hồ sơ mua bán</option>
                        <option value="hosothue">Hồ sơ thuê</option>
                        <option value="bienban">Biểu mẫu đăng ký</option>
                    </select>
                </div>
                
                <div>
                    <label for="ben_mua" class="block font-semibold text-gray-700 mb-1">
                        <i class="fa-solid fa-user-tag text-blue-400 mr-1"></i> Người mua <span class="text-red-500">*</span>
                    </label>
                    <select id="ben_mua" name="ben_mua" class="w-full border border-gray-300 outline-none px-4 py-2 rounded-lg bg-white form-input shadow-sm appearance-none" required>
                        <option value="">-- Chọn người mua --</option>
                        <?php foreach($nguoi_mua as $user): ?>
                            <option value="<?= $user['id_nguoi_dung'] ?>"><?= htmlspecialchars($user['ho_ten']) ?> (<?= htmlspecialchars($user['email']) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            
            <div>
                <label for="tep_dk" class="block font-semibold text-gray-700 mb-1">
                    <i class="fa-solid fa-paperclip text-blue-400 mr-1"></i> Tệp đính kèm (.pdf, .doc, ảnh)
                </label>
                <input type="file" id="tep_dk" name="tep_dk" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" class="w-full border border-gray-300 p-2 rounded-lg bg-white form-input shadow-sm file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
            </div>
            
            <button type="submit" name="tao_hoso" class="w-full px-6 py-3 bg-blue-600 text-white font-bold rounded-lg hover:bg-blue-700 transition duration-300 shadow-md hover:shadow-lg transform hover:scale-[1.005]">
                <i class="fa-solid fa-plus-circle mr-1"></i> TẠO HỒ SƠ & GỬI DUYỆT
            </button>
            
        </form>
        
    </div>
</body>
</html>