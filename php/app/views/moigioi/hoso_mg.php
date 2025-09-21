<div>
    <h2 class="text-3xl font-bold mb-6 text-blue-600">Hồ sơ cá nhân</h2>

    <?php
    // Giả lập thông tin user
    $user = [
        'name' => 'Nguyễn Văn M',
        'email' => 'nguyenvanm@example.com',
        'phone' => '0909876543',
        'address' => '123 Đường ABC, Quận 1, TP.HCM',
    ];
    ?>

    <form action="#" method="post" class="max-w-lg bg-white p-6 rounded shadow space-y-4">
        <div>
            <label class="block font-semibold mb-1" for="name">Họ và tên</label>
            <input type="text" id="name" name="name" value="<?= htmlspecialchars($user['name']) ?>" class="w-full border px-3 py-2 rounded-lg focus:outline-none focus:ring focus:ring-blue-300" />
        </div>
        <div>
            <label class="block font-semibold mb-1" for="email">Email</label>
            <input type="email" id="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" class="w-full border px-3 py-2 rounded-lg focus:outline-none focus:ring focus:ring-blue-300" />
        </div>
        <div>
            <label class="block font-semibold mb-1" for="phone">Số điện thoại</label>
            <input type="text" id="phone" name="phone" value="<?= htmlspecialchars($user['phone']) ?>" class="w-full border px-3 py-2 rounded-lg focus:outline-none focus:ring focus:ring-blue-300" />
        </div>
        <div>
            <label class="block font-semibold mb-1" for="address">Địa chỉ</label>
            <textarea id="address" name="address" rows="3" class="w-full border px-3 py-2 rounded-lg focus:outline-none focus:ring focus:ring-blue-300"><?= htmlspecialchars($user['address']) ?></textarea>
        </div>

        <button type="submit" class="bg-blue-500 text-white px-5 py-2 rounded-lg hover
