-- CƠ SỞ DỮ LIỆU POSTGRESQL
-- Chủ đề: "Sàn giao dịch thương mại điện tử bất động sản"
-- Tổng: 
-- Phân công: Nguyễn Tuấn Anh = quản trị, Lê Ngọc Quỳnh = môi giới, Trương Quốc Đăng = khách hàng

-- ===========================
-- Extension cho UUID
-- ===========================
CREATE EXTENSION IF NOT EXISTS "uuid-ossp";

-- ===========================
-- 0. Bảng nguoi_dung
-- Mô tả: Lưu thông tin đăng nhập chung cho tất cả nhóm người dùng (quản trị, môi giới, khách hàng)
-- Phụ trách: Tuấn Anh (tổng quản lý dữ liệu người dùng)
-- ===========================
CREATE TABLE IF NOT EXISTS nguoi_dung (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    ten_dang_nhap VARCHAR(100) NOT NULL UNIQUE,
    mat_khau VARCHAR(255) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    so_dt VARCHAR(20) DEFAULT 'chuacapnhat',
    vai_tro VARCHAR(50) DEFAULT 'khachhang',
    trang_thai VARCHAR(50) DEFAULT 'chuakichhoat',
    hoat_dong VARCHAR(50) DEFAULT 'offline', 
    ngay_tao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT chk_nguoi_dung_vai_tro CHECK (vai_tro IN ('khachhang','quantri','moigioi')),
    CONSTRAINT chk_nguoi_dung_trang_thai CHECK (trang_thai IN ('danghoatdong','chuakichhoat','khoa')),
    CONSTRAINT chk_nguoi_dung_hoat_dong CHECK (hoat_dong IN ('online','offline')),
    CONSTRAINT chk_nguoi_dung_so_dt CHECK (so_dt ~ '^[0-9]{1,11}$' OR so_dt = 'chuacapnhat')
);

SELECT * FROM nguoi_dung

INSERT INTO nguoi_dung (ten_dang_nhap, mat_khau, email, so_dt, vai_tro, trang_thai, hoat_dong)
VALUES
('nguyenvana', 'demo@123', 'vana.nguyen@example.com', '0987654321', 'khachhang', 'danghoatdong', 'offline'),
('tranthibich', 'demo@123', 'bich.tran@example.com', '0912345678', 'khachhang', 'danghoatdong', 'online'),
('lehoangnam', 'demo@123', 'nam.le@example.com', '0901234567', 'moigioi', 'danghoatdong', 'offline'),
('phamthuydung', 'demo@123', 'dung.pham@example.com', '0976543210', 'khachhang', 'chuakichhoat', 'offline'),
('vuhoanganh', 'demo@123', 'anh.vu@example.com', '0934567890', 'moigioi', 'danghoatdong', 'online'),
('dinhquanghuy', 'demo@123', 'huy.dinh@example.com', '0981112233', 'khachhang', 'khoa', 'offline'),
('ngothutrang', 'demo@123', 'trang.ngo@example.com', '0923456789', 'khachhang', 'danghoatdong', 'online'),
('truongminhtuan', 'demo@123', 'tuan.truong@example.com', '0945671234', 'quantri', 'danghoatdong', 'online'),
('phanthihong', 'demo@123', 'hong.phan@example.com', '0956789123', 'khachhang', 'danghoatdong', 'offline'),
('buiducmanh', 'demo@123', 'manh.bui@example.com', '0967891234', 'moigioi', 'chuakichhoat', 'offline'),
('doanthithu', 'demo@123', 'thu.doan@example.com', '0978912345', 'khachhang', 'danghoatdong', 'online'),
('hoangminhquan', 'demo@123', 'quan.hoang@example.com', '0989123456', 'khachhang', 'khoa', 'offline'),
('trinhthibao', 'demo@123', 'bao.trinh@example.com', '0912233445', 'moigioi', 'danghoatdong', 'online'),
('maiquangvinh', 'demo@123', 'vinh.mai@example.com', '0923344556', 'khachhang', 'danghoatdong', 'offline'),
('nguyenhongson', 'demo@123', 'son.nguyen@example.com', '0934455667', 'quantri', 'danghoatdong', 'online'),
('dangthithao', 'demo@123', 'thao.dang@example.com', '0945566778', 'khachhang', 'chuakichhoat', 'offline'),
('trankhanhlinh', 'demo@123', 'linh.tran@example.com', '0956677889', 'moigioi', 'danghoatdong', 'online'),
('luongminhduc', 'demo@123', 'duc.luong@example.com', '0967788990', 'khachhang', 'danghoatdong', 'offline'),
('hoangthianh', 'demo@123', 'anh.hoang@example.com', '0978899001', 'khachhang', 'danghoatdong', 'online'),
('nguyenquynhanh', 'demo@123', 'anh.quynh@example.com', '0989900112', 'khachhang', 'khoa', 'offline');

