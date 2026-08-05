<?php
include 'db_config.php';
session_start();

// --- 1. Settings Update Logic ---
if (isset($_POST['update_settings'])) {
    // Business Info
    $shop_name = $conn->real_escape_string($_POST['shop_name']);
    $shop_address = $conn->real_escape_string($_POST['shop_address']);
    $shop_phone = $conn->real_escape_string($_POST['shop_phone']);
    $shop_email = $conn->real_escape_string($_POST['shop_email']);
    $currency = $conn->real_escape_string($_POST['currency']);

    // Job & Invoice Settings
    $job_prefix = $conn->real_escape_string($_POST['job_prefix']);
    $next_job_no = intval($_POST['next_job_no']);
    $invoice_prefix = $conn->real_escape_string($_POST['invoice_prefix']);
    $next_invoice_no = intval($_POST['next_invoice_no']);
    $invoice_terms = $conn->real_escape_string($_POST['invoice_terms']);

    // Policies
    $storage_limit = intval($_POST['storage_limit']);
    $monthly_fee = floatval($_POST['monthly_fee']);
    $disposal_limit = intval($_POST['disposal_limit']);

    // SMS Gateway
    $sms_key = $conn->real_escape_string($_POST['sms_api_key']);

    $sql = "UPDATE system_settings SET 
            shop_name='$shop_name', shop_address='$shop_address', shop_phone='$shop_phone', 
            shop_email='$shop_email', currency='$currency', job_prefix='$job_prefix', 
            next_job_no='$next_job_no', invoice_prefix='$invoice_prefix', 
            next_invoice_no='$next_invoice_no', invoice_terms='$invoice_terms',
            storage_limit='$storage_limit', monthly_fee='$monthly_fee', 
            disposal_limit='$disposal_limit', sms_api_key='$sms_key' 
            WHERE id=1";

    if ($conn->query($sql)) {
        $_SESSION['msg'] = "All settings updated successfully!";
        $_SESSION['msg_type'] = "success";
    } else {
        $_SESSION['msg'] = "Error updating settings: " . $conn->error;
        $_SESSION['msg_type'] = "danger";
    }
    header("Location: profile_settings.php");
    exit();
}

// --- 2. Password Update Logic ---
if (isset($_POST['update_password'])) {
    $new_pass = $_POST['new_password'];
    $confirm_pass = $_POST['confirm_password'];

    if(!empty($new_pass) && $new_pass === $confirm_pass) {
        $sql = "UPDATE system_settings SET admin_password='$new_pass' WHERE id=1";
        $conn->query($sql);
        $_SESSION['msg'] = "Password changed successfully!";
        $_SESSION['msg_type'] = "success";
    } else {
        $_SESSION['msg'] = "Passwords do not match!";
        $_SESSION['msg_type'] = "danger";
    }
    header("Location: admin_settings.php");
    exit();
}

