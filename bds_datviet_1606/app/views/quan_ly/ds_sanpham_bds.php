<?php
    require_once "../../../config/database.php";

    // --- XỬ LÝ HÀNH ĐỘNG POST (DUYỆT, HOÀN TÁC, XÓA) ---
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'], $_POST['action'])) {
        $pdo = ketnoicsdl();
        $id = $_POST['id'];
        $action = $_POST['action'];
        $message = "Hành động không hợp lệ.";
        $msg_type = 'error';

        try {
            if ($action === 'duyet' || $action === 'hoantac') {
                $new_status = ($action === 'duyet') ? 'daduyet' : 'chuaduyet';
                $stmt = $pdo->prepare("UPDATE bat_dong_san SET trang_thai = :trang_thai WHERE id = :id");
                $stmt->execute([':trang_thai' => $new_status, ':id' => $id]);
                $message = "Cập nhật trạng thái thành công!";
                $msg_type = 'success';

            } elseif ($action === 'xoa') {
                $stmt = $pdo->prepare("DELETE FROM bat_dong_san WHERE id = :id");
                $stmt->execute([':id' => $id]);
                $message = ($stmt->rowCount() > 0) ? "Đã xóa tin đăng thành công!" : "Không tìm thấy tin đăng để xóa.";
                $msg_type = 'success';
            }
        } catch (PDOException $e) {
            $message = "Lỗi cơ sở dữ liệu: " . $e->getMessage();
            $msg_type = 'error';
        }

        // Chuyển hướng chung (không còn `updated_id`)
        $redirect_url = "trangchu.php?page=ds_sanpham_bds&message=" . urlencode($message) . "&msg_type=" . $msg_type;
        echo "<script>window.location.href = '$redirect_url';</script>";
        exit();
    }

    // --- CÁC HÀM HELPER VÀ LOGIC LẤY DỮ LIỆU ---
    function formatPrice($price) {
        if ($price >= 1000000000) return round($price / 1000000000, 2) . ' tỷ';
        if ($price >= 1000000) return round($price / 1000000, 2) . ' triệu';
        return number_format($price) . ' đ';
    }

    function getStatusInfo($status) {
        $map = [
            'chuaduyet' => ['text' => 'Chờ duyệt', 'classes' => 'bg-yellow-100 text-yellow-800'],
            'daduyet'   => ['text' => 'Đã duyệt', 'classes' => 'bg-green-100 text-green-800'],
        ];
        return $map[$status] ?? ['text' => 'Không rõ', 'classes' => 'bg-gray-100 text-gray-800'];
    }

    $pdo = ketnoicsdl();
    $search = $_GET['search'] ?? '';

    $sql = "SELECT id, tieu_de, gia, dien_tich, dia_chi, ngay_dang, trang_thai FROM bat_dong_san";
    $params = [];
    if (!empty($search)) {
        $sql .= " WHERE tieu_de ILIKE :search OR dia_chi ILIKE :search";
        $params[':search'] = '%' . $search . '%';
    }
    $sql .= " ORDER BY ngay_dang DESC, id DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="vi" class="bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Bất động sản</title>
