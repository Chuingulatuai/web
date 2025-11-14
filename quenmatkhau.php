<link rel="stylesheet" href="css/app.css">`n<style>
    body, html {
        font-family: 'Source Sans Pro', sans-serif;
        background-color: #0094DA; /* Màu nền giống trang đăng nhập */
        padding: 0;
        margin: 0;
    }

    .container {
        margin: 0;
        top: 50px;
        left: 50%;
        position: absolute;
        text-align: center;
        transform: translateX(-50%);
        background-color: #ffffff; /* Màu nền trắng cho container */
        border-radius: 12px; /* Bo góc cho container */
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1); /* Đổ bóng nhẹ */
        width: 400px;
        padding: 20px; /* Thêm padding cho container */
    }

    .has-error {
        color: violet;
    }

    .btn1 {
        border: 0;
        background: #007BB5; /* Màu nút giống như trang đăng ký */
        color: #ffffff; /* Chữ màu trắng */
        border-radius: 5px; /* Bo góc cho nút */
        width: 100%; /* Chiều rộng 100% cho nút */
        height: 48px; /* Chiều cao nút */
        font-size: 18px; /* Tăng kích thước chữ cho nút */
        transition: 0.3s;
        cursor: pointer;
    }

    .btn1:hover {
        background: #0056b3; /* Màu khi hover */
    }

    .error {
        background: #ff3333;
        text-align: center;
        width: 90%;
        height: 40px; /* Đặt chiều cao cho thông báo lỗi */
        padding: 8px;
        border: 0;
        border-radius: 5px;
        margin: 10px auto; /* Tự động căn giữa */
        color: white;
        display: <?= isset($loi['email']) ? 'block' : 'none' ?>; /* Hiển thị nếu có thông báo lỗi */
        font-size: 16px; /* Tăng kích thước chữ cho thông báo lỗi */
    }
</style>

<?php
if (!isset($_SESSION)) {
    session_start();
}
include 'connect_db.php';
$loi = [];
if (isset($_POST['submit'])) {
    $email = $_POST['email'];
    if (empty($email)) {
        $loi['email'] = "Bạn chưa nhập email";
    }
    if (empty($loi)) {
        $sql = mysqli_query($con, "SELECT * FROM `user` WHERE `email` = '$email'");
        $test = mysqli_num_rows($sql);
        if ($test == 0) {
            $loi['email'] = "Email không tồn tại";
        } else {
            $result = sendnewpd($email);
        }
    }
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function sendnewpd($email)
{
    include "PHPMailer/src/PHPMailer.php";
    include "PHPMailer/src/Exception.php";
    include "PHPMailer/src/OAuth.php";
    include "PHPMailer/src/POP3.php";
    include "PHPMailer/src/SMTP.php";
    $mail = new PHPMailer(true);
    try {
        $mail->SMTPDebug = 0;
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'phuchuu1122@gmail.com';
        $mail->Password = 'lwvajfycsvyikmgz'; // Lưu ý: Không nên để mật khẩu ở đây, sử dụng biến môi trường
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;
        $mail->CharSet = "UTF-8";
        $mail->setFrom('ComputerStore@gmail.com', 'Computerstore.com');
        $mail->addAddress($email);
        $mail->isHTML(true);
        $mail->Subject = "Thư gửi lại mật khẩu";
        $mail->Body = "<form method='POST' action='http://localhost/doanWeb/kichhoat.php'>
                            <input type='hidden' name='email' value='$email'>
                            Vui lòng truy cập <button type='submit'> Tại đây</button> để đổi lại mật khẩu
                        </form>";
        $mail->send();
        echo 'Message has been sent';
    } catch (Exception $e) {
        echo 'Message could not be sent. Mailer Error: ', $mail->ErrorInfo;
    }
}
?>

<link rel="icon" type="logo/png" sizes="32x32" href="logo/logo.png">
<div class="container auth-wrap">
    <form method="post" class="box" onsubmit="return checkStuff()">
        <h4 class="mb-3">QUÊN MẬT KHẨU</h4>
        <div class="mb-3">
            <label for="email" class="form-label">Email address</label>
            <input value="<?php if (isset($email)) echo $email ?>" type="email" class="form-control" id="email" aria-describedby="emailHelp" name="email">
            <br>
            <br>
            <div class="has-error">
                <span><?php echo (isset($loi['email'])) ? $loi['email'] : '' ?></span>
            </div>
        </div>
        <button type="submit" name="submit" class="btn1">Gửi</button>
    </form>
</div>

