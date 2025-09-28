<?php
    $nd = [
        ["id"=>1, "name"=>"Nguyễn Văn A", "email"=>"a@gmail.com"],
        ["id"=>2, "name"=>"Trần Thị B", "email"=>"b@gmail.com"],
        ["id"=>3, "name"=>"Lê Văn C", "email"=>"c@gmail.com"],
        ["id"=>4, "name"=>"Phạm Thị D", "email"=>"d@gmail.com"],
        ["id"=>5, "name"=>"Hoàng Văn E", "email"=>"e@gmail.com"],
    ];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gửi thông báo</title>
</head>
<body>

<!-- Header -->
<header class="flex items-center gap-4 bg-white shadow p-4  border-b-2">
    <img src="../../../public/assets/anhht/0/notification.gif" alt="Chat" class="w-12 h-12">
    <h1 class="text-2xl font-bold text-gray-600">Gửi thông báo</h1>
</header>

<div class="w-full bg-white p-6">
    <!-- Khung chọn user -->
    <div class="mb-4" id="userSelectArea">
        <label class="block text-gray-600 font-medium mb-2">Tài khoản được chọn</label>
        <div id="selectedUsers" class="min-h-[80px] border border-gray-300 rounded-lg p-3 bg-gray-50 text-sm text-gray-600">
            <p class="text-gray-400">Chưa có tài khoản nào được chọn</p>
        </div>

        <!-- Ô tìm kiếm -->
        <div class="mt-3 relative">
            <input type="text" id="searchUser" placeholder="Tìm kiếm tài khoản..."
                class="w-full border border-gray-300 rounded-lg p-2 outline-none focus:ring focus:ring-blue-200 focus:border-blue-400">
            <!-- Gợi ý kết quả -->
            <div id="searchResults" class="absolute w-full bg-white border border-gray-200 rounded-lg shadow mt-1 hidden max-h-40 overflow-y-auto z-10"></div>
        </div>
    </div>

    <!-- Checkbox chọn tất cả -->
    <div class="mb-6 flex items-center gap-2">
        <input type="checkbox" id="selectAll" class="w-4 h-4 text-blue-500 border-gray-300 rounded">
        <label for="selectAll" class="text-gray-700">Chọn tất cả tài khoản</label>
    </div>

    <!-- Hình thức gửi -->
    <div class="mb-6">
        <label class="block text-gray-600 font-medium mb-2">Hình thức gửi</label>
        <div class="flex gap-6">
            <label class="flex items-center gap-2">
                <input type="radio" name="sendMethod" value="email" class="w-4 h-4 text-blue-500 border-gray-300">
                <i class="fa-solid fa-envelope text-blue-400"></i><span>Email</span>
            </label>
            <label class="flex items-center gap-2">
                <input type="radio" name="sendMethod" value="chat" class="w-4 h-4 text-blue-500 border-gray-300">
                <i class="fa-regular fa-comments text-blue-400"></i><span>Hộp thoại chat</span>
            </label>
        </div>
    </div>

    <!-- Tiêu đề -->
    <div class="mb-4">
        <label class="block text-gray-600 font-medium mb-2">Tiêu đề</label>
        <input type="text" id="title" class="w-full rounded-lg shadow-sm outline-none focus:ring focus:ring-blue-200 p-2 border border-gray-300" placeholder="Nhập tiêu đề thông báo...">
    </div>

    <!-- Nội dung -->
    <div class="mb-6">
        <label class="block text-gray-600 font-medium mb-2">Nội dung</label>
        <textarea id="content" rows="4" class="w-full rounded-lg shadow-sm outline-none focus:ring focus:ring-blue-200 p-2 border border-gray-300" placeholder="Nhập nội dung thông báo..."></textarea>
    </div>

    <!-- Nút gửi -->
    <div class="flex justify-end">
        <button id="sendBtn" class="bg-blue-500 hover:bg-blue-600 text-white font-semibold py-2 px-5 rounded-lg shadow flex items-center gap-2 transition">
            <i class="fa-solid fa-paper-plane"></i> Gửi thông báo
        </button>
    </div>
