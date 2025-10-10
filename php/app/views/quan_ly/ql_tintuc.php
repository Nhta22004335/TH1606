<?php
    require_once "../../../config/database.php";
    $pdo = ketnoicsdl();

    $sql = "SELECT t.id, t.tieu_de, t.mo_ta, t.chuyen_muc, t.anh_tin, t.ngay_dang, COALESCE(t.luot_xem, 0) AS luot_xem, u.so_dt AS sdt, u.avt, COALESCE(i.ho_ten, u.ten_dang_nhap) AS ten_moi_gioi 
        FROM tin_tuc t JOIN nguoi_dung u ON t.id_khach_hang = u.id 
        LEFT JOIN info_nguoi_dung i ON u.id = i.id_nguoi_dung 
        WHERE t.trang_thai IN ('choduyet', 'dangban', 'daban', 'dathue') 
        ORDER BY t.ngay_dang DESC, luot_xem DESC";
    $stmt = $pdo->query($sql);
    $data = $stmt->fetchAll();


    $noibat = [];
    $tintuc = [];
    if (count($data) > 0) {
        $first_tin = array_shift($data);
        $noibat = [
            'img' => htmlspecialchars($first_tin['anh_tin']), 'chuyenmuc' => htmlspecialchars($first_tin['chuyen_muc']), 'tieude' => htmlspecialchars($first_tin['tieu_de']), 'mota' => htmlspecialchars(substr($first_tin['mo_ta'], 0, 200)) . (strlen($first_tin['mo_ta']) > 200 ? '...' : ''), 'moigioi' => htmlspecialchars($first_tin['ten_moi_gioi']), 'avt' => htmlspecialchars($first_tin['avt']), 'sdt' => htmlspecialchars($first_tin['sdt']), 'ngay' => date('d/m/Y', strtotime($first_tin['ngay_dang'])), 'view' => $first_tin['luot_xem'],
        ];
        foreach ($data as $tin) {
            $tintuc[] = [
                'img' => htmlspecialchars($tin['anh_tin']), 'tieude' => htmlspecialchars($tin['tieu_de']), 'mota' => htmlspecialchars(substr($tin['mo_ta'], 0, 100)) . (strlen($tin['mo_ta']) > 100 ? '...' : ''), 'moigioi' => htmlspecialchars($tin['ten_moi_gioi']), 'avt' => htmlspecialchars($tin['avt']), 'sdt' => htmlspecialchars($tin['sdt']), 'ngay' => date('d/m/Y', strtotime($tin['ngay_dang'])), 'view' => $tin['luot_xem'], 'chuyenmuc' => htmlspecialchars($tin['chuyen_muc']),
            ];
        }
    } else {
        $noibat = ['img' => 'chuacapnhat.png', 'chuyenmuc' => 'Chưa cập nhật', 'tieude' => 'Hiện chưa có tin tức nào.', 'mota' => 'Vui lòng kiểm tra lại hoặc thêm dữ liệu mới.', 'moigioi' => 'Hệ thống', 'avt' => 'avt.png', 'sdt' => 'N/A', 'ngay' => date('d/m/Y'), 'view' => 0];
    }

    // Xử lý bộ lọc từ URL
    $chuyenmuc = $_GET['chuyenmuc'] ?? '';
    $moigioi = $_GET['moigioi'] ?? '';
    $ngaydang = $_GET['ngaydang'] ?? '';

    if ($chuyenmuc || $moigioi || $ngaydang) {
        $filtered_tintuc = [];
        foreach ($tintuc as $tin) {
            $match = true;
            if ($chuyenmuc && strcasecmp($tin['chuyenmuc'], $chuyenmuc) !== 0) {
                $match = false;
            }
            if ($moigioi && stripos($tin['moigioi'], $moigioi) === false) {
                $match = false;
            }
            if ($ngaydang) {
                $tin_ngay = date('Y-m-d', strtotime($tin['ngay']));
                if ($tin_ngay !== $ngaydang) {
                    $match = false;
                }
            }
            if ($match) {
                $filtered_tintuc[] = $tin;
            }
        }
        $tintuc = $filtered_tintuc;
    }
?>

<!DOCTYPE html>
<html lang="vi" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <title>Tin tức Bất động sản</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>[x-cloak] { display: none !important; }</style>
</head>
<body class="h-full">

