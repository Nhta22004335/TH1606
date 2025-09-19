-- 1. Bảng người dùng (chung)
CREATE TABLE nguoi_dung (
    id SERIAL PRIMARY KEY,
    ten_dang_nhap VARCHAR(100),
    mat_khau VARCHAR(255),
    email VARCHAR(150),
    so_dt VARCHAR(20),
    vai_tro VARCHAR(50), -- admin, khachhang, moigioi
    trang_thai VARCHAR(50),    -- hoat_dong, khoa
    ngay_tao TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);	

		
INSERT INTO nguoi_dung (ten_dang_nhap, mat_khau, email, so_dt, vai_tro, trang_thai)
VALUES
('admin1', '22004335', 'admin1@example.com', '0901000001', 'admin', 'hoat_dong'),
('admin2', '22004335', 'admin2@example.com', '0901000002', 'admin', 'chua_kich_hoat'),
('khach1', '123456', 'khach1@example.com', '0911000001', 'khachhang', 'hoat_dong'),
('khach2', '123456', 'khach2@example.com', '0911000002', 'khachhang', 'khoa'),
('khach3', '123456', 'khach3@example.com', '0911000003', 'khachhang', 'chua_kich_hoat'),
('moigioi1', '123456', 'moigioi1@example.com', '0922000001', 'moigioi', 'hoat_dong'),
('moigioi2', '123456', 'moigioi2@example.com', '0922000002', 'moigioi', 'khoa'),
('moigioi3', '123456', 'moigioi3@example.com', '0922000003', 'moigioi', 'chua_kich_hoat');

-- 2. Bảng admin
CREATE TABLE admin (
    id SERIAL PRIMARY KEY,
    id_nguoi_dung INT,    
    ho_ten VARCHAR(150),
    gioi_tinh VARCHAR(10),
    dia_chi TEXT,
    ngay_sinh DATE
);

INSERT INTO admin (id_nguoi_dung, ho_ten, gioi_tinh, dia_chi, ngay_sinh)
VALUES
(1, 'Nguyễn Văn A', 'Nam', '123 Nguyễn Huệ, Quận 1, TP.HCM', '1985-04-15'),
(2, 'Trần Thị B', 'Nữ', '45 Lê Lợi, Quận 3, TP.HCM', '1990-09-22');

-- 3. Bảng khách hàng
CREATE TABLE khach_hang (
    id SERIAL PRIMARY KEY,
    id_nguoi_dung INT,
    ho_ten VARCHAR(150),
    gioi_tinh VARCHAR(10),
    dia_chi TEXT,
    ngay_sinh DATE
);

INSERT INTO khach_hang (id_nguoi_dung, ho_ten, gioi_tinh, dia_chi, ngay_sinh)
VALUES
(3, 'Lê Văn C', 'Nam', '12 Trần Phú, Hà Nội', '1995-03-10'),
(4, 'Phạm Thị D', 'Nữ', '34 Nguyễn Trãi, Hà Nội', '1992-07-21'),
(5, 'Nguyễn Văn E', 'Nam', '56 Lý Thường Kiệt, TP.HCM', '1988-11-05'),

-- 4. Bảng môi giới
CREATE TABLE moi_gioi (
    id SERIAL PRIMARY KEY,
    id_nguoi_dung INT,
    ho_ten VARCHAR(150),
    gioi_tinh VARCHAR(10),
    cty VARCHAR(200),
    kinh_nghiem INT,
    mo_ta TEXT
);
			
INSERT INTO moi_gioi (id_nguoi_dung, ho_ten, gioi_tinh, cty, kinh_nghiem, mo_ta)
VALUES
(6, 'Nguyễn Văn G', 'Nam', 'Công ty BĐS A', 5, 'Chuyên môi giới căn hộ cao cấp tại TP.HCM.'),
(7, 'Trần Thị H', 'Nữ', 'Công ty BĐS B', 3, 'Chuyên môi giới nhà phố và biệt thự tại Hà Nội.'),
(8, 'Lê Văn I', 'Nam', 'Công ty BĐS C', 7, 'Có kinh nghiệm nhiều năm trong thị trường đất nền.'),

-- 5. Bảng bất động sản
CREATE TABLE bat_dong_san (
    id SERIAL PRIMARY KEY,
    tieu_de VARCHAR(200),
    mo_ta TEXT,
    gia NUMERIC(18,2),
    dien_tich NUMERIC(10,2),
    dia_chi TEXT,
    loai VARCHAR(100), -- bán, cho thuê, dự án
    khu_vuc VARCHAR(100),
	trang_thai VARCHAR(50),
    ngay_dang TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    id_moi_gioi INT
);

INSERT INTO bat_dong_san (tieu_de, mo_ta, gia, dien_tich, dia_chi, loai, khu_vuc, id_moi_gioi)
VALUES
('Căn hộ cao cấp Quận 1 view sông', 
 'Căn hộ 2 phòng ngủ, nội thất hiện đại, tiện ích hồ bơi, gym, an ninh 24/7.', 
 3500000000, 75.5, '123 Nguyễn Huệ, Quận 1, TP.HCM', 'Căn hộ', 'TP.HCM', 6),

('Nhà phố 3 tầng Bình Thạnh', 
 'Nhà phố 3 tầng, 4 phòng ngủ, sân vườn, gara ô tô, gần chợ và trường học.', 
 5500000000, 120, '45 Bùi Hữu Nghĩa, Bình Thạnh, TP.HCM', 'Nhà phố', 'TP.HCM', 6),

('Căn hộ dịch vụ Quận 7 full nội thất', 
 'Cho thuê căn hộ dịch vụ 55m2, đầy đủ nội thất, gần RMIT, tiện đi lại.', 
 12000000, 55, '678 Nguyễn Văn Linh, Quận 7, TP.HCM', 'Căn hộ', 'TP.HCM', 7),

('Chung cư mini Cầu Giấy giá rẻ', 
 'Chung cư mini 35m2, có thang máy, phù hợp sinh viên và gia đình nhỏ.', 
 850000000, 35, '12 Trần Thái Tông, Cầu Giấy, Hà Nội', 'Chung cư', 'Hà Nội', 8),

('Biệt thự nghỉ dưỡng ven hồ Tây', 
 'Biệt thự 200m2, hồ bơi riêng, thiết kế sang trọng, yên tĩnh, gần trung tâm.', 
 12000000000, 200, 'Hồ Tây, Tây Hồ, Hà Nội', 'Biệt thự', 'Hà Nội', 6),

('Đất nền dự án Ecopark', 
 'Đất nền 100m2, hạ tầng đồng bộ, gần công viên và trường học, thích hợp xây nhà.', 
 3000000000, 100, 'Ecopark, Văn Giang, Hưng Yên', 'Đất nền', 'Hưng Yên', 7),

('Nhà phố kinh doanh quận 3', 
 'Nhà 4 tầng, mặt tiền 6m, thích hợp mở cửa hàng hoặc văn phòng, trung tâm sầm uất.', 
 9500000000, 110, '89 Lý Chính Thắng, Quận 3, TP.HCM', 'Nhà phố', 'TP.HCM', 8);

-- 6. Bảng hình ảnh sản phẩm
CREATE TABLE hinh_anh (
    id SERIAL PRIMARY KEY,
    id_bds INT,
    url VARCHAR(300),
    mo_ta VARCHAR(200)
);

-- 7. Bảng video sản phẩm
CREATE TABLE video (
    id SERIAL PRIMARY KEY,
    id_bds INT,
    url VARCHAR(300),
    mo_ta VARCHAR(200)
);

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