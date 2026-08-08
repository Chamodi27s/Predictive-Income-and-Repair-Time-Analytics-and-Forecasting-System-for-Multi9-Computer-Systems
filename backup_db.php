<?php
session_start();
// if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] != 'admin') {
//     header("Location: login.php");
//     exit();
// }

date_default_timezone_set("Asia/Colombo");
$dbname = "servidedb";

// Navbar 
$current_page = basename($_SERVER['PHP_SELF']);
$user_name = isset($_SESSION['username']) ? $_SESSION['username'] : 'User';
$user_initial = strtoupper(substr($user_name, 0, 1));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Backup | Multi9 Computer Systems</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <style>
        /* --- THEME VARIABLES --- */
        :root {
            --primary: #10b981;
            --primary-hover: #059669;
            --primary-glow: rgba(16, 185, 129, 0.35);
            --bg-color: #f8fafc;
            --card-bg: #ffffff;
            --text-main: #0f172a;
            --text-sub: #64748b;
            --border-color: #e2e8f0;
            --status-bg: #f1f5f9;
        }

        body.dark-mode {
            --primary: #10b981;
            --primary-hover: #34d399;
            --primary-glow: rgba(16, 185, 129, 0.25);
            --bg-color: #0b0f19;
            --card-bg: #1e293b;
            --text-main: #f8fafc;
            --text-sub: #94a3b8;
            --border-color: #334155;
            --status-bg: #0f172a;
        }

        /* --- GLOBAL STYLES --- */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: linear-gradient(135deg, var(--bg-color) 0%, #e2e8f0 100%);
            color: var(--text-main);
            transition: background 0.3s, color 0.3s;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 110px 16px 50px;
        }

        body.dark-mode {
            background: linear-gradient(135deg, #0b0f19 0%, #020617 100%);
        }

        /* --- NAVBAR --- */
        .topbar {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            z-index: 9999;
            background: rgba(4, 63, 46, 0.95);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            color: white;
            padding: 12px 30px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        }

        body.dark-mode .topbar {
            background: rgba(2, 6, 23, 0.95);
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }

        .brand-section {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .brand strong {
            font-size: 18px;
            letter-spacing: 1px;
            color: #ffffff;
            font-weight: 800;
        }

        .online-pulse {
            display: inline-block;
            width: 8px;
            height: 8px;
            background: #10b981;
            border-radius: 50%;
            box-shadow: 0 0 10px #10b981;
            margin-left: 6px;
        }

        .menu {
            display: flex;
            gap: 8px;
            align-items: center;
        }

        .menu a {
            color: #a7f3d0 !important;
            text-decoration: none;
            font-size: 13.5px;
            font-weight: 600;
            padding: 6px 12px;
            border-radius: 8px;
            transition: 0.3s;
        }

        .menu a:hover,
        .menu a.active {
            color: #ffffff !important;
            background: rgba(255, 255, 255, 0.1);
        }

        .user-controls {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .theme-switch-wrapper {
            display: flex;
            align-items: center;
        }

        .theme-switch {
            display: inline-block;
            height: 22px;
            position: relative;
            width: 44px;
            cursor: pointer;
        }

        .theme-switch input {
            display: none;
        }

        .slider {
            background-color: rgba(255, 255, 255, 0.2);
            bottom: 0;
            left: 0;
            position: absolute;
            right: 0;
            top: 0;
            transition: .3s;
            border-radius: 34px;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .slider:before {
            background-color: #fff;
            bottom: 3px;
            content: "";
            height: 14px;
            left: 4px;
            position: absolute;
            transition: .3s;
            width: 14px;
            border-radius: 50%;
        }

        input:checked + .slider {
            background-color: var(--primary);
        }

        input:checked + .slider:before {
            transform: translateX(20px);
        }

        .profile-card {
            background: linear-gradient(135deg, #10b981, #047857);
            color: white;
            width: 34px;
            height: 34px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: 13px;
            box-shadow: 0 4px 10px rgba(16, 185, 129, 0.4);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        /* --- BACKUP CARD --- */
        .container {
            width: 100%;
            max-width: 480px;
            margin: auto;
        }

        .backup-card {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 24px;
            padding: 36px 30px;
            text-align: center;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.08);
            transition: transform 0.3s, box-shadow 0.3s;
            position: relative;
            overflow: hidden;
        }

        .backup-card::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 6px;
            background: linear-gradient(90deg, #10b981, #059669, #3b82f6);
        }

        body.dark-mode .backup-card {
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.5);
            background: rgba(30, 41, 59, 0.95);
        }

        .icon-circle {
            width: 84px;
            height: 84px;
            border-radius: 50%;
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.15), rgba(5, 150, 105, 0.25));
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 0 auto 20px;
            font-size: 36px;
            color: var(--primary);
            box-shadow: 0 0 30px var(--primary-glow);
            border: 2px solid rgba(16, 185, 129, 0.3);
            animation: pulseGlow 3s infinite ease-in-out;
        }

        @keyframes pulseGlow {
            0%, 100% { transform: scale(1); box-shadow: 0 0 25px var(--primary-glow); }
            50% { transform: scale(1.05); box-shadow: 0 0 35px var(--primary-glow); }
        }

        .status-tag {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(16, 185, 129, 0.12);
            color: #10b981;
            padding: 5px 14px;
            border-radius: 20px;
            font-size: 11.5px;
            font-weight: 800;
            letter-spacing: 0.8px;
            text-transform: uppercase;
            margin-bottom: 12px;
        }

        h2 {
            margin: 6px 0 6px;
            font-size: 26px;
            font-weight: 800;
            color: var(--text-main);
            letter-spacing: -0.5px;
        }

        .subtitle {
            color: var(--text-sub);
            font-size: 13.5px;
            margin-bottom: 26px;
            font-weight: 500;
        }

        /* --- STATS CONTAINER --- */
        .status-box {
            background-color: var(--status-bg);
            border-radius: 16px;
            padding: 20px;
            text-align: left;
            margin-bottom: 26px;
            border: 1px solid var(--border-color);
            display: flex;
            flex-direction: column;
            gap: 14px;
        }

        .status-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 13.5px;
            gap: 10px;
        }

        .status-label {
            color: var(--text-sub);
            display: flex;
            align-items: center;
            gap: 8px;
            font-weight: 600;
        }

        .status-label i {
            color: var(--primary);
            font-size: 15px;
            width: 18px;
            text-align: center;
        }

        .status-value {
            color: var(--text-main);
            font-weight: 700;
            text-align: right;
        }

        .val-badge {
            background: rgba(16, 185, 129, 0.15);
            color: #10b981;
            padding: 4px 10px;
            border-radius: 8px;
            font-size: 12.5px;
        }

        .val-code {
            background: rgba(100, 116, 139, 0.15);
            padding: 4px 8px;
            border-radius: 6px;
            font-family: 'Courier New', monospace;
            font-size: 12.5px;
        }

        /* --- BUTTON & FOOTER --- */
        .btn-backup {
            width: 100%;
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            border: none;
            padding: 16px;
            border-radius: 14px;
            font-size: 15.5px;
            font-weight: 800;
            color: white;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
            box-shadow: 0 10px 25px var(--primary-glow);
        }

        .btn-backup:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 30px var(--primary-glow);
            background: linear-gradient(135deg, #34d399 0%, #059669 100%);
        }

        .btn-backup:active {
            transform: translateY(0);
        }

        .footer-links {
            margin-top: 22px;
        }

        .btn-back-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            color: var(--text-sub);
            font-size: 13.5px;
            font-weight: 600;
            padding: 10px 18px;
            border-radius: 12px;
            background: var(--status-bg);
            border: 1px solid var(--border-color);
            transition: 0.3s;
        }

        .btn-back-link:hover {
            color: var(--primary);
            border-color: var(--primary);
            transform: translateX(-3px);
        }

        .system-info {
            margin-top: 20px;
            font-size: 11px;
            color: var(--text-sub);
            letter-spacing: 0.6px;
            font-weight: 700;
            opacity: 0.8;
        }

        @media print {
            .no-print { display: none !important; }
        }

        /* ==================== RESPONSIVE MEDIA QUERIES ==================== */
        @media (max-width: 768px) {
            body {
                padding: 95px 12px 40px;
            }

            .topbar {
                padding: 10px 16px;
            }

            .brand strong {
                font-size: 16px;
            }

            .menu {
                display: flex;
            }

            .menu a {
                font-size: 12.5px;
                padding: 5px 8px;
            }

            .backup-card {
                padding: 28px 20px;
                border-radius: 20px;
            }

            .icon-circle {
                width: 72px;
                height: 72px;
                font-size: 30px;
                margin-bottom: 16px;
            }

            h2 {
                font-size: 22px;
            }

            .subtitle {
                font-size: 12.5px;
                margin-bottom: 20px;
            }

            .status-box {
                padding: 16px 14px;
                gap: 12px;
                margin-bottom: 22px;
            }

            .status-row {
                font-size: 13px;
            }

            .btn-backup {
                padding: 14px;
                font-size: 14.5px;
                border-radius: 12px;
            }
        }

        @media (max-width: 480px) {
            body {
                padding: 85px 8px 30px;
            }

            .topbar {
                padding: 8px 12px;
            }

            .backup-card {
                padding: 24px 16px;
                border-radius: 18px;
            }

            .status-row {
                flex-direction: column;
                align-items: flex-start;
                gap: 4px;
            }

            .status-value {
                text-align: left;
                width: 100%;
                word-break: break-all;
            }

            .menu {
                gap: 4px;
            }

            .menu a {
                font-size: 12px;
                padding: 4px 6px;
            }
        }
    </style>
