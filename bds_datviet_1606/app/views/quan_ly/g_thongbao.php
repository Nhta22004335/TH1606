<?php
// PHP data array for users (as provided in the original code)
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
<body class="bg-gray-100 min-h-screen">

<div class="max-w-3xl mx-auto bg-white rounded-xl shadow-lg overflow-hidden">

    <header class="flex items-center gap-4 bg-white shadow p-4 border-b-2 border-gray-100">
        <img src="../../../public/assets/anhht/0/notification.gif" alt="Notification" class="w-12 h-12 rounded-full border p-1">
        <h1 class="text-2xl font-bold text-blue-600">Gửi thông báo</h1>
    </header>

    <div class="w-full p-6">
        <div class="mb-6" id="userSelectArea">
            <label class="block text-gray-700 font-bold mb-2">Tài khoản được chọn</label>
            <div id="selectedUsers" class="min-h-[80px] border border-gray-300 rounded-lg p-3 bg-gray-50 text-sm text-gray-600 shadow-inner transition duration-150">
                <p class="text-gray-400">Chưa có tài khoản nào được chọn</p>
            </div>

            <div class="mt-3 relative">
                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                    <i class="fa-solid fa-magnifying-glass text-gray-400"></i>
                </div>
                <input type="text" id="searchUser" placeholder="Tìm kiếm tài khoản theo tên hoặc email..."
                    class="w-full border border-gray-300 rounded-lg pl-10 pr-4 py-2 outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-150">
                
                <div id="searchResults" class="absolute w-full bg-white border border-gray-200 rounded-lg shadow-xl mt-1 hidden max-h-40 overflow-y-auto z-10"></div>
            </div>
        </div>
        
        <div class="mb-8 border-t pt-4 border-gray-100">
            <div class="flex items-center gap-3 bg-blue-50 p-3 rounded-lg border border-blue-200">
                <input type="checkbox" id="selectAll" class="w-5 h-5 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500">
                <label for="selectAll" class="text-blue-700 font-semibold cursor-pointer select-none">Chọn tất cả tài khoản</label>
            </div>
        </div>
        
        <div class="mb-6">
            <label class="block text-gray-700 font-bold mb-3">Hình thức gửi</label>
            <div class="flex gap-8">
                <label class="flex items-center gap-2 bg-white p-3 border border-gray-300 rounded-lg shadow-sm hover:border-blue-500 transition duration-150 cursor-pointer">
                    <input type="radio" name="sendMethod" value="email" class="w-4 h-4 text-blue-500 border-gray-300 focus:ring-blue-500" required>
                    <i class="fa-solid fa-envelope text-lg text-blue-500"></i><span class="font-medium text-gray-700">Email</span>
                </label>
                <label class="flex items-center gap-2 bg-white p-3 border border-gray-300 rounded-lg shadow-sm hover:border-blue-500 transition duration-150 cursor-pointer">
                    <input type="radio" name="sendMethod" value="chat" class="w-4 h-4 text-blue-500 border-gray-300 focus:ring-blue-500" required>
                    <i class="fa-regular fa-comments text-lg text-blue-500"></i><span class="font-medium text-gray-700">Hộp thoại chat</span>
                </label>
            </div>
        </div>

        <div class="mb-4">
            <label for="title" class="block text-gray-700 font-bold mb-2">Tiêu đề</label>
            <input type="text" id="title" class="w-full rounded-lg shadow-sm outline-none focus:ring-2 focus:ring-blue-500 p-3 border border-gray-300" placeholder="Nhập tiêu đề thông báo..." required>
        </div>

        <div class="mb-8">
            <label for="content" class="block text-gray-700 font-bold mb-2">Nội dung</label>
            <textarea id="content" rows="5" class="w-full rounded-lg shadow-sm outline-none focus:ring-2 focus:ring-blue-500 p-3 border border-gray-300 resize-y" placeholder="Nhập nội dung thông báo..." required></textarea>
        </div>

        <div class="flex justify-end">
            <button id="sendBtn" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-6 rounded-xl shadow-lg flex items-center gap-2 transition duration-300 transform hover:scale-[1.02]">
                <i class="fa-solid fa-paper-plane"></i> Gửi thông báo
            </button>
        </div>
    </div>
