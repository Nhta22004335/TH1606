<?php
    // =================================================================
    // PHẦN 1: XỬ LÝ PHP (GIỮ NGUYÊN THEO YÊU CẦU CỦA BẠN)
    // =================================================================
    require_once "../../../config/database.php"; 
    if (session_status() == PHP_SESSION_NONE) { session_start(); }
    $pdo = ketnoicsdl();

    $sql = "
        SELECT 
            nd.id AS id_nguoi_dung,
            info.ho_ten,
            STRING_AGG(q.vai_tro, ', ') AS cac_vai_tro,
            nd.avt
        FROM nguoi_dung nd
        JOIN info_nguoi_dung info ON nd.id = info.id_nguoi_dung
        JOIN phan_quyen pq ON nd.id = pq.id_nguoi_dung
        JOIN quyen q ON pq.id_quyen = q.id
        GROUP BY nd.id, info.ho_ten, nd.avt;
    ";
    $stmt = $pdo->query($sql);
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $currentUser = $_SESSION['id_nguoi_dung'];
    $currentChat = $_GET['chat'] ?? null;
    $idkey = $_GET['idkey'] ?? null;

    $sql = "
        SELECT 
            ht.id AS id_hop_thoai,
            ht.da_khoa,
            ht.da_xoa,
            info_g.ho_ten AS ten_nguoi_gui,
            info_n.ho_ten AS ten_nguoi_nhan,
            ng.avt AS avt_nguoi_gui,
            ng.id AS id_nguoi_gui,
            nn.id AS id_nguoi_nhan,
            nn.avt AS avt_nguoi_nhan,
            tn.tg_gui,
            tn.noi_dung,
            tn.anh_tn,
            tn.da_thu_hoi,
            tn.da_xoa,
            tn.id AS id_tin_nhan,
            MAX(tn.tg_gui) AS tg_moi_nhat,
            COUNT(tn.id) AS tong_tin_nhan
        FROM hop_thoai ht
        JOIN tin_nhan tn ON tn.id_hop_thoai = ht.id
        JOIN nguoi_dung ng ON tn.nguoi_gui = ng.id
        JOIN info_nguoi_dung info_g ON ng.id = info_g.id_nguoi_dung
        JOIN nguoi_dung nn ON tn.nguoi_nhan = nn.id
        JOIN info_nguoi_dung info_n ON nn.id = info_n.id_nguoi_dung
        
        GROUP BY ht.id, info_g.ho_ten, info_n.ho_ten, ng.avt, nn.avt, ng.id, nn.id, tn.tg_gui, tn.noi_dung, tn.anh_tn, tn.id, tn.da_thu_hoi, tn.da_xoa, ht.da_khoa, ht.da_xoa
        ORDER BY tg_moi_nhat ASC
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $chatGroups = [];
    foreach ($messages as $m) {
        $pair = [$m['ten_nguoi_gui'], $m['ten_nguoi_nhan']];
        $idkey_participants = [$m['id_nguoi_gui'], $m['id_nguoi_nhan']];
        sort($pair);
        $chatKey = implode("_", $pair);
        sort($idkey_participants);
        $idkey_sorted = implode("_", $idkey_participants);
        $chatGroups[$chatKey] = $chatGroups[$chatKey] ?? ['msgs' => [], 'idkey' => $idkey_sorted];
        $chatGroups[$chatKey]['msgs'][] = $m;
    }
?>

<!DOCTYPE html>
<html lang="vi" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Chat</title>
    <style>
        [x-cloak] { display: none !important; }
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: #f1f5f9; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 3px;}
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
        .chat-bubble-me { border-radius: 1.2rem 1.2rem 0.3rem 1.2rem; }
        .chat-bubble-other { border-radius: 1.2rem 1.2rem 1.2rem 0.3rem; }
    </style>
</head>
<body class="bg-slate-100 h-full flex items-center justify-center antialiased">

