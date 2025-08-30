<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: DELETE');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/database.php';

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    try {
        // Validate required fields
        if (empty($input['feedingID'])) {
            throw new Exception('Feeding ID is required');
        }

        // Check if feeding schedule exists
        $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM tblfeedingschedule WHERE cfeedingid = ?");
        $checkStmt->execute([$input['feedingID']]);
        
        if ($checkStmt->fetchColumn() == 0) {
            throw new Exception('Feeding schedule not found');
        }

        // Delete feeding schedule
        $stmt = $pdo->prepare("DELETE FROM tblfeedingschedule WHERE cfeedingid = ?");
        $stmt->execute([$input['feedingID']]);
        
        echo json_encode([
            'status' => 'success',
            'message' => 'Feeding schedule deleted successfully',
            'feedingID' => $input['feedingID']
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
} else {
    http_response_code(405);
    echo json_encode([
        'status' => 'error',
        'message' => 'Method not allowed'
    ]);
}
?>