</head>

<body>

<div class="topbar no-print">
    <div class="brand-section">
        <i class="fas fa-database" style="color: #10b981; font-size: 20px;"></i>
        <div class="brand">
            <strong>MULTI 9</strong>
            <span class="online-pulse" title="System Online"></span>
        </div>
    </div>
    
    <div class="menu">
        <a href="index.php"><i class="fas fa-home"></i> Dashboard</a>
        <a href="backup_db.php" class="active"><i class="fas fa-shield-alt"></i> Backup</a>
    </div>

    <div class="user-controls">
        <div class="theme-switch-wrapper">
            <span style="margin-right: 6px; font-size: 13px;">🌙</span>
            <label class="theme-switch">
                <input type="checkbox" id="darkToggle">
                <span class="slider"></span>
            </label>
        </div>
        <div class="user-section">
            <div class="profile-card"><?= $user_initial ?></div>
        </div>
    </div>
</div>

<div class="container">
    <div class="backup-card">
        <div class="icon-circle">
            <i class="fas fa-database"></i>
        </div>

        <div class="status-tag">
            <i class="fas fa-check-circle"></i> Maintenance Console
        </div>

        <h2>System Backup</h2>
        <div class="subtitle">Multi9 Computer Systems - Database Protection Engine</div>

        <div class="status-box">
            <div class="status-row">
                <span class="status-label"><i class="fas fa-server"></i> Database Name</span>
                <span class="status-value"><strong class="val-badge"><?php echo $dbname ?></strong></span>
            </div>
            <div class="status-row">
                <span class="status-label"><i class="fas fa-clock"></i> Last Sync Time</span>
                <span class="status-value"><strong id="serverTime" style="font-family: monospace; font-size: 13px; color: var(--primary);"><?php echo date("Y-m-d H:i:s") ?></strong></span>
            </div>
            <div class="status-row">
                <span class="status-label"><i class="fas fa-hdd"></i> Storage Path</span>
                <span class="status-value"><strong class="val-code">/backups/sql/</strong></span>
            </div>
        </div>

        <button id="startBackup" class="btn-backup">
            <i class="fas fa-shield-alt"></i> Generate Full Backup
        </button>

        <div class="footer-links">
            <a href="index.php" class="btn-back-link"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
        </div>

        <div class="system-info">
            🔒 SECURE ENCRYPTED BACKUP ENGINE v2.0
        </div>
    </div>
