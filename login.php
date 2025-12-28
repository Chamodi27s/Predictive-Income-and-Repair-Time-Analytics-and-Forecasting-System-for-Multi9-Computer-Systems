<?php
session_start();

// Dummy credentials
$valid_username = "multi9";
$valid_password = "multi912#";

$error = '';

if (isset($_POST['login'])) {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if ($username === $valid_username && $password === $valid_password) {
        $_SESSION['username'] = $username;
        header("Location: index.php");
        exit();
    } else {
        $error = "Invalid username or password!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Multi 9 Computer System Login</title>

<!-- Google Font -->
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">

<style>
    body {
        font-family: 'Poppins', sans-serif;
        background: linear-gradient(to bottom, #d0d8d4, #e7d3d3);
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100vh;
        margin: 0;
    }

    .page-title {
        position: absolute;
        top: 40px;
        font-size: 34px;
        font-weight: 600;
        color: #000;
        text-align: center;
        width: 100%;
    }

    .login-container {
        background: linear-gradient(to bottom right, #d7e2dd, #ead1d1);
        padding: 35px 45px;
        border-radius: 14px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        display: flex;
        align-items: center;
        gap: 50px;
    }

    .login-form {
        width: 320px;
    }

    .login-form h2 {
        font-size: 22px;
        font-weight: 500;
        margin-bottom: 20px;
    }

    label {
        font-size: 14px;
        font-weight: 500;
        margin-bottom: 6px;
        display: block;
    }

    input[type="text"],
    input[type="password"] {
        width: 100%;
        padding: 10px 12px;
        margin-bottom: 16px;
        border-radius: 6px;
        border: 1px solid #999;
        font-size: 14px;
    }

    input[type="submit"] {
        width: 100%;
        padding: 12px;
        background: #1f7a63;
        border: none;
        border-radius: 8px;
        color: #fff;
        font-size: 16px;
        font-weight: 500;
        cursor: pointer;
    }

    input[type="submit"]:hover {
        background: #155c4a;
    }

    .error {
        color: red;
        font-size: 14px;
        margin-bottom: 10px;
        text-align: center;
    }

    .forgot {
        margin-top: 12px;
        text-align: center;
        font-size: 13px;
    }

    .forgot a {
        color: #2b2b8c;
        text-decoration: none;
    }

    .illustration img {
        max-width: 260px;
    }

    @media(max-width: 900px) {
        .login-container {
            flex-direction: column;
        }
        .illustration img {
            display: none;
        }
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

        <?php if($error) echo "<div class='error'>$error</div>"; ?>

        <form method="POST">
            <label>Username :</label>
            <input type="text" name="username" placeholder="multi9" required>

            <label>Password :</label>
            <input type="password" name="password" placeholder="multi912#" required>

            <input type="submit" name="login" value="Log in">
        </form>

        <div class="forgot">
            <a href="#">Forgotten your username or password?</a>
        </div>
    </div>

    <div class="illustration">
        <img src="uploads/devices/multi.png" alt="Multi 9">
    </div>
</div>

</body>
</html>
