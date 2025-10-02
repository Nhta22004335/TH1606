<?php
// Nếu chưa đăng nhập thì quay lại trang login
if (!isset($_SESSION['id_nguoi_dung'])) {
    header("Location: ../auth/dangnhap.html");
    exit;
}

require_once __DIR__ . '../../../database_mg.php'; // file kết nối PDO

// Lấy id khách hàng từ session
$id_khach = $_SESSION['id_nguoi_dung'];

// Truy vấn lấy các tin đăng của khách hàng
$stmt = $pdo->prepare("SELECT id_tin, tieu_de, gia, dien_tich, dia_chi, ngay_dang 
                       FROM tin_bds 
                       WHERE id_khach = ? 
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
      <h1 class="text-3xl font-bold text-blue-700">📋 Quản lý tin đăng</h1>
      <a href="dang_tin.php" 
         class="px-6 py-2 rounded-xl text-white bg-gradient-to-r from-blue-500 to-indigo-600 hover:from-indigo-600 hover:to-pink-500 shadow-lg">
         ➕ Đăng tin mới
      </a>
    </div>

    <!-- Bảng danh sách tin -->
    <div class="bg-white shadow-lg rounded-xl overflow-hidden">
      <table class="min-w-full text-sm text-gray-700">
        <thead class="bg-blue-600 text-white">
          <tr>
            <th class="px-4 py-3 text-left">Tiêu đề</th>
            <th class="px-4 py-3">Giá (VNĐ)</th>
            <th class="px-4 py-3">Diện tích (m²)</th>
            <th class="px-4 py-3">Địa chỉ</th>
            <th class="px-4 py-3">Ngày đăng</th>
            <th class="px-4 py-3 text-center">Hành động</th>
          </tr>
        </thead>
        <tbody>
          <?php if (count($tin_dang) > 0): ?>
            <?php foreach ($tin_dang as $tin): ?>
              <tr class="border-b hover:bg-gray-50">
                <td class="px-4 py-3 font-semibold"><?= htmlspecialchars($tin['tieu_de']) ?></td>
                <td class="px-4 py-3"><?= number_format($tin['gia'], 0, ',', '.') ?></td>
                <td class="px-4 py-3"><?= $tin['dien_tich'] ?></td>
                <td class="px-4 py-3"><?= htmlspecialchars($tin['dia_chi']) ?></td>
                <td class="px-4 py-3"><?= $tin['ngay_dang'] ?></td>
                <td class="px-4 py-3 text-center space-x-2">
                  <a href="sua_tin.php?id=<?= $tin['id_tin'] ?>" 
                     class="px-3 py-1 bg-yellow-400 rounded text-white hover:bg-yellow-500">✏️ Sửa</a>
                  <a href="xoa_tin.php?id=<?= $tin['id_tin'] ?>" 
                     onclick="return confirm('Bạn có chắc muốn xóa tin này?')"
                     class="px-3 py-1 bg-red-500 rounded text-white hover:bg-red-600">🗑️ Xóa</a>
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
