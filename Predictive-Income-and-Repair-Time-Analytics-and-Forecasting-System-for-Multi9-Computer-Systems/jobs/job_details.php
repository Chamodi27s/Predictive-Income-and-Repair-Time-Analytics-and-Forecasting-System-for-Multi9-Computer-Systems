<?php
include("../config/db.php");

/* ---------------------------
   1. Validate Job ID
---------------------------- */
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("<h3 style='color:red;'>Job ID not provided</h3>");
}

$job_no = $_GET['id'];

/* ---------------------------
   2. Prepare SQL (SAFE)
---------------------------- */
$sql = "SELECT 
            j.job_no,
            j.phone_number,
            j.job_status,
            c.customer_name,
            c.address,
            jd.device_name,
            jd.model
        FROM job j
        LEFT JOIN customer c ON j.phone_number = c.phone_number
        LEFT JOIN job_device jd ON j.job_no = jd.job_no
        WHERE j.job_no = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $job_no);
$stmt->execute();
$result = $stmt->get_result();

/* ---------------------------
   3. Check Data Exists
---------------------------- */
if ($result->num_rows === 0) {
    die("<h3 style='color:red;'>No job found for Job No: $job_no</h3>");
}

$data = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Job Details - #<?php echo htmlspecialchars($job_no); ?></title>
    <style>
        body { font-family: sans-serif; background: #f8f9fa; padding: 20px; }
        .container { max-width: 900px; margin: auto; background: white; padding: 20px; border-radius: 10px; box-shadow: 0 4px 8px rgba(0,0,0,0.1); }
        .header { border-bottom: 2px solid #2e7d32; padding-bottom: 10px; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { padding: 12px; border: 1px solid #ddd; text-align: left; }
        th { background: #e8f5e9; }
        .btn-complete { background: #2e7d32; color: white; padding: 12px 20px; border: none; border-radius: 5px; cursor: pointer; float: right; }
    </style>
</head>
<body>

<div class="container">

    <div class="header">
        <h2>Job Details: #<?php echo htmlspecialchars($job_no); ?></h2>
    </div>

    <div style="display:flex; gap:50px;">
        <div>
            <h4>Customer Info</h4>
            <p><b>Name:</b> <?php echo htmlspecialchars($data['customer_name'] ?? 'N/A'); ?></p>
            <p><b>Phone:</b> <?php echo htmlspecialchars($data['phone_number'] ?? 'N/A'); ?></p>
            <p><b>Address:</b> <?php echo htmlspecialchars($data['address'] ?? 'N/A'); ?></p>
        </div>

        <div>
            <h4>Device Info</h4>
            <p><b>Device:</b> <?php echo htmlspecialchars($data['device_name'] ?? 'N/A'); ?></p>
            <p><b>Model:</b> <?php echo htmlspecialchars($data['model'] ?? 'N/A'); ?></p>
            <p><b>Status:</b> <?php echo htmlspecialchars($data['job_status'] ?? 'N/A'); ?></p>
        </div>
    </div>

    <hr>

    <h3>Add Parts & Billing</h3>

    <form action="generate_invoice.php" method="POST">
        <input type="hidden" name="job_no" value="<?php echo htmlspecialchars($job_no); ?>">

        <table>
            <tr>
                <th>Select Parts (from Stock)</th>
                <th>Quantity</th>
                <th>Price Per Unit</th>
            </tr>
            <tr>
                <td>
                    <select name="item_code" style="width:100%; padding:8px;">
                        <option value="">-- Select Part --</option>
                        <?php
                        $stock = $conn->query("SELECT * FROM stock WHERE quantity > 0");
                        while ($item = $stock->fetch_assoc()) {
                            echo "<option value='{$item['item_code']}'>
                                    {$item['item_name']} (Stock: {$item['quantity']}) - Rs.{$item['unit_price']}
                                  </option>";
                        }
                        ?>
                    </select>
                </td>
                <td>
                    <input type="number" name="qty" value="1" min="1" style="width:80px; padding:8px;">
                </td>
                <td>
                    <input type="text" value="Auto" readonly style="width:120px; padding:8px; background:#eee;">
                </td>
            </tr>
        </table>

        <div style="margin-top:20px; text-align:right;">
            <p>Service Charge (Rs.): 
                <input type="number" name="service_charge" required style="padding:8px;">
            </p>

            <p>Parts Total (Rs.): 
                <input type="number" name="parts_total" value="0" style="padding:8px;">
            </p>

            <button type="submit" class="btn-complete">
                Complete Job & Generate Invoice
            </button>
        </div>
    </form>

</div>

</body>
</html>
