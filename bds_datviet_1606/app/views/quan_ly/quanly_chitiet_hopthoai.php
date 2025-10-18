<?php
    require_once "../../../config/database.php"; // Đảm bảo đường dẫn này đúng
    $pdo = ketnoicsdl();

    // --- 1. LẤY THAM SỐ TỪ URL ---
    $id_hop_thoai = $_GET['id'] ?? null;
    $id_admin_dang_nhap = $_SESSION['id_nguoi_dung'] ?? null; // ID của Admin

    if (!$id_hop_thoai || !$id_admin_dang_nhap) {
        die("Lỗi: Yêu cầu không hợp lệ. Vui lòng cung cấp ID hội thoại và ID Admin.");
    }

    // --- 3. LẤY THÔNG TIN HỘI THOẠI ---
    $sql_info = "SELECT 
                    h.id AS id_hop_thoai, h.da_khoa, h.id_nguoi_1, h.id_nguoi_2,
                    COALESCE(info1.ho_ten, nd1.ten_dang_nhap) AS ten_nguoi_1,
                    COALESCE(info2.ho_ten, nd2.ten_dang_nhap) AS ten_nguoi_2
                FROM hop_thoai h
                JOIN nguoi_dung nd1 ON h.id_nguoi_1 = nd1.id
                JOIN nguoi_dung nd2 ON h.id_nguoi_2 = nd2.id
                LEFT JOIN info_nguoi_dung info1 ON nd1.id = info1.id_nguoi_dung
                LEFT JOIN info_nguoi_dung info2 ON nd2.id = info2.id_nguoi_dung
                WHERE h.id = :id_hop_thoai";
    $stmt_info = $pdo->prepare($sql_info);
    $stmt_info->execute([':id_hop_thoai' => $id_hop_thoai]);
    $hop_thoai = $stmt_info->fetch(PDO::FETCH_ASSOC);
    if (!$hop_thoai) { die("Lỗi: Không tìm thấy hội thoại."); }
    $da_khoa = $hop_thoai['da_khoa'];
    $id_nguoi_1 = $hop_thoai['id_nguoi_1'];
    $id_nguoi_2 = $hop_thoai['id_nguoi_2'];
    $ten_nguoi_1 = $hop_thoai['ten_nguoi_1'];
    $ten_nguoi_2 = $hop_thoai['ten_nguoi_2'];

    // --- 4. LẤY TẤT CẢ TIN NHẮN ---
    $sql_msgs = "SELECT 
                    id, nguoi_gui, noi_dung, anh_tn, video_tn, tg_gui, trang_thai,
                    (SELECT ten_dang_nhap FROM nguoi_dung WHERE id = nguoi_gui) AS ten_nguoi_gui
                FROM tin_nhan
                WHERE id_hop_thoai = :id_hop_thoai ORDER BY tg_gui ASC"; 
    $stmt_msgs = $pdo->prepare($sql_msgs);
    $stmt_msgs->execute([':id_hop_thoai' => $id_hop_thoai]);
    $ds_tin_nhan = $stmt_msgs->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi tiết Hội thoại (Admin)</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>
    <script type="module" src="https://cdn.jsdelivr.net/npm/emoji-picker-element@^1/index.js"></script>
    <style>
        #chat-messages::-webkit-scrollbar { width: 8px; }
        #chat-messages::-webkit-scrollbar-track { background: #f1f5f9; }
        #chat-messages::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
        #chat-messages::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
        #emoji-picker-container { position: absolute; bottom: 75px; left: 1rem; z-index: 100; display: none; }
        #emoji-picker-container emoji-picker { width: 300px; height: 250px; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.15); }
    </style>
</head>
<body>

