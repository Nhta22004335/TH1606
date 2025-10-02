<?php
    require_once "../../../config/database.php";
    $pdo = ketnoicsdl();

    $page   = $_GET['page']   ?? '';
    $search = $_GET['search'] ?? '';

    $sql = "
        SELECT bm.id, bm.tieu_de, bm.loai, 
            info1.ho_ten AS ten_ben_mua,
            info2.ho_ten AS ten_ben_ban,
            bm.trang_thai, bm.tep_dk, bm.ngay_tao, bm.ngay_cn
        FROM bieu_mau bm
        JOIN nguoi_dung nd1 ON bm.ben_mua = nd1.id
        JOIN nguoi_dung nd2 ON bm.ben_ban = nd2.id
        JOIN info_nguoi_dung info1 ON info1.id_nguoi_dung = nd1.id
        JOIN info_nguoi_dung info2 ON info2.id_nguoi_dung = nd2.id
    ";

    $params = [];
    if (!empty($search)) {
        $sql .= " WHERE bm.tieu_de ILIKE :search 
                OR bm.loai ILIKE :search 
                OR info1.ho_ten ILIKE :search 
                OR info2.ho_ten ILIKE :search 
                OR bm.trang_thai ILIKE :search";
        $params[':search'] = "%$search%";
    }

    // ORDER BY phải nằm cuối
    $sql .= " ORDER BY bm.ngay_tao DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $bieumau = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>


<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý giấy tờ đơn từ</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../../../public/assets/fontawesome/css/all.min.css">
</head>
<body>

<!-- Header -->
<header class="flex items-center gap-4 bg-white shadow p-4 border-b-1">
    <img src="../../../public/assets/anhht/0/customs-clearance.gif" alt="Chat" class="w-12 h-12">
    <h1 class="text-2xl font-bold text-gray-600">Giấy tờ đơn từ</h1>
</header>

<table class="w-full border-collapse my-6">
    <thead>
        <tr class="bg-gray-200 text-gray-700">
            <th class="p-3 text-left">Tiêu đề</th>
            <th class="p-3 text-left">Loại</th>
            <th class="p-3 text-left">Người mua</th>
            <th class="p-3 text-left">Người bán</th>
            <th class="p-3 text-left">Trạng thái</th>
            <th class="p-3 text-center">Hành động</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach($bieumau as $bm): ?>
            <tr class="border-b hover:bg-gray-50">
                <td class="p-3"><?= $bm["tieu_de"] ?></td>
                <td class="p-3"><?= $bm["loai"] ?></td>
                <td class="p-3"><?= $bm["ten_ben_mua"] ?></td>
                <td class="p-3"><?= $bm["ten_ben_ban"] ?></td>
                <td class="p-3">
                    <?php if($bm["trang_thai"]=="choduyet"): ?>
                        <span class="px-2 py-1 bg-yellow-100 text-yellow-700 rounded">Chờ duyệt</span>
                    <?php elseif($bm["trang_thai"]=="daduyet"): ?>
                        <span class="px-2 py-1 bg-green-100 text-green-700 rounded">Đã duyệt</span>
                    <?php elseif($bm["trang_thai"]=="daky"): ?>
                        <span class="px-2 py-1 bg-blue-100 text-blue-700 rounded">Đã ký</span>
                    <?php elseif($bm["trang_thai"]=="huy"): ?>
                        <span class="px-2 py-1 bg-red-100 text-red-700 rounded">Hủy</span>
                    <?php endif; ?>
                </td>

                <td class="p-3 text-center">
                    <button 
                        onclick="document.getElementById('docModal<?= $bm['id'] ?>').classList.remove('hidden')" 
                        class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1.5 rounded-lg flex items-center justify-center gap-1 mx-auto shadow">
                        <i class="fa-solid fa-eye"></i> Xem
                    </button>
                </td>

            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<!-- Render modal chi tiết cho từng đơn -->
<?php foreach($bieumau as $bm): ?>
    <div id="docModal<?= $bm["id"] ?>" class="fixed inset-0 bg-black bg-opacity-40 hidden flex items-center justify-center z-[9999]">
        <div class="bg-white rounded-xl shadow-lg w-96 p-6 relative">
            <!-- Nút đóng -->
            <button onclick="document.getElementById('docModal<?= $bm['id'] ?>').classList.add('hidden')" 
                    class="absolute top-3 right-3 text-gray-500 hover:text-red-500">
                <i class="fa-solid fa-xmark text-xl"></i>
            </button>

            <h2 class="text-xl font-bold text-gray-700 mb-4 flex items-center"><i class="fa-solid fa-file-lines text-blue-500 mr-2"></i> Chi tiết đơn</h2>

            <div class="space-y-2 text-gray-700 text-sm">
                <p><strong>Tiêu đề:</strong> <?= $bm["tieu_de"] ?></p>
                <p><strong>Loại:</strong> <?= $bm["loai"] ?></p>
                <p><strong>Người mua:</strong> <?= $bm["ten_ben_mua"] ?></p>
                <p><strong>Người bán:</strong> <?= $bm["ten_ben_ban"] ?></p>
                <p><strong>Trạng thái:</strong> <?= $bm["trang_thai"] ?></p>
                <p><strong>Tệp tin:</strong> 
                    <a href="../../../storage/documents/<?= $bm["tep_dk"] ?>" class="text-blue-500 hover:underline" target="_blank">Xem tệp</a>
                </p>
            </div>

            <!-- Nút duyệt/từ chối -->
            <?php if($bm["trang_thai"]=="choduyet"): ?>
                <div class="mt-5 flex justify-between">
                    <button onclick="capNhatTrangThai('<?php echo $bm['id']; ?>','daduyet')" 
                        class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg flex items-center gap-2">
                        <i class="fa-solid fa-check"></i> Duyệt
                    </button>
                    <button onclick="capNhatTrangThai('<?php echo $bm['id']; ?>','huy')"
                        class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg flex items-center gap-2">
                        <i class="fa-solid fa-xmark"></i> Từ chối
                    </button>
                </div>
            <?php endif; ?>
        </div>
    </div>
<?php endforeach; ?>

<script>
    function capNhatTrangThai(id, trangThai) {
        fetch("../../models/cn_trangthai_bm.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: JSON.stringify({
                id: id,
                trang_thai: trangThai
            })
        })
        .then(res => res.json())
        .then(data => {
            alert(data.message);
            if (data.status === "success") {
                location.reload(); 
            }
        })
        .catch(err => console.error(err));
    }
</script>
</body>
</html>
