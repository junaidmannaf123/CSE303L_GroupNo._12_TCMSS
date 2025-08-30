<?php
header('Content-Type: application/json');
include '../config/database.php';

$data = json_decode(file_get_contents('php://input'), true);

// Assumes fields: clogid, cincubatorid, caction, dtimestamp, cnotes, cstaffid
$stmt = $conn->prepare("UPDATE tblincubatorlog SET cincubatorid=?, caction=?, dtimestamp=?, cnotes=?, cstaffid=? WHERE clogid=?");
$stmt->bind_param(
    "ssssss",
    $data['incubatorId'],
    $data['action'],
    $data['timestamp'],
    $data['notes'],
    $data['staffId'],
    $data['logId']
);

$success = $stmt->execute();
echo json_encode(['success' => $success]);

$stmt->close();
$conn->close();
?>