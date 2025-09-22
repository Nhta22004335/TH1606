<?php
session_start();
require_once __DIR__ . '/database_mg.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: dangnhap_mg.php');
    exit;
}

$userId = $_SESSION['user_id'];
$error = '';
$message = '';

// Lấy thông tin user
$stmt = $pdo->prepare("SELECT * FROM nguoi_dung WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

// Xử lý POST khi cập nhật
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ten = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $dia_chi = trim($_POST['address']);

    if ($ten === '' || $email === '') {
        $error = "Tên và Email không được để trống!";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Email không hợp lệ!";
    } else {
        $update = $pdo->prepare("UPDATE nguoi_dung SET ten=?, email=?, phone=?, dia_chi=? WHERE id=?");
        $update->execute([$ten, $email, $phone, $dia_chi, $userId]);

        // Lấy lại dữ liệu mới
        $stmt = $pdo->prepare("SELECT * FROM nguoi_dung WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();

        $message = "Cập nhật hồ sơ thành công!";
    }
}
?>

<div class="max-w-lg mx-auto mt-10 bg-white p-6 rounded-2xl shadow-lg">
    <h2 class="text-3xl font-bold mb-6 text-blue-600 text-center">Hồ sơ cá nhân</h2>

    <!-- Thông báo luôn ẩn mặc định -->
    <p id="errorMsg" class="text-red-500 mb-4 text-center font-medium" style="display:none;"></p>
    <p id="successMsg" class="text-green-600 mb-4 text-center font-medium" style="display:none;"></p>

    <form method="POST" class="space-y-4" autocomplete="off">
        <input type="text" name="fakeuser" style="display:none" autocomplete="off">
        <input type="password" name="fakepass" style="display:none" autocomplete="off">

        <div>
            <label class="block font-semibold mb-1" for="name">Họ và tên *</label>
            <input type="text" id="name" name="name" value="<?= htmlspecialchars($user['ten']) ?>" 
                   class="w-full border px-3 py-2 rounded-lg focus:outline-none focus:ring focus:ring-blue-300" 
                   readonly autocomplete="off">
        </div>

        <div>
            <label class="block font-semibold mb-1" for="email">Email *</label>
            <input type="email" id="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" 
                   class="w-full border px-3 py-2 rounded-lg focus:outline-none focus:ring focus:ring-blue-300" 
                   readonly autocomplete="off">
        </div>

        <div>
            <label class="block font-semibold mb-1" for="phone">Số điện thoại</label>
            <input type="text" id="phone" name="phone" value="<?= htmlspecialchars($user['phone']) ?>" 
                   class="w-full border px-3 py-2 rounded-lg focus:outline-none focus:ring focus:ring-blue-300" 
                   readonly autocomplete="off">
        </div>

        <div>
            <label class="block font-semibold mb-1" for="address">Địa chỉ</label>
            <textarea id="address" name="address" rows="3" 
                      class="w-full border px-3 py-2 rounded-lg focus:outline-none focus:ring focus:ring-blue-300" 
                      readonly autocomplete="off"><?= htmlspecialchars($user['dia_chi']) ?></textarea>
        </div>

        <div class="flex justify-between">
            <button type="button" id="editBtn" 
                    class="bg-yellow-500 text-white px-5 py-2 rounded-lg hover:bg-yellow-600 transition">Chỉnh sửa</button>
            <button type="submit" id="saveBtn" 
                    class="bg-blue-500 text-white px-5 py-2 rounded-lg hover:bg-blue-600 transition hidden">Lưu</button>
        </div>
    </form>
</div>

<script>
const editBtn = document.getElementById('editBtn');
const saveBtn = document.getElementById('saveBtn');
const errorMsg = document.getElementById('errorMsg');
const successMsg = document.getElementById('successMsg');

// Nếu có POST thì show thông báo tương ứng
<?php if($_SERVER['REQUEST_METHOD'] === 'POST'): ?>
    <?php if($error): ?>
        errorMsg.textContent = "<?= addslashes($error) ?>";
        errorMsg.style.display = "block";
    <?php endif; ?>
    <?php if($message): ?>
        successMsg.textContent = "<?= addslashes($message) ?>";
        successMsg.style.display = "block";
    <?php endif; ?>
<?php endif; ?>

// Khi nhấn "Chỉnh sửa"
editBtn.addEventListener('click', function() {
    document.querySelectorAll('input, textarea').forEach(el => el.removeAttribute('readonly'));
    saveBtn.classList.remove('hidden');
    editBtn.classList.add('hidden');

    errorMsg.style.display = 'none';
    successMsg.style.display = 'none';
});

// Khi reload, input readonly và nút Lưu ẩn, thông báo ẩn
window.addEventListener('load', () => {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        document.querySelectorAll('input, textarea').forEach(el => el.setAttribute('readonly', true));
        saveBtn.classList.add('hidden');
        editBtn.classList.remove('hidden');

        errorMsg.style.display = 'none';
        successMsg.style.display = 'none';
    }
});
</script>
