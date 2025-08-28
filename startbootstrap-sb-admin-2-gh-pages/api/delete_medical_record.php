<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

try {
    $input = json_decode(file_get_contents('php://input'), true);
    if (empty($input['crecordid'])) {
        throw new Exception('Missing record id');
    }

    // Ensure record exists
    $stmt = $pdo->prepare('SELECT crecordid FROM tblmedicalrecords WHERE crecordid = :id');
    $stmt->execute([':id' => $input['crecordid']]);
    if (!$stmt->fetch()) {
        throw new Exception('Record not found');
    }

    $stmt = $pdo->prepare('DELETE FROM tblmedicalrecords WHERE crecordid = :id');
    $stmt->execute([':id' => $input['crecordid']]);

    echo json_encode(['success' => true, 'message' => 'Medical record deleted']);
} catch (Throwable $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>


