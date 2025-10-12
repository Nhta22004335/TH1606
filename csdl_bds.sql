-- CƠ SỞ DỮ LIỆU POSTGRESQL
-- Chủ đề: "Sàn giao dịch thương mại điện tử bất động sản"
-- Tổng: 
-- Phân công: Nguyễn Tuấn Anh = quản trị, Lê Ngọc Quỳnh = môi giới, Trương Quốc Đăng = khách hàng

-- ===========================
-- Extension cho UUID
-- =========================== tuấn anh 
CREATE EXTENSION IF NOT EXISTS "uuid-ossp";

CREATE TABLE IF NOT EXISTS quyen (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    vai_tro VARCHAR(50) NOT NULL UNIQUE
);

-- 0. Bảng nguoi_dung
CREATE TABLE IF NOT EXISTS nguoi_dung (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    ten_dang_nhap VARCHAR(100) NOT NULL UNIQUE,
    mat_khau VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    so_dt VARCHAR(20) DEFAULT 'chuacapnhat',
	avt TEXT DEFAULT 'avt.png',
	anh_bia TEXT DEFAULT 'anhbia.png',
	trang_thai VARCHAR(50) DEFAULT 'chuakichhoat',
    hoat_dong VARCHAR(50) DEFAULT 'offline', 
    ngay_tao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT chk_nguoi_dung_trang_thai CHECK (trang_thai IN ('danghoatdong','chuakichhoat','khoa')),
    CONSTRAINT chk_nguoi_dung_hoat_dong CHECK (hoat_dong IN ('online','offline')),
    CONSTRAINT chk_nguoi_dung_so_dt CHECK (so_dt ~ '^[0-9]{1,11}$' OR so_dt = 'chuacapnhat')
);

CREATE TABLE IF NOT EXISTS phan_quyen (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    id_nguoi_dung UUID NOT NULL,
    id_quyen UUID NOT NULL,
    CONSTRAINT fk_phan_quyen_nguoi_dung FOREIGN KEY (id_nguoi_dung) REFERENCES nguoi_dung(id) ON DELETE CASCADE,
    CONSTRAINT fk_phan_quyen_quyen FOREIGN KEY (id_quyen) REFERENCES quyen(id) ON DELETE CASCADE,
    UNIQUE (id_nguoi_dung, id_quyen) 
); 

CREATE TABLE IF NOT EXISTS info_nguoi_dung (
	id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
	id_nguoi_dung UUID UNIQUE NOT NULL,
	ho_ten VARCHAR(150) DEFAULT 'chuacapnhat',
	gioi_tinh VARCHAR(20) DEFAULT 'chuacapnhat',
	dia_chi TEXT DEFAULT 'chuacapnhat',
	ngay_sinh DATE DEFAULT (CURRENT_DATE - INTERVAL '18 years'),
	mo_ta TEXT DEFAULT 'chuacapnhat',
	CONSTRAINT fk_info_nd FOREIGN KEY (id_nguoi_dung) REFERENCES nguoi_dung(id) ON DELETE CASCADE,
    CONSTRAINT chk_quan_tri_gioi_tinh CHECK (gioi_tinh IN ('nam','nu','khac','chuacapnhat')),
    CONSTRAINT chk_quan_tri_tuoi CHECK (ngay_sinh <= CURRENT_DATE - INTERVAL '18 years')
)

-- 4. Bảng phien_dang_nhap (lưu lại phiên làm việc của người dùng)
CREATE TABLE IF NOT EXISTS phien_dang_nhap (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),              
    id_nguoi_dung UUID NOT NULL,           
	selector VARCHAR(255),
	verifier_hash VARCHAR(255),
    bat_dau TIMESTAMP DEFAULT CURRENT_TIMESTAMP,  
    het_han TIMESTAMP,         
    dang_hoat_dong BOOLEAN DEFAULT TRUE, 
    CONSTRAINT fk_phien_dang_nhap_nguoi_dung FOREIGN KEY (id_nguoi_dung) REFERENCES nguoi_dung(id) ON DELETE CASCADE,
    CONSTRAINT chk_phien_dang_nhap_time_range CHECK (het_han IS NULL OR het_han > bat_dau),
    CONSTRAINT chk_phien_dang_nhap_token_nonempty CHECK (length(trim(token_phien)) > 0)
);

