<?php
// BẬT HIỂN THỊ LỖI ĐỂ DEBUG (NÊN XÓA KHI ĐƯA LÊN PRODUCTION)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once "../../../config/database.php";
// Thêm khối try-catch để bắt lỗi kết nối CSDL
try {
    $pdo = ketnoicsdl();
} catch (PDOException $e) {
    // Thông báo lỗi nếu kết nối thất bại
    die("Lỗi kết nối CSDL: " . $e->getMessage());
}


$page = $_GET['page'] ?? '';
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
    // Sử dụng LOWER/LIKE cho khả năng tương thích cao
    $sql .= " WHERE LOWER(bm.tieu_de) LIKE LOWER(:search) 
            OR LOWER(bm.loai) LIKE LOWER(:search) 
            OR LOWER(info1.ho_ten) LIKE LOWER(:search) 
            OR LOWER(info2.ho_ten) LIKE LOWER(:search) 
            OR LOWER(bm.trang_thai) LIKE LOWER(:search)";
    $params[':search'] = "%$search%";
}

$sql .= " ORDER BY bm.ngay_tao DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$bieumau = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>


<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Giấy tờ & Đơn từ | Hệ thống</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../../../public/assets/fontawesome/css/all.min.css">
    <style>
        .modal-content {
            max-height: 90vh;
            overflow-y: auto;
        }
    </style>
</head>
<body>

<header class="flex bg-white shadow p-4 border-b border-gray-200">
    <img src="../../../public/assets/anhht/0/list.gif" alt="Quản lý sản phẩm" style="width: 40px; height: 40px; margin-right: 10px;">
    <h1 class="text-2xl font-bold text-gray-600">Quản lý biểu mẫu</h1>
</header>

<div class="bg-white p-4">


    <main class="container mx-auto p-2 md:p-2">

        <div class="bg-white rounded-xl shadow-xl overflow-hidden border border-gray-200">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-200 text-gray-700">
                        <tr>
                            <th class="py-2 px-3 text-left text-sm font-medium text-gray-700">Tiêu đề</th>
                            <th class="py-2 px-3 text-left text-sm font-medium text-gray-700">Loại</th>
                            <th class="py-2 px-3 text-left text-sm font-medium text-gray-700">Bên Mua</th>
                            <th class="py-2 px-3 text-left text-sm font-medium text-gray-700">Bên Bán</th>
                            <th class="py-2 px-3 text-left text-sm font-medium text-gray-700">Trạng thái</th>
                            <th class="py-2 px-3 text-center text-sm font-medium text-gray-700">Hành động</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-100 bg-white">
                        <?php if (empty($bieumau)): ?>
                            <tr>
                                <td colspan="6" class="p-6 text-center text-gray-500 italic text-lg">Không tìm thấy biểu mẫu nào.</td>
                            </tr>
                        <?php endif; ?>
                        <?php foreach($bieumau as $bm): 
                            // Định nghĩa lại biến trạng thái cho mỗi hàng
                            $status_text = "";
                            $status_classes = "";
                            switch($bm["trang_thai"]) {
                                case "choduyet":
                                    $status_text = "Chờ duyệt";
                                    $status_classes = "bg-yellow-100 text-yellow-800 border-yellow-300";
                                    break;
                                case "daduyet":
                                    $status_text = "Đã duyệt";
                                    $status_classes = "bg-green-100 text-green-800 border-green-300";
                                    break;
                                case "daky":
                                    $status_text = "Đã ký";
                                    $status_classes = "bg-blue-100 text-blue-800 border-blue-300";
                                    break;
                                case "huy":
                                    $status_text = "Hủy";
                                    $status_classes = "bg-red-100 text-red-800 border-red-300";
                                    break;
                                default:
                                    $status_text = $bm["trang_thai"];
                                    $status_classes = "bg-gray-100 text-gray-600 border-gray-300";
                            }
                        ?>
                            <tr class="hover:bg-blue-50 transition duration-150">
                                <td class="p-4 text-gray-700 truncate"><?= htmlspecialchars($bm["tieu_de"]) ?></td>
                                <td class="p-4 text-gray-600"><?= htmlspecialchars($bm["loai"]) ?></td>
                                <td class="p-4 text-gray-600"><?= htmlspecialchars($bm["ten_ben_mua"]) ?></td>
                                <td class="p-4 text-gray-600"><?= htmlspecialchars($bm["ten_ben_ban"]) ?></td>
                                <td class="p-4">
                                    <span class="px-3 py-1 text-xs font-semibold rounded-full border shadow-sm <?= $status_classes ?>">
                                        <?= $status_text ?>
                                    </span>
                                </td>
                                <td class="p-4 text-center">
                                    <button 
                                        onclick="document.getElementById('docModal<?= $bm['id'] ?>').classList.remove('hidden')" 
                                        class="bg-blue-500 hover:bg-blue-600 active:bg-blue-700 text-white text-xs font-medium px-3 py-2 rounded-full flex items-center justify-center gap-1 mx-auto shadow-md transition duration-150 transform hover:scale-105">
                                        <i class="fa-solid fa-folder-open"></i> Xem
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>


<?php foreach($bieumau as $bm): 
    // Re-define status variables for modal
    $status_text = "";
    $status_classes = "";
    switch($bm["trang_thai"]) {
        case "choduyet":
            $status_text = "Chờ duyệt";
            $status_classes = "bg-yellow-100 text-yellow-800 border-yellow-300";
            break;
        case "daduyet":
            $status_text = "Đã duyệt";
            $status_classes = "bg-green-100 text-green-800 border-green-300";
            break;
        case "daky":
            $status_text = "Đã ký";
            $status_classes = "bg-blue-100 text-blue-800 border-blue-300";
            break;
        case "huy":
            $status_text = "Hủy";
            $status_classes = "bg-red-100 text-red-800 border-red-300";
            break;
        default:
            $status_text = $bm["trang_thai"];
            $status_classes = "bg-gray-100 text-gray-600 border-gray-300";
    }
