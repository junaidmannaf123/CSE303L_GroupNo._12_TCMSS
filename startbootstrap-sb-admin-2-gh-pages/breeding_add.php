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

$cbreedingid = isset($input['cbreedingid']) ? trim($input['cbreedingid']) : '';
$nincubationperiod = isset($input['nincubationperiod']) && $input['nincubationperiod'] !== '' ? (int)$input['nincubationperiod'] : null;
$dstartdate = isset($input['dstartdate']) && $input['dstartdate'] !== '' ? trim($input['dstartdate']) : null;
$denddate = isset($input['denddate']) && $input['denddate'] !== '' ? trim($input['denddate']) : null;
$neggscount = isset($input['neggscount']) && $input['neggscount'] !== '' ? (int)$input['neggscount'] : null;
$nhatchingservicerate = isset($input['nhatchingservicerate']) && $input['nhatchingservicerate'] !== '' ? (int)$input['nhatchingservicerate'] : null;
$cstaffid = isset($input['cstaffid']) && $input['cstaffid'] !== '' ? trim($input['cstaffid']) : null;
$ctortoiseid = isset($input['ctortoiseid']) && $input['ctortoiseid'] !== '' ? trim($input['ctortoiseid']) : null;

if ($cbreedingid === '') {
	send_json(['success' => false, 'error' => 'cbreedingid is required'], 422);
}

$stmt = $mysqli->prepare("INSERT INTO tblbreedingrecord (cbreedingid, nincubationperiod, dstartdate, denddate, neggscount, nhatchingservicerate, cstaffid, ctortoiseid) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
if ($stmt === false) {
	send_json(['success' => false, 'error' => $mysqli->error], 500);
}

// Types: s i s s i i s s
$stmt->bind_param(
	'sissiiss',
	$cbreedingid,
	$nincubationperiod,
	$dstartdate,
	$denddate,
	$neggscount,
	$nhatchingservicerate,
	$cstaffid,
	$ctortoiseid
);

if (!$stmt->execute()) {
	send_json(['success' => false, 'error' => $stmt->error], 500);
}

send_json(['success' => true]);