-- 5. Bảng yeu_cau_otp (quản lý các mã OTP (One-Time Password) phục vụ cho xác thực người dùng trong hệ thống)
CREATE TABLE IF NOT EXISTS yeu_cau_otp (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(), 
    so_dt VARCHAR(20) NULL,
    email VARCHAR(255) NULL,
    otp_code VARCHAR(10) NOT NULL,
    trang_thai VARCHAR(20) DEFAULT 'choxacnhan',
    bat_dau TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    het_han TIMESTAMP NOT NULL,
    cap_nhat TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT chk_yeu_cau_otp_trang_thai CHECK (trang_thai IN ('choxacnhan', 'daxacnhan', 'dahuy')),
    CONSTRAINT chk_yeu_cau_otp_contact_only CHECK (
        (so_dt IS NOT NULL AND email IS NULL) OR 
        (so_dt IS NULL AND email IS NOT NULL)
    ),
    CONSTRAINT chk_yeu_cau_otp_time_range CHECK (het_han > bat_dau)
);

-- 6. Bảng lich_su_xac_thuc (lưu lại vết đăng nhập, đăng ký hay đổi mật khẩu từ người dùng)
CREATE TABLE IF NOT EXISTS lich_su_xac_thuc (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    id_nguoi_dung UUID NOT NULL,
    loai_su_kien VARCHAR(30) NOT NULL,
    thoi_gian_bat_dau TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    thoi_gian_ket_thuc TIMESTAMP,
    dia_chi_ip VARCHAR(45),
    user_agent TEXT,
    ghi_chu TEXT,
    CONSTRAINT fk_lich_su_xac_thuc_nguoi_dung FOREIGN KEY (id_nguoi_dung) REFERENCES nguoi_dung(id) ON DELETE CASCADE,
    CONSTRAINT chk_lich_su_xac_thuc_loai_su_kien CHECK (loai_su_kien IN ('dangnhap', 'dangxuat', 'doimatkhau', 'quenmatkhau')),
    CONSTRAINT chk_lich_su_xac_thuc_time_range CHECK (thoi_gian_ket_thuc IS NULL OR thoi_gian_ket_thuc >= thoi_gian_bat_dau)
);

-- 7. Bảng bat_dong_san (lưu thống tin các sản phẩm bất động sản)
CREATE TABLE IF NOT EXISTS bat_dong_san (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    id_nguoi_dung UUID, 
    tieu_de VARCHAR(200) DEFAULT 'chuacapnhat',
    mo_ta TEXT DEFAULT 'chuacapnhat',
    gia NUMERIC(18,2) CHECK (gia >= 0) DEFAULT 0,
    dien_tich NUMERIC(10,2) CHECK (dien_tich > 0),
    dia_chi TEXT DEFAULT 'chuacapnhat',
    loai VARCHAR(100) DEFAULT 'chuacapnhat',
    khu_vuc VARCHAR(100) DEFAULT 'chuacapnhat',
    trang_thai VARCHAR(50) DEFAULT 'chuacapnhat',
    hinh_thuc VARCHAR(50) DEFAULT 'chuacapnhat',
    ngay_dang TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_bds_nguoi_dung FOREIGN KEY (id_nguoi_dung) REFERENCES nguoi_dung(id) ON DELETE CASCADE, 
    CONSTRAINT chk_loai_bds CHECK (loai IN ('canho', 'nhapho', 'datnen', 'bietthu', 'chuacapnhat')),
    CONSTRAINT chk_trang_thai_bds CHECK (trang_thai IN ('chuaduyet', 'daduyet', 'daban', 'dathue', 'chuacapnhat')),
    CONSTRAINT chk_hinh_thuc_bds CHECK (hinh_thuc IN ('ban', 'chothue', 'chuacapnhat'))
);

