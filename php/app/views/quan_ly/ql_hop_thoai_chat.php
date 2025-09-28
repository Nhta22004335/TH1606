<?php
$users = [
    ['id'=>1, 'name'=>'Admin', 'role'=>'admin','avatar'=>'../../../public/assets/anhht/0/avt.png'],
    ['id'=>2, 'name'=>'Môi giới 1', 'role'=>'broker','avatar'=>'../../../public/assets/anhht/0/avt.png'],
    ['id'=>3, 'name'=>'Môi giới 2', 'role'=>'broker','avatar'=>'../../../public/assets/anhht/0/avt.png'],
    ['id'=>4, 'name'=>'Khách hàng 1', 'role'=>'customer','avatar'=>'../../../public/assets/anhht/0/avt.png'],
    ['id'=>5, 'name'=>'Khách hàng 2', 'role'=>'customer','avatar'=>'../../../public/assets/anhht/0/avt.png'],
];

$tinnhan = [
    ['id_chat'=>1,'from'=>2,'to'=>4,'content'=>'Chào bạn, bạn quan tâm dự án nào?','timestamp'=>'10:00 24/09/2025'],
    ['id_chat'=>1,'from'=>4,'to'=>2,'content'=>'Tôi muốn xem VinHomes Central Park.','timestamp'=>'10:01 24/09/2025'],
    ['id_chat'=>2,'from'=>3,'to'=>5,'content'=>'Xin chào, tôi có thể tư vấn cho bạn.','timestamp'=>'10:05 24/09/2025'],
    ['id_chat'=>2,'from'=>5,'to'=>3,'content'=>'Vâng, tôi muốn tìm nhà quận 1.','timestamp'=>'10:06 24/09/2025'],
    ['id_chat'=>100,'from'=>1,'to'=>2,'content'=>'Nhắc nhở cập nhật thông tin dự án.','timestamp'=>'09:00 24/09/2025'],
    ['id_chat'=>101,'from'=>1,'to'=>4,'content'=>'Hướng dẫn thủ tục đặt cọc.','timestamp'=>'09:15 24/09/2025'],
];

$currentUser = $users[0];
$currentChat = $_GET['chat'] ?? null;
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý chat BĐS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="../../../public/assets/fontawesome/css/all.min.css">
    <style>[x-cloak]{display:none!important;}</style>
</head>
<body class="flex flex-col min-h-screen">

<!-- Header -->
<header class="flex items-center gap-4 bg-white shadow p-4">
    <img src="../../../public/assets/anhht/0/discussion.gif" alt="Chat" class="w-12 h-12">
    <h1 class="text-2xl font-bold text-gray-600">Quản lý hộp thoại chat</h1>
</header>

