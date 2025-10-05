<?php
    require_once "../../config/database.php";
    $pdo = ketnoicsdl();
    session_start();

    $currentUser = $_SESSION['id_nguoi_dung'];
    $currentChat = $_GET['chat'] ?? null;

    if (!$currentChat) exit;

    // Lấy tin nhắn
    $sql = "
        SELECT 
            ht.id AS id_hop_thoai,
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
        
        GROUP BY ht.id, info_g.ho_ten, info_n.ho_ten, ng.avt, nn.avt, ng.id, nn.id, tn.tg_gui, tn.noi_dung, tn.anh_tn, tn.id, tn.da_thu_hoi, tn.da_xoa
        ORDER BY tg_moi_nhat ASC
    ";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Nhóm chat
    $chatGroups = [];
    foreach ($messages as $m) {
        $pair = [$m['ten_nguoi_gui'], $m['ten_nguoi_nhan']];
        sort($pair);
        $chatKey = implode("_", $pair);
        $chatGroups[$chatKey][] = $m;
    }

    $msgs = $chatGroups[$currentChat] ?? [];

    ob_start();
    foreach ($msgs as $m):
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
                <p class="italic text-white"><i class="fas fa-info-circle"></i> Tin nhắn đã được thu hồi</p>
            <?php elseif ($m['da_xoa'] === 1): ?>
                <p class="italic text-white"><i class="fas fa-info-circle"></i> Tin nhắn đã được xóa</p>
            <?php elseif (!empty($m['anh_tn'])): ?>
                <p class="font-semibold mb-1"><?= htmlspecialchars($m['ten_nguoi_gui']) ?></p>
                <!-- Nếu là ảnh -->
                <img src="../../../storage/pictures/messages/<?= htmlspecialchars($m['anh_tn']) ?>" 
                    alt="Image" class="max-w-xs rounded-md shadow mb-1">
            <?php else: ?>
                <p class="font-semibold mb-1"><?= htmlspecialchars($m['ten_nguoi_gui']) ?></p>
                <p class="mb-1"><?= nl2br(htmlspecialchars($m['noi_dung'])) ?></p>
                <p class="text-xs <?= $timestampColor ?>"><?= date('H:i j/n/Y', strtotime($m['tg_gui'])) ?></p>
                <?php if ($isMe): ?>
                    <?php if ($hoursPassed <= 24): ?>
                        <!-- Nút thu hồi, hiển thị khi hover vào tin nhắn -->
                        <button onclick="revokeMessage('<?= $m['id_hop_thoai'] ?>')" 
                            class="absolute -left-8 top-16 text-gray-500 hover:text-gray-600 text-sm">
                            <i class="fas fa-undo"></i>
                        </button>
                    <?php else: ?>
                        <!-- Nút thu hồi, hiển thị khi hover vào tin nhắn -->
                        <button onclick="deleteMessage('<?= $m['id_hop_thoai'] ?>')" 
                            class="absolute -left-8 top-16 text-gray-500 hover:text-gray-600 text-sm">
                            <i class="fas fa-trash"></i>
                        </button>
                    <?php endif; ?>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
    <?php endforeach;
    echo ob_get_clean();
?>
