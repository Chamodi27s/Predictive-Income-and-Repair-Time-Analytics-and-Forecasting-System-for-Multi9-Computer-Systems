<?php
header('Content-Type: application/json');
include 'db_config.php';

if (!isset($_GET['job_no'])) {
    echo json_encode(["status" => "error", "message" => "Job number is required"]);
    exit;
}

$job_no = mysqli_real_escape_string($conn, $_GET['job_no']);

// Fetch data from database
$query = "SELECT * FROM job_device WHERE job_no = '$job_no' LIMIT 1";
$result = mysqli_query($conn, $query);

if (!$result || mysqli_num_rows($result) === 0) {
    echo json_encode(["status" => "error", "message" => "Job not found in database"]);
    exit;
}

$row = mysqli_fetch_assoc($result);

$device_type = $row['device_name'] ?? 'Unknown';
$item_model = $row['device_name'] ?? 'Unknown'; 
$fault_description = $row['issue_name'] ?? 'Unknown';

// Prepare payload with our 3 fields + defaults for the rest
$payload = [
    "Device_Type" => $device_type,
    "Item_Model" => $item_model,
    "Fault_Description" => $fault_description,
    "Technician" => "Default Tech",
    "Repair_Path" => "Carry-In",
    "Warranty" => "No",
    "Solution" => "Pending Diagnosis"
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
    
    if ($return_value === 0 && is_numeric($prediction)) {
        // We will calculate a placeholder cost for now since we don't have the cost ML model
        // e.g. base fee $50 + ($20 * predicted days)
        $cost = 50 + (20 * round($prediction));
        
        echo json_encode([
            "status" => "success", 
            "days" => $prediction,
            "cost" => number_format($cost, 2),
            "device" => $device_type,
            "issue" => $fault_description
        ]);
    } else {
        echo json_encode(["status" => "error", "message" => "AI Engine Error: " . htmlspecialchars($error . $output)]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Failed to start Python prediction engine"]);
}
?>
