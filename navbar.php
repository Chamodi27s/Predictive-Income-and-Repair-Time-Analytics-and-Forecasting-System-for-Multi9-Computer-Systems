<?php
// Session එක දැනටමත් active ද කියා පරීක්ෂා කර පසුව start කරයි
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// වර්තමාන පිටුව හඳුනා ගැනීම
$current_page = basename($_SERVER['PHP_SELF']);

// පරිශීලක තොරතුරු
$user_name = isset($_SESSION['username']) ? $_SESSION['username'] : 'User';
$user_initial = strtoupper(substr($user_name, 0, 1));
?>
<!-- Premium Fonts and Icons -->
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<script src="https://unpkg.com/@phosphor-icons/web"></script>
<link rel="stylesheet" href="CSS/global.css">

<style>
/* ---------------- NAVBAR STYLES ---------------- */
:root {
    --nav-bg: #043f2e;
    --nav-accent: #10b981;
}

.topbar {
    position: fixed; top: 0; left: 0; width: 100%; height: var(--nav-height); z-index: 9999;
    background: rgba(4, 63, 46, 0.98);
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
    border-bottom: 1px solid rgba(255,255,255,0.08);
    padding: 0 40px; display: flex; align-items: center; justify-content: space-between;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2); box-sizing: border-box;
    transition: 0.3s;
}

body.dark-mode .topbar { 
    background: rgba(2, 6, 23, 0.95) !important;
    border-bottom: 1px solid rgba(255,255,255,0.05);
    box-shadow: 0 4px 30px rgba(0, 0, 0, 0.5);
}