</div>

<script>
    const users = <?php echo json_encode($nd); ?>;

    const searchInput = document.getElementById("searchUser");
    const searchResults = document.getElementById("searchResults");
    const selectedUsers = document.getElementById("selectedUsers");
    const selectAll = document.getElementById("selectAll");
    const userSelectArea = document.getElementById("userSelectArea");

    let chosenUsers = [];

    // Hiển thị user đã chọn
    function renderSelectedUsers() {
        if (chosenUsers.length === 0) {
            selectedUsers.innerHTML = `<p class="text-gray-400">Chưa có tài khoản nào được chọn</p>`;
            return;
        }
        selectedUsers.innerHTML = chosenUsers.map(u =>
            `<span class="inline-block bg-green-100 text-green-700 px-2 py-1 rounded-lg mr-2 mb-2"><i class="fa-solid fa-user mr-1"></i>${u.name}</span>`
        ).join("");
    }

    // Tìm kiếm user
    searchInput.addEventListener("input", () => {
        const keyword = searchInput.value.toLowerCase();
        if (!keyword) {
            searchResults.classList.add("hidden");
            return;
        }

        const filtered = users.filter(u =>
            u.name.toLowerCase().includes(keyword) || u.email.toLowerCase().includes(keyword)
        );

        if (filtered.length === 0) {
            searchResults.innerHTML = `<p class="p-2 text-gray-500">Không tìm thấy</p>`;
        } else {
            searchResults.innerHTML = filtered.map(u =>
            `<div class="p-2 hover:bg-blue-50 cursor-pointer" data-id="${u.id}">
                <i class="fa-solid fa-user text-gray-500 mr-1"></i>${u.name} - ${u.email}
            </div>`
            ).join("");
        }
        searchResults.classList.remove("hidden");
    });

    // Chọn user từ danh sách gợi ý
    searchResults.addEventListener("click", (e) => {
        const id = e.target.closest("div")?.getAttribute("data-id");
        if (!id) return;

        const user = users.find(u => u.id == id);
        if (user && !chosenUsers.some(u => u.id == id)) {
            chosenUsers.push(user);
            renderSelectedUsers();
        }
        searchInput.value = "";
        searchResults.classList.add("hidden");
    });

    // Ẩn/hiện khung chọn user khi tick "Chọn tất cả"
    selectAll.addEventListener("change", function() {
        if (this.checked) {
            userSelectArea.style.display = "none"; 
            selectedUsers.innerHTML = `
            <span class="inline-block bg-blue-100 text-blue-600 px-2 py-1 rounded-lg mr-2 mb-2">
                <i class="fa-solid fa-users mr-1"></i>Tất cả người dùng
            </span>
            `;
        } else {
            userSelectArea.style.display = "block"; 
            chosenUsers = [];
            renderSelectedUsers();
        }
    });

    document.getElementById("sendBtn").addEventListener("click", () => {
        const title = document.getElementById("title").value.trim();
        const content = document.getElementById("content").value.trim();
        const method = document.querySelector("input[name='sendMethod']:checked");

        if (!title || !content) {
            alert("⚠️ Vui lòng nhập đầy đủ tiêu đề và nội dung!");
            return;
        }
        if (!method) {
            alert("⚠️ Vui lòng chọn hình thức gửi (Email hoặc Chat)!");
            return;
        }
        if (!selectAll.checked && chosenUsers.length === 0) {
            alert("⚠️ Vui lòng chọn ít nhất một tài khoản!");
            return;
        }

        const recipients = selectAll.checked ? "Tất cả người dùng" : chosenUsers.map(u => u.name).join(", ");

        alert(`✅ Gửi thành công!\n\n👥 Người nhận: ${recipients}\n📢 Tiêu đề: ${title}\n📧 Qua: ${method.value}`);
    });
</script>
</body>
</html>