--
-- PostgreSQL database dump
--

-- Dumped from database version 17.5
-- Dumped by pg_dump version 17.5

-- Started on 2025-10-15 12:42:45

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET transaction_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

--
-- TOC entry 6 (class 2615 OID 17690)
-- Name: public; Type: SCHEMA; Schema: -; Owner: postgres
--

-- *not* creating schema, since initdb creates it


ALTER SCHEMA public OWNER TO postgres;

--
-- TOC entry 5176 (class 0 OID 0)
-- Dependencies: 6
-- Name: SCHEMA public; Type: COMMENT; Schema: -; Owner: postgres
--

COMMENT ON SCHEMA public IS '';


--
-- TOC entry 2 (class 3079 OID 17691)
-- Name: uuid-ossp; Type: EXTENSION; Schema: -; Owner: -
--

CREATE EXTENSION IF NOT EXISTS "uuid-ossp" WITH SCHEMA public;


--
-- TOC entry 5178 (class 0 OID 0)
-- Dependencies: 2
-- Name: EXTENSION "uuid-ossp"; Type: COMMENT; Schema: -; Owner: 
--

COMMENT ON EXTENSION "uuid-ossp" IS 'generate universally unique identifiers (UUIDs)';


--
-- TOC entry 256 (class 1255 OID 26507)
-- Name: tao_info_nguoi_dung_trigger_func(); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.tao_info_nguoi_dung_trigger_func() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
BEGIN
    -- Chèn một bản ghi mới vào bảng info_nguoi_dung,
    -- lấy id từ bản ghi vừa được thêm vào bảng nguoi_dung.
    -- Các cột khác trong info_nguoi_dung sẽ tự động nhận giá trị DEFAULT.
    INSERT INTO info_nguoi_dung (id_nguoi_dung)
    VALUES (NEW.id);

    -- Trả về bản ghi mới, đây là yêu cầu cho các hàm trigger AFTER.
    RETURN NEW;
END;
$$;


ALTER FUNCTION public.tao_info_nguoi_dung_trigger_func() OWNER TO postgres;

SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- TOC entry 245 (class 1259 OID 26658)
-- Name: bai_dang; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.bai_dang (
    id uuid DEFAULT public.uuid_generate_v4() NOT NULL,
    id_nguoi_dung uuid NOT NULL,
    id_bat_dong_san uuid NOT NULL,
    tieu_de character varying(200) NOT NULL,
    mo_ta text,
    gia numeric(18,2) NOT NULL,
    hinh_thuc character varying(50) NOT NULL,
    trang_thai character varying(50) DEFAULT 'chuaduyet'::character varying,
    ngay_dang timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    ngay_het_han timestamp without time zone,
    luot_xem integer,
    CONSTRAINT bai_dang_gia_check CHECK ((gia >= (0)::numeric)),
    CONSTRAINT chk_hinh_thuc_baidang CHECK (((hinh_thuc)::text = ANY ((ARRAY['ban'::character varying, 'chothue'::character varying])::text[]))),
    CONSTRAINT chk_trang_thai_baidang CHECK (((trang_thai)::text = ANY ((ARRAY['chuaduyet'::character varying, 'daduyet'::character varying, 'daban'::character varying, 'dathue'::character varying, 'an'::character varying, 'hethan'::character varying])::text[])))
);


ALTER TABLE public.bai_dang OWNER TO postgres;

--
-- TOC entry 241 (class 1259 OID 26538)
-- Name: bat_dong_san; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.bat_dong_san (
    id uuid DEFAULT public.uuid_generate_v4() NOT NULL,
    id_nguoi_dung uuid,
    tieu_de character varying(200) DEFAULT 'chuacapnhat'::character varying,
    mo_ta text DEFAULT 'chuacapnhat'::text,
    gia numeric(18,2) DEFAULT 0,
    dien_tich numeric(10,2),
    dia_chi text DEFAULT 'chuacapnhat'::text,
    loai character varying(100) DEFAULT 'chuacapnhat'::character varying,
    khu_vuc character varying(100) DEFAULT 'chuacapnhat'::character varying,
    trang_thai character varying(50) DEFAULT 'chuacapnhat'::character varying,
    hinh_thuc character varying(50) DEFAULT 'chuacapnhat'::character varying,
    ngay_dang timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT bat_dong_san_dien_tich_check CHECK ((dien_tich > (0)::numeric)),
    CONSTRAINT bat_dong_san_gia_check CHECK ((gia >= (0)::numeric)),
    CONSTRAINT chk_hinh_thuc_bds CHECK (((hinh_thuc)::text = ANY ((ARRAY['ban'::character varying, 'chothue'::character varying, 'chuacapnhat'::character varying])::text[]))),
    CONSTRAINT chk_loai_bds CHECK (((loai)::text = ANY ((ARRAY['canho'::character varying, 'nhapho'::character varying, 'datnen'::character varying, 'bietthu'::character varying, 'chuacapnhat'::character varying])::text[]))),
    CONSTRAINT chk_trang_thai_bds CHECK (((trang_thai)::text = ANY ((ARRAY['chuaduyet'::character varying, 'daduyet'::character varying, 'daban'::character varying, 'dathue'::character varying, 'chuacapnhat'::character varying])::text[])))
);


ALTER TABLE public.bat_dong_san OWNER TO postgres;

--
-- TOC entry 232 (class 1259 OID 18076)
-- Name: bieu_mau; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.bieu_mau (
    id uuid DEFAULT public.uuid_generate_v4() NOT NULL,
    tieu_de character varying(255) NOT NULL,
    loai character varying(100) NOT NULL,
    ben_mua uuid NOT NULL,
    ben_ban uuid NOT NULL,
    trang_thai character varying(50) DEFAULT 'choduyet'::character varying,
    tep_dk character varying(255),
    ngay_tao timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    ngay_cn timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT chk_trangthai CHECK (((trang_thai)::text = ANY ((ARRAY['choduyet'::character varying, 'daduyet'::character varying, 'daky'::character varying, 'huy'::character varying])::text[])))
);


ALTER TABLE public.bieu_mau OWNER TO postgres;

--
-- TOC entry 225 (class 1259 OID 17846)
-- Name: danh_gia_bds; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.danh_gia_bds (
    id uuid DEFAULT public.uuid_generate_v4() NOT NULL,
    id_nguoi_dung uuid,
    id_bds uuid NOT NULL,
    diem integer,
    binh_luan text,
    trang_thai character varying(10) DEFAULT 'hien'::character varying,
    ngay_tao timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT chk_danh_gia_bds_trang_thai CHECK (((trang_thai)::text = ANY ((ARRAY['hien'::character varying, 'an'::character varying])::text[]))),
    CONSTRAINT danh_gia_bds_diem_check CHECK (((diem >= 1) AND (diem <= 5)))
);


ALTER TABLE public.danh_gia_bds OWNER TO postgres;

--
-- TOC entry 231 (class 1259 OID 17982)
-- Name: danh_gia_mg; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.danh_gia_mg (
    id uuid DEFAULT public.uuid_generate_v4() NOT NULL,
    id_khach_hang uuid NOT NULL,
    id_moi_gioi uuid NOT NULL,
    diem integer,
    binh_luan text,
    ngay_dg timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT chk_kh_mg_khacnhau CHECK ((id_khach_hang <> id_moi_gioi)),
    CONSTRAINT danh_gia_mg_diem_check CHECK (((diem >= 1) AND (diem <= 5)))
);


ALTER TABLE public.danh_gia_mg OWNER TO postgres;

--
-- TOC entry 238 (class 1259 OID 26428)
-- Name: dot_thanh_toan; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.dot_thanh_toan (
    id uuid DEFAULT public.uuid_generate_v4() NOT NULL,
    id_giao_dich uuid NOT NULL,
    lan_tt integer NOT NULL,
    so_tien_tt numeric(18,2) NOT NULL,
    ngay_tt timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    phuong_thuc character varying(100),
    ghichu text,
    CONSTRAINT dot_thanh_toan_so_tien_tt_check CHECK ((so_tien_tt > (0)::numeric))
);


ALTER TABLE public.dot_thanh_toan OWNER TO postgres;

--
-- TOC entry 239 (class 1259 OID 26445)
-- Name: dot_thanh_toan_ct; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.dot_thanh_toan_ct (
    id uuid DEFAULT public.uuid_generate_v4() NOT NULL,
    id_dot_thanh_toan uuid NOT NULL,
    id_bds uuid,
    so_luong integer DEFAULT 1,
    so_tien numeric(18,2),
    CONSTRAINT dot_thanh_toan_ct_so_tien_check CHECK ((so_tien >= (0)::numeric))
);


ALTER TABLE public.dot_thanh_toan_ct OWNER TO postgres;

--
-- TOC entry 222 (class 1259 OID 17794)
-- Name: giao_dich; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.giao_dich (
    id uuid DEFAULT public.uuid_generate_v4() NOT NULL,
    id_nguoi_dung uuid,
    id_bds uuid,
    loai character varying(50) NOT NULL,
    ngay_giao_dich timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    trang_thai character varying(50) DEFAULT 'choxuly'::character varying,
    id_nguoi_ban uuid,
    CONSTRAINT chk_giao_dich_loai CHECK (((loai)::text = ANY ((ARRAY['mua'::character varying, 'ban'::character varying, 'thue'::character varying])::text[]))),
    CONSTRAINT chk_giao_dich_tt CHECK (((trang_thai)::text = ANY ((ARRAY['choxuly'::character varying, 'dangxuly'::character varying, 'hoantat'::character varying, 'dahuy'::character varying])::text[])))
);


ALTER TABLE public.giao_dich OWNER TO postgres;

--
-- TOC entry 242 (class 1259 OID 26566)
-- Name: hinh_anh_bds; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.hinh_anh_bds (
    id uuid DEFAULT public.uuid_generate_v4() NOT NULL,
    id_bds uuid NOT NULL,
    url character varying(300) NOT NULL,
    mo_ta character varying(200) DEFAULT 'chuacapnhat'::character varying,
    ngay_tao timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    kich_thuoc numeric(10,2) DEFAULT 0,
    trang_thai character varying(255) DEFAULT 'binhthuong'::character varying,
    loai character varying(10)
);


ALTER TABLE public.hinh_anh_bds OWNER TO postgres;

