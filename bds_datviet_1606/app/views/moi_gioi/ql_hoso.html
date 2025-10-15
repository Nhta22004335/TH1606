<?php

require_once "../../../config/database.php";

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
    <title>Quản lý hồ sơ (MG)</title>
    <style>.modal-content { max-height: 90vh; overflow-y: auto; }</style>
</head>
<body>

<div class="container">

    <header class="mb-6 border-b pb-4">
        <h1 class="text-2xl font-bold text-gray-800">Quản lý hồ sơ (MG)</h1>
        <p class="text-sm mt-2 text-gray-500">Danh sách các hồ sơ, hợp đồng bạn đã tạo hoặc tham gia.</p>
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
                                    <button data-modal-toggle="docModal<?= $bm['id'] ?>" class="bg-blue-600 hover:bg-blue-700 text-white text-xs font-medium px-4 py-2 rounded-lg flex items-center justify-center gap-1.5 mx-auto shadow-md transition transform hover:scale-105">
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
 </div>
            
             <div class="!mt-4 pt-3 border-t border-gray-200">
                <span class="text-gray-500 flex items-center gap-2 mb-2">
                    <i class="fa-solid fa-paperclip"></i> **Tệp đính kèm:**
                </span>

                <?php if (!empty($bm["tep_dk"])): ?>
                    <iframe src="../../../storage/documents/<?= urlencode($bm["tep_dk"]) ?>" class="w-full h-48 border mt-2 rounded-lg"></iframe>
                    <a href="../../../storage/documents/<?= urlencode($bm["tep_dk"]) ?>" 
                        class="text-blue-600 hover:underline font-bold flex items-center gap-1.5 mt-2" 
                        target="_blank">
                        <i class="fa-solid fa-download"></i> Tải về
                    </a>
                <?php else: ?>
                    <p class="text-gray-400 italic mt-2">Chưa có tệp đính kèm.</p>
                    <form class="upload-form mt-2 flex flex-col gap-2" data-id="<?= $bm['id'] ?>" enctype="multipart/form-data">
                        <input type="file" name="tep_dk" required class="border border-gray-300 rounded px-2 py-1 text-sm">
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold px-4 py-2 rounded shadow transition transform hover:scale-105">
                            <i class="fa-solid fa-upload"></i> Upload tệp
                        </button>
                    </form>
                    <div class="upload-result text-sm mt-1"></div>
                <?php endif; ?>
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
// Hàm helper để JS có thể lấy class CSS mới (cần giống hàm PHP)
function getStatusInfoClient(status) {
    const map = {
        "choduyet": { 'text': "Chờ duyệt", 'classes': "bg-yellow-100 text-yellow-800 border-yellow-300" },
        "daduyet": { 'text': "Đã duyệt", 'classes': "bg-green-100 text-green-800 border-green-300" },
        "daky": { 'text': "Đã ký", 'classes': "bg-blue-100 text-blue-800 border-blue-300" },
        "huy": { 'text': "Đã hủy", 'classes': "bg-red-100 text-red-800 border-red-300" }
            };
    return map[status] || { 'text': status, 'classes': "bg-gray-100 text-gray-600 border-gray-300" };
}

