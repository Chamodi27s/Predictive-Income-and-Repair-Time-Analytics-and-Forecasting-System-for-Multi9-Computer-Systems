<?php
include 'db_config.php';
include 'navbar.php';

$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';

// i.created_at හෝ i.invoice_date ලෙස ඔයාගේ table එකේ දවස තියෙන column එක පාවිච්චි කරන්න
// මෙහිදී මම 'invoice_date' ලෙස උපකල්පනය කළා
$query = "SELECT i.*, j.phone_number, jd.device_name, c.customer_name 
          FROM invoice i 
          JOIN job j ON i.job_no = j.job_no 
          JOIN job_device jd ON i.job_no = jd.job_no
          JOIN customer c ON j.phone_number = c.phone_number";

if ($search != '') {
    $query .= " WHERE i.invoice_no LIKE '%$search%' OR i.job_no LIKE '%$search%' OR j.phone_number LIKE '%$search%' OR c.customer_name LIKE '%$search%'";
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
        .container { max-width: 1200px; margin: auto; background: white; padding: 30px; border-radius: 15px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; }
        .search-box input { padding: 10px; border: 1px solid #ddd; border-radius: 8px; width: 300px; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #065f46; color: white; padding: 15px; text-align: left; }
        td { padding: 15px; border-bottom: 1px solid #f1f1f1; }
        .status { padding: 5px 12px; border-radius: 20px; font-size: 11px; font-weight: bold; }
        .status-paid { background: #dcfce7; color: #166534; }
        .status-pending { background: #fee2e2; color: #991b1b; }
        .rent-badge { background: #fef3c7; color: #92400e; font-size: 10px; padding: 2px 6px; border-radius: 4px; border: 1px solid #f59e0b; display: block; margin-top: 4px; }
        .action-btn { background: #3498db; color: white; padding: 8px 15px; border-radius: 6px; text-decoration: none; font-size: 13px; border:none; cursor:pointer; }
        .print-btn { background: #065f46; }
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
                <th>Customer</th>
                <th>Device</th>
                <th>Original Bill</th>
                <th>Late Rent</th>
                <th>Final Amount</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while($row = $result->fetch_assoc()): 
                
                // --- Rent Calculation Logic ---
                $invoice_date = $row['invoice_date']; // Invoice එක හදපු දවස
                $created = new DateTime($invoice_date);
                $today = new DateTime();
                $diff = $created->diff($today);
                
                // මාස ගණන බැලීම
                $months_passed = ($diff->y * 12) + $diff->m;
                $rent_amount = 0;
                
                // පාරිභෝගිකයා තවමත් බඩුව රැගෙන ගොස් නැත්නම් (Status Pending නම්) 
                // සහ මාස 3කට වඩා වැඩි නම් පමණක් Rent එක හදන්න
                if ($row['payment_status'] != 'Paid' && $months_passed >= 3) {
                    $rent_amount = $months_passed * 1000;
                }
                
                $final_total = $row['grand_total'] + $rent_amount;
                // ------------------------------
            ?>
            <tr>
                <td><strong>#<?= $row['invoice_no'] ?></strong></td>
                <td><?= $row['job_no'] ?></td>
                <td><?= $row['customer_name'] ?></td>
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
                    <span class="status <?= $row['payment_status'] == 'Paid' ? 'status-paid' : 'status-pending' ?>">
                        <?= $row['payment_status'] ?>
                    </span>
                </td>
                <td>
                    <a href="generate_bill.php?job_no=<?= $row['job_no'] ?>&view_only=true" class="action-btn print-btn">👁️ View & Print</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

</body>
<?php include 'chatbot.php'; ?>
</html>