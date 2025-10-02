<?php
    require_once "../../../config/database.php";
    $pdo = ketnoicsdl();

    $page = $_GET['page'] ?? '';
    $search = $_GET['search'] ?? '';
    $filters = [];

    if (isset($_GET['boloc'])) {
        $filters = json_decode($_GET['boloc'], true);
    }

    $sql = "
        SELECT 
            i.ho_ten,
            i.gioi_tinh,
            i.dia_chi,
            nd.avt,
            i.ngay_sinh,
            nd.id,
            nd.ten_dang_nhap,
            nd.email,
            nd.so_dt,
            nd.trang_thai,
            nd.hoat_dong,
            nd.ngay_tao,
            COUNT(gd.id) AS so_don,
            ARRAY_AGG(q.vai_tro) AS danh_sach_quyen
        FROM info_nguoi_dung i
        JOIN nguoi_dung nd ON i.id_nguoi_dung = nd.id
        LEFT JOIN giao_dich gd ON nd.id = gd.id_nguoi_dung AND gd.trang_thai = 'hoanthanh'
        LEFT JOIN phan_quyen pq ON nd.id = pq.id_nguoi_dung
        LEFT JOIN quyen q ON pq.id_quyen = q.id
    ";

    $params = [];
    if (!empty($search)) {
        $sql .= " WHERE (i.ho_ten ILIKE :search OR i.dia_chi ILIKE :search)";
        $params[':search'] = "%" . $search . "%";
    }

    $sql .= "
        GROUP BY 
            i.ho_ten, i.gioi_tinh, i.dia_chi, nd.avt, i.ngay_sinh,
            nd.id, nd.ten_dang_nhap, nd.email, nd.so_dt, nd.trang_thai, nd.hoat_dong, nd.ngay_tao
        ORDER BY so_don DESC
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $roleColors = [
        'quantri' => 'bg-red-100 text-red-700',
        'moigioi' => 'bg-blue-100 text-blue-700',
        'khachhang' => 'bg-yellow-100 text-yellow-700'
    ];

    $labelvaitro = [
        'quantri' => 'Quản trị',
        'moigioi' => 'Môi giới',
        'khachhang' => 'Khách hàng'
    ];
    $labeltrangthai = [
        'danghoatdong' => 'Đang hoạt động',
        'chuakichhoat' => 'Chưa kích hoạt',
        'khoa'         => 'Đã khóa'
    ];
?>

<!DOCTYPE html>
<html lang="vi" x-data="{ openFilter:false }">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý khách hàng</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/alpinejs" defer></script>
    <link rel="stylesheet" href="../../../public/assets/fontawesome/css/all.min.css">
    <style>
        .scrollbar-hide::-webkit-scrollbar {
            display: none;
        }

        .scrollbar-hide {
            scrollbar-width: none;
            -ms-overflow-style: none;
        }
        [x-cloak] { display: none !important; }
        .option.active {
            background-color: #2563eb;
            color: white;
            border-color: #2563eb;
        }
    </style>
</head>
<body>

