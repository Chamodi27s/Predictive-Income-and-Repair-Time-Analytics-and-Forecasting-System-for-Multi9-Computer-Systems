<?php
// දැනට සිටින පිටුවේ නම ලබා ගැනීම
$current_page = basename($_SERVER['PHP_SELF']);

// ලොග් වී සිටින පරිශීලක නම සහ මුල් අකුර ලබා ගැනීම
$user_name = isset($_SESSION['username']) ? $_SESSION['username'] : 'User';
$user_initial = strtoupper(substr($user_name, 0, 1));
?>

<style>
    /* Navbar එකේ මහත සහ පසුබිම් වර්ණ */
    .topbar {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        z-index: 9999;
        background: linear-gradient(90deg, #043f2e, #065f46);
        color: white;
        padding: 22px 45px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        box-shadow: 0 4px 15px rgba(0,0,0,0.3);
        box-sizing: border-box;
    }

    /* --- මුද්‍රණයේදී (Print) Navbar එක ඉවත් කිරීමට මෙම CSS එක එකතු කරන ලදී --- */
    @media print {
        .no-print {
            display: none !important;
        }
    }

    .brand strong {
        font-size: 24px;
        letter-spacing: 1.5px;
        color: #ffffff;
    }

    .brand small {
        font-size: 12px;
        opacity: 0.9;
        color: #d1fae5;
    }

    .menu {
        display: flex;
        gap: 22px;
    }

    .menu a {
        color: #d1fae5;
        text-decoration: none;
        font-size: 15px;
        font-weight: 500;
        padding: 8px 5px;
        position: relative;
        transition: 0.3s;
    }

    .menu a.active {
        color: #ffffff;
    }

    .menu a.active::after {
        content: "";
        position: absolute;
        left: 0;
        bottom: -5px;
        width: 100%;
        height: 3px;
        background: #22c55e;
    }

    .user-section {
        display: flex;
        align-items: center;
        gap: 15px;
        position: relative;
        cursor: pointer;
        padding: 8px 15px;
        border-radius: 50px;
        transition: 0.3s;
        background: rgba(255, 255, 255, 0.05);
    }

    .user-section:hover {
        background: rgba(255, 255, 255, 0.15);
    }

    .profile-card {
        background: #22c55e;
        color: #064e3b;
        width: 46px;
        height: 46px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        font-size: 18px;
        border: 2px solid white;
    }

    .profile-dropdown {
        position: absolute;
        top: 80px;
        right: 0;
        background: white;
        min-width: 200px;
        border-radius: 12px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.4);
        display: none;
        overflow: hidden;
        z-index: 10000;
    }

    .profile-dropdown.active {
        display: block;
        animation: slideDown 0.2s ease-out;
    }

    @keyframes slideDown {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .profile-dropdown a {
        display: block;
        padding: 15px 22px;
        color: #333;
        text-decoration: none;
        font-size: 14px;
        border-bottom: 1px solid #f1f1f1;
        transition: 0.2s;
    }

    .profile-dropdown a:hover {
        background: #f0fdf4;
        color: #065f46;
        padding-left: 28px;
    }

    @media (max-width: 1200px) {
        .menu { gap: 12px; }
        .menu a { font-size: 13px; }
        .topbar { padding: 20px 20px; }
    }
</style>

<div class="topbar no-print">
    <div class="brand">
        <strong>MULTI 9</strong>
        <small>COMPUTER SYSTEM</small>
    </div>

    <div class="menu">
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
        <div class="user-info" style="text-align: right;">
            <span style="font-size: 10px; opacity: 0.8; display: block; text-transform: uppercase;">User Account</span>
            <span style="font-size: 14px; font-weight: 600;"><?= $user_name ?></span>
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
        var trigger = document.getElementById('userMenuTrigger');
        var dropdown = document.getElementById('userDropdown');

        trigger.addEventListener('click', function(e) {
            e.stopPropagation();
            dropdown.classList.toggle('active');
        });

        document.addEventListener('click', function() {
            dropdown.classList.remove('active');
        });
        
        dropdown.addEventListener('click', function(e) {
            e.stopPropagation();
        });
    });
</script>