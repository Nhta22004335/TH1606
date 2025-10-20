<?php
if (session_status() == PHP_SESSION_NONE) { session_start(); }
require_once "../../../config/database.php"; 

// Hàm lấy thông tin trạng thái
function getStatusInfo($status) {
    switch ($status) {
        case "choduyet": return ['text' => "Chờ duyệt", 'icon' => 'fa-solid fa-clock', 'classes' => "text-yellow-600 bg-yellow-100"];
        case "daduyet": return ['text' => "Đã duyệt", 'icon' => 'fa-solid fa-check', 'classes' => "text-green-600 bg-green-100"];
        case "daky": return ['text' => "Đã ký", 'icon' => 'fa-solid fa-signature', 'classes' => "text-blue-600 bg-blue-100"];
        case "huy": return ['text' => "Đã hủy", 'icon' => 'fa-solid fa-times', 'classes' => "text-red-600 bg-red-100"];
        default: return ['text' => $status, 'icon' => 'fa-solid fa-question', 'classes' => "text-gray-600 bg-gray-100"];
    }
}

// Hàm lấy icon cho loại biểu mẫu
function getLoaiInfo($loai) {
    $map = [
        'hosomuaban' => ['text' => 'Hồ sơ mua bán', 'icon' => 'fa-solid fa-file-invoice-dollar'],
        'hosothue' => ['text' => 'Hồ sơ thuê', 'icon' => 'fa-solid fa-file-contract'],
        'bienban' => ['text' => 'Biên bản', 'icon' => 'fa-solid fa-file-pen']
    ];
    return $map[$loai] ?? ['text' => 'Không xác định', 'icon' => 'fa-solid fa-file'];
}

try {
    $pdo = ketnoicsdl();
} catch (PDOException $e) {
    die("Lỗi kết nối CSDL: " . $e->getMessage());
}

// ==========================================================
// == LOGIC PHP & SQL (Đã chuyển sang MySQL) ==
// ==========================================================
$search = $_GET['search'] ?? '';
$params = [];
$sql = "
    SELECT bm.id, bm.tieu_de, bm.loai, 
        info1.ho_ten AS ten_ben_mua,
        info2.ho_ten AS ten_ben_ban,
        bm.trang_thai, bm.tep_dk, bm.ngay_tao, bm.ngay_cn,
        nd1.avt AS avt_ben_mua, nd2.avt AS avt_ben_ban
    FROM bieu_mau bm
    JOIN nguoi_dung nd1 ON bm.ben_mua = nd1.id
    JOIN nguoi_dung nd2 ON bm.ben_ban = nd2.id
    LEFT JOIN info_nguoi_dung info1 ON info1.id_nguoi_dung = nd1.id
    LEFT JOIN info_nguoi_dung info2 ON info2.id_nguoi_dung = nd2.id
";

if (!empty($search)) {
    $searchable_columns = "CONCAT(bm.tieu_de, ' ', bm.loai, ' ', info1.ho_ten, ' ', info2.ho_ten, ' ', bm.trang_thai)";
    $sql .= " WHERE REPLACE({$searchable_columns}, ' ', '') LIKE REPLACE(:search_term, ' ', '')";
    $params[':search_term'] = "%" . trim($search) . "%";
}

$sql .= " ORDER BY bm.ngay_tao DESC";

$stmt = $pdo->prepare($sql);

if (!empty($params)) {
    $stmt->bindValue(':search_term', $params[':search_term'], PDO::PARAM_STR);
}

