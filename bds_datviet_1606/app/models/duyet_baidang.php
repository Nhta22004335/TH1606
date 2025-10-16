<?php
// models/api_baidang_actions.php

header('Content-Type: application/json');
require_once "../../config/database.php"; // Đảm bảo đường dẫn này chính xác

/**
 * Hàm helper để tạo HTML cho badge trạng thái mới.
 */
function getStatusBadgeHtml($status) {
    $map = [
        'chuaduyet' => ['text' => 'Chờ duyệt', 'class' => 'bg-orange-100 text-orange-800'],
        'daduyet'   => ['text' => 'Đang hiển thị', 'class' => 'bg-green-100 text-green-800'],
        'an'        => ['text' => 'Đã ẩn', 'class' => 'bg-slate-100 text-slate-800'],
    ];
    $info = $map[$status] ?? ['text' => ucfirst($status), 'class' => 'bg-gray-100 text-gray-800'];
    $text = htmlspecialchars($info['text']);
    return "<span class='inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {$info['class']}'>{$text}</span>";
}

/**
 * Hàm helper để tạo lại các nút hành động tương ứng với trạng thái mới.
 */
function getActionsHtml($id, $newStatus) {
    $html = '<p class="font-semibold text-base text-red-700">Hành động kiểm duyệt:</p>';
    if ($newStatus === 'chuaduyet') {
        $html .= '<button class="btn-action w-full bg-green-600 text-white py-2 mt-2 rounded-md text-sm font-semibold hover:bg-green-700" data-id="' . $id . '" data-action="approve"><i class="fa-solid fa-check mr-2"></i>Duyệt bài</button>';
        $html .= '<button class="btn-action w-full bg-red-600 text-white py-2 mt-2 rounded-md text-sm font-semibold hover:bg-red-700" data-id="' . $id . '" data-action="reject"><i class="fa-solid fa-ban mr-2"></i>Từ chối & Ẩn</button>';
    } elseif ($newStatus === 'an') {
        $html .= '<button class="btn-action w-full bg-blue-600 text-white py-2 mt-2 rounded-md text-sm font-semibold hover:bg-blue-700" data-id="' . $id . '" data-action="redisplay"><i class="fa-solid fa-eye mr-2"></i>Hiển thị lại</button>';
    } else { 
        $html .= '<button class="btn-action w-full bg-slate-600 text-white py-2 mt-2 rounded-md text-sm font-semibold hover:bg-slate-700" data-id="' . $id . '" data-action="reject"><i class="fa-solid fa-eye-slash mr-2"></i>Gỡ bài (Ẩn)</button>';
    }
    return $html;
}

try {
    $pdo = ketnoicsdl();
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Lỗi kết nối CSDL: ' . $e->getMessage()]);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);
$id = $data['id'] ?? null;
$action = $data['action'] ?? null;

if (!$id || !$action) {
    echo json_encode(['status' => 'error', 'message' => 'Thiếu ID hoặc hành động.']);
    exit();
}

$newStatus = null;
$message = 'Hành động không xác định.';
$status = 'error';

switch ($action) {
    case 'approve': $newStatus = 'daduyet'; $message = 'Đã duyệt bài đăng!'; break;
    case 'reject': $newStatus = 'an'; $message = 'Đã gỡ/ẩn bài đăng!'; break;
    case 'redisplay': $newStatus = 'daduyet'; $message = 'Đã hiển thị lại bài đăng!'; break;
}

if ($newStatus) {
    try {
        $stmt = $pdo->prepare("UPDATE bai_dang SET trang_thai = :trang_thai WHERE id = :id");
        $stmt->execute([':trang_thai' => $newStatus, ':id' => $id]);
        
        if ($stmt->rowCount() > 0) {
            $status = 'success';
            echo json_encode([
                'status' => $status,
                'message' => $message,
                'newStatusHtml' => getStatusBadgeHtml($newStatus),
                'newActionsHtml' => getActionsHtml($id, $newStatus)
            ]);
            exit();
        } else {
            $message = 'Không tìm thấy bài đăng hoặc không có gì thay đổi.';
        }
    } catch (PDOException $e) {
        $message = 'Lỗi cơ sở dữ liệu: ' . $e->getMessage();
    }
}

echo json_encode(['status' => $status, 'message' => $message]);
?>