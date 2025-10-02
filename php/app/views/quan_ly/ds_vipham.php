<?php
// Demo dữ liệu vi phạm
$vipham = [
    [
        "id" => 1,
        "user" => "Nguyễn Văn A",
        "loai" => "Bài đăng",
        "mota" => "Đăng tin trùng lặp nhiều lần",
        "ngay" => "2025-10-02",
        "trangthai" => "Chưa xử lý"
    ],
    [
        "id" => 2,
        "user" => "Trần Thị B",
        "loai" => "Ảnh",
        "mota" => "Ảnh chứa thông tin sai sự thật",
        "ngay" => "2025-09-30",
        "trangthai" => "Đã xử lý"
    ],
    [
        "id" => 3,
        "user" => "Lê Văn C",
        "loai" => "Câu từ",
        "mota" => "Sử dụng từ ngữ phản cảm",
        "ngay" => "2025-09-28",
        "trangthai" => "Đang xử lý"
    ],
    [
        "id" => 4,
        "user" => "Phạm Thị D",
        "loai" => "Hoạt động khác",
        "mota" => "Spam tin nhắn cho nhiều khách hàng",
        "ngay" => "2025-09-25",
        "trangthai" => "Chưa xử lý"
    ]
];
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý vi phạm</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/alpinejs" defer></script>
</head>
<body x-data="viPhamApp()">

    <!-- Tiêu đề -->
    <h1 class="flex items-center text-2xl font-bold mb-4 mt-4 text-gray-600">
        <img src="../../../public/assets/anhht/0/president.gif" 
             class="w-12 h-12 mr-3" alt="icon">
        Quản lý vi phạm
    </h1>

    <!-- Bảng vi phạm -->
    <div class="bg-white shadow-lg rounded-lg overflow-hidden px-6">
        <table class="min-w-full">
            <thead class="bg-gray-100 text-gray-700">
                <tr>
                    <th class="py-3 px-4 text-left">#</th>
                    <th class="py-3 px-4 text-left">Người dùng</th>
                    <th class="py-3 px-4 text-left">Loại vi phạm</th>
                    <th class="py-3 px-4 text-left">Mô tả</th>
                    <th class="py-3 px-4 text-left">Ngày</th>
                    <th class="py-3 px-4 text-left">Trạng thái</th>
                    <th class="py-3 px-4 text-center">Hành động</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($vipham as $vp): ?>
                <tr class="border-b hover:bg-gray-50">
                    <td class="py-3 px-4"><?= $vp["id"] ?></td>
                    <td class="py-3 px-4"><?= $vp["user"] ?></td>
                    <td class="py-3 px-4">
                        <span class="px-2 py-1 rounded text-sm
                            <?= $vp['loai']=='Bài đăng' ? 'bg-red-100 text-red-700' : '' ?>
                            <?= $vp['loai']=='Ảnh' ? 'bg-yellow-100 text-yellow-700' : '' ?>
                            <?= $vp['loai']=='Câu từ' ? 'bg-blue-100 text-blue-700' : '' ?>
                            <?= $vp['loai']=='Hoạt động khác' ? 'bg-purple-100 text-purple-700' : '' ?>
                        ">
                            <?= $vp["loai"] ?>
                        </span>
                    </td>
                    <td class="py-3 px-4"><?= $vp["mota"] ?></td>
                    <td class="py-3 px-4"><?= $vp["ngay"] ?></td>
                    <td class="py-3 px-4">
                        <span class="px-2 py-1 rounded text-sm
                            <?= $vp['trangthai']=='Chưa xử lý' ? 'bg-orange-100 text-orange-700' : '' ?>
                            <?= $vp['trangthai']=='Đã xử lý' ? 'bg-green-100 text-green-700' : '' ?>
                            <?= $vp['trangthai']=='Đang xử lý' ? 'bg-blue-100 text-blue-700' : '' ?>
                        ">
                            <?= $vp["trangthai"] ?>
                        </span>
                    </td>
                    <td class="py-3 px-4 text-center">
                        <button 
                            class="px-3 py-1 bg-indigo-500 hover:bg-indigo-600 text-white rounded text-sm"
                            @click="showDetail($el)"
                            data-id="<?= $vp['id'] ?>"
                            data-user="<?= htmlspecialchars($vp['user']) ?>"
                            data-loai="<?= htmlspecialchars($vp['loai']) ?>"
                            data-mota="<?= htmlspecialchars($vp['mota']) ?>"
                            data-ngay="<?= $vp['ngay'] ?>"
                            data-trangthai="<?= $vp['trangthai'] ?>">
                            Xem chi tiết
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Modal chi tiết -->
    <div x-show="openModal" 
         class="fixed inset-0 bg-black/50 flex items-center justify-center z-50"
         x-transition>
        <div class="bg-white rounded-lg shadow-lg p-6 w-96" @click.away="closeModal">
            <h2 class="text-lg font-bold mb-4 text-gray-700">
                Chi tiết vi phạm #<span x-text="current.id"></span>
            </h2>
            <p class="mb-2 text-sm text-gray-600">Người vi phạm: <b x-text="current.user"></b></p>
            <p class="mb-2 text-sm text-gray-600">Loại: <b x-text="current.loai"></b></p>
            <p class="mb-2 text-sm text-gray-600">Mô tả: <i x-text="current.mota"></i></p>
            <p class="mb-2 text-sm text-gray-600">Ngày: <span x-text="current.ngay"></span></p>
            <p class="mb-4 text-sm text-gray-600">Trạng thái: <span x-text="current.trangthai"></span></p>

            <div class="flex justify-end gap-2">
                <button @click="closeModal"
                        class="px-4 py-2 bg-gray-400 hover:bg-gray-500 text-white rounded">
                    Đóng
                </button>
                <button class="px-4 py-2 bg-yellow-500 hover:bg-yellow-600 text-white rounded">
                    Cảnh cáo
                </button>
                <button class="px-4 py-2 bg-red-500 hover:bg-red-600 text-white rounded">
                    Xóa
                </button>
            </div>
        </div>
    </div>

    <script>
    function viPhamApp() {
        return {
            openModal: false,
            current: {},
            showDetail(el) {
                this.current = {
                    id: el.dataset.id,
                    user: el.dataset.user,
                    loai: el.dataset.loai,
                    mota: el.dataset.mota,
                    ngay: el.dataset.ngay,
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
