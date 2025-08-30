<?php
header("Content-Type: application/json");
include '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
    http_response_code(405);
    echo json_encode(["status" => "error", "message" => "Method Not Allowed."]);
    exit();
}

$input = file_get_contents('php://input');
$data = json_decode($input, true);

if ($data === null) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Invalid JSON input."]);
    exit();
}

$stmt = $conn->prepare("UPDATE environmental_monitor SET ntemperature=?, nhumidity=?, cstatus=?, cwaterquality=?, dtimestamp=?, cincubatorid=?, cenclosureid=?, cstaffid=? WHERE cenvironmentaldataid=?");
$stmt->bind_param("sssssssss", 
    $data['ntemperature'], 
    $data['nhumidity'], 
    $data['cstatus'], 
    $data['cwaterquality'], 
    $data['dtimestamp'], 
    $data['cincubatorid'], 
    $data['cenclosureid'], 
    $data['cstaffid'],
    $data['cenvironmentaldataid']
);

if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        echo json_encode(["status" => "success", "message" => "Record updated successfully."]);
    } else {
        echo json_encode(["status" => "error", "message" => "No record found or no changes made."]);
    }
} else {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Failed to update record: " . $stmt->error]);
}

$stmt->close();
$conn->close();
?>