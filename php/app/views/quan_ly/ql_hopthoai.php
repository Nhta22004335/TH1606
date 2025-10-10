<?php
    require_once "../../../config/database.php"; 
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
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý chat BĐS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/alpinejs" defer></script>
    <link rel="stylesheet" href="../../../public/assets/fontawesome/css/all.min.css">
    <style>[x-cloak]{display:none!important;}</style>
</head>
<body>

<main class="flex flex-1 p-4 gap-4 bg-gray-50">
    <!-- Danh sách chat -->
    <aside class="w-1/3 bg-white rounded-xl shadow overflow-y-auto h-[600px]">
        <div class="p-4">
            <h2 class="text-lg text-white font-semibold mb-4 flex items-center gap-2 bg-blue-500 p-2 rounded">
                <i class="fas fa-comment-dots text-white"></i>
                Danh sách hội thoại
            </h2>

            <div class="mb-3 relative">
                <input type="text" id="searchChatInput" placeholder="Tìm kiếm người nhắn..."
                    class="w-full border rounded-lg px-3 py-2 pl-9 focus:ring focus:ring-blue-200 focus:outline-none text-sm">
                <i class="fas fa-search absolute left-3 top-2.5 text-gray-400"></i>
                <!-- Khung kết quả tìm kiếm -->
                <div id="searchResults" class="absolute bg-white w-full rounded-lg shadow-lg mt-1 z-50 hidden max-h-60 overflow-y-auto"></div>
            </div>

            <div class="space-y-2">
                <?php
                    $chatGroups = [];
                    foreach ($messages as $m) {
                        $pair = [$m['ten_nguoi_gui'], $m['ten_nguoi_nhan']];
                        $idkey = [$m['id_nguoi_gui'], $m['id_nguoi_nhan']];
                        sort($pair);
                        $chatKey = implode("_", $pair);
                        sort($idkey);
                        $idkey = implode("_", $idkey);
                        $chatGroups[$chatKey][] = $m;
                    }

                    foreach ($chatGroups as $chatKey => $msgs) {
                        $participants = [$msgs[0]['id_nguoi_gui'], $msgs[0]['id_nguoi_nhan']];
                        $names = [];
                        $avatars = [];
                        foreach ($participants as $pid) {
                            $user = array_values(array_filter($users, fn($u) => $u['id_nguoi_dung'] == $pid))[0] ?? null;
                            if ($user) {
                                $names[] = $user['ho_ten'];
                                $avatars[] = $user['avt'];
                            }
                        }
                        $active = ($currentChat == $chatKey) ? 'bg-blue-100' : 'hover:bg-blue-50';
                        $isLocked = $msgs[0]['da_khoa'] ?? 0;
                ?>
                    <a href="trangchu.php?page=ql_hopthoai&chat=<?= urlencode($chatKey) ?>&idkey=<?= urlencode($idkey) ?>"
                       class="chat-item flex items-center p-2 rounded-lg cursor-pointer transition <?= $active ?>">
                        <div class="flex items-center">
                            <?php foreach ($avatars as $avt): ?>
                                <img src="../../../storage/pictures/avt/<?= htmlspecialchars($avt) ?>" class="w-8 h-8 rounded-full mr-2">
                            <?php endforeach; ?>
                            <?php
                                $displayNames = array_filter($names, fn($name, $index) => $participants[$index] != $currentUser, ARRAY_FILTER_USE_BOTH);
                            ?>
                            <span class="font-medium text-gray-700"><?= htmlspecialchars(implode(' & ', $displayNames)) ?></span>
                        </div>
                        <!-- Trạng thái hội thoại -->
                        <?php if ($isLocked): ?>
                            <span class="text-xs text-red-500 font-semibold flex items-center gap-1 ml-auto">
                                <i class="fas fa-lock"></i> Đã khóa
                            </span>
                        <?php endif; ?>
                    </a>
                <?php } ?>
            </div>
        </div>
    </aside>

    <!-- Chat area -->
    <section class="flex-1 flex flex-col bg-white rounded-xl border shadow relative">
        <!-- Header -->
        <div class="bg-blue-500 text-white p-3 rounded-t-xl font-semibold text-lg flex justify-between items-center">
            <span>
                <?php
                    if ($currentChat) {
                        $msgs = $chatGroups[$currentChat] ?? [];
                        if (!empty($msgs)) {
                            echo htmlspecialchars($msgs[0]['ten_nguoi_gui']) . ' & ' . htmlspecialchars($msgs[0]['ten_nguoi_nhan']);
                        }
                    } else {
                        echo "Chọn hội thoại";
                    }
                ?>
            </span>
            <div x-data="{ open: false }" class="relative">
                <!-- Button cài đặt -->
                <button @click="open = !open" class="hover:bg-blue-600 px-2 py-1 rounded" title="Cài đặt hội thoại">
                    <i class="fas fa-cog"></i>
                </button>

                <div class="flex items-center gap-3">
                    <div x-show="open" x-cloak @click.outside="open = false" x-transition 
                        class="absolute right-0 top-5 mt-3 w-48 bg-white rounded-lg shadow-lg border border-gray-200 py-2 z-10">
                        <div class="px-4 py-2 flex items-center space-x-2 border-b">
                            <i class="fas fa-cog text-gray-600"></i>
                            <div>
                                <p class="text-sm font-medium text-gray-800">Thiết lập hội thoại</p>
                                <p class="text-xs text-gray-500">Quản lý đoạn chat này</p>
                            </div>
                        </div>

                        <!-- Menu chức năng -->
                        <button id="deleteChatBtn" data-hopthoai="<?= htmlspecialchars($msgs[0]['id_hop_thoai']) ?>"
                            class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-gray-100 flex items-center gap-2">
                            <i class="fas fa-trash-alt"></i> Xóa hội thoại
                        </button>

                        <button id="lockChatBtn" data-hopthoai="<?= htmlspecialchars($msgs[0]['id_hop_thoai']) ?>"
                            class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 flex items-center gap-2">
                            <i class="fas fa-lock"></i> Khóa hội thoại
                        </button>

                        <button id="reportChatBtn" data-chat="<?= htmlspecialchars($idkey) ?>"
                            class="w-full text-left px-4 py-2 text-sm text-yellow-600 hover:bg-gray-100 flex items-center gap-2">
                            <i class="fas fa-exclamation-triangle"></i> Cảnh báo
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Nội dung tin nhắn -->
        <div id="chatBox" class="flex-1 overflow-y-auto p-4 space-y-4 bg-gray-50 max-h-[450px]">
            <?php if ($currentChat && !empty($msgs)): ?>
                <?php foreach ($msgs as $m):
                    $isMe = ($m['id_nguoi_gui'] == $currentUser);
                    $align = $isMe ? 'flex-row-reverse text-right' : 'flex-row text-left';
                    $bg = $isMe ? 'bg-blue-500 text-white' : 'bg-gray-200 text-gray-900';
                    $timestampColor = $isMe ? 'text-blue-200' : 'text-gray-500';
                    // Tính thời gian
                    $sentTime = strtotime($m['tg_gui']);
                    $now = time();
                    $hoursPassed = ($now - $sentTime) / 3600; // số giờ đã trôi qua
                ?>
                    <div class="flex items-start <?= $align ?> gap-3">
                        <img src="../../../storage/pictures/avt/<?= $m['avt_nguoi_gui'] ?>"
                            alt="<?= htmlspecialchars($m['ten_nguoi_gui']) ?>" 
                            class="w-10 h-10 rounded-full shadow flex-shrink-0">
                        <div class="px-4 py-3 <?= $bg ?> rounded-xl shadow max-w-[70%] break-words relative">
                            <?php if ($m['da_thu_hoi'] === 1): ?>
                                <p class="italic text-white"><i class="fas fa-info-circle"></i> Tin nhắn đã được thu hồi </p>
                            <?php elseif ($m['da_xoa'] === 1): ?>
                                <p class="italic text-white"><i class="fas fa-info-circle"></i> Tin nhắn đã được xóa </p>
                            <?php elseif (!empty($m['anh_tn'])): ?>
                                <p class="font-semibold mb-1"><?= htmlspecialchars($m['ten_nguoi_gui']) ?></p>
                                <!-- Nếu là ảnh -->
                                <img src="../../../storage/pictures/messages/<?= htmlspecialchars($m['anh_tn']) ?>" 
                                    alt="Image" class="max-w-xs rounded-md shadow mb-1">
                            <?php else: ?>
                                <p class="font-semibold mb-1"><?= htmlspecialchars($m['ten_nguoi_gui'])?></p>
                                <p class="mb-1"><?= nl2br(htmlspecialchars($m['noi_dung'])) ?></p>
                                <p class="text-xs <?= $timestampColor ?>"><?= date('H:i j/n/Y', strtotime($m['tg_gui'])) ?></p>
                                <?php if ($isMe): ?>
                                    <?php if ($hoursPassed <= 24): ?>
                                        <!-- Nút thu hồi, hiển thị khi hover vào tin nhắn -->
                                        <button onclick="revokeMessage('<?= $m['id_tin_nhan'] ?>')" 
                                            class="absolute -left-8 top-16 text-gray-500 hover:text-gray-600 text-sm">
                                            <i class="fas fa-undo"></i>
                                        </button>
                                    <?php else: ?>
                                        <!-- Nút thu hồi, hiển thị khi hover vào tin nhắn -->
                                        <button onclick="deleteMessage('<?= $m['id_tin_nhan'] ?>')" 
                                            class="absolute -left-8 top-16 text-gray-500 hover:text-gray-600 text-sm">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    <?php endif; ?>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="text-center text-gray-400 mt-10">Chưa có tin nhắn nào.</p>
            <?php endif; ?>
        </div>

        <?php
            $showInput = false;
            $id_nguoi_nhan = null;
            $id_hop_thoai = null;
            if ($currentChat && !empty($msgs)) {
                $ids = [$msgs[0]['id_nguoi_gui'], $msgs[0]['id_nguoi_nhan']];
                if (in_array($currentUser, $ids)) {
                    $showInput = true;
                    $id_nguoi_nhan = ($msgs[0]['id_nguoi_gui'] == $currentUser) ? $msgs[0]['id_nguoi_nhan'] : $msgs[0]['id_nguoi_gui'];
                    // Lấy id hội thoại từ bản ghi tin nhắn đầu tiên
                    $id_hop_thoai = $msgs[0]['id_hop_thoai'];
                }
            }
        ?>

        <?php if ($showInput): ?>
            <form id="chatForm" enctype="multipart/form-data" class="border-t p-3 flex items-center gap-2">
                <!-- Emoji -->
                <button type="button" onclick="openEmojiPicker()" class="p-2 text-gray-600 hover:bg-gray-100 rounded-full">
                    <i class="far fa-smile"></i>
                </button>
                <!-- Gửi ảnh -->
                <label class="p-2 text-gray-600 hover:bg-gray-100 rounded-full cursor-pointer">
                    <i class="far fa-image"></i>
                    <input type="file" name="image" accept="image/*" class="hidden" id="uploadImage">
                </label>
                <!-- Like -->
                <button type="button" id="likeBtn" class="p-2 text-blue-500 hover:bg-blue-100 rounded-full" title="Thả Like">
                    <i class="fas fa-thumbs-up"></i>
                </button>
                <!-- Nhập nội dung -->
                <input type="text" name="message" id="messageInput" placeholder="Nhập tin nhắn..."
                    class="flex-1 border rounded-full px-4 py-2 focus:outline-none focus:ring focus:ring-blue-200">
                <!-- Gửi -->
                <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded-full hover:bg-blue-600">
                    <i class="fas fa-paper-plane"></i>
                </button>

                <input type="hidden" name="nguoi_nhan" value="<?= $id_nguoi_nhan ?>">
                <input type="hidden" name="id_hop_thoai" value="<?= $id_hop_thoai ?>">
            </form>
        <?php endif; ?>


        <!-- Emoji Picker -->
        <div id="emojiPickerWrapper" 
            class="absolute bottom-20 left-10 bg-white shadow-lg rounded-lg p-2 hidden z-10 w-80 h-80 overflow-y-auto">
            <emoji-picker id="emojiPicker" class="w-full h-full"></emoji-picker>
        </div>

    </section>
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
                const msgBox = document.querySelector(`#msg-${msgId} .message-content`);
                if (msgBox) msgBox.innerHTML = '<i class="italic text-white">Tin nhắn đã được thu hồi</i>';
            } else {
                alert("Xóa thất bại!");
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
                const msgElem = document.getElementById(`msg-${msgId}`);
                if (msgElem) msgElem.remove();
            } else {
                alert("Xóa thất bại!");
            }
                 
        })
        .catch(err => console.error(err));
    }

