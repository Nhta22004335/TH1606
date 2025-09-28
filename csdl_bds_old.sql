
-- ===========================
-- 20. Bảng tin_nhan (hệ thống tin nhắn giữa users)
-- Phụ trách: Đặng (kiểm thử), Tuấn Anh (hạ tầng)
-- ===========================
-- 8. Bảng hinh_anh (lưu hình ảnh sản phẩm bất động sản)
CREATE TABLE IF NOT EXISTS hinh_anh_bds (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    id_bds UUID NOT NULL,
    url VARCHAR(300),
    mo_ta VARCHAR(200),
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
-- 8. Bảng hinh_anh_danh_gia_bds (ảnh kèm đánh giá)
-- Phụ trách: Đặng
-- ===========================
CREATE TABLE IF NOT EXISTS hinh_anh_danh_gia_bds (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    id_dg_bds UUID NOT NULL,
    url VARCHAR(300),
    mo_ta VARCHAR(200),
    CONSTRAINT fk_hinh_dg FOREIGN KEY (id_dg_bds) REFERENCES danh_gia_bds(id) ON DELETE CASCADE
);

-- ===========================
-- 9. Bảng video_danh_gia_bds (video kèm đánh giá)
-- Phụ trách: Đặng
-- ===========================
CREATE TABLE IF NOT EXISTS video_danh_gia_bds (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    id_dg_bds UUID NOT NULL,
    url VARCHAR(300),
    mo_ta VARCHAR(200),
    CONSTRAINT fk_video_dg FOREIGN KEY (id_dg_bds) REFERENCES danh_gia_bds(id) ON DELETE CASCADE
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
-- 31. Bảng ho_tro (ticket hỗ trợ khách hàng)
CREATE TABLE ho_tro (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    nguoi_dung_id UUID NOT NULL,          -- người gửi yêu cầu hỗ trợ
    nhan_vien_id UUID,                    -- nhân viên hỗ trợ (nếu đã phân công)
    tieu_de VARCHAR(255) NOT NULL,
    noi_dung TEXT NOT NULL,
    muc_do_uu_tien VARCHAR(50) CHECK (muc_do_uu_tien IN ('thap', 'trung_binh', 'cao', 'khẩn cấp')) DEFAULT 'thap',
    trang_thai VARCHAR(50) CHECK (trang_thai IN ('cho_tiep_nhan', 'dang_xu_ly', 'da_giai_quyet', 'dong')) DEFAULT 'cho_tiep_nhan',
    thoi_gian_tao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    thoi_gian_cap_nhat TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_hotro_nguoidung FOREIGN KEY (nguoi_dung_id) REFERENCES nguoi_dung(id),
    CONSTRAINT fk_hotro_nhanvien FOREIGN KEY (nhan_vien_id) REFERENCES nguoi_dung(id)
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

