<?php include 'db_config.php'; ?>
<!DOCTYPE html>
<html>
<head>
    <title>Cashbook - Multi 9</title>
    <style>
        body { font-family: sans-serif; background: #f4f7f6; padding: 20px; }
        .container { background: white; padding: 20px; border-radius: 10px; box-shadow: 0 4px 8px rgba(0,0,0,0.1); }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 12px; border: 1px solid #ddd; text-align: left; }
        th { background: #2e7d32; color: white; }
        .income { color: green; font-weight: bold; }
        .balance { background: #e8f5e9; font-weight: bold; }
    </style>
</head>
<body>

<div class="container">
    <h2>Main Cashbook (Income Records)</h2>
    <p>Okkoma income ekathu wenne methanata.</p>
    
    <table>
        <tr>
            <th>Cash ID</th>
            <th>Date</th>
            <th>Invoice No</th>
            <th>Income (Rs.)</th>
            <th>Current Balance (Rs.)</th>
        </tr>
        <?php
        // Cashbook eka aluthma ekage indan pennanawa
        $sql = "SELECT * FROM cashbook ORDER BY cashid DESC";
        $result = $conn->query($sql);
        
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                echo "<tr>
                        <td>#{$row['cashid']}</td>
                        <td>{$row['date']}</td>
                        <td>INV-{$row['invoice_no']}</td>
                        <td class='income'>+ ".number_format($row['income'], 2)."</td>
                        <td class='balance'>".number_format($row['balance'], 2)."</td>
                      </tr>";
            }
        } else {
            echo "<tr><td colspan='5' style='text-align:center;'>Thama income ekak natha.</td></tr>";
        }
        ?>
    </table>
</div>

</body>
</html>