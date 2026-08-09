<?php
header("Cache-Control: no-cache, no-store, must-revalidate"); 
header("Pragma: no-cache"); 
header("Expires: 0"); 

session_start();
include 'db_config.php';

if (!isset($_SESSION['reset_email'])) {
    header("Location: forgot.php");
    exit();
}

$message = '';

if (isset($_POST['update_password'])) {
    $new_pass = mysqli_real_escape_string($conn, $_POST['password']);
    $confirm_pass = mysqli_real_escape_string($conn, $_POST['confirm_password']);
    $email = $_SESSION['reset_email'];

    if ($new_pass === $confirm_pass) {
        $update_query = "UPDATE login_users SET password='$new_pass', reset_token=NULL, token_expiry=NULL WHERE email='$email'";
        
        if (mysqli_query($conn, $update_query)) {
            unset($_SESSION['reset_email']); 
            session_destroy();
            
            echo "<script>
                alert('Password updated successfully!');
                window.location.replace('login.php'); 
            </script>";
            exit();
        } else {
            $message = "❌ Database error. Please try again.";
        }
    } else {
        $message = "❌ Passwords do not match!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <title>Create New Password | Multi9 Systems</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body { 
            font-family: 'Inter', sans-serif; 
            background: #090d16; 
            color: #fff; 
            display: flex; 
            flex-direction: column;
            justify-content: center; 
            align-items: center; 
            min-height: 100vh; 
            padding: 24px 16px; 
            position: relative;
            overflow-x: hidden;
        }

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

        @keyframes float1 {
            0% { transform: translate(0, 0) scale(1); }
            100% { transform: translate(100px, 70px) scale(1.15); }
        }

        @keyframes float2 {
            0% { transform: translate(0, 0) scale(1); }
            100% { transform: translate(-90px, -80px) scale(1.1); }
        }

        .page-title { 
            margin-bottom: 26px;
            font-size: 30px; 
            font-weight: 900; 
            color: #ffffff; 
            text-align: center; 
            z-index: 1; 
            letter-spacing: -0.5px;
            text-shadow: 0 4px 20px rgba(0,0,0,0.7);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
        }

        .page-title i {
            color: #10b981;
            font-size: 36px;
        }

        .container { 
            background: rgba(15, 23, 42, 0.78); 
            backdrop-filter: blur(24px); 
            -webkit-backdrop-filter: blur(24px);
            padding: 44px 38px; 
            border-radius: 26px; 
            width: 100%; 
            max-width: 460px; 
            text-align: center; 
            box-shadow: 0 30px 80px rgba(0, 0, 0, 0.7), 0 0 40px rgba(16, 185, 129, 0.15); 
            border: 1.5px solid rgba(255, 255, 255, 0.12); 
            z-index: 1;
            transition: all 0.4s ease;
        }

        .container:hover {
            border-color: rgba(16, 185, 129, 0.4);
            box-shadow: 0 35px 90px rgba(0, 0, 0, 0.8), 0 0 55px rgba(16, 185, 129, 0.22);
        }

        .icon-header {
            width: 64px;
            height: 64px;
            background: rgba(16, 185, 129, 0.15);
            border: 1px solid rgba(16, 185, 129, 0.3);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            color: #10b981;
            font-size: 32px;
            box-shadow: 0 0 20px rgba(16, 185, 129, 0.25);
        }

        h2 { 
            color: #ffffff; 
            margin-bottom: 8px; 
            font-size: 22px; 
            font-weight: 800;
            letter-spacing: -0.3px;
        }

        p.desc {
            font-size: 13.5px;
            color: #94a3b8;
            margin-bottom: 28px;
            line-height: 1.5;
        }

        form {
            text-align: left;
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

        .input-wrapper {
            position: relative;
            width: 100%;
            margin-bottom: 20px;
        }

        input[type="password"], input[type="text"] { 
            width: 100%; 
            padding: 15px 48px 15px 18px; 
            border-radius: 13px; 
            border: 1.5px solid rgba(255, 255, 255, 0.14); 
            font-size: 14.5px; 
            background: rgba(15, 23, 42, 0.65); 
            color: #ffffff; 
            outline: none; 
            transition: all 0.3s ease;
        }

        input[type="password"]:focus, input[type="text"]:focus {
            border-color: #10b981;
            background: rgba(15, 23, 42, 0.9);
            box-shadow: 0 0 20px rgba(16, 185, 129, 0.35);
        }

        .input-wrapper i {
            position: absolute;
            right: 16px;
            top: 16px;
            color: #94a3b8;
            font-size: 20px;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .input-wrapper i:hover {
            color: #10b981;
            transform: scale(1.1);
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
            margin-top: 8px;
            box-shadow: 0 8px 24px rgba(16, 185, 129, 0.35);
        }

        input[type="submit"]:hover { 
            background: linear-gradient(135deg, #34d399 0%, #059669 100%); 
            transform: translateY(-2px);
            box-shadow: 0 12px 30px rgba(16, 185, 129, 0.5); 
        }

        .msg { 
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

        @media (max-width: 480px) {
            .page-title {
                font-size: 24px;
                margin-bottom: 20px;
            }

            .container { 
                padding: 30px 22px; 
                border-radius: 20px; 
                max-width: 96%;
            }

            h2 { font-size: 20px; }
            p.desc { font-size: 12.5px; margin-bottom: 22px; }
            input[type="password"], input[type="text"] { padding: 13px 44px 13px 15px; font-size: 14px; }
            .input-wrapper i { top: 14px; font-size: 18px; }
            input[type="submit"] { padding: 13.5px; font-size: 14.5px; }
        }
    </style>
</head>
<body>

<div class="bg-orbs">
    <div class="orb orb-1"></div>
    <div class="orb orb-2"></div>
</div>

<canvas id="particleCanvas"></canvas>

<div class="page-title">
    <i class="ph-bold ph-cpu"></i> MULTI 9
</div>

<div class="container">
    <div class="icon-header">
        <i class="ph-bold ph-keyhole"></i>
    </div>

    <h2>Create New Password</h2>
    <p class="desc">Enter your new secure password and confirm it below.</p>

    <?php if ($message) echo "<div class='msg'>$message</div>"; ?>

    <form method="POST" autocomplete="off">
        <label>New Password</label>
        <div class="input-wrapper">
            <input type="password" id="password" name="password" placeholder="Enter new password" required>
            <i class="ph ph-eye toggle-pass" data-target="password"></i>
        </div>

        <label>Confirm Password</label>
        <div class="input-wrapper">
            <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm new password" required>
            <i class="ph ph-eye toggle-pass" data-target="confirm_password"></i>
        </div>

        <input type="submit" name="update_password" value="Update Password">
    </form>
</div>

<script>
document.querySelectorAll('.toggle-pass').forEach(function(icon) {
    icon.addEventListener('click', function() {
        const targetId = this.getAttribute('data-target');
        const input = document.getElementById(targetId);
        if (input) {
            const isPass = input.type === 'password';
            input.type = isPass ? 'text' : 'password';
            this.classList.toggle('ph-eye');
            this.classList.toggle('ph-eye-slash');
        }
    });
});

/* ===== CANVAS PARTICLES ===== */
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

for (let i = 0; i < 35; i++) {
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
    particles.forEach(p => { p.update(); p.draw(); });
    requestAnimationFrame(animate);
}
animate();
</script>
</body>
</html>