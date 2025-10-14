const { Server } = require("socket.io");

const io = new Server(3000, {
    cors: { origin: "*" },
});

let onlineUserIds = new Set();
console.log("Socket.IO server is running on port 3000");

const broadcastOnlineUsers = () => {
    io.emit("updateOnlineList", Array.from(onlineUserIds));
    console.log("Broadcasted online users:", Array.from(onlineUserIds));
};

io.on("connection", (socket) => {
    console.log("A client connected:", socket.id);

    // Gửi danh sách online cho client vừa kết nối (giữ nguyên)
    socket.emit("updateOnlineList", Array.from(onlineUserIds));

    // Lắng nghe sự kiện định danh online (giữ nguyên)
    socket.on("userConnected", (userId) => {
        socket.userId = userId;
        onlineUserIds.add(userId);
        broadcastOnlineUsers();
    });

    // === PHẦN MỚI BẮT ĐẦU ===
    // Lắng nghe sự kiện khi client gửi thông tin cá nhân lên
    socket.on("sendUserInfo", (data) => {
        // 'data' sẽ là một object chứa { id: '...', name: '...' }
        console.log(`Received user info from ${data.name} (ID: ${data.id})`);

        // Phát sự kiện này tới TẤT CẢ client đang kết nối, bao gồm cả người gửi
        io.emit("receiveUserInfo", data);
    });
    // === PHẦN MỚI KẾT THÚC ===

    // Khi client ngắt kết nối (giữ nguyên)
    socket.on("disconnect", () => {
        if (socket.userId) {
            onlineUserIds.delete(socket.userId);
            broadcastOnlineUsers();
        }
        console.log("A client disconnected:", socket.id);
    });
});