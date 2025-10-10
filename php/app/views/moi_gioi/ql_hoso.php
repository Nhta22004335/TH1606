<?php
// ===== PHẦN LOGIC PHP - ĐÃ ĐƯỢC TÁI CẤU TRÚC VÀ TỐI ƯU =====
require_once "../../../config/database.php";

// Các hàm helper để code sạch sẽ và không lặp lại
function getStatusInfo($status) {
    $map = [
        "choduyet" => ['text' => "Chờ duyệt", 'classes' => "bg-yellow-100 text-yellow-800 border-yellow-300"],
        "daduyet"  => ['text' => "Đã duyệt",  'classes' => "bg-green-100 text-green-800 border-green-300"],
        "daky"     => ['text' => "Đã ký",      'classes' => "bg-blue-100 text-blue-800 border-blue-300"],
        "huy"      => ['text' => "Đã hủy",     'classes' => "bg-red-100 text-red-800 border-red-300"]
    ];
    return $map[$status] ?? ['text' => $status, 'classes' => "bg-gray-100 text-gray-600 border-gray-300"];
}

function getLoaiText($loai) {
    $map = ['hosomuaban' => 'Hồ sơ mua bán', 'hosothue' => 'Hồ sơ thuê', 'bienban' => 'Biên bản'];
    return $map[$loai] ?? 'Không xác định';
}

$pdo = ketnoicsdl();
$search = $_GET['search'] ?? '';
$id = $_SESSION['id_nguoi_dung'] ?? ''; // Giả sử ID người dùng đã có trong session

// Câu lệnh SQL không đổi, đã tốt
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
    WHERE nd2.id = :id
";
$params = [':id' => $id];

if (!empty($search)) {
    $sql .= " AND (bm.tieu_de ILIKE :search OR bm.loai ILIKE :search OR info1.ho_ten ILIKE :search OR info2.ho_ten ILIKE :search OR bm.trang_thai ILIKE :search)";
    $params[':search'] = "%$search%";
}
$sql .= " ORDER BY bm.ngay_tao DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$bieumau_list = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="vi" class="bg-gray-50">
<head>
    <meta charset="UTF-8">
    <title>Giấy tờ & Đơn từ của tôi</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>.modal-content { max-height: 90vh; overflow-y: auto; }</style>
</head>
<body class="font-sans text-gray-800">

