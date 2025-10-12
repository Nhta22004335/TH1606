<?php
// ===== PHẦN LOGIC PHP - Giữ nguyên logic xử lý nhưng được đặt gọn gàng =====
if (session_status() == PHP_SESSION_NONE) { session_start(); }
require_once "../../../config/database.php";

$pdo = ketnoicsdl();
$id_ben_ban = $_SESSION['id_nguoi_dung'] ?? '';
if (!$id_ben_ban) {
    // Thay vì exit, chuyển hướng đến trang đăng nhập với đường dẫn tuyệt đối
    header("Location: /app/views/auth/dangnhap.php"); // Giả sử đây là đường dẫn đúng
    exit;
}

$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['tao_hoso'])) {
    $tieu_de = trim($_POST['tieu_de'] ?? '');
    $loai = $_POST['loai'] ?? '';
    $ben_mua = $_POST['ben_mua'] ?? '';
    
    if (empty($tieu_de) || empty($loai) || empty($ben_mua)) {
        $error = "Vui lòng điền đầy đủ các trường thông tin bắt buộc.";
    } else {
        $tep_dk = null;
        if (isset($_FILES['tep_dk']) && $_FILES['tep_dk']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = "../../../storage/documents/";
            if (!is_dir($uploadDir)) { mkdir($uploadDir, 0777, true); }
            $originalFileName = basename($_FILES["tep_dk"]["name"]);
            $fileName = time() . "_" . preg_replace("/[^a-zA-Z0-9\.]/", "_", $originalFileName);
            $filePath = $uploadDir . $fileName;
            if (move_uploaded_file($_FILES["tep_dk"]["tmp_name"], $filePath)) {
                $tep_dk = $fileName;
            } else {
                $error = "Lỗi khi upload tệp đính kèm. Vui lòng thử lại!";
            }
        }
        
        if (!$error) {
            try {
                $stmt = $pdo->prepare("INSERT INTO bieu_mau (tieu_de, loai, ben_mua, ben_ban, tep_dk, trang_thai, ngay_tao) VALUES (:tieu_de, :loai, :ben_mua, :ben_ban, :tep_dk, 'choduyet', CURRENT_TIMESTAMP)");
                $stmt->execute([':tieu_de' => $tieu_de, ':loai' => $loai, ':ben_mua' => $ben_mua, ':ben_ban' => $id_ben_ban, ':tep_dk' => $tep_dk]);
                $success = "Tạo hồ sơ thành công! Hồ sơ của bạn đã được gửi đi và đang chờ duyệt.";
            } catch (PDOException $e) {
                $error = "Lỗi CSDL: Không thể tạo hồ sơ. Vui lòng liên hệ quản trị viên.";
            }
        }
    }
}

