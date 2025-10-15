<?php
require_once "../../config/database.php";
$pdo = ketnoicsdl();

$id = $_POST['id'] ?? null;

if (!$id) {
    echo json_encode(['status'=>'error','message'=>'ID hồ sơ không hợp lệ']);
    exit;
}

// Kiểm tra hồ sơ tồn tại và trạng thái
$stmt = $pdo->prepare("SELECT trang_thai FROM bieu_mau WHERE id = :id");
$stmt->execute([':id' => $id]);
$bm = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$bm) {
    echo json_encode(['status'=>'error','message'=>'Hồ sơ không tồn tại']);
    exit;
}

// Chỉ xóa nếu trạng thái là chờ duyệt hoặc đã hủy
if (!in_array($bm['trang_thai'], ['choduyet','huy'])) {
    echo json_encode(['status'=>'error','message'=>'Hồ sơ đã duyệt/đã ký, không thể xóa']);
    exit;
}

// Xóa hồ sơ
$stmtDel = $pdo->prepare("DELETE FROM bieu_mau WHERE id = :id");
if ($stmtDel->execute([':id' => $id])) {
    echo json_encode(['status'=>'success','message'=>'Xóa hồ sơ thành công']);
} else {
    echo json_encode(['status'=>'error','message'=>'Lỗi khi xóa hồ sơ']);
}
