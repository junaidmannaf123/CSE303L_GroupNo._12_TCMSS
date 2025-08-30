<?php
require_once __DIR__ . '/config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
	send_json(['success' => false, 'error' => 'Method not allowed'], 405);
}

$input = $_POST;
if (empty($input)) {
	$raw = file_get_contents('php://input');
	if ($raw) {
		$decoded = json_decode($raw, true);
		if (is_array($decoded)) {
			$input = $decoded;
		}
	}
}

$ceggid = isset($input['ceggid']) ? trim($input['ceggid']) : '';
$nweight = isset($input['nweight']) && $input['nweight'] !== '' ? (int)$input['nweight'] : null;
$nlength = isset($input['nlength']) && $input['nlength'] !== '' ? (int)$input['nlength'] : null;
$nwidth = isset($input['nwidth']) && $input['nwidth'] !== '' ? (int)$input['nwidth'] : null;
$ceggcondition = isset($input['ceggcondition']) && $input['ceggcondition'] !== '' ? trim($input['ceggcondition']) : null;
$cincubatorid = isset($input['cincubatorid']) && $input['cincubatorid'] !== '' ? trim($input['cincubatorid']) : null;
$cbreedingid = isset($input['cbreedingid']) && $input['cbreedingid'] !== '' ? trim($input['cbreedingid']) : null;

if ($ceggid === '') {
	send_json(['success' => false, 'error' => 'ceggid is required'], 422);
}

$stmt = $mysqli->prepare("INSERT INTO tbleggdetails 
    (ceggid, nweight, nlength, nwidth, ceggcondition, cincubatorid, cbreedingid) 
    VALUES (?, ?, ?, ?, ?, ?, ?)");

if ($stmt === false) {
	send_json(['success' => false, 'error' => $mysqli->error], 500);
}

$stmt->bind_param(
    'siiisss',
    $ceggid,
    $nweight,
    $nlength,
    $nwidth,
    $ceggcondition,
    $cincubatorid,
    $cbreedingid
);

if (!$stmt->execute()) {
	send_json(['success' => false, 'error' => $stmt->error], 500);
}

send_json(['success' => true]);
?>