document.addEventListener('DOMContentLoaded', () => {
    // --- 1. XỬ LÝ MỞ/ĐÓNG MODAL --- (Giữ nguyên)
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
    
    // --- 2. XỬ LÝ NÚT XÁC NHẬN ĐÃ KÝ (AJAX) --- (Đã tối ưu để không cần tải lại trang)
    document.body.addEventListener('click', (e) => {
        const actionButton = e.target.closest('[data-action-button]');
        if (actionButton) {
            const id = actionButton.dataset.id;
            const newStatus = actionButton.dataset.status;
            capNhatTrangThai(id, newStatus, actionButton);
        }
    });

    // --- 3. XỬ LÝ FORM UPLOAD TỆP (AJAX) --- (MỚI)
    document.querySelectorAll('.upload-form').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault(); // Ngăn hành vi submit mặc định của form
            
            const id = this.dataset.id;
            const fileInput = this.querySelector('input[name="tep_dk"]');
            const submitButton = this.querySelector('button[type="submit"]');
            const resultDiv = this.closest('.modal-content').querySelector('.upload-result');
            
            if (!fileInput.files.length) {
                alert('Vui lòng chọn một tệp để tải lên.');
                return;
            }

            // Chuẩn bị dữ liệu Form Data (quan trọng cho upload file)
            const formData = new FormData();
            formData.append('id_bm', id); // Thêm ID biểu mẫu
            formData.append('tep_dk', fileInput.files[0]); // Thêm file

            // Hiển thị trạng thái Loading
            const originalButtonHtml = submitButton.innerHTML;
            submitButton.disabled = true;
            submitButton.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Đang tải lên...';
            resultDiv.textContent = 'Đang xử lý...';
            resultDiv.className = 'upload-result text-sm mt-1 text-yellow-600';


            // Gửi yêu cầu AJAX
            fetch("../../models/upload_bm.php", {
                method: "POST",
                body: formData // Không cần đặt header Content-Type khi dùng FormData
               
            })
            .then(res => res.json())
            .then(data => {
                // alert(data.message);
                if (data.status === "success") {

                    resultDiv.textContent = data.message;
                    resultDiv.className = 'upload-result text-sm mt-1 text-green-600 font-semibold';
                    
                    // TẢI LẠI TRANG sau khi upload thành công
                    // Việc này đơn giản hóa việc cập nhật Modal để hiển thị iframe và link download mới
                    location.reload(); 
                    
                    // Nếu bạn không muốn tải lại trang, bạn sẽ cần:
                    // 1. Ẩn form upload.
                    // 2. Tạo và chèn iframe xem trước (iframe src="../../../storage/documents/${data.filename}")
                    // 3. Chèn link tải về mới.


                } else {
                    resultDiv.textContent = data.message;
                    resultDiv.className = 'upload-result text-sm mt-1 text-red-600 font-semibold';
                }
            })
            .catch(err => {
                console.error("Lỗi upload:", err);
                resultDiv.textContent = 'Lỗi hệ thống khi upload.';
                resultDiv.className = 'upload-result text-sm mt-1 text-red-600 font-semibold';
            })
            .finally(() => {
                // Khôi phục trạng thái nút (nếu không reload)
                submitButton.disabled = false;
                submitButton.innerHTML = originalButtonHtml;
            });
        });
    });
});

// Hàm xử lý Xác nhận Đã Ký (Được tối ưu để không cần tải lại trang)
function capNhatTrangThai(id, trangThai, buttonElement) {
    if (!confirm(`Bạn có chắc chắn muốn xác nhận đã ký cho đơn ID ${id} không?`)) return;
    
    // 1. Lưu và chuyển trạng thái Loading
    const originalButtonHtml = buttonElement.innerHTML;
    buttonElement.disabled = true;
    buttonElement.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Đang xử lý...';

    const formData = new URLSearchParams({ id: id, trang_thai: trangThai });
    
    fetch("../../models/cn_trangthai_bm.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: formData
    })
    .then(res => res.ok ? res.json() : Promise.reject(`Lỗi HTTP! Status: ${res.status}`))
    .then(data => {
        alert(data.message);
        if (data.status === "success") {
            // Cập nhật giao diện sau khi thành công (Không cần tải lại trang)
            const statusInfo = getStatusInfoClient(trangThai);
            
            // 2. Cập nhật trong Bảng chính
            const rowInTable = document.querySelector(`[data-modal-toggle="docModal${id}"]`).closest('tr');
            const statusBadgeTable = rowInTable.querySelector('span:first-child');
            if (statusBadgeTable) {
                statusBadgeTable.textContent = statusInfo.text;
                statusBadgeTable.className = `px-3 py-1 text-xs font-medium rounded-full border shadow-sm ${statusInfo.classes}`;
            }

            // 3. Cập nhật trong Modal
            const modal = buttonElement.closest('[data-modal]');
            const statusSpanInModal = modal.querySelector('.px-2\\.5');
            if (statusSpanInModal) {
                statusSpanInModal.textContent = statusInfo.text;
                statusSpanInModal.className = `px-2.5 py-0.5 text-xs font-bold rounded-full border ${statusInfo.classes}`;
            }
            
            // 4. Ẩn nút hành động
            buttonElement.closest('.flex').remove();

            // Đóng Modal (tùy chọn)
            modal.classList.remove('opacity-100');
            modal.querySelector('.modal-content').classList.remove('scale-100');
            setTimeout(() => modal.classList.add('hidden'), 300);

        } else {
             // Khôi phục trạng thái nút nếu lỗi
            buttonElement.disabled = false;
            buttonElement.innerHTML = originalButtonHtml;
        }
    })
    .catch(err => {
        console.error("Lỗi cập nhật:", err);
        alert("Đã xảy ra lỗi khi cập nhật trạng thái.");
        // Khôi phục trạng thái nút nếu lỗi
        buttonElement.disabled = false;
        buttonElement.innerHTML = originalButtonHtml;
    });
}
</script>
</body>
</html>