</div>

<script>
    const darkToggle = document.getElementById('darkToggle');
    const body = document.body;

    // Theme පරීක්ෂාව
    if (localStorage.getItem('theme') === 'dark') {
        body.classList.add('dark-mode');
        darkToggle.checked = true;
    }

    darkToggle.addEventListener('change', () => {
        if (darkToggle.checked) {
            body.classList.add('dark-mode');
            localStorage.setItem('theme', 'dark');
        } else {
            body.classList.remove('dark-mode');
            localStorage.setItem('theme', 'light');
        }
    });

    // වෙලාව Update කිරීම
    function updateClock(){
        let now = new Date();
        let str = now.getFullYear() + "-" + 
                  String(now.getMonth()+1).padStart(2,'0') + "-" + 
                  String(now.getDate()).padStart(2,'0') + " " + 
                  String(now.getHours()).padStart(2,'0') + ":" + 
                  String(now.getMinutes()).padStart(2,'0') + ":" + 
                  String(now.getSeconds()).padStart(2,'0');
        document.getElementById("serverTime").innerHTML = str;
    }
    setInterval(updateClock, 1000);

    // Backup Process
    $("#startBackup").click(function(){
        Swal.fire({
            title: 'Starting Backup...',
            text: 'Please do not close this window.',
            allowOutsideClick: false,
            didOpen: () => { Swal.showLoading(); }
        });

        $.ajax({
            url: "backup_process.php",
            type: "POST",
            dataType: "json",
            success: function(data){
                if(data.status === "success"){
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: 'Database has been backed up.',
                        confirmButtonText: 'Download File',
                        confirmButtonColor: '#10b981'
                    }).then((result) => {
                        if(result.isConfirmed) { window.location.href = data.download_url; }
                    });
                } else {
                    Swal.fire({ icon: 'error', title: 'Error', text: data.message });
                }
            },
            error: function(){
                Swal.fire({ icon: 'error', title: 'Failed', text: 'Connection to backup engine lost.' });
            }
        });
    });
</script>

</body>
</html>