const { Server } = require("socket.io");

// Khởi tạo server Socket.IO trên cổng 3000
const io = new Server(3000, {
    cors: {
        origin: "*", // Cho phép kết nối từ mọi nguồn (thay đổi cho production)
    },
});

console.log("Socket.IO server is running on port 3000");

io.on("connection", (socket) => {
    console.log("A user connected:", socket.id);

    // Lắng nghe sự kiện 'chat message' từ client
    socket.on("chat message", (msg) => {
        console.log("Message from " + socket.id + ": " + msg);
        // Gửi lại tin nhắn cho tất cả các client đang kết nối
        io.emit("chat message", msg);
    });

    // Khi client ngắt kết nối
    socket.on("disconnect", () => {
        console.log("User disconnected:", socket.id);
    });
});