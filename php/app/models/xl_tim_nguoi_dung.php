<?php
require_once "../../config/database.php";
$pdo = ketnoicsdl();

$keyword = trim($_GET['keyword'] ?? '');
$currentUser = $_GET['me'] ?? '';

if ($keyword == '') {
    echo json_encode([]);
    exit;
}

// Tìm người dùng theo tên, loại trừ chính mình
$sql = "
    SELECT nd.id, info.ho_ten, nd.avt
    FROM nguoi_dung nd
    JOIN info_nguoi_dung info ON nd.id = info.id_nguoi_dung
    WHERE LOWER(info.ho_ten) LIKE LOWER(:kw)
      AND nd.id != :me
    LIMIT 10
";

$stmt = $pdo->prepare($sql);
$stmt->execute([
    ':kw' => "%$keyword%",
    ':me' => $currentUser
]);

echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
?>