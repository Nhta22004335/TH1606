<?php
require_once "../../../config/database.php";
$pdo = ketnoicsdl();

// ID người dùng đang đăng nhập (nếu có thể)
$id = $_GET['id'] ?? null;

// === LẤY DỮ LIỆU HỘI THOẠI ===
$sql = "SELECT 
            h.id AS id_hop_thoai,
            h.id_nguoi_1,
            h.id_nguoi_2,
            h.da_khoa,
            h.da_xoa,
            h.ngay_tao,

            -- Thông tin người 1
            nd1.ten_dang_nhap AS ten_nguoi_1,
            nd1.email AS email_nguoi_1,
            info1.ho_ten AS ho_ten_nguoi_1,

            -- Thông tin người 2
            nd2.ten_dang_nhap AS ten_nguoi_2,
            nd2.email AS email_nguoi_2,
            info2.ho_ten AS ho_ten_nguoi_2

        FROM hop_thoai h
        JOIN nguoi_dung nd1 ON h.id_nguoi_1 = nd1.id
        JOIN nguoi_dung nd2 ON h.id_nguoi_2 = nd2.id
        LEFT JOIN info_nguoi_dung info1 ON nd1.id = info1.id_nguoi_dung
        LEFT JOIN info_nguoi_dung info2 ON nd2.id = info2.id_nguoi_dung
        WHERE (:id::uuid IS NULL OR h.id_nguoi_1 = :id OR h.id_nguoi_2 = :id)
        ORDER BY h.ngay_tao DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute([':id' => $id]);
$ds_hop_thoai = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Hội thoại Chuyên nghiệp</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>
    <style>
        /* Thêm một số tùy chỉnh nhỏ nếu cần */
        body {
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }
    </style>
</head>
<body class="bg-gray-50 antialiased">

<div class="container">
    
    <header class="pb-4 border-b border-slate-200 mb-6">
        <div class="sm:flex sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-500">Danh sách hộp thoại - người dùng</h1>
                <p class="mt-1 text-sm text-slate-600">
                    Tổng hợp các cuộc hội thoại giữa người dùng trên hệ thống. Sử dụng bộ lọc để tìm kiếm nhanh.
                </p>
            </div>
            <div>
                <label for="search" class="block text-sm font-medium text-gray-700">Tìm kiếm</label>
                <div class="relative mt-1">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <ion-icon name="search-outline" class="w-5 h-5 text-gray-400"></ion-icon>
                    </div>
                    <input type="text" id="search"
                        class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                        placeholder="Tìm theo ID, Email, Tên...">
                </div>
            </div>
            <div>
                <label for="status" class="block text-sm font-medium text-gray-700">Trạng thái</label>
                <select id="status"
                    class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="all">Tất cả</option>
                    <option value="active">Hoạt động</option>
                    <option value="locked">Đã khóa</option>
                    <option value="deleted">Đã ẩn</option>
                </select>
            </div>
        </div>
    </header>

    <div class="bg-white rounded-lg shadow-lg overflow-hidden border border-gray-200">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-800">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-200 uppercase tracking-wider">ID Hội thoại</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-200 uppercase tracking-wider">Người tham gia</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-200 uppercase tracking-wider">Trạng thái</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-200 uppercase tracking-wider">Ngày tạo</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-200 uppercase tracking-wider">Hành động</th>
                    </tr>
                </thead>
                <tbody id="data-table-body" class="bg-white divide-y divide-gray-200">
                    </tbody>
            </table>
        </div>

        <div class="flex items-center justify-between border-t border-gray-200 bg-white px-4 py-3 sm:px-6">
            <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-start">
                <p id="pagination-summary" class="text-sm text-gray-700"></p>
            </div>
            <div class="flex-1 flex justify-between sm:justify-end">
                <nav id="pagination-controls" class="isolate inline-flex -space-x-px rounded-md shadow-sm" aria-label="Pagination"></nav>
            </div>
        </div>
    </div>
</div>

