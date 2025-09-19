CREATE EXTENSION IF NOT EXISTS "uuid-ossp";

-- 1. Bảng người dùng (chung) với ràng buộc dữ liệu
CREATE TABLE nguoi_dung (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    ten_dang_nhap VARCHAR(100) NOT NULL UNIQUE,       
    mat_khau VARCHAR(255) NOT NULL,                 
    email VARCHAR(150) NOT NULL UNIQUE,              
    so_dt VARCHAR(20) UNIQUE DEFAULT 'chuacapnhat',                        
    vai_tro VARCHAR(50) DEFAULT 'khachhang',     
    trang_thai VARCHAR(50) DEFAULT 'danghoatdong', 
    hoat_dong VARCHAR(50) DEFAULT 'offline',        
    ngay_tao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    -- Ràng buộc CHECK để kiểm soát giá trị hợp lệ
    CONSTRAINT chk_vai_tro CHECK (vai_tro IN ('khachhang', 'quantri', 'moigioi')),
    CONSTRAINT chk_trang_thai CHECK (trang_thai IN ('danghoatdong', 'chuakichhoat', 'khoa')),
    CONSTRAINT chk_hoat_dong CHECK (hoat_dong IN ('online', 'offline')),
    -- Chỉ kiểm tra số điện thoại < 11 số
    CONSTRAINT chk_so_dt CHECK (so_dt ~ '^[0-9]{0,10}$')
);

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

SELECT * FROM nguoi_dung

-- 2. Bảng admin
CREATE TABLE quan_tri (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    id_nguoi_dung UUID UNIQUE NOT NULL,
    ho_ten VARCHAR(150) DEFAULT 'chuacapnhat',
    gioi_tinh VARCHAR(20) DEFAULT 'chuacapnhat',
    avt TEXT DEFAULT 'avt.png',
    dia_chi TEXT DEFAULT 'chuacapnhat',
    ngay_sinh DATE DEFAULT (CURRENT_DATE - INTERVAL '18 years'),
    -- Khóa ngoại
    CONSTRAINT fk_admin_nguoidung FOREIGN KEY (id_nguoi_dung) REFERENCES nguoi_dung(id) ON DELETE CASCADE,
    -- Giới tính hợp lệ
    CONSTRAINT chk_gioi_tinh CHECK (gioi_tinh IN ('nam', 'nu', 'khac', 'chuacapnhat')),
    -- Ngày sinh phải >= 18 tuổi
    CONSTRAINT chk_tuoi CHECK (ngay_sinh <= CURRENT_DATE - INTERVAL '18 years')
);

SELECT * FROM quan_tri

-- 3. Bảng khách hàng
CREATE TABLE khach_hang (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    id_nguoi_dung UUID UNIQUE NOT NULL,
    ho_ten VARCHAR(150) DEFAULT 'chuacapnhat',
    gioi_tinh VARCHAR(20) DEFAULT 'chuacapnhat',
    avt TEXT DEFAULT 'avt.png',
    dia_chi TEXT DEFAULT 'chuacapnhat',
    ngay_sinh DATE DEFAULT (CURRENT_DATE - INTERVAL '18 years'),
    -- Khóa ngoại
    CONSTRAINT fk_khachhang_nguoidung FOREIGN KEY (id_nguoi_dung) REFERENCES nguoi_dung(id) ON DELETE CASCADE,
    -- Giới tính hợp lệ
    CONSTRAINT chk_khachhang_gioitinh CHECK (gioi_tinh IN ('nam', 'nu', 'khac', 'chuacapnhat')),
    -- Ngày sinh phải >= 18 tuổi
    CONSTRAINT chk_khachhang_tuoi CHECK (ngay_sinh <= CURRENT_DATE - INTERVAL '18 years')
);

SELECT * FROM khach_hang

-- 4. Bảng môi giới
CREATE TABLE moi_gioi (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),                            
    id_nguoi_dung UUID UNIQUE NOT NULL,                   
    ho_ten VARCHAR(150) DEFAULT 'chuacapnhat',                        
    avt TEXT DEFAULT 'avt.png',                          
    gioi_tinh VARCHAR(20) DEFAULT 'chuacapnhat',                     
    cty VARCHAR(200) DEFAULT 'chuacapnhat',                                   
    kinh_nghiem INT CHECK (kinh_nghiem >= 0),            
    mo_ta TEXT DEFAULT 'chuacapnhat',
    -- Khóa ngoại
    CONSTRAINT fk_moigioi_nguoidung FOREIGN KEY (id_nguoi_dung) REFERENCES nguoi_dung(id) ON DELETE CASCADE,
    -- Giới tính hợp lệ
    CONSTRAINT chk_gioi_tinh CHECK (gioi_tinh IN ('nam', 'nu', 'khac', 'chuacapnhat'))
);

SELECT * FROM moi_gioi

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