// Fetch Data
$res = $conn->query("SELECT * FROM system_settings WHERE id=1");
$st = $res->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Settings | Multi9</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="CSS/global.css">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <style>
        body { background-color: var(--bg-main); font-family: 'Inter', sans-serif; color: var(--text-main); }
        .main-container { max-width: 1100px; margin: 50px auto; }
        
        .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; }
        .btn-back { background: var(--light-surface); color: var(--text-dark); border-radius: 8px; padding: 10px 18px; text-decoration: none; font-weight: 600; box-shadow: var(--card-shadow); transition: var(--transition); border: 1px solid var(--border-light); display: flex; align-items: center; gap: 8px; }
        .btn-back:hover { background: var(--primary-green); color: #fff; border-color: var(--primary-green); }

        .nav-tabs { border: none; gap: 5px; }
        .nav-link { color: var(--text-muted); border: none !important; padding: 12px 20px; font-weight: 600; border-radius: 10px 10px 0 0 !important; background: var(--light-bg); transition: var(--transition); display: flex; align-items: center; gap: 8px; }
        .nav-link.active { background: var(--light-surface) !important; color: var(--primary-green-dark) !important; box-shadow: 0 -3px 10px rgba(0,0,0,0.05); border-top: 3px solid var(--primary-green) !important; }
        .nav-link:hover:not(.active) { background: var(--border-light); color: var(--text-dark); }
        
        .settings-card { background: var(--light-surface); border: none; border-radius: 0 15px 15px 15px; box-shadow: var(--card-shadow); padding: 35px; border: 1px solid var(--border-light); }
        .section-title { font-size: 1.15rem; font-weight: 700; color: var(--text-dark); margin-bottom: 20px; padding-bottom: 10px; border-bottom: 2px solid var(--border-light); display: flex; align-items: center; gap: 10px; }
        .section-title i { color: var(--primary-green); }
        
        .form-label { font-weight: 600; color: var(--text-muted); font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.5px; }
        .form-control { border-radius: 8px; padding: 10px; border: 1px solid var(--border-light); background: var(--light-bg); color: var(--text-dark); transition: var(--transition); }
        .form-control:focus { border-color: var(--primary-green); box-shadow: 0 0 0 3px rgba(20, 184, 166, 0.15); }

        .btn-save-main { background: linear-gradient(135deg, var(--primary-green) 0%, var(--accent-green) 100%); border: none; padding: 15px; border-radius: 10px; font-weight: 700; font-size: 1.1rem; width: 100%; transition: var(--transition); margin-top: 20px; color: white; display: flex; align-items: center; justify-content: center; gap: 8px; }
        .btn-save-main:hover { background: var(--primary-green-dark); transform: translateY(-1px); box-shadow: 0 5px 15px rgba(20, 184, 166, 0.3); color: white; }
        
        .btn-password { background: #ef4444; color: white; border: none; padding: 10px 20px; border-radius: 8px; font-weight: 600; display: flex; align-items: center; gap: 5px; }
        .btn-password:hover { background: #dc2626; color: white; }
        
        /* Dark Mode support for Bootstrap elements */
        body.dark-mode { background-color: var(--bg-main); color: var(--text-main); }
        body.dark-mode .nav-link { background: var(--dark-bg); color: #94a3b8; }
        body.dark-mode .nav-link.active { background: var(--dark-surface) !important; color: var(--accent-green) !important; }
        body.dark-mode .settings-card { background: var(--dark-surface); border-color: #334155; }
        body.dark-mode .section-title { color: #f1f5f9; border-bottom-color: #334155; }
        body.dark-mode .form-label { color: #cbd5e1; }
        body.dark-mode .form-control { background: #0f172a; border-color: #334155; color: #f1f5f9; }
        body.dark-mode .bg-light-subtle { background-color: rgba(30, 41, 59, 0.5) !important; border-color: #334155 !important; }
        body.dark-mode .text-dark { color: #f1f5f9 !important; }
    </style>
</head>
<body id="settingsBody">

<div class="container main-container">
    <div class="page-header">
        <div>
            <h2 class="fw-bold mb-0" style="color: var(--primary-green); display: flex; align-items: center; gap: 10px;"><i class="ph-fill ph-gear"></i> System Settings</h2>
            <p class="text-muted mb-0">Manage and configure your system global preferences</p>
        </div>
        <a href="index.php" class="btn-back"><i class="ph ph-house"></i> Dashboard</a>
    </div>

    <?php if(isset($_SESSION['msg'])): ?>
        <div class="alert alert-<?= $_SESSION['msg_type'] ?> alert-dismissible fade show border-0 shadow-sm mb-4" style="border-radius: 10px;">
            <i class="fas <?= $_SESSION['msg_type'] == 'success' ? 'fa-check-circle' : 'fa-exclamation-circle' ?> me-2"></i>
            <?= $_SESSION['msg']; unset($_SESSION['msg']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <ul class="nav nav-tabs" id="settingTabs" role="tablist">
        <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-business"><i class="ph-fill ph-storefront"></i> Business Info</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-numbering"><i class="ph-fill ph-list-numbers"></i> Job & Invoice No</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-policy"><i class="ph-fill ph-scales"></i> Policies</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-sms"><i class="ph-fill ph-chat-teardrop-text"></i> SMS Gateway</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-security"><i class="ph-fill ph-shield-check"></i> Security</button></li>
    </ul>

    <div class="card settings-card">
        <form method="POST">
            <div class="tab-content">
                
                <div class="tab-pane fade show active" id="tab-business">
                    <div class="section-title"><i class="ph-fill ph-info"></i> Business Details</div>
                    <div class="row g-3">
                        <div class="col-md-8 mb-3"><label class="form-label">Shop Name</label><input type="text" name="shop_name" class="form-control" value="<?= $st['shop_name'] ?>" required></div>
                        <div class="col-md-4 mb-3"><label class="form-label">Currency Symbol</label><input type="text" name="currency" class="form-control" value="<?= $st['currency'] ?>"></div>
                        <div class="col-12 mb-3"><label class="form-label">Full Address</label><textarea name="shop_address" class="form-control" rows="2"><?= $st['shop_address'] ?></textarea></div>
                        <div class="col-md-6 mb-3"><label class="form-label">Phone Number</label><input type="text" name="shop_phone" class="form-control" value="<?= $st['shop_phone'] ?>"></div>
                        <div class="col-md-6 mb-3"><label class="form-label">Email Address</label><input type="email" name="shop_email" class="form-control" value="<?= $st['shop_email'] ?>"></div>
                    </div>
                </div>

                <div class="tab-pane fade" id="tab-numbering">
                    <div class="section-title"><i class="ph-fill ph-hash"></i> Numbering Configuration</div>
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="p-4 border rounded shadow-sm bg-light-subtle">
                                <h6 class="fw-bold mb-3 text-dark">Job Registration Settings</h6>
                                <label class="form-label">Job Prefix (e.g., ORD-)</label>
                                <input type="text" name="job_prefix" class="form-control mb-3" value="<?= $st['job_prefix'] ?>">
                                <label class="form-label">Next Job Number</label>
                                <input type="number" name="next_job_no" class="form-control" value="<?= $st['next_job_no'] ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-4 border rounded shadow-sm bg-light-subtle">
                                <h6 class="fw-bold mb-3 text-dark">Billing Settings</h6>
                                <label class="form-label">Invoice Prefix (e.g., INV-)</label>
                                <input type="text" name="invoice_prefix" class="form-control mb-3" value="<?= $st['invoice_prefix'] ?>">
                                <label class="form-label">Next Invoice Number</label>
                                <input type="number" name="next_invoice_no" class="form-control" value="<?= $st['next_invoice_no'] ?>">
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Invoice Terms & Conditions (Footer)</label>
                            <textarea name="invoice_terms" class="form-control" rows="3"><?= $st['invoice_terms'] ?></textarea>
                        </div>
                    </div>
                </div>

                <div class="tab-pane fade" id="tab-policy">
                    <div class="section-title"><i class="ph-fill ph-scales"></i> Service Policies & Fees</div>
                    <div class="row g-3">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Free Storage (Months)</label>
                            <input type="number" name="storage_limit" class="form-control" value="<?= $st['storage_limit'] ?>">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Late Fee / Month (<?= $st['currency'] ?>)</label>
                            <input type="number" step="0.01" name="monthly_fee" class="form-control" value="<?= $st['monthly_fee'] ?>">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Disposal Warning (Months)</label>
                            <input type="number" name="disposal_limit" class="form-control" value="<?= $st['disposal_limit'] ?>">
                        </div>
                    </div>
                </div>

                <div class="tab-pane fade" id="tab-sms">
                    <div class="section-title"><i class="ph-fill ph-paper-plane-tilt"></i> SMS Gateway Configuration</div>
                    <div class="mb-4">
                        <label class="form-label">SMSAPI.lk Bearer Token</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white" style="border-color: var(--border-light);"><i class="ph-fill ph-key text-muted"></i></span>
                            <input type="password" name="sms_api_key" id="smsKey" class="form-control border-start-0" value="<?= $st['sms_api_key'] ?>">
                            <button class="btn btn-outline-secondary" style="border-color: var(--border-light);" type="button" onclick="toggleView('smsKey')"><i class="ph-fill ph-eye"></i></button>
                        </div>
                    </div>
                </div>

                <div class="tab-pane fade" id="tab-security">
                    <div class="section-title"><i class="ph-fill ph-lock-key"></i> Change Administrator Password</div>
                    <div class="row g-3">
                        <div class="col-md-6"><label class="form-label">New Password</label><input type="password" name="new_password" class="form-control"></div>
                        <div class="col-md-6"><label class="form-label">Confirm New Password</label><input type="password" name="confirm_password" class="form-control"></div>
                        <div class="col-12 mt-3">
                            <button type="submit" name="update_password" class="btn btn-password">Update Password Only</button>
                        </div>
                    </div>
                </div>

            </div>

            <button type="submit" name="update_settings" class="btn-save-main">
                <i class="ph-fill ph-floppy-disk"></i> Save All Settings
            </button>
        </form>
    </div>
</div>

<script>
function toggleView(id) {
    var x = document.getElementById(id);
    x.type = (x.type === "password") ? "text" : "password";
}
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>