-- ===========================
-- 1. Bảng quan_tri (profile quản trị)
-- Mô tả: Thông tin chi tiết cho quản trị
-- Phụ trách: Tuấn Anh 
-- Chức năng: Mỗi tải khoản chỉ truy cập được dữ liệu cá nhân
-- ===========================
CREATE TABLE IF NOT EXISTS quan_tri (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    id_nguoi_dung UUID UNIQUE NOT NULL,
    ho_ten VARCHAR(150) DEFAULT 'chuacapnhat',
    gioi_tinh VARCHAR(20) DEFAULT 'chuacapnhat',
    avt TEXT DEFAULT 'avt.png',
    dia_chi TEXT DEFAULT 'chuacapnhat',
    ngay_sinh DATE DEFAULT (CURRENT_DATE - INTERVAL '18 years'),
    CONSTRAINT fk_quan_tri_nguoi_dung FOREIGN KEY (id_nguoi_dung) REFERENCES nguoi_dung(id) ON DELETE CASCADE,
    CONSTRAINT chk_quan_tri_gioi_tinh CHECK (gioi_tinh IN ('nam','nu','khac','chuacapnhat')),
    CONSTRAINT chk_quan_tri_tuoi CHECK (ngay_sinh <= CURRENT_DATE - INTERVAL '18 years')
);

-- ===========================
-- 2. Bảng khach_hang (profile khách hàng)
-- Mô tả: Thông tin chi tiết cho khách hàng
-- Phụ trách: Quốc Đặng
-- Chức năng: + Đặng: Chỉ truy cập được dữ liệu cá nhân, có quyền cập nhật
--			  + Quỳnh: Chỉ xem được thông tin khách hàng, thông tin chi tiết
--			  + Tuấn Anh: Xèm toàn bộ thông tin, xóa, gửi yêu cầu cập nhật, kích hoạt hoặc khóa
-- ===========================
CREATE TABLE IF NOT EXISTS khach_hang (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    id_nguoi_dung UUID UNIQUE NOT NULL,
    ho_ten VARCHAR(150) DEFAULT 'chuacapnhat',
    gioi_tinh VARCHAR(20) DEFAULT 'chuacapnhat',
    avt TEXT DEFAULT 'avt.png',
    dia_chi TEXT DEFAULT 'chuacapnhat',
    ngay_sinh DATE DEFAULT (CURRENT_DATE - INTERVAL '18 years'),
    CONSTRAINT fk_khach_hang_nguoi_dung FOREIGN KEY (id_nguoi_dung) REFERENCES nguoi_dung(id) ON DELETE CASCADE,
    CONSTRAINT chk_khach_hang_gioi_tinh CHECK (gioi_tinh IN ('nam','nu','khac','chuacapnhat')),
    CONSTRAINT chk_khach_hang_tuoi CHECK (ngay_sinh <= CURRENT_DATE - INTERVAL '18 years')
);

-- ===========================
-- 3. Bảng moi_gioi (profile môi giới)
-- Mô tả: Thông tin chi tiết cho môi giới
-- Phụ trách: Ngọc Quỳnh
-- Chức năng: + Quỳnh: Chỉ truy cập được dữ liệu cá nhân, có quyền cập nhật, đăng ký -> tài khoản môi giới
--            + Tuấn Anh: Quản lý các tài khoản, phê duyệt yêu cầu đăng ký, xóa, khóa tài khoản
-- ===========================
CREATE TABLE IF NOT EXISTS moi_gioi (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    id_nguoi_dung UUID UNIQUE NOT NULL,
    ho_ten VARCHAR(150) DEFAULT 'chuacapnhat',
    avt TEXT DEFAULT 'avt.png',
    gioi_tinh VARCHAR(20) DEFAULT 'chuacapnhat',
    cty VARCHAR(200) DEFAULT 'chuacapnhat',
    kinh_nghiem INT CHECK (kinh_nghiem >= 0) DEFAULT 0,
    mo_ta TEXT DEFAULT 'chuacapnhat',
    CONSTRAINT fk_moi_gioi_nguoi_dung FOREIGN KEY (id_nguoi_dung) REFERENCES nguoi_dung(id) ON DELETE CASCADE,
    CONSTRAINT chk_moi_gioi_gioi_tinh CHECK (gioi_tinh IN ('nam','nu','khac','chuacapnhat'))
);

CREATE OR REPLACE FUNCTION fn_after_insert_nguoi_dung()
RETURNS TRIGGER AS $$
BEGIN
    -- Nếu là quản trị viên
    IF NEW.vai_tro = 'quantri' THEN
        INSERT INTO quan_tri (id_nguoi_dung) VALUES (NEW.id);
    -- Nếu là khách hàng
    ELSIF NEW.vai_tro = 'khachhang' THEN
        INSERT INTO khach_hang (id_nguoi_dung) VALUES (NEW.id);
    -- Nếu là môi giới
    ELSIF NEW.vai_tro = 'moigioi' THEN
        INSERT INTO moi_gioi (id_nguoi_dung) VALUES (NEW.id);
    END IF;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trg_after_insert_nguoi_dung
