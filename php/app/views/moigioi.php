<?php
    require_once "../../config/database.php";
    $pdo = ketnoicsdl();

    $page = $_GET['page'] ?? '';

    $sql = "
        SELECT 
            mg.id,
            mg.ho_ten,
            mg.gioi_tinh,
            mg.cty,
            mg.avt,
            mg.kinh_nghiem,
            mg.mo_ta,
            nd.id,
            nd.ten_dang_nhap,
            nd.email,
            nd.so_dt,
            nd.vai_tro,
            nd.trang_thai,
            nd.hoat_dong,
            nd.ngay_tao
        FROM moi_gioi mg
        JOIN nguoi_dung nd ON mg.id_nguoi_dung = nd.id";

    $stmt = $pdo->query($sql);
    $moigioi = $stmt->fetchAll();

    $search = $_GET['search'] ?? '';
    $keyword = "%" . $search . "%";
    
    $sql = "
        SELECT 
            mg.id,
            mg.ho_ten,
            mg.gioi_tinh,
            mg.cty,
            mg.avt,
            mg.kinh_nghiem,
            mg.mo_ta,
            nd.id AS id_nguoi_dung,
            nd.ten_dang_nhap,
            nd.email,
            nd.so_dt,
            nd.vai_tro,
            nd.trang_thai,
            nd.hoat_dong,
            nd.ngay_tao
        FROM moi_gioi mg
        JOIN nguoi_dung nd ON mg.id_nguoi_dung = nd.id
        WHERE (mg.ho_ten ILIKE :search OR mg.cty ILIKE :search)
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([':search' => $keyword]);
    $mangtkmoigioi = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