-- 8. Bảng anh (ảnh sản phẩm bất động sản)
CREATE TABLE IF NOT EXISTS hinh_anh_bds (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    id_bds UUID NOT NULL,
    url VARCHAR(300) NOT NULL,
	kich_thuoc NUMERIC(10,2) DEFAULT 0,
	trang_thai VARCHAR(255) CHECK (trang_thai IN ('binhthuong', 'nhe', 'trungbinh', 'nang'))DEFAULT 'binhthuong',
    mo_ta VARCHAR(200) DEFAULT 'chuacapnhat',
    ngay_tao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_hinh_anh_bds_bds FOREIGN KEY (id_bds) REFERENCES bat_dong_san(id) ON DELETE CASCADE
);

-- 9. Bảng video (video sản phẩm bất động sản)
CREATE TABLE IF NOT EXISTS video_bds (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    id_bds UUID NOT NULL,
    url VARCHAR(300),
    mo_ta VARCHAR(200),
    ngay_tao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_video_bds_bds FOREIGN KEY (id_bds) REFERENCES bat_dong_san(id) ON DELETE CASCADE
);
SELECT * FROM bat_dong_san;
9b17fb30-8c6e-4494-920a-cbdd1621ee20
ba11e8d1-b68b-42e9-b35d-40eaea043fc3
6c064758-3b9f-4ab0-af99-bcdbb8efa989
+
-- 10. Bảng danh_gia_bds (khách hàng đánh giá các sản phẩm BĐS mà môi giới rao bán)
CREATE TABLE IF NOT EXISTS danh_gia_bds (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    id_nguoi_dung UUID,
    id_bds UUID NOT NULL,
    diem INT CHECK (diem >= 1 AND diem <= 5),
    binh_luan TEXT,
	trang_thai VARCHAR(10) DEFAULT 'hien',
    ngay_tao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
	CONSTRAINT chk_danh_gia_bds_trang_thai CHECK (trang_thai IN ('hien','an')),
    CONSTRAINT fk_danh_gia_bds_nd FOREIGN KEY (id_nguoi_dung) REFERENCES nguoi_dung(id) ON DELETE SET NULL,
    CONSTRAINT fk_danh_gia_bds_bds FOREIGN KEY (id_bds) REFERENCES bat_dong_san(id) ON DELETE CASCADE
);

INSERT INTO danh_gia_bds (id_nguoi_dung, id_bds, diem, binh_luan) VALUES
('7a6fa374-5628-4870-be48-a4ea18aef621', '9b17fb30-8c6e-4494-920a-cbdd1621ee20', '4', 'Mọi thứ điều rất tốt!');

SELECT * FROM danh_gia_bds;

