<?php
// process.php

// Thông tin kết nối SQL Server
$servername = "DESKTOP-EO0I3PO\\DAK";  // Thay bằng tên server của bạn
$database = "Chu_Ky_So";      // Thay bằng tên cơ sở dữ liệu của bạn

try {
    // Kết nối đến SQL Server bằng PDO ODBC và Integrated Security
    $conn = new PDO("odbc:Driver={SQL Server};Server=$servername;Database=$database;Trusted_Connection=yes");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Kết nối SQL Server thành công!<br>";

    // Lấy dữ liệu từ form
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $name = $_POST["name"] ?? null;
        $tel = $_POST["tel"] ?? null;
        $cccd = $_POST["CCCD"] ?? null;

        if ($name && $tel && $cccd) {
            // Chuẩn bị câu lệnh SQL
            $sql = "INSERT INTO Thong_tin_ca_nhan (Ho_va_ten, so_dien_thoai, CCCD) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($sql);

            // Thực thi câu lệnh SQL với tham số
            $stmt->execute([$name, $tel, $cccd]);

            echo "Dữ liệu đã được lưu thành công";
        } else {
            echo "Vui lòng nhập đầy đủ thông tin!";
        }
    } else {
        echo "Không có dữ liệu POST được gửi.";
    }

} catch (PDOException $e) {
    echo "Lỗi kết nối hoặc truy vấn SQL: " . $e->getMessage();
}

$conn = null;
?>