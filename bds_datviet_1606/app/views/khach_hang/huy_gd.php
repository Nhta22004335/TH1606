<?php
session_start();
if (empty($_SESSION['id_nguoi_dung'])) { header('Location: /login.php'); exit; }

require_once __DIR__ . '/../../../config/database.php';
$pdo = ketnoicsdl();

$id  = (int)($_POST['id'] ?? 0);
$uid = $_SESSION['id_nguoi_dung'];
if ($id<=0) { header('Location: duan.php'); exit; }

$st = $pdo->prepare("UPDATE giao_dich
                     SET trang_thai='canceled'
                     WHERE id=:id AND id_nguoi_dung=:u AND trang_thai='pending'");
$st->execute([':id'=>$id, ':u'=>$uid]);

header('Location: duan.php');
