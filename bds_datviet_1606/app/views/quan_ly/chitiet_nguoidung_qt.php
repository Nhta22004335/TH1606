<?php
    require_once "../../../config/database.php";
    $pdo = ketnoicsdl();

    $id = $_GET['id'] ?? '';

    // Lấy thông tin chi tiết của người dùng
    $stmt = $pdo->prepare("
        SELECT 
            nd.id, nd.ten_dang_nhap, nd.email, nd.so_dt, nd.avt,
            nd.trang_thai, nd.hoat_dong, nd.ngay_tao,
            info.ho_ten, info.gioi_tinh, info.mo_ta, info.ngay_sinh, info.dia_chi,
            COALESCE(array_agg(DISTINCT q.vai_tro) FILTER (WHERE q.vai_tro IS NOT NULL), '{}') AS vai_tro,
            COALESCE(array_agg(DISTINCT pq.id_quyen) FILTER (WHERE pq.id_quyen IS NOT NULL), '{}') AS id_quyen
        FROM nguoi_dung nd
        LEFT JOIN info_nguoi_dung info ON nd.id = info.id_nguoi_dung
        LEFT JOIN phan_quyen pq ON nd.id = pq.id_nguoi_dung
        LEFT JOIN quyen q ON pq.id_quyen = q.id
        WHERE nd.id = :id
        GROUP BY nd.id, info.ho_ten, info.gioi_tinh, info.mo_ta, info.ngay_sinh, info.dia_chi
    ");
    $stmt->execute([':id' => $id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Xử lý nếu không tìm thấy người dùng
    if (!$user) {
        echo '
        <div class="max-w-md mx-auto mt-10 p-6 bg-red-50 border border-red-200 rounded-lg text-center">
            <i class="fas fa-exclamation-triangle text-red-500 text-3xl mb-4"></i>
            <h2 class="text-xl font-semibold text-red-800">Không tìm thấy người dùng</h2>
            <p class="text-red-700 mt-2">
                Người dùng bạn đang tìm kiếm không tồn tại hoặc đã bị xóa. Vui lòng kiểm tra lại.
            </p>
            <a href="javascript:history.back()" class="mt-6 inline-block px-5 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition-colors">
                Quay lại
            </a>
        </div>';
        exit;
    }

    // Lấy danh sách tất cả các quyền để hiển thị trong form
    $stmt_quyen = $pdo->query("SELECT id, vai_tro FROM quyen ORDER BY vai_tro");
    $dsTatCaQuyen = $stmt_quyen->fetchAll(PDO::FETCH_ASSOC);

    // Lấy danh sách đánh giá (đã cập nhật để lấy id và trang_thai)
    $stmt2 = $pdo->prepare("
        SELECT dg.id, dg.trang_thai, info_kh.ho_ten AS ten_khach_hang, kh.avt AS avt_khach_hang, dg.diem, dg.binh_luan, dg.ngay_dg
        FROM danh_gia_mg dg
        JOIN nguoi_dung kh ON dg.id_khach_hang = kh.id
        JOIN info_nguoi_dung info_kh ON kh.id = info_kh.id_nguoi_dung
        WHERE dg.id_moi_gioi = :id ORDER BY dg.ngay_dg DESC
    ");
    $stmt2->execute([':id' => $id]);
    $reviews = $stmt2->fetchAll(PDO::FETCH_ASSOC);

    // Mảng định nghĩa màu và nhãn
    $genderMap = ['nam' => 'Nam', 'nu' => 'Nữ', 'khac' => 'Khác', 'chuacapnhat' => 'Chưa cập nhật'];
    $roleMap = ['quantri' => 'Quản trị', 'moigioi' => 'Môi giới', 'khachhang' => 'Khách hàng'];
    $statusMap = ['danghoatdong' => 'Đang hoạt động', 'chuakichhoat' => 'Chờ kích hoạt', 'khoa' => 'Đã khóa', 'tamngung' => 'Tạm ngưng'];
    $roleColors = ['quantri' => 'bg-red-100 text-red-700', 'moigioi' => 'bg-indigo-100 text-indigo-700', 'khachhang' => 'bg-teal-100 text-teal-700'];
    $statusColors = [ 'danghoatdong' => 'bg-green-100 text-green-700 border-green-300', 'chuakichhoat' => 'bg-gray-100 text-gray-700 border-gray-300', 'khoa' => 'bg-red-100 text-red-700 border-red-300','tamngung' => 'bg-yellow-100 text-yellow-700 border-yellow-300' ];

    // Xử lý mảng vai trò từ PostgreSQL
    $user['vai_tro_array'] = explode(',', trim($user['vai_tro'], '{}'));
    $user['id_quyen_array'] = explode(',', trim($user['id_quyen'], '{}'));
?>
<!DOCTYPE html>
<html lang="vi" class="h-full bg-gray-50">
<head>
    <meta charset="UTF-8">
    <title>Hồ sơ người dùng: <?= htmlspecialchars($user['ho_ten']) ?></title>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    </head>
<body>
    <div class="max-w-7xl mx-auto">
        <div class="mb-6 pb-4 border-b border-gray-200">
            <a href="javascript:history.back()" class="text-sm text-indigo-600 hover:text-indigo-800 mb-2 inline-block">&larr; Quay lại danh sách</a>
            <h1 class="text-2xl font-bold text-gray-800">Chi tiết người dùng</h1>
        </div>
        
        <div class="flex flex-col lg:flex-row gap-8">
            <aside class="w-full lg:w-1/3 xl:w-1/4">
                <div class="bg-white rounded-2xl shadow-lg border border-gray-200 p-6 text-center">
                    <div class="relative w-32 h-32 mx-auto">
                        <img src="../../../storage/pictures/avt/<?= htmlspecialchars($user['avt']) ?>" alt="Avatar" class="w-full h-full rounded-full border-4 border-white shadow-md object-cover" onerror="this.onerror=null; this.src='../../../storage/pictures/avt/avt.png';">
                    </div>
                    <h2 class="mt-4 text-xl font-bold text-gray-800"><?= htmlspecialchars($user['ho_ten']) ?></h2>
                    <p class="text-sm text-gray-500">@<?= htmlspecialchars($user['ten_dang_nhap']) ?></p>
                    <div class="mt-4 flex flex-wrap justify-center gap-2">
                        <?php foreach ($user['vai_tro_array'] as $role): if(empty($role)) continue; ?>
                            <span class="px-3 py-1 text-xs font-semibold rounded-full <?= $roleColors[$role] ?? 'bg-gray-100 text-gray-700' ?>">
                                <?= $roleMap[$role] ?? ucfirst($role) ?>
                            </span>
                        <?php endforeach; ?>
                    </div>
                    <?php if(!empty($user['mo_ta']) && $user['mo_ta'] !== 'chuacapnhat'): ?>
                        <p class="mt-4 text-sm text-gray-600 border-t border-gray-200 pt-4">
                            <?= nl2br(htmlspecialchars($user['mo_ta'])) ?>
                        </p>
                    <?php endif; ?>
                    <div class="mt-6 space-y-3">
                        <a href="trangchu.php?page=thietlap_guiemail_ngoidung&email=<?= urlencode($user['email']) ?>" class="w-full flex items-center justify-center gap-2 px-4 py-2 bg-indigo-600 text-sm text-white rounded-lg hover:bg-indigo-700 transition font-semibold">
                            <i class="fas fa-envelope"></i> Gửi Email
                        </a>
                        <a href="tel:<?= htmlspecialchars($user['so_dt']) ?>" class="w-full flex items-center justify-center gap-2 px-4 py-2 bg-gray-100 text-sm text-gray-800 rounded-lg hover:bg-gray-200 transition font-semibold border border-gray-300">
                            <i class="fas fa-phone"></i> Gọi điện
                        </a>
                    </div>
                </div>
            </aside>

            <main class="w-full lg:w-2/3 xl:w-3/4" x-data="{ tab: 'details' }">
                <div class="border-b border-gray-200">
                    <nav class="-mb-px flex space-x-6">
                        <button @click="tab = 'details'" :class="{ 'border-indigo-500 text-indigo-600': tab === 'details', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': tab !== 'details' }" class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                            Thông tin chi tiết
                        </button>
                        <?php if (in_array('moigioi', $user['vai_tro_array'])): ?>
                            <button @click="tab = 'reviews'" :class="{ 'border-indigo-500 text-indigo-600': tab === 'reviews', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': tab !== 'reviews' }" class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                                Đánh giá (<?= count($reviews) ?>)
                            </button>
                        <?php endif; ?>
                        <button @click="tab = 'settings'" :class="{ 'border-indigo-500 text-indigo-600': tab === 'settings', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': tab !== 'settings' }" class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                            Cài đặt & Quyền
                        </button>
                    </nav>
                </div>
                
                <div class="mt-6">
                    <div x-show="tab === 'details'" class="bg-white p-6 rounded-2xl shadow-lg border border-gray-200">
                        <h3 class="text-xl font-semibold text-gray-900 mb-4">Thông tin cá nhân & Liên hệ</h3>
                        <dl class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4 text-sm">
                             <div class="flex items-start gap-2"><i class="fas fa-envelope text-gray-400 mt-1"></i><div><dt class="font-medium text-gray-800">Email</dt><dd class="text-gray-600"><?= htmlspecialchars($user['email']) ?></dd></div></div>
                             <div class="flex items-start gap-2"><i class="fas fa-phone text-gray-400 mt-1"></i><div><dt class="font-medium text-gray-800">Số điện thoại</dt><dd class="text-gray-600"><?= htmlspecialchars($user['so_dt']) ?></dd></div></div>
                             <div class="flex items-start gap-2"><i class="fas fa-venus-mars text-gray-400 mt-1"></i><div><dt class="font-medium text-gray-800">Giới tính</dt><dd class="text-gray-600"><?= $genderMap[$user['gioi_tinh']] ?? 'N/A' ?></dd></div></div>
                             <div class="flex items-start gap-2"><i class="fas fa-birthday-cake text-gray-400 mt-1"></i><div><dt class="font-medium text-gray-800">Ngày sinh</dt><dd class="text-gray-600"><?= !empty($user['ngay_sinh']) ? date('d/m/Y', strtotime($user['ngay_sinh'])) : 'Chưa cập nhật' ?></dd></div></div>
                             <div class="md:col-span-2 flex items-start gap-2"><i class="fas fa-map-marker-alt text-gray-400 mt-1"></i><div><dt class="font-medium text-gray-800">Địa chỉ</dt><dd class="text-gray-600"><?= !empty($user['dia_chi']) ? htmlspecialchars($user['dia_chi']) : 'Chưa cập nhật' ?></dd></div></div>
                             <div class="md:col-span-2 flex items-start gap-2"><i class="fas fa-calendar-alt text-gray-400 mt-1"></i><div><dt class="font-medium text-gray-800">Ngày tham gia</dt><dd class="text-gray-600"><?= date('d/m/Y H:i', strtotime($user['ngay_tao'])) ?></dd></div></div>
                        </dl>
                    </div>

                    <div x-show="tab === 'reviews'" class="space-y-4">
                        <?php if (empty($reviews)): ?>
                            <div class="text-center py-12 px-4 bg-white rounded-2xl shadow-lg border border-gray-200">
                                <i class="fas fa-comment-slash text-4xl text-gray-300"></i>
                                <h3 class="mt-4 text-lg font-medium text-gray-800">Chưa có đánh giá</h3>
                                <p class="mt-1 text-sm text-gray-500">Người dùng này hiện chưa nhận được đánh giá nào.</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($reviews as $review): ?>
                                <div id="review-<?= $review['id'] ?>" class="relative bg-white p-5 rounded-2xl shadow-lg border border-gray-200 transition-opacity duration-300 <?= $review['trang_thai'] === 'an' ? 'opacity-60 bg-gray-50' : '' ?>">
                                    <?php if ($review['trang_thai'] === 'an'): ?>
                                        <span class="status-badge absolute top-3 right-3 bg-yellow-100 text-yellow-800 text-xs font-medium px-2.5 py-0.5 rounded-full">Đã ẩn</span>
                                    <?php endif; ?>
                                    <div class="flex items-center justify-between mb-2">
                                        <div class="flex items-center gap-3">
                                            <img src="../../../storage/pictures/avt/<?= htmlspecialchars($review['avt_khach_hang']) ?>" class="w-10 h-10 rounded-full object-cover" onerror="this.onerror=null; this.src='../../../storage/pictures/avt/avt.png';">
                                            <div>
                                                <p class="font-semibold text-gray-800"><?= htmlspecialchars($review['ten_khach_hang']) ?></p>
                                                <p class="text-xs text-gray-500"><?= date('d/m/Y', strtotime($review['ngay_dg'])) ?></p>
                                            </div>
                                        </div>
                                        <div class="text-yellow-500 text-sm">
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                <i class="fas fa-star <?= $i <= $review['diem'] ? '' : 'text-gray-300' ?>"></i>
                                            <?php endfor; ?>
                                        </div>
                                    </div>
                                    <p class="text-gray-600 text-sm italic mb-4">"<?= nl2br(htmlspecialchars($review['binh_luan'])) ?>"</p>
                                    <div class="border-t border-gray-200 pt-3 flex items-center justify-end gap-3">
                                        <?php if ($review['trang_thai'] === 'hien'): ?>
                                            <button onclick="handleReviewAction(this)" data-review-id="<?= $review['id'] ?>" data-action="an" class="action-btn text-sm text-gray-500 hover:text-yellow-600 font-medium transition-colors" title="Ẩn đánh giá"><i class="fas fa-eye-slash mr-1"></i> Ẩn</button>
                                        <?php else: ?>
                                            <button onclick="handleReviewAction(this)" data-review-id="<?= $review['id'] ?>" data-action="hien" class="action-btn text-sm text-gray-500 hover:text-green-600 font-medium transition-colors" title="Hiện lại đánh giá"><i class="fas fa-eye mr-1"></i> Hiện</button>
                                        <?php endif; ?>
                                        <button onclick="handleReviewAction(this)" data-review-id="<?= $review['id'] ?>" data-action="xoa" class="action-btn text-sm text-gray-500 hover:text-red-600 font-medium transition-colors" title="Xóa vĩnh viễn"><i class="fas fa-trash-alt mr-1"></i> Xóa</button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <div x-show="tab === 'settings'" class="space-y-6">
                        <div class="bg-white p-6 rounded-2xl shadow-lg border border-gray-200">
                             <h3 class="text-xl font-semibold text-gray-900 mb-4">Trạng thái tài khoản</h3>
                             <p class="text-sm text-gray-600 mb-2">Thay đổi trạng thái hoạt động của người dùng.</p>
                             <div class="flex items-center gap-4">
                                 <span class="px-3 py-1 text-sm font-semibold rounded-full <?= $statusColors[$user['trang_thai']] ?? '' ?>">
                                     <?= $statusMap[$user['trang_thai']] ?? 'N/A' ?>
                                 </span>
                             </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script>        
        async function handleReviewAction(button) {
            const reviewId = button.dataset.reviewId;
            const action = button.dataset.action;
            
            let confirmMessage = "";
            if (action === 'an') confirmMessage = "Bạn có chắc muốn ẩn đánh giá này không?";
            else if (action === 'hien') confirmMessage = "Bạn có chắc muốn hiện lại đánh giá này không?";
            else if (action === 'xoa') confirmMessage = "Hành động này không thể hoàn tác! Bạn có chắc chắn muốn XÓA VĨNH VIỄN đánh giá này?";
            
            if (!confirm(confirmMessage)) return;

            try {
                const response = await fetch('../../models/cn_trangthai_danhgia_nd_qt.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ review_id: reviewId, action: action })
                });

                const result = await response.json();

                if (result.status === 'success') {
                    const reviewCard = document.getElementById(`review-${reviewId}`);
                    if (action === 'xoa') {
                        reviewCard.style.transition = 'opacity 0.3s ease-out';
                        reviewCard.style.opacity = '0';
                        setTimeout(() => reviewCard.remove(), 300);
                    } else {
                        const isHidden = action === 'an';
                        reviewCard.classList.toggle('opacity-60', isHidden);
                        reviewCard.classList.toggle('bg-gray-50', isHidden);
                        
                        const statusBadge = reviewCard.querySelector('.status-badge');
                        if (isHidden) {
                            if (!statusBadge) {
                                const newBadge = document.createElement('span');
                                newBadge.className = 'status-badge absolute top-3 right-3 bg-yellow-100 text-yellow-800 text-xs font-medium px-2.5 py-0.5 rounded-full';
                                newBadge.innerText = 'Đã ẩn';
                                reviewCard.prepend(newBadge);
                            }
                        } else {
                            if (statusBadge) statusBadge.remove();
                        }
                        
                        const actionContainer = button.parentElement;
                        if(action === 'an') {
                            actionContainer.innerHTML = `
                                <button onclick="handleReviewAction(this)" data-review-id="${reviewId}" data-action="hien" class="action-btn text-sm text-gray-500 hover:text-green-600 font-medium transition-colors"><i class="fas fa-eye mr-1"></i> Hiện</button>
                                <button onclick="handleReviewAction(this)" data-review-id="${reviewId}" data-action="xoa" class="action-btn text-sm text-gray-500 hover:text-red-600 font-medium transition-colors"><i class="fas fa-trash-alt mr-1"></i> Xóa</button>
                            `;
                        } else {
                            actionContainer.innerHTML = `
                                <button onclick="handleReviewAction(this)" data-review-id="${reviewId}" data-action="an" class="action-btn text-sm text-gray-500 hover:text-yellow-600 font-medium transition-colors"><i class="fas fa-eye-slash mr-1"></i> Ẩn</button>
                                <button onclick="handleReviewAction(this)" data-review-id="${reviewId}" data-action="xoa" class="action-btn text-sm text-gray-500 hover:text-red-600 font-medium transition-colors"><i class="fas fa-trash-alt mr-1"></i> Xóa</button>
                            `;
                        }
                    }
                } else {
                    alert('Lỗi: ' + result.message);
                }
            } catch (error) {
                console.error('Lỗi fetch:', error);
                alert('Đã xảy ra lỗi kết nối. Vui lòng thử lại.');
            }
        }
    </script>
</body>
</html>