AFTER INSERT ON nguoi_dung
FOR EACH ROW
EXECUTE FUNCTION fn_after_insert_nguoi_dung();

-- ===========================
-- 4. Bảng bat_dong_san (bất động sản)
-- Mô tả: Môi giới sẽ tạo ra các sp bds, để đăng bán
-- Phụ trách: Quỳnh (tạo sp), Tuấn Anh (kiểm duyệt + quản lý các sản phẩm), Đặng (xem được các sản phẩm mà môi giới đăng)
-- ===========================
CREATE TABLE IF NOT EXISTS bat_dong_san (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    id_moi_gioi UUID, 
    tieu_de VARCHAR(200) DEFAULT 'chuacapnhat',
    mo_ta TEXT DEFAULT 'chuacapnhat',
    gia NUMERIC(18,2) CHECK (gia >= 0) DEFAULT 0,
    dien_tich NUMERIC(10,2) CHECK (dien_tich > 0) DEFAULT 0,
    dia_chi TEXT DEFAULT 'chuacapnhat',
    loai VARCHAR(100) DEFAULT 'chuacapnhat',
    khu_vuc VARCHAR(100) DEFAULT 'chuacapnhat',
    trang_thai VARCHAR(50) DEFAULT 'choduyet',
    ngay_dang TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT chk_bds_loai CHECK (loai IN ('ban','thue','duan','chuacapnhat')),
    CONSTRAINT chk_bds_trang_thai CHECK (trang_thai IN ('choduyet','dangban','daban','dathue')),
    CONSTRAINT fk_bds_moi_gioi FOREIGN KEY (id_moi_gioi) REFERENCES moi_gioi(id) ON DELETE CASCADE
);

INSERT INTO bat_dong_san (id_moi_gioi, tieu_de, mo_ta, gia, dien_tich, dia_chi, loai, khu_vuc, trang_thai)
VALUES
-- môi giới 1
('b6e7dbf5-37a3-423d-a51e-59fc00467984', 'Căn hộ cao cấp Vinhomes', 'Căn hộ 2PN full nội thất', 3500000000, 75.5, 'Quận 1, TP.HCM', 'ban', 'TP.HCM', 'dangban'),
('b6e7dbf5-37a3-423d-a51e-59fc00467984', 'Nhà phố trung tâm', 'Nhà 3 tầng, sổ hồng riêng', 5200000000, 120.0, 'Quận 3, TP.HCM', 'ban', 'TP.HCM', 'dangban'),
('b6e7dbf5-37a3-423d-a51e-59fc00467984', 'Căn hộ dịch vụ', 'Cho thuê ngắn hạn, đầy đủ tiện nghi', 15000000, 55.0, 'Quận Bình Thạnh, TP.HCM', 'thue', 'TP.HCM', 'choduyet'),
('b6e7dbf5-37a3-423d-a51e-59fc00467984', 'Đất nền dự án', 'Khu dân cư mới, hạ tầng hoàn chỉnh', 1200000000, 100.0, 'Thủ Đức, TP.HCM', 'duan', 'TP.HCM', 'dangban'),

-- môi giới 2
('0ee0decd-fae2-4ee6-83a4-b2d6d96f0587', 'Nhà nguyên căn cho thuê', 'Nhà 2 tầng, phù hợp gia đình nhỏ', 18000000, 90.0, 'Quận 7, TP.HCM', 'thue', 'TP.HCM', 'dangban'),
('0ee0decd-fae2-4ee6-83a4-b2d6d96f0587', 'Căn hộ mini giá rẻ', '1PN, đầy đủ tiện ích, gần trường học', 950000000, 45.0, 'Quận Gò Vấp, TP.HCM', 'ban', 'TP.HCM', 'choduyet'),
('0ee0decd-fae2-4ee6-83a4-b2d6d96f0587', 'Biệt thự nghỉ dưỡng', 'Biệt thự ven biển, hồ bơi riêng', 15500000000, 300.0, 'Nha Trang, Khánh Hòa', 'ban', 'Khánh Hòa', 'dangban'),
('0ee0decd-fae2-4ee6-83a4-b2d6d96f0587', 'Căn hộ officetel', 'Thiết kế hiện đại, tiện ích đa năng', 2200000000, 60.0, 'Quận 2, TP.HCM', 'ban', 'TP.HCM', 'choduyet'),

