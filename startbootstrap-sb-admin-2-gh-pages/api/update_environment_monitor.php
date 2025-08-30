<?php
header("Content-Type: application/json");
include '../config/database.php'; // Include your database connection file

// Check if the request method is PUT
if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
    http_response_code(405);
    echo json_encode(["status" => "error", "message" => "Method Not Allowed"]);
    exit();
}

// Read the raw input from the PUT request
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// Check if data is valid
if ($data === null) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Invalid JSON input."]);
    exit();
}

$required_fields = ['cenvironmentaldataid', 'ntemperature', 'nhumidity', 'cstatus', 'cwaterquality', 'dtimestamp', 'cincubatorid', 'cenclosureid', 'cstaffid'];
foreach ($required_fields as $field) {
    if (!isset($data[$field])) {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "Missing required field: " . $field]);
        exit();
    }
}

// Extract data
$cenvironmentaldataid = $data['cenvironmentaldataid'];
$ntemperature = $data['ntemperature'];
$nhumidity = $data['nhumidity'];
$cstatus = $data['cstatus'];
$cwaterquality = $data['cwaterquality'];
$dtimestamp = $data['dtimestamp'];
$cincubatorid = $data['cincubatorid'];
$cenclosureid = $data['cenclosureid'];
$cstaffid = $data['cstaffid'];

// SQL to update the record
$sql = "UPDATE environmental_monitor SET 
            ntemperature = ?,
            nhumidity = ?,
            cstatus = ?,
            cwaterquality = ?,
            dtimestamp = ?,
            cincubatorid = ?,
            cenclosureid = ?,
            cstaffid = ?
        WHERE cenvironmentaldataid = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("sssssssss", 
    $ntemperature, 
    $nhumidity, 
    $cstatus, 
    $cwaterquality, 
    $dtimestamp, 
    $cincubatorid, 
    $cenclosureid, 
    $cstaffid, 
    $cenvironmentaldataid
);

if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        echo json_encode(["status" => "success", "message" => "Record updated successfully."]);
    } else {
        echo json_encode(["status" => "error", "message" => "No record found or no changes made."]);
    }
} else {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Execute failed: " . $stmt->error]);
}

$stmt->close();
$conn->close();
?>