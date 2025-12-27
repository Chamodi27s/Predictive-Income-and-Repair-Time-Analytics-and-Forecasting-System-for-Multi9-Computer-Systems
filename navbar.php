<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>

<style>
.topbar {
    background: linear-gradient(90deg, #043f2e, #065f46);
    color: white;
    padding: 18px 50px;          /* ⬅ palal una */
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.brand {
    display: flex;
    flex-direction: column;
    line-height: 1.2;
}

.brand strong {
    font-size: 18px;
    letter-spacing: 1px;
}

.brand small {
    font-size: 11px;
    opacity: 0.85;
}

.menu {
    display: flex;
    gap: 34px;                  /* ⬅ menu items athara ida */
}

.menu a {
    color: #d1fae5;
    text-decoration: none;
    font-size: 15px;            /* ⬅ loku una */
    padding: 6px 2px;
    position: relative;
}

.menu a::after {
    content: "";
    position: absolute;
    left: 0;
    bottom: -6px;
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

.profile {
    background: #22c55e;
    color: #064e3b;
    width: 42px;                /* ⬅ loku una */
    height: 42px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 16px;
}
</style>

<div class="topbar">
    <div class="brand">
        <strong>MULTI 9</strong>
        <small>COMPUTER SYSTEM</small>
    </div>

    <div class="menu">
        <a href="index.php" class="<?= $current_page=='index.php'?'active':'' ?>">Dashboard</a>
        <a href="register.php">Register</a>
        <a href="job_details.php">Jobsheet</a>
        <a href="cashbook_view.php">Collected</a>
        <a href="job_list.php">Order</a>
        <a href="payment.php">Payment</a>
        <a href="report.php">Report</a>
        <a href="stock.php">Stock</a>
        <a href="setting.php">Setting</a>
    </div>

    <div class="profile">V</div>
</div>