.brand-section { display: flex; align-items: center; gap: 14px; }
.brand { display: flex; flex-direction: column; justify-content: center; }
.brand strong { font-size: 20px; letter-spacing: 1.5px; color: #ffffff; font-weight: 800; line-height: 1.2; text-shadow: 0 2px 10px rgba(0,0,0,0.3); }
.brand small { font-size: 10px; letter-spacing: 3px; color: #6ee7b7; font-weight: 700; opacity: 0.9; }

.menu { display: flex; gap: 12px; align-items: center; height: 100%; }
.menu a { 
    color: #a7f3d0; text-decoration: none; font-size: 14px; font-weight: 500; 
    padding: 0 14px; position: relative; transition: 0.3s; 
    display: flex; align-items: center; gap: 8px; height: var(--nav-height); 
    opacity: 0.8;
}
.menu a:hover { color: #ffffff; opacity: 1; }
.menu a.active { color: #ffffff; font-weight: 700; opacity: 1; }
.menu a::after { 
    content: ""; position: absolute; left: 50%; bottom: 0; width: 0; height: 3px; 
    background: var(--nav-accent); border-radius: 4px 4px 0 0; 
    transition: 0.3s ease-out; transform: translateX(-50%); box-shadow: 0 -2px 10px rgba(16,185,129,0.6);
}
.menu a:hover::after { width: 60%; }
.menu a.active::after { width: 100%; }

.menu i.ph { font-size: 18px; transition: 0.3s; }
.menu a:hover i.ph { transform: translateY(-2px); color: var(--nav-accent); }
.menu a.active i.ph { color: var(--nav-accent); }

.user-section { display: flex; align-items: center; gap: 14px; position: relative; cursor: pointer; padding: 6px 8px 6px 20px; border-radius: 50px; background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.1); transition: 0.3s; }
body.dark-mode .user-section { background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.05); }
.user-section:hover { background: rgba(255, 255, 255, 0.15); border-color: rgba(255,255,255,0.25); box-shadow: 0 4px 12px rgba(0,0,0,0.1); }

.user-name-text { font-size: 13px; font-weight: 600; color: #ffffff; display: block; letter-spacing: 0.5px; }

.profile-card { background: linear-gradient(135deg, #10b981, #047857); color: white; width: 34px; height: 34px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 14px; box-shadow: 0 4px 10px rgba(16,185,129,0.4); }

.profile-dropdown { position: absolute; top: calc(100% + 15px); right: 0; background: #ffffff; min-width: 230px; border-radius: 16px; box-shadow: 0 20px 40px rgba(0,0,0,0.2); border: 1px solid rgba(0,0,0,0.05); display: none; overflow: hidden; z-index: 10000; padding:8px; }
body.dark-mode .profile-dropdown { background: #1e293b; border-color: #334155; box-shadow: 0 20px 50px rgba(0,0,0,0.6); }
.profile-dropdown.active { display: block; animation: slideDown 0.2s cubic-bezier(0.16, 1, 0.3, 1); }
.profile-dropdown a { display: flex; align-items: center; gap: 12px; padding: 12px 16px; color: #475569; text-decoration: none; font-size: 14px; font-weight:600; border-radius:10px; transition: 0.2s; }
.profile-dropdown a:hover { background: #f8fafc; color:#0f172a; padding-left: 20px; }
body.dark-mode .profile-dropdown a { color: #cbd5e1; }
body.dark-mode .profile-dropdown a:hover { background: #334155; color:#f8fafc; }
.profile-dropdown a i.ph { font-size: 20px; color: var(--nav-accent); transition:0.2s; }
.profile-dropdown a:hover i.ph { transform:scale(1.1); }


@keyframes slideDown { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }
</style>

<div class="topbar no-print">
    <div class="brand-section">
        <i class="ph ph-cpu" style="font-size: 32px; color: #04d992;"></i>
        <div class="brand">
            <strong>MULTI 9</strong>
            <small>COMPUTER SYSTEM</small>
        </div>
    </div>

    <div class="menu" id="navMenu">
        <a href="index.php" class="<?= $current_page=='index.php'?'active':'' ?>">Dashboard</a>
        <a href="add_customer.php" class="<?= $current_page=='add_customer.php'?'active':'' ?>">Register</a>
        <a href="warranty_list.php" class="<?= $current_page=='warranty_list.php'?'active':'' ?>">Warranty</a>
        <a href="collected.php" class="<?= $current_page=='collected.php'?'active':'' ?>">Collected</a>
        <a href="job_list.php" class="<?= $current_page=='job_list.php'?'active':'' ?>">Order</a>
        <a href="cashbook_view.php" class="<?= $current_page=='cashbook_view.php'?'active':'' ?>">Payment</a>
        <a href="report.php" class="<?= $current_page=='report.php'?'active':'' ?>">Report</a>
        <a href="stock.php" class="<?= $current_page=='stock.php'?'active':'' ?>">Stock</a>
        <a href="invoice_list.php" class="<?= $current_page=='invoice_list.php'?'active':'' ?>">Invoice</a>
        <a href="destroyed_items_view.php" class="<?= $current_page=='destroyed_items_view.php'?'active':'' ?>">Destroy Items</a>
    </div>

    <div style="display: flex; align-items: center; gap: 15px;">
        <button class="dark-toggle" onclick="toggleDarkMode()" style="background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.2); cursor:pointer; color: #ffffff; font-size: 20px; width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; transition: 0.3s;" title="Toggle Dark/Light Mode">
            <i class="ph-bold ph-sun" id="themeIcon"></i>
        </button>

        <div class="user-section" id="userMenuTrigger">
            <div class="user-info" style="text-align: right;">
                <span class="user-name-text"><?= $user_name ?></span>
            </div>
            <div class="profile-card"><?= $user_initial ?></div>
            <div class="profile-dropdown" id="userDropdown">
                <a href="profile_settings.php"><i class="ph-fill ph-gear"></i> System Settings</a>
                <a href="backup_db.php"><i class="ph-fill ph-database"></i> Database Backup</a>
                <a href="logout.php" style="color: #ef4444; font-weight: 700;"><i class="ph-bold ph-sign-out"></i> Log Out</a>
            </div>
        </div>
    </div>
</div>



<script>

(function() {
    const savedTheme = localStorage.getItem("darkMode");
    const themeIcon = document.getElementById("themeIcon");
    if (savedTheme === "enabled") {
        document.body.classList.add("dark-mode");
        if(themeIcon) themeIcon.className = "ph ph-moon-stars";
    }
})();

function toggleDarkMode() {
    const isDarkMode = document.body.classList.toggle("dark-mode");
    const themeIcon = document.getElementById("themeIcon");
    
    if (isDarkMode) {
        if(themeIcon) themeIcon.className = "ph ph-moon-stars";
    } else {
        if(themeIcon) themeIcon.className = "ph ph-sun";
    }
   
    localStorage.setItem("darkMode", isDarkMode ? "enabled" : "disabled");
}



document.getElementById('userMenuTrigger').addEventListener('click', function(e) {
    e.stopPropagation();
    document.getElementById('userDropdown').classList.toggle('active');
});

document.addEventListener('click', () => {
    document.getElementById('userDropdown').classList.remove('active');
});
</script>