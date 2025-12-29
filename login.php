<?php
session_start();

$error = '';

if (isset($_POST['login'])) {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if ($username !== '' && $password !== '') {
        $_SESSION['username'] = $username;
        header("Location: index.php");
        exit();
    } else {
        $error = "Please enter username and password!";
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
    background: rgba(0,0,0,0.25);
    z-index: -2;
}

/* Page title */
.page-title {
    position: absolute;
    top: 60px;
    font-size: 34px;
    font-weight: 600;
    color: #fff;
    width: 100%;
    text-align: center;
    z-index: 1;
}

/* Glass login card */
.login-container {
    background: rgba(50, 50, 50, 0.25);
    backdrop-filter: blur(16px);
    -webkit-backdrop-filter: blur(16px);
    padding: 35px 45px;
    border-radius: 18px;
    box-shadow: 0 15px 40px rgba(0,0,0,0.25);
    display: flex;
    align-items: center;
    gap: 50px;
    border: 1px solid rgba(255,255,255,0.25);
    z-index: 1;
}

/* Form inside card */
.login-form {
    width: 320px;
    color: #fff;
}

.login-form h2 {
    font-size: 24px;
    font-weight: 700;
    color: #e0f7fa; /* subtle accent color */
    margin-bottom: 25px;
    letter-spacing: 0.5px;
    border-left: 4px solid #1f7a63; /* vertical accent line */
    padding-left: 10px;
    text-transform: uppercase;
    box-shadow: 0 2px 8px rgba(0,0,0,0.2);
    padding: 10px 0 10px 12px;
    background: rgba(255,255,255,0.05);
    border-radius: 6px;
    transition: all 0.3s ease;
}

.login-form h2:hover {
    background: rgba(255,255,255,0.1);
    box-shadow: 0 4px 12px rgba(0,0,0,0.3);
}

label {
    font-size: 14px;
    margin-bottom: 6px;
    display: block;
}

input[type="text"],
input[type="password"] {
    width: 100%;
    padding: 11px;
    margin-bottom: 16px;
    border-radius: 7px;
    border: 1px solid rgba(255,255,255,0.5);
    font-size: 14px;
    background: rgba(255,255,255,0.1);
    color: #fff;
}

input::placeholder {
    color: rgba(255,255,255,0.6);
}

input[type="submit"] {
    width: 100%;
    padding: 12px;
    background: rgba(31,122,99,0.85);
    border: none;
    border-radius: 8px;
    color: #fff;
    font-size: 16px;
    cursor: pointer;
    transition: background 0.3s ease, transform 0.2s ease;
}

input[type="submit"]:hover {
    background: rgba(31,122,99,1);
    transform: translateY(-2px);
}

.error {
    color: #ff6b6b;
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
    color: #cfdfff;
    text-decoration: none;
}

/* Illustration image */
.illustration {
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 10px;
}

.illustration img {
    max-width: 260px;
    border-radius: 5px;
    transition: transform 0.2s ease;
}

.illustration img:hover {
    transform: scale(1.05);
}

/* Tablet responsive */
@media (max-width: 900px) and (min-width: 601px) {
    .login-container {
        flex-direction: column;
        gap: 25px;
        padding: 30px 25px;
    }
    .illustration img {
        max-width: 200px;
    }
}

/* Mobile responsive */
@media (max-width: 600px) {
    .login-container {
        flex-direction: column;
        gap: 20px;
        padding: 25px 15px;
    }
    .page-title {
        font-size: 28px;
        top: 20px;
    }
    .login-form h2 {
        font-size: 22px;
        text-align: center;
    }
    .illustration img {
        max-width: 160px;
    }
    input[type="submit"] {
        font-size: 15px;
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

        <form method="POST" autocomplete="off">
            <label>Username :</label>
            <input type="text" name="username" placeholder="Enter Username" value="" autocomplete="off" required>

            <label>Password :</label>
            <input type="password" name="password" placeholder="Enter Password" value="" autocomplete="new-password" required>

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
