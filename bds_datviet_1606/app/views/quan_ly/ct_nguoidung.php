<?php
    require_once "../../../config/database.php";
    $pdo = ketnoicsdl();

    $id = $_GET['id'] ?? '';

    // Lấy thông tin môi giới
    $stmt = $pdo->prepare("
        SELECT 
    nd.id,
    nd.ten_dang_nhap,
    nd.email,
    nd.so_dt,
    nd.avt,
    nd.anh_bia,
    nd.trang_thai,
    nd.hoat_dong,
    nd.ngay_tao,
    info.ho_ten,
    info.gioi_tinh,
    info.mo_ta,
    -- Dùng DISTINCT để đảm bảo mỗi vai trò chỉ xuất hiện 1 lần (phòng trường hợp cấu hình sai)
    COALESCE(array_agg(DISTINCT q.vai_tro) FILTER (WHERE q.vai_tro IS NOT NULL), '{}') AS vai_tro,
    COALESCE(array_agg(DISTINCT pq.id_quyen) FILTER (WHERE pq.id_quyen IS NOT NULL), '{}') AS id_quyen
FROM nguoi_dung nd
LEFT JOIN info_nguoi_dung info ON nd.id = info.id_nguoi_dung
LEFT JOIN phan_quyen pq ON nd.id = pq.id_nguoi_dung
LEFT JOIN quyen q ON pq.id_quyen = q.id
-- KHÔNG CẦN DÒNG NÀY NỮA -- LEFT JOIN bat_dong_san bds ON nd.id = bds.id_nguoi_dung
WHERE nd.id = :id
-- SỬA LẠI GROUP BY, BỎ CÁC CỘT TỔNG HỢP
GROUP BY 
    nd.id, 
    info.ho_ten, 
    info.gioi_tinh, 
    info.mo_ta;
    ");

    $stmt->execute([':id' => $id]);
    $chitiet = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$chitiet) {
        echo "<p class='text-red-500'>❌ Không tìm thấy môi giới.</p>";
        exit;
    }

    // Map giới tính
    $gioiTinhMap = [
        'nam' => 'Nam',
        'nu' => 'Nữ',
        'khac' => 'Khác',
        'chuacapnhat' => 'Chưa cập nhật'
    ];
    $chitiet['gioi_tinh'] = $gioiTinhMap[$chitiet['gioi_tinh']] ?? $chitiet['gioi_tinh'];

    $vaitromap = [
        'moigioi' => 'Môi giới',
        'quantri' => 'Quản trị',
        'khachhang' => 'Khách hàng'
    ];

    $chitiet['vai_tro'] = $vaitromap[$chitiet['vai_tro']] ?? $chitiet['vai_tro'];

    $trangthaimap = [
        'danghoatdong' => 'Đang hoạt động',
        'chuakichhoat' => 'Chưa kích hoạt',
        'khoa' => 'Khóa'
    ];

    $chitiet['trang_thai'] = $trangthaimap[$chitiet['trang_thai']] ?? $chitiet['trang_thai'];

    $motamap = [
        'chuacapnhat' => 'Chưa cập nhật'
    ];

    $chitiet['mo_ta'] = $motamap[$chitiet['mo_ta']] ?? $chitiet['mo_ta'];

    // Lấy danh sách đánh giá
    $stmt2 = $pdo->prepare("
        SELECT 
        mg.id AS id_moi_gioi,
        info_mg.ho_ten AS ten_moi_gioi,
        kh.id AS id_khach_hang,
        kh.avt AS avt_khach_hang,
        info_kh.ho_ten AS ten_khach_hang,
        dg.diem,
        dg.binh_luan,
        dg.ngay_dg
    FROM danh_gia_mg dg
    JOIN nguoi_dung mg ON dg.id_moi_gioi = mg.id
    JOIN info_nguoi_dung info_mg ON mg.id = info_mg.id_nguoi_dung
    JOIN nguoi_dung kh ON dg.id_khach_hang = kh.id
    JOIN info_nguoi_dung info_kh ON kh.id = info_kh.id_nguoi_dung
    WHERE dg.id_moi_gioi = :id
    ORDER BY dg.ngay_dg DESC");

    $stmt2->execute([':id' => $id]);
    $danhgia = $stmt2->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Chi tiết môi giới</title>
  
  <link rel="stylesheet" href="../../../public/assets/fontawesome/css/all.min.css">
</head>
<body>

  <div class="max-w-4xl mx-auto mt-6 bg-white shadow-lg rounded-xl overflow-hidden">

    <!-- Ảnh bìa -->
    <div class="relative h-56">
      <img src="../../../storage/pictures/bia/<?= htmlspecialchars($chitiet['anh_bia']) ?>" alt="Ảnh bìa" class="w-full h-full object-cover">
        <!-- Nút camera cập nhật ảnh bìa --> 
        <form action="../../models/cn_anhbia.php" method="POST" enctype="multipart/form-data" class="absolute top-3 right-3"> 
            <label for="uploadBia" class="cursor-pointer"> <i class="fas fa-camera text-blue-500"></i> </label> 
            <input type="hidden" name="idnguoidung" value="<?= $chitiet['id'] ?>">
            <input type="file" id="uploadBia" name="bia" class="hidden" onchange="this.form.submit()"> 
        </form>
    </div>

    <!-- Avatar + Thông tin cơ bản -->
    <div class="relative px-6">
      <div class="flex items-center absolute -top-16 left-6 z-10">
        <div class="relative">
            <img src="../../../storage/pictures/avt/<?= htmlspecialchars($chitiet['avt']) ?>" alt="Avatar" class="w-32 h-32 rounded-full border-4 border-white shadow-lg object-cover">
            <!-- Nút camera cập nhật avatar --> 
            <form action="../../models/cn_avt.php" method="POST" enctype="multipart/form-data" class="absolute bottom-0 right-0"> 
                <label for="uploadAvt" class="cursor-pointer"> <i class="fas fa-camera text-blue-500"></i> </label> 
                <input type="hidden" name="idnguoidung" value="<?= $chitiet['id'] ?>">
                <input type="file" id="uploadAvt" name="avt" class="hidden" onchange="this.form.submit()"> 
            </form>
        </div>
        <div class="ml-4">
            <div class="bg-white/70 backdrop-blur-sm p-4 rounded-lg shadow">
                <h2 class="text-2xl font-bold text-gray-600">
                    <?= htmlspecialchars($chitiet['ho_ten']) ?>
                </h2>
                <p class="text-gray-600">@<?= htmlspecialchars($chitiet['ten_dang_nhap']) ?></p>
                <span class="inline-block mt-1 px-3 py-1 text-sm rounded-lg 
                    <?= $chitiet['hoat_dong'] === 'online' ? 'bg-green-100 text-green-700' : 'bg-gray-200 text-gray-600' ?>">
                    <?= ucfirst($chitiet['hoat_dong']) ?>
                </span> 
            </div>
            
        </div>
      </div>
    </div>

    <!-- Thông tin chi tiết -->
    <div class="px-6 pt-20 pb-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Cột trái -->
            <div class="space-y-3">
                <p><strong>Email:</strong> <?= htmlspecialchars($chitiet['email']) ?></p>
                <p><strong>SĐT:</strong> <?= htmlspecialchars($chitiet['so_dt']) ?></p>
                <p><strong>Giới tính:</strong> <?= htmlspecialchars($chitiet['gioi_tinh']) ?></p>
                <?php if ($chitiet['vai_tro'] == 'moigioi'): ?>
                    <p><strong>Công ty:</strong> <?= htmlspecialchars($chitiet['cty']) ?></p>
                <?php endif; ?>
            </div>

            <!-- Cột phải -->
            <div class="space-y-3">
                <?php if ($chitiet['vai_tro'] == 'moigioi'): ?>
                    <p><strong>Kinh nghiệm:</strong> <?= htmlspecialchars($chitiet['kinh_nghiem']) ?> năm</p>
                <?php endif; ?>
                <!-- Vai trò -->

                <?php 
                    $chuoiQuyen = $chitiet['vai_tro']; 
                    $dsQuyenArray = explode(',', trim($chuoiQuyen, '{}')); 

                    $labelvaitro = [
                        'quantri' => 'Quản trị',
                        'moigioi' => 'Môi giới',
                        'khachhang' => 'Khách hàng'
                    ];

                    // Ví dụ trong PHP Controller
                    $stmt = $pdo->query("SELECT id, vai_tro FROM quyen ORDER BY vai_tro");
                    $dsTatCaQuyen = $stmt->fetchAll(PDO::FETCH_ASSOC);

                ?>

                <p class="flex items-center flex-wrap gap-2">
                    <strong class="mr-2">Vai trò:</strong>
                    <?php foreach ($dsQuyenArray as $vai_tro): ?>
                        <span 
                            class="px-2 py-1 rounded-full text-xs font-semibold 
                                <?= $roleColors[$vai_tro] ?? 'bg-gray-100 text-gray-700' ?>">
                            <?= $labelvaitro[$vai_tro] ?? ucfirst($vai_tro) ?>
                        </span>
                    <?php endforeach; ?>

                    <button type="button" 
                            onclick="from_vaitro('<?= $chitiet['id'] ?>')" 
                            class="ml-2 text-blue-500 hover:text-blue-700">
                        <i class="fas fa-edit"></i>
                    </button>
                </p>

                <!-- Form chỉnh quyền (ẩn mặc định) -->
                <div id="form-quyen-<?= $chitiet['id'] ?>" class="hidden mt-2 p-3 border rounded bg-gray-50">
                    <form onsubmit="return capnhatvaitro(this, '<?= $chitiet['id'] ?>')">
                        <label class="block mb-1 font-semibold">Chọn quyền:</label>
                        <select name="vai_tro[]" multiple class="border rounded px-2 py-1 w-48">
                            <?php foreach ($dsTatCaQuyen as $q): ?>
                                <!-- <option value="<?= $q['id'] ?>" <?= in_array($q['id'],$dsQuyenArray)?'selected':'' ?>><?= $labelvaitro[$q['vai_tro']] ?></option> -->
                                <?php
                                    // Nếu người dùng đã là "khách hàng" thì ẩn tùy chọn "Khách hàng"
                                    if (in_array($q['vai_tro'], $dsQuyenArray)) continue;
                                ?>
                                <option value="<?= $q['id'] ?>" <?= in_array($q['id'],$dsQuyenArray)?'selected':'' ?>>
                                    <?= $labelvaitro[$q['vai_tro']] ?>
                                </option>
                            <?php endforeach; ?>
                        </select>

                        <div class="mt-3 flex space-x-2">
                            <button type="submit" class="px-3 py-1 bg-green-500 text-white rounded">Lưu</button>
                            <button type="button" onclick="from_vaitro('<?= $chitiet['id'] ?>')" class="px-3 py-1 bg-gray-400 text-white rounded">Hủy</button>
                        </div>
                    </form>
                </div>

                <!-- Trạng thái -->
                <p class="flex items-center">
                    <strong>Trạng thái:</strong> 
                    <span id="trangthaitext" class="ml-2"><?= $chitiet['trang_thai'] ?></span>
                </p>
                <p><strong>Ngày tạo:</strong> <?= htmlspecialchars($chitiet['ngay_tao']) ?></p>
            </div>
        </div>

        <!-- Mô tả chiếm cả 2 cột -->
        <div class="mt-6">
            <p><strong>Mô tả:</strong> <?= nl2br(htmlspecialchars($chitiet['mo_ta'])) ?></p>
        </div>
    </div>

    <!-- Đánh giá -->
    <div class="px-6 py-4 border-t bg-gray-50">
        <h3 class="text-lg font-semibold text-gray-800 mb-3">Đánh giá của khách hàng</h3>

        <?php if (count($danhgia) > 0): ?>
            <div class="space-y-4">
                <?php foreach ($danhgia as $dg): ?>
                    <div class="border p-3 rounded-lg bg-white shadow-sm">
                        <div class="flex items-center justify-between mb-2">
                           <div class="flex items-center space-x-2">
                                <img src="../../../storage/pictures/avt/<?= htmlspecialchars($dg['avt_khach_hang']) ?>" 
                                    alt="avatar" 
                                    class="w-8 h-8 rounded-full object-cover">
                                <span class="font-semibold text-gray-700"><?= htmlspecialchars($dg['ten_khach_hang']) ?></span>
                            </div>
                            <span class="text-yellow-500">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <i class="fas fa-star <?= $i <= $dg['diem'] ? '' : 'text-gray-300' ?>"></i>
                                <?php endfor; ?>
                            </span>
                        </div>
                        <p class="text-gray-700"><?= nl2br(htmlspecialchars($dg['binh_luan'])) ?></p>
                        <p class="text-sm text-gray-500 mt-1">Ngày: <?= htmlspecialchars($dg['ngay_dg']) ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="text-gray-500 italic">Chưa có đánh giá nào.</p>
        <?php endif; ?>
    </div>

    <!-- Nút liên hệ -->
    <div class="px-6 py-4 border-t bg-gray-50 flex space-x-3">
        <a href="mailto:<?= htmlspecialchars($chitiet['email']) ?>" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
            <i class="fas fa-envelope"></i> Gửi Email
        </a>
        <a href="tel:<?= htmlspecialchars($chitiet['so_dt']) ?>" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
            <i class="fas fa-phone"></i> Gọi điện
        </a>
    </div>
</div>


<script>
function from_vaitro(userId) {
    const formDiv = document.getElementById("form-quyen-" + userId);
    formDiv.classList.toggle("hidden");
}

function capnhatvaitro(form, userId) {
    const formData = new FormData(form);
    const roleIds = formData.getAll("vai_tro[]");

    if (roleIds.length === 0) {
        alert("Người dùng phải có ít nhất 1 quyền!");
        return false;
    }

    fetch("../../models/cn_vaitro_nd.php", {
        method: "POST",
        body: JSON.stringify({ id: userId, quyen_ids: roleIds }),
        headers: { "Content-Type": "application/json" }
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert("Cập nhật quyền thành công!");
            location.reload();
        } else {
            alert("Lỗi: " + data.message);
        }
    });

    return false;
}

</script>


<script>
    function btnvaitro(field) {
        document.getElementById('trangthaitext').style.display = 'none';
        document.getElementById('trangthaiedit').classList.remove('hidden');
    }

    function huycapnhat(field) {
        document.getElementById('trangthaitext').style.display = 'inline';
        document.getElementById('trangthaiedit').classList.add('hidden');
    }

    function capnhattrangthai(userId) {
        let tt = document.getElementById('trangthaiselect').value;

        fetch('../../models/cn_trangthai_nd.php', {
            method: 'POST',
            headers: {'Content-Type':'application/json'},
            body: JSON.stringify({id:userId, trangthai:tt})
        })
        .then(res=>res.json())
        .then(data=>{
            if(data.success){
                alert("Cập nhật trạng thái thành công!");
                location.reload();
            } else {
                alert('Cập nhật thất bại!');
            }
        });
    }
</script>

</body>
</html>