document.addEventListener("DOMContentLoaded", () => {

    // === TÌM KIẾM TRONG DANH SÁCH CHAT ===
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

    // === Click chọn người để tạo hội thoại mới ===
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

    // === Các nút quản lý hội thoại ===
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
                document.getElementById("chatBox").innerHTML = ""; 
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
            } else {
                alert("Khóa thất bại: " + (data.msg || "Lỗi không xác định"));
            }
        });
    }


    // === Chat logic ===
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
        if (!chatBox) return;
        const isAtBottom = chatBox.scrollHeight - chatBox.scrollTop - chatBox.clientHeight < 10;
        const res = await fetch(`../../models/load_tinnhan_new_qt.php?chat=${encodeURIComponent(currentChat)}`);
        const html = await res.text();
        chatBox.innerHTML = html;
        if (isAtBottom) chatBox.scrollTop = chatBox.scrollHeight;
    }

    if (chatForm) {
        chatForm.addEventListener("submit", async (e) => {
            e.preventDefault();
            const formData = new FormData(chatForm);
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

    // Cuộn xuống cuối khi mở chat
    if (chatBox) chatBox.scrollTop = chatBox.scrollHeight;
    setInterval(() => { if (currentChat) loadMessages(); }, 2000);
    // Khi chọn emoji thì chèn vào input
    emojiPicker.addEventListener("emoji-click", event => {
        inputMessage.value += event.detail.unicode;
        emojiWrapper.classList.add("hidden");
    });

    // Ẩn emoji picker khi click ra ngoài
    document.addEventListener("click", (e) => {
        if (!emojiWrapper.contains(e.target) && !e.target.closest("button[onclick='openEmojiPicker()']")) {
            emojiWrapper.classList.add("hidden");
        }
    });

});


</script>


<script type="module" src="https://cdn.jsdelivr.net/npm/emoji-picker-element@^1/index.js"></script>

</body>
</html>