-- môi giới 3
('f72326b2-c29f-42f5-b928-f8fa7ddfe328', 'Mặt bằng kinh doanh', 'Mặt tiền đường lớn, đông đúc', 45000000, 150.0, 'Quận Tân Bình, TP.HCM', 'thue', 'TP.HCM', 'dangban'),
('f72326b2-c29f-42f5-b928-f8fa7ddfe328', 'Đất vườn nghỉ dưỡng', 'Không khí trong lành, phù hợp nghỉ dưỡng', 2500000000, 500.0, 'Bảo Lộc, Lâm Đồng', 'ban', 'Lâm Đồng', 'dangban'),
('f72326b2-c29f-42f5-b928-f8fa7ddfe328', 'Nhà phố khu đô thị mới', 'Nhà 4PN, gần trường học, chợ', 4200000000, 140.0, 'Quận 9, TP.HCM', 'ban', 'TP.HCM', 'dangban'),
('f72326b2-c29f-42f5-b928-f8fa7ddfe328', 'Căn hộ cao cấp The Manor', '3PN, view sông', 5800000000, 110.0, 'Quận Bình Thạnh, TP.HCM', 'ban', 'TP.HCM', 'dangban'),

-- môi giới 4
('5b1469ab-190d-41f2-894e-a118718fd18e', 'Đất nền thổ cư', 'Sổ đỏ riêng, xây dựng tự do', 1700000000, 80.0, 'Long An', 'ban', 'Long An', 'choduyet'),
('5b1469ab-190d-41f2-894e-a118718fd18e', 'Căn hộ studio giá rẻ', 'Nội thất đầy đủ, tiện nghi', 1200000000, 40.0, 'Quận 12, TP.HCM', 'ban', 'TP.HCM', 'dangban'),
('5b1469ab-190d-41f2-894e-a118718fd18e', 'Shophouse thương mại', 'Khu dân cư sầm uất, thuận tiện kinh doanh', 7500000000, 160.0, 'Bình Dương', 'ban', 'Bình Dương', 'dangban'),
('5b1469ab-190d-41f2-894e-a118718fd18e', 'Nhà trọ cho thuê', '10 phòng trọ, thu nhập ổn định', 6000000, 200.0, 'Quận Thủ Đức, TP.HCM', 'thue', 'TP.HCM', 'dangban'),

-- môi giới 5
('0c662b99-d4a4-430b-8838-f755d03cabf3', 'Căn hộ cao cấp Landmark 81', 'View sông, nội thất nhập khẩu', 7000000000, 130.0, 'Bình Thạnh, TP.HCM', 'ban', 'TP.HCM', 'dangban'),
('0c662b99-d4a4-430b-8838-f755d03cabf3', 'Biệt thự ven sông', 'Khu compound an ninh', 12500000000, 280.0, 'Quận 2, TP.HCM', 'ban', 'TP.HCM', 'choduyet'),
('0c662b99-d4a4-430b-8838-f755d03cabf3', 'Nhà phố giá rẻ', '2 tầng, thích hợp gia đình nhỏ', 2100000000, 70.0, 'Quận 8, TP.HCM', 'ban', 'TP.HCM', 'dangban'),
('0c662b99-d4a4-430b-8838-f755d03cabf3', 'Đất nền ven biển', 'Khu du lịch tiềm năng', 3200000000, 200.0, 'Phan Thiết, Bình Thuận', 'ban', 'Bình Thuận', 'dangban');

-- ===========================
-- 5. Bảng hinh_anh (ảnh sản phẩm)
-- Mô tả: 1 sản phẩm của bds có thể có 1 hoặc nhiều ảnh
-- Phụ trách: Quỳnh (upload ảnh) + Tuấn Anh (kiểm duyệt)
-- ===========================
CREATE TABLE IF NOT EXISTS hinh_anh (
    id SERIAL PRIMARY KEY,
    id_bds UUID NOT NULL,
    url VARCHAR(300),
    mo_ta VARCHAR(200),
    ngay_tao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_hinh_bds FOREIGN KEY (id_bds) REFERENCES bat_dong_san(id) ON DELETE CASCADE
);

-- ===========================
-- 6. Bảng video (video sản phẩm)
-- Mô tả: Các sản phẩm ngoài việc chỉ có ảnh thì có thể có thêm video
-- Phụ trách: Quỳnh (upload ảnh) + Tuấn Anh (kiểm duyệt)
-- ===========================
CREATE TABLE IF NOT EXISTS video (
    id SERIAL PRIMARY KEY,
    id_bds UUID NOT NULL,
    url VARCHAR(300),
    mo_ta VARCHAR(200),
    ngay_tao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_video_bds FOREIGN KEY (id_bds) REFERENCES bat_dong_san(id) ON DELETE CASCADE
);

