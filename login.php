<?php
session_start();
include 'db_config.php'; 

if (isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}

$error = '';

if (isset($_POST['login'])) {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (!empty($username) && !empty($password)) {
        $stmt = $conn->prepare("SELECT username, password FROM login_users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
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
        $stmt->close();
    } else {
        $error = "Please fill in all fields!";
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
        * { box-sizing: border-box; }
        
        body { 
            font-family: 'Poppins', sans-serif; 
            min-height: 100vh; 
            margin: 0; 
            display: flex; 
            flex-direction: column;
            justify-content: center; 
            align-items: center; 
            overflow-x: hidden; 
            background-color: #1a1a1a; 
            padding: 20px;
        }

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
        body::before { 
            content: ""; 
            position: fixed; 
            inset: 0; 
            background: rgba(0,0,0,0.6); 
            z-index: -2; 
        }

        .page-title { 
            margin-bottom: 30px;
            font-size: 38px; 
            font-weight: 600; 
            color: #fff; 
            text-align: center; 
            z-index: 1; 
            letter-spacing: 1px;
        }

        .login-container { 
            background: rgba(255, 255, 255, 0.08); 
            backdrop-filter: blur(20px); 
            padding: 40px; 
            border-radius: 20px; 
            box-shadow: 0 25px 50px rgba(0,0,0,0.5); 
            display: flex; 
            align-items: center; 
            gap: 40px; 
            border: 1px solid rgba(255,255,255,0.15); 
            z-index: 1; 
            max-width: 750px; /* මෙතනින් පළල අඩු කර ඇත */
            width: 100%;
        }

        .login-form { flex: 1; color: #fff; }
        .login-form h2 { 
            font-size: 20px; 
            font-weight: 600; 
            margin-bottom: 25px; 
            border-left: 4px solid #1f7a63; 
            padding-left: 12px; 
            color: #ececec;
        }

        label { font-size: 13px; margin-bottom: 8px; display: block; color: #bbb; }

        input[type="text"], input[type="password"] { 
            width: 100%; 
            padding: 12px 15px; 
            margin-bottom: 20px; 
            border-radius: 10px; 
            border: 1px solid rgba(255,255,255,0.2); 
            font-size: 14px; 
            background: rgba(0,0,0,0.2); 
            color: #fff; 
            outline: none; 
            transition: 0.3s;
        }

        input[type="text"]:focus, input[type="password"]:focus {
            border-color: #1f7a63;
            background: rgba(0,0,0,0.3);
        }

        .password-wrapper { position: relative; width: 100%; }
        input[type="password"] { padding-right: 45px; }

        .password-wrapper i { 
            position: absolute; 
            right: 15px; 
            top: 13px;
            cursor: pointer; 
            color: rgba(255,255,255,0.6);
            font-size: 16px; 
        }

        input[type="submit"] { 
            width: 100%; 
            padding: 12px; 
            background: #1f7a63; 
            border: none; 
            border-radius: 10px; 
            color: #fff; 
            font-size: 15px; 
            font-weight: 600; 
            cursor: pointer; 
            transition: 0.3s; 
            margin-top: 5px;
        }
        input[type="submit"]:hover { 
            background: #26967a; 
            box-shadow: 0 8px 20px rgba(31,122,99,0.3); 
        }

        .error { 
            background: rgba(255, 82, 82, 0.15); 
            color: #ff5252; 
            padding: 10px; 
            border-radius: 8px; 
            font-size: 13px; 
            margin-bottom: 20px; 
            text-align: center; 
            border: 1px solid rgba(255,82,82,0.3); 
        }

        .forgot { margin-top: 20px; text-align: center; font-size: 12px; }
        .forgot a { color: #999; text-decoration: none; transition: 0.3s; }
        .forgot a:hover { color: #1f7a63; }

        .illustration { flex: 0.8; display: flex; justify-content: center; }
        .illustration img { 
            max-width: 100%; 
            height: auto; 
            filter: drop-shadow(0 15px 35px rgba(0,0,0,0.7)); 
        }

        @media (max-width: 800px) {
            .login-container { flex-direction: column; gap: 30px; max-width: 400px; }
            .illustration { order: -1; }
        }
    </style>
</head>
<body>

<div class="page-title">Multi 9 Computer System</div>

<div class="login-container">
    <div class="login-form">
        <h2>Access to the System</h2>

        <?php if(!empty($error)): ?>
            <div class='error'><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <label>Username</label>
            <input type="text" name="username" placeholder="Enter Username" required>

            <label>Password</label>
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
        <img src="uploads/devices/multi.png" alt="Multi 9 Illustration">
    </div>
</div>

<script>
    const togglePassword = document.querySelector('#togglePassword');
    const passwordField = document.querySelector('#passwordField');

    togglePassword.addEventListener('click', function () {
        const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordField.setAttribute('type', type);
        this.classList.toggle('fa-eye');
        this.classList.toggle('fa-eye-slash');
    });
</script>

</body>
</html>