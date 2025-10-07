<?php
  // Nếu chưa đăng nhập thì quay lại trang login
  if (!isset($_SESSION['id_nguoi_dung'])) {
      header("Location: ../auth/dangnhap.html");
      exit;
  }

  require_once '../../../config/database.php'; 


  $id_khach = $_SESSION['id_nguoi_dung'];


  $stmt = $pdo->prepare("SELECT id, tieu_de,mo_ta,chuyen_muc,trang_thai, ngay_dang 
                        FROM tin_tuc
                        WHERE id_khach_hang = ? 
                        ORDER BY ngay_dang DESC");
  $stmt->execute([$id_khach]);
  $tin_dang = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <script src="https://cdn.tailwindcss.com"></script>
  <title>Quản lý tin đăng - Khách hàng</title>
</head>
<body class="bg-gray-100 min-h-screen">
  <div class="max-w-6xl mx-auto py-10 px-6">
    <!-- Tiêu đề -->
    <div class="flex justify-between items-center mb-8">
      <h1 class="text-3xl font-bold text-blue-700">Quản lý tin đăng</h1>
    </div>

    <!-- Bảng danh sách tin -->
    <div class="bg-white shadow-lg rounded-xl overflow-hidden">
      <table class="min-w-full text-sm text-gray-700">
        <thead class="bg-blue-600 text-white">
          <tr>
            <th class="px-4 py-3 text-left">Tiêu đề</th>
            <th class="px-4 py-3">Mô tả</th>
            <th class="px-4 py-3">Chuyên mục</th>
            <th class="px-4 py-3">Trạng thái</th>       
            <th class="px-4 py-3">Ngày đăng</th>
            <th class="px-4 py-3 text-center">Hành động</th>
          </tr>
        </thead>
        <tbody>
          <?php if (count($tin_dang) > 0): ?>
            <?php foreach ($tin_dang as $tin): ?>
              <tr class="border-b hover:bg-gray-50">
                <td class="px-4 py-3 font-semibold"><?= htmlspecialchars($tin['tieu_de']) ?></td>
                <td class="px-4 py-3"><?= htmlspecialchars($tin['mo_ta']) ?></td>
                <td class="px-4 py-3"><?= $tin['chuyen_muc'] ?></td>
                <td class="px-4 py-3"><?= $tin['trang_thai'] ?></td>
                <td class="px-4 py-3"><?= $tin['ngay_dang'] ?></td>
                 <td class="px-4 py-3 text-center space-x-2 flex">
                  <a href="trangchu.php?page=../../models/cn_tin_mg&id=<?= $tin['id'] ?>" 
                     class="px-3 py-1 bg-yellow-400 rounded text-white hover:bg-yellow-500">Sửa</a>
                  <a href="trangchu.php?page=../../models/xoa_tin_mg&id=<?= $tin['id'] ?>" 
                     onclick="return confirm('Bạn có chắc muốn xóa tin này?')"
                     class="px-3 py-1 bg-red-500 rounded text-white hover:bg-red-600">Xóa</a>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="6" class="px-4 py-6 text-center text-gray-500">Bạn chưa đăng tin nào.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</body>
</html>
