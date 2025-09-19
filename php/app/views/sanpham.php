<?php
    require_once "../../config/database.php";
    $pdo = ketnoicsdl();

    $sql = "
        SELECT 
            b.id,
            b.tieu_de,
            b.mo_ta,
            b.gia,
            b.dien_tich,
            b.dia_chi,
            b.loai,
            b.khu_vuc,
            b.ngay_dang,
            b.trang_thai,
            d.diem as rating
        FROM public.bat_dong_san b
        LEFT JOIN danh_gia_bds d ON d.id_bds = b.id
        ORDER BY b.ngay_dang DESC
        ";
    $stmt = $pdo->query($sql);
    $products = $stmt->fetchAll();

    $filters = [];
    if (isset($_GET['boloc'])) {
        $filters = json_decode($_GET['boloc'], true);
    }

    $search = $_GET['search'] ?? '';
    $page = $_GET['page'] ?? '';

    $sql = "
        SELECT 
            b.id,
            b.tieu_de,
            b.mo_ta,
            b.gia,
            b.dien_tich,
            b.dia_chi,
            b.loai,
            b.khu_vuc,
            b.ngay_dang,
            b.trang_thai,
            d.diem as rating,
            ts_rank_cd(
                to_tsvector('simple', b.tieu_de || :search || b.mo_ta), 
                plainto_tsquery('simple', :search)
            ) AS rank
        FROM public.bat_dong_san b
        LEFT JOIN danh_gia_bds d ON d.id_bds = b.id
        WHERE to_tsvector('simple', b.tieu_de || :search || b.mo_ta) @@ plainto_tsquery('simple', :search)
        ORDER BY rank DESC, b.ngay_dang DESC
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':search', $search, PDO::PARAM_STR);
    $stmt->execute();
    $mangtksanpham = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="vi" x-data="{ openFilter: false }">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Bất động sản</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/alpinejs" defer></script>
    <link rel="stylesheet" href="../../public/assets/fontawesome/css/all.min.css">
    <style>
        .scrollbar-hide::-webkit-scrollbar {
            display: none;
        }

        .scrollbar-hide {
            scrollbar-width: none;
            -ms-overflow-style: none;
        }
    </style>
</head>
<body>

