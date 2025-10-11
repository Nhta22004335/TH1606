<?php
// Demo dữ liệu vi phạm
$vipham = [
    [
        "id" => 1,
        "user" => "Nguyễn Văn A",
        "loai" => "Bài đăng",
        "mota" => "Đăng tin trùng lặp nhiều lần (3 lần)",
        "ngay" => "2025-10-02",
        "trangthai" => "Chưa xử lý"
    ],
    [
        "id" => 2,
        "user" => "Trần Thị B",
        "loai" => "Ảnh",
        "mota" => "Ảnh chứa thông tin sai sự thật về dự án",
        "ngay" => "2025-09-30",
        "trangthai" => "Đã xử lý"
    ],
    [
        "id" => 3,
        "user" => "Lê Văn C",
        "loai" => "Câu từ",
        "mota" => "Sử dụng từ ngữ phản cảm và thiếu chuẩn mực",
        "ngay" => "2025-09-28",
        "trangthai" => "Đang xử lý"
    ],
    [
        "id" => 4,
        "user" => "Phạm Thị D",
        "loai" => "Hoạt động khác",
        "mota" => "Spam tin nhắn cho nhiều khách hàng liên tục",
        "ngay" => "2025-09-25",
        "trangthai" => "Chưa xử lý"
    ],
    [
        "id" => 5,
        "user" => "Hoàng Minh G",
        "loai" => "Bài đăng",
        "mota" => "Sai lệch thông tin về giá và diện tích",
        "ngay" => "2025-09-24",
        "trangthai" => "Đã xử lý"
    ],
];
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý vi phạm</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/alpinejs" defer></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" xintegrity="sha512-SnH5WK+bZxgPHs44uWIX+LLMDJ8S+anWHD9+lWlI/Bw4g8q6uL+yqT2S8cRAB6XQp9r/9C7M/dFm3J8mN/K2uYmQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        .table-responsive { overflow-x: auto; }
        .badge-loai { font-weight: 600; padding: 0.25rem 0.65rem; border-radius: 9999px; font-size: 0.75rem; }
    </style>