<div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    <header class="mb-8">
        <div class="sm:flex sm:items-center sm:justify-between">
            <div>
                <h1 class="text-3xl font-bold tracking-tight text-slate-900">Bảng tin Bất động sản</h1>
                <p class="mt-2 text-base text-slate-600">Khám phá các tin tức, xu hướng và cơ hội đầu tư mới nhất.</p>
            </div>
            <div class="mt-4 sm:mt-0">
                <a href="#" class="inline-flex items-center gap-2 px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <i class="fa-solid fa-plus"></i> Thêm tin mới
                </a>
            </div>
        </div>
    </header>

    <div class="grid grid-cols-1 lg:grid-cols-4 lg:gap-8">

        <aside class="lg:col-span-1 lg:sticky lg:top-8 self-start">
            <div class="bg-white p-5 shadow-lg rounded-lg">
                <h2 class="text-lg font-bold text-slate-800 border-b pb-3 mb-4 flex items-center gap-2">
                    <i class="fa-solid fa-filter text-indigo-600"></i> Bộ lọc
                </h2>
               <form id="filterForm" class="space-y-4">
                    <div>
                        <label for="chuyenmuc-filter" class="block text-sm font-medium text-slate-700">Chuyên mục</label>
                        <select id="chuyenmuc-filter" name="chuyenmuc" class="mt-1 block w-full px-3 py-2 bg-white border border-slate-300 rounded-md text-sm shadow-sm focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                            <option value="">Tất cả</option>
                            <option value="canho">Căn hộ</option>
                            <option value="datnen">Đất nền</option>
                            <option value="bietthu">Biệt thự</option>
                            <option value="nhapho">Nhà phố</option>
                        </select>
                    </div>
                    <div>
                        <label for="moigioi-filter" class="block text-sm font-medium text-slate-700">Tên môi giới</label>
                        <input type="text" id="moigioi-filter" name="moigioi" placeholder="Nhập tên..." class="mt-1 block w-full px-3 py-2 bg-white border border-slate-300 rounded-md text-sm shadow-sm placeholder-slate-400 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label for="ngaydang-filter" class="block text-sm font-medium text-slate-700">Ngày đăng</label>
                        <input type="date" id="ngaydang-filter" name="ngaydang" class="mt-1 block w-full px-3 py-2 bg-white border border-slate-300 rounded-md text-sm shadow-sm focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                    </div>
                    <button type="submit" class="w-full inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700">Áp dụng</button>
                </form>
            </div>
        </aside>

        <script>
            // 1. Tìm đến form bằng ID
            const filterForm = document.getElementById('filterForm');

            // 2. Lắng nghe sự kiện "submit" của form
            filterForm.addEventListener('submit', function(event) {
                // 3. Ngăn chặn hành vi submit mặc định (tải lại trang)
                event.preventDefault();

                // 4. Lấy giá trị từ các ô input
                const chuyenmuc = document.getElementById('chuyenmuc-filter').value;
                const moigioi = document.getElementById('moigioi-filter').value;
                const ngaydang = document.getElementById('ngaydang-filter').value;

                // 5. Xây dựng chuỗi truy vấn (query string) bằng URLSearchParams
                const params = new URLSearchParams();

                // Chỉ thêm tham số vào URL nếu nó có giá trị
                if (chuyenmuc) {
                    params.append('chuyenmuc', chuyenmuc);
                }
                if (moigioi) {
                    params.append('moigioi', moigioi);
                }
                if (ngaydang) {
                    params.append('ngaydang', ngaydang);
                }
                
                // Lấy đường dẫn trang hiện tại (không bao gồm query string cũ)
                const currentPath = window.location.pathname;

                // 6. Điều hướng trang đến URL mới với các tham số đã lọc
                window.location.href = currentPath + '?page=ql_tintuc&' + params.toString();
            });
        </script>

        <main class="lg:col-span-3 mt-8 lg:mt-0">
            <article class="bg-white rounded-lg shadow-lg overflow-hidden mb-10">
                <img src="../../../storage/pictures/anhtin/<?= $noibat['img'] ?>" alt="<?= $noibat['tieude'] ?>" class="w-full h-80 object-cover">
                <div class="p-6">
                    <span class="text-sm font-semibold text-indigo-600 uppercase"><?= $noibat['chuyenmuc'] ?></span>
                    <h2 class="text-3xl font-bold mt-2 mb-3 text-slate-900 leading-tight">
                        <a href="#" class="hover:text-indigo-700 transition-colors"><?= $noibat['tieude'] ?></a>
                    </h2>
                    <p class="text-slate-600 mb-5 text-base leading-relaxed"><?= $noibat['mota'] ?></p>
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between border-t pt-4">
                        <div class="flex items-center gap-3 mb-4 sm:mb-0">
                            <img src="../../../storage/pictures/avt/<?= $noibat['avt'] ?>" class="w-11 h-11 rounded-full border-2 border-white shadow">
                            <div>
                                <p class="font-semibold text-slate-800"><?= $noibat['moigioi'] ?></p>
                                <p class="text-xs text-slate-500"><?= $noibat['ngay'] ?> &bull; <?= number_format($noibat['view']) ?> lượt xem</p>
                            </div>
                        </div>
                        <a href="#" class="inline-flex items-center gap-2 px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700">
                            Đọc tiếp <i class="fa-solid fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </article>

            <div>
                <h3 class="text-2xl font-bold text-slate-900 mb-6 border-b pb-3">Tin tức khác</h3>
                <?php if (empty($tintuc)): ?>
                    <p class="text-slate-500 text-center py-8">Không có tin tức nào khác để hiển thị.</p>
                <?php else: ?>
                    <div class="grid md:grid-cols-2 gap-8">
                        <?php foreach ($tintuc as $tin): ?>
                            
                            <article class="bg-white rounded-lg shadow-lg overflow-hidden flex flex-col group">
                                <img src="../../../storage/pictures/anhtin/<?= $tin['img'] ?>" alt="<?= $tin['tieude'] ?>" class="w-full h-48 object-cover">
                                <div class="p-5 flex flex-col flex-1">
                                    <span class="text-xs font-semibold text-indigo-600 uppercase"><?= $tin['chuyenmuc'] ?></span>
                                    <h4 class="text-lg font-bold mt-2 mb-2 text-slate-900 flex-1">
                                        <a href="#" class="group-hover:text-indigo-700 transition-colors"><?= $tin['tieude'] ?></a>
                                    </h4>
                                    <div class="flex items-center gap-3 mt-4 border-t pt-4">
                                        <img src="../../../storage/pictures/avt/<?= $tin['avt'] ?>" class="w-9 h-9 rounded-full border-2 border-white shadow">
                                        <div>
                                            <p class="text-sm font-semibold text-slate-800"><?= $tin['moigioi'] ?></p>
                                            <p class="text-xs text-slate-500"><?= $tin['ngay'] ?> &bull; <?= number_format($tin['view']) ?> lượt xem</p>
                                        </div>
                                    </div>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
</div>

</body>
</html>