<?php
include 'db_config.php';
include 'navbar.php';

// URL එකෙන් ID එක ලබා ගැනීම
$id = isset($_GET['id']) ? mysqli_real_escape_string($conn, $_GET['id']) : '';

if (empty($id)) {
    echo "<div style='margin-top:150px; text-align:center;'><h3>Invalid Request!</h3></div>";
    exit;
}

// උපකරණයේ විස්තර සහ පාරිභෝගික විස්තර ලබා ගැනීම
$sql = "SELECT jd.*, j.job_no, j.job_date, c.customer_name, c.phone_number 
        FROM job_device jd
        INNER JOIN job j ON jd.job_no = j.job_no
        INNER JOIN customer c ON j.phone_number = c.phone_number
        WHERE jd.job_device_id = '$id'";

$result = mysqli_query($conn, $sql);
$data = mysqli_fetch_assoc($result);

if (!$data) {
    echo "<div style='margin-top:150px; text-align:center;'><h3>Device Not Found!</h3></div>";
    exit;
}

// දින ගණන ගණනය කිරීම
$days_passed = 0;
if($data['completed_date']) {
    $days_passed = floor((time() - strtotime($data['completed_date'])) / 86400);
}

// Confirm බොත්තම එබූ විට ක්‍රියාත්මක වන කොටස
if (isset($_POST['confirm_destroy'])) {
    // විනාශ කළ දිනය සටහන් කිරීම සඳහා අද දිනය ලබා ගැනීම
    $current_date = date('Y-m-d H:i:s');
    
    // Status එක 'Destroyed' කර එම දිනය destroy_notice_sent_date ලෙස සටහන් කිරීම
    $update_sql = "UPDATE job_device SET 
                   device_status = 'Destroyed', 
                   destroy_notice_sent_date = '$current_date' 
                   WHERE job_device_id = '$id'";
    
    if (mysqli_query($conn, $update_sql)) {
        echo "<script>
                alert('Success: Device marked as Destroyed!'); 
                window.location='destroyed_items_view.php'; 
              </script>";
        exit;
    } else {
        echo "<script>alert('Error: " . mysqli_error($conn) . "');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Destroy Confirmation - Multi9</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f4f7f6; margin: 0; padding-top: 150px; }
        .destroy-container { 
            max-width: 550px; 
            margin: auto; 
            background: white; 
            padding: 30px; 
            border-radius: 15px; 
            box-shadow: 0 10px 30px rgba(0,0,0,0.1); 
            border-top: 10px solid #000; 
        }
        h2 { color: #e53935; text-align: center; margin-bottom: 10px; text-transform: uppercase; }
        .device-info { 
            background: #fffafa; 
            border: 1px solid #ffebee; 
            padding: 20px; 
            border-radius: 10px; 
            margin: 20px 0; 
        }
        .info-row { 
            display: flex; 
            justify-content: space-between; 
            padding: 10px 0; 
            border-bottom: 1px solid #eee; 
            font-size: 15px;
        }
        .info-row:last-child { border-bottom: none; }
        .label { font-weight: bold; color: #333; }
        .val { color: #555; }
        .warning-box { 
            background: #000; 
            color: #fff; 
            padding: 15px; 
            text-align: center; 
            border-radius: 8px; 
            font-size: 13px; 
            margin-bottom: 20px;
        }
        .btn-confirm { 
            width: 100%; 
            padding: 15px; 
            background: #e53935; 
            color: white; 
            border: none; 
            border-radius: 8px; 
            font-size: 16px; 
            font-weight: bold; 
            cursor: pointer; 
            transition: 0.3s;
        }
        .btn-confirm:hover { background: #b71c1c; transform: scale(1.02); }
        .btn-back { 
            display: block; 
            text-align: center; 
            margin-top: 15px; 
            text-decoration: none; 
            color: #777; 
            font-size: 14px; 
        }
    </style>
</head>
<body>

<div class="destroy-container">
    <h2>⚠️ Item Disposal</h2>
    <p style="text-align:center; color:#777;">මෙම උපකරණය වසරකට වඩා පැරණි බැවින් විනාශ කිරීමට නිර්දේශිතයි.</p>

    <div class="device-info">
        <div class="info-row"><span class="label">Job No:</span> <span class="val">#<?= $data['job_no'] ?></span></div>
        <div class="info-row"><span class="label">Customer:</span> <span class="val"><?= $data['customer_name'] ?></span></div>
        <div class="info-row"><span class="label">Device:</span> <span class="val"><?= $data['device_name'] ?></span></div>
        <div class="info-row"><span class="label">Completed Date:</span> <span class="val"><?= $data['completed_date'] ?></span></div>
        <div class="info-row"><span class="label">Total Overdue:</span> <span class="val" style="color:red; font-weight:bold;"><?= $days_passed ?> Days</span></div>
    </div>

    <div class="warning-box">
        මෙම පියවරෙන් පසු පද්ධතියේ මෙම දත්ත 'Destroyed' ලෙස සටහන් වන අතර නැවත සාමාන්‍ය ලැයිස්තුවට පෙන්වනු නොලැබේ.
    </div>

    <form method="POST">
        <button type="submit" name="confirm_destroy" class="btn-confirm" onclick="return confirm('Are you sure you want to mark this item as DESTROYED?')">
            CONFIRM & MARK AS DESTROYED
        </button>
    </form>

    <a href="order_page.php" class="btn-back">← Go Back to Order Page</a>
</div>

</body>
<?php include 'chatbot.php'; ?>
</html>