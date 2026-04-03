<?php
session_start();
include 'db_config.php'; 

if (isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}

$error = '';

if (isset($_POST['login'])) {
    $username = mysqli_real_escape_string($conn, trim($_POST['username']));
    $password = mysqli_real_escape_string($conn, trim($_POST['password']));

    $query = "SELECT * FROM login_users WHERE username='$username'";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        
        if ($password === $row['password']) {
            $_SESSION['username'] = $row['username'];
            header("Location: index.php");
            exit();
        } else {
            $error = "Invalid Username or Password!";
        }
    } else {
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; height: 100vh; margin: 0; display: flex; justify-content: center; align-items: center; overflow: hidden; background-color: #1a1a1a; }
        body::after { content: ""; position: fixed; inset: 0; background: url('uploads/devices/multi9.avif') center / cover no-repeat; animation: bgMotion 12s ease-in-out infinite alternate; z-index: -3; }
        @keyframes bgMotion { 0% { transform: scale(1) translate(0,0); } 100% { transform: scale(1.12) translate(-3%, -3%); } }
        body::before { content: ""; position: fixed; inset: 0; background: rgba(0,0,0,0.4); z-index: -2; }
        .page-title { position: absolute; top: 60px; font-size: 34px; font-weight: 600; color: #fff; width: 100%; text-align: center; z-index: 1; text-shadow: 2px 2px 10px rgba(0,0,0,0.5); }
        .login-container { background: rgba(255, 255, 255, 0.1); backdrop-filter: blur(16px); padding: 35px 45px; border-radius: 18px; box-shadow: 0 15px 40px rgba(0,0,0,0.4); display: flex; align-items: center; gap: 50px; border: 1px solid rgba(255,255,255,0.2); z-index: 1; }
        .login-form { width: 320px; color: #fff; }
        .login-form h2 { font-size: 22px; font-weight: 600; color: #fff; margin-bottom: 25px; border-left: 5px solid #1f7a63; padding-left: 15px; text-transform: uppercase; }
        label { font-size: 14px; margin-bottom: 8px; display: block; color: #ddd; }
        
        /* අයිකනය පෙනෙන්නට සලස්වන ප්‍රධාන CSS කොටස */
        .password-wrapper { 
            position: relative; 
            width: 100%; 
            margin-bottom: 20px; 
            display: flex;
            align-items: center;
        }
        
        .password-wrapper input { 
            width: 100%; 
            margin-bottom: 0 !important; /* Wrapper එක ඇතුළේ margin එක ඉවත් කළා */
            padding-right: 45px !important; 
        }
        
        .password-wrapper i { 
            position: absolute; 
            right: 15px; 
            cursor: pointer; 
            color: rgba(255,255,255,0.8); /* පැහැදිලිව පෙනෙන ලෙස වර්ණය වැඩි කළා */
            font-size: 18px; 
            z-index: 100; /* අයිකනය උඩින්ම පෙන්වීමට */
            transition: color 0.3s;
        }
        
        .password-wrapper i:hover { color: #f3f8f6; }

        input[type="text"], input[type="password"] { width: 100%; padding: 12px; margin-bottom: 20px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.3); font-size: 14px; background: rgba(255,255,255,0.1); color: #fff; box-sizing: border-box; outline: none; }
        input[type="submit"] { width: 100%; padding: 12px; background: #1f7a63; border: none; border-radius: 8px; color: #fff; font-size: 16px; font-weight: 600; cursor: pointer; transition: all 0.3s ease; }
        input[type="submit"]:hover { background: #2a9d80; transform: translateY(-2px); box-shadow: 0 5px 15px rgba(31,122,99,0.4); }
        .error { background: rgba(255, 107, 107, 0.2); color: #ff6b6b; padding: 10px; border-radius: 5px; font-size: 13px; margin-bottom: 15px; text-align: center; border: 1px solid #ff6b6b; }
        .forgot { margin-top: 20px; text-align: center; font-size: 13px; }
        .forgot a { color: #dadcdb; text-decoration: none; }
        .forgot a:hover { color: #9bddc6; }
        .illustration img { max-width: 280px; border-radius: 10px; filter: drop-shadow(0 10px 20px rgba(0,0,0,0.5)); }
    </style>
</head>
<body>

<div class="page-title">Welcome to Multi 9 Computer System</div>

<div class="login-container">
    <div class="login-form">
        <h2>Access to the System</h2>

        <?php if(!empty($error)): ?>
            <div class='error'><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST" autocomplete="off">
            <label>Username :</label>
            <input type="text" name="username" placeholder="Enter Username" required>

            <label>Password :</label>
            <div class="password-wrapper">
                <input type="password" name="password" id="passwordField" placeholder="Enter Password" required>
                <i class="fa-solid fa-eye" id="togglePassword"></i>
            </div>

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

<script>
    const togglePassword = document.querySelector('#togglePassword');
    const password = document.querySelector('#passwordField');

    togglePassword.addEventListener('click', function () {
        // Password එක පෙන්වීම සහ සැඟවීම
        const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
        password.setAttribute('type', type);
        
        // Icon එක මාරු කිරීම
        this.classList.toggle('fa-eye');
        this.classList.toggle('fa-eye-slash');
    });
</script>

</body>
</html>