<main class="flex flex-1 p-4 gap-4 bg-gray-50">
    <!-- Danh sách chat -->
    <aside class="w-1/3 bg-white rounded-xl shadow overflow-y-auto h-[600px]">
        <div class="p-4">
            <h2 class="text-lg font-semibold mb-4 text-gray-700">Danh sách hội thoại</h2>
            <div class="space-y-2">
                <?php
                $chatIds = array_unique(array_column($tinnhan, 'id_chat'));
                foreach ($chatIds as $id) {
                    $participants = array_unique(array_map(function($t) use ($currentUser, $id) {
                        if ($t['id_chat'] != $id) return null;
                        return $t['from'] == $currentUser['id'] ? $t['to'] : $t['from'];
                    }, $tinnhan));
                    $participants = array_filter($participants);

                    $names = implode(' & ', array_map(
                        fn($pid) => $users[array_search($pid, array_column($users,'id'))]['name'],
                        $participants
                    ));
                    $active = ($currentChat == $id) ? 'bg-blue-100' : 'hover:bg-blue-50';

                    ?>
                    <a href="trangchu.php?page=ql_hop_thoai_chat&chat=<?= urlencode($id) ?>"
                       class="flex items-center p-2 rounded-lg cursor-pointer transition <?= $active ?>">
                        <?php foreach ($participants as $pid):
                            $u = $users[array_search($pid, array_column($users,'id'))]; ?>
                            <img src="<?= htmlspecialchars($u['avatar']) ?>" alt="<?= htmlspecialchars($u['name']) ?>"
                                 class="w-8 h-8 rounded-full mr-2">
                        <?php endforeach; ?>
                        <span class="font-medium text-gray-700"><?= htmlspecialchars($names) ?> (Chat #<?= $id ?>)</span>
                    </a>
                <?php } ?>
            </div>
        </div>
    </aside>

    <!-- Chat area -->
    <section class="flex-1 flex flex-col bg-white rounded-xl border shadow">
        <div class="bg-blue-500 text-white p-3 rounded-t-xl font-semibold text-lg flex justify-between items-center">
            <div>
                <?php
                if($currentChat){
                    $msgs = array_filter($tinnhan, fn($t)=>$t['id_chat']==$currentChat);
                    $participants = array_unique(array_map(
                        fn($m)=>$m['from']!=$currentUser['id']?$m['from']:$m['to'],
                        $msgs
                    ));
                    echo implode(' & ', array_map(
                        fn($pid)=>$users[array_search($pid, array_column($users,'id'))]['name'],
                        $participants
                    ));
                } else {
                    echo "Chọn hội thoại";
                }
                ?>
            </div>

            <?php if($currentChat): ?>
            <div x-data="{ open: false }" class="relative">
                <button @click="open = !open" class="text-white hover:text-gray-200" title="Cài đặt hội thoại">
                    <i class="fas fa-cog"></i>
                </button>

                <!-- Dropdown -->
                <div x-show="open" x-cloak @click.outside="open = false" x-transition
                     class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 py-2 z-20">
                    <div class="px-4 py-2 flex items-center space-x-2 border-b">
                        <i class="fas fa-cog text-gray-600"></i>
                        <div>
                            <p class="text-sm font-medium text-gray-800">Thiết lập hội thoại</p>
                            <p class="text-xs text-gray-500">Quản lý đoạn chat này</p>
                        </div>
                    </div>

                    <!-- Menu chức năng -->
                    <a href="delete_chat.php?id=<?= urlencode($currentChat) ?>"
                       onclick="return confirm('Bạn có chắc muốn xóa hội thoại này không?')"
                       class="block px-4 py-2 text-sm text-red-600 hover:bg-gray-100">
                       <i class="fas fa-trash-alt mr-2"></i> Xóa hội thoại
                    </a>
                    <a href="lock_chat.php?id=<?= urlencode($currentChat) ?>"
                       onclick="return confirm('Khóa hội thoại này?')"
                       class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                       <i class="fas fa-lock mr-2"></i> Khóa hội thoại
                    </a>
                    <a href="report_chat.php?id=<?= urlencode($currentChat) ?>"
                       onclick="return confirm('Gửi cảnh báo về hội thoại này?')"
                       class="block px-4 py-2 text-sm text-yellow-600 hover:bg-gray-100">
                       <i class="fas fa-exclamation-triangle mr-2"></i> Cảnh báo
                    </a>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <div class="flex-1 overflow-y-auto p-4 space-y-4 bg-gray-50">
            <?php if ($currentChat && !empty($msgs)): ?>
                <?php foreach ($msgs as $m):
                    $key = array_search($m['from'], array_column($users,'id'));
                    $sender = $key !== false ? $users[$key] : ['name'=>'Unknown','avatar'=>'/path/to/default.png'];

                    $isAdmin = ($m['from'] == $currentUser['id']);
                    $align = $isAdmin ? 'flex-row-reverse text-right' : 'flex-row text-left';
                    $bg = $isAdmin ? 'bg-blue-500 text-white' : 'bg-gray-200 text-gray-900';
                    $timestampColor = $isAdmin ? 'text-blue-200' : 'text-gray-500';
                ?>
                <div class="flex items-start <?= $align ?> gap-3">
                    <img src="<?= htmlspecialchars($sender['avatar']) ?>" alt="<?= htmlspecialchars($sender['name']) ?>"
                         class="w-10 h-10 rounded-full shadow flex-shrink-0">
                    <div class="px-4 py-3 <?= $bg ?> rounded-xl shadow max-w-[70%] break-words break-all">
                        <p class="font-semibold mb-1"><?= htmlspecialchars($sender['name']) ?></p>
                        <p class="mb-1"><?= nl2br(htmlspecialchars($m['content'])) ?></p>
                        <p class="text-xs <?= $timestampColor ?>"><?= htmlspecialchars($m['timestamp']) ?></p>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="text-center text-gray-400 mt-10">Chưa có tin nhắn nào.</p>
            <?php endif; ?>
        </div>

        <?php if($currentChat && $currentChat>=100): ?>
        <form class="p-4 flex gap-2 bg-white rounded-b-xl border-t" method="POST" action="" enctype="multipart/form-data">
            <!-- Ô nhập tin nhắn -->
            <input name="message" type="text" placeholder="Nhập tin nhắn..."
                   class="flex-1 p-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400">

            <!-- Nút gửi ảnh -->
            <label for="uploadImage"
                   class="flex items-center justify-center bg-gray-200 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-300 cursor-pointer">
                <i class="fas fa-image"></i>
            </label>
            <input id="uploadImage" name="image" type="file" accept="image/*" class="hidden">

            <!-- Nút chọn emoji -->
            <div class="relative">
                <button type="button" onclick="openEmojiPicker()" 
                        class="bg-yellow-400 text-white px-4 py-4 rounded-lg hover:bg-yellow-500 transition flex items-center justify-center">
                    <i class="fas fa-smile"></i>
                </button>

                <!-- Emoji Picker (ẩn mặc định) -->
                <div id="emojiPickerWrapper" 
                    class="hidden absolute bottom-14 right-0 bg-white border rounded-xl shadow-lg p-2 w-72 h-72 overflow-y-auto z-50">
                    <emoji-picker id="emojiPicker"></emoji-picker>
                </div>
            </div>

            <!-- Nút gửi icon trực tiếp -->
            <button type="submit" name="message" value="👍"
                    class="bg-green-500 text-white px-4 py-2 rounded-lg hover:bg-green-600 transition flex items-center justify-center">
                <i class="fas fa-thumbs-up"></i>
            </button>

            <!-- Nút gửi tin nhắn -->
            <button type="submit"
                    class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition flex items-center justify-center">
                <i class="fas fa-paper-plane"></i>
            </button>
        </form>
        <?php endif; ?>
    </section>
</main>

<script>

    const emojiWrapper = document.getElementById("emojiPickerWrapper");
    const emojiPicker = document.getElementById("emojiPicker");
    const inputMessage = document.querySelector("input[name='message']");

    function openEmojiPicker() {
        emojiWrapper.classList.toggle("hidden");
    }

    // Khi chọn emoji thì chèn vào ô input
    emojiPicker.addEventListener("emoji-click", event => {
        inputMessage.value += event.detail.unicode;
        // Ẩn picker sau khi chọn
        emojiWrapper.classList.add("hidden");
    });

    // Ẩn khi click ra ngoài
    document.addEventListener("click", (e) => {
        if (!emojiWrapper.contains(e.target) && !e.target.closest("button[onclick='openEmojiPicker()']")) {
            emojiWrapper.classList.add("hidden");
        }
    });
</script>

<script type="module" src="https://cdn.jsdelivr.net/npm/emoji-picker-element@^1/index.js"></script>

</body>
</html>
