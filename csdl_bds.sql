-- CƠ SỞ DỮ LIỆU POSTGRESQL
-- Chủ đề: "Sàn giao dịch thương mại điện tử bất động sản"
-- Tổng: 
-- Phân công: Nguyễn Tuấn Anh = quản trị, Lê Ngọc Quỳnh = môi giới, Trương Quốc Đăng = khách hàng

-- ===========================
-- Extension cho UUID
-- =========================== tuấn anh 
CREATE EXTENSION IF NOT EXISTS "uuid-ossp";

-- . Bảng quyen (Bao gồm các quyền cụ thể của hệ thống)
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

-- 1. Bảng phan_quyen (Chứa các quyền tương ứng người dùng)
CREATE TABLE IF NOT EXISTS phan_quyen (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    id_nguoi_dung UUID NOT NULL,
    id_quyen UUID NOT NULL,
    CONSTRAINT fk_phan_quyen_nguoi_dung FOREIGN KEY (id_nguoi_dung) REFERENCES nguoi_dung(id) ON DELETE CASCADE,
    CONSTRAINT fk_phan_quyen_quyen FOREIGN KEY (id_quyen) REFERENCES quyen(id) ON DELETE CASCADE,
    UNIQUE (id_nguoi_dung, id_quyen) 
); 

-- 2. Bảng info_nguoi_dung (Thông tin cá nhân của toàn bộ người dùng)
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

-- 4. Bảng phien_dang_nhap (Lưu lại phiên làm việc của người dùng)
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

-- 5. Bảng yeu_cau_otp (Quản lý các mã OTP (One-Time Password) phục vụ cho xác thực người dùng trong hệ thống)
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

-- 6. Bảng lich_su_xac_thuc (Lưu lại vết đăng nhập, đăng ký hay đổi mật khẩu từ người dùng)
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

-- 7. Bảng bat_dong_san (Lưu thống tin các sản phẩm bất động sản)
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

-- 8. Bảng hin_anh_bds (Hình ảnh sản phẩm bất động sản)
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

-- 19. Bảng danh_gia_bds (khách hàng đánh giá các sản phẩm BĐS mà môi giới rao bán)
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

