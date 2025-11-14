<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" /> 
<meta name="keywords" content="User Register Form Widget Responsive, Login form web template, Sign up Web Templates, Flat Web Templates, Login signup Responsive web template, Smartphone Compatible web template, free webdesigns for Nokia, Samsung, LG, SonyEricsson, Motorola web design" />
<!-- //for-mobile-apps -->
<link href='//fonts.googleapis.com/css?family=Lato:400,100,100italic,300italic,300,400italic,700,700italic,900,900italic' rel='stylesheet' type='text/css'>
<link href='//fonts.googleapis.com/css?family=Amaranth:400,400italic,700,700italic' rel='stylesheet' type='text/css'>
<link rel="icon" type="logo/png" sizes="32x32" href="logo/logo.png">
<link href="css/style.css" rel="stylesheet" type="text/css" media="all" />
<style>
        .has-error {
            color: violet;
        }
    </style>
<?php
if(!isset($_SESSION))
{
    session_start();
}
$error = [];
$email = isset($_POST['email']) ? $_POST['email'] :'';
include 'connect_db.php';
if(isset($_POST['submit']))
{
$password = isset($_POST['password']) ? $_POST['password'] :'';
$rpassword = isset($_POST['rpassword']) ? $_POST['rpassword'] :'';
if(empty($password)) 
{
    $error['password'] = "Bạn chưa nhập password";
}
if(empty($rpassword))
{
    $error['rpassword'] = "Bạn chưa nhập lại password";
}
if($password != $rpassword)
{
    $error['rpassword'] = "Password nhập lại không đúng";
}
if(empty($error))
{
        $sql = mysqli_query($con,"SELECT * FROM user ");
        $test = mysqli_num_rows($sql);
        // print_r($test);
        if ($test > 0) {
            $email = isset($_POST['email']) ? $_POST['email'] :'';
            mysqli_query($con, "UPDATE `user` SET `password` = MD5('$password') WHERE `email`= '$email' ");
            ?>
            <div id="edit-notify" class="box-content">
            <h1><?=   "Sửa mật khẩu thành công" ?></h1>
            <a href="./login.php">Mời bạn đăng nhập</a>
        </div>
        <?php
        }
  
    
}
}
?>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
<form method="POST" style="width:600px" class="border border-primary border-2 m-auto p-2">
    <h4 class="mb-3 text-center" >
        Mật khẩu mới
    </h4>
  <div class="mb-3">
    <label  class="form-label">Nhập mật khẩu mới</label>
    <input value="" type="text" class="form-control" id="password" name="password">
    <div class="has-error">
        <span>
            <?php echo (isset($error['password'])) ? $error['password']:''?>
        </span>
    </div>
    <label  class="form-label">Nhập lại mật khẩu mới</label>
    <input value="" type="text" class="form-control" id="rpassword" name="rpassword">
    <div class="has-error">
        <span>
            <?php echo (isset($error['rpassword'])) ? $error['rpassword']:''?>
        </span>
    </div>
  </div>
  <input type="hidden" name="email" value="<?php echo $email ?>">
  <button type="submit" name="submit" value="" class="btn btn-primary">Submit</button>
</form>