</head>
<body x-data="viPhamApp()">

    <!-- Header -->
    <header class="mb-6 border-b border-gray-400 pb-4">
        <h1 class="flex items-center text-2xl font-bold text-gray-800">
            Quản Lý Vi Phạm
        </h1>
        <p class="text-gray-500 text-sm mt-2">Theo dõi và xử lý các hành vi vi phạm quy tắc nền tảng của người dùng.</p>
    </header>

    <!-- Bảng vi phạm (Responsive Table) -->
    <div class="bg-white shadow-xl rounded-xl overflow-hidden">
        <div class="table-responsive">
            <table class="min-w-full text-sm">
                <thead class="bg-indigo-50 text-indigo-700 uppercase tracking-wider">
                    <tr>
                        <th class="py-4 px-6 text-center w-12">#</th>
                        <th class="py-4 px-6 text-left w-48">Người dùng</th>
                        <th class="py-4 px-6 text-left w-32">Loại vi phạm</th>
                        <th class="py-4 px-6 text-left">Mô tả chi tiết</th>
                        <th class="py-4 px-6 text-center w-24">Ngày báo cáo</th>
                        <th class="py-4 px-6 text-center w-32">Trạng thái</th>
                        <th class="py-4 px-6 text-center w-28">Hành động</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php foreach ($vipham as $vp): ?>
                    <tr class="hover:bg-gray-50 transition duration-150 group">
                        <td class="py-3 px-6 text-center font-medium text-gray-700"><?= $vp["id"] ?></td>
                        <td class="py-3 px-6 font-medium text-gray-900"><?= $vp["user"] ?></td>
                        <td class="py-3 px-6">
                            <?php 
                                $badgeClass = match($vp['loai']) {
                                    'Bài đăng' => 'bg-red-100 text-red-700',
                                    'Ảnh' => 'bg-yellow-100 text-yellow-700',
                                    'Câu từ' => 'bg-blue-100 text-blue-700',
                                    default => 'bg-purple-100 text-purple-700',
                                };
                            ?>
                            <span class="badge-loai <?= $badgeClass ?>">
                                <?= $vp["loai"] ?>
                            </span>
                        </td>
                        <td class="py-3 px-6 text-gray-600 truncate max-w-xs"><?= $vp["mota"] ?></td>
                        <td class="py-3 px-6 text-center text-gray-500"><?= date('d/m/Y', strtotime($vp["ngay"])) ?></td>
                        <td class="py-3 px-6 text-center">
                            <?php 
                                $statusClass = match($vp['trangthai']) {
                                    'Chưa xử lý' => 'bg-orange-100 text-orange-700 font-bold',
                                    'Đã xử lý' => 'bg-green-100 text-green-700',
                                    'Đang xử lý' => 'bg-blue-100 text-blue-700',
                                    default => 'bg-gray-100 text-gray-500',
                                };
                            ?>
                            <span class="px-3 py-1 rounded-full text-xs font-semibold <?= $statusClass ?>">
                                <?= $vp["trangthai"] ?>
                            </span>
                        </td>
                        <td class="py-3 px-6 text-center">
                            <button 
                                class="px-4 py-1.5 bg-indigo-500 hover:bg-indigo-600 text-white rounded-lg text-xs font-medium transition duration-150 shadow-md group-hover:scale-[1.05]"
                                @click="showDetail($el)"
                                data-id="<?= $vp['id'] ?>"
                                data-user="<?= htmlspecialchars($vp['user']) ?>"
                                data-loai="<?= htmlspecialchars($vp['loai']) ?>"
                                data-mota="<?= htmlspecialchars($vp['mota']) ?>"
                                data-ngay="<?= $vp['ngay'] ?>"
                                data-trangthai="<?= $vp['trangthai'] ?>">
                                Chi tiết
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal chi tiết -->
    <div x-show="openModal" x-cloak 
         class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center z-50 p-4"
         x-transition:enter="ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-md transform transition-all" 
             @click.away="closeModal"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
            
            <!-- Modal Header -->
            <div class="p-6 border-b bg-indigo-50 rounded-t-xl flex justify-between items-center">
                <h2 class="text-xl font-bold text-indigo-800 flex items-center">
                    <i class="fas fa-exclamation-triangle mr-3 text-red-500"></i> Hồ sơ vi phạm #<span x-text="current.id"></span>
                </h2>
                <button @click="closeModal" class="text-gray-400 hover:text-gray-600 transition">
                    <i class="fas fa-times text-lg"></i>
                </button>
            </div>

            <!-- Modal Body -->
            <div class="p-6 space-y-4">
                <div class="flex justify-between border-b pb-2">
                    <p class="text-sm font-medium text-gray-500">Người vi phạm:</p>
                    <p class="text-sm font-semibold text-gray-900" x-text="current.user"></p>
                </div>
                
                <div class="flex justify-between border-b pb-2">
                    <p class="text-sm font-medium text-gray-500">Loại vi phạm:</p>
                    <p class="text-sm font-semibold text-red-600" x-text="current.loai"></p>
                </div>
                
                <div class="border-b pb-2">
                    <p class="text-sm font-medium text-gray-500 mb-1">Mô tả chi tiết:</p>
                    <p class="text-sm italic text-gray-700 bg-gray-50 p-3 rounded-lg border" x-text="current.mota"></p>
                </div>
                
                <div class="flex justify-between pb-2">
                    <p class="text-sm font-medium text-gray-500">Ngày báo cáo:</p>
                    <p class="text-sm text-gray-700" x-text="current.ngay"></p>
                </div>
                
                <div class="flex justify-between pt-2 border-t">
                    <p class="text-sm font-medium text-gray-500">Trạng thái:</p>
                    <span class="px-3 py-1 rounded-full text-xs font-bold" 
                          :class="{
                              'bg-orange-100 text-orange-700': current.trangthai === 'Chưa xử lý',
                              'bg-green-100 text-green-700': current.trangthai === 'Đã xử lý',
                              'bg-blue-100 text-blue-700': current.trangthai === 'Đang xử lý'
                          }"
                          x-text="current.trangthai">
                    </span>
                </div>
            </div>

            <!-- Modal Footer - Hành động -->
            <div class="p-6 bg-gray-50 rounded-b-xl flex justify-end gap-3">
                <button @click="closeModal"
                        class="px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-800 rounded-lg font-medium transition">
                    Đóng
                </button>
                <button class="px-4 py-2 bg-yellow-500 hover:bg-yellow-600 text-white rounded-lg font-medium transition shadow-md">
                    <i class="fas fa-bullhorn mr-1"></i> Cảnh cáo
                </button>
                <button class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg font-medium transition shadow-md">
                    <i class="fas fa-user-slash mr-1"></i> Khóa tài khoản
                </button>
            </div>
        </div>
    </div>

    <script>
    function viPhamApp() {
        return {
            openModal: false,
            current: {
                id: '',
                user: '',
                loai: '',
                mota: '',
                ngay: '',
                trangthai: ''
            },
            showDetail(el) {
                this.current = {
                    id: el.dataset.id,
                    user: el.dataset.user,
                    loai: el.dataset.loai,
                    mota: el.dataset.mota,
                    // Định dạng lại ngày tháng cho đẹp hơn trong modal
                    ngay: new Date(el.dataset.ngay).toLocaleDateString('vi-VN', { 
                        year: 'numeric', 
                        month: 'long', 
                        day: 'numeric' 
                    }),
                    trangthai: el.dataset.trangthai
                };
                this.openModal = true;
            },
            closeModal() {
                this.openModal = false;
                this.current = {};
            }
        }
    }
    </script>

</body>
</html>
