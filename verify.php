<?php
session_start();
include 'db_config.php'; 
// Redirect if the session is not set
if (!isset($_SESSION['reset_email'])) {
    header("Location: forgot.php");
    exit();
}

$error = '';

if (isset($_POST['verify'])) {
    // Sanitize input
    $otp = mysqli_real_escape_string($conn, $_POST['otp']);
    $email = $_SESSION['reset_email'];

    // 1. Fetch the user's current token and expiry from the database
    $query = "SELECT reset_token, token_expiry FROM login_users WHERE email='$email'";
    $result = mysqli_query($conn, $query);

    if ($row = mysqli_fetch_assoc($result)) {
        $db_otp = $row['reset_token'];
        $db_expiry = $row['token_expiry'];
        
        // Current time in PHP (matches db_config.php timezone)
        $current_time = date("Y-m-d H:i:s");

        // 2. Check if the OTP matches exactly
        if ($otp === $db_otp) {
            // 3. Check if the current time is before the expiry time
            if ($db_expiry > $current_time) {
                // Success: Redirect to reset page
                header("Location: reset_password.php");
                exit();
            } else {
                $error = "❌ This OTP has expired! Please request a new one.";
            }
        } else {
            $error = "❌ Invalid OTP code. Please check your email and try again.";
        }
    } else {
        $error = "❌ User session error. Please restart the process.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <title>Verify OTP | Multi9 Systems</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        html { -webkit-text-size-adjust: 100%; }
        body { font-family: 'Poppins', sans-serif; background: #121212; color: #fff; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; padding: 16px; box-sizing: border-box; font-size:16px; }
        .container { background: rgba(50,50,50,0.25); backdrop-filter: blur(16px); padding: 40px; border-radius: 16px; width: 100%; max-width: 420px; text-align: center; border: 1px solid rgba(255,255,255,0.1); box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.37); }
        h2 { color: #00ffe0; margin-top: 0; font-size: 22px; }
        .timer { font-size: 24px; font-weight: bold; color: #ff6b6b; margin: 20px 0; }
        input[type="text"] { width: 100%; padding: 16px; margin-bottom: 15px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.2); background: rgba(255,255,255,0.05); color: #fff; text-align: center; font-size: 24px; letter-spacing: 8px; font-weight: bold; box-sizing: border-box; }
        input[type="submit"] { width: 100%; padding: 14px; background: #1f7a63; border: none; border-radius: 8px; color: white; cursor: pointer; font-weight: 600; font-size: 16px; transition: 0.3s; }
        input[type="submit"]:hover { background: #165e4d; }
        .error-msg { color: #ff6b6b; background: rgba(255, 107, 107, 0.1); padding: 12px; border-radius: 8px; margin-bottom: 15px; font-size: 15px; }
        .resend-btn { color: #00ffe0; text-decoration: none; font-size: 14px; display: none; margin-top: 20px; font-weight: 500; }
        .resend-btn:hover { text-decoration: underline; }

        @media (max-width: 480px) {
            body { align-items: flex-start; padding-top: 32px; }
            .container { padding: 22px; max-width: 100%; border-radius: 12px; }
            h2 { font-size: 20px; }
            .timer { font-size: 22px; margin: 16px 0; }
            input[type="text"] { padding: 14px; font-size: 20px; letter-spacing: 6px; }
            input[type="submit"] { padding: 14px; font-size: 16px; }
            .error-msg { font-size: 14px; }
            .resend-btn { font-size: 14px; }
        }

        @media (min-width: 481px) and (max-width: 991px) {
            .container { padding: 28px; max-width: 460px; }
            input[type="text"] { padding: 15px; }
            input[type="submit"] { padding: 14px; }
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Verify OTP</h2>
        <p style="color: #ccc; font-size: 14px; margin-bottom: 5px;">We sent a code to:</p>
        <p style="color: #00ffe0; font-weight: 500; margin-top: 0;"><?php echo htmlspecialchars($_SESSION['reset_email']); ?></p>
        
        <div class="timer" id="timer">03:00</div>

        <?php if ($error): ?>
            <div class="error-msg"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST">
            <input type="text" name="otp" placeholder="••••••" maxlength="6" autocomplete="one-time-code" required autofocus>
            <input type="submit" name="verify" value="Verify & Continue">
        </form>

        <a href="forgot.php" class="resend-btn" id="resendBtn">Didn't receive code? Resend Email</a>
    </div>

    <script>
        let time = 180; // 3 minutes
        const timerElement = document.getElementById('timer');
        const resendBtn = document.getElementById('resendBtn');

        let countdown = setInterval(function() {
            let minutes = Math.floor(time / 60);
            let seconds = time % 60;
            seconds = seconds < 10 ? '0' + seconds : seconds;
            timerElement.innerHTML = `0${minutes}:${seconds}`;

            if (time <= 0) {
                clearInterval(countdown);
                timerElement.innerHTML = "Expired!";
                timerElement.style.color = "#888";
                resendBtn.style.display = "inline-block"; 
            } else {
                time--;
            }
        }, 1000);
    </script>
</body>
</html>