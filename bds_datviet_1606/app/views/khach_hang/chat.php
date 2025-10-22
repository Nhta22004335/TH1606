<?php
// Đảm bảo session đã được khởi tạo
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../../config/database.php';
$pdo = ketnoicsdl();
function e($s){ return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8'); }

// --- 1. LẤY ID & XÁC THỰC ---
$current_user_id = $_SESSION['id_nguoi_dung'] ?? null;
$other_user_id = $_GET['chat_with_id'] ?? null;

// Basic validation (as before)
if (!$current_user_id || !$other_user_id || $current_user_id == $other_user_id) {
    echo "<p class='text-center text-red-500'>Lỗi truy cập chat.</p>";
    exit;
}

// --- 2. TÌM HOẶC TẠO HỘP THOẠI ---
$user1 = min($current_user_id, $other_user_id);
$user2 = max($current_user_id, $other_user_id);
$stmtFind = $pdo->prepare("SELECT id FROM hop_thoai WHERE id_nguoi_1 = ? AND id_nguoi_2 = ? AND da_khoa = FALSE");
$stmtFind->execute([$user1, $user2]);
$id_hop_thoai = $stmtFind->fetchColumn();

if (!$id_hop_thoai) {
    try {
        $sqlInsert = "INSERT INTO hop_thoai (id_nguoi_1, id_nguoi_2) VALUES (?, ?) RETURNING id";
        $stmtInsert = $pdo->prepare($sqlInsert);
        $stmtInsert->execute([$user1, $user2]);
        $id_hop_thoai = $stmtInsert->fetchColumn();
    } catch (PDOException $e) {
        $stmtFind->execute([$user1, $user2]);
        $id_hop_thoai = $stmtFind->fetchColumn();
        if (!$id_hop_thoai) die("Lỗi: Không thể tìm/tạo hộp thoại. " . $e->getMessage());
    }
}

// --- 3. TẢI DỮ LIỆU BAN ĐẦU ĐỂ HIỂN THỊ ---
// Lấy thông tin người kia
$stmtUser = $pdo->prepare("SELECT info.ho_ten, u.avt FROM nguoi_dung u LEFT JOIN info_nguoi_dung info ON u.id = info.id_nguoi_dung WHERE u.id = ?");
$stmtUser->execute([$other_user_id]);
$other_user_info = $stmtUser->fetch(PDO::FETCH_ASSOC);
$other_user_name = $other_user_info['ho_ten'] ?? 'Khách hàng';
$other_user_avatar_filename = ($other_user_info['avt'] ?? 'default_avatar.png');

// Lấy avatar của bạn
$stmtMe = $pdo->prepare("SELECT avt FROM nguoi_dung WHERE id = ?");
$stmtMe->execute([$current_user_id]);
$my_avatar_filename = ($stmtMe->fetchColumn() ?? 'default_avatar.png');

// Tải tin nhắn ban đầu
$sqlFetchMsgs = "SELECT id, nguoi_gui, noi_dung, tg_gui FROM tin_nhan WHERE id_hop_thoai = ? AND trang_thai <> 'xoa' ORDER BY tg_gui ASC";
$stmtFetchMsgs = $pdo->prepare($sqlFetchMsgs);
$stmtFetchMsgs->execute([$id_hop_thoai]);
$initialMessages = $stmtFetchMsgs->fetchAll(PDO::FETCH_ASSOC);

// Đánh dấu tin nhắn đã đọc (chỉ chạy 1 lần khi load trang)
try {
    $sqlMarkRead = "UPDATE tin_nhan SET trang_thai = 'da_doc' WHERE id_hop_thoai = ? AND nguoi_gui = ? AND trang_thai = 'chua_doc'";
    $stmtMarkRead = $pdo->prepare($sqlMarkRead);
    $stmtMarkRead->execute([$id_hop_thoai, $other_user_id]);
} catch (PDOException $e) { /* Ignore */ }

$inbox_url = "trangchu.php?page=../chat/danh_sach_hop_thoai";
$avatar_base_url = "../../../../storage/pictures/avt/"; // Adjust path as needed
?>

<div class="flex flex-col h-[calc(100vh-150px)] max-h-[600px] w-full max-w-3xl mx-auto bg-white rounded-lg shadow-xl overflow-hidden">

    <div class="flex-shrink-0 flex items-center p-4 border-b bg-gray-50">
        <a href="<?= $inbox_url ?>" class="text-blue-600 hover:text-blue-800 p-2 rounded-full hover:bg-gray-200">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
        </a>
        <img class="w-10 h-10 rounded-full object-cover ml-3" src="<?= e($avatar_base_url . $other_user_avatar_filename) ?>" alt="<?= e($other_user_name) ?>">
        <div class="ml-3">
            <p class="text-lg font-semibold text-gray-900"><?= e($other_user_name) ?></p>
        </div>
    </div>

    <div id="chatBox" class="flex-grow p-4 space-y-4 overflow-y-auto bg-gray-100">
        <?php if (empty($initialMessages)): ?>
            <p id="noMessages" class="text-center text-gray-500">Chưa có tin nhắn nào. Hãy bắt đầu!</p>
        <?php else: ?>
             <?php foreach ($initialMessages as $msg): ?>
                 <?= generateMessageHTML($msg, $current_user_id, $my_avatar_filename, $other_user_avatar_filename, $other_user_name, $avatar_base_url) ?>
             <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <div class="flex-shrink-0 p-4 bg-white border-t border-gray-200">
        <form id="messageForm" class="flex space-x-3 items-center">
            <textarea id="messageInput" name="noi_dung"
                class="flex-1 block w-full h-10 px-3 py-2 outline-none rounded-lg border border-gray-300 focus:ring focus:ring-blue-500 focus:ring-opacity-50 resize-none"
                placeholder="Nhập tin nhắn..."></textarea>
            <button id="sendMessageBtn" type="submit"
                class="inline-flex items-center justify-center h-10 w-10 p-2 rounded-full bg-blue-600 text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-150 ease-in-out">
                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-8.707l-3-3a1 1 0 00-1.414 0l-3 3a1 1 0 001.414 1.414L9 9.414V13a1 1 0 102 0V9.414l1.293 1.293a1 1 0 001.414-1.414z"
                        clip-rule="evenodd"></path>
                </svg>
            </button>
        </form>
        <div id="sendError" class="text-red-500 text-sm mt-1"></div>
    </div>
</div>

<?php
// --- Helper function to generate message HTML ---
function generateMessageHTML($msg, $currentUserId, $myAvatar, $otherAvatar, $otherName, $avatarBaseUrl) {
    $time = date('H:i', strtotime($msg['tg_gui']));
    $content = nl2br(e($msg['noi_dung']));
    $output = '';

    if ($msg['nguoi_gui'] == $currentUserId) { // Your message
        $avatarUrl = e($avatarBaseUrl . $myAvatar);
        $output .= '<div class="flex justify-end items-end space-x-2" data-timestamp="'.e($msg['tg_gui']).'">'; // Add timestamp attribute
        $output .= '<div class="flex flex-col items-end">';
        $output .= '<div class="p-3 bg-blue-600 text-white rounded-lg max-w-xs lg:max-w-md break-words">';
        $output .= $content;
        $output .= '</div>';
        $output .= '<span class="text-xs text-gray-500 mt-1">'.$time.'</span>';
        $output .= '</div>';
        $output .= '<img class="w-8 h-8 rounded-full object-cover flex-shrink-0" src="'.$avatarUrl.'" alt="Bạn">';
        $output .= '</div>';
    } else { // Other user's message
        $avatarUrl = e($avatarBaseUrl . $otherAvatar);
        $output .= '<div class="flex justify-start items-end space-x-2" data-timestamp="'.e($msg['tg_gui']).'">'; // Add timestamp attribute
        $output .= '<img class="w-8 h-8 rounded-full object-cover flex-shrink-0" src="'.$avatarUrl.'" alt="'.e($otherName).'">';
        $output .= '<div class="flex flex-col items-start">';
        $output .= '<div class="p-3 bg-white text-gray-800 rounded-lg border max-w-xs lg:max-w-md break-words">';
        $output .= $content;
        $output .= '</div>';
        $output .= '<span class="text-xs text-gray-500 mt-1">'.$time.'</span>';
        $output .= '</div>';
        $output .= '</div>';
    }
    return $output;
}
?>

<script>
    // --- Configuration ---
    const chatBox = document.getElementById('chatBox');
    const messageForm = document.getElementById('messageForm');
    const messageInput = document.getElementById('messageInput');
    const sendMessageBtn = document.getElementById('sendMessageBtn');
    const sendErrorDiv = document.getElementById('sendError');
    const noMessagesP = document.getElementById('noMessages'); // Get the "no messages" paragraph
    const ajaxHandlerUrl = '../../models/ajax_chat_handler.php'; // IMPORTANT: Adjust path

    const conversationId = '<?= e($id_hop_thoai) ?>';
    const currentUserId = '<?= e($current_user_id) ?>';
    const otherUserId = '<?= e($other_user_id) ?>';
    const myAvatarFilename = '<?= e($my_avatar_filename) ?>';
    const otherAvatarFilename = '<?= e($other_user_avatar_filename) ?>';
    const otherUserName = '<?= e($other_user_name) ?>';
    const avatarBaseUrl = '<?= e($avatar_base_url) ?>';
    const pollingInterval = 3000; // Check for new messages every 3 seconds

    let lastMessageTimestamp = '<?= !empty($initialMessages) ? e(end($initialMessages)['tg_gui']) : '1970-01-01 00:00:00' ?>';
    let pollingTimer = null; // To store the interval ID

    // --- Helper Functions ---
    function autoResizeTextarea(elem) {
        elem.style.height = 'auto';
        elem.style.height = (elem.scrollHeight) + 'px';
    }

    function scrollToBottom() {
         // Only scroll if user is near the bottom
        if (chatBox.scrollHeight - chatBox.scrollTop - chatBox.clientHeight < 100) {
            chatBox.scrollTop = chatBox.scrollHeight;
        }
    }

    // Function to create HTML for a message (similar to PHP helper)
    function createMessageHTML(msg) {
        const time = new Date(msg.tg_gui).toLocaleTimeString('vi-VN', { hour: '2-digit', minute: '2-digit' });
        const content = msg.noi_dung.replace(/\n/g, '<br>'); // Handle newlines
        let output = '';

        if (msg.nguoi_gui == currentUserId) { // Your message
            const avatarUrl = avatarBaseUrl + myAvatarFilename;
            output += `<div class="flex justify-end items-end space-x-2" data-timestamp="${msg.tg_gui}">`;
            output += '<div class="flex flex-col items-end">';
            output += `<div class="p-3 bg-blue-600 text-white rounded-lg max-w-xs lg:max-w-md break-words">${content}</div>`;
            output += `<span class="text-xs text-gray-500 mt-1">${time}</span>`;
            output += '</div>';
            output += `<img class="w-8 h-8 rounded-full object-cover flex-shrink-0" src="${avatarUrl}" alt="Bạn">`;
            output += '</div>';
        } else { // Other user's message
            const avatarUrl = avatarBaseUrl + otherAvatarFilename;
            output += `<div class="flex justify-start items-end space-x-2" data-timestamp="${msg.tg_gui}">`;
            output += `<img class="w-8 h-8 rounded-full object-cover flex-shrink-0" src="${avatarUrl}" alt="${otherUserName}">`;
            output += '<div class="flex flex-col items-start">';
            output += `<div class="p-3 bg-white text-gray-800 rounded-lg border max-w-xs lg:max-w-md break-words">${content}</div>`;
            output += `<span class="text-xs text-gray-500 mt-1">${time}</span>`;
            output += '</div>';
            output += '</div>';
        }
        return output;
    }

    // Function to append message to UI
    function appendMessageToUI(msg) {
        // Remove "no messages" placeholder if it exists
        if (noMessagesP) {
            noMessagesP.remove();
        }
        const messageHTML = createMessageHTML(msg);
        chatBox.insertAdjacentHTML('beforeend', messageHTML);
        scrollToBottom();
    }


    // --- AJAX Functions ---
    async function sendMessage() {
        const messageContent = messageInput.value.trim();
        if (!messageContent) return; // Don't send empty messages

        // Disable input/button temporarily
        messageInput.disabled = true;
        sendMessageBtn.disabled = true;
        sendErrorDiv.textContent = ''; // Clear previous errors

        // Optimistic UI Update: Add message immediately
        const tempTimestamp = new Date().toISOString(); // Approximate time
        const optimisticMsg = {
             nguoi_gui: currentUserId,
             noi_dung: messageContent,
             tg_gui: tempTimestamp
        };
        appendMessageToUI(optimisticMsg);
        messageInput.value = ''; // Clear input
        autoResizeTextarea(messageInput); // Reset height


        const formData = new FormData();
        formData.append('action', 'send_message');
        formData.append('id_hop_thoai', conversationId);
        formData.append('noi_dung', messageContent);

        try {
            const response = await fetch(ajaxHandlerUrl, {
                method: 'POST',
                body: formData
            });
            const data = await response.json();

            if (!data.success) {
                console.error('Send error:', data.error);
                sendErrorDiv.textContent = `Lỗi: ${data.error}`;
                // Optional: Remove or mark the optimistic message as failed
                const optimisticElement = chatBox.querySelector(`[data-timestamp="${tempTimestamp}"]`);
                 if(optimisticElement) optimisticElement.style.opacity = '0.5'; // Example: make it faded
            } else {
                 // Update the timestamp of the sent message if needed
                 const sentElement = chatBox.querySelector(`[data-timestamp="${tempTimestamp}"]`);
                 if(sentElement) {
                     sentElement.dataset.timestamp = data.timestamp; // Update with actual server time
                 }
                // Update last message timestamp for fetching
                 if (data.timestamp > lastMessageTimestamp) {
                     lastMessageTimestamp = data.timestamp;
                 }
            }

        } catch (error) {
            console.error('Fetch error:', error);
            sendErrorDiv.textContent = 'Lỗi kết nối mạng khi gửi.';
             // Optional: Mark optimistic message as failed
             const optimisticElement = chatBox.querySelector(`[data-timestamp="${tempTimestamp}"]`);
             if(optimisticElement) optimisticElement.style.opacity = '0.5';
        } finally {
            // Re-enable input/button
            messageInput.disabled = false;
            sendMessageBtn.disabled = false;
            // Focus back on input only if it was successful maybe?
             // messageInput.focus();
        }
    }

    async function loadNewMessages() {
        try {
            // Construct URL with query parameters
             const url = new URL(ajaxHandlerUrl, window.location.origin); // Use base URL
             url.searchParams.append('action', 'get_new_messages');
             url.searchParams.append('id_hop_thoai', conversationId);
             url.searchParams.append('last_message_time', lastMessageTimestamp);
             url.searchParams.append('other_user_id', otherUserId); // Send other user ID for marking read

            const response = await fetch(url.toString()); // Fetch using GET
            const data = await response.json();

            if (data.success && data.messages.length > 0) {
                data.messages.forEach(msg => {
                    appendMessageToUI(msg);
                    // Update the timestamp of the very last message received
                    if (msg.tg_gui > lastMessageTimestamp) {
                         lastMessageTimestamp = msg.tg_gui;
                    }
                });
            } else if (!data.success) {
                console.error('Load error:', data.error);
                // Optionally stop polling on critical errors
                 // clearInterval(pollingTimer);
            }
        } catch (error) {
            console.error('Fetch error during load:', error);
            // Don't overwhelm with errors, maybe just log or show a subtle indicator
        }
    }

    // --- Event Listeners and Initialization ---
    messageForm.addEventListener('submit', (event) => {
        event.preventDefault(); // Prevent traditional form submission
        sendMessage();
    });

    // Handle Enter key press in textarea (optional: Shift+Enter for newline)
     messageInput.addEventListener('keydown', (event) => {
         if (event.key === 'Enter' && !event.shiftKey) {
             event.preventDefault(); // Prevent newline
             sendMessageBtn.click(); // Trigger form submission
         }
     });

    // Initial setup
    (function() {
        scrollToBottom(); // Scroll down on initial load
        autoResizeTextarea(messageInput); // Set initial textarea height

        // Start polling for new messages
        pollingTimer = setInterval(loadNewMessages, pollingInterval);

         // Optional: Pause polling when tab is inactive
         document.addEventListener('visibilitychange', () => {
             if (document.hidden) {
                 clearInterval(pollingTimer);
                 pollingTimer = null;
             } else if (!pollingTimer) { // Resume only if it was stopped
                 loadNewMessages(); // Check immediately on becoming visible
                 pollingTimer = setInterval(loadNewMessages, pollingInterval);
             }
         });

    })();
</script>