-- 5. Bảng bất động sản
CREATE TABLE bat_dong_san (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(), 
	id_moi_gioi UUID NOT NULL,
    tieu_de VARCHAR(200) DEFAULT 'chuacapnhat',
    mo_ta TEXT DEFAULT 'chuacapnhat',
    gia NUMERIC(18,2) CHECK (gia >= 0),                  
    dien_tich NUMERIC(10,2) CHECK (dien_tich > 0),     
    dia_chi TEXT DEFAULT 'chuacapnhat',
    loai VARCHAR(100) DEFAULT 'chuacapnhat',                          
    khu_vuc VARCHAR(100) DEFAULT 'chuacapnhat',
    trang_thai VARCHAR(50) DEFAULT 'choduyet',
    ngay_dang TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    -- Ràng buộc giá trị hợp lệ
    CONSTRAINT chk_loai CHECK (loai IN ('ban', 'thue', 'duan', 'chuacapnhat')),
    CONSTRAINT chk_trang_thai_bds CHECK (trang_thai IN ('choduyet', 'dangban', 'daban', 'dathue')),
    -- Khóa ngoại: mỗi BĐS phải thuộc 1 môi giới
    CONSTRAINT fk_bds_moigioi FOREIGN KEY (id_moi_gioi) REFERENCES moi_gioi(id_nguoi_dung) ON DELETE CASCADE
);

-- 20 bất động sản mẫu với id_moi_gioi có sẵn
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

SELECT * FROM bat_dong_san

-- 6. Bảng hình ảnh sản phẩm
CREATE TABLE hinh_anh_bds (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(), 
    id_bds UUID UNIQUE NOT NULL,
    url TEXT[] NOT NULL,
    mo_ta VARCHAR(200) DEFAULT 'chuacapnhat',
    CONSTRAINT fk_hinhanh_bds FOREIGN KEY (id_bds) REFERENCES bat_dong_san(id) ON DELETE CASCADE
);

SELECT * FROM hinh_anh_bds

-- 7. Bảng video sản phẩm
CREATE TABLE video_bds (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    id_bds UUID UNIQUE NOT NULL,
    url TEXT[] NOT NULL,
    mo_ta VARCHAR(200) DEFAULT 'chuacapnhat',
	CONSTRAINT fk_video_bds FOREIGN KEY (id_bds) REFERENCES bat_dong_san(id) ON DELETE CASCADE
);

SELECT * FROM video_bds

-- 8. Bảng giao dịch
CREATE TABLE giao_dich (
    id SERIAL PRIMARY KEY,
    id_khach_hang INT,
    id_bds INT,
    loai VARCHAR(100), -- mua, ban, thue
    ngay_giao_dich TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    trang_thai VARCHAR(50)
);

INSERT INTO giao_dich (id_khach_hang, id_bds, loai, ngay_giao_dich, trang_thai)
VALUES
(3, 1, 'mua', '2025-09-16 10:30:00', 'hoàn tất'),

(4, 2, 'mua', '2025-09-17 09:00:00', 'đang xử lý'),

(5, 3, 'thue', '2025-09-18 14:20:00', 'hoàn tất'),

(3, 4, 'mua', '2025-09-15 08:45:00', 'đang xử lý'),

(4, 5, 'thue', '2025-09-16 11:15:00', 'hoàn tất'),

(5, 6, 'mua', '2025-09-17 12:00:00', 'hủy'),

(3, 7, 'mua', '2025-09-16 16:30:00', 'hoàn tất');

-- 9. Bảng thanh toán
CREATE TABLE thanh_toan (
    id SERIAL PRIMARY KEY,
    id_giao_dich INT,
    tong_tien NUMERIC(18,2),
    ngay_tt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    phuong_thuc VARCHAR(100)
);

-- 10. Bảng chi tiết thanh toán
CREATE TABLE thanh_toan_ct (
    id SERIAL PRIMARY KEY,
    id_thanh_toan INT,
    id_bds INT,
    so_luong INT,
    so_tien NUMERIC(18,2)
);

-- 11. Bảng quản lý truy cập
CREATE TABLE truy_cap_bds (
    id SERIAL PRIMARY KEY,
    id_bds INT,
    ngay DATE,
    so_luot INT
);

