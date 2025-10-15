<?php
// models/api_bds_actions.php

header('Content-Type: application/json');
require_once "../../config/database.php"; // Đảm bảo đường dẫn này chính xác

/**
 * Hàm helper để tạo HTML cho badge trạng thái mới.
 * @param string $status Trạng thái mới ('chuaduyet' hoặc 'daduyet')
 * @return string HTML của badge
 */
function getStatusBadgeHtml($status) {
    $map = [
        'chuaduyet' => ['text' => 'Chờ duyệt', 'classes' => 'bg-yellow-100 text-yellow-800'],
        'daduyet'   => ['text' => 'Đã duyệt', 'classes' => 'bg-green-100 text-green-800'],
    ];
    $info = $map[$status] ?? ['text' => 'Không rõ', 'classes' => 'bg-gray-100 text-gray-800'];
    // Dùng htmlspecialchars để an toàn
    $text = htmlspecialchars($info['text']);
    return "<span class=\"px-2.5 py-1 text-xs font-semibold rounded-full {$info['classes']}\">{$text}</span>";
}

/**
 * Hàm helper để tạo lại các nút hành động tương ứng với trạng thái mới.
 * @param string $id ID của bất động sản
 * @param string $status Trạng thái mới
 * @return string HTML của các nút hành động
 */
function getActionsHtml($id, $status) {
    // Luôn dùng đường dẫn tuyệt đối để tránh lỗi
    $detailUrl = htmlspecialchars("/app/trangchu.php?page=ct_sanpham&id=" . $id);
    
    $html = '<div class="flex justify-center items-center gap-4">';
    if ($status === 'chuaduyet') {
        $html .= '<button type="button" class="action-btn text-green-600 hover:text-green-800 text-sm font-medium" data-id="' . $id . '" data-action="duyet">Duyệt</button>';
    } elseif ($status === 'daduyet') {
        $html .= '<button type="button" class="action-btn text-yellow-600 hover:text-yellow-800 text-sm font-medium" data-id="' . $id . '" data-action="hoantac">Hoàn tác</button>';
    }
    $html .= '<a href="' . $detailUrl . '" class="text-blue-600 hover:text-blue-800 text-sm font-medium">Chi tiết</a>';
    $html .= '<button type="button" class="action-btn text-red-600 hover:text-red-800 text-sm font-medium" data-id="' . $id . '" data-action="xoa">Xóa</button>';
    $html .= '</div>';
    return $html;
}

// Chỉ chấp nhận phương thức POST
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

    // Xử lý Duyệt hoặc Hoàn tác
    if ($action === 'duyet' || $action === 'hoantac') {
        $new_status = ($action === 'duyet') ? 'daduyet' : 'chuaduyet';
        $stmt = $pdo->prepare("UPDATE bat_dong_san SET trang_thai = :trang_thai WHERE id = :id");
        $stmt->execute([':trang_thai' => $new_status, ':id' => $id]);
        
        $response = [
            'status' => 'success',
            'message' => 'Cập nhật trạng thái thành công!',
            'newStatusHtml' => getStatusBadgeHtml($new_status),
            'newActionsHtml' => getActionsHtml($id, $new_status)
        ];
    // Xử lý Xóa
    } elseif ($action === 'xoa') {
        $stmt = $pdo->prepare("DELETE FROM bat_dong_san WHERE id = :id");
        $stmt->execute([':id' => $id]);
        
        if ($stmt->rowCount() > 0) {
            $response = ['status' => 'success', 'message' => 'Đã xóa tin đăng thành công!'];
        } else {
            $response['message'] = 'Không tìm thấy tin đăng để xóa.';
        }
    }

    echo json_encode($response);

} catch (PDOException $e) {
    // Trong môi trường production, chỉ nên ghi log lỗi thay vì gửi về client
    // error_log($e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Lỗi cơ sở dữ liệu. Vui lòng thử lại.']);
}
?>