<div class="container mx-auto p-4 md:p-6">

    <header class="mb-6">
        <div class="flex items-center gap-3">
            <div>
                <h1 class="text-3xl font-bold text-gray-800">Giấy tờ & Đơn từ</h1>
                <p class="text-gray-500">Danh sách các hồ sơ, hợp đồng bạn đã tạo hoặc tham gia.</p>
            </div>
        </div>
    </header>

    <form id="search-form" method="GET" class="flex items-center mb-6">
        <div class="relative w-72">
            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none"><i class="fas fa-search text-gray-400"></i></div>
            <input type="search" id="search-input" name="search" class="bg-white outline-none border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block w-full pl-10 p-2" 
                placeholder="Tìm theo tiêu đề, tên, loại..." value="<?= htmlspecialchars($search) ?>">
        </div>
        <button type="submit" id="search-button" class="ml-2 px-4 py-2 text-sm font-medium text-white bg-gray-400 rounded-lg hover:bg-gray-500">Tìm</button>
    </form>

    <script>
        // 1. Lấy các phần tử HTML cần thiết qua ID
        const searchForm = document.getElementById('search-form');
        const searchInput = document.getElementById('search-input');
        const searchButton = document.getElementById('search-button');

        // 2. Hàm để thực hiện submit
        function submitSearch() {
            console.log('Đang chuẩn bị chuyển hướng bằng window.location...');

            // 1. Lấy giá trị từ ô input
            const searchValue = searchInput.value;

            // 2. (Quan trọng) Mã hóa giá trị để đảm bảo URL hợp lệ
            //    Ví dụ: "áo thun" -> "ao%20thun"
            const encodedSearchValue = encodeURIComponent(searchValue.trim());

            // 3. Xây dựng URL mới một cách thủ công
            //    Hãy chắc chắn rằng đường dẫn cơ sở '/app/trangchu.php' là đúng với cấu trúc dự án của bạn
            const newUrl = `trangchu.php?page=../moi_gioi/ql_hoso&search=${encodedSearchValue}`;

            // 4. Dùng window.location.href để chuyển hướng trình duyệt đến URL mới
            window.location.href = newUrl;
        }

        // 3. Gán sự kiện cho nút bấm
        searchButton.addEventListener('click', function(event) {
            event.preventDefault(); // Ngăn hành vi mặc định của nút
            submitSearch();
        });

        // 4. Gán sự kiện cho ô input (submit khi nhấn Enter)
        searchInput.addEventListener('keydown', function(event) {
            if (event.key === 'Enter') {
                event.preventDefault(); // Ngăn form bị gửi đi 2 lần
                submitSearch();
            }
        });
    </script>

    <main class="bg-white rounded-xl shadow-xl overflow-hidden border border-gray-200">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="py-3 px-4 text-left text-xs font-bold text-gray-500 uppercase">Tiêu đề</th>
                        <th class="py-3 px-4 text-left text-xs font-bold text-gray-500 uppercase">Loại</th>
                        <th class="py-3 px-4 text-left text-xs font-bold text-gray-500 uppercase">Người mua</th>
                        <th class="py-3 px-4 text-left text-xs font-bold text-gray-500 uppercase">Trạng thái</th>
                        <th class="py-3 px-4 text-center text-xs font-bold text-gray-500 uppercase">Hành động</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php if (empty($bieumau_list)): ?>
                        <tr><td colspan="5" class="p-8 text-center text-gray-500 text-lg">Bạn chưa có giấy tờ nào.</td></tr>
                    <?php else: ?>
                        <?php foreach($bieumau_list as $bm): 
                            $status_info = getStatusInfo($bm["trang_thai"]);
                        ?>
                            <tr class="hover:bg-blue-50/50 transition duration-150">
                                <td class="p-4 text-sm font-medium text-gray-600" title="<?= htmlspecialchars($bm["tieu_de"]) ?>"><?= htmlspecialchars($bm["tieu_de"]) ?></td>
                                <td class="p-4 text-sm text-gray-600"><?= htmlspecialchars(getLoaiText($bm["loai"])) ?></td>
                                <td class="p-4 text-sm text-gray-600"><?= htmlspecialchars($bm["ten_ben_mua"]) ?></td>
                                <td class="p-4"><span class="px-3 py-1 text-xs font-medium rounded-full border shadow-sm <?= $status_info['classes'] ?>"><?= $status_info['text'] ?></span></td>
                                <td class="p-4 text-sm text-gray-600 text-center">
                                    <button data-modal-toggle="docModal<?= $bm['id'] ?>" class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg flex items-center justify-center gap-1.5 mx-auto shadow-md transition transform hover:scale-105">
                                        <i class="fa-solid fa-eye"></i> Xem
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>

<?php foreach($bieumau_list as $bm): 
    $status_info = getStatusInfo($bm["trang_thai"]);