<div class="max-w-7xl mx-auto p-6 flex gap-6">

    <!-- Bộ lọc (Desktop) -->
    <div class="hidden md:block w-64 bg-white shadow rounded-xl p-4 h-fit">
        <h2 class="flex items-center text-lg font-semibold">
            <img src="../../public/assets/anhht/0/filter.gif" alt="Quản lý sản phẩm" style="width: 40px; height: 40px; margin-right: 10px;">
            Bộ lọc
        </h2>

        <label class="block mb-2 text-sm">Loại BĐS</label>
        <select id="loai-desktop" class="w-full border rounded-lg p-2 mb-4 focus:outline-none">
            <option value="" <?= (($filters['loai'] ?? '') == 'Tất cả') ? 'selected' : ''?>>Tất cả</option>
            <option value="Căn hộ" <?= (($filters['loai'] ?? '') == 'Căn hộ') ? 'selected' : ''?>>Căn hộ</option>
            <option value="Nhà phố" <?= (($filters['loai'] ?? '') == 'Nhà phố') ? 'selected' : ''?>>Nhà phố</option>
            <option value="Đất nền" <?= (($filters['loai'] ?? '') == 'Đất nền') ? 'selected' : ''?>>Đất nền</option>
            <option value="Biệt thự" <?= (($filters['loai'] ?? '') == 'Biệt thự') ? 'selected' : ''?>>Biệt thự</option>
        </select>

        <label class="block mb-2 text-sm">Khu vực</label>
        <select id="khuvuc-desktop" class="w-full border rounded-lg p-2 mb-4 focus:outline-none">
            <option value="" <?= (($filters['khuvuc'] ?? '') == 'Tất cả') ? 'selected' : ''?>>Tất cả</option>
            <?php
            $lines = file("../../storage/documents/tinhthanh.txt", FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line):
                $selected = (($filters['khuvuc'] ?? '') == $line) ? 'selected' : '';
                echo "<option value=\"$line\" $selected>$line</option>";
            endforeach;
            ?>
        </select>

        <label class="block mb-2 text-sm">Tình trạng</label>
        <select id="trangthai-desktop" class="w-full border rounded-lg p-2 mb-4 focus:outline-none">
            <option value="" <?= (($filters['trangthai'] ?? '') == 'Tất cả') ? 'selected' : ''?>>Tất cả</option>
            <option value="Còn bán" <?= (($filters['trangthai'] ?? '') == 'Còn bán') ? 'selected' : ''?>>Còn bán</option>
            <option value="Đã bán" <?= (($filters['trangthai'] ?? '') == 'Đã bán') ? 'selected' : ''?>>Đã bán</option>
        </select>

        <label class="block mb-2 text-sm">Giá (VNĐ)</label>
        <div class="flex gap-2 mb-4">
            <input id="giatu-desktop" type="number" placeholder="Từ" class="w-1/2 border rounded-lg p-2 focus:outline-none">
            <input id="giaden-desktop" type="number" placeholder="Đến" class="w-1/2 border rounded-lg p-2 focus:outline-none">
        </div>

        <label class="block mb-2 text-sm">Diện tích (m²)</label>
        <div class="flex gap-2 mb-4">
            <input id="dientu-desktop" type="number" placeholder="Từ" class="w-1/2 border rounded-lg p-2 focus:outline-none">
            <input id="dienden-desktop" type="number" placeholder="Đến" class="w-1/2 border rounded-lg p-2 focus:outline-none">
        </div>

        <label class="block mb-2 text-sm">Đánh giá</label>
        <select id="rating-desktop" class="w-full border rounded-lg p-2 mb-4 focus:outline-none">
            <option value="">Tất cả</option>
            <option value="5" <?= (($filters['rating'] ?? '') == '5') ? 'selected' : '' ?>>⭐⭐⭐⭐⭐</option>
            <option value="4" <?= (($filters['rating'] ?? '') == '4') ? 'selected' : '' ?>>⭐⭐⭐⭐</option>
            <option value="3" <?= (($filters['rating'] ?? '') == '3') ? 'selected' : '' ?>>⭐⭐⭐</option>
            <option value="2" <?= (($filters['rating'] ?? '') == '2') ? 'selected' : '' ?>>⭐⭐</option>
            <option value="1" <?= (($filters['rating'] ?? '') == '1') ? 'selected' : '' ?>>⭐</option>
        </select>

        <div class="flex gap-3 mt-4">
            <!-- Nút áp dụng -->
            <button id="btnloc-desktop" class="flex-1 bg-blue-600 text-white py-2 rounded-lg hover:bg-blue-700 transition">Áp dụng</button>
            <!-- Nút hủy -->
            <button id="btnhuy-desktop" class="flex-1 bg-gray-300 text-gray-800 py-2 rounded-lg hover:bg-gray-400 transition">Hủy</button>
        </div>
    </div>

    <!-- Bộ lọc (Mobile) -->
    <div x-show="openFilter" class="fixed inset-0 bg-black bg-opacity-50 flex justify-start z-50 md:hidden">
        <div class="bg-white w-64 h-full p-4 shadow-lg overflow-y-auto" @click.away="openFilter = false">
            <div class="flex justify-between items-center mb-4">
                <h2 class="flex items-center text-lg font-semibold">
                    <img src="../../public/assets/anhht/0/filter.gif" alt="Quản lý sản phẩm" style="width: 40px; height: 40px; margin-right: 10px;">
                    Bộ lọc
                </h2>
                <button @click="openFilter=false" class="text-gray-600 hover:text-gray-800"><i class="fas fa-times"></i></button>
            </div>
        
            <label class="block mb-2 text-sm">Loại BĐS</label>
            <select id="loai-mobile" class="w-full border rounded-lg p-2 mb-4 focus:outline-none">
                <option value="" <?= (($filters['loai'] ?? '') == 'Tất cả') ? 'selected' : ''?>>Tất cả</option>
                <option value="Căn hộ" <?= (($filters['loai'] ?? '') == 'Căn hộ') ? 'selected' : ''?>>Căn hộ</option>
                <option value="Nhà phố" <?= (($filters['loai'] ?? '') == 'Nhà phố') ? 'selected' : ''?>>Nhà phố</option>
                <option value="Đất nền" <?= (($filters['loai'] ?? '') == 'Đất nền') ? 'selected' : ''?>>Đất nền</option>
                <option value="Biệt thự" <?= (($filters['loai'] ?? '') == 'Biệt thự') ? 'selected' : ''?>>Biệt thự</option>
            </select>

            <label class="block mb-2 text-sm">Khu vực</label>
            <select id="khuvuc-mobile" class="w-full border rounded-lg p-2 mb-4 focus:outline-none">
                <option value="" <?= (($filters['khuvuc'] ?? '') == 'Tất cả') ? 'selected' : ''?>>Tất cả</option>
                <?php
                $lines = file("../../storage/documents/tinhthanh.txt", FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
                foreach ($lines as $line):
                    $selected = (($filters['khuvuc'] ?? '') == $line) ? 'selected' : '';
                    echo "<option value=\"$line\" $selected>$line</option>";
                endforeach;
                ?>
            </select>

            <label class="block mb-2 text-sm">Tình trạng</label>
            <select id="trangthai-mobile" class="w-full border rounded-lg p-2 mb-4 focus:outline-none">
                <option value="" <?= (($filters['trangthai'] ?? '') == 'Tất cả') ? 'selected' : ''?>>Tất cả</option>
                <option value="Còn bán" <?= (($filters['trangthai'] ?? '') == 'Còn bán') ? 'selected' : ''?>>Còn bán</option>
                <option value="Đã bán" <?= (($filters['trangthai'] ?? '') == 'Đã bán') ? 'selected' : ''?>>Đã bán</option>
            </select>

            <label class="block mb-2 text-sm">Giá (VNĐ)</label>
            <div class="flex gap-2 mb-4">
                <input id="giatu-mobile" type="number" placeholder="Từ" class="w-1/2 border rounded-lg p-2 focus:outline-none">
                <input id="giaden-mobile" type="number" placeholder="Đến" class="w-1/2 border rounded-lg p-2 focus:outline-none">
            </div>

            <label class="block mb-2 text-sm">Diện tích (m²)</label>
            <div class="flex gap-2 mb-4">
                <input id="dientu-mobile" type="number" placeholder="Từ" class="w-1/2 border rounded-lg p-2 focus:outline-none">
                <input id="dienden-mobile" type="number" placeholder="Đến" class="w-1/2 border rounded-lg p-2 focus:outline-none">
            </div>

            <label class="block mb-2 text-sm">Đánh giá</label>
            <select id="rating-mobile" class="w-full border rounded-lg p-2 mb-4 focus:outline-none">
                <option value="">Tất cả</option>
                <option value="5" <?= (($filters['rating'] ?? '') == '5') ? 'selected' : '' ?>>⭐⭐⭐⭐⭐</option>
                <option value="4" <?= (($filters['rating'] ?? '') == '4') ? 'selected' : '' ?>>⭐⭐⭐⭐</option>
                <option value="3" <?= (($filters['rating'] ?? '') == '3') ? 'selected' : '' ?>>⭐⭐⭐</option>
                <option value="2" <?= (($filters['rating'] ?? '') == '2') ? 'selected' : '' ?>>⭐⭐</option>
                <option value="1" <?= (($filters['rating'] ?? '') == '1') ? 'selected' : '' ?>>⭐</option>
            </select>

            <div class="flex gap-3 mt-4">
                <!-- Nút áp dụng -->
                <button id="btnloc-mobile" class="flex-1 bg-blue-600 text-white py-2 rounded-lg hover:bg-blue-700 transition">Áp dụng</button>
                <!-- Nút hủy -->
                <button id="btnhuy-mobile" class="flex-1 bg-gray-300 text-gray-800 py-2 rounded-lg hover:bg-gray-400 transition">Hủy</button>
            </div>
        </div>
    </div>

    <!-- Nội dung sản phẩm -->
    <div class="flex-1">
        <div class="flex justify-between items-center mb-6">
            <h1 class="flex items-center text-2xl font-bold text-gray-600">
                <i class="fas fa-building mr-2 text-red-500"></i> Quản lý Bất động sản
            </h1>
            <div class="flex gap-2">
                <button @click="openFilter = true" class="md:hidden bg-gray-200 px-3 py-2 rounded-lg shadow hover:bg-gray-300">
                    <i class="fas fa-filter"></i>
                </button>
            </div>
        </div>

        <!-- Grid -->
        <div class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 max-h-[600px] overflow-y-auto scrollbar-hide">
            <?php if (empty($filters) && empty($mangtksanpham)): ?>
                <?php foreach($products as $p): ?>
                    <div class="bg-white shadow rounded-xl overflow-hidden hover:shadow-lg transition flex flex-col">
                        <img src="https://picsum.photos/300?random=<?= $p['id'] ?>" class="w-full h-40 object-cover">
                        <div class="p-4 flex-1 flex flex-col">
                            <h2 class="text-sm font-semibold text-gray-800 line-clamp-2 min-h-[40px]"><?= $p['tieu_de'] ?></h2>
                            <p class="text-red-600 font-bold mt-2"><?= number_format($p['gia'], 0, ',', '.') ?> đ</p>
                            <p class="mt-1 text-xs text-gray-600">Diện tích: <?= $p['dien_tich'] ?> m²</p>
                            <p class="mt-1 text-xs text-gray-600"><?= $p['dia_chi'] ?></p>
                            <p class="mt-1 text-xs text-gray-600"><?= $p['loai'] ?> - <?= $p['khu_vuc'] ?></p>
                            <p class="mt-1 text-xs text-gray-600 font-semibold">Trạng thái: <?= $p['trang_thai'] ?></p>
                            <p class="mt-1 text-[10px] text-gray-400">Ngày đăng: <?= date("d/m/Y", strtotime($p['ngay_dang'])) ?></p>
                            <div class="flex items-center gap-1 text-yellow-400 mt-1">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <?php if ($i <= $p['rating']): ?>
                                        <i class="fas fa-star"></i>
                                    <?php else: ?>
                                        <i class="far fa-star"></i>
                                    <?php endif; ?>
                                <?php endfor; ?>
                                <span class="text-gray-500 text-xs ml-1">(<?= $p['rating'] ?>/5)</span>
                            </div>
                            <div class="flex justify-between items-center mt-auto pt-4">
                                <a href="#" class="text-blue-600 hover:text-blue-800 text-sm"><i class="fas fa-edit"></i> Sửa </a>
                                <a href="#" class="text-red-600 hover:text-red-800 text-sm"><i class="fas fa-trash-alt"></i> Xóa </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php elseif (!empty($filters)): ?>
                <?php foreach ($products as $p): ?>
                    <?php
                        $match = true;

                        if (isset($filters['loai']) && $filters['loai'] !== $p['loai']) $match = false;
                        if (isset($filters['khuvuc']) && $filters['khuvuc'] !== $p['khu_vuc']) $match = false;
                        if (isset($filters['trangthai']) && $filters['trangthai'] !== $p['trang_thai']) $match = false;
                        if (isset($filters['giatu']) && $p['gia'] < $filters['giatu']) $match = false;
                        if (isset($filters['giaden']) && $p['gia'] > $filters['giaden']) $match = false;
                        if (isset($filters['dttu']) && $p['dien_tich'] < $filters['dttu']) $match = false;
                        if (isset($filters['dtden']) && $p['dien_tich'] > $filters['dtden']) $match = false;
                        if (isset($filters['rating']) && $p['rating'] < $filters['rating']) $match = false;
                    ?>
                    <?php if ($match): ?>
                        <div class="bg-white shadow rounded-xl overflow-hidden hover:shadow-lg transition flex flex-col">
                            <img src="https://picsum.photos/300?random=<?= $p['id'] ?>" class="w-full h-40 object-cover">
                            <div class="p-4 flex-1 flex flex-col">
                                <h2 class="text-sm font-semibold text-gray-800 line-clamp-2 min-h-[40px]"><?= $p['tieu_de'] ?></h2>
                                <p class="text-red-600 font-bold mt-2"><?= number_format($p['gia'], 0, ',', '.') ?> đ</p>
                                <p class="mt-1 text-xs text-gray-600">Diện tích: <?= $p['dien_tich'] ?> m²</p>
                                <p class="mt-1 text-xs text-gray-600"><?= $p['dia_chi'] ?></p>
                                <p class="mt-1 text-xs text-gray-600"><?= $p['loai'] ?> - <?= $p['khu_vuc'] ?></p>
                                <p class="mt-1 text-[10px] text-gray-400">Ngày đăng: <?= date("d/m/Y", strtotime($p['ngay_dang'])) ?></p>
                                <div class="flex items-center gap-1 text-yellow-400 mt-1">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <?php if ($i <= $p['rating']): ?>
                                            <i class="fas fa-star"></i>
                                        <?php else: ?>
                                            <i class="far fa-star"></i>
                                        <?php endif; ?>
                                    <?php endfor; ?>
                                    <span class="text-gray-500 text-xs ml-1">(<?= $p['rating'] ?>/5)</span>
                                </div>
                                <div class="flex justify-between items-center mt-auto pt-4">
                                    <a href="#" class="text-blue-600 hover:text-blue-800 text-sm"><i class="fas fa-edit"></i> Sửa </a>
                                    <a href="#" class="text-red-600 hover:text-red-800 text-sm"><i class="fas fa-trash-alt"></i> Xóa </a>
                                </div>
                            </div>
                        </div>  
                    <?php endif; ?>
                <?php endforeach; ?>
            <?php elseif (!empty($mangtksanpham)): ?> 
                <?php foreach ($mangtksanpham as $m): ?>
                    <div class="bg-white shadow rounded-xl overflow-hidden hover:shadow-lg transition flex flex-col">
                        <img src="https://picsum.photos/300?random=<?= $m['id'] ?>" class="w-full h-40 object-cover">
                        <div class="p-4 flex-1 flex flex-col">
                            <h2 class="text-sm font-semibold text-gray-800 line-clamp-2 min-h-[40px]"><?= $m['tieu_de'] ?></h2>
                            <p class="text-red-600 font-bold mt-2"><?= number_format($m['gia'], 0, ',', '.') ?> đ</p>
                            <p class="mt-1 text-xs text-gray-600">Diện tích: <?= $m['dien_tich'] ?> m²</p>
                            <p class="mt-1 text-xs text-gray-600"><?= $m['dia_chi'] ?></p>
                            <p class="mt-1 text-xs text-gray-600"><?= $m['loai'] ?> - <?= $m['khu_vuc'] ?></p>
                            <p class="mt-1 text-[10px] text-gray-400">Ngày đăng: <?= date("d/m/Y", strtotime($m['ngay_dang'])) ?></p>
                            <div class="flex items-center gap-1 text-yellow-400 mt-1">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <?php if ($i <= $m['rating']): ?>
                                        <i class="fas fa-star"></i>
                                    <?php else: ?>
                                        <i class="far fa-star"></i>
                                    <?php endif; ?>
                                <?php endfor; ?>
                                <span class="text-gray-500 text-xs ml-1">(<?= $m['rating'] ?>/5)</span>
                            </div>
                            <div class="flex justify-between items-center mt-auto pt-4">
                                <a href="#" class="text-blue-600 hover:text-blue-800 text-sm"><i class="fas fa-edit"></i> Sửa </a>
                                <a href="#" class="text-red-600 hover:text-red-800 text-sm"><i class="fas fa-trash-alt"></i> Xóa </a>
                            </div>
                        </div>
                    </div> 
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    function apdungloc(prefix) {
        const keys = ["loai", "khuvuc", "trangthai", "giatu", "giaden", "dientu", "dienden", "rating"];
        let filters = {};

        keys.forEach(key => {
            const el = document.getElementById(key + "-" + prefix);
            if (el && el.value.trim() !== "") {
                filters[key] = el.value.trim();
            }
        });

        const boloc = encodeURIComponent(JSON.stringify(filters));
        window.location.href = "trangchu.php?page=sanpham&boloc=" + boloc;
    }

    document.getElementById("btnloc-desktop").addEventListener("click", () => apdungloc("desktop"));
    document.getElementById("btnloc-mobile").addEventListener("click", () => apdungloc("mobile"));

    function huyloc(prefix) {
        window.location.href = "trangchu.php?page=sanpham";
    }
    document.getElementById("btnhuy-desktop").addEventListener("click", () => huyloc("desktop"));
    document.getElementById("btnhuy-mobile").addEventListener("click", () => huyloc("mobile"));

</script>

</body>
</html>
