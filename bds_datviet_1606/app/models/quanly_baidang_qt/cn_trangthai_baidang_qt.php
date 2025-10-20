<?php
// File: ../../models/cn_trangthai_bd.php
require_once "../../../config/database.php";

// --- SAO CHÉP HÀM HELPER NÀY VÀO FILE API ---
function getStatusBadge($status) {
    $map = [
        'chuaduyet' => ['text' => 'Chờ duyệt', 'class' => 'bg-orange-100 text-orange-800'],
        'daduyet'   => ['text' => 'Đang hiển thị', 'class' => 'bg-green-100 text-green-800'],
        'hethan'    => ['text' => 'Hết hạn', 'class' => 'bg-red-100 text-red-800'],
        'dahuy'     => ['text' => 'Đã hủy', 'class' => 'bg-gray-100 text-gray-800'],
    ];
    $info = $map[$status] ?? ['text' => ucfirst($status), 'class' => 'bg-gray-100 text-gray-800'];
    return "<span class='inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {$info['class']}'>{$info['text']}</span>";
}
// ---------------------------------------------

header('Content-Type: application/json');
$response = ['status' => 'error', 'message' => 'Yêu cầu không hợp lệ.'];
$data = json_decode(file_get_contents('php://input'), true);

$id = $data['id'] ?? null;
$newStatus = $data['status'] ?? null;
$validStatuses = ['daduyet', 'an', 'hethan', 'daban', 'dathue', 'chuaduyet', 'dahuy']; // Thêm 'dahuy'

if ($id && $newStatus && in_array($newStatus, $validStatuses)) {
    try {
        $pdo = ketnoicsdl();
        $stmt = $pdo->prepare("UPDATE bai_dang SET trang_thai = ? WHERE id = ?");
        
        if ($stmt->execute([$newStatus, $id])) {
            $newStatusHtml = getStatusBadge($newStatus);

            // ==========================================================
            // == THAY ĐỔI LỚN: TẠO HTML CHO CÁC NÚT MỚI ==
            // ==========================================================
            $newActionsHtml = '';
            if ($newStatus === 'chuaduyet') {
                $newActionsHtml .= "<button class=\"action-btn w-full text-left block px-4 py-2 text-sm text-green-700 hover:bg-slate-100\" data-id=\"$id\" data-action=\"daduyet\" role=\"menuitem\"><i class=\"fa-solid fa-check mr-2\"></i>Duyệt bài</button>";
                $newActionsHtml .= "<button class=\"action-btn w-full text-left block px-4 py-2 text-sm text-red-700 hover:bg-slate-100\" data-id=\"$id\" data-action=\"dahuy\" role=\"menuitem\"><i class=\"fa-solid fa-ban mr-2\"></i>Hủy</button>";
            } elseif ($newStatus === 'daduyet') {
                $newActionsHtml .= "<button class=\"action-btn w-full text-left block px-4 py-2 text-sm text-yellow-700 hover:bg-slate-100\" data-id=\"$id\" data-action=\"hethan\" role=\"menuitem\"><i class=\"fa-solid fa-calendar-times mr-2\"></i>Đánh dấu Hết hạn</button>";
                $newActionsHtml .= "<button class=\"action-btn w-full text-left block px-4 py-2 text-sm text-red-700 hover:bg-slate-100\" data-id=\"$id\" data-action=\"dahuy\" role=\"menuitem\"><i class=\"fa-solid fa-ban mr-2\"></i>Hủy</button>";
            } elseif ($newStatus === 'hethan' || $newStatus === 'dahuy') {
                $newActionsHtml .= "<button class=\"action-btn w-full text-left block px-4 py-2 text-sm text-blue-700 hover:bg-slate-100\" data-id=\"$id\" data-action=\"chuaduyet\" role=\"menuitem\"><i class=\"fa-solid fa-rotate-left mr-2\"></i>Đăng lại (Chờ duyệt)</button>";
            }
            // Thêm link chi tiết (luôn có)
            $newActionsHtml .= "<a href=\"trangchu.php?page=chitiet_baidang&id=$id\" class=\"block px-4 py-2 text-sm text-slate-700 hover:bg-slate-100\" role=\"menuitem\"><i class=\"fa-solid fa-circle-info mr-2\"></i>Xem chi tiết</a>";
            
            $response = [
                'status' => 'success',
                'message' => 'Cập nhật trạng thái thành công!',
                'newStatusHtml' => $newStatusHtml,
                'newActionsHtml' => $newActionsHtml // Trả HTML của các nút mới về
            ];

        } else {
            $response['message'] = 'Lỗi CSDL khi cập nhật.';
        }
    } catch (PDOException $e) {
        $response['message'] = 'Lỗi CSDL: ' . $e->getMessage();
    }
} else {
    $response['message'] = 'Dữ liệu ID hoặc Trạng thái không hợp lệ.';
}

echo json_encode($response);
?>