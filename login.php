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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body.login-page { 
            font-family: 'Inter', sans-serif;
            padding-top: 0;
            display: flex; 
            flex-direction: column;
            justify-content: center; 
            align-items: center; 
            background: #090d16; 
            padding: 30px 16px;
            min-height: 100vh;
            color: #f8fafc;
            position: relative;
            overflow-x: hidden;
        }

        /* ===== HIGH VISIBILITY DYNAMIC MOTION BACKGROUND ===== */
        #particleCanvas {
            position: fixed;
            inset: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: -1;
        }

        .bg-orbs {
            position: fixed;
            inset: 0;
            pointer-events: none;
            z-index: -2;
            overflow: hidden;
            background: radial-gradient(circle at 50% 50%, #0f172a 0%, #060911 100%);
        }

        .orb {
            position: absolute;
            border-radius: 50%;
            filter: blur(60px);
            opacity: 0.6;
        }

        .orb-1 {
            width: 550px;
            height: 550px;
            background: radial-gradient(circle, rgba(16, 185, 129, 0.45) 0%, rgba(16, 185, 129, 0) 70%);
            top: -120px;
            left: -120px;
            animation: float1 10s infinite ease-in-out alternate;
        }

        .orb-2 {
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, rgba(59, 130, 246, 0.4) 0%, rgba(59, 130, 246, 0) 70%);
            bottom: -100px;
            right: -100px;
            animation: float2 12s infinite ease-in-out alternate;
        }

        .orb-3 {
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, rgba(16, 185, 129, 0.35) 0%, rgba(16, 185, 129, 0) 70%);
            top: 35%;
            left: 40%;
            animation: float3 14s infinite ease-in-out alternate;
        }

        @keyframes float1 {
            0% { transform: translate(0, 0) scale(1); }
            100% { transform: translate(120px, 80px) scale(1.15); }
        }

        @keyframes float2 {
            0% { transform: translate(0, 0) scale(1); }
            100% { transform: translate(-100px, -90px) scale(1.1); }
        }

        @keyframes float3 {
            0% { transform: translate(0, 0) scale(0.9); }
            100% { transform: translate(-80px, 70px) scale(1.2); }
        }

        /* ===== BRAND HEADER ===== */
        .page-title { 
            margin-bottom: 26px;
            font-size: 32px; 
            font-weight: 900; 
            color: #ffffff; 
            text-align: center; 
            z-index: 1; 
            letter-spacing: -0.5px;
            text-shadow: 0 4px 20px rgba(0, 0, 0, 0.7);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
        }

        .page-title i {
            color: #10b981;
            font-size: 38px;
            animation: iconGlow 3s infinite ease-in-out;
        }

        @keyframes iconGlow {
            0%, 100% { transform: scale(1); filter: drop-shadow(0 0 8px rgba(16, 185, 129, 0.6)); }
            50% { transform: scale(1.08); filter: drop-shadow(0 0 18px rgba(16, 185, 129, 0.9)); }
        }

        /* ===== GLASS CONTAINER ===== */
        .login-container { 
            background: rgba(15, 23, 42, 0.78); 
            backdrop-filter: blur(24px); 
            -webkit-backdrop-filter: blur(24px);
            border-radius: 26px; 
            box-shadow: 0 30px 80px rgba(0, 0, 0, 0.7), 0 0 40px rgba(16, 185, 129, 0.15); 
            display: flex; 
            align-items: stretch; 
            border: 1px solid rgba(255, 255, 255, 0.12); 
            z-index: 1; 
            max-width: 950px; 
            width: 100%;
            overflow: hidden;
            transition: all 0.4s ease;
        }

        .login-container:hover {
            border-color: rgba(16, 185, 129, 0.4);
            box-shadow: 0 35px 90px rgba(0, 0, 0, 0.8), 0 0 55px rgba(16, 185, 129, 0.22);
        }

        /* ===== LEFT FORM ===== */
        .login-form { 
            flex: 1.1; 
            padding: 46px 42px; 
            display: flex;
            flex-direction: column;
            justify-content: center;
            color: #fff; 
        }

        .login-form h2 { 
            font-size: 23px; 
            font-weight: 800; 
            margin-bottom: 26px; 
            border-left: 4px solid #10b981; 
            padding-left: 14px; 
            color: #ffffff;
            letter-spacing: -0.3px;
        }

        label { 
            font-size: 11.5px; 
            font-weight: 800;
            margin-bottom: 8px; 
            display: block; 
            color: #94a3b8; 
            letter-spacing: 0.8px;
            text-transform: uppercase;
        }

        input[type="text"], input[type="password"] { 
            width: 100%; 
            padding: 14px 16px; 
            margin-bottom: 22px; 
            border-radius: 13px; 
            border: 1.5px solid rgba(255, 255, 255, 0.14); 
            font-size: 14.5px; 
            background: rgba(15, 23, 42, 0.65); 
            color: #ffffff; 
            outline: none; 
            transition: all 0.3s ease;
        }

        input[type="text"]:focus, input[type="password"]:focus {
            border-color: #10b981;
            background: rgba(15, 23, 42, 0.9);
            box-shadow: 0 0 20px rgba(16, 185, 129, 0.35);
        }

        .password-wrapper { 
            position: relative; 
            width: 100%; 
        }

        input[type="password"] { 
            padding-right: 48px; 
        }

        .password-wrapper i { 
            position: absolute; 
            right: 16px; 
            top: 15px;
            cursor: pointer; 
            color: #94a3b8;
            font-size: 20px; 
            transition: all 0.2s ease;
        }

        .password-wrapper i:hover { 
            color: #10b981; 
        }

        input[type="submit"] { 
            width: 100%; 
            padding: 15px; 
            background: linear-gradient(135deg, #10b981 0%, #059669 100%); 
            border: none; 
            border-radius: 13px; 
            color: #ffffff; 
            font-size: 15.5px; 
            font-weight: 800; 
            letter-spacing: 0.5px;
            cursor: pointer; 
            transition: all 0.3s ease; 
            margin-top: 6px;
            box-shadow: 0 8px 24px rgba(16, 185, 129, 0.35);
        }

        input[type="submit"]:hover { 
            background: linear-gradient(135deg, #34d399 0%, #059669 100%); 
            transform: translateY(-2px);
            box-shadow: 0 12px 30px rgba(16, 185, 129, 0.5); 
        }

        .error { 
            background: rgba(239, 68, 68, 0.18); 
            color: #fca5a5; 
            padding: 12px 16px; 
            border-radius: 12px; 
            font-size: 13.5px; 
            font-weight: 700;
            margin-bottom: 22px; 
            text-align: center; 
            border: 1px solid rgba(239, 68, 68, 0.35); 
        }

        .forgot { 
            margin-top: 22px; 
            text-align: center; 
            font-size: 13px; 
        }

        .forgot a { 
            color: #94a3b8; 
            text-decoration: none; 
            transition: color 0.3s ease; 
            font-weight: 600;
        }

        .forgot a:hover { 
            color: #10b981; 
            text-decoration: underline;
        }

        /* ===== RIGHT WORKSHOP PHOTO - 100% CRYSTAL CLEAR ===== */
        .illustration { 
            flex: 1; 
            position: relative; 
            overflow: hidden; 
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 440px;
            background: #020617;
        }

        .illustration img { 
            width: 100%; 
            height: 100%; 
            object-fit: cover; 
            object-position: center;
            filter: brightness(1.05) contrast(1.05); 
            transition: transform 0.6s ease;
        }

        .login-container:hover .illustration img {
            transform: scale(1.04);
        }

        /* ==================== RESPONSIVE MEDIA QUERIES ==================== */

        @media (max-width: 960px) {
            .login-container {
                max-width: 740px;
            }

            .login-form {
                padding: 38px 32px;
            }
        }

        @media (max-width: 768px) {
            body.login-page {
                padding: 20px 12px;
            }

            .page-title {
                font-size: 25px;
                margin-bottom: 18px;
            }

            .page-title i {
                font-size: 30px;
            }

            .login-container {
                flex-direction: column;
                max-width: 440px;
                border-radius: 22px;
            }

            .illustration {
                order: -1;
                height: 210px;
                min-height: 0;
                width: 100%;
            }

            .illustration img {
                border-radius: 22px 22px 0 0;
                object-position: center 30%;
            }

            .login-form {
                padding: 30px 22px;
                width: 100%;
            }

            .login-form h2 {
                font-size: 19px;
                margin-bottom: 20px;
            }
        }

        @media (max-width: 480px) {
            .page-title {
                font-size: 21px;
                margin-bottom: 14px;
            }

            .login-container {
                width: 95%;
                border-radius: 18px;
            }

            .illustration {
                height: 175px;
            }

            .illustration img {
                border-radius: 18px 18px 0 0;
            }

            .login-form {
                padding: 24px 18px;
            }

            .login-form h2 {
                font-size: 17px;
                margin-bottom: 16px;
            }

            input[type="text"], input[type="password"] {
                padding: 12px 14px;
                margin-bottom: 16px;
            }

            input[type="submit"] {
                padding: 13.5px;
                font-size: 14.5px;
            }
        }
    </style>
</head>
<body class="login-page">

<div class="bg-orbs">
    <div class="orb orb-1"></div>
    <div class="orb orb-2"></div>
    <div class="orb orb-3"></div>
</div>

<canvas id="particleCanvas"></canvas>

<div class="page-title">
    <i class="ph-bold ph-cpu"></i> MULTI 9 COMPUTER SYSTEM
</div>

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
                <i class="ph ph-eye" id="togglePassword"></i>
            </div>

            <input type="submit" name="login" value="Log in">
        </form>

        <div class="forgot">
            <a href="forgot.php">Forgotten your username or password?</a>
        </div>
    </div>

    <div class="illustration">
        <img src="uploads/devices/multi.jpeg" alt="Multi 9 Workshop">
    </div>
</div>

<script>
    /* ===== TOGGLE PASSWORD ===== */
    const togglePassword = document.querySelector('#togglePassword');
    const passwordField = document.querySelector('#passwordField');

    togglePassword.addEventListener('click', function () {
        const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordField.setAttribute('type', type);
        this.classList.toggle('ph-eye');
        this.classList.toggle('ph-eye-slash');
    });

    /* ===== BACKGROUND CANVAS PARTICLE NETWORK ANIMATION ===== */
    const canvas = document.getElementById('particleCanvas');
    const ctx = canvas.getContext('2d');
    let particles = [];

    function resize() {
        canvas.width = window.innerWidth;
        canvas.height = window.innerHeight;
    }
    window.addEventListener('resize', resize);
    resize();

    class Particle {
        constructor() {
            this.x = Math.random() * canvas.width;
            this.y = Math.random() * canvas.height;
            this.size = Math.random() * 2.5 + 1;
            this.speedX = (Math.random() - 0.5) * 0.9;
            this.speedY = (Math.random() - 0.5) * 0.9;
            this.opacity = Math.random() * 0.6 + 0.3;
        }
        update() {
            this.x += this.speedX;
            this.y += this.speedY;
            if (this.x < 0 || this.x > canvas.width) this.speedX *= -1;
            if (this.y < 0 || this.y > canvas.height) this.speedY *= -1;
        }
        draw() {
            ctx.fillStyle = `rgba(16, 185, 129, ${this.opacity})`;
            ctx.beginPath();
            ctx.arc(this.x, this.y, this.size, 0, Math.PI * 2);
            ctx.fill();
        }
    }

    for (let i = 0; i < 45; i++) {
        particles.push(new Particle());
    }

    function animate() {
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        
        for (let a = 0; a < particles.length; a++) {
            for (let b = a + 1; b < particles.length; b++) {
                let dx = particles[a].x - particles[b].x;
                let dy = particles[a].y - particles[b].y;
                let dist = Math.sqrt(dx * dx + dy * dy);
                if (dist < 130) {
                    ctx.strokeStyle = `rgba(16, 185, 129, ${0.2 * (1 - dist / 130)})`;
                    ctx.lineWidth = 0.9;
                    ctx.beginPath();
                    ctx.moveTo(particles[a].x, particles[a].y);
                    ctx.lineTo(particles[b].x, particles[b].y);
                    ctx.stroke();
                }
            }
        }
        
        particles.forEach(p => {
            p.update();
            p.draw();
        });
        requestAnimationFrame(animate);
    }
    animate();
</script>

</body>
</html>