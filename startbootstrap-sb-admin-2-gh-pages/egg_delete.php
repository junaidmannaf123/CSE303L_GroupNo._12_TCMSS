<?php
require_once __DIR__ . '/config/database.php';

$method = $_SERVER['REQUEST_METHOD'];
if ($method !== 'POST' && $method !== 'DELETE') {
	send_json(['success' => false, 'error' => 'Method not allowed'], 405);
}

$input = [];
if ($method === 'POST') {
	$input = $_POST;
} else {
	$raw = file_get_contents('php://input');
	if ($raw) {
		$decoded = json_decode($raw, true);
		if (is_array($decoded)) {
			$input = $decoded;
		}
	}
}

$ceggid = isset($input['ceggid']) ? trim($input['ceggid']) : '';
if ($ceggid === '') {
	send_json(['success' => false, 'error' => 'ceggid is required'], 422);
}

$stmt = $mysqli->prepare('DELETE FROM tbleggdetails WHERE ceggid = ?');
if ($stmt === false) {
	send_json(['success' => false, 'error' => $mysqli->error], 500);
}

$stmt->bind_param('s', $ceggid);
if (!$stmt->execute()) {
	send_json(['success' => false, 'error' => $stmt->error], 500);
}

send_json(['success' => true, 'affected' => $stmt->affected_rows]);