<script>
    // --- Lấy dữ liệu PHP đưa sang JS ---
    const allConversations = <?= json_encode($ds_hop_thoai, JSON_UNESCAPED_UNICODE) ?>;
    const currentUserId = <?= json_encode($id, JSON_UNESCAPED_UNICODE) ?>; 
    let filteredConversations = [...allConversations];
    
    // --- DOM Elements ---
    let currentPage = 1;
    const itemsPerPage = 10; 
    const tableBody = document.getElementById('data-table-body');
    const paginationSummary = document.getElementById('pagination-summary');
    const paginationControls = document.getElementById('pagination-controls');
    const searchInput = document.getElementById('search');
    const statusSelect = document.getElementById('status');

    /**
     * HÀM MỚI: Thực thi hành động (Khóa, Xóa...) bằng Fetch
     */
    async function performAction(event, action, convoId, element) {
        event.preventDefault(); // Ngăn link # nhảy trang

        // 1. Hiển thị thông báo xác nhận
        let message = "";
        if (action === 'lock') message = 'Bạn có chắc muốn KHÓA hội thoại này?';
        if (action === 'delete') message = 'Bạn có chắc muốn XÓA ẨN hội thoại này?';
        if (action === 'unlock') message = 'Bạn có muốn MỞ KHÓA hội thoại này?';
        if (action === 'restore') message = 'Bạn có muốn KHÔI PHỤC hội thoại này?';

        if (!confirm(message)) {
            return; // Hủy nếu người dùng không đồng ý
        }

        // 2. Gọi Fetch
        const url = `../../models/cn_trangthai_hopthoai_qt.php?action=${action}&id=${convoId}`;
        try {
            const response = await fetch(url, { method: 'GET' });
            if (!response.ok) {
                throw new Error('Lỗi từ máy chủ: ' + response.statusText);
            }

            const data = await response.json();

            // 3. Cập nhật giao diện (UI)
            if (data.success && data.newStatus) {
                const row = element.closest('tr');
                const statusCell = row.querySelector('td:nth-child(3)');
                const actionCell = row.querySelector('td:nth-child(5)'); // Cột thứ 5 là "Hành động"

                // Cập nhật lại trạng thái (cột 3)
                statusCell.innerHTML = getStatusHtml(data.newStatus);
                
                // Cập nhật lại các nút hành động (cột 5)
                actionCell.innerHTML = getActionsHtml(data.newStatus, convoId);

                // Cập nhật lại dữ liệu gốc trong JS để bộ lọc hoạt động đúng
                const originalIndex = allConversations.findIndex(c => c.id_hop_thoai === convoId);
                if (originalIndex > -1) {
                    allConversations[originalIndex].da_khoa = (data.newStatus === 'locked');
                    allConversations[originalIndex].da_xoa = (data.newStatus === 'deleted');
                }

            } else {
                alert(data.message || 'Có lỗi xảy ra.');
            }
        } catch (error) {
            console.error('Fetch error:', error);
            alert('Lỗi kết nối. Không thể thực hiện hành động.');
        }
    }

    /**
     * Hàm lấy HTML cho các nút trạng thái (Giữ nguyên)
     */
    function getStatusHtml(status) {
        switch (status) {
            case 'active':
                return '<span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Hoạt động</span>';
            case 'locked':
                return '<span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Đã khóa</span>';
            case 'deleted':
                return '<span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-200 text-gray-800">Đã ẩn</span>';
            default:
                return '';
        }
    }

    /**
     * HÀM ĐÃ CẬP NHẬT: Sửa href thành onclick
     */
    function getActionsHtml(status, convoId) {
        let actions = `<div class="flex items-center justify-end space-x-1.5">
            <a href="trangchu.php?page=quanly_chitiet_hopthoai&id=${convoId}" title="Xem chi tiết" class="p-1.5 rounded-full text-indigo-600 hover:text-indigo-800 transition-colors">
                <ion-icon name="eye-outline" class="w-5 h-5"></ion-icon>
            </a>`;

        // Nút Khóa / Mở khóa (dùng fetch)
        if (status === 'locked') {
            actions += `
                <a href="#" onclick="performAction(event, 'unlock', '${convoId}', this)" title="Mở khóa" class="p-1.5 rounded-full text-green-600 hover:text-green-800 transition-colors">
                    <ion-icon name="lock-open-outline" class="w-5 h-5"></ion-icon>
                </a>`;
        } else {
            actions += `
                <a href="#" onclick="performAction(event, 'lock', '${convoId}', this)" title="Khóa" class="p-1.5 rounded-full text-yellow-600 hover:text-yellow-800 transition-colors">
                    <ion-icon name="lock-closed-outline" class="w-5 h-5"></ion-icon>
                </a>`;
        }

        // Nút Xóa / Khôi phục (dùng fetch) - Theo logic mới của bạn
        if (status === 'deleted') {
            actions += `
                <a href="#" onclick="performAction(event, 'restore', '${convoId}', this)" title="Khôi phục" class="p-1.5 rounded-full text-blue-600 hover:text-blue-800 transition-colors">
                    <ion-icon name="refresh-outline" class="w-5 h-5"></ion-icon>
                </a>`;
        } else {
            actions += `
                <a href="#" onclick="performAction(event, 'delete', '${convoId}', this)" title="Xóa" class="p-1.5 rounded-full text-red-600 hover:text-red-800 transition-colors">
                    <ion-icon name="trash-outline" class="w-5 h-5"></ion-icon>
                </a>`;
        }

        actions += `</div>`;
        return actions;
    }

    //
    // --- CÁC HÀM CÒN LẠI GIỮ NGUYÊN ---
    //

    function getStatus(convo) {
        if (convo.da_xoa) return "deleted";
        if (convo.da_khoa) return "locked";
        return "active";
    }

    function displayData(page, data) {
        tableBody.innerHTML = '';
        const totalItems = data.length;
        if (totalItems === 0) {
             tableBody.innerHTML = '<tr><td colspan="5" class="px-6 py-10 text-center text-gray-500">Không tìm thấy cuộc hội thoại nào phù hợp.</td></tr>';
             paginationSummary.innerHTML = 'Hiển thị 0 kết quả';
             paginationControls.innerHTML = ''; // Xóa phân trang
             return;
        }

        const startIndex = (page - 1) * itemsPerPage;
        const endIndex = Math.min(startIndex + itemsPerPage, totalItems);
        const paginatedItems = data.slice(startIndex, endIndex);

        paginatedItems.forEach(item => {
            const status = getStatus(item);
            const row = `
                <tr class="transition-colors">
                    <td class="px-6 py-4 whitespace-nowrap" title="${item.id_hop_thoai}">
                        <span class="font-mono text-sm text-gray-700">${item.id_hop_thoai.substring(0, 8)}...</span>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex flex-col">
                            <div class="text-sm font-semibold text-gray-900">${item.ho_ten_nguoi_1 ?? item.ten_nguoi_1}</div>
                            <div class="text-xs text-gray-500">${item.email_nguoi_1}</div>
                        </div>
                        <div class="flex flex-col mt-2 pt-2 border-t border-gray-100">
                            <div class="text-sm font-semibold text-gray-900">${item.ho_ten_nguoi_2 ?? item.ten_nguoi_2}</div>
                            <div class="text-xs text-gray-500">${item.email_nguoi_2}</div>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">${getStatusHtml(status)}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${new Date(item.ngay_tao).toLocaleString('vi-VN')}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">${getActionsHtml(status, item.id_hop_thoai)}</td>
                </tr>`;
            tableBody.innerHTML += row;
        });

        paginationSummary.innerHTML = `
            Hiển thị <span class="font-medium">${startIndex + 1}</span>
            đến <span class="font-medium">${endIndex}</span>
            trong <span class="font-medium">${totalItems}</span> kết quả
        `;
        setupPagination(totalItems);
    }

    function setupPagination(totalItems) {
        paginationControls.innerHTML = '';
        const totalPages = Math.ceil(totalItems / itemsPerPage);
        if (totalPages <= 1) return; 

        const prevDisabled = currentPage === 1 ? 'pointer-events-none opacity-50' : '';
        paginationControls.innerHTML += `
            <a href="#" onclick="changePage(event, ${currentPage - 1})" 
               class="relative inline-flex items-center rounded-l-md px-2 py-2 text-gray-400 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 ${prevDisabled}">
                <ion-icon name="chevron-back-outline" class="h-5 w-5"></ion-icon>
            </a>`;
        for (let i = 1; i <= totalPages; i++) {
            const isActive = (i === currentPage) ? 'z-10 bg-indigo-600 text-white focus-visible:outline-indigo-600' : 'text-gray-900 ring-gray-300 hover:bg-gray-50';
            paginationControls.innerHTML += `
                <a href="#" onclick="changePage(event, ${i})" 
                   class="relative inline-flex items-center px-4 py-2 text-sm font-semibold ring-1 ring-inset ${isActive}">${i}</a>`;
        }
        const nextDisabled = currentPage === totalPages ? 'pointer-events-none opacity-50' : '';
        paginationControls.innerHTML += `
            <a href="#" onclick="changePage(event, ${currentPage + 1})" 
               class="relative inline-flex items-center rounded-r-md px-2 py-2 text-gray-400 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 ${nextDisabled}">
                <ion-icon name="chevron-forward-outline" class="h-5 w-5"></ion-icon>
            </a>`;
    }

    function changePage(event, page) {
        event.preventDefault(); 
        const totalItems = filteredConversations.length;
        const totalPages = Math.ceil(totalItems / itemsPerPage);
        if (page < 1) page = 1;
        if (page > totalPages) page = totalPages;
        currentPage = page;
        displayData(currentPage, filteredConversations);
    }

    function updateView() {
        const searchQuery = searchInput.value.toLowerCase();
        const statusFilter = statusSelect.value;

        filteredConversations = allConversations.filter(convo => {
            const status = getStatus(convo);
            const statusMatch = (statusFilter === 'all') || (status === statusFilter);
            const searchMatch = (
                convo.id_hop_thoai.toLowerCase().includes(searchQuery) ||
                (convo.ho_ten_nguoi_1 ?? '').toLowerCase().includes(searchQuery) ||
                (convo.ten_nguoi_1 ?? '').toLowerCase().includes(searchQuery) ||
                (convo.email_nguoi_1 ?? '').toLowerCase().includes(searchQuery) ||
                (convo.ho_ten_nguoi_2 ?? '').toLowerCase().includes(searchQuery) ||
                (convo.ten_nguoi_2 ?? '').toLowerCase().includes(searchQuery) ||
                (convo.email_nguoi_2 ?? '').toLowerCase().includes(searchQuery)
            );
            return statusMatch && searchMatch;
        });

        currentPage = 1; 
        displayData(currentPage, filteredConversations);
    }

    document.addEventListener('DOMContentLoaded', () => {
        searchInput.addEventListener('input', updateView);
        statusSelect.addEventListener('change', updateView);
        updateView(); 
    });
</script>

</body>
</html>