-- CƠ SỞ DỮ LIỆU POSTGRESQL
-- Chủ đề: "Sàn giao dịch thương mại điện tử bất động sản"
-- Tổng: 
-- Phân công: Nguyễn Tuấn Anh = quản trị, Lê Ngọc Quỳnh = môi giới, Trương Quốc Đăng = khách hàng

-- ===========================
-- Extension cho UUID
-- ===========================
CREATE EXTENSION IF NOT EXISTS "uuid-ossp";

-- 0. Bảng nguoi_dung
CREATE TABLE IF NOT EXISTS nguoi_dung (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    ten_dang_nhap VARCHAR(100) NOT NULL UNIQUE,
    mat_khau VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
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

-- 1. Bảng quan_tri (profile quản trị)
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

-- 2. Bảng khach_hang (profile khách hàng)
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

-- 3. Bảng moi_gioi (profile môi giới)
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

-- 4. Bảng phien_dang_nhap (lưu lại phiên làm việc của người dùng)
CREATE TABLE IF NOT EXISTS phien_dang_nhap (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),              
    id_nguoi_dung UUID NOT NULL,           
    token_phien VARCHAR(255) NOT NULL UNIQUE,             
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
    CONSTRAINT chk_bat_dong_san_loai CHECK (loai IN ('ban','thue','duan','chuacapnhat')),
    CONSTRAINT chk_bat_dong_san_trang_thai CHECK (trang_thai IN ('choduyet','dangban','daban','dathue')),
    CONSTRAINT fk_bds_moi_gioi FOREIGN KEY (id_moi_gioi) REFERENCES moi_gioi(id) ON DELETE CASCADE
);

-- 8. Bảng hinh_anh (lưu hình ảnh sản phẩm bất động sản)
CREATE TABLE IF NOT EXISTS hinh_anh_bds (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    id_bds UUID NOT NULL,
    url VARCHAR(300),
    mo_ta VARCHAR(200),
    ngay_tao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_hinh_anh_bds_bat_dong_san FOREIGN KEY (id_bds) REFERENCES bat_dong_san(id) ON DELETE CASCADE
);

-- 9. Bảng video (video sản phẩm bất động sản)
CREATE TABLE IF NOT EXISTS video_bds (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    id_bds UUID NOT NULL,
    url VARCHAR(300),
    mo_ta VARCHAR(200),
    ngay_tao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_video_bds_bat_dong_san FOREIGN KEY (id_bds) REFERENCES bat_dong_san(id) ON DELETE CASCADE
);

-- 10. Bảng danh_gia_bds (khách hàng đánh giá các sản phẩm BĐS mà môi giới rao bán)
CREATE TABLE IF NOT EXISTS danh_gia_bds (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    id_khach_hang UUID,
    id_bds UUID NOT NULL,
    diem INT CHECK (diem >= 1 AND diem <= 5),
    binh_luan TEXT,
	trang_thai VARCHAR(10) DEFAULT 'hien',
    ngay_tao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
	CONSTRAINT chk_danh_gia_bds_trang_thai CHECK (trang_thai IN ('hien','an')),
    CONSTRAINT fk_danh_gia_bds_khach_hang FOREIGN KEY (id_khach_hang) REFERENCES khach_hang(id_nguoi_dung) ON DELETE SET NULL,
    CONSTRAINT fk_danh_gia_bds_bat_dong_san FOREIGN KEY (id_bds) REFERENCES bat_dong_san(id) ON DELETE CASCADE
);

-- 11. Bảng giao_dich (ghi nhận giao dịch mua/bán/thue)
CREATE TABLE IF NOT EXISTS giao_dich (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    id_khach_hang UUID,
    id_bds UUID,
    loai VARCHAR(50) NOT NULL, 
    ngay_giao_dich TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    trang_thai VARCHAR(50) DEFAULT 'dang_xu_ly', 
    CONSTRAINT chk_giao_dich_loai CHECK (loai IN ('mua','ban','thue')),
    CONSTRAINT fk_giao_dich_kh FOREIGN KEY (id_khach_hang) REFERENCES khach_hang(id_nguoi_dung) ON DELETE SET NULL,
    CONSTRAINT fk_giao_dich_bds FOREIGN KEY (id_bds) REFERENCES bat_dong_san(id) ON DELETE SET NULL
);

-- 12. Bảng thanh_toan (tổng thanh toán liên quan giao dịch)
CREATE TABLE IF NOT EXISTS thanh_toan (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    id_giao_dich UUID NOT NULL,
    tong_tien NUMERIC(18,2) CHECK (tong_tien >= 0),
    ngay_tt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    phuong_thuc VARCHAR(100), 
    trang_thai VARCHAR(50) DEFAULT 'mo',
    CONSTRAINT fk_tt_gd FOREIGN KEY (id_giao_dich) REFERENCES giao_dich(id) ON DELETE CASCADE
);

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

-- 14. Bảng thong_bao
CREATE TABLE IF NOT EXISTS thong_bao (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    id_nguoi_dung UUID NOT NULL,
    loai VARCHAR(50) NOT NULL,
    tieu_de VARCHAR(255) NOT NULL,
    noi_dung TEXT NOT NULL,
    thoi_gian_gui TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    trang_thai VARCHAR(20) DEFAULT 'chuaxem',
    CONSTRAINT fk_thong_bao_nguoi_dung FOREIGN KEY (id_nguoi_dung) REFERENCES nguoi_dung(id) ON DELETE CASCADE,
    CONSTRAINT chk_thong_bao_loai CHECK (loai IN (
        'capnhatthongtin',
        'doimatkhau',
        'khoataikhoan',
        'xoataikhoan'
    )),
    CONSTRAINT chk_thong_bao_trang_thai CHECK (trang_thai IN ('chuaxem','daxem'))
);

-- 15. Bảng danh_gia_mg (đánh giá môi giới)
CREATE TABLE IF NOT EXISTS danh_gia_mg (
    id SERIAL PRIMARY KEY,
    id_khach_hang UUID,
    id_moi_gioi UUID,
    diem INT CHECK (diem >= 1 AND diem <= 5),
    binh_luan TEXT,
    ngay_dg TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_danh_gia_mg_kh FOREIGN KEY (id_khach_hang) REFERENCES khach_hang(id_nguoi_dung) ON DELETE SET NULL,
    CONSTRAINT fk_danh_gia_mg_mg FOREIGN KEY (id_moi_gioi) REFERENCES moi_gioi(id_nguoi_dung) ON DELETE SET NULL
);

-- Sự kiện: 
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