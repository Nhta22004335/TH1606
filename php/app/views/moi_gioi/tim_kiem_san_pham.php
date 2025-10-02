<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <title>Tìm kiếm BĐS</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
  <div class="max-w-5xl mx-auto py-10">
    <h1 class="text-2xl font-bold text-blue-600 mb-6">🔍 Tìm kiếm sản phẩm</h1>
    <form class="flex gap-3 mb-6">
      <input type="text" placeholder="Nhập tên dự án, khu vực..." class="flex-1 border p-2 rounded">
      <button class="px-5 bg-blue-500 text-white rounded">Tìm</button>
    </form>

    <!-- Kết quả mẫu -->
    <div class="grid grid-cols-3 gap-4">
      <div class="bg-white shadow rounded p-3">
        <img src="https://via.placeholder.com/200x120" class="rounded mb-2">
        <h3 class="font-bold">Căn hộ Q7</h3>
        <p class="text-gray-600">Giá: 2 tỷ</p>
      </div>
      <div class="bg-white shadow rounded p-3">
        <img src="https://via.placeholder.com/200x120" class="rounded mb-2">
        <h3 class="font-bold">Nhà phố Q9</h3>
        <p class="text-gray-600">Giá: 3.5 tỷ</p>
      </div>
    </div>
  </div>
</body>
</html>