// Lấy danh sách người mua (khách hàng)
try {
    $sql_users = "SELECT info.id_nguoi_dung, info.ho_ten, nd.email FROM info_nguoi_dung info JOIN nguoi_dung nd ON info.id_nguoi_dung = nd.id JOIN phan_quyen pq ON nd.id = pq.id_nguoi_dung JOIN quyen q ON pq.id_quyen = q.id WHERE nd.id != :ben_ban_id AND q.vai_tro = 'khachhang' ORDER BY info.ho_ten ASC";
    $usersStmt = $pdo->prepare($sql_users);
    $usersStmt->execute([':ben_ban_id' => $id_ben_ban]);
    $nguoi_mua_list = $usersStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $nguoi_mua_list = [];
    $error = $error ?: "Không thể tải danh sách người mua.";
}
?>
<!DOCTYPE html>
<html lang="vi" class="bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tạo Hồ Sơ Mới</title>
    <style>
        .step.active .step-circle { background-color: #3b82f6; color: white; border-color: #3b82f6; }
        .step.active .step-text { color: #3b82f6; font-weight: 600; }
        .step.completed .step-circle { background-color: #16a34a; color: white; border-color: #16a34a; }
        .step.completed .step-text { color: #15803d; }
        .step-line { height: calc(100% - 2.5rem); top: 2.5rem; }
        .dropzone-active { border-color: #3b82f6; background-color: #eff6ff; }
    </style>
</head>
<body>
    <div class="container mx-auto p-4 md:p-2">
        <div class="bg-white rounded-2xl  border border-gray-300 flex flex-col md:flex-row min-h-[70vh]">
            
            <aside class="w-full md:w-1/3 lg:w-1/4 p-6 md:p-8 bg-slate-50/70 md:border-r border-gray-200 rounded-t-2xl md:rounded-l-2xl md:rounded-tr-none">
                <h2 class="text-xl font-bold text-gray-800 mb-2">Tạo Hồ Sơ Mới</h2>
                <p class="text-sm text-gray-500 mb-8">Hoàn thành các bước sau để gửi hồ sơ của bạn.</p>
                <nav id="wizard-steps" class="space-y-4">
                    <div id="step-nav-1" class="step active flex items-start cursor-pointer" onclick="goToStep(1)">
                        <div class="relative">
                            <div class="step-circle w-10 h-10 bg-white border-2 border-gray-300 rounded-full flex items-center justify-center font-bold text-gray-500 transition-colors">1</div>
                            <div class="step-line absolute left-1/2 w-0.5 bg-gray-300 -translate-x-1/2"></div>
                        </div>
                        <div class="ml-4">
                            <h3 class="step-text font-medium text-gray-700 transition-colors">Thông tin chung</h3>
                            <p class="text-xs text-gray-500">Tiêu đề & loại hồ sơ</p>
                        </div>
                    </div>
                    <div id="step-nav-2" class="step flex items-start cursor-pointer" onclick="goToStep(2)">
                         <div class="relative">
                            <div class="step-circle w-10 h-10 bg-white border-2 border-gray-300 rounded-full flex items-center justify-center font-bold text-gray-500 transition-colors">2</div>
                            <div class="step-line absolute left-1/2 w-0.5 bg-gray-300 -translate-x-1/2"></div>
                        </div>
                        <div class="ml-4">
                            <h3 class="step-text font-medium text-gray-700 transition-colors">Các bên liên quan</h3>
                            <p class="text-xs text-gray-500">Chọn người mua</p>
                        </div>
                    </div>
                    <div id="step-nav-3" class="step flex items-start cursor-pointer" onclick="goToStep(3)">
                        <div class="step-circle w-10 h-10 bg-white border-2 border-gray-300 rounded-full flex items-center justify-center font-bold text-gray-500 transition-colors">3</div>
                        <div class="ml-4">
                            <h3 class="step-text font-medium text-gray-700 transition-colors">Tài liệu & Hoàn tất</h3>
                            <p class="text-xs text-gray-500">Đính kèm và gửi đi</p>
                        </div>
                    </div>
                </nav>
            </aside>

            <main class="w-full md:w-2/3 lg:w-3/4 p-6 md:p-8">
                <?php if ($success): ?>
                    <div class="p-4 mb-4 text-green-700 bg-green-100 rounded-lg flex items-center gap-3" role="alert">
                        <i class="fa-solid fa-check-circle text-xl"></i>
                        <div><h3 class="font-bold">Thành công!</h3><p class="text-sm"><?= htmlspecialchars($success) ?></p></div>
                    </div>
                <?php endif; ?>
                <?php if ($error): ?>
                    <div class="p-4 mb-4 text-red-700 bg-red-100 rounded-lg flex items-center gap-3" role="alert">
                         <i class="fa-solid fa-triangle-exclamation text-xl"></i>
                         <div><h3 class="font-bold">Đã xảy ra lỗi!</h3><p class="text-sm"><?= htmlspecialchars($error) ?></p></div>
                    </div>
                <?php endif; ?>

                <form id="wizard-form" action="" method="post" enctype="multipart/form-data">
                    <div id="step-content-1" class="step-content space-y-6">
                        <h3 class="text-lg font-semibold text-gray-800">Bước 1: Cung cấp thông tin chung</h3>
                        <div>
                            <label for="tieu_de" class="block text-sm font-medium text-gray-700 mb-1">Tiêu đề Hồ sơ <span class="text-red-500">*</span></label>
                            <input type="text" id="tieu_de" name="tieu_de" class="w-full outline-none border border-gray-300 px-4 py-2 rounded-lg bg-gray-50 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required placeholder="Ví dụ: Hợp đồng mua bán căn hộ A-01">
                        </div>
                        <div class="relative">
                            <label for="loai" class="block text-sm font-medium text-gray-700 mb-1">Loại Hồ sơ <span class="text-red-500">*</span></label>
                            <select id="loai" name="loai" class="w-full border border-gray-300 outline-none px-4 py-2 rounded-lg bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 appearance-none" required>
                                <option value="">-- Chọn loại hồ sơ --</option>
                                <option value="hosomuaban">Hồ sơ mua bán</option>
                                <option value="hosothue">Hồ sơ thuê</option>
                                <option value="bienban">Biên bản</option>
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 top-7 flex items-center px-2 text-gray-700"><i class="fa-solid fa-chevron-down text-xs"></i></div>
                        </div>
                    </div>

                    <div id="step-content-2" class="step-content space-y-6 hidden">
                        <h3 class="text-lg font-semibold text-gray-800">Bước 2: Chọn bên mua</h3>
                        <div class="relative">
                            <label for="ben_mua" class="block text-sm font-medium text-gray-700 mb-1">Người mua <span class="text-red-500">*</span></label>
                            <select id="ben_mua" name="ben_mua" class="w-full border border-gray-300 outline-none px-4 py-2 rounded-lg bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 appearance-none" required>
                                <option value="">-- Chọn người mua từ danh sách khách hàng --</option>
                                <?php foreach($nguoi_mua_list as $user): ?>
                                    <option value="<?= $user['id_nguoi_dung'] ?>"><?= htmlspecialchars($user['ho_ten']) ?> (<?= htmlspecialchars($user['email']) ?>)</option>
                                <?php endforeach; ?>
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 top-7 flex items-center px-2 text-gray-700"><i class="fa-solid fa-chevron-down text-xs"></i></div>
                        </div>
                    </div>

                    <div id="step-content-3" class="step-content space-y-6 hidden">
                        <h3 class="text-lg font-semibold text-gray-800">Bước 3: Đính kèm tài liệu và hoàn tất</h3>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Tệp đính kèm (PDF, DOC, Ảnh...)</label>
                            <div id="dropzone" class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center cursor-pointer hover:border-blue-500 transition-colors">
                                <input type="file" id="tep_dk" name="tep_dk" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" class="hidden">
                                <i class="fa-solid fa-cloud-arrow-up text-4xl text-gray-400 mb-2"></i>
                                <p class="text-sm text-gray-600">Kéo và thả file vào đây, hoặc <span class="font-semibold text-blue-600">nhấn để chọn file</span></p>
                                <p id="file-name" class="text-xs text-gray-500 mt-2 font-medium"></p>
                            </div>
                        </div>
                    </div>

                    <div class="mt-8 pt-6 border-t border-gray-200 flex justify-between items-center">
                        <button type="button" id="prev-btn" onclick="prevStep()" class="px-4 py-2 bg-gray-200 text-gray-700 font-medium rounded-lg hover:bg-gray-300 transition hidden">
                            <i class="fa-solid fa-arrow-left mr-1"></i> Quay lại
                        </button>
                        <div class="flex-grow"></div> <button type="button" id="next-btn" onclick="nextStep()" class="px-6 py-3 bg-blue-600 text-white font-bold rounded-lg hover:bg-blue-700 transition shadow-lg shadow-blue-500/20">
                            Tiếp theo <i class="fa-solid fa-arrow-right ml-1"></i>
                        </button>
                        <button type="submit" name="tao_hoso" id="submit-btn" class="px-6 py-3 bg-green-600 text-white font-bold rounded-lg hover:bg-green-700 transition shadow-lg shadow-green-500/20 hidden">
                            <i class="fa-solid fa-paper-plane mr-1"></i> HOÀN TẤT & GỬI DUYỆT
                        </button>
                    </div>
                </form>
            </main>
        </div>
    </div>
    
    <script>
        let currentStep = 1;
        const totalSteps = 3;

        function goToStep(step) {
            if (step < 1 || step > totalSteps) return;

            // Validate before proceeding (optional but recommended)
            if (step > currentStep && !validateStep(currentStep)) {
                return;
            }

            // Update step navigation UI
            for (let i = 1; i <= totalSteps; i++) {
                document.getElementById(`step-nav-${i}`).classList.remove('active', 'completed');
                document.getElementById(`step-content-${i}`).classList.add('hidden');
                if (i < step) {
                    document.getElementById(`step-nav-${i}`).classList.add('completed');
                }
            }
            document.getElementById(`step-nav-${step}`).classList.add('active');
            document.getElementById(`step-content-${step}`).classList.remove('hidden');

            // Update button visibility
            document.getElementById('prev-btn').classList.toggle('hidden', step === 1);
            document.getElementById('next-btn').classList.toggle('hidden', step === totalSteps);
            document.getElementById('submit-btn').classList.toggle('hidden', step !== totalSteps);
            
            currentStep = step;
        }

        function nextStep() {
            if (currentStep < totalSteps) {
                goToStep(currentStep + 1);
            }
        }

        function prevStep() {
            if (currentStep > 1) {
                goToStep(currentStep - 1);
            }
        }
        
        function validateStep(step) {
            let isValid = true;
            const inputs = document.querySelectorAll(`#step-content-${step} [required]`);
            inputs.forEach(input => {
                if (!input.value) {
                    isValid = false;
                    input.classList.add('border-red-500', 'focus:border-red-500', 'focus:ring-red-500');
                } else {
                    input.classList.remove('border-red-500', 'focus:border-red-500', 'focus:ring-red-500');
                }
            });
            if(!isValid) alert('Vui lòng điền đầy đủ các trường bắt buộc.');
            return isValid;
        }

        // Drag and Drop file upload
        const dropzone = document.getElementById('dropzone');
        const fileInput = document.getElementById('tep_dk');
        const fileNameDisplay = document.getElementById('file-name');

        dropzone.addEventListener('click', () => fileInput.click());
        dropzone.addEventListener('dragover', (e) => {
            e.preventDefault();
            dropzone.classList.add('dropzone-active');
        });
        dropzone.addEventListener('dragleave', () => dropzone.classList.remove('dropzone-active'));
        dropzone.addEventListener('drop', (e) => {
            e.preventDefault();
            dropzone.classList.remove('dropzone-active');
            if (e.dataTransfer.files.length) {
                fileInput.files = e.dataTransfer.files;
                fileNameDisplay.textContent = `Đã chọn: ${fileInput.files[0].name}`;
            }
        });
        fileInput.addEventListener('change', () => {
            if (fileInput.files.length) {
                fileNameDisplay.textContent = `Đã chọn: ${fileInput.files[0].name}`;
            } else {
                 fileNameDisplay.textContent = '';
            }
        });
    </script>
</body>
</html>