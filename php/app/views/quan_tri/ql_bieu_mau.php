<?php
// Demo dữ liệu giấy tờ đơn từ
$docs = [
    [
        "id"=>1,
        "title"=>"Hợp đồng mua bán nhà",
        "type"=>"Hợp đồng",
        "buyer"=>"Nguyễn Văn A",
        "seller"=>"Trần Thị B",
        "status"=>"Chờ duyệt",
        "file"=>"hopdong1.pdf"
    ],
    [
        "id"=>2,
        "title"=>"Đơn xin đặt cọc đất",
        "type"=>"Đơn cọc",
        "buyer"=>"Lê Văn C",
        "seller"=>"Phạm Thị D",
        "status"=>"Đã duyệt",
        "file"=>"doncoc1.pdf"
    ],
    [
        "id"=>3,
        "title"=>"Biên bản thanh toán",
        "type"=>"Biên bản",
        "buyer"=>"Nguyễn Văn E",
        "seller"=>"Trần Thị F",
        "status"=>"Đã ký",
        "file"=>"bienban1.pdf"
    ],
    [
        "id"=>4,
        "title"=>"Đơn khiếu nại",
        "type"=>"Đơn khiếu nại",
        "buyer"=>"Phạm Văn G",
        "seller"=>"Công ty XYZ",
        "status"=>"Chờ duyệt",
        "file"=>"khieunai1.pdf"
    ],
];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý giấy tờ đơn từ</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../../../public/assets/fontawesome/css/all.min.css">
</head>
<body>

<!-- Header -->
<header class="flex items-center gap-4 bg-white shadow p-4  border-b-2">
    <img src="../../../public/assets/anhht/0/customs-clearance.gif" alt="Chat" class="w-12 h-12">
    <h1 class="text-2xl font-bold text-gray-600">Giấy tờ đơn từ</h1>
</header>

<div class="w-full mx-auto p-6 bg-gray-50">

    <div class="bg-white rounded-xl shadow p-6">
        <table class="w-full border-collapse">
            <thead>
                <tr class="bg-gray-200 text-gray-700">
                    <th class="p-3 text-left">Tiêu đề</th>
                    <th class="p-3 text-left">Loại</th>
                    <th class="p-3 text-left">Người mua</th>
                    <th class="p-3 text-left">Người bán</th>
                    <th class="p-3 text-left">Trạng thái</th>
                    <th class="p-3 text-center">Hành động</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($docs as $d): ?>
                    <tr class="border-b hover:bg-gray-50">
                        <td class="p-3"><?= $d["title"] ?></td>
                        <td class="p-3"><?= $d["type"] ?></td>
                        <td class="p-3"><?= $d["buyer"] ?></td>
                        <td class="p-3"><?= $d["seller"] ?></td>
                        <td class="p-3">
                            <?php if($d["status"]=="Chờ duyệt"): ?>
                            <span class="px-2 py-1 bg-yellow-100 text-yellow-700 rounded">Chờ duyệt</span>
                            <?php elseif($d["status"]=="Đã duyệt"): ?>
                            <span class="px-2 py-1 bg-green-100 text-green-700 rounded">Đã duyệt</span>
                            <?php else: ?>
                            <span class="px-2 py-1 bg-blue-100 text-blue-700 rounded">Đã ký</span>
                            <?php endif; ?>
                        </td>
                        <td class="p-3 text-center">
                            <button onclick="document.getElementById('docModal<?= $d['id'] ?>').classList.remove('hidden')" class="text-blue-600 hover:text-blue-800 underline">
                                Xem
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Render modal chi tiết cho từng đơn -->
<?php foreach($docs as $d): ?>
    <div id="docModal<?= $d["id"] ?>" class="fixed inset-0 bg-black bg-opacity-40 hidden flex items-center justify-center z-[9999]">
        <div class="bg-white rounded-xl shadow-lg w-96 p-6 relative">
            <!-- Nút đóng -->
            <button onclick="document.getElementById('docModal<?= $d['id'] ?>').classList.add('hidden')" 
                    class="absolute top-3 right-3 text-gray-500 hover:text-red-500">
                <i class="fa-solid fa-xmark text-xl"></i>
            </button>

            <h2 class="text-xl font-bold text-gray-700 mb-4 flex items-center"><i class="fa-solid fa-file-lines text-blue-500 mr-2"></i> Chi tiết đơn</h2>

            <div class="space-y-2 text-gray-700 text-sm">
                <p><strong>Tiêu đề:</strong> <?= $d["title"] ?></p>
                <p><strong>Loại:</strong> <?= $d["type"] ?></p>
                <p><strong>Người mua:</strong> <?= $d["buyer"] ?></p>
                <p><strong>Người bán:</strong> <?= $d["seller"] ?></p>
                <p><strong>Trạng thái:</strong> <?= $d["status"] ?></p>
                <p><strong>Tệp tin:</strong> 
                    <a href="uploads/<?= $d["file"] ?>" class="text-blue-500 hover:underline" target="_blank">Xem tệp</a>
                </p>
            </div>

            <!-- Nút duyệt/từ chối -->
            <?php if($d["status"]=="Chờ duyệt"): ?>
                <div class="mt-5 flex justify-between">
                    <button class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg flex items-center gap-2">
                        <i class="fa-solid fa-check"></i> Duyệt
                    </button>
                    <button class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg flex items-center gap-2">
                        <i class="fa-solid fa-xmark"></i> Từ chối
                    </button>
                </div>
            <?php elseif($d["status"]=="Đã ký"): ?>
                <div class="mt-5 flex justify-end">
                    <button class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg flex items-center gap-2">
                        <i class="fa-solid fa-xmark"></i> Từ chối
                    </button>
                </div>
            <?php endif; ?>
        </div>
    </div>
<?php endforeach; ?>

</body>
</html>
