<?php
    require_once "../../../config/database.php";
    $pdo = ketnoicsdl();

    $limit  = 6; 
    $page   = isset($_GET['p']) && is_numeric($_GET['p']) ? intval($_GET['p']) : 1;
    $offset = ($page - 1) * $limit;

    if (isset($_GET['export']) && $_GET['export'] === 'csv') {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=lich_su_xac_thuc.csv');
        
        $output = fopen('php://output', 'w');

        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

        fputcsv($output, ['Người dùng', 'Loại sự kiện', 'Thời gian bắt đầu', 'Thời gian kết thúc', 'Địa chỉ IP', 'User Agent', 'Ghi chú']);

        $stmt = $pdo->query("
            SELECT nd.ten_dang_nhap, lsxt.loai_su_kien, lsxt.thoi_gian_bat_dau, lsxt.thoi_gian_ket_thuc, lsxt.dia_chi_ip, lsxt.user_agent, lsxt.ghi_chu
            FROM lich_su_xac_thuc lsxt
            JOIN nguoi_dung nd ON nd.id = lsxt.id_nguoi_dung
            ORDER BY lsxt.thoi_gian_bat_dau DESC
        ");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            fputcsv($output, $row);
        }
        fclose($output);
        exit;
    }

    $totalStmt = $pdo->query("SELECT COUNT(*) FROM lich_su_xac_thuc");
    $totalRows = $totalStmt->fetchColumn();
    $totalPages = ceil($totalRows / $limit);

    $search = $_GET['search'] ?? '';
    if ($search !== '') {
        $sql = "
            SELECT lsxt.*, nd.ten_dang_nhap
            FROM lich_su_xac_thuc lsxt
            JOIN nguoi_dung nd ON nd.id = lsxt.id_nguoi_dung
            WHERE nd.ten_dang_nhap ILIKE :searchPattern
            ORDER BY lsxt.thoi_gian_bat_dau DESC
            LIMIT :limit OFFSET :offset
        ";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':searchPattern', "%$search%", PDO::PARAM_STR);
    } else {
        $sql = "
            SELECT lsxt.*, nd.ten_dang_nhap
            FROM lich_su_xac_thuc lsxt
            JOIN nguoi_dung nd ON nd.id = lsxt.id_nguoi_dung
            ORDER BY lsxt.thoi_gian_bat_dau DESC
            LIMIT :limit OFFSET :offset
        ";
        $stmt = $pdo->prepare($sql);
    }

    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_from'], $_POST['delete_to'])) {
        $from = $_POST['delete_from'];
        $to   = $_POST['delete_to'];

        if ($from && $to) {
            $stmt = $pdo->prepare("DELETE FROM lich_su_xac_thuc WHERE thoi_gian_bat_dau BETWEEN :from AND :to");
            $stmt->execute([
                ':from' => $from,
                ':to'   => $to
            ]);

            $deletedRows = $stmt->rowCount();
            $_SESSION['message'] = "✅ Đã xóa $deletedRows bản ghi từ $from đến $to.";
        } else {
            $_SESSION['message'] = "⚠️ Vui lòng chọn đầy đủ khoảng thời gian.";
        }
    }

    if (isset($_POST['delete_id'])) {
        $id = $_POST['delete_id'];
        $stmt = $pdo->prepare("DELETE FROM lich_su_xac_thuc WHERE id = :id");
        $stmt->execute([':id' => $id]);

        if ($stmt->rowCount() > 0) {
            $_SESSION['message'] = "🗑️ Đã xóa bản ghi #$id thành công.";
        } else {
            $_SESSION['message'] = "⚠️ Không tìm thấy bản ghi cần xóa.";
        }
    }

    $filters = [];

    if (isset($_GET['boloc'])) {
        $filters = json_decode($_GET['boloc'], true);
    }
?>


<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lịch Sử Xác Thực</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>

