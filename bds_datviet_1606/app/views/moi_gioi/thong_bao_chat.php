<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <title>Thông báo & Chat</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
  <div class="max-w-4xl mx-auto py-10 grid grid-cols-2 gap-6">
    
    <!-- Thông báo -->
    <div class="bg-white p-6 rounded shadow">
      <h2 class="text-lg font-bold text-red-600 mb-3">🔔 Thông báo</h2>
      <ul class="space-y-2">
        <li class="border-b pb-2">Admin: Hệ thống bảo trì lúc 22h</li>
        <li class="border-b pb-2">Khách hàng B gửi tin nhắn mới</li>
      </ul>
    </div>

    <!-- Chat -->
    <div class="bg-white p-6 rounded shadow">
      <h2 class="text-lg font-bold text-blue-600 mb-3">💬 Chat với khách hàng</h2>
      <div class="h-48 border p-2 overflow-y-auto">
        <p><strong>B:</strong> Tôi muốn xem căn hộ Q7</p>
        <p><strong>Tôi:</strong> Ok, mai 10h nhé</p>
      </div>
      <div class="mt-2 flex">
        <input type="text" class="flex-1 border rounded-l p-2" placeholder="Nhập tin nhắn...">
        <button class="px-4 bg-blue-500 text-white rounded-r">Gửi</button>
      </div>
    </div>

  </div>
</body>
</html>
