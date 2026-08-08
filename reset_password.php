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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; background: #121212; color: #fff; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .container { background: rgba(50,50,50,0.25); backdrop-filter: blur(16px); padding: 40px; border-radius: 16px; width: 100%; max-width: 400px; text-align: center; border: 1px solid rgba(255,255,255,0.1); }
        h2 { color: #00ffe0; margin-bottom: 25px; }
        input { width: 100%; padding: 12px; margin-bottom: 15px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.2); background: rgba(255,255,255,0.05); color: #fff; box-sizing: border-box; outline: none; transition: 0.3s; }
        input:focus { border-color: #00ffe0; }
        input[type="submit"] { background: #1f7a63; border: none; cursor: pointer; font-weight: 600; transition: 0.3s; color: white; margin-top: 10px; }
        input[type="submit"]:hover { background: #2a9d80; transform: translateY(-2px); }
        .msg { color: #ff6b6b; margin-bottom: 15px; font-size: 14px; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Create New Password</h2>
        <?php if ($message) echo "<p class='msg'>$message</p>"; ?>
        <form method="POST" autocomplete="off">
            <input type="password" name="password" placeholder="New Password" required>
            <input type="password" name="confirm_password" placeholder="Confirm Password" required>
            <input type="submit" name="update_password" value="Update Password">
        </form>
    </div>
</body>
</html>