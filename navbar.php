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
.topbar {
    position: fixed; top: 0; left: 0; width: 100%; height: var(--nav-height); z-index: 9999;
    background: rgba(4, 63, 46, 0.9);
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
    border-bottom: 1px solid rgba(255,255,255,0.1);
    color: white; padding: 0 45px; display: flex; align-items: center; justify-content: space-between;
    box-shadow: 0 4px 30px rgba(0, 0, 0, 0.1); box-sizing: border-box;
}

body.dark-mode .topbar { 
    background: rgba(2, 6, 23, 0.85) !important;
    border-bottom: 1px solid rgba(255,255,255,0.05);
}

.brand-section { display: flex; align-items: center; gap: 10px; }
.brand { display: flex; flex-direction: column; justify-content: center; }
.brand strong { font-size: 20px; letter-spacing: 1px; color: #fff; font-weight: 700; }
.brand small { font-size: 10px; opacity: 0.7; letter-spacing: 2px; color: #a7f3d0; }

.menu { display: flex; gap: 20px; align-items: center; }
.menu a { color: #d1fae5; text-decoration: none; font-size: 14px; font-weight: 500; padding: 10px 0; position: relative; transition: var(--transition); display: flex; align-items: center; gap: 6px; }
.menu a:hover { color: #ffffff; }
.menu a.active { color: #14b8a6; font-weight: 600; }
.menu a.active::after { content: ""; position: absolute; left: 0; bottom: 0; width: 100%; height: 3px; background: #14b8a6; border-radius: 3px 3px 0 0; }
.menu i.ph { font-size: 18px; }

.user-section { display: flex; align-items: center; gap: 15px; position: relative; cursor: pointer; padding: 6px 16px; border-radius: 50px; background: rgba(255, 255, 255, 0.1); border: 1px solid rgba(255, 255, 255, 0.2); transition: var(--transition); }
.user-section:hover { background: rgba(255, 255, 255, 0.15); }

.profile-card { background: linear-gradient(135deg, #14b8a6, #0f766e); color: white; width: 36px; height: 36px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; border: 2px solid rgba(255,255,255,0.5); font-size: 14px; }

.profile-dropdown { position: absolute; top: calc(100% + 10px); right: 0; background: var(--light-surface); min-width: 220px; border-radius: 16px; box-shadow: var(--card-shadow); border: 1px solid var(--border-light); display: none; overflow: hidden; z-index: 10000; }
body.dark-mode .profile-dropdown { background: var(--dark-surface); border-color: #334155; }
.profile-dropdown.active { display: block; animation: slideDown 0.2s ease-out; }
.profile-dropdown a { display: flex; align-items: center; gap: 10px; padding: 14px 20px; color: var(--text-dark); text-decoration: none; font-size: 14px; border-bottom: 1px solid var(--border-light); transition: var(--transition); }
.profile-dropdown a:hover { background: var(--light-bg); padding-left: 25px; }
body.dark-mode .profile-dropdown a { color: #f1f5f9; border-color: #1e293b; }
body.dark-mode .profile-dropdown a:hover { background: #1e293b; }
.profile-dropdown a i.ph { font-size: 18px; color: var(--primary-green); }

/* Assistant / Chatbox Styles */
.chat-trigger { position: fixed; bottom: 30px; right: 30px; background: linear-gradient(135deg, var(--primary-green), var(--accent-green)); color: white; width: 64px; height: 64px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 32px; cursor: pointer; z-index: 10000; transition: var(--transition); box-shadow: 0 10px 25px rgba(20, 184, 166, 0.4); border: 2px solid rgba(255,255,255,0.2); }
.chat-trigger:hover { transform: scale(1.05); }
.chat-box { display: none; width: 380px; height: 550px; position: fixed; bottom: 110px; right: 30px; background: var(--light-surface); border-radius: 20px; box-shadow: 0 15px 40px rgba(0,0,0,0.2); flex-direction: column; z-index: 10001; overflow: hidden; border: 1px solid var(--border-light); }
body.dark-mode .chat-box { background: var(--dark-surface); border-color: #334155; }
body.dark-mode .chat-body { background: var(--dark-bg); }

@keyframes slideDown { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }
</style>

<div class="topbar no-print">
    <div class="brand-section">
        <i class="ph ph-cpu" style="font-size: 32px; color: #14b8a6;"></i>
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

    <div class="user-section" id="userMenuTrigger">
        <button class="dark-toggle" onclick="toggleDarkMode()" style="background:none; border:none; cursor:pointer; color: #d1fae5; font-size: 22px; margin-right: 5px;" title="Toggle Dark/Light Mode">
            <i class="ph ph-moon-stars"></i>
        </button>
        <div class="user-info" style="text-align: right;">
            <span style="font-size: 13px; font-weight: 600; color:white; display: block;"><?= $user_name ?></span>
        </div>
        <div class="profile-card"><?= $user_initial ?></div>
        <div class="profile-dropdown" id="userDropdown">
            <a href="profile_settings.php"><i class="ph ph-gear"></i> System Settings</a>
            <a href="backup_db.php"><i class="ph ph-database"></i> Database Backup</a>
            <a href="logout.php" style="color: #ef4444; font-weight: 600;"><i class="ph ph-sign-out"></i> Log Out</a>
        </div>
    </div>
</div>

<div class="chat-trigger" onclick="toggleChat()"><i class="ph ph-robot"></i></div>
<div class="chat-box" id="globalChatBox">
    <div class="chat-header" style="background:#0f766e; color:white; padding:16px; display:flex; justify-content:space-between;">
        <span>System Assistant</span>
        <span onclick="toggleChat()" style="cursor:pointer;">×</span>
    </div>
    <div class="chat-body" id="chatBody" style="flex:1; padding:15px; overflow-y:auto; background:#f9fafb;">
        <div class="msg bot" style="background:#e5e7eb; padding:10px; border-radius:10px; margin-bottom:10px; font-size:14px; color:#333;">Hello 👋 I can help you with system tasks.</div>
    </div>
    <div class="chat-input" style="padding:10px; display:flex; border-top:1px solid #eee;">
        <input type="text" id="chatMsg" placeholder="Type..." style="flex:1; border:1px solid #ddd; border-radius:5px; padding:8px;">
        <button style="background:#0f766e; color:white; border:none; padding:8px 12px; border-radius:5px; margin-left:5px;">Send</button>
    </div>
</div>

<script>

(function() {
    const savedTheme = localStorage.getItem("darkMode");
    if (savedTheme === "enabled") {
        document.body.classList.add("dark-mode");
    }
})();

function toggleDarkMode() {
    const isDarkMode = document.body.classList.toggle("dark-mode");
   
    localStorage.setItem("darkMode", isDarkMode ? "enabled" : "disabled");
}

function toggleChat() {
    const chat = document.getElementById("globalChatBox");
    chat.style.display = (chat.style.display === "flex") ? "none" : "flex";
}

document.getElementById('userMenuTrigger').addEventListener('click', function(e) {
    e.stopPropagation();
    document.getElementById('userDropdown').classList.toggle('active');
});

document.addEventListener('click', () => {
    document.getElementById('userDropdown').classList.remove('active');
});
</script>