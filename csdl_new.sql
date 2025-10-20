-- CƠ SỞ DỮ LIỆU MYSQL - SÀN GIAO DỊCH BẤT ĐỘNG SẢN
-- Phiên bản đã được tối ưu và tăng cường ràng buộc.
-- Tương thích với MySQL 8.0.16+ (do sử dụng CHECK constraints).

-- ===================================================================
-- PHẦN 1: HÀM TÙY CHỈNH VÀ THIẾT LẬP
-- ===================================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- Xóa hàm cũ nếu tồn tại để tránh lỗi khi chạy lại script
DROP FUNCTION IF EXISTS `generate_custom_id`;

-- Hàm tùy chỉnh để tạo ID duy nhất và có thể sắp xếp theo thời gian
-- ID có dạng: 'YYYYMMDDHHMMSSmicros_random8chars' (ví dụ: '20251019134827123456_a8b1c5d9')
DELIMITER $$
CREATE FUNCTION `generate_custom_id`()
RETURNS VARCHAR(50)
DETERMINISTIC
BEGIN
    -- Kết hợp timestamp chính xác đến micro giây và một chuỗi ngẫu nhiên 8 ký tự
    RETURN CONCAT(DATE_FORMAT(NOW(6), '%Y%m%d%H%i%s%f'), '_', SUBSTRING(MD5(RAND()), 1, 8));
END$$
DELIMITER ;


-- ===================================================================
-- PHẦN 2: TẠO CẤU TRÚC BẢNG (CREATE TABLES)
-- ===================================================================

