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
/* ================= GLOBAL RESET / BASE ================= */
* { box-sizing: border-box; }

/* Push page content below the floating navbar */
body { padding-top: 90px; }

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
    border-color: rgba(255,255,255,0.06) !important;
}
body.dark-mode .menu a { color: #94a3b8 !important; }
body.dark-mode .menu a.active { color: #ffffff !important; background: rgba(34,197,94,0.15) !important; }
body.dark-mode .user-section { background: rgba(255,255,255,0.05) !important; }
body.dark-mode .profile-dropdown {
    background: #0f172a !important;
    border: 1px solid #334155;
}
body.dark-mode .profile-dropdown a {
    color: #e2e8f0 !important;
    border-bottom: 1px solid #1e293b;
}
body.dark-mode .mobile-menu { background: #0f172a !important; border: 1px solid #334155; }
body.dark-mode .mobile-menu a { color: #e2e8f0 !important; border-bottom: 1px solid #1e293b; }

/* ---------------- PROFESSIONAL FLOATING / CURVED NAVBAR ---------------- */
.topbar {
    position: fixed;
    top: 14px;
    left: 50%;
    transform: translateX(-50%);
    width: calc(100% - 40px);
    max-width: 1400px;
    z-index: 9999;
    background: linear-gradient(90deg, #043f2e, #065f46);
    color: white;
    padding: 12px 24px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    border-radius: 22px;
    border: 1px solid rgba(255,255,255,0.08);
    box-shadow: 0 12px 30px rgba(4,63,46,0.35), 0 4px 10px rgba(0,0,0,0.15);
    transition: all 0.3s ease;
}

.brand-section { display: flex; align-items: center; gap: 12px; flex-shrink: 0; }
.brand-logo {
    width: 40px; height: 40px; border-radius: 12px;
    background: linear-gradient(135deg, #22c55e, #16a34a);
    display: flex; align-items: center; justify-content: center;
    font-weight: 800; font-size: 16px; color: #043f2e;
    box-shadow: 0 4px 10px rgba(34,197,94,0.4);
}
.brand strong { font-size: 16px; letter-spacing: 0.5px; line-height: 1.1; display:block; }
.brand small { display:block; font-size:10px; opacity:0.75; letter-spacing: 1px; margin-top:2px; }

/* Center Nav Menu */
.menu {
    display: flex;
    gap: 4px;
    background: rgba(255,255,255,0.06);
    padding: 6px;
    border-radius: 16px;
    flex-wrap: nowrap;
}
.menu a {
    color: #d1fae5;
    text-decoration: none;
    font-size: 13.5px;
    font-weight: 600;
    padding: 9px 14px;
    border-radius: 12px;
    position: relative;
    transition: 0.25s ease;
    white-space: nowrap;
}
.menu a:hover { background: rgba(255,255,255,0.12); color: #ffffff; }
.menu a.active { color: #043f2e; background: #22c55e; box-shadow: 0 4px 12px rgba(34,197,94,0.4); }

/* Right side */
.right-section { display: flex; align-items: center; gap: 10px; flex-shrink: 0; }

.dark-toggle {
    background: rgba(255,255,255,0.08);
    border: none;
    cursor: pointer;
    font-size: 16px;
    width: 38px; height: 38px;
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    transition: 0.25s ease;
}
.dark-toggle:hover { background: rgba(255,255,255,0.18); transform: scale(1.05); }

.user-section {
    display: flex; align-items: center; gap: 10px;
    position: relative; cursor: pointer;
    padding: 6px 14px 6px 6px;
    border-radius: 50px;
    background: rgba(255,255,255,0.07);
    transition: 0.25s ease;
}
.user-section:hover { background: rgba(255,255,255,0.14); }
.profile-card {
    background: linear-gradient(135deg, #22c55e, #16a34a);
    color: #064e3b; width: 34px; height: 34px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-weight: 800; border: 2px solid white; font-size: 14px;
    flex-shrink: 0;
}
.user-info span { font-size: 13px; font-weight: 600; color:white; white-space: nowrap; }

.profile-dropdown {
    position: absolute; top: 55px; right: 0;
    background: white; min-width: 210px;
    border-radius: 14px;
    box-shadow: 0 15px 35px rgba(0,0,0,0.25);
    display: none; overflow: hidden; z-index: 10000;
}
.profile-dropdown.active { display: block; animation: slideDown 0.2s ease-out; }
.profile-dropdown a {
    display: flex; align-items: center; gap: 8px;
    padding: 13px 20px; color: #333; text-decoration: none;
    font-size: 13.5px; font-weight: 500;
    border-bottom: 1px solid #f1f1f1; transition: 0.2s;
}
.profile-dropdown a:hover { background: #f8fafc; padding-left: 24px; }

/* Hamburger (mobile only) */
.hamburger {
    display: none;
    width: 38px; height: 38px;
    border-radius: 50%;
    background: rgba(255,255,255,0.08);
    border: none; cursor: pointer;
    align-items: center; justify-content: center;
    flex-direction: column; gap: 4px;
}
.hamburger span { width: 18px; height: 2px; background: white; border-radius: 2px; transition: 0.3s; }
.hamburger.open span:nth-child(1) { transform: translateY(6px) rotate(45deg); }
.hamburger.open span:nth-child(2) { opacity: 0; }
.hamburger.open span:nth-child(3) { transform: translateY(-6px) rotate(-45deg); }

/* Mobile dropdown menu */
.mobile-menu {
    display: none;
    position: fixed;
    top: 84px; left: 20px; right: 20px;
    background: white;
    border-radius: 18px;
    box-shadow: 0 15px 35px rgba(0,0,0,0.25);
    z-index: 9998;
    overflow: hidden;
    max-height: 0;
    transition: max-height 0.3s ease;
}
.mobile-menu.active { display: block; max-height: 70vh; overflow-y: auto; }
.mobile-menu a {
    display: block; padding: 14px 22px; color: #333;
    text-decoration: none; font-size: 14px; font-weight: 600;
    border-bottom: 1px solid #f1f1f1;
}
.mobile-menu a.active { color: #16a34a; background: #f0fdf4; }

@keyframes slideDown { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }

/* ---------------- RESPONSIVE BREAKPOINTS ---------------- */
@media (max-width: 1100px) {
    .menu { display: none; }
    .hamburger { display: flex; }
}
@media (max-width: 640px) {
    body { padding-top: 78px; }
    .topbar { top: 10px; width: calc(100% - 20px); padding: 10px 14px; border-radius: 18px; }
    .brand small { display: none; }
    .user-info { display: none; }
    .profile-card { width: 32px; height: 32px; }
    .chat-box { width: calc(100% - 30px); right: 15px; left: 15px; bottom: 90px; height: 65vh; }
    .chat-trigger { width: 52px; height: 52px; font-size: 24px; bottom: 18px; right: 18px; }
}

/* Assistant / Chatbox Styles */
.chat-trigger {
    position: fixed; bottom: 25px; right: 25px;
    background: linear-gradient(135deg, #0f766e, #0d9488);
    color: white; width: 60px; height: 60px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 28px; cursor: pointer; z-index: 10000;
    box-shadow: 0 10px 25px rgba(15,118,110,0.45);
    transition: 0.3s ease;
}
.chat-trigger:hover { transform: scale(1.08) rotate(8deg); }
.chat-box {
    display: none; width: 360px; height: 520px;
    position: fixed; bottom: 100px; right: 25px;
    background: #fff; border-radius: 20px;
    box-shadow: 0 20px 45px rgba(0,0,0,0.3);
    flex-direction: column; z-index: 10001; overflow: hidden;
}
body.dark-mode .chat-box { background: #0f172a; border: 1px solid #334155; }
body.dark-mode .chat-body { background: #020617; }
</style>

<div class="topbar no-print">
    <div class="brand-section">
        <div class="brand-logo">M9</div>
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

    <div class="right-section">
        <button class="dark-toggle" onclick="toggleDarkMode()" title="Toggle Dark/Light Mode">🌙</button>

        <div class="user-section" id="userMenuTrigger">
            <div class="profile-card"><?= $user_initial ?></div>
            <div class="user-info">
                <span><?= htmlspecialchars($user_name) ?></span>
            </div>
            <div class="profile-dropdown" id="userDropdown">
                <a href="profile_settings.php">⚙️ System Settings</a>
                <a href="backup_db.php">💾 Database Backup</a>
                <a href="logout.php" style="color: #dc2626; font-weight: 700;">🚪 Log Out</a>
            </div>
        </div>

        <button class="hamburger" id="hamburgerBtn" onclick="toggleMobileMenu()" aria-label="Menu">
            <span></span><span></span><span></span>
        </button>
    </div>
</div>

<!-- Mobile dropdown nav -->
<div class="mobile-menu" id="mobileMenu">
    <a href="index.php" class="<?= $current_page=='index.php'?'active':'' ?>"> Dashboard</a>
    <a href="add_customer.php" class="<?= $current_page=='add_customer.php'?'active':'' ?>"> Register</a>
    <a href="warranty_list.php" class="<?= $current_page=='warranty_list.php'?'active':'' ?>"> Warranty</a>
    <a href="collected.php" class="<?= $current_page=='collected.php'?'active':'' ?>"> Collected</a>
    <a href="job_list.php" class="<?= $current_page=='job_list.php'?'active':'' ?>"> Order</a>
    <a href="cashbook_view.php" class="<?= $current_page=='cashbook_view.php'?'active':'' ?>"> Payment</a>
    <a href="report.php" class="<?= $current_page=='report.php'?'active':'' ?>"> Report</a>
    <a href="stock.php" class="<?= $current_page=='stock.php'?'active':'' ?>"> Stock</a>
    <a href="invoice_list.php" class="<?= $current_page=='invoice_list.php'?'active':'' ?>"> Invoice</a>
    <a href="destroyed_items_view.php" class="<?= $current_page=='destroyed_items_view.php'?'active':'' ?>"> Destroy Items</a>
</div>

<div class="chat-trigger" onclick="toggleChat()">🤖</div>
<div class="chat-box" id="globalChatBox">
    <div class="chat-header" style="background:#0f766e; color:white; padding:16px; display:flex; justify-content:space-between; align-items:center;">
        <span>System Assistant</span>
        <span onclick="toggleChat()" style="cursor:pointer; font-size:20px;">×</span>
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
    document.getElementById('mobileMenu').classList.remove('active');
    document.getElementById('hamburgerBtn').classList.remove('open');
});

function toggleMobileMenu() {
    event.stopPropagation();
    document.getElementById('mobileMenu').classList.toggle('active');
    document.getElementById('hamburgerBtn').classList.toggle('open');
}

// Close mobile menu automatically on resize to desktop width
window.addEventListener('resize', function() {
    if (window.innerWidth > 1100) {
        document.getElementById('mobileMenu').classList.remove('active');
        document.getElementById('hamburgerBtn').classList.remove('open');
    }
});
</script>