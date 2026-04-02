<?php
include 'db_config.php';
include 'navbar.php';

$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';

// Query එකේදී invoice, job, job_device සහ customer table සම්බන්ධ කර ඇත
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
        
        /* Status Styles */
        .status { padding: 6px 14px; border-radius: 20px; font-size: 11px; font-weight: bold; text-transform: uppercase; display: inline-block; }
        .status-paid { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
        .status-pending { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
        
        .rent-badge { background: #fef3c7; color: #92400e; font-size: 10px; padding: 2px 6px; border-radius: 4px; border: 1px solid #f59e0b; display: block; margin-top: 4px; width: fit-content; }
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
                <th>Service + Parts</th>
                <th>Late Rent</th>
                <th>Final Amount</th>
                <th>Payment Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result->num_rows > 0): ?>
                <?php while($row = $result->fetch_assoc()): 
                    
                    // --- Rent Calculation Logic (ඔයාගේම Logic එක) ---
                    $invoice_date = $row['invoice_date']; 
                    $created = new DateTime($invoice_date);
                    $today = new DateTime();
                    $diff = $created->diff($today);
                    
                    $months_passed = ($diff->y * 12) + $diff->m;
                    $rent_amount = 0;
                    
                    // 'Paid' නොවන සහ මාස 3 ඉක්මවූ අයට පමණක් rent එකතු වේ
                    if ($row['payment_status'] != 'Paid' && $months_passed >= 3) {
                        $rent_amount = ($months_passed - 2) * 1000; // මාස 3 සිට ගණනය කරන්නේ නම්
                    }
                    
                    $final_total = $row['grand_total'] + $rent_amount;
                ?>
                <tr>
                    <td><strong>#<?= $row['invoice_no'] ?></strong></td>
                    <td><small><?= $row['job_no'] ?></small></td>
                    <td>
                        <strong><?= $row['customer_name'] ?></strong><br>
                        <small style="color: #666;"><?= $row['phone_number'] ?></small>
                    </td>
                    <td><?= $row['device_name'] ?></td>
                    <td>Rs. <?= number_format($row['grand_total'], 2) ?></td>
                    <td style="color: #e67e22; font-weight: bold;">
                        <?= ($rent_amount > 0) ? "Rs. " . number_format($rent_amount, 2) : "-" ?>
                        <?php if($rent_amount > 0): ?>
                            <span class="rent-badge"><?= $months_passed ?> Months Late</span>
                        <?php endif; ?>
                    </td>
                    <td style="font-weight: 800; color: #2c3e50;">
                        Rs. <?= number_format($final_total, 2) ?>
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
<?php include 'chatbot.php'; ?>
</html>