CREATE TABLE IF NOT EXISTS hinh_anh_danh_gia_bds (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    id_dg_bds UUID NOT NULL,
    url VARCHAR(300),
    mo_ta VARCHAR(200),
    CONSTRAINT fk_hinh_dg FOREIGN KEY (id_dg_bds) REFERENCES danh_gia_bds(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS video_danh_gia_bds (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    id_dg_bds UUID NOT NULL,
    url VARCHAR(300),
    mo_ta VARCHAR(200),
    CONSTRAINT fk_video_dg FOREIGN KEY (id_dg_bds) REFERENCES danh_gia_bds(id) ON DELETE CASCADE
);

-- 11. Bảng giao_dich (ghi nhận giao dịch mua/bán/thue)
CREATE TABLE IF NOT EXISTS giao_dich (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    id_nguoi_dung UUID,
	id_nguoi_ban UUID,
    id_bds UUID,
    loai VARCHAR(50) NOT NULL, 
    ngay_giao_dich TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    trang_thai VARCHAR(50) DEFAULT 'choxuly', 
	CONSTRAINT chk_giao_dich_tt CHECK (trang_thai IN ('choxuly', 'dangxuly','hoantat','dahuy')),
    CONSTRAINT chk_giao_dich_loai CHECK (loai IN ('mua','ban','thue')),
    CONSTRAINT fk_giao_dich_nd FOREIGN KEY (id_nguoi_dung) REFERENCES nguoi_dung(id) ON DELETE SET NULL,
    CONSTRAINT fk_giao_dich_bds FOREIGN KEY (id_bds) REFERENCES bat_dong_san(id) ON DELETE SET NULL
);

ALTER TABLE giao_dich
ADD COLUMN id_nguoi_ban UUID


INSERT INTO giao_dich (id_nguoi_dung, id_bds, loai) VALUES
('7a6fa374-5628-4870-be48-a4ea18aef621', '9b17fb30-8c6e-4494-920a-cbdd1621ee20', 'ban');

-- Giao dịch MUA BÁN
INSERT INTO giao_dich (id, id_nguoi_dung, id_nguoi_ban, id_bds, loai, trang_thai)
VALUES (
    '87654321-abcd-abcd-abcd-123456789012', -- ID Giao dịch Mẫu
    '7a6fa374-5628-4870-be48-a4ea18aef621', -- id_nguoi_dung (Người mua/thuê)
    'ea5c0d77-9ce2-4309-b0e7-cbe579f9209d', -- id_nguoi_ban (Người bán)
    '9b17fb30-8c6e-4494-920a-cbdd1621ee20', -- id_bds
    'mua',
    'dangxuly' -- Đang trong quá trình xử lý
);

-- Kế hoạch Thanh toán (Tổng giá trị)
INSERT INTO ke_hoach_thanh_toan (id_giao_dich, tong_gia_tri, so_tien_da_tt, trang_thai_tt)
VALUES (
    '87654321-abcd-abcd-abcd-123456789012', 
    2500000000.00,  -- Tổng giá trị hợp đồng
    1000000000.00,  -- Số tiền đã TT (Đợt 1 + Đợt 2)
    'dangthanhtoan' -- Đang trong quá trình thanh toán
);

-- Đợt Thanh toán 1: Đặt cọc
INSERT INTO dot_thanh_toan (id, id_giao_dich, lan_tt, so_tien_tt, ngay_tt, phuong_thuc)
VALUES (
    'a1b2c3d4-0000-0000-0001-000000000001', 
    '87654321-abcd-abcd-abcd-123456789012', 
    1, 
    500000000.00, 
    '2025-09-01 10:00:00',
    'Chuyen khoan'
);

-- Đợt Thanh toán 2: Ký hợp đồng
INSERT INTO dot_thanh_toan (id, id_giao_dich, lan_tt, so_tien_tt, ngay_tt, phuong_thuc)
VALUES (
    'a1b2c3d4-0000-0000-0002-000000000002', 
    '87654321-abcd-abcd-abcd-123456789012', 
    2, 
    500000000.00, 
    '2025-10-01 15:30:00',
    'Tien mat'
);

-- Đợt Thanh toán 3: Bàn giao (Giả định đợt này CHƯA có bản ghi trong bảng dot_thanh_toan 
-- vì nó chưa được thực hiện, nhưng nó là một phần của kế hoạch)
-- Bảng dot_thanh_toan chỉ lưu các đợt ĐÃ hoàn thành.
-- Nếu bạn có một bảng Kế hoạch Đợt, bạn sẽ thêm nó vào đó.
SELECT * FROM giao_dich;
SELECT * FROM nguoi_dung
d4b0ccdd-4554-4456-b0d8-c1fc783b0cc1

CREATE TABLE IF NOT EXISTS ke_hoach_thanh_toan (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    id_giao_dich UUID NOT NULL UNIQUE,
    tong_gia_tri NUMERIC(18,2) NOT NULL CHECK (tong_gia_tri >= 0), -- Tổng giá trị Hợp đồng/GD
    so_tien_da_tt NUMERIC(18,2) DEFAULT 0 CHECK (so_tien_da_tt >= 0), -- Tổng số tiền đã thanh toán (Tính toán)
    trang_thai_tt VARCHAR(50) DEFAULT 'chuathanhtoan', -- Trạng thái tổng quát của TT
    
    CONSTRAINT chk_khtt_trang_thai CHECK (trang_thai_tt IN ('chuathanhtoan', 'dangthanhtoan', 'hoantat')),
    CONSTRAINT fk_khtt_gd FOREIGN KEY ( id_giao_dich) REFERENCES giao_dich(id) ON DELETE CASCADE
);
select * from ke_hoach_thanh_toan 
CREATE TABLE IF NOT EXISTS dot_thanh_toan (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    id_giao_dich UUID NOT NULL,
    lan_tt INT NOT NULL,                                     -- Lần thanh toán thứ mấy (1, 2, 3...)
    so_tien_tt NUMERIC(18,2) NOT NULL CHECK (so_tien_tt > 0), -- Số tiền của đợt thanh toán này
    ngay_tt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    phuong_thuc VARCHAR(100),
    ghichu TEXT,
    
    CONSTRAINT fk_dtt_gd FOREIGN KEY (id_giao_dich) REFERENCES giao_dich(id) ON DELETE CASCADE,
    UNIQUE (id_giao_dich, lan_tt) -- Đảm bảo không có 2 đợt cùng số lần trong 1 giao dịch
);

CREATE TABLE IF NOT EXISTS dot_thanh_toan_ct (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    id_dot_thanh_toan UUID NOT NULL,
    id_bds UUID,                                            -- Mặc dù giao dịch đã có id_bds, nhưng để linh hoạt cho dự án lớn hơn.
    so_luong INT DEFAULT 1,
    so_tien NUMERIC(18,2) CHECK (so_tien >= 0),
    
    CONSTRAINT fk_dttct_dtt FOREIGN KEY (id_dot_thanh_toan) REFERENCES dot_thanh_toan(id) ON DELETE CASCADE,
    CONSTRAINT fk_dttct_bds FOREIGN KEY (id_bds) REFERENCES bat_dong_san(id) ON DELETE SET NULL
);
select * from giao_dich
select * from dot_thanh_toan
-- 12. Bảng thanh_toan (tổng thanh toán liên quan giao dịch)
CREATE TABLE IF NOT EXISTS thanh_toan (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    id_giao_dich UUID NOT NULL,
    tong_tien NUMERIC(18,2) CHECK (tong_tien >= 0),
    ngay_tt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    phuong_thuc VARCHAR(100), 
    trang_thai VARCHAR(50) DEFAULT 'dathanhtoan',
    CONSTRAINT fk_tt_gd FOREIGN KEY (id_giao_dich) REFERENCES giao_dich(id) ON DELETE CASCADE
);

INSERT INTO thanh_toan (id_giao_dich, tong_tien, phuong_thuc) VALUES
('d4b0ccdd-4554-4456-b0d8-c1fc783b0cc1', '2800000000', 'ck');

SELECT * FROM thanh_toan;
437a2754-0c15-4225-be14-cd319b06dacb

-- 13. Bảng thanh_toan_ct (chi tiết thanh toán)
CREATE TABLE IF NOT EXISTS thanh_toan_ct (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    id_thanh_toan UUID NOT NULL,
    id_bds UUID,
    so_luong INT DEFAULT 1,
    so_tien NUMERIC(18,2) CHECK (so_tien >= 0),
    CONSTRAINT fk_ttc_tt FOREIGN KEY (id_thanh_toan) REFERENCES thanh_toan(id) ON DELETE CASCADE,
    CONSTRAINT fk_ttc_bds FOREIGN KEY (id_bds) REFERENCES bat_dong_san(id) ON DELETE SET NULL
);

INSERT INTO thanh_toan_ct (id_thanh_toan, id_bds, so_luong, so_tien) VALUES 
('437a2754-0c15-4225-be14-cd319b06dacb', '9b17fb30-8c6e-4494-920a-cbdd1621ee20', '1', '2800000000');

SELECT * FROM thanh_toan_ct;
SELECT * FROM thong_bao;
-- 14. Bảng thong_bao
CREATE TABLE IF NOT EXISTS thong_bao (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    id_nguoi_dung UUID NOT NULL,
    loai VARCHAR(50) NOT NULL,
    tieu_de VARCHAR(255) NOT NULL,
    noi_dung TEXT NOT NULL,
    thoi_gian_gui TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    trang_thai VARCHAR(20) DEFAULT 'chuaxem',
    CONSTRAINT fk_thong_bao_nd FOREIGN KEY (id_nguoi_dung) REFERENCES nguoi_dung(id) ON DELETE CASCADE,
    CONSTRAINT chk_thong_bao_loai CHECK (loai IN (
        'capnhatthongtin',
        'doimatkhau',
        'khoataikhoan',
        'xoataikhoan'
    )),
    CONSTRAINT chk_thong_bao_trang_thai CHECK (trang_thai IN ('chuaxem','daxem'))
);

SELECT * FROM thong_bao;
SELECT * FROM nguoi_dung;
-- 15. Bảng danh_gia_mg (đánh giá môi giới)
CREATE TABLE IF NOT EXISTS danh_gia_mg (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    id_khach_hang UUID NOT NULL,
    id_moi_gioi UUID NOT NULL,
    diem INT CHECK (diem >= 1 AND diem <= 5),
    binh_luan TEXT,
    ngay_dg TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_danh_gia_kh_nd FOREIGN KEY (id_khach_hang) REFERENCES nguoi_dung(id) ON DELETE CASCADE,
    CONSTRAINT fk_danh_gia_mg_nd FOREIGN KEY (id_moi_gioi) REFERENCES nguoi_dung(id) ON DELETE CASCADE,
    CONSTRAINT chk_kh_mg_khacnhau CHECK (id_khach_hang <> id_moi_gioi)
);

INSERT INTO danh_gia_mg (id_khach_hang, id_moi_gioi, diem, binh_luan) VALUES 
('7a6fa374-5628-4870-be48-a4ea18aef621', 'ea5c0d77-9ce2-4309-b0e7-cbe579f9209d', 5, 
'Môi giới hỗ trợ rất nhiệt tình, giải thích rõ ràng và làm việc chuyên nghiệp.');

CREATE TABLE bieu_mau (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),           
    tieu_de VARCHAR(255) NOT NULL,    
    loai VARCHAR(100) NOT NULL,        -- loại (ví dụ: Hợp đồng, Biên bản, ...)
    ben_mua UUID NOT NULL,       -- bên mua
    ben_ban UUID NOT NULL,      -- bên bán
    trang_thai VARCHAR(50) DEFAULT 'choduyet', 
    tep_dk VARCHAR(255),               
    ngay_tao TIMESTAMP DEFAULT CURRENT_TIMESTAMP, 
    ngay_cn TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
	CONSTRAINT fk_benmua FOREIGN KEY (ben_mua) REFERENCES nguoi_dung(id) ON DELETE CASCADE,
    CONSTRAINT fk_benban FOREIGN KEY (ben_ban) REFERENCES nguoi_dung(id) ON DELETE CASCADE,
	CONSTRAINT chk_trangthai CHECK (trang_thai IN ('choduyet','daduyet', 'daky', 'huy'))
);

INSERT INTO bieu_mau (tieu_de, loai, ben_mua, ben_ban, trang_thai, tep_dk) VALUES 
('Hợp đồng mua bán nhà', 'Hợp đồng', '7a6fa374-5628-4870-be48-a4ea18aef621', 'ea5c0d77-9ce2-4309-b0e7-cbe579f9209d', 'choduyet', 'hopdong1.pdf');

SELECT * FROM bieu_mau
SELECT * FROM nguoi_dung
CREATE TABLE IF NOT EXISTS yeu_cau (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(), 
    
    id_nguoi_dung UUID NOT NULL,
    loai VARCHAR(100) NOT NULL,               
    id_bds UUID,                             
    trang_thai VARCHAR(50) DEFAULT 'choxuly',
    ngay_tao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_yeucau_nguoidung FOREIGN KEY (id_nguoi_dung) REFERENCES nguoi_dung(id) ON DELETE CASCADE,
    CONSTRAINT fk_yeucau_bds FOREIGN KEY (id_bds) REFERENCES bat_dong_san(id) ON DELETE SET NULL,

    CONSTRAINT chk_yeucau_trangthai CHECK (trang_thai IN ('choxuly', 'daduyet', 'dahuy')),
	CONSTRAINT chk_yeucau_loai CHECK (loai IN ('mua', 'ban', 'thue'))
);

ALTER TABLE yeu_cau
ADD COLUMN mo_ta_chi_tiet TEXT DEFAULT 'chuacapnhat'

select * from yeu_cau

INSERT INTO yeu_cau (id_nguoi_dung, loai, id_bds, trang_thai)
VALUES
    ('7a6fa374-5628-4870-be48-a4ea18aef621', 'mua', '9b17fb30-8c6e-4494-920a-cbdd1621ee20', 'choxuly'),
    ('7a6fa374-5628-4870-be48-a4ea18aef621', 'thue', '6c064758-3b9f-4ab0-af99-bcdbb8efa989', 'daduyet');
	
select * from bat_dong_san
-- 1. Bảng tin đăng
CREATE TABLE tin_tuc (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),       
    id_khach_hang UUID NOT NULL,                                                   
    tieu_de VARCHAR(200) NOT NULL DEFAULT 'chuacapnhat',
    mo_ta TEXT DEFAULT 'chuacapnhat',
    chuyen_muc VARCHAR(100) DEFAULT 'chuacapnhat',                  
    trang_thai VARCHAR(50) DEFAULT 'choduyet',  
	anh_tin TEXT DEFAULT 'chuacapnhat.png',
    ngay_dang TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT chk_trang_thai_tin CHECK (trang_thai IN ('choduyet','dangban','daban','dathue')),
    CONSTRAINT fk_tin_khachhang FOREIGN KEY (id_khach_hang) REFERENCES nguoi_dung(id) ON DELETE CASCADE
);