--
-- TOC entry 243 (class 1259 OID 26620)
-- Name: hinh_anh_danh_gia_bds; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.hinh_anh_danh_gia_bds (
    id uuid DEFAULT public.uuid_generate_v4() NOT NULL,
    id_dg_bds uuid NOT NULL,
    url character varying(300) NOT NULL,
    mo_ta character varying(200) DEFAULT 'Chưa mô tả'::character varying,
    kich_thuoc numeric(10,2) DEFAULT 0,
    ngay_tao timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.hinh_anh_danh_gia_bds OWNER TO postgres;

--
-- TOC entry 234 (class 1259 OID 26351)
-- Name: hop_thoai; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.hop_thoai (
    id uuid DEFAULT public.uuid_generate_v4() NOT NULL,
    da_khoa integer DEFAULT 0,
    da_xoa integer DEFAULT 0
);


ALTER TABLE public.hop_thoai OWNER TO postgres;

--
-- TOC entry 221 (class 1259 OID 17749)
-- Name: info_nguoi_dung; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.info_nguoi_dung (
    id uuid DEFAULT public.uuid_generate_v4() NOT NULL,
    id_nguoi_dung uuid NOT NULL,
    ho_ten character varying(150) DEFAULT 'chuacapnhat'::character varying,
    gioi_tinh character varying(20) DEFAULT 'chuacapnhat'::character varying,
    dia_chi text DEFAULT 'chuacapnhat'::text,
    ngay_sinh date DEFAULT (CURRENT_DATE - '18 years'::interval),
    mo_ta text DEFAULT 'chuacapnhat'::text,
    CONSTRAINT chk_quan_tri_gioi_tinh CHECK (((gioi_tinh)::text = ANY ((ARRAY['nam'::character varying, 'nu'::character varying, 'khac'::character varying, 'chuacapnhat'::character varying])::text[]))),
    CONSTRAINT chk_quan_tri_tuoi CHECK ((ngay_sinh <= (CURRENT_DATE - '18 years'::interval)))
);


ALTER TABLE public.info_nguoi_dung OWNER TO postgres;

--
-- TOC entry 237 (class 1259 OID 26410)
-- Name: ke_hoach_thanh_toan; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.ke_hoach_thanh_toan (
    id uuid DEFAULT public.uuid_generate_v4() NOT NULL,
    id_giao_dich uuid NOT NULL,
    tong_gia_tri numeric(18,2) NOT NULL,
    so_tien_da_tt numeric(18,2) DEFAULT 0,
    trang_thai_tt character varying(50) DEFAULT 'chuathanhtoan'::character varying,
    CONSTRAINT chk_khtt_trang_thai CHECK (((trang_thai_tt)::text = ANY ((ARRAY['chuathanhtoan'::character varying, 'dangthanhtoan'::character varying, 'hoantat'::character varying])::text[]))),
    CONSTRAINT ke_hoach_thanh_toan_so_tien_da_tt_check CHECK ((so_tien_da_tt >= (0)::numeric)),
    CONSTRAINT ke_hoach_thanh_toan_tong_gia_tri_check CHECK ((tong_gia_tri >= (0)::numeric))
);


ALTER TABLE public.ke_hoach_thanh_toan OWNER TO postgres;

--
-- TOC entry 240 (class 1259 OID 26494)
-- Name: lich_su; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.lich_su (
    id uuid DEFAULT gen_random_uuid() NOT NULL,
    id_bat_dong_san uuid NOT NULL,
    id_nguoi_dung uuid,
    hanh_dong character varying(50) NOT NULL,
    ghi_chu text,
    ngay_tao timestamp without time zone DEFAULT now() NOT NULL
);


ALTER TABLE public.lich_su OWNER TO postgres;

--
-- TOC entry 227 (class 1259 OID 17885)
-- Name: lich_su_xac_thuc; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.lich_su_xac_thuc (
    id uuid DEFAULT public.uuid_generate_v4() NOT NULL,
    id_nguoi_dung uuid NOT NULL,
    loai_su_kien character varying(30) NOT NULL,
    thoi_gian_bat_dau timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    thoi_gian_ket_thuc timestamp without time zone,
    dia_chi_ip character varying(45),
    user_agent text,
    ghi_chu text,
    CONSTRAINT chk_lich_su_xac_thuc_loai_su_kien CHECK (((loai_su_kien)::text = ANY ((ARRAY['dangnhap'::character varying, 'dangxuat'::character varying, 'doimatkhau'::character varying, 'quenmatkhau'::character varying])::text[]))),
    CONSTRAINT chk_lich_su_xac_thuc_time_range CHECK (((thoi_gian_ket_thuc IS NULL) OR (thoi_gian_ket_thuc >= thoi_gian_bat_dau)))
);


ALTER TABLE public.lich_su_xac_thuc OWNER TO postgres;

--
-- TOC entry 244 (class 1259 OID 26636)
-- Name: lich_trinh; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.lich_trinh (
    id uuid DEFAULT public.uuid_generate_v4() NOT NULL,
    id_khach_hang uuid NOT NULL,
    id_moi_gioi uuid NOT NULL,
    thoi_gian_bat_dau timestamp with time zone NOT NULL,
    thoi_gian_ket_thuc timestamp with time zone NOT NULL,
    trang_thai character varying(50) DEFAULT 'choxacnhan'::character varying NOT NULL,
    ghi_chu text DEFAULT 'chuacapnhat'::text,
    CONSTRAINT chk_lichtrinh_trangthai CHECK (((trang_thai)::text = ANY ((ARRAY['choxacnhan'::character varying, 'daxacnhan'::character varying, 'dahuy'::character varying])::text[]))),
    CONSTRAINT chk_thoigian_hople CHECK ((thoi_gian_ket_thuc > thoi_gian_bat_dau))
);


ALTER TABLE public.lich_trinh OWNER TO postgres;

--
-- TOC entry 219 (class 1259 OID 17710)
-- Name: nguoi_dung; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.nguoi_dung (
    id uuid DEFAULT public.uuid_generate_v4() NOT NULL,
    ten_dang_nhap character varying(100) NOT NULL,
    mat_khau character varying(255) NOT NULL,
    email character varying(255) NOT NULL,
    so_dt character varying(20) DEFAULT 'chuacapnhat'::character varying,
    avt text DEFAULT 'avt.png'::text,
    trang_thai character varying(50) DEFAULT 'chuakichhoat'::character varying,
    hoat_dong character varying(50) DEFAULT 'offline'::character varying,
    ngay_tao timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT chk_nguoi_dung_hoat_dong CHECK (((hoat_dong)::text = ANY ((ARRAY['online'::character varying, 'offline'::character varying])::text[]))),
    CONSTRAINT chk_nguoi_dung_so_dt CHECK ((((so_dt)::text ~ '^[0-9]{1,11}$'::text) OR ((so_dt)::text = 'chuacapnhat'::text))),
    CONSTRAINT chk_nguoi_dung_trang_thai CHECK (((trang_thai)::text = ANY ((ARRAY['danghoatdong'::character varying, 'chuakichhoat'::character varying, 'khoa'::character varying])::text[])))
);


ALTER TABLE public.nguoi_dung OWNER TO postgres;

--
-- TOC entry 220 (class 1259 OID 17731)
-- Name: phan_quyen; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.phan_quyen (
    id uuid DEFAULT public.uuid_generate_v4() NOT NULL,
    id_nguoi_dung uuid NOT NULL,
    id_quyen uuid NOT NULL
);


ALTER TABLE public.phan_quyen OWNER TO postgres;

--
-- TOC entry 226 (class 1259 OID 17868)
-- Name: phien_dang_nhap; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.phien_dang_nhap (
    id uuid DEFAULT public.uuid_generate_v4() NOT NULL,
    id_nguoi_dung uuid NOT NULL,
    bat_dau timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    het_han timestamp without time zone,
    dang_hoat_dong boolean DEFAULT true,
    selector character varying(255),
    verifier_hash character varying(255),
    CONSTRAINT chk_phien_dang_nhap_time_range CHECK (((het_han IS NULL) OR (het_han > bat_dau)))
);


ALTER TABLE public.phien_dang_nhap OWNER TO postgres;

--
-- TOC entry 218 (class 1259 OID 17702)
-- Name: quyen; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.quyen (
    id uuid DEFAULT public.uuid_generate_v4() NOT NULL,
    vai_tro character varying(50) NOT NULL
);


ALTER TABLE public.quyen OWNER TO postgres;

--
-- TOC entry 223 (class 1259 OID 17814)
-- Name: thanh_toan; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.thanh_toan (
    id uuid DEFAULT public.uuid_generate_v4() NOT NULL,
    id_giao_dich uuid NOT NULL,
    tong_tien numeric(18,2),
    ngay_tt timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    phuong_thuc character varying(100),
    trang_thai character varying(50) DEFAULT 'mo'::character varying,
    CONSTRAINT thanh_toan_tong_tien_check CHECK ((tong_tien >= (0)::numeric))
);


ALTER TABLE public.thanh_toan OWNER TO postgres;

--
-- TOC entry 224 (class 1259 OID 17828)
-- Name: thanh_toan_ct; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.thanh_toan_ct (
    id uuid DEFAULT public.uuid_generate_v4() NOT NULL,
    id_thanh_toan uuid NOT NULL,
    id_bds uuid,
    so_luong integer DEFAULT 1,
    so_tien numeric(18,2),
    CONSTRAINT thanh_toan_ct_so_tien_check CHECK ((so_tien >= (0)::numeric))
);


ALTER TABLE public.thanh_toan_ct OWNER TO postgres;

--
-- TOC entry 230 (class 1259 OID 17964)
-- Name: thong_bao; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.thong_bao (
    id uuid DEFAULT public.uuid_generate_v4() NOT NULL,
    id_nguoi_dung uuid NOT NULL,
    loai character varying(50) NOT NULL,
    tieu_de character varying(255) NOT NULL,
    noi_dung text NOT NULL,
    thoi_gian_gui timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    trang_thai character varying(20) DEFAULT 'chuaxem'::character varying,
    id_nguoi_gui uuid,
    CONSTRAINT chk_thong_bao_trang_thai CHECK (((trang_thai)::text = ANY ((ARRAY['chuaxem'::character varying, 'daxem'::character varying])::text[])))
);


ALTER TABLE public.thong_bao OWNER TO postgres;

--
-- TOC entry 235 (class 1259 OID 26360)
-- Name: tin_nhan; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.tin_nhan (
    id uuid DEFAULT public.uuid_generate_v4() NOT NULL,
    id_hop_thoai uuid,
    nguoi_gui uuid NOT NULL,
    nguoi_nhan uuid NOT NULL,
    noi_dung text,
    anh_tn text,
    video_tn text,
    tg_gui timestamp without time zone DEFAULT now() NOT NULL,
    da_thu_hoi integer DEFAULT 0,
    da_xoa integer DEFAULT 0,
    CONSTRAINT chk_gui_nhan CHECK ((nguoi_gui <> nguoi_nhan))
);


ALTER TABLE public.tin_nhan OWNER TO postgres;

--
-- TOC entry 236 (class 1259 OID 26387)
-- Name: tin_tuc; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.tin_tuc (
    id uuid DEFAULT public.uuid_generate_v4() NOT NULL,
    id_khach_hang uuid NOT NULL,
    tieu_de character varying(200) DEFAULT 'chuacapnhat'::character varying NOT NULL,
    mo_ta text DEFAULT 'chuacapnhat'::text,
    chuyen_muc character varying(100) DEFAULT 'chuacapnhat'::character varying,
    trang_thai character varying(50) DEFAULT 'choduyet'::character varying,
    anh_tin text DEFAULT 'chuacapnhat.png'::text,
    ngay_dang timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    luot_xem integer,
    CONSTRAINT chk_trang_thai_tin CHECK (((trang_thai)::text = ANY ((ARRAY['choduyet'::character varying, 'dangban'::character varying, 'daban'::character varying, 'dathue'::character varying])::text[])))
);


ALTER TABLE public.tin_tuc OWNER TO postgres;

--
-- TOC entry 229 (class 1259 OID 17951)
-- Name: video_danh_gia_bds; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.video_danh_gia_bds (
    id uuid DEFAULT public.uuid_generate_v4() NOT NULL,
    id_dg_bds uuid NOT NULL,
    url character varying(300),
    mo_ta character varying(200)
);


ALTER TABLE public.video_danh_gia_bds OWNER TO postgres;

--
-- TOC entry 233 (class 1259 OID 18098)
-- Name: yeu_cau; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.yeu_cau (
    id uuid DEFAULT public.uuid_generate_v4() NOT NULL,
    id_nguoi_dung uuid NOT NULL,
    loai character varying(100) NOT NULL,
    id_bds uuid,
    trang_thai character varying(50) DEFAULT 'choxuly'::character varying,
    ngay_tao timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    mo_ta_chi_tiet text DEFAULT 'chuacapnhat'::text,
    CONSTRAINT chk_yeucau_loai CHECK (((loai)::text = ANY ((ARRAY['mua'::character varying, 'ban'::character varying, 'thue'::character varying])::text[]))),
    CONSTRAINT chk_yeucau_trangthai CHECK (((trang_thai)::text = ANY ((ARRAY['choxuly'::character varying, 'daduyet'::character varying, 'dahuy'::character varying])::text[])))
);


ALTER TABLE public.yeu_cau OWNER TO postgres;

--
-- TOC entry 228 (class 1259 OID 17901)
-- Name: yeu_cau_otp; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.yeu_cau_otp (
    id uuid DEFAULT public.uuid_generate_v4() NOT NULL,
    so_dt character varying(20),
    email character varying(255),
    trang_thai character varying(20) DEFAULT 'choxacnhan'::character varying,
    bat_dau timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    het_han timestamp without time zone NOT NULL,
    cap_nhat timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    token_code character varying(255),
    user_data_json jsonb,
    otp_hash character varying(255),
    CONSTRAINT chk_yeu_cau_otp_contact_only CHECK ((((so_dt IS NOT NULL) AND (email IS NULL)) OR ((so_dt IS NULL) AND (email IS NOT NULL)))),
    CONSTRAINT chk_yeu_cau_otp_time_range CHECK ((het_han > bat_dau)),
    CONSTRAINT chk_yeu_cau_otp_trang_thai CHECK (((trang_thai)::text = ANY ((ARRAY['choxacnhan'::character varying, 'daxacnhan'::character varying, 'dahuy'::character varying])::text[])))
);


ALTER TABLE public.yeu_cau_otp OWNER TO postgres;

--
-- TOC entry 5170 (class 0 OID 26658)
-- Dependencies: 245
-- Data for Name: bai_dang; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.bai_dang (id, id_nguoi_dung, id_bat_dong_san, tieu_de, mo_ta, gia, hinh_thuc, trang_thai, ngay_dang, ngay_het_han, luot_xem) FROM stdin;
ac3a943a-8050-47b3-8a9a-d4e57c1e79d5	0216dbde-2061-4841-ad42-0445ffd0692d	d558834d-66c1-4eb5-aa02-cc35b2b46d76	Bán căn hộ Metropole Thủ Thiêm 2PN, view sông, giá tốt	Cần bán gấp căn hộ cao cấp Metropole, diện tích 75.5m2, bàn giao nội thất cơ bản. Vị trí đắc địa ngay trung tâm tài chính mới.	15000000000.00	ban	daduyet	2025-10-10 09:00:00	2025-12-10 09:00:00	20
b34c0a05-7209-43d4-9431-8bbd7b8095d9	144ee4c9-bf24-438f-8a6b-e15dd0e71705	d61ca29b-11ea-4733-bbd7-a2fdd2bddb9f	Cho thuê nhà mặt tiền Pasteur, Quận 3, kinh doanh sầm uất	Cho thuê dài hạn nhà mặt tiền đường Pasteur, diện tích 90m2, kết cấu 1 trệt 3 lầu. Thích hợp làm văn phòng, showroom, spa.	95000000.00	chothue	daduyet	2025-10-11 11:30:00	\N	20
ce8418eb-6a79-4951-b8ad-0514fd90b141	1cabfcda-923b-400f-b05d-9b900516380c	5b0981ed-37ce-4729-8bb7-2da081ee2b36	Bán đất nền Cityland Park Hills Gò Vấp, sổ hồng riêng	Lô đất 120m2, vị trí đẹp, đường nội bộ 12m. Khu dân cư cao cấp, đầy đủ tiện ích. Xây dựng tự do.	13000000000.00	ban	daban	2025-08-01 14:00:00	2025-09-01 14:00:00	20
cf916bf9-e3c7-4cca-b5b0-0a9a97845a64	29e897be-0b07-4051-bf51-04ee0286f394	aa316098-b32d-4e11-b2aa-e3dfbe56e800	Cho thuê biệt thự Thảo Điền có hồ bơi, sân vườn rộng	Biệt thự 350m2, 4 phòng ngủ, full nội thất cao cấp. Khu compound an ninh, yên tĩnh, cộng đồng văn minh. Ưu tiên khách thuê dài hạn.	120000000.00	chothue	daduyet	2025-09-25 10:20:00	2025-11-25 10:20:00	20
2fdbfe49-cb57-4e3a-86e5-794cf3ed9809	3551fa73-fadc-4ef2-a1a3-6929a8e65c11	4cf78123-afc6-461a-a49e-87ace9b3b508	Cho thuê nhà nguyên căn Phan Xích Long, Phú Nhuận	Nhà trong hẻm xe hơi, diện tích 85.5m2, 1 trệt 2 lầu. Khu vực ăn uống sầm uất, thuận tiện di chuyển.	35000000.00	chothue	dathue	2025-09-10 18:00:00	2025-10-10 18:00:00	20
843a81f8-759c-427b-b7be-268328c1e1d3	59c9d360-7c64-45cf-b229-4e574b8c7ceb	d47acb24-261b-47b6-8110-bbb6b59fa7d0	Bán đất Vạn Phúc City, mặt tiền sông Sài Gòn	Lô đất 100m2, vị trí đắc địa tại khu đô thị Vạn Phúc. Gần trường học, bệnh viện, trung tâm thương mại. Sổ đỏ chính chủ.	18000000000.00	ban	daduyet	2025-10-01 08:45:00	\N	20
37c745fa-355d-4313-bbb1-6f9143c9ee08	78ed62c4-70ed-42c1-898b-6e400c6def37	5799c0c2-0bee-43e0-a580-74c5fddad75c	Cho thuê căn hộ Midtown Phú Mỹ Hưng, 3PN, view công viên	Căn hộ 110m2, thiết kế sang trọng, nội thất đầy đủ. Tận hưởng tiện ích cao cấp: hồ bơi, gym, công viên hoa anh đào.	45000000.00	chothue	daduyet	2025-10-05 16:00:00	2025-12-05 16:00:00	20
a5dad341-0988-4cef-9e9c-2018798c4df8	7efdc816-e04f-4493-9997-6b0766bae0db	34111548-a16c-4ce0-8827-532d3875a764	Bán nhà mặt tiền Bùi Viện, Quận 1, đang cho thuê giá cao	Cần bán nhà mặt tiền khu phố Tây Bùi Viện, 70m2, đang có hợp đồng thuê 150tr/tháng. Sản phẩm đầu tư sinh lời ngay.	65000000000.00	ban	daduyet	2025-09-28 13:10:00	\N	20
3aedb591-4bb7-4620-9987-ac35f35c9650	90e4f116-b60a-4796-8f0d-26289f7dae50	1014202c-2ab9-492b-81ff-338cbb2f0e8e	Cho thuê đất nền dự án Đông Tăng Long, Thủ Đức	Cho thuê đất trống 95m2, thời hạn 5 năm. Thích hợp làm kho bãi, quán ăn sân vườn. Giá thuê rẻ.	8000000.00	chothue	daduyet	2025-10-12 11:00:00	\N	20
80b90075-961e-437a-bffd-f8ba5f995008	9e4364ac-289e-4f4a-b18f-57bb5d05a336	f141e5f0-7fff-4713-960b-da42f16f8465	Bán căn hộ studio Vinhomes Grand Park, full nội thất	Căn hộ 55m2, thiết kế thông minh, nội thất hiện đại. Thích hợp cho người độc thân hoặc đầu tư cho thuê.	1850000000.00	ban	daduyet	2025-09-15 09:30:00	2025-11-15 09:30:00	20
e237cd7d-bfcc-4709-a7e7-9c3a168daf86	b11d78c1-73a5-4d18-8002-c505ef2e9986	08dc8dd4-1201-431c-b2f0-449695b97b1f	Cho thuê nhà hẻm Nguyễn Trãi, Quận 5	Nhà 65m2, 1 trệt 1 lầu, 2 phòng ngủ. Hẻm thông, an ninh, gần chợ và trường học. Ưu tiên gia đình.	12000000.00	chothue	an	2025-07-20 17:00:00	2025-08-20 17:00:00	20
1905eed6-81e9-474d-b480-763d96a13a3b	d20c59af-cbf7-47b5-aec4-90b6b119a816	fabe2a78-1231-4543-827e-acfa16f6df1f	Bán gấp căn hộ Masteri Thảo Điền 2PN, view hồ bơi	Cần tiền bán gấp căn hộ 82m2, tầng trung, view nội khu yên tĩnh. Tặng lại toàn bộ nội thất cho khách thiện chí.	6100000000.00	ban	daduyet	2025-10-09 20:00:00	\N	20
792a4d40-2c48-44da-bf90-d4aed747ffa6	db5ac513-6077-4717-832c-ae37eda2c1d1	c7b5c0d3-7779-4299-a40b-4f104d4845ef	Bán đất thổ cư Bình Chánh, gần chợ, sổ riêng	Đất 150m2, đã lên thổ cư 100%, xây dựng tự do. Đường nhựa 6m, xe hơi vào tận nơi. Khu dân cư hiện hữu.	3200000000.00	ban	chuaduyet	2025-10-13 21:00:00	2025-12-13 21:00:00	20
3755725c-ec90-4d08-aee1-a4fc1f33a693	eb111e8c-1671-40a1-a970-64e683945c90	fdfe9f43-a19a-42b9-97bc-a8e9fed6b24b	Cho thuê nhà mặt tiền Lý Thường Kiệt, Quận 10	Nhà 120m2, 1 trệt 4 lầu, có thang máy. Vị trí đẹp, phù hợp mở trung tâm anh ngữ, phòng khám, ngân hàng.	150000000.00	chothue	daduyet	2025-09-02 14:25:00	\N	20
2e4416c8-714b-4107-8730-3b53541a07d9	f5f4022f-eccc-48e6-a435-1a9b1cdb3f3a	fc080893-c646-4521-a9b7-c5541f166a58	Bán căn hộ Celadon City Tân Phú, 3PN, đối diện Aeon Mall	Căn hộ 98m2, không gian xanh mát, tiện ích đẳng cấp. Sổ hồng trao tay, hỗ trợ vay ngân hàng.	5300000000.00	ban	daban	2025-06-15 11:50:00	2025-07-15 11:50:00	20
e417afc7-3179-44a6-8182-26aa50538ea3	f9857b4a-d49f-45c5-850e-d5b6d0d3eb55	d5793b54-78d7-4033-8647-0f3ccccda434	Cho thuê biệt thự Lakeview City, view hồ cảnh quan	Biệt thự song lập 280m2, thiết kế hiện đại, có sân vườn. Khu đô thị đáng sống với hồ cảnh quan 3.6ha.	60000000.00	chothue	daduyet	2025-09-29 19:00:00	2025-11-29 19:00:00	20
8fc38f40-e08b-4f8e-a57d-d0d379cb0f38	fe1dcc48-4a84-45a7-8c38-610f6686f0f7	e92e8e4e-5ea2-4039-9d62-5a0e58e27d8f	Bán nhà hẻm Huỳnh Tấn Phát, Quận 7, giá rẻ	Nhà 1 trệt 1 lầu, 50m2, 2 phòng ngủ. Sổ hồng chung, công chứng vi bằng. Giá rẻ cho vợ chồng trẻ.	1950000000.00	ban	hethan	2025-08-10 22:00:00	2025-09-10 22:00:00	20
9806835c-0203-4144-baf1-faeeb42ac0d3	3ebc4930-2923-4085-b9ca-192f087ba6bf	63ce5217-1344-4228-9455-6329b83585f3	Bán đất gần khu công nghệ cao, đường Nguyễn Xiển	Lô đất 80m2, sổ hồng riêng. Gần Vinhomes Grand Park và Khu công nghệ cao, tiềm năng tăng giá lớn.	4500000000.00	ban	daduyet	2025-10-06 09:00:00	2025-12-06 09:00:00	20
c1adca6e-3fc9-49e6-b0b7-267e08ee55d8	0023b190-c734-45f6-8425-a1949e08e8d5	3049148d-1d50-45b0-a5d5-4012798dbc16	Cho thuê căn hộ City Garden Bình Thạnh, 2PN, nội thất Châu Âu	Căn hộ 72m2, thiết kế độc đáo, ban công rộng. Tiện ích nghỉ dưỡng 5 sao. Cho thuê dài hạn.	30000000.00	chothue	daduyet	2025-10-07 10:40:00	\N	20
001b3bb7-6f3e-4b3b-bb52-487476b6ff5d	0216dbde-2061-4841-ad42-0445ffd0692d	a14b3837-0b85-4403-bcb2-db93996fc185	Bán nhà Quận 4 gần cầu Calmette, tiện di chuyển	Nhà cấp 4, diện tích 35m2, sổ hồng riêng. Vị trí trung tâm, cách Quận 1 chỉ một cây cầu. Tiện xây mới hoặc sửa chữa cho thuê.	4100000000.00	ban	daduyet	2025-10-14 11:20:00	\N	20
b27e81d2-cb40-45cd-ac4f-7d5ea8356299	144ee4c9-bf24-438f-8a6b-e15dd0e71705	11dd94ce-d64b-4bf2-bde5-e484fbfd5e5d	Cho thuê nhà Gò Vấp nguyên căn gần sân bay	Nhà 1 trệt 1 lầu, 2PN. Khu dân trí cao, yên tĩnh. Phù hợp cho gia đình nhỏ, nhân viên văn phòng.	10000000.00	chothue	dathue	2025-09-05 08:00:00	2025-10-05 08:00:00	20
fa82e597-9bae-4235-a7b4-5c3729178663	29e897be-0b07-4051-bf51-04ee0286f394	d87c88bf-3bb0-44f4-ad78-a35bf01a2410	Bán đất ven biển Long Hải, Vũng Tàu, sổ đỏ thổ cư	Lô đất nghỉ dưỡng 150m2, cách biển 500m. Hạ tầng hoàn thiện, xây dựng ngay. Thích hợp làm second home.	2600000000.00	ban	daduyet	2025-10-11 17:00:00	\N	20
03775aa8-2798-4cdd-aae8-438819578966	886a1f9f-d5b6-4964-ae73-e1eb5a69139e	8109b350-cfef-45a4-b7a7-7c4b0d413bb1	Bán biệt thự Chateau Phú Mỹ Hưng, đẳng cấp hoàng gia	Biệt thự 420m2, kiến trúc Pháp cổ điển, nội thất xa hoa. Nằm trong khu compound an ninh nhất Phú Mỹ Hưng.	120000000000.00	ban	an	2025-10-15 10:00:00	\N	20
0481aadb-9e87-4148-bc89-45b4542e0c35	30f7a140-9e0f-4763-8c73-d0ba585e0584	10e62f11-9535-44f7-90c5-c62066ca55c1	Bán căn hộ The Sun Avenue, 2PN, view Mai Chí Thọ	Chính chủ bán căn hộ 68m2, đã có sổ hồng. Để lại toàn bộ nội thất, chỉ cần xách vali vào ở. Giá thương lượng.	4200000000.00	ban	an	2025-10-14 15:00:00	\N	20
9362ec71-51bb-4ccf-8ce6-f4d41c2d69ce	3551fa73-fadc-4ef2-a1a3-6929a8e65c11	90ec7744-8ac0-4e2c-a4a3-9b95776d46bc	Bán chung cư cũ Tôn Thất Thuyết, Quận 4, nhận nhà ngay	Căn hộ lầu 3, 50m2, đã sửa đẹp. Giấy tờ hợp lệ, có thể vay ngân hàng. Khu vực trung tâm, tiện ích đủ đầy.	2400000000.00	ban	daban	2025-07-29 10:45:00	2025-08-29 10:45:00	20
13c20644-fdd3-4c38-815b-a68f6a4e514c	59c9d360-7c64-45cf-b229-4e574b8c7ceb	e44bf5c0-ad3a-4846-af51-81c146eec74e	Cho thuê kho xưởng KCN Tân Tạo, Bình Tân, 500m2	Kho xưởng cao ráo, nền bê tông chịu lực, xe container ra vào 24/24. Điện 3 pha, PCCC tiêu chuẩn.	42000000.00	chothue	daduyet	2025-09-23 09:30:00	\N	20
\.


--
-- TOC entry 5166 (class 0 OID 26538)
-- Dependencies: 241
-- Data for Name: bat_dong_san; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.bat_dong_san (id, id_nguoi_dung, tieu_de, mo_ta, gia, dien_tich, dia_chi, loai, khu_vuc, trang_thai, hinh_thuc, ngay_dang) FROM stdin;
d558834d-66c1-4eb5-aa02-cc35b2b46d76	0216dbde-2061-4841-ad42-0445ffd0692d	Căn hộ cao cấp view sông Quận 7	Căn hộ 2 phòng ngủ, nội thất hiện đại, ban công rộng hướng sông. Nằm trong khu dân cư an ninh, tiện ích nội khu đầy đủ gồm hồ bơi, phòng gym và siêu thị mini. Gần trung tâm thương mại Crescent Mall, thuận tiện di chuyển về quận 1 và quận 4.	3500000000.00	76.50	Nguyễn Văn Linh, Quận 7, TP.HCM	canho	TP.HCM	daduyet	ban	2025-10-12 21:55:12.6814
d61ca29b-11ea-4733-bbd7-a2fdd2bddb9f	0216dbde-2061-4841-ad42-0445ffd0692d	Đất nền sổ hồng riêng tại Bình Chánh	Lô đất thổ cư 100%, diện tích 100m², mặt tiền đường 12m, khu dân cư đông đúc. Gần chợ Bình Chánh và trường học. Sổ hồng riêng, sang tên trong ngày. Phù hợp xây nhà ở hoặc đầu tư lâu dài.	1200000000.00	100.00	Đinh Đức Thiện, Bình Chánh, TP.HCM	datnen	TP.HCM	chuaduyet	ban	2025-10-12 21:55:12.6814
5b0981ed-37ce-4729-8bb7-2da081ee2b36	0216dbde-2061-4841-ad42-0445ffd0692d	Căn hộ cho thuê tại Quận 4	Căn hộ studio diện tích 40m², đầy đủ nội thất cao cấp, tầng cao thoáng mát, có ban công nhìn ra Bitexco. Khu vực an ninh 24/7, gần chợ, siêu thị và bến Bạch Đằng. Thích hợp cho người đi làm hoặc cặp đôi trẻ.	15000000.00	40.00	Đường Hoàng Diệu, Quận 4, TP.HCM	canho	TP.HCM	daduyet	chothue	2025-10-12 21:55:12.6814
aa316098-b32d-4e11-b2aa-e3dfbe56e800	0216dbde-2061-4841-ad42-0445ffd0692d	Nhà phố mặt tiền Nguyễn Trãi	Nhà 3 tầng, mặt tiền 6m, khu kinh doanh sầm uất. Vị trí vàng gần ngã sáu Phù Đổng, thuận tiện mở shop, quán cà phê hoặc văn phòng công ty. Nhà còn mới, dọn vào ở hoặc kinh doanh ngay.	15500000000.00	120.00	Nguyễn Trãi, Quận 1, TP.HCM	nhapho	TP.HCM	daduyet	ban	2025-10-12 21:55:12.6814
10e62f11-9535-44f7-90c5-c62066ca55c1	144ee4c9-bf24-438f-8a6b-e15dd0e71705	Biệt thự nghỉ dưỡng tại Đà Lạt view hồ Tuyền Lâm	Biệt thự 2 tầng, diện tích sử dụng 400m², khuôn viên 800m², có sân vườn, hồ cá và phòng xông hơi. Thiết kế hiện đại pha lẫn phong cách cổ điển châu Âu, không gian yên tĩnh và trong lành. Phù hợp nghỉ dưỡng hoặc homestay cao cấp.	12500000000.00	400.00	Hồ Tuyền Lâm, Đà Lạt, Lâm Đồng	bietthu	Lâm Đồng	daduyet	ban	2025-10-12 21:55:12.6814
4cf78123-afc6-461a-a49e-87ace9b3b508	144ee4c9-bf24-438f-8a6b-e15dd0e71705	Nhà phố cho thuê trung tâm Nha Trang	Nhà 2 tầng, mặt tiền 8m, gần biển, nằm trong khu phố du lịch. Diện tích 180m², thích hợp mở cửa hàng, spa hoặc văn phòng. Hợp đồng thuê dài hạn, giá thuê tốt so với khu vực trung tâm.	30000000.00	180.00	Trần Phú, Nha Trang, Khánh Hòa	nhapho	Khánh Hòa	chuaduyet	chothue	2025-10-12 21:55:12.6814
d47acb24-261b-47b6-8110-bbb6b59fa7d0	144ee4c9-bf24-438f-8a6b-e15dd0e71705	Căn hộ view biển Mỹ Khê, Đà Nẵng	Căn hộ 1 phòng ngủ, 1 phòng khách, ban công hướng biển Mỹ Khê. Cách bãi biển 200m, nội thất nhập khẩu, tòa nhà có hồ bơi, gym và nhà hàng. Thích hợp cho thuê du lịch hoặc nghỉ dưỡng cá nhân.	3100000000.00	60.00	Võ Nguyên Giáp, Sơn Trà, Đà Nẵng	canho	Đà Nẵng	daduyet	ban	2025-10-12 21:55:12.6814
5799c0c2-0bee-43e0-a580-74c5fddad75c	144ee4c9-bf24-438f-8a6b-e15dd0e71705	Đất nền khu đô thị Nam Cần Thơ	Khu đất 120m², vị trí gần đại học Cần Thơ, khu dân cư đông đúc. Đường 10m, điện âm nước máy, pháp lý rõ ràng. Thích hợp đầu tư xây nhà trọ hoặc kinh doanh nhỏ.	980000000.00	120.00	Nam Cần Thơ, Cần Thơ	datnen	Cần Thơ	chuaduyet	ban	2025-10-12 21:55:12.6814
34111548-a16c-4ce0-8827-532d3875a764	1cabfcda-923b-400f-b05d-9b900516380c	Căn hộ cao cấp Empire City Thủ Thiêm	Căn hộ 3 phòng ngủ, 2 phòng tắm, diện tích 120m². Thiết kế sang trọng, view sông Sài Gòn và trung tâm thành phố. Khu dân cư cao cấp, có hồ bơi, phòng gym, công viên ven sông. Phù hợp gia đình trẻ hoặc người nước ngoài.	11500000000.00	120.00	Thủ Thiêm, Quận 2, TP.HCM	canho	TP.HCM	daduyet	ban	2025-10-12 21:55:12.6814
8109b350-cfef-45a4-b7a7-7c4b0d413bb1	1cabfcda-923b-400f-b05d-9b900516380c	Nhà phố 1 trệt 2 lầu tại Thủ Đức	Nhà mới xây, thiết kế hiện đại, diện tích 85m², nằm trong khu dân cư an ninh, đường ô tô vào tận nơi. Gần Vincom Thủ Đức và Đại học Quốc gia. Phù hợp cho hộ gia đình hoặc đầu tư cho thuê.	5200000000.00	85.00	Linh Trung, Thủ Đức, TP.HCM	nhapho	TP.HCM	daduyet	ban	2025-10-12 21:55:12.6814
1014202c-2ab9-492b-81ff-338cbb2f0e8e	1cabfcda-923b-400f-b05d-9b900516380c	Phòng trọ mini full nội thất Bình Thạnh	Phòng 25m², có máy lạnh, máy giặt, khu nấu ăn riêng. Nhà mới xây, giờ giấc tự do, an ninh 24/7. Gần Hutech, UEF, Pearl Plaza. Thích hợp cho sinh viên hoặc người đi làm.	6000000.00	25.00	Điện Biên Phủ, Bình Thạnh, TP.HCM	canho	TP.HCM	daduyet	chothue	2025-10-12 21:55:12.6814
f141e5f0-7fff-4713-960b-da42f16f8465	1cabfcda-923b-400f-b05d-9b900516380c	Đất vườn 500m² tại Long Thành	Đất trồng cây lâu năm, cách cao tốc TP.HCM – Long Thành 1km, khu dân cư đang phát triển mạnh. Thích hợp làm nhà vườn, nghỉ dưỡng cuối tuần hoặc đầu tư dài hạn.	2500000000.00	500.00	Long Thành, Đồng Nai	datnen	Đồng Nai	chuaduyet	ban	2025-10-12 21:55:12.6814
08dc8dd4-1201-431c-b2f0-449695b97b1f	3551fa73-fadc-4ef2-a1a3-6929a8e65c11	Nhà phố cho thuê gần Aeon Bình Tân	Nhà 3 tầng, diện tích 150m², gồm 4 phòng ngủ, 1 phòng khách rộng. Đầy đủ nội thất, gần trung tâm thương mại Aeon Mall, trường học và bệnh viện. Rất thích hợp cho gia đình thuê ở lâu dài.	25000000.00	150.00	Tân Kỳ Tân Quý, Bình Tân, TP.HCM	nhapho	TP.HCM	daduyet	chothue	2025-10-12 21:55:12.6814
fabe2a78-1231-4543-827e-acfa16f6df1f	3551fa73-fadc-4ef2-a1a3-6929a8e65c11	Căn hộ EcoGreen Nguyễn Văn Linh	Căn hộ 2 phòng ngủ, 70m², nội thất hoàn thiện, ban công rộng. Khu dân cư có hồ bơi, công viên và khu vui chơi trẻ em. Giao thông thuận tiện đi quận 1, quận 5. Phù hợp hộ gia đình trẻ.	4100000000.00	70.00	Nguyễn Văn Linh, Quận 7, TP.HCM	canho	TP.HCM	daduyet	ban	2025-10-12 21:55:12.6814
c7b5c0d3-7779-4299-a40b-4f104d4845ef	3551fa73-fadc-4ef2-a1a3-6929a8e65c11	Đất nền khu dân cư Bà Rịa	Lô đất 150m², đường trước nhà 8m, dân cư đông đúc, gần trường học và trung tâm hành chính. Pháp lý rõ ràng, sang tên ngay. Giá tốt cho nhà đầu tư.	950000000.00	150.00	Phước Hưng, Bà Rịa	datnen	Bà Rịa - Vũng Tàu	chuaduyet	ban	2025-10-12 21:55:12.6814
fdfe9f43-a19a-42b9-97bc-a8e9fed6b24b	3551fa73-fadc-4ef2-a1a3-6929a8e65c11	Nhà cho thuê quận Gò Vấp	Nhà nguyên căn, 1 trệt 1 lầu, diện tích 90m². Có sân trước rộng, gần chợ Hạnh Thông Tây, khu vực an ninh. Thích hợp hộ gia đình nhỏ hoặc nhóm sinh viên thuê chung.	17000000.00	90.00	Nguyễn Oanh, Gò Vấp, TP.HCM	nhapho	TP.HCM	daduyet	chothue	2025-10-12 21:55:12.6814
fc080893-c646-4521-a9b7-c5541f166a58	30f7a140-9e0f-4763-8c73-d0ba585e0584	Biệt thự ven sông Thảo Điền	Biệt thự sang trọng 3 tầng, hồ bơi riêng, diện tích 450m². Thiết kế hiện đại, khu dân cư yên tĩnh, gần trường quốc tế BIS và trung tâm thương mại An Phú. Không gian xanh mát, thích hợp cho gia đình có thu nhập cao.	28000000000.00	450.00	Thảo Điền, Quận 2, TP.HCM	bietthu	TP.HCM	daduyet	ban	2025-10-12 21:55:12.6814
d5793b54-78d7-4033-8647-0f3ccccda434	30f7a140-9e0f-4763-8c73-d0ba585e0584	Căn hộ cho thuê Landmark 81 tầng cao	Căn hộ 1 phòng ngủ, view toàn cảnh sông Sài Gòn, nội thất nhập khẩu, đầy đủ tiện nghi. Miễn phí sử dụng hồ bơi, gym, BBQ. Gần Vincom, bệnh viện quốc tế. Phù hợp chuyên gia nước ngoài.	40000000.00	55.00	Landmark 81, Bình Thạnh, TP.HCM	canho	TP.HCM	daduyet	chothue	2025-10-12 21:55:12.6814
e92e8e4e-5ea2-4039-9d62-5a0e58e27d8f	30f7a140-9e0f-4763-8c73-d0ba585e0584	Nhà phố trung tâm Hà Nội - Quận Cầu Giấy	Nhà 4 tầng, diện tích 100m², thiết kế hiện đại, nội thất gỗ tự nhiên. Nằm trong khu dân trí cao, gần công viên Cầu Giấy và các trường đại học lớn. Phù hợp vừa ở vừa kinh doanh.	11500000000.00	100.00	Cầu Giấy, Hà Nội	nhapho	Hà Nội	daduyet	ban	2025-10-12 21:55:12.6814
63ce5217-1344-4228-9455-6329b83585f3	30f7a140-9e0f-4763-8c73-d0ba585e0584	Đất nền khu đô thị mới Thanh Hóa	Đất 200m², khu vực quy hoạch đồng bộ, gần bệnh viện và trường học. Pháp lý rõ ràng, có thể xây dựng ngay. Cơ hội đầu tư sinh lời cao trong 1–2 năm tới.	1250000000.00	200.00	Khu đô thị Đông Hải, Thanh Hóa	datnen	Thanh Hóa	chuaduyet	ban	2025-10-12 21:55:12.6814
3049148d-1d50-45b0-a5d5-4012798dbc16	29e897be-0b07-4051-bf51-04ee0286f394	Biệt thự mini gần biển Nha Trang	Biệt thự mini 1 trệt 1 lầu, diện tích 180m², cách biển chỉ 500m, có sân vườn và bể bơi nhỏ. Không gian thoáng mát, khu dân cư yên tĩnh, rất phù hợp nghỉ dưỡng hoặc cho thuê homestay.	6800000000.00	180.00	Nguyễn Thiện Thuật, Nha Trang, Khánh Hòa	bietthu	Khánh Hòa	daduyet	ban	2025-10-12 21:57:50.037199
a14b3837-0b85-4403-bcb2-db93996fc185	29e897be-0b07-4051-bf51-04ee0286f394	Căn hộ Sunrise Riverside Nhà Bè	Căn hộ 2 phòng ngủ, diện tích 68m², view hồ bơi, có sẵn nội thất. Khu căn hộ cao cấp của Novaland, tiện ích nội khu đầy đủ gồm hồ bơi tràn, khu BBQ, phòng tập gym, sân thể thao. Di chuyển thuận tiện về quận 7 và quận 1.	3200000000.00	68.00	Nguyễn Hữu Thọ, Nhà Bè, TP.HCM	canho	TP.HCM	daduyet	ban	2025-10-12 21:57:50.037199
11dd94ce-d64b-4bf2-bde5-e484fbfd5e5d	29e897be-0b07-4051-bf51-04ee0286f394	Biệt thự nghỉ dưỡng tại Phú Quốc	Biệt thự biển 2 tầng, diện tích 300m², hướng biển, thiết kế sang trọng, nội thất cao cấp nhập khẩu. Có hồ bơi riêng, sân vườn và khu BBQ. Nằm trong khu resort 5 sao, pháp lý rõ ràng, sổ hồng riêng.	18500000000.00	300.00	Bãi Trường, Phú Quốc, Kiên Giang	bietthu	Kiên Giang	daduyet	ban	2025-10-12 21:57:50.037199
d87c88bf-3bb0-44f4-ad78-a35bf01a2410	29e897be-0b07-4051-bf51-04ee0286f394	Căn hộ mini trung tâm Đà Lạt	Căn hộ nhỏ 35m², đầy đủ tiện nghi, ban công hướng đồi, khu vực yên tĩnh. Cách chợ Đà Lạt 1km, phù hợp cho cặp đôi hoặc khách thuê ngắn hạn. Giao thông thuận tiện, an ninh tốt.	9500000.00	35.00	Phan Đình Phùng, Đà Lạt, Lâm Đồng	canho	Lâm Đồng	daduyet	chothue	2025-10-12 21:57:50.037199
90ec7744-8ac0-4e2c-a4a3-9b95776d46bc	3551fa73-fadc-4ef2-a1a3-6929a8e65c11	Biệt thự song lập khu LakeView Quận 9	Biệt thự 2 tầng, diện tích đất 240m², có sân vườn và gara ô tô. Nằm trong khu compound an ninh, gần cao tốc Long Thành – Dầu Giây. Không gian sống trong lành, nhiều cây xanh.	9500000000.00	240.00	LakeView, Quận 9, TP.HCM	bietthu	TP.HCM	daduyet	ban	2025-10-12 21:55:12.6814
e44bf5c0-ad3a-4846-af51-81c146eec74e	29e897be-0b07-4051-bf51-04ee0286f394	Đất thổ cư gần khu công nghiệp VSIP Bình Dương	Lô đất 120m², thổ cư 100%, gần khu công nghiệp VSIP, dân cư đông, hạ tầng hoàn thiện. Gần trường học, chợ, tiện ích đầy đủ. Phù hợp xây trọ hoặc đầu tư lâu dài.	1150000000.00	120.00	Thuận An, Bình Dương	datnen	Bình Dương	chuaduyet	ban	2025-10-12 21:57:50.037199
\.


--
-- TOC entry 5157 (class 0 OID 18076)
-- Dependencies: 232
-- Data for Name: bieu_mau; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.bieu_mau (id, tieu_de, loai, ben_mua, ben_ban, trang_thai, tep_dk, ngay_tao, ngay_cn) FROM stdin;
97083a50-9e35-42af-9e0c-96c8b7d2fa4e	Biên bản làm việc và bàn giao #24	bienban	3ebc4930-2923-4085-b9ca-192f087ba6bf	d20c59af-cbf7-47b5-aec4-90b6b119a816	choduyet	hop_dong_dinh_kem_24.pdf	2025-10-11 22:33:45.362915	2025-10-14 22:54:51.075203
cfab6580-7720-4896-88d1-c140413ec525	Hồ sơ mua bán tài sản ngày 17-09-2025	hosomuaban	ed9f5adb-413b-43cc-81f1-99f0ca57b321	f9857b4a-d49f-45c5-850e-d5b6d0d3eb55	daky	hop_dong_dinh_kem_1.pdf	2025-09-17 05:02:07.905926	2025-09-20 08:57:37.788094
97da0178-46aa-45e1-ab4d-9ea799e802e0	Hồ sơ mua bán tài sản ngày 31-07-2025	hosomuaban	17716784-ec18-43b4-b8cb-a784d1127421	144ee4c9-bf24-438f-8a6b-e15dd0e71705	huy	hop_dong_dinh_kem_2.pdf	2025-07-31 16:34:03.573375	2025-08-04 22:08:06.852521
67103fc4-2edc-485e-b219-e2c045b7ecc2	Hợp đồng cho thuê căn hộ #3	hosothue	2a11d082-fc6e-4713-9738-8650e95844f4	886a1f9f-d5b6-4964-ae73-e1eb5a69139e	daky	hop_dong_dinh_kem_3.pdf	2025-08-23 07:21:20.694565	2025-08-25 02:10:34.540783
05c41396-c1e2-4d6c-93ab-4ed5c98f2424	Hợp đồng cho thuê căn hộ #4	hosothue	e2ce533c-9b1a-4bc6-bb84-440192b688d3	db5ac513-6077-4717-832c-ae37eda2c1d1	huy	hop_dong_dinh_kem_4.pdf	2025-07-16 08:55:31.835538	2025-07-18 11:01:38.246424
fc8b1248-cb41-422a-9455-cfa21d4ff473	Hợp đồng cho thuê căn hộ #5	hosothue	92d1b032-a891-4176-87dd-cdeab7473d61	d20c59af-cbf7-47b5-aec4-90b6b119a816	daky	hop_dong_dinh_kem_5.pdf	2025-10-07 21:33:25.172287	2025-10-11 19:12:02.683304
a4bb5f7d-5e0a-4746-9f7a-73a74c9e4030	Hợp đồng cho thuê căn hộ #6	hosothue	3ebc4930-2923-4085-b9ca-192f087ba6bf	90e4f116-b60a-4796-8f0d-26289f7dae50	daky	hop_dong_dinh_kem_6.pdf	2025-10-11 04:45:34.398353	2025-10-12 14:04:56.781415
9d62f592-edf4-49a6-ac93-2fa4314cdd74	Hợp đồng cho thuê căn hộ #7	hosothue	f64c8b9b-8b2a-4c16-87a0-9354988951d9	eb111e8c-1671-40a1-a970-64e683945c90	daky	hop_dong_dinh_kem_7.pdf	2025-08-21 12:22:11.463094	2025-08-25 03:54:28.043049
c08bf377-7da3-42af-8e27-9d8cf69125fa	Hồ sơ mua bán tài sản ngày 01-08-2025	hosomuaban	8e233604-c830-4df5-b863-29ec060327d3	9e4364ac-289e-4f4a-b18f-57bb5d05a336	huy	hop_dong_dinh_kem_8.pdf	2025-08-01 10:26:44.697792	2025-08-03 07:19:12.02844
bf54bb56-8148-4bf8-8ec4-7fdd4746a145	Hồ sơ mua bán tài sản ngày 03-10-2025	hosomuaban	07121b39-ffae-4f5a-be25-d9af117b1a8c	30f7a140-9e0f-4763-8c73-d0ba585e0584	daduyet	hop_dong_dinh_kem_9.pdf	2025-10-03 23:47:07.990721	2025-10-08 00:40:13.034832
c586d2be-d2b5-49ac-9f4e-8e83dc345314	Hồ sơ mua bán tài sản ngày 02-09-2025	hosomuaban	bc2ed64f-8ae4-4637-9632-75c5af63066c	59c9d360-7c64-45cf-b229-4e574b8c7ceb	daky	hop_dong_dinh_kem_10.pdf	2025-09-02 03:11:45.437584	2025-09-05 17:25:04.156447
a34f43d7-7843-4c09-9f43-3788bda050e8	Hồ sơ mua bán tài sản ngày 15-08-2025	hosomuaban	5ee334f1-bd31-41b7-8672-df8903f2747a	fe1dcc48-4a84-45a7-8c38-610f6686f0f7	daduyet	hop_dong_dinh_kem_11.pdf	2025-08-15 12:28:42.773675	2025-08-17 22:38:38.727588
9987fb5d-ccf1-41c0-ab46-08e2d755d099	Hợp đồng cho thuê căn hộ #12	hosothue	d6207506-d2af-4427-b599-7c51661f3bdd	1cabfcda-923b-400f-b05d-9b900516380c	daky	hop_dong_dinh_kem_12.pdf	2025-07-17 06:57:39.515051	2025-07-18 08:27:12.704792
102b51a7-f78f-4f0a-ac43-03c927b85a08	Hồ sơ mua bán tài sản ngày 04-08-2025	hosomuaban	79039562-eda7-4a5b-94cb-0cca2b078742	7efdc816-e04f-4493-9997-6b0766bae0db	choduyet	hop_dong_dinh_kem_13.pdf	2025-08-04 23:31:43.286125	2025-08-08 02:20:19.140842
a83172c3-ce5b-4333-a25a-d9876401799e	Biên bản làm việc và bàn giao #14	bienban	34a86257-d43c-44e7-88ab-bccabdd5c644	f5f4022f-eccc-48e6-a435-1a9b1cdb3f3a	huy	hop_dong_dinh_kem_14.pdf	2025-10-03 01:12:54.982752	2025-10-05 16:14:06.204853
1f4909b7-bc6d-4da0-a44e-00f622d3ac32	Hồ sơ mua bán tài sản ngày 17-09-2025	hosomuaban	a78c47f4-b1c4-4313-b90a-8938f6ba8cac	29e897be-0b07-4051-bf51-04ee0286f394	huy	hop_dong_dinh_kem_15.pdf	2025-09-17 21:02:23.064776	2025-09-18 19:59:10.920451
935b59f3-3f14-47d3-8be4-0d864eaf4100	Hồ sơ mua bán tài sản ngày 23-08-2025	hosomuaban	fb9d3af2-aeef-46b1-a91c-438cf099a636	0216dbde-2061-4841-ad42-0445ffd0692d	huy	hop_dong_dinh_kem_16.pdf	2025-08-23 23:59:51.481193	2025-08-25 13:34:47.889341
4e13bd74-c0f9-46a6-b376-01e7eb37ea60	Hợp đồng cho thuê căn hộ #17	hosothue	809f4af0-c6c4-4478-be5e-93c696302b7b	b11d78c1-73a5-4d18-8002-c505ef2e9986	huy	hop_dong_dinh_kem_17.pdf	2025-09-17 15:40:31.999764	2025-09-18 17:42:44.580211
fc6c385c-c789-46c1-9253-a2999e92de2f	Hợp đồng cho thuê căn hộ #18	hosothue	234a3664-f36a-442b-9bc2-a111ae14c1dc	78ed62c4-70ed-42c1-898b-6e400c6def37	daduyet	hop_dong_dinh_kem_18.pdf	2025-07-22 00:25:17.949685	2025-07-22 15:00:34.685095
b8e1efb8-b2ee-4310-b13f-bbdce15be065	Biên bản làm việc và bàn giao #19	bienban	ed9f5adb-413b-43cc-81f1-99f0ca57b321	3551fa73-fadc-4ef2-a1a3-6929a8e65c11	daky	hop_dong_dinh_kem_19.pdf	2025-10-01 13:08:45.548467	2025-10-06 01:52:02.176366
059babda-711a-4ed8-971b-b1446b422419	Biên bản làm việc và bàn giao #20	bienban	17716784-ec18-43b4-b8cb-a784d1127421	f9857b4a-d49f-45c5-850e-d5b6d0d3eb55	choduyet	hop_dong_dinh_kem_20.pdf	2025-07-22 10:05:49.044882	2025-07-22 13:56:53.850679
e20f0c87-f46f-4c31-b0e4-b5fc782d0663	Biên bản làm việc và bàn giao #21	bienban	2a11d082-fc6e-4713-9738-8650e95844f4	144ee4c9-bf24-438f-8a6b-e15dd0e71705	daky	hop_dong_dinh_kem_21.pdf	2025-09-08 22:41:15.375076	2025-09-10 17:44:19.833773
05c8099c-105e-4809-89ce-bb695f500826	Hợp đồng cho thuê căn hộ #22	hosothue	e2ce533c-9b1a-4bc6-bb84-440192b688d3	886a1f9f-d5b6-4964-ae73-e1eb5a69139e	daky	hop_dong_dinh_kem_22.pdf	2025-08-23 12:50:01.69166	2025-08-24 18:58:42.613422
d108c3cf-2197-4b18-aa3d-76843554c149	Hồ sơ mua bán tài sản ngày 17-08-2025	hosomuaban	92d1b032-a891-4176-87dd-cdeab7473d61	db5ac513-6077-4717-832c-ae37eda2c1d1	huy	hop_dong_dinh_kem_23.pdf	2025-08-17 08:50:10.702476	2025-08-18 12:26:16.200584
20620d04-eccb-464c-8b26-2657ac65554d	Hợp đồng cho thuê căn hộ #25	hosothue	f64c8b9b-8b2a-4c16-87a0-9354988951d9	90e4f116-b60a-4796-8f0d-26289f7dae50	daduyet	hop_dong_dinh_kem_25.pdf	2025-09-11 20:57:55.622552	2025-09-16 04:14:18.288664
\.


--
-- TOC entry 5150 (class 0 OID 17846)
-- Dependencies: 225
-- Data for Name: danh_gia_bds; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.danh_gia_bds (id, id_nguoi_dung, id_bds, diem, binh_luan, trang_thai, ngay_tao) FROM stdin;
b82a1f46-d67c-4623-9fad-9d68cf62fb53	8e233604-c830-4df5-b863-29ec060327d3	08dc8dd4-1201-431c-b2f0-449695b97b1f	4	Khá ổn trong tầm giá, có thể tốt hơn.	hien	2025-10-13 17:07:55.10154
defb2db6-e7a7-41a3-9bb8-8fb279567fb0	ed9f5adb-413b-43cc-81f1-99f0ca57b321	d47acb24-261b-47b6-8110-bbb6b59fa7d0	3	Khá ổn trong tầm giá, có thể tốt hơn.	hien	2025-10-13 17:07:55.10154
3966d11c-d001-4d7d-ba67-4df238b4dcfb	92d1b032-a891-4176-87dd-cdeab7473d61	3049148d-1d50-45b0-a5d5-4012798dbc16	5	Khá ổn trong tầm giá, có thể tốt hơn.	hien	2025-10-13 17:07:55.10154
d2a4e985-16ce-4a69-a1ed-ce1449606c41	d6207506-d2af-4427-b599-7c51661f3bdd	d558834d-66c1-4eb5-aa02-cc35b2b46d76	3	Vị trí đắc địa, dịch vụ chuyên nghiệp. Rất hài lòng!	hien	2025-10-13 17:07:55.10154
ac947bc2-4664-4c19-9e21-f2ae1298ed69	809f4af0-c6c4-4478-be5e-93c696302b7b	10e62f11-9535-44f7-90c5-c62066ca55c1	3	Vị trí đắc địa, dịch vụ chuyên nghiệp. Rất hài lòng!	hien	2025-10-13 17:07:55.10154
4bbaf50e-3fd9-4681-891c-097ebd24b6f2	f64c8b9b-8b2a-4c16-87a0-9354988951d9	fc080893-c646-4521-a9b7-c5541f166a58	5	Vị trí đắc địa, dịch vụ chuyên nghiệp. Rất hài lòng!	hien	2025-10-13 17:07:55.10154
0203be3c-be70-43f6-b43c-634118bad9f1	79039562-eda7-4a5b-94cb-0cca2b078742	63ce5217-1344-4228-9455-6329b83585f3	3	Khá ổn trong tầm giá, có thể tốt hơn.	hien	2025-10-13 17:07:55.10154
2ba9afdc-ca6d-48f1-9da3-5f9b93565bcf	e2ce533c-9b1a-4bc6-bb84-440192b688d3	90ec7744-8ac0-4e2c-a4a3-9b95776d46bc	4	Khá ổn trong tầm giá, có thể tốt hơn.	hien	2025-10-13 17:07:55.10154
ce10cf70-e950-4e12-b6c3-013e6bb0ce27	07121b39-ffae-4f5a-be25-d9af117b1a8c	4cf78123-afc6-461a-a49e-87ace9b3b508	4	Vị trí đắc địa, dịch vụ chuyên nghiệp. Rất hài lòng!	hien	2025-10-13 17:07:55.10154
fd2bc6ae-4eec-495e-84f2-4d207bdae50e	234a3664-f36a-442b-9bc2-a111ae14c1dc	e44bf5c0-ad3a-4846-af51-81c146eec74e	4	Căn hộ đẹp, tiện nghi đầy đủ, đáng để cân nhắc.	hien	2025-10-13 17:07:55.10154
d8ca87f0-c682-4060-a663-75f82e023259	17716784-ec18-43b4-b8cb-a784d1127421	d61ca29b-11ea-4733-bbd7-a2fdd2bddb9f	4	Vị trí đắc địa, dịch vụ chuyên nghiệp. Rất hài lòng!	hien	2025-10-13 17:07:55.10154
7e0b8279-b3e8-41a4-a8b7-a3e31d25999c	5ee334f1-bd31-41b7-8672-df8903f2747a	a14b3837-0b85-4403-bcb2-db93996fc185	3	Vị trí đắc địa, dịch vụ chuyên nghiệp. Rất hài lòng!	hien	2025-10-13 17:07:55.10154
5915d30f-7eb4-49cb-97d9-152a5926fb45	a78c47f4-b1c4-4313-b90a-8938f6ba8cac	34111548-a16c-4ce0-8827-532d3875a764	4	Vị trí đắc địa, dịch vụ chuyên nghiệp. Rất hài lòng!	hien	2025-10-13 17:07:55.10154
a9425e1a-64c9-4973-9f35-10dec350ff21	34a86257-d43c-44e7-88ab-bccabdd5c644	f141e5f0-7fff-4713-960b-da42f16f8465	4	Căn hộ đẹp, tiện nghi đầy đủ, đáng để cân nhắc.	hien	2025-10-13 17:07:55.10154
5c9442a9-f9b0-4a74-877c-0c1fdea4d216	bc2ed64f-8ae4-4637-9632-75c5af63066c	11dd94ce-d64b-4bf2-bde5-e484fbfd5e5d	4	Vị trí đắc địa, dịch vụ chuyên nghiệp. Rất hài lòng!	hien	2025-10-13 17:07:55.10154
f5d8f878-f0f3-476a-a321-1995008d9f37	fb9d3af2-aeef-46b1-a91c-438cf099a636	aa316098-b32d-4e11-b2aa-e3dfbe56e800	5	Vị trí đắc địa, dịch vụ chuyên nghiệp. Rất hài lòng!	hien	2025-10-13 17:07:55.10154
a3b14f34-b095-42da-8919-3c82be841b52	2a11d082-fc6e-4713-9738-8650e95844f4	c7b5c0d3-7779-4299-a40b-4f104d4845ef	3	Khá ổn trong tầm giá, có thể tốt hơn.	hien	2025-10-13 17:07:55.10154
1afb51f5-2bd1-41cf-bf51-d22b43a64b26	3ebc4930-2923-4085-b9ca-192f087ba6bf	5b0981ed-37ce-4729-8bb7-2da081ee2b36	5	Vị trí đắc địa, dịch vụ chuyên nghiệp. Rất hài lòng!	hien	2025-10-13 17:07:55.10154
\.


--
-- TOC entry 5156 (class 0 OID 17982)
-- Dependencies: 231
-- Data for Name: danh_gia_mg; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.danh_gia_mg (id, id_khach_hang, id_moi_gioi, diem, binh_luan, ngay_dg) FROM stdin;
9dbd386a-fe47-4592-886a-9bd91ebf4111	07121b39-ffae-4f5a-be25-d9af117b1a8c	db5ac513-6077-4717-832c-ae37eda2c1d1	5	Rất chuyên nghiệp và nhiệt tình. Tôi rất hài lòng với dịch vụ.	2025-10-14 21:17:52.09109
ca25ab3b-8a65-4a6c-89ff-01dc80eceb46	e2ce533c-9b1a-4bc6-bb84-440192b688d3	29e897be-0b07-4051-bf51-04ee0286f394	4	Anh môi giới hỗ trợ tốt, tìm được căn nhà ưng ý.	2025-10-14 21:17:52.09109
f0a8ed16-f94c-4f1b-bbaf-aeb05a4c1a2f	92d1b032-a891-4176-87dd-cdeab7473d61	fe1dcc48-4a84-45a7-8c38-610f6686f0f7	3	Tư vấn tạm ổn, nhưng đôi khi phản hồi hơi chậm.	2025-10-14 21:17:52.09109
72dbff6f-33b2-42c0-ae8e-0772a6f097af	fb9d3af2-aeef-46b1-a91c-438cf099a636	59c9d360-7c64-45cf-b229-4e574b8c7ceb	5	Tuyệt vời! Anh ấy đã giúp tôi giải quyết mọi thủ tục giấy tờ nhanh gọn.	2025-10-14 21:17:52.09109
dd11a8b2-e183-4892-9006-152f0e9d16f6	3ebc4930-2923-4085-b9ca-192f087ba6bf	78ed62c4-70ed-42c1-898b-6e400c6def37	2	Không hài lòng lắm, thông tin cung cấp ban đầu chưa được chính xác.	2025-10-14 21:17:52.09109
\.


--
-- TOC entry 5163 (class 0 OID 26428)
-- Dependencies: 238
-- Data for Name: dot_thanh_toan; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.dot_thanh_toan (id, id_giao_dich, lan_tt, so_tien_tt, ngay_tt, phuong_thuc, ghichu) FROM stdin;
3a8e7e28-fbd6-4ff4-8846-16f1e107cc89	1b1587d1-e2f9-45eb-9586-14d18cce3a4d	1	6870204738.00	2025-09-13 00:37:24.130554	Chuyển khoản ngân hàng	Thanh toán cho đợt 1
5892e1dc-7cb3-423a-9366-319d601f46ef	1b1587d1-e2f9-45eb-9586-14d18cce3a4d	2	7244607142.00	2025-11-03 07:59:44.857902	Ủy nhiệm chi	Thanh toán cho đợt 2
a42bc29d-a320-4d90-844e-a25212082f77	1b1587d1-e2f9-45eb-9586-14d18cce3a4d	3	6125246914.00	2026-01-02 16:14:39.888053	Chuyển khoản ngân hàng	Thanh toán cho đợt 3
bcca2ad0-75ad-4f66-a009-87307e7a9171	1b1587d1-e2f9-45eb-9586-14d18cce3a4d	4	9329065387.00	2026-03-09 19:42:13.962513	Ủy nhiệm chi	Thanh toán cho đợt 4
fc621eff-e632-4790-b1e3-ff6fb381f32f	29b748e8-6859-499f-978c-1c35c867edeb	1	3223993990.00	2025-12-19 10:40:55.939143	Tiền mặt	Thanh toán cho đợt 1
75b09e1b-4e91-4554-aaf4-87b0471c771b	29b748e8-6859-499f-978c-1c35c867edeb	2	2895531866.00	2026-03-04 04:21:33.164681	Chuyển khoản ngân hàng	Thanh toán cho đợt 2
0032f89d-541b-47fb-b193-e9c8268e843d	29b748e8-6859-499f-978c-1c35c867edeb	3	3074071037.00	2026-04-30 19:28:07.727422	Ủy nhiệm chi	Thanh toán cho đợt 3
377678c5-2fbe-4425-9a41-b46720681be8	29b748e8-6859-499f-978c-1c35c867edeb	4	1762426623.00	2026-07-01 23:33:12.232864	Ủy nhiệm chi	Thanh toán cho đợt 4
cd0bffe3-2e9c-4d72-8fe4-fa4bd69927fe	2b6bf62e-c6f3-4dfc-89dc-c8ccfaf3ed7f	1	10466301782.00	2025-05-27 11:25:39.858938	Ủy nhiệm chi	Thanh toán cho đợt 1
46678066-5ec4-42ca-8728-356e7162777e	2b6bf62e-c6f3-4dfc-89dc-c8ccfaf3ed7f	2	14248665114.00	2025-08-07 01:09:49.822622	Ủy nhiệm chi	Thanh toán cho đợt 2
a3b9e629-57e5-4372-b791-ce2d03ebde39	37b73648-c247-4551-8ce8-0418a1b1677f	1	7728164408.00	2025-11-27 03:18:29.347659	Chuyển khoản ngân hàng	Thanh toán cho đợt 1
7270ae61-ee30-450b-974c-716c33b37349	37b73648-c247-4551-8ce8-0418a1b1677f	2	8936630836.00	2026-01-11 20:16:54.816456	Chuyển khoản ngân hàng	Thanh toán cho đợt 2
48c96e66-821d-4f61-83f0-4383acdb0e63	37b73648-c247-4551-8ce8-0418a1b1677f	3	10026130230.00	2026-03-22 18:14:26.169261	Tiền mặt	Thanh toán cho đợt 3
bb638152-cb2f-461c-9987-c1dfb29e8294	39dcb4a9-abf7-4468-8dea-a889a0d97c00	1	10723100912.00	2025-01-09 00:54:18.509583	Ủy nhiệm chi	Thanh toán cho đợt 1
484e6818-6ff7-4769-a7d9-47ed4d68d770	39dcb4a9-abf7-4468-8dea-a889a0d97c00	2	9282714883.00	2025-03-14 00:12:25.632256	Ủy nhiệm chi	Thanh toán cho đợt 2
0ae71aa8-e05b-4b25-9e6e-177708c3c21d	6006c52a-0c2a-4e27-b5c4-0ba4dfb586e5	1	3139722721.00	2025-06-28 05:32:50.289211	Tiền mặt	Thanh toán cho đợt 1
addcadb6-f85f-43f3-b328-9bed0ef19559	6006c52a-0c2a-4e27-b5c4-0ba4dfb586e5	2	4683837989.00	2025-09-23 09:45:30.182562	Chuyển khoản ngân hàng	Thanh toán cho đợt 2
569d5122-9538-4a6f-ad5a-b5d9bc182c67	63ed9dca-eeb9-49b3-b24d-af65330f4c44	1	1759342277.00	2025-05-19 03:18:09.885171	Tiền mặt	Thanh toán cho đợt 1
42e41f99-750a-40b0-8d56-da19cc5b27fc	63ed9dca-eeb9-49b3-b24d-af65330f4c44	2	1615944628.00	2025-07-22 10:09:20.883537	Chuyển khoản ngân hàng	Thanh toán cho đợt 2
334870a7-d117-4de4-a589-82b3cd30a7c0	63ed9dca-eeb9-49b3-b24d-af65330f4c44	3	1517534081.00	2025-09-02 11:37:43.490841	Tiền mặt	Thanh toán cho đợt 3
367f9908-a693-41be-89b0-6331ac6d9d16	63ed9dca-eeb9-49b3-b24d-af65330f4c44	4	2123768457.00	2025-10-07 14:11:52.316911	Chuyển khoản ngân hàng	Thanh toán cho đợt 4
02329f87-54d2-4fa1-8092-8b5448edb6c5	672a27b6-326f-4b3e-9eaf-eb66de5b736d	1	3165671913.00	2025-11-01 17:53:07.207042	Tiền mặt	Thanh toán cho đợt 1
4a9cfeae-54e7-4c72-a477-f3a93173e5df	672a27b6-326f-4b3e-9eaf-eb66de5b736d	2	2972705575.00	2025-12-17 19:38:13.848064	Ủy nhiệm chi	Thanh toán cho đợt 2
12908a7b-289a-4e83-9a0d-67eb11d2471a	672a27b6-326f-4b3e-9eaf-eb66de5b736d	3	3015817035.00	2026-03-27 03:23:34.404145	Chuyển khoản ngân hàng	Thanh toán cho đợt 3
35ac533e-42eb-4463-8be9-cc31314c56bf	84769419-daa9-42d4-9fa7-76982a3b6a99	1	1353266209.00	2025-02-08 19:33:37.243758	Tiền mặt	Thanh toán cho đợt 1
4cf61908-a884-4fa4-90b8-f58ec52d3577	84769419-daa9-42d4-9fa7-76982a3b6a99	2	1357342154.00	2025-03-13 20:13:31.420062	Tiền mặt	Thanh toán cho đợt 2
70124e4d-60ff-4fff-9334-5bd74204e21a	84769419-daa9-42d4-9fa7-76982a3b6a99	3	1232187456.00	2025-06-04 11:09:41.726622	Ủy nhiệm chi	Thanh toán cho đợt 3
b9dcf978-0ea6-498c-9605-50a2bbff4e15	84769419-daa9-42d4-9fa7-76982a3b6a99	4	733446115.00	2025-08-18 14:38:18.15645	Chuyển khoản ngân hàng	Thanh toán cho đợt 4
6e714af4-8697-47c4-b8c1-b131f61b7e21	8fb9499d-702b-4674-900f-f8171a255b9c	1	3865202636.00	2026-01-11 22:17:19.292743	Chuyển khoản ngân hàng	Thanh toán cho đợt 1
7bb43421-6c26-4200-b709-b8b5600dac04	8fb9499d-702b-4674-900f-f8171a255b9c	2	4342351343.00	2026-03-16 02:46:23.920112	Chuyển khoản ngân hàng	Thanh toán cho đợt 2
580fcfe0-aa2a-4881-8505-054a98fe9f9d	8fb9499d-702b-4674-900f-f8171a255b9c	3	4161579401.00	2026-05-03 05:59:02.488527	Ủy nhiệm chi	Thanh toán cho đợt 3
a7614303-51b0-404e-8892-b98af037096a	8fb9499d-702b-4674-900f-f8171a255b9c	4	3745989316.00	2026-07-11 10:35:15.307952	Chuyển khoản ngân hàng	Thanh toán cho đợt 4
9ac66e10-93eb-4201-885a-3350c1293c6c	8fb9499d-702b-4674-900f-f8171a255b9c	5	3386168051.00	2026-09-01 19:14:19.583333	Tiền mặt	Thanh toán cho đợt 5
3d676217-c048-4779-b9f6-65b5df14b426	bc8aac5a-364a-4d33-a5a7-2d864b4d33a7	1	7098195004.00	2025-09-26 07:52:57.536876	Chuyển khoản ngân hàng	Thanh toán cho đợt 1
cdd180f3-990a-4056-abd6-942fb52f96f8	bc8aac5a-364a-4d33-a5a7-2d864b4d33a7	2	8438988718.00	2025-12-03 21:33:28.108909	Chuyển khoản ngân hàng	Thanh toán cho đợt 2
448e90db-5276-4a65-b28f-1c504b7ab6a3	bc8aac5a-364a-4d33-a5a7-2d864b4d33a7	3	6184547651.00	2026-01-07 12:58:14.200758	Ủy nhiệm chi	Thanh toán cho đợt 3
2fb2fe4e-aaa2-49e0-b988-a399cc3c1e06	bc8aac5a-364a-4d33-a5a7-2d864b4d33a7	4	8540080455.00	2026-04-19 01:07:55.136406	Chuyển khoản ngân hàng	Thanh toán cho đợt 4
bc7cfa08-4b10-4b80-a13f-1f23d3935648	c0c31be6-927e-440e-91fe-f22a8fcb04dc	1	195465521.00	2025-05-02 12:43:40.59623	Ủy nhiệm chi	Thanh toán cho đợt 1
f5eeb512-3efc-4d0f-8f96-b713666d49a0	c0c31be6-927e-440e-91fe-f22a8fcb04dc	2	171788164.00	2025-06-13 07:16:04.900136	Ủy nhiệm chi	Thanh toán cho đợt 2
2c341117-30ab-456b-9864-b5fe63e9ae19	c0c31be6-927e-440e-91fe-f22a8fcb04dc	3	135894981.00	2025-09-03 21:16:21.909821	Tiền mặt	Thanh toán cho đợt 3
b97b79e4-c050-4eb7-9569-c0e1112e78c4	c4ac28b5-4c64-4d11-8acd-ebc1d10209ea	1	2971474995.00	2025-04-20 04:07:58.141507	Tiền mặt	Thanh toán cho đợt 1
994fcdf5-e7de-45c5-9c5d-7a0b2b410d94	c4ac28b5-4c64-4d11-8acd-ebc1d10209ea	2	3048363896.00	2025-06-04 17:33:06.029576	Chuyển khoản ngân hàng	Thanh toán cho đợt 2
20d33204-05e9-48a6-8b8b-3ec94596f7e5	c4ac28b5-4c64-4d11-8acd-ebc1d10209ea	3	2998741594.00	2025-06-26 20:10:16.782882	Tiền mặt	Thanh toán cho đợt 3
1d8279e6-c17d-40f4-9d1c-29cfea38554a	c4ac28b5-4c64-4d11-8acd-ebc1d10209ea	4	4854054970.00	2025-09-04 20:08:04.20075	Ủy nhiệm chi	Thanh toán cho đợt 4
e1a879c7-5ada-4283-86ac-7af3e8c7a2f8	cd298262-e209-4be3-a1c4-7409a7a437ed	1	12467482011.00	2025-09-14 15:07:35.584002	Ủy nhiệm chi	Thanh toán cho đợt 1
46b6e4cf-f343-443c-bca0-eec77eaaac31	cd298262-e209-4be3-a1c4-7409a7a437ed	2	14650528302.00	2025-11-25 12:14:42.299631	Ủy nhiệm chi	Thanh toán cho đợt 2
44865c9f-7302-4f44-9b04-10eaf5dae67f	d1faadb3-a43b-4279-a9ad-d179eff5c438	1	5516298706.00	2025-09-13 09:48:46.172813	Chuyển khoản ngân hàng	Thanh toán cho đợt 1
070c3a18-21c6-460a-92d0-bdb8307ecc10	d1faadb3-a43b-4279-a9ad-d179eff5c438	2	6066364358.00	2025-10-21 09:02:33.45069	Tiền mặt	Thanh toán cho đợt 2
e6811b18-b858-44eb-8571-f29f9b1a039f	d1faadb3-a43b-4279-a9ad-d179eff5c438	3	6005545243.00	2026-01-09 01:33:01.518856	Tiền mặt	Thanh toán cho đợt 3
99167390-f36c-4929-899c-dabaa3fec088	d1faadb3-a43b-4279-a9ad-d179eff5c438	4	2995244973.00	2026-03-26 08:21:46.912925	Chuyển khoản ngân hàng	Thanh toán cho đợt 4
9247d4f5-814e-44fb-aeef-d8ccce6866a2	dc4f87ef-df71-4e1d-80d0-a4c1afc7c21b	1	7704233100.00	2025-08-29 18:05:00.804868	Tiền mặt	Thanh toán cho đợt 1
febdf528-d110-46e8-8a10-aeb75722b2b8	dc4f87ef-df71-4e1d-80d0-a4c1afc7c21b	2	6061801970.00	2025-12-10 04:54:47.879684	Tiền mặt	Thanh toán cho đợt 2
ef9dfe50-c74d-4ff6-b469-71e42c6ec47f	dc4f87ef-df71-4e1d-80d0-a4c1afc7c21b	3	7396986533.00	2026-01-28 11:21:32.716215	Tiền mặt	Thanh toán cho đợt 3
059b72ab-8a4d-4058-958f-3df0b732a96a	dc4f87ef-df71-4e1d-80d0-a4c1afc7c21b	4	5578016333.00	2026-04-10 07:57:46.854011	Ủy nhiệm chi	Thanh toán cho đợt 4
1a477c9b-7374-4e78-bd60-dabe47330e1b	f4070b66-a255-4afe-b715-d15fd7db21b0	1	4006603576.00	2025-06-29 12:49:34.212695	Chuyển khoản ngân hàng	Thanh toán cho đợt 1
56344d20-6f6f-4fba-98be-3130b0676846	f4070b66-a255-4afe-b715-d15fd7db21b0	2	3919505957.00	2025-08-19 04:03:42.621101	Chuyển khoản ngân hàng	Thanh toán cho đợt 2
5da629b1-b2aa-4e29-8af5-e243a12e6eb1	f4070b66-a255-4afe-b715-d15fd7db21b0	3	4311215320.00	2025-10-27 21:16:39.495733	Ủy nhiệm chi	Thanh toán cho đợt 3
bc19114a-8745-44eb-806d-683f13286f7d	f4070b66-a255-4afe-b715-d15fd7db21b0	4	4400935321.00	2026-01-26 06:46:31.640222	Tiền mặt	Thanh toán cho đợt 4
7f54b393-a219-4fd5-9420-f68cf93ba01e	f48766c9-ce86-4fca-835c-eb2c0946d078	1	1586840727.00	2025-06-25 20:00:03.0201	Tiền mặt	Thanh toán cho đợt 1
70c84e8c-8e6b-44cd-b50a-846ca0b76c91	f48766c9-ce86-4fca-835c-eb2c0946d078	2	1403091984.00	2025-09-06 23:05:54.77555	Ủy nhiệm chi	Thanh toán cho đợt 2
450b45cf-7c05-4a98-b50a-2ed200f3d54e	f48766c9-ce86-4fca-835c-eb2c0946d078	3	1794131662.00	2025-11-07 19:42:09.097129	Tiền mặt	Thanh toán cho đợt 3
191be60f-6e94-43b5-b43d-26d4bb9a16ee	f48766c9-ce86-4fca-835c-eb2c0946d078	4	1674243752.00	2026-02-08 15:52:30.805862	Ủy nhiệm chi	Thanh toán cho đợt 4
c2af5416-868d-47be-8a55-ad6c2a99238a	f48766c9-ce86-4fca-835c-eb2c0946d078	5	1926598986.00	2026-03-21 11:16:45.183502	Tiền mặt	Thanh toán cho đợt 5
b1b1d998-c4e6-4973-a2ee-1d97758b1ac4	f9d6e97f-3755-47b1-8752-dd1c4c57ec5f	1	1172683504.00	2025-12-15 22:11:50.151912	Ủy nhiệm chi	Thanh toán cho đợt 1
d26090f6-a631-439b-8187-2197d1a79725	f9d6e97f-3755-47b1-8752-dd1c4c57ec5f	2	1073183073.00	2026-01-31 02:14:28.538188	Chuyển khoản ngân hàng	Thanh toán cho đợt 2
\.


--
-- TOC entry 5164 (class 0 OID 26445)
-- Dependencies: 239
-- Data for Name: dot_thanh_toan_ct; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.dot_thanh_toan_ct (id, id_dot_thanh_toan, id_bds, so_luong, so_tien) FROM stdin;
f6cee750-0050-4273-80f5-aba1c03791c3	3a8e7e28-fbd6-4ff4-8846-16f1e107cc89	d558834d-66c1-4eb5-aa02-cc35b2b46d76	1	6870204738.00
e5c1590b-5d38-4d86-b265-30a26c346841	5892e1dc-7cb3-423a-9366-319d601f46ef	d558834d-66c1-4eb5-aa02-cc35b2b46d76	1	7244607142.00
a8608b4f-ee5a-4cad-bf39-b0aa28cd781b	a42bc29d-a320-4d90-844e-a25212082f77	d558834d-66c1-4eb5-aa02-cc35b2b46d76	1	6125246914.00
0ffebb49-75b9-424c-8d61-afbe08ffe704	bcca2ad0-75ad-4f66-a009-87307e7a9171	d558834d-66c1-4eb5-aa02-cc35b2b46d76	1	9329065387.00
fe178cd0-87d0-4bd8-b98b-1e934d8bb9f9	fc621eff-e632-4790-b1e3-ff6fb381f32f	fc080893-c646-4521-a9b7-c5541f166a58	1	3223993990.00
f0758310-851c-4800-85b9-124709d2d107	75b09e1b-4e91-4554-aaf4-87b0471c771b	fc080893-c646-4521-a9b7-c5541f166a58	1	2895531866.00
9d321569-5642-42a4-8648-4b8f6294267d	0032f89d-541b-47fb-b193-e9c8268e843d	fc080893-c646-4521-a9b7-c5541f166a58	1	3074071037.00
29aa0fa9-1bfd-4530-8cb8-4a9916ee1a07	377678c5-2fbe-4425-9a41-b46720681be8	fc080893-c646-4521-a9b7-c5541f166a58	1	1762426623.00
78d39632-7f65-4851-9277-5c6c90954be7	cd0bffe3-2e9c-4d72-8fe4-fa4bd69927fe	5799c0c2-0bee-43e0-a580-74c5fddad75c	1	10466301782.00
5e877fea-924f-47f3-82b0-7505da07ece5	46678066-5ec4-42ca-8728-356e7162777e	5799c0c2-0bee-43e0-a580-74c5fddad75c	1	14248665114.00
50dba2da-b62e-4a20-ae15-fc6e49f67b2c	a3b9e629-57e5-4372-b791-ce2d03ebde39	34111548-a16c-4ce0-8827-532d3875a764	1	7728164408.00
7f26ae73-0832-45a9-82e4-e2a84dbe729e	7270ae61-ee30-450b-974c-716c33b37349	34111548-a16c-4ce0-8827-532d3875a764	1	8936630836.00
5e377618-f62d-49af-a2df-9db341b69114	48c96e66-821d-4f61-83f0-4383acdb0e63	34111548-a16c-4ce0-8827-532d3875a764	1	10026130230.00
55a21261-85fa-4d0c-9568-b86b2535c1ff	bb638152-cb2f-461c-9987-c1dfb29e8294	e92e8e4e-5ea2-4039-9d62-5a0e58e27d8f	1	10723100912.00
3c1860aa-148c-4871-9714-0fea0c35f953	484e6818-6ff7-4769-a7d9-47ed4d68d770	e92e8e4e-5ea2-4039-9d62-5a0e58e27d8f	1	9282714883.00
def9d8f3-1332-4198-b3e7-36bf7abc6efe	0ae71aa8-e05b-4b25-9e6e-177708c3c21d	fdfe9f43-a19a-42b9-97bc-a8e9fed6b24b	1	3139722721.00
694182f7-52a0-4f0d-be56-020604338b34	addcadb6-f85f-43f3-b328-9bed0ef19559	fdfe9f43-a19a-42b9-97bc-a8e9fed6b24b	1	4683837989.00
c563d376-46b7-4839-9589-ae3fe4bcffee	569d5122-9538-4a6f-ad5a-b5d9bc182c67	1014202c-2ab9-492b-81ff-338cbb2f0e8e	1	1759342277.00
be2c30a6-a1c9-48f8-8376-e8fee7fb796f	42e41f99-750a-40b0-8d56-da19cc5b27fc	1014202c-2ab9-492b-81ff-338cbb2f0e8e	1	1615944628.00
90f5f950-6b6b-4de2-8b5c-48c9c9e9362b	334870a7-d117-4de4-a589-82b3cd30a7c0	1014202c-2ab9-492b-81ff-338cbb2f0e8e	1	1517534081.00
e6a5104c-e062-41b0-afdf-e0cf3987ca34	367f9908-a693-41be-89b0-6331ac6d9d16	1014202c-2ab9-492b-81ff-338cbb2f0e8e	1	2123768457.00
c9a1132c-dc5d-414a-b720-ee45229aa2cd	02329f87-54d2-4fa1-8092-8b5448edb6c5	3049148d-1d50-45b0-a5d5-4012798dbc16	1	3165671913.00
c6594438-bc56-4617-a16f-18de30e239d4	4a9cfeae-54e7-4c72-a477-f3a93173e5df	3049148d-1d50-45b0-a5d5-4012798dbc16	1	2972705575.00
640e62d6-61bf-4c6a-b69d-c866e9c5073c	12908a7b-289a-4e83-9a0d-67eb11d2471a	3049148d-1d50-45b0-a5d5-4012798dbc16	1	3015817035.00
5bbed6be-28c1-49bb-8596-a1abf9c2cab7	35ac533e-42eb-4463-8be9-cc31314c56bf	10e62f11-9535-44f7-90c5-c62066ca55c1	1	1353266209.00
3f4be0e3-d1d7-47d1-aefc-e96c4793074a	4cf61908-a884-4fa4-90b8-f58ec52d3577	10e62f11-9535-44f7-90c5-c62066ca55c1	1	1357342154.00
67f911f4-88f0-45ab-ae40-7c4af8db209f	70124e4d-60ff-4fff-9334-5bd74204e21a	10e62f11-9535-44f7-90c5-c62066ca55c1	1	1232187456.00
7de1bc65-05ac-451f-97e9-c661a257ef19	b9dcf978-0ea6-498c-9605-50a2bbff4e15	10e62f11-9535-44f7-90c5-c62066ca55c1	1	733446115.00
b27c7f52-5373-4e55-96c0-d85a438652a5	6e714af4-8697-47c4-b8c1-b131f61b7e21	5b0981ed-37ce-4729-8bb7-2da081ee2b36	1	3865202636.00
609851e9-313c-4d4d-8b06-73ce74037dd2	7bb43421-6c26-4200-b709-b8b5600dac04	5b0981ed-37ce-4729-8bb7-2da081ee2b36	1	4342351343.00
45240e0b-8ba7-4034-84ce-53d6c2f5acba	580fcfe0-aa2a-4881-8505-054a98fe9f9d	5b0981ed-37ce-4729-8bb7-2da081ee2b36	1	4161579401.00
4f182e9d-0394-46fc-95d8-22ad1bf1c070	a7614303-51b0-404e-8892-b98af037096a	5b0981ed-37ce-4729-8bb7-2da081ee2b36	1	3745989316.00
c7716fc2-ac72-4022-ad10-69296e0fde3f	9ac66e10-93eb-4201-885a-3350c1293c6c	5b0981ed-37ce-4729-8bb7-2da081ee2b36	1	3386168051.00
ea5c2a3f-45b3-40ed-bffd-399c6f20deb2	3d676217-c048-4779-b9f6-65b5df14b426	d47acb24-261b-47b6-8110-bbb6b59fa7d0	1	7098195004.00
7ab47110-111a-4afb-a25d-ae5ac619cb2a	cdd180f3-990a-4056-abd6-942fb52f96f8	d47acb24-261b-47b6-8110-bbb6b59fa7d0	1	8438988718.00
eabdc2f3-ddd1-4816-ada7-91a22d989b25	448e90db-5276-4a65-b28f-1c504b7ab6a3	d47acb24-261b-47b6-8110-bbb6b59fa7d0	1	6184547651.00
4e6c0fb7-b2fa-436d-8877-66b5c726dd98	2fb2fe4e-aaa2-49e0-b988-a399cc3c1e06	d47acb24-261b-47b6-8110-bbb6b59fa7d0	1	8540080455.00
deda4adb-2441-4f77-8690-ceb0816e9edc	bc7cfa08-4b10-4b80-a13f-1f23d3935648	d87c88bf-3bb0-44f4-ad78-a35bf01a2410	1	195465521.00
74162ace-7ab5-43d6-ab62-933fd9d1e1b4	f5eeb512-3efc-4d0f-8f96-b713666d49a0	d87c88bf-3bb0-44f4-ad78-a35bf01a2410	1	171788164.00
4fee03c6-5409-4ad4-88b6-475014e8f0ba	2c341117-30ab-456b-9864-b5fe63e9ae19	d87c88bf-3bb0-44f4-ad78-a35bf01a2410	1	135894981.00
18e0ab0e-585f-4aa2-9163-d7cf243ad419	b97b79e4-c050-4eb7-9569-c0e1112e78c4	d5793b54-78d7-4033-8647-0f3ccccda434	1	2971474995.00
50baf293-4dc1-4ed6-98e1-a1525a52aebc	994fcdf5-e7de-45c5-9c5d-7a0b2b410d94	d5793b54-78d7-4033-8647-0f3ccccda434	1	3048363896.00
50dd9c66-3322-4286-9eec-a7f1e802a4be	20d33204-05e9-48a6-8b8b-3ec94596f7e5	d5793b54-78d7-4033-8647-0f3ccccda434	1	2998741594.00
d3613de6-4948-4ba9-8b47-f2b8308c3f05	1d8279e6-c17d-40f4-9d1c-29cfea38554a	d5793b54-78d7-4033-8647-0f3ccccda434	1	4854054970.00
c6100021-53fa-426c-b2f3-24c0c4a597ce	e1a879c7-5ada-4283-86ac-7af3e8c7a2f8	aa316098-b32d-4e11-b2aa-e3dfbe56e800	1	12467482011.00
cb441d0e-3c77-4e53-b380-cb7ab69f6e58	46b6e4cf-f343-443c-bca0-eec77eaaac31	aa316098-b32d-4e11-b2aa-e3dfbe56e800	1	14650528302.00
fd5bdcba-a909-4724-b045-748d4b0f6136	44865c9f-7302-4f44-9b04-10eaf5dae67f	d61ca29b-11ea-4733-bbd7-a2fdd2bddb9f	1	5516298706.00
447febe1-f7f1-4245-adf4-60e5739e8dc9	070c3a18-21c6-460a-92d0-bdb8307ecc10	d61ca29b-11ea-4733-bbd7-a2fdd2bddb9f	1	6066364358.00
a6b53422-f7d0-4efa-a22f-d9ee13018bce	e6811b18-b858-44eb-8571-f29f9b1a039f	d61ca29b-11ea-4733-bbd7-a2fdd2bddb9f	1	6005545243.00
65489e15-7369-42ab-afbd-0c6bb3c181bb	99167390-f36c-4929-899c-dabaa3fec088	d61ca29b-11ea-4733-bbd7-a2fdd2bddb9f	1	2995244973.00
bac3c70c-555f-4dc7-aa0b-013480b1cde3	9247d4f5-814e-44fb-aeef-d8ccce6866a2	08dc8dd4-1201-431c-b2f0-449695b97b1f	1	7704233100.00
19285e32-7432-4ed2-bb6d-7e57911300c8	febdf528-d110-46e8-8a10-aeb75722b2b8	08dc8dd4-1201-431c-b2f0-449695b97b1f	1	6061801970.00
0e4ac597-f9b0-42e1-a151-1b81d199e67c	ef9dfe50-c74d-4ff6-b469-71e42c6ec47f	08dc8dd4-1201-431c-b2f0-449695b97b1f	1	7396986533.00
2b9283ac-9483-4b7b-8fd1-15f40968a09e	059b72ab-8a4d-4058-958f-3df0b732a96a	08dc8dd4-1201-431c-b2f0-449695b97b1f	1	5578016333.00
071c705d-e0bf-4d01-b186-e8e361a5c9f9	1a477c9b-7374-4e78-bd60-dabe47330e1b	90ec7744-8ac0-4e2c-a4a3-9b95776d46bc	1	4006603576.00
3b1fc243-4f7f-4bf1-be41-8edd3c796ca7	56344d20-6f6f-4fba-98be-3130b0676846	90ec7744-8ac0-4e2c-a4a3-9b95776d46bc	1	3919505957.00
f3e4a5dc-d07e-4075-bc52-1e0f1fc0e245	5da629b1-b2aa-4e29-8af5-e243a12e6eb1	90ec7744-8ac0-4e2c-a4a3-9b95776d46bc	1	4311215320.00
97dc3eba-ecba-4bed-aca2-b5caf73a9e27	bc19114a-8745-44eb-806d-683f13286f7d	90ec7744-8ac0-4e2c-a4a3-9b95776d46bc	1	4400935321.00
6c7f7c2f-93db-433a-996f-f1f425a8b376	7f54b393-a219-4fd5-9420-f68cf93ba01e	4cf78123-afc6-461a-a49e-87ace9b3b508	1	1586840727.00
cf7c06be-136b-4f94-a318-add9600cec36	70c84e8c-8e6b-44cd-b50a-846ca0b76c91	4cf78123-afc6-461a-a49e-87ace9b3b508	1	1403091984.00
8c50f74a-2fd7-4746-9840-c35037b1550f	450b45cf-7c05-4a98-b50a-2ed200f3d54e	4cf78123-afc6-461a-a49e-87ace9b3b508	1	1794131662.00
7bb746f7-f1d7-46bc-8c58-2295d7584834	191be60f-6e94-43b5-b43d-26d4bb9a16ee	4cf78123-afc6-461a-a49e-87ace9b3b508	1	1674243752.00
46ec7439-b118-4bb9-b4d3-3048497e38f5	c2af5416-868d-47be-8a55-ad6c2a99238a	4cf78123-afc6-461a-a49e-87ace9b3b508	1	1926598986.00
3a07faca-1841-4805-9413-808720cddac4	b1b1d998-c4e6-4973-a2ee-1d97758b1ac4	a14b3837-0b85-4403-bcb2-db93996fc185	1	1172683504.00
774bc3a1-c9c4-4295-89ed-a5e60db4f809	d26090f6-a631-439b-8187-2197d1a79725	a14b3837-0b85-4403-bcb2-db93996fc185	1	1073183073.00
\.


--
-- TOC entry 5147 (class 0 OID 17794)
-- Dependencies: 222
-- Data for Name: giao_dich; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.giao_dich (id, id_nguoi_dung, id_bds, loai, ngay_giao_dich, trang_thai, id_nguoi_ban) FROM stdin;
c4ac28b5-4c64-4d11-8acd-ebc1d10209ea	17716784-ec18-43b4-b8cb-a784d1127421	d5793b54-78d7-4033-8647-0f3ccccda434	thue	2024-12-22 15:36:19.643019	choxuly	90e4f116-b60a-4796-8f0d-26289f7dae50
2b6bf62e-c6f3-4dfc-89dc-c8ccfaf3ed7f	809f4af0-c6c4-4478-be5e-93c696302b7b	5799c0c2-0bee-43e0-a580-74c5fddad75c	thue	2025-03-06 15:26:11.192045	choxuly	3551fa73-fadc-4ef2-a1a3-6929a8e65c11
39dcb4a9-abf7-4468-8dea-a889a0d97c00	07121b39-ffae-4f5a-be25-d9af117b1a8c	e92e8e4e-5ea2-4039-9d62-5a0e58e27d8f	mua	2024-11-05 00:44:24.402121	hoantat	db5ac513-6077-4717-832c-ae37eda2c1d1
c0c31be6-927e-440e-91fe-f22a8fcb04dc	34a86257-d43c-44e7-88ab-bccabdd5c644	d87c88bf-3bb0-44f4-ad78-a35bf01a2410	thue	2025-02-04 21:31:19.198546	dangxuly	59c9d360-7c64-45cf-b229-4e574b8c7ceb
672a27b6-326f-4b3e-9eaf-eb66de5b736d	fb9d3af2-aeef-46b1-a91c-438cf099a636	3049148d-1d50-45b0-a5d5-4012798dbc16	mua	2025-08-15 08:00:04.002879	choxuly	78ed62c4-70ed-42c1-898b-6e400c6def37
353c2a84-a191-4388-8012-6b85bd0ae09b	3ebc4930-2923-4085-b9ca-192f087ba6bf	11dd94ce-d64b-4bf2-bde5-e484fbfd5e5d	mua	2025-06-13 04:00:52.844472	dahuy	d20c59af-cbf7-47b5-aec4-90b6b119a816
f48766c9-ce86-4fca-835c-eb2c0946d078	234a3664-f36a-442b-9bc2-a111ae14c1dc	4cf78123-afc6-461a-a49e-87ace9b3b508	thue	2025-04-15 16:25:32.235268	choxuly	1cabfcda-923b-400f-b05d-9b900516380c
83f48790-fa7b-42cc-9c14-6b311c5b1848	ed9f5adb-413b-43cc-81f1-99f0ca57b321	f141e5f0-7fff-4713-960b-da42f16f8465	mua	2025-02-16 10:05:24.145864	choxuly	886a1f9f-d5b6-4964-ae73-e1eb5a69139e
33cb6323-6170-4bf6-b094-56a65c10c26e	bc2ed64f-8ae4-4637-9632-75c5af63066c	fabe2a78-1231-4543-827e-acfa16f6df1f	thue	2025-06-18 09:55:50.10458	choxuly	7efdc816-e04f-4493-9997-6b0766bae0db
1b1587d1-e2f9-45eb-9586-14d18cce3a4d	e2ce533c-9b1a-4bc6-bb84-440192b688d3	d558834d-66c1-4eb5-aa02-cc35b2b46d76	mua	2025-06-14 12:31:24.816387	hoantat	29e897be-0b07-4051-bf51-04ee0286f394
cefc79a8-688f-4dad-b23a-7db0c7a03d46	8e233604-c830-4df5-b863-29ec060327d3	8109b350-cfef-45a4-b7a7-7c4b0d413bb1	thue	2024-10-15 04:14:40.422086	choxuly	f9857b4a-d49f-45c5-850e-d5b6d0d3eb55
f4070b66-a255-4afe-b715-d15fd7db21b0	79039562-eda7-4a5b-94cb-0cca2b078742	90ec7744-8ac0-4e2c-a4a3-9b95776d46bc	mua	2025-04-17 20:57:58.910642	dangxuly	eb111e8c-1671-40a1-a970-64e683945c90
bc8aac5a-364a-4d33-a5a7-2d864b4d33a7	2a11d082-fc6e-4713-9738-8650e95844f4	d47acb24-261b-47b6-8110-bbb6b59fa7d0	thue	2025-07-01 16:54:04.93739	choxuly	30f7a140-9e0f-4763-8c73-d0ba585e0584
cd298262-e209-4be3-a1c4-7409a7a437ed	92d1b032-a891-4176-87dd-cdeab7473d61	aa316098-b32d-4e11-b2aa-e3dfbe56e800	thue	2025-06-28 08:33:11.287945	hoantat	fe1dcc48-4a84-45a7-8c38-610f6686f0f7
dc4f87ef-df71-4e1d-80d0-a4c1afc7c21b	f64c8b9b-8b2a-4c16-87a0-9354988951d9	08dc8dd4-1201-431c-b2f0-449695b97b1f	thue	2025-06-16 04:32:55.903465	choxuly	144ee4c9-bf24-438f-8a6b-e15dd0e71705
d1faadb3-a43b-4279-a9ad-d179eff5c438	a78c47f4-b1c4-4313-b90a-8938f6ba8cac	d61ca29b-11ea-4733-bbd7-a2fdd2bddb9f	thue	2025-06-08 09:16:46.488508	dangxuly	f5f4022f-eccc-48e6-a435-1a9b1cdb3f3a
63ed9dca-eeb9-49b3-b24d-af65330f4c44	d6207506-d2af-4427-b599-7c51661f3bdd	1014202c-2ab9-492b-81ff-338cbb2f0e8e	mua	2025-02-04 22:48:37.446638	dangxuly	9e4364ac-289e-4f4a-b18f-57bb5d05a336
5851082c-6a68-4147-883d-36349f874f10	17716784-ec18-43b4-b8cb-a784d1127421	c7b5c0d3-7779-4299-a40b-4f104d4845ef	mua	2024-11-13 05:44:49.324145	dangxuly	0216dbde-2061-4841-ad42-0445ffd0692d
84769419-daa9-42d4-9fa7-76982a3b6a99	5ee334f1-bd31-41b7-8672-df8903f2747a	10e62f11-9535-44f7-90c5-c62066ca55c1	thue	2024-11-04 15:04:25.722441	choxuly	3551fa73-fadc-4ef2-a1a3-6929a8e65c11
aba97ba5-f371-4f57-a749-4c0f33064f1f	07121b39-ffae-4f5a-be25-d9af117b1a8c	63ce5217-1344-4228-9455-6329b83585f3	thue	2025-03-05 12:06:33.763127	dahuy	b11d78c1-73a5-4d18-8002-c505ef2e9986
8fb9499d-702b-4674-900f-f8171a255b9c	34a86257-d43c-44e7-88ab-bccabdd5c644	5b0981ed-37ce-4729-8bb7-2da081ee2b36	thue	2025-10-10 12:57:51.501538	dahuy	db5ac513-6077-4717-832c-ae37eda2c1d1
6006c52a-0c2a-4e27-b5c4-0ba4dfb586e5	fb9d3af2-aeef-46b1-a91c-438cf099a636	fdfe9f43-a19a-42b9-97bc-a8e9fed6b24b	thue	2025-04-25 05:06:47.052634	hoantat	59c9d360-7c64-45cf-b229-4e574b8c7ceb
f9d6e97f-3755-47b1-8752-dd1c4c57ec5f	3ebc4930-2923-4085-b9ca-192f087ba6bf	a14b3837-0b85-4403-bcb2-db93996fc185	mua	2025-09-19 12:31:43.019029	hoantat	78ed62c4-70ed-42c1-898b-6e400c6def37
37b73648-c247-4551-8ce8-0418a1b1677f	5ee334f1-bd31-41b7-8672-df8903f2747a	34111548-a16c-4ce0-8827-532d3875a764	mua	2025-09-08 10:40:28.058772	dahuy	b11d78c1-73a5-4d18-8002-c505ef2e9986
29b748e8-6859-499f-978c-1c35c867edeb	809f4af0-c6c4-4478-be5e-93c696302b7b	fc080893-c646-4521-a9b7-c5541f166a58	mua	2025-09-18 06:26:20.9946	hoantat	90e4f116-b60a-4796-8f0d-26289f7dae50
\.


--
-- TOC entry 5167 (class 0 OID 26566)
-- Dependencies: 242
-- Data for Name: hinh_anh_bds; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.hinh_anh_bds (id, id_bds, url, mo_ta, ngay_tao, kich_thuoc, trang_thai, loai) FROM stdin;
aa1a339e-358d-47f3-9614-b307a407f8e1	d558834d-66c1-4eb5-aa02-cc35b2b46d76	bds01.jpg	Ảnh mặt tiền hoặc không gian chính của bất động sản.	2025-10-12 23:16:44.779754	0.00	binhthuong	anh
bb76809c-0a7d-41b9-b520-9b944cef1208	d61ca29b-11ea-4733-bbd7-a2fdd2bddb9f	bds02.jpg	Ảnh nội thất hoặc góc nhìn khác của bất động sản, thể hiện chi tiết rõ nét.	2025-10-12 23:16:44.779754	0.00	binhthuong	anh
4d13a184-146b-4e8e-b514-e45079635610	5b0981ed-37ce-4729-8bb7-2da081ee2b36	bds03.jpg	Ảnh mặt tiền hoặc không gian chính của bất động sản.	2025-10-12 23:16:44.779754	0.00	binhthuong	anh
a03e965f-5655-450c-bda7-813e79905fc4	aa316098-b32d-4e11-b2aa-e3dfbe56e800	bds04.jpg	Ảnh nội thất hoặc góc nhìn khác của bất động sản, thể hiện chi tiết rõ nét.	2025-10-12 23:16:44.779754	0.00	binhthuong	anh
7169a5e2-fc3e-4910-b245-1b4b1f288838	10e62f11-9535-44f7-90c5-c62066ca55c1	bds05.jpg	Ảnh mặt tiền hoặc không gian chính của bất động sản.	2025-10-12 23:16:44.779754	0.00	binhthuong	anh
2a21d403-daca-4613-8182-f00c231b703a	4cf78123-afc6-461a-a49e-87ace9b3b508	bds06.jpg	Ảnh nội thất hoặc góc nhìn khác của bất động sản, thể hiện chi tiết rõ nét.	2025-10-12 23:16:44.779754	0.00	binhthuong	anh
a1a33468-21b0-490b-a697-cdec3da80720	d47acb24-261b-47b6-8110-bbb6b59fa7d0	bds07.jpg	Ảnh mặt tiền hoặc không gian chính của bất động sản.	2025-10-12 23:16:44.779754	0.00	binhthuong	anh
c1ebb1fb-b632-4604-9e8e-28c95cc22808	5799c0c2-0bee-43e0-a580-74c5fddad75c	bds08.jpg	Ảnh nội thất hoặc góc nhìn khác của bất động sản, thể hiện chi tiết rõ nét.	2025-10-12 23:16:44.779754	0.00	binhthuong	anh
d66681f1-accd-4ae7-b034-a35663eb08ee	34111548-a16c-4ce0-8827-532d3875a764	bds09.jpg	Ảnh mặt tiền hoặc không gian chính của bất động sản.	2025-10-12 23:16:44.779754	0.00	binhthuong	anh
62bc6996-6cf1-49ef-8d21-da8befc44a34	8109b350-cfef-45a4-b7a7-7c4b0d413bb1	bds10.jpg	Ảnh nội thất hoặc góc nhìn khác của bất động sản, thể hiện chi tiết rõ nét.	2025-10-12 23:16:44.779754	0.00	binhthuong	anh
9db9456d-5267-445a-b4a1-ada46677dd69	1014202c-2ab9-492b-81ff-338cbb2f0e8e	bds11.jpg	Ảnh mặt tiền hoặc không gian chính của bất động sản.	2025-10-12 23:16:44.779754	0.00	binhthuong	anh
8f869e67-0afc-4790-a3a8-94ddcd514f45	f141e5f0-7fff-4713-960b-da42f16f8465	bds12.jpg	Ảnh nội thất hoặc góc nhìn khác của bất động sản, thể hiện chi tiết rõ nét.	2025-10-12 23:16:44.779754	0.00	binhthuong	anh
0096da64-99e6-4bd4-80cf-292ab8374527	08dc8dd4-1201-431c-b2f0-449695b97b1f	bds13.jpg	Ảnh mặt tiền hoặc không gian chính của bất động sản.	2025-10-12 23:16:44.779754	0.00	nang	anh
584273ba-1573-4a22-8394-9702dded93d4	90ec7744-8ac0-4e2c-a4a3-9b95776d46bc	bds14.jpg	Ảnh nội thất hoặc góc nhìn khác của bất động sản, thể hiện chi tiết rõ nét.	2025-10-12 23:16:44.779754	0.00	binhthuong	anh
c0a9df65-b3f7-4d32-8de1-c106b6dabae8	fabe2a78-1231-4543-827e-acfa16f6df1f	bds15.jpg	Ảnh mặt tiền hoặc không gian chính của bất động sản.	2025-10-12 23:16:44.779754	0.00	binhthuong	anh
08b203b5-68e5-4a51-99e9-057ef5a4d197	c7b5c0d3-7779-4299-a40b-4f104d4845ef	bds16.jpg	Ảnh nội thất hoặc góc nhìn khác của bất động sản, thể hiện chi tiết rõ nét.	2025-10-12 23:16:44.779754	0.00	binhthuong	anh
f8530455-bb07-491c-a4a9-3527ecc03407	fdfe9f43-a19a-42b9-97bc-a8e9fed6b24b	bds17.jpg	Ảnh mặt tiền hoặc không gian chính của bất động sản.	2025-10-12 23:16:44.779754	0.00	binhthuong	anh
052fd848-150f-435b-8293-e7c73a3ffdb8	fc080893-c646-4521-a9b7-c5541f166a58	bds18.jpg	Ảnh nội thất hoặc góc nhìn khác của bất động sản, thể hiện chi tiết rõ nét.	2025-10-12 23:16:44.779754	0.00	binhthuong	anh
5f4fd9d3-cd7b-4790-9bae-b05269b4366d	d5793b54-78d7-4033-8647-0f3ccccda434	bds19.jpg	Ảnh mặt tiền hoặc không gian chính của bất động sản.	2025-10-12 23:16:44.779754	0.00	binhthuong	anh
a5fa68e2-63f0-4e65-a6dc-471c4e754864	e92e8e4e-5ea2-4039-9d62-5a0e58e27d8f	bds20.jpg	Ảnh nội thất hoặc góc nhìn khác của bất động sản, thể hiện chi tiết rõ nét.	2025-10-12 23:16:44.779754	0.00	binhthuong	anh
fc1fb5e6-2f53-42e8-8be4-419bfdc6eda1	63ce5217-1344-4228-9455-6329b83585f3	bds21.jpg	Ảnh mặt tiền hoặc không gian chính của bất động sản.	2025-10-12 23:16:44.779754	0.00	binhthuong	anh
f5902b11-1a38-482c-810b-867c21790ee2	3049148d-1d50-45b0-a5d5-4012798dbc16	bds22.jpg	Ảnh nội thất hoặc góc nhìn khác của bất động sản, thể hiện chi tiết rõ nét.	2025-10-12 23:16:44.779754	0.00	binhthuong	anh
cf113a77-d653-4da5-9e03-3c5d2cd2ce8a	a14b3837-0b85-4403-bcb2-db93996fc185	bds23.jpg	Ảnh mặt tiền hoặc không gian chính của bất động sản.	2025-10-12 23:16:44.779754	0.00	binhthuong	anh
7025bf0b-c31f-41ee-a47a-c73117a1b5dd	e44bf5c0-ad3a-4846-af51-81c146eec74e	bds24.jpg	Ảnh nội thất hoặc góc nhìn khác của bất động sản, thể hiện chi tiết rõ nét.	2025-10-12 23:16:44.779754	0.00	binhthuong	anh
70a40652-1e62-43d2-8b2c-4bba14a69d29	11dd94ce-d64b-4bf2-bde5-e484fbfd5e5d	bds25.jpg	Ảnh mặt tiền hoặc không gian chính của bất động sản.	2025-10-12 23:16:44.779754	0.00	binhthuong	anh
1ca8c3b2-4ac9-432b-b96f-fc521b582734	d87c88bf-3bb0-44f4-ad78-a35bf01a2410	bds26.jpg	Ảnh nội thất hoặc góc nhìn khác của bất động sản, thể hiện chi tiết rõ nét.	2025-10-12 23:16:44.779754	0.00	binhthuong	anh
\.


--
-- TOC entry 5168 (class 0 OID 26620)
-- Dependencies: 243
-- Data for Name: hinh_anh_danh_gia_bds; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.hinh_anh_danh_gia_bds (id, id_dg_bds, url, mo_ta, kich_thuoc, ngay_tao) FROM stdin;
\.


--
-- TOC entry 5159 (class 0 OID 26351)
-- Dependencies: 234
-- Data for Name: hop_thoai; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.hop_thoai (id, da_khoa, da_xoa) FROM stdin;
916f1dfc-7e3d-44ef-844c-1835de5f4fdb	0	0
eafce559-0d9f-49be-acb0-59e79fc6323f	1	0
c69c1fa3-2693-489e-b122-7a6ffcb8a3fa	0	0
16173d0a-da65-4e57-b7c0-d7f39dfa8da4	0	1
9a684ae7-6944-427d-b6be-2cfd6eefc719	0	0
70d8fefb-d734-4cb0-8ceb-be224fcf98d7	0	0
877fbe29-75e9-41bc-9cec-ed3a431580a9	0	0
3cd7dd8a-50cf-4cc2-a8c6-1e99af001960	0	0
fd77a594-2c47-47ae-a446-17d363285f0d	0	0
90d56d82-f47d-4bd2-9cc6-99f5fac99a38	0	0
071ec056-7151-43be-88c8-1c9f973b6470	0	0
8242a594-3df9-4969-baef-0c76e2b3d176	1	0
87630b25-70af-48e7-b48b-6f5bdfd70c0f	0	0
497a2d1b-223b-4ae8-a15b-e17d4a7f059c	0	0
0f874310-2063-4a67-9895-ea53d793dc9e	0	0
\.


--
-- TOC entry 5146 (class 0 OID 17749)
-- Dependencies: 221
-- Data for Name: info_nguoi_dung; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.info_nguoi_dung (id, id_nguoi_dung, ho_ten, gioi_tinh, dia_chi, ngay_sinh, mo_ta) FROM stdin;
8d8df79c-dfe8-43b5-a80b-288a80de03b0	234a3664-f36a-442b-9bc2-a111ae14c1dc	Phan Phương Thảo	khac	Bến Tre	2001-01-09	Sinh viên trường Đại học Sư phạm Kỹ thuật Vĩnh Long
8721d0de-b7fb-4864-81a5-9671c090e0bd	59c9d360-7c64-45cf-b229-4e574b8c7ceb	Sơn Thanh Huy	nu	Hậu Giang	2002-11-11	Sinh viên trường Đại học Sư phạm Kỹ thuật Vĩnh Long
7b634d6f-b571-479f-8967-827057d3cc5f	85d0592f-15ed-4fb5-92ad-57c9af78c8aa	Lê Nhựt Linh	nu	Cần Thơ	2000-03-07	Sinh viên trường Đại học Sư phạm Kỹ thuật Vĩnh Long
4727458d-d6e5-4d27-b5f8-f64c775a4034	e2ce533c-9b1a-4bc6-bb84-440192b688d3	Bạch Lê Trọng Ân	nam	Cần Thơ	2000-08-28	Sinh viên trường Đại học Sư phạm Kỹ thuật Vĩnh Long
f7417722-c434-4f96-bb51-4816ebc2b220	886a1f9f-d5b6-4964-ae73-e1eb5a69139e	Diệp Thị Mỹ Duyên	khac	Trà Vinh	2004-09-26	Sinh viên trường Đại học Sư phạm Kỹ thuật Vĩnh Long
ef66acb7-797e-4ad2-b2cf-76a4bf4631fb	809f4af0-c6c4-4478-be5e-93c696302b7b	Châu Hoàng Việt	khac	Kiên Giang	2002-12-27	Sinh viên trường Đại học Sư phạm Kỹ thuật Vĩnh Long
e00143b0-a5d7-47e5-8973-736bbd3cc5ac	f9784f9e-7c83-4573-933c-335ed3b7e02d	Nguyễn Thái Vinh	khac	Vĩnh Long	2000-10-22	Sinh viên trường Đại học Sư phạm Kỹ thuật Vĩnh Long
f4be8218-6bdd-43cc-b753-957defa4ad8d	0e65ec6f-2792-48aa-a665-9b11b4796a0f	Ngô Thị Kiều Diễm	nu	Tiền Giang	2000-01-01	Sinh viên trường Đại học Sư phạm Kỹ thuật Vĩnh Long
7dd7c173-a402-4ca0-b590-fcf68984c273	0023b190-c734-45f6-8425-a1949e08e8d5	Võ Tấn An	khac	Bến Tre	2004-01-15	Sinh viên trường Đại học Sư phạm Kỹ thuật Vĩnh Long
a7b12731-d69f-4341-9854-36795649b69b	8e233604-c830-4df5-b863-29ec060327d3	Cao Thành Phát	khac	Vĩnh Long	2000-02-18	Sinh viên trường Đại học Sư phạm Kỹ thuật Vĩnh Long
527dafba-d652-41c2-ad42-ff3299f8b39b	1cabfcda-923b-400f-b05d-9b900516380c	Nguyễn Vũ Kha	nu	Bến Tre	2002-04-29	Sinh viên trường Đại học Sư phạm Kỹ thuật Vĩnh Long
0a113484-a0f8-4dbe-9b4d-daa5f4aea7b0	144ee4c9-bf24-438f-8a6b-e15dd0e71705	Lê Nguyễn Gia Đạt	khac	An Giang	2004-12-28	Sinh viên trường Đại học Sư phạm Kỹ thuật Vĩnh Long
c8a31790-a32f-4698-bf68-b391aa5ecf2e	ed9f5adb-413b-43cc-81f1-99f0ca57b321	Nguyễn Anh Tuấn	nu	Vĩnh Long	2003-07-04	Sinh viên trường Đại học Sư phạm Kỹ thuật Vĩnh Long
3c089c3b-22ec-4a65-972c-ec15bb17373d	53d15311-5c74-452a-8988-e9e1b683efad	Trần Đại Triệu Hào Anh	nu	Hậu Giang	2003-02-14	Sinh viên trường Đại học Sư phạm Kỹ thuật Vĩnh Long
8d510a19-09b6-4f90-b828-24f907bcfc7c	f37ca5eb-d199-46a7-9c75-70df9c9772bc	Nguyễn Tiến Khoa	nu	Sóc Trăng	2000-01-13	Sinh viên trường Đại học Sư phạm Kỹ thuật Vĩnh Long
13bddd7c-dd13-4757-abc9-e1009320fb20	9e4364ac-289e-4f4a-b18f-57bb5d05a336	Lê Thành Lợi	khac	Kiên Giang	2000-07-23	Sinh viên trường Đại học Sư phạm Kỹ thuật Vĩnh Long
0506de9b-150c-4be2-a12f-962c70aa8a5f	30f7a140-9e0f-4763-8c73-d0ba585e0584	Lê Ngọc Quỳnh	khac	Hậu Giang	2004-09-02	Sinh viên trường Đại học Sư phạm Kỹ thuật Vĩnh Long
de2253ad-7f46-4436-a753-9692770f1f79	34e0c86a-a19f-4042-bbef-371a64693ba1	Ngô Văn Hoàng Huy	nu	Đồng Tháp	2000-06-15	Sinh viên trường Đại học Sư phạm Kỹ thuật Vĩnh Long
254a63a4-fc44-41b2-aa91-7abd0600201d	f5f4022f-eccc-48e6-a435-1a9b1cdb3f3a	Nguyễn Ngọc Hưởng	khac	Tiền Giang	2004-10-09	Sinh viên trường Đại học Sư phạm Kỹ thuật Vĩnh Long
ce8ddbfd-10fc-4fe4-8ee6-d23d9b6391c2	5ee334f1-bd31-41b7-8672-df8903f2747a	Hồ Gia Huy	nu	Sóc Trăng	2001-12-20	Sinh viên trường Đại học Sư phạm Kỹ thuật Vĩnh Long
8e6e36ac-88cd-4331-a485-d174661e7d1b	79039562-eda7-4a5b-94cb-0cca2b078742	Phạm Thị Thảo Nguyên	nu	Sóc Trăng	2004-01-27	Sinh viên trường Đại học Sư phạm Kỹ thuật Vĩnh Long
fad5b83a-0612-420e-bc37-c093ab94eaf9	fb9d3af2-aeef-46b1-a91c-438cf099a636	Dương Anh Văn	nam	Kiên Giang	2002-10-26	Sinh viên trường Đại học Sư phạm Kỹ thuật Vĩnh Long
5429d8c8-e6ad-4658-a6ae-99f7e812e1f0	8fe15279-afda-4edc-bd9c-c2c7307df4c4	Huỳnh Khã Hân	khac	Bến Tre	2000-09-21	Sinh viên trường Đại học Sư phạm Kỹ thuật Vĩnh Long
590b8ad1-2e07-468a-af6f-d85b14227c96	9976c99e-d95f-4e55-92ed-195d29be7ba6	Võ Hoàng Thái	khac	Bến Tre	2000-04-09	Sinh viên trường Đại học Sư phạm Kỹ thuật Vĩnh Long
83d958be-1a03-463a-81c6-bacf36c2352d	07121b39-ffae-4f5a-be25-d9af117b1a8c	Võ Thị Như Quỳnh	khac	Tiền Giang	2004-07-30	Sinh viên trường Đại học Sư phạm Kỹ thuật Vĩnh Long
19fd1d7e-0bb5-472d-b72e-acb2dd6fc5b9	db5ac513-6077-4717-832c-ae37eda2c1d1	Đoàn Thảo Như	nu	Sóc Trăng	2003-08-19	Sinh viên trường Đại học Sư phạm Kỹ thuật Vĩnh Long
2af117ce-70c0-41fe-9621-763511ae23e6	f64c8b9b-8b2a-4c16-87a0-9354988951d9	Phạm Thanh Thảo	nam	Cần Thơ	2001-03-09	Sinh viên trường Đại học Sư phạm Kỹ thuật Vĩnh Long
77c95831-fb3a-4d30-a619-f461d560ae47	ab903ef8-936c-425a-ad3a-68b69aafa9f1	Nguyễn Đức Thắng	nam	Kiên Giang	2004-04-02	Sinh viên trường Đại học Sư phạm Kỹ thuật Vĩnh Long
0923e7a9-6188-439a-8162-27555b947e46	186acf33-877d-4963-902f-35bbfa0d6ecf	Hồ Tuấn Khanh	nu	Sóc Trăng	2003-08-15	Sinh viên trường Đại học Sư phạm Kỹ thuật Vĩnh Long
7143110f-9ade-48e4-936a-17a38cc00cec	7765d2a9-2e1c-41bd-86ad-c63e3f4cd079	Trương Quốc Đặng	nu	Tiền Giang	2003-09-01	Sinh viên trường Đại học Sư phạm Kỹ thuật Vĩnh Long
8c844da3-7088-44c5-aaa0-dc4b32ee4355	b11d78c1-73a5-4d18-8002-c505ef2e9986	Hàng Lê Trung Thiện	khac	Vĩnh Long	2002-02-25	Sinh viên trường Đại học Sư phạm Kỹ thuật Vĩnh Long
d9966d29-c161-42c0-b5dd-5525d8cd6206	29e897be-0b07-4051-bf51-04ee0286f394	Phạm Trần Ngọc Hân	khac	Sóc Trăng	2003-04-22	Sinh viên trường Đại học Sư phạm Kỹ thuật Vĩnh Long
c43bc793-ca8d-47ae-8fe7-d4f281a3e59c	7c68c28a-7d22-4489-bf78-09771d1af05a	Nguyễn Mỹ Diền	nam	Đồng Tháp	2004-07-18	Sinh viên trường Đại học Sư phạm Kỹ thuật Vĩnh Long
1e4ba282-87f1-4fc8-bceb-01768a3d9c9a	78ed62c4-70ed-42c1-898b-6e400c6def37	Bành Thế Nam	khac	Bến Tre	2002-09-04	Sinh viên trường Đại học Sư phạm Kỹ thuật Vĩnh Long
778b9b4b-889f-4f6c-8bc2-84d3df3359ef	90e4f116-b60a-4796-8f0d-26289f7dae50	Nguyễn Tuấn Kiệt	nam	Hậu Giang	2001-04-08	Sinh viên trường Đại học Sư phạm Kỹ thuật Vĩnh Long
9ad232e1-a2a7-4c1e-85b3-bb89f1e478a2	d6a49ad8-fdb7-4649-88c3-5759befcc4a5	Nguyễn Thị Ngọc Huyền	nam	Hậu Giang	2000-07-16	Sinh viên trường Đại học Sư phạm Kỹ thuật Vĩnh Long
b2cdc2dd-383c-45e5-a352-aaead9170275	a78c47f4-b1c4-4313-b90a-8938f6ba8cac	Phan Thị Thùy Trang	khac	Bến Tre	2000-12-25	Sinh viên trường Đại học Sư phạm Kỹ thuật Vĩnh Long
eabfec91-7475-4965-9f32-e4946069af7b	2a11d082-fc6e-4713-9738-8650e95844f4	Nguyễn Thị Như Ý	khac	Tiền Giang	2001-10-25	Sinh viên trường Đại học Sư phạm Kỹ thuật Vĩnh Long
036009fa-bbd1-4831-879e-0810fe4e9be5	17716784-ec18-43b4-b8cb-a784d1127421	Lê Minh Nhựt	nu	Đồng Tháp	2000-11-28	Sinh viên trường Đại học Sư phạm Kỹ thuật Vĩnh Long
569162ed-f245-4b13-b053-ba1c945552ad	92d1b032-a891-4176-87dd-cdeab7473d61	Hồ Ngọc Gấm	nu	Cần Thơ	2001-12-11	Sinh viên trường Đại học Sư phạm Kỹ thuật Vĩnh Long
bfa6e133-7058-4cfb-acfc-e1b80f7801c6	2614049e-5760-4853-b02a-5df11a4f947a	Nguyễn Thanh Nam	nu	Cần Thơ	2003-11-06	Sinh viên trường Đại học Sư phạm Kỹ thuật Vĩnh Long
5877cbe2-5022-498c-be93-c8aa67c9f321	f9857b4a-d49f-45c5-850e-d5b6d0d3eb55	Nguyễn Hoài Phong	nam	An Giang	2002-04-02	Sinh viên trường Đại học Sư phạm Kỹ thuật Vĩnh Long
14d94011-d47c-415b-aefd-9df2a61c9e35	e66b4329-be12-44ac-915b-7a7b761433c0	Đặng Thị Thúy Ngân	khac	Cần Thơ	2004-11-30	Sinh viên trường Đại học Sư phạm Kỹ thuật Vĩnh Long
52948ea6-2e2a-41ff-8ad4-8a066d45080a	34a86257-d43c-44e7-88ab-bccabdd5c644	Mai Thị Ngọc Trâm	khac	Hậu Giang	2002-07-21	Sinh viên trường Đại học Sư phạm Kỹ thuật Vĩnh Long
6e28e879-55ed-4e84-922c-17d2647ad9f3	fe1dcc48-4a84-45a7-8c38-610f6686f0f7	Dương Trần Phi Yến	nu	Hậu Giang	2001-11-10	Sinh viên trường Đại học Sư phạm Kỹ thuật Vĩnh Long
a2efd487-b1e9-40cd-a84b-1c6d61a74dff	ce1ead4c-e4ef-4712-979e-6d358511f4cd	Nguyễn Trần Cẩm Nhung	nu	Vĩnh Long	2000-01-05	Sinh viên trường Đại học Sư phạm Kỹ thuật Vĩnh Long
0316bcee-c235-48eb-9b8b-ab9e7682fcf2	7efdc816-e04f-4493-9997-6b0766bae0db	Lê Văn Khanh	nam	Trà Vinh	2001-07-11	Sinh viên trường Đại học Sư phạm Kỹ thuật Vĩnh Long
f3bf26fe-b92a-4ba9-953e-113ab7e57910	3ebc4930-2923-4085-b9ca-192f087ba6bf	Thạch Chí Hiếu	nu	An Giang	2003-10-27	Sinh viên trường Đại học Sư phạm Kỹ thuật Vĩnh Long
2a89dd64-f0c4-4446-8878-e177e9ec3219	3551fa73-fadc-4ef2-a1a3-6929a8e65c11	Huỳnh Trường Giang	nu	Hậu Giang	2002-10-18	Sinh viên trường Đại học Sư phạm Kỹ thuật Vĩnh Long
b383a2f4-3cf1-42b6-ac53-0b569d28f87f	0216dbde-2061-4841-ad42-0445ffd0692d	Trịnh Khắc Nhựt	khac	Hậu Giang	2002-11-29	Sinh viên trường Đại học Sư phạm Kỹ thuật Vĩnh Long
ee27337b-b244-49d9-8753-bbe7c67d2831	fce8f322-59a9-4711-88ae-8c10c7d87f21	Trương Minh Thư	nu	An Giang	2001-11-28	Sinh viên trường Đại học Sư phạm Kỹ thuật Vĩnh Long
d1105e28-5b09-44fc-9651-63ab92ed86cf	d20c59af-cbf7-47b5-aec4-90b6b119a816	Nguyễn Minh Tuyến	khac	Trà Vinh	2001-04-08	Sinh viên trường Đại học Sư phạm Kỹ thuật Vĩnh Long
0c78996b-640e-4c35-8bb5-ca69269638e1	eb111e8c-1671-40a1-a970-64e683945c90	Nguyễn Thị Hồng Thi	nam	Tiền Giang	2004-05-06	Sinh viên trường Đại học Sư phạm Kỹ thuật Vĩnh Long
b16de48e-98c3-4a63-b6e6-1dcb91e2928f	2dae71cc-a4e8-487b-be5a-3f65bdd9205d	Nguyễn Tuấn Anh	khac	Hậu Giang	2001-09-13	Sinh viên trường Đại học Sư phạm Kỹ thuật Vĩnh Long
f8f3fba1-479b-4f62-ae5d-cd6b6571e95a	bc2ed64f-8ae4-4637-9632-75c5af63066c	Trần Huỳnh Viễn Hưng	khac	Tiền Giang	2000-04-19	Sinh viên trường Đại học Sư phạm Kỹ thuật Vĩnh Long
459b8d0f-ca61-4433-8448-b04e1130d0e6	d6207506-d2af-4427-b599-7c51661f3bdd	Hồ Tuấn Khanh	nam	Đồng Tháp	2003-09-19	Sinh viên trường Đại học Sư phạm Kỹ thuật Vĩnh Long
\.


--
-- TOC entry 5162 (class 0 OID 26410)
-- Dependencies: 237
-- Data for Name: ke_hoach_thanh_toan; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.ke_hoach_thanh_toan (id, id_giao_dich, tong_gia_tri, so_tien_da_tt, trang_thai_tt) FROM stdin;
f906979c-9b5f-4a26-b9d8-d164c23b8f53	c4ac28b5-4c64-4d11-8acd-ebc1d10209ea	45629043180.00	13872635455.00	dangthanhtoan
4e4cb076-610d-4607-9675-7f35eeffe903	2b6bf62e-c6f3-4dfc-89dc-c8ccfaf3ed7f	33602308761.00	24714966896.00	dangthanhtoan
1dc07ab0-bc7b-4528-9891-3cb1e5e52f56	37b73648-c247-4551-8ce8-0418a1b1677f	30076163265.00	26690925474.00	dangthanhtoan
194fccc6-347f-4cb1-b58f-5b386844b185	39dcb4a9-abf7-4468-8dea-a889a0d97c00	20005815795.00	20005815795.00	hoantat
5947751a-d5d0-4ccb-b320-3c8642bb1bec	c0c31be6-927e-440e-91fe-f22a8fcb04dc	1423665829.00	503148666.00	dangthanhtoan
be1661df-311e-44b7-bb5c-ff89f6368f35	672a27b6-326f-4b3e-9eaf-eb66de5b736d	9154194523.00	9154194523.00	hoantat
64e123c9-bccb-4d4b-9cd9-4b70ece5a6d3	353c2a84-a191-4388-8012-6b85bd0ae09b	24728149872.00	0.00	chuathanhtoan
31bbd75e-5992-4722-a9bf-f7ebe8012fd1	f48766c9-ce86-4fca-835c-eb2c0946d078	16691803274.00	8384907111.00	dangthanhtoan
79db726b-eea7-49a9-9d36-ceb5edde0110	83f48790-fa7b-42cc-9c14-6b311c5b1848	24398409219.00	0.00	chuathanhtoan
81516373-95ee-402d-94f0-9bc60e948c03	33cb6323-6170-4bf6-b094-56a65c10c26e	13073968090.00	0.00	chuathanhtoan
9a26aa45-1a46-4c84-bb92-3a30a54f308b	1b1587d1-e2f9-45eb-9586-14d18cce3a4d	29569124181.00	29569124181.00	hoantat
51b012cd-85a6-4cf4-a67f-afd67ce9495f	cefc79a8-688f-4dad-b23a-7db0c7a03d46	27407920345.00	0.00	chuathanhtoan
d4ddfe18-f0be-4c3a-ba29-737934c4ba6f	f4070b66-a255-4afe-b715-d15fd7db21b0	27805612348.00	16638260174.00	dangthanhtoan
45076568-618e-472b-866d-c796176e387c	bc8aac5a-364a-4d33-a5a7-2d864b4d33a7	45823111391.00	30261811828.00	dangthanhtoan
5ea17aa5-f107-40f4-af12-bc55fa99a82b	cd298262-e209-4be3-a1c4-7409a7a437ed	27118010313.00	27118010313.00	hoantat
7083ca7a-0ad4-45e4-82ed-50d2337a089c	dc4f87ef-df71-4e1d-80d0-a4c1afc7c21b	26741037936.00	26741037936.00	hoantat
c572719b-acb1-4ae3-8104-04b95ceb3c09	d1faadb3-a43b-4279-a9ad-d179eff5c438	32094850095.00	20583453280.00	dangthanhtoan
497ef47d-c4ca-4f8e-a8a0-79ba554f3c26	63ed9dca-eeb9-49b3-b24d-af65330f4c44	11787103101.00	7016589443.00	dangthanhtoan
cf40b9fe-49fa-4e2e-9955-1bac380e937a	5851082c-6a68-4147-883d-36349f874f10	39993767253.00	0.00	chuathanhtoan
f4fb6d80-b9d0-4829-9aef-deb27b53ddad	29b748e8-6859-499f-978c-1c35c867edeb	10956023516.00	10956023516.00	hoantat
18dafdc8-5a34-41f8-a438-cea6d77fb726	84769419-daa9-42d4-9fa7-76982a3b6a99	5546564266.00	4676241934.00	dangthanhtoan
fb473225-51a3-4db8-9fae-34767fd1e49c	aba97ba5-f371-4f57-a749-4c0f33064f1f	32198754804.00	0.00	chuathanhtoan
93901c57-b0a3-40fd-9616-b13eef7b6d38	8fb9499d-702b-4674-900f-f8171a255b9c	36332428038.00	19501290747.00	dangthanhtoan
8a99538c-3b71-44b1-8b99-977a76f35b70	6006c52a-0c2a-4e27-b5c4-0ba4dfb586e5	18334477590.00	7823560710.00	dangthanhtoan
063464e7-967f-4496-8221-398fc9c0fa73	f9d6e97f-3755-47b1-8752-dd1c4c57ec5f	11629017384.00	2245866577.00	dangthanhtoan
\.


--
-- TOC entry 5165 (class 0 OID 26494)
-- Dependencies: 240
-- Data for Name: lich_su; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.lich_su (id, id_bat_dong_san, id_nguoi_dung, hanh_dong, ghi_chu, ngay_tao) FROM stdin;
\.


--
-- TOC entry 5152 (class 0 OID 17885)
-- Dependencies: 227
-- Data for Name: lich_su_xac_thuc; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.lich_su_xac_thuc (id, id_nguoi_dung, loai_su_kien, thoi_gian_bat_dau, thoi_gian_ket_thuc, dia_chi_ip, user_agent, ghi_chu) FROM stdin;
0815266b-dd11-42a3-8320-e5dd76a90ff3	2dae71cc-a4e8-487b-be5a-3f65bdd9205d	dangnhap	2025-10-12 19:55:37.477487	2025-10-12 21:21:08.01394	172.18.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36	dangnhap_thanhcong
7a5cc670-0e45-4d29-95a1-48e0654e3ab4	2dae71cc-a4e8-487b-be5a-3f65bdd9205d	dangnhap	2025-10-12 21:21:19.260327	\N	172.18.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36	dangnhap_thanhcong
7424e58d-1a93-4f4e-a3fb-3c4578713920	2dae71cc-a4e8-487b-be5a-3f65bdd9205d	dangnhap	2025-10-13 08:55:15.90147	\N	172.18.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36	dangnhap_thatbai_saimatkhau
e8ef90c4-cca4-4b06-ac61-b918c77e97a5	2dae71cc-a4e8-487b-be5a-3f65bdd9205d	dangnhap	2025-10-13 08:55:28.661486	2025-10-13 18:44:17.767892	172.18.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36	dangnhap_thanhcong
2349acea-b143-44f6-8bd0-baaf81531dd7	2dae71cc-a4e8-487b-be5a-3f65bdd9205d	dangnhap	2025-10-13 18:44:59.938736	\N	172.18.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36	dangnhap_thanhcong
d0ade762-8013-4c3f-9e61-7a3ff4ceef3b	30f7a140-9e0f-4763-8c73-d0ba585e0584	dangnhap	2025-10-13 19:46:14.980093	\N	172.18.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36	dangnhap_thatbai_bivohieuhoa
5425bb92-03ce-487d-90ac-ef7a2944d834	30f7a140-9e0f-4763-8c73-d0ba585e0584	dangnhap	2025-10-13 19:46:49.79369	\N	172.18.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36	dangnhap_thanhcong
3d122fb8-141c-41c1-934a-0adfc9a2c180	7765d2a9-2e1c-41bd-86ad-c63e3f4cd079	dangnhap	2025-10-14 05:23:31.674155	\N	172.18.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36	dangnhap_thatbai_saimatkhau
df8ac9a7-2af9-4966-aa74-0a91c97c472a	7765d2a9-2e1c-41bd-86ad-c63e3f4cd079	dangnhap	2025-10-14 05:23:46.518784	2025-10-14 05:24:20.143539	172.18.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36	dangnhap_thanhcong
42d21ebd-d65a-4670-b821-869746969612	17716784-ec18-43b4-b8cb-a784d1127421	dangnhap	2025-10-14 05:24:32.569481	2025-10-14 05:27:09.200346	172.18.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36	dangnhap_thanhcong
98d39bb4-0941-4f6a-959f-2ea4ec56afca	2dae71cc-a4e8-487b-be5a-3f65bdd9205d	dangnhap	2025-10-14 05:27:20.661706	\N	172.18.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36	dangnhap_thanhcong
8a7b4ebb-875a-4c9b-9eed-4bb2c3702110	0023b190-c734-45f6-8425-a1949e08e8d5	dangnhap	2025-10-15 00:22:32.828123	\N	172.18.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36	dangnhap_thanhcong
2b5f6209-08dc-43c2-baa7-af58d45619e3	2dae71cc-a4e8-487b-be5a-3f65bdd9205d	dangnhap	2025-10-15 09:28:38.699566	\N	172.18.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36	dangnhap_thanhcong
\.


--
-- TOC entry 5169 (class 0 OID 26636)
-- Dependencies: 244
-- Data for Name: lich_trinh; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.lich_trinh (id, id_khach_hang, id_moi_gioi, thoi_gian_bat_dau, thoi_gian_ket_thuc, trang_thai, ghi_chu) FROM stdin;
4abeaa41-84b6-465a-ab28-f62332151689	809f4af0-c6c4-4478-be5e-93c696302b7b	d20c59af-cbf7-47b5-aec4-90b6b119a816	2025-10-19 15:34:53.29366+07	2025-10-19 16:34:53.29366+07	daxacnhan	Lịch hẹn đã được xác nhận qua điện thoại với khách hàng.
dcd00be6-e9f3-44a6-ad90-7de50a5e1ede	ed9f5adb-413b-43cc-81f1-99f0ca57b321	90e4f116-b60a-4796-8f0d-26289f7dae50	2025-11-01 08:34:53.29366+07	2025-11-01 09:34:53.29366+07	choxacnhan	Đang chờ xác nhận từ cả hai bên.
3e0e1b63-673b-433b-880c-326c1f1c5e2a	8e233604-c830-4df5-b863-29ec060327d3	29e897be-0b07-4051-bf51-04ee0286f394	2025-10-27 11:34:53.29366+07	2025-10-27 12:34:53.29366+07	daxacnhan	Lịch hẹn đã được xác nhận qua điện thoại với khách hàng.
45942da2-f83c-43f8-a303-cb38c63105c5	fb9d3af2-aeef-46b1-a91c-438cf099a636	78ed62c4-70ed-42c1-898b-6e400c6def37	2025-10-23 13:34:53.29366+07	2025-10-23 14:34:53.29366+07	choxacnhan	Đang chờ xác nhận từ cả hai bên.
b5d806a5-df9b-4239-90a3-da3537781010	3ebc4930-2923-4085-b9ca-192f087ba6bf	1cabfcda-923b-400f-b05d-9b900516380c	2025-11-11 11:34:53.29366+07	2025-11-11 12:34:53.29366+07	dahuy	Khách hàng báo bận đột xuất, sẽ liên hệ lại sau.
b47e1d92-4188-42ca-b220-d3ff9d630ff1	f64c8b9b-8b2a-4c16-87a0-9354988951d9	144ee4c9-bf24-438f-8a6b-e15dd0e71705	2025-10-24 06:34:53.29366+07	2025-10-24 07:34:53.29366+07	dahuy	Khách hàng báo bận đột xuất, sẽ liên hệ lại sau.
3890fa33-b23c-4971-adfb-b7954f1d298c	07121b39-ffae-4f5a-be25-d9af117b1a8c	fe1dcc48-4a84-45a7-8c38-610f6686f0f7	2025-10-24 07:34:53.29366+07	2025-10-24 08:34:53.29366+07	choxacnhan	Đang chờ xác nhận từ cả hai bên.
7d5f956c-3e2e-43dc-ae8c-c8cd3fae1012	79039562-eda7-4a5b-94cb-0cca2b078742	59c9d360-7c64-45cf-b229-4e574b8c7ceb	2025-10-18 12:34:53.29366+07	2025-10-18 13:34:53.29366+07	daxacnhan	Lịch hẹn đã được xác nhận qua điện thoại với khách hàng.
9456c5ab-48f6-4e73-ad6a-dd8ac67cbaca	5ee334f1-bd31-41b7-8672-df8903f2747a	b11d78c1-73a5-4d18-8002-c505ef2e9986	2025-11-12 13:34:53.29366+07	2025-11-12 14:34:53.29366+07	dahuy	Khách hàng báo bận đột xuất, sẽ liên hệ lại sau.
beec2b42-6b9d-453f-b683-e0c480195a00	234a3664-f36a-442b-9bc2-a111ae14c1dc	3551fa73-fadc-4ef2-a1a3-6929a8e65c11	2025-10-27 07:34:53.29366+07	2025-10-27 08:34:53.29366+07	dahuy	Khách hàng báo bận đột xuất, sẽ liên hệ lại sau.
01edf24d-5413-465c-8337-3cfb86f752e1	34a86257-d43c-44e7-88ab-bccabdd5c644	7efdc816-e04f-4493-9997-6b0766bae0db	2025-10-14 08:34:53.29366+07	2025-10-14 09:34:53.29366+07	choxacnhan	Đang chờ xác nhận từ cả hai bên.
44d13a1a-4593-4589-8c4b-8938596091aa	a78c47f4-b1c4-4313-b90a-8938f6ba8cac	eb111e8c-1671-40a1-a970-64e683945c90	2025-10-26 08:34:53.29366+07	2025-10-26 09:34:53.29366+07	choxacnhan	Đang chờ xác nhận từ cả hai bên.
af3c08ac-5d0a-4bdc-9b1d-4288a012d78e	e2ce533c-9b1a-4bc6-bb84-440192b688d3	f5f4022f-eccc-48e6-a435-1a9b1cdb3f3a	2025-11-02 11:34:53.29366+07	2025-11-02 12:34:53.29366+07	daxacnhan	Lịch hẹn đã được xác nhận qua điện thoại với khách hàng.
a1ff0722-aeff-4df2-909e-51d374e4bc0f	d6207506-d2af-4427-b599-7c51661f3bdd	30f7a140-9e0f-4763-8c73-d0ba585e0584	2025-10-14 13:34:53.29366+07	2025-10-14 14:34:53.29366+07	dahuy	Khách hàng báo bận đột xuất, sẽ liên hệ lại sau.
9a604d38-d4a5-4509-bf2e-52b4e3624666	17716784-ec18-43b4-b8cb-a784d1127421	9e4364ac-289e-4f4a-b18f-57bb5d05a336	2025-10-19 13:34:53.29366+07	2025-10-19 14:34:53.29366+07	daxacnhan	Lịch hẹn đã được xác nhận qua điện thoại với khách hàng.
5a1ed78a-2658-495f-adc1-a8654484a8f3	92d1b032-a891-4176-87dd-cdeab7473d61	f9857b4a-d49f-45c5-850e-d5b6d0d3eb55	2025-10-16 09:34:53.29366+07	2025-10-16 10:34:53.29366+07	choxacnhan	Đang chờ xác nhận từ cả hai bên.
36d5f94c-d3dc-4e46-b7f5-3813152bc03d	bc2ed64f-8ae4-4637-9632-75c5af63066c	886a1f9f-d5b6-4964-ae73-e1eb5a69139e	2025-10-23 15:34:53.29366+07	2025-10-23 16:34:53.29366+07	dahuy	Khách hàng báo bận đột xuất, sẽ liên hệ lại sau.
2da4d64e-cb0c-40c3-81fd-81731af7e69c	2a11d082-fc6e-4713-9738-8650e95844f4	db5ac513-6077-4717-832c-ae37eda2c1d1	2025-11-01 07:34:53.29366+07	2025-11-01 08:34:53.29366+07	choxacnhan	Đang chờ xác nhận từ cả hai bên.
88a9350c-cd77-45d6-be60-a99ab2fe68a7	809f4af0-c6c4-4478-be5e-93c696302b7b	d20c59af-cbf7-47b5-aec4-90b6b119a816	2025-11-08 09:34:53.29366+07	2025-11-08 10:34:53.29366+07	dahuy	Khách hàng báo bận đột xuất, sẽ liên hệ lại sau.
8c493b2c-0a0f-4d15-b908-12a7853eae80	ed9f5adb-413b-43cc-81f1-99f0ca57b321	90e4f116-b60a-4796-8f0d-26289f7dae50	2025-10-25 13:34:53.29366+07	2025-10-25 14:34:53.29366+07	choxacnhan	Đang chờ xác nhận từ cả hai bên.
4c2196fe-320c-4ee8-b8cf-3b4a27f7c5c1	8e233604-c830-4df5-b863-29ec060327d3	29e897be-0b07-4051-bf51-04ee0286f394	2025-10-20 11:34:53.29366+07	2025-10-20 12:34:53.29366+07	daxacnhan	Lịch hẹn đã được xác nhận qua điện thoại với khách hàng.
41f349fe-42a2-457c-9029-ac84e7cd7ec7	f64c8b9b-8b2a-4c16-87a0-9354988951d9	144ee4c9-bf24-438f-8a6b-e15dd0e71705	2025-11-05 10:34:53.29366+07	2025-11-05 11:34:53.29366+07	daxacnhan	Lịch hẹn đã được xác nhận qua điện thoại với khách hàng.
63856c0f-5a2f-4f99-8c62-75ca7dada3a9	07121b39-ffae-4f5a-be25-d9af117b1a8c	fe1dcc48-4a84-45a7-8c38-610f6686f0f7	2025-10-27 11:34:53.29366+07	2025-10-27 12:34:53.29366+07	daxacnhan	Lịch hẹn đã được xác nhận qua điện thoại với khách hàng.
09c7ca9d-4194-41be-a280-cc42a4a104be	fb9d3af2-aeef-46b1-a91c-438cf099a636	78ed62c4-70ed-42c1-898b-6e400c6def37	2025-10-23 13:34:53.29366+07	2025-10-23 17:34:53.29366+07	dahuy	Khách hàng báo bận đột xuất, sẽ liên hệ lại sau.
376b0a94-5268-44a0-a2a1-ec056479df14	3ebc4930-2923-4085-b9ca-192f087ba6bf	78ed62c4-70ed-42c1-898b-6e400c6def37	2025-10-23 13:34:53.29366+07	2025-10-23 16:34:53.29366+07	dahuy	Khách hàng báo bận đột xuất, sẽ liên hệ lại sau.
\.


--
-- TOC entry 5144 (class 0 OID 17710)
-- Dependencies: 219
-- Data for Name: nguoi_dung; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.nguoi_dung (id, ten_dang_nhap, mat_khau, email, so_dt, avt, trang_thai, hoat_dong, ngay_tao) FROM stdin;
0e65ec6f-2792-48aa-a665-9b11b4796a0f	diemnt	$argon2id$v=19$m=65536,t=3,p=4$l4ptltIvcKNyDeuOZ2dfDg$XBg6sR18fXH+1Uj7DcfbXg08dz6tb61tko1U+JyIzIE	21004118demo@st.vlute.edu.vn	0951424506	avt.png	khoa	offline	2025-10-12 19:47:07.485764
0023b190-c734-45f6-8425-a1949e08e8d5	anvt	$argon2id$v=19$m=65536,t=3,p=4$l4ptltIvcKNyDeuOZ2dfDg$XBg6sR18fXH+1Uj7DcfbXg08dz6tb61tko1U+JyIzIE	21004212demo@st.vlute.edu.vn	0956189563	avt.png	danghoatdong	online	2025-10-12 19:47:07.485764
8e233604-c830-4df5-b863-29ec060327d3	phatct	$argon2id$v=19$m=65536,t=3,p=4$l4ptltIvcKNyDeuOZ2dfDg$XBg6sR18fXH+1Uj7DcfbXg08dz6tb61tko1U+JyIzIE	21004286demo@st.vlute.edu.vn	0915450242	avt.png	chuakichhoat	offline	2025-10-12 19:47:07.485764
1cabfcda-923b-400f-b05d-9b900516380c	khanv	$argon2id$v=19$m=65536,t=3,p=4$l4ptltIvcKNyDeuOZ2dfDg$XBg6sR18fXH+1Uj7DcfbXg08dz6tb61tko1U+JyIzIE	21004292demo@st.vlute.edu.vn	0961646024	avt.png	khoa	online	2025-10-12 19:47:07.485764
144ee4c9-bf24-438f-8a6b-e15dd0e71705	datln	$argon2id$v=19$m=65536,t=3,p=4$l4ptltIvcKNyDeuOZ2dfDg$XBg6sR18fXH+1Uj7DcfbXg08dz6tb61tko1U+JyIzIE	22004001demo@st.vlute.edu.vn	0928781845	avt.png	danghoatdong	offline	2025-10-12 19:47:07.485764
ed9f5adb-413b-43cc-81f1-99f0ca57b321	tuanna	$argon2id$v=19$m=65536,t=3,p=4$l4ptltIvcKNyDeuOZ2dfDg$XBg6sR18fXH+1Uj7DcfbXg08dz6tb61tko1U+JyIzIE	22004004demo@st.vlute.edu.vn	0945432507	avt.png	chuakichhoat	online	2025-10-12 19:47:07.485764
53d15311-5c74-452a-8988-e9e1b683efad	anhtd	$argon2id$v=19$m=65536,t=3,p=4$l4ptltIvcKNyDeuOZ2dfDg$XBg6sR18fXH+1Uj7DcfbXg08dz6tb61tko1U+JyIzIE	22004009demo@st.vlute.edu.vn	0932827377	avt.png	khoa	offline	2025-10-12 19:47:07.485764
f37ca5eb-d199-46a7-9c75-70df9c9772bc	khoant	$argon2id$v=19$m=65536,t=3,p=4$l4ptltIvcKNyDeuOZ2dfDg$XBg6sR18fXH+1Uj7DcfbXg08dz6tb61tko1U+JyIzIE	22004011demo@st.vlute.edu.vn	0921122047	avt.png	danghoatdong	online	2025-10-12 19:47:07.485764
9e4364ac-289e-4f4a-b18f-57bb5d05a336	loilt	$argon2id$v=19$m=65536,t=3,p=4$l4ptltIvcKNyDeuOZ2dfDg$XBg6sR18fXH+1Uj7DcfbXg08dz6tb61tko1U+JyIzIE	22004012demo@st.vlute.edu.vn	0974003433	avt.png	chuakichhoat	offline	2025-10-12 19:47:07.485764
34e0c86a-a19f-4042-bbef-371a64693ba1	huynv	$argon2id$v=19$m=65536,t=3,p=4$l4ptltIvcKNyDeuOZ2dfDg$XBg6sR18fXH+1Uj7DcfbXg08dz6tb61tko1U+JyIzIE	22004016demo@st.vlute.edu.vn	0939255514	avt.png	danghoatdong	offline	2025-10-12 19:47:07.485764
f5f4022f-eccc-48e6-a435-1a9b1cdb3f3a	huongnn	$argon2id$v=19$m=65536,t=3,p=4$l4ptltIvcKNyDeuOZ2dfDg$XBg6sR18fXH+1Uj7DcfbXg08dz6tb61tko1U+JyIzIE	22004020demo@st.vlute.edu.vn	0997483742	avt.png	chuakichhoat	online	2025-10-12 19:47:07.485764
5ee334f1-bd31-41b7-8672-df8903f2747a	huyhg	$argon2id$v=19$m=65536,t=3,p=4$l4ptltIvcKNyDeuOZ2dfDg$XBg6sR18fXH+1Uj7DcfbXg08dz6tb61tko1U+JyIzIE	22004027demo@st.vlute.edu.vn	0969846670	avt.png	khoa	offline	2025-10-12 19:47:07.485764
79039562-eda7-4a5b-94cb-0cca2b078742	nguyenpt	$argon2id$v=19$m=65536,t=3,p=4$l4ptltIvcKNyDeuOZ2dfDg$XBg6sR18fXH+1Uj7DcfbXg08dz6tb61tko1U+JyIzIE	22004044demo@st.vlute.edu.vn	0934549475	avt.png	danghoatdong	online	2025-10-12 19:47:07.485764
fb9d3af2-aeef-46b1-a91c-438cf099a636	vanda	$argon2id$v=19$m=65536,t=3,p=4$l4ptltIvcKNyDeuOZ2dfDg$XBg6sR18fXH+1Uj7DcfbXg08dz6tb61tko1U+JyIzIE	22004046demo@st.vlute.edu.vn	0994955634	avt.png	chuakichhoat	offline	2025-10-12 19:47:07.485764
8fe15279-afda-4edc-bd9c-c2c7307df4c4	hanhk	$argon2id$v=19$m=65536,t=3,p=4$l4ptltIvcKNyDeuOZ2dfDg$XBg6sR18fXH+1Uj7DcfbXg08dz6tb61tko1U+JyIzIE	22004048demo@st.vlute.edu.vn	0983523870	avt.png	khoa	online	2025-10-12 19:47:07.485764
9976c99e-d95f-4e55-92ed-195d29be7ba6	thaivh	$argon2id$v=19$m=65536,t=3,p=4$l4ptltIvcKNyDeuOZ2dfDg$XBg6sR18fXH+1Uj7DcfbXg08dz6tb61tko1U+JyIzIE	22004049demo@st.vlute.edu.vn	0925818262	avt.png	danghoatdong	offline	2025-10-12 19:47:07.485764
07121b39-ffae-4f5a-be25-d9af117b1a8c	quynhvt	$argon2id$v=19$m=65536,t=3,p=4$l4ptltIvcKNyDeuOZ2dfDg$XBg6sR18fXH+1Uj7DcfbXg08dz6tb61tko1U+JyIzIE	22004053demo@st.vlute.edu.vn	0948995340	avt.png	chuakichhoat	online	2025-10-12 19:47:07.485764
db5ac513-6077-4717-832c-ae37eda2c1d1	nhudt	$argon2id$v=19$m=65536,t=3,p=4$l4ptltIvcKNyDeuOZ2dfDg$XBg6sR18fXH+1Uj7DcfbXg08dz6tb61tko1U+JyIzIE	22004058demo@st.vlute.edu.vn	0937451590	avt.png	khoa	offline	2025-10-12 19:47:07.485764
f64c8b9b-8b2a-4c16-87a0-9354988951d9	thaopt	$argon2id$v=19$m=65536,t=3,p=4$l4ptltIvcKNyDeuOZ2dfDg$XBg6sR18fXH+1Uj7DcfbXg08dz6tb61tko1U+JyIzIE	22004059demo@st.vlute.edu.vn	0966812799	avt.png	danghoatdong	online	2025-10-12 19:47:07.485764
ab903ef8-936c-425a-ad3a-68b69aafa9f1	thangnd	$argon2id$v=19$m=65536,t=3,p=4$l4ptltIvcKNyDeuOZ2dfDg$XBg6sR18fXH+1Uj7DcfbXg08dz6tb61tko1U+JyIzIE	22004063demo@st.vlute.edu.vn	0956003469	avt.png	chuakichhoat	offline	2025-10-12 19:47:07.485764
186acf33-877d-4963-902f-35bbfa0d6ecf	tunt	$argon2id$v=19$m=65536,t=3,p=4$l4ptltIvcKNyDeuOZ2dfDg$XBg6sR18fXH+1Uj7DcfbXg08dz6tb61tko1U+JyIzIE	22004067demo@st.vlute.edu.vn	0960100354	avt.png	khoa	online	2025-10-12 19:47:07.485764
7765d2a9-2e1c-41bd-86ad-c63e3f4cd079	dangtq	$argon2id$v=19$m=65536,t=3,p=4$l4ptltIvcKNyDeuOZ2dfDg$XBg6sR18fXH+1Uj7DcfbXg08dz6tb61tko1U+JyIzIE	22004069demo@st.vlute.edu.vn	0922246792	avt.png	danghoatdong	offline	2025-10-12 19:47:07.485764
b11d78c1-73a5-4d18-8002-c505ef2e9986	thienhl	$argon2id$v=19$m=65536,t=3,p=4$l4ptltIvcKNyDeuOZ2dfDg$XBg6sR18fXH+1Uj7DcfbXg08dz6tb61tko1U+JyIzIE	22004096demo@st.vlute.edu.vn	0962174100	avt.png	chuakichhoat	online	2025-10-12 19:47:07.485764
29e897be-0b07-4051-bf51-04ee0286f394	hanpt	$argon2id$v=19$m=65536,t=3,p=4$l4ptltIvcKNyDeuOZ2dfDg$XBg6sR18fXH+1Uj7DcfbXg08dz6tb61tko1U+JyIzIE	22004100demo@st.vlute.edu.vn	0937202624	avt.png	khoa	offline	2025-10-12 19:47:07.485764
7c68c28a-7d22-4489-bf78-09771d1af05a	diennm	$argon2id$v=19$m=65536,t=3,p=4$l4ptltIvcKNyDeuOZ2dfDg$XBg6sR18fXH+1Uj7DcfbXg08dz6tb61tko1U+JyIzIE	22004127demo@st.vlute.edu.vn	0961587089	avt.png	danghoatdong	online	2025-10-12 19:47:07.485764
78ed62c4-70ed-42c1-898b-6e400c6def37	nambt	$argon2id$v=19$m=65536,t=3,p=4$l4ptltIvcKNyDeuOZ2dfDg$XBg6sR18fXH+1Uj7DcfbXg08dz6tb61tko1U+JyIzIE	22004175demo@st.vlute.edu.vn	0969118270	avt.png	chuakichhoat	offline	2025-10-12 19:47:07.485764
90e4f116-b60a-4796-8f0d-26289f7dae50	kietnt	$argon2id$v=19$m=65536,t=3,p=4$l4ptltIvcKNyDeuOZ2dfDg$XBg6sR18fXH+1Uj7DcfbXg08dz6tb61tko1U+JyIzIE	22004176demo@st.vlute.edu.vn	0916922746	avt.png	khoa	online	2025-10-12 19:47:07.485764
d6a49ad8-fdb7-4649-88c3-5759befcc4a5	huyennt	$argon2id$v=19$m=65536,t=3,p=4$l4ptltIvcKNyDeuOZ2dfDg$XBg6sR18fXH+1Uj7DcfbXg08dz6tb61tko1U+JyIzIE	22004188demo@st.vlute.edu.vn	0925992400	avt.png	danghoatdong	offline	2025-10-12 19:47:07.485764
a78c47f4-b1c4-4313-b90a-8938f6ba8cac	trangpt	$argon2id$v=19$m=65536,t=3,p=4$l4ptltIvcKNyDeuOZ2dfDg$XBg6sR18fXH+1Uj7DcfbXg08dz6tb61tko1U+JyIzIE	22004197demo@st.vlute.edu.vn	0917862285	avt.png	chuakichhoat	online	2025-10-12 19:47:07.485764
2a11d082-fc6e-4713-9738-8650e95844f4	ynt	$argon2id$v=19$m=65536,t=3,p=4$l4ptltIvcKNyDeuOZ2dfDg$XBg6sR18fXH+1Uj7DcfbXg08dz6tb61tko1U+JyIzIE	22004198demo@st.vlute.edu.vn	0945342451	avt.png	khoa	offline	2025-10-12 19:47:07.485764
17716784-ec18-43b4-b8cb-a784d1127421	nhutlm	$argon2id$v=19$m=65536,t=3,p=4$l4ptltIvcKNyDeuOZ2dfDg$XBg6sR18fXH+1Uj7DcfbXg08dz6tb61tko1U+JyIzIE	22004200demo@st.vlute.edu.vn	0994833459	avt.png	danghoatdong	online	2025-10-12 19:47:07.485764
92d1b032-a891-4176-87dd-cdeab7473d61	gamhn	$argon2id$v=19$m=65536,t=3,p=4$l4ptltIvcKNyDeuOZ2dfDg$XBg6sR18fXH+1Uj7DcfbXg08dz6tb61tko1U+JyIzIE	22004204demo@st.vlute.edu.vn	0945189413	avt.png	chuakichhoat	offline	2025-10-12 19:47:07.485764
2614049e-5760-4853-b02a-5df11a4f947a	namnt	$argon2id$v=19$m=65536,t=3,p=4$l4ptltIvcKNyDeuOZ2dfDg$XBg6sR18fXH+1Uj7DcfbXg08dz6tb61tko1U+JyIzIE	22004207demo@st.vlute.edu.vn	0941694207	avt.png	khoa	online	2025-10-12 19:47:07.485764
30f7a140-9e0f-4763-8c73-d0ba585e0584	quynhln	$argon2id$v=19$m=65536,t=3,p=4$l4ptltIvcKNyDeuOZ2dfDg$XBg6sR18fXH+1Uj7DcfbXg08dz6tb61tko1U+JyIzIE	22004013demo@st.vlute.edu.vn	0937690799	avt.png	danghoatdong	online	2025-10-12 19:47:07.485764
d6207506-d2af-4427-b599-7c51661f3bdd	khanhht	$argon2id$v=19$m=65536,t=3,p=4$l4ptltIvcKNyDeuOZ2dfDg$XBg6sR18fXH+1Uj7DcfbXg08dz6tb61tko1U+JyIzIE	20004067demo@st.vlute.edu.vn	0938577420	avt.png	danghoatdong	offline	2025-10-12 19:47:07.485764
234a3664-f36a-442b-9bc2-a111ae14c1dc	thaopp	$argon2id$v=19$m=65536,t=3,p=4$l4ptltIvcKNyDeuOZ2dfDg$XBg6sR18fXH+1Uj7DcfbXg08dz6tb61tko1U+JyIzIE	20004192demo@st.vlute.edu.vn	0940865116	avt.png	chuakichhoat	online	2025-10-12 19:47:07.485764
59c9d360-7c64-45cf-b229-4e574b8c7ceb	huyst	$argon2id$v=19$m=65536,t=3,p=4$l4ptltIvcKNyDeuOZ2dfDg$XBg6sR18fXH+1Uj7DcfbXg08dz6tb61tko1U+JyIzIE	20004267demo@st.vlute.edu.vn	0946354998	avt.png	khoa	offline	2025-10-12 19:47:07.485764
85d0592f-15ed-4fb5-92ad-57c9af78c8aa	linhln	$argon2id$v=19$m=65536,t=3,p=4$l4ptltIvcKNyDeuOZ2dfDg$XBg6sR18fXH+1Uj7DcfbXg08dz6tb61tko1U+JyIzIE	21004020demo@st.vlute.edu.vn	0922619718	avt.png	danghoatdong	online	2025-10-12 19:47:07.485764
e2ce533c-9b1a-4bc6-bb84-440192b688d3	anbl	$argon2id$v=19$m=65536,t=3,p=4$l4ptltIvcKNyDeuOZ2dfDg$XBg6sR18fXH+1Uj7DcfbXg08dz6tb61tko1U+JyIzIE	21004046demo@st.vlute.edu.vn	0962060054	avt.png	chuakichhoat	offline	2025-10-12 19:47:07.485764
886a1f9f-d5b6-4964-ae73-e1eb5a69139e	duyendt	$argon2id$v=19$m=65536,t=3,p=4$l4ptltIvcKNyDeuOZ2dfDg$XBg6sR18fXH+1Uj7DcfbXg08dz6tb61tko1U+JyIzIE	21004053demo@st.vlute.edu.vn	0957888546	avt.png	khoa	online	2025-10-12 19:47:07.485764
809f4af0-c6c4-4478-be5e-93c696302b7b	vietch	$argon2id$v=19$m=65536,t=3,p=4$l4ptltIvcKNyDeuOZ2dfDg$XBg6sR18fXH+1Uj7DcfbXg08dz6tb61tko1U+JyIzIE	21004065demo@st.vlute.edu.vn	0970588530	avt.png	danghoatdong	offline	2025-10-12 19:47:07.485764
f9784f9e-7c83-4573-933c-335ed3b7e02d	vinhnt	$argon2id$v=19$m=65536,t=3,p=4$l4ptltIvcKNyDeuOZ2dfDg$XBg6sR18fXH+1Uj7DcfbXg08dz6tb61tko1U+JyIzIE	21004074demo@st.vlute.edu.vn	0997668785	avt.png	chuakichhoat	online	2025-10-12 19:47:07.485764
f9857b4a-d49f-45c5-850e-d5b6d0d3eb55	phongnh	$argon2id$v=19$m=65536,t=3,p=4$l4ptltIvcKNyDeuOZ2dfDg$XBg6sR18fXH+1Uj7DcfbXg08dz6tb61tko1U+JyIzIE	22004212demo@st.vlute.edu.vn	0937554757	avt.png	danghoatdong	offline	2025-10-12 19:47:07.485764
e66b4329-be12-44ac-915b-7a7b761433c0	ngandt	$argon2id$v=19$m=65536,t=3,p=4$l4ptltIvcKNyDeuOZ2dfDg$XBg6sR18fXH+1Uj7DcfbXg08dz6tb61tko1U+JyIzIE	22004214demo@st.vlute.edu.vn	0984481292	avt.png	chuakichhoat	online	2025-10-12 19:47:07.485764
34a86257-d43c-44e7-88ab-bccabdd5c644	trammt	$argon2id$v=19$m=65536,t=3,p=4$l4ptltIvcKNyDeuOZ2dfDg$XBg6sR18fXH+1Uj7DcfbXg08dz6tb61tko1U+JyIzIE	22004225demo@st.vlute.edu.vn	0997511993	avt.png	khoa	offline	2025-10-12 19:47:07.485764
fe1dcc48-4a84-45a7-8c38-610f6686f0f7	yendt	$argon2id$v=19$m=65536,t=3,p=4$l4ptltIvcKNyDeuOZ2dfDg$XBg6sR18fXH+1Uj7DcfbXg08dz6tb61tko1U+JyIzIE	22004227demo@st.vlute.edu.vn	0934360028	avt.png	danghoatdong	online	2025-10-12 19:47:07.485764
ce1ead4c-e4ef-4712-979e-6d358511f4cd	nhungnt	$argon2id$v=19$m=65536,t=3,p=4$l4ptltIvcKNyDeuOZ2dfDg$XBg6sR18fXH+1Uj7DcfbXg08dz6tb61tko1U+JyIzIE	22004231demo@st.vlute.edu.vn	0943114634	avt.png	chuakichhoat	offline	2025-10-12 19:47:07.485764
7efdc816-e04f-4493-9997-6b0766bae0db	khanhlv	$argon2id$v=19$m=65536,t=3,p=4$l4ptltIvcKNyDeuOZ2dfDg$XBg6sR18fXH+1Uj7DcfbXg08dz6tb61tko1U+JyIzIE	22004238demo@st.vlute.edu.vn	0936128535	avt.png	khoa	online	2025-10-12 19:47:07.485764
3ebc4930-2923-4085-b9ca-192f087ba6bf	hieutc	$argon2id$v=19$m=65536,t=3,p=4$l4ptltIvcKNyDeuOZ2dfDg$XBg6sR18fXH+1Uj7DcfbXg08dz6tb61tko1U+JyIzIE	22004266demo@st.vlute.edu.vn	0935206045	avt.png	danghoatdong	offline	2025-10-12 19:47:07.485764
3551fa73-fadc-4ef2-a1a3-6929a8e65c11	gianght	$argon2id$v=19$m=65536,t=3,p=4$l4ptltIvcKNyDeuOZ2dfDg$XBg6sR18fXH+1Uj7DcfbXg08dz6tb61tko1U+JyIzIE	22004267demo@st.vlute.edu.vn	0986808240	avt.png	chuakichhoat	online	2025-10-12 19:47:07.485764
0216dbde-2061-4841-ad42-0445ffd0692d	nhuttk	$argon2id$v=19$m=65536,t=3,p=4$l4ptltIvcKNyDeuOZ2dfDg$XBg6sR18fXH+1Uj7DcfbXg08dz6tb61tko1U+JyIzIE	22004294demo@st.vlute.edu.vn	0923543289	avt.png	khoa	offline	2025-10-12 19:47:07.485764
fce8f322-59a9-4711-88ae-8c10c7d87f21	thutm	$argon2id$v=19$m=65536,t=3,p=4$l4ptltIvcKNyDeuOZ2dfDg$XBg6sR18fXH+1Uj7DcfbXg08dz6tb61tko1U+JyIzIE	22004297demo@st.vlute.edu.vn	0989033106	avt.png	danghoatdong	online	2025-10-12 19:47:07.485764
d20c59af-cbf7-47b5-aec4-90b6b119a816	tuyennm	$argon2id$v=19$m=65536,t=3,p=4$l4ptltIvcKNyDeuOZ2dfDg$XBg6sR18fXH+1Uj7DcfbXg08dz6tb61tko1U+JyIzIE	22004313demo@st.vlute.edu.vn	0996952042	avt.png	chuakichhoat	offline	2025-10-12 19:47:07.485764
eb111e8c-1671-40a1-a970-64e683945c90	thint	$argon2id$v=19$m=65536,t=3,p=4$l4ptltIvcKNyDeuOZ2dfDg$XBg6sR18fXH+1Uj7DcfbXg08dz6tb61tko1U+JyIzIE	22004323demo@st.vlute.edu.vn	0945569238	avt.png	khoa	online	2025-10-12 19:47:07.485764
bc2ed64f-8ae4-4637-9632-75c5af63066c	hungth	$argon2id$v=19$m=65536,t=3,p=4$l4ptltIvcKNyDeuOZ2dfDg$XBg6sR18fXH+1Uj7DcfbXg08dz6tb61tko1U+JyIzIE	24004292demo@st.vlute.edu.vn	0988513364	avt.png	chuakichhoat	online	2025-10-12 19:47:07.485764
2dae71cc-a4e8-487b-be5a-3f65bdd9205d	anhnt	$argon2id$v=19$m=65536,t=3,p=4$l4ptltIvcKNyDeuOZ2dfDg$XBg6sR18fXH+1Uj7DcfbXg08dz6tb61tko1U+JyIzIE	22004335@st.vlute.edu.vn	0932936898	avt.png	danghoatdong	offline	2025-10-12 19:47:07.485764
\.


--
-- TOC entry 5145 (class 0 OID 17731)
-- Dependencies: 220
-- Data for Name: phan_quyen; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.phan_quyen (id, id_nguoi_dung, id_quyen) FROM stdin;
12c6fa67-f423-4de7-bb80-a282217bbe8d	0216dbde-2061-4841-ad42-0445ffd0692d	21ea2b50-e9d2-4894-bc4a-a9818ef226b1
960e7b1a-3308-4028-b407-06aeea1fa793	07121b39-ffae-4f5a-be25-d9af117b1a8c	6bc0b436-c0ab-4970-82b0-b0907136c9f0
64fd7892-1919-4665-be99-d1de78e6087d	0e65ec6f-2792-48aa-a665-9b11b4796a0f	bd8fc7f4-7941-4bae-80c4-ede4e907a904
ccc268e0-ff8b-47ca-a1ff-a3f185262184	144ee4c9-bf24-438f-8a6b-e15dd0e71705	21ea2b50-e9d2-4894-bc4a-a9818ef226b1
6ad4993d-8f7e-49f1-a862-b665ea6d9a82	17716784-ec18-43b4-b8cb-a784d1127421	6bc0b436-c0ab-4970-82b0-b0907136c9f0
1fd0d909-dd47-4f7b-bb0f-e38cdccd3896	186acf33-877d-4963-902f-35bbfa0d6ecf	bd8fc7f4-7941-4bae-80c4-ede4e907a904
b36dc5ca-168f-4b49-a1a9-f3ab7f724774	1cabfcda-923b-400f-b05d-9b900516380c	21ea2b50-e9d2-4894-bc4a-a9818ef226b1
a9555c63-be4b-4f51-abb2-50deaea96ac7	234a3664-f36a-442b-9bc2-a111ae14c1dc	6bc0b436-c0ab-4970-82b0-b0907136c9f0
bed0b27c-b610-4ef0-aafa-1558b2cbcfb2	2614049e-5760-4853-b02a-5df11a4f947a	bd8fc7f4-7941-4bae-80c4-ede4e907a904
f205e45c-780b-43ec-b744-f9719852c80f	29e897be-0b07-4051-bf51-04ee0286f394	21ea2b50-e9d2-4894-bc4a-a9818ef226b1
6ccd56a6-9ac2-4dbd-936d-18696e10532e	2a11d082-fc6e-4713-9738-8650e95844f4	6bc0b436-c0ab-4970-82b0-b0907136c9f0
63927aa4-9d6e-4fa0-8a51-733bdceb411e	2dae71cc-a4e8-487b-be5a-3f65bdd9205d	bd8fc7f4-7941-4bae-80c4-ede4e907a904
32fc3570-2f95-4783-b4d7-1e77a444b255	30f7a140-9e0f-4763-8c73-d0ba585e0584	21ea2b50-e9d2-4894-bc4a-a9818ef226b1
48388db8-7b2a-4b93-bc5b-80203ecd7bde	34a86257-d43c-44e7-88ab-bccabdd5c644	6bc0b436-c0ab-4970-82b0-b0907136c9f0
68163d84-e325-46c1-b5ff-af3f8e03e85a	34e0c86a-a19f-4042-bbef-371a64693ba1	bd8fc7f4-7941-4bae-80c4-ede4e907a904
275d850c-da95-40c1-8df8-60e85109875e	3551fa73-fadc-4ef2-a1a3-6929a8e65c11	21ea2b50-e9d2-4894-bc4a-a9818ef226b1
7abe3e30-80ae-420d-9665-b95fe743663f	53d15311-5c74-452a-8988-e9e1b683efad	bd8fc7f4-7941-4bae-80c4-ede4e907a904
1543bfa7-e25f-4229-99ae-c4889abf5f6f	59c9d360-7c64-45cf-b229-4e574b8c7ceb	21ea2b50-e9d2-4894-bc4a-a9818ef226b1
9a06fdf7-65d1-4359-af1f-0f8c24d90eb6	5ee334f1-bd31-41b7-8672-df8903f2747a	6bc0b436-c0ab-4970-82b0-b0907136c9f0
834279ca-9a53-4015-b9e0-40132114927e	7765d2a9-2e1c-41bd-86ad-c63e3f4cd079	bd8fc7f4-7941-4bae-80c4-ede4e907a904
2859dda4-3922-42d1-8366-0b79a2f329d5	78ed62c4-70ed-42c1-898b-6e400c6def37	21ea2b50-e9d2-4894-bc4a-a9818ef226b1
117cc38c-c9d0-40aa-bc77-92ad4aff84c1	79039562-eda7-4a5b-94cb-0cca2b078742	6bc0b436-c0ab-4970-82b0-b0907136c9f0
cedf7ae2-a5fa-46e7-84ab-65da6b6a3df0	7c68c28a-7d22-4489-bf78-09771d1af05a	bd8fc7f4-7941-4bae-80c4-ede4e907a904
04926759-2416-47bc-8c1d-bfbb8564dc63	7efdc816-e04f-4493-9997-6b0766bae0db	21ea2b50-e9d2-4894-bc4a-a9818ef226b1
739f2774-f8af-469d-a9d8-69651f089467	809f4af0-c6c4-4478-be5e-93c696302b7b	6bc0b436-c0ab-4970-82b0-b0907136c9f0
c597440c-6f8f-4ac5-be82-d8e04dadc515	85d0592f-15ed-4fb5-92ad-57c9af78c8aa	bd8fc7f4-7941-4bae-80c4-ede4e907a904
faed22c4-3878-4407-a11d-c83c0ab71a8b	886a1f9f-d5b6-4964-ae73-e1eb5a69139e	21ea2b50-e9d2-4894-bc4a-a9818ef226b1
daecea39-c96e-4d7f-8a72-a8fd129d65b4	8e233604-c830-4df5-b863-29ec060327d3	6bc0b436-c0ab-4970-82b0-b0907136c9f0
c6fcba9d-3f93-4d07-abc8-70c9457e1640	8fe15279-afda-4edc-bd9c-c2c7307df4c4	bd8fc7f4-7941-4bae-80c4-ede4e907a904
1f0c0810-fa7f-4856-ad67-16a2a29afd51	90e4f116-b60a-4796-8f0d-26289f7dae50	21ea2b50-e9d2-4894-bc4a-a9818ef226b1
3f772551-6fbe-46b6-aac9-8b1a128ebd4c	92d1b032-a891-4176-87dd-cdeab7473d61	6bc0b436-c0ab-4970-82b0-b0907136c9f0
f99ccccc-70b6-4acb-be18-67241cf8d6cc	9976c99e-d95f-4e55-92ed-195d29be7ba6	bd8fc7f4-7941-4bae-80c4-ede4e907a904
73446606-4b95-4b90-8ca8-5b8ff4eac940	9e4364ac-289e-4f4a-b18f-57bb5d05a336	21ea2b50-e9d2-4894-bc4a-a9818ef226b1
d96f6fbd-45a3-4956-9f76-425776745d5d	a78c47f4-b1c4-4313-b90a-8938f6ba8cac	6bc0b436-c0ab-4970-82b0-b0907136c9f0
8561b528-bfee-4267-a250-68bdadd01cb8	ab903ef8-936c-425a-ad3a-68b69aafa9f1	bd8fc7f4-7941-4bae-80c4-ede4e907a904
e9c97cf8-4e67-43c8-8013-c9ff55f1e7ba	b11d78c1-73a5-4d18-8002-c505ef2e9986	21ea2b50-e9d2-4894-bc4a-a9818ef226b1
8e3d393d-c7e5-4b47-a930-d8c828f1783e	bc2ed64f-8ae4-4637-9632-75c5af63066c	6bc0b436-c0ab-4970-82b0-b0907136c9f0
a6bc3c08-7251-41f0-b660-29802e4c5e19	ce1ead4c-e4ef-4712-979e-6d358511f4cd	bd8fc7f4-7941-4bae-80c4-ede4e907a904
71a4ffab-6fb8-4b6b-bb0b-0501b07abbdc	d20c59af-cbf7-47b5-aec4-90b6b119a816	21ea2b50-e9d2-4894-bc4a-a9818ef226b1
81f87de3-8d71-4b50-b6b2-383b32d9be96	d6207506-d2af-4427-b599-7c51661f3bdd	6bc0b436-c0ab-4970-82b0-b0907136c9f0
a9946c3f-0559-4881-84a5-b9af2310c8ab	d6a49ad8-fdb7-4649-88c3-5759befcc4a5	bd8fc7f4-7941-4bae-80c4-ede4e907a904
1173fa54-dfad-4302-817e-8bc367c221f9	db5ac513-6077-4717-832c-ae37eda2c1d1	21ea2b50-e9d2-4894-bc4a-a9818ef226b1
27fc3e5f-dc09-47dd-a498-e3655272d5f2	e2ce533c-9b1a-4bc6-bb84-440192b688d3	6bc0b436-c0ab-4970-82b0-b0907136c9f0
b9e42316-60d4-4b1c-89fb-d0d6b43a4b01	e66b4329-be12-44ac-915b-7a7b761433c0	bd8fc7f4-7941-4bae-80c4-ede4e907a904
16306819-f10a-49b3-b1fa-95d459c66e0f	eb111e8c-1671-40a1-a970-64e683945c90	21ea2b50-e9d2-4894-bc4a-a9818ef226b1
219675c5-cf04-4f33-bf4f-f57683101d18	ed9f5adb-413b-43cc-81f1-99f0ca57b321	6bc0b436-c0ab-4970-82b0-b0907136c9f0
7466504a-d016-43c9-a118-fb30e6ce5bf1	f37ca5eb-d199-46a7-9c75-70df9c9772bc	bd8fc7f4-7941-4bae-80c4-ede4e907a904
fe718bfa-143f-4c27-ae31-6e6dd5a8b4cc	f5f4022f-eccc-48e6-a435-1a9b1cdb3f3a	21ea2b50-e9d2-4894-bc4a-a9818ef226b1
e4d57f8a-7dd0-4895-bf9d-3264816d46a9	f64c8b9b-8b2a-4c16-87a0-9354988951d9	6bc0b436-c0ab-4970-82b0-b0907136c9f0
33bf6ab8-3317-4627-bc91-5dbf154d25ce	f9784f9e-7c83-4573-933c-335ed3b7e02d	bd8fc7f4-7941-4bae-80c4-ede4e907a904
ce034e6e-00f1-4b22-b0c4-e1c388625109	f9857b4a-d49f-45c5-850e-d5b6d0d3eb55	21ea2b50-e9d2-4894-bc4a-a9818ef226b1
e7ee4ca8-a13d-465c-9e05-ef358a0a25fd	fb9d3af2-aeef-46b1-a91c-438cf099a636	6bc0b436-c0ab-4970-82b0-b0907136c9f0
3e3e35bf-dcf0-4411-a18a-ffc72f02a3dd	fce8f322-59a9-4711-88ae-8c10c7d87f21	bd8fc7f4-7941-4bae-80c4-ede4e907a904
31ad6b17-5411-4229-878a-38ae640cda60	fe1dcc48-4a84-45a7-8c38-610f6686f0f7	21ea2b50-e9d2-4894-bc4a-a9818ef226b1
2d7295dd-5e57-47ac-ae04-80ed5c3a69bc	3ebc4930-2923-4085-b9ca-192f087ba6bf	6bc0b436-c0ab-4970-82b0-b0907136c9f0
9eaee3c1-be38-4ed4-8bd4-02e26875071c	3ebc4930-2923-4085-b9ca-192f087ba6bf	21ea2b50-e9d2-4894-bc4a-a9818ef226b1
06b7b179-369d-45b4-b0b6-705d29dec5ab	0023b190-c734-45f6-8425-a1949e08e8d5	21ea2b50-e9d2-4894-bc4a-a9818ef226b1
e424bd52-0e64-42b6-af65-297627c4d8e0	0023b190-c734-45f6-8425-a1949e08e8d5	bd8fc7f4-7941-4bae-80c4-ede4e907a904
\.


--
-- TOC entry 5151 (class 0 OID 17868)
-- Dependencies: 226
-- Data for Name: phien_dang_nhap; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.phien_dang_nhap (id, id_nguoi_dung, bat_dau, het_han, dang_hoat_dong, selector, verifier_hash) FROM stdin;
\.


--
-- TOC entry 5143 (class 0 OID 17702)
-- Dependencies: 218
-- Data for Name: quyen; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.quyen (id, vai_tro) FROM stdin;
bd8fc7f4-7941-4bae-80c4-ede4e907a904	quantri
21ea2b50-e9d2-4894-bc4a-a9818ef226b1	moigioi
6bc0b436-c0ab-4970-82b0-b0907136c9f0	khachhang
\.


--
-- TOC entry 5148 (class 0 OID 17814)
-- Dependencies: 223
-- Data for Name: thanh_toan; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.thanh_toan (id, id_giao_dich, tong_tien, ngay_tt, phuong_thuc, trang_thai) FROM stdin;
\.


--
-- TOC entry 5149 (class 0 OID 17828)
-- Dependencies: 224
-- Data for Name: thanh_toan_ct; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.thanh_toan_ct (id, id_thanh_toan, id_bds, so_luong, so_tien) FROM stdin;
\.


--
-- TOC entry 5155 (class 0 OID 17964)
-- Dependencies: 230
-- Data for Name: thong_bao; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.thong_bao (id, id_nguoi_dung, loai, tieu_de, noi_dung, thoi_gian_gui, trang_thai, id_nguoi_gui) FROM stdin;
91003dd4-821c-4115-8c04-07c15bfc51b4	ed9f5adb-413b-43cc-81f1-99f0ca57b321	quantrivien	aaa	12333	2025-10-15 12:39:00.221042	chuaxem	2dae71cc-a4e8-487b-be5a-3f65bdd9205d
\.


--
-- TOC entry 5160 (class 0 OID 26360)
-- Dependencies: 235
-- Data for Name: tin_nhan; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.tin_nhan (id, id_hop_thoai, nguoi_gui, nguoi_nhan, noi_dung, anh_tn, video_tn, tg_gui, da_thu_hoi, da_xoa) FROM stdin;
23264658-8143-4bc8-9003-6a2856cddee1	916f1dfc-7e3d-44ef-844c-1835de5f4fdb	0023b190-c734-45f6-8425-a1949e08e8d5	0e65ec6f-2792-48aa-a665-9b11b4796a0f	Đây bạn nhé.	\N	\N	2025-10-14 07:00:25.956702	0	0
875b18cc-395b-4139-a4be-e6f4f75d29e1	c69c1fa3-2693-489e-b122-7a6ffcb8a3fa	5ee334f1-bd31-41b7-8672-df8903f2747a	f37ca5eb-d199-46a7-9c75-70df9c9772bc	\N	\N	\N	2025-10-14 03:58:25.956702	0	0
b2192871-edb3-47da-91bb-45e234d9999f	9a684ae7-6944-427d-b6be-2cfd6eefc719	a78c47f4-b1c4-4313-b90a-8938f6ba8cac	07121b39-ffae-4f5a-be25-d9af117b1a8c	\N	\N	\N	2025-10-14 00:49:25.956702	0	0
a921f566-20c9-4a48-892a-1114030c792d	916f1dfc-7e3d-44ef-844c-1835de5f4fdb	0e65ec6f-2792-48aa-a665-9b11b4796a0f	0023b190-c734-45f6-8425-a1949e08e8d5	Chào bạn, tôi thấy bạn đăng tin bán nhà mặt phố. Tin này còn không ạ?	\N	\N	2025-10-14 06:47:25.956702	0	0
a75e9533-b96f-43ff-a42c-a6b7fe598162	916f1dfc-7e3d-44ef-844c-1835de5f4fdb	0023b190-c734-45f6-8425-a1949e08e8d5	0e65ec6f-2792-48aa-a665-9b11b4796a0f	Vâng chào bạn, tin vẫn còn nhé.	\N	\N	2025-10-14 06:52:25.956702	0	0
a2868aaf-d04d-4f97-8c60-97d765207138	916f1dfc-7e3d-44ef-844c-1835de5f4fdb	0e65ec6f-2792-48aa-a665-9b11b4796a0f	0023b190-c734-45f6-8425-a1949e08e8d5	Bạn cho tôi xin thêm hình ảnh sổ đỏ được không?	\N	\N	2025-10-14 06:57:25.956702	0	0
d04d3c26-8c35-4816-8b6f-a4a6144f0148	eafce559-0d9f-49be-acb0-59e79fc6323f	8e233604-c830-4df5-b863-29ec060327d3	ed9f5adb-413b-43cc-81f1-99f0ca57b321	Alo, mình muốn hẹn xem căn hộ The Landmark 81 bạn đăng cho thuê.	\N	\N	2025-10-13 08:47:25.956702	0	0
23f4fdcc-e93a-4aae-aa7d-44023685b9b5	eafce559-0d9f-49be-acb0-59e79fc6323f	ed9f5adb-413b-43cc-81f1-99f0ca57b321	8e233604-c830-4df5-b863-29ec060327d3	Chào bạn, cuối tuần này bạn rảnh không ạ? Tầm 3h chiều thứ 7 nhé?	\N	\N	2025-10-13 09:47:25.956702	0	0
4c8bd4dc-1516-493d-b925-96990b12f18a	eafce559-0d9f-49be-acb0-59e79fc6323f	8e233604-c830-4df5-b863-29ec060327d3	ed9f5adb-413b-43cc-81f1-99f0ca57b321	Ok bạn. Hẹn bạn chiều thứ 7.	\N	\N	2025-10-13 10:47:25.956702	0	0
ca8e0824-50ea-435c-9739-4d5dde8320c7	c69c1fa3-2693-489e-b122-7a6ffcb8a3fa	f37ca5eb-d199-46a7-9c75-70df9c9772bc	5ee334f1-bd31-41b7-8672-df8903f2747a	Giá đất nền dự án Gem Sky World có thương lượng được không bạn?	\N	\N	2025-10-14 03:47:25.956702	0	0
f31139b5-1a93-4db9-8ed9-93b661eeb706	c69c1fa3-2693-489e-b122-7a6ffcb8a3fa	5ee334f1-bd31-41b7-8672-df8903f2747a	f37ca5eb-d199-46a7-9c75-70df9c9772bc	Giá này là giá tốt nhất rồi ạ. Em gửi anh video thực tế dự án để anh tham khảo.	\N	\N	2025-10-14 03:57:25.956702	0	0
50ad43a7-565d-448e-972e-dc463cdbdb97	16173d0a-da65-4e57-b7c0-d7f39dfa8da4	79039562-eda7-4a5b-94cb-0cca2b078742	fb9d3af2-aeef-46b1-a91c-438cf099a636	Phòng trọ còn không bạn ơi?	\N	\N	2025-10-11 08:47:25.956702	0	0
cbb26708-159c-4dfa-ab48-97d7aeca0b3c	16173d0a-da65-4e57-b7c0-d7f39dfa8da4	fb9d3af2-aeef-46b1-a91c-438cf099a636	79039562-eda7-4a5b-94cb-0cca2b078742	Còn bạn nhé	\N	\N	2025-10-11 08:47:25.956702	0	0
7579ffcf-5d3d-4166-a236-adbba3f91cba	9a684ae7-6944-427d-b6be-2cfd6eefc719	07121b39-ffae-4f5a-be25-d9af117b1a8c	a78c47f4-b1c4-4313-b90a-8938f6ba8cac	Mình quan tâm shophouse Vinhomes, bạn gửi mình xem mấy tấm hình với.	\N	\N	2025-10-14 00:47:25.956702	0	0
8a13639d-f541-48bb-bdd2-84f2fcc7f653	9a684ae7-6944-427d-b6be-2cfd6eefc719	a78c47f4-b1c4-4313-b90a-8938f6ba8cac	07121b39-ffae-4f5a-be25-d9af117b1a8c	Ok bạn, mình gửi liền đây.	\N	\N	2025-10-14 00:48:25.956702	0	0
92deac1f-3b78-43e4-bde7-180695e96b67	70d8fefb-d734-4cb0-8ceb-be224fcf98d7	1cabfcda-923b-400f-b05d-9b900516380c	144ee4c9-bf24-438f-8a6b-e15dd0e71705	Chào bạn, mảnh đất thổ cư ở Bình Chánh đã có sổ hồng riêng chưa?	\N	\N	2025-10-14 04:47:25.956702	0	0
273a09d7-c169-4cfd-a396-137d2182dd5d	70d8fefb-d734-4cb0-8ceb-be224fcf98d7	144ee4c9-bf24-438f-8a6b-e15dd0e71705	1cabfcda-923b-400f-b05d-9b900516380c	Dạ có sổ riêng rồi ạ, bao sang tên công chứng trong ngày luôn anh.	\N	\N	2025-10-14 05:02:25.956702	0	0
605f6c16-15c5-4d31-8964-79e6fc0f49b3	70d8fefb-d734-4cb0-8ceb-be224fcf98d7	1cabfcda-923b-400f-b05d-9b900516380c	144ee4c9-bf24-438f-8a6b-e15dd0e71705	Ok, vậy để tôi sắp xếp tài chính.	\N	\N	2025-10-14 05:47:25.956702	0	0
f65e6cb3-5b84-4c9b-8073-8c232d99279a	877fbe29-75e9-41bc-9cec-ed3a431580a9	53d15311-5c74-452a-8988-e9e1b683efad	9e4364ac-289e-4f4a-b18f-57bb5d05a336	Bạn ơi, văn phòng Bitexco còn cho thuê không?	\N	\N	2025-10-12 08:47:25.956702	0	0
e75bad14-4f6b-4e85-877d-562814003730	877fbe29-75e9-41bc-9cec-ed3a431580a9	9e4364ac-289e-4f4a-b18f-57bb5d05a336	53d15311-5c74-452a-8988-e9e1b683efad	Xin lỗi bạn mình trả lời chậm, bên mình vẫn còn diện tích trống nhé.	\N	\N	2025-10-14 02:47:25.956702	0	0
462048ca-3a28-4d2f-998c-8ed897d26db7	3cd7dd8a-50cf-4cc2-a8c6-1e99af001960	34e0c86a-a19f-4042-bbef-371a64693ba1	f5f4022f-eccc-48e6-a435-1a9b1cdb3f3a	Chung cư Ecopark gần đó có trường học, siêu thị không bạn?	\N	\N	2025-10-13 22:47:25.956702	0	0
114b237f-b11a-4df9-a04d-fdf2a33464de	3cd7dd8a-50cf-4cc2-a8c6-1e99af001960	f5f4022f-eccc-48e6-a435-1a9b1cdb3f3a	34e0c86a-a19f-4042-bbef-371a64693ba1	Dạ dưới chân tòa nhà có siêu thị K-Market, và trong khu đô thị có hệ thống trường liên cấp Vinschool và bệnh viện Vinmec luôn ạ.	\N	\N	2025-10-13 22:57:25.956702	0	0
a1aa62cc-b085-4634-bcc4-8e74184aa626	fd77a594-2c47-47ae-a446-17d363285f0d	9976c99e-d95f-4e55-92ed-195d29be7ba6	db5ac513-6077-4717-832c-ae37eda2c1d1	Giá thuê nhà nguyên căn quận 7 là 25tr/tháng có bớt được không ạ?	\N	\N	2025-10-13 06:47:25.956702	0	0
634e8ccc-ad28-4bae-817d-e9a22adfb081	fd77a594-2c47-47ae-a446-17d363285f0d	db5ac513-6077-4717-832c-ae37eda2c1d1	9976c99e-d95f-4e55-92ed-195d29be7ba6	Nếu anh/chị thuê dài hạn trên 2 năm thì em có thể giảm còn 23tr/tháng ạ.	\N	\N	2025-10-13 07:47:25.956702	0	0
a68033d5-88ae-48f0-a17b-6be7ca7ae253	fd77a594-2c47-47ae-a446-17d363285f0d	9976c99e-d95f-4e55-92ed-195d29be7ba6	db5ac513-6077-4717-832c-ae37eda2c1d1	Ok chốt giá đó nhé. Khi nào ký hợp đồng được?	\N	\N	2025-10-13 08:47:25.956702	0	0
99f4c22c-57ce-43a2-9ee8-0a20443f964b	90d56d82-f47d-4bd2-9cc6-99f5fac99a38	f64c8b9b-8b2a-4c16-87a0-9354988951d9	ab903ef8-936c-425a-ad3a-68b69aafa9f1	Gửi bạn hình ảnh thực tế căn hộ dịch vụ Thảo Điền nhé.	\N	\N	2025-10-14 08:42:25.956702	0	0
fc6c269e-d646-4b7f-94f0-d60d9f2c1691	071ec056-7151-43be-88c8-1c9f973b6470	186acf33-877d-4963-902f-35bbfa0d6ecf	7765d2a9-2e1c-41bd-86ad-c63e3f4cd079	Biệt thự sân vườn Quận 9 bạn đăng hướng gì vậy?	\N	\N	2025-10-14 02:47:25.956702	0	0
c66e4305-6601-4cbc-a1ee-de139e9aac1e	071ec056-7151-43be-88c8-1c9f973b6470	7765d2a9-2e1c-41bd-86ad-c63e3f4cd079	186acf33-877d-4963-902f-35bbfa0d6ecf	Dạ biệt thự hướng Đông Nam mát mẻ quanh năm ạ.	\N	\N	2025-10-14 03:17:25.956702	0	0
67026606-936c-44d7-bedc-3718c6d40b01	8242a594-3df9-4969-baef-0c76e2b3d176	b11d78c1-73a5-4d18-8002-c505ef2e9986	29e897be-0b07-4051-bf51-04ee0286f394	Nhà cấp 4 Hóc Môn còn không bạn?	\N	\N	2025-10-09 08:47:25.956702	0	0
0f2640f4-2612-471f-b33b-695b1257e0e4	8242a594-3df9-4969-baef-0c76e2b3d176	29e897be-0b07-4051-bf51-04ee0286f394	b11d78c1-73a5-4d18-8002-c505ef2e9986	Nhà đó mình bán rồi bạn ơi. Cảm ơn bạn đã quan tâm.	\N	\N	2025-10-10 08:47:25.956702	0	0
5c9797da-f0d1-4822-93d5-bea364ff510e	87630b25-70af-48e7-b48b-6f5bdfd70c0f	7c68c28a-7d22-4489-bf78-09771d1af05a	78ed62c4-70ed-42c1-898b-6e400c6def37	Tôi xem nhà Gò Vấp rồi, nhà đẹp nhưng hẻm hơi nhỏ so với xe nhà tôi.	\N	\N	2025-10-13 20:47:25.956702	0	0
b1d51f95-78a3-447e-a7e7-f22429d4deee	87630b25-70af-48e7-b48b-6f5bdfd70c0f	78ed62c4-70ed-42c1-898b-6e400c6def37	7c68c28a-7d22-4489-bf78-09771d1af05a	Dạ vâng, tiếc quá. Cảm ơn anh đã dành thời gian ạ.	\N	\N	2025-10-13 21:47:25.956702	0	0
5c23fdc1-105c-498d-a031-bb16d0072c1b	497a2d1b-223b-4ae8-a15b-e17d4a7f059c	90e4f116-b60a-4796-8f0d-26289f7dae50	d6a49ad8-fdb7-4649-88c3-5759befcc4a5	Chung cư mini Cầu Giấy phí dịch vụ thế nào bạn?	\N	\N	2025-10-13 23:47:25.956702	0	0
fd32388d-52a2-49df-88c9-5a042b1983c8	497a2d1b-223b-4ae8-a15b-e17d4a7f059c	90e4f116-b60a-4796-8f0d-26289f7dae50	d6a49ad8-fdb7-4649-88c3-5759befcc4a5	Gửi xe có mất phí không?	\N	\N	2025-10-13 23:47:25.956702	0	0
c5c7c13f-8b11-4c78-954c-2ee548ef44c2	497a2d1b-223b-4ae8-a15b-e17d4a7f059c	d6a49ad8-fdb7-4649-88c3-5759befcc4a5	90e4f116-b60a-4796-8f0d-26289f7dae50	Dạ phí dịch vụ là 100k/phòng, gửi xe 80k/xe/tháng ạ.	\N	\N	2025-10-13 23:52:25.956702	0	0
d391efd5-9b09-43ba-be96-0645a8de2752	0f874310-2063-4a67-9895-ea53d793dc9e	30f7a140-9e0f-4763-8c73-d0ba585e0584	2614049e-5760-4853-b02a-5df11a4f947a	Tôi không tiện gõ chữ, tôi gửi tin nhắn thoại nhé.	\N	\N	2025-10-14 08:17:25.956702	0	0
fa0e35e9-6751-4ffd-91ff-53e8c33d7427	0f874310-2063-4a67-9895-ea53d793dc9e	2614049e-5760-4853-b02a-5df11a4f947a	30f7a140-9e0f-4763-8c73-d0ba585e0584	Ok mình nghe rồi.	\N	\N	2025-10-14 08:22:25.956702	0	0
23321069-916d-458f-a846-a73842e6a5cf	9a684ae7-6944-427d-b6be-2cfd6eefc719	a78c47f4-b1c4-4313-b90a-8938f6ba8cac	07121b39-ffae-4f5a-be25-d9af117b1a8c	\N	\N	\N	2025-10-14 00:49:25.956702	0	0
97651309-63cf-4243-909e-ba3629202df2	0f874310-2063-4a67-9895-ea53d793dc9e	30f7a140-9e0f-4763-8c73-d0ba585e0584	2614049e-5760-4853-b02a-5df11a4f947a	\N	\N	\N	2025-10-14 08:18:25.956702	0	0
7e74604c-efe6-43c1-9e47-576520440eea	90d56d82-f47d-4bd2-9cc6-99f5fac99a38	f64c8b9b-8b2a-4c16-87a0-9354988951d9	ab903ef8-936c-425a-ad3a-68b69aafa9f1	Gửi bạn hình ảnh thực tế căn hộ dịch vụ Thảo Điền nhé.	\N	\N	2025-10-14 08:43:25.956702	0	0
b69e13ac-2dbc-41a1-897f-71de74096728	90d56d82-f47d-4bd2-9cc6-99f5fac99a38	f64c8b9b-8b2a-4c16-87a0-9354988951d9	ab903ef8-936c-425a-ad3a-68b69aafa9f1	Biệt thự sân vườn Quận 9 bạn đăng hướng gì vậy?	\N	\N	2025-10-14 08:43:25.956702	0	0
9f9ea63e-b25c-4e82-a0e4-6013c151685b	16173d0a-da65-4e57-b7c0-d7f39dfa8da4	2dae71cc-a4e8-487b-be5a-3f65bdd9205d	fb9d3af2-aeef-46b1-a91c-438cf099a636	cái j	\N	\N	2025-10-15 00:02:05.785557	0	0
68e14c69-bd50-4e5c-a117-c6dc48c9825c	16173d0a-da65-4e57-b7c0-d7f39dfa8da4	2dae71cc-a4e8-487b-be5a-3f65bdd9205d	fb9d3af2-aeef-46b1-a91c-438cf099a636	sao	\N	\N	2025-10-15 00:02:55.043465	0	0
b834fa45-e106-4366-9531-f817e5b30767	fd77a594-2c47-47ae-a446-17d363285f0d	2dae71cc-a4e8-487b-be5a-3f65bdd9205d	9976c99e-d95f-4e55-92ed-195d29be7ba6	ok	\N	\N	2025-10-15 00:03:34.313924	0	0
4503b3d9-65dd-474c-9142-18ce89b22a76	fd77a594-2c47-47ae-a446-17d363285f0d	0023b190-c734-45f6-8425-a1949e08e8d5	2dae71cc-a4e8-487b-be5a-3f65bdd9205d	hả	\N	\N	2025-10-15 00:23:47.326303	0	0
1a1ac218-7e60-4b3d-bec5-7930d66d9d01	fd77a594-2c47-47ae-a446-17d363285f0d	0023b190-c734-45f6-8425-a1949e08e8d5	2dae71cc-a4e8-487b-be5a-3f65bdd9205d	sao	\N	\N	2025-10-15 00:23:57.896462	0	0
562933e8-0369-42e5-a927-aa4818629316	fd77a594-2c47-47ae-a446-17d363285f0d	2dae71cc-a4e8-487b-be5a-3f65bdd9205d	0023b190-c734-45f6-8425-a1949e08e8d5	ùm	\N	\N	2025-10-15 00:30:10.492621	0	0
f93470f7-8664-4cc9-b6e6-2f964d42ad89	fd77a594-2c47-47ae-a446-17d363285f0d	2dae71cc-a4e8-487b-be5a-3f65bdd9205d	0023b190-c734-45f6-8425-a1949e08e8d5	có j ko ô	\N	\N	2025-10-15 00:30:33.542257	0	0
bb62551c-fe93-4b61-b9a9-fa071345e08d	fd77a594-2c47-47ae-a446-17d363285f0d	0023b190-c734-45f6-8425-a1949e08e8d5	2dae71cc-a4e8-487b-be5a-3f65bdd9205d	ko á bna	\N	\N	2025-10-15 00:30:42.140669	0	0
\.


--
-- TOC entry 5161 (class 0 OID 26387)
-- Dependencies: 236
-- Data for Name: tin_tuc; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.tin_tuc (id, id_khach_hang, tieu_de, mo_ta, chuyen_muc, trang_thai, anh_tin, ngay_dang, luot_xem) FROM stdin;
a6dd6ecc-745f-4351-8ad2-2490920d0efb	07121b39-ffae-4f5a-be25-d9af117b1a8c	Bán nhà mặt phố Quận 1, kinh doanh sầm uất	Nhà 5 tầng, diện tích 60m2, vị trí đắc địa, đang cho thuê 50 triệu/tháng. Sổ hồng chính chủ.	Bán nhà mặt phố	dangban	chuacapnhat.png	2025-10-14 08:34:49.038555	1578
587c6e85-9294-447e-8c4c-0abc7aefcb0a	17716784-ec18-43b4-b8cb-a784d1127421	Cho thuê căn hộ cao cấp The Landmark 81, full nội thất	Căn hộ 2 phòng ngủ, 2 WC, tầng 35 view sông Sài Gòn. Nội thất nhập khẩu châu Âu, chỉ cần xách vali vào ở.	Cho thuê căn hộ	dangban	chuacapnhat.png	2025-10-14 08:34:49.038555	2345
52fc286d-b13c-4196-9087-9b4ccd3d52db	234a3664-f36a-442b-9bc2-a111ae14c1dc	Đất nền dự án Gem Sky World, Long Thành, giá tốt	Lô đất 100m2, mặt tiền đường 12m, hạ tầng hoàn thiện, gần sân bay Long Thành. Sổ đỏ trao tay.	Bán đất nền dự án	dangban	chuacapnhat.png	2025-10-14 08:34:49.038555	890
c92fb700-cb6d-4933-a97f-f2351334619b	2a11d082-fc6e-4713-9738-8650e95844f4	Cần bán gấp biệt thự sân vườn Quận 9 (cũ)	Biệt thự 300m2, có hồ bơi và sân vườn rộng. Khu dân cư an ninh, yên tĩnh. Giảm giá cho khách thiện chí.	Bán biệt thự	daban	chuacapnhat.png	2025-10-14 08:34:49.038555	3120
851fe39a-0031-4a43-91c6-ea4367df6f3e	34a86257-d43c-44e7-88ab-bccabdd5c644	Cho thuê văn phòng hạng A tại tòa nhà Bitexco	Diện tích linh hoạt từ 50m2 đến 500m2. Dịch vụ chuyên nghiệp, an ninh 24/7. Giá cả cạnh tranh.	Cho thuê văn phòng	dathue	chuacapnhat.png	2025-10-14 08:34:49.038555	1123
80764965-cd13-4a8f-a3a3-db4924c73b85	3ebc4930-2923-4085-b9ca-192f087ba6bf	Bán chung cư mini Cầu Giấy, Hà Nội, dòng tiền tốt	Tòa nhà 7 tầng, 15 phòng khép kín đang cho thuê full. Doanh thu 80 triệu/tháng. Sổ đỏ đầy đủ.	Bán chung cư	dangban	chuacapnhat.png	2025-10-14 08:34:49.038555	955
1c683e1f-76be-410f-bcb0-3919c964138f	5ee334f1-bd31-41b7-8672-df8903f2747a	Nhà hẻm xe hơi Gò Vấp, 4x16m, 4 tầng	Nhà mới xây, thiết kế hiện đại, 4 phòng ngủ. Hẻm thông, an ninh. Gần chợ và trường học.	Bán nhà trong hẻm	choduyet	chuacapnhat.png	2025-10-14 08:34:49.038555	256
1a9676bb-9dbd-4b2e-a74b-109febba175c	79039562-eda7-4a5b-94cb-0cca2b078742	Cho thuê nhà nguyên căn Quận 7, khu Phú Mỹ Hưng	Nhà 1 trệt 2 lầu, đầy đủ tiện nghi, phù hợp cho gia đình hoặc làm văn phòng công ty.	Cho thuê nhà nguyên căn	dathue	chuacapnhat.png	2025-10-14 08:34:49.038555	1890
a35558f6-7238-4ed5-8c5a-7d557469bc9b	809f4af0-c6c4-4478-be5e-93c696302b7b	Bán đất thổ cư Bình Chánh, gần chợ	Đất 80m2, đã có sổ hồng riêng, xây dựng tự do. Khu dân cư hiện hữu, không vướng quy hoạch.	Bán đất thổ cư	dangban	chuacapnhat.png	2025-10-14 08:34:49.038555	732
6d008d54-cce7-4bc2-b2d4-e54b1bc2c08a	8e233604-c830-4df5-b863-29ec060327d3	Căn hộ dịch vụ cho thuê tại Thảo Điền, Quận 2	Studio 35m2, có ban công, nội thất cao cấp. Bao phí quản lý, wifi, dọn phòng. An ninh tốt.	Cho thuê căn hộ	dangban	chuacapnhat.png	2025-10-14 08:34:49.038555	1430
f39937da-3713-4443-968a-0bca5b6c15a6	92d1b032-a891-4176-87dd-cdeab7473d61	Nhà cấp 4 giá rẻ, Hóc Môn, TPHCM	Nhà cấp 4 diện tích 5x20m, có gác lửng. Gần khu công nghiệp, thích hợp cho công nhân.	Bán nhà cấp 4	daban	chuacapnhat.png	2025-10-14 08:34:49.038555	654
8576abbd-0a05-4f8e-aa2b-116cae851186	a78c47f4-b1c4-4313-b90a-8938f6ba8cac	Shophouse Vinhomes Grand Park cần sang nhượng	Vị trí đẹp, đối diện công viên 36ha. Thích hợp kinh doanh đa ngành nghề. Đang có hợp đồng thuê sẵn.	Bán Shophouse	choduyet	chuacapnhat.png	2025-10-14 08:34:49.038555	1987
7fc61a7b-2165-4d37-b623-92f4ae66d0fa	bc2ed64f-8ae4-4637-9632-75c5af63066c	Mảnh đất vườn nghỉ dưỡng tại Bảo Lộc, Lâm Đồng	Diện tích 500m2, view đồi chè, không khí trong lành. Thích hợp làm nhà vườn cuối tuần.	Bán đất vườn	dangban	chuacapnhat.png	2025-10-14 08:34:49.038555	450
eac799d3-e054-4876-b085-6e9b1e89c4ac	d6207506-d2af-4427-b599-7c51661f3bdd	Chung cư Ecopark, Hưng Yên, 1 phòng ngủ	Căn hộ 45m2, thiết kế tối giản, ban công view sân golf. Cần bán nhanh để chuyển công tác.	Bán chung cư	dangban	chuacapnhat.png	2025-10-14 08:34:49.038555	888
83feb182-9f25-4450-97e5-87e77c82ad62	e2ce533c-9b1a-4bc6-bb84-440192b688d3	Phòng trọ giá sinh viên gần Đại học Quốc gia	Phòng 15m2, có gác, an ninh, giờ giấc tự do. Chỉ cho nữ thuê. Điện nước giá nhà nước.	Cho thuê phòng trọ	dathue	chuacapnhat.png	2025-10-14 08:34:49.038555	1203
6e0a6004-d4c4-456b-b684-5940af6f8612	ed9f5adb-413b-43cc-81f1-99f0ca57b321	Bán nhà nát tiện xây mới, Quận Bình Thạnh	Diện tích công nhận 70m2, hẻm ba gác. Khu vực được phép xây 4 tầng. Giá đầu tư.	Bán nhà cũ	dangban	chuacapnhat.png	2025-10-14 08:34:49.038555	310
ea042ee8-b619-4605-8793-7c9b65226280	f64c8b9b-8b2a-4c16-87a0-9354988951d9	Mặt bằng kinh doanh đường Nguyễn Trãi, Quận 5	Mặt bằng 100m2, vỉa hè rộng. Khu kinh doanh thời trang sầm uất. Hợp đồng cho thuê dài hạn.	Cho thuê mặt bằng	choduyet	chuacapnhat.png	2025-10-14 08:34:49.038555	1620
\.


--
-- TOC entry 5154 (class 0 OID 17951)
-- Dependencies: 229
-- Data for Name: video_danh_gia_bds; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.video_danh_gia_bds (id, id_dg_bds, url, mo_ta) FROM stdin;
\.


--
-- TOC entry 5158 (class 0 OID 18098)
-- Dependencies: 233
-- Data for Name: yeu_cau; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.yeu_cau (id, id_nguoi_dung, loai, id_bds, trang_thai, ngay_tao, mo_ta_chi_tiet) FROM stdin;
431c04c2-299d-4b09-882f-d207e9daa84a	92d1b032-a891-4176-87dd-cdeab7473d61	ban	d61ca29b-11ea-4733-bbd7-a2fdd2bddb9f	choxuly	2025-09-18 22:05:11.924164	Tôi là chủ sở hữu và cần tư vấn các thủ tục cần thiết để có thể bán bất động sản này trong thời gian sớm nhất.
3f3c59bf-41d7-4127-9486-0c4cf193b31b	07121b39-ffae-4f5a-be25-d9af117b1a8c	thue	fdfe9f43-a19a-42b9-97bc-a8e9fed6b24b	dahuy	2025-09-05 07:31:35.928816	Tôi muốn biết thêm thông tin về việc thuê dài hạn bất động sản này. Vui lòng cung cấp chi tiết về hợp đồng và các chi phí liên quan.
a337b572-16b5-4434-81d4-dafa62d21e1a	fb9d3af2-aeef-46b1-a91c-438cf099a636	mua	90ec7744-8ac0-4e2c-a4a3-9b95776d46bc	choxuly	2025-09-19 14:09:16.725698	Tôi muốn biết thêm thông tin chi tiết về bất động sản này và muốn sắp xếp một buổi xem nhà vào cuối tuần.
98429639-9ee2-4f57-b618-6f8920e741c2	f64c8b9b-8b2a-4c16-87a0-9354988951d9	ban	fc080893-c646-4521-a9b7-c5541f166a58	daduyet	2025-09-25 22:17:49.548318	Tôi là chủ sở hữu và cần tư vấn các thủ tục cần thiết để có thể bán bất động sản này trong thời gian sớm nhất.
bb4b6448-468a-40a5-8b34-f6a5956cc65c	d6207506-d2af-4427-b599-7c51661f3bdd	thue	1014202c-2ab9-492b-81ff-338cbb2f0e8e	daduyet	2025-09-23 22:01:27.845498	Tôi muốn biết thêm thông tin về việc thuê dài hạn bất động sản này. Vui lòng cung cấp chi tiết về hợp đồng và các chi phí liên quan.
4a2455ac-7b5b-43dd-84f9-008d691439bc	a78c47f4-b1c4-4313-b90a-8938f6ba8cac	ban	3049148d-1d50-45b0-a5d5-4012798dbc16	dahuy	2025-08-24 07:58:50.575834	Tôi là chủ sở hữu và cần tư vấn các thủ tục cần thiết để có thể bán bất động sản này trong thời gian sớm nhất.
abd70ae0-340f-4dcf-b730-bec5b33f8316	bc2ed64f-8ae4-4637-9632-75c5af63066c	ban	d87c88bf-3bb0-44f4-ad78-a35bf01a2410	dahuy	2025-09-23 02:09:39.435334	Tôi là chủ sở hữu và cần tư vấn các thủ tục cần thiết để có thể bán bất động sản này trong thời gian sớm nhất.
c183b36c-0ff3-4e46-82f5-3713a09c56fb	34a86257-d43c-44e7-88ab-bccabdd5c644	thue	c7b5c0d3-7779-4299-a40b-4f104d4845ef	daduyet	2025-09-16 17:42:56.528633	Tôi muốn biết thêm thông tin về việc thuê dài hạn bất động sản này. Vui lòng cung cấp chi tiết về hợp đồng và các chi phí liên quan.
1885681e-33a3-450a-bbef-1f743743a28a	79039562-eda7-4a5b-94cb-0cca2b078742	ban	d5793b54-78d7-4033-8647-0f3ccccda434	dahuy	2025-08-19 14:57:38.706704	Tôi là chủ sở hữu và cần tư vấn các thủ tục cần thiết để có thể bán bất động sản này trong thời gian sớm nhất.
af1c8121-ea00-48de-bcfa-179c21cd5a51	5ee334f1-bd31-41b7-8672-df8903f2747a	ban	5b0981ed-37ce-4729-8bb7-2da081ee2b36	daduyet	2025-09-01 02:46:12.261504	Tôi là chủ sở hữu và cần tư vấn các thủ tục cần thiết để có thể bán bất động sản này trong thời gian sớm nhất.
8e6ad076-5914-4a3b-ab74-5f20d1cf7ba9	2a11d082-fc6e-4713-9738-8650e95844f4	ban	4cf78123-afc6-461a-a49e-87ace9b3b508	dahuy	2025-10-07 01:10:11.891012	Tôi là chủ sở hữu và cần tư vấn các thủ tục cần thiết để có thể bán bất động sản này trong thời gian sớm nhất.
0da1a623-b569-4200-80fc-f757e9f47135	234a3664-f36a-442b-9bc2-a111ae14c1dc	ban	f141e5f0-7fff-4713-960b-da42f16f8465	choxuly	2025-09-01 04:23:33.855842	Tôi là chủ sở hữu và cần tư vấn các thủ tục cần thiết để có thể bán bất động sản này trong thời gian sớm nhất.
b9a9c14a-e2d0-45ce-bf70-d1f3cfc0f184	809f4af0-c6c4-4478-be5e-93c696302b7b	ban	d47acb24-261b-47b6-8110-bbb6b59fa7d0	daduyet	2025-09-26 00:58:52.985038	Tôi là chủ sở hữu và cần tư vấn các thủ tục cần thiết để có thể bán bất động sản này trong thời gian sớm nhất.
de77ef96-117c-45ed-af7b-0114efd7a302	17716784-ec18-43b4-b8cb-a784d1127421	thue	a14b3837-0b85-4403-bcb2-db93996fc185	choxuly	2025-09-19 02:39:56.745165	Tôi muốn biết thêm thông tin về việc thuê dài hạn bất động sản này. Vui lòng cung cấp chi tiết về hợp đồng và các chi phí liên quan.
87f8f91b-3b67-44ef-82ad-b2395781fb8a	8e233604-c830-4df5-b863-29ec060327d3	mua	5799c0c2-0bee-43e0-a580-74c5fddad75c	dahuy	2025-08-31 02:11:10.732797	Tôi muốn biết thêm thông tin chi tiết về bất động sản này và muốn sắp xếp một buổi xem nhà vào cuối tuần.
02ae50b8-0b65-4b6b-821a-6525e8a2307f	3ebc4930-2923-4085-b9ca-192f087ba6bf	mua	fabe2a78-1231-4543-827e-acfa16f6df1f	choxuly	2025-10-13 21:39:11.231874	Tôi muốn biết thêm thông tin chi tiết về bất động sản này và muốn sắp xếp một buổi xem nhà vào cuối tuần.
3752577b-5554-4943-a774-d567fb099181	e2ce533c-9b1a-4bc6-bb84-440192b688d3	thue	d558834d-66c1-4eb5-aa02-cc35b2b46d76	daduyet	2025-08-29 03:48:15.245443	Tôi muốn biết thêm thông tin về việc thuê dài hạn bất động sản này. Vui lòng cung cấp chi tiết về hợp đồng và các chi phí liên quan.
6542b951-090b-4a6f-8472-ee6122381dd1	ed9f5adb-413b-43cc-81f1-99f0ca57b321	thue	aa316098-b32d-4e11-b2aa-e3dfbe56e800	daduyet	2025-09-29 04:29:35.426012	Tôi muốn biết thêm thông tin về việc thuê dài hạn bất động sản này. Vui lòng cung cấp chi tiết về hợp đồng và các chi phí liên quan.
7d63f8b4-2fb9-46d2-9e70-54845f04978f	92d1b032-a891-4176-87dd-cdeab7473d61	ban	8109b350-cfef-45a4-b7a7-7c4b0d413bb1	dahuy	2025-09-25 06:28:08.358763	Tôi là chủ sở hữu và cần tư vấn các thủ tục cần thiết để có thể bán bất động sản này trong thời gian sớm nhất.
1b509a85-4f5e-4a34-a8d7-f06a36c4739a	07121b39-ffae-4f5a-be25-d9af117b1a8c	ban	11dd94ce-d64b-4bf2-bde5-e484fbfd5e5d	dahuy	2025-09-24 11:21:17.108636	Tôi là chủ sở hữu và cần tư vấn các thủ tục cần thiết để có thể bán bất động sản này trong thời gian sớm nhất.
\.


--
-- TOC entry 5153 (class 0 OID 17901)
-- Dependencies: 228
-- Data for Name: yeu_cau_otp; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.yeu_cau_otp (id, so_dt, email, trang_thai, bat_dau, het_han, cap_nhat, token_code, user_data_json, otp_hash) FROM stdin;
\.


--
-- TOC entry 4966 (class 2606 OID 26670)
-- Name: bai_dang bai_dang_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.bai_dang
    ADD CONSTRAINT bai_dang_pkey PRIMARY KEY (id);


--
-- TOC entry 4957 (class 2606 OID 26559)
-- Name: bat_dong_san bat_dong_san_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.bat_dong_san
    ADD CONSTRAINT bat_dong_san_pkey PRIMARY KEY (id);


--
-- TOC entry 4935 (class 2606 OID 18087)
-- Name: bieu_mau bieu_mau_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.bieu_mau
    ADD CONSTRAINT bieu_mau_pkey PRIMARY KEY (id);


--
-- TOC entry 4921 (class 2606 OID 17857)
-- Name: danh_gia_bds danh_gia_bds_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.danh_gia_bds
    ADD CONSTRAINT danh_gia_bds_pkey PRIMARY KEY (id);


--
-- TOC entry 4933 (class 2606 OID 17992)
-- Name: danh_gia_mg danh_gia_mg_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.danh_gia_mg
    ADD CONSTRAINT danh_gia_mg_pkey PRIMARY KEY (id);


--
-- TOC entry 4953 (class 2606 OID 26452)
-- Name: dot_thanh_toan_ct dot_thanh_toan_ct_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.dot_thanh_toan_ct
    ADD CONSTRAINT dot_thanh_toan_ct_pkey PRIMARY KEY (id);


--
-- TOC entry 4949 (class 2606 OID 26439)
-- Name: dot_thanh_toan dot_thanh_toan_id_giao_dich_lan_tt_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.dot_thanh_toan
    ADD CONSTRAINT dot_thanh_toan_id_giao_dich_lan_tt_key UNIQUE (id_giao_dich, lan_tt);


--
-- TOC entry 4951 (class 2606 OID 26437)
-- Name: dot_thanh_toan dot_thanh_toan_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.dot_thanh_toan
    ADD CONSTRAINT dot_thanh_toan_pkey PRIMARY KEY (id);


--
-- TOC entry 4915 (class 2606 OID 17803)
-- Name: giao_dich giao_dich_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.giao_dich
    ADD CONSTRAINT giao_dich_pkey PRIMARY KEY (id);


--
-- TOC entry 4960 (class 2606 OID 26576)
-- Name: hinh_anh_bds hinh_anh_bds_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.hinh_anh_bds
    ADD CONSTRAINT hinh_anh_bds_pkey PRIMARY KEY (id);


--
-- TOC entry 4962 (class 2606 OID 26630)
-- Name: hinh_anh_danh_gia_bds hinh_anh_danh_gia_bds_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.hinh_anh_danh_gia_bds
    ADD CONSTRAINT hinh_anh_danh_gia_bds_pkey PRIMARY KEY (id);


--
-- TOC entry 4939 (class 2606 OID 26359)
-- Name: hop_thoai hop_thoai_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.hop_thoai
    ADD CONSTRAINT hop_thoai_pkey PRIMARY KEY (id);


--
-- TOC entry 4911 (class 2606 OID 17764)
-- Name: info_nguoi_dung info_nguoi_dung_id_nguoi_dung_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.info_nguoi_dung
    ADD CONSTRAINT info_nguoi_dung_id_nguoi_dung_key UNIQUE (id_nguoi_dung);


--
-- TOC entry 4913 (class 2606 OID 17762)
-- Name: info_nguoi_dung info_nguoi_dung_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.info_nguoi_dung
    ADD CONSTRAINT info_nguoi_dung_pkey PRIMARY KEY (id);


--
-- TOC entry 4945 (class 2606 OID 26422)
-- Name: ke_hoach_thanh_toan ke_hoach_thanh_toan_id_giao_dich_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.ke_hoach_thanh_toan
    ADD CONSTRAINT ke_hoach_thanh_toan_id_giao_dich_key UNIQUE (id_giao_dich);


--
-- TOC entry 4947 (class 2606 OID 26420)
-- Name: ke_hoach_thanh_toan ke_hoach_thanh_toan_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.ke_hoach_thanh_toan
    ADD CONSTRAINT ke_hoach_thanh_toan_pkey PRIMARY KEY (id);


--
-- TOC entry 4955 (class 2606 OID 26502)
-- Name: lich_su lich_su_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.lich_su
    ADD CONSTRAINT lich_su_pkey PRIMARY KEY (id);


--
-- TOC entry 4925 (class 2606 OID 17895)
-- Name: lich_su_xac_thuc lich_su_xac_thuc_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.lich_su_xac_thuc
    ADD CONSTRAINT lich_su_xac_thuc_pkey PRIMARY KEY (id);


--
-- TOC entry 4964 (class 2606 OID 26647)
-- Name: lich_trinh lich_trinh_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.lich_trinh
    ADD CONSTRAINT lich_trinh_pkey PRIMARY KEY (id);


--
-- TOC entry 4901 (class 2606 OID 17730)
-- Name: nguoi_dung nguoi_dung_email_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.nguoi_dung
    ADD CONSTRAINT nguoi_dung_email_key UNIQUE (email);


--
-- TOC entry 4903 (class 2606 OID 17726)
-- Name: nguoi_dung nguoi_dung_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.nguoi_dung
    ADD CONSTRAINT nguoi_dung_pkey PRIMARY KEY (id);


--
-- TOC entry 4905 (class 2606 OID 17728)
-- Name: nguoi_dung nguoi_dung_ten_dang_nhap_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.nguoi_dung
    ADD CONSTRAINT nguoi_dung_ten_dang_nhap_key UNIQUE (ten_dang_nhap);


--
-- TOC entry 4907 (class 2606 OID 17738)
-- Name: phan_quyen phan_quyen_id_nguoi_dung_id_quyen_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.phan_quyen
    ADD CONSTRAINT phan_quyen_id_nguoi_dung_id_quyen_key UNIQUE (id_nguoi_dung, id_quyen);


--
-- TOC entry 4909 (class 2606 OID 17736)
-- Name: phan_quyen phan_quyen_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.phan_quyen
    ADD CONSTRAINT phan_quyen_pkey PRIMARY KEY (id);


--
-- TOC entry 4923 (class 2606 OID 17877)
-- Name: phien_dang_nhap phien_dang_nhap_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.phien_dang_nhap
    ADD CONSTRAINT phien_dang_nhap_pkey PRIMARY KEY (id);


--
-- TOC entry 4897 (class 2606 OID 17707)
-- Name: quyen quyen_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.quyen
    ADD CONSTRAINT quyen_pkey PRIMARY KEY (id);


--
-- TOC entry 4899 (class 2606 OID 17709)
-- Name: quyen quyen_vai_tro_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.quyen
    ADD CONSTRAINT quyen_vai_tro_key UNIQUE (vai_tro);


--
-- TOC entry 4919 (class 2606 OID 17835)
-- Name: thanh_toan_ct thanh_toan_ct_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.thanh_toan_ct
    ADD CONSTRAINT thanh_toan_ct_pkey PRIMARY KEY (id);


--
-- TOC entry 4917 (class 2606 OID 17822)
-- Name: thanh_toan thanh_toan_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.thanh_toan
    ADD CONSTRAINT thanh_toan_pkey PRIMARY KEY (id);


--
-- TOC entry 4931 (class 2606 OID 17975)
-- Name: thong_bao thong_bao_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.thong_bao
    ADD CONSTRAINT thong_bao_pkey PRIMARY KEY (id);


--
-- TOC entry 4941 (class 2606 OID 26369)
-- Name: tin_nhan tin_nhan_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.tin_nhan
    ADD CONSTRAINT tin_nhan_pkey PRIMARY KEY (id);


--
-- TOC entry 4943 (class 2606 OID 26401)
-- Name: tin_tuc tin_tuc_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.tin_tuc
    ADD CONSTRAINT tin_tuc_pkey PRIMARY KEY (id);


--
-- TOC entry 4929 (class 2606 OID 17958)
-- Name: video_danh_gia_bds video_danh_gia_bds_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.video_danh_gia_bds
    ADD CONSTRAINT video_danh_gia_bds_pkey PRIMARY KEY (id);


--
-- TOC entry 4927 (class 2606 OID 17912)
-- Name: yeu_cau_otp yeu_cau_otp_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.yeu_cau_otp
    ADD CONSTRAINT yeu_cau_otp_pkey PRIMARY KEY (id);


--
-- TOC entry 4937 (class 2606 OID 18107)
-- Name: yeu_cau yeu_cau_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.yeu_cau
    ADD CONSTRAINT yeu_cau_pkey PRIMARY KEY (id);


--
-- TOC entry 4958 (class 1259 OID 26601)
-- Name: idx_bds_fts; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_bds_fts ON public.bat_dong_san USING gin (to_tsvector('simple'::regconfig, (((((tieu_de)::text || ' '::text) || mo_ta) || ' '::text) || dia_chi)));


--
-- TOC entry 4997 (class 2620 OID 26508)
-- Name: nguoi_dung trigger_tao_info_nguoi_dung; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER trigger_tao_info_nguoi_dung AFTER INSERT ON public.nguoi_dung FOR EACH ROW EXECUTE FUNCTION public.tao_info_nguoi_dung_trigger_func();


--
-- TOC entry 4995 (class 2606 OID 26676)
-- Name: bai_dang fk_baidang_bds; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.bai_dang
    ADD CONSTRAINT fk_baidang_bds FOREIGN KEY (id_bat_dong_san) REFERENCES public.bat_dong_san(id) ON DELETE CASCADE;


--
-- TOC entry 4996 (class 2606 OID 26671)
-- Name: bai_dang fk_baidang_nguoidung; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.bai_dang
    ADD CONSTRAINT fk_baidang_nguoidung FOREIGN KEY (id_nguoi_dung) REFERENCES public.nguoi_dung(id) ON DELETE CASCADE;


--
-- TOC entry 4990 (class 2606 OID 26560)
-- Name: bat_dong_san fk_bds_nguoi_dung; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.bat_dong_san
    ADD CONSTRAINT fk_bds_nguoi_dung FOREIGN KEY (id_nguoi_dung) REFERENCES public.nguoi_dung(id) ON DELETE CASCADE;


--
-- TOC entry 4980 (class 2606 OID 18093)
-- Name: bieu_mau fk_benban; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.bieu_mau
    ADD CONSTRAINT fk_benban FOREIGN KEY (ben_ban) REFERENCES public.nguoi_dung(id) ON DELETE CASCADE;


--
-- TOC entry 4981 (class 2606 OID 18088)
-- Name: bieu_mau fk_benmua; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.bieu_mau
    ADD CONSTRAINT fk_benmua FOREIGN KEY (ben_mua) REFERENCES public.nguoi_dung(id) ON DELETE CASCADE;


--
-- TOC entry 4973 (class 2606 OID 17858)
-- Name: danh_gia_bds fk_danh_gia_bds_nd; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.danh_gia_bds
    ADD CONSTRAINT fk_danh_gia_bds_nd FOREIGN KEY (id_nguoi_dung) REFERENCES public.nguoi_dung(id) ON DELETE SET NULL;


--
-- TOC entry 4978 (class 2606 OID 17993)
-- Name: danh_gia_mg fk_danh_gia_kh_nd; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.danh_gia_mg
    ADD CONSTRAINT fk_danh_gia_kh_nd FOREIGN KEY (id_khach_hang) REFERENCES public.nguoi_dung(id) ON DELETE CASCADE;


--
-- TOC entry 4979 (class 2606 OID 17998)
-- Name: danh_gia_mg fk_danh_gia_mg_nd; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.danh_gia_mg
    ADD CONSTRAINT fk_danh_gia_mg_nd FOREIGN KEY (id_moi_gioi) REFERENCES public.nguoi_dung(id) ON DELETE CASCADE;


--
-- TOC entry 4988 (class 2606 OID 26440)
-- Name: dot_thanh_toan fk_dtt_gd; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.dot_thanh_toan
    ADD CONSTRAINT fk_dtt_gd FOREIGN KEY (id_giao_dich) REFERENCES public.giao_dich(id) ON DELETE CASCADE;


--
-- TOC entry 4989 (class 2606 OID 26453)
-- Name: dot_thanh_toan_ct fk_dttct_dtt; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.dot_thanh_toan_ct
    ADD CONSTRAINT fk_dttct_dtt FOREIGN KEY (id_dot_thanh_toan) REFERENCES public.dot_thanh_toan(id) ON DELETE CASCADE;


--
-- TOC entry 4970 (class 2606 OID 17804)
-- Name: giao_dich fk_giao_dich_nd; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.giao_dich
    ADD CONSTRAINT fk_giao_dich_nd FOREIGN KEY (id_nguoi_dung) REFERENCES public.nguoi_dung(id) ON DELETE SET NULL;


--
-- TOC entry 4983 (class 2606 OID 26370)
-- Name: tin_nhan fk_gui; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.tin_nhan
    ADD CONSTRAINT fk_gui FOREIGN KEY (nguoi_gui) REFERENCES public.nguoi_dung(id) ON DELETE CASCADE;


--
-- TOC entry 4991 (class 2606 OID 26577)
-- Name: hinh_anh_bds fk_hinh_anh_bds_bds; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.hinh_anh_bds
    ADD CONSTRAINT fk_hinh_anh_bds_bds FOREIGN KEY (id_bds) REFERENCES public.bat_dong_san(id) ON DELETE CASCADE;


--
-- TOC entry 4992 (class 2606 OID 26631)
-- Name: hinh_anh_danh_gia_bds fk_hinh_dg; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.hinh_anh_danh_gia_bds
    ADD CONSTRAINT fk_hinh_dg FOREIGN KEY (id_dg_bds) REFERENCES public.danh_gia_bds(id) ON DELETE CASCADE;


--
-- TOC entry 4984 (class 2606 OID 26380)
-- Name: tin_nhan fk_id_hop_thoai; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.tin_nhan
    ADD CONSTRAINT fk_id_hop_thoai FOREIGN KEY (id_hop_thoai) REFERENCES public.hop_thoai(id) ON DELETE CASCADE;


--
-- TOC entry 4969 (class 2606 OID 17765)
-- Name: info_nguoi_dung fk_info_nd; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.info_nguoi_dung
    ADD CONSTRAINT fk_info_nd FOREIGN KEY (id_nguoi_dung) REFERENCES public.nguoi_dung(id) ON DELETE CASCADE;


--
-- TOC entry 4987 (class 2606 OID 26423)
-- Name: ke_hoach_thanh_toan fk_khtt_gd; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.ke_hoach_thanh_toan
    ADD CONSTRAINT fk_khtt_gd FOREIGN KEY (id_giao_dich) REFERENCES public.giao_dich(id) ON DELETE CASCADE;


--
-- TOC entry 4975 (class 2606 OID 17896)
-- Name: lich_su_xac_thuc fk_lich_su_xac_thuc_nguoi_dung; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.lich_su_xac_thuc
    ADD CONSTRAINT fk_lich_su_xac_thuc_nguoi_dung FOREIGN KEY (id_nguoi_dung) REFERENCES public.nguoi_dung(id) ON DELETE CASCADE;


--
-- TOC entry 4993 (class 2606 OID 26648)
-- Name: lich_trinh fk_lichtrinh_khachhang; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.lich_trinh
    ADD CONSTRAINT fk_lichtrinh_khachhang FOREIGN KEY (id_khach_hang) REFERENCES public.nguoi_dung(id) ON DELETE CASCADE;


--
-- TOC entry 4994 (class 2606 OID 26653)
-- Name: lich_trinh fk_lichtrinh_moigioi; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.lich_trinh
    ADD CONSTRAINT fk_lichtrinh_moigioi FOREIGN KEY (id_moi_gioi) REFERENCES public.nguoi_dung(id) ON DELETE CASCADE;


--
-- TOC entry 4985 (class 2606 OID 26375)
-- Name: tin_nhan fk_nhan; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.tin_nhan
    ADD CONSTRAINT fk_nhan FOREIGN KEY (nguoi_nhan) REFERENCES public.nguoi_dung(id) ON DELETE CASCADE;


--
-- TOC entry 4967 (class 2606 OID 17739)
-- Name: phan_quyen fk_phan_quyen_nguoi_dung; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.phan_quyen
    ADD CONSTRAINT fk_phan_quyen_nguoi_dung FOREIGN KEY (id_nguoi_dung) REFERENCES public.nguoi_dung(id) ON DELETE CASCADE;


--
-- TOC entry 4968 (class 2606 OID 17744)
-- Name: phan_quyen fk_phan_quyen_quyen; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.phan_quyen
    ADD CONSTRAINT fk_phan_quyen_quyen FOREIGN KEY (id_quyen) REFERENCES public.quyen(id) ON DELETE CASCADE;


--
-- TOC entry 4974 (class 2606 OID 17880)
-- Name: phien_dang_nhap fk_phien_dang_nhap_nguoi_dung; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.phien_dang_nhap
    ADD CONSTRAINT fk_phien_dang_nhap_nguoi_dung FOREIGN KEY (id_nguoi_dung) REFERENCES public.nguoi_dung(id) ON DELETE CASCADE;


--
-- TOC entry 4977 (class 2606 OID 17976)
-- Name: thong_bao fk_thong_bao_nd; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.thong_bao
    ADD CONSTRAINT fk_thong_bao_nd FOREIGN KEY (id_nguoi_dung) REFERENCES public.nguoi_dung(id) ON DELETE CASCADE;


--
-- TOC entry 4986 (class 2606 OID 26402)
-- Name: tin_tuc fk_tin_khachhang; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.tin_tuc
    ADD CONSTRAINT fk_tin_khachhang FOREIGN KEY (id_khach_hang) REFERENCES public.nguoi_dung(id) ON DELETE CASCADE;


--
-- TOC entry 4971 (class 2606 OID 17823)
-- Name: thanh_toan fk_tt_gd; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.thanh_toan
    ADD CONSTRAINT fk_tt_gd FOREIGN KEY (id_giao_dich) REFERENCES public.giao_dich(id) ON DELETE CASCADE;


--
-- TOC entry 4972 (class 2606 OID 17836)
-- Name: thanh_toan_ct fk_ttc_tt; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.thanh_toan_ct
    ADD CONSTRAINT fk_ttc_tt FOREIGN KEY (id_thanh_toan) REFERENCES public.thanh_toan(id) ON DELETE CASCADE;


--
-- TOC entry 4976 (class 2606 OID 17959)
-- Name: video_danh_gia_bds fk_video_dg; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.video_danh_gia_bds
    ADD CONSTRAINT fk_video_dg FOREIGN KEY (id_dg_bds) REFERENCES public.danh_gia_bds(id) ON DELETE CASCADE;


--
-- TOC entry 4982 (class 2606 OID 18108)
-- Name: yeu_cau fk_yeucau_nguoidung; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.yeu_cau
    ADD CONSTRAINT fk_yeucau_nguoidung FOREIGN KEY (id_nguoi_dung) REFERENCES public.nguoi_dung(id) ON DELETE CASCADE;


--
-- TOC entry 5177 (class 0 OID 0)
-- Dependencies: 6
-- Name: SCHEMA public; Type: ACL; Schema: -; Owner: postgres
--

REVOKE USAGE ON SCHEMA public FROM PUBLIC;


-- Completed on 2025-10-15 12:42:46

--
-- PostgreSQL database dump complete
--

