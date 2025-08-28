<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

try {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) {
        $input = $_POST; // Fallback for form-encoded
    }

    // Validate required fields
    $required = ['ctortoiseid', 'drecordingdate', 'ctype'];
    foreach ($required as $field) {
        if (empty($input[$field])) {
            throw new Exception("Missing required field: $field");
        }
    }

    // Ensure tortoise exists
    $stmt = $pdo->prepare('SELECT ctortoiseid FROM tbltortoise WHERE ctortoiseid = :id');
    $stmt->execute([':id' => $input['ctortoiseid']]);
    if (!$stmt->fetch()) {
        throw new Exception('Invalid tortoise ID');
    }

    // Generate new record id HR-style or use sequential MR??? Table key is crecordid
    $stmt = $pdo->query("SELECT MAX(CAST(SUBSTRING(crecordid,3) AS UNSIGNED)) AS max_num FROM tblmedicalrecords WHERE crecordid REGEXP '^MR[0-9]+'");
    $row = $stmt->fetch();
    $nextNum = (int)($row['max_num'] ?? 0) + 1;
    $newId = 'MR' . str_pad($nextNum, 3, '0', STR_PAD_LEFT);

    $sql = 'INSERT INTO tblmedicalrecords (
        crecordid, drecordingdate, cdiagnosis, ctreatment, ctype, ddate,
        cvaccinationstatus, dcheckdate, dchecktime, cstaffid, ctortoiseid
    ) VALUES (
        :crecordid, :drecordingdate, :cdiagnosis, :ctreatment, :ctype, :ddate,
        :cvaccinationstatus, :dcheckdate, :dchecktime, :cstaffid, :ctortoiseid
    )';

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':crecordid' => $newId,
        ':drecordingdate' => $input['drecordingdate'],
        ':cdiagnosis' => $input['cdiagnosis'] ?? null,
        ':ctreatment' => $input['ctreatment'] ?? null,
        ':ctype' => $input['ctype'],
        ':ddate' => $input['ddate'] ?? $input['drecordingdate'],
        ':cvaccinationstatus' => $input['cvaccinationstatus'] ?? null,
        ':dcheckdate' => $input['dcheckdate'] ?? null,
        ':dchecktime' => $input['dchecktime'] ?? null,
        ':cstaffid' => $input['cstaffid'] ?? 'SM004',
        ':ctortoiseid' => $input['ctortoiseid']
    ]);

    echo json_encode(['success' => true, 'message' => 'Medical record added', 'record_id' => $newId]);
} catch (Throwable $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>


