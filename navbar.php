<?php
// දැනට සිටින පිටුවේ නම ලබා ගැනීම
$current_page = basename($_SERVER['PHP_SELF']);

// දැනට ලොග් වී සිටින පරිශීලකයාගේ නමේ මුල් අකුර ලබා ගැනීම
// Session එකේ username එක නැතිනම් 'U' ලෙස පෙන්වයි
$user_name = isset($_SESSION['username']) ? $_SESSION['username'] : 'User';
$user_initial = strtoupper(substr($user_name, 0, 1));
?>

<style>
    /* Main Topbar */
    .topbar {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        z-index: 9999;
        background: linear-gradient(90deg, #043f2e, #065f46);
        color: white;
        padding: 12px 40px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        box-shadow: 0 2px 10px rgba(0,0,0,0.2);
    }

    /* Brand Logo Section */
    .brand {
        display: flex;
        flex-direction: column;
        line-height: 1.2;
        min-width: 150px;
    }

    .brand strong {
        font-size: 18px;
        letter-spacing: 1px;
    }

    .brand small {
        font-size: 11px;
        opacity: 0.85;
    }

    /* Menu Links */
    .menu {
        display: flex;
        gap: 25px;
    }

    .menu a {
        color: #d1fae5;
        text-decoration: none;
        font-size: 14px;
        padding: 6px 2px;
        position: relative;
        transition: 0.3s;
    }

    .menu a::after {
        content: "";
        position: absolute;
        left: 0;
        bottom: -4px;
        width: 0;
        height: 2px;
        background: #22c55e;
        transition: 0.3s;
    }

    .menu a:hover::after,
    .menu a.active::after {
        width: 100%;
    }

    .menu a:hover,
    .menu a.active {
        color: #ffffff;
    }

    /* User Profile Section */
    .user-section {
        display: flex;
        align-items: center;
        gap: 12px;
        position: relative;
    }

    .user-info {
        text-align: right;
        display: flex;
        flex-direction: column;
    }

    .user-info .welcome {
        font-size: 10px;
        opacity: 0.7;
        text-transform: uppercase;
    }

    .user-info .name {
        font-size: 13px;
        font-weight: 500;
    }

    .profile-card {
        background: #22c55e;
        color: #064e3b;
        width: 38px;
        height: 38px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        font-size: 16px;
        cursor: pointer;
        border: 2px solid rgba(255,255,255,0.2);
        transition: 0.3s;
    }

    /* Dropdown Menu */
    .profile-dropdown {
        position: absolute;
        top: 50px;
        right: 0;
        background: white;
        min-width: 160px;
        border-radius: 8px;
        box-shadow: 0 10px 25px rgba(0,0,0,0.2);
        display: none;
        overflow: hidden;
        z-index: 10000;
    }

    .profile-dropdown a {
        display: block;
        padding: 12px 18px;
        color: #333;
        text-decoration: none;
        font-size: 13px;
        transition: 0.2s;
        border-bottom: 1px solid #f1f1f1;
    }

    .profile-dropdown a:hover {
        background: #f0fdf4;
        color: #065f46;
    }

    .user-section:hover .profile-dropdown {
        display: block;
    }

    /* Responsive adjustments */
    @media (max-width: 1100px) {
        .menu { gap: 15px; }
        .menu a { font-size: 13px; }
        .topbar { padding: 12px 20px; }
    }
</style>

<div class="topbar">
    <div class="brand">
        <strong>MULTI 9</strong>
        <small>COMPUTER SYSTEM</small>
    </div>

    <div class="menu">
        <a href="index.php" class="<?= $current_page=='index.php'?'active':'' ?>">Dashboard</a>
        <a href="add_customer.php" class="<?= $current_page=='add_customer.php'?'active':'' ?>">Register</a>
        <a href="warranty_list.php" class="<?= $current_page=='warranty_list.php'?'active':'' ?>">Warranty</a>
        <a href="duration.php" class="<?= $current_page=='duration.php'?'active':'' ?>">Duration</a>
        <a href="collected.php" class="<?= $current_page=='collected.php'?'active':'' ?>">Collected</a>
        <a href="job_list.php" class="<?= $current_page=='job_list.php'?'active':'' ?>">Order</a>
        <a href="cashbook_view.php" class="<?= $current_page=='cashbook_view.php'?'active':'' ?>">Payment</a>
        <a href="report.php" class="<?= $current_page=='report.php'?'active':'' ?>">Report</a>
        <a href="stock.php" class="<?= $current_page=='stock.php'?'active':'' ?>">Stock</a>
        <a href="destroyed_items_view.php" class="<?= $current_page=='destroyed_items_view.php'?'active':'' ?>">Destroy Items</a>
    </div>

    <div class="user-section">
        <div class="user-info">
            <span class="welcome">Welcome</span>
            <span class="name"><?= $user_name ?></span>
        </div>
        <div class="profile-card">
            <?= $user_initial ?>
        </div>
        
        <div class="profile-dropdown">
            <a href="profile_settings.php">⚙️ System Settings</a>
            <a href="backup_db.php">💾 Database Backup</a>
            <a href="logout.php" style="color: #dc2626; font-weight: 600;">🚪 Log Out</a>
        </div>
    </div>
</div>