<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: PUT, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

try {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) {
        throw new Exception('Invalid JSON input');
    }

    if (empty($input['crecordid'])) {
        throw new Exception('Missing record id');
    }

    // Ensure record exists
    $stmt = $pdo->prepare('SELECT crecordid FROM tblmedicalrecords WHERE crecordid = :id');
    $stmt->execute([':id' => $input['crecordid']]);
    if (!$stmt->fetch()) {
        throw new Exception('Record not found');
    }

    $fields = [
        'drecordingdate', 'cdiagnosis', 'ctreatment', 'ctype', 'ddate',
        'cvaccinationstatus', 'dcheckdate', 'dchecktime', 'cstaffid', 'ctortoiseid'
    ];
    $sets = [];
    $params = [':id' => $input['crecordid']];
    foreach ($fields as $f) {
        if (array_key_exists($f, $input)) {
            $sets[] = "$f = :$f";
            $params[":$f"] = $input[$f];
        }
    }

    if (empty($sets)) {
        echo json_encode(['success' => true, 'message' => 'Nothing to update']);
        exit;
    }

    $sql = 'UPDATE tblmedicalrecords SET ' . implode(', ', $sets) . ' WHERE crecordid = :id';
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    echo json_encode(['success' => true, 'message' => 'Medical record updated']);
} catch (Throwable $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>


