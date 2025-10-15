<?php
    // =================================================================
    // PHẦN 1: XỬ LÝ DỮ LIỆU PHP (GIỮ NGUYÊN THEO YÊU CẦU)
    // =================================================================
    require_once "../../config/database.php";
    $pdo = ketnoicsdl();
    session_start();

    $currentUser = $_SESSION['id_nguoi_dung'];
    $currentChat = $_GET['chat'] ?? null;

    if (!$currentChat) exit;

    // Lấy tin nhắn (Logic gốc được giữ nguyên)
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

    // Nhóm chat (Logic gốc được giữ nguyên)
    $chatGroups = [];
    foreach ($messages as $m) {
        $pair = [$m['ten_nguoi_gui'], $m['ten_nguoi_nhan']];
        sort($pair);
        $chatKey = implode("_", $pair);
        $chatGroups[$chatKey][] = $m;
    }

    $msgs = $chatGroups[$currentChat] ?? [];

    // =================================================================
    // PHẦN 2: TẠO GIAO DIỆN HTML (ĐÃ CẬP NHẬT ĐỂ ĐỒNG BỘ)
    // =================================================================
    ob_start();
    foreach ($msgs as $m):
        $isMe = ($m['id_nguoi_gui'] == $currentUser);
        $align = $isMe ? 'justify-end' : 'justify-start';
        $bubbleClasses = $isMe ? 'bg-indigo-500 text-white chat-bubble-me' : 'bg-white text-slate-800 chat-bubble-other';
        $hoursPassed = (time() - strtotime($m['tg_gui'])) / 3600;
    ?>
        <div class="flex items-end gap-2.5 <?= $align ?> group">
            <?php if (!$isMe): ?>
                <img src="../../../storage/pictures/avt/<?= htmlspecialchars($m['avt_nguoi_gui']) ?>" class="w-8 h-8 rounded-full shadow flex-shrink-0 object-cover">
            <?php endif; ?>

            <div class="px-4 py-3 shadow-sm max-w-[70%] break-words relative <?= $bubbleClasses ?>">
                <?php if ($m['da_thu_hoi'] == 1): ?>
                    <p class="italic text-sm opacity-75"><i class="fas fa-ban"></i> Tin nhắn đã được thu hồi</p>
                <?php elseif ($m['da_xoa'] == 1): ?>
                    <p class="italic text-sm opacity-75"><i class="fas fa-ban"></i> Tin nhắn đã được xóa</p>
                <?php elseif (!empty($m['anh_tn'])): ?>
                    <img src="../../../storage/pictures/messages/<?= htmlspecialchars($m['anh_tn']) ?>" alt="Image" class="max-w-xs rounded-md shadow mb-1">
                <?php else: ?>
                    <p><?= ($m['noi_dung']) ?></p>
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
    <?php 
    endforeach;
    echo ob_get_clean();
?>