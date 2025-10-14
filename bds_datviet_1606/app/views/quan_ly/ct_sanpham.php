<?php
    // ===== PHẦN LOGIC PHP (Giữ nguyên) =====
    require_once "../../../config/database.php";

    function formatPrice($price) {
        if ($price >= 1000000000) return round($price / 1000000000, 2) . ' tỷ';
        if ($price >= 1000000) return round($price / 1000000, 2) . ' triệu';
        return number_format($price) . ' đ';
    }
    function getLoaiBDS($key) {
        $map = ['canho' => 'Căn hộ', 'nhapho' => 'Nhà phố', 'datnen' => 'Đất nền', 'bietthu' => 'Biệt thự'];
        return $map[$key] ?? 'N/A';
    }
    function getHinhThuc($key) {
        $map = ['ban' => 'Rao bán', 'chothue' => 'Cho thuê'];
        return $map[$key] ?? 'N/A';
    }
    function getStatusInfo($status) {
        $map = [
            'binhthuong' => ['text' => "Bình thường", 'classes' => "bg-green-100 text-green-800"],
            'nhe'        => ['text' => "Nhẹ", 'classes' => "bg-yellow-100 text-yellow-800"],
            'trungbinh'  => ['text' => "Trung bình", 'classes' => "bg-orange-100 text-orange-800"],
            'nang'       => ['text' => "Nặng", 'classes' => "bg-red-100 text-red-800"]
        ];
        return $map[$status] ?? ['text' => ucfirst($status), 'classes' => "bg-gray-100 text-gray-800"];
    }

    if (!isset($_GET['id']) || empty($_GET['id'])) {
        die("Lỗi: Không tìm thấy ID của bất động sản.");
    }
    $bds_id = $_GET['id'];

    $pdo = ketnoicsdl();
    $sql_main = "
        SELECT b.*, nd.email, nd.so_dt, nd.avt, info.ho_ten
        FROM bat_dong_san b
        LEFT JOIN nguoi_dung nd ON b.id_nguoi_dung = nd.id
        LEFT JOIN info_nguoi_dung info ON nd.id = info.id_nguoi_dung
        WHERE b.id = :id";
    $stmt_main = $pdo->prepare($sql_main);
    $stmt_main->execute([':id' => $bds_id]);
    $bds = $stmt_main->fetch(PDO::FETCH_ASSOC);

    if (!$bds) {
        die("Không tìm thấy bất động sản với ID này.");
    }

    $sql_images = "SELECT id, url, trang_thai FROM hinh_anh_bds WHERE id_bds = :id_bds ORDER BY ngay_tao ASC";
    $stmt_images = $pdo->prepare($sql_images);
    $stmt_images->execute([':id_bds' => $bds_id]);
    $images = $stmt_images->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="vi" class="bg-gray-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <title>Chi tiết: <?= htmlspecialchars($bds['tieu_de']) ?></title>
    <style>
        .slider-item { transition: opacity 0.3s ease-in-out; }
    </style>
