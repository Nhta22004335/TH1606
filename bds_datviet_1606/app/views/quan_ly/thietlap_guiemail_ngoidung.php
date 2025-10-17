<?php
// PHẦN PHP GIỜ ĐÂY RẤT GỌN GÀNG
require_once "../../../config/database.php";

$pdo = ketnoicsdl();

// Chỉ cần lấy thông tin để hiển thị ban đầu
$email_nguoi_nhan = $_GET['email'] ?? '';
if (!$email_nguoi_nhan) {
    die("Lỗi: Không tìm thấy email người nhận.");
}

$stmt = $pdo->prepare("SELECT ho_ten FROM info_nguoi_dung i JOIN nguoi_dung nd ON i.id_nguoi_dung = nd.id WHERE nd.email = ?");
$stmt->execute([$email_nguoi_nhan]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
$ho_ten_nguoi_nhan = $user['ho_ten'] ?? 'Không rõ';
?>

<!DOCTYPE html>
<html lang="vi" class="bg-gray-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Soạn Email</title>
</head>
<body>

<div class="min-h-screen flex items-center justify-center p-4">
    <div class="max-w-2xl w-full mx-auto p-8 bg-white rounded-xl shadow-lg border border-gray-200">
        <div class="flex items-center mb-6 border-b border-gray-200 pb-5">
            <div class="w-12 h-12 flex-shrink-0 bg-indigo-100 text-indigo-600 rounded-full flex items-center justify-center">
                <i class="fas fa-paper-plane text-xl"></i>
            </div>
            <div class="ml-4">
                <h1 class="text-xl font-bold text-gray-800">Soạn và Gửi Email</h1>
                <p class="text-sm mt-1 text-gray-500">
                    Gửi tới: <span class="font-semibold"><?= htmlspecialchars($ho_ten_nguoi_nhan) ?></span>
                </p>
            </div>
        </div>

        <div id="response-message" class="mb-5"></div>

        <form id="email-form" class="space-y-6">
            <input type="hidden" name="email_nguoi_nhan" value="<?= htmlspecialchars($email_nguoi_nhan) ?>">
            <div>
                <label for="tieu_de" class="block mb-2 text-sm font-medium text-gray-700">Tiêu đề</label>
                <input type="text" id="tieu_de" name="tieu_de" class="transition-colors duration-300 bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block w-full p-2.5 outline-none" placeholder="Nhập tiêu đề email..." required>
            </div>
            <div>
                <label for="noi_dung" class="block mb-2 text-sm font-medium text-gray-700">Nội dung</label>
                <textarea id="noi_dung" name="noi_dung" rows="10" class="transition-colors duration-300 block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 outline-none" placeholder="Soạn nội dung của bạn ở đây..." required></textarea>
            </div>
            <div class="flex items-center justify-end space-x-4 pt-4 border-t border-gray-200">
                <a href="trangchu.php?page=quanly_nguoidung_qt" class="px-5 py-2.5 text-sm font-medium text-gray-700 bg-white rounded-lg border border-gray-300 hover:bg-gray-100">
                    Quay lại
                </a>
                <button type="submit" id="submit-button" class="text-white bg-indigo-600 hover:bg-indigo-700 focus:ring-4 focus:ring-indigo-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center w-36">
                    <span class="button-text"><i class="fas fa-paper-plane mr-2"></i>Gửi Email</span>
                    <span class="button-loader hidden"><i class="fas fa-spinner fa-spin"></i> Đang gửi...</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const emailForm = document.getElementById('email-form');
    const submitButton = document.getElementById('submit-button');
    const buttonText = submitButton.querySelector('.button-text');
    const buttonLoader = submitButton.querySelector('.button-loader');
    const responseMessageContainer = document.getElementById('response-message');

    emailForm.addEventListener('submit', async function(event) {
        event.preventDefault(); // Ngăn form gửi theo cách truyền thống

        // 1. Hiển thị trạng thái đang gửi
        submitButton.disabled = true;
        buttonText.classList.add('hidden');
        buttonLoader.classList.remove('hidden');
        responseMessageContainer.innerHTML = ''; // Xóa thông báo cũ

        // 2. Lấy dữ liệu từ form
        const formData = new FormData(emailForm);

        try {
            // 3. Gửi yêu cầu fetch đến file xử lý
            const response = await fetch('../../models/xuly_thietlap_guiemail_nguoidung.php', {
                method: 'POST',
                body: formData
            });

            if (!response.ok) {
                throw new Error(`Lỗi mạng: ${response.statusText}`);
            }

            const result = await response.json(); // Chuyển đổi phản hồi thành JSON

            // 4. Hiển thị kết quả
            let messageClass = '';
            let messageIcon = '';

            if (result.status === 'success') {
                messageClass = 'bg-green-100 text-green-800';
                messageIcon = '<i class="fas fa-check-circle mr-3"></i>';
                emailForm.reset(); // Xóa nội dung form nếu gửi thành công
            } else {
                messageClass = 'bg-red-100 text-red-800';
                messageIcon = '<i class="fas fa-exclamation-triangle mr-3"></i>';
            }

            responseMessageContainer.innerHTML = `
                <div class="flex items-center p-4 text-sm rounded-lg ${messageClass}" role="alert">
                    ${messageIcon}
                    <span>${result.message}</span>
                </div>
            `;

        } catch (error) {
            // Xử lý lỗi kết nối hoặc lỗi server
            console.error('Lỗi khi gửi form:', error);
            responseMessageContainer.innerHTML = `
                <div class="flex items-center p-4 text-sm rounded-lg bg-red-100 text-red-800" role="alert">
                    <i class="fas fa-exclamation-triangle mr-3"></i>
                    <span>Không thể kết nối đến máy chủ. Vui lòng thử lại.</span>
                </div>
            `;
        } finally {
            // 5. Khôi phục lại nút bấm sau khi hoàn tất
            submitButton.disabled = false;
            buttonText.classList.remove('hidden');
            buttonLoader.classList.add('hidden');
        }
    });
});
</script>

</body>
</html>