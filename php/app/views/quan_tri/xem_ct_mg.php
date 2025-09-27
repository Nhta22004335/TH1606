<?php
    require_once "../../../config/database.php";
    $pdo = ketnoicsdl();

    $id = $_GET['id'] ?? '';

    // Lấy thông tin môi giới
    $stmt = $pdo->prepare("
        SELECT mg.*, nd.ten_dang_nhap, nd.email, nd.so_dt, nd.trang_thai, nd.hoat_dong, nd.vai_tro, nd.ngay_tao
        FROM moi_gioi mg
        JOIN nguoi_dung nd ON nd.id = mg.id_nguoi_dung
        WHERE mg.id = :id
    ");
    $stmt->execute([':id' => $id]);
    $moigioi = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$moigioi) {
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
    $moigioi['gioi_tinh'] = $gioiTinhMap[$moigioi['gioi_tinh']] ?? $moigioi['gioi_tinh'];

    $vaitromap = [
        'moigioi' => 'Môi giới',
        'quantri' => 'Quản trị',
        'khachhang' => 'Khách hàng'
    ];

    $moigioi['vai_tro'] = $vaitromap[$moigioi['vai_tro']] ?? $moigioi['vai_tro'];

    $trangthaimap = [
        'danghoatdong' => 'Đang hoạt động',
        'chuakichhoat' => 'Chưa kích hoạt',
        'khoa' => 'Khóa'
    ];

    $moigioi['trang_thai'] = $trangthaimap[$moigioi['trang_thai']] ?? $moigioi['trang_thai'];

    $motamap = [
        'chuacapnhat' => 'Chưa cập nhật'
    ];

    $moigioi['mo_ta'] = $motamap[$moigioi['mo_ta']] ?? $moigioi['mo_ta'];

    // Lấy danh sách đánh giá
    $stmt2 = $pdo->prepare("
        SELECT dg.*, nd.ten_dang_nhap 
        FROM danh_gia_mg dg
        LEFT JOIN nguoi_dung nd ON nd.id = dg.id_khach_hang
        WHERE dg.id_moi_gioi = :id_mg
        ORDER BY dg.ngay_dg DESC
    ");
    $stmt2->execute([':id_mg' => $moigioi['id_nguoi_dung']]);
    $danhgias = $stmt2->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Chi tiết môi giới</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="../../../public/assets/fontawesome/css/all.min.css">
</head>
<body>

  <div class="max-w-4xl mx-auto mt-6 bg-white shadow-lg rounded-xl overflow-hidden">

    <!-- Ảnh bìa -->
    <div class="relative h-56">
      <img src="../../../public/assets/anhht/0/<?= htmlspecialchars($moigioi['avt']) ?>" alt="Ảnh bìa" class="w-full h-full object-cover">
        <!-- Nút camera cập nhật ảnh bìa --> 
        <form method="POST" enctype="multipart/form-data" class="absolute top-3 right-3"> 
            <label for="uploadBia" class="cursor-pointer"> <i class="fas fa-camera text-blue-500"></i> </label> 
            <input type="file" id="uploadBia" name="bia" class="hidden" onchange="this.form.submit()"> 
        </form>
    </div>

    <!-- Avatar + Thông tin cơ bản -->
    <div class="relative px-6">
      <div class="flex items-center absolute -top-16 left-6 z-10">
        <div class="relative">
            <img src="../../../public/assets/anhht/0/<?= htmlspecialchars($moigioi['avt']) ?>" alt="Avatar" class="w-32 h-32 rounded-full border-4 border-white shadow-lg object-cover">
            <!-- Nút camera cập nhật avatar --> 
            <form method="POST" enctype="multipart/form-data" class="absolute bottom-0 right-0"> 
                <label for="uploadAvt" class="cursor-pointer"> <i class="fas fa-camera text-blue-500"></i> </label> 
                <input type="file" id="uploadAvt" name="avt" class="hidden" onchange="this.form.submit()"> 
            </form>
        </div>
        <div class="ml-4">
          <h2 class="text-2xl font-bold text-gray-800">
            <?= htmlspecialchars($moigioi['ho_ten']) ?>
          </h2>
          <p class="text-gray-600">@<?= htmlspecialchars($moigioi['ten_dang_nhap']) ?></p>
          <span class="inline-block mt-1 px-3 py-1 text-sm rounded-lg 
            <?= $moigioi['hoat_dong'] === 'online' ? 'bg-green-100 text-green-700' : 'bg-gray-200 text-gray-600' ?>">
            <?= ucfirst($moigioi['hoat_dong']) ?>
          </span>
        </div>
      </div>
    </div>

    <!-- Thông tin chi tiết -->
    <div class="px-6 pt-20 pb-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Cột trái -->
            <div class="space-y-3">
                <p><strong>Email:</strong> <?= htmlspecialchars($moigioi['email']) ?></p>
                <p><strong>SĐT:</strong> <?= htmlspecialchars($moigioi['so_dt']) ?></p>
                <p><strong>Giới tính:</strong> <?= htmlspecialchars($moigioi['gioi_tinh']) ?></p>
                <p><strong>Công ty:</strong> <?= htmlspecialchars($moigioi['cty']) ?></p>
            </div>

            <!-- Cột phải -->
            <div class="space-y-3">
                <p><strong>Kinh nghiệm:</strong> <?= htmlspecialchars($moigioi['kinh_nghiem']) ?> năm</p>
                <!-- Vai trò -->
                <p class="flex items-center">
                    <strong>Vai trò:</strong> 
                    <span id="vaitrotext" class="ml-2"><?= htmlspecialchars($moigioi['vai_tro']) ?></span>
                    <button onclick="btnvaitro('vaitro')" class="ml-2 text-blue-500 hover:text-blue-700">
                        <i class="fas fa-edit"></i>
                    </button>
                </p>
                <div id="vaitroedit" class="hidden">
                    <select id="vaitroselect" class="border rounded px-2 py-1">
                        <option value="moigioi" <?= $moigioi['vai_tro']=='Môi giới'?'selected':'' ?>>Môi giới</option>
                        <option value="quantri">Quản trị</option>
                    </select>
                    <button onclick="capnhatvaitro('vaitro','<?= $moigioi['id_nguoi_dung'] ?>')" class="px-2 py-1 bg-green-500 text-white rounded">Lưu</button>
                    <button onclick="huycapnhat('vaitro')" class="px-2 py-1 bg-gray-400 text-white rounded">Hủy</button>
                </div>
                <!-- Trạng thái -->
                <p class="flex items-center">
                    <strong>Trạng thái:</strong> 
                    <span id="trangthaitext" class="ml-2"><?= htmlspecialchars($moigioi['trang_thai']) ?></span>
                    <button onclick="btnvaitro('trangthai')" class="ml-2 text-blue-500 hover:text-blue-700">
                        <i class="fas fa-edit"></i>
                    </button>
                </p>
                <div id="trangthaiedit" class="hidden">
                    <select id="trangthaiselect" class="border rounded px-2 py-1">
                        <option value="danghoatdong" <?= $moigioi['trang_thai']=='active'?'selected':'' ?>>Hoạt động</option>
                        <option value="chuakichhoat" <?= $moigioi['trang_thai']=='inactive'?'selected':'' ?>>Ngưng hoạt động</option>
                    </select>
                    <button onclick="capnhatvaitro('trangthai','<?= $moigioi['id_nguoi_dung'] ?>')" class="px-2 py-1 bg-green-500 text-white rounded">Lưu</button>
                    <button onclick="huycapnhat('trangthai')" class="px-2 py-1 bg-gray-400 text-white rounded">Hủy</button>
                </div>
                <p><strong>Ngày tạo:</strong> <?= htmlspecialchars($moigioi['ngay_tao']) ?></p>
            </div>
        </div>

        <!-- Mô tả chiếm cả 2 cột -->
        <div class="mt-6">
            <p><strong>Mô tả:</strong> <?= nl2br(htmlspecialchars($moigioi['mo_ta'])) ?></p>
        </div>
    </div>


    <!-- Đánh giá -->
    <div class="px-6 py-4 border-t bg-gray-50">
        <h3 class="text-lg font-semibold text-gray-800 mb-3">Đánh giá của khách hàng</h3>

        <?php if (count($danhgias) > 0): ?>
            <div class="space-y-4">
                <?php foreach ($danhgias as $dg): ?>
                    <div class="border p-3 rounded-lg bg-white shadow-sm">
                        <div class="flex items-center justify-between mb-2">
                            <span class="font-semibold text-gray-700"><?= htmlspecialchars($dg['ten_dang_nhap'] ?? 'Ẩn danh') ?></span>
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
        <a href="mailto:<?= htmlspecialchars($moigioi['email']) ?>" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
            <i class="fas fa-envelope"></i> Gửi Email
        </a>
        <a href="tel:<?= htmlspecialchars($moigioi['so_dt']) ?>" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
            <i class="fas fa-phone"></i> Gọi điện
        </a>
    </div>
</div>

<script>
    function btnvaitro(field) {
        document.getElementById(field+'text').style.display = 'none';
        document.getElementById(field+'edit').classList.remove('hidden');
    }

    function huycapnhat(field) {
        document.getElementById(field+'text').style.display = 'inline';
        document.getElementById(field+'edit').classList.add('hidden');
    }

    function capnhatvaitro(field, userId) {
        let value = document.getElementById(field+'select').value;

        fetch('../../models/capnhat_tt_mg.php', {
            method: 'POST',
            headers: {'Content-Type':'application/json'},
            body: JSON.stringify({id:userId, field:field, value:value})
        })
        .then(res=>res.json())
        .then(data=>{
            if(data.success){
                document.getElementById(field+'text').innerText = data.newValue;
                huycapnhat(field); 
            } else {
                alert('Cập nhật thất bại');
            }
        });
    }
</script>

</body>
</html>