ALTER TABLE tin_tuc
ADD COLUMN luot_xem INT 

-- Dữ liệu mẫu cho bảng tin_tuc
INSERT INTO tin_tuc (id_khach_hang, tieu_de, mo_ta, chuyen_muc, trang_thai)
VALUES
('ea5c0d77-9ce2-4309-b0e7-cbe579f9209d', 'Mở bán căn hộ Vinhomes', 'Cập nhật thông tin dự án mới nhất tại Vinhomes.', 'Bất động sản', 'choduyet'),
('ea5c0d77-9ce2-4309-b0e7-cbe579f9209d', 'Những lưu ý khi mua nhà phố', 'Hướng dẫn khách hàng tránh rủi ro khi mua nhà phố.', 'Hướng dẫn', 'choduyet');

select * from tin_tuc
-- Ví dụ thêm 1 tin đăng
INSERT INTO tin_dang (id_khach_hang, id_bds, tieu_de, mo_ta, gia, dien_tich, dia_chi, loai, trang_thai)
VALUES
('d7a1f6c2-xxxx-xxxx-xxxx-xxxxxxxxxxxx',  -- UUID khách hàng
 'b6e7dbf5-37a3-423d-a51e-59fc00467984',  -- UUID BĐS
 'Căn hộ cao cấp Vinhomes', 
 'Căn hộ 2PN full nội thất', 
 3500000000, 
 75.5, 
 'Quận 1, TP.HCM', 
 'ban', 
 'dangban'
);
ALTER TABLE tin_tuc
ADD COLUMN anh_tin TEXT DEFAULT 'chuacapnhat.png';

