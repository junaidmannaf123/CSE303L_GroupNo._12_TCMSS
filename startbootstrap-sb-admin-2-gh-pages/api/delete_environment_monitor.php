<?php
header('Content-Type: application/json');
require_once '../config/database.php';

$input = json_decode(file_get_contents('php://input'), true);

try {
    if (empty($input['cenvironmentaldataid'])) {
        throw new Exception('Environmental Data ID is required');
    }

    $stmt = $pdo->prepare("DELETE FROM tblenvironmentaldata WHERE cenvironmentaldataid = ?");
    $stmt->execute([$input['cenvironmentaldataid']]);

    echo json_encode([
        'status' => 'success',
        'message' => 'Environmental record deleted successfully'
    ]);
} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error: ' . $e->getMessage()
    ]);
} catch(Exception $e) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>