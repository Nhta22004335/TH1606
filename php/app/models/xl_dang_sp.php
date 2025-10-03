<?php
require_once "../../../config/database.php";
$pdo = ketnoicsdl();

if(!isset($_SESSION['id_nguoi_dung'])) {
    header("Location: ../auth/dangnhap.html");
    exit;
}

$id_nguoi_dung = $_SESSION['id_nguoi_dung'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tieu_de   = $_POST['tieu_de'] ?? '';
    $mo_ta     = $_POST['mo_ta'] ?? '';
    $loai      = $_POST['loai'] ?? '';
    $hinh_thuc = $_POST['hinh_thuc'] ?? '';
    $gia       = $_POST['gia'] ?? 0;
    $dien_tich = $_POST['dien_tich'] ?? 0;
    $dia_chi   = $_POST['dia_chi'] ?? '';

    // $hinh_anh_files = $_FILES['hinh_anh'] ?? null;
    // $hinh_anh_paths = [];
    // if ($hinh_anh_files && $hinh_anh_files['name'][0] != '') {
    //     $upload_dir = "../../../storage/bds/";
    //     if (!file_exists($upload_dir)) mkdir($upload_dir, 0777, true);
    //     foreach ($hinh_anh_files['tmp_name'] as $i => $tmp_name) {
    //         $filename = uniqid() . "_" . basename($hinh_anh_files['name'][$i]);
    //         move_uploaded_file($tmp_name, $upload_dir . $filename);
    //         $hinh_anh_paths[] = $filename;
    //     }
    // }
    // $hinh_anh_str = implode(',', $hinh_anh_paths);


    // $video_path = '';
    // if (!empty($_FILES['video']['name'])) {
    //     $video_tmp  = $_FILES['video']['tmp_name'];
    //     $video_name = uniqid() . "_" . basename($_FILES['video']['name']);
    //     move_uploaded_file($video_tmp, $upload_dir . $video_name);
    //     $video_path = $video_name;
    // }


    $stmt = $pdo->prepare("
        INSERT INTO bat_dong_san (id_nguoi_dung, tieu_de, mo_ta, loai, khu_vuc, gia, dien_tich, dia_chi, trang_thai)
        VALUES (:id_nguoi_dung, :tieu_de, :mo_ta, :loai, :khu_vuc, :gia, :dien_tich, :dia_chi, 'chuaduyet')
    ");

    $stmt->execute([
        ':id_nguoi_dung' => $id_nguoi_dung,
        ':tieu_de'       => $tieu_de,
        ':mo_ta'         => $mo_ta,
        ':loai'          => $loai,
        ':khu_vuc'       => $hinh_thuc, 
        ':gia'           => $gia,
        ':dien_tich'     => $dien_tich,
        ':dia_chi'       => $dia_chi
    ]);

    echo "<script>alert('Đăng sản phẩm thành công!'); window.location.href='trangchu.php?page=../moi_gioi/dang_sp';</script>";
}
?>