$stmt->execute();
$bieumau_list = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Xử lý avatar 
foreach ($bieumau_list as $key => $bm) {
    $bieumau_list[$key]['avt_ben_mua'] = '../../../storage/pictures/avt/' . ($bm['avt_ben_mua'] ?? 'default-avatar.png');
    $bieumau_list[$key]['avt_ben_ban'] = '../../../storage/pictures/avt/' . ($bm['avt_ben_ban'] ?? 'default-avatar.png');
}
?>
<!DOCTYPE html>
<html lang="vi" class="bg-slate-100 h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Biểu mẫu</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .document-item.active { background-color: #eef2ff; border-color: #4f46e5; }
        .hidden-pane { display: none; }
        .fade-in { animation: fadeIn 0.3s ease-out; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        .hiden-overlow-y-auto::-webkit-scrollbar { display: none; }
    </style>
</head>
<body class="p-4 md:p-6 h-full">

<div class="max-w-7xl mx-auto h-full flex flex-col">
    
    <header class="mb-4 border-b pb-2 flex-shrink-0">
        <h1 class="text-xl md:text-2xl font-bold text-gray-500">Quản lý Hợp đồng & Biểu mẫu</h1>
        <p class="text-sm mt-1 text-gray-600 hidden sm:block">Duyệt, xem và quản lý tất cả các hồ sơ, hợp đồng.</p>
    </header>

    <div class="flex-grow flex min-h-0 relative overflow-hidden">
        
        <aside id="master-pane" class="absolute inset-0 w-full md:relative md:w-2/5 lg:w-1/3 xl:w-1/4 h-full flex flex-col rounded-lg bg-white shadow-lg md:shadow-sm flex-shrink-0 transition-transform duration-300 ease-out z-20 transform md:translate-x-0">
            <div class="p-4 flex-shrink-0 border-b border-slate-200">
                <form id="search-form" method="GET" action="trangchu.php">
                    <input type="hidden" name="page" value="ql_bieumau">
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                            <i class="fas fa-search text-slate-400"></i>
                        </div>
                        <input type="search" name="search" id="search-input" class="bg-slate-50 border border-slate-300 text-slate-900 text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block w-full pl-10 p-2.5 transition" 
                            placeholder="Tìm kiếm biểu mẫu..." value="<?= htmlspecialchars($search) ?>">
                    </div>
                </form>
            </div>
            
            <div class="flex-grow overflow-y-auto" id="document-list-pane">
                <?php if (empty($bieumau_list)): ?>
                    <div class="p-8 text-center text-slate-500">
                        <i class="fa-solid fa-folder-open fa-3x text-slate-300"></i>
                        <p class="mt-4 font-semibold">Không tìm thấy biểu mẫu</p>
                        <p class="text-sm">Không có biểu mẫu nào khớp với tìm kiếm của bạn.</p>
                    </div>
                <?php else: ?>
                    <ul>
                        <?php foreach($bieumau_list as $bm): 
                            $status_info = getStatusInfo($bm["trang_thai"]);
                            $loai_info = getLoaiInfo($bm["loai"]);
                        ?>
                        <li class="document-item p-3 border-l-4 border-transparent hover:bg-slate-50 cursor-pointer transition-colors" data-id="<?= $bm['id'] ?>">
                            <div class="flex justify-between items-center mb-1">
                                <span class="text-xs font-semibold text-indigo-700 flex items-center">
                                    <i class="<?= $loai_info['icon'] ?> mr-2"></i>
                                    <?= htmlspecialchars($loai_info['text']) ?>
                                </span>
                                <span class="document-status-badge px-2 py-0.5 text-xs font-medium rounded-full <?= $status_info['classes'] ?>">
                                    <?= $status_info['text'] ?>
                                </span>
                            </div>
                            <h3 class="font-semibold text-sm text-slate-800 truncate" title="<?= htmlspecialchars($bm["tieu_de"]) ?>">
                                <?= htmlspecialchars($bm["tieu_de"]) ?>
                            </h3>
                            <div class="text-xs text-slate-500 mt-1 flex items-center gap-1">
                                <span class="truncate"><?= htmlspecialchars($bm["ten_ben_mua"]) ?></span> & 
                                <span class="truncate"><?= htmlspecialchars($bm["ten_ben_ban"]) ?></span>
                            </div>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </aside>

        <div id="detail-pane" class="absolute inset-0 md:relative md:flex-grow h-full bg-slate-50 shadow-xl md:shadow-none transform translate-x-full md:translate-x-0 transition-transform duration-300 ease-out z-10 overflow-y-auto hiden-overlow-y-auto">
            
            <div class="md:hidden sticky top-0 bg-white p-4 border-b border-slate-200 z-30 shadow-sm">
                <button id="back-to-list" class="flex items-center text-indigo-600 font-semibold text-sm hover:text-indigo-800 transition">
                    <i class="fa-solid fa-arrow-left mr-2"></i> Quay lại danh sách
                </button>
            </div>
            
            <div id="detail-placeholder" class="h-full flex flex-col items-center justify-center text-center text-slate-500 p-10">
                <i class="fa-regular fa-hand-pointer fa-4x text-slate-300"></i>
                <h2 class="mt-6 text-xl font-semibold text-slate-700">Chọn một biểu mẫu</h2>
                <p class="mt-2 text-sm">Chọn một mục từ danh sách bên trái để xem chi tiết.</p>
            </div>

            <div id="detail-content" class="hidden-pane p-4 md:p-6">
                <div class="space-y-4">
                    <div class="bg-white p-4 rounded-lg shadow-md border border-slate-200">
                        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-3">
                            <h2 id="detail-title" class="text-xl font-bold text-slate-800 mb-2 sm:mb-0"></h2>
                            <span id="detail-status-badge" class="px-3 py-1 text-sm font-bold rounded-full flex items-center gap-2 flex-shrink-0">
                                <i id="detail-status-icon"></i>
                                <span id="detail-status-text"></span>
                            </span>
                        </div>
                        <p class="text-sm text-slate-500">
                            Loại: <span id="detail-loai" class="font-medium"></span> <span class="mx-1 hidden sm:inline">|</span> 
                            <br class="sm:hidden"/>Ngày tạo: <span id="detail-ngay-tao" class="font-medium"></span>
                        </p>
                        <div id="detail-action-buttons" class="flex justify-end gap-3 mt-4 pt-4 border-t border-slate-200"></div>
                    </div>

                    <div class="bg-white p-4 rounded-lg shadow-md border border-slate-200">
                        <h3 class="text-base font-semibold text-slate-700 mb-3">Các bên liên quan</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="flex items-center">
                                <img id="detail-avt-mua" src="" alt="Avatar" class="w-9 h-9 rounded-full object-cover mr-3">
                                <div>
                                    <p class="text-xs text-slate-500">Bên Mua / Thuê</p>
                                    <p id="detail-ten-mua" class="font-semibold text-sm text-slate-800"></p>
                                </div>
                            </div>
                            <div class="flex items-center">
                                <img id="detail-avt-ban" src="" alt="Avatar" class="w-9 h-9 rounded-full object-cover mr-3">
                                <div>
                                    <p class="text-xs text-slate-500">Bên Bán / Cho Thuê</p>
                                    <p id="detail-ten-ban" class="font-semibold text-sm text-slate-800"></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white p-4 rounded-lg shadow-md border border-slate-200">
                        <h3 class="text-base font-semibold text-slate-700 mb-3">Xem Nhanh Tệp Đính Kèm</h3>
                        
                        <div id="document-viewer-container" class="border border-slate-200 rounded-lg overflow-hidden bg-slate-50">
                            <iframe id="document-viewer-iframe" 
                                    src="about:blank" 
                                    class="w-full h-80" frameborder="0">
                            </iframe>
                        </div>
                        <div id="document-viewer-fallback" class="hidden text-center p-6 bg-slate-50 rounded-lg border border-slate-200">
                            <i id="fallback-icon" class="fa-solid fa-file-lines fa-3x text-slate-400"></i>
                            <p class="text-sm font-semibold text-slate-600 mt-3">Không thể xem trước tệp</p>
                            <p class="text-xs text-slate-500 mt-1">Tệp này (<span id="fallback-file-ext" class="font-medium"></span>) không hỗ trợ xem trước.</p>
                            <a id="fallback-download-link" href="#" target="_blank" download class="mt-3 inline-flex items-center gap-1.5 text-xs text-indigo-600 hover:text-indigo-800 font-bold transition">
                                <i class="fa-solid fa-download"></i> Tải về
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // 1. CHUẨN BỊ DỮ LIỆU
    const allDocuments = <?= json_encode($bieumau_list) ?>;
    const docMap = new Map(allDocuments.map(doc => [doc.id.toString(), doc]));
    const getStatusInfoJS = {
        "choduyet": { "text": "Chờ duyệt", "icon": "fa-solid fa-clock", "classes": "text-yellow-600 bg-yellow-100" },
        "daduyet": { "text": "Đã duyệt", "icon": "fa-solid fa-check", "classes": "text-green-600 bg-green-100" },
        "daky": { "text": "Đã ký", "icon": "fa-solid fa-signature", "classes": "text-blue-600 bg-blue-100" },
        "huy": { "text": "Đã hủy", "icon": "fa-solid fa-times", "classes": "text-red-600 bg-red-100" },
    };
    const getLoaiInfoJS = {
        'hosomuaban': { "text": "Hồ sơ mua bán", "icon": "fa-solid fa-file-invoice-dollar" },
        'hosothue': { "text": "Hồ sơ thuê", "icon": "fa-solid fa-file-contract" },
        'bienban': { "text": "Biên bản", "icon": "fa-solid fa-file-pen" },
    };

    // 2. LẤY CÁC PHẦN TỬ DOM
    const masterPane = document.getElementById('master-pane');
    const detailPane = document.getElementById('detail-pane');
    const backButton = document.getElementById('back-to-list');
    const listPane = document.getElementById('document-list-pane');
    const placeholder = document.getElementById('detail-placeholder');
    const detailContent = document.getElementById('detail-content');

    // 3. HÀM CHUYỂN ĐỔI VIEW CHO MOBILE
    function switchToDetailView() {
        if (window.innerWidth < 768) {
            masterPane.classList.add('-translate-x-full'); // Ẩn Master (Đẩy sang trái 100%)
            detailPane.classList.remove('translate-x-full'); // Xóa lớp ẩn
            detailPane.classList.add('translate-x-0'); // Hiện Detail (Đưa về 0)
        }
    }

    function switchToListView() {
        if (window.innerWidth < 768) {
            masterPane.classList.remove('-translate-x-full'); // Hiện Master (Đưa về 0)
            detailPane.classList.add('translate-x-full'); // Ẩn Detail (Đẩy sang phải 100%)
            detailPane.classList.remove('translate-x-0');
        }
    }
    
    // 4. HÀM POPULATE
    function populateDetailPane(doc) {
        if (!doc) return;

        const status = getStatusInfoJS[doc.trang_thai] || getStatusInfoJS['default'];
        const loai = getLoaiInfoJS[doc.loai] || getLoaiInfoJS['default'];
        const ngayTao = new Date(doc.ngay_tao).toLocaleDateString('vi-VN', { day: '2-digit', month: '2-digit', year: 'numeric' });
        const filePath = `../../../storage/documents/${doc.tep_dk}`;
        const fileName = doc.tep_dk;
        const fileExt = fileName.split('.').pop().toLowerCase();

        // Điền thông tin chung
        detailContent.querySelector('#detail-title').textContent = doc.tieu_de;
        detailContent.querySelector('#detail-loai').textContent = loai.text;
        detailContent.querySelector('#detail-ngay-tao').textContent = ngayTao;

        // Cập nhật Status Badge
        const badge = detailContent.querySelector('#detail-status-badge');
        badge.className = `px-3 py-1 text-sm font-bold rounded-full flex items-center gap-2 flex-shrink-0 ${status.classes}`;
        detailContent.querySelector('#detail-status-icon').className = status.icon;
        detailContent.querySelector('#detail-status-text').textContent = status.text;

        // Cập nhật các bên
        detailContent.querySelector('#detail-avt-mua').src = doc.avt_ben_mua;
        detailContent.querySelector('#detail-ten-mua').textContent = doc.ten_ben_mua;
        detailContent.querySelector('#detail-avt-ban').src = doc.avt_ben_ban;
        detailContent.querySelector('#detail-ten-ban').textContent = doc.ten_ben_ban;
        
        // Cập nhật Trình xem tệp (iFrame)
        const iframe = detailContent.querySelector('#document-viewer-iframe');
        const container = detailContent.querySelector('#document-viewer-container');
        const fallback = detailContent.querySelector('#document-viewer-fallback');
        const fallbackDownloadLink = fallback.querySelector('#fallback-download-link');
        const viewableExtensions = ['pdf', 'jpg', 'jpeg', 'png', 'gif', 'txt', 'html']; 

        if (viewableExtensions.includes(fileExt)) {
            container.classList.remove('hidden');
            fallback.classList.add('hidden');
            iframe.src = 'about:blank'; 
            setTimeout(() => { iframe.src = filePath; }, 100); 
        } else {
            container.classList.add('hidden');
            fallback.classList.remove('hidden');
            const fallbackIcon = fallback.querySelector('#fallback-icon');
            fallback.querySelector('#fallback-file-ext').textContent = fileExt;
            fallbackDownloadLink.href = filePath;
            fallbackDownloadLink.download = fileName;
            if (fileExt === 'doc' || fileExt === 'docx') {
                fallbackIcon.className = 'fa-solid fa-file-word fa-3x text-blue-500';
            } else if (fileExt === 'xls' || fileExt === 'xlsx') {
                fallbackIcon.className = 'fa-solid fa-file-excel fa-3x text-green-500';
            } else if (fileExt === 'zip' || fileExt === 'rar') {
                fallbackIcon.className = 'fa-solid fa-file-zipper fa-3x text-yellow-500';
            } else {
                fallbackIcon.className = 'fa-solid fa-file-lines fa-3x text-slate-400';
            }
        }

        // Cập nhật nút hành động
        updateActionButtons(doc);

        // Hiển thị pane chi tiết
        placeholder.classList.add('hidden-pane');
        detailContent.classList.remove('hidden-pane');
        detailContent.classList.add('fade-in');
        setTimeout(() => detailContent.classList.remove('fade-in'), 300);
    }

    // 5. HÀM CẬP NHẬT NÚT HÀNH ĐỘNG
    function updateActionButtons(doc) {
        const container = document.getElementById('detail-action-buttons');
        container.innerHTML = '';
        let buttonsHTML = '';

        switch (doc.trang_thai) {
            case 'choduyet':
                buttonsHTML = `
                    <button data-action="huy" class="bg-gray-200 hover:bg-red-100 text-sm text-red-500 font-bold px-4 py-2 rounded-lg transition-colors">
                        Từ chối
                    </button>
                    <button data-action="daduyet" class="bg-green-500 hover:bg-green-600 text-sm text-white font-bold px-4 py-2 rounded-lg shadow-lg shadow-green-500/20 transition">
                        Duyệt
                    </button>
                `;
                break;
            case 'daduyet':
                buttonsHTML = `
                    <button data-action="huy" class="bg-gray-200 hover:bg-red-100 text-sm text-red-500 font-bold px-4 py-2 rounded-lg transition-colors">
                        Hủy
                    </button>
                    <button data-action="daky" class="bg-blue-500 hover:bg-blue-600 text-sm text-white font-bold px-4 py-2 rounded-lg shadow-lg shadow-blue-500/20 transition flex items-center gap-2">
                        <i class="fa-solid fa-signature"></i> Ký hợp đồng
                    </button>
                `;
                break;
            case 'huy':
                buttonsHTML = `
                    <button data-action="choduyet" class="bg-yellow-400 hover:bg-yellow-500 text-sm text-white font-bold px-4 py-2 rounded-lg transition flex items-center gap-2">
                        <i class="fa-solid fa-rotate-left"></i> Hoàn tác
                    </button>
                `;
                break;
            case 'daky':
                buttonsHTML = `<p class="text-sm text-slate-500 italic">Biểu mẫu đã được ký và hoàn tất.</p>`;
                break;
        }
        container.innerHTML = buttonsHTML;
        
        container.querySelectorAll('button').forEach(button => {
            button.addEventListener('click', () => {
                capNhatTrangThai(doc.id, button.dataset.action);
            });
        });
    }

    // 6. HÀM CẬP NHẬT TRẠNG THÁI (FETCH API)
    function capNhatTrangThai(id, newStatus) {
        const actionMap = {
            'daduyet': 'DUYỆT',
            'huy': 'TỪ CHỐI/HỦY',
            'daky': 'KÝ',
            'choduyet': 'HOÀN TÁC'
        };
        const confirmMessage = `Bạn có chắc chắn muốn ${actionMap[newStatus] || 'cập nhật'} biểu mẫu này không?`;
        
        if (!confirm(confirmMessage)) return;

        const formData = new URLSearchParams({ id: id, trang_thai: newStatus });

        fetch("../../models/cn_trangthai_bm.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: formData
        })
        .then(res => res.ok ? res.json() : Promise.reject(`Lỗi HTTP! Status: ${res.status}`))
        .then(data => {
            if (data.status === "success") {
                alert(data.message);
                
                const doc = docMap.get(id.toString());
                doc.trang_thai = newStatus;
                docMap.set(id.toString(), doc);

                const listItem = listPane.querySelector(`.document-item[data-id="${id}"]`);
                if (listItem) {
                    const status = getStatusInfoJS[newStatus];
                    const badge = listItem.querySelector('.document-status-badge');
                    badge.className = `document-status-badge px-2 py-0.5 text-xs font-medium rounded-full ${status.classes}`;
                    badge.textContent = status.text;
                }
                
                populateDetailPane(doc);

            } else {
                alert(data.message);
            }
        })
        .catch(err => {
            console.error("Lỗi cập nhật:", err);
            alert("Đã xảy ra lỗi khi cập nhật.");
        });
    }

    document.addEventListener('DOMContentLoaded', () => {
        // 7. GÁN SỰ KIỆN CLICK CHO DANH SÁCH
        listPane.addEventListener('click', (e) => {
            const item = e.target.closest('.document-item');
            if (!item) return;
            const id = item.dataset.id;
            const doc = docMap.get(id);
            
            listPane.querySelectorAll('.document-item.active').forEach(activeItem => {
                activeItem.classList.remove('active');
            });
            item.classList.add('active');
            
            if (doc) {
                populateDetailPane(doc);
                // GỌI HÀM CHUYỂN VIEW KHI NGƯỜI DÙNG CLICK
                switchToDetailView(); 
            }
        });

        // 8. GÁN SỰ KIỆN NÚT QUAY LẠI
        backButton.addEventListener('click', switchToListView);
        
        // 9. XỬ LÝ SEARCH (Giữ nguyên)
        document.getElementById('search-form').addEventListener('submit', function(e) {
            // Form sẽ tự động submit và tải lại trang với query
        });

        // 10. TỰ ĐỘNG LOAD ITEM ĐẦU TIÊN VÀ THIẾT LẬP TRẠNG THÁI BAN ĐẦU
        if (allDocuments.length > 0) {
            const firstDoc = allDocuments[0];
            const firstItemElement = listPane.querySelector(`.document-item[data-id="${firstDoc.id}"]`);
            if (firstItemElement) {
                firstItemElement.classList.add('active');
            }
            populateDetailPane(firstDoc);
            
            // THIẾT LẬP TRẠNG THÁI MOBILE BAN ĐẦU: MASTER HIỆN, DETAIL ẨN
            if (window.innerWidth < 768) {
                detailPane.classList.add('translate-x-full');
            }
        }
    });
</script>
</body>
</html>