-- ===========================
-- 7. Bảng danh_gia_bds (đánh giá bất động sản)
-- Mô tả: khách hàng đánh giá các sản phẩm BĐS mà môi giới gao bán
-- Phụ trách: Đặng (thực hiện đánh giá, feedback), Quỳnh (quản lý toàn bộ đánh giá cá nhân), Tuấn Anh (Quản lý)
-- Lưu ý: id_khach_hang cho phép NULL nếu khách hàng bị xóa nhưng đánh giá muốn giữ.
-- ===========================
CREATE TABLE IF NOT EXISTS danh_gia_bds (
    id SERIAL PRIMARY KEY,
    id_khach_hang UUID,
    id_bds UUID NOT NULL,
    diem INT CHECK (diem >= 1 AND diem <= 5),
    binh_luan TEXT,
    ngay_dg TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_dg_kh FOREIGN KEY (id_khach_hang) REFERENCES khach_hang(id) ON DELETE SET NULL,
    CONSTRAINT fk_dg_bds FOREIGN KEY (id_bds) REFERENCES bat_dong_san(id) ON DELETE CASCADE
);

-- ===========================
-- 8. Bảng hinh_anh_danh_gia_bds (ảnh kèm đánh giá)
-- Phụ trách: Đặng
-- ===========================
CREATE TABLE IF NOT EXISTS hinh_anh_danh_gia_bds (
    id SERIAL PRIMARY KEY,
    id_danh_gia_bds INT NOT NULL,
    url VARCHAR(300),
    mo_ta VARCHAR(200),
    CONSTRAINT fk_hinh_dg FOREIGN KEY (id_danh_gia_bds) REFERENCES danh_gia_bds(id) ON DELETE CASCADE
);

-- ===========================
-- 9. Bảng video_danh_gia_bds (video kèm đánh giá)
-- Phụ trách: Đặng
-- ===========================
CREATE TABLE IF NOT EXISTS video_danh_gia_bds (
    id SERIAL PRIMARY KEY,
    id_danh_gia_bds INT NOT NULL,
    url VARCHAR(300),
    mo_ta VARCHAR(200),
    CONSTRAINT fk_video_dg FOREIGN KEY (id_danh_gia_bds) REFERENCES danh_gia_bds(id) ON DELETE CASCADE
);

-- ===========================
-- 10. Bảng danh_gia_mg (đánh giá môi giới)
-- Phụ trách: Đặng (gửi feedback), Quỳnh (đáp ứng)
-- ===========================
CREATE TABLE IF NOT EXISTS danh_gia_mg (
    id SERIAL PRIMARY KEY,
    id_khach_hang UUID,
    id_moi_gioi UUID,
    diem INT CHECK (diem >= 1 AND diem <= 5),
    binh_luan TEXT,
    ngay_dg TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_dgmg_kh FOREIGN KEY (id_khach_hang) REFERENCES khach_hang(id) ON DELETE SET NULL,
    CONSTRAINT fk_dgmg_mg FOREIGN KEY (id_moi_gioi) REFERENCES moi_gioi(id) ON DELETE SET NULL
);

-- ===========================
-- 11. Bảng giao_dich (ghi nhận giao dịch mua/bán/thue)
-- Phụ trách: Tuấn Anh (quản lý giao dịch), Đặng (kiểm tra thực tế)
-- Lưu ý: id_khach_hang/id_bds có thể NULL nếu bên liên quan bị xóa nhưng muốn giữ bản ghi giao dịch.
-- ===========================
CREATE TABLE IF NOT EXISTS giao_dich (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    id_khach_hang UUID,
    id_bds UUID,
    loai VARCHAR(50) NOT NULL,  -- 'mua','ban','thue'
    ngay_giao_dich TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    trang_thai VARCHAR(50) DEFAULT 'dang_xu_ly', -- 'dang_xu_ly','hoan_tat','huy'
    CONSTRAINT chk_gd_loai CHECK (loai IN ('mua','ban','thue')),
    CONSTRAINT fk_gd_kh FOREIGN KEY (id_khach_hang) REFERENCES khach_hang(id) ON DELETE SET NULL,
    CONSTRAINT fk_gd_bds FOREIGN KEY (id_bds) REFERENCES bat_dong_san(id) ON DELETE SET NULL
);

-- ===========================
-- 12. Bảng thanh_toan (tổng thanh toán liên quan giao dịch)
-- Phụ trách: Tuấn Anh, Đặng
-- ===========================
CREATE TABLE IF NOT EXISTS thanh_toan (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    id_giao_dich UUID NOT NULL,
    tong_tien NUMERIC(18,2) CHECK (tong_tien >= 0),
    ngay_tt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    phuong_thuc VARCHAR(100), -- e.g. 'chuyenkhoan','visa','cod'
    trang_thai VARCHAR(50) DEFAULT 'mo', -- 'mo','thanh_cong','that_bai'
    CONSTRAINT fk_tt_gd FOREIGN KEY (id_giao_dich) REFERENCES giao_dich(id) ON DELETE CASCADE
);

