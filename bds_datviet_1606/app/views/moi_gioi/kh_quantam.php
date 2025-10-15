<?php
// Giả lập môi trường và kết nối CSDL để có thể xem trước
// Trong môi trường thực tế, bạn sẽ sử dụng file kết nối thật
require_once "../../../config/database.php";
$pdo = ketnoicsdl();

// Lấy id BĐS từ GET
$khachhang_quantam = [];
$error_msg = null;

// =======================================================
// ====[ XỬ LÝ YÊU CẦU AJAX ĐỂ LƯU LỊCH SỬ TÌM KIẾM ]====
// =======================================================

// Lấy ID Admin/Người dùng hiện tại (giả định đã được lưu trong session)
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
$id_admin = $_SESSION['id_nguoi_dung'] ?? null; 

// ...
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_search') {
    $search = trim($_POST['search'] ?? '');
    $id = $id_admin; // Gán ID cho biến $id (như trong code bạn cung cấp)

    if (!empty($search)) {
        try {
            // SỬ DỤNG ĐOẠN CODE CỦA BẠN (ĐÃ THÊM thoi_gian_tim cho đầy đủ)
            // LƯU Ý: Nếu cột `thoi_gian_tim` không có trong bảng, hãy xóa `, thoi_gian_tim` và `NOW()`
            $sql = "INSERT INTO lich_su_tim_kiem (id_nguoi_dung, tu_khoa_tim_kiem, thoi_gian_tim) VALUES (?, ?, NOW())";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$id, $search]);

            // Phản hồi thành công cho AJAX (quan trọng)
            header('Content-Type: application/json');
            echo json_encode(['status' => 'search_saved']);
            
        } catch (PDOException $e) {
            // error_log("Lỗi khi lưu lịch sử tìm kiếm: " . $e->getMessage()); // Ghi log nếu cần
            // Phản hồi lỗi cho AJAX nếu có
            header('Content-Type: application/json');
            http_response_code(500);
            echo json_encode(['status' => 'error']);
        }
    } else {
        // Phản hồi nếu từ khóa rỗng (không cần lưu)
        header('Content-Type: application/json');
        echo json_encode(['status' => 'skipped']);
    }
    // DỪNG VÀ THOÁT KHỎI TẬP LỆNH
    exit; 
}
// ...
// =======================================================
// ====[ KẾT THÚC KHỐI XỬ LÝ AJAX ]====
// =======================================================


// Hàm kiểm tra UUID hợp lệ
function is_valid_uuid($uuid) {
    return preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $uuid);
}