</head>
<body class="p-4 md:p-6">
    <div class="max-w-7xl mx-auto">
        <?php if (!empty($_GET['message'])): ?>
            <div id="alertBox" class="p-4 rounded-md border bg-green-50 border-green-200 text-green-800 mb-4">
                <?= htmlspecialchars(urldecode($_GET['message'])) ?>
            </div>
        <?php endif; ?>

        <header class="mb-6 border-b pb-4">
            <h1 class="text-2xl font-bold text-gray-800">Quản lý Bất động sản</h1>
            <p class="text-sm mt-2 text-gray-500">Xem, tìm kiếm và quản lý các tin đăng bất động sản.</p>
        </header>

        <form id="search-form" method="GET" class="flex items-center mb-6">
            <input type="hidden" name="page" value="ds_sanpham_bds">
            <div class="relative w-full md:w-72">
                <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                <input type="search" id="search-input" name="search" class="bg-white outline-none border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block w-full pl-10 p-2" 
                       placeholder="Tìm kiếm tin đăng..." value="<?= htmlspecialchars($search) ?>">
            </div>
            <button type="submit" class="ml-2 px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700">Tìm</button>
        </form>

        <main class="bg-white rounded-xl shadow-lg overflow-hidden border border-gray-200/80">
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="p-3 text-left text-xs font-bold text-gray-500 uppercase">Tên Bất động sản</th>
                            <th class="p-3 text-left text-xs font-bold text-gray-500 uppercase">Giá</th>
                            <th class="p-3 text-left text-xs font-bold text-gray-500 uppercase">Diện tích</th>
                            <th class="p-3 text-left text-xs font-bold text-gray-500 uppercase">Trạng thái</th>
                            <th class="p-3 text-left text-xs font-bold text-gray-500 uppercase">Ngày đăng</th>
                            <th class="p-3 text-center text-xs font-bold text-gray-500 uppercase">Hành động</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php if (empty($products)): ?>
                            <tr><td colspan="6" class="p-8 text-center text-gray-500">Không tìm thấy bất động sản nào.</td></tr>
                        <?php else: ?>
                            <?php foreach($products as $p): ?>
                                <tr class="hover:bg-slate-50 transition duration-150">
                                    <td class="p-4">
                                        <p class="font-medium text-sm text-gray-900 line-clamp-1"><?= htmlspecialchars($p['tieu_de']) ?></p>
                                        <p class="text-xs text-gray-500 line-clamp-1"><?= htmlspecialchars($p['dia_chi']) ?></p>
                                    </td>
                                    <td class="p-4 font-semibold text-red-600 text-sm"><?= formatPrice($p['gia']) ?></td>
                                    <td class="p-4 text-gray-700 text-sm"><?= htmlspecialchars($p['dien_tich']) ?> m²</td>
                                    <td class="p-4">
                                        <?php $status_info = getStatusInfo($p["trang_thai"]); ?>
                                        <span class="px-2.5 py-1 text-xs font-semibold rounded-full <?= $status_info['classes'] ?>">
                                            <?= $status_info['text'] ?>
                                        </span>
                                    </td>
                                    <td class="p-4 text-gray-500 text-sm"><?= date("d/m/Y", strtotime($p['ngay_dang'])) ?></td>
                                    <td class="p-4">
                                        <div class="flex justify-center items-center gap-4">
                                            
                                            <?php if ($p['trang_thai'] === 'chuaduyet'): ?>
                                                <form method="POST" onsubmit="return confirm('Bạn có chắc muốn duyệt tin này?');" class="m-0">
                                                    <input type="hidden" name="id" value="<?= $p['id'] ?>">
                                                    <input type="hidden" name="action" value="duyet">
                                                    <button type="submit" class="text-green-600 hover:text-green-800 text-sm font-medium">Duyệt</button>
                                                </form>
                                            <?php elseif ($p['trang_thai'] === 'daduyet'): ?>
                                                <form method="POST" onsubmit="return confirm('Bạn có chắc muốn hoàn tác? Tin này sẽ trở về trạng thái Chờ duyệt.');" class="m-0">
                                                    <input type="hidden" name="id" value="<?= $p['id'] ?>">
                                                    <input type="hidden" name="action" value="hoantac">
                                                    <button type="submit" class="text-yellow-600 hover:text-yellow-800 text-sm font-medium">Hoàn tác</button>
                                                </form>
                                            <?php endif; ?>
                                            
                                            <a href="trangchu.php?page=ct_sanpham&id=<?= $p['id'] ?>" class="text-blue-600 hover:text-blue-800 text-sm font-medium">Chi tiết</a>
                                            
                                            <form method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn xóa tin này? Hành động này không thể hoàn tác.');" class="m-0">
                                                <input type="hidden" name="id" value="<?= $p['id'] ?>">
                                                <input type="hidden" name="action" value="xoa">
                                                <button type="submit" class="text-red-600 hover:text-red-800 text-sm font-medium">Xóa</button>
                                            </form>

                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <script>
        document.getElementById('search-form').addEventListener('submit', function(event) {
            event.preventDefault();
            const searchValue = document.getElementById('search-input').value.trim();
            const url = new URL(window.location.href);
            url.searchParams.set('search', searchValue);
            window.location.href = url.toString();
        });

        // Tự động ẩn thông báo và làm sạch URL
        const alertBox = document.getElementById("alertBox");
        if (alertBox) {
            setTimeout(() => {
                alertBox.style.transition = 'opacity 0.5s';
                alertBox.style.opacity = '0';
                setTimeout(() => alertBox.remove(), 500);
                
                const url = new URL(window.location.href);
                url.searchParams.delete('message');
                url.searchParams.delete('msg_type');
                // Không cần xóa updated_id nữa
                history.replaceState(null, '', url.toString());
            }, 3000);
        }
    </script>
</body>
</html>