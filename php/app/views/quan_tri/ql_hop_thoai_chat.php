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
    <link rel="stylesheet" href="../../../public/assets/fontawesome/css/all.min.css">
    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body>

<!-- Header -->
<header class="flex items-center gap-4 bg-white shadow p-4">
    <img src="../../../public/assets/anhht/0/discussion.gif" alt="Chat" class="w-12 h-12">
    <h1 class="text-2xl font-bold text-gray-700">Quản lý hộp thoại chat</h1>
</header>

<main class="flex flex-1 p-4 gap-4 bg-gray-50">
    <!-- Danh sách chat -->
    <aside class="w-1/3 bg-white rounded-xl shadow overflow-y-auto h-[600px]">
        <div class="p-4">
            <h2 class="text-lg font-semibold mb-4 text-gray-700">Danh sách hội thoại</h2>
            <div class="space-y-2">
                <?php
                    $chatIds = [...array_unique(array_column($tinnhan, 'id_chat'))];
                    foreach ($chatIds as $id) {
                        $participants = array_unique(array_map(function($t) use ($currentUser, $id) {
                            if ($t['id_chat'] != $id) return null;
                            return $t['from'] == $currentUser['id'] ? $t['to'] : $t['from'];
                        }, $tinnhan));
                        $participants = array_filter($participants);
                        $names = implode(' & ', array_map(fn($pid) => $users[array_search($pid, array_column($users,'id'))]['name'], $participants));
                        $active = ($currentChat == $id) ? 'bg-blue-100' : 'hover:bg-blue-50';
                        echo "<a href='trangchu.php?page=ql_hop_thoai_chat&chat=$id' class='flex items-center p-2 rounded-lg cursor-pointer transition $active'>";
                        foreach ($participants as $pid) {
                            $u = $users[array_search($pid, array_column($users,'id'))];
                            echo "<img src='{$u['avatar']}' alt='{$u['name']}' class='w-8 h-8 rounded-full mr-2'>";
                        }
                        echo "<span class='font-medium text-gray-700'>$names (Chat #$id)</span></a>";
                    }
                ?>
            </div>
            <div x-data="{ openForm: false }" class="relative">
                <!-- Nút tạo hội thoại mới -->
                <a href="javascript:void(0)" @click="openForm = true" class="flex items-center justify-center mt-4 p-2 bg-green-500 text-white rounded-lg hover:bg-green-600 transition font-medium">
                    <i class="fas fa-plus mr-2"></i>
                    Tạo hội thoại mới
                </a>
                <!-- Popup form -->
                <div x-show="openForm" x-cloak class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" x-transition>
                    <div class="bg-white rounded-xl shadow-lg p-6 w-96">
                        <h2 class="text-lg font-semibold mb-4">Thông báo</h2>

                        <label class="block text-sm font-medium mb-1">Tiêu đề</label>
                        <input type="text" class="w-full outline-none border rounded-lg p-2 mb-3 focus:ring focus:border-blue-400" placeholder="Nhập tiêu đề...">
                        
                        <label class="block text-sm font-medium mb-1">Nội dung</label>
                        <textarea class="w-full border rounded-lg p-2 mb-3 outline-none focus:ring focus:border-blue-400" rows="3" placeholder="Nhập nội dung..."></textarea>
                        
                        <div class="flex justify-end space-x-2">
                            <button @click="openForm = false" class="px-4 py-2 rounded-lg border border-gray-300 hover:bg-gray-100">Hủy</button>
                            <button class="px-4 py-2 rounded-lg bg-blue-600 text-white hover:bg-blue-700">Gửi</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </aside>

    <!-- Chat area -->
    <section class="flex-1 flex flex-col bg-white rounded-xl border shadow">
        <div class="bg-blue-500 text-white p-3 rounded-t-xl font-semibold text-lg">
            <?php
            if($currentChat){
                $msgs = array_filter($tinnhan, fn($t)=>$t['id_chat']==$currentChat);
                $participants = array_unique(array_map(fn($m)=>$m['from']!=$currentUser['id']?$m['from']:$m['to'],$msgs));
                echo implode(' & ', array_map(fn($pid)=>$users[array_search($pid, array_column($users,'id'))]['name'],$participants));
            } else {
                echo "Chọn hội thoại";
            }
            ?>
        </div>

        <div class="flex-1 overflow-y-auto p-4 space-y-4 bg-gray-50">
            <?php if ($currentChat && !empty($msgs)): ?>
                <?php foreach ($msgs as $m): 
                    $key = array_search($m['from'], array_column($users,'id'));
                    $sender = $key !== false ? $users[$key] : [
                        'name' => 'Unknown',
                        'avatar' => '/path/to/default.png'
                    ];

                    $isAdmin = ($m['from'] == $currentUser['id']);
                    $align = $isAdmin ? 'flex-row-reverse text-right' : 'flex-row text-left';
                    $bg = $isAdmin ? 'bg-blue-500 text-white' : 'bg-gray-200 text-gray-900';
                    $timestampColor = $isAdmin ? 'text-blue-200' : 'text-gray-500';
                ?>
                    <div class="flex items-start <?= $align ?> gap-3">
                        <img src="<?= $sender['avatar'] ?>" alt="<?= htmlspecialchars($sender['name']) ?>" class="w-10 h-10 rounded-full shadow flex-shrink-0">
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
            <form class="p-4 flex gap-2 bg-white rounded-b-xl border-t" method="POST" action="">
                <input name="message" type="text" placeholder="Nhập tin nhắn..." class="flex-1 p-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400">
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition font-medium">Gửi</button>
            </form>
        <?php endif; ?>
    </section>
</main>
</body>
</html>
