use Chu_Ky_So;


-- CREATE TABLE Thong_tin_ca_nhan (
--     STT INT PRIMARY KEY IDENTITY(1,1), -- Thêm IDENTITY để tự động tăng
--     Ho_va_ten VARCHAR(255) NOT NULL,
--     so_dien_thoai VARCHAR(20) NOT NULL,
--     CCCD VARCHAR(50) NOT NULL,
--     Hinh_anh VARCHAR(255) -- Thêm cột Hinh_anh
-- );


-- DROP TABLE Thong_tin_ca_nhan;
-- TRUNCATE TABLE Thong_tin_ca_nhan;
-- SELECT * FROM Thong_tin_ca_nhan;

-- CREATE TABLE Chu_Ky (
--     STT INT PRIMARY KEY IDENTITY(1,1),
--     CCCD_ma_hoa VARBINARY(MAX) NOT NULL,
--     Hinh_anh_ma_hoa VARBINARY(MAX)
-- );
TRUNCATE TABLE Chu_Ky;
select* from Chu_Ky;