-- Bảng quyen
DROP TABLE IF EXISTS `quyen`;
CREATE TABLE `quyen` (
  `id` varchar(50) NOT NULL DEFAULT (generate_custom_id()),
  `vai_tro` varchar(50) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `vai_tro` (`vai_tro`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- Bảng nguoi_dung
DROP TABLE IF EXISTS `nguoi_dung`;
CREATE TABLE `nguoi_dung` (
  `id` varchar(50) NOT NULL DEFAULT (generate_custom_id()),
  `ten_dang_nhap` varchar(100) NOT NULL,
  `mat_khau` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `so_dt` varchar(20) DEFAULT 'chuacapnhat',
  `avt` text DEFAULT 'avt.png',
  `trang_thai` varchar(50) DEFAULT 'chuakichhoat',
  `hoat_dong` varchar(50) DEFAULT 'offline',
  `ngay_tao` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ten_dang_nhap` (`ten_dang_nhap`),
  UNIQUE KEY `email` (`email`),
  CONSTRAINT `chk_nguoi_dung_hoat_dong` CHECK ((`hoat_dong` in (_utf8mb4'online',_utf8mb4'offline'))),
  CONSTRAINT `chk_nguoi_dung_so_dt` CHECK ((regexp_like(`so_dt`,_utf8mb4'^[0-9]{1,11}$') or (`so_dt` = _utf8mb4'chuacapnhat'))),
  CONSTRAINT `chk_nguoi_dung_trang_thai` CHECK ((`trang_thai` in (_utf8mb4'danghoatdong',_utf8mb4'chuakichhoat',_utf8mb4'khoa',_utf8mb4'tamngung')))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- Bảng phan_quyen
DROP TABLE IF EXISTS `phan_quyen`;
CREATE TABLE `phan_quyen` (
  `id` varchar(50) NOT NULL DEFAULT (generate_custom_id()),
  `id_nguoi_dung` varchar(50) NOT NULL,
  `id_quyen` varchar(50) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `phan_quyen_id_nguoi_dung_id_quyen_key` (`id_nguoi_dung`,`id_quyen`),
  KEY `fk_phan_quyen_quyen` (`id_quyen`),
  CONSTRAINT `fk_phan_quyen_nguoi_dung` FOREIGN KEY (`id_nguoi_dung`) REFERENCES `nguoi_dung` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_phan_quyen_quyen` FOREIGN KEY (`id_quyen`) REFERENCES `quyen` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- Bảng info_nguoi_dung
DROP TABLE IF EXISTS `info_nguoi_dung`;
CREATE TABLE `info_nguoi_dung` (
  `id` varchar(50) NOT NULL DEFAULT (generate_custom_id()),
  `id_nguoi_dung` varchar(50) NOT NULL,
  `ho_ten` varchar(150) DEFAULT 'chuacapnhat',
  `gioi_tinh` varchar(20) DEFAULT 'chuacapnhat',
  `dia_chi` text,
  `ngay_sinh` date DEFAULT (date_sub(curdate(), interval 18 year)),
  `mo_ta` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `info_nguoi_dung_id_nguoi_dung_key` (`id_nguoi_dung`),
  CONSTRAINT `fk_info_nd` FOREIGN KEY (`id_nguoi_dung`) REFERENCES `nguoi_dung` (`id`) ON DELETE CASCADE,
  CONSTRAINT `chk_quan_tri_gioi_tinh` CHECK ((`gioi_tinh` in (_utf8mb4'nam',_utf8mb4'nu',_utf8mb4'khac',_utf8mb4'chuacapnhat'))),
  CONSTRAINT `chk_quan_tri_tuoi` CHECK ((`ngay_sinh` <= date_sub(curdate(), interval 18 year)))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- Bảng phien_dang_nhap
DROP TABLE IF EXISTS `phien_dang_nhap`;
CREATE TABLE `phien_dang_nhap` (
  `id` varchar(50) NOT NULL DEFAULT (generate_custom_id()),
  `id_nguoi_dung` varchar(50) NOT NULL,
  `bat_dau` datetime DEFAULT CURRENT_TIMESTAMP,
  `het_han` datetime DEFAULT NULL,
  `dang_hoat_dong` tinyint(1) DEFAULT '1',
  `selector` varchar(255) DEFAULT NULL,
  `verifier_hash` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_phien_dang_nhap_nguoi_dung` (`id_nguoi_dung`),
  CONSTRAINT `chk_phien_dang_nhap_time_range` CHECK (((`het_han` is null) or (`het_han` > `bat_dau`))),
  CONSTRAINT `fk_phien_dang_nhap_nguoi_dung` FOREIGN KEY (`id_nguoi_dung`) REFERENCES `nguoi_dung` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- Bảng yeu_cau_otp
DROP TABLE IF EXISTS `yeu_cau_otp`;
CREATE TABLE `yeu_cau_otp` (
  `id` varchar(50) NOT NULL DEFAULT (generate_custom_id()),
  `so_dt` varchar(20) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `user_data_json` json DEFAULT NULL,
  `otp_hash` varchar(255) DEFAULT NULL,
  `trang_thai` varchar(20) DEFAULT 'choxacnhan',
  `bat_dau` datetime DEFAULT CURRENT_TIMESTAMP,
  `het_han` datetime NOT NULL,
  `cap_nhat` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `so_lan_thu_sai` int DEFAULT '0',
  PRIMARY KEY (`id`),
  CONSTRAINT `chk_yeu_cau_otp_contact_only` CHECK ((((`so_dt` is not null) and (`email` is null)) or ((`so_dt` is null) and (`email` is not null)))),
  CONSTRAINT `chk_yeu_cau_otp_time_range` CHECK ((`het_han` > `bat_dau`)),
  CONSTRAINT `chk_yeu_cau_otp_trang_thai` CHECK ((`trang_thai` in (_utf8mb4'choxacnhan',_utf8mb4'daxacnhan',_utf8mb4'dahuy')))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- Bảng lich_su_xac_thuc
DROP TABLE IF EXISTS `lich_su_xac_thuc`;
CREATE TABLE `lich_su_xac_thuc` (
  `id` varchar(50) NOT NULL DEFAULT (generate_custom_id()),
  `id_nguoi_dung` varchar(50) DEFAULT NULL,
  `loai_su_kien` varchar(30) NOT NULL,
  `thoi_gian_bat_dau` datetime DEFAULT CURRENT_TIMESTAMP,
  `thoi_gian_ket_thuc` datetime DEFAULT NULL,
  `dia_chi_ip` varchar(45) DEFAULT NULL,
  `user_agent` text,
  `ghi_chu` text,
  PRIMARY KEY (`id`),
  KEY `fk_lich_su_xac_thuc_nguoi_dung` (`id_nguoi_dung`),
  CONSTRAINT `chk_lich_su_xac_thuc_time_range` CHECK (((`thoi_gian_ket_thuc` is null) or (`thoi_gian_ket_thuc` >= `thoi_gian_bat_dau`))),
  CONSTRAINT `fk_lich_su_xac_thuc_nguoi_dung` FOREIGN KEY (`id_nguoi_dung`) REFERENCES `nguoi_dung` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- Bảng danh_muc
DROP TABLE IF EXISTS `danh_muc`;
CREATE TABLE `danh_muc` (
  `id` varchar(50) NOT NULL DEFAULT (generate_custom_id()),
  `ma_danh_muc` varchar(50) NOT NULL,
  `ten_danh_muc` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ma_danh_muc` (`ma_danh_muc`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- Bảng bat_dong_san
DROP TABLE IF EXISTS `bat_dong_san`;
CREATE TABLE `bat_dong_san` (
  `id` varchar(50) NOT NULL DEFAULT (generate_custom_id()),
  `id_chu_so_huu` varchar(50) DEFAULT NULL,
  `id_danh_muc` varchar(50) DEFAULT NULL,
  `trang_thai` varchar(100) DEFAULT 'chuaduyet',
  `dia_chi_day_du` text NOT NULL,
  `ma_tinh_thanh` varchar(10) DEFAULT NULL,
  `ma_quan_huyen` varchar(10) DEFAULT NULL,
  `ma_phuong_xa` varchar(10) DEFAULT NULL,
  `vi_do` decimal(10,7) DEFAULT NULL,
  `kinh_do` decimal(10,7) DEFAULT NULL,
  `dien_tich_dat` decimal(10,2) DEFAULT NULL,
  `dien_tich_su_dung` decimal(10,2) DEFAULT NULL,
  `mat_tien` decimal(5,2) DEFAULT NULL,
  `duong_vao` decimal(5,2) DEFAULT NULL,
  `huong_nha` varchar(50) DEFAULT NULL,
  `so_tang` int unsigned DEFAULT '0',
  `so_phong_ngu` int unsigned DEFAULT '0',
  `so_phong_tam` int unsigned DEFAULT '0',
  `thong_tin_phap_ly` text,
  `dac_diem_chi_tiet` json DEFAULT NULL,
  `ngay_tao` datetime DEFAULT CURRENT_TIMESTAMP,
  `ngay_cap_nhat` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_bds_chuso_huu` (`id_chu_so_huu`),
  KEY `fk_bds_danhmuc` (`id_danh_muc`),
  CONSTRAINT `fk_bds_chuso_huu` FOREIGN KEY (`id_chu_so_huu`) REFERENCES `nguoi_dung` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_bds_danhmuc` FOREIGN KEY (`id_danh_muc`) REFERENCES `danh_muc` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- Bảng hinh_anh_bds
DROP TABLE IF EXISTS `hinh_anh_bds`;
CREATE TABLE `hinh_anh_bds` (
  `id` varchar(50) NOT NULL DEFAULT (generate_custom_id()),
  `id_bds` varchar(50) NOT NULL,
  `url` varchar(300) NOT NULL,
  `kich_thuoc` decimal(10,2) DEFAULT '0.00',
  `trang_thai` varchar(255) DEFAULT 'binhthuong',
  `mo_ta` varchar(200) DEFAULT 'chuacapnhat',
  `ngay_tao` datetime DEFAULT CURRENT_TIMESTAMP,
  `loai` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_hinh_anh_bds_bds` (`id_bds`),
  CONSTRAINT `chk_hinh_anh_bds_trang_thai` CHECK ((`trang_thai` in (_utf8mb4'binhthuong',_utf8mb4'nhe',_utf8mb4'trungbinh',_utf8mb4'nang'))),
  CONSTRAINT `fk_hinh_anh_bds_bds` FOREIGN KEY (`id_bds`) REFERENCES `bat_dong_san` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- Bảng danh_gia_bds
DROP TABLE IF EXISTS `danh_gia_bds`;
CREATE TABLE `danh_gia_bds` (
  `id` varchar(50) NOT NULL DEFAULT (generate_custom_id()),
  `id_nguoi_dung` varchar(50) DEFAULT NULL,
  `id_bds` varchar(50) NOT NULL,
  `diem` int NOT NULL,
  `binh_luan` text,
  `trang_thai` varchar(10) DEFAULT 'hien',
  `ngay_tao` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_danh_gia_bds_nd` (`id_nguoi_dung`),
  KEY `fk_danh_gia_bds_bds` (`id_bds`),
  CONSTRAINT `chk_danh_gia_bds_diem` CHECK ((`diem` >= 1 and `diem` <= 5)),
  CONSTRAINT `chk_danh_gia_bds_trang_thai` CHECK ((`trang_thai` in (_utf8mb4'hien',_utf8mb4'an'))),
  CONSTRAINT `fk_danh_gia_bds_bds` FOREIGN KEY (`id_bds`) REFERENCES `bat_dong_san` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_danh_gia_bds_nd` FOREIGN KEY (`id_nguoi_dung`) REFERENCES `nguoi_dung` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- Bảng bai_dang
DROP TABLE IF EXISTS `bai_dang`;
CREATE TABLE `bai_dang` (
  `id` varchar(50) NOT NULL DEFAULT (generate_custom_id()),
  `id_nguoi_dung` varchar(50) DEFAULT NULL,
  `id_bat_dong_san` varchar(50) DEFAULT NULL,
  `tieu_de` varchar(200) NOT NULL,
  `mo_ta` text,
  `dia_chi_lien_he` varchar(200) DEFAULT NULL,
  `hinh_thuc` varchar(50) NOT NULL,
  `trang_thai` varchar(50) DEFAULT 'chuaduyet',
  `ngay_dang` datetime DEFAULT CURRENT_TIMESTAMP,
  `ngay_het_han` datetime DEFAULT NULL,
  `luot_xem` int unsigned DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `fk_baidang_nguoidung` (`id_nguoi_dung`),
  KEY `fk_baidang_bds` (`id_bat_dong_san`),
  CONSTRAINT `chk_hinh_thuc_baidang` CHECK ((`hinh_thuc` in (_utf8mb4'ban',_utf8mb4'chothue'))),
  CONSTRAINT `fk_baidang_bds` FOREIGN KEY (`id_bat_dong_san`) REFERENCES `bat_dong_san` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_baidang_nguoidung` FOREIGN KEY (`id_nguoi_dung`) REFERENCES `nguoi_dung` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- Bảng binh_luan
DROP TABLE IF EXISTS `binh_luan`;
CREATE TABLE `binh_luan` (
  `id` varchar(50) NOT NULL DEFAULT (generate_custom_id()),
  `id_bai_dang` varchar(50) NOT NULL,
  `id_nguoi_dung` varchar(50) NOT NULL,
  `id_cha` varchar(50) DEFAULT NULL,
  `noi_dung` text NOT NULL,
  `ngay_tao` datetime DEFAULT CURRENT_TIMESTAMP,
  `ngay_sua` datetime DEFAULT NULL,
  `trang_thai` varchar(20) DEFAULT 'hienthi',
  PRIMARY KEY (`id`),
  KEY `fk_binhluan_baidang` (`id_bai_dang`),
  KEY `fk_binhluan_nguoidung` (`id_nguoi_dung`),
  KEY `fk_binhluan_cha` (`id_cha`),
  CONSTRAINT `binh_luan_trang_thai_check` CHECK ((`trang_thai` in (_utf8mb4'hienthi',_utf8mb4'an',_utf8mb4'xoa'))),
  CONSTRAINT `fk_binhluan_baidang` FOREIGN KEY (`id_bai_dang`) REFERENCES `bai_dang` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_binhluan_cha` FOREIGN KEY (`id_cha`) REFERENCES `binh_luan` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_binhluan_nguoidung` FOREIGN KEY (`id_nguoi_dung`) REFERENCES `nguoi_dung` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- Bảng giao_dich
DROP TABLE IF EXISTS `giao_dich`;
CREATE TABLE `giao_dich` (
  `id` varchar(50) NOT NULL DEFAULT (generate_custom_id()),
  `id_nguoi_dung` varchar(50) DEFAULT NULL,
  `id_nguoi_ban` varchar(50) DEFAULT NULL,
  `id_bds` varchar(50) DEFAULT NULL,
  `loai` varchar(50) NOT NULL,
  `ngay_giao_dich` datetime DEFAULT CURRENT_TIMESTAMP,
  `trang_thai` varchar(50) DEFAULT 'choxuly',
  PRIMARY KEY (`id`),
  KEY `fk_giao_dich_nd` (`id_nguoi_dung`),
  KEY `fk_giao_dich_nguoiban` (`id_nguoi_ban`),
  KEY `fk_giao_dich_bds` (`id_bds`),
  CONSTRAINT `chk_giao_dich_loai` CHECK ((`loai` in (_utf8mb4'mua',_utf8mb4'ban',_utf8mb4'thue'))),
  CONSTRAINT `chk_giao_dich_tt` CHECK ((`trang_thai` in (_utf8mb4'choxuly',_utf8mb4'dangxuly',_utf8mb4'hoantat',_utf8mb4'dahuy'))),
  CONSTRAINT `fk_giao_dich_bds` FOREIGN KEY (`id_bds`) REFERENCES `bat_dong_san` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_giao_dich_nd` FOREIGN KEY (`id_nguoi_dung`) REFERENCES `nguoi_dung` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_giao_dich_nguoiban` FOREIGN KEY (`id_nguoi_ban`) REFERENCES `nguoi_dung` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- Bảng ke_hoach_thanh_toan
DROP TABLE IF EXISTS `ke_hoach_thanh_toan`;
CREATE TABLE `ke_hoach_thanh_toan` (
  `id` varchar(50) NOT NULL DEFAULT (generate_custom_id()),
  `id_giao_dich` varchar(50) NOT NULL,
  `tong_gia_tri` decimal(18,2) NOT NULL,
  `so_tien_da_tt` decimal(18,2) DEFAULT '0.00',
  `trang_thai_tt` varchar(50) DEFAULT 'chuathanhtoan',
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_giao_dich` (`id_giao_dich`),
  CONSTRAINT `chk_khtt_so_tien_da_tt` CHECK ((`so_tien_da_tt` >= 0)),
  CONSTRAINT `chk_khtt_tong_gia_tri` CHECK ((`tong_gia_tri` >= 0)),
  CONSTRAINT `chk_khtt_trang_thai` CHECK ((`trang_thai_tt` in (_utf8mb4'chuathanhtoan',_utf8mb4'dangthanhtoan',_utf8mb4'hoantat'))),
  CONSTRAINT `fk_khtt_gd` FOREIGN KEY (`id_giao_dich`) REFERENCES `giao_dich` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- Bảng dot_thanh_toan
DROP TABLE IF EXISTS `dot_thanh_toan`;
CREATE TABLE `dot_thanh_toan` (
  `id` varchar(50) NOT NULL DEFAULT (generate_custom_id()),
  `id_giao_dich` varchar(50) NOT NULL,
  `lan_tt` int unsigned NOT NULL,
  `so_tien_tt` decimal(18,2) NOT NULL,
  `ngay_tt` datetime DEFAULT CURRENT_TIMESTAMP,
  `phuong_thuc` varchar(100) DEFAULT NULL,
  `ghichu` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_giao_dich` (`id_giao_dich`,`lan_tt`),
  CONSTRAINT `chk_dtt_so_tien_tt` CHECK ((`so_tien_tt` > 0)),
  CONSTRAINT `fk_dtt_gd` FOREIGN KEY (`id_giao_dich`) REFERENCES `giao_dich` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- Bảng dot_thanh_toan_ct
DROP TABLE IF EXISTS `dot_thanh_toan_ct`;
CREATE TABLE `dot_thanh_toan_ct` (
  `id` varchar(50) NOT NULL DEFAULT (generate_custom_id()),
  `id_dot_thanh_toan` varchar(50) NOT NULL,
  `id_bds` varchar(50) DEFAULT NULL,
  `so_luong` int unsigned DEFAULT '1',
  `so_tien` decimal(18,2) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_dttct_dtt` (`id_dot_thanh_toan`),
  KEY `fk_dttct_bds` (`id_bds`),
  CONSTRAINT `chk_dttct_so_tien` CHECK ((`so_tien` >= 0)),
  CONSTRAINT `fk_dttct_bds` FOREIGN KEY (`id_bds`) REFERENCES `bat_dong_san` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_dttct_dtt` FOREIGN KEY (`id_dot_thanh_toan`) REFERENCES `dot_thanh_toan` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- Bảng thong_bao
DROP TABLE IF EXISTS `thong_bao`;
CREATE TABLE `thong_bao` (
  `id` varchar(50) NOT NULL DEFAULT (generate_custom_id()),
  `id_nguoi_dung` varchar(50) NOT NULL,
  `id_nguoi_gui` varchar(50) DEFAULT NULL,
  `loai` varchar(50) NOT NULL,
  `tieu_de` varchar(255) NOT NULL,
  `noi_dung` text NOT NULL,
  `thoi_gian_gui` datetime DEFAULT CURRENT_TIMESTAMP,
  `trang_thai` varchar(20) DEFAULT 'chuaxem',
  PRIMARY KEY (`id`),
  KEY `fk_thong_bao_nd` (`id_nguoi_dung`),
  KEY `fk_thong_bao_nguoigui` (`id_nguoi_gui`),
  CONSTRAINT `chk_thong_bao_trang_thai` CHECK ((`trang_thai` in (_utf8mb4'chuaxem',_utf8mb4'daxem'))),
  CONSTRAINT `fk_thong_bao_nd` FOREIGN KEY (`id_nguoi_dung`) REFERENCES `nguoi_dung` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_thong_bao_nguoigui` FOREIGN KEY (`id_nguoi_gui`) REFERENCES `nguoi_dung` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- Bảng danh_gia_mg
DROP TABLE IF EXISTS `danh_gia_mg`;
CREATE TABLE `danh_gia_mg` (
  `id` varchar(50) NOT NULL DEFAULT (generate_custom_id()),
  `id_khach_hang` varchar(50) NOT NULL,
  `id_moi_gioi` varchar(50) NOT NULL,
  `diem` int NOT NULL,
  `binh_luan` text,
  `ngay_dg` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_danh_gia_kh_nd` (`id_khach_hang`),
  KEY `fk_danh_gia_mg_nd` (`id_moi_gioi`),
  CONSTRAINT `chk_danh_gia_mg_diem` CHECK ((`diem` >= 1 and `diem` <= 5)),
  CONSTRAINT `chk_kh_mg_khacnhau` CHECK ((`id_khach_hang` <> `id_moi_gioi`)),
  CONSTRAINT `fk_danh_gia_kh_nd` FOREIGN KEY (`id_khach_hang`) REFERENCES `nguoi_dung` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_danh_gia_mg_nd` FOREIGN KEY (`id_moi_gioi`) REFERENCES `nguoi_dung` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- Bảng bieu_mau
DROP TABLE IF EXISTS `bieu_mau`;
CREATE TABLE `bieu_mau` (
  `id` varchar(50) NOT NULL DEFAULT (generate_custom_id()),
  `tieu_de` varchar(255) NOT NULL,
  `loai` varchar(100) NOT NULL,
  `ben_mua` varchar(50) NOT NULL,
  `ben_ban` varchar(50) NOT NULL,
  `trang_thai` varchar(50) DEFAULT 'choduyet',
  `tep_dk` varchar(255) DEFAULT NULL,
  `ngay_tao` datetime DEFAULT CURRENT_TIMESTAMP,
  `ngay_cn` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_benmua` (`ben_mua`),
  KEY `fk_benban` (`ben_ban`),
  CONSTRAINT `chk_trangthai` CHECK ((`trang_thai` in (_utf8mb4'choduyet',_utf8mb4'daduyet',_utf8mb4'daky',_utf8mb4'huy'))),
  CONSTRAINT `fk_benban` FOREIGN KEY (`ben_ban`) REFERENCES `nguoi_dung` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_benmua` FOREIGN KEY (`ben_mua`) REFERENCES `nguoi_dung` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- Bảng yeu_cau
DROP TABLE IF EXISTS `yeu_cau`;
CREATE TABLE `yeu_cau` (
  `id` varchar(50) NOT NULL DEFAULT (generate_custom_id()),
  `id_nguoi_dung` varchar(50) NOT NULL,
  `id_moi_gioi` varchar(50) NOT NULL,
  `loai` varchar(100) NOT NULL,
  `id_bds` varchar(50) DEFAULT NULL,
  `trang_thai` varchar(50) DEFAULT 'choxuly',
  `mo_ta_chi_tiet` text,
  `ngay_tao` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_yeucau_nguoidung` (`id_nguoi_dung`),
  KEY `fk_yeucau_moigioi` (`id_moi_gioi`),
  KEY `fk_yeucau_bds` (`id_bds`),
  CONSTRAINT `chk_yeucau_loai` CHECK ((`loai` in (_utf8mb4'mua',_utf8mb4'ban',_utf8mb4'thue'))),
  CONSTRAINT `chk_yeucau_trangthai` CHECK ((`trang_thai` in (_utf8mb4'choxuly',_utf8mb4'daduyet',_utf8mb4'dahuy'))),
  CONSTRAINT `fk_yeucau_bds` FOREIGN KEY (`id_bds`) REFERENCES `bat_dong_san` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_yeucau_moigioi` FOREIGN KEY (`id_moi_gioi`) REFERENCES `nguoi_dung` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_yeucau_nguoidung` FOREIGN KEY (`id_nguoi_dung`) REFERENCES `nguoi_dung` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- Bảng lich_trinh
DROP TABLE IF EXISTS `lich_trinh`;
CREATE TABLE `lich_trinh` (
  `id` varchar(50) NOT NULL DEFAULT (generate_custom_id()),
  `id_khach_hang` varchar(50) NOT NULL,
  `id_moi_gioi` varchar(50) NOT NULL,
  `thoi_gian_bat_dau` timestamp NOT NULL,
  `thoi_gian_ket_thuc` timestamp NOT NULL,
  `trang_thai` varchar(50) NOT NULL DEFAULT 'choxacnhan',
  `ghi_chu` text,
  `tieu_de` varchar(255) DEFAULT NULL,
  `dia_diem` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_lichtrinh_khachhang` (`id_khach_hang`),
  KEY `fk_lichtrinh_moigioi` (`id_moi_gioi`),
  CONSTRAINT `chk_lichtrinh_trangthai` CHECK ((`trang_thai` in (_utf8mb4'choxacnhan',_utf8mb4'daxacnhan',_utf8mb4'dahuy'))),
  CONSTRAINT `chk_thoigian_hople` CHECK ((`thoi_gian_ket_thuc` > `thoi_gian_bat_dau`)),
  CONSTRAINT `fk_lichtrinh_khachhang` FOREIGN KEY (`id_khach_hang`) REFERENCES `nguoi_dung` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_lichtrinh_moigioi` FOREIGN KEY (`id_moi_gioi`) REFERENCES `nguoi_dung` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- Bảng tin_tuc
DROP TABLE IF EXISTS `tin_tuc`;
CREATE TABLE `tin_tuc` (
  `id` varchar(50) NOT NULL DEFAULT (generate_custom_id()),
  `id_khach_hang` varchar(50) NOT NULL,
  `tieu_de` varchar(200) NOT NULL DEFAULT 'chuacapnhat',
  `mo_ta` text,
  `chuyen_muc` varchar(100) DEFAULT 'chuacapnhat',
  `trang_thai` varchar(50) DEFAULT 'choduyet',
  `anh_tin` text,
  `luot_xem` int unsigned DEFAULT '0',
  `ngay_dang` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_tin_khachhang` (`id_khach_hang`),
  CONSTRAINT `fk_tin_khachhang` FOREIGN KEY (`id_khach_hang`) REFERENCES `nguoi_dung` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- Bảng hop_thoai
DROP TABLE IF EXISTS `hop_thoai`;
CREATE TABLE `hop_thoai` (
  `id` varchar(50) NOT NULL DEFAULT (generate_custom_id()),
  `id_nguoi_1` varchar(50) NOT NULL,
  `id_nguoi_2` varchar(50) NOT NULL,
  `da_khoa` tinyint(1) DEFAULT '0',
  `da_xoa` tinyint(1) DEFAULT '0',
  `ngay_tao` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_cap_nguoi` (`id_nguoi_1`,`id_nguoi_2`),
  KEY `fk_ht_nguoi2` (`id_nguoi_2`),
  CONSTRAINT `chk_khacnguoi` CHECK ((`id_nguoi_1` <> `id_nguoi_2`)),
  CONSTRAINT `chk_hop_thoai_order` CHECK (`id_nguoi_1` < `id_nguoi_2`), -- Ràng buộc mới chặt chẽ hơn
  CONSTRAINT `fk_ht_nguoi1` FOREIGN KEY (`id_nguoi_1`) REFERENCES `nguoi_dung` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_ht_nguoi2` FOREIGN KEY (`id_nguoi_2`) REFERENCES `nguoi_dung` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- Bảng tin_nhan
DROP TABLE IF EXISTS `tin_nhan`;
CREATE TABLE `tin_nhan` (
  `id` varchar(50) NOT NULL DEFAULT (generate_custom_id()),
  `id_hop_thoai` varchar(50) NOT NULL,
  `nguoi_gui` varchar(50) NOT NULL,
  `noi_dung` text,
  `anh_tn` text,
  `video_tn` text,
  `tg_gui` datetime DEFAULT CURRENT_TIMESTAMP,
  `trang_thai` varchar(20) DEFAULT 'chua_doc',
  PRIMARY KEY (`id`),
  KEY `fk_tn_hopthoai` (`id_hop_thoai`),
  KEY `fk_tn_nguoi_gui` (`nguoi_gui`),
  CONSTRAINT `chk_tin_nhan_noi_dung` CHECK (((`noi_dung` is not null) or (`anh_tn` is not null) or (`video_tn` is not null))),
  CONSTRAINT `chk_tin_nhan_trang_thai` CHECK ((`trang_thai` in (_utf8mb4'chua_doc',_utf8mb4'da_doc',_utf8mb4'xoa'))),
  CONSTRAINT `fk_tn_hopthoai` FOREIGN KEY (`id_hop_thoai`) REFERENCES `hop_thoai` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_tn_nguoi_gui` FOREIGN KEY (`nguoi_gui`) REFERENCES `nguoi_dung` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- Bảng lich_su_tim_kiem
DROP TABLE IF EXISTS `lich_su_tim_kiem`;
CREATE TABLE `lich_su_tim_kiem` (
  `id` varchar(50) NOT NULL DEFAULT (generate_custom_id()),
  `id_nguoi_dung` varchar(50) DEFAULT NULL,
  `tu_khoa_tim_kiem` text NOT NULL,
  `thoi_gian_tim_kiem` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_lstk_nd` (`id_nguoi_dung`),
  CONSTRAINT `fk_lstk_nd` FOREIGN KEY (`id_nguoi_dung`) REFERENCES `nguoi_dung` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- Bảng lich_su_xem_bds
DROP TABLE IF EXISTS `lich_su_xem_bds`;
CREATE TABLE `lich_su_xem_bds` (
  `id` varchar(50) NOT NULL DEFAULT (generate_custom_id()),
  `id_nguoi_dung` varchar(50) NOT NULL,
  `id_bds` varchar(50) NOT NULL,
  `thoi_gian_xem` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_lsxb_nd` (`id_nguoi_dung`),
  KEY `fk_lsxb_bds` (`id_bds`),
  CONSTRAINT `fk_lsxb_bds` FOREIGN KEY (`id_bds`) REFERENCES `bat_dong_san` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_lsxb_nd` FOREIGN KEY (`id_nguoi_dung`) REFERENCES `nguoi_dung` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- Bảng lich_su_mua_hang
DROP TABLE IF EXISTS `lich_su_mua_hang`;
CREATE TABLE `lich_su_mua_hang` (
  `id` varchar(50) NOT NULL DEFAULT (generate_custom_id()),
  `id_nguoi_dung` varchar(50) NOT NULL,
  `id_bds` varchar(50) NOT NULL,
  `so_luong` int unsigned NOT NULL,
  `gia_tai_thoi_diem_mua` decimal(18,2) NOT NULL,
  `tong_tien` decimal(20,2) NOT NULL,
  `ngay_mua` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_lsmh_nd` (`id_nguoi_dung`),
  KEY `fk_lsmh_bds` (`id_bds`),
  CONSTRAINT `chk_lsmh_gia` CHECK ((`gia_tai_thoi_diem_mua` >= 0)),
  CONSTRAINT `chk_lsmh_so_luong` CHECK ((`so_luong` > 0)),
  CONSTRAINT `fk_lsmh_bds` FOREIGN KEY (`id_bds`) REFERENCES `bat_dong_san` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `fk_lsmh_nd` FOREIGN KEY (`id_nguoi_dung`) REFERENCES `nguoi_dung` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ===================================================================
-- PHẦN 3: TẠO TRIGGERS
-- ===================================================================

DROP TRIGGER IF EXISTS `trigger_tao_info_nguoi_dung`;
DELIMITER $$
CREATE TRIGGER `trigger_tao_info_nguoi_dung` AFTER INSERT ON `nguoi_dung` FOR EACH ROW
BEGIN
    INSERT INTO info_nguoi_dung (id, id_nguoi_dung)
    VALUES (generate_custom_id(), NEW.id);
END$$
DELIMITER ;

DROP TRIGGER IF EXISTS `trigger_tinh_tong_tien_mua_hang`;
DELIMITER $$
CREATE TRIGGER `trigger_tinh_tong_tien_mua_hang` BEFORE INSERT ON `lich_su_mua_hang` FOR EACH ROW
BEGIN
    SET NEW.tong_tien = NEW.so_luong * NEW.gia_tai_thoi_diem_mua;
END$$
DELIMITER ;


-- ===================================================================
-- PHẦN 4: CHÈN DỮ LIỆU MẪU (INSERT DATA)
-- ===================================================================
INSERT INTO `phan_quyen`(`id_nguoi_dung`, `id_quyen`) VALUES ('20251019115300801752_685e6eed','20251019115700259347_1_b844ed2cace2')
-- Chèn dữ liệu cho bảng 'quyen'
INSERT INTO `quyen` (`id`, `vai_tro`) VALUES
('bd8fc7f4-7941-4bae-80c4-ede4e907a904', 'quantri'),
('21ea2b50-e9d2-4894-bc4a-a9818ef226b1', 'moigioi'),
('6bc0b436-c0ab-4970-82b0-b0907136c9f0', 'khachhang');

-- Chèn dữ liệu cho bảng 'nguoi_dung'
-- (Lưu ý: Trigger sẽ tự động chèn dữ liệu tương ứng vào 'info_nguoi_dung')
INSERT INTO `nguoi_dung` (`id`, `ten_dang_nhap`, `mat_khau`, `email`, `so_dt`, `avt`, `trang_thai`, `hoat_dong`, `ngay_tao`) VALUES
('8e233604-c830-4df5-b863-29ec060327d3', 'phatct', '$argon2id$v=19$m=65536,t=3,p=4$l4ptltIvcKNyDeuOZ2dfDg$XBg6sR18fXH+1Uj7DcfbXg08dz6tb61tko1U+JyIzIE', '21004286demo@st.vlute.edu.vn', '0915450242', 'avt.png', 'chuakichhoat', 'offline', '2025-10-12 19:47:07'),
('1cabfcda-923b-400f-b05d-9b900516380c', 'khanv', '$argon2id$v=19$m=65536,t=3,p=4$l4ptltIvcKNyDeuOZ2dfDg$XBg6sR18fXH+1Uj7DcfbXg08dz6tb61tko1U+JyIzIE', '21004292demo@st.vlute.edu.vn', '0961646024', 'avt.png', 'khoa', 'online', '2025-10-12 19:47:07'),
('144ee4c9-bf24-438f-8a6b-e15dd0e71705', 'datln', '$argon2id$v=19$m=65536,t=3,p=4$l4ptltIvcKNyDeuOZ2dfDg$XBg6sR18fXH+1Uj7DcfbXg08dz6tb61tko1U+JyIzIE', '22004001demo@st.vlute.edu.vn', '0928781845', 'avt.png', 'danghoatdong', 'offline', '2025-10-12 19:47:07'),
('ed9f5adb-413b-43cc-81f1-99f0ca57b321', 'tuanna', '$argon2id$v=19$m=65536,t=3,p=4$l4ptltIvcKNyDeuOZ2dfDg$XBg6sR18fXH+1Uj7DcfbXg08dz6tb61tko1U+JyIzIE', '22004004demo@st.vlute.edu.vn', '0945432507', 'avt.png', 'chuakichhoat', 'online', '2025-10-12 19:47:07'),
('53d15311-5c74-452a-8988-e9e1b683efad', 'anhtd', '$argon2id$v=19$m=65536,t=3,p=4$l4ptltIvcKNyDeuOZ2dfDg$XBg6sR18fXH+1Uj7DcfbXg08dz6tb61tko1U+JyIzIE', '22004009demo@st.vlute.edu.vn', '0932827377', 'avt.png', 'khoa', 'offline', '2025-10-12 19:47:07'),
('f37ca5eb-d199-46a7-9c75-70df9c9772bc', 'khoant', '$argon2id$v=19$m=65536,t=3,p=4$l4ptltIvcKNyDeuOZ2dfDg$XBg6sR18fXH+1Uj7DcfbXg08dz6tb61tko1U+JyIzIE', '22004011demo@st.vlute.edu.vn', '0921122047', 'avt.png', 'danghoatdong', 'online', '2025-10-12 19:47:07'),
('9e4364ac-289e-4f4a-b18f-57bb5d05a336', 'loilt', '$argon2id$v=19$m=65536,t=3,p=4$l4ptltIvcKNyDeuOZ2dfDg$XBg6sR18fXH+1Uj7DcfbXg08dz6tb61tko1U+JyIzIE', '22004012demo@st.vlute.edu.vn', '0974003433', 'avt.png', 'chuakichhoat', 'offline', '2025-10-12 19:47:07'),
('34e0c86a-a19f-4042-bbef-371a64693ba1', 'huynv', '$argon2id$v=19$m=65536,t=3,p=4$l4ptltIvcKNyDeuOZ2dfDg$XBg6sR18fXH+1Uj7DcfbXg08dz6tb61tko1U+JyIzIE', '22004016demo@st.vlute.edu.vn', '0939255514', 'avt.png', 'danghoatdong', 'offline', '2025-10-12 19:47:07'),
('f5f4022f-eccc-48e6-a435-1a9b1cdb3f3a', 'huongnn', '$argon2id$v=19$m=65536,t=3,p=4$l4ptltIvcKNyDeuOZ2dfDg$XBg6sR18fXH+1Uj7DcfbXg08dz6tb61tko1U+JyIzIE', '22004020demo@st.vlute.edu.vn', '0997483742', 'avt.png', 'chuakichhoat', 'online', '2025-10-12 19:47:07'),
('5ee334f1-bd31-41b7-8672-df8903f2747a', 'huyhg', '$argon2id$v=19$m=65536,t=3,p=4$l4ptltIvcKNyDeuOZ2dfDg$XBg6sR18fXH+1Uj7DcfbXg08dz6tb61tko1U+JyIzIE', '22004027demo@st.vlute.edu.vn', '0969846670', 'avt.png', 'khoa', 'offline', '2025-10-12 19:47:07'),
('79039562-eda7-4a5b-94cb-0cca2b078742', 'nguyenpt', '$argon2id$v=19$m=65536,t=3,p=4$l4ptltIvcKNyDeuOZ2dfDg$XBg6sR18fXH+1Uj7DcfbXg08dz6tb61tko1U+JyIzIE', '22004044demo@st.vlute.edu.vn', '0934549475', 'avt.png', 'danghoatdong', 'online', '2025-10-12 19:47:07'),
('fb9d3af2-aeef-46b1-a91c-438cf099a636', 'vanda', '$argon2id$v=19$m=65536,t=3,p=4$l4ptltIvcKNyDeuOZ2dfDg$XBg6sR18fXH+1Uj7DcfbXg08dz6tb61tko1U+JyIzIE', '22004046demo@st.vlute.edu.vn', '0994955634', 'avt.png', 'chuakichhoat', 'offline', '2025-10-12 19:47:07'),
('8fe15279-afda-4edc-bd9c-c2c7307df4c4', 'hanhk', '$argon2id$v=19$m=65536,t=3,p=4$l4ptltIvcKNyDeuOZ2dfDg$XBg6sR18fXH+1Uj7DcfbXg08dz6tb61tko1U+JyIzIE', '22004048demo@st.vlute.edu.vn', '0983523870', 'avt.png', 'khoa', 'online', '2025-10-12 19:47:07'),
('9976c99e-d95f-4e55-92ed-195d29be7ba6', 'thaivh', '$argon2id$v=19$m=65536,t=3,p=4$l4ptltIvcKNyDeuOZ2dfDg$XBg6sR18fXH+1Uj7DcfbXg08dz6tb61tko1U+JyIzIE', '22004049demo@st.vlute.edu.vn', '0925818262', 'avt.png', 'danghoatdong', 'offline', '2025-10-12 19:47:07'),
('07121b39-ffae-4f5a-be25-d9af117b1a8c', 'quynhvt', '$argon2id$v=19$m=65536,t=3,p=4$l4ptltIvcKNyDeuOZ2dfDg$XBg6sR18fXH+1Uj7DcfbXg08dz6tb61tko1U+JyIzIE', '22004053demo@st.vlute.edu.vn', '0948995340', 'avt.png', 'chuakichhoat', 'online', '2025-10-12 19:47:07'),
('db5ac513-6077-4717-832c-ae37eda2c1d1', 'nhudt', '$argon2id$v=19$m=65536,t=3,p=4$l4ptltIvcKNyDeuOZ2dfDg$XBg6sR18fXH+1Uj7DcfbXg08dz6tb61tko1U+JyIzIE', '22004058demo@st.vlute.edu.vn', '0937451590', 'avt.png', 'khoa', 'offline', '2025-10-12 19:47:07'),
('f64c8b9b-8b2a-4c16-87a0-9354988951d9', 'thaopt', '$argon2id$v=19$m=65536,t=3,p=4$l4ptltIvcKNyDeuOZ2dfDg$XBg6sR18fXH+1Uj7DcfbXg08dz6tb61tko1U+JyIzIE', '22004059demo@st.vlute.edu.vn', '0966812799', 'avt.png', 'danghoatdong', 'online', '2025-10-12 19:47:07'),
('ab903ef8-936c-425a-ad3a-68b69aafa9f1', 'thangnd', '$argon2id$v=19$m=65536,t=3,p=4$l4ptltIvcKNyDeuOZ2dfDg$XBg6sR18fXH+1Uj7DcfbXg08dz6tb61tko1U+JyIzIE', '22004063demo@st.vlute.edu.vn', '0956003469', 'avt.png', 'chuakichhoat', 'offline', '2025-10-12 19:47:07'),
('186acf33-877d-4963-902f-35bbfa0d6ecf', 'tunt', '$argon2id$v=19$m=65536,t=3,p=4$l4ptltIvcKNyDeuOZ2dfDg$XBg6sR18fXH+1Uj7DcfbXg08dz6tb61tko1U+JyIzIE', '22004067demo@st.vlute.edu.vn', '0960100354', 'avt.png', 'khoa', 'online', '2025-10-12 19:47:07'),
('7765d2a9-2e1c-41bd-86ad-c63e3f4cd079', 'dangtq', '$argon2id$v=19$m=65536,t=3,p=4$l4ptltIvcKNyDeuOZ2dfDg$XBg6sR18fXH+1Uj7DcfbXg08dz6tb61tko1U+JyIzIE', '22004069demo@st.vlute.edu.vn', '0922246792', 'avt.png', 'danghoatdong', 'offline', '2025-10-12 19:47:07'),
('b11d78c1-73a5-4d18-8002-c505ef2e9986', 'thienhl', '$argon2id$v=19$m=65536,t=3,p=4$l4ptltIvcKNyDeuOZ2dfDg$XBg6sR18fXH+1Uj7DcfbXg08dz6tb61tko1U+JyIzIE', '22004096demo@st.vlute.edu.vn', '0962174100', 'avt.png', 'chuakichhoat', 'online', '2025-10-12 19:47:07'),
('29e897be-0b07-4051-bf51-04ee0286f394', 'hanpt', '$argon2id$v=19$m=65536,t=3,p=4$l4ptltIvcKNyDeuOZ2dfDg$XBg6sR18fXH+1Uj7DcfbXg08dz6tb61tko1U+JyIzIE', '22004100demo@st.vlute.edu.vn', '0937202624', 'avt.png', 'khoa', 'offline', '2025-10-12 19:47:07'),
('7c68c28a-7d22-4489-bf78-09771d1af05a', 'diennm', '$argon2id$v=19$m=65536,t=3,p=4$l4ptltIvcKNyDeuOZ2dfDg$XBg6sR18fXH+1Uj7DcfbXg08dz6tb61tko1U+JyIzIE', '22004127demo@st.vlute.edu.vn', '0961587089', 'avt.png', 'danghoatdong', 'online', '2025-10-12 19:47:07'),
('78ed62c4-70ed-42c1-898b-6e400c6def37', 'nambt', '$argon2id$v=19$m=65536,t=3,p=4$l4ptltIvcKNyDeuOZ2dfDg$XBg6sR18fXH+1Uj7DcfbXg08dz6tb61tko1U+JyIzIE', '22004175demo@st.vlute.edu.vn', '0969118270', 'avt.png', 'chuakichhoat', 'offline', '2025-10-12 19:47:07'),
('90e4f116-b60a-4796-8f0d-26289f7dae50', 'kietnt', '$argon2id$v=19$m=65536,t=3,p=4$l4ptltIvcKNyDeuOZ2dfDg$XBg6sR18fXH+1Uj7DcfbXg08dz6tb61tko1U+JyIzIE', '22004176demo@st.vlute.edu.vn', '0916922746', 'avt.png', 'khoa', 'online', '2025-10-12 19:47:07'),
('d6a49ad8-fdb7-4649-88c3-5759befcc4a5', 'huyennt', '$argon2id$v=19$m=65536,t=3,p=4$l4ptltIvcKNyDeuOZ2dfDg$XBg6sR18fXH+1Uj7DcfbXg08dz6tb61tko1U+JyIzIE', '22004188demo@st.vlute.edu.vn', '0925992400', 'avt.png', 'danghoatdong', 'offline', '2025-10-12 19:47:07'),
('a78c47f4-b1c4-4313-b90a-8938f6ba8cac', 'trangpt', '$argon2id$v=19$m=65536,t=3,p=4$l4ptltIvcKNyDeuOZ2dfDg$XBg6sR18fXH+1Uj7DcfbXg08dz6tb61tko1U+JyIzIE', '22004197demo@st.vlute.edu.vn', '0917862285', 'avt.png', 'chuakichhoat', 'online', '2025-10-12 19:47:07'),
('2a11d082-fc6e-4713-9738-8650e95844f4', 'ynt', '$argon2id$v=19$m=65536,t=3,p=4$l4ptltIvcKNyDeuOZ2dfDg$XBg6sR18fXH+1Uj7DcfbXg08dz6tb61tko1U+JyIzIE', '22004198demo@st.vlute.edu.vn', '0945342451', 'avt.png', 'khoa', 'offline', '2025-10-12 19:47:07'),
('17716784-ec18-43b4-b8cb-a784d1127421', 'nhutlm', '$argon2id$v=19$m=65536,t=3,p=4$l4ptltIvcKNyDeuOZ2dfDg$XBg6sR18fXH+1Uj7DcfbXg08dz6tb61tko1U+JyIzIE', '22004200demo@st.vlute.edu.vn', '0994833459', 'avt.png', 'danghoatdong', 'online', '2025-10-12 19:47:07'),
('92d1b032-a891-4176-87dd-cdeab7473d61', 'gamhn', '$argon2id$v=19$m=65536,t=3,p=4$l4ptltIvcKNyDeuOZ2dfDg$XBg6sR18fXH+1Uj7DcfbXg08dz6tb61tko1U+JyIzIE', '22004204demo@st.vlute.edu.vn', '0945189413', 'avt.png', 'chuakichhoat', 'offline', '2025-10-12 19:47:07'),
('2614049e-5760-4853-b02a-5df11a4f947a', 'namnt', '$argon2id$v=19$m=65536,t=3,p=4$l4ptltIvcKNyDeuOZ2dfDg$XBg6sR18fXH+1Uj7DcfbXg08dz6tb61tko1U+JyIzIE', '22004207demo@st.vlute.edu.vn', '0941694207', 'avt.png', 'khoa', 'online', '2025-10-12 19:47:07'),
('0023b190-c734-45f6-8425-a1949e08e8d5', 'anvt', '$argon2id$v=19$m=65536,t=3,p=4$l4ptltIvcKNyDeuOZ2dfDg$XBg6sR18fXH+1Uj7DcfbXg08dz6tb61tko1U+JyIzIE', '21004212demo@st.vlute.edu.vn', '0956189563', 'avt.png', 'tamngung', 'online', '2025-10-12 19:47:07'),
('0e65ec6f-2792-48aa-a665-9b11b4796a0f', 'diemnt', '$argon2id$v=19$m=65536,t=3,p=4$l4ptltIvcKNyDeuOZ2dfDg$XBg6sR18fXH+1Uj7DcfbXg08dz6tb61tko1U+JyIzIE', '21004118demo@st.vlute.edu.vn', '0987654321', 'avt.png', 'khoa', 'offline', '2025-10-12 19:47:07'),
('30f7a140-9e0f-4763-8c73-d0ba585e0584', 'quynhln', '$argon2id$v=19$m=65536,t=3,p=4$l4ptltIvcKNyDeuOZ2dfDg$XBg6sR18fXH+1Uj7DcfbXg08dz6tb61tko1U+JyIzIE', '22004013demo@st.vlute.edu.vn', '0937690799', 'avt.png', 'danghoatdong', 'online', '2025-10-12 19:47:07'),
('d6207506-d2af-4427-b599-7c51661f3bdd', 'khanhht', '$argon2id$v=19$m=65536,t=3,p=4$l4ptltIvcKNyDeuOZ2dfDg$XBg6sR18fXH+1Uj7DcfbXg08dz6tb61tko1U+JyIzIE', '20004067demo@st.vlute.edu.vn', '0938577420', 'avt.png', 'danghoatdong', 'offline', '2025-10-12 19:47:07'),
('234a3664-f36a-442b-9bc2-a111ae14c1dc', 'thaopp', '$argon2id$v=19$m=65536,t=3,p=4$l4ptltIvcKNyDeuOZ2dfDg$XBg6sR18fXH+1Uj7DcfbXg08dz6tb61tko1U+JyIzIE', '20004192demo@st.vlute.edu.vn', '0940865116', 'avt.png', 'chuakichhoat', 'online', '2025-10-12 19:47:07'),
('59c9d360-7c64-45cf-b229-4e574b8c7ceb', 'huyst', '$argon2id$v=19$m=65536,t=3,p=4$l4ptltIvcKNyDeuOZ2dfDg$XBg6sR18fXH+1Uj7DcfbXg08dz6tb61tko1U+JyIzIE', '20004267demo@st.vlute.edu.vn', '0946354998', 'avt.png', 'khoa', 'offline', '2025-10-12 19:47:07'),
('85d0592f-15ed-4fb5-92ad-57c9af78c8aa', 'linhln', '$argon2id$v=19$m=65536,t=3,p=4$l4ptltIvcKNyDeuOZ2dfDg$XBg6sR18fXH+1Uj7DcfbXg08dz6tb61tko1U+JyIzIE', '21004020demo@st.vlute.edu.vn', '0922619718', 'avt.png', 'danghoatdong', 'online', '2025-10-12 19:47:07'),
('886a1f9f-d5b6-4964-ae73-e1eb5a69139e', 'duyendt', '$argon2id$v=19$m=65536,t=3,p=4$l4ptltIvcKNyDeuOZ2dfDg$XBg6sR18fXH+1Uj7DcfbXg08dz6tb61tko1U+JyIzIE', '21004053demo@st.vlute.edu.vn', '0957888546', 'avt.png', 'khoa', 'online', '2025-10-12 19:47:07'),
('809f4af0-c6c4-4478-be5e-93c696302b7b', 'vietch', '$argon2id$v=19$m=65536,t=3,p=4$l4ptltIvcKNyDeuOZ2dfDg$XBg6sR18fXH+1Uj7DcfbXg08dz6tb61tko1U+JyIzIE', '21004065demo@st.vlute.edu.vn', '0970588530', 'avt.png', 'danghoatdong', 'offline', '2025-10-12 19:47:07'),
('f9784f9e-7c83-4573-933c-335ed3b7e02d', 'vinhnt', '$argon2id$v=19$m=65536,t=3,p=4$l4ptltIvcKNyDeuOZ2dfDg$XBg6sR18fXH+1Uj7DcfbXg08dz6tb61tko1U+JyIzIE', '21004074demo@st.vlute.edu.vn', '0997668785', 'avt.png', 'chuakichhoat', 'online', '2025-10-12 19:47:07'),
('e2ce533c-9b1a-4bc6-bb84-440192b688d3', 'anbl', '$argon2id$v=19$m=65536,t=3,p=4$l4ptltIvcKNyDeuOZ2dfDg$XBg6sR18fXH+1Uj7DcfbXg08dz6tb61tko1U+JyIzIE', '21004046demo@st.vlute.edu.vn', '0962060054', 'avt.png', 'danghoatdong', 'offline', '2025-10-12 19:47:07'),
('8278d931-95e1-48d6-b02a-a33cb4f9a69c', 'anv', '$argon2id$v=19$m=65536,t=4,p=1$MUtyM0FDN3lLaHNZZVdkOA$9FxhDYMh8zmlZmHtTyZk1cPaUtw8nQkf98i0r6eyyqc', '22004335666@st.vlute.edu.vn', '1234567890', 'avt.png', 'danghoatdong', 'offline', '2025-10-17 22:29:12'),
('f9857b4a-d49f-45c5-850e-d5b6d0d3eb55', 'phongnh', '$argon2id$v=19$m=65536,t=3,p=4$l4ptltIvcKNyDeuOZ2dfDg$XBg6sR18fXH+1Uj7DcfbXg08dz6tb61tko1U+JyIzIE', '22004212demo@st.vlute.edu.vn', '0937554757', 'avt.png', 'danghoatdong', 'offline', '2025-10-12 19:47:07'),
('e66b4329-be12-44ac-915b-7a7b761433c0', 'ngandt', '$argon2id$v=19$m=65536,t=3,p=4$l4ptltIvcKNyDeuOZ2dfDg$XBg6sR18fXH+1Uj7DcfbXg08dz6tb61tko1U+JyIzIE', '22004214demo@st.vlute.edu.vn', '0984481292', 'avt.png', 'chuakichhoat', 'online', '2025-10-12 19:47:07'),
('34a86257-d43c-44e7-88ab-bccabdd5c644', 'trammt', '$argon2id$v=19$m=65536,t=3,p=4$l4ptltIvcKNyDeuOZ2dfDg$XBg6sR18fXH+1Uj7DcfbXg08dz6tb61tko1U+JyIzIE', '22004225demo@st.vlute.edu.vn', '0997511993', 'avt.png', 'khoa', 'offline', '2025-10-12 19:47:07'),
('fe1dcc48-4a84-45a7-8c38-610f6686f0f7', 'yendt', '$argon2id$v=19$m=65536,t=3,p=4$l4ptltIvcKNyDeuOZ2dfDg$XBg6sR18fXH+1Uj7DcfbXg08dz6tb61tko1U+JyIzIE', '22004227demo@st.vlute.edu.vn', '0934360028', 'avt.png', 'danghoatdong', 'online', '2025-10-12 19:47:07'),
('ce1ead4c-e4ef-4712-979e-6d358511f4cd', 'nhungnt', '$argon2id$v=19$m=65536,t=3,p=4$l4ptltIvcKNyDeuOZ2dfDg$XBg6sR18fXH+1Uj7DcfbXg08dz6tb61tko1U+JyIzIE', '22004231demo@st.vlute.edu.vn', '0943114634', 'avt.png', 'chuakichhoat', 'offline', '2025-10-12 19:47:07'),
('7efdc816-e04f-4493-9997-6b0766bae0db', 'khanhlv', '$argon2id$v=19$m=65536,t=3,p=4$l4ptltIvcKNyDeuOZ2dfDg$XBg6sR18fXH+1Uj7DcfbXg08dz6tb61tko1U+JyIzIE', '22004238demo@st.vlute.edu.vn', '0936128535', 'avt.png', 'khoa', 'online', '2025-10-12 19:47:07'),
('3ebc4930-2923-4085-b9ca-192f087ba6bf', 'hieutc', '$argon2id$v=19$m=65536,t=3,p=4$l4ptltIvcKNyDeuOZ2dfDg$XBg6sR18fXH+1Uj7DcfbXg08dz6tb61tko1U+JyIzIE', '22004266demo@st.vlute.edu.vn', '0935206045', 'avt.png', 'danghoatdong', 'offline', '2025-10-12 19:47:07'),
('3551fa73-fadc-4ef2-a1a3-6929a8e65c11', 'gianght', '$argon2id$v=19$m=65536,t=3,p=4$l4ptltIvcKNyDeuOZ2dfDg$XBg6sR18fXH+1Uj7DcfbXg08dz6tb61tko1U+JyIzIE', '22004267demo@st.vlute.edu.vn', '0986808240', 'avt.png', 'chuakichhoat', 'online', '2025-10-12 19:47:07'),
('fce8f322-59a9-4711-88ae-8c10c7d87f21', 'thutm', '$argon2id$v=19$m=65536,t=3,p=4$l4ptltIvcKNyDeuOZ2dfDg$XBg6sR18fXH+1Uj7DcfbXg08dz6tb61tko1U+JyIzIE', '22004297demo@st.vlute.edu.vn', '0989033106', 'avt.png', 'danghoatdong', 'online', '2025-10-12 19:47:07'),
('d20c59af-cbf7-47b5-aec4-90b6b119a816', 'tuyennm', '$argon2id$v=19$m=65536,t=3,p=4$l4ptltIvcKNyDeuOZ2dfDg$XBg6sR18fXH+1Uj7DcfbXg08dz6tb61tko1U+JyIzIE', '22004313demo@st.vlute.edu.vn', '0996952042', 'avt.png', 'chuakichhoat', 'offline', '2025-10-12 19:47:07'),
('eb111e8c-1671-40a1-a970-64e683945c90', 'thint', '$argon2id$v=19$m=65536,t=3,p=4$l4ptltIvcKNyDeuOZ2dfDg$XBg6sR18fXH+1Uj7DcfbXg08dz6tb61tko1U+JyIzIE', '22004323demo@st.vlute.edu.vn', '0945569238', 'avt.png', 'khoa', 'online', '2025-10-12 19:47:07'),
('bc2ed64f-8ae4-4637-9632-75c5af63066c', 'hungth', '$argon2id$v=19$m=65536,t=3,p=4$l4ptltIvcKNyDeuOZ2dfDg$XBg6sR18fXH+1Uj7DcfbXg08dz6tb61tko1U+JyIzIE', '24004292demo@st.vlute.edu.vn', '0988513364', 'avt.png', 'chuakichhoat', 'online', '2025-10-12 19:47:07'),
('0216dbde-2061-4841-ad42-0445ffd0692d', 'nhuttk', '$argon2id$v=19$m=65536,t=3,p=4$l4ptltIvcKNyDeuOZ2dfDg$XBg6sR18fXH+1Uj7DcfbXg08dz6tb61tko1U+JyIzIE', '22004294demo@st.vlute.edu.vn', '0923543289', 'avt.png', 'danghoatdong', 'offline', '2025-10-12 19:47:07'),
('2dae71cc-a4e8-487b-be5a-3f65bdd9205d', 'anhnt', '$2y$10$cUCEA64gIRZG4qvkkxW5EuOQbb1H7trqUwbk4q0rpfw.TIZ/zMi3G', '22004335@st.vlute.edu.vn', '0932936898', 'avt.png', 'danghoatdong', 'offline', '2025-10-12 19:47:07');

-- ... (Các INSERT INTO khác giữ nguyên) ...

-- ===================================================================
-- PHẦN 5: BẬT LẠI KIỂM TRA KHÓA NGOẠI
-- ===================================================================

SET FOREIGN_KEY_CHECKS = 1;
