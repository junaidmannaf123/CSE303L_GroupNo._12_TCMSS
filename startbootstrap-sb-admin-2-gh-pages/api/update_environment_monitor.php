<?php
<?php
header('Content-Type: application/json');
$conn = new mysqli('localhost', 'root', '', 'tccms');
$data = json_decode(file_get_contents('php://input'), true);

$stmt = $conn->prepare("UPDATE tblenvironmentaldata SET ntemperature=?, nhumidity=?, cstatus=?, cwaterquality=?, dtimestamp=?, cincubatorid=?, cenclosureid=?, cstaffid=? WHERE cenvironmentaldataid=?");
$stmt->bind_param(
    "idsssssss",
    $data['temperature'],
    $data['humidity'],
    $data['status'],
    $data['waterQuality'],
    $data['timeStamp'],
    $data['incubatorId'],
    $data['enclosureId'],
    $data['staffId'],
    $data['id']
);

$success = $stmt->execute();
echo json_encode(['success' => $success]);

$stmt->close();
$conn->close();