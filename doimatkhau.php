<!DOCTYPE html>
<html lang="vi">

<head>
    <link rel="icon" type="image/png" sizes="32x32" href="logo/logo.png">
    <title>Đổi thông tin thành viên</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f8f9fa;
        }

        .box-content {
            margin: 40px auto;
            width: 100%;
            max-width: 500px;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 8px;
            background-color: #ffffff;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        h1 {
            font-size: 1.5rem;
            margin-bottom: 20px;
        }

        .alert {
            margin-top: 20px;
        }
    </style>
</head>

<body>
    <?php
    include './connect_db.php';
    session_start();

    $error = false;
    if (isset($_GET['action']) && $_GET['action'] == 'edit') {
        if (isset($_POST['user_id']) && !empty($_POST['user_id']) && 
            isset($_POST['old_password']) && !empty($_POST['old_password']) && 
            isset($_POST['new_password']) && !empty($_POST['new_password'])) {

            $userResult = mysqli_query($con, "SELECT * FROM `user` WHERE (`id` = " . $_POST['user_id'] . " AND `password` = '" . md5($_POST['old_password']) . "')");
            if ($userResult->num_rows > 0) {
                $result = mysqli_query($con, "UPDATE `user` SET `password` = MD5('" . $_POST['new_password'] . "'), `last_updated`=" . time() . " WHERE (`id` = " . $_POST['user_id'] . " AND `password` = '" . md5($_POST['old_password']) . "')");
                if (!$result) {
                    $error = "Không thể cập nhật tài khoản";
                }
            } else {
                $error = "Mật khẩu cũ không đúng.";
            }
            mysqli_close($con);

            if ($error !== false) {
                ?>
                <div class="box-content alert alert-danger text-center">
                    <h1>Thông báo</h1>
                    <h4><?= $error ?></h4>
                    <a href="./doimatkhau.php" class="btn btn-primary">Đổi lại mật khẩu</a>
                </div>
                <?php 
            } else { ?>
                <div class="box-content alert alert-success text-center">
                    <h1>Sửa tài khoản thành công</h1>
                    <a href="./login.php" class="btn btn-primary">Quay lại tài khoản</a>
                </div>
            <?php 
            }
        } else { ?>
            <div class="box-content alert alert-warning text-center">
                <h1>Vui lòng nhập đủ thông tin để sửa tài khoản</h1>
                <a href="./doimatkhau.php" class="btn btn-primary">Quay lại sửa tài khoản</a>
            </div>
        <?php 
        }
    } else {
        $user = $_SESSION['user'];
        if (!empty($user)) { ?>
            <div id="edit_user" class="box-content">
                <h1>Xin chào "<?= $user['fullname'] ?>". Bạn đang thay đổi mật khẩu</h1>
                <form action="./doimatkhau.php?action=edit" method="POST" autocomplete="off">
                    <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                    <div class="form-group">
                        <label for="old_password">Password cũ</label>
                        <input type="password" class="form-control" name="old_password" id="old_password" required>
                    </div>
                    <div class="form-group">
                        <label for="new_password">Password mới</label>
                        <input type="password" class="form-control" name="new_password" id="new_password" required>
                    </div>
                    <button type="submit" class="btn btn-success btn-block">Đổi Mật Khẩu</button>
                </form>
            </div>
        <?php 
        }
    }
    ?>
    
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>

</html>