<div class="flex flex-col w-full max-w-3xl h-[75vh] bg-white shadow-2xl rounded-lg overflow-hidden mx-auto relative z-10">
    
    <header class="flex items-center justify-between p-4 border-b border-gray-200 shadow-sm">
        <div class="flex items-center space-x-3">
            <a href="trangchu.php?page=quanly_hopthoai_chat" class="p-2 text-gray-500 rounded-full hover:bg-gray-100 hover:text-indigo-600 transition-colors" title="Quay lại danh sách">
                <ion-icon name="arrow-back-outline" class="w-6 h-6"></ion-icon>
            </a>
            <?php 
                $ten_nguoi_chat_cung = ($id_admin_dang_nhap == $id_nguoi_1) ? $ten_nguoi_2 : $ten_nguoi_1;
            ?>
            <div class="w-10 h-10 bg-gray-500 rounded-full flex items-center justify-center text-white font-bold text-xl uppercase border-2 border-white">
                <?= mb_substr($ten_nguoi_chat_cung, 0, 1) ?>
            </div>
            <div>
                <h2 class="text-lg font-semibold text-gray-900"><?= htmlspecialchars($ten_nguoi_chat_cung) ?></h2>
            </div>
        </div>
        <div class="flex items-center space-x-2">
            <?php if ($da_khoa): ?>
                <span class="flex items-center px-3 py-1 text-xs font-medium text-red-700 bg-red-100 rounded-full"><ion-icon name="lock-closed-outline" class="w-4 h-4 mr-1"></ion-icon> Đã khóa</span>
            <?php else: ?>
                 <span class="flex items-center px-3 py-1 text-xs font-medium text-green-700 bg-green-100 rounded-full"><ion-icon name="lock-open-outline" class="w-4 h-4 mr-1"></ion-icon> Đang hoạt động</span>
            <?php endif; ?>
        </div>
    </header>

    <main id="chat-messages" class="flex-1 p-6 space-y-4 overflow-y-auto bg-slate-100">
        <?php if (empty($ds_tin_nhan)): ?>
            <div id="no-message-placeholder" class="text-center text-gray-500 pt-10">Không có tin nhắn nào.</div>
        <?php endif; ?>
        <?php foreach ($ds_tin_nhan as $tin_nhan): ?>
            <?php $isAdminMessage = ($tin_nhan['nguoi_gui'] == $id_admin_dang_nhap); ?>
            <?php if ($isAdminMessage): ?>
                <div class="flex justify-end group">
                    <div class="max-w-xs lg:max-w-md p-3 bg-indigo-600 text-white rounded-l-lg rounded-br-lg shadow-md">
                        <div class="font-semibold text-xs text-indigo-200 mb-1">
                            <ion-icon name="checkmark-circle-outline"></ion-icon>
                            Bạn (<?= htmlspecialchars($tin_nhan['ten_nguoi_gui']) ?>)
                        </div>
                        <?php if (!empty($tin_nhan['anh_tn'])): ?><img src="<?= htmlspecialchars($tin_nhan['anh_tn']) ?>" alt="Hình ảnh" class="rounded-md mb-2 max-w-full h-auto"><?php endif; ?>
                        <?php if (!empty($tin_nhan['noi_dung'])): ?><p class="text-sm"><?= htmlspecialchars($tin_nhan['noi_dung']) ?></p><?php endif; ?>
                        <div class="flex justify-end items-center mt-1 space-x-2">
                            <span class="text-xs text-indigo-200"><?= date('H:i, d/m/Y', strtotime($tin_nhan['tg_gui'])) ?></span>
                            <?php if ($tin_nhan['trang_thai'] == 'da_doc'): ?><span class_="text-xs text-indigo-100" title="Người dùng đã xem"><ion-icon name="checkmark-done-outline" class="w-4 h-4"></ion-icon></span><?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="flex justify-start items-start space-x-2">
                    <?php 
                        $sender_name = ($tin_nhan['nguoi_gui'] == $id_nguoi_1) ? $ten_nguoi_1 : $ten_nguoi_2;
                        $sender_avatar_char = mb_substr($sender_name, 0, 1);
                    ?>
                    <div class="w-8 h-8 bg-gray-500 rounded-full flex items-center justify-center text-white font-bold text-sm uppercase flex-shrink-0 mt-1">
                        <?= htmlspecialchars($sender_avatar_char) ?>
                    </div>
                    <div class="max-w-xs lg:max-w-md p-3 bg-white text-gray-800 rounded-r-lg rounded-bl-lg shadow-md border border-gray-200">
                        <div class="font-semibold text-xs text-gray-700 mb-1">
                            <?= htmlspecialchars($sender_name) ?>
                        </div>
                        <?php if (!empty($tin_nhan['anh_tn'])): ?><img src="<?= htmlspecialchars($tin_nhan['anh_tn']) ?>" alt="Hình ảnh" class="rounded-md mb-2 max-w-full h-auto"><?php endif; ?>
                        <?php if (!empty($tin_nhan['noi_dung'])): ?><p class="text-sm"><?= htmlspecialchars($tin_nhan['noi_dung']) ?></p><?php endif; ?>
                        <span class="block mt-1 text-xs text-gray-500 text-left"><?= date('H:i, d/m/Y', strtotime($tin_nhan['tg_gui'])) ?></span>
                    </div>
                </div>
            <?php endif; ?>
        <?php endforeach; ?>
    </main>

    <footer class="p-4 bg-white border-t border-gray-200 relative">
        <div id="image-preview-container" class="mb-2 relative w-24"></div>
        <div id="emoji-picker-container"><emoji-picker class="light"></emoji-picker></div>
        <div id="thong-bao-container">
            </div>
        
        <form method="POST" action="../../models/xuly_gui_tinnhan_qt.php?id=<?= htmlspecialchars($id_hop_thoai) ?>" enctype="multipart/form-data" id="chat-form"> 
            <input type="file" name="anh_tn" id="file-input" accept="image/*" class="hidden">
            <div class="flex items-center space-x-2">
                <button type="button" id="btn-attach" class="p-2 text-gray-500 rounded-full hover:bg-gray-100" title="Đính kèm ảnh"><ion-icon name="image-outline" class="w-6 h-6"></ion-icon></button>
                <button type="button" id="btn-emoji" class="p-2 text-gray-500 rounded-full hover:bg-gray-100" title="Chọn emoji"><ion-icon name="happy-outline" class="w-6 h-6"></ion-icon></button>
                <input type="text" name="noi_dung" id="noi_dung_input" class="flex-1 block w-full px-4 py-2 text-sm text-gray-900 bg-gray-100 border border-gray-300 rounded-full focus:outline-none focus:ring-2 focus:ring-indigo-500" placeholder="Nhắn với tư cách Quản trị viên..." autocomplete="off">
                <button type="submit" id="btn-send" class="p-2 text-indigo-600 rounded-full hover:bg-indigo-100" title="Gửi tin nhắn"><ion-icon name="send" class="w-6 h-6"></ion-icon></button>
            </div>
        </form>
    </footer>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        
        const chatMessages = document.getElementById('chat-messages');
        const chatForm = document.getElementById('chat-form');
        const btnSend = document.getElementById('btn-send');
        const btnAttach = document.getElementById('btn-attach');
        const fileInput = document.getElementById('file-input');
        const btnEmoji = document.getElementById('btn-emoji');
        const emojiPickerContainer = document.getElementById('emoji-picker-container');
        const textInput = document.getElementById('noi_dung_input');
        const previewContainer = document.getElementById('image-preview-container');
        const thongBaoContainer = document.getElementById('thong-bao-container');

        if (chatMessages) {
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }

        function showNotification(message, isError = true) {
            thongBaoContainer.innerHTML = `<p class="thong-bao-text text-sm ${isError ? 'text-red-600' : 'text-green-600'} mb-2 text-center">${message}</p>`;
        }
        function clearNotification() {
            thongBaoContainer.innerHTML = '';
        }

        // JavaScript Fetch không cần thay đổi, vì nó đọc 'action' từ form
        chatForm.addEventListener('submit', async (e) => {
            e.preventDefault(); 
            clearNotification();
            
            // Thêm kiểm tra: không cho gửi form rỗng
            if (textInput.value.trim() === '' && fileInput.files.length === 0) {
                return; // Không làm gì cả
            }

            btnSend.disabled = true;
            const formData = new FormData(chatForm);

            try {
                // 'chatForm.action' giờ đã là 'xuly_gui_tinnhan_qt.php?id=...'
                const response = await fetch(chatForm.action, {
                    method: 'POST',
                    body: formData
                });

                if (response.ok) {
                    const newMessageHtml = await response.text();
                    const placeholder = document.getElementById('no-message-placeholder');
                    if (placeholder) { placeholder.remove(); }
                    chatMessages.insertAdjacentHTML('beforeend', newMessageHtml);
                    chatMessages.scrollTop = chatMessages.scrollHeight;
                    textInput.value = '';
                    fileInput.value = '';
                    previewContainer.innerHTML = '';
                } else {
                    const errorData = await response.json();
                    showNotification(errorData.error || 'Lỗi không xác định.');
                }
            } catch (error) {
                showNotification('Lỗi kết nối. Vui lòng thử lại.');
                console.error('Fetch Error:', error);
            } finally {
                btnSend.disabled = false;
            }
        });

        // Code xử lý đính kèm, emoji, preview (giữ nguyên)
        btnAttach.addEventListener('click', () => { fileInput.click(); });
        fileInput.addEventListener('change', () => {
            if (fileInput.files && fileInput.files[0]) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    previewContainer.innerHTML = `
                        <img src="${e.target.result}" class="w-24 h-24 object-cover rounded-md border">
                        <button type="button" id="btn-remove-preview" class="absolute top-1 right-1 p-0.5 bg-red-500 text-white rounded-full leading-none">
                            <ion-icon name="close-outline" class="w-4 h-4"></ion-icon>
                        </button>
                    `;
                    document.getElementById('btn-remove-preview').addEventListener('click', () => {
                        fileInput.value = '';
                        previewContainer.innerHTML = '';
                    });
                };
                reader.readAsDataURL(fileInput.files[0]);
            }
        });
        btnEmoji.addEventListener('click', () => {
            const isHidden = emojiPickerContainer.style.display === 'none' || emojiPickerContainer.style.display === '';
            emojiPickerContainer.style.display = isHidden ? 'block' : 'none';
        });
        document.querySelector('emoji-picker').addEventListener('emoji-click', event => {
            textInput.value += event.detail.unicode;
            emojiPickerContainer.style.display = 'none';
            textInput.focus();
        });
        document.addEventListener('click', (event) => {
            if (!emojiPickerContainer.contains(event.target) && !btnEmoji.contains(event.target)) {
                emojiPickerContainer.style.display = 'none';
            }
        });
    });
</script>

</body>
</html>