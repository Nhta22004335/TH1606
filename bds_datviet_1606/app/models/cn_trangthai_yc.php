<?php
header('Content-Type: application/json');
require_once "../../config/database.php"; // Đảm bảo đường dẫn này chính xác

/**
 * Hàm helper để tạo HTML cho badge trạng thái mới.
 */
function getStatusBadgeHtml($status) {
    $map = [
        'choxuly' => ['text' => 'Chờ xử lý', 'class' => 'bg-orange-100 text-orange-800'],
        'daduyet' => ['text' => 'Đã duyệt', 'class' => 'bg-green-100 text-green-800'],
        'dahuy'   => ['text' => 'Đã hủy', 'class' => 'bg-red-100 text-red-700'],
    ];
    $info = $map[$status] ?? ['text' => ucfirst($status), 'class' => 'bg-gray-100 text-gray-700'];
    $text = htmlspecialchars($info['text']);
    return "<span class=\"px-2.5 py-0.5 {$info['class']} rounded-full text-xs font-medium\">{$text}</span>";
}

/**
 * Hàm helper để tạo lại các nút hành động tương ứng với trạng thái mới.
 */
function getActionsHtml($id, $newStatus) {
    $html = '<div class="flex justify-center items-center gap-4">';
    // Nút xem chi tiết luôn hiển thị
    $html .= '<button class="action-btn text-sm text-indigo-600 hover:text-indigo-800 transition" data-id="' . $id . '" data-action="view"><i class="fas fa-eye"></i></button>';

    if ($newStatus == "choxuly") {
        $html .= '<button class="action-btn text-sm text-green-600 hover:text-green-800 transition" data-id="' . $id . '" data-action="daduyet"><i class="fas fa-check"></i></button>';
        $html .= '<button class="action-btn text-sm text-red-600 hover:text-red-800 transition" data-id="' . $id . '" data-action="dahuy"><i class="fas fa-times-circle"></i></button>';
    } else { // 'daduyet' hoặc 'dahuy'
        $html .= '<button class="action-btn text-sm text-yellow-400 hover:text-yellow-500 transition" data-id="' . $id . '" data-action="choxuly"><i class="fas fa-rotate-left"></i></button>';
    }
    $html .= '</div>';
    return $html;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Phương thức không hợp lệ.']);
    exit();
}

$id = $_POST['id'] ?? null;
$newStatus = $_POST['newStatus'] ?? null;

if (!$id || !$newStatus) {
    echo json_encode(['status' => 'error', 'message' => 'Thiếu ID hoặc trạng thái mới.']);
    exit();
}

try {
    $pdo = ketnoicsdl();
    $stmt = $pdo->prepare("UPDATE yeu_cau SET trang_thai = :trang_thai WHERE id = :id");
    $stmt->execute([':trang_thai' => $newStatus, ':id' => $id]);

    if ($stmt->rowCount() > 0) {
        echo json_encode([
            'status' => 'success',
            'message' => 'Cập nhật trạng thái thành công!',
            'newStatusHtml' => getStatusBadgeHtml($newStatus),
            'newActionsHtml' => getActionsHtml($id, $newStatus)
        ]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Không tìm thấy yêu cầu hoặc không có gì thay đổi.']);
    }

} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Lỗi cơ sở dữ liệu.']);
}
?>