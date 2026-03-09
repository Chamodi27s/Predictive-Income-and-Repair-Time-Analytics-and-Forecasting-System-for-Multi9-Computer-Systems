<?php
session_start();

/* ===============================
   DUMMY USER DATA (NO DATABASE)
================================ */
$DUMMY_EMAIL    = "multi9@gmail.com";
$DUMMY_USERNAME = "multi9";
$DUMMY_PASSWORD = "multi912#";

$error = '';
$success = '';

if (isset($_POST['reset'])) {
    $email = trim($_POST['email']);

    if ($email === '') {
        $error = "❌ Please enter your email address.";
    } elseif ($email === $DUMMY_EMAIL) {
        $success = "
            ✅ Account found!<br><br>
            <strong>Username:</strong> $DUMMY_USERNAME<br>
            <strong>Password:</strong> $DUMMY_PASSWORD<br><br>
           
        ";
    } else {
        $error = "❌ Email not found!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Forgot Username / Password</title>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">

<style>
body {
    font-family: 'Poppins', sans-serif;
    background: #1f1f1f;
    color: #fff;
    height: 100vh;
    margin: 0;
    display: flex;
    justify-content: center;
    align-items: center;
}
.container {
    background: rgba(50,50,50,0.25);
    backdrop-filter: blur(16px);
    padding: 30px 40px;
    border-radius: 16px;
    max-width: 420px;
    width: 100%;
    text-align: center;
    box-shadow: 0 15px 40px rgba(0,0,0,0.35);
}
h2 {
    margin-bottom: 20px;
    color: #00ffe0;
}
input {
    width: 100%;
    padding: 12px;
    margin-bottom: 15px;
    border-radius: 8px;
    border: 1px solid rgba(255,255,255,0.5);
    background: rgba(255,255,255,0.1);
    color: #fff;
}
input::placeholder {
    color: rgba(255,255,255,0.6);
}
input[type="submit"] {
    background: #1f7a63;
    border: none;
    cursor: pointer;
    transition: 0.3s;
}
input[type="submit"]:hover {
    background: #0e5b43;
}
.error {
    color: #ff6b6b;
    margin-bottom: 15px;
}
.success {
    color: #a2ff8e;
    margin-bottom: 15px;
    line-height: 1.6;
}
.back {
    display: block;
    margin-top: 12px;
    color: #cfdfff;
    text-decoration: none;
    font-size: 14px;
}
</style>
</head>

<body>

<div class="container">
    <h2>Forgot Username / Password</h2>

    <?php if ($error)   echo "<div class='error'>$error</div>"; ?>
    <?php if ($success) echo "<div class='success'>$success</div>"; ?>

    <form method="POST" autocomplete="off">
        <input type="email" name="email" placeholder="Enter your email" required>
        <input type="submit" name="reset" value="Recover Account">
    </form>

    <a class="back" href="login.php">← Back to Login</a>
</div>

</body>
<?php include 'chatbot.php'; ?>
</html>