-- ===========================
-- 13. Bảng thanh_toan_ct (chi tiết thanh toán)
-- Phụ trách: Tuấn Anh
-- ===========================
CREATE TABLE IF NOT EXISTS thanh_toan_ct (
    id SERIAL PRIMARY KEY,
    id_thanh_toan UUID NOT NULL,
    id_bds UUID,
    so_luong INT DEFAULT 1,
    so_tien NUMERIC(18,2) CHECK (so_tien >= 0),
    CONSTRAINT fk_ttc_tt FOREIGN KEY (id_thanh_toan) REFERENCES thanh_toan(id) ON DELETE CASCADE,
    CONSTRAINT fk_ttc_bds FOREIGN KEY (id_bds) REFERENCES bat_dong_san(id) ON DELETE SET NULL
);

-- ===========================
-- 14. Bảng lich_su_thanh_toan (log thanh toán online)
-- Phụ trách: Đặng (kiểm thử thanh toán), Tuấn Anh (tích hợp)
-- ===========================
CREATE TABLE IF NOT EXISTS lich_su_thanh_toan (
    id SERIAL PRIMARY KEY,
    id_thanh_toan UUID,
    provider VARCHAR(100), -- e.g. 'momo','vnpay','stripe'
    provider_transaction_id VARCHAR(200),
    amount NUMERIC(18,2),
    status VARCHAR(50),
    payload JSONB,
    ngay_log TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_lstt_tt FOREIGN KEY (id_thanh_toan) REFERENCES thanh_toan(id) ON DELETE SET NULL
);

-- ===========================
-- 15. Bảng truy_cap_bds (thống kê lượt truy cập)
-- Phụ trách: Tuấn Anh (analytics)
-- ===========================
CREATE TABLE IF NOT EXISTS truy_cap_bds (
    id SERIAL PRIMARY KEY,
    id_bds UUID NOT NULL,
    ngay DATE NOT NULL,
    so_luot INT DEFAULT 0,
    CONSTRAINT fk_truycap_bds FOREIGN KEY (id_bds) REFERENCES bat_dong_san(id) ON DELETE CASCADE,
    CONSTRAINT uq_truycap UNIQUE (id_bds, ngay)
);

-- ===========================
-- 16. Bảng lich_su_xem_bds (lưu lịch sử cá nhân xem BĐS)
-- Phụ trách: Đặng
-- ===========================
CREATE TABLE IF NOT EXISTS lich_su_xem_bds (
    id SERIAL PRIMARY KEY,
    id_nguoi_dung UUID NOT NULL,
    id_bds UUID NOT NULL,
    thoi_gian TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_lsx_nguoi FOREIGN KEY (id_nguoi_dung) REFERENCES nguoi_dung(id) ON DELETE CASCADE,
    CONSTRAINT fk_lsx_bds FOREIGN KEY (id_bds) REFERENCES bat_dong_san(id) ON DELETE CASCADE
);

-- ===========================
-- 17. Bảng lich_su_tim_kiem (lưu lịch sử tìm kiếm)
-- Phụ trách: Tuấn Anh (analytics)
-- ===========================
CREATE TABLE IF NOT EXISTS lich_su_tim_kiem (
    id SERIAL PRIMARY KEY,
    id_nguoi_dung UUID,
    tu_khoa VARCHAR(200),
    filters JSONB,
    ngay TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_lstk_nguoi FOREIGN KEY (id_nguoi_dung) REFERENCES nguoi_dung(id) ON DELETE SET NULL
);

-- ===========================
-- 18. Bảng dat_lich (đặt lịch xem)
-- Phụ trách: Quỳnh (sắp xếp lịch), Đặng (khách hàng tạo yêu cầu)
-- Lưu ý: id_khach_hang có thể NULL nếu khách bị xóa nhưng muốn lưu lịch (lịch có thể được giữ).
-- ===========================
CREATE TABLE IF NOT EXISTS dat_lich (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    id_khach_hang UUID,
    id_bds UUID NOT NULL,
    thoi_gian TIMESTAMP NOT NULL,
    trang_thai VARCHAR(50) DEFAULT 'cho_xac_nhan', -- 'cho_xac_nhan','da_xac_nhan','da_huy'
    ghi_chu TEXT,
    CONSTRAINT fk_datlich_kh FOREIGN KEY (id_khach_hang) REFERENCES khach_hang(id) ON DELETE SET NULL,
    CONSTRAINT fk_datlich_bds FOREIGN KEY (id_bds) REFERENCES bat_dong_san(id) ON DELETE CASCADE
);

