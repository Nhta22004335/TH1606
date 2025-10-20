<?php
// Đảm bảo session đã được khởi tạo
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../../config/database.php';
$pdo = ketnoicsdl();
function e($s){ return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8'); }

// 1. LẤY ID MÔI GIỚI ĐANG ĐĂNG NHẬP
$current_user_id = $_SESSION['id_nguoi_dung'] ?? null;

if (!$current_user_id) {
    echo "<p class='text-center text-red-500'>Lỗi: Bạn chưa đăng nhập.</p>";
    exit;
}

$sqlConversations = "
    SELECT 
        ht.id AS id_hop_thoai,
        
        -- Lấy ID của người dùng kia
        other_user.id AS id_nguoi_dung_khac,
        
        -- Lấy thông tin của người dùng kia
        info.ho_ten AS ten_nguoi_dung_khac,
        other_user.avt AS avatar_nguoi_dung_khac,
        
        -- Lấy tin nhắn cuối cùng
        last_msg.noi_dung AS noi_dung_cuoi,
        last_msg.tg_gui AS tg_gui_cuoi,
        
        -- Đếm số tin nhắn chưa đọc (là tin nhắn không phải do TÔI gửi)
        (
            SELECT COUNT(*)
            FROM tin_nhan unread
            WHERE unread.id_hop_thoai = ht.id
              AND unread.nguoi_gui <> :current_user_id
              AND unread.trang_thai = 'chua_doc'
        ) AS so_tin_chua_doc

    FROM hop_thoai ht
    
    -- JOIN để lấy ID và thông tin người kia
    JOIN nguoi_dung other_user ON other_user.id = (
        CASE
            WHEN ht.id_nguoi_1 = :current_user_id THEN ht.id_nguoi_2
            ELSE ht.id_nguoi_1
        END
    )
    JOIN info_nguoi_dung info ON info.id_nguoi_dung = other_user.id
    
    -- LEFT JOIN LATERAL để lấy tin nhắn cuối cùng
    LEFT JOIN LATERAL (
        SELECT noi_dung, tg_gui
        FROM tin_nhan tn
        WHERE tn.id_hop_thoai = ht.id
        ORDER BY tn.tg_gui DESC
        LIMIT 1
    ) last_msg ON TRUE

    -- Lọc các hộp thoại của người dùng hiện tại
    WHERE 
        (ht.id_nguoi_1 = :current_user_id OR ht.id_nguoi_2 = :current_user_id)
        AND ht.da_khoa = FALSE 
        AND ht.da_xoa = FALSE
        
    -- Sắp xếp theo tin nhắn mới nhất
    ORDER BY last_msg.tg_gui DESC NULLS LAST
";

$stmt = $pdo->prepare($sqlConversations);
$stmt->execute([':current_user_id' => $current_user_id]);
$conversations = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<div class="max-w-3xl mx-auto bg-white rounded-lg shadow-xl overflow-hidden" 
    style="height: calc(100vh - 120px); max-height: 490px; display: flex; flex-direction: column;">
    
    <div class="flex-shrink-0 p-4 border-b">
        <h1 class="text-2xl font-bold text-gray-900">Hộp thư</h1>
    </div>

    <div class="flex-grow overflow-y-auto">
        <?php if (empty($conversations)): ?>
            <p class="text-center text-gray-500 p-10">Bạn chưa có cuộc trò chuyện nào.</p>
        <?php else: ?>
            <div class="divide-y divide-gray-200">
                <?php foreach ($conversations as $con): ?>
                    <?php
                        $avatar = (e($con['avatar_nguoi_dung_khac']) ?: 'default_avatar.png');
                        $last_message = e($con['noi_dung_cuoi'] ?? 'Chưa có tin nhắn...');
                        $unread_count = (int)$con['so_tin_chua_doc'];
                        
                        $time_display = '';
                        if ($con['tg_gui_cuoi']) {
                            $time = strtotime($con['tg_gui_cuoi']);
                            // Hiển thị H:i nếu là hôm nay, ngược lại hiển thị d/m
                            if (date('Ymd', $time) == date('Ymd')) {
                                $time_display = date('H:i', $time);
                            } else {
                                $time_display = date('d/m/Y', $time);
                            }
                        }
                        
                        // Link đến trang chat chi tiết, gửi ID của người kia
                        $chat_link = "trangchu.php?page=../moi_gioi/chat&chat_with_id=" . e($con['id_nguoi_dung_khac']);
                    ?>

                    <a href="<?= $chat_link ?>" class="flex items-center p-4 hover:bg-gray-50 transition-colors duration-150">
                        <div class="flex-shrink-0 relative">
                            <img class="w-12 h-12 rounded-full object-cover" src="../../../../storage/pictures/avt/<?= $avatar ?>" alt="<?= e($con['ten_nguoi_dung_khac']) ?>">
                            <?php if ($unread_count > 0): ?>
                                <span class="absolute top-0 right-0 block h-3 w-3 rounded-full ring-2 ring-white bg-red-500"></span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="flex-grow ml-4 min-w-0">
                            <p class="text-base font-semibold text-gray-900 truncate <?= $unread_count > 0 ? 'font-bold' : '' ?>">
                                <?= e($con['ten_nguoi_dung_khac']) ?>
                            </p>
                            <p class="text-sm text-gray-600 truncate <?= $unread_count > 0 ? 'font-semibold text-gray-800' : '' ?>">
                                <?= e(mb_strimwidth($last_message, 0, 50, "...")) ?>
                            </p>
                        </div>
                        
                        <div class="flex-shrink-0 ml-4 flex flex-col items-end space-y-1">
                            <span class="text-xs text-gray-500"><?= $time_display ?></span>
                            <?php if ($unread_count > 0): ?>
                                <span class="px-2 py-0.5 text-xs font-semibold text-white bg-red-500 rounded-full">
                                    <?= $unread_count ?>
                                </span>
                            <?php endif; ?>
                        </div>
                    </a>

                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>