<?php
session_start();
$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    echo "<p class='text-red-600'>Bạn chưa đăng nhập.</p>";
    exit;
}
?>

<h2 class="text-2xl font-bold mb-4">Đăng tin bất động sản mới</h2>

<form action="xl_dangtin.php" method="post" class="max-w-lg bg-white p-6 rounded shadow" >
    <label class="block mb-2">
        Tiêu đề:
        <input type="text" name="tieu_de" required class="w-full border border-gray-300 p-2 rounded" />
    </label>

    <label class="block mb-2">
        Mô tả:
        <textarea name="mo_ta" required class="w-full border border-gray-300 p-2 rounded"></textarea>
    </label>

    <label class="block mb-2">
        Giá (VNĐ):
        <input type="number" name="gia" required class="w-full border border-gray-300 p-2 rounded" />
    </label>

    <label class="block mb-2">
        Địa chỉ:
        <input type="text" name="dia_chi" required class="w-full border border-gray-300 p-2 rounded" />
    </label>

    <label class="block mb-2">
        Loại:
        <select name="loai" required class="w-full border border-gray-300 p-2 rounded">
            <option value="ban">Bán</option>
            <option value="thue">Thuê</option>
        </select>
    </label>

    <label class="block mb-4">
        Trạng thái:
        <select name="trang_thai" required class="w-full border border-gray-300 p-2 rounded">
            <option value="dangban">Đang bán</option>
            <option value="dangtinh">Đang tính</option>
        </select>
    </label>

    <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Đăng tin</button>
</form>