<main class="flex w-full h-full max-h-[95vh] max-w-6xl bg-transparent p-0 sm:p-4 relative overflow-hidden gap-4" x-data="{ view: '<?php echo $currentChat ? 'chat' : 'list'; ?>' }">
    <aside 
        class="flex flex-col bg-white rounded-xl shadow-lg transition-transform duration-300 w-full md:w-[340px] lg:w-[380px] absolute inset-0 md:static z-10"
        :class="{ '-translate-x-full md:translate-x-0': view === 'chat', 'translate-x-0': view === 'list' }"
    >
        <div class="p-4 border-b">
            <h2 class="text-2xl font-bold text-slate-800 mb-4">Danh sách hội thoại</h2>
            <div class="relative">
                <input type="text" id="searchChatInput" placeholder="Tìm kiếm người nhắn..."
                       class="w-full border border-slate-200 rounded-lg py-2 pl-10 pr-4 focus:ring-2 focus:ring-indigo-300 focus:outline-none text-sm">
                <i class="fas fa-search absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400"></i>
                <div id="searchResults" class="absolute bg-white w-full rounded-lg shadow-lg mt-1 z-50 hidden max-h-60 overflow-y-auto border"></div>
            </div>
        </div>
        
        <div class="flex-1 overflow-y-auto p-2 space-y-1">
            <?php foreach ($chatGroups as $chatKey => $chatData): 
                $msgs = $chatData['msgs'];
                $idkey_from_group = $chatData['idkey'];
                $lastMsg = end($msgs);
                $participants = [$lastMsg['id_nguoi_gui'], $lastMsg['id_nguoi_nhan']];
                $otherUserId = ($lastMsg['id_nguoi_gui'] == $currentUser) ? $lastMsg['id_nguoi_nhan'] : $lastMsg['id_nguoi_gui'];
                $otherUser = array_values(array_filter($users, fn($u) => $u['id_nguoi_dung'] == $otherUserId))[0] ?? null;

                $active = ($currentChat == $chatKey) ? 'bg-indigo-50' : 'hover:bg-slate-50';
                $isLocked = $lastMsg['da_khoa'] ?? 0;
            ?>
                <a href="trangchu.php?page=ql_hopthoai&chat=<?= urlencode($chatKey) ?>&idkey=<?= urlencode($idkey_from_group) ?>"
                   @click="view = 'chat'"
                   class="flex items-center p-3 rounded-lg cursor-pointer transition-colors duration-200 <?= $active ?>">
                    <div class="relative flex-shrink-0">
                        <img src="../../../storage/pictures/avt/<?= htmlspecialchars($otherUser['avt'] ?? 'default.png') ?>" class="w-12 h-12 rounded-full object-cover border">
                    </div>
                    <div class="flex-1 ml-3 overflow-hidden">
                        <p class="font-bold text-slate-800 truncate"><?= htmlspecialchars($otherUser['ho_ten'] ?? 'Người dùng') ?></p>
                        <p class="text-sm text-slate-500 truncate"><?= htmlspecialchars($lastMsg['noi_dung'] ?? '...') ?></p>
                    </div>
                    <div class="flex flex-col items-end text-xs text-slate-400 ml-2 flex-shrink-0">
                        <span class="font-medium whitespace-nowrap"><?= date('H:i', strtotime($lastMsg['tg_gui'])) ?></span>
                        <?php if ($isLocked): ?>
                            <i class="fas fa-lock text-red-500 mt-1" title="Đã khóa"></i>
                        <?php endif; ?>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </aside>

    <section 
        class="flex-1 flex flex-col bg-slate-50 rounded-xl border shadow-lg transition-transform duration-300 w-full absolute inset-0 md:static"
        :class="{ 'translate-x-0': view === 'chat', 'translate-x-full md:translate-x-0': view === 'list' }"
    >
        <?php if ($currentChat && isset($chatGroups[$currentChat])): 
            $msgs = $chatGroups[$currentChat]['msgs'];
            $firstMsg = $msgs[0];
            $otherUserId = ($firstMsg['id_nguoi_gui'] == $currentUser) ? $firstMsg['id_nguoi_nhan'] : $firstMsg['id_nguoi_gui'];
            $otherUser = array_values(array_filter($users, fn($u) => $u['id_nguoi_dung'] == $otherUserId))[0] ?? null;
        ?>
            <div class="bg-white text-slate-800 p-3 rounded-t-xl font-semibold flex justify-between items-center border-b">
                <div class="flex items-center gap-3">
                    <button @click="view = 'list'" class="md:hidden text-slate-500 hover:text-indigo-600 p-2 -ml-2">
                        <i class="fas fa-arrow-left"></i>
                    </button>
                    <img src="../../../storage/pictures/avt/<?= htmlspecialchars($otherUser['avt'] ?? 'default.png') ?>" class="w-10 h-10 rounded-full object-cover">
                    <div>
                        <span class="font-bold"><?= htmlspecialchars($otherUser['ho_ten'] ?? 'Chọn hội thoại') ?></span>
                        <p class="text-xs text-green-600 font-normal">Đang hoạt động</p>
                    </div>
                </div>
                
                <div x-data="{ open: false }" class="relative">
                    <button @click="open = !open" class="text-slate-500 hover:bg-slate-100 p-2 rounded-full" title="Tùy chọn">
                        <i class="fas fa-cog"></i>
                    </button>
                    <div x-show="open" x-cloak @click.outside="open = false" x-transition class="absolute right-0 mt-2 w-56 bg-white rounded-lg shadow-xl border py-1 z-10">
                        <button id="deleteChatBtn" data-hopthoai="<?= htmlspecialchars($firstMsg['id_hop_thoai']) ?>" class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-slate-100 flex items-center gap-3">
                            <i class="fas fa-trash-alt fa-fw"></i> Xóa hội thoại
                        </button>
                        <button id="lockChatBtn" data-hopthoai="<?= htmlspecialchars($firstMsg['id_hop_thoai']) ?>" class="w-full text-left px-4 py-2 text-sm text-slate-700 hover:bg-slate-100 flex items-center gap-3">
                            <i class="fas fa-lock fa-fw"></i> Khóa hội thoại
                        </button>
                        <button id="reportChatBtn" data-chat="<?= htmlspecialchars($idkey) ?>" class="w-full text-left px-4 py-2 text-sm text-yellow-600 hover:bg-slate-100 flex items-center gap-3">
                            <i class="fas fa-exclamation-triangle fa-fw"></i> Cảnh báo
                        </button>
                    </div>
                </div>
            </div>

            <div id="chatBox" class="flex-1 overflow-y-auto p-4 space-y-4">
                <?php foreach ($msgs as $m):
                    $isMe = ($m['id_nguoi_gui'] == $currentUser);
                    $align = $isMe ? 'justify-end' : 'justify-start';
                    $bubbleClasses = $isMe ? 'bg-indigo-500 text-white chat-bubble-me' : 'bg-white text-slate-800 chat-bubble-other';
                    $hoursPassed = (time() - strtotime($m['tg_gui'])) / 3600;
                ?>
                    <div class="flex items-end gap-2.5 <?= $align ?> group">
                        <?php if (!$isMe): ?>
                            <img src="../../../storage/pictures/avt/<?= $m['avt_nguoi_gui'] ?>" class="w-8 h-8 rounded-full shadow flex-shrink-0 object-cover">
                        <?php endif; ?>

                        <div class="px-4 py-3 shadow-sm max-w-[70%] break-words relative <?= $bubbleClasses ?>">
                            <?php if ($m['da_thu_hoi'] == 1): ?>
                                <p class="italic text-sm opacity-75"><i class="fas fa-ban"></i> Tin nhắn đã được thu hồi</p>
                            <?php elseif ($m['da_xoa'] == 1): ?>
                                <p class="italic text-sm opacity-75"><i class="fas fa-ban"></i> Tin nhắn đã được xóa</p>
                            <?php elseif (!empty($m['anh_tn'])): ?>
                                <img src="../../../storage/pictures/messages/<?= htmlspecialchars($m['anh_tn']) ?>" alt="Image" class="max-w-xs rounded-md shadow mb-1">
                            <?php else: ?>
                                <p><?= nl2br(htmlspecialchars($m['noi_dung'])) ?></p>
                            <?php endif; ?>
                            <p class="text-xs opacity-60 text-right mt-1"><?= date('H:i', strtotime($m['tg_gui'])) ?></p>
                        </div>
                        
                        <?php if ($isMe && !$m['da_thu_hoi'] && !$m['da_xoa']): ?>
                        <div class="flex-shrink-0 opacity-0 group-hover:opacity-100 transition-opacity">
                            <?php if ($hoursPassed <= 24): ?>
                                <button onclick="revokeMessage('<?= $m['id_tin_nhan'] ?>')" title="Thu hồi" class="text-slate-400 hover:text-indigo-600"><i class="fas fa-undo"></i></button>
                            <?php else: ?>
                                <button onclick="deleteMessage('<?= $m['id_tin_nhan'] ?>')" title="Xóa" class="text-slate-400 hover:text-red-500"><i class="fas fa-trash"></i></button>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>

            <?php
            $isLocked = $msgs[0]['da_khoa'] ?? 0;
            if (!$isLocked):
            ?>
                <form id="chatForm" enctype="multipart/form-data" class="border-t bg-white p-3 flex items-center gap-2">
                    <button type="button" onclick="openEmojiPicker()" class="p-2 text-slate-500 hover:bg-slate-100 rounded-full transition-colors">
                        <i class="far fa-smile text-xl"></i>
                    </button>
                    <label class="p-2 text-slate-500 hover:bg-slate-100 rounded-full cursor-pointer transition-colors">
                        <i class="far fa-image text-xl"></i>
                        <input type="file" name="image" accept="image/*" class="hidden" id="uploadImage">
                    </label>
                    <button type="button" id="likeBtn" class="p-2 text-indigo-500 hover:bg-slate-100 rounded-full transition-colors" title="Thả Like">
                        <i class="fas fa-thumbs-up text-xl"></i>
                    </button>
                    <input type="text" name="message" id="messageInput" placeholder="Nhập tin nhắn..." class="flex-1 bg-slate-100 border-transparent rounded-full px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-400">
                    <button type="submit" class="bg-indigo-500 text-white w-11 h-11 flex-shrink-0 rounded-full hover:bg-indigo-600 transition-colors flex items-center justify-center">
                        <i class="fas fa-paper-plane"></i>
                    </button>
                    <input type="hidden" name="nguoi_nhan" value="<?= $otherUserId ?>">
                    <input type="hidden" name="id_hop_thoai" value="<?= $firstMsg['id_hop_thoai'] ?>">
                </form>
            <?php else: ?>
                <div class="border-t bg-white p-4 text-center text-sm text-red-600 font-medium">
                    <i class="fas fa-lock mr-2"></i> Cuộc hội thoại này đã bị khóa.
                </div>
            <?php endif; ?>

        <?php else: ?>
            <div class="flex-1 flex flex-col items-center justify-center text-center text-slate-400 bg-slate-50 p-4 rounded-xl">
                <i class="fa-solid fa-comments fa-4x mb-4 text-slate-300"></i>
                <h3 class="text-xl font-bold text-slate-600">Bắt đầu trò chuyện</h3>
                <p>Chọn một người từ danh sách để xem tin nhắn.</p>
            </div>
        <?php endif; ?>
    </section>

    <div id="emojiPickerWrapper" class="absolute bottom-20 left-10 bg-white shadow-lg rounded-lg p-2 hidden z-20 w-80 h-80 overflow-y-auto">
        <emoji-picker id="emojiPicker" class="w-full h-full"></emoji-picker>
    </div>
