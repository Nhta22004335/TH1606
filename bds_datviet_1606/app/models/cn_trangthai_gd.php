<?php

header('Content-Type: application/json');
require_once "../../config/database.php"; // Đảm bảo đường dẫn này chính xác

/**
 * Hàm helper để tạo HTML cho badge trạng thái mới.
 */
function getStatusBadgeHtml($status) {
    $map = [
        'choxuly' => ['text' => 'Chờ xử lý', 'class' => 'bg-yellow-100 text-yellow-800 border-yellow-200'],
        'dangxuly' => ['text' => 'Đang xử lý', 'class' => 'bg-blue-100 text-blue-800 border-blue-200'],
        'hoantat' => ['text' => 'Hoàn tất', 'class' => 'bg-green-100 text-green-800 border-green-200'],
        'dahuy' => ['text' => 'Đã hủy', 'class' => 'bg-red-100 text-red-800 border-red-200']
    ];
    $info = $map[$status] ?? ['text' => 'Không rõ', 'class' => 'bg-gray-100 text-gray-800'];
    $text = htmlspecialchars($info['text']);
    return "<span class=\"px-2.5 py-1 text-xs font-semibold rounded-full border {$info['class']}\">{$text}</span>";
}

/**
 * Hàm helper để tạo lại các nút hành động tương ứng với trạng thái mới.
 */
function getActionsHtml($id, $newStatus) {
    // Luôn dùng đường dẫn tuyệt đối để tránh lỗi
    $detailUrl = htmlspecialchars("/app/trangchu.php?page=ct_giaodich&id=" . $id);
    
    $html = '<div class="py-1" role="none">';
    if ($newStatus === 'choxuly') {
        $html .= '<button type="button" class="action-btn w-full text-left px-4 py-2 text-sm text-slate-700 hover:bg-slate-100" data-id="' . $id . '" data-action="dangxuly">Bắt đầu xử lý</button>';
        $html .= '<button type="button" class="action-btn w-full text-left px-4 py-2 text-sm text-slate-700 hover:bg-slate-100" data-id="' . $id . '" data-action="dahuy">Hủy giao dịch</button>';
    }
    if ($newStatus === 'dangxuly') {
        $html .= '<button type="button" class="action-btn w-full text-left px-4 py-2 text-sm text-slate-700 hover:bg-slate-100" data-id="' . $id . '" data-action="hoantat">Hoàn tất</button>';
        $html .= '<button type="button" class="action-btn w-full text-left px-4 py-2 text-sm text-slate-700 hover:bg-slate-100" data-id="' . $id . '" data-action="dahuy">Hủy giao dịch</button>';
    }
    
    $html .= '<a href="' . $detailUrl . '" class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-100">Xem chi tiết</a>';
    $html .= '<div class="border-t my-1 border-slate-100"></div>';
    $html .= '<button type="button" class="action-btn w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50 hover:text-red-700" data-id="' . $id . '" data-action="xoa">Xóa</button>';
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

    if (in_array($action, ['dangxuly', 'hoantat', 'dahuy'])) {
        $stmt = $pdo->prepare("UPDATE giao_dich SET trang_thai = :trang_thai WHERE id = :id");
        $stmt->execute([':trang_thai' => $action, ':id' => $id]);
        
        $response = [
            'status' => 'success',
            'message' => 'Cập nhật trạng thái thành công!',
            'newStatusHtml' => getStatusBadgeHtml($action),
            'newActionsHtml' => getActionsHtml($id, $action)
        ];
    } elseif ($action === 'xoa') {
        $stmt = $pdo->prepare("DELETE FROM giao_dich WHERE id = :id");
        $stmt->execute([':id' => $id]);
        
        if ($stmt->rowCount() > 0) {
            $response = ['status' => 'success', 'message' => 'Đã xóa giao dịch thành công!'];
        } else {
            $response['message'] = 'Không tìm thấy giao dịch để xóa.';
        }
    }

    echo json_encode($response);

} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Lỗi cơ sở dữ liệu.']);
}
?>