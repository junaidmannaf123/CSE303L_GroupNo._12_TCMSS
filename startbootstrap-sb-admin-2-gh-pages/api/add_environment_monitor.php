<?php
header("Content-Type: application/json");
include '../config/database.php';

$input = file_get_contents('php://input');
$data = json_decode($input, true);

if ($data === null) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Invalid JSON input."]);
    exit();
}

$stmt = $conn->prepare("INSERT INTO environmental_monitor (cenvironmentaldataid, ntemperature, nhumidity, cstatus, cwaterquality, dtimestamp, cincubatorid, cenclosureid, cstaffid) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("sssssssss", 
    $data['cenvironmentaldataid'], 
    $data['ntemperature'], 
    $data['nhumidity'], 
    $data['cstatus'], 
    $data['cwaterquality'], 
    $data['dtimestamp'], 
    $data['cincubatorid'], 
    $data['cenclosureid'], 
    $data['cstaffid']
);

if ($stmt->execute()) {
    echo json_encode(["status" => "success", "message" => "New record created successfully."]);
} else {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Failed to create record: " . $stmt->error]);
}

$stmt->close();
$conn->close();
?>