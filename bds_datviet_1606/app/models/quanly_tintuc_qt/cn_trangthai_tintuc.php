<?php
header('Content-Type: application/json');
require_once "../../../config/database.php"; // Đảm bảo đường dẫn này chính xác

/**
 * Hàm helper để tạo lại các nút hành động tương ứng với trạng thái mới.
 * @param string $id ID của tin tức
 * @param string $newStatus Trạng thái mới ('choduyet' hoặc 'dangban')
 * @return string HTML của các nút hành động
 */
function getActionsHtml($id, $newStatus) {
    $html = '<div class="flex justify-center items-center gap-x-4">';
    $html .= '<a href="#" class="text-xs text-blue-600 hover:text-blue-800 transition-colors"><i class="fa-solid fa-eye text-base"></i></a>';

    if ($newStatus === 'choduyet') {
        $html .= '<button type="button" class="text-xs action-btn text-green-600 hover:text-green-800 transition-colors" data-id="' . $id . '" data-action="approve"><i class="fa-solid fa-check-circle text-base"></i></button>';
    } elseif ($newStatus === 'dangban') {
        $html .= '<button type="button" class="text-xs action-btn text-yellow-400 hover:text-yellow-500 transition-colors" data-id="' . $id . '" data-action="undo"><i class="fa-solid fa-rotate-left text-base"></i></button>';
    }
    
    $html .= '<button type="button" class="text-xs action-btn text-red-600 hover:text-red-800 transition-colors" data-id="' . $id . '" data-action="delete"><i class="fa-solid fa-trash-can text-base"></i></button>';
    $html .= '</div>';
    return $html;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Phương thức không hợp lệ.']);
    exit();
}

$id = $_POST['id'] ?? null;
$action = $_POST['action'] ?? null;

if (!$id || !$action) {
    echo json_encode(['status' => 'error', 'message' => 'Thiếu ID hoặc hành động.']);
    exit();
}

try {
    $pdo = ketnoicsdl();
    $response = ['status' => 'error', 'message' => 'Hành động không xác định.'];

    // Cập nhật trạng thái (Duyệt, Hoàn tác)
    if ($action === 'approve' || $action === 'undo') {
        $new_status = ($action === 'approve') ? 'dangban' : 'choduyet';
        $stmt = $pdo->prepare("UPDATE tin_tuc SET trang_thai = :trang_thai WHERE id = :id");
        $stmt->execute([':trang_thai' => $new_status, ':id' => $id]);
        
        $response = [
            'status' => 'success',
            'message' => 'Cập nhật trạng thái thành công!',
            'newActionsHtml' => getActionsHtml($id, $new_status)
        ];
    // Xóa
    } elseif ($action === 'delete') {
        $stmt = $pdo->prepare("DELETE FROM tin_tuc WHERE id = :id");
        $stmt->execute([':id' => $id]);
        
        if ($stmt->rowCount() > 0) {
            $response = ['status' => 'success', 'message' => 'Đã xóa tin đăng thành công!'];
        } else {
            $response['message'] = 'Không tìm thấy tin đăng để xóa.';
        }
    }

    echo json_encode($response);

} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Lỗi cơ sở dữ liệu.']);
}
?>