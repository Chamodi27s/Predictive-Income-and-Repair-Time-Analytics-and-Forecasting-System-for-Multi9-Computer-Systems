<?php
// account_management.php
include 'navbar.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Account Management</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, Helvetica, sans-serif;
        }

        body {
            background: #fbfaf7;
        }

        /* ===== PAGE WRAPPER (AFTER NAVBAR) ===== */
        .page-wrapper {
            min-height: calc(100vh - 70px); /* adjust if navbar height differs */
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* ===== MAIN CONTAINER ===== */
        .account-box {
            width: 520px;
            background: #efeceb;
            border-radius: 25px;
            padding: 50px 40px;
            border: 1px solid #c84c4c;
            text-align: center;
        }

        .account-box h2 {
            font-size: 32px;
            margin-bottom: 40px;
            color: #000;
        }

        .form-group {
            margin-bottom: 30px;
        }

        .form-group input {
            width: 100%;
            padding: 18px;
            border-radius: 10px;
            border: 1px solid #bdbdbd;
            font-size: 18px;
            color: #555;
            background: #f3f1f1;
        }

        .form-group input::placeholder {
            color: #9b9b9b;
        }

        .btn-edit {
            margin-top: 20px;
            width: 100%;
            padding: 18px;
            border: none;
            border-radius: 10px;
            background: linear-gradient(to right, #7b1f2a, #8d2b35);
            color: #fff;
            font-size: 22px;
            cursor: pointer;
        }

        .btn-edit:hover {
            opacity: 0.9;
        }
    </style>
</head>
<body>

<div class="page-wrapper">
    <div class="account-box">
        <h2>Account management</h2>

        <div class="form-group">
            <input type="password" placeholder="Change Password">
        </div>

        <div class="form-group">
            <input type="text" placeholder="Manage Users">
        </div>

        <button class="btn-edit">Edit</button>
    </div>
</div>

</body>
</html>