CREATE TABLE IF NOT EXISTS hinh_anh_danh_gia_bds (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    id_dg_bds UUID NOT NULL,
    url VARCHAR(300) NOT NULL,                     -- không cho phép null
    mo_ta VARCHAR(200) DEFAULT 'Chưa mô tả',       -- mặc định nếu không có mô tả
    kich_thuoc NUMERIC(10,2) DEFAULT 0,  -- thêm cột kích thước ảnh
    ngay_tao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,  -- thời gian tạo ảnh
    CONSTRAINT fk_hinh_dg FOREIGN KEY (id_dg_bds) REFERENCES danh_gia_bds(id) ON DELETE CASCADE
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

CREATE TABLE IF NOT EXISTS ke_hoach_thanh_toan (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    id_giao_dich UUID NOT NULL UNIQUE,
    tong_gia_tri NUMERIC(18,2) NOT NULL CHECK (tong_gia_tri >= 0), -- Tổng giá trị Hợp đồng/GD
    so_tien_da_tt NUMERIC(18,2) DEFAULT 0 CHECK (so_tien_da_tt >= 0), -- Tổng số tiền đã thanh toán (Tính toán)
    trang_thai_tt VARCHAR(50) DEFAULT 'chuathanhtoan', -- Trạng thái tổng quát của TT
    
    CONSTRAINT chk_khtt_trang_thai CHECK (trang_thai_tt IN ('chuathanhtoan', 'dangthanhtoan', 'hoantat')),
    CONSTRAINT fk_khtt_gd FOREIGN KEY ( id_giao_dich) REFERENCES giao_dich(id) ON DELETE CASCADE
);

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

select id_bds from giao_dich
select id from dot_thanh_toan

CREATE TABLE IF NOT EXISTS dot_thanh_toan_ct (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    id_dot_thanh_toan UUID NOT NULL,
    id_bds UUID,                                -- Mặc dù giao dịch đã có id_bds, nhưng để linh hoạt cho dự án lớn hơn.
    so_luong INT DEFAULT 1,
    so_tien NUMERIC(18,2) CHECK (so_tien >= 0),
    
    CONSTRAINT fk_dttct_dtt FOREIGN KEY (id_dot_thanh_toan) REFERENCES dot_thanh_toan(id) ON DELETE CASCADE,
    CONSTRAINT fk_dttct_bds FOREIGN KEY (id_bds) REFERENCES bat_dong_san(id) ON DELETE SET NULL
);
	

-- 14. Bảng thong_bao
-- CREATE TABLE IF NOT EXISTS thong_bao (
--     id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
--     id_nguoi_dung UUID NOT NULL,
--     loai VARCHAR(50) NOT NULL,
--     tieu_de VARCHAR(255) NOT NULL,
--     noi_dung TEXT NOT NULL,
--     thoi_gian_gui TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
--     trang_thai VARCHAR(20) DEFAULT 'chuaxem',
--     CONSTRAINT fk_thong_bao_nd FOREIGN KEY (id_nguoi_dung) REFERENCES nguoi_dung(id) ON DELETE CASCADE,
--     CONSTRAINT chk_thong_bao_loai CHECK (loai IN (
--         'capnhatthongtin',
--         'doimatkhau',
--         'khoataikhoan',
--         'xoataikhoan'
--     )),
--     CONSTRAINT chk_thong_bao_trang_thai CHECK (trang_thai IN ('chuaxem','daxem'))
-- );

-- 15. Bảng danh_gia_mg (đánh giá môi giới)
-- CREATE TABLE IF NOT EXISTS danh_gia_mg (
--     id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
--     id_khach_hang UUID NOT NULL,
--     id_moi_gioi UUID NOT NULL,
--     diem INT CHECK (diem >= 1 AND diem <= 5),
--     binh_luan TEXT,
--     ngay_dg TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
--     CONSTRAINT fk_danh_gia_kh_nd FOREIGN KEY (id_khach_hang) REFERENCES nguoi_dung(id) ON DELETE CASCADE,
--     CONSTRAINT fk_danh_gia_mg_nd FOREIGN KEY (id_moi_gioi) REFERENCES nguoi_dung(id) ON DELETE CASCADE,
--     CONSTRAINT chk_kh_mg_khacnhau CHECK (id_khach_hang <> id_moi_gioi)
-- );

CREATE TABLE bieu_mau (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),           
    tieu_de VARCHAR(255) NOT NULL,    
    loai VARCHAR(100) NOT NULL,        -- loại (ví dụ: hosomuaban, hosothue, bienban)
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

-- select nd.id from nguoi_dung nd
-- left join phan_quyen pq on pq.id_nguoi_dung = nd.id
-- left join quyen q on q.id=pq.id_quyen
-- where q.vai_tro='khachhang'

CREATE TABLE IF NOT EXISTS yeu_cau (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(), 
    
    id_nguoi_dung UUID NOT NULL,
    loai VARCHAR(100) NOT NULL,               
    id_bds UUID,                             
    trang_thai VARCHAR(50) DEFAULT 'choxuly',
	mo_ta_chi_tiet TEXT DEFAULT 'chuacapnhat',
    ngay_tao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_yeucau_nguoidung FOREIGN KEY (id_nguoi_dung) REFERENCES nguoi_dung(id) ON DELETE CASCADE,
    CONSTRAINT fk_yeucau_bds FOREIGN KEY (id_bds) REFERENCES bat_dong_san(id) ON DELETE SET NULL,

    CONSTRAINT chk_yeucau_trangthai CHECK (trang_thai IN ('choxuly', 'daduyet', 'dahuy')),
	CONSTRAINT chk_yeucau_loai CHECK (loai IN ('mua', 'ban', 'thue'))
);

-- Cần có extension uuid-ossp để sử dụng uuid_generate_v4()
-- CREATE EXTENSION IF NOT EXISTS "uuid-ossp";

CREATE TABLE IF NOT EXISTS lich_trinh (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    id_khach_hang UUID NOT NULL,
    id_moi_gioi UUID NOT NULL,
    thoi_gian_bat_dau TIMESTAMPTZ NOT NULL,
    thoi_gian_ket_thuc TIMESTAMPTZ NOT NULL,
    trang_thai VARCHAR(50) NOT NULL DEFAULT 'choxacnhan',
    ghi_chu TEXT DEFAULT 'chuacapnhat',
    CONSTRAINT fk_lichtrinh_khachhang FOREIGN KEY (id_khach_hang) REFERENCES nguoi_dung(id) ON DELETE CASCADE,
    CONSTRAINT fk_lichtrinh_moigioi FOREIGN KEY (id_moi_gioi) REFERENCES nguoi_dung(id) ON DELETE CASCADE,
    CONSTRAINT chk_thoigian_hople CHECK (thoi_gian_ket_thuc > thoi_gian_bat_dau),
    CONSTRAINT chk_lichtrinh_trangthai CHECK (trang_thai IN ('choxacnhan', 'daxacnhan', 'dahuy'))
);

select * from lich_trinh
-- 1. Bảng tin đăng
-- CREATE TABLE tin_tuc (
--     id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),       
--     id_khach_hang UUID NOT NULL,                                                   
--     tieu_de VARCHAR(200) NOT NULL DEFAULT 'chuacapnhat',
--     mo_ta TEXT DEFAULT 'chuacapnhat',
--     chuyen_muc VARCHAR(100) DEFAULT 'chuacapnhat',                  
--     trang_thai VARCHAR(50) DEFAULT 'choduyet',  
-- 	   anh_tin TEXT DEFAULT 'chuacapnhat.png',
--     luot_xem INT,
--     ngay_dang TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
--     CONSTRAINT chk_trang_thai_tin CHECK (trang_thai IN ('choduyet','dangban','daban','dathue')),
--     CONSTRAINT fk_tin_khachhang FOREIGN KEY (id_khach_hang) REFERENCES nguoi_dung(id) ON DELETE CASCADE
-- );

