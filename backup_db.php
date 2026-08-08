<?php
session_start();
include 'db_config.php';
include 'navbar.php';

date_default_timezone_set("Asia/Colombo");
$dbname = "servidedb";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Backup | Multi9 Computer Systems</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <style>
        /* --- THEME VARIABLES --- */
        :root {
            --primary: #2ea043;
            --bg-color: #f4f7f6;
            --card-bg: #ffffff;
            --text-main: #24292f;
            --text-sub: #57606a;
            --border-color: #d0d7de;
            --status-bg: #f6f8fa;
        }

        body.dark-mode {
            --bg-color: #0d1117;
            --card-bg: #161b22;
            --text-main: #ffffff;
            --text-sub: #8b949e;
            --border-color: #30363d;
            --status-bg: #0d1117;
        }

        /* --- GLOBAL STYLES --- */
        body {
            margin: 0;
            font-family: 'Poppins', sans-serif;
            background-color: var(--bg-color);
            color: var(--text-main);
            transition: background-color 0.3s, color 0.3s;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding-top: var(--nav-height);
        }

        /* --- BACKUP CARD --- */
        .container { width: 100%; max-width: 500px; padding: 20px; }
        
        .backup-card {
            background-color: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 16px;
            padding: 35px;
            text-align: center;
            box-shadow: 0 8px 24px rgba(0,0,0,0.1);
            transition: 0.3s;
        }
        body.dark-mode .backup-card { box-shadow: 0 10px 35px rgba(0,0,0,0.5); }

        .icon-circle {
            width: 70px; height: 70px;
            border-radius: 50%;
            background: linear-gradient(135deg, #2ea043, #3fb950);
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 20px;
            box-shadow: 0 6px 20px rgba(46,160,67,0.4);
        }
        .icon-circle i { font-size: 30px; color: white; }

        h2 { font-size: 22px; font-weight: 700; margin-bottom: 6px; color: var(--text-main); }
        .subtitle { font-size: 13px; color: var(--text-sub); margin-bottom: 25px; }

        .status-box {
            background: var(--status-bg);
            border: 1px solid var(--border-color);
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 25px;
            text-align: left;
        }
        .status-row { display: flex; justify-content: space-between; margin-bottom: 10px; font-size: 14px; }
        .status-row:last-child { margin-bottom: 0; }
        .status-row span { color: var(--text-sub); }
        .status-row strong { color: var(--text-main); }
        .status-row i { margin-right: 8px; color: var(--primary); }

        .btn-backup {
            width: 100%; background-color: var(--primary); border: none; padding: 16px;
            border-radius: 10px; font-size: 16px; font-weight: 600; color: white;
            cursor: pointer; transition: 0.3s; display: flex; justify-content: center; align-items: center; gap: 10px;
        }
        .btn-backup:hover { background-color: #3fb950; transform: translateY(-2px); box-shadow: 0 5px 15px rgba(46,160,67,0.3); }

        .footer-links { margin-top: 25px; }
        .footer-links a { text-decoration: none; color: #0969da; font-size: 14px; font-weight: 500; }
        body.dark-mode .footer-links a { color: #58a6ff; }

        .system-info { margin-top: 20px; font-size: 11px; color: var(--text-sub); letter-spacing: 0.5px; }

        @media print { .no-print { display: none !important; } }
    </style>
</head>

<body>

<div class="container">
    <div class="backup-card">
        <div class="icon-circle">
            <i class="fas fa-database"></i>
        </div>

        <h2>System Backup</h2>
        <div class="subtitle">Multi9 Computer Systems - Maintenance Console</div>

        <div class="status-box">
            <div class="status-row">
                <span><i class="fas fa-server"></i> Database Name</span>
                <strong><?php echo $dbname ?></strong>
            </div>
            <div class="status-row">
                <span><i class="fas fa-clock"></i> Last Sync Time</span>
                <strong id="serverTime"><?php echo date("Y-m-d H:i:s") ?></strong>
            </div>
            <div class="status-row">
                <span><i class="fas fa-hdd"></i> Storage Path</span>
                <strong>/backups/sql/</strong>
            </div>
        </div>

        <button id="startBackup" class="btn-backup">
            <i class="fas fa-shield-alt"></i> Generate Full Backup
        </button>

        <div class="footer-links">
            <a href="index.php"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
        </div>

        <div class="system-info">
            SECURE ENCRYPTED BACKUP ENGINE v2.0
        </div>
    </div>
</div>

<script>
    // Sync dark mode with the rest of the app (uses 'darkMode' key like all pages)
    (function() {
        if (localStorage.getItem('darkMode') === 'enabled') {
            document.body.classList.add('dark-mode');
        }
    })();

    // Clock update
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
                        confirmButtonColor: '#2ea043'
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