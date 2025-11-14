<?php
if (!isset($_SESSION)) {
    session_start();
}
include './connect_db.php';
$error = [];
$successMessage = ""; // Variable to hold success message

if (isset($_POST['fullname'])) {
    $fullname = $_POST['fullname'];
    $email = $_POST['email'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    $rpassword = $_POST['rpassword'];
    $phone = $_POST['phone'];
    $gender = $_POST['gender'];

    // Input validation
    if (empty($fullname)) {
        $error['fullname'] = 'Bạn chưa nhập tên';
    }
    if (empty($email)) {
        $error['email'] = 'Bạn chưa nhập email';
    }
    if (empty($username)) {
        $error['username'] = 'Bạn chưa nhập tên đăng nhập';
    }
    if (empty($password)) {
        $error['password'] = 'Bạn chưa nhập mật khẩu';
    }
    if ($password != $rpassword) {
        $error['rpassword'] = 'Mật khẩu nhập lại không đúng';
    }
    if (empty($phone)) {
        $error['phone'] = 'Bạn chưa nhập số điện thoại';
    }
    if (empty($gender)) {
        $error['gender'] = 'Bạn chưa nhập giới tính';
    }

    // If no errors, proceed with registration
    if (empty($error)) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $role = 'user'; // Default role
        $sql = mysqli_query($con, "INSERT INTO `users`(`fullname`, `email`, `username`, `password`, `sdt`, `gioitinh`, `role`, `created_time`, `last_updated`) VALUES ('$fullname','$email', '$username', '$hashedPassword','$phone', '$gender', '$role', NOW(), NOW())");

        if ($sql) {
            $successMessage = "Đăng ký tài khoản thành công! Mời bạn đăng nhập.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng Ký Tài Khoản</title>
    <link href='//fonts.googleapis.com/css?family=Lato:400,700' rel='stylesheet' type='text/css'>
    <link rel="icon" type="logo/png" sizes="32x32" href="logo/logo.png">
    <style>
        @import url('https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400');

        body {
            background-color: #0094DA;
            font-family: 'Source Sans Pro', sans-serif;
            color: #333;
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 0;
        }

        .content {
            background-color: #ffffff;
            border-radius: 10px;
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.1);
            padding: 30px;
            max-width: 800px;
            width: 100%;
            margin: 20px;
        }

        h1 {
            text-align: center;
            color: #007BB5;
            margin-bottom: 20px;
        }

        h3 {
            margin: 15px 0;
        }

        .form-row {
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
        }

        .form-column {
            flex: 1;
            min-width: 250px;
            margin: 10px;
        }

        input[type="text"],
        input[type="email"],
        input[type="password"],
        input[type="phone"],
        select {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box;
            font-size: 16px;
        }

        input[type="radio"] {
            margin-right: 5px;
        }

        input[type="submit"] {
            background-color: #007BB5;
            color: white;
            border: none;
            border-radius: 5px;
            padding: 12px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s;
            width: 100%;
        }

        input[type="submit"]:hover {
            background-color: #005f8a;
        }

        .has-error {
            color: red;
            font-size: 14px;
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto; 
            background-color: rgba(0, 0, 0, 0.5);
        }

        /* Modal Content */
        .modal-content {
            position: absolute;
            top: 50%; 
            left: 50%;
            transform: translate(-50%, -50%);
            background-color: #ffffff;
            padding: 20px;
            border: 1px solid #888;
            border-radius: 10px;
            text-align: center;
            max-width: 600px;
            width: 60%;
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }

        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }

        .button {
            background-color: #007BB5;
            color: white;
            border: none;
            border-radius: 5px;
            padding: 10px 20px;
            cursor: pointer;
            font-size: 16px;
            margin-top: 10px;
        }

        .button:hover {
            background-color: #005f8a;
        }
        
        .login-link {
            text-align: center;
            margin-top: 20px;
            font-size: 14px;
        }

        .login-link a {
            color: #007BB5;
            text-decoration: none;
        }

        .login-link a:hover {
            text-decoration: underline;
        }
    </style>
    <script>
        window.onload = function() {
            // Check if there's a success message and show the modal
            <?php if ($successMessage): ?>
                document.getElementById('successModal').style.display = 'block';
            <?php endif; ?>
        }

        function closeModal() {
            document.getElementById('successModal').style.display = 'none';
        }

        function redirectToLogin() {
            window.location.href = './login.php'; // Change the URL to your login page
        }
    </script>
  <link rel="stylesheet" href="css/app.css">`n</head>

<body>

    <div class="content auth-card">
        <h1>Đăng Ký Tài Khoản</h1>

        <form action="./register.php?action=reg" method="POST">
            <div class="form-row">
                <div class="form-column">
                    <h3>Họ và tên</h3>
                    <input type="text" name="fullname" value="" required />
                    <div class="has-error">
                        <span><?php echo (isset($error['fullname'])) ? $error['fullname'] : '' ?></span>
                    </div>

                    <h3>Email</h3>
                    <input type="email" name="email" value="" required />
                    <div class="has-error">
                        <span><?php echo (isset($error['email'])) ? $error['email'] : '' ?></span>
                    </div>

                    <h3>Tên đăng nhập</h3>
                    <input type="text" name="username" value="" required />
                    <div class="has-error">
                        <span><?php echo (isset($error['username'])) ? $error['username'] : '' ?></span>
                    </div>

                    <h3>Mật khẩu</h3>
                    <input type="password" name="password" value="" required />
                    <div class="has-error">
                        <span><?php echo (isset($error['password'])) ? $error['password'] : '' ?></span>
                    </div>
                </div>

                <div class="form-column">
                    <h3>Nhập lại mật khẩu</h3>
                    <input type="password" name="rpassword" value="" required />
                    <div class="has-error">
                        <span><?php echo (isset($error['rpassword'])) ? $error['rpassword'] : '' ?></span>
                    </div>

                    <h3>Số điện thoại</h3>
                    <input type="phone" name="phone" value="" required />
                    <div class="has-error">
                        <span><?php echo (isset($error['phone'])) ? $error['phone'] : '' ?></span>
                    </div>

                    <h3>Giới tính</h3>
                    <br>
                    <div>
    <label>
        <input type="radio" name="gender" value="Nam" required /> Nam
    </label>
    <label>
        <input type="radio" name="gender" value="Nữ" required /> Nữ
    </label>
    <label>
        <input type="radio" name="gender" value="Khác" required /> Khác
    </label>
    <span><?php echo (isset($error['gender'])) ? $error['gender'] : '' ?></span>
</div>

                </div>
            </div>
            <br>
            <input type="submit" value="Đăng ký">
        </form>

        <!-- Added login link -->
        <div class="login-link">
            <p>Bạn đã có tài khoản? <a href="./login.php">Đăng nhập</a></p>
        </div>
    </div>

    <!-- Modal -->
    <div id="successModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h2><?php echo $successMessage; ?></h2>
            <button class="button" onclick="redirectToLogin()">Chuyển hướng đến trang đăng nhập</button>
        </div>
    </div>

</body>

</html>