try {
    // Câu truy vấn được cập nhật để nhóm theo khách hàng và đếm số lần quan tâm
    $sql = "
        SELECT
            nd.id,
            info.ho_ten,
            nd.email,
            COUNT(yc.id) AS so_lan_quan_tam,
            MAX(yc.ngay_tao) AS ngay_quan_tam_moi_nhat
        FROM
            yeu_cau yc
        JOIN
            nguoi_dung nd ON yc.id_nguoi_dung = nd.id
        LEFT JOIN
            info_nguoi_dung info ON nd.id = info.id_nguoi_dung
        GROUP BY
            nd.id, info.ho_ten, nd.email
        ORDER BY
            ngay_quan_tam_moi_nhat DESC
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $khachhang_quantam = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
catch (PDOException $e) {
    $error_msg = "Lỗi truy vấn cơ sở dữ liệu: " . $e->getMessage();
    $khachhang_quantam = [];
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Khách hàng quan tâm BĐS</title>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <style>
        :root { font-family: 'Inter', sans-serif; }
        .custom-scrollbar::-webkit-scrollbar { width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background-color: #a5b4fc; border-radius: 3px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: #e0e7ff; }
    </style>
</head>

<body class="bg-slate-100 text-slate-800">

<div class="flex min-h-screen">
    
    <main class="flex-grow p-4 sm:p-6 lg:p-8 overflow-y-auto" x-data="customerApp(<?= htmlspecialchars(json_encode($khachhang_quantam)) ?>)">
        <div class="max-w-6xl mx-auto">
            <header class="mb-8 pb-4 border-b border-slate-200">
                <h1 class="text-3xl font-bold text-slate-900">Khách Hàng Quan Tâm</h1>
                
                <div class="mt-5 w-full md:w-2/3 lg:w-1/2">
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-slate-400">
                            <i class="fa-solid fa-magnifying-glass w-4 h-4"></i> 
                        </div>
                        <input type="text" 
                               placeholder="Tìm kiếm theo tên khách hàng hoặc email..." 
                               x-model="search"
                               x-on:input.debounce.500ms="sendSearchToBackend($event.target.value)"
                               class="w-full border border-slate-300 rounded-lg pl-10 pr-4 py-2.5 text-base shadow-sm 
                                      focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 
                                      placeholder-slate-500 transition duration-150 ease-in-out">
                    </div>
                </div>
                </header>

            <?php if ($error_msg): ?>
                <div class="bg-red-100 border-l-4 border-red-500 text-red-800 p-4 rounded-lg mb-6">
                    <p><?= htmlspecialchars($error_msg) ?></p>
                </div>
            <?php endif; ?>

            <div class="space-y-4 max-h-[75vh] overflow-y-auto custom-scrollbar pr-2">
                
                <template x-for="kh in filteredCustomers" :key="kh.id">
                    <div class="p-4 bg-white rounded-xl border border-slate-200 shadow-sm flex flex-col sm:flex-row items-start sm:items-center gap-4 hover:shadow-md transition">
                        <div class="flex items-center gap-4 flex-grow">
                            <div class="relative flex-shrink-0">
                                 <div class="w-11 h-11 bg-indigo-100 text-indigo-600 rounded-full flex items-center justify-center font-semibold text-lg">
                                    <span><span x-text="kh.ho_ten ? kh.ho_ten.charAt(0).toUpperCase() : '?'"></span></span>
                                </div>
                                <div class="absolute -bottom-1 -right-1 bg-indigo-600 text-white text-xs font-bold w-6 h-6 rounded-full flex items-center justify-center border-2 border-white" title="Số lần quan tâm">
                                    <span x-text="kh.so_lan_quan_tam"></span>
                                </div>
                            </div>
                            <div class="text-sm min-w-0">
                                <h2 class="font-semibold text-slate-800 truncate" x-text="kh.ho_ten ?? '[Chưa có tên]'"></h2>
                                <p class="text-slate-500 text-xs truncate" x-text="kh.email"></p>
                            </div>
                        </div>
                        <div class="text-sm text-slate-500 text-left sm:text-right flex-shrink-0 w-full sm:w-auto border-t sm:border-0 pt-3 sm:pt-0">
                            <p class="font-medium text-slate-700">Lần cuối quan tâm</p>
                            <p class="text-xs mt-1" x-text="formatDate(kh.ngay_quan_tam_moi_nhat)"></p>
                        </div>
                    </div>
                </template>
                
                <div x-show="!filteredCustomers.length" class="text-center py-16 text-slate-500 flex flex-col items-center bg-white rounded-xl border border-slate-200">
                    <svg class="h-16 w-16 text-slate-300" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 15.75l-2.489-2.489m0 0a3.375 3.375 0 10-4.773-4.773 3.375 3.375 0 004.774 4.774zM21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    <p class="font-semibold mt-4" x-text="customers.length > 0 ? 'Không tìm thấy khách hàng nào khớp với từ khóa.' : 'Chưa có khách hàng quan tâm'"></p>
                </div>
                
            </div>
        </div>
    </main>
</div>

<script>
    function formatDate(isoString) {
        if (!isoString) return '';
        const date = new Date(isoString);
        return date.toLocaleDateString('vi-VN') + ' ' + date.toLocaleTimeString('vi-VN', { hour: '2-digit', minute: '2-digit' });
    }

    // Hàm gửi tìm kiếm (sử dụng AJAX)
    function sendSearchToBackend(term) {
        const trimmedTerm = term.trim();
        // Chỉ gửi khi từ khóa có ít nhất 2 ký tự
        if (trimmedTerm.length < 2) { 
            return; 
        }
        
        // Gửi yêu cầu POST tới chính file này.
        fetch(window.location.href, { 
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=save_search&search=${encodeURIComponent(trimmedTerm)}` 
        })
        .catch(error => {
            console.error('Lỗi kết nối khi gửi từ khóa:', error);
        });
    }

    // Khối dữ liệu Alpine.js
    document.addEventListener('alpine:init', () => {
        Alpine.data('customerApp', (initialData) => ({
            search: '',
            customers: initialData,
            
            // Hàm tính toán danh sách khách hàng đã lọc
            get filteredCustomers() {
                if (this.search === '') return this.customers;
                const searchLower = this.search.toLowerCase();
                return this.customers.filter(c =>
                    (c.ho_ten && c.ho_ten.toLowerCase().includes(searchLower)) ||
                    c.email.toLowerCase().includes(searchLower)
                );
            },
            
            formatDate: formatDate,
            sendSearchToBackend: sendSearchToBackend,
        }));
    });
</script>

</body>
</html>