-- ===========================
-- 19. Bảng bao_cao (báo cáo vi phạm)
-- Phụ trách: Tuấn Anh (xử lý), Quỳnh (liên quan)
-- ===========================
CREATE TABLE IF NOT EXISTS bao_cao (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    id_khach_hang UUID,
    id_moi_gioi UUID,
    id_bds UUID,
    noi_dung TEXT,
    ngay_bc TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    trang_thai VARCHAR(50) DEFAULT 'cho_xu_ly', -- 'cho_xu_ly','da_xu_ly','khong_xac_thuc'
    CONSTRAINT fk_baocao_kh FOREIGN KEY (id_khach_hang) REFERENCES khach_hang(id) ON DELETE SET NULL,
    CONSTRAINT fk_baocao_mg FOREIGN KEY (id_moi_gioi) REFERENCES moi_gioi(id) ON DELETE SET NULL,
    CONSTRAINT fk_baocao_bds FOREIGN KEY (id_bds) REFERENCES bat_dong_san(id) ON DELETE SET NULL
);

-- ===========================
-- 20. Bảng tin_nhan (hệ thống tin nhắn giữa users)
-- Phụ trách: Đặng (kiểm thử), Tuấn Anh (hạ tầng)
-- ===========================
CREATE TABLE IF NOT EXISTS tin_nhan (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    id_gui UUID NOT NULL,
    id_nhan UUID NOT NULL,
    noi_dung TEXT,
    ngay_gui TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_tn_gui FOREIGN KEY (id_gui) REFERENCES nguoi_dung(id) ON DELETE CASCADE,
    CONSTRAINT fk_tn_nhan FOREIGN KEY (id_nhan) REFERENCES nguoi_dung(id) ON DELETE CASCADE
);

-- ===========================
-- 21. Bảng bai_viet (tin tức / blog)
-- Phụ trách: Tuấn Anh (quản lý nội dung), Quỳnh (viết nội dung môi giới)
-- ===========================
CREATE TABLE IF NOT EXISTS bai_viet (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    tieu_de VARCHAR(200),
    noi_dung TEXT,
    tac_gia UUID,
    loai VARCHAR(50),
    ngay_dang TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_bv_tacgia FOREIGN KEY (tac_gia) REFERENCES nguoi_dung(id) ON DELETE SET NULL
);

-- ===========================
-- 22. Bảng bai_viet_binh_luan (bình luận bài viết)
-- Phụ trách: Đặng (quản lý comment)
-- ===========================
CREATE TABLE IF NOT EXISTS bai_viet_binh_luan (
    id SERIAL PRIMARY KEY,
    id_nguoi_dung UUID NOT NULL,
    id_bai_viet UUID NOT NULL,
    noi_dung TEXT,
    ngay_bl TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_bvb_nguoi FOREIGN KEY (id_nguoi_dung) REFERENCES nguoi_dung(id) ON DELETE CASCADE,
    CONSTRAINT fk_bvb_bai FOREIGN KEY (id_bai_viet) REFERENCES bai_viet(id) ON DELETE CASCADE
);

-- ===========================
-- 23. Bảng bai_viet_yeu_thich (yêu thích bài viết)
-- Phụ trách: Đặng
-- ===========================
CREATE TABLE IF NOT EXISTS bai_viet_yeu_thich (
    id SERIAL PRIMARY KEY,
    id_nguoi_dung UUID NOT NULL,
    id_bai_viet UUID NOT NULL,
    ngay_them TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_bvyt_nguoi FOREIGN KEY (id_nguoi_dung) REFERENCES nguoi_dung(id) ON DELETE CASCADE,
    CONSTRAINT fk_bvyt_bai FOREIGN KEY (id_bai_viet) REFERENCES bai_viet(id) ON DELETE CASCADE,
    CONSTRAINT uq_bvyt UNIQUE (id_nguoi_dung, id_bai_viet)
);

