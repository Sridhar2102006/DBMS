<?php
// api/generate_data.php
require_once '../config/db.php';
header('Content-Type: application/json');

// Get the number of records to generate, default is 1
$count = isset($_GET['count']) ? (int)$_GET['count'] : 1;
if ($count > 100) $count = 100; // limit to 100 max at a time

$success_count = 0;

for ($i = 0; $i < $count; $i++) {
    // Call the stored procedure to generate a random reading
    if ($conn->query("CALL generate_random_reading()")) {
        $success_count++;
    }
}

echo json_encode([
    "status" => "success",
    "message" => "Generated $success_count random readings."
]);

$conn->close();
?>
