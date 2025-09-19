<?php
    $users = [
        ["id"=>1,"name"=>"Nguyễn Văn A","email"=>"a.nguyen@example.com","phone"=>"0909123456","vaitro"=>"Admin","hoatdong"=>"Online","sodon"=>12,"ngaytao"=>"2024-01-10","avatar"=>"https://i.pravatar.cc/150?img=1"],
        ["id"=>2,"name"=>"Trần Thị B","email"=>"b.tran@example.com","phone"=>"0912345678","vaitro"=>"User","hoatdong"=>"Offline","sodon"=>3,"ngaytao"=>"2023-11-05","avatar"=>"https://i.pravatar.cc/150?img=2"],
        ["id"=>3,"name"=>"Lê Văn C","email"=>"c.le@example.com","phone"=>"0987654321","vaitro"=>"User","hoatdong"=>"Online","sodon"=>7,"ngaytao"=>"2024-03-15","avatar"=>"https://i.pravatar.cc/150?img=3"],
        ["id"=>4,"name"=>"Phạm Thị D","email"=>"d.pham@example.com","phone"=>"0909876543","vaitro"=>"Moderator","hoatdong"=>"Offline","sodon"=>20,"ngaytao"=>"2023-09-20","avatar"=>"https://i.pravatar.cc/150?img=4"],
        ["id"=>5,"name"=>"Hoàng Văn E","email"=>"e.hoang@example.com","phone"=>"0911987654","vaitro"=>"User","hoatdong"=>"Online","sodon"=>5,"ngaytao"=>"2024-05-12","avatar"=>"https://i.pravatar.cc/150?img=5"],
        ["id"=>6,"name"=>"Đỗ Thị F","email"=>"f.do@example.com","phone"=>"0905432198","vaitro"=>"User","hoatdong"=>"Offline","sodon"=>0,"ngaytao"=>"2024-02-22","avatar"=>"https://i.pravatar.cc/150?img=6"],
        ["id"=>7,"name"=>"Vũ Văn G","email"=>"g.vu@example.com","phone"=>"0912765432","vaitro"=>"Admin","hoatdong"=>"Online","sodon"=>15,"ngaytao"=>"2023-12-01","avatar"=>"https://i.pravatar.cc/150?img=7"],
        ["id"=>8,"name"=>"Ngô Thị H","email"=>"h.ngo@example.com","phone"=>"0902345678","vaitro"=>"Moderator","hoatdong"=>"Offline","sodon"=>8,"ngaytao"=>"2024-04-10","avatar"=>"https://i.pravatar.cc/150?img=8"],
    ];

    $filters = [];
    if (isset($_GET['boloc'])) {
        $filters = json_decode($_GET['boloc'], true);
    }
?>
<!DOCTYPE html>
<html lang="vi" x-data="{ openFilter:false }">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Quản lý tài khoản người dùng</title>
<script src="https://cdn.tailwindcss.com"></script>
<script src="https://unpkg.com/alpinejs" defer></script>
<link rel="stylesheet" href="../../public/assets/fontawesome/css/all.min.css">
</head>
<body>