?>
    <div id="docModal<?= $bm["id"] ?>" class="fixed inset-0 bg-gray-900/60 hidden flex items-center justify-center z-50 p-4 transition-opacity duration-300 opacity-0" data-modal>
        <div class="bg-white rounded-lg shadow-2xl w-full max-w-md p-5 relative modal-content transform transition-transform duration-300 scale-95">
            <button data-modal-close="docModal<?= $bm['id'] ?>" class="absolute top-3 right-3 text-gray-400 hover:text-gray-800 transition-colors"><i class="fa-solid fa-xmark text-2xl"></i></button>
            <h2 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2"><i class="fa-solid fa-file-lines text-blue-600"></i> Chi tiết Đơn từ</h2>
            <div class="border-t border-gray-200 pt-4 space-y-3 text-sm">
                <div class="flex justify-between items-start"><span class="text-gray-500">Tiêu đề:</span><p class="font-bold text-gray-800 text-right w-3/5"><?= htmlspecialchars($bm["tieu_de"]) ?></p></div>
                <div class="flex justify-between items-center"><span class="text-gray-500">Loại:</span><span class="font-semibold text-gray-800 flex items-center gap-1.5"><i class="fa-solid fa-tags text-blue-500"></i> <?= htmlspecialchars(getLoaiText($bm["loai"])) ?></span></div>
                <div class="flex justify-between items-center"><span class="text-gray-500">Trạng thái:</span><span class="px-2.5 py-0.5 text-xs font-bold rounded-full border <?= $status_info['classes'] ?>"><?= $status_info['text'] ?></span></div>
                <div class="flex justify-between items-start"><span class="text-gray-500">Người mua:</span><span class="font-semibold text-gray-800"><?= htmlspecialchars($bm["ten_ben_mua"]) ?></span></div>
                <div class="flex justify-between items-start"><span class="text-gray-500">Người bán:</span><span class="font-semibold text-gray-800"><?= htmlspecialchars($bm["ten_ben_ban"]) ?></span></div>
                <div class="!mt-4 pt-3 border-t border-gray-200 flex justify-between items-center">
                    <span class="text-gray-500 flex items-center gap-2"><i class="fa-solid fa-paperclip"></i> Tệp đính kèm:</span>
                    <a href="../../../storage/documents/<?= htmlspecialchars($bm["tep_dk"]) ?>" class="text-blue-600 hover:underline font-bold flex items-center gap-1.5 transition" target="_blank"><i class="fa-solid fa-download"></i> Tải về</a>
                </div>
            </div>
            <?php if($bm["trang_thai"] == "daduyet"): ?>
                <div class="mt-5 pt-4 border-t border-gray-200 flex justify-end">
                    <button data-action-button data-id="<?= $bm['id'] ?>" data-status="daky" class="bg-green-600 hover:bg-green-700 text-white font-bold px-5 py-2.5 rounded-lg flex items-center gap-2 shadow-lg shadow-green-500/20 transition transform hover:scale-105">
                        <i class="fa-solid fa-signature"></i> XÁC NHẬN ĐÃ KÝ
                    </button>
                </div>
            <?php endif; ?>
        </div>
    </div>
<?php endforeach; ?>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // Xử lý mở/đóng Modal
    document.body.addEventListener('click', (e) => {
        const toggleButton = e.target.closest('[data-modal-toggle]');
        const closeButton = e.target.closest('[data-modal-close]');
        
        if (toggleButton) {
            const modalId = toggleButton.dataset.modalToggle;
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.classList.remove('hidden');
                setTimeout(() => { 
                    modal.classList.add('opacity-100');
                    modal.querySelector('.modal-content').classList.add('scale-100');
                }, 10);
            }
        }

        if (closeButton) {
            const modal = closeButton.closest('[data-modal]');
            if (modal) {
                modal.classList.remove('opacity-100');
                modal.querySelector('.modal-content').classList.remove('scale-100');
                setTimeout(() => modal.classList.add('hidden'), 300);
            }
        }
    });
    
    // Xử lý nút hành động
    document.body.addEventListener('click', (e) => {
        const actionButton = e.target.closest('[data-action-button]');
        if (actionButton) {
            const id = actionButton.dataset.id;
            const status = actionButton.dataset.status;
            capNhatTrangThai(id, status);
        }
    });
});

function capNhatTrangThai(id, trangThai) {
    if (!confirm(`Bạn có chắc chắn muốn xác nhận đã ký cho đơn ID ${id} không?`)) return;
    const formData = new URLSearchParams({ id: id, trang_thai: trangThai });
    fetch("../../models/cn_trangthai_bm.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: formData
    })
    .then(res => res.ok ? res.json() : Promise.reject(`Lỗi HTTP! Status: ${res.status}`))
    .then(data => {
        alert(data.message);
        if (data.status === "success") location.reload();
    })
    .catch(err => {
        console.error("Lỗi cập nhật:", err);
        alert("Đã xảy ra lỗi khi cập nhật.");
    });
}
</script>
</body>
</html>