-- CREATE TABLE hop_thoai (
-- 	id UUID PRIMARY KEY DEFAULT uuid_generate_v4(), 
-- 	da_khoa INT DEFAULT 0,
-- 	da_xoa INT DEFAULT 0
-- )

-- CREATE TABLE tin_nhan (
--     id UUID PRIMARY KEY DEFAULT uuid_generate_v4(), 
-- 	id_hop_thoai UUID,
--     nguoi_gui UUID NOT NULL,                 -- Người gửi
--     nguoi_nhan UUID NOT NULL,                   -- Người nhận
--     noi_dung TEXT, -- Không cho phép rỗng
-- 	anh_tn TEXT,
-- 	video_tn TEXT,
--     tg_gui TIMESTAMP NOT NULL DEFAULT NOW(),
--     -- Ràng buộc khóa ngoại
--     CONSTRAINT fk_gui FOREIGN KEY (nguoi_gui) REFERENCES nguoi_dung(id) ON DELETE CASCADE,
--     CONSTRAINT fk_nhan  FOREIGN KEY (nguoi_nhan)   REFERENCES nguoi_dung(id) ON DELETE CASCADE,
-- 	CONSTRAINT fk_id_hop_thoai FOREIGN KEY (id_hop_thoai) REFERENCES hop_thoai(id) ON DELETE CASCADE,
--     -- Ràng buộc: người gửi và người nhận không được trùng
--     CONSTRAINT chk_gui_nhan CHECK (nguoi_gui <> nguoi_nhan)
);

-- CREATE TABLE IF NOT EXISTS khach_quan_tam_bds (
--     id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
--     id_khach_hang UUID NOT NULL,
--     id_bat_dong_san UUID NOT NULL,
--     ngay_tao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
--     trang_thai VARCHAR(20) DEFAULT 'active' CHECK (trang_thai IN ('active', 'huy')),
--     ghi_chu TEXT,

--     CONSTRAINT fk_kqt_kh FOREIGN KEY (id_khach_hang)
--         REFERENCES nguoi_dung(id) ON DELETE CASCADE,
--     CONSTRAINT fk_kqt_bds FOREIGN KEY (id_bat_dong_san)
--         REFERENCES bat_dong_san(id) ON DELETE CASCADE,

--     CONSTRAINT uq_kqt UNIQUE (id_khach_hang, id_bat_dong_san)
-- );


-- CREATE TABLE lich_su (
--     id UUID PRIMARY KEY DEFAULT gen_random_uuid(), -- mã lịch sử tự sinh
--     id_bat_dong_san UUID NOT NULL,                 -- ID bất động sản
--     id_nguoi_dung UUID,                            -- người thực hiện hành động (khách hoặc admin)
--     hanh_dong VARCHAR(50) NOT NULL,               -- ví dụ: 'quan_tam', 'duyet', 'mua', 'thanh_toan'
--     ghi_chu TEXT,                                  -- thông tin chi tiết nếu cần
--     ngay_tao TIMESTAMP NOT NULL DEFAULT now()     -- thời gian thực hiện
-- );

-- CREATE TABLE IF NOT EXISTS ghi_chu_khach_hang (
--     id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
--     id_nguoi_dung UUID NOT NULL, -- ID của khách hàng được ghi chú
--     id_bds UUID, -- Ghi chú này liên quan đến BĐS nào (có thể NULL nếu là ghi chú chung)
--     id_moi_gioi UUID NOT NULL, -- ID của môi giới tạo ghi chú
--     ghi_chu TEXT NOT NULL,
--     ngay_tao TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
--     CONSTRAINT fk_ghichu_khachhang FOREIGN KEY (id_nguoi_dung) REFERENCES nguoi_dung(id) ON DELETE CASCADE,
--     CONSTRAINT fk_ghichu_bds FOREIGN KEY (id_bds) REFERENCES bat_dong_san(id) ON DELETE SET NULL,
--     CONSTRAINT fk_ghichu_moigioi FOREIGN KEY (id_moi_gioi) REFERENCES nguoi_dung(id) ON DELETE CASCADE
-- );