<div class="space-y-6">

    <?php if (!empty($_SESSION['message'])): ?>
    <div id="alertBox" class="p-4 rounded-lg bg-emerald-100 text-emerald-800 shadow-md">
        <?= htmlspecialchars($_SESSION['message']) ?>
    </div>
    <?php unset($_SESSION['message']); ?>
    <?php endif; ?>

    <header class="mb-6 border-b pb-4">
        <h1 class="text-2xl font-bold text-gray-800">Lịch Sử Xác Thực</h1>
        <p class="text-sm mt-2 text-gray-500">Ghi nhận các hoạt động truy cập và bảo mật của người dùng.</p>
    </header>

    <div class="bg-white p-4 rounded-lg shadow-sm border space-y-4">
        
        <div class="flex flex-col md:flex-row gap-4">
            <form method="GET" class="flex-grow">
                <input type="hidden" name="page" value="ls_xacthuc">
                <div class="relative">
                    <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none"></i>
                    <input type="text" name="search" placeholder="Tìm theo tên đăng nhập..."
                           value="<?= htmlspecialchars($search) ?>"
                           class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-indigo-500 focus:outline-none transition">
                </div>
            </form>
            
            <a href="?page=ls_xacthuc&export=csv" class="flex-shrink-0 flex items-center justify-center px-4 py-2 bg-gray-800 text-white text-sm font-medium rounded-md shadow-sm hover:bg-gray-900 transition">
               <i class="fas fa-download mr-2"></i> Xuất Báo Cáo
            </a>
        </div>

        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 pt-4 border-t">
            <div class="flex items-center gap-2">
                <select id="loaisukien" class="border px-2 py-2 border-gray-300 rounded-md text-sm w-full md:w-auto focus:outline-none focus:ring-2 focus:ring-indigo-500 transition">
                    <option value="">Tất cả sự kiện</option>
                    <option value="dangnhap" <?= (($filters['loaisukien'] ?? '') === 'dangnhap') ? 'selected' : '' ?>>Đăng nhập</option>
                    <option value="dangxuat" <?= (($filters['loaisukien'] ?? '') === 'dangxuat') ? 'selected' : '' ?>>Đăng xuất</option>
                    <option value="doimatkhau" <?= (($filters['loaisukien'] ?? '') === 'doimatkhau') ? 'selected' : '' ?>>Đổi mật khẩu</option>
                    <option value="quenmatkhau" <?= (($filters['loaisukien'] ?? '') === 'quenmatkhau') ? 'selected' : '' ?>>Quên mật khẩu</option>
                </select>
                <button id="btnloc" class="px-2 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md  hover:bg-indigo-700 transition">Lọc</button>
                <button id="btnhuy" class="px-2 py-2 bg-gray-200 text-gray-700 text-sm font-medium rounded-md hover:bg-gray-300 transition">Hủy</button>
            </div>

            <form method="POST" class="flex flex-col sm:flex-row items-center gap-2">
                <input type="date" name="delete_from" class="border-gray-300 rounded-md p-2 w-full sm:w-auto focus:outline-none transition" required>
                <span class="hidden sm:inline text-gray-500">→</span>
                <input type="date" name="delete_to" class="border-gray-300 rounded-md p-2 w-full sm:w-auto focus:outline-none transition" required>
                <button type="submit" class="w-full sm:w-auto px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-md shadow-sm hover:bg-red-700 transition">Xóa</button>
            </form>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-sm border overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="p-3 text-left text-xs font-medium text-gray-500 uppercase ">Người dùng</th>
                    <th class="p-3 text-left text-xs font-medium text-gray-500 uppercase ">Sự kiện</th>
                    <th class="p-3 text-left text-xs font-medium text-gray-500 uppercase ">Thời gian</th>
                    <th class="p-3 text-left text-xs font-medium text-gray-500 uppercase ">Địa chỉ IP</th>
                    <th class="p-3 text-left text-xs font-medium text-gray-500 uppercase hidden xl:table-cell">User Agent</th>
                    <th class="p-3 text-center text-xs font-medium text-gray-500 uppercase"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                <?php if (!empty($filters)): ?>
                    <?php foreach ($logs as $log): ?>
                        <?php
                          $match = true;
                          if (isset($filters['loaisukien']) && $filters['loaisukien'] !== $log['loai_su_kien']) $match = false;
                        ?>
                        <?php if ($match): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="p-3 font-medium text-gray-900"><?= htmlspecialchars($log['ten_dang_nhap']) ?></td>
                            <td class="p-3">
                                <span class="px-2.5 py-0.5 text-xs font-medium rounded-full 
                                    <?= $log['loai_su_kien'] === 'dangnhap' ? 'bg-green-100 text-green-800' : '' ?>
                                    <?= $log['loai_su_kien'] === 'dangxuat' ? 'bg-yellow-100 text-yellow-800' : '' ?>
                                    <?= $log['loai_su_kien'] === 'doimatkhau' ? 'bg-blue-100 text-blue-800' : '' ?>
                                    <?= $log['loai_su_kien'] === 'quenmatkhau' ? 'bg-purple-100 text-purple-800' : '' ?>
                                ">
                                    <?= htmlspecialchars(ucfirst($log['loai_su_kien'])) ?>
                                </span>
                            </td>
                            <td class="p-3 text-gray-600 whitespace-nowrap"><?= htmlspecialchars($log['thoi_gian_bat_dau']) ?></td>
                            <td class="p-3 text-gray-600"><?= htmlspecialchars($log['dia_chi_ip']) ?></td>
                            <td class="p-3 text-gray-600 max-w-sm truncate hidden xl:table-cell" title="<?= htmlspecialchars($log['user_agent']) ?>">
                                <?= htmlspecialchars($log['user_agent']) ?>
                            </td>
                            <td class="p-3 text-center">
                                <form method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn xóa bản ghi này?');">
                                    <input type="hidden" name="delete_id" value="<?= $log['id'] ?>">
                                    <button type="submit" class="text-gray-400 hover:text-red-600 transition" title="Xóa"><i class="fas fa-trash-alt"></i></button>
                                </form>
                            </td>
                        </tr>
                        <?php endif; ?>
                    <?php endforeach; ?>
                <?php else: ?>
                    <?php foreach ($logs as $log): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="p-3 font-medium text-gray-900"><?= htmlspecialchars($log['ten_dang_nhap']) ?></td>
                        <td class="p-3">
                            <span class="px-2.5 py-0.5 text-xs font-medium rounded-full 
                                <?= $log['loai_su_kien'] === 'dangnhap' ? 'bg-green-100 text-green-800' : '' ?>
                                <?= $log['loai_su_kien'] === 'dangxuat' ? 'bg-yellow-100 text-yellow-800' : '' ?>
                                <?= $log['loai_su_kien'] === 'doimatkhau' ? 'bg-blue-100 text-blue-800' : '' ?>
                                <?= $log['loai_su_kien'] === 'quenmatkhau' ? 'bg-purple-100 text-purple-800' : '' ?>
                            ">
                                <?= htmlspecialchars(ucfirst($log['loai_su_kien'])) ?>
                            </span>
                        </td>
                        <td class="p-3 text-gray-500 whitespace-nowrap"><?= htmlspecialchars($log['thoi_gian_bat_dau']) ?></td>
                        <td class="p-3 text-gray-500"><?= htmlspecialchars($log['dia_chi_ip']) ?></td>
                        <td class="p-3 text-gray-500 max-w-sm truncate hidden xl:table-cell" title="<?= htmlspecialchars($log['user_agent']) ?>">
                            <?= htmlspecialchars($log['user_agent']) ?>
                        </td>
                        <td class="p-3 text-center">
                            <form method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn xóa bản ghi này?');">
                                <input type="hidden" name="delete_id" value="<?= $log['id'] ?>">
                                <button type="submit" class="text-gray-400 hover:text-red-600 transition" title="Xóa"><i class="fas fa-trash-alt"></i></button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>

                <?php if (count($logs) === 0): ?>
                <tr>
                    <td colspan="6" class="p-8 text-center text-gray-500">
                        <i class="fas fa-folder-open text-4xl mb-2"></i>
                        <p class="font-medium">Không có dữ liệu</p>
                        <p class="text-sm">Không tìm thấy bản ghi nào phù hợp với tiêu chí của bạn.</p>
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php if ($totalPages > 1): ?>
    <nav class="flex items-center justify-between">
        <span class="text-sm text-gray-700">
            Trang <span class="font-semibold"><?= $page ?></span> trên <span class="font-semibold"><?= $totalPages ?></span>
        </span>
        <div class="flex items-center space-x-2">
            <a href="?page=ls_xacthuc&p=<?= $page - 1 ?>" class="flex items-center justify-center px-3 py-1.5 text-sm font-medium text-gray-600 bg-white border border-gray-300 rounded-md hover:bg-gray-100 <?= ($page <= 1) ? 'pointer-events-none opacity-50' : '' ?>">
                <i class="fas fa-arrow-left mr-1"></i> Trước
            </a>
            <a href="?page=ls_xacthuc&p=<?= $page + 1 ?>" class="flex items-center justify-center px-3 py-1.5 text-sm font-medium text-gray-600 bg-white border border-gray-300 rounded-md hover:bg-gray-100 <?= ($page >= $totalPages) ? 'pointer-events-none opacity-50' : '' ?>">
                Sau <i class="fas fa-arrow-right ml-1"></i>
            </a>
        </div>
    </nav>
    <?php endif; ?>

</div>


<script>
    function apdungloc() {
        const keys = ["loaisukien"];
        let filters = {};

        keys.forEach(key => {
            const el = document.getElementById("loaisukien");
            if (el && el.value.trim() !== "") {
                filters[key] = el.value.trim();
            }
        });

        const boloc = encodeURIComponent(JSON.stringify(filters));
        window.location.href = "trangchu.php?page=ls_xacthuc&boloc=" + boloc;
    }

    document.getElementById("btnloc").addEventListener("click", () => apdungloc());

    function huyloc() {
        window.location.href = "trangchu.php?page=ls_xacthuc";
    }

    document.getElementById("btnhuy").addEventListener("click", () => huyloc());

    document.addEventListener("DOMContentLoaded", function() {
        let alertBox = document.getElementById("alertBox");
        if (alertBox) {
            setTimeout(() => {
                alertBox.style.display = "none";
                window.location.href = "trangchu.php?page=ls_xacthuc";
            }, 2000); 
        }
    });
</script>

</body>
</html>
