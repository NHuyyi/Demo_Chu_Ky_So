<?php
// filepath: d:\hoc_tap\project\chu_ky_so\process.php
session_start();

// Khóa bí mật (nên lưu trữ ở nơi an toàn, không nên đặt trực tiếp trong code)
$encryption_key = bin2hex(random_bytes(32));

/**
 * Mã hóa số CCCD sử dụng AES-256-CBC.
 *
 * @param string $cccd Số CCCD cần mã hóa.
 * @param string $key Khóa mã hóa (32 byte).
 *
 * @return string|false Chuỗi đã mã hóa hoặc false nếu có lỗi.
 */
function encrypt_cccd(string $cccd, string $key): string|false
{
    $cipher = 'aes-256-cbc';
    $ivlen = openssl_cipher_iv_length($cipher);

    if (is_null($ivlen)) {
        return false;
    }

    $iv = openssl_random_pseudo_bytes($ivlen);

    if ($iv === false) {
        return false;
    }

    $ciphertext = openssl_encrypt($cccd, $cipher, $key, OPENSSL_RAW_DATA, $iv);

    if ($ciphertext === false) {
        return false;
    }

    // Trả về IV và ciphertext đã được base64 encode để dễ dàng lưu trữ và truyền tải
    $iv_base64 = base64_encode($iv);
    $ciphertext_base64 = base64_encode($ciphertext);

    return $iv_base64 . ':' . $ciphertext_base64;
}

/**
 * Chèn dữ liệu vào ảnh bằng phương pháp LSB.
 *
 * @param string $image_path Đường dẫn đến file ảnh.
 * @param string $data Dữ liệu cần chèn.
 * @param string $output_path Đường dẫn để lưu ảnh đã chèn dữ liệu.
 *
 * @return bool True nếu thành công, false nếu thất bại.
 */
function embed_data_in_image(string $image_path, string $data, string $output_path): bool
{
    // 1. Đọc ảnh
    $image = imagecreatefromstring(file_get_contents($image_path));
    if (!$image) {
        error_log("Không thể đọc file ảnh: " . $image_path);
        return false;
    }

    $width = imagesx($image);
    $height = imagesy($image);

    // 2. Chuyển đổi dữ liệu thành chuỗi bit
    $binary_data = '';
    for ($i = 0; $i < strlen($data); $i++) {
        $binary_data .= str_pad(decbin(ord($data[$i])), 8, '0', STR_PAD_LEFT);
    }

    // Thêm dấu hiệu kết thúc để biết khi nào dừng đọc dữ liệu
    $binary_data .= '1111111111111110'; // Dấu hiệu kết thúc (16 bit)

    $data_length = strlen($binary_data);

    $pixel_index = 0;
    $bit_index = 0;

    // 3. Chèn dữ liệu vào các bit LSB của pixel
    for ($y = 0; $y < $height; $y++) {
        for ($x = 0; $x < $width; $x++) {
            if ($bit_index < $data_length) {
                $pixel = imagecolorat($image, $x, $y);
                $colors = imagecolorsforindex($image, $pixel);

                $red = $colors['red'];
                $green = $colors['green'];
                $blue = $colors['blue'];

                // Thay đổi bit LSB của mỗi kênh màu
                $red = ($red & 0xFE) | (int)$binary_data[$bit_index % $data_length];
                $bit_index++;

                if ($bit_index < $data_length) {
                    $green = ($green & 0xFE) | (int)$binary_data[$bit_index % $data_length];
                    $bit_index++;
                }

                if ($bit_index < $data_length) {
                    $blue = ($blue & 0xFE) | (int)$binary_data[$bit_index % $data_length];
                    $bit_index++;
                }

                // Cấp phát màu mới
                $new_color = imagecolorallocate($image, $red, $green, $blue);

                // Đặt màu mới cho pixel
                imagesetpixel($image, $x, $y, $new_color);
            } else {
                break 2; // Dừng khi đã chèn hết dữ liệu
            }
        }
    }

    // 4. Lưu ảnh đã chèn dữ liệu
    $result = imagepng($image, $output_path);

    // Giải phóng bộ nhớ
    imagedestroy($image);

    return $result;
}

// Thông tin kết nối SQL Server
$servername = "DESKTOP-EO0I3PO\\DAK";  // Thay bằng tên server của bạn
$database = "Chu_Ky_So";      // Thay bằng tên cơ sở dữ liệu của bạn