CREATE TABLE hop_thoai (
	id UUID PRIMARY KEY DEFAULT uuid_generate_v4(), 
	da_khoa INT DEFAULT 0,
	da_xoa INT DEFAULT 0
)

ALTER TABLE hop_thoai
DROP COLUMN xoa_boi;

CREATE TABLE tin_nhan (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(), 
	id_hop_thoai UUID,
    nguoi_gui UUID NOT NULL,                 -- Người gửi
    nguoi_nhan UUID NOT NULL,                   -- Người nhận
    noi_dung TEXT, -- Không cho phép rỗng
	anh_tn TEXT,
	video_tn TEXT,
    tg_gui TIMESTAMP NOT NULL DEFAULT NOW(),
    -- Ràng buộc khóa ngoại
    CONSTRAINT fk_gui FOREIGN KEY (nguoi_gui) REFERENCES nguoi_dung(id) ON DELETE CASCADE,
    CONSTRAINT fk_nhan  FOREIGN KEY (nguoi_nhan)   REFERENCES nguoi_dung(id) ON DELETE CASCADE,
	CONSTRAINT fk_id_hop_thoai FOREIGN KEY (id_hop_thoai) REFERENCES hop_thoai(id) ON DELETE CASCADE,
    -- Ràng buộc: người gửi và người nhận không được trùng
    CONSTRAINT chk_gui_nhan CHECK (nguoi_gui <> nguoi_nhan)
);

