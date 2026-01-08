<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}
?>
$error = '';

// නිවැරදි Login දත්ත මෙහි සඳහන් කර ඇත
$correct_username = "multi9";
$correct_password = "multi912#";

if (isset($_POST['login'])) {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // Username සහ Password එක ඔබ ලබා දුන් දත්ත වලට සමාන දැයි පරීක්ෂා කිරීම
    if ($username === $correct_username && $password === $correct_password) {
        $_SESSION['username'] = $username;
        header("Location: index.php");
        exit();
    } else {
        // වැරදි දත්ත ඇතුළත් කළහොත් පෙන්වන මැසේජ් එක
        $error = "Invalid Username or Password!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Multi 9 Computer System Login</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            height: 100vh;
            margin: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            overflow: hidden;
            background-color: #1a1a1a;
        }

        /* Animated background */
        body::after {
            content: "";
            position: fixed;
            inset: 0;
            background: url('uploads/devices/multi9.avif') center / cover no-repeat;
            animation: bgMotion 12s ease-in-out infinite alternate;
            z-index: -3;
        }

        @keyframes bgMotion {
            0% { transform: scale(1) translate(0,0); }
            100% { transform: scale(1.12) translate(-3%, -3%); }
        }

        /* Dark overlay */
        body::before {
            content: "";
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.4);
            z-index: -2;
        }

        .page-title {
            position: absolute;
            top: 60px;
            font-size: 34px;
            font-weight: 600;
            color: #fff;
            width: 100%;
            text-align: center;
            z-index: 1;
            text-shadow: 2px 2px 10px rgba(0,0,0,0.5);
        }

        /* Glass login card */
        .login-container {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            padding: 35px 45px;
            border-radius: 18px;
            box-shadow: 0 15px 40px rgba(0,0,0,0.4);
            display: flex;
            align-items: center;
            gap: 50px;
            border: 1px solid rgba(255,255,255,0.2);
            z-index: 1;
        }

        .login-form {
            width: 320px;
            color: #fff;
        }

        .login-form h2 {
            font-size: 22px;
            font-weight: 600;
            color: #fff;
            margin-bottom: 25px;
            border-left: 5px solid #1f7a63;
            padding-left: 15px;
            text-transform: uppercase;
        }

        label {
            font-size: 14px;
            margin-bottom: 8px;
            display: block;
            color: #ddd;
        }

        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 12px;
            margin-bottom: 20px;
            border-radius: 8px;
            border: 1px solid rgba(255,255,255,0.3);
            font-size: 14px;
            background: rgba(255,255,255,0.1);
            color: #fff;
            box-sizing: border-box;
            outline: none;
        }

        input:focus {
            border-color: #1f7a63;
            background: rgba(255,255,255,0.15);
        }

        input::placeholder {
            color: rgba(255,255,255,0.5);
        }

        input[type="submit"] {
            width: 100%;
            padding: 12px;
            background: #1f7a63;
            border: none;
            border-radius: 8px;
            color: #fff;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        input[type="submit"]:hover {
            background: #2a9d80;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(31,122,99,0.4);
        }

        .error {
            background: rgba(255, 107, 107, 0.2);
            color: #ff6b6b;
            padding: 10px;
            border-radius: 5px;
            font-size: 13px;
            margin-bottom: 15px;
            text-align: center;
            border: 1px solid #ff6b6b;
        }

        .forgot {
            margin-top: 20px;
            text-align: center;
            font-size: 13px;
        }

        .forgot a {
            color: #aaa;
            text-decoration: none;
        }

        .forgot a:hover {
            color: #fff;
            text-decoration: underline;
        }

        .illustration img {
            max-width: 280px;
            border-radius: 10px;
            filter: drop-shadow(0 10px 20px rgba(0,0,0,0.5));
        }

        /* Responsiveness */
        @media (max-width: 800px) {
            .login-container {
                flex-direction: column;
                gap: 30px;
                width: 90%;
            }
            .illustration { order: -1; }
            .illustration img { max-width: 180px; }
            .page-title { font-size: 24px; top: 30px; }
        }
    </style>
</head>
<body>

<div class="page-title">
    Welcome to Multi 9 Computer System
</div>

<div class="login-container">
    <div class="login-form">
        <h2>Access to the System</h2>

        <?php if($error): ?>
            <div class='error'><?= $error ?></div>
        <?php endif; ?>

        <form method="POST" autocomplete="off">
            <label>Username :</label>
            <input type="text" name="username" placeholder="Enter Username" required>

            <label>Password :</label>
            <input type="password" name="password" placeholder="Enter Password" required>

            <input type="submit" name="login" value="Log in">
        </form>

        <div class="forgot">
            <a href="forgot.php">Forgotten your username or password?</a>
        </div>
    </div>

    <div class="illustration">
        <img src="uploads/devices/multi.png" alt="Multi 9">
    </div>
</div>

</body>
</html>