<div class="max-w-7xl mx-auto mt-4 flex gap-6">

    <!-- Bộ lọc Desktop -->
    <div class="hidden md:block w-64 bg-white shadow rounded-xl p-4 h-fit">
        <h2 class="flex items-center text-lg font-semibold mb-4"> 
            <img src="../../../public/assets/anhht/0/filter.gif" class="w-10 h-10 mr-2"> Bộ lọc
        </h2>

        <label class="block mb-2 text-sm">Hoạt động</label>
        <select id="hoatdong-desktop" class="w-full border rounded-lg p-2 mb-4 outline-none focus:ring focus:border-blue-400 cursor-pointer">
            <option value="" <?= (($filters['hoatdong'] ?? '') == 'tatca') ? 'selected' : ''?>>Tất cả</option>
            <option value="online" <?= (($filters['hoatdong'] ?? '') == 'online') ? 'selected' : ''?>>Online</option>
            <option value="offline" <?= (($filters['hoatdong'] ?? '') == 'offline') ? 'selected' : ''?>>Offline</option>
        </select>

        <label class="block mb-2 text-sm">Trạng thái</label>
        <select id="trangthai-desktop" class="w-full border rounded-lg p-2 mb-4 outline-none focus:ring focus:border-blue-400 cursor-pointer">
            <option value="" <?= (($filters['trangthai'] ?? '') == 'tatca') ? 'selected' : ''?>>Tất cả</option>
            <option value="danghoatdong" <?= (($filters['trangthai'] ?? '') == 'danghoatdong') ? 'selected' : ''?>>Đang hoạt động</option>
            <option value="chuakichhoat" <?= (($filters['trangthai'] ?? '') == 'chuakichhoat') ? 'selected' : ''?>>Chưa kích hoạt</option>
            <option value="khoa" <?= (($filters['trangthai'] ?? '') == 'khoa') ? 'selected' : ''?>>Khóa</option>
        </select>

        <label class="block mb-2 text-sm">Ngày tạo tài khoản trước</label>
        <input id="ngaytruoc-desktop" value="<?= ($filters['ngaytruoc'] ?? '') ?>" type="date" class="w-full border rounded-lg p-2 mb-4 outline-none focus:ring focus:border-blue-400 cursor-pointer">

        <label class="block mb-2 text-sm">Số đơn lớn hơn</label>
        <input id="sodon-desktop" value="<?= ($filters['sodon'] ?? '') ?>" type="number" placeholder="Số đơn tối thiểu" class="w-full border rounded-lg p-2 mb-4 outline-none focus:ring focus:border-blue-400 cursor-pointer">

        <div class="flex gap-3 mt-4 cursor-pointer">
            <button id="btnloc-desktop" class="flex-1 bg-blue-600 text-white py-2 rounded-lg hover:bg-blue-700 transition">Áp dụng</button>
            <button id="btnhuy-desktop" class="flex-1 bg-gray-300 text-gray-800 py-2 rounded-lg hover:bg-gray-400 transition">Hủy</button>
        </div>
    </div>

    <!-- Bộ lọc Mobile -->
    <div x-show="openFilter" class="fixed inset-0 bg-black bg-opacity-50 flex justify-start z-50 md:hidden">
        <div class="bg-white w-64 h-full p-4 shadow-lg overflow-y-auto" @click.away="openFilter=false">

            <div class="flex justify-between items-center mb-4">
                <h2 class="flex items-center text-lg font-semibold">
                    <img src="../../../public/assets/anhht/0/filter.gif" style="width: 40px; height: 40px; margin-right: 10px;"> Bộ lọc
                </h2>
                <button @click="openFilter=false" class="text-gray-600 hover:text-gray-800"><i class="fas fa-times"></i></button>
            </div>

            <label class="block mb-2 text-sm">Hoạt động</label>
            <select id="hoatdong-mobile" class="w-full border rounded-lg p-2 mb-4 outline-none focus:ring focus:border-blue-400">
                <option value="" <?= (($filters['hoatdong'] ?? '') == 'tatca') ? 'selected' : ''?>>Tất cả</option>
                <option value="online" <?= (($filters['hoatdong'] ?? '') == 'online') ? 'selected' : ''?>>Online</option>
                <option value="offline" <?= (($filters['hoatdong'] ?? '') == 'offline') ? 'selected' : ''?>>Offline</option>
            </select>

            <label class="block mb-2 text-sm">Trạng thái</label>
            <select id="trangthai-mobile" class="w-full border rounded-lg p-2 mb-4 outline-none focus:ring focus:border-blue-400">
                <option value="" <?= (($filters['trangthai'] ?? '') == 'tatca') ? 'selected' : ''?>>Tất cả</option>
                <option value="danghoatdong" <?= (($filters['trangthai'] ?? '') == 'danghoatdong') ? 'selected' : ''?>>Đang hoạt động</option>
                <option value="chuakichhoat" <?= (($filters['trangthai'] ?? '') == 'chuakichhoat') ? 'selected' : ''?>>Chưa kích hoạt</option>
                <option value="khoa" <?= (($filters['trangthai'] ?? '') == 'khoa') ? 'selected' : ''?>>Khóa</option>
            </select>

            <label class="block mb-2 text-sm">Ngày tạo tài khoản trước</label>
            <input id="ngaytruoc-mobile" value="<?= ($filters['ngaytruoc'] ?? '') ?>" type="date" class="w-full border rounded-lg p-2 mb-4 outline-none focus:ring focus:border-blue-400">

            <label class="block mb-2 text-sm">Số đơn lớn hơn</label>
            <input id="sodon-mobile" value="<?= ($filters['sodon'] ?? '') ?>" type="number" placeholder="Số đơn tối thiểu" class="w-full border rounded-lg p-2 mb-4 outline-none focus:ring focus:border-blue-400">

            <div class="flex gap-3 mt-4">
                <button id="btnloc-mobile" class="flex-1 bg-blue-600 text-white py-2 rounded-lg hover:bg-blue-700 transition">Áp dụng</button>
                <button id="btnhuy-mobile" class="flex-1 bg-gray-300 text-gray-800 py-2 rounded-lg hover:bg-gray-400 transition">Hủy</button>
            </div>

        </div>
    </div>
    
    <!-- Hàm hiển thị thẻ người dùng -->
    <?php function theNguoiDung($u, $roleColors, $labelvaitro, $labeltrangthai) { ?>
        <div class="bg-blue-50 border border-blue-200 rounded-xl overflow-hidden transition flex flex-col relative cursor-pointer">
            <div class="p-4 flex flex-col items-center">
                <img src="../../../storage/pictures/avt/<?= $u['avt'] ?>" class="w-20 h-20 rounded-full object-cover border-2 border-gray-200">
                <h2 class="mt-2 font-semibold text-gray-800 text-center"><?= $u['ho_ten'] ?></h2>
                <p class="text-gray-500 text-sm text-center"><?= $u['email'] ?></p>
                <p class="text-gray-500 text-sm text-center"><?= $u['so_dt'] ?></p>
                <span class="mt-2 px-2 py-1 rounded-full text-xs font-semibold <?= $u['hoat_dong']=='online'?'bg-green-100 text-green-700':'bg-gray-200 text-gray-700' ?>"><?= ucfirst($u['hoat_dong']) ?></span>
                <?php
                    $chuoiQuyen = $u['danh_sach_quyen']; 
                    $dsQuyenArray = explode(',', trim($chuoiQuyen, '{}'));  
                ?>
                <div class="flex flex-wrap gap-1">
                    <?php foreach ($dsQuyenArray as $vai_tro): ?>
                        <span class="mt-1 px-2 py-1 rounded-full text-xs font-semibold <?= $roleColors[$vai_tro] ?? 'bg-gray-100 text-gray-700' ?>">
                            <?= $labelvaitro[$vai_tro] ?? $vai_tro ?>
                        </span>
                    <?php endforeach; ?>
                </div>
                <span class="mt-1 px-2 py-1 rounded-full text-xs font-semibold 
                    <?= $u['trang_thai']=='danghoatdong' ? 'bg-green-50 text-green-600' : ($u['trang_thai']=='chuakichhoat' ? 'bg-yellow-50 text-yellow-600' : 'bg-red-50 text-red-600') ?>">
                    <?= $labeltrangthai[$u['trang_thai']] ?>
                </span>
                <p class="mt-2 text-sm text-gray-600">Đã đặt: <?= $u['so_don'] ?> đơn</p>
                <p class="mt-1 text-xs text-gray-400">Ngày tạo: <?= date("d/m/Y",strtotime($u['ngay_tao'])) ?></p>
            </div>
            <div x-data="{ openForm: false, openOption: false}" class="relative">

                <!-- Nút hành động -->
                <div class="flex justify-around border-t p-2 mt-auto">
                    <a href="javascript:void(0)" @click="openForm = true" class="text-blue-600 hover:text-blue-800"><i class="fas fa-edit"></i></a> 
                    <a href="javascript:void(0)" @click="openOption = true" class="text-red-600 hover:text-red-800"><i class="fas fa-key"></i></a>
                    <a href="trangchu.php?page=ct_nguoidung&id=<?= $u['id'] ?>" class="text-green-600 hover:text-green-800"><i class="fas fa-eye"></i></a>
                </div>

                <!-- Popup form -->
                <div x-show="openForm" x-cloak class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" x-transition>
                    <div class="bg-white rounded-xl shadow-lg p-6 w-96">
                        <h2 class="text-lg font-semibold mb-4">Thông báo</h2>
                        <input type="hidden" id="idnguoidung" value="<?= htmlspecialchars($u['id']) ?>">

                        <label class="block text-sm font-medium mb-1">Loại thông báo</label>
                        <select id="loaithongbao" class="w-full border rounded-lg p-2 mb-3 outline-none focus:ring focus:border-blue-400">
                            <option value="capnhatthongtin">Yêu cầu cập nhật thông tin cá nhân</option>
                            <option value="doimatkhau">Yêu cầu đổi mật khẩu</option>
                            <option value="khoataikhoan">Yêu cầu khóa tài khoản</option>
                            <option value="xoataikhoan">Yêu cầu xóa tài khoản</option>
                        </select>

                        <label class="block text-sm font-medium mb-1">Tiêu đề</label>
                        <input type="text" id="tieude"
                            class="w-full outline-none border rounded-lg p-2 mb-3 focus:ring focus:border-blue-400" 
                            placeholder="Nhập tiêu đề...">

                        <label class="block text-sm font-medium mb-1">Nội dung</label>
                        <textarea id="noidung"
                                class="w-full border rounded-lg p-2 mb-3 outline-none focus:ring focus:border-blue-400" 
                                rows="3" placeholder="Nhập nội dung..."></textarea>

                        <div class="flex justify-end space-x-2">
                            <button @click="openForm = false" class="px-4 py-2 rounded-lg border border-gray-300 hover:bg-gray-100">Hủy</button>
                            <button id="btnguithongbao" class="px-4 py-2 rounded-lg bg-blue-600 text-white hover:bg-blue-700">Gửi</button>
                        </div>
                    </div>
                </div>

                <!-- Popup Lựa chọn -->
                <div x-show="openOption" x-cloak class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" x-transition>
                    <div class="bg-white rounded-xl shadow-lg p-6 w-80">
                        <h2 class="text-lg font-semibold mb-4 text-blue-600">Lựa chọn</h2>
                        <input type="hidden" id="trangthai-value" name="trangthai" value="danghoatdong">

                        <div class="w-full">
                            <label class="block mb-2 text-gray-700">Trạng thái</label>
                            <div id="trangthai" 
                                class="w-full border rounded-lg overflow-hidden divide-y divide-gray-200">
                                <div data-value="danghoatdong" class="option px-4 py-2 cursor-pointer hover:bg-blue-50">
                                    Kích hoạt
                                </div>
                                <div data-value="chuakichhoat" class="option px-4 py-2 cursor-pointer hover:bg-blue-50">
                                    Tạm ngừng
                                </div>
                                <div data-value="khoa" class="option px-4 py-2 cursor-pointer hover:bg-blue-50">
                                    Khóa
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-end space-x-2 mt-2">
                            <button @click="openOption = false" class="px-4 py-2 rounded-lg border border-gray-300 hover:bg-gray-100">Hủy</button>
                            <button onclick="capnhattrangthai('<?= $u['id'] ?>')" class="px-4 py-2 rounded-lg bg-blue-600 text-white hover:bg-blue-700">Lưu</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php } ?>

    <!-- Nội dung -->
    <div class="flex-1">

        <div class="flex justify-between items-center mb-6">
            <h1 class="flex items-center text-2xl font-bold text-gray-600">
                <img src="../../../public/assets/anhht/0/user.gif" style="width: 50px; height: 50px; margin-right: 10px;"> Quản lý Khách hàng
            </h1>
            <div class="flex gap-2">
                <button @click="openFilter=true" class="md:hidden mr-4 bg-gray-200 px-3 py-2 rounded-lg shadow hover:bg-gray-300">
                    <i class="fas fa-filter"></i>
                </button>
            </div>
        </div>
        
        <!-- Thẻ người dùng -->
        <div class="h-[500px] overflow-y-scroll p-2 scrollbar-hide">
            <div class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                <?php if (empty($filters) && empty($mangtkkhachhang)): ?>
                    <?php foreach($users as $u): ?>
                        <?php theNguoiDung($u, $roleColors, $labelvaitro, $labeltrangthai); ?>
                    <?php endforeach; ?>
                <?php elseif (!empty($filters)): ?> 
                    <?php foreach($users as $u): ?>
                        <?php
                            $match = true;
                            if (isset($filters['hoatdong']) && $filters['hoatdong'] !== $u['hoat_dong']) $match = false;
                            if (isset($filters['trangthai']) && $filters['trangthai'] !== $u['trang_thai']) $match = false;
                            if (isset($filters['ngaytruoc']) && $u['ngay_tao'] > $filters['ngaytruoc']) $match = false;
                            if (isset($filters['sodon']) && $u['so_don'] > $filters['sodon']) $match = false;
                        ?>
                        <?php if ($match): ?>
                            <?php theNguoiDung($u, $roleColors, $labelvaitro, $labeltrangthai); ?>
                        <?php endif; ?>
                    <?php endforeach; ?>
                <?php elseif (!empty($search)): ?>
                    <?php foreach ($users as $u): ?>
                         <?php theNguoiDung($u, $roleColors, $labelvaitro, $labeltrangthai); ?>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
    function apdungloc(prefix) {
        const keys = ["hoatdong", "trangthai", "ngaytruoc", "sodon"];
        let filters = {};

        keys.forEach(key => {
            const el = document.getElementById(key + "-" + prefix);
            if (el && el.value.trim() !== "") {
                filters[key] = el.value.trim();
            }
        });

        const boloc = encodeURIComponent(JSON.stringify(filters));
        window.location.href = "trangchu.php?page=ds_nguoidung&boloc=" + boloc;
    }

    document.getElementById("btnloc-desktop").addEventListener("click", () => apdungloc("desktop"));
    document.getElementById("btnloc-mobile").addEventListener("click", () => apdungloc("mobile"));

    function huyloc(prefix) {
        window.location.href = "trangchu.php?page=ds_nguoidung";
    }

    document.getElementById("btnhuy-desktop").addEventListener("click", () => huyloc("desktop"));
    document.getElementById("btnhuy-mobile").addEventListener("click", () => huyloc("mobile"));

    document.querySelectorAll('#trangthai .option').forEach(opt => {
        opt.addEventListener('click', function() {
            document.querySelectorAll('#trangthai .option').forEach(o => o.classList.remove('active'));
            this.classList.add('active');
            document.getElementById('trangthai-value').value = this.dataset.value;
        });
    });

    async function capnhattrangthai(id) {
        let trangthai = document.getElementById("trangthai-value").value;
        fetch('../../models/capnhat_tt_kh.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id, trangthai })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Đã xảy ra lỗi khi đăng nhập.');
        });
    }

    document.getElementById("btnguithongbao").addEventListener("click", function () {
        const idnguoidung = document.getElementById("idnguoidung").value;
        const loaithongbao = document.getElementById("loaithongbao").value;
        const tieude       = document.getElementById("tieude").value.trim();
        const noidung      = document.getElementById("noidung").value.trim();

        if (!tieude || !noidung) {
            alert("Vui lòng nhập đầy đủ tiêu đề và nội dung!");
            return;
        }

        fetch("../../models/gui_tb_nd.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({
                loai: loaithongbao,
                tieude: tieude,
                noidung: noidung,
                id: idnguoidung
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                window.location.reload();
            } else {
                alert("Có lỗi xảy ra: " + data.message);
            }
        })
        .catch(err => console.error(err));
    });
</script>
</body>
</html>
