<?php
// දැනට සිටින පිටුවේ නම ලබා ගැනීම
$current_page = basename($_SERVER['PHP_SELF']);

// ලොග් වී සිටින පරිශීලක නම සහ මුල් අකුර ලබා ගැනීම
$user_name = isset($_SESSION['username']) ? $_SESSION['username'] : 'User';
$user_initial = strtoupper(substr($user_name, 0, 1));
?>

<style>
    /* Navbar එකේ මූලික සැකසුම් */
    .topbar {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        z-index: 9999;
        background: linear-gradient(90deg, #043f2e, #065f46);
        color: white;
        padding: 15px 45px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        box-shadow: 0 4px 15px rgba(0,0,0,0.3);
        box-sizing: border-box;
    }

    @media print {
        .no-print { display: none !important; }
    }

    /* Logo සහ Brand කොටස */
    .brand-section {
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .brand { display: flex; flex-direction: column; }
    .brand strong { font-size: 22px; letter-spacing: 1.5px; color: #ffffff; line-height: 1.2; }
    .brand small { font-size: 10px; opacity: 0.8; color: #d1fae5; }

    /* Hamburger Menu Icon */
    .mobile-menu-btn {
        display: none; /* Desktop එකේදී සඟවයි */
        font-size: 26px;
        cursor: pointer;
        color: white;
        padding: 5px;
    }

    /* Desktop Menu */
    .menu {
        display: flex;
        gap: 12px; /* මෙනු අයිතම වැඩි නිසා පොඩ්ඩක් පරතරය අඩු කළා */
    }

    .menu a {
        color: #d1fae5;
        text-decoration: none;
        font-size: 14px;
        font-weight: 500;
        padding: 8px 5px;
        position: relative;
        transition: 0.3s;
        white-space: nowrap;
    }

    .menu a.active { color: #ffffff; }

    .menu a.active::after {
        content: "";
        position: absolute;
        left: 0;
        bottom: -5px;
        width: 100%;
        height: 3px;
        background: #22c55e;
    }

    /* User Profile Section */
    .user-section {
        display: flex;
        align-items: center;
        gap: 10px;
        position: relative;
        cursor: pointer;
        padding: 5px 12px;
        border-radius: 50px;
        transition: 0.3s;
        background: rgba(255, 255, 255, 0.05);
    }

    .user-section:hover { background: rgba(255, 255, 255, 0.15); }

    .profile-card {
        background: #22c55e;
        color: #064e3b;
        width: 36px;
        height: 36px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        font-size: 15px;
        border: 2px solid white;
    }

    .profile-dropdown {
        position: absolute;
        top: 55px;
        right: 0;
        background: white;
        min-width: 200px;
        border-radius: 12px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.4);
        display: none;
        overflow: hidden;
        z-index: 10000;
    }

    .profile-dropdown.active { display: block; animation: slideDown 0.2s ease-out; }

    @keyframes slideDown {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .profile-dropdown a {
        display: block;
        padding: 12px 20px;
        color: #333;
        text-decoration: none;
        font-size: 14px;
        border-bottom: 1px solid #f1f1f1;
    }

    .profile-dropdown a:hover { background: #f0fdf4; color: #065f46; }

    /* --- RESPONSIVE MOBILE CSS --- */
    @media (max-width: 1150px) { /* Responsive වෙන සීමාව පොඩ්ඩක් වැඩි කළා මෙනු එක වැඩි නිසා */
        .topbar { padding: 15px 20px; }
        
        .mobile-menu-btn { 
            display: block; 
        }

        .menu {
            display: none; 
            position: absolute;
            top: 100%;
            left: 0;
            width: 100%;
            background: #043f2e;
            flex-direction: column;
            gap: 0;
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
        }

        .menu.show { display: flex; }

        .menu a {
            padding: 15px 25px;
            border-bottom: 1px solid rgba(255,255,255,0.05);
            width: 100%;
        }

        .menu a.active { background: rgba(34, 197, 94, 0.2); border-left: 4px solid #22c55e; }
        .menu a.active::after { display: none; }

        .user-info { display: none; } 
    }
</style>

<div class="topbar no-print">
    <div class="brand-section">
        <div class="mobile-menu-btn" id="mobileMenuBtn">☰</div>
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
        <a href="invoice_list.php" class="<?= $current_page=='invoice_list.php'?'active':'' ?>">Invoice</a>
        <a href="cashbook_view.php" class="<?= $current_page=='cashbook_view.php'?'active':'' ?>">Payment</a>
        <a href="report.php" class="<?= $current_page=='report.php'?'active':'' ?>">Report</a>
        <a href="stock.php" class="<?= $current_page=='stock.php'?'active':'' ?>">Stock</a>
        <a href="destroyed_items_view.php" class="<?= $current_page=='destroyed_items_view.php'?'active':'' ?>">Destroy Items</a>
    </div>

    <div class="user-section" id="userMenuTrigger">
        <div class="user-info" style="text-align: right;">
            <span style="font-size: 10px; opacity: 0.8; display: block; text-transform: uppercase;">Admin</span>
            <span style="font-size: 13px; font-weight: 600;"><?= $user_name ?></span>
        </div>
        <div class="profile-card">
            <?= $user_initial ?>
        </div>
        
        <div class="profile-dropdown" id="userDropdown">
            <a href="profile_settings.php">⚙️ System Settings</a>
            <a href="backup_db.php">💾 Database Backup</a>
            <a href="logout.php" style="color: #dc2626; font-weight: 700;">🚪 Log Out</a>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        var mobileBtn = document.getElementById('mobileMenuBtn');
        var navMenu = document.getElementById('navMenu');
        var userTrigger = document.getElementById('userMenuTrigger');
        var userDropdown = document.getElementById('userDropdown');

        // Mobile Menu Toggle
        mobileBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            navMenu.classList.toggle('show');
            userDropdown.classList.remove('active'); 
        });

        // User Dropdown Toggle
        userTrigger.addEventListener('click', function(e) {
            e.stopPropagation();
            userDropdown.classList.toggle('active');
            navMenu.classList.remove('show');
        });

        // පිටත ක්ලික් කළ විට වසන්න
        document.addEventListener('click', function() {
            navMenu.classList.remove('show');
            userDropdown.classList.remove('active');
        });

        navMenu.addEventListener('click', function(e) { e.stopPropagation(); });
        userDropdown.addEventListener('click', function(e) { e.stopPropagation(); });
    });
</script>