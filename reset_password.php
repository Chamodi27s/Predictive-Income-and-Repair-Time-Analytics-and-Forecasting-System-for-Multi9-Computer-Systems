<?php
// Browser caching නිසා පරණ දත්ත පෙන්වීම වැළැක්වීමට headers එක් කිරීම
header("Cache-Control: no-cache, no-store, must-revalidate"); 
header("Pragma: no-cache"); 
header("Expires: 0"); 

session_start();
include 'db_config.php';

// 1. forgot.php හරහා ආපු කෙනෙක්ද කියලා බලනවා
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
        // 2. Password එක Database එකේ Auto Update කිරීම
        // reset_token සහ expiry දත්තද මෙහිදීම ඉවත් කරනු ලබයි
        $update_query = "UPDATE login_users SET password='$new_pass', reset_token=NULL, token_expiry=NULL WHERE email='$email'";
        
        if (mysqli_query($conn, $update_query)) {
            // 3. සාර්ථක නම් Session එක ඉවත් කර අලුත් password එක පාවිච්චි කිරීමට ඉඩ සැලසීම
            unset($_SESSION['reset_email']); 
            session_destroy();
            
            // සාර්ථක පණිවිඩය පෙන්වා වහාම login පිටුවට යොමු කිරීම (Auto Update/Refresh)
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
    <!-- Viewport set to avoid zooming out on mobile and keep UI user-friendly -->
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <title>Reset Password</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        html { -webkit-text-size-adjust: 100%; }
        body { font-family: 'Poppins', sans-serif; background: #121212; color: #fff; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; padding: 16px; box-sizing: border-box; font-size:16px; }
        .container { background: rgba(50,50,50,0.25); backdrop-filter: blur(16px); padding: 40px; border-radius: 16px; width: 100%; max-width: 400px; text-align: center; border: 1px solid rgba(255,255,255,0.1); }
        h2 { color: #00ffe0; margin-bottom: 25px; font-size:20px; }
        input { width: 100%; padding: 12px; margin-bottom: 15px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.2); background: rgba(255,255,255,0.05); color: #fff; box-sizing: border-box; outline: none; transition: 0.3s; font-size:14px; }
        .input-group { position: relative; }
        .input-group input { padding-right: 44px; }
        .toggle-password { position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: transparent; border: none; color: #fff; cursor: pointer; padding: 6px; display: inline-flex; align-items: center; justify-content: center; }
        .toggle-password svg { width: 20px; height: 20px; stroke: currentColor; fill: none; stroke-width: 1.5; }
        input:focus { border-color: #00ffe0; }
        input[type="submit"] { background: #1f7a63; border: none; cursor: pointer; font-weight: 600; transition: 0.3s; color: white; margin-top: 10px; padding:12px; }
        input[type="submit"]:hover { background: #2a9d80; transform: translateY(-2px); }
        .msg { color: #ff6b6b; margin-bottom: 15px; font-size: 14px; }

        /* Small devices (phones) - make controls larger to avoid zoom */
        @media (max-width: 480px) {
            body { align-items: flex-start; padding-top: 36px; }
            .container { padding: 22px; border-radius: 12px; max-width: 98%; box-shadow: 0 10px 30px rgba(0,0,0,0.5); }
            h2 { font-size: 20px; margin-bottom: 12px; }
            input { padding: 14px; font-size: 16px; }
            input[type="submit"] { padding: 14px; font-size: 16px; }
        }

        /* Medium devices (tablets) */
        @media (min-width: 481px) and (max-width: 991px) {
            body { padding: 24px; }
            .container { padding: 28px; max-width: 420px; }
            h2 { font-size: 20px; }
        }

        /* Large devices (desktops) */
        @media (min-width: 992px) {
            .container { max-width: 480px; padding: 48px; }
            h2 { font-size: 22px; }
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Create New Password</h2>
        <?php if ($message) echo "<p class='msg'>$message</p>"; ?>
        <form method="POST" autocomplete="off">
            <div class="input-group">
                <input type="password" id="password" name="password" placeholder="New Password" required>
                <button type="button" class="toggle-password" data-target="password" aria-label="Show password">
                    <svg viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7S1 12 1 12z"></path>
                        <circle cx="12" cy="12" r="3"></circle>
                    </svg>
                </button>
            </div>

            <div class="input-group">
                <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm Password" required>
                <button type="button" class="toggle-password" data-target="confirm_password" aria-label="Show confirm password">
                    <svg viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7S1 12 1 12z"></path>
                        <circle cx="12" cy="12" r="3"></circle>
                    </svg>
                </button>
            </div>

            <input type="submit" name="update_password" value="Update Password">
        </form>
    </div>
</body>
<script>
// Toggle password visibility for inputs with .toggle-password
document.querySelectorAll('.toggle-password').forEach(function(btn){
    btn.addEventListener('click', function(){
        var targetId = btn.getAttribute('data-target');
        var input = document.getElementById(targetId);
        if (!input) return;
        var isPassword = input.type === 'password';
        if (isPassword) {
            input.type = 'text';
            btn.setAttribute('aria-label', 'Hide password');
            // eye-off (slash) icon
            btn.innerHTML = '\n+                <svg viewBox="0 0 24 24" aria-hidden="true">\n+                    <path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7S1 12 1 12z"></path>\n+                    <circle cx="12" cy="12" r="3"></circle>\n+                    <path d="M3 3l18 18" stroke-width="1.5"></path>\n+                </svg>';
        } else {
            input.type = 'password';
            btn.setAttribute('aria-label', 'Show password');
            // eye icon
            btn.innerHTML = '\n+                <svg viewBox="0 0 24 24" aria-hidden="true">\n+                    <path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7S1 12 1 12z"></path>\n+                    <circle cx="12" cy="12" r="3"></circle>\n+                </svg>';
        }
    });
});
</script>
</html>