</main>

<script>
    function openEmojiPicker() {
        const picker = document.getElementById("emojiPickerWrapper");
        if (picker.classList.contains("hidden")) {
            picker.classList.remove("hidden");
        } else {
            picker.classList.add("hidden");
        }
    }

    function revokeMessage(msgId) {
        if (!confirm("Bạn có chắc muốn thu hồi tin nhắn này?")) return;
        fetch('../../models/th_tinnhan_qt.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `id=${msgId}`
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert("Thu hồi thất bại!");
            }
        })
        .catch(err => console.error(err));
    }

    function deleteMessage(msgId) {
        if (!confirm("Bạn có chắc muốn xóa tin nhắn này?")) return;
        fetch('../../models/xoa_tinnhan_qt.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `id=${msgId}`
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                 // Simple page reload to reflect change
                location.reload();
            } else {
                alert("Xóa thất bại!");
            }
        })
        .catch(err => console.error(err));
    }

    document.addEventListener("DOMContentLoaded", () => {
        const searchChatInput = document.getElementById("searchChatInput");
        const searchResults = document.getElementById("searchResults");
        const currentUserId = "<?php echo $currentUser; ?>";
        let searchTimeout = null;

        if (searchChatInput) {
            searchChatInput.addEventListener("input", () => {
                const keyword = searchChatInput.value.trim().toLowerCase();
                if (!keyword) {
                    searchResults.innerHTML = "";
                    searchResults.classList.add("hidden");
                    return;
                }
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(async () => {
                    const res = await fetch(`../../models/xl_tim_nguoi_dung.php?keyword=${encodeURIComponent(keyword)}&me=${currentUserId}`);
                    const users = await res.json();
                    if (users.length === 0) {
                        searchResults.innerHTML = `<div class="p-2 text-gray-500 text-sm">Không tìm thấy người dùng nào.</div>`;
                    } else {
                        searchResults.innerHTML = users.map(u => `
                            <div class="p-2 hover:bg-blue-50 cursor-pointer flex items-center gap-2 user-suggestion" data-id="${u.id}">
                                <img src="../../../storage/pictures/avt/${u.avt || 'avt.png'}" class="w-8 h-8 rounded-full border">
                                <span class="text-sm font-medium">${u.ho_ten}</span>
                            </div>
                        `).join('');
                    }
                    searchResults.classList.remove("hidden");
                }, 300);
            });
        }

        document.addEventListener("click", async (e) => {
            const el = e.target.closest(".user-suggestion");
            if (el) {
                const idNguoiNhan = el.dataset.id;
                const res = await fetch("../../models/xl_tao_hop_thoai.php", {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify({ nguoi_gui: currentUserId, nguoi_nhan: idNguoiNhan })
                });
                const data = await res.json();
                if (data.success) {
                    window.location.href = `trangchu.php?page=ql_hopthoai&chat=${data.chat}&idkey=${data.idkey}`;
                } else {
                    alert("Lỗi: " + data.message);
                }
            } else if (searchResults && !searchResults.contains(e.target) && e.target !== searchChatInput) {
                searchResults.classList.add("hidden");
            }
        });

        const deleteChatBtn = document.getElementById("deleteChatBtn");
        const lockChatBtn = document.getElementById("lockChatBtn");

        if (deleteChatBtn) {
            deleteChatBtn.addEventListener("click", async function() {
                const idHopThoai = this.dataset.hopthoai;
                if (!idHopThoai) {
                    alert("Không xác định được ID hội thoại!");
                    return;
                }
                if (!confirm("Bạn có chắc muốn xóa hội thoại này không?")) return;
                const formData = new FormData();
                formData.append("id_hop_thoai", idHopThoai);
                const res = await fetch("../../models/xoa_hopthoai_qt.php", { 
                    method: "POST", 
                    body: formData 
                });
                const data = await res.json();
                if (data.status === "ok") {
                    alert(data.msg || "Hội thoại đã được xóa!");
                    window.location.href = 'trangchu.php?page=ql_hopthoai';
                } else {
                    alert("Xóa thất bại: " + data.msg);
                }
            });
        }

        if (lockChatBtn) {
            lockChatBtn.addEventListener("click", async function() {
                const idHopThoai = this.dataset.hopthoai;
                if (!idHopThoai) {
                    alert("Không xác định được ID hội thoại!");
                    return;
                }
                if (!confirm("Khóa hội thoại này?")) return;
                const formData = new FormData();
                formData.append("id_hop_thoai", idHopThoai);
                const res = await fetch("../../models/khoa_hopthoai_qt.php", { 
                    method: "POST", 
                    body: formData 
                });
                const data = await res.json();
                if (data.status === "ok") {
                    alert("Hội thoại đã được khóa!");
                    location.reload();
                } else {
                    alert("Khóa thất bại: " + (data.msg || "Lỗi không xác định"));
                }
            });
        }

        const chatForm = document.getElementById("chatForm");
        const chatBox = document.getElementById("chatBox");
        const msgInput = document.getElementById("messageInput");
        const currentChat = "<?= $currentChat ?>";
        const likeBtn = document.getElementById("likeBtn");
        const uploadImage = document.getElementById("uploadImage");
        const emojiWrapper = document.getElementById("emojiPickerWrapper");
        const emojiPicker = document.getElementById("emojiPicker");
        const inputMessage = document.querySelector("input[name='message']");

        async function loadMessages() {
            if (!chatBox || !currentChat) return;
            const isAtBottom = chatBox.scrollHeight - chatBox.scrollTop - chatBox.clientHeight < 50;
            try {
                const res = await fetch(`../../models/load_tinnhan_new_qt.php?chat=${encodeURIComponent(currentChat)}`);
                const html = await res.text();
                // Avoid jarring reload if content is the same
                if(chatBox.innerHTML !== html) {
                    chatBox.innerHTML = html;
                    if (isAtBottom) chatBox.scrollTop = chatBox.scrollHeight;
                }
            } catch (error) {
                console.error("Failed to load messages:", error);
            }
        }

        if (chatForm) {
            chatForm.addEventListener("submit", async (e) => {
                e.preventDefault();
                const formData = new FormData(chatForm);
                if (!formData.get('message').trim()) return;
                const res = await fetch("../../models/xl_gui_tn_qt.php", { method: "POST", body: formData });
                const data = await res.json();
                if (data.status === "ok") {
                    msgInput.value = "";
                    await loadMessages();
                }
            });
        }

        if (likeBtn) {
            likeBtn.addEventListener("click", async () => {
                const formData = new FormData(chatForm);
                formData.append("like", "1");
                const res = await fetch("../../models/xl_gui_tn_qt.php", { method: "POST", body: formData });
                const data = await res.json();
                if (data.status === "ok") await loadMessages();
            });
        }

        if (uploadImage) {
            uploadImage.addEventListener("change", async () => {
                if (!uploadImage.files.length) return;
                const formData = new FormData(chatForm);
                formData.append("send_image", "1");
                const res = await fetch("../../models/xl_gui_tn_qt.php", { method: "POST", body: formData });
                const data = await res.json();
                if (data.status === "ok") {
                    await loadMessages();
                    uploadImage.value = "";
                }
            });
        }

        if (chatBox) chatBox.scrollTop = chatBox.scrollHeight;
        if (currentChat) {
             setInterval(loadMessages, 3000); // Check for new messages every 3 seconds
        }

        if (emojiPicker) {
            emojiPicker.addEventListener("emoji-click", event => {
                inputMessage.value += event.detail.unicode;
                emojiWrapper.classList.add("hidden");
            });
        }
        
        document.addEventListener("click", (e) => {
            if (emojiWrapper && !emojiWrapper.contains(e.target) && !e.target.closest("button[onclick='openEmojiPicker()']")) {
                emojiWrapper.classList.add("hidden");
            }
        });
    });
</script>

<script type="module" src="https://cdn.jsdelivr.net/npm/emoji-picker-element@^1/index.js"></script>

</body>
</html>