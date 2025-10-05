<?php
require_once "../../config/database.php";
$pdo = ketnoicsdl();

$data = json_decode(file_get_contents("php://input"), true);
$nguoi_gui = $data['nguoi_gui'] ?? '';
$nguoi_nhan = $data['nguoi_nhan'] ?? '';

if (!$nguoi_gui || !$nguoi_nhan) {
    echo json_encode(["success" => false, "message" => "Thiếu thông tin người gửi hoặc người nhận"]);
    exit;
}

// 🔹 Lấy họ tên của người gửi và người nhận
$sql_ten = "
    SELECT 
        (SELECT ho_ten FROM info_nguoi_dung WHERE id_nguoi_dung = :a) AS ten_gui,
        (SELECT ho_ten FROM info_nguoi_dung WHERE id_nguoi_dung = :b) AS ten_nhan
";
$stmt = $pdo->prepare($sql_ten);
$stmt->execute([':a' => $nguoi_gui, ':b' => $nguoi_nhan]);
$info = $stmt->fetch(PDO::FETCH_ASSOC);

$ten_gui = $info['ten_gui'] ?? 'NguoiGui';
$ten_nhan = $info['ten_nhan'] ?? 'NguoiNhan';

// 🔹 Kiểm tra xem đã có hội thoại giữa 2 người này chưa
$sql_check = "
    SELECT DISTINCT ht.id AS id_hop_thoai
    FROM hop_thoai ht
    JOIN tin_nhan tn ON tn.id_hop_thoai = ht.id
    WHERE 
        (tn.nguoi_gui = :a AND tn.nguoi_nhan = :b)
        OR 
        (tn.nguoi_gui = :b AND tn.nguoi_nhan = :a)
    LIMIT 1
";
$stmt = $pdo->prepare($sql_check);
$stmt->execute([':a' => $nguoi_gui, ':b' => $nguoi_nhan]);
$existing = $stmt->fetch(PDO::FETCH_ASSOC);

if ($existing) {
    $id_hop_thoai = $existing['id_hop_thoai'];

    // Chuẩn hóa theo thứ tự cố định (alphabet để đảm bảo cả hai chiều giống nhau)
    $ten_pair = [$ten_gui, $ten_nhan];
    sort($ten_pair, SORT_STRING | SORT_FLAG_CASE);
    $chat_name = urlencode(implode('_', $ten_pair));

    $id_pair = [$nguoi_gui, $nguoi_nhan];
    sort($id_pair, SORT_STRING);
    $idkey = implode('_', $id_pair);

    $link_chat = "chat={$chat_name}&idkey={$idkey}";

    echo json_encode([
        "success" => true,
        "id_hop_thoai" => $id_hop_thoai,
        "chat" => $chat_name,
        "idkey" => $idkey,
        "link" => $link_chat
    ]);
    exit;
}

// 🔹 Nếu chưa có hội thoại → tạo mới
$sql_insert = "INSERT INTO hop_thoai DEFAULT VALUES RETURNING id";
$stmt = $pdo->prepare($sql_insert);
$stmt->execute();
$newId = $stmt->fetchColumn();

// 🔸 Tạo dòng tin nhắn trống để khởi tạo hội thoại
$sql_insert_msg = "
    INSERT INTO tin_nhan (id_hop_thoai, nguoi_gui, nguoi_nhan, noi_dung)
    VALUES (:id_hop_thoai, :nguoi_gui, :nguoi_nhan, '')
";
$stmt = $pdo->prepare($sql_insert_msg);
$stmt->execute([
    ':id_hop_thoai' => $newId,
    ':nguoi_gui' => $nguoi_gui,
    ':nguoi_nhan' => $nguoi_nhan
]);

// 🔸 Chuẩn hóa key hội thoại
$ten_pair = [$ten_gui, $ten_nhan];
sort($ten_pair, SORT_STRING | SORT_FLAG_CASE);
$chat_name = urlencode(implode('_', $ten_pair));

$id_pair = [$nguoi_gui, $nguoi_nhan];
sort($id_pair, SORT_STRING);
$idkey = implode('_', $id_pair);

$link_chat = "chat={$chat_name}&idkey={$idkey}";

echo json_encode([
    "success" => true,
    "id_hop_thoai" => $newId,
    "chat" => $chat_name,
    "idkey" => $idkey,
    "link" => $link_chat
]);
?>