<div class="max-w-7xl mx-auto mt-4 flex gap-6">
    <!-- Bộ lọc Desktop -->
    <div class="hidden md:block w-64 bg-white shadow rounded-xl p-4 h-fit">
        <h2 class="flex items-center text-lg font-semibold mb-4">
            <img src="../../public/assets/anhht/0/filter.gif" alt="Filter" class="w-10 h-10 mr-2">
            Bộ lọc
        </h2>

        <label class="block mb-2 text-sm">Hoạt động</label>
        <select id="hoatdong-desktop" class="w-full border rounded-lg p-2 mb-4">
            <option value="" <?= (($filters['hoatdong'] ?? '') == 'Tất cả') ? 'selected' : ''?>>Tất cả</option>
            <option value="Online" <?= (($filters['hoatdong'] ?? '') == 'Online') ? 'selected' : ''?>>Online</option>
            <option value="Offline" <?= (($filters['hoatdong'] ?? '') == 'Offline') ? 'selected' : ''?>>Offline</option>
        </select>

        <label class="block mb-2 text-sm">Trạng thái</label>
        <select id="trangthai-desktop" class="w-full border rounded-lg p-2 mb-4">
            <option value="" <?= (($filters['trangthai'] ?? '') == 'Tất cả') ? 'selected' : ''?>>Tất cả</option>
            <option value="Đang hoạt động" <?= (($filters['trangthai'] ?? '') == 'Đang hoạt động') ? 'selected' : ''?>>Đang hoạt động</option>
            <option value="Chưa kích hoạt" <?= (($filters['trangthai'] ?? '') == 'Chưa kích hoạt') ? 'selected' : ''?>>Chưa kích hoạt</option>
            <option value="Khóa" <?= (($filters['trangthai'] ?? '') == 'Khóa') ? 'selected' : ''?>>Khóa</option>
        </select>

        <label class="block mb-2 text-sm">Vai trò</label>
        <select id="vaitro-desktop" class="w-full border rounded-lg p-2 mb-4">
            <option value="" <?= (($filters['vaitro'] ?? '') == 'Tất cả') ? 'selected' : ''?>>Tất cả</option>
            <option value="Admin" <?= (($filters['vaitro'] ?? '') == 'Admin') ? 'selected' : ''?>>Admin</option>
            <option value="Moderator" <?= (($filters['vaitro'] ?? '') == 'Moderator') ? 'selected' : ''?>>Moderator</option>
            <option value="User" <?= (($filters['vaitro'] ?? '') == 'User') ? 'selected' : ''?>>User</option>
        </select>

        <label class="block mb-2 text-sm">Ngày tạo tài khoản trước</label>
        <input id="ngaytruoc-desktop" value="<?= ($filters['ngaytruoc'] ?? '') ?>" type="date" class="w-full border rounded-lg p-2 mb-4">

        <label class="block mb-2 text-sm">Số đơn lớn hơn</label>
        <input id="sodon-desktop" value="<?= ($filters['sodon'] ?? '') ?>" type="number" placeholder="Số đơn tối thiểu" class="w-full border rounded-lg p-2 mb-4">

        <div class="flex gap-3 mt-4">
            <!-- Nút áp dụng -->
            <button id="btnloc-desktop" class="flex-1 bg-blue-600 text-white py-2 rounded-lg hover:bg-blue-700 transition">Áp dụng</button>
            <!-- Nút hủy -->
            <button id="btnhuy-desktop" class="flex-1 bg-gray-300 text-gray-800 py-2 rounded-lg hover:bg-gray-400 transition">Hủy</button>
        </div>
    </div>

    <!-- Mobile filter overlay -->
    <div x-show="openFilter" class="fixed inset-0 bg-black bg-opacity-50 flex justify-start z-50 md:hidden">
        <div class="bg-white w-64 h-full p-4 shadow-lg overflow-y-auto" @click.away="openFilter=false">
            <div class="flex justify-between items-center mb-4">
                <h2 class="flex items-center text-lg font-semibold">
                    <img src="../../public/assets/anhht/0/filter.gif" alt="Quản lý sản phẩm" style="width: 40px; height: 40px; margin-right: 10px;">
                    Bộ lọc
                </h2>
                <button @click="openFilter=false" class="text-gray-600 hover:text-gray-800"><i class="fas fa-times"></i></button>
            </div>

            <label class="block mb-2 text-sm">Trạng thái</label>
            <select class="w-full border rounded-lg p-2 mb-4">
                <option value="" <?= (($filters['hoatdong'] ?? '') == 'Tất cả') ? 'selected' : ''?>>Tất cả</option>
                <option value="Online" <?= (($filters['hoatdong'] ?? '') == 'Online') ? 'selected' : ''?>>Online</option>
                <option value="Offline" <?= (($filters['hoatdong'] ?? '') == 'Offline') ? 'selected' : ''?>>Offline</option>
            </select>

            <label class="block mb-2 text-sm">Quyền hạn</label>
            <select class="w-full border rounded-lg p-2 mb-4">
                <option value="" <?= (($filters['vaitro'] ?? '') == 'Tất cả') ? 'selected' : ''?>>Tất cả</option>
                <option value="Admin" <?= (($filters['vaitro'] ?? '') == 'Admin') ? 'selected' : ''?>>Admin</option>
                <option value="Moderator" <?= (($filters['vaitro'] ?? '') == 'Moderator') ? 'selected' : ''?>>Moderator</option>
                <option value="User" <?= (($filters['vaitro'] ?? '') == 'User') ? 'selected' : ''?>>User</option>
            </select>

            <label class="block mb-2 text-sm">Ngày tạo tài khoản trước</label>
            <input id="ngaytruoc-desktop" value="<?= ($filters['ngaytruoc'] ?? '') ?>" type="date" class="w-full border rounded-lg p-2 mb-4">

            <label class="block mb-2 text-sm">Số đơn lớn hơn</label>
            <input id="sodon-desktop" value="<?= ($filters['sodon'] ?? '') ?>" type="number" placeholder="Số đơn tối thiểu" class="w-full border rounded-lg p-2 mb-4">

            <button class="w-full bg-blue-600 text-white py-2 rounded-lg hover:bg-blue-700">Áp dụng</button>
            <button id="btnhuy-desktop" class="flex-1 bg-gray-300 text-gray-800 py-2 rounded-lg hover:bg-gray-400 transition">Hủy</button>
        </div>
    </div>

    <!-- Nội dung -->
    <div class="flex-1">
        <!-- Header + mobile filter -->
        <div class="flex justify-between items-center mb-6">
            <h1 class="flex items-center text-2xl font-bold text-gray-600">
                <img src="../../public/assets/anhht/0/user.gif" alt="Users" style="width: 50px; height: 50px; margin-right: 10px;">
                Quản lý tài khoản
            </h1>
            <div class="flex gap-2">
                <button @click="openFilter=true" class="md:hidden mr-4 bg-gray-200 px-3 py-2 rounded-lg shadow hover:bg-gray-300">
                    <i class="fas fa-filter"></i>
                </button>
            </div>
        </div>
        
        <!-- Grid user card -->
        <div class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
            <?php if (empty($filters)): ?>
                <?php foreach($users as $u): ?>
                    <div class="bg-white shadow rounded-xl overflow-hidden hover:shadow-lg transition flex flex-col relative">
                        <div class="p-4 flex flex-col items-center">
                            <img src="<?= $u['avatar'] ?>" alt="<?= $u['name'] ?>" class="w-20 h-20 rounded-full object-cover border-2 border-gray-200">
                            <h2 class="mt-2 font-semibold text-gray-800 text-center"><?= $u['name'] ?></h2>
                            <p class="text-gray-500 text-sm text-center"><?= $u['email'] ?></p>
                            <p class="text-gray-500 text-sm text-center"><?= $u['phone'] ?></p>
                            <span class="mt-2 px-2 py-1 rounded-full text-xs font-semibold <?= $u['hoatdong']=='online'?'bg-green-100 text-green-700':'bg-gray-200 text-gray-700' ?>"><?= ucfirst($u['hoatdong']) ?></span>
                            <span class="mt-1 px-2 py-1 rounded-full text-xs font-semibold <?= $u['vaitro']=='Admin'?'bg-red-100 text-red-700':($u['vaitro']=='Moderator'?'bg-blue-100 text-blue-700':'bg-yellow-100 text-yellow-700') ?>"><?= $u['vaitro'] ?></span>
                            <p class="mt-2 text-sm text-gray-600">Đã đặt: <?= $u['sodon'] ?> đơn</p>
                            <p class="mt-1 text-xs text-gray-400">Ngày tạo: <?= date("d/m/Y",strtotime($u['ngaytao'])) ?></p>
                        </div>
                        <!-- Nút hành động -->
                        <div class="flex justify-around border-t p-2 mt-auto">
                            <a href="#" class="text-blue-600 hover:text-blue-800"><i class="fas fa-edit"></i></a>
                            <a href="#" class="text-red-600 hover:text-red-800"><i class="fas fa-trash-alt"></i></a>
                            <a href="#" class="text-purple-600 hover:text-purple-800"><i class="fas fa-key"></i></a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php elseif (!empty($filters)): ?> 
                <?php foreach($users as $u): ?>
                    <?php
                        $match = true;
                        if (isset($filters['hoatdong']) && $filters['hoatdong'] !== $u['hoatdong']) $match = false;
                        if (isset($filters['trangthai']) && $filters['trangthai'] !== $u['trangthai']) $match = false;
                        if (isset($filters['vaitro']) && $filters['vaitro'] !== $u['vaitro']) $match = false;
                        if (isset($filters['ngaytruoc']) && $u['ngaytao'] > $filters['ngaytruoc']) $match = false;
                        if (isset($filters['sodon']) && $u['sodon'] > $filters['sodon']) $match = false;
                    ?>
                    <?php if ($match): ?>
                        <div class="bg-white shadow rounded-xl overflow-hidden hover:shadow-lg transition flex flex-col relative">
                            <div class="p-4 flex flex-col items-center">
                                <img src="<?= $u['avatar'] ?>" alt="<?= $u['name'] ?>" class="w-20 h-20 rounded-full object-cover border-2 border-gray-200">
                                <h2 class="mt-2 font-semibold text-gray-800 text-center"><?= $u['name'] ?></h2>
                                <p class="text-gray-500 text-sm text-center"><?= $u['email'] ?></p>
                                <p class="text-gray-500 text-sm text-center"><?= $u['phone'] ?></p>
                                <span class="mt-2 px-2 py-1 rounded-full text-xs font-semibold <?= $u['hoatdong']=='online'?'bg-green-100 text-green-700':'bg-gray-200 text-gray-700' ?>"><?= ucfirst($u['hoatdong']) ?></span>
                                <span class="mt-1 px-2 py-1 rounded-full text-xs font-semibold <?= $u['vaitro']=='Admin'?'bg-red-100 text-red-700':($u['vaitro']=='Moderator'?'bg-blue-100 text-blue-700':'bg-yellow-100 text-yellow-700') ?>"><?= $u['vaitro'] ?></span>
                                <p class="mt-2 text-sm text-gray-600">Đã đặt: <?= $u['sodon'] ?> đơn</p>
                                <p class="mt-1 text-xs text-gray-400">Ngày tạo: <?= date("d/m/Y",strtotime($u['ngaytao'])) ?></p>
                            </div>
                            <!-- Nút hành động -->
                            <div class="flex justify-around border-t p-2 mt-auto">
                                <a href="#" class="text-blue-600 hover:text-blue-800"><i class="fas fa-edit"></i></a>
                                <a href="#" class="text-red-600 hover:text-red-800"><i class="fas fa-trash-alt"></i></a>
                                <a href="#" class="text-purple-600 hover:text-purple-800"><i class="fas fa-key"></i></a>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    function apdungloc(prefix) {
        const keys = ["hoatdong", "vaitro", "trangthai", "ngaytruoc", "sodon"];
        let filters = {};

        keys.forEach(key => {
            const el = document.getElementById(key + "-" + prefix);
            if (el && el.value.trim() !== "") {
                filters[key] = el.value.trim();
            }
        });

        const boloc = encodeURIComponent(JSON.stringify(filters));
        window.location.href = "trangchu.php?page=taikhoan&boloc=" + boloc;
    }

    document.getElementById("btnloc-desktop").addEventListener("click", () => apdungloc("desktop"));
    document.getElementById("btnloc-mobile").addEventListener("click", () => apdungloc("mobile"));

    function huyloc(prefix) {
        window.location.href = "trangchu.php?page=taikhoan";
    }
    document.getElementById("btnhuy-desktop").addEventListener("click", () => huyloc("desktop"));
    document.getElementById("btnhuy-mobile").addEventListener("click", () => huyloc("mobile"));

</script>

</body>
</html>