-- 12. Bảng đánh giá bất động sản
CREATE TABLE danh_gia_bds (
    id SERIAL PRIMARY KEY,
    id_khach_hang INT,
    id_bds INT,
    diem INT,
    binh_luan TEXT,
    ngay_dg TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


select * from danh_gia_bds

-- 6. Bảng hình ảnh sản phẩm
CREATE TABLE hinh_anh_danh_gia_bds (
    id SERIAL PRIMARY KEY,
    id_danh_gia_bds INT,
    url VARCHAR(300),
    mo_ta VARCHAR(200)
);

-- 7. Bảng video sản phẩm
CREATE TABLE video_danh_gia_bds (
    id SERIAL PRIMARY KEY,
    id_danh_gia_bds INT,
    url VARCHAR(300),
    mo_ta VARCHAR(200)
);

INSERT INTO danh_gia_bds (id_khach_hang, id_bds, diem, binh_luan, ngay_dg)
VALUES
(3, 1, 5, 'Căn hộ tuyệt vời, view sông rất đẹp!', '2025-09-16 10:00:00'),
(4, 2, 4, 'Nhà phố rộng rãi, gần chợ, tiện đi lại.', '2025-09-17 09:30:00'),
(5, 3, 5, 'Căn hộ dịch vụ đầy đủ nội thất, rất tiện.', '2025-09-18 14:45:00'),
(3, 4, 3, 'Dự án khá tốt nhưng hơi xa trung tâm.', '2025-09-15 08:50:00'),
(4, 5, 4, 'Chung cư mini giá hợp lý, phù hợp sinh viên.', '2025-09-16 11:20:00'),
(5, 6, 2, 'Căn hộ nhỏ, hơi chật, nhưng giá ổn.', '2025-09-17 12:10:00'),
(3, 7, 4, 'Nhà ở tốt, tiện ích đầy đủ, hài lòng.', '2025-09-16 16:40:00');

-- 13. Bảng đánh giá môi giới
CREATE TABLE danh_gia_mg (
    id SERIAL PRIMARY KEY,
    id_khach_hang INT,
    id_moi_gioi INT,
    diem INT,
    binh_luan TEXT,
    ngay_dg TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 14. Bảng tin tức / bài viết
CREATE TABLE bai_viet (
    id SERIAL PRIMARY KEY,
    tieu_de VARCHAR(200),
    noi_dung TEXT,
    loai VARCHAR(50),
    ngay_dang TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 15. Bảng yêu thích bài viết
CREATE TABLE bai_viet_yeu_thich (
    id SERIAL PRIMARY KEY,
    id_nguoi_dung INT,      
    id_bai_viet INT,        
    ngay_them TIMESTAMP DEFAULT CURRENT_TIMESTAMP 
);

-- 16. Bảng bình luận bài viết
CREATE TABLE bai_viet_binh_luan (
    id SERIAL PRIMARY KEY,
    id_nguoi_dung INT,       
    id_bai_viet INT,         
    noi_dung TEXT,            
    ngay_bl TIMESTAMP DEFAULT CURRENT_TIMESTAMP 
);

-- 17. Bảng banner / slider
CREATE TABLE banner (
    id SERIAL PRIMARY KEY,
    tieu_de VARCHAR(200),
    url VARCHAR(300),
    vi_tri VARCHAR(100),
    ngay_tao TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 19. Bảng FAQ
CREATE TABLE faq (
    id SERIAL PRIMARY KEY,
    cau_hoi TEXT,
    cau_tra_loi TEXT
);

-- 20. Bảng thông báo
CREATE TABLE thong_bao (
    id SERIAL PRIMARY KEY,
    tieu_de VARCHAR(200),
    noi_dung TEXT,
    doi_tuong VARCHAR(50), -- tat ca, khach hang, moi gioi
    ngay_tb TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 21. Bảng tin nhắn
CREATE TABLE tin_nhan (
    id SERIAL PRIMARY KEY,
    id_gui INT,
    id_nhan INT,
    noi_dung TEXT,
    ngay_gui TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 22. Bảng đặt lịch xem
CREATE TABLE dat_lich (
    id SERIAL PRIMARY KEY,
    id_khach_hang INT,
    id_bds INT,
    thoi_gian TIMESTAMP,
    trang_thai VARCHAR(50)
);

-- 23. Bảng báo cáo vi phạm
CREATE TABLE bao_cao (
    id SERIAL PRIMARY KEY,
    id_khach_hang INT,
    id_moi_gioi INT,
    id_bds INT,
    noi_dung TEXT,
    ngay_bc TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 24. Bảng lưu lịch sử xem
CREATE TABLE lich_su_xem_bds (
    id SERIAL PRIMARY KEY,
    id_nguoi_dung INT,
    id_bds INT,
    thoi_gian TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 24. Bảng lưu lịch sử tìm kiếm
CREATE TABLE lich_su_tim_kiem (
    id SERIAL PRIMARY KEY,
    id_nguoi_dung INT,
    tu_khoa VARCHAR(200),
    ngay TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 25. Bảng khuyến mãi
CREATE TABLE khuyen_mai (
    id SERIAL PRIMARY KEY,
    ma_giam VARCHAR(50),
    mo_ta TEXT,
    phan_tram NUMERIC(5,2),   
    so_tien NUMERIC(18,2),  
    ngay_bd DATE,
    ngay_kt DATE
);

-- 26. Bảng gói dịch vụ
CREATE TABLE goi_dich_vu (
    id SERIAL PRIMARY KEY,
    ten_goi VARCHAR(100),
    mo_ta TEXT,
    gia NUMERIC(18,2),
    thoi_han INT 
);

-- 27. Bảng đăng ký gói dịch vụ
CREATE TABLE dang_ky_goi (
    id SERIAL PRIMARY KEY,
    id_moi_gioi INT,
    id_goi INT,
    ngay_dk TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ngay_het TIMESTAMP
);