<title>Quản lý môi giới</title>
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
        <select id="hoatdong-desktop" class="w-full border rounded-lg p-2 mb-4 focus:outline-none focus:ring focus:border-blue-400">
            <option value="" <?= (($filters['hoatdong'] ?? '') == 'Tất cả') ? 'selected' : ''?>>Tất cả</option>
            <option value="Online" <?= (($filters['hoatdong'] ?? '') == 'Online') ? 'selected' : ''?>>Online</option>
            <option value="Offline" <?= (($filters['hoatdong'] ?? '') == 'Offline') ? 'selected' : ''?>>Offline</option>
        </select>

        <label class="block mb-2 text-sm">Trạng thái</label>
        <select id="trangthai-desktop" class="w-full border rounded-lg p-2 mb-4 focus:outline-none focus:ring focus:border-blue-400">
            <option value="" <?= (($filters['trangthai'] ?? '') == 'Tất cả') ? 'selected' : ''?>>Tất cả</option>
            <option value="Đang hoạt động" <?= (($filters['trangthai'] ?? '') == 'Đang hoạt động') ? 'selected' : ''?>>Đang hoạt động</option>
            <option value="Chưa kích hoạt" <?= (($filters['trangthai'] ?? '') == 'Chưa kích hoạt') ? 'selected' : ''?>>Chưa kích hoạt</option>
            <option value="Khóa" <?= (($filters['trangthai'] ?? '') == 'Khóa') ? 'selected' : ''?>>Khóa</option>
        </select>

        <label class="block mb-2 text-sm">Ngày tạo tài khoản trước</label>
        <input id="ngaytruoc-desktop" value="<?= ($filters['ngaytruoc'] ?? '') ?>" type="date" class="w-full border rounded-lg p-2 mb-4 focus:outline-none focus:ring focus:border-blue-400">

        <label class="block mb-2 text-sm">Kinh nghiệm</label>
        <input id="kinhnghiem-desktop" value="<?= ($filters['kinhnghiem'] ?? '') ?>" type="number" placeholder="Số năm" class="w-full border rounded-lg p-2 mb-4 focus:outline-none focus:ring focus:border-blue-400">

        <label class="block mb-2 text-sm">Uy tín</label>
        <input id="uytin-desktop" value="<?= ($filters['uytin'] ?? '') ?>" type="number" placeholder="Điểm uy tín" class="w-full border rounded-lg p-2 mb-4 focus:outline-none focus:ring focus:border-blue-400">

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

            <label class="block mb-2 text-sm">Hoạt động</label>
            <select id="hoatdong-mobile" class="w-full border rounded-lg p-2 mb-4 focus:outline-none focus:ring focus:border-blue-400">
                <option value="" <?= (($filters['hoatdong'] ?? '') == 'Tất cả') ? 'selected' : ''?>>Tất cả</option>
                <option value="Online" <?= (($filters['hoatdong'] ?? '') == 'Online') ? 'selected' : ''?>>Online</option>
                <option value="Offline" <?= (($filters['hoatdong'] ?? '') == 'Offline') ? 'selected' : ''?>>Offline</option>
            </select>

            <label class="block mb-2 text-sm">Trạng thái</label>
            <select id="trangthai-mobile" class="w-full border rounded-lg p-2 mb-4 focus:outline-none focus:ring focus:border-blue-400">
                <option value="" <?= (($filters['trangthai'] ?? '') == 'Tất cả') ? 'selected' : ''?>>Tất cả</option>
                <option value="Đang hoạt động" <?= (($filters['trangthai'] ?? '') == 'Đang hoạt động') ? 'selected' : ''?>>Đang hoạt động</option>
                <option value="Chưa kích hoạt" <?= (($filters['trangthai'] ?? '') == 'Chưa kích hoạt') ? 'selected' : ''?>>Chưa kích hoạt</option>
                <option value="Khóa" <?= (($filters['trangthai'] ?? '') == 'Khóa') ? 'selected' : ''?>>Khóa</option>
            </select>

            <label class="block mb-2 text-sm">Ngày tạo tài khoản trước</label>
            <input id="ngaytruoc-mobile" value="<?= ($filters['ngaytruoc'] ?? '') ?>" type="date" class="w-full border rounded-lg p-2 mb-4 focus:outline-none focus:ring focus:border-blue-400">

             <label class="block mb-2 text-sm">Kinh nghiệm</label>
            <input id="kinhnghiem-desktop" value="<?= ($filters['kinhnghiem'] ?? '') ?>" type="number" placeholder="Số năm" class="w-full border rounded-lg p-2 mb-4 focus:outline-none focus:ring focus:border-blue-400">

            <label class="block mb-2 text-sm">Uy tín</label>
            <input id="uytin-desktop" value="<?= ($filters['uytin'] ?? '') ?>" type="number" placeholder="Điểm uy tín" class="w-full border rounded-lg p-2 mb-4 focus:outline-none focus:ring focus:border-blue-400">

            <div class="flex gap-3 mt-4">
                <!-- Nút áp dụng -->
                <button id="btnloc-mobile" class="flex-1 bg-blue-600 text-white py-2 rounded-lg hover:bg-blue-700 transition">Áp dụng</button>
                <!-- Nút hủy -->
                <button id="btnhuy-mobile" class="flex-1 bg-gray-300 text-gray-800 py-2 rounded-lg hover:bg-gray-400 transition">Hủy</button>
            </div>
        </div>
    </div>

    <!-- Nội dung -->
    <div class="flex-1">
        <!-- Header + mobile filter -->
        <div class="flex justify-between items-center mb-6">
            <h1 class="flex items-center text-2xl font-bold text-gray-600">
                <img src="../../public/assets/anhht/0/user.gif" alt="Users" style="width: 50px; height: 50px; margin-right: 10px;">
                Quản lý môi giới
            </h1>
            <div class="flex gap-2">
                <button @click="openFilter=true" class="md:hidden mr-4 bg-gray-200 px-3 py-2 rounded-lg shadow hover:bg-gray-300">
                    <i class="fas fa-filter"></i>
                </button>
            </div>
        </div>
        
        <!-- Grid user card -->
        <div class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
            <?php if (empty($filters) && empty($mangtkmoigioi)): ?>
                <?php foreach($moigioi as $mg): ?>
                    <div class="bg-white shadow rounded-xl overflow-hidden hover:shadow-lg transition flex flex-col relative">
                        <div class="p-4 flex flex-col items-center">
                            <img src="../../public/assets/anhht/0/<?= $mg['avt'] ?>" class="w-20 h-20 rounded-full object-cover border-2 border-gray-200">
                            <h2 class="mt-2 font-semibold text-gray-800 text-center"><?= $mg['ho_ten'] ?></h2>
                            <p class="text-gray-500 text-sm text-center"><?= $mg['email'] ?></p>
                            <p class="text-gray-500 text-sm text-center"><?= $mg['so_dt'] ?></p>
                            <div class="flex items-center gap-2">
                                <span class="mt-2 px-2 py-1 rounded-full text-xs font-semibold <?= $mg['hoat_dong']=='Online'?'bg-green-100 text-green-700':'bg-gray-200 text-gray-700' ?>"><?= ucfirst($mg['hoat_dong']) ?></span>
                                <span class="mt-1 px-2 py-1 rounded-full text-xs font-semibold <?= $mg['vai_tro']=='Admin'?'bg-red-100 text-red-700':($mg['vai_tro']=='Moderator'?'bg-blue-100 text-blue-700':'bg-yellow-100 text-yellow-700') ?>"><?= $mg['vai_tro'] ?></span>
                            </div>
                            <!-- Năm kinh nghiệm + Điểm đánh giá -->
                            <div class="mt-2 text-sm text-gray-600">
                                <p><strong>Kinh nghiệm:</strong> <?= $mg['kinh_nghiem'] ?? 0 ?> năm</p>
                                <p><strong>Điểm uy tín:</strong> ⭐ <?= $mg['diem_quy_tinh'] ?? 'Chưa có' ?></p>
                            </div>
                            <p class="mt-2 text-sm text-gray-600">Trực thuộc: <?= $mg['cty'] ?></p>
                            <p class="mt-1 text-xs text-gray-400">Ngày tạo: <?= date("d/m/Y",strtotime($mg['ngay_tao'])) ?></p>
                        </div>
                        <!-- Nút hành động -->
                        <div class="flex justify-around border-t p-2 mt-auto">
                            <a href="#" class="text-blue-600 hover:text-blue-800" title="Sửa"><i class="fas fa-edit"></i></a>
                            <a href="#" class="text-red-600 hover:text-red-800" title="Xóa"><i class="fas fa-trash-alt"></i></a>
                            <a href="#" class="text-purple-600 hover:text-purple-800" title="Đổi mật khẩu"><i class="fas fa-key"></i></a>
                            <a href="chitiet.php?id=<?= $m['id'] ?>" class="text-green-600 hover:text-green-800" title="Xem chi tiết"><i class="fas fa-eye"></i></a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php elseif (!empty($filters)): ?> 
                <?php foreach($moigioi as $mg): ?>
                    <?php
                        $match = true;
                        if (isset($filters['hoatdong']) && $filters['hoatdong'] !== $mg['hoat_dong']) $match = false;
                        if (isset($filters['trangthai']) && $filters['trangthai'] !== $mg['trang_thai']) $match = false;
                        if (isset($filters['ngaytruoc']) && $mg['ngay_tao'] > $filters['ngaytruoc']) $match = false;
                        if (isset($filters['kinhnghiem']) && $mg['kinh_nghiem'] != $filters['kinhnghiem']) $match = false;
                        if (isset($filters['uytin']) && $mg['uy_tin'] != $filters['uytin']) $match = false;
                    ?>
                    <?php if ($match): ?>
                        <div class="bg-white shadow rounded-xl overflow-hidden hover:shadow-lg transition flex flex-col relative">
                            <div class="p-4 flex flex-col items-center">
                                <img src="../../public/assets/anhht/0/<?= $mg['avt'] ?>" alt="<?= $mg['ho_ten'] ?>" class="w-20 h-20 rounded-full object-cover border-2 border-gray-200">
                                <h2 class="mt-2 font-semibold text-gray-800 text-center"><?= $mg['ho_ten'] ?></h2>
                                <p class="text-gray-500 text-sm text-center"><?= $mg['email'] ?></p>
                                <p class="text-gray-500 text-sm text-center"><?= $mg['so_dt'] ?></p>
                                <div class="flex items-center gap-2">
                                    <span class="mt-2 px-2 py-1 rounded-full text-xs font-semibold <?= $mg['hoat_dong']=='online'?'bg-green-100 text-green-700':'bg-gray-200 text-gray-700' ?>"><?= ucfirst($mg['hoat_dong']) ?></span>
                                    <span class="mt-1 px-2 py-1 rounded-full text-xs font-semibold <?= $mg['vai_tro']=='Admin'?'bg-red-100 text-red-700':($mg['vai_tro']=='Moderator'?'bg-blue-100 text-blue-700':'bg-yellow-100 text-yellow-700') ?>"><?= $mg['vai_tro'] ?></span>
                                </div>
                                <!-- Năm kinh nghiệm + Điểm đánh giá -->
                                <div class="mt-2 text-sm text-gray-600">
                                    <p><strong>Kinh nghiệm:</strong> <?= $mg['kinh_nghiem'] ?? 0 ?> năm</p>
                                    <p><strong>Điểm uy tín:</strong> ⭐ <?= $mg['diem_quy_tinh'] ?? 'Chưa có' ?></p>
                                </div>
                                <p class="mt-2 text-sm text-gray-600">Trực thuộc: <?= $mg['cty'] ?></p>
                                <p class="mt-1 text-xs text-gray-400">Ngày tạo: <?= date("d/m/Y",strtotime($mg['ngay_tao'])) ?></p>
                            </div>
                             <!-- Nút hành động -->
                            <div class="flex justify-around border-t p-2 mt-auto">
                                <a href="#" class="text-blue-600 hover:text-blue-800" title="Sửa"><i class="fas fa-edit"></i></a>
                                <a href="#" class="text-red-600 hover:text-red-800" title="Xóa"><i class="fas fa-trash-alt"></i></a>
                                <a href="#" class="text-purple-600 hover:text-purple-800" title="Đổi mật khẩu"><i class="fas fa-key"></i></a>
                                <a href="chitiet.php?id=<?= $m['id'] ?>" class="text-green-600 hover:text-green-800" title="Xem chi tiết"><i class="fas fa-eye"></i></a>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            <?php elseif (!empty($mangtkmoigioi)): ?>
                <?php foreach ($mangtkmoigioi as $m): ?>
                    <div class="bg-white shadow rounded-xl overflow-hidden hover:shadow-lg transition flex flex-col relative">
                        <div class="p-4 flex flex-col items-center">
                            <img src="../../public/assets/anhht/0/<?= $m['avt'] ?>" alt="<?= $m['ho_ten'] ?>" class="w-20 h-20 rounded-full object-cover border-2 border-gray-200">
                            <h2 class="mt-2 font-semibold text-gray-800 text-center"><?= $m['ho_ten'] ?></h2>
                            <p class="text-gray-500 text-sm text-center"><?= $m['email'] ?></p>
                            <p class="text-gray-500 text-sm text-center"><?= $m['so_dt'] ?></p>
                            <div class="flex items-center gap-2">
                                <span class="mt-2 px-2 py-1 rounded-full text-xs font-semibold <?= $m['hoat_dong']=='online'?'bg-green-100 text-green-700':'bg-gray-200 text-gray-700' ?>"><?= ucfirst($m['hoat_dong']) ?></span>
                                <span class="mt-1 px-2 py-1 rounded-full text-xs font-semibold <?= $m['vai_tro']=='Admin'?'bg-red-100 text-red-700':($m['vai_tro']=='Moderator'?'bg-blue-100 text-blue-700':'bg-yellow-100 text-yellow-700') ?>"><?= $m['vai_tro'] ?></span>
                            </div>
                            <!-- Năm kinh nghiệm + Điểm đánh giá -->
                            <div class="mt-2 text-sm text-gray-600">
                                <p><strong>Kinh nghiệm:</strong> <?= $m['kinh_nghiem'] ?? 0 ?> năm</p>
                                <p><strong>Điểm uy tín:</strong> ⭐ <?= $m['diem_quy_tinh'] ?? 'Chưa có' ?></p>
                            </div>
                            <p class="mt-2 text-sm text-gray-600">Trực thuộc: <?= $m['cty'] ?></p>
                            <p class="mt-1 text-xs text-gray-400">Ngày tạo: <?= date("d/m/Y",strtotime($m['ngay_tao'])) ?></p>
                        </div>
                        <!-- Nút hành động -->
                        <div class="flex justify-around border-t p-2 mt-auto">
                            <a href="#" class="text-blue-600 hover:text-blue-800" title="Sửa"><i class="fas fa-edit"></i></a>
                            <a href="#" class="text-red-600 hover:text-red-800" title="Xóa"><i class="fas fa-trash-alt"></i></a>
                            <a href="#" class="text-purple-600 hover:text-purple-800" title="Đổi mật khẩu"><i class="fas fa-key"></i></a>
                            <a href="chitiet.php?id=<?= $m['id'] ?>" class="text-green-600 hover:text-green-800" title="Xem chi tiết"><i class="fas fa-eye"></i></a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    function apdungloc(prefix) {
        const keys = ["hoatdong", "uytin", "trangthai", "ngaytruoc", "kinhnghiem"];
        let filters = {};

        keys.forEach(key => {
            const el = document.getElementById(key + "-" + prefix);
            if (el && el.value.trim() !== "") {
                filters[key] = el.value.trim();
            }
        });

        const boloc = encodeURIComponent(JSON.stringify(filters));
        window.location.href = "trangchu.php?page=moigioi&boloc=" + boloc;
    }

    document.getElementById("btnloc-desktop").addEventListener("click", () => apdungloc("desktop"));
    document.getElementById("btnloc-mobile").addEventListener("click", () => apdungloc("mobile"));

    function huyloc(prefix) {
        window.location.href = "trangchu.php?page=moigioi";
    }

    document.getElementById("btnhuy-desktop").addEventListener("click", () => huyloc("desktop"));
    document.getElementById("btnhuy-mobile").addEventListener("click", () => huyloc("mobile"));

</script>

</body>
</html>
