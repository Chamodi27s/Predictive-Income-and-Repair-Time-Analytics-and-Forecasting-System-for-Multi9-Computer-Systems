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

<style>
/* ---------------- GLOBAL DARK MODE (System-wide) ---------------- */
body.dark-mode {
    background: linear-gradient(135deg,#020617,#0f172a) !important;
    color:#e2e8f0 !important;
}

/* Glass Effect Cards - Dark Mode එකේදී පමණක් වැඩ කරයි */
body.dark-mode .card, 
body.dark-mode .dashboard-card, 
body.dark-mode .stat-card {
    background: rgba(30,41,59,0.55) !important;
    backdrop-filter: blur(14px);
    -webkit-backdrop-filter: blur(14px);
    border: 1px solid rgba(255,255,255,0.08) !important;
    border-radius: 20px;
    box-shadow: 0 10px 25px rgba(0,0,0,0.5), inset 0 1px 0 rgba(255,255,255,0.05);
    transition: all 0.3s ease;
}

/* Navbar Changes for Dark Mode */
body.dark-mode .topbar { 
    background: linear-gradient(90deg,#020617,#0f172a) !important; 
}
body.dark-mode .profile-dropdown { 
    background: #0f172a !important; 
    border: 1px solid #334155;
}
body.dark-mode .profile-dropdown a { 
    color: #e2e8f0 !important; 
    border-bottom: 1px solid #1e293b;
}

/* ---------------- DEFAULT NAVBAR STYLES ---------------- */
.topbar {
    position: fixed; top: 0; left: 0; width: 100%; z-index: 9999;
    background: linear-gradient(90deg, #043f2e, #065f46);
    color: white; padding: 15px 45px; display: flex; align-items: center; justify-content: space-between;
    box-shadow: 0 4px 15px rgba(0,0,0,0.3); box-sizing: border-box;
}

.menu { display: flex; gap: 15px; }
.menu a { color: #d1fae5; text-decoration: none; font-size: 14px; font-weight: 500; padding: 8px 5px; position: relative; transition: 0.3s; }
.menu a.active { color: #ffffff; }
.menu a.active::after { content: ""; position: absolute; left: 0; bottom: -5px; width: 100%; height: 3px; background: #22c55e; }

.user-section { display: flex; align-items: center; gap: 10px; position: relative; cursor: pointer; padding: 5px 12px; border-radius: 50px; background: rgba(255, 255, 255, 0.05); transition: 0.3s; }
.profile-card { background: #22c55e; color: #064e3b; width: 36px; height: 36px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; border: 2px solid white; }

.profile-dropdown { position: absolute; top: 55px; right: 0; background: white; min-width: 200px; border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.4); display: none; overflow: hidden; z-index: 10000; }
.profile-dropdown.active { display: block; animation: slideDown 0.2s ease-out; }
.profile-dropdown a { display: block; padding: 12px 20px; color: #333; text-decoration: none; font-size: 14px; border-bottom: 1px solid #f1f1f1; }

/* Assistant / Chatbox Styles */
.chat-trigger { position: fixed; bottom: 25px; right: 25px; background: #0f766e; color: white; width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 28px; cursor: pointer; z-index: 10000; transition: 0.3s; }
.chat-box { display: none; width: 360px; height: 520px; position: fixed; bottom: 100px; right: 25px; background: #fff; border-radius: 18px; box-shadow: 0 10px 30px rgba(0,0,0,0.25); flex-direction: column; z-index: 10001; overflow: hidden; }
body.dark-mode .chat-box { background: #0f172a; border: 1px solid #334155; }
body.dark-mode .chat-body { background: #020617; }

@keyframes slideDown { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }
</style>

<div class="topbar no-print">
    <div class="brand-section">
        <div class="brand">
            <strong>MULTI 9</strong>
            <small style="display:block; font-size:10px; opacity:0.8;">COMPUTER SYSTEM</small>
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
        <a href="destroyed_items_view.php" class="<?= $current_page=='destroyed_items_view.php'?'active':'' ?>">Destroy Items</a>
    </div>

    <div class="user-section" id="userMenuTrigger">
        <button class="dark-toggle" onclick="toggleDarkMode()" style="background:none; border:none; cursor:pointer; font-size:18px;" title="Toggle Dark/Light Mode">🌙</button>
        <div class="user-info" style="text-align: right;">
            <span style="font-size: 13px; font-weight: 600; color:white;"><?= $user_name ?></span>
        </div>
        <div class="profile-card"><?= $user_initial ?></div>
        <div class="profile-dropdown" id="userDropdown">
            <a href="profile_settings.php">⚙️ System Settings</a>
            <a href="backup_db.php">💾 Database Backup</a>
            <a href="logout.php" style="color: #dc2626; font-weight: 700;">🚪 Log Out</a>
        </div>
    </div>
</div>

<div class="chat-trigger" onclick="toggleChat()">🤖</div>
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
// පිටුව Load වන විට තිබෙන Theme එක පරීක්ෂා කර ක්‍රියාත්මක කිරීම
(function() {
    const savedTheme = localStorage.getItem("darkMode");
    if (savedTheme === "enabled") {
        document.body.classList.add("dark-mode");
    }
})();

function toggleDarkMode() {
    const isDarkMode = document.body.classList.toggle("dark-mode");
    // තේරීම localStorage වල save කිරීම
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