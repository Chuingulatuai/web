<?php
session_start();
include './connect_db.php';

$timeout_duration = 1200;

if (isset($_SESSION['last_activity'])) {
    $time_elapsed = time() - $_SESSION['last_activity'];
    
    if ($time_elapsed > $timeout_duration) {
        session_unset();
        session_destroy();
        header("Location: login.php?timeout=true");
        exit();
    }
}

$_SESSION['last_activity'] = time();

if (isset($_POST['username'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM `users` WHERE `username` = '$username'";
    $query = mysqli_query($con, $sql);
    $data = mysqli_fetch_assoc($query);

    if ($data && password_verify($password, $data['password'])) {
        $_SESSION['user'] = $data;
        $_SESSION['last_activity'] = time();

        if ($data['role'] === 'admin' || $data['role'] === 'staff') {
            header("Location: admin/index.php");
        } else {
            header("Location: index.php");
        }

        exit();
    } else {
        $error_message = "Thông tin đăng nhập không chính xác";
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng Nhập</title>
    <link rel="icon" type="logo/png" sizes="32x32" href="logo/logo.png">
    <style>
        body, html {
            font-family: 'Source Sans Pro', sans-serif;
            background-color: #0094DA;
            padding: 0;
            margin: 0;
        }

        #particles-js {
            position: absolute;
            width: 100%;
            height: 100%;
        }

        .container {
            margin: 0;
            top: 50px;
            left: 50%;
            position: absolute;
            text-align: center;
            transform: translateX(-50%);
            background-color: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            width: 400px;
            padding: 20px;
        }

        .box h4 {
            color: #4a4a4a;
            font-size: 28px;
            margin-top: 20px;
        }

        .box h4 span {
            color: #007bff;
            font-weight: lighter;
        }

        .box h5 {
            font-size: 16px;
            color: #666;
            letter-spacing: 1px;
            margin-top: 10px;
            margin-bottom: 30px;
        }

        .box input[type="text"],
        .box input[type="password"] {
            display: block;
            margin: 10px auto;
            background: #f7f9fc;
            border: 1px solid #dcdcdc;
            border-radius: 5px;
            padding: 12px 10px;
            width: 90%;
            outline: none;
            color: #333;
            font-size: 16px;
            transition: all .2s ease-out;
        }

        .box input[type="text"]:focus,
        .box input[type="password"]:focus {
            border: 1px solid #007bff;
            background: #ffffff;
        }

        a {
            color: #007bff;
            text-decoration: none;
            font-size: 16px;
        }

        a:hover {
            text-decoration: underline;
        }

        .btn1 {
            border: 0;
            background: #007BB5;
            color: #ffffff;
            border-radius: 5px;
            width: 100%;
            height: 48px;
            font-size: 18px;
            transition: 0.3s;
            cursor: pointer;
        }

        .btn1:hover {
            background: #0056b3;
        }

        .forgetpass {
            display: block;
            margin-top: 10px;
            color: #007bff;
            font-size: 14px;
        }

        .dnthave {
            display: block;
            margin-top: 20px;
            color: #666;
            font-size: 14px;
        }

        .error {
            background: #ff3333;
            text-align: center;
            width: 90%;
            height: 40px;
            padding: 8px;
            border: 0;
            border-radius: 5px;
            margin: 10px auto;
            color: white;
            display: <?= isset($error_message) ? 'block' : 'none' ?>;
            font-size: 16px;
        }
    </style>
</head>
<body id="particles-js">
<div class="animated bounceInDown">
    <div class="container">
        <?php if (isset($_GET['timeout'])): ?>
            <span class="error animated tada" id="msg">Phiên làm việc đã hết hạn, vui lòng đăng nhập lại.</span>
        <?php else: ?>
            <span class="error animated tada" id="msg"><?= isset($error_message) ? $error_message : '' ?></span>
        <?php endif; ?>

        <form action="./login.php" name="form1" class="box" onsubmit="return checkStuff()" method="POST" autocomplete="off">
            <h4>Chào mừng<span> bạn quay lại</span></h4>
            <h5>Đăng nhập vào tài khoản của bạn.</h5>
            <input type="text" name="username" placeholder="Tên đăng nhập" autocomplete="off" required>
            <input type="password" name="password" placeholder="Mật khẩu" id="pwd" autocomplete="off" required>
            <a href="quenmatkhau.php" class="forgetpass">Bạn quên mật khẩu ?</a>
            <input type="submit" value="Đăng nhập" class="btn1">
        </form>
        <a href="register.php" class="dnthave">Nhấn vào đây nếu bạn chưa có tài khoản</a>
    </div>
</div>

<script>
    var pwd = document.getElementById('pwd');
    var eye = document.getElementById('eye');

    eye.addEventListener('click', togglePass);

    function togglePass() {
        eye.classList.toggle('active');
        pwd.type = (pwd.type === 'password') ? 'text' : 'password';
    }

    function checkStuff() {
        var username = document.form1.username;
        var password = document.form1.password;
        var msg = document.getElementById('msg');

        if (username.value === "") {
            msg.style.display = 'block';
            msg.innerHTML = "Vui lòng nhập tên đăng nhập";
            username.focus();
            return false;
        } else {
            msg.innerHTML = "";
        }

        if (password.value === "") {
            msg.innerHTML = "Vui lòng nhập mật khẩu";
            password.focus();
            return false;
        } else {
            msg.innerHTML = "";
        }
    }

    particlesJS("particles-js", {
        "particles": {
            "number": {
                "value": 60,
                "density": {
                    "enable": true,
                    "value_area": 800
                }
            },
            "color": {
                "value": "#007bff"
            },
            "shape": {
                "type": "circle",
                "stroke": {
                    "width": 0,
                    "color": "#000000"
                },
                "polygon": {
                    "nb_sides": 5
                },
                "image": {
                    "src": "img/github.svg",
                    "width": 100,
                    "height": 100
                }
            },
            "opacity": {
                "value": 0.5,
                "random": false,
                "anim": {
                    "enable": false,
                    "speed": 1,
                    "opacity_min": 0.1,
                    "sync": false
                }
            },
            "size": {
                "value": 10,
                "random": true,
                "anim": {
                    "enable": false,
                    "speed": 40,
                    "size_min": 0.1,
                    "sync": false
                }
            },
            "line_linked": {
                "enable": true,
                "distance": 150,
                "color": "#ffffff",
                "opacity": 0.4,
                "width": 1
            },
            "move": {
                "enable": true,
                "speed": 6,
                "direction": "none",
                "random": false,
                "straight": false,
                "out_mode": "out",
                "bounce": false,
                "attract": {
                    "enable": false,
                    "rotateX": 600,
                    "rotateY": 1200
                }
            }
        },
        "interactivity": {
            "detect_on": "canvas",
            "events": {
                "onhover": {
                    "enable": true,
                    "mode": "repulse"
                },
                "onclick": {
                    "enable": true,
                    "mode": "push"
                },
                "resize": true
            },
            "modes": {
                "grab": {
                    "distance": 400,
                    "line_linked": {
                        "opacity": 1
                    }
                },
                "bubble": {
                    "distance": 400,
                    "size": 40,
                    "duration": 2,
                    "opacity": 8,
                    "speed": 3
                },
                "repulse": {
                    "distance": 200,
                    "duration": 0.4
                },
                "push": {
                    "particles_nb": 4
                },
                "remove": {
                    "particles_nb": 2
                }
            }
        },
        "retina_detect": true
    });
</script>
</body>
</html>
