<?php
require_once "../../../config/database.php";

// Kết nối CSDL
try {
    $pdo = ketnoicsdl();
} catch (PDOException $e) {
    die("Lỗi kết nối CSDL: " . $e->getMessage());
}

// Lấy thông tin người nhận để hiển thị ban đầu
$email_nguoi_nhan = $_GET['email'] ?? '';
if (!$email_nguoi_nhan) {
    die("Lỗi: Không tìm thấy email người nhận.");
}

// Truy vấn để lấy tên và avatar (nếu có)
$stmt = $pdo->prepare("
    SELECT i.ho_ten, nd.avt 
    FROM nguoi_dung nd
    LEFT JOIN info_nguoi_dung i ON nd.id = i.id_nguoi_dung 
    WHERE nd.email = ?
");
$stmt->execute([$email_nguoi_nhan]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

$ho_ten_nguoi_nhan = $user['ho_ten'] ?? 'Không rõ';
// Xử lý đường dẫn avatar, nếu không có thì dùng placeholder
$avatar_url = !empty($user['avt'])
    ? '../../../storage/pictures/avt/' . $user['avt']
    : 'https://ui-avatars.com/api/?name=' . urlencode($ho_ten_nguoi_nhan) . '&background=dbeafe&color=3730a3';

?>
<!DOCTYPE html>
<html lang="vi" class="h-full bg-gray-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Soạn Thư Mới</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        *:focus { outline: none; }
    </style>
</head>
<body class="h-full flex items-center justify-center">

    <div class="max-w-xl w-full mx-auto bg-white rounded-2xl shadow-2xl shadow-slate-300/30 overflow-hidden flex flex-col pt-4">
        
        <form id="email-form" class="flex-grow flex flex-col">
            <div class="p-4 space-y-4">
                <div id="response-message"></div>
                
                <div class="flex items-center space-x-2 text-sm border-b border-gray-200 pb-2">
                    <label class="text-gray-500">Tới:</label>
                    <div class="inline-flex items-center bg-indigo-50 rounded-full py-1 pl-2 pr-3">
                        <img class="w-6 h-6 rounded-full object-cover mr-2" src="<?= htmlspecialchars($avatar_url) ?>" alt="Avatar">
                        <span class="font-semibold text-indigo-800"><?= htmlspecialchars($ho_ten_nguoi_nhan) ?></span>
                        <span class="text-indigo-600 ml-1">(<?= htmlspecialchars($email_nguoi_nhan) ?>)</span>
                    </div>
                </div>

                <div class="border-b border-gray-200">
                    <input type="text" id="tieu_de" name="tieu_de" class="w-full py-3 text-sm text-gray-900 placeholder-gray-400 border-0 focus:ring-0" placeholder="Thêm tiêu đề" required>
                </div>

                <div class="flex-grow">
                    <textarea 
                        id="noi_dung" 
                        name="noi_dung" 
                        rows="8" 
                        class="w-full h-full py-3 text-sm text-gray-800 placeholder-gray-400 border-0 focus:ring-0 resize-none" 
                        placeholder="Soạn nội dung email..." 
                        required
                    ></textarea>
                </div>
            </div>

            <div class="mt-auto p-4 bg-gray-50 border-t border-gray-200 flex items-center justify-between">
                <a href="trangchu.php?page=quanly_nguoidung" class="px-5 py-2 text-sm font-medium text-white bg-red-500 rounded-lg border border-gray-300 hover:bg-red-700 transition-colors">
                    <i class="fas fa-times mr-2"></i>Hủy
                </a>
                <div class="flex items-center space-x-4">
                    <input type="hidden" name="email_nguoi_nhan" value="<?= htmlspecialchars($email_nguoi_nhan) ?>">
                    <button type="submit" id="submit-button" class="inline-flex items-center justify-center text-white bg-indigo-600 hover:bg-indigo-700 font-medium rounded-lg text-sm px-6 py-2.5 text-center w-36 transition-colors disabled:bg-indigo-400">
                        <span class="button-text"><i class="fas fa-paper-plane mr-2"></i>Gửi</span>
                        <span class="button-loader hidden"><i class="fas fa-spinner fa-spin mr-2"></i>Đang gửi</span>
                    </button>
                </div>
            </div>
        </form>
    </div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // === PHẦN MỚI: TEXTAREA TỰ ĐỘNG CO DÃN ===
    const textarea = document.getElementById('noi_dung');
    textarea.addEventListener('input', () => {
        textarea.style.height = 'auto'; // Reset chiều cao
        textarea.style.height = (textarea.scrollHeight) + 'px'; // Set chiều cao mới dựa trên nội dung
    });

    // === PHẦN CŨ (GIỮ NGUYÊN LOGIC): XỬ LÝ GỬI FORM ===
    const emailForm = document.getElementById('email-form');
    const submitButton = document.getElementById('submit-button');
    const buttonText = submitButton.querySelector('.button-text');
    const buttonLoader = submitButton.querySelector('.button-loader');
    const responseMessageContainer = document.getElementById('response-message');

    emailForm.addEventListener('submit', async function(event) {
        event.preventDefault();

        // 1. Hiển thị trạng thái đang gửi
        submitButton.disabled = true;
        buttonText.classList.add('hidden');
        buttonLoader.classList.remove('hidden');
        responseMessageContainer.innerHTML = '';

        const formData = new FormData(emailForm);

        try {
            // 2. Gửi yêu cầu fetch
            const response = await fetch('../../models/xuly_thietlap_guiemail_nguoidung.php', {
                method: 'POST',
                body: formData
            });

            if (!response.ok) {
                throw new Error(`Lỗi mạng: ${response.statusText}`);
            }
            
            const result = await response.json();

            // 3. Hiển thị kết quả
            const messageClass = result.status === 'success' 
                ? 'bg-green-100 text-green-800' 
                : 'bg-red-100 text-red-800';
            const messageIcon = result.status === 'success'
                ? '<i class="fas fa-check-circle mr-3"></i>'
                : '<i class="fas fa-exclamation-triangle mr-3"></i>';

            responseMessageContainer.innerHTML = `
                <div class="flex items-center p-4 text-sm rounded-lg ${messageClass}" role="alert">
                    ${messageIcon}
                    <span>${result.message}</span>
                </div>
            `;
            
            // Xóa form và co nhỏ textarea nếu thành công
            if (result.status === 'success') {
                document.getElementById('tieu_de').value = '';
                textarea.value = '';
                textarea.style.height = 'auto';
            }
        } catch (error) {
            console.error('Lỗi khi gửi form:', error);
            responseMessageContainer.innerHTML = `
                <div class="flex items-center p-4 text-sm rounded-lg bg-red-100 text-red-800" role="alert">
                    <i class="fas fa-exclamation-triangle mr-3"></i>
                    <span>Không thể kết nối đến máy chủ. Vui lòng thử lại.</span>
                </div>
            `;
        } finally {
            // 4. Khôi phục lại nút bấm
            submitButton.disabled = false;
            buttonText.classList.remove('hidden');
            buttonLoader.classList.add('hidden');
        }
    });
});
</script>

</body>
</html>