<?php include("../config/db.php"); ?>


<!DOCTYPE html>
<html>
<head>
    <title>Cash Book - Multi 9 Computer System</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; padding: 20px; background-color: #f4f7f6; }
        .cashbook-container { max-width: 900px; margin: auto; background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); }
        h2 { text-align: center; color: #333; margin-bottom: 25px; text-transform: uppercase; letter-spacing: 1px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { padding: 15px; border-bottom: 1px solid #eee; text-align: left; }
        th { background-color: #2c3e50; color: white; font-weight: 500; }
        tr:hover { background-color: #f9f9f9; }
        .income-text { color: #27ae60; font-weight: bold; }
        .balance-text { color: #2980b9; font-weight: bold; }
        .no-data { text-align: center; padding: 20px; color: #888; }
        .header-info { display: flex; justify-content: space-between; margin-bottom: 10px; font-size: 14px; color: #666; }
    </style>
</head>
<body>

<div class="cashbook-container">
    <h2>Daily Cash Book</h2>
    
    <div class="header-info">
        <span>System: Multi 9 Computer System</span>
        <span>Date: <?php echo date('Y-m-d'); ?></span>
    </div>

    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Reference (Invoice)</th>
                <th>Income (LKR)</th>
                <th>Net Balance (LKR)</th>
            </tr>
        </thead>
        <tbody>
            <?php
           
            $sql = "SELECT * FROM cashbook ORDER BY cashid DESC";
            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . $row['date'] . "</td>";
                    echo "<td>Invoice #" . $row['invoice_no'] . "</td>";
                    echo "<td class='income-text'>+ " . number_format($row['income'], 2) . "</td>";
                    echo "<td class='balance-text'>" . number_format($row['balance'], 2) . "</td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='4' class='no-data'>තවමත් කිසිදු ගනුදෙනුවක් සිදු කර නොමැත.</td></tr>";
            }
            ?>
        </tbody>
    </table>
    
    <div style="margin-top: 25px; text-align: center;">
        <button onclick="window.print()" style="padding: 10px 20px; background: #666; color: white; border: none; border-radius: 5px; cursor: pointer;">Print Report</button>
        <button onclick="window.location.href='index.php'" style="padding: 10px 20px; background: #2c3e50; color: white; border: none; border-radius: 5px; cursor: pointer; margin-left: 10px;">Dashboard</button>
    </div>
</div>

</body>
</html>
