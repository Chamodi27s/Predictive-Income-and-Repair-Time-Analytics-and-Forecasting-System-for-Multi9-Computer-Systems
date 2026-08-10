<?php
header('Content-Type: application/json');
include 'db_config.php';

if (!isset($_GET['job_no'])) {
    echo json_encode(["status" => "error", "message" => "Job number is required"]);
    exit;
}

$job_no = mysqli_real_escape_string($conn, $_GET['job_no']);

// Fetch data from database
$query = "SELECT jd.*, j.job_date, t.name as technician_name 
          FROM job_device jd 
          JOIN job j ON jd.job_no = j.job_no 
          LEFT JOIN technicians t ON j.technician_id = t.technician_id 
          WHERE jd.job_no = '$job_no' LIMIT 1";
$result = mysqli_query($conn, $query);

if (!$result || mysqli_num_rows($result) === 0) {
    echo json_encode(["status" => "error", "message" => "Job not found in database"]);
    exit;
}

$row = mysqli_fetch_assoc($result);

$device_type = $row['device_name'] ?? 'Unknown';
$item_model = !empty($row['item_model']) ? $row['item_model'] : 'Unknown';
$fault_description = $row['issue_name'] ?? 'Unknown';
$technician = !empty($row['technician_name']) ? $row['technician_name'] : 'Unknown';
$repair_path = !empty($row['repair_path']) ? $row['repair_path'] : 'In-House';
$warranty = !empty($row['warranty_status']) ? $row['warranty_status'] : 'No';
$solution = !empty($row['solution']) ? $row['solution'] : 'Pending Diagnosis';
$date_in = $row['job_date'] ?? date('Y-m-d');

// Prepare payload with real values
$payload = [
    "Device_Type" => $device_type,
    "Item_Model" => $item_model,
    "Fault_Description" => $fault_description,
    "Technician" => $technician,
    "Repair_Path" => $repair_path,
    "Warranty" => $warranty,
    "Solution" => $solution
];

$json_payload = json_encode($payload);

// Execute the python script
// We need to run it from inside the time_prediction_project folder, or specify the full path.
$cwd = __DIR__ . '/time_prediction_project';
$command = "python predict_api.py";

$process = proc_open($command, [
    0 => ["pipe", "r"], // stdin
    1 => ["pipe", "w"], // stdout
    2 => ["pipe", "w"]  // stderr
], $pipes, $cwd);

if (is_resource($process)) {
    fwrite($pipes[0], $json_payload);
    fclose($pipes[0]);
    
    $output = stream_get_contents($pipes[1]);
    fclose($pipes[1]);
    
    $error = stream_get_contents($pipes[2]);
    fclose($pipes[2]);
    
    $return_value = proc_close($process);
    
    $prediction = trim($output);
    $data = json_decode($prediction, true);
    
    if ($return_value === 0 && $data !== null && isset($data['days'])) {
        
        $predicted_days = floatval($data['days']);
        $completion_date = date('Y-m-d', strtotime($date_in . " + " . round($predicted_days) . " days"));

        echo json_encode([
            "status" => "success", 
            "days" => $data['days'],
            "completion_date" => $completion_date,
            "cost" => number_format($data['cost'], 2),
            "parts" => $data['parts'],
            "device" => $device_type,
            "model" => $item_model,
            "issue" => $fault_description,
            "repair_path" => $repair_path,
            "solution" => $solution,
            "technician" => $technician
        ]);
    } else {
        echo json_encode(["status" => "error", "message" => "AI Engine Error: " . htmlspecialchars($error . $output)]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Failed to start Python prediction engine"]);
}
?>
