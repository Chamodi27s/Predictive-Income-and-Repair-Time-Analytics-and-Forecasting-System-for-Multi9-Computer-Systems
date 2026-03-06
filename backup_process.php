
<?php

header("Content-Type: application/json");

$host = "localhost";
$user = "root";
$pass = "";
$db   = "servidedb";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    echo json_encode([
        "status" => "error",
        "message" => "Database connection failed"
    ]);
    exit();
}

$tables = [];
$result = $conn->query("SHOW TABLES");

while ($row = $result->fetch_row()) {
    $tables[] = $row[0];
}

$sql = "-- Database Backup\n\n";

foreach ($tables as $table) {

    $result = $conn->query("SELECT * FROM `$table`");

    $sql .= "DROP TABLE IF EXISTS `$table`;\n";

    $row2 = $conn->query("SHOW CREATE TABLE `$table`")->fetch_row();
    $sql .= $row2[1] . ";\n\n";

    while ($row = $result->fetch_row()) {

        $sql .= "INSERT INTO `$table` VALUES(";

        for ($i = 0; $i < count($row); $i++) {

            if (isset($row[$i])) {
                $sql .= '"' . addslashes($row[$i]) . '"';
            } else {
                $sql .= 'NULL';
            }

            if ($i < (count($row) - 1)) {
                $sql .= ",";
            }
        }

        $sql .= ");\n";
    }

    $sql .= "\n\n";
}

$date = date("Y-m-d_H-i-s");

$backup_dir = "backups/";

if (!is_dir($backup_dir)) {
    mkdir($backup_dir, 0755, true);
}

$sql_file = $backup_dir . "backup_" . $date . ".sql";

if (!file_put_contents($sql_file, $sql)) {

    echo json_encode([
        "status" => "error",
        "message" => "SQL file creation failed"
    ]);
    exit();
}

$zip_file = $backup_dir . "backup_" . $date . ".zip";

$zip = new ZipArchive();

if ($zip->open($zip_file, ZipArchive::CREATE) === TRUE) {

    $zip->addFile($sql_file, basename($sql_file));
    $zip->close();

    unlink($sql_file);

    echo json_encode([
        "status" => "success",
        "download_url" => $zip_file
    ]);

} else {

    echo json_encode([
        "status" => "error",
        "message" => "ZIP creation failed"
    ]);
}
?>