</div>

<script>
    // PHP data is converted to a JavaScript array
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
        // Added a clickable 'X' for removing users
        selectedUsers.innerHTML = chosenUsers.map(u =>
            `<span data-id="${u.id}" class="inline-flex items-center bg-green-100 text-green-800 px-3 py-1 rounded-full mr-2 mb-2 text-sm font-medium">
                <i class="fa-solid fa-user mr-2"></i>${u.name}
                <button type="button" class="ml-2 text-green-600 hover:text-green-900 remove-user-btn leading-none" data-id="${u.id}">&times;</button>
            </span>`
        ).join("");
    }
    
    // Xóa user đã chọn
    selectedUsers.addEventListener("click", (e) => {
        if (e.target.classList.contains("remove-user-btn")) {
            const idToRemove = e.target.getAttribute("data-id");
            chosenUsers = chosenUsers.filter(u => u.id != idToRemove);
            renderSelectedUsers();
        }
    });

    // Tìm kiếm user
    searchInput.addEventListener("input", () => {
        const keyword = searchInput.value.toLowerCase();
        if (!keyword) {
            searchResults.classList.add("hidden");
            return;
        }

        // Filter out users already in chosenUsers
        const filtered = users.filter(u =>
            (u.name.toLowerCase().includes(keyword) || u.email.toLowerCase().includes(keyword)) && 
            !chosenUsers.some(cu => cu.id === u.id)
        );

        if (filtered.length === 0) {
            searchResults.innerHTML = `<p class="p-3 text-gray-500 italic">Không tìm thấy hoặc đã chọn tất cả kết quả</p>`;
        } else {
            searchResults.innerHTML = filtered.map(u =>
            `<div class="p-3 hover:bg-blue-50 cursor-pointer transition duration-100 border-b border-gray-100 last:border-b-0" data-id="${u.id}">
                <div class="font-medium text-gray-800">${u.name}</div>
                <div class="text-sm text-gray-500">${u.email}</div>
            </div>`
            ).join("");
        }
        searchResults.classList.remove("hidden");
    });

    // Chọn user từ danh sách gợi ý
    searchResults.addEventListener("click", (e) => {
        const item = e.target.closest("div[data-id]");
        if (!item) return;
        
        const id = item.getAttribute("data-id");
        const user = users.find(u => u.id == id);
        
        if (user && !chosenUsers.some(u => u.id == id)) {
            chosenUsers.push(user);
            renderSelectedUsers();
        }
        // Clear search input and hide results after selection
        searchInput.value = "";
        searchResults.classList.add("hidden");
    });

    // Ẩn/hiện khung chọn user khi tick "Chọn tất cả"
    selectAll.addEventListener("change", function() {
        if (this.checked) {
            userSelectArea.style.display = "none"; 
            selectedUsers.innerHTML = `
            <span class="inline-block bg-blue-100 text-blue-700 px-3 py-1 rounded-full mr-2 mb-2 font-medium">
                <i class="fa-solid fa-users mr-1"></i>Tất cả người dùng
            </span>
            `;
            // Clear chosenUsers so it's not confused with "All"
            chosenUsers = [];
        } else {
            userSelectArea.style.display = "block"; 
            renderSelectedUsers(); // Re-render if there were previous selections
        }
    });
    
    // Gửi thông báo
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
            alert("⚠️ Vui lòng chọn ít nhất một tài khoản hoặc chọn 'Tất cả tài khoản'!");
            return;
        }

        const recipients = selectAll.checked ? "Tất cả người dùng" : chosenUsers.map(u => u.name).join(", ");
        const methodDisplay = method.value === 'email' ? 'Email' : 'Hộp thoại chat';

        // Prepare the final alert message
        alert(`✅ Gửi thành công!\n\n👥 Người nhận: ${recipients}\n📢 Tiêu đề: ${title}\n📧 Hình thức: ${methodDisplay}\n\nNội dung đã gửi: "${content.substring(0, 50)}..."`);
    });
    
    // Initial render
    renderSelectedUsers();
</script>
</body>
</html>