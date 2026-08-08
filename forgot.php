<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/Exception.php';
require 'PHPMailer/PHPMailer.php';
require 'PHPMailer/SMTP.php';

session_start();
include 'db_config.php'; 

$error = '';

if (isset($_POST['reset'])) {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    
    
    $query = "SELECT * FROM login_users WHERE email='$email'";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) > 0) {
        $otp = rand(100000, 999999);
        
        $expiry = date("Y-m-d H:i:s", strtotime('+5 minutes'));

        
        $update = "UPDATE login_users SET reset_token='$otp', token_expiry='$expiry' WHERE email='$email'";
        mysqli_query($conn, $update);

        $mail = new PHPMailer(true);

        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'vibuddha2025@gmail.com'; // ඔයාගේ Gmail එක
            $mail->Password   = 'jzfdneipexlnjzat';       // App Password එක (හිස්තැන් නැතිව)
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            // Recipients
            $mail->setFrom('vibuddha2025@gmail.com', 'Multi9 Systems');
            $mail->addAddress($email);

            // Content
            $mail->isHTML(true);
            $mail->Subject = 'Your Password Reset OTP';
            $mail->Body    = "Your verification code is: <b>$otp</b>. It will expire in 5 minutes.";

            $mail->send();
            $_SESSION['reset_email'] = $email;
            header("Location: verify.php"); // සාර්ථක නම් verify.php වෙත
            exit();

        } catch (Exception $e) {
            $error = "Mail error: {$mail->ErrorInfo}";
        }
    } else {
        $error = "❌ Email not found in our system!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Forgot Password</title>
    <!-- Viewport: set initial scale and prevent pinch-zoom (max-scale=1) -->
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        /* Base styles */
        html { -webkit-text-size-adjust: 100%; }
        body { font-family: 'Poppins', sans-serif; background: #121212; color: #fff; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; padding: 16px; box-sizing: border-box; font-size:16px; }
        .container { background: rgba(50,50,50,0.25); backdrop-filter: blur(16px); padding: 40px; border-radius: 16px; width: 100%; max-width: 400px; text-align: center; box-shadow: 0 15px 40px rgba(0,0,0,0.4); border: 1px solid rgba(255,255,255,0.1); }
        h2 { color: #00ffe0; margin-bottom: 20px; font-size: 20px; }
        input { width: 100%; padding: 12px; margin-bottom: 15px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.2); background: rgba(255,255,255,0.05); color: #fff; box-sizing: border-box; }
        input[type="submit"] { background: #1f7a63; border: none; cursor: pointer; font-weight: 600; transition: 0.3s; padding: 12px; }
        input[type="submit"]:hover { background: #165e4d; }
        .error { color: #ff6b6b; font-size: 14px; margin-bottom: 15px; }
        .back { display: block; margin-top: 15px; color: #00ffe0; text-decoration: none; font-size: 14px; }

        /* Small devices (phones) */
        @media (max-width: 480px) {
            /* Make UI larger and avoid the need to zoom */
            body { align-items: flex-start; padding-top: 36px; }
            .container { padding: 22px; border-radius: 12px; max-width: 98%; box-shadow: 0 10px 30px rgba(0,0,0,0.5); }
            h2 { font-size: 20px; margin-bottom: 12px; }
            input { padding: 14px; font-size: 16px; }
            input[type="submit"] { padding: 14px; font-size: 16px; }
            .back { font-size: 14px; }
        }

        /* Medium devices (tablets) */
        @media (min-width: 481px) and (max-width: 991px) {
            body { padding: 24px; }
            .container { padding: 28px; max-width: 420px; }
            h2 { font-size: 20px; }
        }

        /* Large devices (desktops) - gentle scaling */
        @media (min-width: 992px) {
            .container { max-width: 480px; padding: 48px; }
            h2 { font-size: 22px; }
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Recover Account</h2>
        <?php if ($error) echo "<div class='error'>$error</div>"; ?>
        <form method="POST">
            <input type="email" name="email" placeholder="Enter your email" required>
            <input type="submit" name="reset" value="Send Security Code">
        </form>
        <a href="login.php" class="back">← Back to Login</a>
    </div>
</body>

