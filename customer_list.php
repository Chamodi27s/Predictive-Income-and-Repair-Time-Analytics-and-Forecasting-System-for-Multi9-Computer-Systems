<?php include 'db_config.php'; ?>
<h2>Active Repair Records</h2>
<form method="GET">
    <input type="text" name="q" placeholder="Search Issue, Job No or Name..." style="padding: 10px; width: 300px;">
    <button type="submit" style="padding: 10px;">Search</button>
</form>

<table border="1" style="width: 100%; border-collapse: collapse; margin-top: 20px;">
    <tr style="background: #2e7d32; color: white;">
        <th>Job No</th>
        <th>Customer</th>
        <th>Device</th>
        <th>Date</th>
        <th>Status</th>
        <th>Action</th>
    </tr>
    <?php
    $q = $_GET['q'] ?? '';
    // Join එකක් මගින් Issue එකත් සමඟම දත්ත සොයයි
    $sql = "SELECT j.job_no, c.customer_name, jd.device_name, j.job_date, j.job_status 
            FROM job j 
            JOIN customer c ON j.phone_number = c.phone_number 
            JOIN job_device jd ON j.job_no = jd.job_no 
            WHERE j.job_no LIKE '%$q%' OR c.customer_name LIKE '%$q%' OR jd.device_name LIKE '%$q%'";
    
    $res = $conn->query($sql);
    while($row = $res->fetch_assoc()) {
        echo "<tr>
                <td>#{$row['job_no']}</td>
                <td>{$row['customer_name']}</td>
                <td>{$row['device_name']}</td>
                <td>{$row['job_date']}</td>
                <td>{$row['job_status']}</td>
                <td><a href='generate_invoice.php?job_no={$row['job_no']}'>Make Bill</a></td>
              </tr>";
    }
    ?>
</table>