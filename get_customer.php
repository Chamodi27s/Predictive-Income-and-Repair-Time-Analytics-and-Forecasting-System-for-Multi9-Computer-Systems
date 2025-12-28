<?php
include 'db_config.php';

$phone = isset($_GET['phone']) ? mysqli_real_escape_string($conn, $_GET['phone']) : '';
$response = ['found' => false];

if ($phone != '') {
    $query = "SELECT * FROM customer WHERE phone_number = '$phone' LIMIT 1";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) > 0) {
        $data = mysqli_fetch_assoc($result);
        $response = [
            'found' => true,
            'name' => $data['customer_name'],
            'email' => $data['email'] ?? '',
            'address' => $data['address'] ?? ''
        ];
    }
}

echo json_encode($response);