?>
    <div id="docModal<?= $bm["id"] ?>" class="fixed inset-0 bg-gray-900 bg-opacity-70 hidden flex items-center justify-center z-[9999] p-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-6 relative modal-content transform transition-all duration-300 scale-100">
            <button onclick="document.getElementById('docModal<?= $bm['id'] ?>').classList.add('hidden')" 
                    class="absolute top-3 right-3 text-gray-400 hover:text-red-600 p-2">
                <i class="fa-solid fa-xmark text-xl"></i>
            </button>

            <h2 class="text-xl font-bold text-blue-700 mb-6 border-b-2 pb-3 flex items-center gap-2">
                <i class="fa-solid fa-file-contract"></i> Chi tiết Biểu mẫu
            </h2>

            <div class="space-y-4 text-gray-700">
                <div class="p-3 bg-blue-50 rounded-xl border border-blue-200">
                    <strong class="text-xs uppercase text-blue-600 block mb-1">Tiêu đề</strong>
                    <p class="font-bold text-sm text-gray-600"><?= htmlspecialchars($bm["tieu_de"]) ?></p>
                </div>
                
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div class="p-3 border rounded-xl bg-white shadow-sm">
                        <p class="text-xs uppercase text-gray-500 mb-1">Loại</p>
                        <p class="font-semibold text-gray-900 flex items-center gap-1"><i class="fa-solid fa-tags text-blue-400"></i> <?= htmlspecialchars($bm["loai"]) ?></p>
                    </div>
                    <div class="p-3 border rounded-xl bg-white shadow-sm">
                        <p class="text-xs uppercase text-gray-500 mb-2">Trạng thái</p>
                        <span class="px-3 py-1 text-sm font-bold rounded-full border <?= $status_classes ?>">
                            <?= $status_text ?>
                        </span>
                    </div>
                </div>
                
                <div class="p-3 border rounded-xl bg-white shadow-sm">
                    <p class="text-xs uppercase text-gray-500 mb-2">Thông tin các bên</p>
                    <p class="font-medium text-gray-800 flex items-center gap-2"><i class="fa-solid fa-user-check text-green-500"></i> <strong>Người mua:</strong> <?= htmlspecialchars($bm["ten_ben_mua"]) ?></p>
                    <p class="font-medium text-gray-800 flex items-center gap-2 mt-1"><i class="fa-solid fa-handshake text-orange-500"></i> <strong>Người bán:</strong> <?= htmlspecialchars($bm["ten_ben_ban"]) ?></p>
                </div>

                <div class="p-3 border-2 border-dashed border-gray-300 rounded-xl flex items-center justify-between bg-gray-50">
                    <p class="font-medium text-gray-800 flex items-center gap-2"><i class="fa-solid fa-paperclip text-blue-500"></i> Tệp đính kèm:</p>
                    <a href="../../../storage/documents/<?= htmlspecialchars($bm["tep_dk"]) ?>" 
                        class="text-blue-600 hover:text-blue-800 hover:underline flex items-center gap-1 font-bold text-sm transition duration-150" 
                        target="_blank">
                        <i class="fa-solid fa-download"></i> Tải & Xem
                    </a>
                </div>
            </div>

            <?php if($bm["trang_thai"]=="choduyet"): ?>
                <div class="mt-8 pt-4 border-t border-gray-200 flex justify-end gap-4">
                    <button onclick="capNhatTrangThai('<?php echo $bm['id']; ?>','huy')"
                        class="bg-red-600 hover:bg-red-700 active:bg-red-800 text-white font-bold px-5 py-2.5 rounded-lg flex items-center gap-2 shadow-lg transition duration-150 transform hover:scale-[1.02]">
                        <i class="fa-solid fa-times-circle"></i> TỪ CHỐI
                    </button>
                    <button onclick="capNhatTrangThai('<?php echo $bm['id']; ?>','daduyet')" 
                        class="bg-green-600 hover:bg-green-700 active:bg-green-800 text-white font-bold px-5 py-2.5 rounded-lg flex items-center gap-2 shadow-lg transition duration-150 transform hover:scale-[1.02]">
                        <i class="fa-solid fa-check-circle"></i> DUYỆT
                    </button>
                </div>
            <?php endif; ?>
        </div>
    </div>
<?php endforeach; ?>

<script>
    function capNhatTrangThai(id, trangThai) {
        if (!confirm(`Bạn có chắc chắn muốn ${trangThai === 'daduyet' ? 'DUYỆT' : 'TỪ CHỐI'} biểu mẫu ID ${id} này không?`)) {
            return;
        }

        // Tạo dữ liệu form chuẩn
        const formData = new URLSearchParams();
        formData.append('id', id);
        formData.append('trang_thai', trangThai);

        fetch("../../models/cn_trangthai_bm.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: formData.toString()
        })
        .then(res => {
            if (!res.ok) {
                throw new Error(`Lỗi HTTP! Status: ${res.status}`);
            }
            return res.json();
        })
        .then(data => {
            alert(data.message);
            if (data.status === "success") {
                location.reload(); 
            }
        })
        .catch(err => {
            console.error("Lỗi cập nhật trạng thái:", err);
            alert("Đã xảy ra lỗi khi cập nhật trạng thái. Vui lòng kiểm tra console.");
        });
    }
</script>
</body>
</html>