<?php
// filepath: d:\hoc_tap\project\chu_ky_so\index.php
session_start();
?>

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
      if (isset($_SESSION['thong_bao'])) {
          echo $_SESSION['thong_bao'];
          unset($_SESSION['thong_bao']); // Xóa thông báo khỏi session
      }
      ?>
      <form action="process.php" method="post" class="form__input" enctype="multipart/form-data">
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
            <?php
      
      if (isset($_SESSION['image_encoded_path'])) {
          $image_encoded_path = $_SESSION['image_encoded_path'];
          echo "<h2 class='desc'>Chữ ký số của bạn:</h2>";
          echo "<img class='img_ma_hoa' src='" . htmlspecialchars($image_encoded_path) . "' alt='Ảnh đã mã hóa'>";
          unset($_SESSION['image_encoded_path']); // Xóa session sau khi hiển thị
      }
      ?>
    </div>
  </div>

  <script>
document.addEventListener("DOMContentLoaded", function() {
  const thongBaos = document.querySelectorAll(".thong_bao");
  thongBaos.forEach(thongBao => {
    setTimeout(() => {
      thongBao.classList.add("an");
    }, 2000);
  });
});
</script>
</body>
</html>