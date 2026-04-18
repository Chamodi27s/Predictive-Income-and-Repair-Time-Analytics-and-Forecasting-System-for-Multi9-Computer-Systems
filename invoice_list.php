<?php
include 'db_config.php';
include 'navbar.php';

$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';

// Query එකේදී late_fee column එකත් ඇතුළත් කර ඇත
$query = "SELECT i.*, j.phone_number, jd.device_name, jd.device_status, c.customer_name 
          FROM invoice i 
          JOIN job j ON i.job_no = j.job_no 
          JOIN job_device jd ON i.job_no = jd.job_no
          JOIN customer c ON j.phone_number = c.phone_number";

if ($search != '') {
    $query .= " WHERE i.invoice_no LIKE '%$search%' 
                OR i.job_no LIKE '%$search%' 
                OR j.phone_number LIKE '%$search%' 
                OR c.customer_name LIKE '%$search%'";
}

$query .= " ORDER BY i.invoice_no DESC";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice List | Multi9</title>
    <style>
        body { font-family: 'Inter', sans-serif; background: #f8fafc; padding: 120px 20px 40px; }
        .container { max-width: 1250px; margin: auto; background: white; padding: 30px; border-radius: 15px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; }
        .search-box input { padding: 10px; border: 1px solid #ddd; border-radius: 8px; width: 300px; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #065f46; color: white; padding: 15px; text-align: left; font-size: 14px; }
        td { padding: 15px; border-bottom: 1px solid #f1f1f1; font-size: 14px; vertical-align: middle; }
        
        .status { padding: 6px 14px; border-radius: 20px; font-size: 11px; font-weight: bold; text-transform: uppercase; display: inline-block; }
        .status-paid { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
        .status-pending { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
        
        .action-btn { background: #3498db; color: white; padding: 8px 15px; border-radius: 6px; text-decoration: none; font-size: 13px; border:none; cursor:pointer; transition: 0.3s; }
        .action-btn:hover { opacity: 0.8; }
        .print-btn { background: #065f46; }
        
        tr:hover { background-color: #f9fafb; }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <h2>🧾 Invoice Management</h2>
        <form method="GET" class="search-box">
            <input type="text" name="search" placeholder="Search Invoice, Job, Customer..." value="<?= htmlspecialchars($search) ?>">
            <button type="submit" class="action-btn">Search</button>
        </form>
    </div>

    <table>
        <thead>
            <tr>
                <th>Inv No</th>
                <th>Job No</th>
                <th>Customer Details</th>
                <th>Device</th>
                <th style="text-align:right;">Subtotal</th>
                <th style="text-align:right;">Late Rent</th>
                <th style="text-align:right;">Final Amount</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result->num_rows > 0): ?>
                <?php while($row = $result->fetch_assoc()): 
                    // Database එකේ සේව් වී ඇති අගයන් කෙලින්ම ලබා ගැනීම
                    $late_fee = floatval($row['late_fee'] ?? 0);
                    $grand_total = floatval($row['grand_total']); // මෙය (Service + Parts + Late Fee) දැනටමත් සේව් වී ඇති අගයයි
                ?>
                <tr>
                    <td><strong>#<?= $row['invoice_no'] ?></strong></td>
                    <td><small><?= $row['job_no'] ?></small></td>
                    <td>
                        <strong><?= $row['customer_name'] ?></strong><br>
                        <small style="color: #666;"><?= $row['phone_number'] ?></small>
                    </td>
                    <td><?= $row['device_name'] ?></td>
                    <td style="text-align:right;">Rs. <?= number_format($grand_total - $late_fee, 2) ?></td>
                    <td style="text-align:right; color: #e67e22; font-weight: bold;">
                        <?= ($late_fee > 0) ? "Rs. " . number_format($late_fee, 2) : "-" ?>
                    </td>
                    <td style="text-align:right; font-weight: 800; color: #2c3e50;">
                        Rs. <?= number_format($grand_total, 2) ?>
                    </td>
                    <td>
                        <span class="status <?= ($row['payment_status'] == 'Paid') ? 'status-paid' : 'status-pending' ?>">
                            <?= $row['payment_status'] ?>
                        </span>
                    </td>
                    <td>
                        <a href="generate_bill.php?job_no=<?= $row['job_no'] ?>&view_only=true" class="action-btn print-btn">👁️ View Bill</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="9" style="text-align: center; padding: 50px; color: #999;">No invoices found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

</body>

</html>