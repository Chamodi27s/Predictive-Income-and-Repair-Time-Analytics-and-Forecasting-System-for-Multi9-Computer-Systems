<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

include 'db_config.php';
include 'navbar.php';

date_default_timezone_set('Asia/Colombo');
$today = date('Y-m-d');
$hour  = date('H');

$greeting = "Welcome";
$icon = "";

if ($hour < 12) {
    $greeting = "Good Morning";
    $icon = "☀️";
} elseif ($hour < 17) {
    $greeting = "Good Afternoon";
    $icon = "🌤️";
} else {
    $greeting = "Good Evening";
    $icon = "🌙";
}

/* Queries */
$pending_count = $conn->query("SELECT COUNT(*) c FROM job_device WHERE device_status='Pending'")->fetch_assoc()['c'];
$inprogress_count = $conn->query("SELECT COUNT(*) c FROM job_device WHERE device_status='In Progress'")->fetch_assoc()['c'];
$completed_count = $conn->query("
    SELECT COUNT(*) c
    FROM job_device jd
    JOIN job j ON jd.job_no=j.job_no
    WHERE jd.device_status='Completed'
    AND j.job_date='$today'
")->fetch_assoc()['c'];
$total_customers = $conn->query("SELECT COUNT(*) c FROM customer")->fetch_assoc()['c'];
$revenue_today = $conn->query("SELECT SUM(income) total FROM cashbook WHERE DATE(date)='$today'")->fetch_assoc()['total'] ?? 0;

// Returned Orders logic 
$returned_count = $conn->query("SELECT COUNT(*) c FROM invoice WHERE payment_status='Paid'")->fetch_assoc()['c'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Dashboard | Multi9</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<style>
/* ---------------- DASHBOARD CSS ---------------- */
body { background: var(--bg-light, #f8fafc); }
body.dark-mode { background: var(--bg-dark, #0f172a) !important; }

.main-container { max-width: 1400px; width: 96%; margin: 0 auto; padding-top: 35px; padding-bottom: 50px; }

.welcome-section { margin-bottom: 40px; display:flex; align-items:center; gap:20px; }
.welcome-icon { width:64px; height:64px; border-radius:20px; background:linear-gradient(135deg, rgba(16,185,129,0.2), rgba(16,185,129,0.05)); display:flex; align-items:center; justify-content:center; color:#10b981; font-size:36px; border:1px solid rgba(16,185,129,0.3); }
.welcome-text h1 { font-size: 32px; font-weight: 800; color: #1e293b; margin-bottom:6px; letter-spacing:-0.5px; }
body.dark-mode .welcome-text h1 { color: #f8fafc; }
.welcome-text .sub-text { font-size: 15px; color: #64748b; font-weight: 500; }
body.dark-mode .welcome-text .sub-text { color: #94a3b8; }
.welcome-text .sub-text strong { color: #10b981; font-weight:700; }

.dashboard-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 30px; }

.dashboard-card {
    background: #ffffff;
    border-radius: 24px;
    padding: 35px; 
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.04);
    border: 1.5px solid rgba(0,0,0,0.15); /* More visible outline */
    transition: 0.4s cubic-bezier(0.16, 1, 0.3, 1);
    display: flex; flex-direction: column; justify-content: space-between;
    text-decoration: none; position: relative; overflow: hidden;
    z-index: 10;
}

body.dark-mode .dashboard-card {
    background: rgba(30, 41, 59, 0.4);
    backdrop-filter: blur(16px);
    -webkit-backdrop-filter: blur(16px);
    border: 1.5px solid rgba(255, 255, 255, 0.15); /* More visible outline */
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
}

.dashboard-card:hover { transform: translateY(-8px); box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1); }
body.dark-mode .dashboard-card:hover { box-shadow: 0 20px 40px rgba(0, 0, 0, 0.5); border-color:rgba(255,255,255,0.1); }

.card-header { display: flex; justify-content: space-between; align-items: flex-start; }

.card-title { font-size: 13px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: #64748b; }
body.dark-mode .card-title { color: #94a3b8; }

.icon-box { width: 56px; height: 56px; border-radius: 16px; display: flex; align-items: center; justify-content: center; font-size: 28px; }

.card-value { font-size: 46px; font-weight: 800; margin-top: 25px; color:#0f172a; line-height:1; letter-spacing:-1px; }
body.dark-mode .card-value { color:#f8fafc; }

.card-footer { font-size: 13px; font-weight: 600; padding-top: 15px; color:#94a3b8; display:flex; align-items:center; gap:8px; }

/* Category Accents */
.accent-pending .icon-box { color: #f59e0b; background: rgba(245, 158, 11, 0.1); border:1px solid rgba(245, 158, 11, 0.2); box-shadow: inset 0 0 15px rgba(245,158,11,0.1); }
.accent-pending .card-footer i { color: #f59e0b; }
.dashboard-card.accent-pending:hover { border-bottom: 4px solid #f59e0b; padding-bottom: 31px; }

.accent-progress .icon-box { color: #3b82f6; background: rgba(59, 130, 246, 0.1); border:1px solid rgba(59, 130, 246, 0.2); box-shadow: inset 0 0 15px rgba(59,130,246,0.1); }
.accent-progress .card-footer i { color: #3b82f6; }
.dashboard-card.accent-progress:hover { border-bottom: 4px solid #3b82f6; padding-bottom: 31px; }

.accent-completed .icon-box { color: #10b981; background: rgba(16, 185, 129, 0.1); border:1px solid rgba(16, 185, 129, 0.2); box-shadow: inset 0 0 15px rgba(16,185,129,0.1); }
.accent-completed .card-footer i { color: #10b981; }
.dashboard-card.accent-completed:hover { border-bottom: 4px solid #10b981; padding-bottom: 31px; }

.accent-customers .icon-box { color: #8b5cf6; background: rgba(139, 92, 246, 0.1); border:1px solid rgba(139, 92, 246, 0.2); box-shadow: inset 0 0 15px rgba(139,92,246,0.1); }
.accent-customers .card-footer i { color: #8b5cf6; }
.dashboard-card.accent-customers:hover { border-bottom: 4px solid #8b5cf6; padding-bottom: 31px; }

.accent-revenue .icon-box { color: #06b6d4; background: rgba(6, 182, 212, 0.1); border:1px solid rgba(6, 182, 212, 0.2); box-shadow: inset 0 0 15px rgba(6,182,212,0.1); }
.accent-revenue .card-footer i { color: #06b6d4; }
.dashboard-card.accent-revenue:hover { border-bottom: 4px solid #06b6d4; padding-bottom: 31px; }

.accent-returned .icon-box { color: #ec4899; background: rgba(236, 72, 153, 0.1); border:1px solid rgba(236, 72, 153, 0.2); box-shadow: inset 0 0 15px rgba(236,72,153,0.1); }
.accent-returned .card-footer i { color: #ec4899; }
.dashboard-card.accent-returned:hover { border-bottom: 4px solid #ec4899; padding-bottom: 31px; }

/* MOBILE & TABLET RESPONSIVE */
@media screen and (max-width: 1024px) {
    .main-container {
        padding-top: 105px;
        padding-bottom: 60px;
        width: 94%;
    }

    .welcome-section {
        margin-bottom: 30px;
        gap: 16px;
    }

    .welcome-icon {
        width: 56px;
        height: 56px;
        font-size: 30px;
        border-radius: 16px;
    }

    .welcome-text h1 {
        font-size: 26px;
    }

    .welcome-text .sub-text {
        font-size: 14px;
    }

    .dashboard-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 20px;
    }

    .dashboard-card {
        padding: 26px 22px;
        border-radius: 20px;
    }

    .card-value {
        font-size: 38px;
        margin-top: 20px;
    }

    .icon-box {
        width: 50px;
        height: 50px;
        font-size: 24px;
        border-radius: 14px;
    }
}

@media screen and (max-width: 768px) {
    .main-container {
        padding-top: 100px;
        padding-bottom: 100px; /* Extra spacing so floating chatbot widget never covers content */
        width: 92%;
    }

    .welcome-section {
        flex-direction: row;
        align-items: center;
        gap: 14px;
        margin-bottom: 24px;
    }

    .welcome-icon {
        width: 48px;
        height: 48px;
        font-size: 26px;
        border-radius: 14px;
        flex-shrink: 0;
    }

    .welcome-text h1 {
        font-size: 22px;
        margin-bottom: 2px;
    }

    .welcome-text .sub-text {
        font-size: 13px;
    }

    .dashboard-grid {
        grid-template-columns: 1fr;
        gap: 16px;
    }

    .dashboard-card {
        padding: 22px 18px;
        border-radius: 18px;
    }

    .card-title {
        font-size: 12px;
    }

    .card-value {
        font-size: 34px;
        margin-top: 16px;
    }

    .card-footer {
        font-size: 12px;
        padding-top: 12px;
    }

    .icon-box {
        width: 46px;
        height: 46px;
        font-size: 22px;
        border-radius: 12px;
    }
}

@media screen and (max-width: 480px) {
    .main-container {
        padding-top: 95px;
        padding-bottom: 100px;
        width: 94%;
    }

    .welcome-section {
        gap: 12px;
    }

    .welcome-text h1 {
        font-size: 20px;
    }

    .welcome-text .sub-text {
        font-size: 12.5px;
    }

    .card-value {
        font-size: 30px;
    }
}
/* BACKGROUND ANIMATION */
.bg-animation {
    position: fixed;
    top: 0; left: 0; width: 100vw; height: 100vh;
    z-index: -1;
    overflow: hidden;
    pointer-events: none;
}
.orb {
    position: absolute;
    border-radius: 50%;
    filter: blur(70px);
    opacity: 0.9;
    animation: float 12s infinite ease-in-out alternate;
}
.orb-1 { width: 600px; height: 600px; background: rgba(16, 185, 129, 0.35); top: -150px; left: -150px; animation-delay: 0s; }
.orb-2 { width: 500px; height: 500px; background: rgba(59, 130, 246, 0.35); bottom: -100px; right: -100px; animation-delay: -3s; }
.orb-3 { width: 450px; height: 450px; background: rgba(139, 92, 246, 0.35); top: 35%; left: 50%; animation-delay: -7s; }

body.dark-mode .orb { opacity: 0.6; }
body.dark-mode .orb-1 { background: rgba(16, 185, 129, 0.6); }
body.dark-mode .orb-2 { background: rgba(59, 130, 246, 0.6); }
body.dark-mode .orb-3 { background: rgba(139, 92, 246, 0.6); }

@keyframes float {
    0% { transform: translate(0, 0) scale(1); }
    33% { transform: translate(100px, -120px) scale(1.2); }
    66% { transform: translate(-80px, 100px) scale(0.8); }
    100% { transform: translate(0, 0) scale(1); }
}
</style>
</head>
<body>
    <div class="bg-animation">
        <div class="orb orb-1"></div>
        <div class="orb orb-2"></div>
        <div class="orb orb-3"></div>
    </div>
    <?php include 'chatbot.php'; ?>

<div class="main-container">
    <div class="welcome-section">
        <div class="welcome-icon">
            <i class="ph-fill ph-hand-waving"></i>
        </div>
        <div class="welcome-text">
            <h1><?php echo $greeting; ?>, Multi9</h1>
            <div class="sub-text">
                Business Overview for <strong><?php echo date('l, F j, Y'); ?></strong>
            </div>
        </div>
    </div>

    <div class="dashboard-grid">
        <a href="job_list.php?status=Pending" class="dashboard-card accent-pending">
            <div class="card-header">
                <span class="card-title">Pending Repairs</span>
                <span class="icon-box"><i class="ph-fill ph-hourglass-high"></i></span>
            </div>
            <div>
                <div class="card-value"><span class="counter" data-target="<?php echo $pending_count; ?>">0</span></div>
                <div class="card-footer"><i class="ph-bold ph-clock"></i> Waiting for action</div>
            </div>
        </a>

        <a href="job_list.php?status=In Progress" class="dashboard-card accent-progress">
            <div class="card-header">
                <span class="card-title">In Progress</span>
                <span class="icon-box"><i class="ph-fill ph-spinner-gap"></i></span>
            </div>
            <div>
                <div class="card-value"><span class="counter" data-target="<?php echo $inprogress_count; ?>">0</span></div>
                <div class="card-footer"><i class="ph-bold ph-wrench"></i> Currently working</div>
            </div>
        </a>

        <a href="job_list.php?status=Completed" class="dashboard-card accent-completed">
            <div class="card-header">
                <span class="card-title">Completed Today</span>
                <span class="icon-box"><i class="ph-fill ph-check-circle"></i></span>
            </div>
            <div>
                <div class="card-value"><span class="counter" data-target="<?php echo $completed_count; ?>">0</span></div>
                <div class="card-footer"><i class="ph-bold ph-thumbs-up"></i> Successfully done</div>
            </div>
        </a>

        <a href="customer_list.php" class="dashboard-card accent-customers">
            <div class="card-header">
                <span class="card-title">Total Customers</span>
                <span class="icon-box"><i class="ph-fill ph-users"></i></span>
            </div>
            <div>
                <div class="card-value"><span class="counter" data-target="<?php echo $total_customers; ?>">0</span></div>
                <div class="card-footer"><i class="ph-bold ph-address-book"></i> Total Registered</div>
            </div>
        </a>

        <a href="cashbook_view.php" class="dashboard-card accent-revenue">
            <div class="card-header">
                <span class="card-title">Revenue Today</span>
                <span class="icon-box"><i class="ph-fill ph-wallet"></i></span>
            </div>
            <div>
                <div class="card-value">Rs.<span class="counter-currency" data-target="<?php echo $revenue_today; ?>">0.00</span></div>
                <div class="card-footer"><i class="ph-bold ph-trend-up"></i> Daily Income</div>
            </div>
        </a>

        <a href="returned_jobs.php" class="dashboard-card accent-returned">
            <div class="card-header">
                <span class="card-title">Returned Orders</span>
                <span class="icon-box"><i class="ph-fill ph-arrow-counter-clockwise"></i></span>
            </div>
            <div>
                <div class="card-value"><span class="counter" data-target="<?php echo $returned_count; ?>">0</span></div>
                <div class="card-footer"><i class="ph-bold ph-package"></i> Paid & Handed over</div>
            </div>
        </a>
    </div>
</div>

<script>
    // Theme sync
    function syncDashboardTheme() {
        const theme = localStorage.getItem("darkMode");
        if (theme === "enabled") {
            document.body.classList.add("dark-mode");
        } else {
            document.body.classList.remove("dark-mode");
        }
    }
    syncDashboardTheme();
    window.addEventListener('storage', syncDashboardTheme);

    // Number Counter Animation
    document.addEventListener("DOMContentLoaded", () => {
        const speed = 200; // The lower the slower
        
        // Regular counters
        document.querySelectorAll('.counter').forEach(counter => {
            const updateCount = () => {
                const target = +counter.getAttribute('data-target');
                const count = +counter.innerText;
                const inc = target / speed;
                if (count < target) {
                    counter.innerText = Math.ceil(count + inc);
                    setTimeout(updateCount, 15);
                } else {
                    counter.innerText = target;
                }
            };
            updateCount();
        });

        // Currency counter (with decimals and commas)
        document.querySelectorAll('.counter-currency').forEach(counter => {
            const updateCount = () => {
                const target = +counter.getAttribute('data-target');
                const count = +(counter.innerText.replace(/,/g, ''));
                const inc = target / speed;
                if (count < target) {
                    const newValue = count + inc;
                    counter.innerText = newValue.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                    setTimeout(updateCount, 15);
                } else {
                    counter.innerText = target.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                }
            };
            updateCount();
        });

        // 3D Tilt Effect for Cards
        const cards = document.querySelectorAll('.dashboard-card');
        cards.forEach(card => {
            // Force navigation on click to bypass transform-interrupted native clicks
            card.addEventListener('click', (e) => {
                e.preventDefault(); // Stop native link just in case
                window.location.href = card.getAttribute('href');
            });

            card.addEventListener('mousemove', (e) => {
                const rect = card.getBoundingClientRect();
                const x = e.clientX - rect.left;
                const y = e.clientY - rect.top;
                
                const centerX = rect.width / 2;
                const centerY = rect.height / 2;
                
                const rotateX = ((y - centerY) / centerY) * -8; // subtle tilt
                const rotateY = ((x - centerX) / centerX) * 8;
                
                card.style.transform = `perspective(1000px) rotateX(${rotateX}deg) rotateY(${rotateY}deg) translateY(-8px) scale(1.02)`;
                card.style.transition = 'none';
                
                // Add a dynamic spotlight gradient based on mouse position
                card.style.background = `radial-gradient(circle at ${x}px ${y}px, rgba(255,255,255,0.8) 0%, rgba(255,255,255,1) 80%)`;
                if(document.body.classList.contains('dark-mode')) {
                     card.style.background = `radial-gradient(circle at ${x}px ${y}px, rgba(255,255,255,0.08) 0%, rgba(30, 41, 59, 0.4) 60%)`;
                }
            });
            
            card.addEventListener('mouseleave', () => {
                card.style.transform = 'translateY(0) scale(1) rotateX(0) rotateY(0)';
                card.style.transition = 'all 0.4s cubic-bezier(0.16, 1, 0.3, 1)';
                card.style.background = ''; // reset to default CSS
            });
        });
    });
</script>

</body>

</html>