<?php
require_once "../../../config/database.php";

$id = $_GET['id'] ?? '';
if (!$id) {
    die("Thiếu ID biểu mẫu!");
}

$pdo = ketnoicsdl();
$stmt = $pdo->prepare("SELECT * FROM bieu_mau WHERE id = :id");
$stmt->execute([':id' => $id]);
$bm = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$bm) {
    die("Không tìm thấy hồ sơ!");
}
?>

<!-- Nút quay lại -->
<a href="trangchu.php?page=../moi_gioi/ql_hoso" 
   class="inline-flex items-center mb-4 text-blue-600 hover:text-blue-800">
    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none"
         viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
    </svg>
    Trở về danh sách hồ sơ
</a>

<!-- Form cập nhật -->
<form id="formSuaBM" action="../../models/load_bm.php" method="POST" enctype="multipart/form-data" class="bg-white shadow-md rounded-lg p-6 max-w-xl mx-auto">
    <input type="hidden" name="id_bm" value="<?= htmlspecialchars($bm['id']) ?>">

    <label class="block mb-2 font-semibold">Tiêu đề:</label>
    <input type="text" name="tieu_de" required 
           value="<?= htmlspecialchars($bm['tieu_de']) ?>"
           class="border border-gray-300 p-2 rounded w-full mb-4 focus:outline-none focus:ring-2 focus:ring-blue-400">

    <label class="block mb-2 font-semibold">Loại:</label>
    <input type="text" name="loai" 
           value="<?= htmlspecialchars($bm['loai']) ?>"
           class="border border-gray-300 p-2 rounded w-full mb-4 focus:outline-none focus:ring-2 focus:ring-blue-400">

    <label class="block mb-2 font-semibold">Tệp hiện tại:</label>
    <div id="tepHienTai" class="mb-4">
        <?php if (!empty($bm['tep_dk'])): ?>
           <a href="/storage/documents/<?= htmlspecialchars(basename($bm['tep_dk'])) ?>" target="_blank" class="text-blue-600 underline">
             <?= htmlspecialchars(basename($bm['tep_dk'])) ?>
            </a>

        <?php else: ?>
            <span class="text-gray-500 italic">Chưa có tệp</span>
        <?php endif; ?>
    </div>

    <label class="block mb-2 font-semibold">Chọn tệp mới (nếu cần):</label>
    <input type="file" name="tep_dk" class="border border-gray-300 p-2 rounded w-full mb-4">

    <button type="submit" 
            class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg">
        Cập nhật biểu mẫu
    </button>
</form>


<script>
document.getElementById('formSuaBM').addEventListener('submit', async (e) => {
    e.preventDefault();
    const formData = new FormData(e.target);

    try {
        const res = await fetch(e.target.action, {
            method: 'POST',
            body: formData
        });

        if (!res.ok) throw new Error("Lỗi server: " + res.status);

        const data = await res.json();

        if (data.status === 'success') {
            alert("✅ " + data.message);

            // Cập nhật phần hiển thị tệp mới nếu có
            if (data.tep_dk) {
                document.getElementById('tepHienTai').innerHTML = `
                    <a href="../../storage/documents/${data.tep_dk}" target="_blank" class="text-blue-600 underline">
                        ${data.tep_dk}
                    </a>
                `;
            } else {
                document.getElementById('tepHienTai').innerHTML = `
                    <span class="text-gray-500 italic">Chưa có tệp</span>
                `;
            }

            // Quay lại trang danh sách hồ sơ ngay sau khi thông báo
            window.location.href = 'trangchu.php?page=../moi_gioi/ql_hoso';
        } else {
            alert("❌ " + data.message);
        }
    } catch (err) {
        alert("❌ " + err.message);
    }
});
</script>
