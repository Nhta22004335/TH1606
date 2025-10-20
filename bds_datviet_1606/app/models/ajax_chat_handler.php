<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../../config/database.php';
$pdo = ketnoicsdl();

// Set header to return JSON
header('Content-Type: application/json');

// --- Basic Security & Input Validation ---
$current_user_id = $_SESSION['id_nguoi_dung'] ?? null;
if (!$current_user_id) {
    echo json_encode(['success' => false, 'error' => 'Not authenticated']);
    exit;
}

$action = $_REQUEST['action'] ?? null; // Use $_REQUEST to handle both GET and POST
$id_hop_thoai = $_REQUEST['id_hop_thoai'] ?? null;

if (!$id_hop_thoai || !$action) {
    echo json_encode(['success' => false, 'error' => 'Missing parameters']);
    exit;
}

// Verify user is part of this conversation
$stmtVerify = $pdo->prepare("SELECT COUNT(*) FROM hop_thoai 
                             WHERE id = ? AND (id_nguoi_1 = ? OR id_nguoi_2 = ?)");
$stmtVerify->execute([$id_hop_thoai, $current_user_id, $current_user_id]);
if ($stmtVerify->fetchColumn() == 0) {
     echo json_encode(['success' => false, 'error' => 'Unauthorized conversation access']);
    exit;
}


// --- Action: Send Message ---
if ($action === 'send_message' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $noi_dung = trim($_POST['noi_dung'] ?? '');

    if (empty($noi_dung)) {
        echo json_encode(['success' => false, 'error' => 'Message cannot be empty']);
        exit;
    }

    try {
        $sqlMsg = "INSERT INTO tin_nhan (id_hop_thoai, nguoi_gui, noi_dung) 
                   VALUES (?, ?, ?) RETURNING id, tg_gui"; // Return ID and timestamp
        $stmtMsg = $pdo->prepare($sqlMsg);
        $stmtMsg->execute([$id_hop_thoai, $current_user_id, $noi_dung]);
        $newMessage = $stmtMsg->fetch(PDO::FETCH_ASSOC);

        if ($newMessage) {
             // Return success and the timestamp of the new message
             echo json_encode([
                 'success' => true,
                 'message_id' => $newMessage['id'],
                 'timestamp' => $newMessage['tg_gui'] // ISO 8601 format
             ]);
        } else {
             echo json_encode(['success' => false, 'error' => 'Failed to save message']);
        }
        exit;
    } catch (PDOException $e) {
        // Log the detailed error server-side if needed
        error_log("Chat Send Error: " . $e->getMessage());
        echo json_encode(['success' => false, 'error' => 'Database error while sending']);
        exit;
    }
}

// --- Action: Get New Messages ---
if ($action === 'get_new_messages' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    // Get the timestamp of the last message the client has
    // Use a default far in the past if it's the first load via AJAX
    $last_message_time = $_GET['last_message_time'] ?? '1970-01-01 00:00:00';
    $other_user_id = $_GET['other_user_id'] ?? null; // Needed for marking as read

     if (!$other_user_id) {
        echo json_encode(['success' => false, 'error' => 'Missing other user ID']);
        exit;
    }

    try {
        // Fetch new messages
        $sqlFetch = "SELECT id, nguoi_gui, noi_dung, tg_gui 
                     FROM tin_nhan 
                     WHERE id_hop_thoai = ? AND tg_gui > ? AND trang_thai <> 'xoa'
                     ORDER BY tg_gui ASC";
        $stmtFetch = $pdo->prepare($sqlFetch);
        $stmtFetch->execute([$id_hop_thoai, $last_message_time]);
        $newMessages = $stmtFetch->fetchAll(PDO::FETCH_ASSOC);

        // Mark fetched messages from the other user as read
        $readMessageIds = [];
        foreach ($newMessages as $msg) {
            if ($msg['nguoi_gui'] == $other_user_id) {
                $readMessageIds[] = $msg['id'];
            }
        }

        if (!empty($readMessageIds)) {
            // Use IN clause which requires placeholders based on the count
            $placeholders = rtrim(str_repeat('?,', count($readMessageIds)), ',');
            $sqlMarkRead = "UPDATE tin_nhan SET trang_thai = 'da_doc' 
                            WHERE id_hop_thoai = ? AND id IN ($placeholders) AND nguoi_gui = ? AND trang_thai = 'chua_doc'";
            $stmtMarkRead = $pdo->prepare($sqlMarkRead);
            $params = array_merge([$id_hop_thoai], $readMessageIds, [$other_user_id]);
            $stmtMarkRead->execute($params);
        }

        // Return new messages
        echo json_encode(['success' => true, 'messages' => $newMessages]);
        exit;

    } catch (PDOException $e) {
         error_log("Chat Fetch Error: " . $e->getMessage());
        echo json_encode(['success' => false, 'error' => 'Database error while fetching']);
        exit;
    }
}

// --- Fallback for unknown actions ---
echo json_encode(['success' => false, 'error' => 'Invalid action']);
exit;
?>