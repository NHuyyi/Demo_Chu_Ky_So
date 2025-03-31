<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="stylesheet" href="./asset/css/reset.css" />
  <link rel="stylesheet" href="./asset/css/style.css" />
  <title>Tạo Chữ Ký Số</title>
</head>
<body>
  <div class="container">
    <div class="container__header">
      <div class="form__title"><h1>Nhập thông tin cá nhân</h1></div>
      <?php
      // Thông tin kết nối SQL Server
      $servername = "DESKTOP-EO0I3PO\\DAK";  // Thay bằng tên server của bạn
      $database = "Chu_Ky_So";      // Thay bằng tên cơ sở dữ liệu của bạn

      try {
          // Kết nối đến SQL Server bằng PDO ODBC và Integrated Security
          $conn = new PDO("odbc:Driver={SQL Server};Server=$servername;Database=$database;Trusted_Connection=yes");
          $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
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

                  echo "<p class='thong_bao dung'>Dữ liệu đã được lưu thành công</p>";
              }
          } 

      } catch (PDOException $e) {
          echo "<p class='thong_bao sai'>Lỗi kết nối hoặc truy vấn SQL: " . $e->getMessage() . "</p>";
      }

      $conn = null;
      ?>
      <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" class="form__input">
        <div class="form__input--name">
          <label for="name">Nhập tên của bạn</label>
          <input
            type="text"
            id="name"
            class="name"
            name="name"
            placeholder="Nguyễn Văn A"
            required
          />
        </div>
        <div class="form__input--tel">
          <label for="tel">Nhập Số điện thoại của bạn</label>
          <input
            type="tel"
            id="tel"
            class="tel"
            name="tel"
            placeholder="0xxxxxxxxx"
            required
          />
        </div>
        <div class="form__input--CCCD">
          <label for="CCCD">Nhập số căn cước công dân</label>
          <input
            type="text"
            class="CCCD"
            id="CCCD"
            name="CCCD"
            placeholder="xxxxxxxxxxxx"
            required
          />
        </div>
        <div class="form__input--IMG">
          <label for="IMG">Tải ảnh muốn làm chữ ký số</label>
          <input
            type="file"
            class="IMG"
            id="IMG"
            name="IMG"
            accept="image/*"
            required
          />
        </div>
        <div class="form__input--submit">
          <input type="submit" value="xác nhận" class="submit" />
        </div>
      </form>
    </div>
  </div>
</body>
</html>