ALTER TABLE tin_nhan
ADD COLUMN da_xoa INT DEFAULT 0

select * from hop_thoai
select * from tin_nhan 

INSERT INTO tin_nhan (nguoi_gui, nguoi_nhan, noi_dung) VALUES 
('ea5c0d77-9ce2-4309-b0e7-cbe579f9209d', '7a6fa374-5628-4870-be48-a4ea18aef621', 'Anh chị cần tư vấn về những thông tin j ạ ?'),
('7a6fa374-5628-4870-be48-a4ea18aef621', 'ea5c0d77-9ce2-4309-b0e7-cbe579f9209d', 'Về căn hộ B đó có vấn đề về tranh chấp ko ?'),
('ab76fa3c-893e-487d-983f-d8429ee95436', 'ea5c0d77-9ce2-4309-b0e7-cbe579f9209d', 'Bạn muốn phản hồi j về cho tôi? Hay muốn giải đáp!'),
('7a6fa374-5628-4870-be48-a4ea18aef621', 'ab76fa3c-893e-487d-983f-d8429ee95436', 'Tôi cần nắm rỏ các quy định về chính sách');



CREATE TABLE IF NOT EXISTS khach_quan_tam_bds (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    id_khach_hang UUID NOT NULL,
    id_bat_dong_san UUID NOT NULL,
    ngay_tao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    trang_thai VARCHAR(20) DEFAULT 'active' CHECK (trang_thai IN ('active', 'huy')),
    ghi_chu TEXT,

    CONSTRAINT fk_kqt_kh FOREIGN KEY (id_khach_hang)
        REFERENCES nguoi_dung(id) ON DELETE CASCADE,
    CONSTRAINT fk_kqt_bds FOREIGN KEY (id_bat_dong_san)
        REFERENCES bat_dong_san(id) ON DELETE CASCADE,

    CONSTRAINT uq_kqt UNIQUE (id_khach_hang, id_bat_dong_san)
);


