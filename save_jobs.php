<?php
include 'db_config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // මූලික දත්ත ලබා ගැනීම සහ ආරක්ෂිතව සකස් කිරීම (SQL Injection වැළැක්වීමට)
    $job_no = mysqli_real_escape_string($conn, $_POST['job_no']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone_number']);
    $cust_name = mysqli_real_escape_string($conn, $_POST['customer_name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);
    $tech_id = $_POST['technician_id'];
    $job_date = date('Y-m-d');

    // 1. අලුත් Technician කෙනෙක් නම් ඔහුව ඇතුළත් කර ID එක ලබා ගැනීම
    if ($tech_id == 'new' && !empty($_POST['new_technician'])) {
        $new_name = mysqli_real_escape_string($conn, $_POST['new_technician']);
        mysqli_query($conn, "INSERT INTO technicians (name) VALUES ('$new_name')");
        $tech_id = mysqli_insert_id($conn);
    }

    // 2. Customer table එකට දත්ත ඇතුළත් කිරීම හෝ දැනට සිටින අයෙකුගේ විස්තර Update කිරීම
    $cust_sql = "INSERT INTO customer (phone_number, customer_name, email, address) 
                 VALUES ('$phone', '$cust_name', '$email', '$address') 
                 ON DUPLICATE KEY UPDATE customer_name='$cust_name', email='$email', address='$address'";
    mysqli_query($conn, $cust_sql);

    // 3. Job table එකට ප්‍රධාන තොරතුරු ඇතුළත් කිරීම
    mysqli_query($conn, "INSERT INTO job (job_no, job_date, phone_number, technician_id) 
                         VALUES ('$job_no', '$job_date', '$phone', '$tech_id')");

    // 4. Multiple Devices සහ ඒවායේ Warranty තත්ත්වයන් ඇතුළත් කිරීම
    if (isset($_POST['devices'])) {
        foreach ($_POST['devices'] as $key => $device) {
            $device_name = mysqli_real_escape_string($conn, $device);
            $issue = mysqli_real_escape_string($conn, $_POST['issues'][$key]);
            $warranty = mysqli_real_escape_string($conn, $_POST['warranty_status'][$key]);
            
            mysqli_query($conn, "INSERT INTO job_device (job_no, device_name, issue_name, device_status, warranty_status) 
                                 VALUES ('$job_no', '$device_name', '$issue', 'Pending', '$warranty')");
        }
    }

    // සාර්ථක පණිවිඩය පෙන්වීම (SweetAlert2)
    echo "
    <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
    <body style='font-family:sans-serif;'>
    <script>
        Swal.fire({
            icon: 'success',
            title: 'Job Registered!',
            text: 'Job Number: $job_no saved successfully.',
            confirmButtonColor: '#007bff',
            confirmButtonText: 'View Job List'
        }).then(() => {
            window.location = 'job_list.php';
        });
    </script>
    </body>";
}
?>