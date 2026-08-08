<?php
include 'db_config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
   
    $job_no = mysqli_real_escape_string($conn, $_POST['job_no'] ?? '');
    $phone = mysqli_real_escape_string($conn, $_POST['phone_number'] ?? '');
    $cust_name = mysqli_real_escape_string($conn, $_POST['customer_name'] ?? '');
    $email = mysqli_real_escape_string($conn, $_POST['email'] ?? ''); 
    $address = mysqli_real_escape_string($conn, $_POST['address'] ?? ''); 
    $tech_id = $_POST['technician_id'] ?? '';
    $job_date = date('Y-m-d');

    // add new  Technician 
    if ($tech_id == 'new' && !empty($_POST['new_technician'])) {
        $new_name = mysqli_real_escape_string($conn, $_POST['new_technician']);
        mysqli_query($conn, "INSERT INTO technicians (name) VALUES ('$new_name')");
        $tech_id = mysqli_insert_id($conn);
    }

    
    $cust_sql = "INSERT INTO customer (phone_number, customer_name, email, address) 
                 VALUES ('$phone', '$cust_name', '$email', '$address') 
                 ON DUPLICATE KEY UPDATE customer_name='$cust_name', email='$email', address='$address'";
    mysqli_query($conn, $cust_sql)
    $sql_job = "INSERT INTO job (job_no, job_date, phone_number, technician_id) 
                VALUES ('$job_no', '$job_date', '$phone', '$tech_id')";
    
    if (mysqli_query($conn, $sql_job)) {

        
        
        mysqli_query($conn, "UPDATE system_settings SET next_job_no = next_job_no + 1 WHERE id = 1");
        
        
        //  Multiple Devices add
        if (isset($_POST['devices']) && is_array($_POST['devices'])) {
            foreach ($_POST['devices'] as $key => $device) {
                $device_name = mysqli_real_escape_string($conn, $device);
                
                // Issue Logic 
                $issue_val = $_POST['issues'][$key] ?? '';
                $final_issue_name = "";

                if ($issue_val == 'new' && !empty($_POST['new_issues'][$key])) {
                    $new_issue_text = mysqli_real_escape_string($conn, $_POST['new_issues'][$key]);
                    
                    $check_issue = mysqli_query($conn, "SELECT issue_name FROM issue WHERE issue_name = '$new_issue_text'");
                    if (mysqli_num_rows($check_issue) == 0) {
                        mysqli_query($conn, "INSERT INTO issue (issue_name) VALUES ('$new_issue_text')");
                    }
                    $final_issue_name = $new_issue_text;
                } else {
                    $res = mysqli_query($conn, "SELECT issue_name FROM issue WHERE issue_name = '$issue_val'");
                    $row = mysqli_fetch_assoc($res);
                    $final_issue_name = $row ? $row['issue_name'] : $issue_val;
                }
                
                // Warranty, Description and anothre notr
                $warranty = isset($_POST['warranty'][$key]) ? mysqli_real_escape_string($conn, $_POST['warranty'][$key]) : 'No Warranty';
                $description = isset($_POST['descriptions'][$key]) ? mysqli_real_escape_string($conn, $_POST['descriptions'][$key]) : '';
                $another_note = isset($_POST['another_notes'][$key]) ? mysqli_real_escape_string($conn, $_POST['another_notes'][$key]) : '';
                
                $img_name = ""; 

                // Image Upload 
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

                
                $sql_device = "INSERT INTO job_device (job_no, device_name, issue_name, device_status, warranty_status, description, another_note, device_image) 
                               VALUES ('$job_no', '$device_name', '$final_issue_name', 'Pending', '$warranty', '$description', '$another_note', '$img_name')";
                
                mysqli_query($conn, $sql_device);
            }
        }

        
        echo "
        <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
        <body style='font-family:sans-serif;'>
        <script>
            Swal.fire({
                icon: 'success',
                iconColor: '#28a745',
                title: 'Job Registered Successfully!',
                html: '<b>Job No:</b> $job_no <br> <b>Customer:</b> $cust_name',
                confirmButtonColor: '#28a745',
                confirmButtonText: 'View Full Details',
                allowOutsideClick: false
            }).then((result) => {
                if (result.isConfirmed) {
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