CREATE TABLE lich_su (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(), -- mã lịch sử tự sinh
    id_bat_dong_san UUID NOT NULL,                 -- ID bất động sản
    id_nguoi_dung UUID,                            -- người thực hiện hành động (khách hoặc admin)
    hanh_dong VARCHAR(50) NOT NULL,               -- ví dụ: 'quan_tam', 'duyet', 'mua', 'thanh_toan'
    ghi_chu TEXT,                                  -- thông tin chi tiết nếu cần
    ngay_tao TIMESTAMP NOT NULL DEFAULT now()     -- thời gian thực hiện
);

select * from lich_su

CREATE TABLE IF NOT EXISTS ghi_chu_khach_hang (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    id_nguoi_dung UUID NOT NULL, -- ID của khách hàng được ghi chú
    id_bds UUID, -- Ghi chú này liên quan đến BĐS nào (có thể NULL nếu là ghi chú chung)
    id_moi_gioi UUID NOT NULL, -- ID của môi giới tạo ghi chú
    ghi_chu TEXT NOT NULL,
    ngay_tao TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_ghichu_khachhang FOREIGN KEY (id_nguoi_dung) REFERENCES nguoi_dung(id) ON DELETE CASCADE,
    CONSTRAINT fk_ghichu_bds FOREIGN KEY (id_bds) REFERENCES bat_dong_san(id) ON DELETE SET NULL,
    CONSTRAINT fk_ghichu_moigioi FOREIGN KEY (id_moi_gioi) REFERENCES nguoi_dung(id) ON DELETE CASCADE
);