try {
    // Kết nối đến SQL Server bằng PDO ODBC và Integrated Security
    $conn = new PDO("odbc:Driver={SQL Server};Server=$servername;Database=$database;Trusted_Connection=yes");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $name = htmlspecialchars($_POST["name"] ?? null);
        $tel = htmlspecialchars($_POST["tel"] ?? null);
        $cccd = htmlspecialchars($_POST["CCCD"] ?? null);

        // Kiểm tra xem CCCD đã tồn tại trong cơ sở dữ liệu chưa
        $sql_check = "SELECT COUNT(*) FROM Thong_tin_ca_nhan WHERE CCCD = ?";
        $stmt_check = $conn->prepare($sql_check);
        $stmt_check->execute([$cccd]);
        $count = $stmt_check->fetchColumn();

        if ($count > 0) {
            $_SESSION['thong_bao'] = "<p class='thong_bao sai'>Lỗi: CCCD đã được đăng ký.</p>";
        } else {

            if (isset($_FILES["IMG"]) && $_FILES["IMG"]["error"] == 0) {
                $allowed = array("jpg" => "image/jpg", "jpeg" => "image/jpeg", "gif" => "image/gif", "png" => "image/png");
                $filename = $_FILES["IMG"]["name"];
                $filetype = $_FILES["IMG"]["type"];
                $filesize = $_FILES["IMG"]["size"];
              
                $ext = pathinfo($filename, PATHINFO_EXTENSION);
                if(!array_key_exists($ext, $allowed)) {
                    $_SESSION['thong_bao'] = "<p class='thong_bao sai'>Lỗi: Vui lòng chọn đúng định dạng ảnh.</p>";
                } else {

                    $maxsize = 5 * 1024 * 1024;
                    if($filesize > $maxsize) {
                        $_SESSION['thong_bao'] = "<p class='thong_bao sai'>Lỗi: Kích thước ảnh vượt quá giới hạn cho phép.</p>";
                    } else {

                        if(in_array($filetype, $allowed)){
                            $target_dir = "img/";
                            if (!file_exists($target_dir)) {
                                mkdir($target_dir, 0777, true);
                            }
                            $target_file = $target_dir . basename($filename);

                            if(move_uploaded_file($_FILES["IMG"]["tmp_name"], $target_file)){
                                if ($name && $tel && $cccd) {
                                    $sql = "INSERT INTO Thong_tin_ca_nhan (Ho_va_ten, so_dien_thoai, CCCD, Hinh_anh) VALUES (?, ?, ?, ?)";
                                    $stmt = $conn->prepare($sql);
                                    $stmt->execute([$name, $tel, $cccd, $target_file]);

                                } else {
                                    $_SESSION['thong_bao'] = "<p class='thong_bao sai'>Vui lòng nhập đầy đủ thông tin!</p>";
                                }

                            } else{
                                $_SESSION['thong_bao'] = "<p class='thong_bao sai'>Lỗi: Đã xảy ra lỗi khi tải ảnh lên.</p>";
                            }
                        } else{
                            $_SESSION['thong_bao'] = "<p class='thong_bao sai'>Lỗi: Vui lòng chọn đúng định dạng ảnh.</p>";
                        }
                    }
                }
            } else {
                $_SESSION['thong_bao'] = "<p class='thong_bao sai'>Lỗi: Chưa có ảnh nào được tải lên.</p>";
            }
            $cccd_ma_hoa=encrypt_cccd( $cccd, $encryption_key);

            // Đường dẫn để lưu ảnh đã mã hóa
            $image_encoded_path = 'img/image_encoded_' . time() . '.png';

            // Chèn CCCD đã mã hóa vào ảnh bằng phương pháp LSB
            if (embed_data_in_image($target_file, $cccd_ma_hoa, $image_encoded_path)) {
                // Lưu đường dẫn ảnh đã mã hóa vào session
                $_SESSION['image_encoded_path'] = $image_encoded_path;

                // Lưu thông tin vào CSDL
                $sql = "INSERT INTO Chu_Ky (CCCD_ma_hoa, Hinh_anh_ma_hoa) VALUES (?,?)";
                $stmt = $conn->prepare($sql);
                $stmt->execute([$cccd_ma_hoa, $image_encoded_path]);

                $_SESSION['thong_bao'] = "<p class='thong_bao dung'>Đã tạo dữ liệu thành công</p>";
            } else {
                $_SESSION['thong_bao'] = "<p class='thong_bao sai'>Lỗi: Không thể chèn dữ liệu vào ảnh.</p>";
            }
        }
    }
} 

    // Toàn bộ code trong file process.php
 catch (Exception $e) {
    $_SESSION['thong_bao'] = "<p class='thong_bao sai'>Lỗi: " . htmlspecialchars($e->getMessage()) . "</p>";
    header("Location: index.php");
    exit();
}

$conn = null;

header("Location: index.php"); // Chuyển hướng về index.php
exit();
?>