</head>
<body class="text-gray-800">

    <div class="container"> <header class="mb-6">
            <nav class="text-sm mb-2">
                <a href="javascript:history.back()" class="text-indigo-600 hover:underline">
                    <i class="fas fa-arrow-left mr-1"></i> Quay lại
                </a>
            </nav>
            <h1 class="text-2xl font-bold text-gray-800 leading-tight">
                <?= htmlspecialchars($bds['tieu_de']) ?>
            </h1>
            <p class="text-sm text-gray-600 mt-1 flex items-center">
                <i class="fas fa-map-marker-alt mr-2 text-gray-400"></i>
                <?= htmlspecialchars($bds['dia_chi']) ?>
            </p>
        </header>

        <main class="space-y-6">
            <section id="image-slider-container" class="relative rounded-xl overflow-hidden shadow-lg bg-gray-200">
                <?php if (!empty($images)): ?>
                    <?php foreach ($images as $index => $img): ?>
                        <div class="slider-item <?= $index > 0 ? 'hidden' : '' ?>">
                            <img src="../../../storage/pictures/bds/<?= htmlspecialchars($img['url']) ?>" 
                                alt="Ảnh bất động sản <?= $index + 1 ?>"
                                class="w-full h-auto object-cover max-h-[500px]">
                            
                            <?php $statusInfo = getStatusInfo($img['trang_thai']); ?>
                            <div class="absolute top-4 left-4 z-10">
                                <span id="status-label-<?= $img['id'] ?>" class="px-2.5 py-1 text-xs font-semibold rounded-full <?= $statusInfo['classes'] ?>">
                                    <?= $statusInfo['text'] ?>
                                </span>
                            </div>

                            <div class="absolute bottom-0 left-0 right-0 p-3 bg-gradient-to-t from-black/60 to-transparent">
                                <div class="flex items-center justify-center gap-2" id="approval-controls-<?= $img['id'] ?>">
                                    <?php if ($img['trang_thai'] === 'trungbinh' || $img['trang_thai'] === 'nang'): ?>
                                        <a href="#" class="approval-btn px-3 py-1 text-xs font-semibold text-white bg-blue-500 rounded-full hover:bg-blue-600 transition"
                                        data-image-id="<?= $img['id'] ?>" data-new-status="binhthuong">Phục hồi</a>
                                    <?php endif; ?>

                                    <?php if ($img['trang_thai'] !== 'nang'): ?>
                                        <a href="#" class="approval-btn px-3 py-1 text-xs font-semibold text-gray-800 bg-green-200 rounded-full hover:bg-green-300 transition"
                                        data-image-id="<?= $img['id'] ?>" data-new-status="binhthuong">Bình thường</a>
                                        <a href="#" class="approval-btn px-3 py-1 text-xs font-semibold text-gray-800 bg-yellow-200 rounded-full hover:bg-yellow-300 transition"
                                        data-image-id="<?= $img['id'] ?>" data-new-status="nhe">Nhẹ</a>
                                        <a href="#" class="approval-btn px-3 py-1 text-xs font-semibold text-gray-800 bg-orange-200 rounded-full hover:bg-orange-300 transition"
                                        data-image-id="<?= $img['id'] ?>" data-new-status="trungbinh">Trung bình</a>
                                        <a href="#" class="approval-btn px-3 py-1 text-xs font-semibold text-white bg-red-600 rounded-full hover:bg-red-700 transition"
                                        data-image-id="<?= $img['id'] ?>" data-new-status="nang"
                                        onclick="return confirm('Bạn có chắc muốn đánh dấu ảnh này là vi phạm nặng? Hành động này KHÔNG THỂ hoàn tác.')">Nặng</a>
                                    <?php else: ?>
                                        <span class="px-3 py-1 text-xs font-semibold text-white bg-red-800 rounded-full">Đã khóa vĩnh viễn</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <?php if (count($images) > 1): ?>
                        <button id="prev-btn" class="absolute top-1/2 left-4 transform -translate-y-1/2 bg-black bg-opacity-40 text-white rounded-full h-10 w-10 flex items-center justify-center hover:bg-opacity-60 transition z-10"><i class="fas fa-chevron-left"></i></button>
                        <button id="next-btn" class="absolute top-1/2 right-4 transform -translate-y-1/2 bg-black bg-opacity-40 text-white rounded-full h-10 w-10 flex items-center justify-center hover:bg-opacity-60 transition z-10"><i class="fas fa-chevron-right"></i></button>
                        <div id="image-counter" class="absolute top-4 right-4 bg-black bg-opacity-50 text-white text-xs font-semibold px-2.5 py-1 rounded-full z-10">1 / <?= count($images) ?></div>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="h-96 flex items-center justify-center"><span class="text-gray-500">Chưa có hình ảnh</span></div>
                <?php endif; ?>
            </section>

            <section class="bg-white p-4 rounded-xl shadow-md border border-gray-200">
                <div class="flex flex-wrap justify-around items-center divide-x divide-gray-200">
                    <div class="flex-1 text-center px-2 py-1">
                        <p class="text-sm text-gray-500">Mức giá</p>
                        <p class="font-bold text-lg text-red-600"><?= formatPrice($bds['gia']) ?></p>
                    </div>
                    <div class="flex-1 text-center px-2 py-1">
                        <p class="text-sm text-gray-500">Diện tích</p>
                        <p class="font-bold text-lg"><i class="fas fa-ruler-combined text-indigo-500 mr-1"></i> <?= htmlspecialchars($bds['dien_tich']) ?> m²</p>
                    </div>
                    <div class="flex-1 text-center px-2 py-1">
                        <p class="text-sm text-gray-500">Loại hình</p>
                        <p class="font-bold text-lg"><i class="far fa-building text-indigo-500 mr-1"></i> <?= getLoaiBDS($bds['loai']) ?></p>
                    </div>
                    <div class="flex-1 text-center px-2 py-1">
                        <p class="text-sm text-gray-500">Hình thức</p>
                        <p class="font-bold text-lg"><i class="fas fa-handshake text-indigo-500 mr-1"></i> <?= getHinhThuc($bds['hinh_thuc']) ?></p>
                    </div>
                </div>
            </section>

            <section class="bg-white p-6 rounded-xl shadow-md border border-gray-200">
                <h2 class="text-xl font-semibold mb-4 border-b pb-3">Thông tin mô tả</h2>
                <div class="prose prose-sm max-w-none text-gray-700 leading-relaxed">
                    <?= nl2br(htmlspecialchars($bds['mo_ta'])) ?>
                </div>

                <hr class="my-8 border-gray-200">

                <div>
                    <h3 class="text-xl font-semibold mb-4">Thông tin liên hệ</h3>
                    <div class="bg-gray-50 p-5 rounded-lg flex flex-col sm:flex-row items-center gap-5">
                        <img class="h-14 w-14 rounded-full object-cover border-2 border-indigo-200 flex-shrink-0" 
                            src="../../../storage/pictures/avt/<?= htmlspecialchars($bds['avt'] ?? 'avt.png') ?>" 
                            alt="Avatar">
                        <div class="flex-grow text-center sm:text-left">
                            <p class="font-bold text-gray-800 text-lg">
                            <?= htmlspecialchars($bds['ho_ten'] ?? 'Chưa cập nhật') ?>
                            </p>
                            <p class="text-sm text-gray-500">Người đăng tin</p>
                        </div>
                        <div class="flex flex-col sm:flex-row gap-3 w-full sm:w-auto">
                            <a href="tel:<?= htmlspecialchars($bds['so_dt']) ?>" class="w-full flex items-center justify-center gap-2 px-4 py-2.5 font-semibold rounded-lg text-white bg-indigo-600 hover:bg-indigo-700 transition-colors text-sm">
                                <i class="fas fa-phone-alt"></i>
                                <span><?= htmlspecialchars($bds['so_dt'] ?? 'Chưa có SĐT') ?></span>
                            </a>
                            <a href="mailto:<?= htmlspecialchars($bds['email']) ?>" class="w-full flex items-center justify-center gap-2 px-4 py-2.5 font-semibold rounded-lg text-indigo-700 bg-indigo-100 hover:bg-indigo-200 transition-colors text-sm">
                                <i class="fas fa-envelope"></i>
                                <span>Gửi Email</span>
                            </a>
                        </div>
                    </div>
                </div>
            </section>

        </main>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const sliderContainer = document.getElementById('image-slider-container');
            if (!sliderContainer) return;

            const slides = sliderContainer.querySelectorAll('.slider-item');
            const prevButton = document.getElementById('prev-btn');
            const nextButton = document.getElementById('next-btn');
            const imageCounter = document.getElementById('image-counter');

            if (slides.length > 1) {
                let currentIndex = 0;

                function showSlide(index) {
                    slides.forEach(slide => {
                        slide.classList.add('hidden', 'opacity-0');
                    });
                    const activeSlide = slides[index];
                    activeSlide.classList.remove('hidden');
                    setTimeout(() => {
                        activeSlide.classList.remove('opacity-0');
                    }, 20);
                    imageCounter.textContent = `${index + 1} / ${slides.length}`;
                }

                nextButton.addEventListener('click', () => {
                    currentIndex = (currentIndex + 1) % slides.length;
                    showSlide(currentIndex);
                });

                prevButton.addEventListener('click', () => {
                    currentIndex = (currentIndex - 1 + slides.length) % slides.length;
                    showSlide(currentIndex);
                });
            }
        });

        document.addEventListener('DOMContentLoaded', function() {
            const sliderContainer = document.getElementById('image-slider-container');
            if (!sliderContainer) return;

            // --- LOGIC CHO SLIDER (KHÔNG THAY ĐỔI) ---
            const slides = sliderContainer.querySelectorAll('.slider-item');
            const prevButton = document.getElementById('prev-btn');
            const nextButton = document.getElementById('next-btn');
            const imageCounter = document.getElementById('image-counter');

            if (slides.length > 1) {
                let currentIndex = 0;
                function showSlide(index) {
                    slides.forEach(slide => slide.classList.add('hidden', 'opacity-0'));
                    const activeSlide = slides[index];
                    activeSlide.classList.remove('hidden');
                    setTimeout(() => activeSlide.classList.remove('opacity-0'), 20);
                    imageCounter.textContent = `${index + 1} / ${slides.length}`;
                }
                nextButton.addEventListener('click', () => {
                    currentIndex = (currentIndex + 1) % slides.length;
                    showSlide(currentIndex);
                });
                prevButton.addEventListener('click', () => {
                    currentIndex = (currentIndex - 1 + slides.length) % slides.length;
                    showSlide(currentIndex);
                });
            }

            // --- LOGIC MỚI CHO CÁC NÚT PHÊ DUYỆT BẰNG AJAX ---
            const statusMap = {
                'binhthuong': { text: "Bình thường", classes: "bg-green-100 text-green-800" },
                'nhe':        { text: "Nhẹ", classes: "bg-yellow-100 text-yellow-800" },
                'trungbinh':  { text: "Trung bình", classes: "bg-orange-100 text-orange-800" },
                'nang':       { text: "Nặng", classes: "bg-red-100 text-red-800" }
            };
            
            // Sử dụng event delegation để bắt sự kiện click trên các nút được thêm động
            sliderContainer.addEventListener('click', function(event) {
                // Chỉ xử lý nếu phần tử được click có class 'approval-btn'
                if (!event.target.matches('.approval-btn')) {
                    return;
                }

                event.preventDefault(); // Ngăn hành vi mặc định của thẻ <a>
                
                const button = event.target;
                const imageId = button.dataset.imageId;
                const newStatus = button.dataset.newStatus;
                
                // Gửi yêu cầu AJAX
                fetch(`../../models/pheduyet_anh.php?id=${imageId}&status=${newStatus}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Cập nhật giao diện nếu thành công
                            updateImageUI(imageId, data.new_status);
                        } else {
                            // Thông báo lỗi nếu thất bại
                            alert(data.message || 'Có lỗi xảy ra, vui lòng thử lại.');
                        }
                    })
                    .catch(error => {
                        console.error('Fetch Error:', error);
                        alert('Không thể kết nối đến máy chủ.');
                    });
            });

            function updateImageUI(imageId, newStatus) {
                // Cập nhật nhãn trạng thái
                const label = document.getElementById(`status-label-${imageId}`);
                const statusInfo = statusMap[newStatus];
                if (label && statusInfo) {
                    label.textContent = statusInfo.text;
                    label.className = `px-2.5 py-1 text-xs font-semibold rounded-full ${statusInfo.classes}`;
                }

                // Cập nhật lại các nút điều khiển
                const controlsContainer = document.getElementById(`approval-controls-${imageId}`);
                if (controlsContainer) {
                    let newControlsHTML = '';
                    if (newStatus === 'trungbinh') {
                        newControlsHTML += `<a href="#" class="approval-btn ..." data-image-id="${imageId}" data-new-status="binhthuong">Phục hồi</a>`;
                    }
                    if (newStatus !== 'nang') {
                        newControlsHTML += `
                            <a href="#" class="approval-btn ..." data-image-id="${imageId}" data-new-status="binhthuong">Bình thường</a>
                            <a href="#" class="approval-btn ..." data-image-id="${imageId}" data-new-status="nhe">Nhẹ</a>
                            <a href="#" class="approval-btn ..." data-image-id="${imageId}" data-new-status="trungbinh">Trung bình</a>
                            <a href="#" class="approval-btn ..." data-image-id="${imageId}" data-new-status="nang" onclick="return confirm(...)">Nặng</a>
                        `;
                    } else {
                        newControlsHTML = `<span class="px-3 py-1 ...">Đã khóa vĩnh viễn</span>`;
                    }
                    // Tạm thời chỉ làm mờ các nút để đơn giản, việc render lại HTML sẽ phức tạp hơn
                    controlsContainer.style.opacity = '0.5';
                    controlsContainer.style.pointerEvents = 'none';
                    setTimeout(() => { 
                        // Cách đơn giản nhất là reload lại trang sau khi thành công để thấy bộ nút mới
                        // Nếu muốn cập nhật động hoàn toàn, cần viết hàm render lại bộ nút phức tạp hơn.
                        window.location.reload(); 
                    }, 500); // Tải lại sau nửa giây để người dùng thấy thay đổi
                }
            }
        });
    </script>
</body>
</html>