-- ===========================
-- 24. Bảng banner (slider)
-- Phụ trách: Tuấn Anh (quản trị giao diện)
-- ===========================
CREATE TABLE IF NOT EXISTS banner (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    tieu_de VARCHAR(200),
    url VARCHAR(300),
    vi_tri VARCHAR(100),
    ngay_tao TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ===========================
-- 25. Bảng faq (câu hỏi thường gặp)
-- Phụ trách: Tuấn Anh
-- ===========================
CREATE TABLE IF NOT EXISTS faq (
    id SERIAL PRIMARY KEY,
    cau_hoi TEXT,
    cau_tra_loi TEXT
);

-- ===========================
-- 26. Bảng thong_bao (thông báo hệ thống)
-- Phụ trách: Tuấn Anh
-- ===========================
CREATE TABLE IF NOT EXISTS thong_bao (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    tieu_de VARCHAR(200),
    noi_dung TEXT,
    doi_tuong VARCHAR(50) DEFAULT 'tat_ca', -- 'tat_ca','khach_hang','moi_gioi'
    ngay_tb TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ===========================
-- 27. Bảng khuyen_mai (chiến dịch/ mã giảm giá)
-- Phụ trách: Tuấn Anh (xây dựng), Đặng (kiểm thử)
-- ===========================
CREATE TABLE IF NOT EXISTS khuyen_mai (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    ma_giam VARCHAR(50) UNIQUE,
    mo_ta TEXT,
    phan_tram NUMERIC(5,2), -- ví dụ 10.00 = 10%
    so_tien NUMERIC(18,2),  -- hoặc số tiền cố định
    ngay_bd DATE,
    ngay_kt DATE,
    dieu_kien JSONB, -- điều kiện áp dụng (json)
    trang_thai VARCHAR(50) DEFAULT 'hoat_dong' -- 'hoat_dong','het_han','tam_dung'
);

-- ===========================
-- 28. Bảng voucher (mã đã phát/thuộc KH)
-- Phụ trách: Đặng (khách) quản lý mã khuyến mãi
-- ===========================
CREATE TABLE IF NOT EXISTS voucher (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    id_kh UUID,
    id_khuyen_mai UUID,
    ma VARCHAR(100),
    ngay_phat TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ngay_sd TIMESTAMP,
    trang_thai VARCHAR(50) DEFAULT 'chua_su_dung', -- 'chua_su_dung','da_su_dung','het_han'
    CONSTRAINT fk_voucher_kh FOREIGN KEY (id_kh) REFERENCES khach_hang(id) ON DELETE SET NULL,
    CONSTRAINT fk_voucher_km FOREIGN KEY (id_khuyen_mai) REFERENCES khuyen_mai(id) ON DELETE CASCADE
);

-- ===========================
-- 29. Bảng goi_dich_vu (gói dịch vụ/trả phí)
-- Phụ trách: Tuấn Anh
-- ===========================
CREATE TABLE IF NOT EXISTS goi_dich_vu (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    ten_goi VARCHAR(100),
    mo_ta TEXT,
    gia NUMERIC(18,2),
    thoi_han INT -- số ngày
);

-- ===========================
-- 30. Bảng dang_ky_goi (môi giới đăng ký gói)
-- Phụ trách: Quỳnh (môi giới) / Tuấn Anh (xác nhận)
-- ===========================
CREATE TABLE IF NOT EXISTS dang_ky_goi (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    id_moi_gioi UUID NOT NULL,
    id_goi UUID NOT NULL,
    ngay_dk TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ngay_het TIMESTAMP,
    trang_thai VARCHAR(50) DEFAULT 'dang_hieu_luc',
    CONSTRAINT fk_dkg_mg FOREIGN KEY (id_moi_gioi) REFERENCES moi_gioi(id) ON DELETE CASCADE,
    CONSTRAINT fk_dkg_goi FOREIGN KEY (id_goi) REFERENCES goi_dich_vu(id) ON DELETE CASCADE
);

-- ===========================
-- 31. Bảng ho_tro (ticket hỗ trợ / CRM)
-- Phụ trách: Quỳnh (tương tác môi giới), Tuấn Anh (giải quyết kỹ thuật)
-- ===========================
CREATE TABLE IF NOT EXISTS ho_tro (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    id_nguoi_dung UUID NOT NULL,
    tieu_de VARCHAR(200),
    noi_dung TEXT,
    trang_thai VARCHAR(50) DEFAULT 'mo', -- 'mo','dang_xu_ly','dong'
    nguoi_phut_rac UUID,
    ngay_tao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ngay_cap_nhat TIMESTAMP,
    CONSTRAINT fk_hotro_nguoi FOREIGN KEY (id_nguoi_dung) REFERENCES nguoi_dung(id) ON DELETE SET NULL,
    CONSTRAINT fk_hotro_phutrach FOREIGN KEY (nguoi_phut_rac) REFERENCES nguoi_dung(id) ON DELETE SET NULL
);

-- ===========================
-- 32. Bảng doi_tac (đối tác: ngân hàng, thẩm định, luật sư...)
-- Phụ trách: Quỳnh (quan hệ đối tác), Tuấn Anh (admin hợp tác)
-- ===========================
CREATE TABLE IF NOT EXISTS doi_tac (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    ten_doi_tac VARCHAR(200),
    loai VARCHAR(100), -- 'ngan_hang','tham_dinh','luat_su','khac'
    lien_he JSONB,
    dia_chi TEXT,
    trang_thai VARCHAR(50) DEFAULT 'hoat_dong'
);

-- ===========================
-- 33. Bảng lich_su_he_thong (log hệ thống chung)
-- Phụ trách: Tuấn Anh
-- ===========================
CREATE TABLE IF NOT EXISTS lich_su_he_thong (
    id SERIAL PRIMARY KEY,
    actor UUID,
    action VARCHAR(200),
    object_type VARCHAR(100),
    object_id VARCHAR(200),
    payload JSONB,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_lsh_actor FOREIGN KEY (actor) REFERENCES nguoi_dung(id) ON DELETE SET NULL
);

