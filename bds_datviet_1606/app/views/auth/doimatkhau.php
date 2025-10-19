<!DOCTYPE html>
<html lang="vi" class="bg-gray-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đổi Mật khẩu</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
    <style>
        .loader {
            border: 2px solid #f3f3f3;
            border-top: 2px solid #3b82f6;
            border-radius: 50%;
            width: 1rem;
            height: 1rem;
            animation: spin 1s linear infinite;
            display: inline-block;
            margin-right: 0.5rem;
            vertical-align: middle;
        }
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
    </style>
</head>
<body class="flex items-center justify-center min-h-screen p-4 bg-cover bg-center" style="background-image: url('https://images.unsplash.com/photo-1505691723518-36a5ac3be353?auto=format&fit=crop&w=1350&q=80');">

<div class="bg-white shadow-2xl rounded-2xl w-full max-w-md mx-auto p-6 sm:p-8 relative">
    <a href="trangchu.php" title="Quay về trang chủ" class="absolute top-6 left-6 text-gray-400 hover:text-blue-600 transition-colors duration-300">
        <i class="fa-solid fa-arrow-left fa-lg"></i>
    </a>

    <div class="flex justify-center mb-2">
        <img src="../../../public/images/datviet.png" alt="Logo hệ thống" class="w-16 h-16 sm:w-20 sm:h-20 object-contain">
    </div>
    <p class="text-center text-gray-500 italic text-sm mb-2">Không gian sống lý tưởng cho bạn</p>
    <h1 class="text-xl sm:text-2xl font-bold text-center text-blue-600 mb-6">Đổi mật khẩu hệ thống</h1>

    <div id="message" class="hidden p-3 mb-4 rounded-lg text-sm text-center"></div>

    <form id="formDoiMatKhau" class="space-y-2">
        
        <input type="hidden" id="idnguoidung" name="idnguoidung" value="<?php echo htmlspecialchars($idnguoidung ?? ''); ?>">

        <div>
            <label for="tendangnhap" class="sr-only">Tên đăng nhập</label>
            <div class="relative">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                    <i class="fa-solid fa-user text-gray-400"></i>
                </div>
                <input type="text" id="tendangnhap" name="tendangnhap" placeholder="Tên đăng nhập" autocomplete="off" required
                       class="w-full border-2 border-gray-200 rounded-lg pl-10 pr-4 py-2.5 text-base focus:outline-none focus:border-blue-500 transition">
            </div>
            <div id="tendangnhapCheck" class="mt-1 text-xs h-4 flex items-center"></div>
        </div>

        <div>
            <label for="matkhaucu" class="sr-only">Mật khẩu cũ</label>
             <div class="relative">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                    <i class="fa-solid fa-lock text-gray-400"></i>
                </div>
                <input type="password" id="matkhaucu" name="matkhaucu" placeholder="Mật khẩu cũ" autocomplete="off" required
                    class="w-full border-2 border-gray-200 rounded-lg pl-10 pr-4 py-2.5 text-base focus:outline-none focus:border-blue-500 transition">
            </div>
            <div id="matkhaucuCheck" class="mt-1 text-xs h-4 flex items-center"></div>
        </div>
        
        <div>
            <label for="matkhaumoi" class="sr-only">Mật khẩu mới</label>
            <div class="relative">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                    <i class="fa-solid fa-key text-gray-400"></i>
                </div>
                <input type="password" id="matkhaumoi" name="matkhaumoi" placeholder="Mật khẩu mới" autocomplete="new-password" required
                       class="w-full border-2 border-gray-200 rounded-lg pl-10 pr-4 py-2.5 text-base focus:outline-none focus:border-blue-500 transition disabled:bg-gray-100 disabled:cursor-not-allowed" disabled>
            </div>
            <div id="matkhaumoiStrengthCheck" class="mt-1 text-xs h-4 flex items-center text-gray-500"></div>
        </div>
        
        <div>
            <label for="xacnhanmatkhau" class="sr-only">Xác nhận Mật khẩu mới</label>
            <div class="relative">
                 <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                    <i class="fa-solid fa-circle-check text-gray-400"></i>
                </div>
                <input type="password" id="xacnhanmatkhau" name="xacnhanmatkhau" placeholder="Xác nhận Mật khẩu mới" autocomplete="new-password" required
                       class="w-full border-2 border-gray-200 rounded-lg pl-10 pr-4 py-2.5 text-base focus:outline-none focus:border-blue-500 transition disabled:bg-gray-100 disabled:cursor-not-allowed" disabled>
            </div>
            <div id="matkhaumoiMatchCheck" class="mt-1 text-xs h-4 flex items-center"></div>
        </div>

        <button type="submit" id="btnThucHien" class="w-full bg-blue-600 text-white text-base font-semibold py-3 rounded-lg hover:bg-blue-700 transition duration-300 disabled:bg-gray-400 disabled:cursor-not-allowed flex items-center justify-center" disabled>
            Đổi Mật khẩu
        </button>
    </form>

    <p class="text-center text-xs text-gray-400 mt-6">© 2025 Đất Việt BDS. Quản trị BĐS: Minh bạch, Hiệu quả, Tăng trưởng.</p>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('formDoiMatKhau');
        const btn = document.getElementById('btnThucHien');
        const messageDiv = document.getElementById('message');
        const inputs = {
            tendangnhap: document.getElementById('tendangnhap'),
            matkhaucu: document.getElementById('matkhaucu'),
            matkhaumoi: document.getElementById('matkhaumoi'),
            xacnhanmatkhau: document.getElementById('xacnhanmatkhau')
        };
        const messages = {
            tendangnhap: document.getElementById('tendangnhapCheck'),
            matkhaucu: document.getElementById('matkhaucuCheck'),
            matkhaumoiStrength: document.getElementById('matkhaumoiStrengthCheck'),
            matkhaumoiMatch: document.getElementById('matkhaumoiMatchCheck')
        };

        let validationState = { tendangnhapValid: false, matkhaucuValid: false, matkhaumoiStrength: false, matkhaumoiMatch: false };
        let tendangnhapDebounce;

        function hienThiThongBao(element, message, type) {
            element.textContent = message;
            element.className = 'mt-1 text-xs h-4 flex items-center'; // Reset
            if (type === 'success') element.classList.add('text-green-600');
            else if (type === 'error') element.classList.add('text-red-600');
            else if (type === 'loading') element.innerHTML = `<span class="loader"></span> ${message}`;
        }
        
        function updateSubmitButtonState() {
            btn.disabled = !Object.values(validationState).every(Boolean);
        }

        function toggleNewPasswordFields(enable) {
            inputs.matkhaumoi.disabled = !enable;
            inputs.xacnhanmatkhau.disabled = !enable;
            if (!enable) {
                inputs.matkhaumoi.value = '';
                inputs.xacnhanmatkhau.value = '';
                validationState.matkhaumoiStrength = false;
                validationState.matkhaumoiMatch = false;
                messages.matkhaumoiStrength.innerHTML = '';
                messages.matkhaumoiMatch.innerHTML = '';
            }
        }

        async function validateUsername() {
            const username = inputs.tendangnhap.value.trim();
            if (!username) {
                validationState.tendangnhapValid = false;
                hienThiThongBao(messages.tendangnhap, '', 'info');
                updateSubmitButtonState();
                return;
            }
            hienThiThongBao(messages.tendangnhap, 'Đang kiểm tra...', 'loading');
            try {
                const response = await fetch('../../models/auth/kiemtratendangnhap.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ tendangnhap: username })
                });
                const result = await response.json();
                validationState.tendangnhapValid = result.success;
                hienThiThongBao(messages.tendangnhap, result.success ? 'Tên đăng nhập hợp lệ.' : result.message, result.success ? 'success' : 'error');
            } catch (error) {
                validationState.tendangnhapValid = false;
                hienThiThongBao(messages.tendangnhap, 'Lỗi kết nối.', 'error');
            }
            updateSubmitButtonState();
        }

        async function validateOldPassword() {
            const password = inputs.matkhaucu.value;
            const username = inputs.tendangnhap.value;
            if (!password || !username) {
                validationState.matkhaucuValid = false;
                toggleNewPasswordFields(false);
                updateSubmitButtonState();
                return;
            }
            hienThiThongBao(messages.matkhaucu, 'Đang kiểm tra...', 'loading');
            try {
                const response = await fetch('../../models/auth/kiemtramatkhaucu.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ matkhaucu: password, tendangnhap: username })
                });
                const result = await response.json();
                validationState.matkhaucuValid = result.success;
                hienThiThongBao(messages.matkhaucu, result.success ? 'Mật khẩu cũ chính xác.' : result.message, result.success ? 'success' : 'error');
                toggleNewPasswordFields(result.success);
                if (result.success) {
                    inputs.matkhaumoi.focus();
                }
            } catch (error) {
                validationState.matkhaucuValid = false;
                hienThiThongBao(messages.matkhaucu, 'Lỗi kết nối.', 'error');
                toggleNewPasswordFields(false);
            }
            updateSubmitButtonState();
        }

        function validateNewPassword() {
            const mk = inputs.matkhaumoi.value;
            const rules = [
                { test: /^.{8,}$/, msg: 'ít nhất 8 ký tự' },
                { test: /[a-z]/, msg: 'chữ thường' },
                { test: /[A-Z]/, msg: 'chữ HOA' },
                { test: /\d/, msg: 'số' },
                { test: /[\W_]/, msg: 'ký tự đặc biệt' }
            ];
            const failedRules = rules.filter(rule => !rule.test.test(mk));
            validationState.matkhaumoiStrength = failedRules.length === 0;

            if (!mk) {
                hienThiThongBao(messages.matkhaumoiStrength, '', 'info');
            } else if (failedRules.length > 0) {
                hienThiThongBao(messages.matkhaumoiStrength, `Yếu: thiếu ${failedRules.map(r => r.msg).join(', ')}.`, 'error');
            } else {
                hienThiThongBao(messages.matkhaumoiStrength, 'Mật khẩu đủ mạnh.', 'success');
            }
            
            const mk2 = inputs.xacnhanmatkhau.value;
            if (mk && mk2) {
                const areMatching = mk === mk2;
                validationState.matkhaumoiMatch = areMatching;
                hienThiThongBao(messages.matkhaumoiMatch, areMatching ? 'Mật khẩu khớp.' : 'Mật khẩu không khớp.', areMatching ? 'success' : 'error');
            } else {
                validationState.matkhaumoiMatch = false;
                messages.matkhaumoiMatch.innerHTML = '';
            }
            updateSubmitButtonState();
        }

        inputs.tendangnhap.addEventListener('input', () => {
            clearTimeout(tendangnhapDebounce);
            validationState.tendangnhapValid = false;
            updateSubmitButtonState();
            hienThiThongBao(messages.tendangnhap, 'Đang kiểm tra...', 'loading');
            tendangnhapDebounce = setTimeout(validateUsername, 800);
        });
        
        inputs.matkhaucu.addEventListener('input', () => {
            validationState.matkhaucuValid = false;
            toggleNewPasswordFields(false);
            updateSubmitButtonState();
            hienThiThongBao(messages.matkhaucu, '', 'info');
        });
        inputs.matkhaucu.addEventListener('blur', validateOldPassword);

        inputs.matkhaumoi.addEventListener('input', validateNewPassword);
        inputs.xacnhanmatkhau.addEventListener('input', validateNewPassword);
        
        form.addEventListener('submit', async function(e) {
            e.preventDefault();
            if (!Object.values(validationState).every(Boolean)) {
                hienThiThongBao(messageDiv, 'Vui lòng điền đúng và đủ thông tin.', 'error');
                messageDiv.classList.remove('hidden');
                return;
            }

            btn.disabled = true;
            btn.innerHTML = `<span class="loader"></span> Đang xử lý...`;
            hienThiThongBao(messageDiv, '', 'info');
            
            const data = {
                tendangnhap: inputs.tendangnhap.value,
                matkhaucu: inputs.matkhaucu.value,
                matkhaumoi: inputs.matkhaumoi.value
            };

            try {
                const response = await fetch('../../models/auth/xuly_doi_matkhau.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data)
                });
                const result = await response.json();
                
                // CẬP NHẬT: Thêm thông báo chuyển hướng
                const displayMessage = result.success ? result.message + ' Đang chuyển hướng...' : result.message;
                hienThiThongBao(messageDiv, displayMessage, result.success ? 'success' : 'error');
                messageDiv.classList.remove('hidden');

                if (result.success) {
                    // CẬP NHẬT: Chuyển hướng sau 2 giây
                    setTimeout(() => {
                        window.location.href = 'dangnhap.html';
                    }, 2000);
                }

            } catch (error) {
                console.error('Lỗi Fetch:', error);
                hienThiThongBao(messageDiv, 'Không thể kết nối đến server. Vui lòng thử lại.', 'error');
                messageDiv.classList.remove('hidden');
            } finally {
                // Giữ nút disabled sau khi submit thành công để chờ chuyển hướng
                if (!validationState.matkhaucuValid) { // Chỉ enable lại nếu thất bại
                     btn.disabled = false;
                     btn.textContent = 'Đổi Mật khẩu';
                }
            }
        });
    });
</script>
</body>
</html>

