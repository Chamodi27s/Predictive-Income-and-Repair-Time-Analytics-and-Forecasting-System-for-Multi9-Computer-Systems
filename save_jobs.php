<?php
include 'db_config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 1. මූලික දත්ත ලබා ගැනීම (Sanitize)
    $job_no = mysqli_real_escape_string($conn, $_POST['job_no']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone_number']);
    $cust_name = mysqli_real_escape_string($conn, $_POST['customer_name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);
    $tech_id = $_POST['technician_id'];
    $job_date = date('Y-m-d');

    // 2. අලුත් Technician කෙනෙක් නම් ඇතුළත් කිරීම
    if ($tech_id == 'new' && !empty($_POST['new_technician'])) {
        $new_name = mysqli_real_escape_string($conn, $_POST['new_technician']);
        mysqli_query($conn, "INSERT INTO technicians (name) VALUES ('$new_name')");
        $tech_id = mysqli_insert_id($conn);
    }

    // 3. Customer තොරතුරු Update කිරීම හෝ අලුතින් දැමීම (Phone Number is Primary Key)
    $cust_sql = "INSERT INTO customer (phone_number, customer_name, email, address) 
                 VALUES ('$phone', '$cust_name', '$email', '$address') 
                 ON DUPLICATE KEY UPDATE customer_name='$cust_name', email='$email', address='$address'";
    mysqli_query($conn, $cust_sql);

    // 4. ප්‍රධාන Job එක ඇතුළත් කිරීම
    $sql_job = "INSERT INTO job (job_no, job_date, phone_number, technician_id) 
                VALUES ('$job_no', '$job_date', '$phone', '$tech_id')";
    
    if (mysqli_query($conn, $sql_job)) {
        
        // 5. Multiple Devices Loop එක
        if (isset($_POST['devices'])) {
            foreach ($_POST['devices'] as $key => $device) {
                $device_name = mysqli_real_escape_string($conn, $device);
                $issue = mysqli_real_escape_string($conn, $_POST['issues'][$key]);
                $warranty = mysqli_real_escape_string($conn, $_POST['warranty_status'][$key]);
                $description = mysqli_real_escape_string($conn, $_POST['descriptions'][$key]);
                
                $img_name = ""; 

                // Image Upload කොටස
                if (!empty($_FILES['device_images']['name'][$key])) {
                    $target_dir = "uploads/devices/";
                    if (!is_dir($target_dir)) {
                        mkdir($target_dir, 0777, true);
                    }

                    $file_ext = pathinfo($_FILES['device_images']['name'][$key], PATHINFO_EXTENSION);
                    $img_name = "IMG_" . uniqid() . "_" . $key . "." . $file_ext;
                    $target_file = $target_dir . $img_name;

                    move_uploaded_file($_FILES['device_images']['tmp_name'][$key], $target_file);
                }

                $sql_device = "INSERT INTO job_device (job_no, device_name, issue_name, device_status, warranty_status, description, device_image) 
                               VALUES ('$job_no', '$device_name', '$issue', 'Pending', '$warranty', '$description', '$img_name')";
                
                mysqli_query($conn, $sql_device);
            }
        }

        // 6. සාර්ථක පණිවිඩය සහ Redirection (Primary Key එක URL එකට එක් කර ඇත)
        echo "
        <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
        <body style='font-family:sans-serif;'>
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Job Registered Successfully!',
                text: 'Job No: $job_no | Customer: $cust_name',
                confirmButtonColor: '#28a745',
                confirmButtonText: 'View Full Details',
                allowOutsideClick: false
            }).then((result) => {
                if (result.isConfirmed) {
                    // Navbar එකේ නැතත් Phone Number එක Primary Key එක නිසා එය URL එකට යවා පේජ් එකට යයි
                    window.location.href = 'customer_details.php?phone=' + encodeURIComponent('$phone');
                }
            });
        </script>
        </body>";
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
?>