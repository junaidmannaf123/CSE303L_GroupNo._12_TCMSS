<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

try {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input || empty($input['id'])) {
        throw new Exception('Missing tortoise ID');
    }
    
    $tortoise_id = $input['id'];
    
    // Check if tortoise exists
    $stmt = $pdo->prepare("SELECT ctortoiseid FROM tbltortoise WHERE ctortoiseid = :id");
    $stmt->execute([':id' => $tortoise_id]);
    
    if (!$stmt->fetch()) {
        throw new Exception('Tortoise not found');
    }
    
    // Check for foreign key constraints
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM tblbreedingrecord WHERE ctortoiseid = :id");
    $stmt->execute([':id' => $tortoise_id]);
    $breeding_count = $stmt->fetch()['count'];
    
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM tbltortoisemeasurement WHERE ctortoiseid = :id");
    $stmt->execute([':id' => $tortoise_id]);
    $measurement_count = $stmt->fetch()['count'];
    
    if ($breeding_count > 0 || $measurement_count > 0) {
        throw new Exception('Cannot delete tortoise: Related records exist in breeding or measurement tables');
    }
    
    // Delete tortoise
    $query = "DELETE FROM tbltortoise WHERE ctortoiseid = :id";
    $stmt = $pdo->prepare($query);
    $stmt->execute([':id' => $tortoise_id]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Tortoise deleted successfully'
    ]);
    
} catch(Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
